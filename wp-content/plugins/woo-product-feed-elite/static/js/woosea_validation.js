jQuery(document).ready(function ($) {
  // Validate woosea installment months
  $('#_woosea_installment_months').blur('input', function () {
    var input = $(this);
    var re = /^[0-9]*$/;
    var woosea_installment_months = re.test(input.val());
    // Check for allowed characters
    if (!woosea_installment_months) {
      $('.notice').replaceWith(
        "<div class='notice notice-error woosea-notice-month is-dismissible'><p>Sorry, only numbers are allowed for the installment month field.</p></div>"
      );
      // Disable submit button too
      $('#publish').attr('disabled', true);
    } else {
      $('.woosea-notice-month').remove();
      $('#publish').attr('disabled', false);
    }
  });

  // Validate woosea installment amount
  $('#_woosea_installment_amount').blur('input', function () {
    var input = $(this);
    var re = /^[0-9.,]*$/;
    var woosea_installment_amount = re.test(input.val());
    // Check for allowed characters
    if (!woosea_installment_amount) {
      $('.notice').replaceWith(
        "<div class='notice notice-error woosea-notice-amount is-dismissible'><p>Sorry, only numbers are allowed for the installment amount field.</p></div>"
      );
      // Disable submit button too
      $('#publish').attr('disabled', true);
    } else {
      $('.woosea-notice-amount').remove();
      $('#publish').attr('disabled', false);
    }
  });

  // Validate woosea GTIN field
  $('#_woosea_gtin').blur('input', function () {
    var input = $(this);
    var re = /^[0-9]*$/;
    var woosea_gtin = re.test(input.val());
    // Check for allowed characters
    if (!woosea_gtin) {
      $('.notice').replaceWith(
        "<div class='notice notice-error woosea-notice-gtin is-dismissible'><p>Sorry, only numbers are allowed for the GTIN field.</p></div>"
      );
      // Disable submit button too
      $('#publish').attr('disabled', true);
    } else {
      $('.woosea-notice-gtin').remove();
      $('#publish').attr('disabled', false);
    }
  });

  // Validate woosea MPN field
  $('#_woosea_mpn').blur('input', function () {
    var input = $(this);
    var re = /^[a-zA-Z0-9-_]*$/;
    var woosea_mpn = re.test(input.val());
    // Check for allowed characters
    if (!woosea_mpn) {
      $('.notice').replaceWith(
        "<div class='notice notice-error woosea-notice-mpn is-dismissible'><p>Sorry, only numbers are allowed for the MPN field.</p></div>"
      );
      // Disable submit button too
      $('#publish').attr('disabled', true);
    } else {
      $('.woosea-notice-mpn').remove();
      $('#publish').attr('disabled', false);
    }
  });

  // Validate woosea UPC field
  $('#_woosea_upc').blur('input', function () {
    var input = $(this);
    var re = /^[0-9]*$/;
    var woosea_upc = re.test(input.val());
    // Check for allowed characters
    if (!woosea_upc) {
      $('.notice').replaceWith(
        "<div class='notice notice-error woosea-notice-upc is-dismissible'><p>Sorry, only numbers are allowed for the UPC field.</p></div>"
      );
      // Disable submit button too
      $('#publish').attr('disabled', true);
    } else {
      $('.woosea-notice-upc').remove();
      $('#publish').attr('disabled', false);
    }
  });

  // Validate woosea EAN field
  $('#_woosea_ean').blur('input', function () {
    var input = $(this);
    var re = /^[0-9]*$/;
    var woosea_ean = re.test(input.val());
    // Check for allowed characters
    if (!woosea_ean) {
      $('.notice').replaceWith(
        "<div class='notice notice-error woosea-notice-ean is-dismissible'><p>Sorry, only numbers are allowed for the EAN field.</p></div>"
      );
      // Disable submit button too
      $('#publish').attr('disabled', true);
    } else {
      $('.woosea-notice-ean').remove();
      $('#publish').attr('disabled', false);
    }
  });

  // Validate woosea Brand field
  $('#_woosea_brand').blur('input', function () {
    var input = $(this);
    var re = /^[a-zA-Z0-9-_. ]*$/;
    var woosea_brand = re.test(input.val());
    // Check for allowed characters
    if (!woosea_brand) {
      $('.notice').replaceWith(
        "<div class='notice notice-error woosea-notice-brand is-dismissible'><p>Sorry, only letters, numbers, whitespaces, -, . and _ are allowed for the brand field.</p></div>"
      );
      // Disable submit button too
      $('#publish').attr('disabled', true);
    } else {
      $('.woosea-notice-brand').remove();
      $('#publish').attr('disabled', false);
    }
  });

  // Validate woosea unit pricing base measure field
  $('#_woosea_unit_pricing_base_measure').blur('input', function () {
    var input = $(this);
    var re = /^[a-zA-Z0-9-_. ]*$/;
    var woosea_unit_pricing_base_measure = re.test(input.val());
    // Check for allowed characters
    if (!woosea_unit_pricing_base_measure) {
      $('.notice').replaceWith(
        "<div class='notice notice-error woosea-notice-unit-pricing-base-measure is-dismissible'><p>Sorry, only letters, numbers, whitespaces, -, . and _ are allowed for the unit pricing base measure field.</p></div>"
      );
      // Disable submit button too
      $('#publish').attr('disabled', true);
    } else {
      $('.woosea-notice-unit-pricing-base-measure').remove();
      $('#publish').attr('disabled', false);
    }
  });

  // Validate woosea unit pricing measure field
  $('#_woosea_unit_pricing_measure').blur('input', function () {
    var input = $(this);
    var re = /^[a-zA-Z0-9-_. ]*$/;
    var woosea_unit_pricing_measure = re.test(input.val());
    // Check for allowed characters
    if (!woosea_unit_pricing_measure) {
      $('.notice').replaceWith(
        "<div class='notice notice-error woosea-notice-unit-pricing-measure is-dismissible'><p>Sorry, only letters, numbers, whitespaces, -, . and _ are allowed for the unit pricing measure field.</p></div>"
      );
      // Disable submit button too
      $('#publish').attr('disabled', true);
    } else {
      $('.woosea-notice-unit-pricing-measure').remove();
      $('#publish').attr('disabled', false);
    }
  });

  // Validate woosea Optimized title field
  $('#_woosea_optimized_title').blur('input', function () {
    var input = $(this);
    var re =
      /^[AaĄąBbCcĆćDdEeĘęFfGgHhIiJjKkLlŁłMmNnŃńOoÓóPpRrSsŚśTtUuWwYyZzŹźŻża-zA-Z0-9-_.àèìòùÀÈÌÒÙáéíóúýÁÉÍÓÚÝâêîôûÂÊÎÔÛãñõÃÑÕäëïöüÿÄËÏÖÜŸçÇßØøÅåÆæœ ]*$/;
    var woosea_optimized_title = re.test(input.val());
    // Check for allowed characters
    if (!woosea_optimized_title) {
      $('.notice').replaceWith(
        "<div class='notice notice-error woosea-notice-optimized-title is-dismissible'><p>Sorry, only letters, numbers, whitespaces, -, . and _ are allowed for the optimized title field.</p></div>"
      );
      // Disable submit button too
      $('#publish').attr('disabled', true);
    } else {
      $('.woosea-notice-optimized-title').remove();
      $('#publish').attr('disabled', false);
    }
  });
});
