<?php

namespace custif\includes;

use custif\widget\Custom_IFrame_Widget;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Widget Manager Class
 *
 * Handles the registration and initialization of custom Elementor widgets.
 *
 * @since 1.0.0
 */
class Widget_Manager {
	/**
	 * Class constructor.
	 *
	 * Hooks into Elementor's init action to initialize the widget.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct() {
		add_action( 'elementor/init', array( $this, 'init' ) );
	}

	/**
	 * Initialize the widget.
	 *
	 * Loads the widget file and registers it with Elementor.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init() {
		require_once CUSTIF_PATH . 'widget/class-custom-iframe-widget.php';
		add_action( 'elementor/widgets/register', array( $this, 'register_widget' ) );
	}

	/**
	 * Register the custom iframe widget with Elementor.
	 *
	 * @since 1.0.0
	 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
	 * @return void
	 */
	public function register_widget( $widgets_manager ) {
		$widgets_manager->register( new Custom_IFrame_Widget() );
	}
}

new Widget_Manager();
