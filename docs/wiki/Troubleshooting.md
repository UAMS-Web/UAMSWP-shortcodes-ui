<!-- cspell:ignore uams UAMSWP -->

Written against v0.7.0.

- [The dashboard says Shortcake is required](#the-dashboard-says-shortcake-is-required)
- [The dashboard says PHP 8.2 is required](#the-dashboard-says-php-82-is-required)
- [There is no Add Post Element button](#there-is-no-add-post-element-button)
- [A shortcode shows on the page as text in brackets](#a-shortcode-shows-on-the-page-as-text-in-brackets)
- [A PDF shortcode renders nothing](#a-pdf-shortcode-renders-nothing)
- [An error occurred while loading the PDF](#an-error-occurred-while-loading-the-pdf)
- [The PDF proxy answers 403 or 502](#the-pdf-proxy-answers-403-or-502)

## The dashboard says Shortcake is required

`UAMSWP Shortcode UI requires Shortcake (Shortcode UI) plugin to be active!`

**Cause:** the plugin registers nothing until the Shortcake (Shortcode UI) plugin is active, and shows this notice instead.

**Fix:** install and activate [Shortcake (Shortcode UI)](https://wordpress.org/plugins/shortcode-ui/). The notice goes away on the next page load.

## The dashboard says PHP 8.2 is required

`UAMSWP Shortcode UI requires PHP 8.2 to function properly. Please upgrade PHP or deactivate the plugin.`

**Cause:** the site runs a PHP older than 8.2, and the plugin stops before registering anything.

**Fix:** move the site to PHP 8.2 or later, or deactivate the plugin. The requirements are in the [README](https://github.com/UAMS-Web/UAMSWP-shortcodes-ui#requirements).

## There is no Add Post Element button

**Cause:** the post is open in the block editor. Shortcake's forms only exist in the classic editor.

**Fix:** edit the post with the classic editor, or type the shortcode into a Shortcode block using the tags and attributes in [Shortcodes](Shortcodes.md).

## A shortcode shows on the page as text in brackets

For example `[iframe src="..."]` appears on the page as typed.

**Cause:** nothing is registered under that tag. Either the tag is misspelled, or it belongs to a shortcode the plugin carries but does not register (`[iframe]`, `[script]`, `[facebook]` and the others under [Not registered](Shortcodes.md#not-registered)).

**Fix:** correct the tag against [Shortcodes](Shortcodes.md), or have a developer register the class ([Adding a shortcode to a site](Adding-a-shortcode-to-a-site.md)).

## A PDF shortcode renders nothing

**Cause:** `[pdf]` outputs nothing when it has no PDF to show: the `attachment` is not a PDF in the media library and there is no `url`, or the address does not end in `.pdf`.

**Fix:** pick the file again under **Select or upload PDF**, or use an address whose path ends in `.pdf`.

## An error occurred while loading the PDF

The viewer frame appears, but shows `An error occurred while loading the PDF.`

**Cause:** the PDF is on another site that does not send cross-origin (CORS) headers, so the reader's browser refuses to hand it to the viewer.

**Fix:** download the PDF and upload it to the media library, or have a developer switch on the proxy and allow the other site's host ([Embedding a PDF](Embedding-a-PDF.md#through-the-proxy-for-a-pdf-that-refuses-to-embed)), then check **Proxy through local domain?** on the shortcode.

## The PDF proxy answers 403 or 502

The viewer still fails with the proxy checked, and the browser's network panel shows the `admin-ajax.php?action=uams_shortcodes_asset_proxy` request answering `403` or `502`.

**Cause of a 403:** the proxy is switched off (`uams_shortcodes_pdf_enable_cors_proxy`), the PDF's host is not in `uams_shortcodes_asset_proxy_allowed_hosts`, the address is not an `http` or `https` `.pdf` URL, or the page was cached long enough for its security token to expire.

**Cause of a 502:** the proxy reached the other site but got no usable PDF: an error or redirect, a response not served as `application/pdf`, a file over the 20 MiB cap, or no answer within 5 seconds.

**Fix:** check each cause against [Hooks](Hooks.md#pdf-and-the-asset-proxy). For a stale token, clear the page cache.
