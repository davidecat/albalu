/**
 * Settings page attribute management
 *
 * @package AdTribes\PFE\Vanilla\Settings
 */

declare var $: any;
declare var toastr: any;

import './style.scss';

(function (w, d, $) {
  const { __ } = w.wp.i18n;

  $('.woo-product-feed-pro-table tr[id^="custom_attributes__"] .checkbox-field').change(function (this: HTMLElement) {
    const $element = $(this);
    const attributeValue = $element.val();
    const attributeName = $element.attr('name');
    const attributeStatus = $element.prop('checked');

    $.ajax({
      method: 'POST',
      url: w.ajaxurl,
      data: {
        action: 'woosea_elite_add_attributes',
        security: w.adtObjElite.extraAttributesNonce,
        attribute_name: attributeName,
        attribute_value: attributeValue,
        active: attributeStatus,
      },
      success: function (response: any) {
        if (response.success) {
          toastr.success(response.data.message);
        } else {
          toastr.error(response.data.message);
        }
      },
      error: function (xhr: any, status: any, error: any) {
        toastr.error(
          xhr.responseJSON?.message || __('An error occurred while updating the attribute.', 'woo-product-feed-elite')
        );
      },
    });
  });
})(window, document, jQuery);
