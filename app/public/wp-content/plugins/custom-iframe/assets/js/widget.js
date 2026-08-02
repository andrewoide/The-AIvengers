// // Initialize for Elementor
(function (elementor) {
    'use strict';
    window.addEventListener('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/custif_iframe_widget.default', widgetIframe);
    });

}(window.elementorFrontend));

// Initialize for Gutenberg / Standard
document.addEventListener('DOMContentLoaded', function () {
    var wrappers = document.querySelectorAll('.custif-iframe-wrapper');
    wrappers.forEach(function (wrapper) {
        // Skip if inside Elementor widget (handled by hook)
        if (!wrapper.closest('.elementor-widget-custif_iframe_widget')) {
            // Pass wrapper itself as scope[0] to our flexible function
            widgetIframe([wrapper]);
        }
    });

    // Observer for dynamic content (Gutenberg Editor)
    var observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            mutation.addedNodes.forEach(function (node) {
                if (node.nodeType === 1) { // Element node
                    // Check if node itself is the wrapper
                    if (node.classList && node.classList.contains('custif-iframe-wrapper')) {
                        if (!node.closest('.elementor-widget-custif_iframe_widget')) {
                            widgetIframe([node]);
                        }
                    }
                    // Check descendants
                    var children = node.querySelectorAll('.custif-iframe-wrapper');
                    children.forEach(function (child) {
                        if (!child.closest('.elementor-widget-custif_iframe_widget')) {
                            widgetIframe([child]);
                        }
                    });
                }
            });
        });
    });

    observer.observe(document.body, { childList: true, subtree: true });
});

function widgetIframe(scope) {
    if (!scope || !scope[0]) {
        return;
    }

    var iframe = scope[0].querySelector('.custif-iframe-wrapper > iframe');

    // If iframe not found with default selector, try finding it directly (useful if scope IS the wrapper)
    if (!iframe && scope[0].classList.contains('custif-iframe-wrapper')) {
        iframe = scope[0].querySelector('iframe');
    }

    if (!iframe) {
        return;
    }

    var autoHeight = iframe.dataset.autoHeight,
        refreshInterval = iframe.dataset.refreshInterval ? parseInt(iframe.dataset.refreshInterval) : 0;

    // Auto height only works when cross origin properly set
    if (autoHeight === 'yes') {
        try {
            var resizeIframe = function () {
                var height = iframe.contentDocument.querySelector('html').scrollHeight;
                iframe.style.height = height + 'px';
            };

            iframe.addEventListener('load', resizeIframe);
            resizeIframe(); // Try immediately
        } catch (e) {
            console.log('Cross origin iframe detected');
        }
    }

    // Refresh interval
    if (refreshInterval > 0) {
        setInterval(() => {
            iframe.src = iframe.src;
        }, refreshInterval * 1000);
    }
}