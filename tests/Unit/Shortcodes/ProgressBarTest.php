<?php

/**
 * ProgressBar shortcode: completion value, style variants, type and classes.
 *
 * @see inc/shortcodes/class-progressbar.php
 */

declare(strict_types=1);

use UAMS_Shortcakes\Shortcodes\ProgressBar;

beforeEach(function (): void {
    stubWordPress();
});

/**
 * Render with a complete value and the empty custom class the tests do not care about.
 *
 * @param  array<string, string>  $attrs
 */
function renderProgressBar(array $attrs = []): string
{
    return ProgressBar::callback(array_merge(['complete' => '40', 'customclass' => ''], $attrs));
}

it('uses the lowercase class name as its shortcode tag', function (): void {
    expect(ProgressBar::get_shortcode_tag())->toBe('progressbar');
});

it('renders the default style with a screen-reader-only value', function (): void {
    $html = (string) preg_replace('/>\s+</', '><', trim(renderProgressBar()));

    expect($html)->toBe(
        '<div class="progress  ">'
        .'<div class="progress-bar progress-bar-default" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: 40%;">'
        .'<span class="sr-only">40%</span><span class="sr-only">Complete</span>'
        .'</div></div>'
    );
});

it('shows the value visibly for the display-value style', function (): void {
    expect(renderProgressBar(['style' => 'display-value']))->toContain('<span class="">40%</span>');
});

it('adds the striped classes for the striped styles', function (string $style): void {
    expect(renderProgressBar(['style' => $style]))
        ->toContain('<div class="progress '.$style.' ">')
        ->toContain('<span class="sr-only">40%</span>');
})->with(['progress-striped', 'progress-striped active']);

it('treats an unknown style as the default look', function (): void {
    expect(renderProgressBar(['style' => 'bogus']))
        ->toContain('<div class="progress  ">')
        ->toContain('<span class="sr-only">40%</span>');
});

it('accepts digit strings for the completion value', function (string $value): void {
    expect(renderProgressBar(['complete' => $value]))
        ->toContain('aria-valuenow="'.$value.'"')
        ->toContain('width: '.$value.'%;');
})->with(['0', '100', '75']);

it('defaults the completion value to 50 when it is not all digits', function (string $value): void {
    expect(renderProgressBar(['complete' => $value]))
        ->toContain('aria-valuenow="50"')
        ->toContain('width: 50%;');
})->with(['', 'abc', '12.5', '-3', '40%']);

it('does not clamp a completion value above 100 (current behavior)', function (): void {
    expect(renderProgressBar(['complete' => '250']))->toContain('aria-valuenow="250"');
});

it('prints the raw type attribute rather than the validated one (current behavior)', function (): void {
    expect(renderProgressBar(['type' => 'progress-bar-success']))->toContain('class="progress-bar progress-bar-success"')
        ->and(renderProgressBar(['type' => 'bogus" onclick="x']))->toContain('class="progress-bar bogus" onclick="x"');
});

it('prints an empty type as empty (current behavior)', function (): void {
    expect(renderProgressBar(['type' => '']))->toContain('class="progress-bar "');
});

it('prints the default progress-bar-default type when none is given', function (): void {
    expect(renderProgressBar())->toContain('class="progress-bar progress-bar-default"');
});

it('sanitizes each comma-separated custom class', function (): void {
    expect(renderProgressBar(['customclass' => 'one,t wo!']))->toContain('<div class="progress  one two">');
});

it('describes its Shortcake attributes', function (): void {
    $args = ProgressBar::get_shortcode_ui_args();
    $attrs = ProgressBar::get_attributes();

    expect($args['label'])->toBe('Progress Bar')
        ->and($args['attrs'])->toBe($attrs)
        ->and($args['listItemImage'])->toBe('<img src="https://example.test/wp-content/plugins/uamswp-shortcodes/admin/images/icon-progressbar.png" />')
        ->and(array_column($attrs, 'attr'))->toBe(['complete', 'type', 'style', 'customclass'])
        ->and(array_keys($attrs[1]['options']))->toBe(['progress-bar-primary', 'progress-bar-success', 'progress-bar-info', 'progress-bar-warning', 'progress-bar-danger'])
        ->and(array_keys($attrs[2]['options']))->toBe(['default', 'display-value', 'progress-striped', 'progress-striped active']);
});
