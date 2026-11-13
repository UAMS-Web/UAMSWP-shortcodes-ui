<?php

/**
 * Shortcode base class: the static helpers every shortcode inherits.
 *
 * @see inc/shortcodes/class-shortcode.php
 */

declare(strict_types=1);

use UAMS_Shortcakes\Shortcodes\Shortcode;

use function Brain\Monkey\Functions\when;

/**
 * Concrete subclass that exposes the protected helpers for testing.
 */
class ShortcodeProbe extends Shortcode
{
    public static function expose_parse_url(string $url, int $component = -1): mixed
    {
        return self::parse_url($url, $component);
    }

    public static function expose_parse_iframes(string $content): mixed
    {
        return self::parse_iframes($content);
    }

    public static function expose_parse_scripts(string $content): mixed
    {
        return self::parse_scripts($content);
    }

    public static function expose_parse_tag_attributes(string $text): array
    {
        return self::parse_tag_attributes($text);
    }

    public static function expose_make_replacements(string $content, array $replacements): string
    {
        return self::make_replacements_to_content($content, $replacements);
    }
}

beforeEach(function (): void {
    stubWordPress();
});

it('derives the shortcode tag from the lowercase class name', function (): void {
    expect(ShortcodeProbe::get_shortcode_tag())->toBe('shortcodeprobe');
});

it('passes the tag and class through the uams_shortcodes_shortcode_tag filter', function (): void {
    when('apply_filters')->alias(
        fn (string $hook, string $tag, string $class): string => $hook.'|'.$tag.'|'.$class
    );

    expect(ShortcodeProbe::get_shortcode_tag())->toBe('uams_shortcodes_shortcode_tag|shortcodeprobe|ShortcodeProbe');
});

it('has base no-op implementations for the overridable hooks', function (): void {
    expect(ShortcodeProbe::get_attributes())->toBe([])
        ->and(ShortcodeProbe::get_shortcode_ui_args())->toBe([])
        ->and(ShortcodeProbe::reversal('<b>x</b>'))->toBe('<b>x</b>')
        ->and(ShortcodeProbe::callback(['a' => 'b'], 'inner'))->toBe('')
        ->and(ShortcodeProbe::setup_actions())->toBeNull();
});

it('converts text to a css friendly string', function (string $input, string $expected): void {
    expect(ShortcodeProbe::text_to_css_friendly($input))->toBe($expected);
})->with([
    'lowercases' => ['Hello World', 'hello-world'],
    'strips punctuation' => ['Hi! There?', 'hi-there'],
    'collapses spaces and dashes' => ['a  -  b', 'a-b'],
    'underscore becomes dash' => ['snake_case', 'snake-case'],
    'empty string' => ['', ''],
    'only symbols' => ['!!!', ''],
]);

it('returns file contents through uams_shortcodes_get_contents', function (): void {
    $file = tempnam(sys_get_temp_dir(), 'uams');
    file_put_contents($file, '<svg/>');

    try {
        expect(ShortcodeProbe::uams_shortcodes_get_contents($file))->toBe('<svg/>');
    } finally {
        unlink($file);
    }
});

it('returns null from uams_shortcodes_get_contents for an empty file', function (): void {
    $file = tempnam(sys_get_temp_dir(), 'uams');

    try {
        expect(ShortcodeProbe::uams_shortcodes_get_contents($file))->toBeNull();
    } finally {
        unlink($file);
    }
});

it('returns null from uams_shortcodes_get_contents when the content is the falsy string 0', function (): void {
    $file = tempnam(sys_get_temp_dir(), 'uams');
    file_put_contents($file, '0');

    try {
        expect(ShortcodeProbe::uams_shortcodes_get_contents($file))->toBeNull();
    } finally {
        unlink($file);
    }
});

it('returns null from uams_shortcodes_get_contents for a missing file and raises a warning', function (): void {
    $warnings = [];
    set_error_handler(function (int $no, string $str) use (&$warnings): bool {
        $warnings[] = $str;

        return true;
    });

    try {
        $result = ShortcodeProbe::uams_shortcodes_get_contents(sys_get_temp_dir().'/uams-does-not-exist.svg');
    } finally {
        restore_error_handler();
    }

    expect($result)->toBeNull()
        ->and($warnings)->not->toBeEmpty();
});

it('exposes the icon collection through get_icon_collection', function (): void {
    $icons = ShortcodeProbe::get_icon_collection();

    expect($icons['noicon'])->toBe('No Icon')
        ->and($icons['i-twitter'])->toBe('Twitter')
        ->and($icons)->toHaveKeys(['ic-book', 'i-Lamp', 'i-Tools'])
        ->and(count($icons))->toBeGreaterThan(200);
});

it('lists the ignored icon names', function (): void {
    expect(ShortcodeProbe::get_ignored_icon_names())->toBe([
        'noicon', 'commonicons', 'uamsicons', 'misc', 'allicons', 'uamsglyph', 'wpdash', 'numbers',
    ]);
});

it('lists the brand colors', function (): void {
    $colors = ShortcodeProbe::get_uams_brand_colors();

    expect($colors)->toHaveCount(15)
        ->and($colors['uamsred'])->toBe('Cardinal Red (Primary)')
        ->and($colors['white'])->toBe('White');
});

it('lists the brand colors plus the transparent variants', function (): void {
    $colors = ShortcodeProbe::get_uams_brand_colors_plus();

    expect($colors)->toHaveCount(20)
        ->and(array_keys($colors)[0])->toBe('transparentwhite')
        ->and($colors['semilight'])->toBe('Semi-transparent light (Black Text)');
});

it('lists the accessible text colors', function (): void {
    expect(ShortcodeProbe::get_accessible_text_brand_colors())->toBe([
        'wolfpackred' => 'Cardinal Red',
        'gray90' => '90% Gray',
        'black' => 'Black',
    ]);
});

it('adds http to a protocol-relative url and removes the scheme from the full parse', function (): void {
    expect(ShortcodeProbe::expose_parse_url('//example.com/a?b=1'))->toBe([
        'host' => 'example.com',
        'path' => '/a',
        'query' => 'b=1',
    ]);
});

it('returns an empty scheme for a protocol-relative url when only the scheme is asked for', function (): void {
    expect(ShortcodeProbe::expose_parse_url('//example.com/a', PHP_URL_SCHEME))->toBe('');
});

it('still returns the host of a protocol-relative url', function (): void {
    expect(ShortcodeProbe::expose_parse_url('//example.com/a', PHP_URL_HOST))->toBe('example.com');
});

it('parses a normal url like parse_url', function (): void {
    expect(ShortcodeProbe::expose_parse_url('https://example.com:8080/a', PHP_URL_PORT))->toBe(8080)
        ->and(ShortcodeProbe::expose_parse_url('https://example.com/a', PHP_URL_SCHEME))->toBe('https')
        ->and(ShortcodeProbe::expose_parse_url('https://example.com/a')['scheme'])->toBe('https');
});

it('returns false for a url parse_url cannot parse', function (): void {
    expect(ShortcodeProbe::expose_parse_url('http:///'))->toBeFalse();
});

it('returns false from parse_iframes when there is no iframe', function (): void {
    expect(ShortcodeProbe::expose_parse_iframes('<p>no frame</p>'))->toBeFalse();
});

it('returns false from parse_iframes when an iframe tag is present but not closed', function (): void {
    expect(ShortcodeProbe::expose_parse_iframes('<iframe src="x"'))->toBeFalse();
});

it('parses an iframe into its original markup, surrounding text and attributes', function (): void {
    $iframes = ShortcodeProbe::expose_parse_iframes('before <iframe src="https://a.test/x" width="500"></iframe> after');

    expect($iframes)->toHaveCount(1)
        ->and($iframes[0]->original)->toBe('<iframe src="https://a.test/x" width="500"></iframe>')
        ->and($iframes[0]->before)->toBe('before ')
        ->and($iframes[0]->after)->toBe(' after')
        ->and($iframes[0]->inner)->toBe('')
        ->and($iframes[0]->attrs)->toBe(['src' => 'https://a.test/x', 'width' => '500']);
});

it('parses iframes separated by a blank line as separate matches', function (): void {
    $content = "<iframe src=\"https://a.test/1\"></iframe>\n\n<iframe src=\"https://a.test/2\"></iframe>";
    $iframes = ShortcodeProbe::expose_parse_iframes($content);

    expect($iframes)->toHaveCount(2)
        ->and($iframes[0]->attrs['src'])->toBe('https://a.test/1')
        ->and($iframes[1]->attrs['src'])->toBe('https://a.test/2');
});

it('parses every iframe when two or three sit on adjacent lines', function (string $eol): void {
    $one = '<iframe src="https://a.test/1"></iframe>';
    $two = '<iframe src="https://a.test/2"></iframe>';
    $three = '<iframe src="https://a.test/3"></iframe>';

    $pair = ShortcodeProbe::expose_parse_iframes($one.$eol.$two);
    $trio = ShortcodeProbe::expose_parse_iframes($one.$eol.$two.$eol.$three);

    expect(array_column($pair, 'original'))->toBe([$one, $two])
        ->and(array_column($trio, 'original'))->toBe([$one, $two, $three])
        ->and(array_map(fn ($tag) => $tag->attrs['src'], $trio))->toBe(['https://a.test/1', 'https://a.test/2', 'https://a.test/3']);
})->with(["\n", "\r\n"]);

it('reports the neighboring line as the before and after text of adjacent iframes', function (): void {
    $one = '<iframe src="https://a.test/1"></iframe>';
    $two = '<iframe src="https://a.test/2"></iframe>';
    $iframes = ShortcodeProbe::expose_parse_iframes("intro\n".$one."\n".$two."\noutro");

    expect($iframes[0]->before)->toBe("intro\n")
        ->and($iframes[0]->after)->toBe("\n".$two)
        ->and($iframes[1]->before)->toBe($one."\n")
        ->and($iframes[1]->after)->toBe("\noutro");
});

it('reports empty before and after text for an iframe alone or set apart by blank lines', function (): void {
    $one = '<iframe src="https://a.test/1"></iframe>';
    $two = '<iframe src="https://a.test/2"></iframe>';

    $alone = ShortcodeProbe::expose_parse_iframes($one);
    $apart = ShortcodeProbe::expose_parse_iframes($one.'

'.$two);

    expect($alone[0]->before)->toBe('')
        ->and($alone[0]->after)->toBe('')
        ->and($apart[0]->before)->toBe('')
        ->and($apart[0]->after)->toBe('')
        ->and($apart[1]->before)->toBe('')
        ->and($apart[1]->after)->toBe('');
});

it('parses every script when two sit on adjacent lines', function (): void {
    $scripts = ShortcodeProbe::expose_parse_scripts("<script src=\"a.js\">var a;</script>\n<script src=\"b.js\">var b;</script>");

    expect($scripts)->toHaveCount(2)
        ->and($scripts[0]->inner)->toBe('var a;')
        ->and($scripts[1]->inner)->toBe('var b;');
});

it('does not match an uppercase tag because the pattern is case-sensitive', function (): void {
    expect(ShortcodeProbe::expose_parse_iframes('<IFRAME src="x"></IFRAME>'))->toBeFalse();
});

it('parses script tags with inner text', function (): void {
    $scripts = ShortcodeProbe::expose_parse_scripts('<script src="https://a.test/s.js">var a;</script>');

    expect($scripts)->toHaveCount(1)
        ->and($scripts[0]->inner)->toBe('var a;')
        ->and($scripts[0]->attrs)->toBe(['src' => 'https://a.test/s.js']);
});

it('returns false from parse_scripts when there is no script', function (): void {
    expect(ShortcodeProbe::expose_parse_scripts('<p>x</p>'))->toBeFalse();
});

it('parses double, single and unquoted attributes and bare words', function (): void {
    expect(ShortcodeProbe::expose_parse_tag_attributes('a="1" b=\'2\' c=3 "quoted" bare'))->toBe([
        'a' => '1',
        'b' => '2',
        'c' => '3',
        'quoted' => null,
        'bare' => null,
    ]);
});

it('unescapes backslash sequences in attribute values', function (): void {
    expect(ShortcodeProbe::expose_parse_tag_attributes('a="x\\ty"'))->toBe(['a' => "x\ty"]);
});

it('treats non-breaking spaces between attributes as separators', function (): void {
    expect(ShortcodeProbe::expose_parse_tag_attributes("a=\"1\"\u{00a0}b=\"2\""))->toBe(['a' => '1', 'b' => '2']);
});

it('skips an empty quoted token and keeps a quoted word', function (): void {
    expect(ShortcodeProbe::expose_parse_tag_attributes('"" "word"'))->toBe(['word' => null]);
});

it('keeps an attribute whose name is the string 0', function (): void {
    expect(ShortcodeProbe::expose_parse_tag_attributes('0="x"'))->toBe([0 => 'x'])
        ->and(ShortcodeProbe::expose_parse_tag_attributes("0='y'"))->toBe([0 => 'y'])
        ->and(ShortcodeProbe::expose_parse_tag_attributes('0=z'))->toBe([0 => 'z']);
});

it('returns an empty array for empty attribute text', function (): void {
    expect(ShortcodeProbe::expose_parse_tag_attributes(''))->toBe([]);
});

it('replaces every key with its value', function (): void {
    expect(ShortcodeProbe::expose_make_replacements('a b a', ['a' => 'x', 'b' => 'y']))->toBe('x y x');
});

it('returns the content unchanged when there are no replacements', function (): void {
    expect(ShortcodeProbe::expose_make_replacements('abc', []))->toBe('abc');
});
