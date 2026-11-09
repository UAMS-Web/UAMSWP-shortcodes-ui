<?php

/**
 * Vimeo shortcode: the player markup `callback()` returns, `reversal()` and the Shortcake arguments.
 *
 * @see inc/shortcodes/class-vimeo.php
 */

declare(strict_types=1);

use UAMS_Shortcakes\Shortcodes\Vimeo;

use function Brain\Monkey\Functions\when;

/**
 * Run a callback while collecting the PHP errors it raises.
 *
 * @return array{0: mixed, 1: list<string>}
 */
function vimeoCapture(callable $run): array
{
    $errors = [];
    set_error_handler(function (int $no, string $str) use (&$errors): bool {
        $errors[] = $str;

        return true;
    });

    try {
        $result = $run();
    } finally {
        restore_error_handler();
    }

    return [$result, $errors];
}

beforeEach(function (): void {
    stubWordPress();
});

it('uses the lowercase class name as its shortcode tag', function (): void {
    expect(Vimeo::get_shortcode_tag())->toBe('vimeo');
});

it('describes its Shortcake arguments', function (): void {
    $args = Vimeo::get_shortcode_ui_args();

    expect($args['label'])->toBe('Vimeo')
        ->and($args['listItemImage'])->toBe('<img src="https://example.test/wp-content/plugins/uamswp-shortcodes/assets/images/svg/icon-vimeo.svg" />')
        ->and($args['attrs'])->toBe([
            [
                'label' => 'URL',
                'attr' => 'url',
                'type' => 'text',
                'description' => 'Full Vimeo URL',
            ],
        ]);
});

it('has no separate get_attributes list', function (): void {
    expect(Vimeo::get_attributes())->toBe([]);
});

it('renders the player wrapper for a vimeo.com url', function (): void {
    expect(Vimeo::callback(['url' => 'https://vimeo.com/123456']))
        ->toBe('<div class="nc-video-player" role="region" aria-label="video" tabindex=-1><div class="tube-wrapper"><iframe class="uams-shortcodes-responsive" width="500" height="281" src="https://player.vimeo.com/video/123456" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe></div></div>');
});

it('accepts the www host and keeps only the first path segment as the id', function (): void {
    expect(Vimeo::callback(['url' => 'https://www.vimeo.com/123456/abcdef?x=1']))
        ->toContain('src="https://player.vimeo.com/video/123456"');
});

it('accepts a protocol-relative url', function (): void {
    expect(Vimeo::callback(['url' => '//vimeo.com/42']))->toContain('src="https://player.vimeo.com/video/42"');
});

it('returns an empty string for a host that is not Vimeo', function (string $url): void {
    [$html] = vimeoCapture(fn (): string => Vimeo::callback(['url' => $url]));

    expect($html)->toBe('');
})->with([
    'other site' => 'https://example.com/123',
    'player host' => 'https://player.vimeo.com/video/123',
    'empty url' => '',
]);

it('uses an empty id when the url has only a slash path', function (): void {
    expect(Vimeo::callback(['url' => 'https://vimeo.com/']))->toContain('src="https://player.vimeo.com/video/"');
});

it('raises a missing-key warning when the url attribute is absent, then returns an empty string', function (): void {
    [$html, $errors] = vimeoCapture(fn (): string => Vimeo::callback([]));

    expect($html)->toBe('')
        ->and($errors)->toContain('Undefined array key "url"');
});

it('raises a null-to-trim deprecation for a url with no path', function (): void {
    [$html, $errors] = vimeoCapture(fn (): string => Vimeo::callback(['url' => 'https://vimeo.com']));

    expect($html)->toContain('src="https://player.vimeo.com/video/"')
        ->and($errors)->not->toBeEmpty();
});

it('passes the embed url through esc_url', function (): void {
    when('esc_url')->alias(fn (string $url): string => 'ESC['.$url.']');

    expect(Vimeo::callback(['url' => 'https://vimeo.com/9']))->toContain('src="ESC[https://player.vimeo.com/video/9]"');
});

it('turns a Vimeo player iframe into a shortcode', function (): void {
    $content = 'Watch <iframe src="https://player.vimeo.com/video/123456?h=abc" width="640" height="360"></iframe>';

    expect(Vimeo::reversal($content))->toBe('Watch [vimeo url="https://vimeo.com/123456"]');
});

it('leaves an iframe from another host in place', function (): void {
    $content = '<iframe src="https://example.com/video/1"></iframe>';

    expect(Vimeo::reversal($content))->toBe($content);
});

it('leaves a Vimeo iframe whose path has no second segment in place', function (): void {
    $content = '<iframe src="https://player.vimeo.com/video"></iframe>';

    expect(Vimeo::reversal($content))->toBe($content);
});

it('returns content without an iframe unchanged', function (): void {
    expect(Vimeo::reversal('<p>plain</p>'))->toBe('<p>plain</p>');
});

it('passes the reversed url through esc_url_raw', function (): void {
    when('esc_url_raw')->alias(fn (string $url): string => 'RAW['.$url.']');

    expect(Vimeo::reversal('<iframe src="https://player.vimeo.com/video/77"></iframe>'))
        ->toBe('[vimeo url="RAW[https://vimeo.com/77]"]');
});
