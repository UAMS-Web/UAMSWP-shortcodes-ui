<?php

namespace UAMS_Shortcakes;

/**
 * Proxy handler for remote assets which need local headers to bypass CORS
 * restrictions. Used by the [pdf] shortcode.
 *
 * The handler answers only while the `uams_shortcodes_pdf_enable_cors_proxy`
 * filter enables the proxy, and only for a PDF on a host the
 * `uams_shortcodes_asset_proxy_allowed_hosts` filter allows. Without that
 * filter the list is the site's own hosts, so an external PDF needs the
 * host added before the proxy will fetch it.
 */
class Asset_Proxy
{
    /**
     * Largest PDF the proxy will relay, in bytes, before the
     * `uams_shortcodes_asset_proxy_max_bytes` filter.
     */
    public const DEFAULT_MAX_BYTES = 20971520;

    private static ?Asset_Proxy $instance = null;

    public static function get_instance(): Asset_Proxy
    {
        if (! isset(self::$instance)) {
            self::$instance = new Asset_Proxy;
            self::$instance->setup_actions();
        }

        return self::$instance;
    }

    private function setup_actions(): void
    {
        add_action('wp_ajax_uams_shortcodes_asset_proxy', $this->handle_asset_proxy(...));
        add_action('wp_ajax_nopriv_uams_shortcodes_asset_proxy', $this->handle_asset_proxy(...));
    }

    /**
     * Whether the [pdf] shortcode's CORS proxy option is enabled.
     *
     * The same filter adds the option to the shortcode UI; see PDF::get_shortcode_ui_args().
     */
    public static function is_enabled(): bool
    {
        // @pest-mutate-ignore: RemoveBooleanCast
        return (bool) apply_filters('uams_shortcodes_pdf_enable_cors_proxy', false);
    }

    /**
     * Hosts the proxy may fetch from, lowercased.
     *
     * @return list<string>
     */
    public static function allowed_hosts(): array
    {
        $hosts = [];

        $upload_dir = wp_get_upload_dir();

        // @pest-mutate-ignore: EmptyStringToNotEmpty
        foreach ([home_url(), site_url(), $upload_dir['baseurl'] ?? ''] as $url) {
            // @codingStandardsIgnoreStart
            // @pest-mutate-ignore: RemoveStringCast
            $host = parse_url((string) $url, PHP_URL_HOST);
            // @codingStandardsIgnoreEnd
            // @pest-mutate-ignore: EmptyStringToNotEmpty
            if (is_string($host) && $host !== '') {
                $hosts[] = strtolower($host);
            }
        }

        /*
         * Filter the hosts the asset proxy will fetch a PDF from.
         *
         * Defaults to the hosts of the site, home and uploads URLs. Add the
         * host of any external PDF the [pdf] shortcode should proxy.
         *
         * @param list<string> Hostnames, compared case-insensitively.
         */
        $hosts = apply_filters('uams_shortcodes_asset_proxy_allowed_hosts', array_values(array_unique($hosts)));

        if (! is_array($hosts)) {
            return [];
        }

        return array_values(array_map(strtolower(...), array_filter($hosts, is_string(...))));
    }

    /**
     * Whether a URL is one the proxy may fetch: http or https, no credentials,
     * a .pdf path and a host on the allowed list.
     */
    public static function is_allowed_url(string $url): bool
    {
        // @codingStandardsIgnoreStart
        $parts = parse_url($url);
        // @codingStandardsIgnoreEnd

        if (! is_array($parts) || empty($parts['scheme']) || empty($parts['host']) || isset($parts['user']) || isset($parts['pass'])) {
            return false;
        }

        if (! in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
            return false;
        }

        // @pest-mutate-ignore: EmptyStringToNotEmpty
        if (strtolower(pathinfo($parts['path'] ?? '', PATHINFO_EXTENSION)) !== 'pdf') {
            return false;
        }

        return in_array(strtolower($parts['host']), self::allowed_hosts(), true);
    }

    /**
     * Fetch an allowed PDF, or null when the URL is not allowed, the request
     * fails, the response is not a PDF or the body is over the size cap.
     */
    public function fetch_asset(string $url): ?string
    {
        if (! self::is_allowed_url($url)) {
            return null;
        }

        /*
         * Filter the largest PDF the asset proxy will relay, in bytes.
         *
         * @param int Size cap, 20 MiB by default.
         */
        $max_bytes = (int) apply_filters('uams_shortcodes_asset_proxy_max_bytes', self::DEFAULT_MAX_BYTES);

        /*
         * Filter the request arguments for the asset proxy.
         *
         * Can be used to increase or decrease the timeout at which point the
         * server will stop loading the CORS asset proxy. The request is made
         * with wp_safe_remote_get(), so unsafe URLs are rejected whatever the
         * arguments say.
         *
         * @param array Arguments for wp_safe_remote_get()
         */
        $args = apply_filters(
            'uams_shortcodes_asset_proxy_request_args',
            [
                'timeout' => 5,     // Hang up if PDF takes more than 5 seconds to transfer
                'redirection' => 0, // A redirect could leave the allowed hosts
                'limit_response_size' => $max_bytes + 1, // One byte over the cap marks a truncated body
            ]
        );

        $response = wp_safe_remote_get($url, $args);

        if (is_wp_error($response) || (int) wp_remote_retrieve_response_code($response) !== 200) {
            return null;
        }

        $content_type = wp_remote_retrieve_header($response, 'content-type');
        if (is_array($content_type)) {
            $content_type = reset($content_type);
        }

        // @pest-mutate-ignore: RemoveStringCast
        if (strtolower(trim(explode(';', (string) $content_type)[0])) !== 'application/pdf') {
            return null;
        }

        $body = wp_remote_retrieve_body($response);

        if ($body === '' || strlen($body) > $max_bytes) {
            return null;
        }

        return $body;
    }

    public function handle_asset_proxy(): void
    {
        $asset_url = isset($_REQUEST['url']) && is_string($_REQUEST['url']) ? wp_unslash($_REQUEST['url']) : '';
        $security_check = isset($_REQUEST['_nonce']) && is_string($_REQUEST['_nonce']) ? wp_unslash($_REQUEST['_nonce']) : '';

        if ($asset_url === '' || ! wp_verify_nonce($security_check, 'asset-proxy-'.$asset_url)) {
            status_header(403);
            exit();
        }

        if (! self::is_enabled() || ! self::is_allowed_url($asset_url)) {
            status_header(403);
            exit();
        }

        $body = $this->fetch_asset($asset_url);

        if ($body === null) {
            status_header(502);
            exit();
        }

        // Only these headers are sent; nothing from the upstream response is forwarded.
        header('Content-Type: application/pdf');
        header('Content-Length: '.strlen($body));
        header('X-Content-Type-Options: nosniff');

        echo $body;

        exit;
    }
}
