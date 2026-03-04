<?php

/**
 * LeadCapture Form - Elementor Widgets Loader
 *
 * This class handles the registration and loading of Elementor widgets
 * for the LeadCapture Form plugin. It ensures widgets are only loaded
 * when Elementor is active and provides proper integration.
 *
 * @package LeadCaptureForm\Elementor
 * @version 1.0.0
 * @since 1.0.0
 * @author Silver Assist
 */

namespace LeadCaptureForm\Elementor;

defined( 'ABSPATH' ) || exit;

use Elementor\Plugin;
use Elementor\Elements_Manager;
use Elementor\Widgets_Manager;

/**
 * Elementor Widgets Loader
 *
 * Manages the registration of custom Elementor widgets for LeadCapture forms.
 * Implements singleton pattern for consistent integration and handles
 * widget categories, scripts, and styles registration.
 *
 * @since 1.0.0
 */
class WidgetsLoader {

	/**
	 * Single instance of the widgets loader.
	 *
	 * @since 1.0.0
	 * @var WidgetsLoader|null
	 */
	private static ?WidgetsLoader $instance = null;

	/**
	 * Get the single instance of the widgets loader.
	 *
	 * @since 1.0.0
	 * @return WidgetsLoader The single instance.
	 */
	public static function get_instance(): WidgetsLoader {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Private constructor to prevent direct instantiation.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		if ( ! $this->is_elementor_loaded() ) {
			return;
		}

		$this->init_hooks();
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
	 * Check if Elementor is loaded.
	 *
	 * @since 1.0.0
	 * @return bool True if Elementor is loaded.
	 */
	private function is_elementor_loaded(): bool {
		return \did_action( 'elementor/loaded' );
	}

	/**
	 * Initialize WordPress hooks for Elementor integration.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function init_hooks(): void {
		\add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );
		\add_action( 'elementor/elements/categories_registered', array( $this, 'register_widget_category' ) );
		\add_action( 'elementor/frontend/after_register_scripts', array( $this, 'register_frontend_scripts' ) );
	}

	/**
	 * Get the list of available widgets.
	 *
	 * @since 1.0.0
	 * @return array Widget key => class name mapping.
	 */
	public static function get_widget_list(): array {
		return array(
			'leadcapture-form' => 'LeadCaptureFormWidget',
		);
	}

	/**
	 * Include widget PHP files.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function include_widget_files(): void {
		$widget_list = self::get_widget_list();

		foreach ( $widget_list as $widget_key => $widget_class ) {
			$widget_file = LEADCAPTURE_FORM_PLUGIN_PATH . "includes/elementor/widgets/{$widget_class}.php";
			if ( \file_exists( $widget_file ) ) {
				require_once $widget_file;
			}
		}
	}

	/**
	 * Register custom widget category in Elementor.
	 *
	 * @since 1.0.0
	 * @param Elements_Manager $elements_manager Elementor elements manager.
	 * @return void
	 */
	public function register_widget_category( Elements_Manager $elements_manager ): void {
		$elements_manager->add_category(
			'leadcapture-forms',
			array(
				'title' => __( 'LeadCapture', 'leadcapture-form' ),
				'icon'  => 'eicon-form-horizontal',
			)
		);
	}

	/**
	 * Register widgets with Elementor.
	 *
	 * @since 1.0.0
	 * @param Widgets_Manager $widgets_manager Elementor widgets manager.
	 * @return void
	 */
	public function register_widgets( Widgets_Manager $widgets_manager ): void {
		$this->include_widget_files();

		$widget_list = self::get_widget_list();

		foreach ( $widget_list as $widget_key => $widget_class ) {
			$widget_class_name = "LeadCaptureForm\\Elementor\\Widgets\\{$widget_class}";

			if ( \class_exists( $widget_class_name ) ) {
				$widgets_manager->register( new $widget_class_name() );
			}
		}
	}

	/**
	 * Register frontend scripts for Elementor widgets.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_frontend_scripts(): void {
		\wp_register_style(
			'leadcapture-elementor-css',
			LEADCAPTURE_FORM_PLUGIN_URL . 'assets/css/leadcapture-elementor.css',
			array( 'leadcapture-form-css' ),
			LEADCAPTURE_FORM_VERSION
		);
	}
}
