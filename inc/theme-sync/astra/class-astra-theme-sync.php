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
		if ( 'astra' !== get_stylesheet() ) {
			return;
		}
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
	 *
	 * @return void
	 */
	public function handle_stylekit_customizer_export() {
		$kit_id = $_REQUEST['kit_id'];

		$values = $this->export_stylekit_customizer( $kit_id );

		$this->handle_stylekit_customizer_mapping( $values );
	}

	/**
	 * Handles Stylekit to Customizer final export mapping.
	 *
	 * @param array $values Customizer data.
	 * @return void
	 */
	public function handle_stylekit_customizer_mapping( $values ) {

		$theme_options = get_option( 'astra-settings', [] );

		/**
		 * Make switch case for family, weight, transform, size(responsive), line-height etc.
		 */
		foreach ( $values as $key => $value ) {
			switch ( $key ) {
				case 'ang_body_font_family':
					$theme_options['body-font-family'] = "'$value', sans-serif";
					break;
				case 'ang_body_font_weight':
					$theme_options['body-font-weight'] = $value;
					break;
				case 'ang_body_text_transform':
					$theme_options['body-text-transform'] = $value;
					break;
				case 'ang_body_line_height':
					$theme_options['body-line-height'] = $value;
					break;
				case 'ang_body_font_size':
					$theme_options['font-size-body']['desktop-unit'] = $value['unit'];
					$theme_options['font-size-body']['desktop']      = $value['size'];
					break;
				case 'ang_body_font_size_tablet':
					$theme_options['font-size-body']['tablet-unit'] = $value['unit'];
					$theme_options['font-size-body']['tablet']      = $value['size'];
					break;
				case 'ang_body_font_size_mobile':
					$theme_options['font-size-body']['mobile-unit'] = $value['unit'];
					$theme_options['font-size-body']['mobile']      = $value['size'];
					break;
				case 'ang_default_heading_font_family':
					$theme_options['headings-font-family'] = "'$value', sans-serif";
					break;
				case 'ang_default_heading_font_weight':
					$theme_options['headings-font-weight'] = $value;
					break;
				case 'ang_default_heading_text_transform':
					$theme_options['headings-text-transform'] = $value;
					break;
				case 'ang_heading_1_font_size':
					$theme_options['font-size-h1']['desktop-unit'] = $value['unit'];
					$theme_options['font-size-h1']['desktop']      = $value['size'];
					break;
				case 'ang_heading_1_font_size_tablet':
					$theme_options['font-size-h1']['tablet-unit'] = $value['unit'];
					$theme_options['font-size-h1']['tablet']      = $value['size'];
					break;
				case 'ang_heading_1_font_size_mobile':
					$theme_options['font-size-h1']['mobile-unit'] = $value['unit'];
					$theme_options['font-size-h1']['mobile']      = $value['size'];
					break;
				case 'ang_heading_2_font_size':
					$theme_options['font-size-h2']['desktop-unit'] = $value['unit'];
					$theme_options['font-size-h2']['desktop']      = $value['size'];
					break;
				case 'ang_heading_2_font_size_tablet':
					$theme_options['font-size-h2']['tablet-unit'] = $value['unit'];
					$theme_options['font-size-h2']['tablet']      = $value['size'];
					break;
				case 'ang_heading_2_font_size_mobile':
					$theme_options['font-size-h2']['mobile-unit'] = $value['unit'];
					$theme_options['font-size-h2']['mobile']      = $value['size'];
					break;
				case 'ang_heading_3_font_size':
					$theme_options['font-size-h3']['desktop-unit'] = $value['unit'];
					$theme_options['font-size-h3']['desktop']      = $value['size'];
					break;
				case 'ang_heading_3_font_size_tablet':
					$theme_options['font-size-h3']['tablet-unit'] = $value['unit'];
					$theme_options['font-size-h3']['tablet']      = $value['size'];
					break;
				case 'ang_heading_3_font_size_mobile':
					$theme_options['font-size-h3']['mobile-unit'] = $value['unit'];
					$theme_options['font-size-h3']['mobile']      = $value['size'];
					break;
				case 'ang_heading_4_font_size':
					$theme_options['font-size-h4']['desktop-unit'] = $value['unit'];
					$theme_options['font-size-h4']['desktop']      = $value['size'];
					break;
				case 'ang_heading_4_font_size_tablet':
					$theme_options['font-size-h4']['tablet-unit'] = $value['unit'];
					$theme_options['font-size-h4']['tablet']      = $value['size'];
					break;
				case 'ang_heading_4_font_size_mobile':
					$theme_options['font-size-h4']['mobile-unit'] = $value['unit'];
					$theme_options['font-size-h4']['mobile']      = $value['size'];
					break;
				case 'ang_heading_5_font_size':
					$theme_options['font-size-h5']['desktop-unit'] = $value['unit'];
					$theme_options['font-size-h5']['desktop']      = $value['size'];
					break;
				case 'ang_heading_5_font_size_tablet':
					$theme_options['font-size-h5']['tablet-unit'] = $value['unit'];
					$theme_options['font-size-h5']['tablet']      = $value['size'];
					break;
				case 'ang_heading_5_font_size_mobile':
					$theme_options['font-size-h5']['mobile-unit'] = $value['unit'];
					$theme_options['font-size-h5']['mobile']      = $value['size'];
					break;
				case 'ang_heading_6_font_size':
					$theme_options['font-size-h6']['desktop-unit'] = $value['unit'];
					$theme_options['font-size-h6']['desktop']      = $value['size'];
					break;
				case 'ang_heading_6_font_size_tablet':
					$theme_options['font-size-h6']['tablet-unit'] = $value['unit'];
					$theme_options['font-size-h6']['tablet']      = $value['size'];
					break;
				case 'ang_heading_6_font_size_mobile':
					$theme_options['font-size-h6']['mobile-unit'] = $value['unit'];
					$theme_options['font-size-h6']['mobile']      = $value['size'];
					break;
				default:
					break;
			}
		}

		update_option( 'astra-settings', $theme_options );

		$message = new WP_Error( 'ang-success', __( 'Successfully sent to Customizer!', 'ang' ) );
		if ( is_wp_error( $message ) ) {
			wp_die( $message );
		}
	}

	/**
	 * Register Customizer settings & controls for Customizer to Stylekits export.
	 *
	 * @param obj $wp_customize WP Customizer Object.
	 * @return void
	 */
	public function customizer_export( $wp_customize ) {

		// Creates a section for Analog Stylekit.
		$wp_customize->add_section(
			'analogwp-stylekits-sync',
			array(
				'title'    => __( 'Analog Stylekit Sync', 'ang' ),
				'priority' => 999,
			)
		);

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
				'section'     => 'analogwp-stylekits-sync',
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
					'section'  => 'analogwp-stylekits-sync',
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
					'section'  => 'analogwp-stylekits-sync',
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
					'section'  => 'analogwp-stylekits-sync',
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

		$token_data['ang_body_typography'] = 'custom';
		foreach ( $data as $key => $value ) {
			switch ( $value[0] ) {
				case 'astra-settings[body-font-family]':
					$token_data['ang_body_font_family'] = $value[1];
					break;
				case 'astra-settings[body-font-weight]':
					$token_data['ang_body_font_weight'] = $value[1];
					break;
				case 'astra-settings[body-text-transform]':
					$token_data['ang_body_text_transform'] = $value[1];
					break;
				case 'astra-settings[body-line-height]':
					$token_data['ang_body_line_height']['unit'] = 'em';
					$token_data['ang_body_line_height']['size'] = (float) $value[1];
					break;
				case 'astra-settings[font-size-body]':
					// Desktop.
					$token_data['ang_body_font_size']['unit'] = $value[1]['desktop-unit'];
					$token_data['ang_body_font_size']['size'] = $value[1]['desktop'];
					// Tablet.
					$token_data['ang_body_font_size_tablet']['unit'] = $value[1]['tablet-unit'];
					$token_data['ang_body_font_size_tablet']['size'] = $value[1]['tablet'];
					// Mobile.
					$token_data['ang_body_font_size_mobile']['unit'] = $value[1]['mobile-unit'];
					$token_data['ang_body_font_size_mobile']['size'] = $value[1]['mobile'];
					break;
				case 'astra-settings[headings-font-family]':
					$token_data['ang_default_heading_font_family'] = $value[1];
					break;
				case 'astra-settings[headings-font-weight]':
					$token_data['ang_default_heading_font_weight'] = $value[1];
					break;
				case 'astra-settings[headings-text-transform]':
					$token_data['ang_default_heading_text_transform'] = $value[1];
					break;
				case 'astra-settings[font-size-h1]':
					// Desktop.
					$token_data['ang_heading_1_font_size']['unit'] = $value[1]['desktop-unit'];
					$token_data['ang_heading_1_font_size']['size'] = $value[1]['desktop'];
					// Tablet.
					$token_data['ang_heading_1_font_size_tablet']['unit'] = $value[1]['tablet-unit'];
					$token_data['ang_heading_1_font_size_tablet']['size'] = $value[1]['tablet'];
					// Mobile.
					$token_data['ang_heading_1_font_size_mobile']['unit'] = $value[1]['mobile-unit'];
					$token_data['ang_heading_1_font_size_mobile']['size'] = $value[1]['mobile'];
					break;
				case 'astra-settings[font-size-h2]':
					// Desktop.
					$token_data['ang_heading_2_font_size']['unit'] = $value[1]['desktop-unit'];
					$token_data['ang_heading_2_font_size']['size'] = $value[1]['desktop'];
					// Tablet.
					$token_data['ang_heading_2_font_size_tablet']['unit'] = $value[1]['tablet-unit'];
					$token_data['ang_heading_2_font_size_tablet']['size'] = $value[1]['tablet'];
					// Mobile.
					$token_data['ang_heading_2_font_size_mobile']['unit'] = $value[1]['mobile-unit'];
					$token_data['ang_heading_2_font_size_mobile']['size'] = $value[1]['mobile'];
					break;
				case 'astra-settings[font-size-h3]':
					// Desktop.
					$token_data['ang_heading_3_font_size']['unit'] = $value[1]['desktop-unit'];
					$token_data['ang_heading_3_font_size']['size'] = $value[1]['desktop'];
					// Tablet.
					$token_data['ang_heading_3_font_size_tablet']['unit'] = $value[1]['tablet-unit'];
					$token_data['ang_heading_3_font_size_tablet']['size'] = $value[1]['tablet'];
					// Mobile.
					$token_data['ang_heading_3_font_size_mobile']['unit'] = $value[1]['mobile-unit'];
					$token_data['ang_heading_3_font_size_mobile']['size'] = $value[1]['mobile'];
					break;
				case 'astra-settings[font-size-h4]':
					// Desktop.
					$token_data['ang_heading_4_font_size']['unit'] = $value[1]['desktop-unit'];
					$token_data['ang_heading_4_font_size']['size'] = $value[1]['desktop'];
					// Tablet.
					$token_data['ang_heading_4_font_size_tablet']['unit'] = $value[1]['tablet-unit'];
					$token_data['ang_heading_4_font_size_tablet']['size'] = $value[1]['tablet'];
					// Mobile.
					$token_data['ang_heading_4_font_size_mobile']['unit'] = $value[1]['mobile-unit'];
					$token_data['ang_heading_4_font_size_mobile']['size'] = $value[1]['mobile'];
					break;
				case 'astra-settings[font-size-h5]':
					// Desktop.
					$token_data['ang_heading_5_font_size']['unit'] = $value[1]['desktop-unit'];
					$token_data['ang_heading_5_font_size']['size'] = $value[1]['desktop'];
					// Tablet.
					$token_data['ang_heading_5_font_size_tablet']['unit'] = $value[1]['tablet-unit'];
					$token_data['ang_heading_5_font_size_tablet']['size'] = $value[1]['tablet'];
					// Mobile.
					$token_data['ang_heading_5_font_size_mobile']['unit'] = $value[1]['mobile-unit'];
					$token_data['ang_heading_5_font_size_mobile']['size'] = $value[1]['mobile'];
					break;
				case 'astra-settings[font-size-h6]':
					// Desktop.
					$token_data['ang_heading_6_font_size']['unit'] = $value[1]['desktop-unit'];
					$token_data['ang_heading_6_font_size']['size'] = $value[1]['desktop'];
					// Tablet.
					$token_data['ang_heading_6_font_size_tablet']['unit'] = $value[1]['tablet-unit'];
					$token_data['ang_heading_6_font_size_tablet']['size'] = $value[1]['tablet'];
					// Mobile.
					$token_data['ang_heading_6_font_size_mobile']['unit'] = $value[1]['mobile-unit'];
					$token_data['ang_heading_6_font_size_mobile']['size'] = $value[1]['mobile'];
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

		$post_meta = json_decode( get_post_meta( $kit_id, '_tokens_data', true ), true );

		// Prepare tokens_data for update in post_meta.
		$tokens_data = array_merge( $post_meta, $token_data );

		$tokens_data = wp_json_encode( $tokens_data );

		return update_post_meta( $kit_id, '_tokens_data', $tokens_data );
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

		$export_status = $this->customizer_stylekit_export( $data, $kit_id );

		// To be further improvised.
		if ( '0' !== $kit_id && $export_status ) {
			echo 'Data Saved!';
		} else {
			echo 'Failed to save!';
		}

		wp_die(); // This is required to terminate immediately and return a proper response.
	}
}

new Astra_Theme_Sync();
