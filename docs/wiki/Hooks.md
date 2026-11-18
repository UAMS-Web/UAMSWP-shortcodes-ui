<!-- cspell:ignore uams youtubeui -->

Written against v0.7.0.

The filters and actions a theme or plugin can use to change what the plugin registers and how its shortcodes behave. Add a filter before WordPress's `init` action unless the entry says otherwise.

## Registration

| Hook | Type | Receives | Effect |
| --- | --- | --- | --- |
| `uams_shortcodes_shortcode_classes` | filter | `list<class-string>` of shortcode classes | The classes registered on `init`. Add a class to register it, remove one to drop it. See [Adding a shortcode to a site](Adding-a-shortcode-to-a-site.md). |
| `uams_shortcodes_shortcode_tag` | filter | the tag (`string`), the class name (`string`) | The tag a class registers under. Defaults to the lowercased short class name. |

```php
add_filter('uams_shortcodes_shortcode_tag', function (string $tag, string $class): string {
    return $tag === 'button' ? 'uams-button' : $tag;
}, 10, 2);
```

## PDF and the asset proxy

**Note:** in v0.7.0 the plugin never loads the asset proxy, so only `uams_shortcodes_pdf_enable_cors_proxy` (which adds the checkbox) and `uams_shortcodes_pdf_asset_proxy_url` (which sets the address the viewer asks) take effect. The three `uams_shortcodes_asset_proxy_*` filters are read by the proxy itself and have no effect until it is loaded ([#160](https://github.com/UAMS-Web/UAMSWP-shortcodes-ui/issues/160)).

| Hook | Type | Receives | Effect |
| --- | --- | --- | --- |
| `uams_shortcodes_pdf_enable_cors_proxy` | filter | `bool`, `false` by default | `true` adds the **Proxy through local domain?** option to `[pdf]` and, once the proxy is loaded, lets it answer. A `proxy` attribute is ignored while this is `false`. |
| `uams_shortcodes_asset_proxy_allowed_hosts` | filter | `list<string>` of host names | The hosts the proxy fetches from. Defaults to the hosts of the site's home, site and uploads URLs. Compared case-insensitively. |
| `uams_shortcodes_asset_proxy_max_bytes` | filter | `int`, `20971520` (20 MiB) by default | The largest PDF the proxy relays. |
| `uams_shortcodes_asset_proxy_request_args` | filter | `array` for `wp_safe_remote_get()` | The proxy's request arguments: `timeout` 5, `redirection` 0 and a response size limit. The request stays `wp_safe_remote_get()` whatever is returned. |
| `uams_shortcodes_pdf_asset_proxy_url` | filter | the proxy URL (`string`), the original PDF URL (`string`) | The URL the viewer loads a proxied PDF from. Return another address to use an external proxy. |

[Embedding a PDF](Embedding-a-PDF.md#through-the-proxy-for-a-pdf-that-refuses-to-embed) has a worked example.

## Embeds

| Hook | Type | Receives | Effect |
| --- | --- | --- | --- |
| `uams_shortcodes_youtube_embed_url` | filter | the embed URL (`string`), the shortcode's attributes (`array`) | The address `[youtubeui]` loads in its frame. |
| `shortcake_bakery_show_add_embed` | filter | `bool`, `true` by default | Whether the media modal offers **Insert Embed Code**, which turns pasted embed code into the matching shortcode. |
| `shortcake_bakery_embed_reversal` | filter | the result (`array`), the pasted embed code (`string`), the post ID (`int`) | What the editor's embed-paste request returns after the plugin turns pasted embed code into shortcodes. |
| `shortcake_bakery_reversed_embed` | action | the same three values | Runs after that request has built its result. |

The `uams_shortcodes_iframe_src`, `uams_shortcodes_whitelisted_iframe_domains`, `uams_shortcodes_script_src` and `uams_shortcodes_whitelisted_script_domains` filters belong to the `Iframe` and `Script` shortcodes, which are not registered ([Shortcodes](Shortcodes.md#not-registered)); they have no effect unless a site registers those classes.
