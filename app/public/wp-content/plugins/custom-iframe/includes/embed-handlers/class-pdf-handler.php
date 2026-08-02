<?php

namespace custif\includes\embed_handlers;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PDF Handler Class
 *
 * Handles the embedding and rendering of PDF files with customizable controls.
 *
 * @since 1.0.0
 */
class PDF_Handler {

	/**
	 * Embed self hosted PDFs.
	 *
	 * Renders a PDF viewer with customizable controls for user interaction.
	 *
	 * @param  array $settings  The settings for the PDF viewer.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function embed_pdf( $settings = array() ) {
		$pdf_type = $settings['pdf_type'];
		$pdf_url  = '';

		if ( 'file' === $pdf_type && ! empty( $settings['pdf_Uploader']['url'] ) ) {
			$pdf_url = esc_url( $settings['pdf_Uploader']['url'] );
		} elseif ( 'url' === $pdf_type && ! empty( $settings['pdf_file_link']['url'] ) ) {
			$pdf_url = esc_url( $settings['pdf_file_link']['url'] );
		}

		if ( ! empty( $pdf_url ) ) {
			// Retrieve Elementor settings for PDF controls.
			$zoom = $settings['pdf_zoom'];
			if ( 'custom' === $zoom && ! empty( $settings['pdf_zoom_custom'] ) ) {
				$zoom = $settings['pdf_zoom_custom'];
			}

			// Convert toolbar settings to parameters.
			$toolbar        = isset( $settings['pdf_toolbar'] ) ? ( 'yes' === $settings['pdf_toolbar'] ) : true;
			$print_download = isset( $settings['pdf_print_download'] ) ? ( 'yes' === $settings['pdf_print_download'] ) : true;
			$presentation   = isset( $settings['pdf_presentation_mode'] ) ? ( 'yes' === $settings['pdf_presentation_mode'] ) : true;
			$text_copy      = isset( $settings['pdf_text_copy'] ) ? ( 'yes' === $settings['pdf_text_copy'] ) : true;
			$add_text       = isset( $settings['pdf_add_text'] ) ? ( 'yes' === $settings['pdf_add_text'] ) : true;
			$draw           = isset( $settings['pdf_draw'] ) ? ( 'yes' === $settings['pdf_draw'] ) : true;
			$rotation       = isset( $settings['pdf_rotate_access'] ) ? ( 'yes' === $settings['pdf_rotate_access'] ) : true;
			$image          = isset( $settings['pdf_add_image'] ) ? ( 'yes' === $settings['pdf_add_image'] ) : true;
			$properties     = isset( $settings['pdf_details'] ) ? ( 'yes' === $settings['pdf_details'] ) : true;
			$lazy_load      = isset( $settings['pdf_lazyload'] ) ? ( 'yes' === $settings['pdf_lazyload'] ) : false;
			$theme_mode     = isset( $settings['pdf_theme_mode'] ) ? $settings['pdf_theme_mode'] : 'default';
			$custom_color   = isset( $settings['pdf_custom_color'] ) ? $settings['pdf_custom_color'] : '#38383d';
			$position       = isset( $settings['pdf_toolbar_position'] ) ? $settings['pdf_toolbar_position'] : 'top';
			$selection_tool = isset( $settings['selection_tool'] ) ? $settings['selection_tool'] : '0';
			$scrolling      = isset( $settings['scrolling'] ) ? $settings['scrolling'] : '0';
			$spreads        = isset( $settings['spreads'] ) ? $settings['spreads'] : '-1';
			$zoom           = isset( $settings['pdf_zoom'] ) ? $settings['pdf_zoom'] : 'auto';

			if ( 'custom' === $zoom ) {
				$zoom = isset( $settings['pdf_zoom_custom'] ) ? $settings['pdf_zoom_custom'] : '';
			}

			// Build parameter object for the key parameter.
			$param_obj = array(
				'themeMode'      => $theme_mode,
				'toolbar'        => $toolbar ? 'true' : 'false',
				'position'       => $position,
				'presentation'   => $presentation ? 'true' : 'false',
				'lazyLoad'       => $lazy_load ? 'true' : 'false',
				'download'       => $print_download ? 'true' : 'false',
				'copy_text'      => $text_copy ? 'true' : 'false',
				'add_text'       => $add_text ? 'true' : 'false',
				'draw'           => $draw ? 'true' : 'false',
				'pdf_rotation'   => $rotation ? 'true' : 'false',
				'pdf_image'      => $image ? 'true' : 'false',
				'pdf_details'    => $properties ? 'true' : 'false',
				'selection_tool' => $selection_tool,
				'scrolling'      => $scrolling,
				'spreads'        => $spreads,
				'pdf_zoom'       => $zoom,
			);

			if ( 'custom' === $theme_mode && ! empty( $custom_color ) ) {
				$param_obj['customColor'] = $custom_color;
			}

			// Encode the parameters for the key.
			$encoded_params = base64_encode( http_build_query( $param_obj ) );

			// Construct the viewer URL with query parameters.
			$pdf_viewer_url = CUSTIF_URL . 'assets/pdfjs/web/viewer.html' .
							  '?file=' . urlencode( $pdf_url ) .
							  '&key=' . urlencode( $encoded_params );

			// Get responsive height if set.
			$iframe_height = ! empty( $settings['iframe_height']['size'] ) ? $settings['iframe_height']['size'] . $settings['iframe_height']['unit'] : '600px';

			// Set auto height if enabled.
			$auto_height_attr = isset( $settings['auto_height'] ) && 'yes' === $settings['auto_height'] ? 'data-auto-height="yes"' : '';

			?>
			<iframe
					src="<?php echo esc_url( $pdf_viewer_url ); ?>"
					width="100%"
					height="<?php echo esc_attr( $iframe_height ); ?>"
				<?php echo esc_attr( $auto_height_attr ); ?>
					loading="<?php echo isset( $settings['enable_lazy_load'] ) && 'yes' === $settings['enable_lazy_load'] ? 'lazy' : ''; ?>"
			></iframe>
			<?php
		} else {
			?>
			<div class="custif-iframe-notice">
				<div class="notice-icon">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
						<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
							  stroke-width="2"
							  d="M12 8v4m0 4h.01M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12 6.477 2 12 2s10 4.477 10 10z"/>
					</svg>
				</div>
				<p><?php esc_html_e( 'No PDF file selected.', 'custom-iframe' ); ?></p>
			</div>
			<?php
		}
	}
}