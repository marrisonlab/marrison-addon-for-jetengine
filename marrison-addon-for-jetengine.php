<?php
/**
 * Plugin Name: Marrison Addon for JetEngine
 * Description: Adds custom layouts and title options to JetEngine Listing Grid widget.
 * Version: 1.0.0
 * Author: Angelo Marra
 * Author URI: https://www.marrisonlab.com/
 * Text Domain: mafj
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Marrison_Addon_For_JetEngine {

	public function __construct() {
		// Elementor Control
		add_action( 'elementor/element/jet-listing-grid/section_general/after_section_end', array( $this, 'register_controls' ), 10, 2 );
		
		// Add layout class to the grid container
		add_filter( 'jet-engine/listing/container-classes', array( $this, 'add_layout_classes' ), 10, 3 );
		
		// Render Title
		add_filter( 'elementor/widget/render_content', [ $this, 'render_title' ], 10, 2 );

		// Enqueue Styles
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
	}

	public function register_controls( $widget, $args ) {
		
		$widget->start_controls_section(
			'section_marrison_addon',
			array(
				'label' => __( 'Marrison Addon Settings', 'mafj' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

        // --- Custom Layouts Controls ---
        $widget->add_control(
			'mafj_layout_heading',
			[
				'label' => __( 'Grid Layout (First Batch Only)', 'mafj' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$layout_options = array(
			'' => __( 'Default', 'mafj' ),
			'one_left_four_right' => __( '1 Left - 4 Right (5 items)', 'mafj' ),
			'four_left_one_right' => __( '4 Left - 1 Right (5 items)', 'mafj' ),
			'one_left_two_right'  => __( '1 Left - 2 Right (3 items)', 'mafj' ),
			'two_left_one_right'  => __( '2 Left - 1 Right (3 items)', 'mafj' ),
		);

		$widget->add_responsive_control(
			'custom_layout',
			array(
				'label'   => __( 'Custom Layout', 'mafj' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => $layout_options,
				'description' => __( 'Select layout for each device. Applies only to the first batch.', 'mafj' ),
			)
		);

        // --- Custom Title Controls ---
        $widget->add_control(
			'mafj_title_heading',
			[
				'label' => __( 'Listing Title', 'mafj' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$widget->add_control(
			'custom_title_enabled',
			[
				'label'        => __( 'Enable Title', 'mafj' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'mafj' ),
				'label_off'    => __( 'No', 'mafj' ),
				'return_value' => 'yes',
				'default'      => '',
			]
		);

		$widget->add_control(
			'custom_title_text',
			[
				'label'       => __( 'Title', 'mafj' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Listing Title', 'mafj' ),
				'placeholder' => __( 'Enter title', 'mafj' ),
				'condition'   => [
					'custom_title_enabled' => 'yes',
				],
				'dynamic'     => [
					'active' => true,
				],
			]
		);

		$widget->add_control(
			'custom_title_tag',
			[
				'label'   => __( 'HTML Tag', 'mafj' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'h1'   => 'H1',
					'h2'   => 'H2',
					'h3'   => 'H3',
					'h4'   => 'H4',
					'h5'   => 'H5',
					'h6'   => 'H6',
					'div'  => 'div',
					'span' => 'span',
					'p'    => 'p',
				],
				'default' => 'h3',
				'condition' => [
					'custom_title_enabled' => 'yes',
				],
			]
		);
		
		$widget->add_responsive_control(
			'custom_title_align',
			[
				'label' => __( 'Alignment', 'mafj' ),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => __( 'Left', 'mafj' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'mafj' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => __( 'Right', 'mafj' ),
						'icon' => 'eicon-text-align-right',
					],
					'justify' => [
						'title' => __( 'Justified', 'mafj' ),
						'icon' => 'eicon-text-align-justify',
					],
				],
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .jet-listing-custom-title' => 'text-align: {{VALUE}};',
				],
				'condition' => [
					'custom_title_enabled' => 'yes',
				],
			]
		);

		$widget->end_controls_section();

        // --- Title Style Section ---
		$widget->start_controls_section(
			'section_custom_title_style',
			[
				'label'     => __( 'Listing Title Style', 'mafj' ),
				'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'custom_title_enabled' => 'yes',
				],
			]
		);

		$widget->add_control(
			'custom_title_color',
			[
				'label'     => __( 'Text Color', 'mafj' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .jet-listing-custom-title' => 'color: {{VALUE}};',
				],
			]
		);

		$widget->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'custom_title_typography',
				'selector' => '{{WRAPPER}} .jet-listing-custom-title',
			]
		);

		$widget->add_responsive_control(
			'custom_title_margin',
			[
				'label'      => __( 'Margin', 'mafj' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .jet-listing-custom-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$widget->add_responsive_control(
			'custom_title_padding',
			[
				'label'      => __( 'Padding', 'mafj' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .jet-listing-custom-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$widget->end_controls_section();
	}

	public function add_layout_classes( $classes, $settings, $render ) {
		$devices = array(
			'desktop' => 'custom_layout',
			'tablet'  => 'custom_layout_tablet',
			'mobile'  => 'custom_layout_mobile',
		);
		
		$has_layout = false;

		foreach ( $devices as $device => $setting_key ) {
			if ( ! empty( $settings[ $setting_key ] ) ) {
				$classes[] = 'jet-custom-layout-' . $device . '-' . $settings[ $setting_key ];
				$has_layout = true;
			}
		}
		
		if ( $has_layout ) {
			// Apply layout only to the first batch
			$classes[] = 'jet-custom-layout-first-only';
		}

		return $classes;
	}

	public function render_title( $content, $widget ) {
		if ( 'jet-listing-grid' !== $widget->get_name() ) {
			return $content;
		}

		$settings = $widget->get_settings_for_display();

		if ( empty( $settings['custom_title_enabled'] ) || 'yes' !== $settings['custom_title_enabled'] ) {
			return $content;
		}

		if ( empty( $settings['custom_title_text'] ) ) {
			return $content;
		}

		$title_tag = ! empty( $settings['custom_title_tag'] ) ? $settings['custom_title_tag'] : 'h3';
		$title_text = $settings['custom_title_text'];

		$title_html = sprintf(
			'<%1$s class="jet-listing-custom-title">%2$s</%1$s>',
			esc_attr( $title_tag ),
			esc_html( $title_text )
		);

		return $title_html . $content;
	}

	public function enqueue_styles() {
		wp_enqueue_style( 
			'mafj-styles', 
			plugins_url( 'assets/css/style.css', __FILE__ ), 
			array(), 
			'1.0.0' 
		);
	}

}

new Marrison_Addon_For_JetEngine();
