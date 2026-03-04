<?php

/**
 * LeadCapture Form Elementor Widget
 *
 * Elementor widget for displaying LeadCapture.io forms with embed and popup modes.
 * Integrates with the existing shortcode functionality.
 *
 * @package LeadCaptureForm\Elementor\Widgets
 * @version 1.0.0
 * @since 1.0.0
 * @author Silver Assist
 */

namespace LeadCaptureForm\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Plugin;
use Elementor\Widget_Base;

/**
 * LeadCapture Form Widget for Elementor
 *
 * Provides Elementor integration for the LeadCapture Form shortcode,
 * allowing users to configure form token and display mode
 * through the Elementor visual editor interface.
 *
 * @since 1.0.0
 */
class LeadCaptureFormWidget extends Widget_Base {

	/**
	 * Get widget name.
	 *
	 * @since 1.0.0
	 * @return string Widget name.
	 */
	public function get_name(): string {
		return 'leadcapture-form';
	}

	/**
	 * Get widget title.
	 *
	 * @since 1.0.0
	 * @return string Widget title.
	 */
	public function get_title(): string {
		return __( 'LeadCapture Form', 'leadcapture-form' );
	}

	/**
	 * Get widget icon.
	 *
	 * @since 1.0.0
	 * @return string Widget icon.
	 */
	public function get_icon(): string {
		return 'eicon-form-horizontal';
	}

	/**
	 * Get widget categories.
	 *
	 * @since 1.0.0
	 * @return array Widget categories.
	 */
	public function get_categories(): array {
		return array( 'leadcapture-forms' );
	}

	/**
	 * Get widget keywords.
	 *
	 * @since 1.0.0
	 * @return array Widget keywords for search.
	 */
	public function get_keywords(): array {
		return array( 'leadcapture', 'form', 'lead', 'capture', 'popup', 'embed' );
	}

	/**
	 * Get widget style dependencies.
	 *
	 * @since 1.0.0
	 * @return array Widget style dependencies.
	 */
	public function get_style_depends(): array {
		return array( 'leadcapture-form-css' );
	}

	/**
	 * Get widget script dependencies.
	 *
	 * @since 1.0.0
	 * @return array Widget script dependencies.
	 */
	public function get_script_depends(): array {
		return array( 'leadcapture-form-js' );
	}

	/**
	 * Register widget controls.
	 *
	 * Adds controls for form token, mode, trigger class, and height parameters.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function register_controls(): void {
		$this->register_content_controls();
		$this->register_style_controls();
	}

	/**
	 * Register content controls.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function register_content_controls(): void {
		$this->start_controls_section(
			'section_form_settings',
			array(
				'label' => __( 'Form Settings', 'leadcapture-form' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'form_token',
			array(
				'label'       => __( 'Form Token', 'leadcapture-form' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => 'GLFT-XXXXXXXXXXXXXXXXXXXXXXX',
				'description' => __( 'Your LeadCapture.io form token (e.g., GLFT-XXXX). Find it in LeadCapture.io → Settings → Publish.', 'leadcapture-form' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'mode',
			array(
				'label'       => __( 'Display Mode', 'leadcapture-form' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'embed',
				'options'     => array(
					'embed' => __( 'Embed (Inline Form)', 'leadcapture-form' ),
					'popup' => __( 'Popup (Trigger on Click)', 'leadcapture-form' ),
				),
				'description' => __( 'Embed displays the form inline. Popup loads the script and triggers via a CSS class on buttons.', 'leadcapture-form' ),
			)
		);

		$this->add_control(
			'trigger_class',
			array(
				'label'       => __( 'Trigger Class', 'leadcapture-form' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => 'leadforms-trigger-XX',
				'description' => __( 'CSS class that triggers the popup. Provided by LeadCapture.io → Settings → Publish.', 'leadcapture-form' ),
				'label_block' => true,
				'condition'   => array(
					'mode' => 'popup',
				),
			)
		);

		$this->add_control(
			'mode_divider',
			array(
				'type' => Controls_Manager::DIVIDER,
			)
		);

		$this->add_control(
			'height',
			array(
				'label'       => __( 'Placeholder Height', 'leadcapture-form' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => '600px',
				'description' => __( 'Height of the loading placeholder (e.g., 600px, 50vh). Leave empty for default.', 'leadcapture-form' ),
				'condition'   => array(
					'mode' => 'embed',
				),
			)
		);

		$this->add_control(
			'form_token_note',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => '<div style="background: #f1f1f1; padding: 10px; border-radius: 4px; margin-top: 10px;">' .
					'<strong>' . __( 'Note:', 'leadcapture-form' ) . '</strong><br>' .
					__( 'The form token is required. Get it from your LeadCapture.io dashboard under Settings → Publish.', 'leadcapture-form' ) .
					'</div>',
				'content_classes' => 'elementor-control-field-description',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register style controls.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function register_style_controls(): void {
		$this->start_controls_section(
			'section_form_style',
			array(
				'label' => __( 'Form Style', 'leadcapture-form' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'form_alignment',
			array(
				'label'     => __( 'Alignment', 'leadcapture-form' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'left'   => array(
						'title' => __( 'Left', 'leadcapture-form' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => __( 'Center', 'leadcapture-form' ),
						'icon'  => 'eicon-text-align-center',
					),
					'right'  => array(
						'title' => __( 'Right', 'leadcapture-form' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'default'   => 'center',
				'selectors' => array(
					'{{WRAPPER}} .leadcapture-form-container' => 'text-align: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'form_width',
			array(
				'label'      => __( 'Width', 'leadcapture-form' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'vw' ),
				'range'      => array(
					'px' => array(
						'min'  => 100,
						'max'  => 1200,
						'step' => 10,
					),
					'%'  => array(
						'min'  => 10,
						'max'  => 100,
						'step' => 1,
					),
				),
				'default'    => array(
					'unit' => '%',
					'size' => 100,
				),
				'selectors'  => array(
					'{{WRAPPER}} .leadcapture-form-container' => 'max-width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render widget output on the frontend.
	 *
	 * Uses the existing shortcode functionality to maintain consistency
	 * across different implementation methods (shortcode, Gutenberg, Elementor).
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function render(): void {
		$settings = $this->get_settings_for_display();

		$form_token    = ! empty( $settings['form_token'] ) ? \sanitize_text_field( $settings['form_token'] ) : '';
		$mode          = ! empty( $settings['mode'] ) ? \sanitize_text_field( $settings['mode'] ) : 'embed';
		$trigger_class = ! empty( $settings['trigger_class'] ) ? \sanitize_text_field( $settings['trigger_class'] ) : '';
		$height        = ! empty( $settings['height'] ) ? \sanitize_text_field( $settings['height'] ) : '';

		// Validate form token.
		if ( empty( $form_token ) ) {
			if ( Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="leadcapture-form-error elementor-alert elementor-alert-warning">' .
					'<span class="elementor-alert-title">' . \esc_html__( 'LeadCapture Form Widget', 'leadcapture-form' ) . '</span>' .
					'<span class="elementor-alert-description">' . \esc_html__( 'Please configure a Form Token in the widget settings.', 'leadcapture-form' ) . '</span>' .
					'</div>';
			}
			return;
		}

		// Use the existing shortcode function to render the form.
		$plugin_instance = \LeadCaptureForm\LeadCapture_Form::get_instance();

		$shortcode_atts = array(
			'form-token' => $form_token,
			'mode'       => $mode,
		);

		if ( ! empty( $trigger_class ) ) {
			$shortcode_atts['trigger-class'] = $trigger_class;
		}

		if ( ! empty( $height ) ) {
			$shortcode_atts['height'] = $height;
		}

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Shortcode output is escaped internally.
		echo $plugin_instance->render_shortcode( $shortcode_atts );
	}

	/**
	 * Render widget content template for live preview.
	 *
	 * Used by Elementor editor for live preview functionality.
	 * Shows a placeholder representation of the form.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function content_template(): void {
		?>
		<# var formToken=settings.form_token || '' ; var mode=settings.mode || 'embed' ; var
			triggerClass=settings.trigger_class || '' ; if (!formToken) { #>
			<div
				style="padding: 20px; text-align: center; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px;">
				<?php echo \esc_html__( 'Please configure a Form Token in the widget settings.', 'leadcapture-form' ); ?>
			</div>
			<# return; } #>
				<div
					style="padding: 20px; text-align: center; background: #e7f3ff; border: 2px dashed #0073aa; border-radius: 5px;">
					<h3 style="margin-top: 0; color: #0073aa;">LeadCapture Form</h3>
					<p style="font-family: monospace; background: white; padding: 10px; border-radius: 3px; margin: 10px 0;">
						[leadcapture_form form-token="{{{ formToken }}}" mode="{{{ mode }}}"<# if (mode==='popup'
							&& triggerClass) { #> trigger-class="{{{ triggerClass }}}"<# } #>]
					</p>
					<p style="color: #666; font-size: 12px; margin-bottom: 0;">
						<# if (mode==='popup' ) { #>
							<?php echo \esc_html__( 'Popup mode — The form will open as a popup when elements with the trigger class are clicked.', 'leadcapture-form' ); ?>
							<# } else { #>
								<?php echo \esc_html__( 'Embed mode — The form will render inline on the page.', 'leadcapture-form' ); ?>
								<# } #>
					</p>
				</div>
				<?php
	}
}
