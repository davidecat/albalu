jQuery(document).ready(function ($) {
  // Add standard filters
  jQuery('.add-field-manipulation').click(function () {
    var nonce = $('#_wpnonce').val();
    var TrueRowCount = $('#woosea-ajax-table >tbody >tr').length - 1;
    var rowCount = Math.round(new Date().getTime() + Math.random() * 100);
    var plusCount = Math.round(new Date().getTime() + Math.random() * 100);

    jQuery
      .ajax({
        method: 'POST',
        url: ajaxurl,
        data: {
          action: 'woosea_ajax_get_data_manipulation_attributes',
          type: 'html',
          security: nonce,
        },
      })

      .done(function (response) {
        if (!response.success) {
          console.log('Error: ' + response.message);
          return;
        }

        if (TrueRowCount == 0) {
          $('#woosea-ajax-table')
            .find('tbody:first')
            .append(
              '<tr><td valign="top"><input type="hidden" name="field_manipulation[' +
                rowCount +
                '][rowCount]" value="' +
                rowCount +
                '"><input type="checkbox" name="record" class="checkbox-field"></td><td valign="top"><select name="field_manipulation[' +
                rowCount +
                '][product_type]" class="select-field woo-sea-select2"><option value="all">Simple and variable</option><option value="simple">Simple</option><option value="variable">Variable</option></select></td></td><td valign="top"><select name="field_manipulation[' +
                rowCount +
                '][attribute]" class="select-field woo-sea-select2" id="field_manipulation_' +
                rowCount +
                '">' +
                response.data +
                '</select></td><td class="becomes_fields_' +
                rowCount +
                '" valign="top"><div class="becomes-field-wrapper" style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;"><select name="field_manipulation[' +
                rowCount +
              '][becomes][1][attribute]" class="select-field woo-sea-select2" id="field_manipulation_becomes_attribute_' +
              rowCount +
              '" style="flex: 1;">' +
              response.data +
              '</select><span class="dashicons dashicons-no-alt remove-becomes-field" style="cursor: pointer; color: #dc3232; display: none;" title="Remove this attribute"></span></div></td><td><span class="dashicons dashicons-plus field_extra field_manipulation_extra_' +
                rowCount +
                '" style="display: inline-block;" title="Add an attribute to this field"></span></td></tr>'
            );
        } else {
          $(
            '<tr><td valign="top"><input type="hidden" name="field_manipulation[' +
              rowCount +
              '][rowCount]" value="' +
              rowCount +
              '"><input type="checkbox" name="record" class="checkbox-field"></td><td valign="top"><select name="field_manipulation[' +
              rowCount +
              '][product_type]" class="select-field woo-sea-select2"><option value="all">Simple and variable</option><option value="simple">Simple</option><option value="variable">Variable</option></select></td></td><td valign="top"><select name="field_manipulation[' +
              rowCount +
              '][attribute]" class="select-field woo-sea-select2" id="field_manipulation_' +
              rowCount +
              '">' +
              response.data +
              '</select></td><td class="becomes_fields_' +
              rowCount +
              '" valign="top"><div class="becomes-field-wrapper" style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;"><select name="field_manipulation[' +
              rowCount +
              '][becomes][1][attribute]" class="select-field woo-sea-select2" id="field_manipulation_becomes_' +
              rowCount +
              '" style="flex: 1;">' +
              response.data +
              '</select><span class="dashicons dashicons-no-alt remove-becomes-field" style="cursor: pointer; color: #dc3232; display: none;" title="Remove this attribute"></span></div><td><span class="dashicons dashicons-plus field_extra field_manipulation_extra_' +
              rowCount +
              '" style="display: inline-block;" title="Add an attribute to this field"></span></td></tr>'
          ).insertBefore('.rules-buttons');
        }

        // Check if user selected a data manipulation condition
        jQuery('.field_manipulation_extra_' + rowCount).on('click', function () {
          plusCount = Math.round(new Date().getTime() + Math.random() * 100);
          var becomesCell = jQuery('.becomes_fields_' + rowCount);
          becomesCell.append(
            '<div class="becomes-field-wrapper" style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;"><select name="field_manipulation[' +
              rowCount +
              '][becomes][' +
              plusCount +
              '][attribute]" class="select-field woo-sea-select2" id="field_manipulation_becomes_attribute_' +
              rowCount +
              '" style="flex: 1;">' +
              response.data +
              '</select><span class="dashicons dashicons-no-alt remove-becomes-field" style="cursor: pointer; color: #dc3232;" title="Remove this attribute"></span></div>'
          );
          // Initialize select2 for the new field
          $(document.body).trigger('init_woosea_select2');
          // Update delete button visibility
          updateBecomesDeleteButtonsVisibility(becomesCell);
        });

        // Initialize select2 for the new row
        $(document.body).trigger('init_woosea_select2');
      })
      .fail(function (data) {
        console.log('Failed AJAX Call :( /// Return Data: ' + data);
      });
  });

  // Add extra fields to existing field manipulations
  jQuery('.field_extra').click(function () {
    var nonce = $('#_wpnonce').val();
    var className = $(this).attr('class').split(' ')[3];
    var rowCount = className.split('_')[3];
    var plusCount = Math.round(new Date().getTime() + Math.random() * 100);

    jQuery
      .ajax({
        method: 'POST',
        url: ajaxurl,
        data: {
          action: 'woosea_ajax_get_data_manipulation_attributes',
          type: 'html',
          security: nonce,
        },
      })
      .done(function (response) {
        if (!response.success) {
          console.log('Error: ' + response.message);
          return;
        }

        var becomesCell = jQuery('.becomes_fields_' + rowCount);
        becomesCell.append(
          '<div class="becomes-field-wrapper" style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;"><select name="field_manipulation[' +
            rowCount +
            '][becomes][' +
            plusCount +
            '][attribute]" class="select-field woo-sea-select2" id="field_manipulation_becomes_attribute_' +
            rowCount +
            '" style="flex: 1;">' +
            response.data +
            '</select><span class="dashicons dashicons-no-alt remove-becomes-field" style="cursor: pointer; color: #dc3232;" title="Remove this attribute"></span></div>'
        );

        // Initialize select2 for the new row
        $(document.body).trigger('init_woosea_select2');
        // Update delete button visibility
        updateBecomesDeleteButtonsVisibility(becomesCell);
      })
      .fail(function (data) {
        console.log('Failed AJAX Call :( /// Return Data: ' + data);
      });
  });

  // Remove individual becomes fields
  jQuery(document).on('click', '.remove-becomes-field', function () {
    var wrapper = $(this).closest('.becomes-field-wrapper');
    var becomesCell = wrapper.closest('td');

    // Only remove if there's more than one becomes field in this row
    if (becomesCell.find('.becomes-field-wrapper').length > 1) {
      wrapper.remove();
      // Update visibility of remaining delete buttons
      updateBecomesDeleteButtonsVisibility(becomesCell);
    }
  });

  // Function to update delete button visibility based on field count
  function updateBecomesDeleteButtonsVisibility(becomesCell) {
    var fieldCount = becomesCell.find('.becomes-field-wrapper').length;
    if (fieldCount === 1) {
      // Hide delete button when only one field remains
      becomesCell.find('.remove-becomes-field').hide();
    } else {
      // Show all delete buttons when multiple fields exist
      becomesCell.find('.remove-becomes-field').show();
    }
  }

  // Initialize delete button visibility on page load
  jQuery(document).ready(function() {
    jQuery('td[class*="becomes_fields_"]').each(function() {
      updateBecomesDeleteButtonsVisibility($(this));
    });
  });

  // Find and remove selected table rows
  $('.delete-row').on('click', function () {
    $('input[name="record"]').each(function () {
      if ($(this).is(':checked')) {
        $(this).closest('tr').remove();
      }
    });
  });
});
