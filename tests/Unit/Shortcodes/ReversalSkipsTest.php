<?php

/**
 * Reversal of an embed iframe keeps going past an iframe it leaves alone.
 *
 * Each case puts the iframes a class must skip in front of one it converts, so
 * a loop that stops at the first skipped iframe leaves the later one as an
 * iframe.
 *
 * @see inc/shortcodes
 */

declare(strict_types=1);

use UAMS_Shortcakes\Shortcodes\Facebook;
use UAMS_Shortcakes\Shortcodes\Flickr;
use UAMS_Shortcakes\Shortcodes\GoogleDocs;
use UAMS_Shortcakes\Shortcodes\Iframe;
use UAMS_Shortcakes\Shortcodes\Instagram;
use UAMS_Shortcakes\Shortcodes\Scribd;
use UAMS_Shortcakes\Shortcodes\SoundCloud;
use UAMS_Shortcakes\Shortcodes\Vimeo;
use UAMS_Shortcakes\Shortcodes\YouTube;
use UAMS_Shortcakes\Shortcodes\YouTubeUI;

use function Brain\Monkey\Functions\when;

beforeEach(function (): void {
    stubWordPress();
    when('apply_filters')->alias(
        fn ($tag, $value) => $tag === 'uams_shortcodes_whitelisted_iframe_domains' ? ['good.test'] : $value
    );
});

it('converts the iframe after each one it skips', function (string $class, array $skipped, string $good, string $expected): void {
    $before = implode("\n\n", $skipped);

    expect($class::reversal($before."\n\n".$good))->toBe($before."\n\n".$expected);
})->with([
    'Facebook' => [
        Facebook::class,
        [
            '<iframe src="https://example.test/x"></iframe>',
            '<iframe src="https://www.facebook.com/plugins/post.php?href=https%3A%2F%2Fexample.test%2Fp"></iframe>',
        ],
        '<iframe src="https://www.facebook.com/plugins/post.php?href=https%3A%2F%2Fwww.facebook.com%2FUAMS%2Fposts%2F1&amp;width=350"></iframe>',
        '[facebook url="https://www.facebook.com/UAMS/posts/1"]',
    ],
    'Flickr' => [
        Flickr::class,
        ['<iframe src="https://example.test/x"></iframe>'],
        '<iframe src="https://www.flickr.com/photos/u/albums/1/player/"></iframe>',
        '[flickr url="https://www.flickr.com/photos/u/albums/1/"]',
    ],
    'GoogleDocs' => [
        GoogleDocs::class,
        [
            '<iframe src="https://example.test/x"></iframe>',
            '<iframe src="https://docs.google.com/doc-ument"></iframe>',
        ],
        '<iframe src="https://docs.google.com/document/d/ABC/pub?embedded=true"></iframe>',
        '[googledocs url="https://docs.google.com/document/d/ABC"]',
    ],
    'Iframe' => [
        Iframe::class,
        ['<iframe src="https://other.test/x"></iframe>'],
        '<iframe src="https://good.test/x"></iframe>',
        '[iframe src="https://good.test/x"]',
    ],
    'Instagram' => [
        Instagram::class,
        [
            '<iframe src="https://example.test/p/ABC/embed/"></iframe>',
            '<iframe src="https://instagram.com/other"></iframe>',
        ],
        '<iframe src="https://instagram.com/p/ABC/embed/"></iframe>',
        '[instagram url="https://instagram.com/p/ABC/"]',
    ],
    'Scribd' => [
        Scribd::class,
        ['<iframe src="https://example.test/x"></iframe>'],
        '<iframe src="https://www.scribd.com/embeds/272220183/content?start_page=1"></iframe>',
        '[scribd url="https://www.scribd.com/doc/272220183/"]',
    ],
    'SoundCloud' => [
        SoundCloud::class,
        [
            '<iframe src="https://example.test/x"></iframe>',
            '<iframe src="https://w.soundcloud.com/player/?visual=true"></iframe>',
        ],
        '<iframe src="https://w.soundcloud.com/player/?url=https%3A%2F%2Fapi.soundcloud.com%2Ftracks%2F1&amp;visual=true"></iframe>',
        '[soundcloud url="https://api.soundcloud.com/tracks/1" type="visual" autoplay="0"]',
    ],
    'Vimeo' => [
        Vimeo::class,
        [
            '<iframe src="https://example.test/x"></iframe>',
            '<iframe src="https://player.vimeo.com/video/"></iframe>',
        ],
        '<iframe src="https://player.vimeo.com/video/123"></iframe>',
        '[vimeo url="https://vimeo.com/123"]',
    ],
    'YouTube' => [
        YouTube::class,
        [
            '<iframe src="https://example.test/x"></iframe>',
            '<iframe src="https://www.youtube.com/watch?v=1"></iframe>',
        ],
        '<iframe src="https://www.youtube.com/embed/abc"></iframe>',
        '[youtube url="https://www.youtube.com/watch?v=abc"]',
    ],
    'YouTubeUI' => [
        YouTubeUI::class,
        [
            '<iframe src="https://example.test/x"></iframe>',
            '<iframe src="https://www.youtube.com/watch?v=1"></iframe>',
        ],
        '<iframe src="https://www.youtube.com/embed/abc"></iframe>',
        '[youtubeui url="https://www.youtube.com/watch?v=abc"]',
    ],
]);
