<?php

/**
 * UAMS_Shortcakes methods that need no WordPress behavior.
 *
 * @see inc/class-uams-shortcodes.php
 */

declare(strict_types=1);

beforeEach(function (): void {
    // get_instance() would register hooks and enqueue assets, so build the
    // object without it. No shortcode class is registered on it.
    $this->shortcakes = (new ReflectionClass(UAMS_Shortcakes::class))->newInstanceWithoutConstructor();
});

it('passes content through the kses filter when no shortcode is registered', function (): void {
    $content = '<p>[button]Apply[/button]</p>';

    expect($this->shortcakes->filter_pre_kses($content))->toBe($content);
});

it('renders nothing for an unregistered shortcode', function (): void {
    expect($this->shortcakes->do_shortcode_callback([], '', 'not-registered'))->toBe('');
});
