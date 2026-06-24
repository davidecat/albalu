declare var jQuery: any;
declare var $: any;

import './style.scss';

(function (w, d, $) {
  const { __ } = w.wp.i18n;
  const $form = $('form.adt-edit-feed-form');
  const $refreshInterval = $form.find('#refresh_interval');
  const $refreshIntervalSelect = $refreshInterval.find('select');
  const $customRefreshInterval = $refreshInterval.next('#custom_refresh_interval');
  const $scheduleHasDays = ['hourly', 'daily', 'twicedaily', 'hours'];

  // On Load.
  init();

  /**
   * Initialize the custom refresh interval.
   */
  function init() {
    if ($refreshIntervalSelect.val() === 'custom') {
      $customRefreshInterval.show();

      const value = $customRefreshInterval.find('.custom_refresh_interval_schedule:checked').val();
      const hasDays = $scheduleHasDays.includes(value);
      if (hasDays) {
        $('.adt-custom-refresh-interval-options-group-days').show();
      } else {
        $('.adt-custom-refresh-interval-options-group-days').hide();
      }
    } else {
      $customRefreshInterval.hide();
    }
  }

  /**
   * Initialize the flatpickr date picker.
   */
  $('.custom_refresh_interval_commence_date').flatpickr({
    dateFormat: 'Y-m-d H:i',
    minDate: 'today',
    time_24hr: false,
    enableTime: true,
  });

  /**
   * Handle the refresh interval change.
   */
  $('form.adt-edit-feed-form #refresh_interval').on('change', function (this: HTMLElement, e: any) {
    e.preventDefault();

    const $select = $(this).find('select');
    const $customSelect = $(this).next('#custom_refresh_interval');
    const value = $select.val();

    if (value === 'custom') {
      $customSelect.show();
    } else {
      $customSelect.hide();
    }
  });

  /**
   * Handle the custom refresh interval frequency change.
   */
  $('.adt-custom-refresh-interval-options-group-frequency input[type="radio"]').on(
    'change',
    function (this: HTMLElement, e: any) {
      e.preventDefault();

      const value = $(this).val();
      const hasDays = $scheduleHasDays.includes(value);

      if (hasDays) {
        $('.adt-custom-refresh-interval-options-group-days').show();
      } else {
        $('.adt-custom-refresh-interval-options-group-days').hide();
      }
    }
  );

  // Form Submit.
  $form.on('submit', function (this: HTMLElement, e: any) {
    const $this = $(this);
    const $refreshInterval = $this.find('#refresh_interval');
    const $customRefreshInterval = $this.find('#custom_refresh_interval');

    const $refreshIntervalSelect = $refreshInterval.find('select');
    const value = $refreshIntervalSelect.val();

    if (value === 'custom') {
      const schedule = $customRefreshInterval.find('.custom_refresh_interval_schedule:checked').val();
      const commence = $customRefreshInterval.find('.custom_refresh_interval_commence:checked').val();
      const allowDays = $scheduleHasDays.includes(schedule);

      if (allowDays) {
        // Error if no days are selected.
        const days = $customRefreshInterval.find('.custom_refresh_interval_days:checked');
        if (days.length === 0) {
          alert(__('Please select at least one day.', 'woocommerce-product-feed-elite'));
          return false;
        }
      }

      if (schedule === 'hours') {
        const hours = $customRefreshInterval.find('.custom_refresh_interval_schedule_hours').val();
        if (hours < 1 || hours > 24) {
          alert(__('Please enter a valid number of hours.', 'woocommerce-product-feed-elite'));
          return false;
        }
      }

      if (commence === 'date') {
        const commenceDate = $customRefreshInterval.find('.custom_refresh_interval_commence_date').val();
        if (!commenceDate) {
          alert(__('Please select a date.', 'woocommerce-product-feed-elite'));
          return false;
        }
      }
    }
  });
})(window, document, jQuery);
