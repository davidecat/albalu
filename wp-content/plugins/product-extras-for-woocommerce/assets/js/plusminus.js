/**
 * Plus/minus buttons for Products field quantity inputs
 * @since 4.1.4
 * @package WooCommerce Product Add-Ons Ultimate
 */
( function( $ ) {

	'use strict';

	function updateButtonStates( $input ) {
		var val     = parseFloat( $input.val() ) || 0;
		var minAttr = $input.attr( 'min' );
		var maxAttr = $input.attr( 'max' );
		var min     = ( minAttr !== undefined && minAttr !== '' ) ? parseFloat( minAttr ) : null;
		var max     = ( maxAttr !== undefined && maxAttr !== '' ) ? parseFloat( maxAttr ) : null;
		var $wrapper = $input.closest( '.pewc-plusminus-wrapper' );

		$wrapper.find( '.pewc-minus-btn' ).prop( 'disabled', min !== null && val <= min );
		$wrapper.find( '.pewc-plus-btn' ).prop( 'disabled', max !== null && val >= max );
	}

	function attachButton( $btn, $input ) {
		// Direct binding so stopPropagation fires before the event reaches body-level handlers
		$btn.on( 'click', function( e ) {
			e.stopPropagation();
			e.preventDefault();

			var step    = parseFloat( $input.attr( 'step' ) ) || 1;
			var minAttr = $input.attr( 'min' );
			var maxAttr = $input.attr( 'max' );
			var min     = ( minAttr !== undefined && minAttr !== '' ) ? parseFloat( minAttr ) : null;
			var max     = ( maxAttr !== undefined && maxAttr !== '' ) ? parseFloat( maxAttr ) : null;
			var val     = parseFloat( $input.val() ) || 0;
			var newVal  = $btn.hasClass( 'pewc-plus-btn' ) ? val + step : val - step;

			if ( min !== null && newVal < min ) { newVal = min; }
			if ( max !== null && newVal > max ) { newVal = max; }

			$input.val( newVal ).trigger( 'change' );
			updateButtonStates( $input );
		} );
	}

	function initPlusMinusButtons() {
		$( '.pewc-child-quantity-field, .pewc-grid-quantity-field' ).each( function() {
			var $input = $( this );

			// Skip if already initialised
			if ( $input.closest( '.pewc-plusminus-wrapper' ).length ) {
				return;
			}

			$input.wrap( '<span class="pewc-plusminus-wrapper"></span>' );
			var $wrapper = $input.parent();

			var $minus = $( '<button type="button" class="pewc-plusminus-btn pewc-minus-btn" aria-label="' + pewc_plusminus_vars.decrease_label + '">-</button>' );
			var $plus  = $( '<button type="button" class="pewc-plusminus-btn pewc-plus-btn" aria-label="' + pewc_plusminus_vars.increase_label + '">+</button>' );

			$wrapper.prepend( $minus );
			$wrapper.append( $plus );

			var inputHeight = $input.outerHeight();
			if ( inputHeight ) {
				var btnBorderY = parseInt( $minus.css( 'borderTopWidth' ) ) + parseInt( $minus.css( 'borderBottomWidth' ) );
				var btnHeight  = inputHeight - btnBorderY;
				$minus.height( btnHeight );
				$plus.height( btnHeight );
			}

			attachButton( $minus, $input );
			attachButton( $plus,  $input );

			updateButtonStates( $input );
		} );
	}

	$( document ).on( 'change input', '.pewc-child-quantity-field, .pewc-grid-quantity-field', function() {
		updateButtonStates( $( this ) );
	} );

	$( document ).ready( function() {
		initPlusMinusButtons();
	} );

	// Re-init after conditions or AJAX events update the DOM
	$( document ).on( 'pewc_conditions_updated pewc_fields_updated', function() {
		initPlusMinusButtons();
	} );

} )( jQuery );
