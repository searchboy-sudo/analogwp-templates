/**
 * Astra Theme Sync's Customizer API Data Handlers.
 *
 * @package AnalogWP
 */

( function( $ ) {
	wp.customize.bind( 'ready', function() {
		const customize = this;

		$( '#analogwp-customizer-export' ).on( 'click', function() {

			customize( 'astra-settings[font-size-h1]', function( value ) {
				console.log( value._value );
			} );

			// console.log( 'Something Happened.' );
		} );
	} );
}( jQuery ) );
