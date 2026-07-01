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

    // Initialize Swiper sliders only when the library is loaded
    if (typeof Swiper === 'undefined') {
        return;
    }

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

    // Re-initialize product swipers with custom breakpoints (2/3/4)
    $('.product-slider .cards').each(function (i) {
        var el = $(this);
        var slideCount = el.find('.swiper-slide').length;
        var container = el.closest('.product-slider');
        new Swiper(this, {
            slidesPerView: 2,
            spaceBetween: 20,
            loop: slideCount > 4,
            grabCursor: true,
            pagination: {
                el: container.find('.swiper-pagination')[0],
                clickable: true,
            },
            navigation: {
                nextEl: container.find('.swiper-button-next')[0],
                prevEl: container.find('.swiper-button-prev')[0],
            },
            breakpoints: {
                768: {
                    slidesPerView: 3,
                },
                992: {
                    slidesPerView: 4,
                },
            }
        });
    });

    // Sticky product gallery on desktop
    if (window.innerWidth >= 992 && $('body').hasClass('single-product')) {
        var $gallery = $('.woocommerce-product-gallery');
        var $summary = $('.summary.entry-summary');

        if ($gallery.length && $summary.length) {
            var galleryWidth = $gallery.outerWidth();
            var galleryLeft = $gallery.offset().left;
            var galleryTop = $gallery.offset().top;
            var summaryBottom = $summary.offset().top + $summary.outerHeight();
            var galleryHeight = $gallery.outerHeight();
            var topOffset = 20;

            $(window).on('scroll resize', function () {
                var scrollTop = $(window).scrollTop();
                summaryBottom = $summary.offset().top + $summary.outerHeight();
                galleryHeight = $gallery.outerHeight();

                if (scrollTop + topOffset > galleryTop && scrollTop + topOffset + galleryHeight < summaryBottom) {
                    $gallery.css({
                        position: 'fixed',
                        top: topOffset + 'px',
                        width: galleryWidth + 'px',
                        left: galleryLeft + 'px'
                    });
                } else if (scrollTop + topOffset + galleryHeight >= summaryBottom) {
                    $gallery.css({
                        position: 'absolute',
                        top: (summaryBottom - galleryHeight - $gallery.parent().offset().top) + 'px',
                        width: galleryWidth + 'px',
                        left: ''
                    });
                } else {
                    $gallery.css({
                        position: '',
                        top: '',
                        width: '',
                        left: ''
                    });
                }
            });
        }
    }

    // Mobile sticky product gallery – use position:sticky + fix ancestors
    if (window.innerWidth < 992 && $('body').hasClass('single-product')) {
        var $mGallery = $('.woocommerce-product-gallery');
        if ($mGallery.length) {
            // Force all ancestors to have overflow:visible so sticky works
            $mGallery.parents().each(function () {
                var el = $(this);
                var tag = this.tagName.toLowerCase();
                if (tag === 'html' || tag === 'body') return;
                var ov = el.css('overflow');
                var ovX = el.css('overflow-x');
                var ovY = el.css('overflow-y');
                if (ov !== 'visible' || ovX !== 'visible' || ovY !== 'visible') {
                    el.css({
                        'overflow': 'visible',
                        'overflow-x': 'visible',
                        'overflow-y': 'visible'
                    });
                }
            });
            $mGallery.css({
                'position': 'sticky',
                'top': '0',
                'z-index': '1000',
                'background': '#fff'
            });
        }
    }

    // Cart: collapsible variations
    $('.woocommerce-cart-form .product-name dl.variation').each(function () {
        var $dl = $(this);
        var $showBtn = $('<span class="cart-variation-toggle cart-variation-show">Vedi Dettaglio</span>');
        var $hideBtn = $('<span class="cart-variation-toggle cart-variation-hide" style="display:none;">Nascondi Dettaglio</span>');
        $dl.after($hideBtn).after($showBtn);

        $showBtn.on('click', function () {
            $dl.slideDown(200);
            $(this).hide();
            $hideBtn.show();
        });
        $hideBtn.on('click', function () {
            $dl.slideUp(200);
            $(this).hide();
            $showBtn.show();
        });
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

    // Meta Pixel: classic WooCommerce AJAX add-to-cart (Meta plugin v3.7+ only hooks Store API).
    function resolveAddToCartProductId($button) {
        var $btn = ($button && $button.length) ? $button : $('form.cart .single_add_to_cart_button').first();
        var productId = $btn.data('variation_id') || $btn.data('product_id');

        if (!productId && $btn.length) {
            var $form = $btn.closest('form.cart, form.variations_form');
            var variationId = $form.find('input[name="variation_id"]').val();
            if (variationId && variationId !== '0') {
                productId = variationId;
            } else if ($form.length) {
                productId = $form.data('product_id');
            }
        }

        return productId;
    }

    function fireMetaAddToCart(productId) {
        if (!productId) {
            return;
        }

        var params = {
            content_ids: [String(productId)],
            content_type: 'product',
            currency: typeof wc_add_to_cart_params !== 'undefined' && wc_add_to_cart_params.currency
                ? wc_add_to_cart_params.currency
                : 'EUR'
        };

        if (typeof window.FacebookSignals !== 'undefined' &&
            typeof window.FacebookSignals.trackEvent === 'function') {
            window.FacebookSignals.trackEvent('AddToCart', params);
        } else if (typeof fbq === 'function') {
            fbq('track', 'AddToCart', params);
        }
    }

    $(document.body).on('added_to_cart', function (e, fragments, hash, $button) {
        fireMetaAddToCart(resolveAddToCartProductId($button));
    });

    // Gallery lightbox: open carousel at clicked slide
    $('.gallery-lightbox-modal').on('show.bs.modal', function (e) {
        var trigger = $(e.relatedTarget);
        var slideTo = trigger.data('bs-slide-to');
        if (typeof slideTo !== 'undefined') {
            var carouselEl = $(this).find('.carousel')[0];
            if (carouselEl) {
                var carousel = bootstrap.Carousel.getOrCreateInstance(carouselEl);
                carousel.to(slideTo);
            }
        }
    });

}); // jQuery End
