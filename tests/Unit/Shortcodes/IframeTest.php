<?php

/**
 * Iframe shortcode: whitelist handling, markup and reversal of raw embeds.
 *
 * @see inc/shortcodes/class-iframe.php
 */

declare(strict_types=1);

use UAMS_Shortcakes\Shortcodes\Iframe;

use function Brain\Monkey\Functions\stubs;
use function Brain\Monkey\Functions\when;

beforeEach(function (): void {
    stubWordPress();
    stubs([
        'esc_url' => fn ($url) => $url,
        'current_user_can' => false,
    ]);
    when('apply_filters')->alias(
        fn ($tag, $value) => $tag === 'uams_shortcodes_whitelisted_iframe_domains' ? ['example.com', 'assets.example.com'] : $value
    );
});

it('uses the lowercase class name as its shortcode tag', function (): void {
    expect(Iframe::get_shortcode_tag())->toBe('iframe');
});

it('returns the whitelist supplied by the filter', function (): void {
    expect(Iframe::get_whitelisted_iframe_domains())->toBe(['example.com', 'assets.example.com']);
});

it('returns an empty whitelist when nothing filters it', function (): void {
    when('apply_filters')->alias(fn ($tag, $value) => $value);

    expect(Iframe::get_whitelisted_iframe_domains())->toBe([]);
});

it('renders nothing without a src', function (): void {
    expect(Iframe::callback([]))->toBe('')
        ->and(Iframe::callback(['src' => '']))->toBe('');
});

it('renders a responsive iframe with the default size for a whitelisted host', function (): void {
    expect(Iframe::callback(['src' => 'https://example.com/embed']))->toBe(
        '<iframe src="https://example.com/embed" width="670" height="600" data-true-width="670" data-true-height="600" frameborder="0" scrolling="no" class="uams-shortcodes-responsive"></iframe>'
    );
});

it('uses the supplied width and height', function (): void {
    expect(Iframe::callback(['src' => 'https://example.com/x', 'width' => '300', 'height' => '200']))
        ->toContain('width="300" height="200" data-true-width="300" data-true-height="200"');
});

it('drops the responsive class when responsiveness is disabled', function (): void {
    expect(Iframe::callback(['src' => 'https://example.com/x', 'disableresponsiveness' => 'true']))
        ->toContain('class=""')
        ->not->toContain('uams-shortcodes-responsive');
});

it('accepts a protocol-relative URL on a whitelisted host', function (): void {
    expect(Iframe::callback(['src' => '//assets.example.com/a']))->toContain('<iframe src="//assets.example.com/a"');
});

it('lets the src filter rewrite the URL before the host check', function (): void {
    when('apply_filters')->alias(fn ($tag, $value) => match ($tag) {
        'uams_shortcodes_whitelisted_iframe_domains' => ['example.com'],
        'uams_shortcodes_iframe_src' => 'https://example.com/rewritten',
        default => $value,
    });

    expect(Iframe::callback(['src' => 'https://other.test/x']))->toContain('src="https://example.com/rewritten"');
});

it('renders nothing for a non-whitelisted host when the user cannot edit posts', function (): void {
    expect(Iframe::callback(['src' => 'https://evil.test/x']))->toBe('');
});

it('shows an error box for a non-whitelisted host when the user can edit posts', function (): void {
    when('current_user_can')->justReturn(true);

    expect(Iframe::callback(['src' => 'https://evil.test/x']))
        ->toBe('<div class="uams-shortcodes-error"><p>Invalid hostname in URL: https://evil.test/x</p></div>');
});

it('rejects a host that only contains a whitelisted domain', function (): void {
    expect(Iframe::callback(['src' => 'https://example.com.evil.test/x']))->toBe('');
});

it('reverses a whitelisted iframe embed into a shortcode', function (): void {
    $content = 'Before <iframe src="https://example.com/embed"></iframe> after';

    expect(Iframe::reversal($content))->toBe('Before [iframe src="https://example.com/embed"] after');
});

it('leaves a non-whitelisted iframe embed alone', function (): void {
    $content = 'Before <iframe src="https://evil.test/embed"></iframe> after';

    expect(Iframe::reversal($content))->toBe($content);
});

it('returns content without iframes unchanged', function (): void {
    expect(Iframe::reversal('<p>plain</p>'))->toBe('<p>plain</p>');
});

it('describes its Shortcake attributes', function (): void {
    $args = Iframe::get_shortcode_ui_args();

    expect($args['label'])->toBe('Iframe')
        ->and($args['listItemImage'])->toBe('dashicons-admin-site')
        ->and(array_column($args['attrs'], 'attr'))->toBe(['src', 'height', 'width', 'disableresponsiveness']);
});
