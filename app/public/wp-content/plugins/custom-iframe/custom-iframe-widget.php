<?php
/**
 * Plugin Name: Custom iFrame
 * Plugin URI: https://coderzstudio.com/plugins/custom-iframe
 * Description: An advanced plugin to embed iFrames with customizable options.
 * Version: 2.0.3
 * Author: Coderz Studio
 * Author URI: https://coderzstudio.com/
 * Text Domain: custom-iframe
 * Domain Path: /languages
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package CustomIFrame
 * @author Coderz Studio
 * @copyright 2025 Coderz Studio
 * @license GPL-2.0+
 *
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Elementor tested up to: 4.0
 * Elementor Pro tested up to: 4.0
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load plugin text domain for internationalization.
 *
 * This function loads the text domain for the plugin to enable
 * translation support. It uses the 'custom-iframe'
 * text domain and looks for translation files in the /languages directory.
 *
 * @return void
 * @since 1.0.0
 */
function custif_load_textdomain() {
	// For WordPress 4.6 and later, this isn't needed for plugins on WordPress.org.
	// But we'll keep it for compatibility with older WordPress versions.
	load_plugin_textdomain(
		'custom-iframe',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);
}

add_action( 'init', 'custif_load_textdomain' );

// Define constants.
define( 'CUSTIF_VERSION', '2.0.3' );
define( 'CUSTIF_URL', plugin_dir_url( __FILE__ ) );
define( 'CUSTIF_PATH', plugin_dir_path( __FILE__ ) );

// Load required files.
$include_files = glob( CUSTIF_PATH . 'includes/class-*.php' );

if ( ! empty( $include_files ) ) {
	foreach ( $include_files as $file ) {
		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
}

// Load Gutenberg Block.
require_once CUSTIF_PATH . 'includes/class-gutenberg-block.php';
