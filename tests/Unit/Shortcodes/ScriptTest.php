<?php

/**
 * Script shortcode: host whitelist, the src filter, the editor-only error and
 * the reversal of whitelisted script tags.
 *
 * @see inc/shortcodes/class-script.php
 */

declare(strict_types=1);

use UAMS_Shortcakes\Shortcodes\Script;

use function Brain\Monkey\Functions\when;

/**
 * Make the plugin's two filters return the given values; every other filter
 * passes its value through.
 *
 * @param  list<string>  $domains
 */
function scriptFilters(array $domains, ?Closure $src = null): void
{
    when('apply_filters')->alias(
        function (string $tag, $value, ...$rest) use ($domains, $src) {
            if ($tag === 'uams_shortcodes_whitelisted_script_domains') {
                return $domains;
            }

            if ($tag === 'uams_shortcodes_script_src' && $src instanceof Closure) {
                return $src($value, ...$rest);
            }

            return $value;
        }
    );
}

beforeEach(function (): void {
    stubWordPress();
});

it('uses the lowercase class name as its shortcode tag', function (): void {
    expect(Script::get_shortcode_tag())->toBe('script');
});

it('has no whitelisted domains until a filter adds some', function (): void {
    expect(Script::get_whitelisted_script_domains())->toBe([]);

    scriptFilters(['ajax.googleapis.com']);

    expect(Script::get_whitelisted_script_domains())->toBe(['ajax.googleapis.com']);
});

it('renders nothing when src is missing or empty', function (): void {
    scriptFilters(['cdn.example.test']);

    expect(Script::callback([]))->toBe('')
        ->and(Script::callback(['src' => '']))->toBe('');
});

it('renders a script tag for a whitelisted host', function (): void {
    scriptFilters(['cdn.example.test']);

    expect(Script::callback(['src' => 'https://cdn.example.test/lib.js']))
        ->toBe('<script src="https://cdn.example.test/lib.js"></script>');
});

it('accepts a protocol-relative src on a whitelisted host', function (): void {
    scriptFilters(['cdn.example.test']);

    expect(Script::callback(['src' => '//cdn.example.test/lib.js']))
        ->toStartWith('<script src="')
        ->toContain('cdn.example.test/lib.js"></script>');
});

it('matches the whitelist on the exact host only', function (): void {
    scriptFilters(['cdn.example.test']);
    when('current_user_can')->justReturn(false);

    expect(Script::callback(['src' => 'https://sub.cdn.example.test/lib.js']))->toBe('')
        ->and(Script::callback(['src' => 'https://cdn.example.test.evil.test/lib.js']))->toBe('');
});

it('renders nothing for a host that is not whitelisted when the viewer cannot edit posts', function (): void {
    scriptFilters(['cdn.example.test']);
    when('current_user_can')->justReturn(false);

    expect(Script::callback(['src' => 'https://evil.test/x.js']))->toBe('');
});

it('shows an error naming the url to a viewer who can edit posts', function (): void {
    scriptFilters(['cdn.example.test']);
    Brain\Monkey\Functions\expect('current_user_can')->once()->with('edit_posts')->andReturn(true);

    expect(Script::callback(['src' => 'https://evil.test/x.js']))
        ->toBe('<div class="uams-shortcodes-error"><p>Invalid hostname in URL: https://evil.test/x.js</p></div>');
});

it('renders nothing for a src without a host', function (): void {
    scriptFilters(['cdn.example.test']);
    when('current_user_can')->justReturn(false);

    expect(Script::callback(['src' => '/local/lib.js']))->toBe('');
});

it('lets the src filter rewrite the url before the host check', function (): void {
    $seen = [];
    scriptFilters(['cdn.example.test'], function ($src, $attrs) use (&$seen): string {
        $seen = [$src, $attrs];

        return 'https://cdn.example.test/rewritten.js';
    });

    expect(Script::callback(['src' => 'https://evil.test/x.js']))
        ->toBe('<script src="https://cdn.example.test/rewritten.js"></script>')
        ->and($seen)->toBe(['https://evil.test/x.js', ['src' => 'https://evil.test/x.js']]);
});

it('describes its Shortcake attributes', function (): void {
    $args = Script::get_shortcode_ui_args();

    expect($args['label'])->toBe('Script')
        ->and($args['listItemImage'])->toBe('dashicons-media-code')
        ->and(array_column($args['attrs'], 'attr'))->toBe(['src']);
});

it('turns a whitelisted script tag back into a shortcode', function (): void {
    scriptFilters(['cdn.example.test']);

    $content = 'Before <script src="https://cdn.example.test/lib.js"></script> after';

    expect(Script::reversal($content))->toBe('Before [script src="https://cdn.example.test/lib.js"] after');
});

it('leaves a script tag alone when its host is not whitelisted', function (): void {
    scriptFilters(['cdn.example.test']);

    $content = '<script src="https://evil.test/x.js"></script>';

    expect(Script::reversal($content))->toBe($content);
});

it('returns content without script tags unchanged', function (): void {
    scriptFilters(['cdn.example.test']);

    expect(Script::reversal('<p>Plain</p>'))->toBe('<p>Plain</p>');
});

it('considers every tag when several are on adjacent lines', function (): void {
    scriptFilters(['cdn.example.test']);

    $first = '<script src="https://cdn.example.test/a.js"></script>
<script src="https://evil.test/b.js"></script>';
    $second = '<script src="https://evil.test/b.js"></script>
<script src="https://cdn.example.test/a.js"></script>';

    expect(Script::reversal($first))->toBe('[script src="https://cdn.example.test/a.js"]
<script src="https://evil.test/b.js"></script>')
        ->and(Script::reversal($second))->toBe('<script src="https://evil.test/b.js"></script>
[script src="https://cdn.example.test/a.js"]');
});
