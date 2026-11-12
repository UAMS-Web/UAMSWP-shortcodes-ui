<?php

/**
 * PDF shortcode: viewer markup, attachment and URL sources, proxy and hooks.
 *
 * @see inc/shortcodes/class-pdf.php
 */

declare(strict_types=1);

use UAMS_Shortcakes\Shortcodes\PDF;

use function Brain\Monkey\Functions\stubs;
use function Brain\Monkey\Functions\when;

beforeEach(function (): void {
    stubWordPress();
    stubs([
        'esc_url' => fn ($url) => $url,
        'get_post_mime_type' => 'application/pdf',
        'wp_get_attachment_url' => fn ($id): string => 'https://files.test/doc-'.$id.'.pdf',
        'add_query_arg' => fn (...$args): string => is_array($args[0]) ? $args[1].'?'.http_build_query($args[0]) : $args[2].'?'.$args[0].'='.$args[1],
        'wp_create_nonce' => fn ($action): string => 'nonce',
        'admin_url' => fn ($path): string => 'https://site.test/wp-admin/'.$path,
        '_n_noop' => fn ($single, $plural, $domain): array => [$single, $plural, $domain],
    ]);
});

const PDF_VIEWER = 'https://example.test/wp-content/plugins/uamswp-shortcodes/assets/pdf-viewer/viewer.html';

/**
 * Answer the given filters with fixed values and every other one with its input.
 *
 * @param  array<string, mixed>  $values  Filter tag to the value it returns
 */
function pdfFilters(array $values): void
{
    when('apply_filters')->alias(fn ($tag, $value) => array_key_exists($tag, $values) ? $values[$tag] : $value);
}

it('uses the lowercase class name as its shortcode tag', function (): void {
    expect(PDF::get_shortcode_tag())->toBe('pdf');
});

it('renders nothing without an attachment or url', function (): void {
    expect(PDF::callback([]))->toBe('');
});

it('embeds an attachment through the first-party PDF viewer', function (): void {
    expect(PDF::callback(['attachment' => '12']))->toBe(
        '<iframe class="uams-shortcodes-responsive" data-true-height="800px" data-true-width="600px" width="600px" height="800px" frameBorder="0" src="'
        .PDF_VIEWER.'?file='.rawurlencode('https://files.test/doc-12.pdf').'"></iframe>'
    );
});

it('embeds a url through the first-party PDF viewer', function (): void {
    expect(PDF::callback(['url' => 'https://other.test/a.pdf']))
        ->toContain('src="'.PDF_VIEWER.'?file='.rawurlencode('https://other.test/a.pdf').'"');
});

it('falls back to the url when the attachment is not a PDF', function (): void {
    when('get_post_mime_type')->justReturn('image/png');

    expect(PDF::callback(['attachment' => '12', 'url' => 'https://other.test/a.pdf']))->toContain(rawurlencode('https://other.test/a.pdf'))
        ->and(PDF::callback(['attachment' => '12']))->toBe('');
});

it('prefers the attachment over the url when both are given', function (): void {
    expect(PDF::callback(['attachment' => '12', 'url' => 'https://other.test/a.pdf']))->toContain(rawurlencode('https://files.test/doc-12.pdf'));
});

it('renders nothing when the file is not a PDF by extension', function (): void {
    expect(PDF::callback(['url' => 'https://other.test/a.txt']))->toBe('')
        ->and(PDF::callback(['url' => 'https://other.test/noext']))->toBe('');
});

it('matches the pdf extension case-insensitively', function (): void {
    expect(PDF::callback(['url' => 'https://other.test/A.PDF']))->toContain('<iframe');
});

it('accepts a protocol-relative url', function (): void {
    expect(PDF::callback(['url' => '//other.test/a.pdf']))->toContain(rawurlencode('//other.test/a.pdf'));
});

it('does not proxy the url unless asked', function (): void {
    pdfFilters(['uams_shortcodes_pdf_enable_cors_proxy' => true]);

    expect(PDF::callback(['url' => 'https://other.test/a.pdf', 'proxy' => '']))->not->toContain(rawurlencode('admin-ajax.php'));
});

it('ignores the proxy attribute while the CORS proxy filter is off', function (): void {
    $nonces = 0;
    when('wp_create_nonce')->alias(function () use (&$nonces): string {
        $nonces++;

        return 'nonce';
    });

    expect(PDF::callback(['url' => 'https://other.test/a.pdf', 'proxy' => '1']))
        ->toContain('file='.rawurlencode('https://other.test/a.pdf'))
        ->not->toContain(rawurlencode('admin-ajax.php'))
        ->and($nonces)->toBe(0);
});

it('routes the url through the admin-ajax proxy when proxy is set and the filter is on', function (): void {
    pdfFilters(['uams_shortcodes_pdf_enable_cors_proxy' => true]);
    $proxied = 'https://site.test/wp-admin/admin-ajax.php?action=uams_shortcodes_asset_proxy&_nonce=nonce&url='.rawurlencode('https://other.test/a.pdf');

    expect(PDF::callback(['url' => 'https://other.test/a.pdf', 'proxy' => 'true']))
        ->toContain('file='.rawurlencode($proxied));
});

it('creates the proxy nonce for the original url', function (): void {
    pdfFilters(['uams_shortcodes_pdf_enable_cors_proxy' => true]);
    $actions = [];
    when('wp_create_nonce')->alias(function (string $action) use (&$actions): string {
        $actions[] = $action;

        return 'nonce';
    });

    PDF::callback(['url' => 'https://other.test/a.pdf', 'proxy' => '1']);

    expect($actions)->toBe(['asset-proxy-https://other.test/a.pdf']);
});

it('lets the proxy url filter replace the proxy address', function (): void {
    pdfFilters([
        'uams_shortcodes_pdf_enable_cors_proxy' => true,
        'uams_shortcodes_pdf_asset_proxy_url' => 'https://proxy.test/a.pdf',
    ]);

    expect(PDF::callback(['url' => 'https://other.test/a.pdf', 'proxy' => '1']))->toContain('file='.rawurlencode('https://proxy.test/a.pdf'));
});

it('describes its Shortcake attributes without the proxy option by default', function (): void {
    $args = PDF::get_shortcode_ui_args();

    expect($args['label'])->toBe('PDF')
        ->and($args['listItemImage'])->toBe('dashicons-media-document')
        ->and(array_column($args['attrs'], 'attr'))->toBe(['attachment', 'url']);
});

it('adds the proxy option when the CORS proxy filter is enabled', function (): void {
    when('apply_filters')->alias(
        fn ($tag, $value) => $tag === 'uams_shortcodes_pdf_enable_cors_proxy' ? true : $value
    );

    $attrs = PDF::get_shortcode_ui_args()['attrs'];

    expect(array_column($attrs, 'attr'))->toBe(['attachment', 'url', 'proxy'])
        ->and($attrs[2]['type'])->toBe('checkbox');
});

it('hooks the media editor and mime type filters', function (): void {
    $hooks = [];
    when('add_filter')->alias(function (string $tag, $callback, int $priority = 10, int $args = 1) use (&$hooks): bool {
        $hooks[$tag] = [$priority, $args];

        return true;
    });

    PDF::setup_actions();

    expect($hooks)->toBe([
        'media_send_to_editor' => [10, 2],
        'post_mime_types' => [10, 1],
    ]);
});

it('replaces the editor markup for a PDF attachment with the shortcode', function (): void {
    expect(PDF::filter_media_send_to_editor('<a>x</a>', 7))->toBe('[pdf attachment="7" /]');
});

it('keeps the editor markup for other attachments', function (): void {
    when('get_post_mime_type')->justReturn('image/png');

    expect(PDF::filter_media_send_to_editor('<a>x</a>', 7))->toBe('<a>x</a>');
});

it('adds PDF documents to the mime type filters once', function (): void {
    $types = PDF::filter_post_mime_types(['image' => ['Images']]);

    expect($types)->toHaveKeys(['image', 'application/pdf'])
        ->and($types['application/pdf'][0])->toBe('PDF documents')
        ->and($types['application/pdf'][1])->toBe('Manage PDFs')
        ->and($types['application/pdf'][2][0])->toBe('PDF <span class="count">(%s)</span>');
});

it('does not overwrite an existing PDF mime type entry', function (): void {
    expect(PDF::filter_post_mime_types(['application/pdf' => ['Mine']])['application/pdf'])->toBe(['Mine']);
});
