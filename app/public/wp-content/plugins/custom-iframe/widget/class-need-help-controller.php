<?php
namespace custif\widget;

use Elementor\Controls_Manager;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$this->start_controls_section(
	'custif_section_needhelp',
	array(
		'label' => esc_html__( 'Need Help ?', 'custom-iframe' ),
		'tab' => Controls_Manager::TAB_CONTENT,
	)
);
$this->add_control(
	'custif_raise_a_ticket',
	array(
		'type' => Controls_Manager::RAW_HTML,
		'raw' => wp_kses_post( "<a class='' href='https://wordpress.org/support/plugin/custom-iframe/' target='_blank' rel='noopener noreferrer'> Raise a Ticket </a>" ),
	)
);
$this->add_control(
	'custif_read_documentation',
	array(
		'type' => Controls_Manager::RAW_HTML,
		'raw' => wp_kses_post( "<a class='' href='https://customiframe.com/docs/?utm_source=elementor&utm_medium=widget_settings&utm_campaign=read_docs' target='_blank' rel='noopener noreferrer'> Read Documentation </a>" ),
	)
);
$this->add_control(
	'custif_help_suggest_feature',
	array(
		'type' => Controls_Manager::RAW_HTML,
		'raw' => wp_kses_post( "<a class='' href='https://customiframe.com/contact/?utm_source=elementor&utm_medium=widget_settings&utm_campaign=suggest_feature' target='_blank' rel='noopener noreferrer'> Suggest Feature </a>" ),
	)
);
$this->add_control(
	'custif_bug_reports',
	array(
		'type' => Controls_Manager::RAW_HTML,
		'raw' => wp_kses_post( "<a class='' href='https://customiframe.com/support/?utm_source=elementor&utm_medium=widget_settings&utm_campaign=support_links&utm_content=report_issue' target='_blank' rel='noopener noreferrer'> Didn't work like you wanted? Report Issue </a>" ),
	)
);
$this->end_controls_section();
