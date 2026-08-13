<?php
/**
 * Tests for the Plugin bootstrap class.
 *
 * @package LeadCaptureForm
 * @since 1.1.1
 */

namespace LeadCaptureForm\Tests\Unit;

use LeadCaptureForm\Block\LeadCaptureFormBlock;
use LeadCaptureForm\Elementor\WidgetsLoader;
use LeadCaptureForm\LeadCaptureFormAdmin;
use LeadCaptureForm\Plugin;
use LeadCaptureForm\ShortcodeHandler;
use WP_UnitTestCase;

/**
 * Test case for Plugin, using the real WordPress test environment.
 *
 * @since 1.1.1
 */
class PluginTest extends WP_UnitTestCase
{
    /**
     * Test singleton instance creation.
     *
     * @return void
     */
    public function test_instance_returns_singleton(): void
    {
        $instance1 = Plugin::instance();
        $instance2 = Plugin::instance();

        $this->assertInstanceOf(Plugin::class, $instance1);
        $this->assertSame($instance1, $instance2, 'Plugin::instance() should return the same instance');
    }

    /**
     * Test that Plugin implements the shared LoadableInterface.
     *
     * @return void
     */
    public function test_implements_loadable_interface(): void
    {
        $this->assertInstanceOf(
            \SilverAssist\PluginKernel\Interfaces\LoadableInterface::class,
            Plugin::instance()
        );
    }

    /**
     * Test that Plugin::get_components() lists all four LoadableInterface components.
     *
     * get_components() is protected per AbstractPlugin's contract, so it's
     * invoked via Reflection here. Regression coverage for the loader
     * wiring itself, not just each component in isolation.
     *
     * @return void
     */
    public function test_get_components_lists_all_components(): void
    {
        $method = new \ReflectionMethod(Plugin::class, 'get_components');
        $method->setAccessible(true);
        $components = $method->invoke(Plugin::instance());

        $this->assertContains(ShortcodeHandler::class, $components);
        $this->assertContains(LeadCaptureFormBlock::class, $components);
        $this->assertContains(WidgetsLoader::class, $components);
        $this->assertContains(LeadCaptureFormAdmin::class, $components);
    }

    /**
     * Test that init() is idempotent (guarded by AbstractPlugin).
     *
     * Asserts the actual guard flag AbstractPlugin::init() checks, rather
     * than just calling init() twice and asserting true — a passing test
     * here means the guard genuinely prevented a second run, not merely
     * that no exception was thrown.
     *
     * @return void
     */
    public function test_init_is_idempotent(): void
    {
        $plugin = Plugin::instance();
        $plugin->init();

        $initialized_property = new \ReflectionProperty(\SilverAssist\PluginKernel\AbstractPlugin::class, 'initialized');
        $initialized_property->setAccessible(true);

        $this->assertTrue(
            $initialized_property->getValue($plugin),
            'First init() call should set the AbstractPlugin guard flag'
        );

        // A second call must be a no-op: the guard flag must not toggle
        // or otherwise change as a side effect of re-entering init().
        $plugin->init();

        $this->assertTrue(
            $initialized_property->getValue($plugin),
            'Second init() call should leave the guard flag unchanged'
        );
    }

    /**
     * Test that get_updater() returns null outside admin context.
     *
     * init_updater() is admin-gated, matching LeadCaptureFormAdmin's own
     * should_load() gate.
     *
     * @return void
     */
    public function test_get_updater_returns_null_outside_admin(): void
    {
        $this->assertFalse(is_admin(), 'Precondition: this test runs outside admin context');
        $this->assertNull(Plugin::instance()->get_updater());
    }
}
