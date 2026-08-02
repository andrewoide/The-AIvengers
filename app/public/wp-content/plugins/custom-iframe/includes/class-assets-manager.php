<?php

namespace custif\includes;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Assets Manager Class
 *
 * Handles registration and enqueuing of all CSS and JavaScript assets
 * required by the Custom IFrame Widget for Elementor.
 *
 * @since 1.0.0
 */
class Assets_Manager {
	/**
	 * Class constructor.
	 *
	 * Sets up all the necessary hooks for enqueueing assets in both
	 * frontend and editor contexts.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_frontend_assets' ) );
		add_action( 'elementor/frontend/after_enqueue_styles', array( $this, 'enqueue_frontend_styles' ) );
		add_action( 'elementor/frontend/after_register_scripts', array( $this, 'enqueue_frontend_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'wp_ajax_dismiss_custom_iframe_notice', array( $this, 'dismiss_custom_iframe_notice' ) );
		add_action( 'wp_ajax_dismiss_custom_iframe_pro_notice', array( $this, 'dismiss_custom_iframe_pro_notice' ) );
		add_action( 'wp_ajax_custif_dismiss_rating_notice', array( $this, 'custif_dismiss_rating_notice' ) );
		add_action( 'wp_ajax_custif_remind_later_rating', array( $this, 'custif_remind_later_rating' ) );
	}

	/**
	 * Register frontend assets.
	 *
	 * Registers all CSS and JavaScript files needed for the frontend.
	 * This includes the main widget styles, and widget scripts.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function register_frontend_assets() {
		wp_register_style(
			'custif-styles',
			CUSTIF_URL . 'assets/css/style.css',
			array(),
			CUSTIF_VERSION
		);

		wp_register_script(
			'custif-scripts',
			CUSTIF_URL . 'assets/js/widget.js',
			array( 'jquery' ),
			CUSTIF_VERSION,
			true
		);
	}

	/**
	 * Enqueue frontend styles.
	 *
	 * Enqueues the main widget stylesheet for the frontend.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function enqueue_frontend_styles() {
		wp_enqueue_style( 'custif-styles' );
	}

	/**
	 * Enqueue frontend scripts.
	 *
	 * Enqueues all JavaScript files needed for the widget functionality
	 * including the main widget script.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function enqueue_frontend_scripts() {
		wp_enqueue_script( 'custif-scripts' );
	}

	/**
	 * Enqueue the JavaScript needed to handle notice dismissal.
	 *
	 * This script allows users to dismiss the plugin activation notice,
	 * and ensures it doesn't reappear once dismissed.
	 *
	 * @param string $hook return admin page slug.
	 *
	 * @return void
	 * @since 1.0.8
	 */
	public function enqueue_admin_assets( $hook ) {
		// Enqueue assets for plugin settings page.

		wp_enqueue_script(
			'custom-iframe-dismiss-notice',
			CUSTIF_URL . 'assets/js/dismiss-notice.js',
			array( 'jquery' ),
			CUSTIF_VERSION,
			true
		);

		wp_localize_script(
			'custom-iframe-dismiss-notice',
			'customIframeNotice',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'dismiss_notice_nonce' ),
			)
		);

		if ( 'toplevel_page_custom-iframe' === $hook ) {
			wp_enqueue_style(
				'custif-admin',
				CUSTIF_URL . 'assets/css/admin/admin.css',
				array(),
				CUSTIF_VERSION
			);
		}

		// Enqueue assets for deactivation feedback.
		if ( in_array( get_current_screen()->id, array( 'plugins', 'plugins-network' ), true ) ) {
			wp_enqueue_style(
				'custif-admin-feedback',
				CUSTIF_URL . 'assets/css/admin/deactivate-feedback.css',
				array(),
				CUSTIF_VERSION
			);

			wp_enqueue_script(
				'custif-admin-feedback',
				CUSTIF_URL . 'assets/js/admin/deactivate-feedback.js',
				array( 'jquery' ),
				CUSTIF_VERSION,
				true
			);

			wp_localize_script(
				'custif-admin-feedback',
				'custifFeedback',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'custif-deactivate-feedback' ),
				)
			);
		}
	}

	/**
	 * Handle AJAX request to dismiss the plugin activation notice.
	 *
	 * Sets a user meta key to remember the dismissed state, so the notice
	 * doesn't show up again for the current user.
	 *
	 * @return void Outputs JSON success or error.
	 * @since 1.0.8
	 */
	public function dismiss_custom_iframe_notice() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'dismiss_notice_nonce' ) ) {
			wp_send_json_error();
		}

		update_user_meta( get_current_user_id(), '_custom_iframe_notice_dismissed', 1 );
		wp_send_json_success();
	}

	/**
	 * Handle AJAX request to dismiss the plugin activation notice.
	 *
	 * Sets a user meta key to remember the dismissed state, so the notice
	 * doesn't show up again for the current user.
	 *
	 * @return void Outputs JSON success or error.
	 * @since 1.0.8
	 */
	public function dismiss_custom_iframe_pro_notice() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'dismiss_notice_nonce' ) ) {
			wp_send_json_error();
		}

		update_user_meta( get_current_user_id(), '_custom_iframe_pro_notice_dismissed', 1 );
		wp_send_json_success();
	}

	/**
	 * Handle rating notice dismissal.
	 *
	 * @since 1.0.10
	 */
	public function custif_dismiss_rating_notice() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'dismiss_notice_nonce' ) ) {
			wp_send_json_error();
		}
		update_user_meta( get_current_user_id(), '_custif_rating_notice_dismissed', 1 );
		wp_send_json_success();
	}

	/**
	 * Handle remind later action.
	 *
	 * @since 1.0.10
	 */
	public function custif_remind_later_rating() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'dismiss_notice_nonce' ) ) {
			wp_send_json_error();
		}

		update_user_meta( get_current_user_id(), '_custif_rating_remind_later', current_time( 'mysql' ) );
		wp_send_json_success();
	}
}

new Assets_Manager();
