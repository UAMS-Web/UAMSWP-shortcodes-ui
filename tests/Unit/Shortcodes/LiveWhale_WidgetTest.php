<?php

/**
 * LiveWhale calendar widget shortcode: the widget div, the loader script tag,
 * the custom class wrapper and the Shortcake arguments.
 *
 * @see inc/shortcodes/class-livewhale-widget.php
 */

declare(strict_types=1);

use UAMS_Shortcakes\Shortcodes\LiveWhale_Widget;

beforeEach(function (): void {
    stubWordPress();

    unset($GLOBALS['id'], $GLOBALS['customclasses']);
});

it('uses the lowercase class name, underscore included, as its shortcode tag', function (): void {
    expect(LiveWhale_Widget::get_shortcode_tag())->toBe('livewhale_widget');
});

it('renders the widget div with the id and the loader script', function (): void {
    $html = LiveWhale_Widget::callback(['id' => '7']);

    expect($html)
        ->toContain('<div class="lwcw" data-options="id=7&format=html"></div>')
        ->toContain('<script type="text/javascript" id="lw_lwcw" src="https://calendar.uams.edu/livewhale/theme/core/scripts/lwcw.js"></script>')
        ->not->toContain('<div class="Array">');
});

it('stores the id in a global', function (): void {
    LiveWhale_Widget::callback(['id' => '12']);

    expect($GLOBALS['id'])->toBe('12');
});

it('renders an empty id when none is given', function (): void {
    expect(LiveWhale_Widget::callback([]))->toContain('data-options="id=&format=html"');
});

it('falls back to the defaults when the attributes are not an array', function (): void {
    expect(LiveWhale_Widget::callback('', ''))->toContain('data-options="id=&format=html"');
});

it('does not reuse the id of an earlier call when the id is missing', function (): void {
    LiveWhale_Widget::callback(['id' => '3']);

    expect(LiveWhale_Widget::callback([]))->toContain('data-options="id=&format=html"')
        ->and($GLOBALS['id'])->toBe('');
});

it('leaves the customclasses global empty without a custom class', function (): void {
    LiveWhale_Widget::callback(['id' => '1']);

    expect($GLOBALS['customclasses'])->toBe([]);
});

it('wraps in a div carrying the sanitized custom classes', function (): void {
    $errors = [];
    set_error_handler(function (int $no, string $message) use (&$errors): bool {
        $errors[] = $message;

        return true;
    });

    try {
        $html = LiveWhale_Widget::callback(['id' => '1', 'customclass' => 'a,b<x>']);
    } finally {
        restore_error_handler();
    }

    expect($GLOBALS['customclasses'])->toBe(['a', 'bx'])
        ->and($html)->toStartWith('<div class="a bx">')
        ->and($html)->toEndWith('</div>')
        ->and($errors)->toBe([]);
});

it('has a public URL constant for the calendar host', function (): void {
    expect(LiveWhale_Widget::URL)->toBe('https://calendar.uams.edu');
});

it('describes its Shortcake attributes', function (): void {
    expect(array_column(LiveWhale_Widget::get_attributes(), 'attr'))->toBe(['id', 'customclass']);

    $args = LiveWhale_Widget::get_shortcode_ui_args();

    expect($args['label'])->toBe('UAMS Calendar Widget')
        ->and($args['listItemImage'])->toBe('dashicons-calendar')
        ->and($args['attrs'])->toBe(LiveWhale_Widget::get_attributes());
});
