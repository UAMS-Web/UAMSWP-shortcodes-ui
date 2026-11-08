<?php

use Brain\Monkey;
use PHPUnit\Framework\TestCase;

/**
 * UAMS_Shortcakes methods that need no WordPress behavior.
 *
 * @see inc/class-uams-shortcodes.php
 */
final class ShortcakesTest extends TestCase
{
    private UAMS_Shortcakes $shortcakes;

    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();

        // get_instance() would register hooks and enqueue assets, so build the
        // object without it. No shortcode class is registered on it.
        $this->shortcakes = (new ReflectionClass(UAMS_Shortcakes::class))->newInstanceWithoutConstructor();
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }

    public function test_content_passes_through_the_kses_filter_when_no_shortcode_is_registered(): void
    {
        $content = '<p>[button]Apply[/button]</p>';

        $this->assertSame($content, $this->shortcakes->filter_pre_kses($content));
    }

    public function test_an_unregistered_shortcode_renders_nothing(): void
    {
        $this->assertSame('', $this->shortcakes->do_shortcode_callback([], '', 'not-registered'));
    }
}
