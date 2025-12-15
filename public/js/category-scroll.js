/**
 * Minimal Category Navigation - Horizontal Scrolling
 * Handles left/right arrow clicks and visibility for text-based navigation
 *
 * @package Partner_CIU_Manager
 * @version 2.0.0
 */

(function($) {
    'use strict';

    /**
     * Initialize minimal category navigation
     */
    function initCategoryScroll() {

        // Try new minimal navigation first
        let container = document.getElementById('categoryListScroll');
        let leftArrow = document.getElementById('navLeft');
        let rightArrow = document.getElementById('navRight');

        // Fallback to old IDs if new ones not found
        if (!container || !leftArrow || !rightArrow) {
            container = document.getElementById('categoriesContainer');
            leftArrow = document.getElementById('scrollLeftArrow');
            rightArrow = document.getElementById('scrollRightArrow');
        }

        // Exit if elements not found
        if (!container || !leftArrow || !rightArrow) {
            return;
        }

        // Scroll amount (pixels to scroll per click)
        const scrollAmount = 250;

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
         * Category link click handler (for minimal navigation)
         */
        const categoryLinks = document.querySelectorAll('.category-link');
        categoryLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();

                // Remove active from all links
                categoryLinks.forEach(function(l) {
                    l.classList.remove('active');
                    l.setAttribute('aria-selected', 'false');
                });

                // Add active to clicked link
                this.classList.add('active');
                this.setAttribute('aria-selected', 'true');

                // Get category slug
                const categorySlug = this.getAttribute('data-category');
                console.log('Category selected:', categorySlug);

                // Here you can add logic to show/hide category content
                // For example, show the corresponding tab panel
                const panels = document.querySelectorAll('.tab-panel');
                panels.forEach(function(panel) {
                    panel.classList.remove('active');
                });

                const targetPanel = document.getElementById('tab-panel-' + categorySlug);
                if (targetPanel) {
                    targetPanel.classList.add('active');
                }
            });
        });

        /**
         * Optional: Mouse drag to scroll
         * Allows users to click and drag the category navigation
         */
        let isDown = false;
        let startX;
        let scrollLeftStart;

        container.addEventListener('mousedown', function(e) {
            // Only enable drag on non-interactive elements
            if (e.target.closest('.tab-button') || e.target.closest('.category-link')) return;

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
