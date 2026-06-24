/**
 * Addon Install Handler for Elite Plugin.
 *
 * Listens for the `adt:addon-install` custom jQuery event fired by PRO's
 * notice system when a button with data-addon_software_key is clicked.
 * Performs the SLMW AJAX install flow and updates the UI on completion.
 *
 * @since 5.0.8
 */
(function (w, d, $) {
  const { __ } = w.wp.i18n;

  $(document).on('adt:addon-install', function (e: any) {
    const { softwareKey, nonce, button: $button, container: $container, isDrawerItem } = e.addonData;

    if (!softwareKey || !nonce) return;

    const originalText = $button.text();

    // Show loading state.
    $button.text(__('Installing...', 'woo-product-feed-elite')).prop('disabled', true).addClass('disabled');

    // Clear any previous error messages.
    $container.find('.adt-addon-error').remove();

    // Call Elite's AJAX handler (3-step SLMW flow).
    $.ajax({
      url: w.ajaxurl,
      type: 'POST',
      data: {
        action: 'adt_pfe_install_addon',
        software_key: softwareKey,
        nonce: nonce,
      },
      success: (response: any) => {
        if (response.success) {
          $button.text(__('Installed', 'woo-product-feed-elite'));

          // Dismiss the notice via PRO's dismiss mechanism.
          const noticeId = $container.attr('id') || $container.data('notice-id');
          const containerNonce = $container.data('nonce');

          if (noticeId && containerNonce) {
            $.post(w.ajaxurl, {
              action: 'adt_pfp_dismiss_admin_notice',
              notice_id: noticeId,
              nonce: containerNonce,
              response: 'dismissed',
            });
          }

          // Remove from UI using PRO's exposed utility.
          const utils = (w as any).adtNotificationUtils;
          if (utils && typeof utils.removeNotification === 'function') {
            utils.removeNotification($container, isDrawerItem);
          } else {
            // Fallback: simple fade-out.
            $container.fadeOut(200, () => $container.remove());
          }
        } else {
          const msg = response.data?.message || __('Installation failed.', 'woo-product-feed-elite');
          $button.text(originalText).prop('disabled', false).removeClass('disabled');

          // Show error inline next to the button.
          $button.after(
            $('<span class="adt-addon-error"></span>')
              .css({ color: 'red', marginLeft: '8px', fontSize: '13px' })
              .html(msg),
          );
        }
      },
      error: () => {
        $button.text(originalText).prop('disabled', false).removeClass('disabled');
        $button.after(
          $('<span class="adt-addon-error"></span>')
            .css({ color: 'red', marginLeft: '8px', fontSize: '13px' })
            .html(__('Installation failed. Please try again.', 'woo-product-feed-elite')),
        );
      },
    });
  });
})(window, document, jQuery);
