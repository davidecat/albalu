/**
 * Portable (cross-site) add-on export/import UI.
 * @since 4.4.6
 */
( function( $ ) {

	'use strict';

	var vars = window.pewc_portable_vars || {};
	var i18n = vars.i18n || {};

	/**
	 * Block/unblock a region using WooCommerce's default blockUI spinner UX.
	 */
	function blockRegion( $el ) {
		if ( ! $el || ! $el.length || typeof $el.block !== 'function' ) {
			return;
		}
		$el.block( {
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		} );
	}

	function unblockRegion( $el ) {
		if ( $el && $el.length && typeof $el.unblock === 'function' ) {
			$el.unblock();
		}
	}

	/**
	 * POST an import request. `payload` is a FormData instance.
	 */
	function runImport( payload, $status, opts ) {

		opts = opts || {};
		if ( ! payload.has( 'action' ) ) {
			payload.append( 'action', 'pewc_portable_import' );
			payload.append( 'security', vars.import_nonce );
		}

		if ( $status && $status.length ) {
			$status.text( i18n.working || 'Working...' );
		}

		return $.ajax( {
			url: vars.ajaxurl,
			type: 'POST',
			data: payload,
			processData: false,
			contentType: false
		} ).done( function( response ) {

			if ( ! response || ! response.success ) {
				var msg = ( response && response.data && response.data.message ) ? response.data.message : ( i18n.error || 'Error' );
				if ( $status && $status.length ) {
					$status.text( msg );
				}
				if ( opts.onError ) {
					opts.onError( msg );
				}
				return;
			}

			// Two-step: the file has child products, ask before creating them.
			if ( response.data && response.data.needs_confirm === 'children' ) {
				var msg = ( i18n.confirm_import_children || 'This file includes %d referenced product(s). Continue?' )
					.replace( '%d', response.data.count );
				var create = window.confirm( msg ) ? '1' : '0';
				// The file input is already consumed; re-send the same payload with the answer.
				payload.append( 'create_children', create );
				runImport( payload, $status, opts );
				return;
			}

			if ( opts.onSuccess ) {
				opts.onSuccess( response.data );
			}

		} ).fail( function( jqxhr ) {
			var msg = ( i18n.error || 'Error' ) + ' (' + jqxhr.status + ')';
			if ( $status && $status.length ) {
				$status.text( msg );
			}
			if ( opts.onError ) {
				opts.onError( msg );
			}
		} );

	}

	/**
	 * Render the result summary + report into a container.
	 */
	function renderResult( $container, data, reloadOnDone ) {

		var summary = ( i18n.imported || 'Imported %1$d group(s) and %2$d field(s).' )
			.replace( '%1$d', data.imported_groups )
			.replace( '%2$d', data.imported_fields );

		var html = '<div class="notice notice-success"><p><strong>' + summary + '</strong></p>';

		if ( data.report && data.report.length ) {
			html += '<ul class="pewc-portable-report">';
			data.report.forEach( function( line ) {
				html += '<li>' + $( '<div>' ).text( line ).html() + '</li>';
			} );
			html += '</ul>';
		}

		if ( reloadOnDone ) {
			html += '<p>' + ( i18n.reloading || 'Reloading...' ) + '</p>';
		} else if ( data.edit_url ) {
			html += '<p><a class="button" href="' + data.edit_url + '">' + ( i18n.go_to_product || 'Go to product' ) + '</a></p>';
		}

		html += '</div>';

		$container.html( html ).prop( 'hidden', false );

	}

	/* ---- Import tool page ---- */

	function initImportPage() {

		var $go = $( '#pewc_portable_import_go' );
		if ( ! $go.length ) {
			return;
		}

		$go.on( 'click', function( e ) {

			e.preventDefault();

			var productId = $( '#pewc_portable_import_product' ).val();
			var fileInput = document.getElementById( 'pewc_portable_import_file' );
			var $result = $( '#pewc_portable_import_result' );
			var $spinner = $go.siblings( '.spinner' );

			if ( ! productId ) {
				window.alert( i18n.choose_product || 'Choose a product.' );
				return;
			}
			if ( ! fileInput || ! fileInput.files.length ) {
				window.alert( i18n.choose_file || 'Choose a file.' );
				return;
			}

			var payload = new FormData();
			payload.append( 'source', 'file' );
			payload.append( 'product_id', productId );
			payload.append( 'import_file', fileInput.files[0] );

			var $region = $( '.pewc-portable-wrap' );

			$go.prop( 'disabled', true );
			$spinner.addClass( 'is-active' );
			blockRegion( $region );

			runImport( payload, null, {
				onSuccess: function( data ) {
					unblockRegion( $region );
					renderResult( $result, data, false );
					$go.prop( 'disabled', false );
					$spinner.removeClass( 'is-active' );
				},
				onError: function( msg ) {
					unblockRegion( $region );
					$result.html( '<div class="notice notice-error"><p>' + $( '<div>' ).text( msg ).html() + '</p></div>' ).prop( 'hidden', false );
					$go.prop( 'disabled', false );
					$spinner.removeClass( 'is-active' );
				}
			} );

		} );

	}

	/* ---- Product edit screen: export button + demo picker ---- */

	/**
	 * After a demo import + reload, WooCommerce shows the first product-data tab.
	 * Reopen the Product Add-Ons tab so the user is back where they were.
	 */
	function maybeReopenAddonsTab() {
		var flag = false;
		var result = null;
		try {
			flag = window.sessionStorage.getItem( 'pewc_reopen_addons_tab' );
			window.sessionStorage.removeItem( 'pewc_reopen_addons_tab' );
			var raw = window.sessionStorage.getItem( 'pewc_import_result' );
			window.sessionStorage.removeItem( 'pewc_import_result' );
			if ( raw ) {
				result = JSON.parse( raw );
			}
		} catch ( err ) {}
		if ( ! flag && window.location.search.indexOf( 'pewc_reopen_addons=1' ) === -1 ) {
			return;
		}
		// Defer so it runs after WooCommerce binds its wc-tabs click handler.
		window.setTimeout( function() {
			var $tab = $( '#woocommerce-product-data ul.wc-tabs a[href="#pewc_options"]' );
			if ( $tab.length ) {
				$tab.trigger( 'click' );
			}
			if ( result ) {
				showImportResult( result );
			}
		}, 0 );
	}

	/**
	 * Render the import result summary + report as a dismissible notice at the
	 * top of the Add-Ons panel (after a post-import reload).
	 */
	function showImportResult( data ) {
		var $panel = $( '#pewc_options' );
		if ( ! $panel.length ) {
			return;
		}
		var summary = ( i18n.imported || 'Imported %1$d group(s) and %2$d field(s).' )
			.replace( '%1$d', data.imported_groups )
			.replace( '%2$d', data.imported_fields );

		var html = '<div class="notice notice-success is-dismissible pewc-portable-import-notice"><p><strong>'
			+ summary + '</strong></p>';
		if ( data.report && data.report.length ) {
			html += '<ul class="pewc-portable-report">';
			data.report.forEach( function( line ) {
				html += '<li>' + $( '<div>' ).text( line ).html() + '</li>';
			} );
			html += '</ul>';
		}
		html += '</div>';

		var $notice = $( html );
		$panel.prepend( $notice );
		$notice.on( 'click', '.notice-dismiss', function() {
			$notice.remove();
		} );
		// WP adds the dismiss button to .is-dismissible notices on its own ready
		// handler; add one ourselves in case that has already run.
		if ( ! $notice.find( '.notice-dismiss' ).length ) {
			$( '<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss</span></button>' )
				.appendTo( $notice )
				.on( 'click', function() { $notice.remove(); } );
		}
	}

	/**
	 * Show a confirmation notice after an export download is triggered.
	 * `lines` is an array of extra notes (issues, counts).
	 */
	function showExportResult( $context, lines ) {
		var $box = $context.find( '.pewc-portable-export-result' );
		if ( ! $box.length ) {
			return;
		}
		var html = '<div class="notice notice-success"><p><strong>'
			+ ( i18n.export_created || 'Export file created.' ) + '</strong></p>';
		if ( lines && lines.length ) {
			html += '<ul class="pewc-portable-report">';
			lines.forEach( function( line ) {
				html += '<li>' + $( '<div>' ).text( line ).html() + '</li>';
			} );
			html += '</ul>';
		}
		html += '</div>';
		$box.html( html ).prop( 'hidden', false );
	}

	/**
	 * Start a download: check whether the product has Products/Categories fields;
	 * if so, ask whether to include child products, then navigate to the download
	 * URL and show a confirmation notice.
	 */
	function startExport( productId, base, $context ) {
		if ( ! productId ) {
			window.alert( i18n.choose_product || 'Choose a product.' );
			return;
		}

		function go( includeChildren, data ) {
			window.location.href = base
				+ '&product_id=' + encodeURIComponent( productId )
				+ '&include_children=' + ( includeChildren ? '1' : '0' );

			var lines = [];
			if ( data ) {
				if ( includeChildren && data.simple_count > 0 ) {
					lines.push( ( i18n.export_children_note || '%d referenced product(s) included.' )
						.replace( '%d', data.simple_count ) );
				}
				// Server-computed limitations for the chosen mode.
				var notes = includeChildren ? data.notes_with : data.notes_without;
				if ( notes && notes.length ) {
					lines = lines.concat( notes );
				}
			}
			showExportResult( $context, lines );
		}

		if ( ! vars.export_check_nonce ) {
			go( false, null );
			return;
		}

		$.post( vars.ajaxurl, {
			action: 'pewc_portable_export_check',
			security: vars.export_check_nonce,
			product_id: productId
		} ).done( function( response ) {
			var data = ( response && response.success && response.data ) ? response.data : null;
			// Only ask when there are simple products that CAN be included.
			if ( data && data.simple_count > 0 ) {
				go( window.confirm( i18n.confirm_export_children || 'Also export referenced products?' ), data );
			} else {
				go( false, data );
			}
		} ).fail( function() {
			go( false, null );
		} );
	}

	function initProductScreen() {

		maybeReopenAddonsTab();

		// Export from the standalone tool page (has a product search box).
		var $exportGo = $( '#pewc_portable_export_go' );
		if ( $exportGo.length ) {
			$exportGo.on( 'click', function( e ) {
				e.preventDefault();
				startExport( $( '#pewc_portable_export_product' ).val(), $exportGo.data( 'download-base' ), $exportGo.closest( '.wrap' ) );
			} );
		}

		// Export from the product Add-Ons panel button.
		var $exportOpen = $( '#pewc_portable_export_open' );
		if ( $exportOpen.length ) {
			$exportOpen.on( 'click', function( e ) {
				e.preventDefault();
				startExport( $exportOpen.data( 'product-id' ), $exportOpen.data( 'download-base' ), $exportOpen.closest( '.pewc-portable-buttons' ) );
			} );
		}

		// The Add-Ons product-data panel - blocked during any import.
		function addonsPanel() {
			var $panel = $( '#pewc_options' );
			if ( ! $panel.length ) {
				$panel = $( '.pewc-portable-buttons' ).closest( '.woocommerce_options_panel, .panel' ).first();
			}
			return $panel;
		}

		// Reload the page, reopen the Add-Ons tab, and (optionally) carry an import
		// result summary through so it can be shown as a notice afterwards.
		function reloadToAddons( resultData ) {
			try {
				window.sessionStorage.setItem( 'pewc_reopen_addons_tab', '1' );
				if ( resultData ) {
					window.sessionStorage.setItem( 'pewc_import_result', JSON.stringify( {
						imported_groups: resultData.imported_groups,
						imported_fields: resultData.imported_fields,
						report: resultData.report || []
					} ) );
				}
			} catch ( err ) {}
			window.location.reload();
		}

		/* ---- Import-from-file modal ---- */

		var $modal = $( '#pewc_portable_import_modal' );

		function openModal() {
			$modal.find( '.pewc-portable-modal-result' ).empty().prop( 'hidden', true );
			$modal.find( '#pewc_portable_modal_file' ).val( '' );
			$modal.find( '#pewc_portable_modal_import' ).prop( 'disabled', false );
			$modal.css( 'display', 'block' );
		}

		function closeModal() {
			$modal.css( 'display', 'none' );
		}

		$( '#pewc_portable_import_open' ).on( 'click', function( e ) {
			e.preventDefault();
			openModal();
		} );

		$modal.on( 'click', '.pewc-portable-modal-close, .pewc-portable-modal-backdrop', function( e ) {
			e.preventDefault();
			closeModal();
		} );

		$( document ).on( 'keydown.pewcPortable', function( e ) {
			if ( e.key === 'Escape' && $modal.is( ':visible' ) ) {
				closeModal();
			}
		} );

		$( '#pewc_portable_modal_import' ).on( 'click', function( e ) {

			e.preventDefault();

			var $btn = $( this );
			var fileInput = $modal.find( '#pewc_portable_modal_file' )[0];
			var productId = $( fileInput ).data( 'product-id' );
			var $result = $modal.find( '.pewc-portable-modal-result' );

			if ( ! fileInput || ! fileInput.files.length ) {
				$result.html( '<div class="notice notice-error"><p>' + ( i18n.choose_file || 'Choose a file.' ) + '</p></div>' ).prop( 'hidden', false );
				return;
			}

			var payload = new FormData();
			payload.append( 'source', 'file' );
			payload.append( 'product_id', productId );
			payload.append( 'import_file', fileInput.files[0] );

			var $panel = addonsPanel();
			$btn.prop( 'disabled', true );
			blockRegion( $modal.find( '.pewc-portable-modal-box' ) );

			runImport( payload, null, {
				onSuccess: function( data ) {
					// Keep everything blocked through the reload.
					blockRegion( $panel );
					reloadToAddons( data );
				},
				onError: function( msg ) {
					unblockRegion( $modal.find( '.pewc-portable-modal-box' ) );
					$result.html( '<div class="notice notice-error"><p>' + $( '<div>' ).text( msg ).html() + '</p></div>' ).prop( 'hidden', false );
					$btn.prop( 'disabled', false );
				}
			} );

		} );

		/* ---- Demo picker ---- */

		var $demoGo = $( '#pewc_create_from_demo' );
		if ( ! $demoGo.length ) {
			return;
		}

		$demoGo.on( 'click', function( e ) {

			e.preventDefault();

			var slug = $( '#pewc_demo_product_select' ).val();
			var productId = $demoGo.data( 'product-id' );
			var $status = $( '.pewc-portable-demo-status' );

			if ( ! slug ) {
				$status.text( i18n.choose_demo || 'Choose a demo.' );
				return;
			}
			if ( ! window.confirm( i18n.confirm_demo || 'Add these add-ons?' ) ) {
				return;
			}

			var payload = new FormData();
			payload.append( 'source', 'demo' );
			payload.append( 'product_id', productId );
			payload.append( 'demo_slug', slug );

			var $panel = addonsPanel();
			$demoGo.prop( 'disabled', true );
			blockRegion( $panel );

			runImport( payload, $status, {
				onSuccess: function( data ) {
					var summary = ( i18n.imported || 'Imported %1$d group(s) and %2$d field(s).' )
						.replace( '%1$d', data.imported_groups )
						.replace( '%2$d', data.imported_fields );
					$status.text( summary + ' ' + ( i18n.reloading || 'Reloading...' ) );
					reloadToAddons( data );
				},
				onError: function() {
					unblockRegion( $panel );
					$demoGo.prop( 'disabled', false );
				}
			} );

		} );

	}

	$( function() {
		initImportPage();
		initProductScreen();
	} );

} )( jQuery );
