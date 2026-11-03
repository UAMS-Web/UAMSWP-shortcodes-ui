<?php

/**
 * Scribd shortcode: the iframe `callback()` returns, `reversal()` and the Shortcake arguments.
 *
 * @see inc/shortcodes/class-scribd.php
 */

declare(strict_types=1);

use UAMS_Shortcakes\Shortcodes\Scribd;

use function Brain\Monkey\Functions\when;

beforeEach(function (): void {
    stubWordPress();
});

it('uses the lowercase class name as its shortcode tag', function (): void {
    expect(Scribd::get_shortcode_tag())->toBe('scribd');
});

it('describes its Shortcake arguments', function (): void {
    $args = Scribd::get_shortcode_ui_args();

    expect($args['label'])->toBe('Scribd')
        ->and($args['listItemImage'])->toBe('<img src="https://example.test/wp-content/plugins/uamswp-shortcodes/assets/images/svg/icon-scribd.svg" />')
        ->and($args['attrs'])->toBe([
            [
                'label' => 'URL',
                'attr' => 'url',
                'type' => 'text',
                'description' => 'Full URL to the Scribd document',
            ],
        ]);
});

it('has no separate get_attributes list', function (): void {
    expect(Scribd::get_attributes())->toBe([]);
});

it('returns an empty string without a url, even for an editor', function (): void {
    when('current_user_can')->justReturn(true);

    expect(Scribd::callback([]))->toBe('')
        ->and(Scribd::callback(['url' => '']))->toBe('');
});

it('renders the embed iframe for a doc url', function (): void {
    expect(Scribd::callback(['url' => 'https://www.scribd.com/doc/272220183/Some-Title']))
        ->toBe('<iframe class="scribd_iframe_embed uams-shortcodes-responsive" src="https://www.scribd.com/embeds/272220183/content?start_page=1&view_mode=scroll&access_key=key-ooxdrkmSg8ieauz9qYXL&show_recommendations=true" data-auto-height="false" data-aspect-ratio="0.7631133671742809" scrolling="no" width="100%" height="600" frameborder="0"></iframe>');
});

it('accepts a document url', function (): void {
    expect(Scribd::callback(['url' => 'http://www.scribd.com/document/99/Title']))
        ->toContain('src="https://www.scribd.com/embeds/99/content?');
});

it('uses an empty id for a doc url whose digits follow the word directly', function (): void {
    // The slash after "doc" is optional in the pattern, which shifts the explode() index.
    expect(Scribd::callback(['url' => 'https://www.scribd.com/doc123/x']))
        ->toContain('src="https://www.scribd.com/embeds//content?');
});

it('shows an error to a user who can edit posts when the url is not a Scribd document', function (): void {
    when('current_user_can')->alias(fn (string $cap): bool => $cap === 'edit_posts');

    expect(Scribd::callback(['url' => 'https://example.com/doc/1/x']))
        ->toBe('<div class="uams-shortcodes-error"><p>Invalid Scribd URL: https://example.com/doc/1/x</p></div>');
});

it('returns an empty string to other users when the url is not a Scribd document', function (): void {
    when('current_user_can')->justReturn(false);

    expect(Scribd::callback(['url' => 'https://example.com/doc/1/x']))->toBe('');
});

it('treats a Scribd doc url with no trailing path as invalid', function (): void {
    when('current_user_can')->justReturn(false);

    expect(Scribd::callback(['url' => 'https://www.scribd.com/doc/123']))->toBe('');
});

it('passes the embed url and the error url through esc_url', function (): void {
    when('esc_url')->alias(fn (string $url): string => 'ESC['.$url.']');
    when('current_user_can')->justReturn(true);

    expect(Scribd::callback(['url' => 'https://www.scribd.com/doc/5/x']))
        ->toContain('src="ESC[https://www.scribd.com/embeds/5/content?')
        ->and(Scribd::callback(['url' => 'bad']))->toContain('Invalid Scribd URL: ESC[bad]');
});

it('turns a Scribd embed iframe into a doc shortcode', function (): void {
    $content = '<iframe src="https://www.scribd.com/embeds/272220183/content?start_page=1&amp;view_mode=scroll&amp;show_recommendations=true" width="100%" height="600"></iframe>';

    expect(Scribd::reversal($content))->toBe('[scribd url="https://www.scribd.com/doc/272220183/"]');
});

it('leaves an iframe from another host in place', function (): void {
    $content = '<iframe src="https://example.com/embeds/1/content?x=1"></iframe>';

    expect(Scribd::reversal($content))->toBe($content);
});

it('returns content without an iframe unchanged', function (): void {
    expect(Scribd::reversal('<p>plain</p>'))->toBe('<p>plain</p>');
});

it('passes the reversed url through esc_url_raw', function (): void {
    when('esc_url_raw')->alias(fn (string $url): string => 'RAW['.$url.']');

    expect(Scribd::reversal('<iframe src="https://scribd.com/embeds/8/content?a=1"></iframe>'))
        ->toBe('[scribd url="RAW[https://scribd.com/doc/8/]"]');
});
