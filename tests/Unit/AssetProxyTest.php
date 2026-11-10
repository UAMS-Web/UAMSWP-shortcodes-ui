<?php

/**
 * Asset_Proxy: the singleton, the AJAX hooks it registers, the URL allow list
 * and the fetch that relays a PDF.
 *
 * handle_asset_proxy() ends every path in exit, so it cannot run inside the
 * test process; the coverage ticket records that remainder.
 *
 * @see inc/class-asset-proxy.php
 */

declare(strict_types=1);

use Brain\Monkey\Functions;
use UAMS_Shortcakes\Asset_Proxy;

use function Brain\Monkey\Functions\stubs;
use function Brain\Monkey\Functions\when;

/**
 * Stand-in for WP_Error so is_wp_error() can tell a failed request apart.
 */
class Asset_Proxy_WP_Error_Stub {}

beforeEach(function (): void {
    require_once dirname(__DIR__, 2).'/inc/class-asset-proxy.php';
    when('add_action')->justReturn(true);
    stubs([
        'home_url' => 'https://www.example.test/',
        'site_url' => 'https://Example.test/wp',
        'wp_get_upload_dir' => ['baseurl' => 'https://files.example.test/uploads'],
        'apply_filters' => fn ($tag, $value) => $value,
        'wp_unslash' => fn ($value) => $value,
    ]);
    when('is_wp_error')->alias(fn ($thing): bool => $thing instanceof Asset_Proxy_WP_Error_Stub);
});

/**
 * Answer the given filters with fixed values and every other one with its input.
 *
 * @param  array<string, mixed>  $values  Filter tag to the value it returns
 */
function assetProxyFilters(array $values): void
{
    when('apply_filters')->alias(fn ($tag, $value) => array_key_exists($tag, $values) ? $values[$tag] : $value);
}

/**
 * A response array in the shape wp_safe_remote_get() returns, read by the retrieve stubs.
 *
 * @return array<string, mixed>
 */
function assetProxyResponse(int $code = 200, string|array $content_type = 'application/pdf', string $body = '%PDF-1.7'): array
{
    stubs([
        'wp_remote_retrieve_response_code' => fn ($response) => $response['code'],
        'wp_remote_retrieve_header' => fn ($response, $header) => $response['headers'][$header] ?? '',
        'wp_remote_retrieve_body' => fn ($response) => $response['body'],
    ]);

    return ['code' => $code, 'headers' => ['content-type' => $content_type], 'body' => $body];
}

it('registers the proxy for logged-in and logged-out requests once', function (): void {
    $hooks = [];
    when('add_action')->alias(function (string $hook) use (&$hooks): void {
        $hooks[] = $hook;
    });

    $first = Asset_Proxy::get_instance();

    expect(Asset_Proxy::get_instance())->toBe($first)
        ->and($hooks)->toBe(['wp_ajax_uams_shortcodes_asset_proxy', 'wp_ajax_nopriv_uams_shortcodes_asset_proxy']);
});

it('is disabled until the CORS proxy filter enables it', function (): void {
    expect(Asset_Proxy::is_enabled())->toBeFalse();

    assetProxyFilters(['uams_shortcodes_pdf_enable_cors_proxy' => true]);

    expect(Asset_Proxy::is_enabled())->toBeTrue();
});

it('reports a boolean for a filter that returns a truthy or falsy value that is not one', function (): void {
    assetProxyFilters(['uams_shortcodes_pdf_enable_cors_proxy' => '1']);
    expect(Asset_Proxy::is_enabled())->toBeTrue();

    assetProxyFilters(['uams_shortcodes_pdf_enable_cors_proxy' => '']);
    expect(Asset_Proxy::is_enabled())->toBeFalse();
});

it('allows the home, site and uploads hosts by default, lowercased and deduplicated', function (): void {
    expect(Asset_Proxy::allowed_hosts())->toBe(['www.example.test', 'example.test', 'files.example.test']);
});

it('lets the allowed hosts filter replace the list and drops entries that are not strings', function (): void {
    assetProxyFilters(['uams_shortcodes_asset_proxy_allowed_hosts' => ['CDN.example.org', 42, null]]);

    expect(Asset_Proxy::allowed_hosts())->toBe(['cdn.example.org']);
});

it('allows no host when the filter returns something other than a list', function (): void {
    assetProxyFilters(['uams_shortcodes_asset_proxy_allowed_hosts' => 'cdn.example.org']);

    expect(Asset_Proxy::allowed_hosts())->toBe([])
        ->and(Asset_Proxy::is_allowed_url('https://cdn.example.org/a.pdf'))->toBeFalse();
});

it('allows an http or https PDF on an allowed host, whatever the case', function (string $url): void {
    expect(Asset_Proxy::is_allowed_url($url))->toBeTrue();
})->with([
    'https://www.example.test/a.pdf',
    'http://example.test/dir/b.PDF',
    'HTTPS://FILES.EXAMPLE.TEST/uploads/2026/c.pdf?v=2#page=3',
]);

it('rejects a URL off the allowed hosts, with credentials, with another scheme or without a .pdf path', function (string $url): void {
    expect(Asset_Proxy::is_allowed_url($url))->toBeFalse();
})->with([
    'https://other.test/a.pdf',
    'https://www.example.test.evil.test/a.pdf',
    'https://user:pass@www.example.test/a.pdf',
    'https://user@www.example.test/a.pdf',
    'ftp://www.example.test/a.pdf',
    'file:///etc/passwd.pdf',
    '//www.example.test/a.pdf',
    'https://www.example.test/a.txt',
    'https://www.example.test/',
    'https://www.example.test/a.pdf.exe',
    'www.example.test/a.pdf',
    '',
]);

it('does not request a URL that is not allowed', function (): void {
    Functions\expect('wp_safe_remote_get')->never();

    expect(Asset_Proxy::get_instance()->fetch_asset('https://other.test/a.pdf'))->toBeNull();
});

it('fetches an allowed PDF with wp_safe_remote_get, no redirects and a size limit one byte over the cap', function (): void {
    $response = assetProxyResponse();
    Functions\expect('wp_safe_remote_get')->once()->with('https://www.example.test/a.pdf', [
        'timeout' => 5,
        'redirection' => 0,
        'limit_response_size' => Asset_Proxy::DEFAULT_MAX_BYTES + 1,
    ])->andReturn($response);

    expect(Asset_Proxy::get_instance()->fetch_asset('https://www.example.test/a.pdf'))->toBe('%PDF-1.7');
});

it('lets the request arguments filter change the request', function (): void {
    $response = assetProxyResponse();
    assetProxyFilters(['uams_shortcodes_asset_proxy_request_args' => ['timeout' => 30]]);
    Functions\expect('wp_safe_remote_get')->once()->with('https://www.example.test/a.pdf', ['timeout' => 30])->andReturn($response);

    expect(Asset_Proxy::get_instance()->fetch_asset('https://www.example.test/a.pdf'))->toBe('%PDF-1.7');
});

it('returns null when the request fails', function (): void {
    assetProxyResponse();
    when('wp_safe_remote_get')->justReturn(new Asset_Proxy_WP_Error_Stub);

    expect(Asset_Proxy::get_instance()->fetch_asset('https://www.example.test/a.pdf'))->toBeNull();
});

it('returns null for a response that is not 200', function (): void {
    when('wp_safe_remote_get')->justReturn(assetProxyResponse(302));

    expect(Asset_Proxy::get_instance()->fetch_asset('https://www.example.test/a.pdf'))->toBeNull();
});

it('returns null for a response that is not a PDF', function (string|array $content_type): void {
    when('wp_safe_remote_get')->justReturn(assetProxyResponse(200, $content_type));

    expect(Asset_Proxy::get_instance()->fetch_asset('https://www.example.test/a.pdf'))->toBeNull();
})->with([
    'text/html; charset=utf-8',
    'application/octet-stream',
    '',
    [['text/html', 'application/pdf']],
]);

it('accepts a PDF content type with parameters or in another case, and the first of repeated headers', function (string|array $content_type): void {
    when('wp_safe_remote_get')->justReturn(assetProxyResponse(200, $content_type));

    expect(Asset_Proxy::get_instance()->fetch_asset('https://www.example.test/a.pdf'))->toBe('%PDF-1.7');
})->with([
    'application/pdf',
    'Application/PDF; charset=binary',
    ' application/pdf ',
    [['application/pdf', 'text/html']],
]);

it('returns null for an empty body', function (): void {
    when('wp_safe_remote_get')->justReturn(assetProxyResponse(200, 'application/pdf', ''));

    expect(Asset_Proxy::get_instance()->fetch_asset('https://www.example.test/a.pdf'))->toBeNull();
});

it('returns null for a body over the size cap and the body at the cap', function (): void {
    assetProxyFilters(['uams_shortcodes_asset_proxy_max_bytes' => 8]);
    when('wp_safe_remote_get')->justReturn(assetProxyResponse(200, 'application/pdf', '%PDF-1.7x'));

    expect(Asset_Proxy::get_instance()->fetch_asset('https://www.example.test/a.pdf'))->toBeNull();

    when('wp_safe_remote_get')->justReturn(assetProxyResponse(200, 'application/pdf', '%PDF-1.7'));

    expect(Asset_Proxy::get_instance()->fetch_asset('https://www.example.test/a.pdf'))->toBe('%PDF-1.7');
});
