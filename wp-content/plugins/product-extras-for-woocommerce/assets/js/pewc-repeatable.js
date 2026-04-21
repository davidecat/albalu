// since 3.22.0
const pewc_repeatable = {

	init: function() {

		// if the page has been submitted, let's update the group container IDs
		if ( jQuery( '.pewc-cloned-group' ).length > 0 ) {
			var prev_group = 0, curr_count = 2;
			jQuery( '.pewc-cloned-group' ).each( function( index, element ){
				if ( jQuery( this ).attr( 'data-group-id' ) != prev_group ) {
					// new group, reset clone_count
					curr_count = 2;
					prev_group = jQuery( this ).attr( 'data-group-id' );
				} else {
					curr_count++;
				}
				var curr_id = jQuery( this ).attr( 'id' );
				jQuery( this ).attr( 'id', curr_id + '-cloned-' + curr_count );
			});
		}

		if ( jQuery( '.pewc-repeat-by-quantity' ).length > 0 ) {
			// get the max repeat limit and apply it to the product quantity
			var max_repeat_limit = 0;
			jQuery( '.pewc-repeat-by-quantity' ).each( function( index, element ){
				var repeat_limit = parseInt( jQuery( this ).attr( 'data-repeat-limit' ) );
				if ( repeat_limit > max_repeat_limit ) {
					max_repeat_limit = repeat_limit;
				}
				// hide the Add More buttons for repeatable groups attached to quantity
				var group_id = jQuery( this ).attr( 'id' ).replace( 'pewc-repeat-group-count-', '' );
				jQuery( '.pewc-repeat-group-' + group_id ).hide();
			});
			if ( max_repeat_limit > 0 ) {
				jQuery( 'form.cart .qty' ).attr( 'max', max_repeat_limit+1 );
			}

			// attach an on change event to the quantity
			// 3.26.6, changed 'change' to 'keyup input change paste'
			jQuery( 'form.cart .qty' ).on( 'keyup input change paste', function( e ){
				// get the current quantity
				var curr_quantity = parseInt( jQuery( 'form.cart .qty' ).val() );

				if ( isNaN( curr_quantity ) ) {
					// 3.26.6
					return;
				}

				// repeat all groups that are dependent on quantity
				jQuery( '.pewc-repeat-by-quantity' ).each( function( index, element ){
					//var repeat_button = jQuery( this ).find( '.pewc-repeat-group-button' );
					//var group_id = repeat_button.attr( 'id' ).replace( 'pewc-repeat-group-', '' );
					var group_id = jQuery( this ).attr( 'id' ).replace( 'pewc-repeat-group-count-', '' );
					var prev_quantity = parseInt( jQuery( '#pewc-repeat-group-count-' + group_id ).val() ); //parseInt( repeat_button.attr( 'data-prev-quantity' ) );
					var loop_counter = 1;

					if ( prev_quantity == curr_quantity ) {
						return;
					} else if ( isNaN( prev_quantity ) || curr_quantity > prev_quantity ) {
						// repeat the group
						// 3.26.6, we now use looping in case a customer types a quantity that is more than 1 from the previous quantity e.g. from 1 to 4
						if ( ! isNaN( prev_quantity ) ) {
							loop_counter = prev_quantity;
						}
						for ( var i = loop_counter; i < curr_quantity; i++ ) {
							pewc_repeatable.repeat_group( group_id, i+1 );
						}
						//pewc_repeatable.repeat_group( group_id, curr_quantity );
					} else {
						// hide a repeated group
						// 3.26.6, we now use looping
						for ( var i = prev_quantity; i > curr_quantity; i-- ) {
							pewc_repeatable.hide_repeated_group( group_id, i-1 );
						}
						//pewc_repeatable.hide_repeated_group( group_id, curr_quantity );
					}

					// hide Add More buttons
					jQuery( '.pewc-repeat-group-' + group_id ).hide();

					// keep track of the quantity
					//repeat_button.attr( 'data-prev-quantity', curr_quantity );
					jQuery( '#pewc-repeat-group-count-' + group_id ).val( curr_quantity );
				});
			});

			// 3.26.15, if default quantity is > 1, trigger quantity field so that groups are auto-cloned on load. Only do this if the page hasn't been submitted already?
			if ( jQuery( 'form.cart .qty' ).val() && jQuery( 'form.cart .qty' ).val() > 1 && jQuery( '.pewc-cloned-group' ).length < 1 ) {
				jQuery( 'form.cart .qty' ).trigger( 'change' );
			}
		}

		if ( jQuery( '.pewc-repeat-group-button' ).length > 0 ) {
			pewc_repeatable.attach_event_to_repeat_button();
		}

	},

	// 3.26.14, separated this into a function so that we can call this again when a group and the button is cloned
	attach_event_to_repeat_button() {
		// attach the click event to the Add More button for groups that are not repeatable by quantity
		jQuery( '.pewc-repeat-group-button' ).not( '.pewc-clickable' ).each( function( e ){
			jQuery( this ).addClass( 'pewc-clickable' );
			jQuery( this ).on( 'click', function( e ){
				e.preventDefault();
				var group_id = jQuery( this ).attr( 'id' ).replace( 'pewc-repeat-group-', '' );
				return pewc_repeatable.repeat_group( group_id );
			});
		});
	},

	is_repeatable_field( field_type ) {

		if ( pewc_vars.repeatable_fields && pewc_vars.repeatable_fields.length > 0 ) {
			return pewc_vars.repeatable_fields.includes( field_type );
		}

		return false;

	},

	repeat_group: function( group_id, curr_quantity ) {

		var repeat_button = jQuery( '#pewc-repeat-group-' + group_id );
		if ( repeat_button.length < 1 ) {
			return; // button not found
		}
		repeat_button.removeClass( 'pewc-clickable' ); // so that we can attach the click event later
		var clone_count = parseInt( jQuery( '#pewc-repeat-group-count-' + group_id ).val() );
		var repeat_limit = parseInt( jQuery( '#pewc-repeat-group-count-' + group_id ).attr( 'data-repeat-limit' ) );
		var repeat_labeling = repeat_button.attr( 'data-repeat-labeling' );
		var repeat_label_format = repeat_button.attr( 'data-repeat-label-format' );

		if ( jQuery( '.pewc-cloned-group-' + group_id ).length >= curr_quantity-1 ) {
			// we may have some hidden cloned groups, show them instead, but only if he original group is visible (maybe condition dependent)
			var counter = 2;
			jQuery( '.pewc-cloned-group-' + group_id ).each( function( index, element ){
				if ( counter <= curr_quantity ) {
					//if ( ! jQuery( '#pewc-group-' + group_id ).hasClass( 'pewc-group-hidden' ) ) {
					//	jQuery( this ).removeClass( 'pewc-group-hidden' );
					//}
					jQuery( this ).removeClass( 'pewc-cloned-hidden' );
				}
				counter++;
			});
			return;
		}
		if ( clone_count >= repeat_limit+1 ) {
			return; // we've reached the limit, don't do anything
		}

		// clone the group and add it above the button
		var orig_title = repeat_button.attr( 'data-group-title' );
		var parent_group = jQuery( '#pewc-group-' + group_id );
		var cloned_group = parent_group.clone();//.insertBefore( '.pewc-repeat-group-' + group_id ); // 3.26.5, commented out because of issues in radio buttons

		// change IDs and add some classes, and remove fields that are not allowed to be cloned
		clone_count++;
		cloned_group.attr( 'id', 'pewc-group-' + group_id + '-cloned-' + clone_count );
		cloned_group.addClass( 'pewc-cloned-group' );
		cloned_group.addClass( 'pewc-cloned-group-' + group_id );

		if ( 'group' === repeat_labeling && orig_title != '' ) {
			// change the group title
			//var new_title = orig_title + ' ' + clone_count;
			var new_title = repeat_label_format.replace( '[group_title]', orig_title ).replace( '[clone_count]', clone_count );
			var orig_html = cloned_group.find( '.pewc-group-heading-wrapper' ).html();
			cloned_group.find( '.pewc-group-heading-wrapper' ).html( orig_html.replace( orig_title, new_title ) );
		}

		cloned_group.find( '.pewc-item' ).each(function( index, element ){

			if ( ! pewc_repeatable.is_repeatable_field( jQuery( this ).attr( 'data-field-type' ) ) ) {
				jQuery( this ).remove();
				return; // 3.26.5, no need to proceed
			}
			if ( 'field' === repeat_labeling ) {
				// 3.26.18, moved to new function
				pewc_repeatable.update_field_label( jQuery( this ), repeat_label_format, clone_count );
			}

			// 3.26.6, add unique classes?
			if ( ! jQuery( this ).hasClass( 'pewc-cloned-field-' + clone_count ) ) {
				jQuery( this ).addClass( 'pewc-cloned-field-' + clone_count );
			}

			// reset value
			jQuery( this ).find( 'input.pewc-form-field' ).val( '' );
			//jQuery( this ).find( 'input.pewc-form-field' ).attr( 'id', jQuery( this ).attr( 'data-id' ) + '_' + clone_count ); // 3.26.6, text fields? not sure if needed
			jQuery( this ).attr( 'data-field-value', jQuery( this ).attr( 'data-default-value' ) );
			if ( /*jQuery( this ).attr( 'data-field-value' ) == '' &&*/ jQuery( this ).attr( 'data-field-type' ) == 'select' ) {
				// for select fields, default field value is the first item
				jQuery( this ).find( '.pewc-form-field' ).attr( 'id', jQuery( this ).attr( 'data-id' ) + '_' + clone_count ).trigger( 'change' ); // 3.26.6, select fields also wants unique IDs
				jQuery( this ).attr( 'data-field-value', jQuery( this ).find( '.pewc-form-field' ).val() );
			} else if ( /*jQuery( this ).attr( 'data-field-value' ) == '' && */jQuery( this ).attr( 'data-field-type' ) == 'radio' ) {
				// For radio fields, ensure we have unique IDs and names
				id = jQuery( this ).attr( 'data-id' );
				var radio_index = clone_count-1; // 3.26.5, index causes duplicate radio buttons, use clone_count instead
				jQuery( this ).find( 'li' ).each(function( count, el ){
					label = jQuery( this ).find( '.pewc-radio-form-label' ).attr( 'for' );
					jQuery( this ).find( '.pewc-radio-form-label' )
						.attr( 'for', label + '_' + radio_index )
						.removeClass( 'active-swatch' );
					jQuery( this ).find( '.pewc-radio-form-field' )
						.attr( 'id', label + '_' + radio_index )
						.attr( 'name', id + '[' + radio_index + ']' )
						.attr( 'data-orig-label', label ) // 3.26.18, used to update radio button IDs when removing clones
						.prop( 'checked', false ); // uncheck duplicated radio button
				});
			} else if ( jQuery( this ).attr( 'data-field-type' ) == 'checkbox' ) {
				// aou-repeatable-conditions-checkbox
				id = jQuery( this ).attr( 'data-id' );
				var checkbox_index = clone_count-1;
				var label = jQuery( this ).find( '.pewc-checkbox-form-label' ).attr( 'for' );
				jQuery( this ).find( '.pewc-checkbox-form-label' ).attr( 'for', label + '_' + checkbox_index );
				jQuery( this ).find( '.pewc-form-field' ).each( function( index, el ) {
					jQuery( this )
						.attr( 'id', id + '_' + checkbox_index )
						.attr( 'name', id + '[' + checkbox_index + ']')
						.val( '__checked__' ) // put back value
						.prop( 'checked', false ); // uncheck
				});
			} else if ( jQuery( this ).attr( 'data-field-type' ) == 'textarea' ) {
				// aou-repeatable-conditions-textarea
				jQuery( this ).find( 'textarea.pewc-form-field' ).val( '' );
			} else if ( jQuery( this ).attr( 'data-field-type' ) == 'upload' ) {
				// aou-repeatable-conditions-upload
				pewc_repeatable.reset_cloned_field_value_upload( jQuery( this ), clone_count );
			}

			// remove validations
			jQuery( this ).removeClass( 'pewc-passed-validation' );
			jQuery( this ).removeClass( 'pewc-failed-validation' );
			jQuery( this ).find( '.pewc-js-validation-notice' ).html( '' );

		});

		// 3.26.5, moved here. We update IDs first before inserting it to the page because radio button values get reset if it detects duplicate IDs
		//cloned_group.insertBefore( '.pewc-repeat-group-' + group_id );
		// 3.26.14, insert at the end. The above won't work anymore since the button is now inside the group container
		jQuery( '.pewc-group-wrap-' + group_id ).last().after( cloned_group );

		// 3.26.14, hide all repeat buttons except the last one
		pewc_repeatable.update_repeat_buttons( group_id );

		// update clone counter
		jQuery( '#pewc-repeat-group-count-' + group_id ).val( clone_count );

		if ( clone_count >= repeat_limit+1 ) {
			// hide button
			repeat_button.hide();
		}

		// 3.26.18, initialize Remove button
		cloned_group.find( '.pewc-remove-clone' ).on( 'click', function( e ){

			e.preventDefault();
			if ( confirm( pewc_vars.repeatable_confirm_remove ) ) {
				var cloned_group = jQuery( this ).closest( '.pewc-group-wrap' );
				pewc_repeatable.remove_repeated_group( cloned_group );
			}
			return false;

		});

		// 3.26.5, attach condition events, then trigger pewc_trigger_initial_check
		jQuery( document ).trigger( 'pewc_attach_condition_events' );
		jQuery( document ).trigger( 'pewc_trigger_initial_check' );

		return;

	},

	// used only if attached to quantity
	hide_repeated_group: function( group_id, curr_quantity ) {

		var repeat_button = jQuery( '#pewc-repeat-group-' + group_id );
		if ( repeat_button.length < 1 ) {
			return; // button not found
		}
		var clone_count = parseInt( jQuery( '#pewc-repeat-group-count-' + group_id ).val() );
		var repeat_limit = parseInt( jQuery( '#pewc-repeat-group-count-' + group_id ).attr( 'data-repeat-limit' ) );

		if ( clone_count > curr_quantity ) {
			// we have an excess of repeated groups that we need to hide
			var counter = 2; // we always start with 2
			jQuery( '.pewc-cloned-group-' + group_id ).each( function( index, element ) {
				if ( counter > curr_quantity ) {
					jQuery( this )
						//.addClass( 'pewc-group-hidden' )
						.addClass( 'pewc-cloned-hidden' );
				}
				counter++;
			});
		}

		// update clone count
		jQuery( '#pewc-repeat-group-count-' + group_id ).val( curr_quantity );

	},

	// 3.26.14
	update_repeat_buttons: function( group_id ) {

		// hide all buttons for this group first
		jQuery( '.pewc-repeat-group-' + group_id ).hide();
		// show only the last button
		jQuery( '.pewc-repeat-group-' + group_id ).last().show();
		// attach the click events again
		pewc_repeatable.attach_event_to_repeat_button();
	},

	// 3.26.18
	remove_repeated_group: function( cloned_group ) {

		if ( cloned_group.hasClass( 'pewc-cloned-group' ) ) {
			var group_id = jQuery( cloned_group ).attr( 'data-group-id' );
			var repeat_button = jQuery( '#pewc-repeat-group-' + group_id );
			var repeat_labeling = repeat_button.attr( 'data-repeat-labeling' );
			var repeat_label_format = repeat_button.attr( 'data-repeat-label-format' );
			var orig_title = repeat_button.attr( 'data-group-title' );
			var clone_counter = 1;

			//if ( repeat_button.length > 0 ) {
				clone_counter = parseInt( jQuery( '#pewc-repeat-group-count-' + group_id ).val() ); 
				// decrease clone_count
				clone_counter -= 1;
				// update it
				jQuery( '#pewc-repeat-group-count-' + group_id ). val( clone_counter );
			//}
			cloned_group.remove();

			// update group or field labels
			if ( clone_counter > 1 ) {
				var i = 2;
				jQuery( '.pewc-cloned-group-' + group_id ).each( function(){
					// update ID
					jQuery( this ).attr( 'id', 'pewc-group-' + group_id + '-cloned-' + i );

					// update group title
					if ( 'group' === repeat_labeling && orig_title != '' ) {
						// change the group title
						var new_title = repeat_label_format.replace( '[group_title]', orig_title ).replace( '[clone_count]', i );
						var orig_html = jQuery( this ).find( '.pewc-group-heading-wrapper' ).html();
						var old_title = jQuery( orig_html ).text();
						jQuery( this ).find( '.pewc-group-heading-wrapper' ).html( orig_html.replace( old_title, new_title ) );
					}

					// loop through the fields
					jQuery( this ).find( '.pewc-item' ).each( function(){
						if ( 'field' === repeat_labeling ) {
							pewc_repeatable.update_field_label( jQuery( this ), repeat_label_format, i );
						}
						// update radio IDs so that there are no duplicates and they are selectable
						if ( jQuery( this ).attr( 'data-field-type' ) == 'radio' ) {
							// For radio fields, ensure we have unique IDs and names
							id = jQuery( this ).attr( 'data-id' );
							var radio_index = i-1;
							jQuery( this ).find( 'li' ).each(function( count, el ){
								//label = jQuery( this ).find( '.pewc-radio-form-label' ).attr( 'for' );
								label = jQuery( this ).find( '.pewc-radio-form-field' ).attr( 'data-orig-label' );
								jQuery( this ).find( '.pewc-radio-form-label' ).attr( 'for', label + '_' + radio_index );
								jQuery( this ).find( '.pewc-radio-form-field' )
									.attr( 'id', label + '_' + radio_index )
									.attr( 'name', id + '[' + radio_index + ']' );
							});
						} else if ( jQuery( this ).attr( 'data-field-type' ) == 'checkbox' ) {
							// aou-repeatable-conditions-checkbox
							id = jQuery( this ).attr( 'data-id' );
							var checkbox_index = i-1;
							var label = jQuery( this ).find( '.pewc-checkbox-form-label' ).attr( 'for' );
							jQuery( this ).find( '.pewc-checkbox-form-label' ).attr( 'for', label + '_' + checkbox_index );
							jQuery( this ).find( '.pewc-form-field' ).each( function( index, el ) {
								jQuery( this )
									.attr( 'id', id + '_' + checkbox_index )
									.attr( 'name', id + '[' + checkbox_index + ']');
							});
						}
					});
					
					i++;
				});
			}

			pewc_repeatable.update_repeat_buttons( group_id );
		}

	},

	// 3.26.18, created a separate function
	update_field_label: function( pewc_item, repeat_label_format, clone_count ) {

		var label = pewc_item.find( '.pewc-field-label-text' );
		var orig_label = pewc_item.attr( 'data-field-label' ); //label.text();
		var new_label_text = '';
		if ( label && orig_label != '' ) {
			//new_label_text = orig_label + ' ' + clone_count;
			new_label_text = repeat_label_format.replace( '[field_label]', orig_label ).replace( '[clone_count]', clone_count );
			label.text( new_label_text );

			// update labels in optimised validation messages
			var old_message = '';
			if ( pewc_item.attr( 'data-validation-notice' ) != undefined ) {
				old_message = pewc_item.attr( 'data-validation-notice' );
				pewc_item.attr( 'data-validation-notice', old_message.replace( orig_label, new_label_text ) );
			}
			if ( pewc_item.attr( 'data-field-minchars-error' ) != undefined ) {
				old_message = pewc_item.attr( 'data-field-minchars-error' );
				pewc_item.attr( 'data-field-minchars-error', old_message.replace( orig_label, new_label_text ) );
			}
			if ( pewc_item.attr( 'data-field-maxchars-error' ) != undefined ) {
				old_message = pewc_item.attr( 'data-field-maxchars-error' );
				pewc_item.attr( 'data-field-maxchars-error', old_message.replace( orig_label, new_label_text ) );
			}
			if ( pewc_item.attr( 'data-field-minval-error' ) != undefined ) {
				old_message = pewc_item.attr( 'data-field-minval-error' );
				pewc_item.attr( 'data-field-minval-error', old_message.replace( orig_label, new_label_text ) );
			}
			if ( pewc_item.attr( 'data-field-maxval-error' ) != undefined ) {
				old_message = pewc_item.attr( 'data-field-maxval-error' );
				pewc_item.attr( 'data-field-maxval-error', old_message.replace( orig_label, new_label_text ) );
			}
		}

	},

	// aou-repeatable-conditions-uploads
	reset_cloned_field_value_upload: function( cloned_field, clone_count ) {

		if ( clone_count < 2 ) {
			// this is wrong
			console.log('Incorrect clone count:' + clone_count);
			return;
		}

		var pewc_id = cloned_field.attr( 'data-id' );
		var cloned_pewc_id = pewc_id + '_cloned_' + clone_count;

		if ( cloned_field.find( '#dz_' + pewc_id ).length < 1 ) {
			// not an AJAX dropzone, maybe a simple upload
			cloned_field.find( '#' + pewc_id )
				.attr( 'id', cloned_pewc_id )
				.attr( 'name', cloned_pewc_id + '[]' );
			return;
		}

		// replace IDs, so that they are unique?
		cloned_field.find( '#dz_' + pewc_id )
			.html( '' )
			.attr( 'id', 'dz_'+ cloned_pewc_id )
			.attr( 'class', 'dropzone pewc-upload-dropzone' );
		cloned_field.find( '#' + pewc_id )
			.attr( 'id', cloned_pewc_id )
			.val( cloned_pewc_id );
		cloned_field.find( '#' + pewc_id + '_file_data' )
			.attr( 'id', cloned_pewc_id + '_file_data' )
			.val( '' );
		cloned_field.find( '#' + pewc_id + '_number_uploads' )
			.attr( 'id', cloned_pewc_id + '_number_uploads' )
			.val( '' );
		cloned_field.find( '#' + pewc_id + '_multiply_price' ).attr( 'id', cloned_pewc_id + '_multiply_price' );
		cloned_field.find( '#' + pewc_id + '_base_price' ).attr( 'id', cloned_pewc_id + '_base_price' );
		cloned_field.find( '#' + pewc_id + '_pdf_count' )
			.attr( 'id', cloned_pewc_id + '_pdf_count' )
			.val( '' );

		// initialize Dropzone on the cloned Upload field. We need to wait some time.
		setTimeout(
			function(){
				jQuery( 'body' ).trigger( 'pewc_initialize_upload_dropzones', [ cloned_pewc_id ] );
			},
			500
		);

	}

};

jQuery( document ).ready( function(){
	pewc_repeatable.init();
});
