<?php
/**
 * Main Plugin Class
 *
 * Handles plugin initialization and coordinates between components.
 *
 * @package LeadCaptureForm
 * @since 1.1.0
 * @version 1.1.0
 * @author Silver Assist
 */

namespace LeadCaptureForm;

use LeadCaptureForm\Block\LeadCaptureFormBlock;
use LeadCaptureForm\Elementor\WidgetsLoader;
use SilverAssist\PluginKernel\AbstractPlugin;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Plugin
 *
 * Singleton access (instance()) and the priority-ordered component loading
 * loop are inherited from AbstractPlugin (silverassist/wp-plugin-kernel) —
 * this class only declares which components to load (get_components()) and
 * the plugin-specific setup that runs alongside them (init_hooks()).
 *
 * Extracted from the plugin's former monolithic LeadCapture_Form class,
 * which lived directly in the main plugin file.
 *
 * @since 1.1.0
 */
class Plugin extends AbstractPlugin {

	/**
	 * Updater instance
	 *
	 * @var LeadCaptureFormUpdater|null
	 */
	private ?LeadCaptureFormUpdater $updater = null;

	/**
	 * List the component classes this plugin loads
	 *
	 * Loading order is determined by each component's get_priority(), not
	 * by the order they're listed here.
	 *
	 * @since 1.1.0
	 * @return array<class-string>
	 */
	protected function get_components(): array {
		return array(
			ShortcodeHandler::class,
			LeadCaptureFormBlock::class,
			WidgetsLoader::class,
			LeadCaptureFormAdmin::class,
		);
	}

	/**
	 * Plugin-level setup that isn't itself a LoadableInterface component
	 *
	 * Runs after all components have loaded.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	protected function init_hooks(): void {
		$this->load_textdomain();
		$this->init_updater();
	}

	/**
	 * Load plugin textdomain for translations
	 *
	 * @since 1.1.0
	 * @return void
	 */
	private function load_textdomain(): void {
		\load_plugin_textdomain(
			'leadcapture-form',
			false,
			\dirname( LEADCAPTURE_FORM_PLUGIN_BASENAME ) . '/languages'
		);
	}

	/**
	 * Initialize GitHub updater
	 *
	 * Sets up automatic updates from GitHub releases. Admin-only, matching
	 * LeadCaptureFormAdmin's should_load() gate — the updater's only
	 * consumer is that admin page's "Check Updates" action.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	private function init_updater(): void {
		if ( ! \is_admin() ) {
			return;
		}

		if ( ! \class_exists( LeadCaptureFormUpdater::class ) ) {
			return;
		}

		// Public repository - no authentication required.
		$this->updater = new LeadCaptureFormUpdater( LEADCAPTURE_FORM_FILE, 'SilverAssist/leadcapture-form' );
	}

	/**
	 * Get Updater instance
	 *
	 * @since 1.1.0
	 * @return LeadCaptureFormUpdater|null
	 */
	public function get_updater(): ?LeadCaptureFormUpdater {
		return $this->updater;
	}
}
