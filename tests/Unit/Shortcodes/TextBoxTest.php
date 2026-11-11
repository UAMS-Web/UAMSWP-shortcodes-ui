<?php

/**
 * TextBox shortcode: the markup `callback()` returns and the Shortcake arguments.
 *
 * @see inc/shortcodes/class-textbox.php
 */

declare(strict_types=1);

use Brain\Monkey\Functions;
use UAMS_Shortcakes\Shortcodes\TextBox;

use function Brain\Monkey\Functions\when;

beforeEach(function (): void {
    stubWordPress();
});

it('uses the lowercase class name as its shortcode tag', function (): void {
    expect(TextBox::get_shortcode_tag())->toBe('textbox');
});

it('renders the default box with no background and dark text', function (): void {
    $html = TextBox::callback(['customclass' => ''], 'Body');

    expect($html)->toBe('<div class="text-box-wrapper none dark  " ><h2 class="text-box-title"></h2><div class="text-box-content"><p>Body</p></div></div>');
});

it('falls back to none for a background that is not allowed', function (): void {
    expect(TextBox::callback(['background' => 'green', 'customclass' => ''], 'x'))->toContain('text-box-wrapper none ')
        ->and(TextBox::callback(['background' => '', 'customclass' => ''], 'x'))->toContain('text-box-wrapper none ');
});

it('keeps each allowed background', function (string $background): void {
    expect(TextBox::callback(['background' => $background, 'customclass' => ''], 'x'))
        ->toContain('text-box-wrapper '.$background.' ');
})->with(['none', 'red', 'gray', 'blue', 'image']);

it('falls back to dark for an unknown text color and keeps light', function (): void {
    expect(TextBox::callback(['txtcolor' => 'pink', 'customclass' => ''], 'x'))->toContain('none dark ')
        ->and(TextBox::callback(['txtcolor' => 'light', 'customclass' => ''], 'x'))->toContain('none light ')
        ->and(TextBox::callback(['txtcolor' => '', 'customclass' => ''], 'x'))->toContain('none dark ');
});

it('uses the attachment as an inline background image only for the image background', function (): void {
    Functions\expect('wp_get_attachment_image_url')->once()->with(42, 'Full')->andReturn('https://example.test/bg.jpg');

    $html = TextBox::callback(['background' => 'image', 'imagebg' => 42, 'customclass' => ''], 'x');

    expect($html)->toContain('" style="background-image:url(https://example.test/bg.jpg);"><h2');
});

it('ignores the image when the background is not image or no image is set', function (): void {
    Functions\expect('wp_get_attachment_image_url')->never();

    expect(TextBox::callback(['background' => 'red', 'imagebg' => 42, 'customclass' => ''], 'x'))->not->toContain('background-image')
        ->and(TextBox::callback(['background' => 'image', 'customclass' => ''], 'x'))->not->toContain('background-image');
});

it('adds the full width class when fullscreen is truthy', function (): void {
    expect(TextBox::callback(['fullscreen' => true, 'customclass' => ''], 'x'))->toContain('none dark uams-full-width ')
        ->and(TextBox::callback(['fullscreen' => '', 'customclass' => ''], 'x'))->not->toContain('uams-full-width');
});

it('adds the full width class for the string a shortcode attribute carries', function (): void {
    expect(TextBox::callback(['fullscreen' => '1', 'customclass' => ''], 'x'))->toContain('uams-full-width');
});

it('prints the title inside an h2', function (): void {
    expect(TextBox::callback(['titletxt' => 'A <b>Title</b>', 'customclass' => ''], 'x'))
        ->toContain('<h2 class="text-box-title">A <b>Title</b></h2>');
});

it('passes the title through wp_kses_post', function (): void {
    when('wp_kses_post')->alias(fn ($content): string => strip_tags((string) $content));

    expect(TextBox::callback(['titletxt' => 'A <b>Title</b><script>x</script>', 'customclass' => ''], 'x'))
        ->toContain('<h2 class="text-box-title">A Titlex</h2>')
        ->not->toContain('<script>');
});

it('url-decodes the body before wpautop', function (): void {
    expect(TextBox::callback(['customclass' => ''], 'a%20b'))->toContain('<div class="text-box-content"><p>a b</p></div>');
});

it('sanitizes each comma-separated custom class', function (): void {
    expect(TextBox::callback(['customclass' => 'one,t wo!'], 'x'))->toContain('none dark  one two"');
});

it('renders without a deprecation when no custom class is given', function (): void {
    $messages = [];
    set_error_handler(function (int $no, string $str) use (&$messages): bool {
        $messages[] = $str;

        return true;
    });

    try {
        $html = TextBox::callback([], 'x');
    } finally {
        restore_error_handler();
    }

    expect($messages)->toBe([])
        ->and($html)->toBe(TextBox::callback(['customclass' => ''], 'x'));
});

it('renders with the defaults when WordPress passes an empty string for attributes', function (): void {
    expect(TextBox::callback('', 'x'))->toBe(TextBox::callback([], 'x'));
});

it('describes its Shortcake attributes', function (): void {
    $args = TextBox::get_shortcode_ui_args();

    expect(array_column(TextBox::get_attributes(), 'attr'))->toBe(['background', 'imagebg', 'txtcolor', 'titletxt', 'fullscreen', 'customclass'])
        ->and($args['attrs'])->toBe(TextBox::get_attributes())
        ->and($args['label'])->toBe('Text Box')
        ->and($args['inner_content']['encode'])->toBeTrue();
});
