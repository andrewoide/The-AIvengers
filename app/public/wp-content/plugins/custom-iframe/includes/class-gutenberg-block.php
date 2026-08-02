<?php

namespace custif\includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gutenberg Block Class
 *
 * Handles the registration of the Gutenberg block.
 *
 * @since 1.0.19
 */
class Gutenberg_Block {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_block' ) );
	}

	/**
	 * Register the block.
	 */
	public function register_block() {
		// Calculate the correct path for build assets.
		// Assuming we will compile JS to assets/js/block/build or similar.
		// For now, let's point to assets/js/block where we will put block.json.

		register_block_type(
			CUSTIF_PATH . 'assets/js/block/build',
			array(
				'render_callback' => array( $this, 'render_callback' ),
				'style'           => 'custif-styles',
				'editor_style'    => 'custif-styles',
				'script'          => 'custif-scripts',
			)
		);
	}

	/**
	 * Render callback for the block.
	 *
	 * @param array  $attributes The block attributes.
	 * @param string $content    The block content.
	 *
	 * @return string The rendered block HTML.
	 */
	public function render_callback( $attributes, $content ) {
		if ( ! class_exists( 'custif\includes\Renderer' ) ) {
			require_once CUSTIF_PATH . 'includes/class-renderer.php';
		}

		$renderer = new \custif\includes\Renderer();
		$settings = array(
			'custif_custom_id'  => isset( $attributes['customId'] ) ? $attributes['customId'] : '',
			'source'            => isset( $attributes['source'] ) ? $attributes['source'] : 'default',
			'iframe_url'        => isset( $attributes['iframeUrl'] ) ? $attributes['iframeUrl'] : 'https://example.com',
			'align'             => isset( $attributes['align'] ) ? $attributes['align'] : '',
			'height'            => isset( $attributes['iframeHeight'] ) ? $attributes['iframeHeight'] : '500px',
			'height_tablet'     => isset( $attributes['iframeHeightTablet'] ) ? $attributes['iframeHeightTablet'] : '400px',
			'height_mobile'     => isset( $attributes['iframeHeightMobile'] ) ? $attributes['iframeHeightMobile'] : '300px',
			'padding'           => isset( $attributes['padding'] ) ? $attributes['padding'] : array(),
			'padding_tablet'    => isset( $attributes['paddingTablet'] ) ? $attributes['paddingTablet'] : array(),
			'padding_mobile'    => isset( $attributes['paddingMobile'] ) ? $attributes['paddingMobile'] : array(),
			'iframe_width'      => isset( $attributes['iframeWidth'] ) ? $attributes['iframeWidth'] : '100%',
			'iframe_width_tablet' => isset( $attributes['iframeWidthTablet'] ) ? $attributes['iframeWidthTablet'] : '100%',
			'iframe_width_mobile' => isset( $attributes['iframeWidthMobile'] ) ? $attributes['iframeWidthMobile'] : '100%',
			'color_value'       => isset( $attributes['colorValue'] ) ? $attributes['colorValue'] : '',
			'gradient_value'    => isset( $attributes['gradientValue'] ) ? $attributes['gradientValue'] : '',
			'background_style'  => isset( $attributes['backgroundStyle'] ) ? $attributes['backgroundStyle'] : 'solid',
			'background_image_url' => isset( $attributes['backgroundImageUrl'] ) ? $attributes['backgroundImageUrl'] : '',
			'border'            => isset( $attributes['border'] ) ? $attributes['border'] : array(),
			'shadow'            => isset( $attributes['shadow'] ) ? $attributes['shadow'] : array(),
			'auto_height'       => ( isset( $attributes['autoHeight'] ) && $attributes['autoHeight'] ) ? 'yes' : 'no',
			'show_scrollbars'   => ( isset( $attributes['showScrollbar'] ) && $attributes['showScrollbar'] ) ? 'yes' : 'no',
			'refresh_interval'  => isset( $attributes['refreshInterval'] ) ? $attributes['refreshInterval'] : 0,
			'enable_lazy_load'  => ( isset( $attributes['lazyLoad'] ) && $attributes['lazyLoad'] ) ? 'yes' : 'no',
			'placeholder_image' => ( ! empty( $attributes['lazyLoad'] ) && ! empty( $attributes['lazyLoadImageUrl'] ) ) ? $attributes['lazyLoadImageUrl'] : '',
			'pdf_type'          => isset( $attributes['pdfSourceType'] ) ? $attributes['pdfSourceType'] : 'file',
			'pdf_Uploader'      => array(
				'id'  => isset( $attributes['pdfFileId'] ) ? $attributes['pdfFileId'] : 0,
				'url' => isset( $attributes['pdfFileUrl'] ) ? $attributes['pdfFileUrl'] : '',
			),
			'pdf_file_link'     => array(
				'url' => isset( $attributes['pdfUrl'] ) ? $attributes['pdfUrl'] : '',
			),
			'pdf_lazyload'      => ( isset( $attributes['pdfLazyLoad'] ) && $attributes['pdfLazyLoad'] ) ? 'yes' : 'no',
			'pdf_theme_mode'    => isset( $attributes['theme'] ) ? $attributes['theme'] : 'default',
			'pdf_custom_color'  => isset( $attributes['themeColor'] ) ? $attributes['themeColor'] : '',
			'pdf_zoom'          => isset( $attributes['zoom'] ) ? $attributes['zoom'] : 'auto',
			'selection_tool'    => isset( $attributes['defaultSelectionTool'] ) ? $attributes['defaultSelectionTool'] : '0',
			'spreads'           => isset( $attributes['defaultSpreads'] ) ? $attributes['defaultSpreads'] : '0',
			'scrolling'         => isset( $attributes['defaultScrolling'] ) ? $attributes['defaultScrolling'] : '0',
			'pdf_toolbar'           => ( isset( $attributes['pdfToolbar'] ) && $attributes['pdfToolbar'] ) ? 'yes' : 'no',
			'pdf_toolbar_position'  => isset( $attributes['pdfToolbarPosition'] ) ? $attributes['pdfToolbarPosition'] : 'top',
			'pdf_print_download'    => ( isset( $attributes['pdfPrintDownload'] ) && $attributes['pdfPrintDownload'] ) ? 'yes' : 'no',
			'pdf_presentation_mode' => ( isset( $attributes['pdfPresentationMode'] ) && $attributes['pdfPresentationMode'] ) ? 'yes' : 'no',
			'pdf_text_copy'         => ( isset( $attributes['pdfTextCopy'] ) && $attributes['pdfTextCopy'] ) ? 'yes' : 'no',
			'pdf_add_text'          => ( isset( $attributes['pdfAddText'] ) && $attributes['pdfAddText'] ) ? 'yes' : 'no',
			'pdf_draw'              => ( isset( $attributes['pdfDraw'] ) && $attributes['pdfDraw'] ) ? 'yes' : 'no',
			'pdf_add_image'         => ( isset( $attributes['pdfAddImage'] ) && $attributes['pdfAddImage'] ) ? 'yes' : 'no',
			'pdf_rotate_access'     => ( isset( $attributes['pdfRotateAccess'] ) && $attributes['pdfRotateAccess'] ) ? 'yes' : 'no',
			'pdf_details'           => ( isset( $attributes['pdfDetails'] ) && $attributes['pdfDetails'] ) ? 'yes' : 'no',
			'pdf_view_mode'         => ( isset( $attributes['pdfViewMode'] ) && $attributes['pdfViewMode'] ) ? 'yes' : 'no',
			'pdf_zoom_in'           => ( isset( $attributes['pdfZoomIn'] ) && $attributes['pdfZoomIn'] ) ? 'yes' : 'no',
			'pdf_zoom_out'          => ( isset( $attributes['pdfZoomOut'] ) && $attributes['pdfZoomOut'] ) ? 'yes' : 'no',
			'watermark_enable'      => ( isset( $attributes['enableWatermark'] ) && $attributes['enableWatermark'] ) ? 'yes' : 'no',
			'watermark_type'        => isset( $attributes['watermarkType'] ) ? $attributes['watermarkType'] : 'text',
			'watermark_text'        => isset( $attributes['watermarkText'] ) ? $attributes['watermarkText'] : 'Watermark',
			'watermark_image_id'    => isset( $attributes['watermarkImageId'] ) ? $attributes['watermarkImageId'] : 0,
			'watermark_image_url'   => isset( $attributes['watermarkImageUrl'] ) ? $attributes['watermarkImageUrl'] : '',
			'watermark_position'    => isset( $attributes['watermarkPosition'] ) ? $attributes['watermarkPosition'] : 'bottom-right',
			'watermark_opacity'     => isset( $attributes['watermarkOpacity'] ) ? $attributes['watermarkOpacity'] : 0.5,
			'watermark_color'       => isset( $attributes['watermarkColor'] ) ? $attributes['watermarkColor'] : 'rgba(0, 0, 0, 0.5)',
			'watermark_image_size'  => isset( $attributes['watermarkImgSize'] ) ? $attributes['watermarkImgSize'] : '12px',
			'sandbox'             => ( isset( $attributes['sandbox'] ) && $attributes['sandbox'] ) ? 'yes' : 'no',
			'sandbox_options'     => isset( $attributes['sandboxOptions'] ) ? $attributes['sandboxOptions'] : '',
			'extra_attributes_enable' => ( isset( $attributes['extraAttributes'] ) && $attributes['extraAttributes'] ) ? 'yes' : 'no',
			'custom_iframe_attributes' => isset( $attributes['customAttributes'] ) ? $attributes['customAttributes'] : '',
			'x_theme'             => isset( $attributes['xTheme'] ) ? $attributes['xTheme'] : 'light',
			'x_hide_media'        => ( isset( $attributes['xHideMedia'] ) && $attributes['xHideMedia'] ) ? 'yes' : 'no',
			'x_hide_thread'       => ( isset( $attributes['xHideThread'] ) && $attributes['xHideThread'] ) ? 'yes' : 'no',
			'x_lang'              => isset( $attributes['xLang'] ) ? $attributes['xLang'] : 'en',
			'x_dnt'               => ( isset( $attributes['xDnt'] ) && $attributes['xDnt'] ) ? 'yes' : 'no',
			'youtube_autoplay'    => ( isset( $attributes['youtubeAutoplay'] ) && $attributes['youtubeAutoplay'] ) ? 'yes' : 'no',
			'youtube_mute'        => ( isset( $attributes['youtubeMute'] ) && $attributes['youtubeMute'] ) ? 'yes' : 'no',
			'youtube_controls'    => ( isset( $attributes['youtubeControls'] ) && $attributes['youtubeControls'] ) ? 'yes' : 'no',
			'youtube_loop'        => ( isset( $attributes['youtubeLoop'] ) && $attributes['youtubeLoop'] ) ? 'yes' : 'no',
			'youtube_privacy_mode' => ( isset( $attributes['youtubePrivacyMode'] ) && $attributes['youtubePrivacyMode'] ) ? 'yes' : 'no',
			'youtube_playsinline' => ( isset( $attributes['youtubePlaysinline'] ) && $attributes['youtubePlaysinline'] ) ? 'yes' : 'no',
			'youtube_start_time'  => isset( $attributes['youtubeStartTime'] ) ? $attributes['youtubeStartTime'] : 0,
			'youtube_end_time'    => isset( $attributes['youtubeEndTime'] ) ? $attributes['youtubeEndTime'] : 0,
			'youtube_playlist'    => isset( $attributes['youtubePlaylist'] ) ? $attributes['youtubePlaylist'] : '',
			'youtube_color'       => isset( $attributes['youtubeColor'] ) ? $attributes['youtubeColor'] : 'red',
			'youtube_rel'         => ( isset( $attributes['youtubeRel'] ) && $attributes['youtubeRel'] ) ? 'yes' : 'no',
			'youtube_fullscreen'  => ( isset( $attributes['youtubeFullscreen'] ) && $attributes['youtubeFullscreen'] ) ? 'yes' : '',
			'youtube_disable_annotations' => ( isset( $attributes['youtubeDisableAnnotations'] ) && $attributes['youtubeDisableAnnotations'] ) ? 'yes' : '',
			'youtube_keyboard_controls'   => ( isset( $attributes['youtubeKeyboardControls'] ) && $attributes['youtubeKeyboardControls'] ) ? 'yes' : '',
			'youtube_cc_load_policy'      => ( isset( $attributes['youtubeCcLoadPolicy'] ) && $attributes['youtubeCcLoadPolicy'] ) ? 'yes' : '',
			'youtube_cc_lang_pref'        => isset( $attributes['youtubeCcLangPref'] ) ? $attributes['youtubeCcLangPref'] : '',
			'youtube_lang'                => isset( $attributes['youtubeLang'] ) ? $attributes['youtubeLang'] : '',
			'youtube_player_api_support'  => ( isset( $attributes['youtubePlayerApiSupport'] ) && $attributes['youtubePlayerApiSupport'] ) ? 'yes' : '',
			'youtube_js_callback'         => isset( $attributes['youtubeJsCallback'] ) ? $attributes['youtubeJsCallback'] : '',
			'youtube_widget_referrer'     => isset( $attributes['youtubeWidgetReferrer'] ) ? $attributes['youtubeWidgetReferrer'] : '',
			'youtube_custom_parameters'   => isset( $attributes['youtubeCustomParameters'] ) ? $attributes['youtubeCustomParameters'] : '',
			'vimeo_autoplay'      => ( isset( $attributes['vimeoAutoplay'] ) && $attributes['vimeoAutoplay'] ) ? 'yes' : 'no',
			'vimeo_muted'         => ( isset( $attributes['vimeoMuted'] ) && $attributes['vimeoMuted'] ) ? 'yes' : 'no',
			'vimeo_loop'          => ( isset( $attributes['vimeoLoop'] ) && $attributes['vimeoLoop'] ) ? 'yes' : 'no',
			'vimeo_controls'      => ( isset( $attributes['vimeoControls'] ) && $attributes['vimeoControls'] ) ? 'yes' : 'no',
			'vimeo_start_time'    => isset( $attributes['vimeoStartTime'] ) ? $attributes['vimeoStartTime'] : '',
			'vimeo_quality'       => isset( $attributes['vimeoQuality'] ) ? $attributes['vimeoQuality'] : 'auto',
			'vimeo_autopause'     => ( isset( $attributes['vimeoAutopause'] ) && $attributes['vimeoAutopause'] ) ? 'yes' : 'no',
			'vimeo_playsinline'   => ( isset( $attributes['vimeoPlaysinline'] ) && $attributes['vimeoPlaysinline'] ) ? 'yes' : 'no',
			'vimeo_color'         => isset( $attributes['vimeoColor'] ) ? $attributes['vimeoColor'] : '#00adef',
			'vimeo_byline'        => ( isset( $attributes['vimeoByline'] ) && $attributes['vimeoByline'] ) ? 'yes' : 'no',
			'vimeo_portrait'      => ( isset( $attributes['vimeoPortrait'] ) && $attributes['vimeoPortrait'] ) ? 'yes' : 'no',
			'vimeo_title'         => ( isset( $attributes['vimeoTitle'] ) && $attributes['vimeoTitle'] ) ? 'yes' : 'no',
			'vimeo_dnt'           => ( isset( $attributes['vimeoDnt'] ) && $attributes['vimeoDnt'] ) ? 'yes' : 'no',
			'vimeo_keyboard'      => ( isset( $attributes['vimeoKeyboard'] ) && $attributes['vimeoKeyboard'] ) ? 'yes' : 'no',
			'vimeo_pip'           => ( isset( $attributes['vimeoPip'] ) && $attributes['vimeoPip'] ) ? 'yes' : 'no',
			'vimeo_speed'         => ( isset( $attributes['vimeoSpeed'] ) && $attributes['vimeoSpeed'] ) ? 'yes' : 'no',
			'vimeo_transparent'   => ( isset( $attributes['vimeoTransparent'] ) && $attributes['vimeoTransparent'] ) ? 'yes' : 'no',
			'fullscreen'          => ( isset( $attributes['fullscreen'] ) && $attributes['fullscreen'] ) ? 'yes' : 'no',
			'enable_device_frame' => ( isset( $attributes['enableDeviceFrame'] ) && $attributes['enableDeviceFrame'] ) ? 'yes' : 'no',
			'device_frame_type'   => isset( $attributes['deviceFrameType'] ) ? $attributes['deviceFrameType'] : 'desktop',
			'fullscreenPosition'  => isset( $attributes['fullscreenPosition'] ) ? $attributes['fullscreenPosition'] : 'top-right',
			'fullscreenPositionX' => isset( $attributes['fullscreenPositionX'] ) ? $attributes['fullscreenPositionX'] : '0',
			'fullscreenPositionXTablet' => isset( $attributes['fullscreenPositionXTablet'] ) ? $attributes['fullscreenPositionXTablet'] : '0',
			'fullscreenPositionXMobile' => isset( $attributes['fullscreenPositionXMobile'] ) ? $attributes['fullscreenPositionXMobile'] : '0',
			'fullscreenPositionY' => isset( $attributes['fullscreenPositionY'] ) ? $attributes['fullscreenPositionY'] : '0',
			'fullscreenPositionYTablet' => isset( $attributes['fullscreenPositionYTablet'] ) ? $attributes['fullscreenPositionYTablet'] : '0',
			'fullscreenPositionYMobile' => isset( $attributes['fullscreenPositionYMobile'] ) ? $attributes['fullscreenPositionYMobile'] : '0',
			'fullscreenIconColor' => isset( $attributes['fullscreenIconColor'] ) ? $attributes['fullscreenIconColor'] : '#fff',
			'fullscreenIconHoverColor' => isset( $attributes['fullscreenIconHoverColor'] ) ? $attributes['fullscreenIconHoverColor'] : '#fff',
			'fullscreenBgColor' => isset( $attributes['fullscreenBgColor'] ) ? $attributes['fullscreenBgColor'] : 'rgba(0, 0, 0, 0.6)',
			'fullscreenBgHoverColor' => isset( $attributes['fullscreenBgHoverColor'] ) ? $attributes['fullscreenBgHoverColor'] : 'rgba(0, 0, 0, 0.8)',
			'fullscreenClosePosition' => isset( $attributes['fullscreenClosePosition'] ) ? $attributes['fullscreenClosePosition'] : 'top-right',
			'fullscreenClosePositionX' => isset( $attributes['fullscreenClosePositionX'] ) ? $attributes['fullscreenClosePositionX'] : '0',
			'fullscreenClosePositionY' => isset( $attributes['fullscreenClosePositionY'] ) ? $attributes['fullscreenClosePositionY'] : '0',
			'fullscreenCloseIconColor' => isset( $attributes['fullscreenCloseIconColor'] ) ? $attributes['fullscreenCloseIconColor'] : '#fff',
			'fullscreenCloseIconHoverColor' => isset( $attributes['fullscreenCloseIconHoverColor'] ) ? $attributes['fullscreenCloseIconHoverColor'] : '#fff',
			'fullscreenCloseBgColor' => isset( $attributes['fullscreenCloseBgColor'] ) ? $attributes['fullscreenCloseBgColor'] : 'rgba(0, 0, 0, 0.6)',
			'fullscreenCloseBgHoverColor' => isset( $attributes['fullscreenCloseBgHoverColor'] ) ? $attributes['fullscreenCloseBgHoverColor'] : 'rgba(0, 0, 0, 0.8)',
			'is_gutenberg'      => true,
		);

		ob_start();
		$renderer->render( $settings, 'block-' . uniqid() );
		return ob_get_clean();
	}
}

new Gutenberg_Block();
