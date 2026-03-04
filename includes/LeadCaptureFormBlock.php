<?php

/**
 * LeadCapture Form Gutenberg Block Handler
 *
 * Handles registration and management of the LeadCapture Form Gutenberg block.
 * Provides integration between WordPress block editor and the shortcode system.
 *
 * @package LeadCaptureForm\Block
 * @version 1.0.0
 * @since 1.0.0
 * @author Silver Assist
 */

namespace LeadCaptureForm\Block;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class LeadCaptureFormBlock
 *
 * Manages the Gutenberg block for LeadCapture forms, including registration,
 * script enqueuing, and server-side rendering.
 *
 * @since 1.0.0
 * @package LeadCaptureForm\Block
 */
class LeadCaptureFormBlock {

	/**
	 * Single instance of the block handler.
	 *
	 * @since 1.0.0
	 * @var LeadCaptureFormBlock|null
	 */
	private static ?LeadCaptureFormBlock $instance = null;

	/**
	 * Private constructor to prevent direct instantiation.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		$this->init();
	}

	/**
	 * Prevent object cloning.
	 *
	 * @since 1.0.0
	 */
	private function __clone(): void {
	}

	/**
	 * Prevent object unserialization.
	 *
	 * @since 1.0.0
	 * @throws \Exception Always thrown to prevent unserialization.
	 */
	public function __wakeup(): void {
		throw new \Exception( 'Cannot unserialize singleton' );
	}

	/**
	 * Get the single instance of the block handler.
	 *
	 * @since 1.0.0
	 * @return LeadCaptureFormBlock The single instance.
	 */
	public static function get_instance(): LeadCaptureFormBlock {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initialize the block handler.
	 *
	 * @since 1.0.0
	 */
	private function init(): void {
		\add_action( 'init', array( $this, 'register_block' ) );
		\add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
	}

	/**
	 * Register the Gutenberg block.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_block(): void {
		\register_block_type(
			LEADCAPTURE_FORM_PLUGIN_PATH . 'blocks/leadcapture-form/block.json',
			array(
				'render_callback' => array( $this, 'render_block' ),
			)
		);
	}

	/**
	 * Enqueue block editor assets.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_block_editor_assets(): void {
		\wp_enqueue_style(
			'leadcapture-form-block-editor',
			LEADCAPTURE_FORM_PLUGIN_URL . 'blocks/leadcapture-form/editor.css',
			array(),
			LEADCAPTURE_FORM_VERSION
		);

		\wp_enqueue_script(
			'leadcapture-form-block-js',
			LEADCAPTURE_FORM_PLUGIN_URL . 'blocks/leadcapture-form/block.js',
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor' ),
			LEADCAPTURE_FORM_VERSION,
			true
		);
	}

	/**
	 * Render the block on the frontend via server-side rendering.
	 *
	 * Converts block attributes to shortcode attributes and delegates
	 * rendering to the main shortcode handler.
	 *
	 * @since 1.0.0
	 * @param array $attributes Block attributes.
	 * @return string Rendered HTML output.
	 */
	public function render_block( array $attributes ): string {
		$form_token    = \sanitize_text_field( $attributes['formToken'] ?? '' );
		$mode          = \sanitize_text_field( $attributes['mode'] ?? 'embed' );
		$trigger_class = \sanitize_text_field( $attributes['triggerClass'] ?? '' );
		$height        = \sanitize_text_field( $attributes['height'] ?? '' );

		if ( empty( $form_token ) ) {
			return '<div class="leadcapture-form-error">' .
				\esc_html__( 'Error: A form token is required.', 'leadcapture-form' ) .
				'</div>';
		}

		$shortcode_parts   = array();
		$shortcode_parts[] = "form-token=\"{$form_token}\"";

		if ( ! empty( $mode ) && $mode !== 'embed' ) {
			$shortcode_parts[] = "mode=\"{$mode}\"";
		}

		if ( ! empty( $trigger_class ) ) {
			$shortcode_parts[] = "trigger-class=\"{$trigger_class}\"";
		}

		if ( ! empty( $height ) ) {
			$shortcode_parts[] = "height=\"{$height}\"";
		}

		$shortcode_string = '[leadcapture_form ' . implode( ' ', $shortcode_parts ) . ']';

		return \do_shortcode( $shortcode_string );
	}
}
