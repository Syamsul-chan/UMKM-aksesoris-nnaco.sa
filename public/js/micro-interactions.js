/**
 * nnaco.sa - Micro Interactions JavaScript Engine
 * Provides:
 * 1. Dynamic Ripple Wave on all buttons and clickable elements
 * 2. Skeleton Shimmer Generator for AJAX Loading States
 */

(function () {
    'use strict';

    // -------------------------------------------------------------------------
    // 1. Dynamic Ripple Wave Effect on Click
    // -------------------------------------------------------------------------
    function createRipple(e) {
        const target = e.currentTarget;

        // Skip disabled buttons
        if (target.disabled || target.classList.contains('disabled')) return;

        // Make sure target has relative positioning & overflow hidden
        if (!target.classList.contains('btn-ripple')) {
            target.classList.add('btn-ripple');
        }

        // Remove old ripples inside this element
        const oldRipple = target.querySelector('.btn-ripple-wave');
        if (oldRipple) {
            oldRipple.remove();
        }

        const rect = target.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = e.clientX - rect.left - size / 2;
        const y = e.clientY - rect.top - size / 2;

        const ripple = document.createElement('span');
        ripple.classList.add('btn-ripple-wave');
        ripple.style.width = ripple.style.height = `${size}px`;
        ripple.style.left = `${x}px`;
        ripple.style.top = `${y}px`;

        target.appendChild(ripple);

        // Auto remove ripple after animation ends
        setTimeout(() => {
            if (ripple && ripple.parentNode) {
                ripple.remove();
            }
        }, 600);
    }

    // Attach ripple listeners
    function attachRipples() {
        const buttonSelectors = [
            'button:not(.no-ripple)',
            '.btn:not(.no-ripple)',
            '.btn-signin-capsule',
            '.btn-wa-capsule',
            '.d-btn-action-primary',
            '.d-btn-cyan-subtle',
            '.d-btn-pill-buy',
            '.d-category-pill',
            '.d-mode-toggle-wrap',
            '.page-link'
        ];

        document.querySelectorAll(buttonSelectors.join(',')).forEach((el) => {
            if (!el.dataset.hasRipple) {
                el.dataset.hasRipple = 'true';
                el.addEventListener('click', createRipple);
            }
        });
    }

    // -------------------------------------------------------------------------
    // 2. Skeleton Shimmer Generator Utility
    // -------------------------------------------------------------------------
    window.NnacoSkeleton = {
        /**
         * Generate Product Card Skeleton HTML
         * @param {number} count Number of skeleton cards
         * @returns {string} HTML string of skeleton cards
         */
        generateProductSkeletons: function (count = 4) {
            let html = '';
            for (let i = 0; i < count; i++) {
                html += `
                <div class="col skeleton-shimmer-item">
                    <div class="skeleton-card">
                        <div class="skeleton-box"></div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="skeleton-badge"></span>
                            <span class="skeleton-line" style="width: 40px; margin-bottom: 0;"></span>
                        </div>
                        <div class="skeleton-title"></div>
                        <div class="skeleton-line medium"></div>
                        <div class="skeleton-line short mb-3"></div>
                        <div class="mt-auto">
                            <div class="skeleton-btn"></div>
                        </div>
                    </div>
                </div>`;
            }
            return html;
        },

        /**
         * Render skeleton cards into a target container
         * @param {HTMLElement|string} container Target element or selector
         * @param {number} count Number of items
         */
        showIn: function (container, count = 4) {
            const el = typeof container === 'string' ? document.querySelector(container) : container;
            if (!el) return;
            el.dataset.previousContent = el.innerHTML;
            el.innerHTML = this.generateProductSkeletons(count);
        },

        /**
         * Restore original content if needed
         */
        restore: function (container) {
            const el = typeof container === 'string' ? document.querySelector(container) : container;
            if (!el || !el.dataset.previousContent) return;
            el.innerHTML = el.dataset.previousContent;
            delete el.dataset.previousContent;
        }
    };

    // Initialize on DOM load and observe dynamic DOM changes
    document.addEventListener('DOMContentLoaded', function () {
        attachRipples();

        // MutationObserver to auto-attach ripples to dynamically created buttons
        const observer = new MutationObserver(function () {
            attachRipples();
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    });
})();

