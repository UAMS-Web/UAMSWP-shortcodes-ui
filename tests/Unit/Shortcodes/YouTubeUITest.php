<?php

/**
 * YouTube UI shortcode: the video player wrapper markup, embed id handling,
 * playlist handling and the reversal of YouTube iframes.
 *
 * @see inc/shortcodes/class-youtubeui.php
 */

declare(strict_types=1);

use UAMS_Shortcakes\Shortcodes\YouTubeUI;

use function Brain\Monkey\Functions\when;

beforeEach(function (): void {
    stubWordPress();

    // Same contract as WordPress' add_query_arg() for the (key, value, url) form.
    when('add_query_arg')->alias(
        fn (string $key, string $value, string $url): string => $url.(str_contains($url, '?') ? '&' : '?').http_build_query([$key => $value])
    );
});

it('uses the lowercase class name as its shortcode tag', function (): void {
    expect(YouTubeUI::get_shortcode_tag())->toBe('youtubeui');
});

it('renders the player wrapper around the iframe for a watch url', function (): void {
    expect(YouTubeUI::callback(['url' => 'https://www.youtube.com/watch?v=hDlpVFDmXrc']))
        ->toBe('<div class="nc-video-player" role="region" aria-label="video" tabindex=-1><div class="tube-wrapper"><iframe class="shortcake-bakery-responsive" data-uams-youtube-type="single" data-uams-youtube="hDlpVFDmXrc" width="640" height="360" frameborder="0" allowfullscreen="1" src="https://youtube.com/embed/hDlpVFDmXrc?rel=0"></iframe></div></div>');
});

it('takes the embed id from the path of a short url', function (): void {
    expect(YouTubeUI::callback(['url' => 'https://youtu.be/nc7F_qv3eI8']))
        ->toContain('data-uams-youtube="nc7F_qv3eI8"')
        ->toContain('src="https://youtube.com/embed/nc7F_qv3eI8?rel=0"');
});

it('reads the video from a url with encoded ampersands', function (): void {
    expect(YouTubeUI::callback(['url' => 'https://www.youtube.com/watch?feature=share&amp;v=abc']))
        ->toContain('data-uams-youtube="abc"');
});

it('reads the video from a url with the numeric form of the encoded ampersand', function (): void {
    expect(YouTubeUI::callback(['url' => 'https://www.youtube.com/watch?feature=share&#038;v=abc']))
        ->toContain('data-uams-youtube="abc"');
});

it('joins the list and rel arguments of a playlist url with an ampersand', function (): void {
    expect(YouTubeUI::callback(['url' => 'https://www.youtube.com/watch?v=abc&list=PL123']))
        ->toContain('src="https://youtube.com/embed/abc?list=PL123&rel=0"');
});

it('escapes the embed id in the data attribute', function (): void {
    when('esc_attr')->alias(fn ($text): string => htmlspecialchars((string) $text, ENT_QUOTES));

    expect(YouTubeUI::callback(['url' => 'https://www.youtube.com/watch?v=a%22b']))
        ->toContain('data-uams-youtube="a&quot;b"')
        ->not->toContain('data-uams-youtube="a"b"');
});

it('renders nothing for a watch url without a v argument', function (): void {
    expect(YouTubeUI::callback(['url' => 'https://www.youtube.com/watch?list=PL123']))->toBe('');
});

it('renders nothing for a url with no query string without a deprecation', function (): void {
    $errors = [];
    set_error_handler(function (int $no, string $message) use (&$errors): bool {
        $errors[] = $message;

        return true;
    });

    try {
        $html = YouTubeUI::callback(['url' => 'https://www.youtube.com/channel/abc']);
    } finally {
        restore_error_handler();
    }

    expect($html)->toBe('')
        ->and($errors)->toBe([]);
});

it('renders nothing for an empty url or a host that is not YouTube', function (): void {
    expect(YouTubeUI::callback(['url' => '']))->toBe('')
        ->and(YouTubeUI::callback(['url' => 'https://example.com/watch?v=abc']))->toBe('')
        ->and(YouTubeUI::callback(['url' => 'https://m.youtube.com/watch?v=abc']))->toBe('');
});

it('renders nothing without a warning when the url attribute is missing', function (): void {
    $errors = [];
    set_error_handler(function (int $no, string $message) use (&$errors): bool {
        $errors[] = $message;

        return true;
    });

    try {
        $html = YouTubeUI::callback([]);
    } finally {
        restore_error_handler();
    }

    expect($html)->toBe('')
        ->and($errors)->toBe([]);
});

it('lets a filter change the embed url', function (): void {
    when('apply_filters')->alias(fn (string $tag, $value, ...$rest): string => 'https://youtube-nocookie.com/embed/changed');

    expect(YouTubeUI::callback(['url' => 'https://youtu.be/abc']))
        ->toContain('data-uams-youtube="abc"')
        ->toContain('src="https://youtube-nocookie.com/embed/changed?rel=0"');
});

it('describes its Shortcake attributes', function (): void {
    $args = YouTubeUI::get_shortcode_ui_args();

    expect($args['label'])->toBe('YouTube')
        ->and($args['listItemImage'])->toBe('<img src="https://example.test/wp-content/plugins/uamswp-shortcodes/assets/images/svg/icon-youtube.svg" />')
        ->and(array_column($args['attrs'], 'attr'))->toBe(['url']);
});

it('turns a YouTube iframe into a shortcode using its own tag', function (): void {
    $content = '<iframe width="560" height="315" src="https://www.youtube.com/embed/abc123" frameborder="0"></iframe>';

    expect(YouTubeUI::reversal($content))->toBe('[youtubeui url="https://www.youtube.com/watch?v=abc123"]');
});

it('leaves other iframes alone', function (): void {
    $other = '<iframe src="https://example.com/embed/abc123"></iframe>';
    $watch = '<iframe src="https://www.youtube.com/watch?v=abc123"></iframe>';

    expect(YouTubeUI::reversal($other))->toBe($other)
        ->and(YouTubeUI::reversal($watch))->toBe($watch)
        ->and(YouTubeUI::reversal('<p>Plain</p>'))->toBe('<p>Plain</p>');
});
