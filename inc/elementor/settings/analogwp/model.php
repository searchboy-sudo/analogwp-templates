<?php

namespace Analog\Elementor\Settings\AnalogWP;

use Elementor\Controls_Manager;
use Elementor\Core\Settings\Base\Model as BaseModel;

defined( 'ABSPATH' ) || exit;

class Model extends BaseModel {

	/**
	 * Get model name.
	 *
	 * Retrieve global settings model name.
	 *
	 * @access public
	 * @return string Model name.
	 */
	public function get_name() {
		return 'ang-styles';
	}

	/**
	 * Get CSS wrapper selector.
	 *
	 * Retrieve the wrapper selector for the global settings model.
	 *
	 * @access public
	 * @return string CSS wrapper selector.
	 */
	public function get_css_wrapper_selector() {
		return '';
	}

	/**
	 * Get panel page settings.
	 *
	 * Retrieve the panel setting for the global settings model.
	 *
	 * @access public
	 * @return array {
	 *    Panel settings.
	 *
	 *    @type string $title The panel title.
	 *    @type array  $menu  The panel menu.
	 * }
	 */
	public function get_panel_page_settings() {
		return [
			'title' => __( 'AnalogWP Settings', 'ang' ),
			'menu'  => [
				'icon'       => 'fa fa-cogs',
				'beforeItem' => 'elementor-settings',
			],
		];
	}

	/**
	 * Register model controls.
	 *
	 * Used to add new controls to the global settings model.
	 *
	 * @since 1.6.0
	 * @access protected
	 */
	protected function _register_controls() {
		$controls_list = self::get_controls_list();

		foreach ( $controls_list as $tab_name => $sections ) {

			foreach ( $sections as $section_name => $section_data ) {

				$this->start_controls_section(
					$section_name,
					[
						'label' => $section_data['label'],
						'tab'   => $tab_name,
					]
				);

				foreach ( $section_data['controls'] as $control_name => $control_data ) {
					$this->add_control( $control_name, $control_data );
				}

				$this->end_controls_section();
			}
		}
	}

	/**
	 * Get controls list.
	 *
	 * Retrieve the global settings model controls list.
	 *
	 * @since 1.6.0
	 * @access public
	 * @static
	 *
	 * @return array Controls list.
	 */
	public static function get_controls_list() {
		return [
			Controls_Manager::TAB_STYLE => [
				'stylezz' => [
					'label'    => __( 'Style', 'elementor' ),
					'controls' => [
						'elementor_default_generic_fontszz' => [
							'label'       => __( 'Default Generic Fonts', 'elementor' ),
							'type'        => Controls_Manager::TEXT,
							'default'     => 'Sans-serif',
							'description' => __( 'The list of fonts used if the chosen font is not available.', 'elementor' ),
							'label_block' => true,
						],
					],
				],
			],
		];
	}
}

new Model();
