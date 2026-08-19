<?php
/**
 * Main Plugin Class
 *
 * Handles file uploads, shortcode rendering, and WordPress integration
 * for the WP Dropzone plugin.
 *
 * @package WP_Dropzone
 * @since   1.0.0
 */

namespace WP_Dropzone;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

use WP_Filesystem_Direct;

/**
 * Main Plugin Class
 *
 * Coordinates plugin functionality including file uploads, asset loading,
 * and shortcode rendering.
 *
 * @since 1.0.0
 */
class Plugin {
	/**
	 * Initialize plugin hooks and actions
	 *
	 * Sets up WordPress hooks for script enqueuing, AJAX handling,
	 * and shortcode registration.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct() {
		// Load text domain for translations.
		add_action( 'plugins_loaded', [ $this, 'load_textdomain' ] );

		// Initialize plugin actions.
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'wp_ajax_wp_dropzone_upload_media', [ $this, 'ajax_upload_handle' ] );
		add_shortcode( 'wp-dropzone', [ $this, 'add_shortcode' ] );
	}

	/**
	 * Load plugin text domain for translations
	 *
	 * Loads the plugin's translation files for internationalization.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'wp-dropzone', false, dirname( WP_DROPZONE_BASENAME ) . '/languages/' );
	}

	/**
	 * Enqueue scripts and styles for pages with shortcode
	 *
	 * Conditionally loads Dropzone CSS and JavaScript files only on pages
	 * that contain the wp-dropzone shortcode for optimal performance.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_scripts() {
		global $post;

		if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'wp-dropzone' ) ) {
			wp_enqueue_style( 'dropzone', WP_DROPZONE_URL . 'css/dropzone.min.css', [], WP_DROPZONE_VERSION );
			wp_enqueue_script( 'dropzone', WP_DROPZONE_URL . 'js/dropzone.min.js', [], WP_DROPZONE_VERSION, true );
			wp_enqueue_script( 'wp-dropzone', WP_DROPZONE_URL . 'js/wp-dropzone.js', [ 'dropzone' ], WP_DROPZONE_VERSION, true );
		}
	}

	/**
	 * Handle AJAX file upload to WordPress media library
	 *
	 * Processes file uploads with security verification, nonce validation, and media library integration.
	 * Includes performance optimizations for better upload handling.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function ajax_upload_handle() {
		// Verify nonce for security.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'wp_dropzone_nonce' ) ) {
			wp_send_json_error( __( 'Security check failed.', 'wp-dropzone' ), 403 );
			return;
		}

		// Verify user has permission to upload files.
		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( __( 'You do not have permission to upload files.', 'wp-dropzone' ), 403 );
			return;
		}

		if ( ! isset( $_FILES['file'] ) || empty( $_FILES['file'] ) ) {
			wp_send_json_error( __( 'no file to upload.', 'wp-dropzone' ), 400 );
			return;
		}

		// phpcs:ignore
		$file = $_FILES['file'];

		// Initialize variables for cleanup.
		$tmp_file      = null;
		$wp_filesystem = null;

		// Handle chunked uploads.
		if ( isset( $_POST['dzuuid'] ) && isset( $_POST['dzchunkindex'] ) && isset( $_POST['dztotalchunkcount'] ) ) {
			$uid          = trim( sanitize_text_field( wp_unslash( $_POST['dzuuid'] ) ) );
			$total_chunks = intval( $_POST['dztotalchunkcount'] );
			$chunk_index  = intval( $_POST['dzchunkindex'] ) + 1;
			$uploads      = wp_upload_dir();

			// Validate file extension before processing chunks (security fix).
			$file_extension = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );

			// Get allowed file types from WordPress.
			$allowed_types      = get_allowed_mime_types();
			$allowed_extensions = [];
			foreach ( $allowed_types as $ext => $mime ) {
				$exts               = explode( '|', $ext );
				$allowed_extensions = array_merge( $allowed_extensions, $exts );
			}

			// Block dangerous file extensions.
			$dangerous_extensions = [ 'php', 'php3', 'php4', 'php5', 'phtml', 'phps', 'pht', 'phar', 'shtml', 'htaccess', 'htpasswd', 'sh', 'bash', 'py', 'pl', 'rb', 'js', 'jsp', 'asp', 'aspx', 'exe', 'dll', 'bat', 'cmd', 'com', 'scr', 'vbs', 'wsf' ];

			if ( in_array( $file_extension, $dangerous_extensions, true ) || ! in_array( $file_extension, $allowed_extensions, true ) ) {
				wp_send_json_error( __( 'File type not allowed.', 'wp-dropzone' ), 400 );
				return;
			}

			if ( ! class_exists( 'WP_Filesystem_Direct' ) ) {
				require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
				require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
			}

			if ( ! defined( 'FS_CHMOD_FILE' ) ) {
				define( 'FS_CHMOD_FILE', ( fileperms( ABSPATH . 'index.php' ) & 0777 | 0644 ) );
			}

			$wp_filesystem = new WP_Filesystem_Direct( null );

			// Use a safer temporary location with sanitized filename (security fix).
			$safe_filename = sanitize_file_name( $file['name'] );
			$tmp_file      = get_temp_dir() . 'wp-dropzone-' . $uid . '-' . $safe_filename;

			// Combine file chunks.
			$existing_content = '';
			if ( $wp_filesystem->exists( $tmp_file ) ) {
				$existing_content = $wp_filesystem->get_contents( $tmp_file );
			}

			$chunk_content = $wp_filesystem->get_contents( $file['tmp_name'] );
			$contents      = $existing_content . $chunk_content;

			$wp_filesystem->put_contents( $tmp_file, $contents, false );

			if ( $total_chunks !== $chunk_index ) {
				wp_send_json_success( [ 'chunk_uploaded' => true ] );
				return;
			}

			// Final chunk - validate before moving to uploads directory.
			$file['tmp_name'] = $tmp_file;
			$file['type']     = isset( $_POST['origtype'] ) ? sanitize_text_field( wp_unslash( $_POST['origtype'] ) ) : '';
			$file['size']     = $wp_filesystem->size( $tmp_file );
		}

		// Include WordPress file handling library.
		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		// Fire hook before upload.
		do_action( 'wp_dropzone_before_upload_file', $file );

		// Upload file to server.
		$movefile = wp_handle_upload( $file, [ 'test_form' => false ] );

		// Handle successful upload.
		if ( $movefile && ! isset( $movefile['error'] ) ) {
			// Clean up temporary chunk file if it exists (security fix).
			if ( isset( $tmp_file ) && ! empty( $tmp_file ) && file_exists( $tmp_file ) ) {
				if ( $wp_filesystem && is_a( $wp_filesystem, 'WP_Filesystem_Direct' ) ) {
					$wp_filesystem->delete( $tmp_file );
				} else {
					wp_delete_file( $tmp_file );
				}
			}

			// Fire hook after upload.
			do_action( 'wp_dropzone_after_upload_file', $file );

			$filename      = $movefile['file'];
			$filetype      = wp_check_filetype( basename( $filename ), null );
			$wp_upload_dir = wp_upload_dir();

			$attachment = [
				'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ),
				'post_mime_type' => $filetype['type'],
				'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
				'post_content'   => '',
				'post_status'    => 'inherit',
			];

			// Add file to media library.
			$attachment_id = wp_insert_attachment( $attachment, $filename );

			// Fire hook after media insertion.
			do_action( 'wp_dropzone_after_insert_attachment', $attachment_id );

			// Generate attachment metadata.
			if ( $attachment_id ) {
				require_once ABSPATH . 'wp-admin/includes/image.php';

				$attachment_data = wp_generate_attachment_metadata( $attachment_id, $filename );
				wp_update_attachment_metadata( $attachment_id, $attachment_data );
			}

			$message = [
				'error' => false,
				'data'  => wp_get_attachment_url( $attachment_id ),
			];
		} else {
			// Clean up temporary chunk file on error (security fix).
			if ( isset( $tmp_file ) && ! empty( $tmp_file ) && file_exists( $tmp_file ) ) {
				if ( $wp_filesystem && is_a( $wp_filesystem, 'WP_Filesystem_Direct' ) ) {
					$wp_filesystem->delete( $tmp_file );
				} else {
					wp_delete_file( $tmp_file );
				}
			}
			wp_send_json_error( $movefile['error'], 400 );
			return;
		}

		wp_send_json( $message );
	}

	/**
	 * Render wp-dropzone shortcode with customizable options
	 *
	 * Generates HTML for dropzone upload areas with configurable styling,
	 * file restrictions, and upload behavior. Configuration is passed via
	 * data-config attribute for JavaScript initialization.
	 *
	 * @since 1.0.0
	 * @param array $atts Shortcode attributes with default values.
	 * @return string HTML output for dropzone with inline styles
	 */
	public function add_shortcode( $atts ) {
		$atts = shortcode_atts(
			[
				'id'                 => bin2hex( random_bytes( 2 ) ),
				'callback'           => '',
				'title'              => '',
				'desc'               => '',
				'border-width'       => '',
				'border-style'       => '',
				'border-color'       => '',
				'background'         => '',
				'margin-bottom'      => '',
				'max-file-size'      => wp_max_upload_size(),
				'remove-links'       => 'false',
				'clickable'          => 'true',
				'accepted-files'     => null,
				'max-files'          => null,
				'max-files-alert'    => __( 'Max file limit exceeded.', 'wp-dropzone' ),
				'auto-process'       => 'true',
				'upload-button-text' => __( 'Upload', 'wp-dropzone' ),
				'dom-id'             => '',
				'resize-width'       => null,
				'resize-height'      => null,
				'resize-quality'     => 0.8,
				'resize-method'      => 'contain',
				'thumbnail-width'    => 120,
				'thumbnail-height'   => 120,
				'thumbnail-method'   => 'crop',
			],
			$atts
		);

		if ( ! is_user_logged_in() ) {
			$atts['desc'] = __( 'Please login to upload files.', 'wp-dropzone' );
		} elseif ( ! current_user_can( 'upload_files' ) ) {
			$atts['desc'] = __( 'You do not have permission to upload files.', 'wp-dropzone' );
		}

		// Sanitize the id for safe use both as an HTML id/class and inside the
		// inline <style> block below (falls back to a random id if it sanitizes
		// away to nothing, e.g. when every character was invalid).
		$atts['id'] = sanitize_html_class( $atts['id'], 'wpdz-' . bin2hex( random_bytes( 2 ) ) );

		// The 'callback' attribute is executed as JavaScript on the front end, so
		// it is only honored when the shortcode's containing post was authored by
		// a user WordPress already trusts with unfiltered_html -- the same trust
		// boundary core uses to decide whether a user's post content may contain
		// raw <script> tags. This prevents a Contributor/Author from storing a
		// malicious callback that executes in an Administrator's browser when the
		// post is previewed or published (CVE-2025-13989). A pattern blocklist
		// alone cannot fix this: the entire function body is attacker-controlled,
		// so any string that avoids a handful of forbidden substrings (e.g. the
		// disclosed PoC, which uses none of them) still runs as arbitrary JS.
		$sanitized_callback = $this->current_post_author_can_use_callback()
			? $this->sanitize_callback( $atts['callback'] )
			: '';

		$configs = [
			'ajax_url'          => esc_url( admin_url( 'admin-ajax.php' ) ),
			'nonce'             => wp_create_nonce( 'wp_dropzone_nonce' ),
			'is_user_logged_in' => is_user_logged_in(),
			'can_upload_files'  => current_user_can( 'upload_files' ),
			'id'                => $atts['id'],
			'callback'          => $sanitized_callback,
			'title'             => $atts['title'],
			'desc'              => $atts['desc'],
			'max_file_size'     => $atts['max-file-size'],
			'remove_links'      => $atts['remove-links'],
			'clickable'         => $atts['clickable'],
			'accepted_files'    => $atts['accepted-files'],
			'max_files'         => $atts['max-files'],
			'max_files_alert'   => $atts['max-files-alert'],
			'auto_process'      => $atts['auto-process'],
			'dom_id'            => $atts['dom-id'],
			'resize_width'      => $atts['resize-width'],
			'resize_height'     => $atts['resize-height'],
			'resize_quality'    => $atts['resize-quality'],
			'resize_method'     => $atts['resize-method'],
			'thumbnail_width'   => $atts['thumbnail-width'],
			'thumbnail_height'  => $atts['thumbnail-height'],
			'thumbnail_method'  => $atts['thumbnail-method'],
		];

		$html = '<div class="dropzone dropzone-' . esc_attr( $atts['id'] ) . '" id="wp-dz-' . esc_attr( $atts['id'] ) . '" data-config="' . esc_attr( wp_json_encode( $configs ) ) . '">';
		if ( $atts['title'] || $atts['desc'] ) {
			$html .= '<div class="dz-message">
				<h3 class="dropzone-title">' . esc_html( $atts['title'] ) . '</h3>
				<p class="dropzone-note">' . esc_html( $atts['desc'] ) . '</p>
				<div class="dropzone-mobile-trigger needsclick"></div>
			</div>';
		}
		$html .= '</div>';

		// Generate inline CSS for styling.
		// Shortcode attributes are attacker-controlled (any Contributor+ can set
		// them), so each value is stripped down to characters that are valid in
		// CSS lengths/colors/keywords before being placed inside the <style>
		// block -- otherwise a value like `1px} </style><script>...` could break
		// out of the style tag and inject arbitrary markup/script.
		$css = '.dropzone-' . $atts['id'] . ' {';
		if ( ! empty( $atts['border-width'] ) ) {
			$css .= 'border-width: ' . $this->sanitize_css_value( $atts['border-width'] ) . ';';
		}
		if ( ! empty( $atts['border-style'] ) ) {
			$css .= 'border-style: ' . $this->sanitize_css_value( $atts['border-style'] ) . ';';
		}
		if ( ! empty( $atts['border-color'] ) ) {
			$css .= 'border-color: ' . $this->sanitize_css_value( $atts['border-color'] ) . ';';
		}
		if ( ! empty( $atts['background'] ) ) {
			$css .= 'background: ' . $this->sanitize_css_value( $atts['background'] ) . ';';
		}
		if ( ! empty( $atts['margin-bottom'] ) ) {
			$css .= 'margin-bottom: ' . $this->sanitize_css_value( $atts['margin-bottom'] ) . ';';
		}
		$css .= '}';

		$thumbnail_width  = absint( $atts['thumbnail-width'] );
		$thumbnail_height = absint( $atts['thumbnail-height'] );

		if ( $thumbnail_width && $thumbnail_height ) {
			$css .= '.dropzone-' . $atts['id'] . ' .dz-preview .dz-image {
				width:100%;
				max-width: ' . $thumbnail_width . 'px;
				height:auto;
				max-height: ' . $thumbnail_width . 'px;
			}';
		}

		$html .= '<style>' . $this->minify_css( $css ) . '</style>';

		if ( 'false' === $atts['auto-process'] ) {
			$html .= '<button type="button" class="process-upload" id="process-' . esc_attr( $atts['id'] ) . '">' . esc_html( $atts['upload-button-text'] ) . '</button>';
		}

		return $html;
	}

	/**
	 * Determine whether the callback attribute should be honored
	 *
	 * The 'callback' attribute is passed to JavaScript and executed as a Dropzone
	 * event handler, so it must only be trusted when it originates from a post
	 * authored by a user WordPress already allows to publish unfiltered
	 * HTML/JavaScript. See the caller in add_shortcode() for the full rationale.
	 *
	 * @since 1.1.2
	 * @return bool True if the current post's author has the unfiltered_html capability.
	 */
	protected function current_post_author_can_use_callback() {
		$post = get_post();

		if ( ! $post instanceof \WP_Post ) {
			return false;
		}

		return user_can( $post->post_author, 'unfiltered_html' );
	}

	/**
	 * Perform basic cleanup of the callback attribute
	 *
	 * Only reached once current_post_author_can_use_callback() has confirmed the
	 * containing post's author is trusted with unfiltered_html; this simply
	 * strips null bytes and surrounding whitespace. It is not, by itself, an XSS
	 * defense -- the capability check is what prevents untrusted input from
	 * reaching this method.
	 *
	 * @since 1.1.2
	 * @param string $callback Raw callback string from shortcode attribute.
	 * @return string Cleaned-up callback string or empty string if invalid.
	 */
	protected function sanitize_callback( $callback ) {
		if ( empty( $callback ) || ! is_string( $callback ) ) {
			return '';
		}

		// Remove any null bytes and trim.
		return str_replace( "\0", '', trim( $callback ) );
	}

	/**
	 * Sanitize a shortcode attribute value for use inside an inline <style> block
	 *
	 * Strips any character that isn't valid in common CSS values (lengths, hex
	 * colors, keywords, rgb()/rgba() functions) so that shortcode attributes
	 * cannot be used to break out of the <style> tag and inject markup/script.
	 *
	 * @since 1.1.2
	 * @param string $value Raw CSS value from a shortcode attribute.
	 * @return string Sanitized CSS value.
	 */
	protected function sanitize_css_value( $value ) {
		if ( ! is_string( $value ) ) {
			return '';
		}

		return preg_replace( '/[^a-zA-Z0-9.,%#()\-\s]/', '', $value );
	}

	/**
	 * Minify CSS for inline styles
	 *
	 * Removes unnecessary whitespace, and optimizes CSS
	 * for better performance when outputting inline styles.
	 *
	 * @since 1.1.0
	 * @param string $css Raw CSS to minify.
	 * @return string Minified CSS.
	 */
	protected function minify_css( $css ) {
		// Remove whitespace around symbols.
		$css = preg_replace( '/\s*([{}|:;,])\s*/', '$1', $css );

		// Remove trailing semicolons inside blocks.
		$css = preg_replace( '/;}/', '}', $css );

		// Remove extra whitespace, newlines, tabs.
		$css = preg_replace( '/\s\s+/', ' ', $css );

		return trim( $css );
	}
}
