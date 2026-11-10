<?php

/**
 * YouTube shortcode: embed id extraction for watch and short urls, playlist
 * handling, the embed url filter and the reversal of YouTube iframes.
 *
 * @see inc/shortcodes/class-youtube.php
 */

declare(strict_types=1);

use UAMS_Shortcakes\Shortcodes\YouTube;

use function Brain\Monkey\Functions\when;

beforeEach(function (): void {
    stubWordPress();

    // Same contract as WordPress' add_query_arg() for the (key, value, url) form.
    when('add_query_arg')->alias(
        fn (string $key, string $value, string $url): string => $url.(str_contains($url, '?') ? '&' : '?').http_build_query([$key => $value])
    );
});

it('uses the lowercase class name as its shortcode tag', function (): void {
    expect(YouTube::get_shortcode_tag())->toBe('youtube');
});

it('renders an iframe for a watch url', function (string $url): void {
    expect(YouTube::callback(['url' => $url]))
        ->toBe('<iframe class="shortcake-bakery-responsive" width="640" height="360" src="https://youtube.com/embed/hDlpVFDmXrc" frameborder="0"></iframe>');
})->with([
    'www' => 'https://www.youtube.com/watch?v=hDlpVFDmXrc',
    'bare host' => 'https://youtube.com/watch?v=hDlpVFDmXrc',
    'extra args' => 'https://www.youtube.com/watch?feature=share&v=hDlpVFDmXrc',
    'encoded ampersand' => 'https://www.youtube.com/watch?feature=share&amp;v=hDlpVFDmXrc',
    'numeric ampersand' => 'https://www.youtube.com/watch?feature=share&#038;v=hDlpVFDmXrc',
]);

it('takes the embed id from the path of a short url', function (): void {
    expect(YouTube::callback(['url' => 'https://youtu.be/nc7F_qv3eI8']))->toContain('src="https://youtube.com/embed/nc7F_qv3eI8"')
        ->and(YouTube::callback(['url' => 'https://youtu.be/nc7F_qv3eI8/']))->toContain('src="https://youtube.com/embed/nc7F_qv3eI8"');
});

it('renders nothing for a short url with no path', function (): void {
    expect(YouTube::callback(['url' => 'https://youtu.be/']))->toBe('')
        ->and(YouTube::callback(['url' => 'https://youtu.be']))->toBe('');
});

it('adds the playlist id to the embed url', function (): void {
    expect(YouTube::callback(['url' => 'https://www.youtube.com/watch?v=abc&list=PL123']))
        ->toContain('src="https://youtube.com/embed/abc?list=PL123"');
});

it('ignores a playlist id on a short url', function (): void {
    expect(YouTube::callback(['url' => 'https://youtu.be/abc?list=PL123']))
        ->toContain('src="https://youtube.com/embed/abc"');
});

it('renders nothing for a watch url without a v argument', function (): void {
    expect(YouTube::callback(['url' => 'https://www.youtube.com/watch?list=PL123']))->toBe('');
});

it('renders nothing for a url with no query string without a deprecation', function (): void {
    $errors = [];
    set_error_handler(function (int $no, string $message) use (&$errors): bool {
        $errors[] = $message;

        return true;
    });

    try {
        $html = YouTube::callback(['url' => 'https://www.youtube.com/channel/abc']);
    } finally {
        restore_error_handler();
    }

    expect($html)->toBe('')
        ->and($errors)->toBe([]);
});

it('renders nothing for an empty url', function (): void {
    expect(YouTube::callback(['url' => '']))->toBe('');
});

it('renders nothing for a host that is not YouTube', function (string $url): void {
    expect(YouTube::callback(['url' => $url]))->toBe('');
})->with([
    'other site' => 'https://example.com/watch?v=abc',
    'look-alike' => 'https://youtube.com.evil.test/watch?v=abc',
    'subdomain' => 'https://m.youtube.com/watch?v=abc',
]);

it('renders nothing without a warning when the url attribute is missing', function (): void {
    $errors = [];
    set_error_handler(function (int $no, string $message) use (&$errors): bool {
        $errors[] = $message;

        return true;
    });

    try {
        $html = YouTube::callback([]);
    } finally {
        restore_error_handler();
    }

    expect($html)->toBe('')
        ->and($errors)->toBe([]);
});

it('lets a filter change the embed url and passes it the attributes', function (): void {
    $seen = [];
    when('apply_filters')->alias(function (string $tag, $value, ...$rest) use (&$seen): string {
        $seen = [$tag, $value, $rest];

        return 'https://youtube-nocookie.com/embed/changed';
    });

    $attrs = ['url' => 'https://youtu.be/abc'];

    expect(YouTube::callback($attrs))->toContain('src="https://youtube-nocookie.com/embed/changed"')
        ->and($seen)->toBe(['uams_shortcodes_youtube_embed_url', 'https://youtube.com/embed/abc', [$attrs]]);
});

it('describes its Shortcake attributes', function (): void {
    $args = YouTube::get_shortcode_ui_args();

    expect($args['label'])->toBe('YouTube')
        ->and($args['listItemImage'])->toBe('<img src="https://example.test/wp-content/plugins/uamswp-shortcodes/assets/images/svg/icon-youtube.svg" />')
        ->and(array_column($args['attrs'], 'attr'))->toBe(['url']);
});

it('turns a YouTube iframe into a shortcode', function (): void {
    $content = '<iframe width="560" height="315" src="https://www.youtube.com/embed/abc123" frameborder="0"></iframe>';

    expect(YouTube::reversal($content))->toBe('[youtube url="https://www.youtube.com/watch?v=abc123"]');
});

it('drops the query string of an embed url when reversing', function (): void {
    $content = '<iframe src="https://www.youtube.com/embed/abc123?rel=0&amp;list=PL1"></iframe>';

    expect(YouTube::reversal($content))->toBe('[youtube url="https://www.youtube.com/watch?v=abc123"]');
});

it('leaves iframes from other hosts alone', function (): void {
    $content = '<iframe src="https://example.com/embed/abc123"></iframe>';

    expect(YouTube::reversal($content))->toBe($content);
});

it('leaves a YouTube iframe whose path is not an embed alone', function (): void {
    $content = '<iframe src="https://www.youtube.com/watch?v=abc123"></iframe>';

    expect(YouTube::reversal($content))->toBe($content);
});

it('returns content without iframes unchanged', function (): void {
    expect(YouTube::reversal('<p>Plain</p>'))->toBe('<p>Plain</p>');
});
