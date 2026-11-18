<!-- cspell:ignore autoloaded Dashicon dashicons kses uams -->

Written against v0.7.0.

A theme or another plugin can add its own shortcode to the editor's element grid, or switch back on one of the shortcodes the plugin carries but does not register (listed under [Not registered](Shortcodes.md#not-registered)). Both go through one filter.

## Switching an unregistered shortcode back on

The plugin's own classes are autoloaded, so naming the class is enough:

```php
add_filter('uams_shortcodes_shortcode_classes', function (array $classes): array {
    $classes[] = \UAMS_Shortcakes\Shortcodes\Iframe::class;

    return $classes;
});
```

Add it before WordPress's `init` action, for example in a plugin's main file or a theme's `functions.php`: the plugin reads the filter once, on `init`.

## Adding a new shortcode

1. **Write a class that extends `UAMS_Shortcakes\Shortcodes\Shortcode`.** Its short class name, lowercased, becomes the tag: a class named `Notice` registers `[notice]`.
2. **Return the editor form from `get_shortcode_ui_args()`**: a `label`, a `listItemImage` (a Dashicon name such as `dashicons-info`, or an `<img>` tag), `attrs` for the fields and, for a shortcode that wraps content, `inner_content`. These are Shortcake's [registration arguments](https://github.com/wp-shortcake/shortcake/wiki/Registering-Shortcode-UI).
3. **Return the markup from `callback($attrs, $content)`.** Return it, never echo it, and escape every attribute on the way out.
4. **Optionally override `setup_actions()`** to add hooks the shortcode needs (it runs once, when the shortcode is registered), and `reversal($content)` to turn pasted embed code back into the shortcode (it runs on the `pre_kses` filter, before WordPress strips the embed).
5. **Load the class yourself.** The plugin's autoloader only looks in its own `inc/shortcodes/` folder, so a class from a theme or plugin has to be required or autoloaded by that theme or plugin.
6. **Register it with the filter:**

```php
require_once __DIR__.'/inc/class-notice.php';

add_filter('uams_shortcodes_shortcode_classes', function (array $classes): array {
    $classes[] = \Example\Shortcodes\Notice::class;

    return $classes;
});
```

The element then appears in **Add Post Element**, and the tag works in post content.

## Reference

- [Hooks](Hooks.md) for `uams_shortcodes_shortcode_classes` and `uams_shortcodes_shortcode_tag`.
- [Shortcodes](Shortcodes.md) for the attributes of the shortcodes the plugin registers, which make working examples to copy.
