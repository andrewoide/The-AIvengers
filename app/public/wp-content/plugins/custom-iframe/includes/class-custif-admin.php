<?php
/**
 * Admin class for Custom iFrame plugin
 *
 * @package CustomIFrame
 * @since 1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CustIF_Admin
 */
class CustIF_Admin {

	/**
	 * Initialize the class
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
	}

	/**
	 * Add admin menu
	 */
	public function add_admin_menu() {
		add_menu_page(
			__( 'Custom iFrame', 'custom-iframe' ),
			__( 'Custom iFrame', 'custom-iframe' ),
			'manage_options',
			'custom-iframe',
			array( $this, 'render_admin_page' ),
			'dashicons-welcome-widgets-menus',
			30
		);
	}

	/**
	 * Check if license is valid
	 *
	 * @return boolean
	 */
	public function get_license_status() {
		return get_option( 'custif_pro_license_status' );
	}

	/**
	 * Get license key
	 *
	 * @return string
	 */
	public function get_license_key() {
		return get_option( 'custif_pro_license_key', '' );
	}

	/**
	 * Check if pro plugin is installed and active
	 *
	 * @return boolean
	 */
	private function is_pro_installed() {
		return file_exists( WP_PLUGIN_DIR . '/custom-iframe-pro/custom-iframe-widget.php' );
	}

	/**
	 * Check if pro plugin is active
	 *
	 * @return boolean
	 */
	private function is_pro_active() {
		return is_plugin_active( 'custom-iframe-pro/custom-iframe-widget.php' );
	}

	/**
	 * Get user avatar
	 */
	private function get_user_avatar() {
		$current_user = wp_get_current_user();
		$avatar = get_avatar( $current_user->ID, 40, '', '', array( 'class' => 'user-avatar' ) );

		if ( ! $avatar ) {
			$avatar = '<div class="user-avatar-placeholder">' . strtoupper( substr( $current_user->display_name, 0, 1 ) ) . '</div>';
		}

		return $avatar;
	}

	/**
	 * Format license key to show only last 4 characters
	 *
	 * @param string $license_key The license key to format.
	 * @return string Formatted license key.
	 */
	private function format_license_key( $license_key ) {
		if ( empty( $license_key ) ) {
			return '';
		}

		$length = strlen( $license_key );
		if ( $length <= 4 ) {
			return $license_key;
		}

		$masked = str_repeat( 'x', $length - 4 );
		$last_four = substr( $license_key, -4 );

		return $masked . $last_four;
	}

	/**
	 * Render admin page
	 */
	public function render_admin_page() {
		$current_user = wp_get_current_user();
		?>
		<div class="wrap custif-admin-wrap">
			<!-- Plugin Header -->
			<header class="plugin-header">
				<div class="plugin-logo">
					<svg width="50" height="50" viewBox="0 0 191 154" fill="none" xmlns="http://www.w3.org/2000/svg">
						<rect x="2.5" y="2.5" width="186" height="148.899" rx="12.5" stroke="white" stroke-width="5"/>
						<path d="M15 0.5H176C184.008 0.5 190.5 6.99187 190.5 15V31.9784C190.5 32.2546 190.276 32.4784 190 32.4784H1C0.723858 32.4784 0.5 32.2546 0.5 31.9784V15C0.5 6.99188 6.99187 0.5 15 0.5Z" fill="white" stroke="white"/>
						<circle cx="17.4208" cy="16.4353" r="3.43525" fill="#4f46e5"/>
						<circle cx="28.4136" cy="16.4353" r="3.43525" fill="#4f46e5"/>
						<circle cx="39.4064" cy="16.4353" r="3.43525" fill="#4f46e5"/>
						<mask id="path-6-outside-1_103_252" maskUnits="userSpaceOnUse" x="32" y="58" width="126" height="64" fill="black">
							<rect fill="white" x="32" y="58" width="126" height="64"/>
							<path d="M69.646 112.958L33.01 91.544V87.416L69.388 68.238V74.344L39.546 89.566L69.646 106.422V112.958ZM83.578 120.612L78.504 118.118L105.422 59.81L110.582 62.476L83.578 120.612ZM119.44 112.958V106.422L149.454 89.566L119.612 74.344V68.238L156.076 87.416V91.544L119.44 112.958Z"/>
						</mask>
						<path d="M69.646 112.958L33.01 91.544V87.416L69.388 68.238V74.344L39.546 89.566L69.646 106.422V112.958ZM83.578 120.612L78.504 118.118L105.422 59.81L110.582 62.476L83.578 120.612ZM119.44 112.958V106.422L149.454 89.566L119.612 74.344V68.238L156.076 87.416V91.544L119.44 112.958Z" fill="white"/>
						<path d="M69.646 112.958L69.1414 113.821L70.646 114.701V112.958H69.646ZM33.01 91.544H32.01V92.1178L32.5054 92.4073L33.01 91.544ZM33.01 87.416L32.5437 86.5314L32.01 86.8127V87.416H33.01ZM69.388 68.238H70.388V66.5804L68.9217 67.3534L69.388 68.238ZM69.388 74.344L69.8424 75.2348L70.388 74.9565V74.344H69.388ZM39.546 89.566L39.0916 88.6752L37.4259 89.5249L39.0574 90.4385L39.546 89.566ZM69.646 106.422H70.646V105.836L70.1346 105.549L69.646 106.422ZM70.1506 112.095L33.5146 90.6807L32.5054 92.4073L69.1414 113.821L70.1506 112.095ZM34.01 91.544V87.416H32.01V91.544H34.01ZM33.4763 88.3006L69.8544 69.1226L68.9217 67.3534L32.5437 86.5314L33.4763 88.3006ZM68.388 68.238V74.344H70.388V68.238H68.388ZM68.9336 73.4532L39.0916 88.6752L40.0004 90.4568L69.8424 75.2348L68.9336 73.4532ZM39.0574 90.4385L69.1574 107.295L70.1346 105.549L40.0346 88.6935L39.0574 90.4385ZM68.646 106.422V112.958H70.646V106.422H68.646ZM83.578 120.612L83.1369 121.509L84.0543 121.96L84.4849 121.033L83.578 120.612ZM78.504 118.118L77.5961 117.699L77.187 118.585L78.0629 119.015L78.504 118.118ZM105.422 59.81L105.881 58.9216L104.952 58.4417L104.514 59.3909L105.422 59.81ZM110.582 62.476L111.489 62.8973L111.893 62.0277L111.041 61.5876L110.582 62.476ZM84.0191 119.715L78.9451 117.221L78.0629 119.015L83.1369 121.509L84.0191 119.715ZM79.4119 118.537L106.33 60.2291L104.514 59.3909L77.5961 117.699L79.4119 118.537ZM104.963 60.6984L110.123 63.3644L111.041 61.5876L105.881 58.9216L104.963 60.6984ZM109.675 62.0547L82.6711 120.191L84.4849 121.033L111.489 62.8973L109.675 62.0547ZM119.44 112.958H118.44V114.701L119.945 113.821L119.44 112.958ZM119.44 106.422L118.95 105.55L118.44 105.837V106.422H119.44ZM149.454 89.566L149.944 90.4379L151.572 89.5236L149.908 88.6752L149.454 89.566ZM119.612 74.344H118.612V74.9565L119.158 75.2348L119.612 74.344ZM119.612 68.238L120.077 67.3529L118.612 66.5822V68.238H119.612ZM156.076 87.416H157.076V86.8121L156.541 86.5309L156.076 87.416ZM156.076 91.544L156.581 92.4073L157.076 92.1178V91.544H156.076ZM120.44 112.958V106.422H118.44V112.958H120.44ZM119.93 107.294L149.944 90.4379L148.964 88.6941L118.95 105.55L119.93 107.294ZM149.908 88.6752L120.066 73.4532L119.158 75.2348L149 90.4568L149.908 88.6752ZM120.612 74.344V68.238H118.612V74.344H120.612ZM119.147 69.1231L155.611 88.3011L156.541 86.5309L120.077 67.3529L119.147 69.1231ZM155.076 87.416V91.544H157.076V87.416H155.076ZM155.571 90.6807L118.935 112.095L119.945 113.821L156.581 92.4073L155.571 90.6807Z" fill="white" mask="url(#path-6-outside-1_103_252)"/>
					</svg>
					<?php esc_html_e( 'Custom Iframe', 'custom-iframe' ); ?>
				</div>
				<div class="header-actions">
					<button class="version-btn">
					<?php
					if ( defined( 'CUSTIF_VERSION_PRO' ) ) {
						echo esc_html( 'Version ' . CUSTIF_VERSION_PRO );
					} else {
						echo esc_html( 'Version ' . CUSTIF_VERSION );
					}
					?>
					</button>
				</div>
			</header>

			<!-- Welcome Section -->
			<section class="welcome-section">
				<div class="welcome-content">
					<div class="user-info">
						<?php echo $this->get_user_avatar(); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span class="user-name">Welcome, <?php echo esc_html( $current_user->display_name ); ?></span>
					</div>
					<div class="welcome-header">
						<div class="welcome-text">
							<p><?php esc_html_e( 'Add powerful iFrame widgets to your Elementor pages. Easily embed any external content with advanced features and controls.', 'custom-iframe' ); ?></p>
						</div>
					</div>
				</div>
			</section>

			<!-- Main Content -->
			<div class="main-content">
				<!-- How it works -->
				<section class="how-it-works">
					<div class="section-header">
						<div class="section-title"><?php esc_html_e( 'How does it work?', 'custom-iframe' ); ?></div>
						<a href="https://youtu.be/EB6MgWB6zLA?si=ho-Bp7u3q70Zrbmi" class="watch-video" target="_blank">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" fill="#FF0000"/>
								<path d="M9.5 15.5L15.5 12L9.5 8.5V15.5Z" fill="white"/>
							</svg>
							<?php esc_html_e( 'Watch Video', 'custom-iframe' ); ?>
						</a>
					</div>

					<div class="steps-container">
						<div class="step-line"></div>

						<div class="step">
							<div class="step-number">1</div>
							<div class="step-title"><?php esc_html_e( 'Add the Widget', 'custom-iframe' ); ?></div>
							<div class="step-desc"><?php esc_html_e( 'Search for Custom iFrame widget, drag & drop it into the editor area.', 'custom-iframe' ); ?></div>
						</div>

						<div class="step">
							<div class="step-number">2</div>
							<div class="step-title"><?php esc_html_e( 'Paste the Link', 'custom-iframe' ); ?></div>
							<div class="step-desc"><?php esc_html_e( 'Enter your content link in the Source field.', 'custom-iframe' ); ?></div>
						</div>

						<div class="step">
							<div class="step-number">3</div>
							<div class="step-title"><?php esc_html_e( 'Adjust Settings', 'custom-iframe' ); ?></div>
							<div class="step-desc"><?php esc_html_e( 'Customize settings as per your requirements.', 'custom-iframe' ); ?></div>
						</div>
					</div>
				</section>

				<!-- Settings Section -->
				<div class="settings-section">
					<!-- Token Container -->
					<div class="token-container">
						<?php if ( ! $this->is_pro_installed() || ! $this->is_pro_active() ) : ?>
							<div class="token-check"><img src="<?php echo esc_url( CUSTIF_URL . 'assets/images/pro-icon.svg' ); ?>" alt="Check icon"></div>
							<div class="token-title"><?php esc_html_e( 'Upgrade to Pro', 'custom-iframe' ); ?></div>
							<div class="token-form">
								<a href="https://coderzstudio.com/plugins/custom-iframe/?utm_source=wordpress&utm_medium=dashboard&utm_campaign=custom_iframe&utm_id=wp_04" target="_blank" class="token-button upgrade-button">
									<?php esc_html_e( 'Upgrade Now', 'custom-iframe' ); ?>
								</a>
							</div>
							<div class="token-status">
								<span class="status-text">
									<?php esc_html_e( 'Get access to all pro features and premium support:', 'custom-iframe' ); ?>
									<div class="pro-features-grid">
										<div class="pro-features-column">
											<ul class="pro-features-list">
												<li>🔄 <?php esc_html_e( 'Lifetime Updates', 'custom-iframe' ); ?></li>
												<li>🛠️ <?php esc_html_e( 'Premium Support', 'custom-iframe' ); ?></li>
												<li>🌐 <?php esc_html_e( '100+ Embed Sources', 'custom-iframe' ); ?></li>
												<li>⚙️ <?php esc_html_e( 'Custom iFrame Attributes', 'custom-iframe' ); ?></li>
												<li>🎨 <?php esc_html_e( 'Custom Watermarks', 'custom-iframe' ); ?> <span class="coming-soon"><?php esc_html_e( 'new', 'custom-iframe' ); ?></span></li>
											</ul>
										</div>
										<div class="pro-features-column">
											<ul class="pro-features-list">
												<li>🔒 <?php esc_html_e( 'Advanced Security Options', 'custom-iframe' ); ?></li>
												<li>🎥 <?php esc_html_e( 'YouTube & Vimeo Controls', 'custom-iframe' ); ?></li>
												<li>🐦 <?php esc_html_e( 'Enhanced X (Twitter) Options', 'custom-iframe' ); ?></li>
												<li>📚 <?php esc_html_e( '3D Flipbook PDF', 'custom-iframe' ); ?> <span class="coming-soon"><?php esc_html_e( 'popular', 'custom-iframe' ); ?></span></li>
												<li>📱 <?php esc_html_e( 'Device Frame Display', 'custom-iframe' ); ?> <span class="coming-soon"><?php esc_html_e( 'new', 'custom-iframe' ); ?></span></li>
											</ul>
										</div>
									</div>
								</span>
							</div>
						<?php else : ?>
							<div class="token-check">✓</div>
							<div class="token-title"><?php esc_html_e( 'License Key Connection', 'custom-iframe' ); ?></div>
							<div class="token-form">
								<input type="text"
									   id="custif-license-key"
									   class="license-input"
									   placeholder="<?php esc_attr_e( 'Enter your license key', 'custom-iframe' ); ?>"
									   value="<?php echo esc_attr( $this->get_license_status() === 'valid' ? $this->format_license_key( $this->get_license_key() ) : $this->get_license_key() ); ?>"
								/>
								<?php
								$license_status = $this->get_license_status();
								if ( 'valid' === $license_status ) {
									?>
									<button class="token-button" id="deactivate-license">
										<?php esc_html_e( 'Deactivate License', 'custom-iframe' ); ?>
									</button>
									<?php
								} else {
									?>
									<button class="token-button" id="verify-license">
										<?php esc_html_e( 'Verify License', 'custom-iframe' ); ?>
									</button>
									<?php
								}
								?>
							</div>
							<div class="token-status">
								<span class="status-text">
									<?php
									if ( 'valid' === $license_status ) {
										echo esc_html__( 'Your license is active.', 'custom-iframe' );
									} else if ( 'expired' === $license_status ) {
										echo esc_html__( 'Your license has expired.', 'custom-iframe' );
									} else {
										echo esc_html__( 'Enter your license key to activate pro features', 'custom-iframe' );
									}
									?>
								</span>
							</div>
						<?php endif; ?>
					</div>

					<div class="token-container">
						<iframe width="560" height="315" src="https://www.youtube-nocookie.com/embed/EB6MgWB6zLA?si=ho-Bp7u3q70Zrbmi" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
					</div>
					
				</div>

				<div class="resources-section cards-grid">
						<div class="resource-card">
							<div class="resource-title"><?php esc_html_e( 'Documentation', 'custom-iframe' ); ?></div>
							<button class="resource-action"><a href="https://customiframe.com/docs/?utm_source=plugin&utm_medium=wpdashboard&utm_campaign=read_docs" target="_blank"><?php esc_html_e( 'Read Now', 'custom-iframe' ); ?></a></button>
						</div>

						<div class="resource-card">
							<div class="resource-title"><?php esc_html_e( 'Join Our Community', 'custom-iframe' ); ?></div>
							<button class="resource-action"><a href="https://www.reddit.com/r/coderzstudio/" target="_blank"><?php esc_html_e( 'Join Now', 'custom-iframe' ); ?></a></button>
						</div>

						<div class="resource-card">
							<div class="resource-title"><?php esc_html_e( 'Video Tutorials', 'custom-iframe' ); ?></div>
							<button class="resource-action"><a href="https://www.youtube.com/@CoderzStudio" target="_blank"><?php esc_html_e( 'Watch Now', 'custom-iframe' ); ?></a></button>
						</div>

						<div class="resource-card">
							<div class="resource-title"><?php esc_html_e( 'Need Help?', 'custom-iframe' ); ?></div>
							<button class="resource-action"><a href="https://store.coderzstudio.com/dashboard/helpdesk/?utm_source=wordpress&utm_medium=dashboard&utm_campaign=custom_iframe&utm_id=wp_04" target="_blank"><?php esc_html_e( 'Raise Ticket', 'custom-iframe' ); ?></a></button>
						</div>
					</div>

			</div>
		</div>
		<?php
	}
}

// Initialize the admin class.
new CustIF_Admin();
