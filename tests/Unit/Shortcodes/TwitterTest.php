<?php

/**
 * Twitter shortcode: the blockquote `callback()` returns, `reversal()` and the Shortcake arguments.
 *
 * @see inc/shortcodes/class-twitter.php
 */

declare(strict_types=1);

use UAMS_Shortcakes\Shortcodes\Twitter;

use function Brain\Monkey\Functions\when;

beforeEach(function (): void {
    stubWordPress();
});

it('uses the lowercase class name as its shortcode tag', function (): void {
    expect(Twitter::get_shortcode_tag())->toBe('twitter');
});

it('describes its Shortcake arguments', function (): void {
    $args = Twitter::get_shortcode_ui_args();

    expect($args['label'])->toBe('Twitter')
        ->and($args['listItemImage'])->toBe('<img src="https://example.test/wp-content/plugins/uamswp-shortcodes/assets/images/svg/icon-twitter.svg" />')
        ->and($args['attrs'])->toBe([
            [
                'label' => 'URL',
                'attr' => 'url',
                'type' => 'text',
                'description' => 'URL to a tweet',
            ],
        ]);
});

it('has no separate get_attributes list', function (): void {
    expect(Twitter::get_attributes())->toBe([]);
});

it('returns an empty string without a url', function (): void {
    expect(Twitter::callback([]))->toBe('')
        ->and(Twitter::callback(['url' => '']))->toBe('');
});

it('returns an empty string for a url that is not a tweet', function (string $url): void {
    expect(Twitter::callback(['url' => $url]))->toBe('');
})->with([
    'profile page' => 'https://twitter.com/user',
    'other host' => 'https://example.com/user/status/1',
    'missing id segment' => 'https://twitter.com/user/status/',
    'x.com host' => 'https://x.com/user/status/1',
]);

it('renders the tweet blockquote and widget script', function (): void {
    expect(Twitter::callback(['url' => 'https://twitter.com/uamshealth/status/12345']))
        ->toBe('<blockquote class="twitter-tweet"><a href="https://twitter.com/uamshealth/status/12345">Tweet from @uamshealth</a></blockquote><script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>');
});

it('accepts an http url', function (): void {
    expect(Twitter::callback(['url' => 'http://twitter.com/user/status/9']))
        ->toContain('Tweet from @user');
});

it('does not anchor the pattern, so a tweet url inside a longer string is accepted', function (): void {
    expect(Twitter::callback(['url' => 'see https://twitter.com/user/status/9 now']))
        ->toContain('Tweet from @user');
});

it('ignores the inner content', function (): void {
    expect(Twitter::callback(['url' => 'https://twitter.com/user/status/9'], 'ignored'))
        ->not->toContain('ignored');
});

it('passes the url through esc_url and the label through esc_html__', function (): void {
    when('esc_url')->alias(fn (string $url): string => 'ESC['.$url.']');
    when('esc_html__')->alias(fn (string $text): string => str_replace('Tweet from', 'TWEET FROM', $text));

    expect(Twitter::callback(['url' => 'https://twitter.com/user/status/9']))
        ->toContain('<a href="ESC[https://twitter.com/user/status/9]">TWEET FROM @user</a>');
});

it('turns an embedded tweet and its widget script into a shortcode', function (): void {
    $embed = '<blockquote class="twitter-tweet"><p>Hello</p>&mdash; UAMS (@uams) <a href="https://twitter.com/uams/status/555">June 1</a></blockquote>'
        ."\n".'<script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>';

    expect(Twitter::reversal('Intro '.$embed.' outro'))->toBe('Intro [twitter url="https://twitter.com/uams/status/555"] outro');
});

it('also reverses a twitter-video embed', function (): void {
    $embed = '<blockquote class="twitter-video"><p>Clip</p><a href="https://twitter.com/uams/status/7">link</a></blockquote><script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>';

    expect(Twitter::reversal($embed))->toBe('[twitter url="https://twitter.com/uams/status/7"]');
});

it('returns content without a script tag unchanged', function (): void {
    $content = '<blockquote class="twitter-tweet"><a href="https://twitter.com/u/status/1">x</a></blockquote>';

    expect(Twitter::reversal($content))->toBe($content);
});

it('returns content with an unrelated script unchanged', function (): void {
    $content = '<script src="https://example.com/a.js"></script>';

    expect(Twitter::reversal($content))->toBe($content);
});

it('passes the reversed url through esc_url_raw', function (): void {
    when('esc_url_raw')->alias(fn (string $url): string => 'RAW['.$url.']');
    $embed = '<blockquote class="twitter-tweet"><a href="https://twitter.com/u/status/1">x</a></blockquote><script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>';

    expect(Twitter::reversal($embed))->toBe('[twitter url="RAW[https://twitter.com/u/status/1]"]');
});
