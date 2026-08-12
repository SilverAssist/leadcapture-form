<?php
/**
 * Plugin Name: LeadCapture Form
 * Plugin URI: https://github.com/SilverAssist/leadcapture-form
 * Description: WordPress plugin that embeds LeadCapture.io forms via shortcode, Gutenberg block, and Elementor widget with lazy loading and popup trigger support.
 * Version: 1.1.0
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
 * @version 1.1.0
 * @author Silver Assist
 */

namespace LeadCaptureForm;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'LEADCAPTURE_FORM_VERSION', '1.1.0' );
define( 'LEADCAPTURE_FORM_FILE', __FILE__ );
define( 'LEADCAPTURE_FORM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'LEADCAPTURE_FORM_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'LEADCAPTURE_FORM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Load Composer autoloader for external packages and this plugin's own PSR-4-mapped classes.
if ( file_exists( LEADCAPTURE_FORM_PLUGIN_PATH . 'vendor/autoload.php' ) ) {
	require_once LEADCAPTURE_FORM_PLUGIN_PATH . 'vendor/autoload.php';
}

/**
 * Initialize the plugin on the "init" action rather than "plugins_loaded".
 *
 * WidgetsLoader::should_load() depends on did_action('elementor/loaded')
 * having already fired. Elementor typically fires that from its own
 * plugins_loaded callback, and plugin load order across a site isn't
 * guaranteed — evaluating should_load() during this plugin's own
 * plugins_loaded callback risks running before Elementor has announced
 * itself. By "init", every plugin's plugins_loaded callback has already
 * run, so the check is reliable. None of this plugin's components need
 * plugins_loaded-specific timing.
 */
\add_action(
	'init',
	static function () {
		Plugin::instance()->init();
	}
);
