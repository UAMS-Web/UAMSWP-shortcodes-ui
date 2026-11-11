<?php

/**
 * ThreeColumn shortcode: ratio classes, column content and custom classes.
 *
 * @see inc/shortcodes/class-threecolumn.php
 */

declare(strict_types=1);

use UAMS_Shortcakes\Shortcodes\ThreeColumn;

use function Brain\Monkey\Functions\when;

beforeEach(function (): void {
    stubWordPress();
});

/**
 * Collapse the whitespace between tags so markup can be compared compactly.
 */
function threeColumnCompact(string $html): string
{
    return (string) preg_replace('/>\s+</', '><', trim($html));
}

it('uses the lowercase class name as its shortcode tag', function (): void {
    expect(ThreeColumn::get_shortcode_tag())->toBe('threecolumn');
});

it('renders three equal columns by default', function (): void {
    $html = threeColumnCompact(ThreeColumn::callback(['col1' => 'A', 'col2' => 'B', 'col3' => 'C', 'customclass' => '']));

    expect($html)->toBe(
        '<div class="row "><div class="inner-container">'
        .'<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12"><p>A</p></div>'
        .'<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12"><p>B</p></div>'
        .'<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12"><p>C</p></div>'
        .'</div></div>'
    );
});

it('maps each ratio to its column classes', function (string $ratio, array $widths): void {
    $html = threeColumnCompact(ThreeColumn::callback(['ratio' => $ratio, 'col1' => 'A', 'col2' => 'B', 'col3' => 'C', 'customclass' => '']));

    foreach ($widths as $index => $width) {
        $letter = ['A', 'B', 'C'][$index];
        expect($html)->toContain('<div class="col-lg-'.$width.' col-md-'.$width.' col-sm-'.$width.' col-xs-12"><p>'.$letter.'</p>');
    }
})->with([
    ['third-third-third', [4, 4, 4]],
    ['quarter-quarter-half', [3, 3, 6]],
    ['quarter-half-quarter', [3, 6, 3]],
    ['half-quarter-quarter', [6, 3, 3]],
    ['bogus', [4, 4, 4]],
]);

it('url-decodes column content', function (): void {
    expect(ThreeColumn::callback(['col1' => 'a%20b', 'col2' => '', 'col3' => '', 'customclass' => '']))->toContain('<p>a b</p>');
});

it('runs each column through wp_kses_post', function (): void {
    when('wp_kses_post')->alias(fn ($content): string => strip_tags((string) $content));

    expect(ThreeColumn::callback(['col1' => '<script>x</script>', 'col2' => '', 'col3' => '', 'customclass' => '']))
        ->toContain('<p>x</p>')
        ->not->toContain('<script>');
});

it('url-decodes a column before wp_kses_post sees it', function (): void {
    when('wp_kses_post')->alias(fn ($content): string => strip_tags((string) $content));

    expect(ThreeColumn::callback(['col1' => '%3Cscript%3Ex%3C/script%3E', 'col2' => '', 'col3' => '', 'customclass' => '']))
        ->toContain('<p>x</p>')
        ->not->toContain('<script>');
});

it('passes column content through do_shortcode', function (): void {
    when('do_shortcode')->alias(fn ($content): string => strtoupper($content));

    expect(ThreeColumn::callback(['col1' => 'a', 'col2' => 'b', 'col3' => 'c', 'customclass' => '']))
        ->toContain('<p>A</p>')->toContain('<p>B</p>')->toContain('<p>C</p>');
});

it('sanitizes each comma-separated custom class', function (): void {
    expect(ThreeColumn::callback(['customclass' => 'one,t wo!', 'col1' => '', 'col2' => '', 'col3' => '']))->toContain('<div class="row one two">');
});

it('renders empty columns when no content is supplied', function (): void {
    expect(substr_count(threeColumnCompact(ThreeColumn::callback(['col1' => '', 'col2' => '', 'col3' => '', 'customclass' => ''])), '<p></p>'))->toBe(3);
});

it('renders empty columns without a deprecation when no attribute is given', function (): void {
    $messages = [];
    set_error_handler(function (int $no, string $str) use (&$messages): bool {
        $messages[] = $str;

        return true;
    });

    try {
        $html = ThreeColumn::callback([]);
    } finally {
        restore_error_handler();
    }

    expect($messages)->toBe([])
        ->and(threeColumnCompact($html))->toBe(threeColumnCompact(ThreeColumn::callback(['col1' => '', 'col2' => '', 'col3' => '', 'customclass' => ''])));
});

it('describes its Shortcake attributes', function (): void {
    $args = ThreeColumn::get_shortcode_ui_args();
    $attrs = ThreeColumn::get_attributes();

    expect($args['label'])->toBe('Row, 3 Columns')
        ->and($args['attrs'])->toBe($attrs)
        ->and($args['listItemImage'])->toBe('<img src="https://example.test/wp-content/plugins/uamswp-shortcodes/admin/images/icon-threecolumn.png" />')
        ->and(array_column($attrs, 'attr'))->toBe(['col1', 'col2', 'col3', 'ratio', 'customclass'])
        ->and(array_keys($attrs[3]['options']))->toBe(['third-third-third', 'quarter-quarter-half', 'quarter-half-quarter', 'half-quarter-quarter']);
});
