<?php

/**
 * BaseButton shortcode: the button `callback()` returns and the Shortcake arguments.
 *
 * @see inc/shortcodes/class-basebutton.php
 */

declare(strict_types=1);

use UAMS_Shortcakes\Shortcodes\BaseButton;

use function Brain\Monkey\Functions\stubs;
use function Brain\Monkey\Functions\when;

beforeEach(function (): void {
    stubWordPress();
    stubs(['esc_url' => fn ($url) => $url]);
});

/**
 * Attributes for a complete call: the class reads title and btncontent
 * without defaults, so a call that omits them raises warnings.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function baseButtonAttrs(array $overrides = []): array
{
    return array_merge(['title' => '', 'btncontent' => 'Go'], $overrides);
}

it('uses the lowercase class name as its shortcode tag', function (): void {
    expect(BaseButton::get_shortcode_tag())->toBe('basebutton');
});

it('renders an anchor around a button with the default classes', function (): void {
    expect(BaseButton::callback(baseButtonAttrs(['url' => 'https://example.test/', 'title' => 'T', 'target' => '_blank'])))
        ->toBe('<a href="https://example.test/" title="T" target="_blank"><button type="button" class=" btn  ">Go</button></a>');
});

it('puts custom classes before the button classes', function (): void {
    expect(BaseButton::callback(baseButtonAttrs(['customclass' => 'one,two', 'color' => 'btn-primary', 'size' => 'btn-lg'])))
        ->toContain('class="one two btn btn-primary btn-lg"');
});

it('does not sanitize custom classes', function (): void {
    expect(BaseButton::callback(baseButtonAttrs(['customclass' => 't wo!'])))->toContain('class="t wo! btn  "');
});

it('adds btn-block when displayblock is truthy', function (): void {
    expect(BaseButton::callback(baseButtonAttrs(['displayblock' => '1'])))->toContain('class=" btn   btn-block"')
        ->and(BaseButton::callback(baseButtonAttrs(['displayblock' => '0'])))->not->toContain('btn-block')
        ->and(BaseButton::callback(baseButtonAttrs(['displayblock' => ''])))->not->toContain('btn-block');
});

it('prints the title, target and content without escaping them', function (): void {
    expect(BaseButton::callback(baseButtonAttrs(['title' => 'a"b', 'target' => '_x', 'btncontent' => '<i>Go</i>'])))
        ->toContain('title="a"b" target="_x"')
        ->toContain('>'.'<i>Go</i></button>');
});

it('runs the url through esc_url', function (): void {
    when('esc_url')->alias(fn ($url): string => 'ESCAPED:'.$url);

    expect(BaseButton::callback(baseButtonAttrs(['url' => 'https://example.test/x'])))->toStartWith('<a href="ESCAPED:https://example.test/x"');
});

it('ignores the btn_content default because the attribute is named btncontent', function (): void {
    expect(BaseButton::callback(baseButtonAttrs(['btn_content' => 'Ignored'])))->toContain('>Go</button>');
});

it('raises warnings for the title and btncontent keys the defaults omit', function (): void {
    $messages = [];
    set_error_handler(function (int $no, string $str) use (&$messages): bool {
        $messages[] = $str;

        return true;
    });

    try {
        $html = BaseButton::callback([]);
    } finally {
        restore_error_handler();
    }

    expect($html)->toBe('<a href="" title="" target=""><button type="button" class=" btn  "></button></a>')
        ->and($messages)->toBe(['Undefined array key "title"', 'Undefined array key "btncontent"']);
});

it('uses the defaults when the attributes are not an array', function (): void {
    set_error_handler(fn (): bool => true);

    try {
        $html = BaseButton::callback('');
    } finally {
        restore_error_handler();
    }

    expect($html)->toBe('<a href="" title="" target=""><button type="button" class=" btn  "></button></a>');
});

it('describes its Shortcake attributes', function (): void {
    $args = BaseButton::get_shortcode_ui_args();
    $attrs = array_column($args['attrs'], null, 'attr');

    expect(array_column(BaseButton::get_attributes(), 'attr'))->toBe(['btncontent', 'title', 'url', 'target', 'color', 'size', 'displayblock', 'customclass'])
        ->and($args['attrs'])->toBe(BaseButton::get_attributes())
        ->and($args['label'])->toBe('Basic Button')
        ->and($args['listItemImage'])->toBe('dashicons-editor-removeformatting')
        ->and(array_keys($attrs['size']['options']))->toBe(['', 'btn-lg', 'btn-sm'])
        ->and(array_keys($attrs['target']['options']))->toBe(['_top', '_blank']);
});
