jQuery(function ($) {

    // Mobile search overlay
    $('#mobile-search-toggle').on('click', function () {
        $('#mobile-search-overlay').addClass('is-open');
        $('#mobile-search-overlay .mobile-search-overlay__input').focus();
    });

    $('#mobile-search-close').on('click', function () {
        $('#mobile-search-overlay').removeClass('is-open');
    });

    $('#mobile-search-overlay').on('click', function (e) {
        if ($(e.target).is('#mobile-search-overlay')) {
            $(this).removeClass('is-open');
        }
    });

}); // jQuery End
