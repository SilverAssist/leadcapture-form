<?php
/**
 * PHPUnit bootstrap file for LeadCapture Form plugin tests.
 *
 * Loads the WordPress test suite environment for real integration testing.
 *
 * @package LeadCaptureForm
 * @since 1.1.1
 */

// Composer autoloader for dependencies.
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Load Yoast PHPUnit Polyfills.
require_once dirname(__DIR__) . '/vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php';

// WordPress tests directory - can be configured via environment variable.
$_tests_dir = getenv('WP_TESTS_DIR');

if (!$_tests_dir) {
    $_tests_dir = rtrim(sys_get_temp_dir(), '/\\') . '/wordpress-tests-lib';
}

if (!file_exists("{$_tests_dir}/includes/functions.php")) {
    echo "\n";
    echo "========================================\n";
    echo "WordPress Test Suite Not Found\n";
    echo "========================================\n";
    echo "Location checked: {$_tests_dir}\n\n";
    echo "To install the WordPress test suite:\n\n";
    echo "  bash scripts/install-wp-tests.sh wordpress_test root '' localhost latest\n\n";
    echo "Or set WP_TESTS_DIR:\n\n";
    echo "  export WP_TESTS_DIR=/path/to/wordpress-tests-lib\n\n";
    exit(1);
}

// Give access to tests_add_filter() function.
require_once "{$_tests_dir}/includes/functions.php";

/**
 * Manually load the plugin being tested.
 *
 * @return void
 */
function _manually_load_plugin()
{
    require dirname(__DIR__) . '/leadcapture-form.php';
}

// Load the plugin before the WordPress test environment, matching production
// bootstrap timing (the plugin itself hooks its own init on "init").
tests_add_filter('muplugins_loaded', '_manually_load_plugin');

// Start up the WordPress testing environment.
require "{$_tests_dir}/includes/bootstrap.php";
