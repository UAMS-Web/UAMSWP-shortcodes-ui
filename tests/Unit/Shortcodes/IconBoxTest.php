<?php

/**
 * IconBox shortcode: the markup `callback()` returns and the Shortcake arguments.
 *
 * @see inc/shortcodes/class-iconbox.php
 */

declare(strict_types=1);

use UAMS_Shortcakes\Shortcodes\IconBox;

use function Brain\Monkey\Functions\stubs;

beforeEach(function (): void {
    stubWordPress();
    stubs(['esc_url' => fn ($url) => $url]);
});

it('uses the lowercase class name as its shortcode tag', function (): void {
    expect(IconBox::get_shortcode_tag())->toBe('iconbox');
});

it('renders the default box with a span wrapper and no icon or heading', function (): void {
    $html = IconBox::callback(['type' => 'default', 'customclass' => ''], 'Body');

    expect($html)->toBe('<div class="icon-box-wrapper default none "><div class="icon-box-icon"><span class="icon-wrap"></span></div><div class="icon-box-content"><span class="icon-wrap"></span><div class="icon-box-description"><p>Body</p></div></div></div>');
});

it('prints the misspelled defualt class for the default type of info', function (): void {
    expect(IconBox::callback(['customclass' => ''], 'x'))->toContain('icon-box-wrapper defualt none ');
});

it('keeps each allowed type and misspells the class for an unknown one', function (): void {
    expect(IconBox::callback(['type' => 'boxed', 'customclass' => ''], 'x'))->toContain('icon-box-wrapper boxed ')
        ->and(IconBox::callback(['type' => 'framed', 'customclass' => ''], 'x'))->toContain('icon-box-wrapper framed ')
        ->and(IconBox::callback(['type' => 'bogus', 'customclass' => ''], 'x'))->toContain('icon-box-wrapper defualt ')
        ->and(IconBox::callback(['type' => '', 'customclass' => ''], 'x'))->toContain('icon-box-wrapper default ');
});

it('keeps each allowed background and falls back to none', function (): void {
    expect(IconBox::callback(['type' => 'default', 'bgcolor' => 'red', 'customclass' => ''], 'x'))->toContain('default red ')
        ->and(IconBox::callback(['type' => 'default', 'bgcolor' => 'gray', 'customclass' => ''], 'x'))->toContain('default gray ')
        ->and(IconBox::callback(['type' => 'default', 'bgcolor' => 'blue', 'customclass' => ''], 'x'))->toContain('default blue ')
        ->and(IconBox::callback(['type' => 'default', 'bgcolor' => 'pink', 'customclass' => ''], 'x'))->toContain('default none ')
        ->and(IconBox::callback(['type' => 'default', 'bgcolor' => '', 'customclass' => ''], 'x'))->toContain('default none ');
});

it('renders the heading icon span unescaped', function (): void {
    $html = IconBox::callback(['type' => 'default', 'headingicon' => 'dashicons-star', 'customclass' => ''], 'x');

    expect($html)->toContain('<div class="icon-box-icon"><span class="icon-wrap"><span class="dashicons-star" aria-hidden="true"></span></span></div>');
});

it('skips a heading icon whose name is on the ignored list', function (string $ignored): void {
    expect(IconBox::callback(['type' => 'default', 'headingicon' => $ignored, 'customclass' => ''], 'x'))
        ->not->toContain('aria-hidden');
})->with(['noicon', 'commonicons', 'uamsicons', 'misc', 'allicons', 'uamsglyph', 'wpdash', 'numbers']);

it('renders the heading unescaped inside an h3', function (): void {
    expect(IconBox::callback(['type' => 'default', 'heading' => 'A <em>Head</em>', 'customclass' => ''], 'x'))
        ->toContain('<span class="icon-wrap"><h3 class="icon-box-title">A <em>Head</em></h3></span>');
});

it('wraps the icon and the heading in links when a url is set', function (): void {
    $html = IconBox::callback(['type' => 'default', 'url' => 'https://example.test/x', 'heading' => 'H', 'headingicon' => 'dashicons-star', 'customclass' => ''], 'x');

    expect($html)
        ->toContain('<div class="icon-box-icon"><a href="https://example.test/x"><span class="dashicons-star" aria-hidden="true"></span></a></div>')
        ->toContain('<div class="icon-box-content"><a href="https://example.test/x"><h3 class="icon-box-title">H</h3></a>')
        ->not->toContain('icon-wrap');
});

it('url-decodes the body before wpautop', function (): void {
    expect(IconBox::callback(['type' => 'default', 'customclass' => ''], 'a%20b'))->toContain('<div class="icon-box-description"><p>a b</p></div>');
});

it('sanitizes each comma-separated custom class', function (): void {
    expect(IconBox::callback(['type' => 'default', 'customclass' => 'one,t wo!'], 'x'))->toContain('default none one two"');
});

it('triggers a deprecation for the default null custom class', function (): void {
    $messages = [];
    set_error_handler(function (int $no, string $str) use (&$messages): bool {
        $messages[] = $str;

        return true;
    });

    try {
        IconBox::callback([], 'x');
    } finally {
        restore_error_handler();
    }

    expect($messages)->toHaveCount(1)->and($messages[0])->toContain('explode()');
});

it('falls back to the defaults when the attributes are not an array', function (): void {
    $messages = [];
    set_error_handler(function (int $no, string $str) use (&$messages): bool {
        $messages[] = $str;

        return true;
    });

    try {
        $html = IconBox::callback('', 'x');
    } finally {
        restore_error_handler();
    }

    expect($html)->toContain('icon-box-wrapper defualt none ')
        ->and($messages)->toHaveCount(1);
});

it('describes its Shortcake attributes', function (): void {
    $args = IconBox::get_shortcode_ui_args();
    $icons = array_column($args['attrs'], null, 'attr')['headingicon'];

    expect(array_column(IconBox::get_attributes(), 'attr'))->toBe(['type', 'bgcolor', 'heading', 'headingicon', 'url', 'customclass'])
        ->and($args['attrs'])->toBe(IconBox::get_attributes())
        ->and($args['label'])->toBe('Icon Box')
        ->and($args['inner_content']['encode'])->toBeTrue()
        ->and($icons['options'])->toBe(IconBox::get_icon_collection());
});
