<?php

/**
 * Instagram shortcode: photo id extraction from the url, the iframe markup and
 * the reversal of Instagram blockquote and iframe embeds.
 *
 * @see inc/shortcodes/class-instagram.php
 */

declare(strict_types=1);

use UAMS_Shortcakes\Shortcodes\Instagram;

beforeEach(function (): void {
    stubWordPress();
});

it('uses the lowercase class name as its shortcode tag', function (): void {
    expect(Instagram::get_shortcode_tag())->toBe('instagram');
});

it('renders nothing for a missing or empty url', function (): void {
    expect(Instagram::callback([]))->toBe('')
        ->and(Instagram::callback(['url' => '']))->toBe('');
});

it('renders nothing for a url that is not an Instagram photo', function (string $url): void {
    expect(Instagram::callback(['url' => $url]))->toBe('');
})->with([
    'other host' => 'https://example.com/p/ABC123/',
    'profile page' => 'https://www.instagram.com/someuser/',
    'no photo id' => 'https://www.instagram.com/p/',
]);

it('renders a responsive iframe for a photo url', function (string $url): void {
    expect(Instagram::callback(['url' => $url]))
        ->toBe('<iframe class="uams-shortcodes-responsive" src="https://instagram.com/p/ABC123/embed/" width="612" height="710" frameborder="0" scrolling="no"></iframe>');
})->with([
    'https www' => 'https://www.instagram.com/p/ABC123/',
    'https bare' => 'https://instagram.com/p/ABC123/',
    'http' => 'http://instagram.com/p/ABC123',
    'protocol relative' => '//instagram.com/p/ABC123/',
    'short domain' => 'https://instagr.am/p/ABC123/',
    'upper case' => 'HTTPS://WWW.INSTAGRAM.COM/p/ABC123/',
    'with query' => 'https://www.instagram.com/p/ABC123/?utm_source=ig_embed',
]);

it('describes its Shortcake attributes', function (): void {
    $args = Instagram::get_shortcode_ui_args();

    expect($args['label'])->toBe('Instagram')
        ->and($args['listItemImage'])->toBe('<img src="https://example.test/wp-content/plugins/uamswp-shortcodes/assets/images/svg/icon-instagram.svg" />')
        ->and(array_column($args['attrs'], 'attr'))->toBe(['url']);
});

it('returns content unchanged when it has no script, iframe or instagram markup', function (): void {
    $content = '<p>Hello</p><blockquote>Quote</blockquote>';

    expect(Instagram::reversal($content))->toBe($content);
});

it('turns an Instagram blockquote embed into a shortcode', function (): void {
    $content = '<blockquote class="instagram-media" data-instgrm-version="7"><div><a href="https://www.instagram.com/p/ABC123/" target="_blank">View on Instagram</a></div></blockquote>';

    expect(Instagram::reversal($content))->toBe('[instagram url="https://www.instagram.com/p/ABC123/"]');
});

it('also removes the platform script that follows the blockquote', function (): void {
    $content = "Intro\n".'<blockquote class="instagram-media"><a href="https://instagram.com/p/XYZ/" target="_blank">View</a></blockquote>'."\n".'<script async defer src="//platform.instagram.com/en_US/embeds.js"></script>'."\nOutro";

    expect(Instagram::reversal($content))->toBe("Intro\n[instagram url=\"https://instagram.com/p/XYZ/\"]\nOutro");
});

it('leaves a blockquote that does not link to a photo alone', function (): void {
    $content = '<blockquote class="instagram-media"><a href="https://www.instagram.com/someuser/" target="_blank">Profile</a></blockquote>';

    expect(Instagram::reversal($content))->toBe($content);
});

it('turns an Instagram iframe into a shortcode', function (): void {
    $content = '<iframe src="https://instagram.com/p/ABC123/embed/" width="612"></iframe>';

    expect(Instagram::reversal($content))->toBe('[instagram url="https://instagram.com/p/ABC123/"]');
});

it('leaves an iframe from another host alone', function (): void {
    $content = '<iframe src="https://example.com/p/ABC123/embed/"></iframe>';

    expect(Instagram::reversal($content))->toBe($content);
});

it('turns an Instagram iframe on the www host into a shortcode', function (): void {
    $content = '<iframe src="https://www.instagram.com/p/ABC123/embed/"></iframe>';

    expect(Instagram::reversal($content))->toBe('[instagram url="https://instagram.com/p/ABC123/"]');
});

it('leaves an Instagram iframe whose path is not an embed alone', function (): void {
    $content = '<iframe src="https://instagram.com/someuser/"></iframe>';

    expect(Instagram::reversal($content))->toBe($content);
});
