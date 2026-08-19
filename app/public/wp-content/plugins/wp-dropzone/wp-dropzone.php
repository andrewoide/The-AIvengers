<?php
/**
 * Plugin Name:       WP Dropzone
 * Plugin URI:        https://wordpress.org/plugins/wp-dropzone/
 * Description:       Upload files into WordPress media library from front-end with drag-and-drop functionality and customizable options.
 * Version:           1.1.2
 * Author:            Nazmul Sabuz
 * Author URI:        https://profiles.wordpress.org/nazsabuz/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-dropzone
 * Domain Path:       /languages
 *
 * @package           WP_Dropzone
 * @version           1.1.2
 * @link              https://wordpress.org/plugins/wp-dropzone/
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

// Define plugin constants.
define( 'WP_DROPZONE_VERSION', '1.1.2' );
define( 'WP_DROPZONE_BASENAME', plugin_basename( __FILE__ ) );
define( 'WP_DROPZONE_DIR', __DIR__ );
define( 'WP_DROPZONE_URL', plugin_dir_url( __FILE__ ) );

// require necessary class files.
require_once 'includes/class-plugin.php';

// kickstart.
new WP_Dropzone\Plugin();
