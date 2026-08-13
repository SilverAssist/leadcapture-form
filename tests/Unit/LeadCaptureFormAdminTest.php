<?php
/**
 * Tests for the LeadCaptureFormAdmin component.
 *
 * @package LeadCaptureForm
 * @since 1.1.1
 */

namespace LeadCaptureForm\Tests\Unit;

use LeadCaptureForm\LeadCaptureFormAdmin;
use WP_UnitTestCase;

/**
 * Test case for LeadCaptureFormAdmin, using the real WordPress test environment.
 *
 * @since 1.1.1
 */
class LeadCaptureFormAdminTest extends WP_UnitTestCase
{
    /**
     * Test singleton instance creation.
     *
     * @return void
     */
    public function test_instance_returns_singleton(): void
    {
        $instance1 = LeadCaptureFormAdmin::instance();
        $instance2 = LeadCaptureFormAdmin::instance();

        $this->assertInstanceOf(LeadCaptureFormAdmin::class, $instance1);
        $this->assertSame($instance1, $instance2);
    }

    /**
     * Test that LeadCaptureFormAdmin implements the shared LoadableInterface.
     *
     * @return void
     */
    public function test_implements_loadable_interface(): void
    {
        $this->assertInstanceOf(
            \SilverAssist\PluginKernel\Interfaces\LoadableInterface::class,
            LeadCaptureFormAdmin::instance()
        );
    }

    /**
     * Test get_priority returns the Admin-tier value.
     *
     * @return void
     */
    public function test_get_priority_returns_expected_value(): void
    {
        $this->assertSame(30, LeadCaptureFormAdmin::instance()->get_priority());
    }

    /**
     * Test should_load tracks is_admin(), both outside and inside admin context.
     *
     * @return void
     */
    public function test_should_load_tracks_is_admin(): void
    {
        $this->assertFalse(is_admin(), 'Precondition: this test starts outside admin context');
        $this->assertFalse(LeadCaptureFormAdmin::instance()->should_load());

        set_current_screen('dashboard');

        try {
            $this->assertTrue(is_admin(), 'Precondition: set_current_screen() should switch to admin context');
            $this->assertTrue(LeadCaptureFormAdmin::instance()->should_load());
        } finally {
            set_current_screen('front');
        }
    }

    /**
     * Test init() registers the admin_menu and admin_enqueue_scripts hooks.
     *
     * @return void
     */
    public function test_init_registers_admin_hooks(): void
    {
        $instance = LeadCaptureFormAdmin::instance();
        $instance->init();

        $this->assertSame(4, has_action('admin_menu', [$instance, 'register_with_hub']));
        $this->assertGreaterThan(0, has_action('admin_enqueue_scripts', [$instance, 'enqueue_admin_scripts']));
    }

    /**
     * Test admin_page() renders nothing for users without manage_options.
     *
     * Unlike a wp_die()-based gate, this method just returns early, so we
     * assert on captured output rather than expecting an exception.
     *
     * @return void
     */
    public function test_admin_page_renders_nothing_without_capability(): void
    {
        ob_start();
        LeadCaptureFormAdmin::instance()->admin_page();
        $output = ob_get_clean();

        $this->assertSame('', $output);
    }
}
