<?php

/**
 * Flickr shortcode: the iframe `callback()` returns, `reversal()` and the Shortcake arguments.
 *
 * @see inc/shortcodes/class-flickr.php
 */

declare(strict_types=1);

use UAMS_Shortcakes\Shortcodes\Flickr;

use function Brain\Monkey\Functions\when;

beforeEach(function (): void {
    stubWordPress();
});

it('uses the lowercase class name as its shortcode tag', function (): void {
    expect(Flickr::get_shortcode_tag())->toBe('flickr');
});

it('describes its Shortcake arguments', function (): void {
    $args = Flickr::get_shortcode_ui_args();

    expect($args['label'])->toBe('Flickr')
        ->and($args['listItemImage'])->toBe('<img src="https://example.test/wp-content/plugins/uamswp-shortcodes/assets/images/svg/icon-flickr.svg" />')
        ->and($args['attrs'])->toBe([
            [
                'label' => 'URL',
                'attr' => 'url',
                'type' => 'text',
                'description' => 'URL to a Flickr gallery',
            ],
        ]);
});

it('has no separate get_attributes list', function (): void {
    expect(Flickr::get_attributes())->toBe([]);
});

it('returns an empty string without a url', function (): void {
    expect(Flickr::callback([]))->toBe('')
        ->and(Flickr::callback(['url' => '']))->toBe('');
});

it('returns an empty string for a host that is not Flickr', function (string $url): void {
    expect(Flickr::callback(['url' => $url]))->toBe('');
})->with([
    'other site' => 'https://example.com/photos/x/albums/1',
    'lookalike subdomain' => 'https://evil.flickr.com/photos/x',
    'lookalike suffix' => 'https://flickr.com.evil.test/photos/x',
    'no host' => 'not a url at all',
]);

it('appends /player/ to a gallery url', function (): void {
    expect(Flickr::callback(['url' => 'https://www.flickr.com/photos/user/albums/72157']))
        ->toBe('<iframe class="uams-shortcodes-responsive" width="500" height="334" src="https://www.flickr.com/photos/user/albums/72157/player/" frameborder="0"></iframe>');
});

it('trims trailing slashes before appending /player/', function (): void {
    expect(Flickr::callback(['url' => 'https://flickr.com/photos/user/albums/72157///']))
        ->toContain('src="https://flickr.com/photos/user/albums/72157/player/"');
});

it('accepts a protocol-relative url', function (): void {
    // Brain Monkey's esc_url stub normalizes the scheme, so assert on the path only.
    expect(Flickr::callback(['url' => '//www.flickr.com/photos/user/albums/1']))
        ->toContain('www.flickr.com/photos/user/albums/1/player/" frameborder');
});

it('leaves a url that already ends in /player/ alone', function (): void {
    expect(Flickr::callback(['url' => 'https://www.flickr.com/photos/user/albums/1/player/']))
        ->toContain('src="https://www.flickr.com/photos/user/albums/1/player/"');
});

it('leaves a url that ends in /player without a trailing slash alone', function (): void {
    expect(Flickr::callback(['url' => 'https://www.flickr.com/photos/user/albums/1/player']))
        ->toContain('src="https://www.flickr.com/photos/user/albums/1/player"');
});

it('ignores the inner content', function (): void {
    expect(Flickr::callback(['url' => 'https://www.flickr.com/photos/u/albums/1'], 'ignored'))
        ->not->toContain('ignored');
});

it('passes the url through esc_url', function (): void {
    when('esc_url')->alias(fn (string $url): string => 'ESC['.$url.']');

    expect(Flickr::callback(['url' => 'https://www.flickr.com/photos/u/albums/1']))
        ->toContain('src="ESC[https://www.flickr.com/photos/u/albums/1/player/]"');
});

it('turns a Flickr player iframe into a shortcode', function (): void {
    $content = 'Before <iframe src="https://www.flickr.com/photos/user/albums/72157/player/" width="500" height="334"></iframe>';

    expect(Flickr::reversal($content))->toBe('Before [flickr url="https://www.flickr.com/photos/user/albums/72157/"]');
});

it('keeps an iframe /player url that has no trailing slash as a slash url', function (): void {
    $content = '<iframe src="https://flickr.com/photos/u/albums/1/player"></iframe>';

    expect(Flickr::reversal($content))->toBe('[flickr url="https://flickr.com/photos/u/albums/1/"]');
});

it('leaves an iframe from another host in place', function (): void {
    $content = '<iframe src="https://example.com/embed"></iframe>';

    expect(Flickr::reversal($content))->toBe($content);
});

it('returns content without an iframe unchanged', function (): void {
    expect(Flickr::reversal('<p>plain</p>'))->toBe('<p>plain</p>');
});

it('passes the reversed url through esc_url_raw', function (): void {
    when('esc_url_raw')->alias(fn (string $url): string => 'RAW['.$url.']');

    expect(Flickr::reversal('<iframe src="https://www.flickr.com/photos/u/albums/1/player/"></iframe>'))
        ->toBe('[flickr url="RAW[https://www.flickr.com/photos/u/albums/1/]"]');
});
