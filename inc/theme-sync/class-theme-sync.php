<?php
/**
 * Analog Theme Stylekits & Customizer Sync.
 *
 * @package AnalogWP
 */

namespace Analog;

use WP_Post;

/**
 * Analog Elementor Tools.
 *
 * @package Analog
 * @since @@
 */
abstract class Theme_Sync {

	abstract protected function add_actions();

	abstract protected function enqueue_scripts();

	abstract protected function get_stylekit_export_link( $kit_id );

	abstract public function post_row_actions( $actions, WP_Post $post );

	abstract public function prepare_stylekit_customizer_export( $kit_id );

	abstract public function export_stylekit_customizer( $kit_id );

	abstract public function handle_stylekit_customizer_export();

	abstract public function handle_stylekit_customizer_mapping( $values );

}
