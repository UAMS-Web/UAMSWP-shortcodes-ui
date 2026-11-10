<?php

/**
 * Behaviors the per-class tests left to chance, found by mutation testing.
 *
 * Each test pins one thing a mutant of the class could change without a failure:
 * every size and flag of a Google Docs reversal, each branch of the shortcode
 * attribute parser, the text of a plain mention in the preview hooks, and the
 * field the PDF proxy filter adds.
 *
 * Mutants no input can kill carry a `@pest-mutate-ignore` comment in `inc/shortcodes`,
 * each with its reason. A mutator that rewrites the commented statement itself
 * (`break`, `return`) is ignored only by a comment on the same line, which the plugin
 * matches by line number; a comment above such a statement does not stop it.
 * `$return .=` takes the comment on the line above:
 * - `BreakToContinue` on a `break` that ends a `switch` case: PHP treats `continue`
 *   inside a `switch` as `break`.
 * - `ConcatEqualToEqual` on `$return .=` in LiveWhale and LiveWhale_Widget, and on
 *   `$output .=` in RSS_Posts: the variable is still the empty string on that line.
 * - `EqualToIdentical` and `NotEqualToNotIdentical` on a comparison of a shortcode
 *   attribute, the view or a feed item count with a string or integer literal in
 *   Callout, LiveWhale, Panel, RSS_Posts and TextBox: both sides are strings (or
 *   integers), so `==` and `===` agree.
 * - `RemoveEarlyReturn` in Instagram and Twitter reversal: the code below the guard
 *   returns content without the markers unchanged. For the same reason `FalseToTrue`
 *   and `IdenticalToNotIdentical` on the guard's `stripos(...) === false` tests in
 *   those two classes change only whether that code runs, and `FalseToTrue` on the
 *   `stripos(...) !== false` guard in Facebook: the patterns below it need a
 *   `<script>` tag, so they match nothing without one.
 * - `RemoveEarlyReturn` on the `! $type` guard in `GoogleDocs::callback()`: without it the
 *   type is `false`, no `switch` case matches, and the default case returns `''`.
 * - `DecrementInteger` on `continue 2` in `GoogleDocs::reversal()`: the `switch` is the last
 *   statement of the loop body, so `continue 1` ends the iteration the same way.
 * - `FalseToTrue` on `get_document_type()`'s `return false`: the file declares no strict types,
 *   so `true` is coerced to `"1"`, which matches no `switch` case.
 * - `RemoveBooleanCast` on `Asset_Proxy::is_enabled()`, and `RemoveStringCast` in
 *   `Asset_Proxy::allowed_hosts()` and `fetch_asset()`: the file declares no strict
 *   types, so PHP coerces the return value and the argument the same way. The
 *   `(string)` cast on a header that `reset()` returns follows the same rule.
 * - `EmptyStringToNotEmpty` on the `?? ''` defaults and the empty-host test in
 *   `Asset_Proxy`: the replacement text has no host, no `.pdf` extension and no
 *   host to compare, so every path ends as it does with `''`.
 * - `IncrementInteger` and `DecrementInteger` on the bounds of `random_int()` in
 *   Panel: the id suffix is random, and no test can see which range it came from.
 * - `IncrementInteger` on the limit of `explode('-', $icon, 2)` in Alert: no icon
 *   name holds a second hyphen, so a limit of 3 splits the same way.
 *
 * @see inc/shortcodes
 */

declare(strict_types=1);

use Brain\Monkey\Functions;
use UAMS_Shortcakes\Shortcodes\Facebook;
use UAMS_Shortcakes\Shortcodes\GoogleDocs;
use UAMS_Shortcakes\Shortcodes\Image_Comparison;
use UAMS_Shortcakes\Shortcodes\PDF;
use UAMS_Shortcakes\Shortcodes\Shortcode;

use function Brain\Monkey\Actions\expectAdded;
use function Brain\Monkey\Functions\when;

beforeEach(function (): void {
    stubWordPress();
    when('add_query_arg')->alias(function (...$args): string {
        [$params, $url] = $args;

        return $url.(str_contains($url, '?') ? '&' : '?').http_build_query($params);
    });
});

/**
 * Exposes the protected attribute parser.
 */
final class MutantGapsShortcodeProbe extends Shortcode
{
    /**
     * @return array<int|string, string|null>
     */
    public static function parse(string $text): array
    {
        return self::parse_tag_attributes($text);
    }
}

it('reverses each Google Docs type with both sizes and every flag, or with none', function (string $src, string $attrs, string $expected): void {
    expect(GoogleDocs::reversal('<iframe src="'.$src.'"'.$attrs.'></iframe>'))->toBe($expected);
})->with([
    'document, sized' => [
        'https://docs.google.com/document/d/ABC/pub?embedded=true', ' width="640" height="480"',
        '[googledocs url="https://docs.google.com/document/d/ABC" height=480 width=640]',
    ],
    'document, unsized' => [
        'https://docs.google.com/document/d/ABC/pub?embedded=true', '',
        '[googledocs url="https://docs.google.com/document/d/ABC"]',
    ],
    'spreadsheet, sized with headers' => [
        'https://docs.google.com/spreadsheets/d/ABC/pubhtml?widget=true&amp;headers=true', ' width="640" height="480"',
        '[googledocs url="https://docs.google.com/spreadsheets/d/ABC" height=480 width=640 headers="true"]',
    ],
    'spreadsheet, sized without headers' => [
        'https://docs.google.com/spreadsheets/d/ABC/pubhtml?widget=true', ' width="640" height="480"',
        '[googledocs url="https://docs.google.com/spreadsheets/d/ABC" height=480 width=640]',
    ],
    'spreadsheet, unsized' => [
        'https://docs.google.com/spreadsheets/d/ABC/pubhtml?widget=true', '',
        '[googledocs url="https://docs.google.com/spreadsheets/d/ABC"]',
    ],
    'presentation, sized with every flag' => [
        'https://docs.google.com/presentation/d/ABC/embed?start=true&amp;loop=true&amp;delayms=2000', ' width="640" height="480"',
        '[googledocs url="https://docs.google.com/presentation/d/ABC" height=480 width=640 start="true" loop="true" delayms=2000]',
    ],
    'presentation, sized with no flags' => [
        'https://docs.google.com/presentation/d/ABC/embed?slide=1', ' width="640" height="480"',
        '[googledocs url="https://docs.google.com/presentation/d/ABC" height=480 width=640]',
    ],
    'presentation, unsized with no flags' => [
        'https://docs.google.com/presentation/d/ABC/embed?slide=1', '',
        '[googledocs url="https://docs.google.com/presentation/d/ABC"]',
    ],
    'form, sized' => [
        'https://docs.google.com/forms/d/ABC/viewform?embedded=true', ' width="640" height="480"',
        '[googledocs url="https://docs.google.com/forms/d/ABC" height=480 width=640]',
    ],
    'form, unsized' => [
        'https://docs.google.com/forms/d/ABC/viewform?embedded=true', '',
        '[googledocs url="https://docs.google.com/forms/d/ABC"]',
    ],
    'map, sized' => [
        'https://www.google.com/maps/d/embed?mid=XYZ', ' width="640" height="480"',
        '[googledocs url="https://www.google.com/maps/d/embed?mid=XYZ" height=480 width=640]',
    ],
    'map, unsized' => [
        'https://www.google.com/maps/d/embed?mid=XYZ', '',
        '[googledocs url="https://www.google.com/maps/d/embed?mid=XYZ"]',
    ],
    'fusion table, sized' => [
        'https://www.google.com/fusiontables/embedviz?q=1', ' width="640" height="480"',
        '[googledocs url="https://www.google.com/fusiontables/embedviz?q=1" height=480 width=640]',
    ],
    'fusion table, unsized' => [
        'https://www.google.com/fusiontables/embedviz?q=1', '',
        '[googledocs url="https://www.google.com/fusiontables/embedviz?q=1"]',
    ],
]);

it('parses each attribute form, including empty and zero values', function (): void {
    expect(MutantGapsShortcodeProbe::parse('a="" b=\'\' c=0 d="x" e=\'y\' f=z "q" "0" bare'))->toBe([
        'a' => '',
        'b' => '',
        'c' => '0',
        'd' => 'x',
        'e' => 'y',
        'f' => 'z',
        'q' => null,
        '0' => null,
        'bare' => null,
    ]);
});

it('prints the Facebook preview script only for a bracketed shortcode', function (): void {
    $callback = null;
    expectAdded('shortcode_ui_after_do_shortcode')->whenHappen(function ($added) use (&$callback): void {
        $callback = $added;
    });
    Facebook::setup_actions();

    ob_start();
    $callback('plain text about facebook');
    $plain = ob_get_clean();

    ob_start();
    $callback('[facebook url="https://www.facebook.com/UAMS/posts/1"]');
    $bracketed = ob_get_clean();

    expect($plain)->toBe('')
        ->and($bracketed)->toContain('connect.facebook.net');
});

it('prints the image comparison preview assets only for a bracketed shortcode', function (): void {
    $callback = null;
    expectAdded('shortcode_ui_after_do_shortcode')->whenHappen(function ($added) use (&$callback): void {
        $callback = $added;
    });
    Image_Comparison::setup_actions();

    ob_start();
    $callback('plain text about image_comparison');
    $plain = ob_get_clean();

    expect($plain)->toBe('');
});

it('checks for and adds the Facebook footer action by its callable name', function (): void {
    when('wp_enqueue_script')->justReturn();
    Functions\expect('has_action')->once()->with('wp_footer', Facebook::class.'::action_wp_footer')->andReturn(false);
    expectAdded('wp_footer')->once()->with(Facebook::class.'::action_wp_footer');

    Facebook::callback(['url' => 'https://www.facebook.com/UAMS/posts/12345']);
});

it('adds the proxy checkbox to the PDF fields when the CORS proxy filter is on', function (): void {
    when('apply_filters')->alias(fn ($tag, $value) => $tag === 'uams_shortcodes_pdf_enable_cors_proxy' ? true : $value);

    $attrs = PDF::get_shortcode_ui_args()['attrs'];

    expect(end($attrs))->toBe([
        'label' => 'Proxy through local domain?',
        'attr' => 'proxy',
        'type' => 'checkbox',
        'description' => "External PDFs require proper Access-Control headers in order to embed. \nIf you are seeing &#039;An error occurred while loading the PDF&#039; errors, try this.",
    ]);
});
