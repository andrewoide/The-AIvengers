<?php

namespace custif\includes;

use custif\includes\embed_handlers\Embed_Converter;
use custif\includes\embed_handlers\PDF_Handler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renderer Class
 *
 * Handles the generation of iframe HTML for both Elementor and Gutenberg.
 *
 * @since 1.0.19
 */
class Renderer {

	/**
	 * Embed converter instance.
	 *
	 * @var Embed_Converter
	 */
	private $embed_converter;

	/**
	 * PDF handler instance.
	 *
	 * @var PDF_Handler
	 */
	private $pdf_handler;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->embed_converter = new Embed_Converter();
		$this->pdf_handler     = apply_filters( 'custif_pdf_handler', new PDF_Handler() );
	}

	/**
	 * Render the iframe output.
	 *
	 * @param array  $settings The settings array (normalized to match Elementor's structure).
	 * @param string $unique_id A unique ID for the wrapper.
	 *
	 * @return void Outputs the HTML directly.
	 */
	public function render( $settings, $unique_id ) {
		$iframe_id = ! empty( $settings['custif_custom_id'] ) ? esc_attr( $settings['custif_custom_id'] ) : 'custif-iframe-' . $unique_id;
		$source    = ! empty( $settings['source'] ) ? $settings['source'] : 'default';
		$pdf_type  = ! empty( $settings['pdf_type'] ) ? $settings['pdf_type'] : 'file';

		// Properly get URL from settings.
		$url_val = '';
		if ( is_array( $settings['iframe_url'] ) && isset( $settings['iframe_url']['url'] ) ) {
			$url_val = $settings['iframe_url']['url'];
		} elseif ( is_string( $settings['iframe_url'] ) ) {
			$url_val = $settings['iframe_url'];
		}

		$url = $this->embed_converter->convert_social_to_embed( $url_val, $settings );

		if ( 'Pdf' === $source && 'url' === $pdf_type ) {
			$pdf_link = '';
			if ( is_array( $settings['pdf_file_link'] ) && isset( $settings['pdf_file_link']['url'] ) ) {
				$pdf_link = $settings['pdf_file_link']['url'];
			} elseif ( is_string( $settings['pdf_file_link'] ) ) {
				$pdf_link = $settings['pdf_file_link'];
			}

			if ( ! empty( $pdf_link ) ) {
				$url = esc_url( $pdf_link );
			}
		}

		// Prepare placeholder image if needed.
		$placeholder_style = '';
		if ( ! empty( $settings['enable_lazy_load'] ) && 'yes' === $settings['enable_lazy_load'] ) {
			$ph_url = '';
			if ( is_array( $settings['placeholder_image'] ) && isset( $settings['placeholder_image']['url'] ) ) {
				$ph_url = $settings['placeholder_image']['url'];
			} elseif ( is_string( $settings['placeholder_image'] ) ) {
				$ph_url = $settings['placeholder_image'];
			}

			if ( ! empty( $ph_url ) ) {
				$placeholder_style = 'background-image: url(' . esc_url( $ph_url ) . '); background-size: cover; background-position: center;';
			}
		}

		$iframe_attributes = array(
			'style'                 => 'width: 100%; display: block;' . $placeholder_style,
			'scrolling'             => ( isset( $settings['show_scrollbars'] ) && 'yes' === $settings['show_scrollbars'] ) ? 'yes' : 'no',
			'data-auto-height'      => isset( $settings['auto_height'] ) ? $settings['auto_height'] : 'no',
			'data-refresh-interval' => isset( $settings['refresh_interval'] ) ? $settings['refresh_interval'] : 0,
			'loading'               => ( ! empty( $settings['enable_lazy_load'] ) && 'yes' === $settings['enable_lazy_load'] ) ? 'lazy' : '',
			'src'                   => esc_url( $url ),
		);

		// Dynamic CSS generation configuration (Only for Gutenberg).
		$dynamic_css = '';
		if ( ! empty( $settings['is_gutenberg'] ) ) {
			$styles_config = array();

			// Responsive heights.
			if ( empty( $settings['auto_height'] ) || 'yes' !== $settings['auto_height'] ) {
				$styles_config[] = array(
					'selector' => "#$iframe_id iframe",
					'rules'    => array(
						'height' => array(
							'desktop' => isset( $settings['height'] ) ? $settings['height'] : '500px',
							'tablet'  => isset( $settings['height_tablet'] ) ? $settings['height_tablet'] : $settings['height'],
							'mobile'  => isset( $settings['height_mobile'] ) ? $settings['height_mobile'] : $settings['height'],
							'unit'    => 'px',
						),
					),
				);
			}

			// Responsive widths.
			$styles_config[] = array(
				'selector' => "#$iframe_id",
				'rules'    => array(
					'width' => array(
						'desktop' => isset( $settings['iframe_width'] ) ? $settings['iframe_width'] : '500px',
						'tablet'  => isset( $settings['iframe_width_tablet'] ) ? $settings['iframe_width_tablet'] : $settings['iframe_width'],
						'mobile'  => isset( $settings['iframe_width_mobile'] ) ? $settings['iframe_width_mobile'] : $settings['iframe_width'],
						'unit'    => 'px',
					),
				),
			);

			// Responsive padding.
			$sides = array( 'top', 'right', 'bottom', 'left' );
			foreach ( $sides as $side ) {
				$prop = 'padding-' . $side;
				$styles_config[] = array(
					'selector' => "#$iframe_id iframe",
					'rules'    => array(
						$prop => array(
							'desktop' => isset( $settings['padding'][ $side ] ) ? $settings['padding'][ $side ] : '',
							'tablet'  => isset( $settings['padding_tablet'][ $side ] ) ? $settings['padding_tablet'][ $side ] : '',
							'mobile'  => isset( $settings['padding_mobile'][ $side ] ) ? $settings['padding_mobile'][ $side ] : '',
						),
					),
				);
			}

			// Alignment.
			$align = isset( $settings['align'] ) ? $settings['align'] : '';
			if ( ! empty( $align ) ) {
				$margin_rules = array();
				if ( 'left' === $align ) {
					$margin_rules['margin-left'] = '0';
					$margin_rules['margin-right'] = 'auto';
				} elseif ( 'center' === $align ) {
					$margin_rules['margin-left'] = 'auto';
					$margin_rules['margin-right'] = 'auto';
				} elseif ( 'right' === $align ) {
					$margin_rules['margin-left'] = 'auto';
					$margin_rules['margin-right'] = '0';
				}

				if ( ! empty( $margin_rules ) ) {
					$styles_config[] = array(
						'selector' => "#$iframe_id",
						'rules'    => array(
							'margin-left' => array(
								'desktop' => isset( $margin_rules['margin-left'] ) ? $margin_rules['margin-left'] : '',
							),
							'margin-right' => array(
								'desktop' => isset( $margin_rules['margin-right'] ) ? $margin_rules['margin-right'] : '',
							),
						),
					);
				}
			}

			// Background.
			$bg_val = '';
			if ( 'solid' === $settings['background_style'] ) {
				if ( ! empty( $settings['gradient_value'] ) ) {
					$bg_val = $settings['gradient_value'];
				} elseif ( ! empty( $settings['color_value'] ) ) {
					$bg_val = $settings['color_value'];
				}
			} elseif ( 'image' === $settings['background_style'] && ! empty( $settings['background_image_url'] ) ) {
				$bg_val = 'url(' . $settings['background_image_url'] . ')';
			}

			if ( ! empty( $bg_val ) ) {
				$bg_rules = array(
					'background' => array(
						'desktop' => $bg_val,
					),
				);

				if ( 'image' === $settings['background_style'] ) {
					$bg_rules['background-size'] = array( 'desktop' => 'cover' );
					$bg_rules['background-position'] = array( 'desktop' => 'center' );
					$bg_rules['background-repeat'] = array( 'desktop' => 'no-repeat' );
				}

				$styles_config[] = array(
					'selector' => "#$iframe_id",
					'rules'    => $bg_rules,
				);
			}

			// Border.
			if ( ! empty( $settings['border'] ) ) {
				$border       = $settings['border'];
				$border_rules = array();

				// Helper to process a border object for a side/all.
				$process_border = function ( $b_data, $side = '' ) use ( &$border_rules ) {
					$suffix = $side ? '-' . $side : '';
					if ( isset( $b_data['color'] ) ) {
						$border_rules[ 'border' . $suffix . '-color' ] = array( 'desktop' => $b_data['color'] );
					}
					if ( isset( $b_data['style'] ) ) {
						$border_rules[ 'border' . $suffix . '-style' ] = array( 'desktop' => $b_data['style'] );
					}
					if ( isset( $b_data['width'] ) ) {
						$border_rules[ 'border' . $suffix . '-width' ] = array( 'desktop' => $b_data['width'] );
					}
				};

				// Check for specific sides.
				$sides     = array( 'top', 'right', 'bottom', 'left' );
				$has_sides = false;
				foreach ( $sides as $side ) {
					if ( isset( $border[ $side ] ) ) {
						$process_border( $border[ $side ], $side );
						$has_sides = true;
					}
				}

				// If no specific sides, try flat border props.
				if ( ! $has_sides ) {
					$process_border( $border );
				}

				// Radius.
				if ( isset( $border['radius'] ) ) {
					if ( is_array( $border['radius'] ) ) {
						if ( isset( $border['radius']['topLeft'] ) ) {
							$border_rules['border-top-left-radius'] = array( 'desktop' => $border['radius']['topLeft'] );
						}
						if ( isset( $border['radius']['topRight'] ) ) {
							$border_rules['border-top-right-radius'] = array( 'desktop' => $border['radius']['topRight'] );
						}
						if ( isset( $border['radius']['bottomLeft'] ) ) {
							$border_rules['border-bottom-left-radius'] = array( 'desktop' => $border['radius']['bottomLeft'] );
						}
						if ( isset( $border['radius']['bottomRight'] ) ) {
							$border_rules['border-bottom-right-radius'] = array( 'desktop' => $border['radius']['bottomRight'] );
						}
					} else {
						$border_rules['border-radius'] = array( 'desktop' => $border['radius'] );
					}
				}

				if ( isset( $border['style'] ) ) {
					$border_rules['border-style'] = array( 'desktop' => $border['style'] );
				}

				if ( ! empty( $border_rules ) ) {
					$styles_config[] = array(
						'selector' => "#$iframe_id iframe",
						'rules'    => $border_rules,
					);
				}
			} else {
				$styles_config[] = array(
					'selector' => "#$iframe_id iframe",
					'rules'    => array(
						'border' => array(
							'desktop' => 'none',
						),
					),
				);
			}

			// Box Shadow.
			if ( ! empty( $settings['shadow'] ) ) {
				$shadow = $settings['shadow'];
				if ( is_array( $shadow ) ) {
					$h_offset = isset( $shadow['hOffset'] ) ? $shadow['hOffset'] : '0px';
					$v_offset = isset( $shadow['vOffset'] ) ? $shadow['vOffset'] : '0px';
					$blur     = isset( $shadow['blur'] ) ? $shadow['blur'] : '0px';
					$spread   = isset( $shadow['spread'] ) ? $shadow['spread'] : '0px';
					$color    = isset( $shadow['color'] ) ? $shadow['color'] : '#000000';
					$inset    = ( isset( $shadow['inset'] ) && $shadow['inset'] ) ? 'inset' : '';

					$box_shadow = trim( "$inset $h_offset $v_offset $blur $spread $color" );

					$styles_config[] = array(
						'selector' => "#$iframe_id iframe",
						'rules'    => array(
							'box-shadow' => array(
								'desktop' => $box_shadow,
							),
						),
					);
				}
			}

			$dynamic_css = $this->generate_responsive_css( $styles_config );
		}

		$iframe_attributes = apply_filters( 'custif_iframe_attributes', $iframe_attributes, $settings );
		$iframe_html       = '<iframe';

		foreach ( $iframe_attributes as $attr => $value ) {
			if ( '' !== $value && null !== $value ) {
				$iframe_html .= ' ' . esc_attr( $attr ) . '="' . esc_attr( $value ) . '"';
			}
		}

		$iframe_html .= '></iframe>';

		ob_start();

		if ( ! empty( $source ) && 'Pdf' !== $source ) :
			if ( ! empty( $url ) ) :
				if ( filter_var( $url, FILTER_VALIDATE_URL ) ) {
					echo $iframe_html; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				} else {
					echo $url; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
			else :
				?>
				<div class="custif-iframe-notice">
					<div class="notice-icon">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
							<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
								  stroke-width="2"
								  d="M12 8v4m0 4h.01M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12 6.477 2 12 2s10 4.477 10 10z"/>
						</svg>
					</div>
					<p><?php esc_html_e( 'Please enter a valid URL', 'custom-iframe' ); ?></p>
				</div>
				<?php
			endif;
		elseif ( ! empty( $source ) && 'Pdf' === $source ) :
			$this->pdf_handler->embed_pdf( $settings );
		endif;

		$inner_content = ob_get_clean();

		// Filter for inner content (e.g. to add watermark).
		$inner_content = apply_filters( 'custif_inner_content', $inner_content, $settings, $iframe_id );

		if ( ! empty( $dynamic_css ) ) :
			?>
			<style>
				<?php echo $dynamic_css; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</style>
			<?php
		endif;

		$output = '<div class="custif-iframe-wrapper" id="' . esc_attr( $iframe_id ) . '">';
		$output .= $inner_content;
		$output .= '</div>';

		// Filter for the whole wrapper (e.g. to add device frame).
		echo apply_filters( 'custif_wrapper_output', $output, $inner_content, $settings, $iframe_id );
	}

	/**
	 * Generate responsive CSS based on configuration.
	 *
	 * @param array $config Configuration array of selectors and rules.
	 *
	 * @return string Generated CSS.
	 */
	private function generate_responsive_css( $config ) {
		$desktop_css = '';
		$tablet_css  = '';
		$mobile_css  = '';

		foreach ( $config as $entry ) {
			$selector = $entry['selector'];
			$rules    = $entry['rules'];

			$d_rules = '';
			$t_rules = '';
			$m_rules = '';

			foreach ( $rules as $property => $values ) {
				$unit = isset( $values['unit'] ) ? $values['unit'] : '';

				if ( isset( $values['desktop'] ) && ( ! empty( $values['desktop'] ) || '0' === (string) $values['desktop'] ) ) {
					$val      = is_numeric( $values['desktop'] ) ? $values['desktop'] . $unit : $values['desktop'];
					$d_rules .= "$property: $val;";
				}

				if ( isset( $values['tablet'] ) && ( ! empty( $values['tablet'] ) || '0' === (string) $values['tablet'] ) ) {
					$val      = is_numeric( $values['tablet'] ) ? $values['tablet'] . $unit : $values['tablet'];
					$t_rules .= "$property: $val;";
				}

				if ( isset( $values['mobile'] ) && ( ! empty( $values['mobile'] ) || '0' === (string) $values['mobile'] ) ) {
					$val      = is_numeric( $values['mobile'] ) ? $values['mobile'] . $unit : $values['mobile'];
					$m_rules .= "$property: $val;";
				}
			}

			if ( ! empty( $d_rules ) ) {
				$desktop_css .= "$selector { $d_rules }";
			}
			if ( ! empty( $t_rules ) ) {
				$tablet_css .= "$selector { $t_rules }";
			}
			if ( ! empty( $m_rules ) ) {
				$mobile_css .= "$selector { $m_rules }";
			}
		}

		$css = $desktop_css;
		if ( ! empty( $tablet_css ) ) {
			$css .= "@media (max-width: 1024px) { $tablet_css }";
		}
		if ( ! empty( $mobile_css ) ) {
			$css .= "@media (max-width: 767px) { $mobile_css }";
		}

		return $css;
	}
}

