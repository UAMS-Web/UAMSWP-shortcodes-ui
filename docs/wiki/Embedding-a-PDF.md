<!-- cspell:ignore uams -->

Written against v0.7.0.

The `[pdf]` shortcode shows a PDF inside the page in the plugin's own PDF.js viewer, so readers can page through it without downloading it.

## From the media library

1. In the editor, choose **Add Post Element**, then **PDF**.
2. Under **Select or upload PDF**, choose a PDF from the media library or upload one. The library is filtered to PDF documents.
3. Choose **Insert Element**. The editor shows `[pdf attachment="123"]`, where `123` is the attachment's ID.

Adding a PDF through **Add Media** and **Insert into post** does the same: the plugin replaces the usual link with a `[pdf]` shortcode.

## From another site

1. In the PDF form, leave **Select or upload PDF** empty and paste the PDF's address into **...or embed from URL**, for example `https://example.org/files/annual-report.pdf`.
2. Insert the element and preview the page.

The address must end in `.pdf`; anything else renders nothing.

**Note:** the viewer loads the file in the reader's browser, so the other site has to allow it with cross-origin (CORS) headers. When it does not, the viewer reports *An error occurred while loading the PDF* ([Troubleshooting](Troubleshooting.md#an-error-occurred-while-loading-the-pdf)).

## Through the proxy, for a PDF that refuses to embed

**Warning:** in v0.7.0 the proxy does not work. The plugin never loads the code that answers its requests, so a proxied PDF fails to load whatever the settings below say ([#160](https://github.com/UAMS-Web/UAMSWP-shortcodes-ui/issues/160)). Until that is fixed, upload the PDF to the media library instead. The rest of this section describes the proxy as written, for when it is loaded.

The proxy fetches an external PDF on the server and hands it to the viewer from the site's own address. It is off by default and only a developer can switch it on, from a theme or plugin:

```php
add_filter('uams_shortcodes_pdf_enable_cors_proxy', '__return_true');

add_filter('uams_shortcodes_asset_proxy_allowed_hosts', function (array $hosts): array {
    $hosts[] = 'example.org';

    return $hosts;
});
```

With the first filter in place, the PDF form gains a **Proxy through local domain?** checkbox, which writes `proxy="true"`. The second filter adds the other site's host: without it the proxy only fetches from the site's own home, site and uploads hosts.

The proxy refuses a URL that is not `http` or `https`, carries a user name or password, does not end in `.pdf`, or is on a host outside the list. It follows no redirects, gives up after 5 seconds, and relays only a response served as `application/pdf` of 20 MiB or less.

## Reference

- [Shortcodes: PDF](Shortcodes.md#pdf) for the attributes.
- [Hooks](Hooks.md#pdf-and-the-asset-proxy) for the proxy filters, including the size cap and request timeout.
