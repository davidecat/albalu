jQuery(document).ready(function ($) {
  // The Aelia currency has changed, make sure to warn the user to also change the currency prefix and/or suffix
  $('.aelia_switch').change(function () {
    var popup_dialog = alert(
      'You have changed the Aelia currency, this will change pricing in your product feed. Make sure the currency prefix and/or suffix on the field mapping page is correct.'
    );
  });

  // The Curcy currency has changed, make sure to warn the user to also change the currency prefix and/or suffix
  $('.curcy_switch').change(function () {
    var popup_dialog = alert(
      'You have changed the Curcy currency, this will change pricing in your product feed. Make sure the currency prefix and/or suffix on the field mapping page is correct.'
    );
  });
});
