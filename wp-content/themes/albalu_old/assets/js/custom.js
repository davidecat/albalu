
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Testimonial Swiper
    var testimonialSwiper = new Swiper('.testimonial-swiper', {
        slidesPerView: 1,
        spaceBetween: 20,
        loop: true,
        autoplay: {
            delay: 3000,
            disableOnInteraction: false,
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
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
    var creationsSwiper = new Swiper('.creations-swiper', {
        slidesPerView: 1,
        spaceBetween: 20,
        loop: true,
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

    // Infinite Scroll
    const productsContainer = document.querySelector('.products');
    // Bootscore pagination container
    let paginationNav = document.querySelector('nav[aria-label="Product Pagination"]');
    
    if (productsContainer && paginationNav) {
        let isLoading = false;
        
        // Helper to find next link
        const getNextLink = () => {
            // Re-query pagination inside the nav
            const paginationLinks = document.querySelectorAll('.pagination .page-link');
            let nextUrl = null;
            
            paginationLinks.forEach(link => {
                // Check for » or Next text (bootscore/woo default is &raquo;)
                if (link.innerHTML.includes('»') || link.textContent.includes('»')) {
                    nextUrl = link.getAttribute('href');
                }
            });
            return nextUrl;
        };

        const loadMoreProducts = async () => {
            if (isLoading) return;

            const url = getNextLink();
            if (!url) return;

            isLoading = true;
            
            // Add loading opacity/indicator if desired
            productsContainer.style.opacity = '0.5';

            try {
                const response = await fetch(url);
                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                const newProducts = doc.querySelectorAll('.products > *'); // Get product columns
                const newPaginationNav = doc.querySelector('nav[aria-label="Product Pagination"]');

                if (newProducts.length > 0) {
                    newProducts.forEach(product => {
                        // Ensure we append the element correctly
                        productsContainer.appendChild(product);
                    });
                }

                // Update pagination
                if (newPaginationNav) {
                    paginationNav.innerHTML = newPaginationNav.innerHTML;
                } else {
                    paginationNav.remove(); // No more pages
                }

            } catch (error) {
                console.error('Error loading products:', error);
            } finally {
                isLoading = false;
                productsContainer.style.opacity = '1';
            }
        };

        // Scroll Event
        window.addEventListener('scroll', () => {
            // Trigger when near bottom (1000px buffer)
            if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 1000) {
                loadMoreProducts();
            }
        });
    }
});
