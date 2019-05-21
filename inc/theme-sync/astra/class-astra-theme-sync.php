<?php
/**
 * Analog Stylekit <> Theme Customizer Sync for Astra .
 *
 * @package AnalogWP
 */

namespace Analog\Theme_Sync\Astra;

use Analog\Elementor\Tools;
use Analog\Theme_Sync;
use WP_Post;
use WP_Error;

/**
 * Analog Elementor Tools.
 *
 * @package Analog\Theme_Sync\Astra
 * @since @@
 */
class Astra_Theme_Sync extends Theme_Sync {

	/**
	 * Initialize the class.
	 */
	public function __construct() {
		$this->add_actions();
	}

	/**
	 * Add all actions and filters.
	 */
	public function add_actions() {
		add_action( 'customize_register', [ $this, 'customizer_export' ] );

		add_action( 'customize_controls_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

		add_action( 'wp_ajax_analog_customizer_export_action', [ $this, 'handle_customizer_stylekit_export' ] );

		if ( is_admin() ) {
			add_filter( 'post_row_actions', [ $this, 'post_row_actions' ], 10, 2 );

			// Send Stylekit values to Customizer.
			add_action( 'wp_ajax_analog_style_kit_customizer_export', [ $this, 'handle_stylekit_customizer_export' ] );
		}
	}

	/**
	 * Enqueue scripts & styles.
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'analogwp-customizer-controls', ANG_PLUGIN_URL . 'inc/theme-sync/astra/customize-controls.js', [ 'jquery', 'customize-controls' ], filemtime( ANG_PLUGIN_DIR . 'inc/theme-sync/astra/customize-controls.js' ), true );

		$params = array(
			'ajaxurl'    => admin_url( 'admin-ajax.php' ),
			'ajax_nonce' => wp_create_nonce( 'analog_ajax' ),
		);

		wp_localize_script( 'analogwp-customizer-controls', 'ajax_object', $params );
	}

	/**
	 * Get StyleKit values export link.
	 *
	 * Retrieve the link used to send a single stylekit values based on the template
	 * ID.
	 *
	 * @access public
	 * @param int $kit_id The template ID.
	 * @return string Template export URL.
	 */
	public function get_stylekit_export_link( $kit_id ) {
		return add_query_arg(
			[
				'action' => 'analog_style_kit_customizer_export',
				'_nonce' => wp_create_nonce( 'analog_ajax' ),
				'kit_id' => $kit_id,
			],
			admin_url( 'admin-ajax.php' )
		);
	}

	/**
	 * Post row actions.
	 *
	 * Add an export link to the template library action links table list.
	 *
	 * Fired by `post_row_actions` filter.
	 *
	 * @access public
	 *
	 * @param array   $actions An array of row action links.
	 * @param WP_Post $post    The post object.
	 *
	 * @return array An updated array of row action links.
	 */
	public function post_row_actions( $actions, WP_Post $post ) {
		if ( Tools::is_tokens_screen() ) {
			$actions['export-stylekit-customizer'] = sprintf( '<a href="%1$s">%2$s</a>', $this->get_stylekit_export_link( $post->ID ), __( 'Send to Customizer', 'ang' ) );
		}
		return $actions;
	}

	/**
	 * Prepare Style Kit values for Customizer export.
	 *
	 * Retrieve the relevant template data and return them as an array.
	 *
	 * @access public
	 *
	 * @param int $kit_id The template ID.
	 * @return WP_Error|array Exported template data.
	 */
	public function prepare_stylekit_customizer_export( $kit_id ) {
		$tokens = get_post_meta( $kit_id, '_tokens_data', true );

		if ( empty( $tokens ) ) {
			return new WP_Error( 'empty_kit', 'The Style Kit is empty' );
		}

		$kit_data = [];

		$kit_data['content'] = $tokens;
		$kit_data['title']   = get_the_title( $kit_id );

		return json_decode( $kit_data['content'], true );
	}

	/**
	 * Export local template Stylekit values.
	 *
	 * Export template to an array.
	 *
	 * @access public
	 *
	 * @param int $kit_id The Style Kit ID.
	 * @return WP_Error WordPress error if template export failed.
	 */
	public function export_stylekit_customizer( $kit_id ) {
		$file_data = $this->prepare_stylekit_customizer_export( $kit_id );

		if ( is_wp_error( $file_data ) ) {
			echo $file_data->get_error_message();
			return;
		}

		// return file contents.
		return $file_data; // @codingStandardsIgnoreLine
	}

	/**
	 * Handles sending Stylekit values to Customizer.
	 */
	public function handle_stylekit_customizer_export() {
		$kit_id = $_REQUEST['kit_id'];

		$values = $this->export_stylekit_customizer( $kit_id );

		if ( defined( 'ASTRA_THEME_SETTINGS' ) ) {
			$this->handle_stylekit_customizer_mapping( $values );
		}
	}

	/**
	 * @emptyComment.
	 */
	public function handle_stylekit_customizer_mapping( $values ) {
		/**
		 * Make switch case for family, weight, transform, size(responsive), line-height etc.
		 */

		$theme_options = get_option( 'astra-settings', [] );

		$body_font_family = $values['ang_body_font_family'];
		if ( $body_font_family !== $theme_options['body-font-family'] ) {
			$theme_options['body-font-family'] = "'$body_font_family', sans-serif";
		}

		$body_font_weight = $values['ang_body_font_weight'];
		if ( $body_font_weight !== $theme_options['body-font-weight'] ) {
			$theme_options['body-font-weight'] = $body_font_weight;
		}

		$body_text_transform = $values['ang_body_text_transform'];
		if ( $body_text_transform !== $theme_options['body-text-transform'] ) {
			$theme_options['body-text-transform'] = $body_text_transform;
		}

		$body_size = $values['ang_body_text_transform'];
		if ( $body_size !== $theme_options['body-size'] ) {
			$theme_options['body-size'] = $body_size;
		}

		update_option( 'astra-settings', $theme_options );

		$mods = [
			ASTRA_THEME_SETTINGS . '[body-font-family]'    => '',
			ASTRA_THEME_SETTINGS . '[body-font-weight]'    => '',
			ASTRA_THEME_SETTINGS . '[body-text-transform]' => '',
			ASTRA_THEME_SETTINGS . '[font-size-body]'      => '',
			ASTRA_THEME_SETTINGS . '[body-line-height]'    => '',
			ASTRA_THEME_SETTINGS . '[headings-font-family]' => '',
			ASTRA_THEME_SETTINGS . '[headings-font-weight]' => '',
			ASTRA_THEME_SETTINGS . '[headings-text-transform]' => '',
			ASTRA_THEME_SETTINGS . '[font-size-h1]'        => '',
			ASTRA_THEME_SETTINGS . '[font-size-h2]'        => '',
			ASTRA_THEME_SETTINGS . '[font-size-h3]'        => '',
			ASTRA_THEME_SETTINGS . '[font-size-h4]'        => '',
			ASTRA_THEME_SETTINGS . '[font-size-h5]'        => '',
			ASTRA_THEME_SETTINGS . '[font-size-h6]'        => '',
		];
	}

	/**
	 * Register Customizer settings & controls for Customizer to Stylekits export.
	 *
	 * @param obj $wp_customize WP Customizer Object.
	 * @return void
	 */
	public function customizer_export( $wp_customize ) {
		$wp_customize->add_setting(
			'analogwp-stylekits-selector',
			array(
				'default'           => 0,
				'transport'         => 'postMessage',
				'sanitize_callback' => '',
			)
		);

		$wp_customize->add_control(
			'analogwp-stylekits-selector',
			array(
				'label'       => __( 'Analog Stylekits', 'ang' ),
				'description' => '<p>' . __( 'Click to export Customizer Typography to a Stylekit', 'ang' ) . '</p>',
				'type'        => 'select',
				'choices'     => $this->get_stylekits(),
				'section'     => 'section-content-typo',
				'priority'    => 1,
			)
		);

		$wp_customize->add_setting(
			'analogwp-export-customizer-vals-description',
			array()
		);

		$wp_customize->add_control(
			new \Astra_Control_Description(
				$wp_customize,
				'analogwp-export-customizer-vals-description',
				array(
					'label'    => '',
					'type'     => 'ast-description',
					'section'  => 'section-content-typo',
					'help'     => __( 'Select which Stylekit to apply the current Customizer Typography values.', 'ang' ),
					'priority' => 1,
				)
			)
		);

		$wp_customize->add_setting(
			'analogwp-export-customizer-vals',
			array()
		);

		$wp_customize->add_control(
			new \Astra_Control_Description(
				$wp_customize,
				'analogwp-export-customizer-vals',
				array(
					'label'    => '',
					'type'     => 'ast-description',
					'section'  => 'section-content-typo',
					'help'     => '<a id="analogwp-customizer-export" href="#" class="button button-primary" rel="noopener">' . __( 'Send to Stylekit', 'ang' ) . '</a>',
					'priority' => 1,
				)
			)
		);

		$wp_customize->add_setting(
			'analogwp-export-customizer-vals-divider',
			array()
		);

		$wp_customize->add_control(
			new \Astra_Control_Divider(
				$wp_customize,
				'analogwp-export-customizer-vals-divider',
				array(
					'label'    => '',
					'type'     => 'ast-divider',
					'section'  => 'section-content-typo',
					'priority' => 1,
				)
			)
		);
	}

	/**
	 * Handles mapping of Customizer options to Stylekits token data array.
	 *
	 * @access public
	 *
	 * @since @@
	 * @return array Stylekits token data array.
	 */
	public function customizer_stylekit_options_mapping( $data ) {
		$token_data = array();

		$token_data[ 'ang_body_typography' ] = 'custom';
		foreach ( $data as $key => $value ) {
			switch ( $value[0] ) {
				case 'astra-settings[body-font-family]':
					$token_data[ 'ang_body_font_family' ] = $value[1];
					break;
				case 'astra-settings[body-font-weight]':
					$token_data[ 'ang_body_font_weight' ] = $value[1];
					break;
				case 'astra-settings[body-text-transform]':
					$token_data[ 'ang_body_text_transform' ] = $value[1];
					break;
				default:
					break;
			}
		}

		return $token_data;
	}

	/**
	 * Save data in Stylekit from Customizer export.
	 *
	 * @access public
	 *
	 * @since @@
	 * @return bool
	 */
	public function customizer_stylekit_export( $data, $kit_id ) {

		$token_data = $this->customizer_stylekit_options_mapping( $data );

		var_dump( $token_data );

		// Prepare tokens_data for update in post_meta.

		$post_meta  = get_post_meta( $kit_id, '_tokens_data', true );

		// var_dump( json_decode( $post_meta, true ) );
	}

	/**
	 * Handles Customizer export ajax action.
	 *
	 * @access public
	 *
	 * @since @@
	 * @return void
	 */
	public function handle_customizer_stylekit_export() {
		// Check nonce verification.
		check_ajax_referer( 'analog_ajax', 'security' );

		$data = wp_unslash( $_POST['data'] );

		$data = json_decode( $data, true );

		$kit_id = $_POST['kit_id'];

		$this->customizer_stylekit_export( $data, $kit_id );
		// var_dump( $data );

		wp_die(); // this is required to terminate immediately and return a proper response
	}
}

new Astra_Theme_Sync();
