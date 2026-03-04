<?php

/**
 * LeadCapture Form Admin Page - Plugin Settings and Update Status
 *
 * Provides admin interface for plugin usage information and update status display.
 * Integrates with SilverAssist Settings Hub for centralized admin interface.
 *
 * @package LeadCaptureForm
 * @since 1.0.0
 * @author Silver Assist
 * @version 1.0.0
 */

namespace LeadCaptureForm;

use SilverAssist\SettingsHub\SettingsHub;

// Prevent direct access.
if (!defined("ABSPATH")) {
    exit;
}

/**
 * Class LeadCaptureFormAdmin
 *
 * Manages the admin page for the LeadCapture Form plugin.
 * Provides usage instructions and integrates with Settings Hub.
 *
 * @since 1.0.0
 */
class LeadCaptureFormAdmin
{
    /**
     * Plugin updater instance.
     *
     * @since 1.0.0
     * @var LeadCaptureFormUpdater
     */
    private LeadCaptureFormUpdater $updater;

    /**
     * Initialize admin functionality.
     *
     * @since 1.0.0
     * @param LeadCaptureFormUpdater $updater Updater instance.
     */
    public function __construct(LeadCaptureFormUpdater $updater)
    {
        $this->updater = $updater;
        $this->init_hooks();
    }

    /**
     * Initialize WordPress hooks.
     *
     * @since 1.0.0
     * @return void
     */
    private function init_hooks(): void
    {
        \add_action("admin_menu", [$this, "register_with_hub"], 4);
        \add_action("admin_enqueue_scripts", [$this, "enqueue_admin_scripts"]);
    }

    /**
     * Register plugin with Settings Hub.
     *
     * Falls back to a standalone settings page if Settings Hub is unavailable.
     *
     * @since 1.0.0
     * @return void
     */
    public function register_with_hub(): void
    {
        if (!\class_exists(SettingsHub::class)) {
            $this->add_standalone_menu();
            return;
        }

        try {
            $hub = SettingsHub::get_instance();

            $actions = [];
            $actions[] = [
                "label" => \__("Check Updates", "leadcapture-form"),
                "callback" => [$this, "render_update_check_script"],
                "class" => "button",
            ];

            $hub->register_plugin(
                "leadcapture-form",
                \__("LeadCapture Form", "leadcapture-form"),
                [$this, "admin_page"],
                [
                    "description" => \__("Shortcode, Gutenberg block and Elementor widget for LeadCapture.io forms with lazy loading and popup support.", "leadcapture-form"),
                    "version" => LEADCAPTURE_FORM_VERSION,
                    "tab_title" => \__("LeadCapture", "leadcapture-form"),
                    "capability" => "manage_options",
                    "plugin_file" => LEADCAPTURE_FORM_FILE,
                    "actions" => $actions,
                ]
            );
        } catch (\Exception $e) {
            \error_log("LeadCapture Form - Settings Hub registration failed: " . $e->getMessage());
            $this->add_standalone_menu();
        }
    }

    /**
     * Fallback: Register standalone menu when Settings Hub unavailable.
     *
     * @since 1.0.0
     * @return void
     */
    private function add_standalone_menu(): void
    {
        \add_options_page(
            \__("LeadCapture Form", "leadcapture-form"),
            \__("LeadCapture", "leadcapture-form"),
            "manage_options",
            "leadcapture-form",
            [$this, "admin_page"]
        );
    }

    /**
     * Render update check script for Settings Hub action button.
     *
     * Delegates to wp-github-updater's built-in enqueueCheckUpdatesScript() which
     * provides centralized JS, AJAX handling, admin notices, and auto-redirect.
     *
     * @since 1.0.0
     * @return void
     */
    public function render_update_check_script(): void
    {
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Inline JavaScript from wp-github-updater.
        echo $this->updater->enqueueCheckUpdatesScript();
    }

    /**
     * Enqueue admin scripts.
     *
     * @since 1.0.0
     * @param string $hook Current admin page hook.
     * @return void
     */
    public function enqueue_admin_scripts(string $hook): void
    {
        $allowed_hooks = [
            "settings_page_leadcapture-form",
            "silver-assist_page_leadcapture-form",
            "toplevel_page_leadcapture-form",
        ];

        if (!\in_array($hook, $allowed_hooks, true)) {
            return;
        }

        \wp_enqueue_style(
            "leadcapture-admin-css",
            LEADCAPTURE_FORM_PLUGIN_URL . "assets/css/admin-settings.css",
            [],
            LEADCAPTURE_FORM_VERSION
        );
    }

    /**
     * Render admin page.
     *
     * Displays usage instructions and integration details.
     *
     * @since 1.0.0
     * @return void
     */
    public function admin_page(): void
    {
        if (!\current_user_can("manage_options")) {
            return;
        }

        ?>
        <div class="wrap leadcapture-admin">

            <div class="leadcapture-settings-grid">

                <!-- Plugin Usage Card -->
                <div class="status-card">
                    <div class="card-header">
                        <span class="dashicons dashicons-editor-code"></span>
                        <h3><?php \esc_html_e("Plugin Usage", "leadcapture-form"); ?></h3>
                    </div>
                    <div class="card-content">
                        <h3><?php \esc_html_e("Shortcode — Embed Mode", "leadcapture-form"); ?></h3>
                        <p><?php \esc_html_e("Embed a form inline on the page:", "leadcapture-form"); ?></p>
                        <code>[leadcapture_form form-token="GLFT-XXXXXXXXXXXXXXXXXXXXXXX"]</code>

                        <p><?php \esc_html_e("With custom placeholder height:", "leadcapture-form"); ?></p>
                        <code>[leadcapture_form form-token="GLFT-XXX" height="800px"]</code>

                        <h3><?php \esc_html_e("Shortcode — Popup Mode", "leadcapture-form"); ?></h3>
                        <p><?php \esc_html_e("Trigger a popup form on button click:", "leadcapture-form"); ?></p>
                        <code>[leadcapture_form form-token="GLFT-XXX" mode="popup" trigger-class="leadforms-trigger-EL"]</code>

                        <p class="description">
                            <?php \esc_html_e("Get your form token and trigger class from LeadCapture.io → Settings → Publish.", "leadcapture-form"); ?>
                        </p>

                        <h3><?php \esc_html_e("Gutenberg Block", "leadcapture-form"); ?></h3>
                        <p>
                            <?php \esc_html_e("Search for 'LeadCapture Form' in the block editor. Configure form token and mode in the sidebar.", "leadcapture-form"); ?>
                        </p>

                        <h3><?php \esc_html_e("Elementor Widget", "leadcapture-form"); ?></h3>
                        <p>
                            <?php \esc_html_e("Drag 'LeadCapture Form' from the 'LeadCapture' category in Elementor.", "leadcapture-form"); ?>
                        </p>
                    </div>
                </div>

                <!-- How Updates Work Card -->
                <div class="status-card">
                    <div class="card-header">
                        <span class="dashicons dashicons-update"></span>
                        <h3><?php \esc_html_e("How Updates Work", "leadcapture-form"); ?></h3>
                    </div>
                    <div class="card-content">
                        <ul class="feature-list">
                            <li><?php \esc_html_e("WordPress automatically checks for updates every 12 hours.", "leadcapture-form"); ?></li>
                            <li><?php \esc_html_e("Notifications appear in the Plugins page when updates are available.", "leadcapture-form"); ?></li>
                            <li><?php \esc_html_e("Updates are downloaded directly from GitHub releases.", "leadcapture-form"); ?></li>
                            <li><?php \esc_html_e("Settings and data are preserved during updates.", "leadcapture-form"); ?></li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
        <?php
    }
}
