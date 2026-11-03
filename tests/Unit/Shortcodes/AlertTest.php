<?php

/**
 * Alert shortcode: the markup `callback()` returns and the Shortcake arguments.
 *
 * @see inc/shortcodes/class-alert.php
 */

declare(strict_types=1);

use UAMS_Shortcakes\Shortcodes\Alert;

beforeEach(function (): void {
    stubWordPress();
});

it('uses the lowercase class name as its shortcode tag', function (): void {
    expect(Alert::get_shortcode_tag())->toBe('alert');
});

it('renders an info alert with the info icon by default', function (): void {
    $html = Alert::callback(['customclass' => ''], 'Body');

    expect($html)
        ->toContain('class="uams-alert alert alert-info ')
        ->toContain('role="alert"')
        ->toContain('<div class="alert-body"><p>Body</p></div>')
        ->not->toContain('data-dismiss');
});

it('falls back to info for a type that is not allowed', function (): void {
    expect(Alert::callback(['type' => 'bogus', 'customclass' => ''], 'x'))->toContain('alert-info');
});

it('keeps each allowed type', function (string $type): void {
    expect(Alert::callback(['type' => $type, 'customclass' => ''], 'x'))->toContain('alert-'.$type);
})->with(['success', 'info', 'help', 'warning', 'danger']);

it('adds a dismiss button when dismiss is set', function (): void {
    expect(Alert::callback(['dismiss' => true, 'customclass' => ''], 'x'))
        ->toContain('<button type="button" class="close" data-dismiss="alert"');
});

it('sanitizes each comma-separated custom class', function (): void {
    expect(Alert::callback(['customclass' => 'one,t wo!'], 'x'))->toContain('alert-info one two"');
});

it('describes its Shortcake attributes', function (): void {
    $attrs = array_column(Alert::get_attributes(), 'attr');

    expect($attrs)->toBe(['type', 'dismiss', 'customclass'])
        ->and(Alert::get_shortcode_ui_args()['attrs'])->toBe(Alert::get_attributes());
});
