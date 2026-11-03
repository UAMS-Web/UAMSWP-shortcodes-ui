<?php

/**
 * Asset_Proxy: the singleton and the AJAX hooks it registers.
 *
 * handle_asset_proxy() ends every path in exit, so it cannot run inside the
 * test process; the coverage ticket records that remainder.
 *
 * @see inc/class-asset-proxy.php
 */

declare(strict_types=1);

use UAMS_Shortcakes\Asset_Proxy;

use function Brain\Monkey\Functions\when;

it('registers the proxy for logged-in and logged-out requests once', function (): void {
    require_once dirname(__DIR__, 2).'/inc/class-asset-proxy.php';
    $hooks = [];
    when('add_action')->alias(function (string $hook) use (&$hooks): void {
        $hooks[] = $hook;
    });

    $first = Asset_Proxy::get_instance();

    expect(Asset_Proxy::get_instance())->toBe($first)
        ->and($hooks)->toBe(['wp_ajax_uams_shortcodes_asset_proxy', 'wp_ajax_nopriv_uams_shortcodes_asset_proxy']);
});
