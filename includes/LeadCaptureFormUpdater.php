<?php

/**
 * LeadCapture Form Updater - GitHub Updates Integration
 *
 * Integrates the reusable silverassist/wp-github-updater package for automatic updates
 * from public GitHub releases. Provides seamless WordPress admin updates.
 *
 * @package LeadCaptureForm
 * @since 1.0.0
 * @author Silver Assist
 * @version 1.0.0
 */

namespace LeadCaptureForm;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use SilverAssist\WpGithubUpdater\Updater as GitHubUpdater;
use SilverAssist\WpGithubUpdater\UpdaterConfig;

/**
 * Class LeadCaptureFormUpdater
 *
 * Extends the reusable GitHub updater package with LeadCapture Form specific configuration.
 * This approach reduces code duplication and centralizes update logic maintenance.
 *
 * @since 1.0.0
 */
class LeadCaptureFormUpdater extends GitHubUpdater {

	/**
	 * Initialize the LeadCapture Form updater with specific configuration.
	 *
	 * @since 1.0.0
	 * @param string $plugin_file Path to main plugin file.
	 * @param string $github_repo GitHub repository (username/repository).
	 */
	public function __construct( string $plugin_file, string $github_repo ) {
		$config = new UpdaterConfig(
			$plugin_file,
			$github_repo,
			array(
				'plugin_name'        => 'LeadCapture Form',
				'plugin_description' => 'WordPress plugin that embeds LeadCapture.io forms via shortcode, Gutenberg block, and Elementor widget.',
				'plugin_author'      => 'Silver Assist',
				'plugin_homepage'    => "https://github.com/{$github_repo}",
				'requires_wordpress' => '6.5',
				'requires_php'       => '8.2',
				'asset_pattern'      => 'leadcapture-form-v{version}.zip',
				'cache_duration'     => 12 * 3600, // 12 hours.
				'ajax_action'        => 'leadcapture_check_version',
				'ajax_nonce'         => 'leadcapture_version_check',
			)
		);

		parent::__construct( $config );
	}
}
