<?php
/**
 * Deactivation Feedback Handler
 *
 * @link       https://coderzstudio.com/
 * @since      1.0.9
 *
 * @package    Custom_Iframe
 * @subpackage Custom_Iframe/includes
 */

namespace Custom_Iframe\Includes;

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Deactivate_Feedback' ) ) {

	/**
	 * Handles plugin deactivation feedback
	 *
	 * @since 1.0.9
	 */
	class Deactivate_Feedback {

		/**
		 * Singleton Instance of the Class.
		 *
		 * @since 1.0.9
		 * @access private
		 * @static
		 * @var null|instance $instance An instance of the class or null if not instantiated yet.
		 */
		private static $instance = null;

		/**
		 * API endpoint for deactivation feedback
		 *
		 * @since 1.0.9
		 * @access private
		 * @var string
		 */
		private $count_api = 'https://store.coderzstudio.com/wp-json/custif/v2/custif_count_api';

		/**
		 * Singleton Instance Creation Method.
		 *
		 * @since 1.0.9
		 * @access public
		 * @static
		 * @return self Instance of the class.
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor Method
		 *
		 * @since 1.0.9
		 * @access public
		 */
		public function __construct() {
			$this->init_deactivate_feedback();
			add_action( 'wp_ajax_custif_deactivate_feedback', array( $this, 'handle_deactivate_feedback' ) );
			add_action( 'wp_ajax_custif_skip_feedback', array( $this, 'handle_skip_feedback' ) );
		}

		/**
		 * Check if the Current Screen is Related to Plugin Management.
		 *
		 * @since 1.0.9
		 * @access private
		 * @return bool True if the current screen is for managing plugins, otherwise false.
		 */
		private function is_plugins_screen() {
			return in_array( get_current_screen()->id, array( 'plugins', 'plugins-network' ), true );
		}

		/**
		 * Initialize Hooks for Deactivation Feedback
		 *
		 * @since 1.0.9
		 * @access public
		 */
		public function init_deactivate_feedback() {
			add_action(
				'current_screen',
				function () {
					if ( ! $this->is_plugins_screen() ) {
						return;
					}
					add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_feedback_dialog' ) );
				}
			);
		}

		/**
		 * Enqueue feedback dialog scripts
		 *
		 * @since 1.0.9
		 * @access public
		 */
		public function enqueue_feedback_dialog() {
			add_action( 'admin_footer', array( $this, 'display_deactivation_feedback_dialog' ) );
			wp_register_script( 'custif-admin-feedback', CUSTIF_URL . 'assets/js/admin/deactivate-feedback.js', array(), CUSTIF_VERSION, true );
			wp_enqueue_script( 'custif-admin-feedback' );
		}

		/**
		 * Display Deactivation Feedback Dialog
		 *
		 * @since 1.0.9
		 * @access public
		 */
		public function display_deactivation_feedback_dialog() {
			$deactivate_reasons = array(
				'temporary_deactivation' => array(
					'title'             => esc_html__( 'This is a temporary deactivation', 'custom-iframe' ),
					'input_placeholder' => '',
				),
				'no_longer_needed'      => array(
					'title'             => esc_html__( 'No longer needed', 'custom-iframe' ),
					'input_placeholder' => '',
				),
				'found_better_plugin'   => array(
					'title'             => esc_html__( 'Found a better plugin', 'custom-iframe' ),
					'input_placeholder' => esc_html__( 'Please share which plugin', 'custom-iframe' ),
				),
				'technical_issues'      => array(
					'title'             => esc_html__( 'Technical issues', 'custom-iframe' ),
					'input_placeholder' => esc_html__( 'Please describe the issue', 'custom-iframe' ),
				),
				'other'                 => array(
					'title'             => esc_html__( 'Other', 'custom-iframe' ),
					'input_placeholder' => esc_html__( 'Please share the reason', 'custom-iframe' ),
				),
			);

			$security = wp_create_nonce( 'custif-deactivate-feedback' );
			?>
			<div id="custif-feedback-dialog-wrapper">
				<div id="custif-feedback-dialog-header">
					<span id="custif-feedback-dialog-header-title">
						<?php echo esc_html__( 'Quick Feedback', 'custom-iframe' ); ?>
					</span>
				</div>
				<form id="custif-feedback-dialog-form" method="post">
					<input type="hidden" name="nonce" value="<?php echo esc_attr( $security ); ?>" />

					<div id="custif-feedback-dialog-form-caption">
						<?php echo esc_html__( "If you have a moment, please let us know why you're deactivating Custom Iframe:", 'custom-iframe' ); ?>
					</div>

					<div id="custif-feedback-dialog-form-body">
						<?php foreach ( $deactivate_reasons as $reason_key => $reason ) : ?>
							<div class="custif-feedback-dialog-input-wrapper">
								<input id="custif-deactivate-feedback-<?php echo esc_attr( $reason_key ); ?>" 
									   class="custif-deactivate-feedback-dialog-input" 
									   type="radio" 
									   name="reason_key" 
									   value="<?php echo esc_attr( $reason_key ); ?>" />

								<label for="custif-deactivate-feedback-<?php echo esc_attr( $reason_key ); ?>" 
									   class="custif-deactivate-feedback-dialog-label">
									<?php echo esc_html( $reason['title'] ); ?>
								</label>

								<?php if ( ! empty( $reason['input_placeholder'] ) ) : ?>
									<input class="custif-feedback-text" 
										   type="text" 
										   name="reason_<?php echo esc_attr( $reason_key ); ?>" 
										   placeholder="<?php echo esc_attr( $reason['input_placeholder'] ); ?>" />
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				</form>
			</div>
			<?php
		}

		/**
		 * Handle Deactivation Feedback
		 *
		 * @since 1.0.9
		 * @access public
		 */
		public function handle_deactivate_feedback() {
			check_ajax_referer( 'custif-deactivate-feedback', 'nonce' );

			$reason_key = ! empty( $_POST['reason_key'] ) ? sanitize_text_field( wp_unslash( $_POST['reason_key'] ) ) : '';
			$reason_text = ! empty( $_POST['reason_text'] ) ? sanitize_text_field( wp_unslash( $_POST['reason_text'] ) ) : '';

			$api_params = array(
				'count' => 1,
				'reason' => $reason_key . ( ! empty( $reason_text ) ? ': ' . $reason_text : '' ),
			);

			$response = wp_remote_post(
				$this->count_api,
				array(
					'timeout' => 30,
					'sslverify' => false,
					'body' => $api_params,
				)
			);

			wp_die();
		}

		/**
		 * Handle Skip Feedback
		 *
		 * @since 1.0.9
		 * @access public
		 */
		public function handle_skip_feedback() {
			check_ajax_referer( 'custif-deactivate-feedback', 'nonce' );

			$api_params = array(
				'count' => 1,
				'reason' => 'skipped',
			);

			$response = wp_remote_post(
				$this->count_api,
				array(
					'body' => $api_params,
					'headers' => array(
						'Content-Type' => 'application/x-www-form-urlencoded',
					),
				)
			);

			wp_die();
		}
	}

	Deactivate_Feedback::instance();
}
