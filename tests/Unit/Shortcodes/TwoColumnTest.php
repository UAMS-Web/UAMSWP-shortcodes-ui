<?php

/**
 * TwoColumn shortcode: ratio classes, column content and custom classes.
 *
 * @see inc/shortcodes/class-twocolumn.php
 */

declare(strict_types=1);

use UAMS_Shortcakes\Shortcodes\TwoColumn;

use function Brain\Monkey\Functions\when;

beforeEach(function (): void {
    stubWordPress();
});

/**
 * Collapse the whitespace between tags so markup can be compared compactly.
 */
function twoColumnCompact(string $html): string
{
    return (string) preg_replace('/>\s+</', '><', trim($html));
}

it('uses the lowercase class name as its shortcode tag', function (): void {
    expect(TwoColumn::get_shortcode_tag())->toBe('twocolumn');
});

it('renders two half-width columns by default', function (): void {
    $html = twoColumnCompact(TwoColumn::callback(['col1' => 'A', 'col2' => 'B', 'customclass' => '']));

    expect($html)->toBe(
        '<div class="row "><div class="inner-container">'
        .'<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12"><p>A</p></div>'
        .'<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12"><p>B</p></div>'
        .'</div></div>'
    );
});

it('maps each ratio to its column classes', function (string $ratio, string $first, string $second): void {
    $html = twoColumnCompact(TwoColumn::callback(['ratio' => $ratio, 'col1' => 'A', 'col2' => 'B', 'customclass' => '']));

    expect($html)
        ->toContain('<div class="col-lg-'.$first.' col-md-'.$first.' col-sm-'.$first.' col-xs-12"><p>A</p>')
        ->toContain('<div class="col-lg-'.$second.' col-md-'.$second.' col-sm-'.$second.' col-xs-12"><p>B</p>');
})->with([
    ['half-half', '6', '6'],
    ['third-twothirds', '4', '8'],
    ['quarter-threequarters', '3', '9'],
    ['twothirds-third', '8', '4'],
    ['threequarters-quarter', '9', '3'],
    ['bogus', '6', '6'],
]);

it('url-decodes column content', function (): void {
    expect(TwoColumn::callback(['col1' => 'a%20b%3Cb%3E', 'col2' => '', 'customclass' => '']))->toContain('<p>a b<b></p>');
});

it('passes column content through do_shortcode', function (): void {
    when('do_shortcode')->alias(fn ($content): string => strtoupper($content));

    expect(TwoColumn::callback(['col1' => 'a', 'col2' => 'b', 'customclass' => '']))->toContain('<p>A</p>')->toContain('<p>B</p>');
});

it('runs each column through wp_kses_post', function (): void {
    when('wp_kses_post')->alias(fn ($content): string => strip_tags((string) $content));

    expect(TwoColumn::callback(['col1' => '<script>x</script>', 'col2' => '', 'customclass' => '']))
        ->toContain('<p>x</p>')
        ->not->toContain('<script>');
});

it('url-decodes a column before wp_kses_post sees it', function (): void {
    when('wp_kses_post')->alias(fn ($content): string => strip_tags((string) $content));

    expect(TwoColumn::callback(['col1' => '%3Cscript%3Ex%3C/script%3E', 'col2' => '', 'customclass' => '']))
        ->toContain('<p>x</p>')
        ->not->toContain('<script>');
});

it('sanitizes each comma-separated custom class', function (): void {
    expect(TwoColumn::callback(['customclass' => 'one,t wo!', 'col1' => '', 'col2' => '']))->toContain('<div class="row one two">');
});

it('renders empty columns when no content is supplied', function (): void {
    $html = twoColumnCompact(TwoColumn::callback(['col1' => '', 'col2' => '', 'customclass' => '']));

    expect($html)->toContain('<p></p></div><div')
        ->and(substr_count($html, '<p></p>'))->toBe(2);
});

it('emits a deprecation when a column or the custom class is missing (current behavior)', function (): void {
    $messages = [];
    set_error_handler(function (int $no, string $str) use (&$messages): bool {
        $messages[] = $str;

        return true;
    }, E_DEPRECATED);

    try {
        TwoColumn::callback([]);
    } finally {
        restore_error_handler();
    }

    expect($messages)->not->toBeEmpty();
});

it('describes its Shortcake attributes', function (): void {
    $args = TwoColumn::get_shortcode_ui_args();
    $attrs = TwoColumn::get_attributes();

    expect($args['label'])->toBe('Row, 2 Columns')
        ->and($args['attrs'])->toBe($attrs)
        ->and($args['listItemImage'])->toBe('<img src="https://example.test/wp-content/plugins/uamswp-shortcodes/admin/images/icon-twocolumn.png" />')
        ->and(array_column($attrs, 'attr'))->toBe(['col1', 'col2', 'ratio', 'customclass'])
        ->and(array_keys($attrs[2]['options']))->toBe(['half-half', 'third-twothirds', 'quarter-threequarters', 'twothirds-third', 'threequarters-quarter']);
});
