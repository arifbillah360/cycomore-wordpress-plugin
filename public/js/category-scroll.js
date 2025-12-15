/**
 * Category Pills Horizontal Scrolling
 * Handles left/right arrow clicks and visibility
 *
 * @package Partner_CIU_Manager
 * @version 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Initialize category scroll functionality
     */
    function initCategoryScroll() {

        const container = document.getElementById('categoriesContainer');
        const leftArrow = document.getElementById('scrollLeftArrow');
        const rightArrow = document.getElementById('scrollRightArrow');

        // Exit if elements not found
        if (!container || !leftArrow || !rightArrow) {
            return;
        }

        // Scroll amount (pixels to scroll per click)
        const scrollAmount = 300;

        /**
         * Update arrow visibility based on scroll position
         */
        function updateArrows() {
            const scrollLeft = container.scrollLeft;
            const maxScroll = container.scrollWidth - container.clientWidth;

            // Disable left arrow if at start
            if (scrollLeft <= 0) {
                leftArrow.classList.add('disabled');
            } else {
                leftArrow.classList.remove('disabled');
            }

            // Disable right arrow if at end
            if (scrollLeft >= maxScroll - 1) {
                rightArrow.classList.add('disabled');
            } else {
                rightArrow.classList.remove('disabled');
            }
        }

        /**
         * Scroll left handler
         */
        leftArrow.addEventListener('click', function(e) {
            e.preventDefault();
            container.scrollBy({
                left: -scrollAmount,
                behavior: 'smooth'
            });
            // Update arrows after scroll completes
            setTimeout(updateArrows, 350);
        });

        /**
         * Scroll right handler
         */
        rightArrow.addEventListener('click', function(e) {
            e.preventDefault();
            container.scrollBy({
                left: scrollAmount,
                behavior: 'smooth'
            });
            // Update arrows after scroll completes
            setTimeout(updateArrows, 350);
        });

        /**
         * Update arrows on scroll
         */
        container.addEventListener('scroll', updateArrows);

        /**
         * Update arrows on window resize
         */
        window.addEventListener('resize', function() {
            updateArrows();
        });

        /**
         * Initial arrow state
         */
        updateArrows();


        /**
         * Optional: Keyboard navigation
         * Press left/right arrow keys to scroll categories
         */
        document.addEventListener('keydown', function(e) {
            // Only if container is in viewport
            const rect = container.getBoundingClientRect();
            const inViewport = (
                rect.top >= 0 &&
                rect.left >= 0 &&
                rect.bottom <= window.innerHeight &&
                rect.right <= window.innerWidth
            );

            if (!inViewport) return;

            if (e.key === 'ArrowLeft') {
                e.preventDefault();
                container.scrollBy({
                    left: -scrollAmount,
                    behavior: 'smooth'
                });
                setTimeout(updateArrows, 350);
            } else if (e.key === 'ArrowRight') {
                e.preventDefault();
                container.scrollBy({
                    left: scrollAmount,
                    behavior: 'smooth'
                });
                setTimeout(updateArrows, 350);
            }
        });


        /**
         * Optional: Mouse drag to scroll
         * Allows users to click and drag the category pills
         */
        let isDown = false;
        let startX;
        let scrollLeftStart;

        container.addEventListener('mousedown', function(e) {
            // Only enable drag on non-button elements
            if (e.target.closest('.tab-button')) return;

            isDown = true;
            container.style.cursor = 'grabbing';
            startX = e.pageX - container.offsetLeft;
            scrollLeftStart = container.scrollLeft;
        });

        container.addEventListener('mouseleave', function() {
            isDown = false;
            container.style.cursor = 'default';
        });

        container.addEventListener('mouseup', function() {
            isDown = false;
            container.style.cursor = 'default';
        });

        container.addEventListener('mousemove', function(e) {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - container.offsetLeft;
            const walk = (x - startX) * 2; // Scroll speed multiplier
            container.scrollLeft = scrollLeftStart - walk;
        });

    }

    /**
     * Initialize on DOM ready
     */
    $(document).ready(function() {
        initCategoryScroll();
    });

})(jQuery);
