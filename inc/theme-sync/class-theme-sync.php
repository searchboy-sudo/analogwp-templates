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

	/**
	 * Get the list of all Stylekits.
	 *
	 * @since @@
	 * @return array List of Stylekits.
	 */
	public function get_stylekits() {
		$defaults = array(
			'post_type' => 'ang_tokens',
			'echo'      => 0,
			'depth'     => 0,
			'child_of'  => 0,
			'nopaging'  => true,
		);

		$array_obj = get_posts( $defaults );

		$cpt_posts = json_decode( wp_json_encode( $array_obj ), true );

		$stylekits    = array();
		$stylekits[0] = __( '-- Select --', 'ang' );

		foreach ( $cpt_posts as $key => $value ) {
			$stylekits[ $value['ID'] ] = $value['post_title'];
		}

		return $stylekits;
	}

	/**
	 * Stylekit export message for front-end.
	 *
	 * @param bool $status Theme options update status.
	 * @return void
	 */
	public function get_stylekit_export_message( $status ) {
		if ( $status ) {
			$message = __( 'Successfully sent to Customizer!', 'ang' );
		} else {
			$message = __( 'Failed to send to Customizer!', 'ang' );
		}

		wp_die( $message );
	}

}
