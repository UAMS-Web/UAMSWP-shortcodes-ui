<?php

/**
 * Pullquote shortcode: markup, alignment and color validation, custom classes.
 *
 * @see inc/shortcodes/class-pullquote.php
 */

declare(strict_types=1);

use UAMS_Shortcakes\Shortcodes\Pullquote;

beforeEach(function (): void {
    stubWordPress();
});

it('uses the lowercase class name as its shortcode tag', function (): void {
    expect(Pullquote::get_shortcode_tag())->toBe('pullquote');
});

it('renders the quote hidden from assistive technology with the defaults', function (): void {
    expect(Pullquote::callback(['customclass' => ''], 'Quote'))->toBe(
        '<aside class="pullquote alignleft " aria-hidden="true"><p class="quote-content reynoldsred">Quote</p></aside>'
    );
});

it('keeps each allowed alignment', function (string $align): void {
    expect(Pullquote::callback(['align' => $align, 'customclass' => ''], 'q'))->toContain('class="pullquote '.$align.' ');
})->with(['alignleft', 'aligncenter', 'alignright']);

it('falls back to alignleft for an unknown alignment', function (): void {
    expect(Pullquote::callback(['align' => 'bogus', 'customclass' => ''], 'q'))->toContain('class="pullquote alignleft ');
});

it('uses alignleft, the first alignment option, when none is given', function (): void {
    expect(Pullquote::callback(['customclass' => ''], 'q'))->toContain('pullquote alignleft');
});

it('keeps each allowed color', function (string $color): void {
    expect(Pullquote::callback(['color' => $color, 'customclass' => ''], 'q'))->toContain('quote-content '.$color.'"');
})->with(['wolfpackred', 'gray90', 'black']);

it('falls back to reynoldsred for the default uamsred color and for unknown colors', function (string $color): void {
    expect(Pullquote::callback(['color' => $color, 'customclass' => ''], 'q'))->toContain('quote-content reynoldsred"');
})->with(['uamsred', 'bogus', '']);

it('adds a sanitized citation', function (): void {
    expect(Pullquote::callback(['cite' => '<b>Ada</b> Lovelace', 'customclass' => ''], 'q'))
        ->toContain('</p><cite>Ada Lovelace</cite></aside>');
});

it('omits the citation when it is empty', function (): void {
    expect(Pullquote::callback(['cite' => '', 'customclass' => ''], 'q'))->not->toContain('<cite>');
});

it('strips HTML from the quote body', function (): void {
    expect(Pullquote::callback(['customclass' => ''], '<b>Hi</b>'))->toContain('>Hi</p>');
});

it('renders an empty quote body', function (): void {
    expect(Pullquote::callback(['customclass' => ''], ''))->toContain('<p class="quote-content reynoldsred"></p>');
});

it('sanitizes each comma-separated custom class', function (): void {
    expect(Pullquote::callback(['customclass' => 'one,t wo!'], 'q'))->toContain('class="pullquote alignleft one two"');
});

it('describes its Shortcake attributes', function (): void {
    $args = Pullquote::get_shortcode_ui_args();
    $attrs = Pullquote::get_attributes();

    expect(array_column($attrs, 'attr'))->toBe(['cite', 'color', 'align', 'customclass'])
        ->and($args['attrs'])->toBe($attrs)
        ->and($args['inner_content']['encode'])->toBeTrue()
        ->and($args['listItemImage'])->toBe('<img src="https://example.test/wp-content/plugins/uamswp-shortcodes/admin/images/icon-pullquote.png" />')
        ->and($attrs[1]['options'])->toBe(Pullquote::get_accessible_text_brand_colors())
        ->and(array_keys($attrs[2]['options']))->toBe(['alignleft', 'aligncenter', 'alignright']);
});
