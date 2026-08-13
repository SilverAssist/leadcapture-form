<?php
/**
 * Tests for the ShortcodeHandler component.
 *
 * @package LeadCaptureForm
 * @since 1.1.1
 */

namespace LeadCaptureForm\Tests\Unit;

use LeadCaptureForm\ShortcodeHandler;
use WP_UnitTestCase;

/**
 * Test case for ShortcodeHandler, using the real WordPress test environment.
 *
 * @since 1.1.1
 */
class ShortcodeHandlerTest extends WP_UnitTestCase
{
    /**
     * Test singleton instance creation.
     *
     * @return void
     */
    public function test_instance_returns_singleton(): void
    {
        $instance1 = ShortcodeHandler::instance();
        $instance2 = ShortcodeHandler::instance();

        $this->assertInstanceOf(ShortcodeHandler::class, $instance1);
        $this->assertSame($instance1, $instance2);
    }

    /**
     * Test that ShortcodeHandler implements the shared LoadableInterface.
     *
     * @return void
     */
    public function test_implements_loadable_interface(): void
    {
        $this->assertInstanceOf(
            \SilverAssist\PluginKernel\Interfaces\LoadableInterface::class,
            ShortcodeHandler::instance()
        );
    }

    /**
     * Test get_priority returns the Services-tier value.
     *
     * @return void
     */
    public function test_get_priority_returns_expected_value(): void
    {
        $this->assertSame(20, ShortcodeHandler::instance()->get_priority());
    }

    /**
     * Test should_load always returns true (no gating dependency).
     *
     * @return void
     */
    public function test_should_load_returns_true(): void
    {
        $this->assertTrue(ShortcodeHandler::instance()->should_load());
    }

    /**
     * Test init() registers the shortcode and enqueue hook.
     *
     * @return void
     */
    public function test_init_registers_shortcode_and_enqueue_hook(): void
    {
        $instance = ShortcodeHandler::instance();
        $instance->init();

        $this->assertTrue(shortcode_exists('leadcapture_form'));
        $this->assertGreaterThan(0, has_action('wp_enqueue_scripts', [$instance, 'enqueue_scripts']));
    }

    /**
     * Test render_shortcode returns an error message when no form token is provided.
     *
     * @return void
     */
    public function test_render_shortcode_errors_without_form_token(): void
    {
        $output = ShortcodeHandler::instance()->render_shortcode([]);

        $this->assertStringContainsString('leadcapture-form-error', $output);
        $this->assertStringContainsString('form-token', $output);
    }

    /**
     * Test render_shortcode renders the embed-mode container by default.
     *
     * @return void
     */
    public function test_render_shortcode_renders_embed_mode_by_default(): void
    {
        $output = ShortcodeHandler::instance()->render_shortcode(['form-token' => 'GLFT-TEST']);

        $this->assertStringContainsString('leadcapture-embed-mode', $output);
        $this->assertStringContainsString('data-form-token="GLFT-TEST"', $output);
        $this->assertStringContainsString('data-mode="embed"', $output);
    }

    /**
     * Test render_shortcode renders the popup-mode container with the trigger class.
     *
     * @return void
     */
    public function test_render_shortcode_renders_popup_mode_with_trigger_class(): void
    {
        $output = ShortcodeHandler::instance()->render_shortcode([
            'form-token' => 'GLFT-TEST',
            'mode' => 'popup',
            'trigger-class' => 'leadforms-trigger-EL',
        ]);

        $this->assertStringContainsString('leadcapture-popup-mode', $output);
        $this->assertStringContainsString('data-mode="popup"', $output);
        $this->assertStringContainsString('data-trigger-class="leadforms-trigger-EL"', $output);
    }
}
