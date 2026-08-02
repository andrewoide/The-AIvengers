/**
 * Custom initialization script for PDF.js viewer
 * Enhanced version with additional controls support
 */
(function() {
    'use strict';

    // Configuration object to store settings
    const custifConfig = {
        themeMode: 'light',
        toolbar: true,
        position: 'top',
        presentation: true,
        lazyLoad: false,
        download: true,
        copy_text: true,
        add_text: true,
        draw: true,
        pdf_rotation: true,
        pdf_image: true,
        pdf_details: true,
        customColor: null,
        zoom: 'auto',
        selection_tool: '0',
        scrolling: '0',
        spreads: '-1'
    };

    /**
     * Process URL parameters and update configuration
     */
    function processUrlParams() {
        const urlParams = new URLSearchParams(window.location.search);

        // Get basic parameters
        if (urlParams.has('file')) {
            custifConfig.file = urlParams.get('file');
        }

        if (urlParams.has('zoom')) {
            custifConfig.zoom = urlParams.get('zoom');
        }

        // Process key parameter (base64 encoded settings)
        if (urlParams.has('key')) {
            try {
                const decodedKey = atob(urlParams.get('key'));
                const keyParams = new URLSearchParams(decodedKey);

                // Update config with decoded key parameters
                if (keyParams.has('themeMode')) {
                    custifConfig.themeMode = keyParams.get('themeMode');
                }

                if (keyParams.has('customColor')) {
                    custifConfig.customColor = keyParams.get('customColor');
                }

                if (keyParams.has('toolbar')) {
                    custifConfig.toolbar = keyParams.get('toolbar') === 'true';
                }

                if (keyParams.has('position')) {
                    custifConfig.position = keyParams.get('position');
                }

                if (keyParams.has('presentation')) {
                    custifConfig.presentation = keyParams.get('presentation') === 'true';
                }

                if (keyParams.has('download')) {
                    custifConfig.download = keyParams.get('download') === 'true';
                }

                if (keyParams.has('lazyLoad')) {
                    custifConfig.lazyLoad = keyParams.get('lazyLoad') === 'true';
                }

                // Add support for text copy
                if (keyParams.has('copy_text')) {
                    custifConfig.copy_text = keyParams.get('copy_text') === 'true';
                }

                // Add support for adding text
                if (keyParams.has('add_text')) {
                    custifConfig.add_text = keyParams.get('add_text') === 'true';
                }

                // Add support for drawing
                if (keyParams.has('draw')) {
                    custifConfig.draw = keyParams.get('draw') === 'true';
                }

                // Add support for rotation
                if (keyParams.has('pdf_rotation')) {
                    custifConfig.pdf_rotation = keyParams.get('pdf_rotation') === 'true';
                }

                // Add support for image
                if (keyParams.has('pdf_rotation')) {
                    custifConfig.pdf_image = keyParams.get('pdf_image') === 'true';
                }

                // Add support for document properties
                if (keyParams.has('pdf_details')) {
                    custifConfig.pdf_details = keyParams.get('pdf_details') === 'true';
                }

                // Add support for selection tool
                if (keyParams.has('selection_tool')) {
                    custifConfig.selection_tool = keyParams.get('selection_tool');
                }

                // Add support for scrolling mode
                if (keyParams.has('scrolling')) {
                    custifConfig.scrolling = keyParams.get('scrolling');
                }

                // Add support for spreads
                if (keyParams.has('spreads')) {
                    custifConfig.spreads = keyParams.get('spreads');
                }
            } catch (error) {
                console.error('Error decoding key parameter:', error);
            }
        }
    }

    /**
     * Apply theme mode to the document
     */
    function applyThemeMode() {
        const htmlEl = document.getElementsByTagName('html')[0];
        if (htmlEl) {
            htmlEl.setAttribute('custif-data-theme', custifConfig.themeMode);

            // Apply custom color if specified
            if (custifConfig.themeMode === 'custom' && custifConfig.customColor) {
                applyCustomThemeColors(custifConfig.customColor);
            }
        }
    }

    /**
     * Calculate the brightness of a hex color
     * @param {string} hexColor - The color in hex format
     * @return {number} - Brightness percentage (0-100)
     */
    function ColorBrightness(hexColor) {
        const r = parseInt(hexColor.slice(1, 3), 16);
        const g = parseInt(hexColor.slice(3, 5), 16);
        const b = parseInt(hexColor.slice(5, 7), 16);

        // Calculate brightness
        const max = Math.max(r, g, b);
        const min = Math.min(r, g, b);
        const l = (max + min) / 2;

        return Math.round(l / 255 * 100);
    }

    /**
     * Adjust a hex color by a percentage
     * @param {string} hexColor - The color in hex format
     * @param {number} percentage - Percentage to adjust by
     * @return {string} - New hex color
     */
    function adjustColor(hexColor, percentage) {
        // Convert hex to RGB
        const r = parseInt(hexColor.slice(1, 3), 16);
        const g = parseInt(hexColor.slice(3, 5), 16);
        const b = parseInt(hexColor.slice(5, 7), 16);

        // Calculate adjustment
        const adjustment = Math.round((percentage / 100) * 255);
        const newR = Math.max(Math.min(r + adjustment, 255), 0);
        const newG = Math.max(Math.min(g + adjustment, 255), 0);
        const newB = Math.max(Math.min(b + adjustment, 255), 0);

        // Convert back to hex
        return '#' + ((1 << 24) + (newR << 16) + (newG << 8) + newB).toString(16).slice(1);
    }

    /**
     * Apply custom theme colors
     * @param {string} baseColor - The base color in hex format
     */
    function applyCustomThemeColors(baseColor) {
        // Create style element
        const style = document.createElement('style');
        style.setAttribute('id', 'CustifCustomThemeStyle');

        // Calculate derived colors
        const colorBrightness = ColorBrightness(baseColor);
        const iconsTextsColor = colorBrightness > 60 ? 'black' : 'white';

        // Generate CSS
        style.textContent = `
            [custif-data-theme="custom"] {
                --body-bg-color: ${baseColor};
                --toolbar-bg-color: ${adjustColor(baseColor, 15)};
                --doorhanger-bg-color: ${baseColor};
                --field-bg-color: ${baseColor};
                --dropdown-btn-bg-color: ${baseColor};
                --button-hover-color: ${adjustColor(baseColor, 25)};
                --toggled-btn-bg-color: ${adjustColor(baseColor, 25)};
                --doorhanger-hover-bg-color: ${adjustColor(baseColor, 20)};
                --toolbar-border-color: ${adjustColor(baseColor, 10)};
                --doorhanger-border-color: ${adjustColor(baseColor, 10)};
                --doorhanger-border-color-whcm: ${adjustColor(baseColor, 10)};
                --separator-color: ${adjustColor(baseColor, 10)};
                --doorhanger-separator-color: ${adjustColor(baseColor, 15)};
                --toolbar-icon-bg-color: ${iconsTextsColor};
                --main-color: ${iconsTextsColor};
                --field-color: ${iconsTextsColor};
                --doorhanger-hover-color: ${iconsTextsColor};
                --toolbar-icon-hover-bg-color: ${iconsTextsColor};
                --toggled-btn-color: ${iconsTextsColor};
            }`;

        // Add style to document
        document.head.appendChild(style);
    }

    /**
     * Apply toolbar visibility and positioning
     */
    function applyToolbarSettings() {
        // Create style element
        const style = document.createElement('style');
        style.setAttribute('id', 'CustifToolbarStyle');

        let toolbarCSS = '';
        let settingsPos = '';

        // Determine toolbar visibility
        const toolbar = custifConfig.toolbar ? 'block' : 'none';

        // Determine toolbar position
        if (custifConfig.position === 'top') {
            settingsPos = '';
        } else {
            settingsPos = `
                .doorHangerRight:after{
                    transform: rotate(180deg);
                    bottom: -16px;
                }
                .doorHangerRight:before {
                    transform: rotate(180deg);
                    bottom: -18px;
                }
                .findbar.doorHanger:before {
                    bottom: -18px;
                    transform: rotate(180deg);
                }
                .findbar.doorHanger:after {
                    bottom: -16px;
                    transform: rotate(180deg);
                }
                div#editorInkParamsToolbar, #editorFreeTextParamsToolbar {
                    bottom: 32px;
                    top: auto; 
                }
                #mainContainer {
                    top: -40px!important;
                }
            `;
        }

        // Set presentation and download features
        const presentation = custifConfig.presentation ? 'flex' : 'none';
        const download = custifConfig.download ? 'flex' : 'none';

        // Handle text selection
        const textSelection = custifConfig.copy_text ? 'text' : 'none';

        // Handle text editing tools
        const addText = custifConfig.add_text ? 'block' : 'none';
        const draw = custifConfig.draw ? 'block' : 'none';

        // Handle rotation and properties
        const rotation = custifConfig.pdf_rotation ? 'block' : 'none';
        const image = custifConfig.pdf_image ? 'block' : 'none';
        const properties = custifConfig.pdf_details ? 'block' : 'none';

        // Build CSS
        toolbarCSS = `
            .toolbar {
                display: ${toolbar}!important;
                position: absolute;
                width: 100%;
                ${custifConfig.position === 'top' ? 'top:0;bottom:auto;' : 'bottom:0;top:auto;'}
            }
            #secondaryToolbar {
                display: ${toolbar};
                ${custifConfig.position === 'bottom' ? 'top: auto; bottom: 32px' : ''}
            }
            #secondaryPresentationMode, #toolbarViewerRight #presentationMode {
                display: ${presentation}!important;
            }
            #secondaryOpenFile, #toolbarViewerRight #openFile {
                display: none!important;
            }
            #secondaryDownload, #secondaryPrint, #printButton, #downloadButton {
                display: ${download}!important;
            }
            .textLayer {
                user-select: ${textSelection}!important;
                -webkit-user-select: ${textSelection}!important;
                -moz-user-select: ${textSelection}!important;
                -ms-user-select: ${textSelection}!important;
            }
            button#cursorSelectTool {
                display: ${textSelection !== 'none' ? 'block' : 'none'}!important;
            }
            #editorFreeText {
                display: ${addText}!important;
            }
            #editorInk {
                display: ${draw}!important;
            }
            #editorStamp {
                display: ${image}!important;
            }
            #pageRotateCw, #pageRotateCcw {
                display: ${rotation}!important;
            }
            #pageRotateCw, #pageRotateCcw {
                display: ${rotation}!important;
            }
            #documentProperties {
                display: ${properties}!important;
            }
            ${settingsPos}
        `;

        style.textContent = toolbarCSS;
        document.head.appendChild(style);
    }

    /**
     * Handle lazy loading
     */
    function handleLazyLoading() {
        if (custifConfig.lazyLoad === false) {
            document.querySelector('html').style.opacity = '1';
        } else {
            function updateOpacity() {
                const pdfViewer = document.querySelector('.pdfViewer');

                if (pdfViewer && pdfViewer.innerHTML.trim()) {
                    document.querySelector('html').style.opacity = '1';
                    document.querySelector('html').style.transition = '500ms';
                    clearInterval(intervalId);
                }
            }

            const intervalId = setInterval(updateOpacity, 100);
            updateOpacity();
        }
    }

    /**
     * Listen for messages from parent window
     */
    function setupMessageListener() {
        window.addEventListener('message', function(event) {
            try {
                const data = typeof event.data === 'string' ? JSON.parse(event.data) : event.data;

                if (data.type === 'custif-pdf-settings') {
                    // Update settings from parent window
                    Object.assign(custifConfig, data.settings);

                    // Apply settings
                    applyThemeMode();
                }
            } catch (error) {
                // Silent error - not all messages will be for us
            }
        });
    }

    /**
     * Setup height reporting for responsive iframe
     */
    function setupHeightReporting() {
        // Initial height report
        function reportHeight() {
            if (window.parent && window.parent !== window) {
                window.parent.postMessage(JSON.stringify({
                    type: 'custif-resize',
                    height: document.body.scrollHeight
                }), '*');
            }
        }

        // Report height when PDF is loaded and when pages change
        if (typeof PDFViewerApplication !== 'undefined') {
            // When document loads
            PDFViewerApplication.eventBus.on('documentloaded', reportHeight);

            // When page changes
            PDFViewerApplication.eventBus.on('pagechanging', reportHeight);

            // When zoom changes
            PDFViewerApplication.eventBus.on('scalechanging', reportHeight);
        }

        // Report periodically and on window resize
        setInterval(reportHeight, 1000);
        window.addEventListener('resize', reportHeight);
    }

    /**
     * Initialize the custom functionality
     */
    function init() {
        // Process URL parameters
        processUrlParams();

        // Apply theme and toolbar settings
        applyThemeMode();
        applyToolbarSettings();

        // Setup lazy loading
        handleLazyLoading();

        // Configure PDF viewer when available
        document.addEventListener('webviewerloaded', function() {
            setupHeightReporting();
        });

        // Setup message listener for parent iframe communication
        setupMessageListener();
    }

    // Run initialization when the document is loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();