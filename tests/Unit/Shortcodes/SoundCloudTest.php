<?php

/**
 * SoundCloud shortcode: the player iframe for each type and autoplay setting,
 * host validation and the reversal of SoundCloud player iframes.
 *
 * @see inc/shortcodes/class-soundcloud.php
 */

declare(strict_types=1);

use UAMS_Shortcakes\Shortcodes\SoundCloud;

use function Brain\Monkey\Functions\when;

beforeEach(function (): void {
    stubWordPress();

    // Same contract as WordPress' add_query_arg() for the (key, value, url) form.
    when('add_query_arg')->alias(
        fn (string $key, string $value, string $url): string => $url.(str_contains($url, '?') ? '&' : '?').http_build_query([$key => $value])
    );
});

it('uses the lowercase class name as its shortcode tag', function (): void {
    expect(SoundCloud::get_shortcode_tag())->toBe('soundcloud');
});

it('renders the simple player at 166 pixels by default', function (): void {
    expect(SoundCloud::callback(['url' => 'https://soundcloud.com/artist/track']))
        ->toBe('<iframe width="100%" height="166" scrolling="no" frameborder="no" src="https://w.soundcloud.com/player/?url=https%3A%2F%2Fsoundcloud.com%2Fartist%2Ftrack"></iframe>');
});

it('renders the visual player at 450 pixels', function (): void {
    expect(SoundCloud::callback(['url' => 'https://soundcloud.com/artist/track', 'type' => 'visual']))
        ->toContain('height="450"')
        ->toContain('track&visual=true"');
});

it('treats any type other than visual as simple', function (): void {
    expect(SoundCloud::callback(['url' => 'https://soundcloud.com/artist/track', 'type' => 'simple']))
        ->toContain('height="166"')
        ->not->toContain('visual=true');
});

it('adds auto_play when autoplay is truthy', function (): void {
    expect(SoundCloud::callback(['url' => 'https://soundcloud.com/artist/track', 'autoplay' => '1']))
        ->toContain('track&auto_play=true"');
});

it('does not autoplay for 0 or an empty value', function (string $value): void {
    expect(SoundCloud::callback(['url' => 'https://soundcloud.com/artist/track', 'autoplay' => $value]))
        ->not->toContain('auto_play');
})->with(['0', '']);

it('combines the visual and autoplay arguments in that order', function (): void {
    expect(SoundCloud::callback(['url' => 'https://soundcloud.com/a/t', 'type' => 'visual', 'autoplay' => '1']))
        ->toContain('t&visual=true&auto_play=true"');
});

it('accepts the API host', function (): void {
    expect(SoundCloud::callback(['url' => 'https://api.soundcloud.com/tracks/123']))
        ->toContain('url=https%3A%2F%2Fapi.soundcloud.com%2Ftracks%2F123');
});

it('renders nothing for an empty url or a host that is not SoundCloud', function (string $url): void {
    expect(SoundCloud::callback(['url' => $url]))->toBe('');
})->with([
    'empty' => '',
    'other site' => 'https://example.com/artist/track',
    'www subdomain' => 'https://www.soundcloud.com/artist/track',
    'look-alike' => 'https://soundcloud.com.evil.test/artist/track',
]);

it('currently warns about an undefined url key when the attribute is missing', function (): void {
    $errors = [];
    set_error_handler(function (int $no, string $message) use (&$errors): bool {
        $errors[] = $message;

        return true;
    });

    try {
        $html = SoundCloud::callback([]);
    } finally {
        restore_error_handler();
    }

    expect($html)->toBe('')
        ->and($errors[0])->toBe('Undefined array key "url"');
});

it('describes its Shortcake attributes', function (): void {
    $args = SoundCloud::get_shortcode_ui_args();

    expect($args['label'])->toBe('SoundCloud')
        ->and($args['listItemImage'])->toBe('<img src="https://example.test/wp-content/plugins/uamswp-shortcodes/assets/images/svg/icon-soundcloud.svg" />')
        ->and(array_column($args['attrs'], 'attr'))->toBe(['url', 'type', 'autoplay'])
        ->and(array_keys($args['attrs'][1]['options']))->toBe(['simple', 'visual'])
        ->and(array_keys($args['attrs'][2]['options']))->toBe([0, 1]);
});

it('turns a SoundCloud player iframe into a shortcode', function (): void {
    $content = '<iframe width="100%" height="166" src="https://w.soundcloud.com/player/?url=https%3A%2F%2Fapi.soundcloud.com%2Ftracks%2F123"></iframe>';

    expect(SoundCloud::reversal($content))
        ->toBe('[soundcloud url="https://api.soundcloud.com/tracks/123" type="simple" autoplay="0"]');
});

it('keeps the visual and auto_play flags when reversing', function (): void {
    $content = '<iframe src="https://w.soundcloud.com/player/?url=https%3A%2F%2Fapi.soundcloud.com%2Ftracks%2F123&amp;visual=true&amp;auto_play=true"></iframe>';

    expect(SoundCloud::reversal($content))
        ->toBe('[soundcloud url="https://api.soundcloud.com/tracks/123" type="visual" autoplay="1"]');
});

it('treats flags other than the string true as off when reversing', function (): void {
    $content = '<iframe src="https://w.soundcloud.com/player/?url=https%3A%2F%2Fapi.soundcloud.com%2Ftracks%2F123&amp;visual=false&amp;auto_play=1"></iframe>';

    expect(SoundCloud::reversal($content))
        ->toBe('[soundcloud url="https://api.soundcloud.com/tracks/123" type="simple" autoplay="0"]');
});

it('leaves a SoundCloud iframe without a url argument alone', function (): void {
    $content = '<iframe src="https://w.soundcloud.com/player/?visual=true"></iframe>';

    expect(SoundCloud::reversal($content))->toBe($content);
});

it('leaves iframes from other hosts alone', function (): void {
    $content = '<iframe src="https://example.com/player/?url=x"></iframe>';

    expect(SoundCloud::reversal($content))->toBe($content);
});

it('returns content without iframes unchanged', function (): void {
    expect(SoundCloud::reversal('<p>Plain</p>'))->toBe('<p>Plain</p>');
});
