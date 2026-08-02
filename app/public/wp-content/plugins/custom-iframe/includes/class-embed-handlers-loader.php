<?php

namespace custif\includes;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Embed Handlers Loader Class
 *
 * Handles the loading and initialization of embed handler classes.
 *
 * @since 1.0.0
 */
class Embed_Handlers_Loader {
	/**
	 * Class constructor.
	 *
	 * Loads necessary embed handler classes.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct() {
		$this->load_dependencies();
	}

	/**
	 * Load dependencies.
	 *
	 * Load the embed handler class files.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function load_dependencies() {
		// Load embed converter class.
		require_once CUSTIF_PATH . 'includes/embed-handlers/class-embed-converter.php';

		// Load PDF handler class.
		require_once CUSTIF_PATH . 'includes/embed-handlers/class-pdf-handler.php';
	}
}

// Initialize the loader.
new Embed_Handlers_Loader();
