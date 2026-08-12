<?php
/**
 * LeadCapture Form Shortcode Handler
 *
 * Registers the [leadcapture_form] shortcode and conditionally enqueues
 * the plugin's frontend assets when the shortcode or an Elementor
 * LeadCapture widget is present on the current page.
 *
 * @package LeadCaptureForm
 * @version 1.1.0
 * @since 1.1.0
 * @author Silver Assist
 */

namespace LeadCaptureForm;

use SilverAssist\PluginKernel\Interfaces\LoadableInterface;
use WP_Post;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ShortcodeHandler
 *
 * Extracted from the plugin's former monolithic LeadCapture_Form class as
 * part of adopting silverassist/wp-plugin-kernel's LoadableInterface
 * bootstrap pattern.
 *
 * @since 1.1.0
 */
class ShortcodeHandler implements LoadableInterface {

	/**
	 * Single instance of the shortcode handler
	 *
	 * @since 1.1.0
	 * @var ShortcodeHandler|null
	 */
	private static ?ShortcodeHandler $instance = null;

	/**
	 * Get the single instance of the shortcode handler
	 *
	 * @since 1.1.0
	 * @return ShortcodeHandler
	 */
	public static function instance(): ShortcodeHandler {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Private constructor to prevent direct instantiation
	 *
	 * @since 1.1.0
	 */
	private function __construct() {
	}

	/**
	 * Initialize the component
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function init(): void {
		\add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		\add_shortcode( 'leadcapture_form', array( $this, 'render_shortcode' ) );
	}

	/**
	 * Get the component loading priority
	 *
	 * @since 1.1.0
	 * @return int
	 */
	public function get_priority(): int {
		return 20;
	}

	/**
	 * Determine if the component should be loaded
	 *
	 * @since 1.1.0
	 * @return bool
	 */
	public function should_load(): bool {
		return true;
	}

	/**
	 * Load scripts and styles.
	 *
	 * Conditionally enqueues CSS and JavaScript files only when the shortcode
	 * is present on the current page or when Elementor widgets are detected.
	 * Also localizes script with global settings.
	 *
	 * @since 1.0.0
	 * @access public
	 * @global WP_Post $post The current post object.
	 * @return void
	 */
	public function enqueue_scripts(): void {
		// Register CSS.
		wp_register_style(
			'leadcapture-form-css',
			LEADCAPTURE_FORM_PLUGIN_URL . 'assets/css/leadcapture-form.css',
			array(),
			LEADCAPTURE_FORM_VERSION
		);

		// Register JavaScript.
		wp_register_script(
			'leadcapture-form-js',
			LEADCAPTURE_FORM_PLUGIN_URL . 'assets/js/leadcapture-form.js',
			array(),
			LEADCAPTURE_FORM_VERSION,
			true
		);

		global $post;
		$should_load_scripts = false;
		$shortcode_instances = array();

		// Check if shortcode is present in post content.
		if ( is_a( $post, WP_Post::class ) && has_shortcode( $post->post_content, 'leadcapture_form' ) ) {
			$should_load_scripts = true;
			$shortcode_instances = $this->extract_shortcode_instances( $post->post_content );
		}

		// Check if Elementor widgets are present.
		if ( ! $should_load_scripts && $this->has_elementor_widgets() ) {
			$should_load_scripts = true;
		}

		if ( $should_load_scripts ) {
			wp_enqueue_style( 'leadcapture-form-css' );
			wp_enqueue_script( 'leadcapture-form-js' );

			// Localize script with global settings.
			wp_localize_script(
				'leadcapture-form-js',
				'leadCaptureFormSettings',
				array(
					'instances'      => $shortcode_instances,
					'pixelScriptUrl' => 'https://api.useleadbot.com/lead-bots/get-pixel-script.js',
				)
			);
		}
	}

	/**
	 * Render the shortcode.
	 *
	 * Processes shortcode attributes and generates HTML output for the form container.
	 * Supports two modes: embed (inline form) and popup trigger (button click).
	 *
	 * @since 1.0.0
	 * @access public
	 * @param array|string $atts {
	 *     Shortcode attributes.
	 *
	 *     @type string $form-token      Required. The LeadCapture.io form token (e.g., GLFT-XXXX).
	 *     @type string $mode            Optional. Display mode: "embed" or "popup". Default "embed".
	 *     @type string $trigger-class   Optional. CSS class for popup trigger (provided by LeadCapture.io).
	 *     @type string $height          Optional. Placeholder height for embed mode (e.g., "600px").
	 * }
	 * @return string HTML output for the shortcode.
	 */
	public function render_shortcode( $atts ): string {
		// Default attributes.
		$atts = shortcode_atts(
			array(
				'form-token'    => '',
				'mode'          => 'embed',
				'trigger-class' => '',
				'height'        => '',
			),
			$atts,
			'leadcapture_form'
		);

		// Validate form token.
		$form_token = \sanitize_text_field( $atts['form-token'] ?? '' );
		if ( empty( $form_token ) ) {
			return '<div class="leadcapture-form-error">' .
				esc_html__( 'Error: The form-token parameter is required.', 'leadcapture-form' ) .
				'</div>';
		}

		$mode          = \sanitize_text_field( $atts['mode'] ?? 'embed' );
		$trigger_class = \sanitize_text_field( $atts['trigger-class'] ?? '' );
		$height        = \sanitize_text_field( $atts['height'] ?? '' );

		// Create unique ID for this shortcode instance.
		$instance_id = 'leadcapture-form-' . \wp_generate_uuid4();

		// Generate form HTML using output buffering.
		ob_start();

		if ( $mode === 'popup' && ! empty( $trigger_class ) ) {
			// Popup mode: the pixel script handles the popup via trigger class.
			// The script is loaded lazily; the trigger class activates the popup.
			?>
			<div class="leadcapture-form-container leadcapture-popup-mode"
				id="<?php echo \esc_attr( $instance_id ); ?>"
				data-form-token="<?php echo \esc_attr( $form_token ); ?>"
				data-mode="popup"
				data-trigger-class="<?php echo \esc_attr( $trigger_class ); ?>">
			</div>
			<?php
		} else {
			// Embed mode: inline form with placeholder animation.
			?>
			<div class="leadcapture-form-container leadcapture-embed-mode"
				id="<?php echo \esc_attr( $instance_id ); ?>"
				data-form-token="<?php echo \esc_attr( $form_token ); ?>"
				data-mode="embed"
				data-height="<?php echo \esc_attr( $height ); ?>">

				<div class="leadcapture-form-wrapper">
					<!-- Placeholder with pulse animation. -->
					<div class="leadcapture-form-placeholder">
						<div class="leadcapture-pulse-animation"></div>
					</div>
					<!-- Form container: LeadCapture.io populates divs with class 'leadforms-embd-form'. -->
					<div class="leadcapture-form-content" style="display: none;">
						<div class="leadforms-embd-form"><!-- LeadCapture.io renders here. --></div>
					</div>
				</div>

			</div>
			<?php
		}

		return ob_get_clean();
	}

	/**
	 * Extract shortcode instances from post content.
	 *
	 * Parses the post content to find all instances of the leadcapture_form shortcode
	 * and extracts their attributes for JavaScript configuration.
	 *
	 * @since 1.0.0
	 * @access private
	 * @param string $content The post content to parse.
	 * @return array Array of shortcode instances with their configurations.
	 */
	private function extract_shortcode_instances( string $content ): array {
		$instances = array();

		// Pattern to find leadcapture_form shortcodes.
		$pattern = '/\[leadcapture_form\s+([^\]]*)\]/';

		if ( preg_match_all( $pattern, $content, $matches, PREG_SET_ORDER ) ) {
			foreach ( $matches as $index => $match ) {
				// Parse shortcode attributes.
				$atts = shortcode_parse_atts( $match[1] );

				if ( $atts ) {
					$form_token    = \sanitize_text_field( $atts['form-token'] ?? '' );
					$mode          = \sanitize_text_field( $atts['mode'] ?? 'embed' );
					$trigger_class = \sanitize_text_field( $atts['trigger-class'] ?? '' );
					$height        = \sanitize_text_field( $atts['height'] ?? '' );

					if ( ! empty( $form_token ) ) {
						$instances[] = array(
							'form_token'    => $form_token,
							'mode'          => $mode,
							'trigger_class' => $trigger_class,
							'height'        => $height,
							'index'         => $index,
						);
					}
				}
			}
		}

		return $instances;
	}

	/**
	 * Check if Elementor LeadCapture widgets are present on the current page.
	 *
	 * Searches for Elementor data to detect if any LeadCapture form widgets are active.
	 * This is used to determine if scripts should be loaded when shortcodes aren't present.
	 *
	 * @since 1.0.0
	 * @access private
	 * @return bool True if Elementor widgets are detected, false otherwise.
	 */
	private function has_elementor_widgets(): bool {
		// Early return if Elementor is not active.
		if ( ! class_exists( '\\Elementor\\Plugin' ) ) {
			return false;
		}

		global $post;
		if ( ! is_a( $post, WP_Post::class ) ) {
			return false;
		}

		// Check if this is an Elementor page.
		$elementor_data = get_post_meta( $post->ID, '_elementor_data', true );

		if ( empty( $elementor_data ) ) {
			return false;
		}

		// Parse Elementor data (it's stored as JSON).
		$elementor_data = json_decode( $elementor_data, true );

		if ( ! is_array( $elementor_data ) ) {
			return false;
		}

		// Recursively search for our widget in the Elementor data.
		return $this->search_elementor_data_for_widget( $elementor_data, 'leadcapture-form' );
	}

	/**
	 * Recursively search Elementor data for specific widget type.
	 *
	 * Searches through the nested Elementor data structure to find widgets
	 * of a specific type (widget name).
	 *
	 * @since 1.0.0
	 * @access private
	 * @param array  $data        The Elementor data array to search.
	 * @param string $widget_name The widget name to search for.
	 * @return bool True if widget is found, false otherwise.
	 */
	private function search_elementor_data_for_widget( array $data, string $widget_name ): bool {
		foreach ( $data as $element ) {
			if ( ! is_array( $element ) ) {
				continue;
			}

			// Check if this element is our widget.
			if ( isset( $element['widgetType'] ) && $element['widgetType'] === $widget_name ) {
				return true;
			}

			// Recursively search in elements (for sections, columns, etc.).
			if ( isset( $element['elements'] ) && is_array( $element['elements'] ) ) {
				if ( $this->search_elementor_data_for_widget( $element['elements'], $widget_name ) ) {
					return true;
				}
			}
		}

		return false;
	}
}
