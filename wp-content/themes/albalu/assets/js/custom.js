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

    // Initialize Testimonial Swiper
    var testimonialSlideCount = $('.testimonial-swiper .swiper-slide').length;
    var testimonialSwiper = new Swiper('.testimonial-swiper', {
        slidesPerView: 1,
        spaceBetween: 20,
        loop: testimonialSlideCount > 4,
        autoplay: {
            delay: 8000,
            disableOnInteraction: false,
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.testimonial-swiper-wrap .swiper-button-next',
            prevEl: '.testimonial-swiper-wrap .swiper-button-prev',
        },
        breakpoints: {
            640: {
                slidesPerView: 1,
                spaceBetween: 20,
            },
            768: {
                slidesPerView: 2,
                spaceBetween: 30,
            },
            1024: {
                slidesPerView: 3,
                spaceBetween: 30,
            },
            1200: {
                slidesPerView: 4,
                spaceBetween: 30,
            }
        }
    });

    // Initialize Creations Swiper
    var creationsSlideCount = $('.creations-swiper .swiper-slide').length;
    var creationsSwiper = new Swiper('.creations-swiper', {
        slidesPerView: 1,
        spaceBetween: 20,
        loop: creationsSlideCount > 4,
        autoplay: {
            delay: 4000,
            disableOnInteraction: false,
        },
        breakpoints: {
            640: {
                slidesPerView: 2,
                spaceBetween: 20,
            },
            768: {
                slidesPerView: 3,
                spaceBetween: 20,
            },
            1024: {
                slidesPerView: 4,
                spaceBetween: 30,
            }
        }
    });

    // Testimonial read more / read less
    $('.testimonial-read-more').on('click', function (e) {
        e.preventDefault();
        var $wrap = $(this).closest('.testimonial-text-wrap');
        $wrap.find('.testimonial-text').addClass('expanded');
        $(this).addClass('d-none');
        $wrap.find('.testimonial-read-less').removeClass('d-none');
    });

    $('.testimonial-read-less').on('click', function (e) {
        e.preventDefault();
        var $wrap = $(this).closest('.testimonial-text-wrap');
        $wrap.find('.testimonial-text').removeClass('expanded');
        $(this).addClass('d-none');
        $wrap.find('.testimonial-read-more').removeClass('d-none');
    });

}); // jQuery End
