<?php

/**
 * Panel shortcode: static and collapsible markup and the Shortcake arguments.
 *
 * @see inc/shortcodes/class-panel.php
 */

declare(strict_types=1);

use UAMS_Shortcakes\Shortcodes\Panel;

use function Brain\Monkey\Functions\stubs;

beforeEach(function (): void {
    stubWordPress();
    stubs([
        'esc_url' => fn ($url) => $url,
        'esc_attr' => fn ($text): string => htmlspecialchars((string) $text),
    ]);
});

/**
 * Collapse the whitespace between tags so the markup can be compared exactly.
 */
function panelFlatten(string $html): string
{
    return preg_replace('/>\s+</', '><', trim($html));
}

it('uses the lowercase class name as its shortcode tag', function (): void {
    expect(Panel::get_shortcode_tag())->toBe('panel');
});

it('renders a static default panel', function (): void {
    $html = panelFlatten(Panel::callback(['heading' => 'Title', 'customclass' => ''], 'Body'));

    expect($html)->toBe('<div class="panel panel-default  "><div class="panel-heading"><p class="panel-title"><span>Title</span></p></div><div id="" class="panel-collapse collapse in"><div class="panel-body"><p>Body</p></div></div></div>');
});

it('strips tags from a static heading with sanitize_text_field', function (): void {
    expect(Panel::callback(['heading' => 'A <b>Bold</b>', 'customclass' => ''], 'x'))
        ->toContain('<span>A Bold</span>');
});

it('falls back to panel-default for an unknown type and keeps each allowed one', function (string $type): void {
    expect(Panel::callback(['type' => $type, 'customclass' => ''], 'x'))->toContain('class="panel '.$type.' ');
})->with(['panel-default', 'panel-primary', 'panel-success', 'panel-info', 'panel-warning', 'panel-danger']);

it('uses panel-default for an unknown or empty type', function (): void {
    expect(Panel::callback(['type' => 'panel-bogus', 'customclass' => ''], 'x'))->toContain('class="panel panel-default ')
        ->and(Panel::callback(['type' => '', 'customclass' => ''], 'x'))->toContain('class="panel panel-default ');
});

it('keeps allowed heading tags, including ones the UI does not offer', function (string $tag): void {
    expect(Panel::callback(['headingtype' => $tag, 'customclass' => ''], 'x'))
        ->toContain('<'.$tag.' class="panel-title">')
        ->toContain('</'.$tag.'>');
})->with(['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p']);

it('falls back to a paragraph heading for an unknown or empty heading type', function (): void {
    expect(Panel::callback(['headingtype' => 'div', 'customclass' => ''], 'x'))->toContain('<p class="panel-title">')
        ->and(Panel::callback(['headingtype' => '', 'customclass' => ''], 'x'))->toContain('<p class="panel-title">');
});

it('adds the noborder or simple style class and ignores any other style', function (): void {
    expect(Panel::callback(['style' => 'noborder', 'customclass' => ''], 'x'))->toContain('class="panel panel-default noborder ')
        ->and(Panel::callback(['style' => 'simple', 'customclass' => ''], 'x'))->toContain('class="panel panel-default simple ')
        ->and(Panel::callback(['style' => 'bootstrap', 'customclass' => ''], 'x'))->toContain('class="panel panel-default  ');
});

it('renders an open collapsible panel with a random id derived from the heading', function (): void {
    $html = Panel::callback(['heading' => 'My Heading!', 'state' => 'open', 'customclass' => ''], 'Body');
    $minus = file_get_contents(UAMS_SHORTCAKES_PATH.'assets/images/glyphicon/minus.svg');
    $plus = file_get_contents(UAMS_SHORTCAKES_PATH.'assets/images/glyphicon/plus-no-bkgrnd.svg');

    expect($html)->toMatch('/<button data-toggle="collapse" data-target="#my-heading-\d+" class="">My Heading!/')
        ->and($html)->toMatch('/<div id="my-heading-\d+" class="panel-collapse collapse in">/')
        ->and($html)->toContain('<span class="showless"><span aria-hidden="true">'.$minus.'</span> Show Less</span>')
        ->toContain('<span class="showmore"><span aria-hidden="true">'.$plus.'</span> Show More</span>');
});

it('renders a closed collapsible panel collapsed and without the in class', function (): void {
    $html = Panel::callback(['heading' => 'Closed', 'state' => 'closed', 'customclass' => ''], 'Body');

    expect($html)->toMatch('/data-target="#closed-\d+" class="collapsed">Closed/')
        ->and($html)->toMatch('/class="panel-collapse collapse "/');
});

it('uses the same id on the toggle and the collapse region', function (): void {
    $html = Panel::callback(['heading' => 'Same', 'state' => 'open', 'customclass' => ''], 'x');

    preg_match('/data-target="#([^"]+)"/', $html, $target);
    preg_match('/<div id="([^"]+)" class="panel-collapse/', $html, $region);

    expect($target[1])->toBe($region[1]);
});

it('leaves the collapsible ids empty when there is no heading', function (): void {
    $html = Panel::callback(['state' => 'open', 'customclass' => ''], 'x');

    expect($html)->toContain('data-target="#" ')
        ->and($html)->toContain('<div id="" class="panel-collapse collapse in">');
});

it('url-decodes the body before wpautop', function (): void {
    expect(Panel::callback(['customclass' => ''], 'a%20b'))->toContain('<p>a b</p>');
});

it('sanitizes each comma-separated custom class', function (): void {
    expect(Panel::callback(['customclass' => 'one,t wo!'], 'x'))->toContain('class="panel panel-default  one two"');
});

it('triggers a deprecation for the default null custom class', function (): void {
    $messages = [];
    set_error_handler(function (int $no, string $str) use (&$messages): bool {
        $messages[] = $str;

        return true;
    });

    try {
        Panel::callback([], 'x');
    } finally {
        restore_error_handler();
    }

    expect($messages)->toHaveCount(1)->and($messages[0])->toContain('explode()');
});

it('throws a TypeError when WordPress passes an empty string for attributes', function (): void {
    expect(fn (): string => Panel::callback('', 'x'))->toThrow(TypeError::class);
});

it('describes its Shortcake attributes', function (): void {
    $args = Panel::get_shortcode_ui_args();
    $style = $args['attrs'][4];

    expect(array_column(Panel::get_attributes(), 'attr'))->toBe(['heading', 'headingtype', 'type', 'state', 'style', 'customclass'])
        ->and($args['attrs'])->toBe(Panel::get_attributes())
        ->and($args['label'])->toBe('Collapsible Panel')
        ->and($args['listItemImage'])->toBe('<img src="https://example.test/wp-content/plugins/uamswp-shortcodes/admin/images/icon-panel.png" />')
        ->and($args['inner_content']['encode'])->toBeTrue()
        ->and(array_keys($style['options']))->toBe(['boostrap', 'noborder', 'simple']);
});
