/**
 * Used by AJAX Conditions
 * @since 4.4.0
 */
const pewc_ajax_conditions = {

	has_options: [ 'checkbox_group', 'image_swatch', 'products', 'product-categories', 'radio', 'select', 'select-box' ],

	init: function() {

		jQuery( document ).ready( function(){

			jQuery( '.pewc-action.update-condition' )
				.off( 'click', pewc_ajax_conditions.update_ajax_condition )
				.on( 'click', pewc_ajax_conditions.update_ajax_condition );
			jQuery( '.pewc-action.undo-condition' )
				.off( 'click', pewc_ajax_conditions.undo_ajax_condition )
				.on( 'click', pewc_ajax_conditions.undo_ajax_condition );
			jQuery( 'body' ).on( 'pewc_ajax_add_value_field', pewc_ajax_conditions.ajax_add_value_field );

		});

	},

	update_ajax_condition: function( e ) {

		var row = jQuery( this ).closest( '.pewc-ajax-condition-row' );
		if ( row.length < 1 ) {
			return;
		}

		var group_id = jQuery( this ).closest( '.group-row' ).attr( 'data-group-id' );
		var field_id = row.attr( 'data-field-id' );

		//var condition_row_id = 'pewc_ajax_condition_' + group_id;
		//if ( field_id ) {
		//	condition_row_id += '_' + field_id;
		//}

		if ( row.hasClass( 'product-extra-action-match-row' ) ) {

			// retrieve action-match row
			var action_match = jQuery( '.new-field-list .product-extra-action-match-row' );
			if ( action_match.length > 0 ) {
				var action_select = action_match.find( 'select.pewc-condition-action' );
				if ( action_select.length > 0 ) {
					var clone_action = action_select.clone();
					row.find( '.pewc-ajax-condition-edit-action' ).append( clone_action ).show();
					var action_name = '_product_extra_groups_' + group_id;
					if ( field_id ) {
						action_name += '_' + field_id;
					}
					action_name += '[condition_action]';
					if ( jQuery( 'input[name="' + action_name + '"]' ).length > 0 ) {
						clone_action.val( jQuery( 'input[name="' + action_name + '"]' ).val() );
					}
					clone_action.attr( 'name', action_name ); // same name as hidden field, overwrites the old value?
				}
				var match_select = action_match.find( 'select.pewc-condition-condition' );
				if ( match_select.length > 0 ) {
					var clone_match = match_select.clone();
					row.find( '.pewc-ajax-condition-edit-match' ).append( clone_match ).show();
					var match_name = '_product_extra_groups_' + group_id;
					if ( field_id ) {
						match_name += '_' + field_id;
					}
					match_name += '[condition_match]';
					if ( jQuery( 'input[name="' + match_name + '"]' ).length > 0 ) {
						clone_match.val( jQuery( 'input[name="' + match_name + '"]' ).val() );
					}
					clone_match.attr( 'name', match_name ); // same name as hidden field, overwrites the old value?
				}
			}

		} else {

			// conditional row
			var condition_count = row.attr( 'data-condition-count' );
			var row_id = row.attr( 'id' );
			var new_conditional_row = jQuery( '.new-conditional-row' );
			if ( new_conditional_row.length > 0 ) {

				// condition field
				new_conditional_row.find( 'div' ).eq(0).clone().appendTo( row.find( '.pewc-ajax-condition-edit-field' ) );
				// remove cloned hidden field type, let's use the one already outputted?
				row.find( '.pewc-ajax-condition-edit-field .pewc-hidden-field-type' ).remove();
				var field_select = row.find( '.pewc-ajax-condition-edit-field .pewc-condition-field' );
				if ( field_select.length > 0 ) {
					var field_name = '_product_extra_groups_' + group_id;
					var condition_field_id = 'condition_field_' + group_id;
					if ( field_id ) {
						field_name += '_' + field_id;
						condition_field_id += '_' + field_id;
					}
					field_name += '[condition_field][' + condition_count + ']';
					condition_field_id += '_' + condition_count;

					if ( field_id ) {
						// current field is already getting removed somewhere?
					} else {
						// remove the optgroup of current group
						field_select.find( 'optgroup[data-group-id="' + group_id + '"]' ).remove();
					}

					// get the value first
					if ( jQuery( 'input[name="' + field_name + '"]' ).length > 0 ) {
						var orig_value = jQuery( 'input[name="' + field_name + '"]' ).val();
						// then set the value
						field_select.val( orig_value );
					}
					if ( ! field_select.hasClass( 'pewc-group-condition-field' ) ) {
						field_select.addClass( 'pewc-group-condition-field' );
					}

					// update name and data
					field_select
						.attr( 'name', field_name )
						.attr( 'id', condition_field_id ) 
						.attr( 'data-group-id', group_id )
						.attr( 'data-condition-id', condition_count )
						.attr( 'data-field-type', row.find( 'pewc-hidden-field-type' ).val() )
						.attr( 'data-value', orig_value );

					if ( field_id ) {
						field_select
							.attr( 'data-item-id', field_id )   // ← add this
							.removeClass( 'pewc-group-condition-field' ); // ← and this
					}
				}

				// condition rule
				new_conditional_row.find( 'div' ).eq(1).clone().appendTo( row.find( '.pewc-ajax-condition-edit-rule' ) );
				var rule_select = row.find( '.pewc-ajax-condition-edit-rule .pewc-condition-rule' );
				if ( rule_select.length > 0 ) {
					var rule_name = '_product_extra_groups_' + group_id;
					var condition_rule_id = 'condition_rule_' + group_id;
					if ( field_id ) {
						rule_name += '_' + field_id;
						condition_rule_id += '_' + field_id;
					}
					rule_name += '[condition_rule][' + condition_count + ']';
					condition_rule_id += '_' + condition_count
					// set the value first
					rule_select.val( jQuery( 'input[name="' + rule_name + '"]' ).val() );
					// update name and data
					rule_select
						.attr( 'name', rule_name )
						.attr( 'id', condition_rule_id )
						.attr( 'data-group-id', group_id )
						.attr( 'data-condition-id', condition_count );
				}

			}

			// attach condition events?
			//jQuery( 'body' ).trigger( 'pewc_actions_re_init_conditions', [ '#' + row_id ] );
			jQuery( 'body' ).trigger( 'pewc_actions_re_init_conditions' );
			if ( field_select ) {
				field_select.trigger( 'change' );
				var condition_value_name = '_product_extra_groups_' + group_id;
				if ( field_id ) {
					condition_value_name += '_' + field_id;
				}
				condition_value_name += '[condition_value][' + condition_count + ']';
				row.find( '.pewc-value-select' ).val( jQuery( 'input[name="' + condition_value_name + '"]').val() );
			}

			// add value to the input?
			var condition_value_field = row.find( '.pewc-condition-value' );
			var condition_orig_value = row.find( '.pewc-condition-value-hidden' );
			if ( condition_value_field && condition_orig_value ) {
				condition_value_field.val( condition_orig_value.val() );
				condition_value_field.attr( 'value', condition_orig_value.val() ); // pewc_add_value_field reads attr( 'value' )
				// also add this class so that it is not cleared again when update_conditional_value_fields() is run
				if ( ! condition_value_field.hasClass( 'pewc-condition-set-value' ) ) {
					condition_value_field.addClass( 'pewc-condition-set-value' );
				}
			}

		}

		jQuery( this ).hide();
		row.find( '.pewc-ajax-condition-display' ).hide();
		row.find( '.pewc-ajax-condition-edit' ).show();
		row.find( '.pewc-action.undo-condition' ).show();

	},

	undo_ajax_condition: function( e ) {

		var row = jQuery( this ).closest( '.pewc-ajax-condition-row' );
		if ( row.length < 1 ) {
			return;
		}

		// put back original value of hidden field type?
		var hidden_field_type = row.find( '.pewc-hidden-field-type' );
		if ( hidden_field_type ) {
			var orig_value = hidden_field_type.attr( 'data-original-value' );
			if ( orig_value ) {
				hidden_field_type.val( orig_value );
			}
		}

		jQuery( this ).hide();
		row.find( '.pewc-ajax-condition-display' ).show();
		row.find( '.pewc-ajax-condition-edit' ).html( '' ).hide();
		row.find( '.pewc-action.update-condition' ).show();

	},

	ajax_add_value_field: function( e, select ) {
		var selected = select.find( ':selected' );
		if ( ! selected.hasClass( 'pewc-other-global-fields' ) || ! selected.val() ) {
			return; // only do this for other global fields
		}
		if ( jQuery.inArray( selected.attr( 'data-type' ), pewc_ajax_conditions.has_options ) > -1 ) {
			var row = select.closest( '.product-extra-conditional-row' );
			row.block({
				message: null,
				overlayCSS:  {
					backgroundColor: '#fff',
					opacity:         0.6,
					cursor:          'wait'
				},
			});

			var tmp = selected.val().split( '_' );
			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {
					action: 'pewc_ajax_add_condition_value_options',
					group_id: tmp[2],
					field_id: tmp[3],
					security: jQuery( '#pewc_ajax_condition_nonce' ).val()
				}
			})
			.done( function( data ){
				var value_select = row.find( 'select.pewc-condition-value' );
				if ( value_select && data.length > 0 ) {
					for ( var i in data ) {
						value_select.append(
							jQuery('<option>', {
								value: data[i],
								text: data[i]
							})
						);
					}
				}

				// put back value if we're editing
				var hidden_value = row.find( '.pewc-condition-value-hidden' );
				if ( hidden_value.length > 0 ) {
					value_select.val( hidden_value.val() );
				}

				row.unblock();
			})
			.fail( function( jqXHR, textStatus, errorThrown ){
				var error_message = '';
				if ( jqXHR.responseJSON.data.message ) {
					error_message = jqXHR.responseJSON.data.message;
				} else {
					error_message = textStatus + ': ' + errorThrown;
				}
				row.unblock();
				alert( error_message );
			});
		}
	}

}

pewc_ajax_conditions.init();
