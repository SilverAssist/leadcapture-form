<?php

/**
 * Plugin Name: LeadCapture Form
 * Plugin URI: https://github.com/SilverAssist/leadcapture-form
 * Description: WordPress plugin that embeds LeadCapture.io forms via shortcode, Gutenberg block, and Elementor widget with lazy loading and popup trigger support.
 * Version: 1.0.0
 * Author: Silver Assist
 * Author URI: http://silverassist.com/
 * Text Domain: leadcapture-form
 * Domain Path: /languages
 * Requires PHP: 8.2
 * Update URI: https://github.com/SilverAssist/leadcapture-form
 * License: Polyform Noncommercial License 1.0.0
 * License URI: https://polyformproject.org/licenses/noncommercial/1.0.0/
 *
 * @package LeadCaptureForm
 * @version 1.0.0
 * @author Silver Assist
 */

namespace LeadCaptureForm;

// Import WordPress core classes.
use WP_Post;
// Import PHP standard classes.
use Exception;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'LEADCAPTURE_FORM_VERSION', '1.0.0' );
define( 'LEADCAPTURE_FORM_FILE', __FILE__ );
define( 'LEADCAPTURE_FORM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'LEADCAPTURE_FORM_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'LEADCAPTURE_FORM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Main plugin class using Singleton pattern.
 *
 * Handles the core functionality of the LeadCapture Form plugin,
 * including shortcode registration, script/style loading, and form rendering.
 *
 * @since 1.0.0
 * @package LeadCaptureForm
 */
class LeadCapture_Form {

	/**
	 * Single instance of the plugin.
	 *
	 * @since 1.0.0
	 * @var LeadCapture_Form|null
	 * @access private
	 * @static
	 */
	private static ?LeadCapture_Form $instance = null;

	/**
	 * Private constructor to prevent direct instantiation.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function __construct() {
		$this->init();
	}

	/**
	 * Prevent object cloning.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function __clone(): void {
	}

	/**
	 * Prevent object unserialization.
	 *
	 * @since 1.0.0
	 * @access public
	 * @throws Exception Always thrown to prevent unserialization.
	 */
	public function __wakeup(): void {
		throw new Exception( 'Cannot unserialize singleton' );
	}

	/**
	 * Get the single instance of the plugin.
	 *
	 * Implements the Singleton pattern to ensure only one instance
	 * of the plugin exists throughout the WordPress lifecycle.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 * @return LeadCapture_Form The single instance of the plugin.
	 */
	public static function get_instance(): LeadCapture_Form {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initialize the plugin.
	 *
	 * Sets up hooks, loads dependencies, and registers the shortcode.
	 * Called from the constructor.
	 *
	 * @since 1.0.0
	 * @access private
	 * @return void
	 */
	private function init(): void {
		// Load necessary files.
		$this->load_dependencies();

		// WordPress hooks.
		add_action( 'init', array( $this, 'init_plugin' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Register shortcode.
		add_shortcode( 'leadcapture_form', array( $this, 'render_shortcode' ) );
	}

	/**
	 * Load plugin dependencies.
	 *
	 * Include additional PHP files from the includes directory.
	 * Loads the Gutenberg block handler, Elementor widgets loader, and updater system.
	 *
	 * @since 1.0.0
	 * @access private
	 * @return void
	 */
	private function load_dependencies(): void {
		// Load Composer autoloader for external packages.
		if ( file_exists( LEADCAPTURE_FORM_PLUGIN_PATH . 'vendor/autoload.php' ) ) {
			require_once LEADCAPTURE_FORM_PLUGIN_PATH . 'vendor/autoload.php';
		}

		// Load Gutenberg block handler.
		require_once LEADCAPTURE_FORM_PLUGIN_PATH . 'includes/LeadCaptureFormBlock.php';

		// Load Elementor widgets loader (only if Elementor is active).
		if ( \did_action( 'elementor/loaded' ) || \class_exists( '\\Elementor\\Plugin' ) ) {
			require_once LEADCAPTURE_FORM_PLUGIN_PATH . 'includes/elementor/WidgetsLoader.php';
		}

		// Load updater system (only in admin).
		if ( \is_admin() ) {
			require_once LEADCAPTURE_FORM_PLUGIN_PATH . 'includes/LeadCaptureFormUpdater.php';
			require_once LEADCAPTURE_FORM_PLUGIN_PATH . 'includes/LeadCaptureFormAdmin.php';
		}
	}

	/**
	 * Initialize plugin after WordPress is loaded.
	 *
	 * Loads the plugin textdomain for internationalization support,
	 * initializes the Gutenberg block handler, sets up Elementor integration,
	 * and initializes the updater system.
	 * This method is called on the "init" hook.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	public function init_plugin(): void {
		// Load textdomain for translations.
		\load_plugin_textdomain(
			'leadcapture-form',
			false,
			dirname( LEADCAPTURE_FORM_PLUGIN_BASENAME ) . '/languages'
		);

		// Initialize Gutenberg block.
		if ( \class_exists( 'LeadCaptureForm\\Block\\LeadCaptureFormBlock' ) ) {
			Block\LeadCaptureFormBlock::get_instance();
		}

		// Initialize Elementor widgets loader.
		if ( \class_exists( 'LeadCaptureForm\\Elementor\\WidgetsLoader' ) ) {
			Elementor\WidgetsLoader::get_instance();
		}

		// Initialize updater system (only in admin).
		if ( \is_admin() && \class_exists( 'LeadCaptureForm\\LeadCaptureFormUpdater' ) ) {
			// Public repository - no authentication required.
			$updater = new LeadCaptureFormUpdater( __FILE__, 'SilverAssist/leadcapture-form' );

			// Initialize admin page.
			if ( \class_exists( 'LeadCaptureForm\\LeadCaptureFormAdmin' ) ) {
				new LeadCaptureFormAdmin( $updater );
			}
		}
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
	 * @param array $data        The Elementor data array to search.
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

/**
 * Initialize the plugin.
 *
 * Factory function to get the singleton instance of the plugin.
 * This function is called on the "plugins_loaded" hook.
 *
 * @since 1.0.0
 * @return LeadCapture_Form The single instance of the plugin.
 */
function leadcapture_form_init(): LeadCapture_Form {
	return LeadCapture_Form::get_instance();
}

// Initialize the plugin when WordPress is ready.
\add_action( 'plugins_loaded', 'LeadCaptureForm\\leadcapture_form_init' );
