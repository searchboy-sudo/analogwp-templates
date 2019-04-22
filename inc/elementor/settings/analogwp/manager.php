<?php

namespace Analog\Elementor\Settings\AnalogWP;

use Elementor\Utils;
use Elementor\Core\Files\CSS\Base;
use Elementor\Core\Settings\Base\Manager as BaseManager;
use Elementor\Core\Settings\Base\Model as BaseModel;

/**
 * AnalogWP Settings Manager class.
 *
 * @uses BaseManager
 * @since 1.2.0
 */
class Manager extends BaseManager {
	/**
	 * Meta key for the page settings.
	 */
	const META_KEY = '_elementor_page_settings';

	/**
	 * Get manager name.
	 *
	 * Retrieve page settings manager name.
	 *
	 * @access public
	 * @return string Manager name.
	 */
	public function get_name() {
		return 'analogwp';
	}

	/**
	 * Get model for config.
	 *
	 * Retrieve the model for settings configuration.
	 *
	 * @access public
	 * @return BaseModel The model object.
	 */
	public function get_model_for_config() {
		return $this->get_model();
	}

	/**
	 * Save settings to DB.
	 *
	 * Save page settings to the database, as post meta data.
	 *
	 * @access protected
	 * @param array $settings Settings.
	 * @param int   $id       Post ID.
	 */
	protected function save_settings_to_db( array $settings, $id ) {
		// Use update/delete_metadata in order to handle also revisions.
		if ( ! empty( $settings ) ) {
			update_metadata( 'post', $id, self::META_KEY, $settings );
		} else {
			delete_metadata( 'post', $id, self::META_KEY );
		}
	}

	/**
	 * Get model for CSS file.
	 *
	 * Retrieve the model for the CSS file.
	 *
	 * @access protected
	 * @param Base $css_file The requested CSS file.
	 * @return BaseModel The model object.
	 */
	protected function get_model_for_css_file( Base $css_file ) {
		return $this->get_model();
	}

	/**
	 * Get CSS file for update.
	 *
	 * Retrieve the CSS file before updating it.
	 *
	 * This method overrides the parent method to disallow updating CSS files for pages.
	 *
	 * @access protected
	 *
	 * @param int $id Post ID.
	 *
	 * @return false Disallow The updating CSS files for pages.
	 */
	protected function get_css_file_for_update( $id ) {
		return false;
	}

	/**
	 * Get saved settings.
	 *
	 * Retrieve the saved settings from the post meta.
	 *
	 * @access protected
	 * @param int $id Post ID.
	 * @return array Saved settings.
	 */
	protected function get_saved_settings( $id ) {
		$settings = get_post_meta( $id, self::META_KEY, true );

		if ( ! $settings ) {
			$settings = [];
		}

		if ( Utils::is_cpt_custom_templates_supported() ) {
			$saved_template = get_post_meta( $id, '_wp_page_template', true );

			if ( $saved_template ) {
				$settings['template'] = $saved_template;
			}
		}

		return $settings;
	}

	/**
	 * Get CSS file name.
	 *
	 * Retrieve CSS file name for the page settings manager.
	 *
	 * @return string CSS file name.
	 */
	protected function get_css_file_name() {
		return 'post';
	}
}

new Manager();
