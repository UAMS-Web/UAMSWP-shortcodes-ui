<?php

/**
 * Lead shortcode: the paragraph `callback()` returns and the Shortcake arguments.
 *
 * @see inc/shortcodes/class-lead.php
 */

declare(strict_types=1);

use UAMS_Shortcakes\Shortcodes\Lead;

use function Brain\Monkey\Functions\stubs;

beforeEach(function (): void {
    stubWordPress();
    stubs(['esc_url' => fn ($url) => $url]);
});

it('uses the lowercase class name as its shortcode tag', function (): void {
    expect(Lead::get_shortcode_tag())->toBe('lead');
});

it('wraps the content in a lead paragraph', function (): void {
    expect(Lead::callback(['customclass' => ''], 'Hello'))->toBe('<p class="lead ">Hello</p>');
});

it('passes the content through unchanged', function (): void {
    expect(Lead::callback(['customclass' => ''], '<b>Hi</b> & more'))->toBe('<p class="lead "><b>Hi</b> & more</p>');
});

it('renders an empty paragraph without content', function (): void {
    expect(Lead::callback(['customclass' => '']))->toBe('<p class="lead "></p>');
});

it('sanitizes each comma-separated custom class', function (): void {
    expect(Lead::callback(['customclass' => 'one,t wo!'], 'x'))->toBe('<p class="lead one two">x</p>');
});

it('falls back to the defaults when the attributes are not an array', function (): void {
    $messages = [];
    set_error_handler(function (int $no, string $str) use (&$messages): bool {
        $messages[] = $str;

        return true;
    });

    try {
        $html = Lead::callback('', 'x');
    } finally {
        restore_error_handler();
    }

    expect($html)->toBe('<p class="lead ">x</p>')
        ->and($messages)->toHaveCount(1)
        ->and($messages[0])->toContain('explode()');
});

it('describes its Shortcake attributes', function (): void {
    $args = Lead::get_shortcode_ui_args();

    expect(array_column(Lead::get_attributes(), 'attr'))->toBe(['customclass'])
        ->and($args['attrs'])->toBe(Lead::get_attributes())
        ->and($args['label'])->toBe('Lead Paragraph')
        ->and($args['listItemImage'])->toBe('<img src="https://example.test/wp-content/plugins/uamswp-shortcodes/admin/images/icon-lead.png" />')
        ->and($args['inner_content']['encode'])->toBeTrue();
});
