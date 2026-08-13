<?php
/**
 * Tests for the LeadCaptureFormBlock component.
 *
 * @package LeadCaptureForm
 * @since 1.1.1
 */

namespace LeadCaptureForm\Tests\Unit;

use LeadCaptureForm\Block\LeadCaptureFormBlock;
use LeadCaptureForm\ShortcodeHandler;
use WP_UnitTestCase;

/**
 * Test case for LeadCaptureFormBlock, using the real WordPress test environment.
 *
 * @since 1.1.1
 */
class LeadCaptureFormBlockTest extends WP_UnitTestCase
{
    /**
     * Test singleton instance creation.
     *
     * @return void
     */
    public function test_instance_returns_singleton(): void
    {
        $instance1 = LeadCaptureFormBlock::instance();
        $instance2 = LeadCaptureFormBlock::instance();

        $this->assertInstanceOf(LeadCaptureFormBlock::class, $instance1);
        $this->assertSame($instance1, $instance2);
    }

    /**
     * Test the deprecated get_instance() alias forwards to instance().
     *
     * @return void
     */
    public function test_deprecated_get_instance_forwards_to_instance(): void
    {
        $this->assertSame(LeadCaptureFormBlock::instance(), LeadCaptureFormBlock::get_instance());
    }

    /**
     * Test that LeadCaptureFormBlock implements the shared LoadableInterface.
     *
     * @return void
     */
    public function test_implements_loadable_interface(): void
    {
        $this->assertInstanceOf(
            \SilverAssist\PluginKernel\Interfaces\LoadableInterface::class,
            LeadCaptureFormBlock::instance()
        );
    }

    /**
     * Test get_priority returns the Services-tier value.
     *
     * @return void
     */
    public function test_get_priority_returns_expected_value(): void
    {
        $this->assertSame(20, LeadCaptureFormBlock::instance()->get_priority());
    }

    /**
     * Test should_load always returns true (no gating dependency).
     *
     * @return void
     */
    public function test_should_load_returns_true(): void
    {
        $this->assertTrue(LeadCaptureFormBlock::instance()->should_load());
    }

    /**
     * Test init() registers the block registration and editor asset hooks.
     *
     * @return void
     */
    public function test_init_registers_hooks(): void
    {
        $instance = LeadCaptureFormBlock::instance();
        $instance->init();

        $this->assertGreaterThan(0, has_action('init', [$instance, 'register_block']));
        $this->assertGreaterThan(0, has_action('enqueue_block_editor_assets', [$instance, 'enqueue_block_editor_assets']));
    }

    /**
     * Test render_block returns an error message when no form token is provided.
     *
     * @return void
     */
    public function test_render_block_errors_without_form_token(): void
    {
        $output = LeadCaptureFormBlock::instance()->render_block([]);

        $this->assertStringContainsString('leadcapture-form-error', $output);
    }

    /**
     * Test render_block renders the shortcode-backed container with a form token.
     *
     * @return void
     */
    public function test_render_block_renders_container_with_form_token(): void
    {
        // render_block() delegates to do_shortcode(), which needs the
        // shortcode registered regardless of ShortcodeHandlerTest's run order.
        ShortcodeHandler::instance()->init();

        $output = LeadCaptureFormBlock::instance()->render_block(['formToken' => 'GLFT-BLOCK']);

        $this->assertStringContainsString('data-form-token="GLFT-BLOCK"', $output);
    }
}
