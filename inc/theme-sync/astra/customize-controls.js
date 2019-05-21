/**
 * Astra Theme Sync's Customizer API Data Handlers.
 *
 * @package AnalogWP
 */

( function( $ ) {
	wp.customize.bind( 'ready', function() {
		const customize = this;

		$( '#analogwp-customizer-export' ).on( 'click', function() {
			window.ang_data = new Array();

			customize( 'analogwp-stylekits-selector', function( value ) {
				window.ang_kit_id = value._value;
			} );

			customize( 'astra-settings[body-font-family]', function( value ) {
				window.ang_data[ 0 ] = [ value.id, value._value ];
			} );

			customize( 'astra-settings[body-font-weight]', function( value ) {
				window.ang_data[ 1 ] = [ value.id, value._value ];
			} );

			customize( 'astra-settings[body-text-transform]', function( value ) {
				window.ang_data[ 2 ] = [ value.id, value._value ];
			} );

			customize( 'astra-settings[body-line-height]', function( value ) {
				window.ang_data[ 3 ] = [ value.id, value._value ];
			} );

			customize( 'astra-settings[font-size-body]', function( value ) {
				window.ang_data[ 4 ] = [ value.id, value._value ];
			} );

			customize( 'astra-settings[headings-font-family]', function( value ) {
				window.ang_data[ 5 ] = [ value.id, value._value ];
			} );

			customize( 'astra-settings[headings-font-weight]', function( value ) {
				window.ang_data[ 6 ] = [ value.id, value._value ];
			} );

			customize( 'astra-settings[headings-text-transform]', function(
				value
			) {
				window.ang_data[ 7 ] = [ value.id, value._value ];
			} );

			customize( 'astra-settings[font-size-h1]', function( value ) {
				window.ang_data[ 8 ] = [ value.id, value._value ];
			} );

			customize( 'astra-settings[font-size-h2]', function( value ) {
				window.ang_data[ 9 ] = [ value.id, value._value ];
			} );

			customize( 'astra-settings[font-size-h3]', function( value ) {
				window.ang_data[ 10 ] = [ value.id, value._value ];
			} );

			customize( 'astra-settings[font-size-h4]', function( value ) {
				window.ang_data[ 11 ] = [ value.id, value._value ];
			} );

			customize( 'astra-settings[font-size-h5]', function( value ) {
				window.ang_data[ 12 ] = [ value.id, value._value ];
			} );

			customize( 'astra-settings[font-size-h6]', function( value ) {
				window.ang_data[ 13 ] = [ value.id, value._value ];
			} );

			let data = {
				'action' : 'analog_customizer_export_action',
				'data' : JSON.stringify( window.ang_data, null ),
				'kit_id' : window.ang_kit_id,
				'security' : window.ajax_object.ajax_nonce
			};

			// console.log( window.analogwp );

			$.post( window.ajax_object.ajaxurl, data, function( response ) {
				console.log( response );
			} );

			// console.log( 'Something Happened.' );
		} );
	} );
}( jQuery ) );
