<?php

/**
 * Button shortcode: the anchor `callback()` returns and the Shortcake arguments.
 *
 * @see inc/shortcodes/class-button.php
 */

declare(strict_types=1);

use UAMS_Shortcakes\Shortcodes\Button;

use function Brain\Monkey\Functions\stubs;
use function Brain\Monkey\Functions\when;

beforeEach(function (): void {
    stubWordPress();
    stubs(['esc_url' => fn ($url) => $url]);
});

/**
 * Attributes for a complete call.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function buttonAttrs(array $overrides = []): array
{
    return array_merge(['text' => 'Go', 'type' => 'btn-go', 'newline' => false], $overrides);
}

it('uses the lowercase class name as its shortcode tag', function (): void {
    expect(Button::get_shortcode_tag())->toBe('button');
});

it('renders a plain anchor with the default classes', function (): void {
    expect(Button::callback(buttonAttrs(['url' => 'https://example.test/', 'target' => '_blank'])))
        ->toBe('<a class="uams-btn btn   btn-go " href="https://example.test/" target="_blank">Go</a>');
});

it('adds color, size and icon type classes in that order', function (): void {
    expect(Button::callback(buttonAttrs(['color' => 'btn-red', 'size' => 'btn-lg', 'type' => 'btn-pdf'])))
        ->toContain('class="uams-btn btn btn-red btn-lg btn-pdf ');
});

it('adds each comma-separated custom class, sanitized, after the button classes', function (): void {
    expect(Button::callback(buttonAttrs(['customclass' => 'one,t wo!'])))
        ->toContain('class="uams-btn btn   btn-go one two"');
});

it('appends a line break when newline is truthy', function (): void {
    expect(Button::callback(buttonAttrs(['newline' => true])))->toEndWith('</a><br/>')
        ->and(Button::callback(buttonAttrs(['newline' => 'yes'])))->toEndWith('</a><br/>')
        ->and(Button::callback(buttonAttrs(['newline' => false])))->toEndWith('</a>')
        ->and(Button::callback(buttonAttrs(['newline' => '0'])))->toEndWith('</a>');
});

it('keeps a browsing-context target and drops any other', function (string $target, string $expected): void {
    expect(Button::callback(buttonAttrs(['target' => $target])))->toContain('target="'.$expected.'">');
})->with([
    ['_blank', '_blank'],
    ['_self', '_self'],
    ['_top', '_top'],
    ['_parent', '_parent'],
    ['_blank" x="y', ''],
    ['frame', ''],
    ['', ''],
]);

it('passes the button text through wp_kses_post', function (): void {
    when('wp_kses_post')->alias(fn ($content): string => strip_tags((string) $content));

    expect(Button::callback(buttonAttrs(['text' => '<b>Go</b><script>x</script>'])))
        ->toContain('>Gox</a>')
        ->not->toContain('<script>');
});

it('escapes the button classes', function (): void {
    expect(Button::callback(buttonAttrs(['color' => 'red" onclick="x'])))
        ->toContain('class="uams-btn btn red&quot; onclick=&quot;x ')
        ->not->toContain('red" onclick');
});

it('runs the url through esc_url', function (): void {
    when('esc_url')->alias(fn ($url): string => 'ESCAPED:'.$url);

    expect(Button::callback(buttonAttrs(['url' => 'https://example.test/x'])))->toContain('href="ESCAPED:https://example.test/x"');
});

it('renders an empty button without warnings when text, type and newline are omitted', function (): void {
    $messages = [];
    set_error_handler(function (int $no, string $str) use (&$messages): bool {
        $messages[] = $str;

        return true;
    });

    try {
        $html = Button::callback([]);
    } finally {
        restore_error_handler();
    }

    expect($html)->toBe('<a class="uams-btn btn    " href="" target=""></a>')
        ->and($messages)->toBe([]);
});

it('uses the defaults when the attributes are not an array', function (): void {
    $silence = fn (): bool => true;
    set_error_handler($silence);

    try {
        $html = Button::callback('');
    } finally {
        restore_error_handler();
    }

    expect($html)->toBe('<a class="uams-btn btn    " href="" target=""></a>');
});

it('describes its Shortcake attributes', function (): void {
    $args = Button::get_shortcode_ui_args();
    $attrs = array_column($args['attrs'], null, 'attr');

    expect(array_column(Button::get_attributes(), 'attr'))->toBe(['text', 'url', 'target', 'color', 'size', 'type', 'newline', 'customclass'])
        ->and($args['attrs'])->toBe(Button::get_attributes())
        ->and($args['label'])->toBe('UAMS Button')
        ->and($args['listItemImage'])->toBe('dashicons-plus-alt')
        ->and(array_keys($attrs['type']['options']))->toBe(['btn-go', 'btn-plus', 'btn-external', 'btn-play', 'btn-pdf'])
        ->and(array_keys($attrs['color']['options']))->toBe(['', 'btn-red', 'btn-gray', 'btn-green', 'btn-blue', 'btn-yellow']);
});
