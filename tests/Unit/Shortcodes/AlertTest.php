<?php

/**
 * Alert shortcode: the markup `callback()` returns and the Shortcake arguments.
 *
 * @see inc/shortcodes/class-alert.php
 */

declare(strict_types=1);

use UAMS_Shortcakes\Shortcodes\Alert;

beforeEach(function (): void {
    stubWordPress();
});

it('uses the lowercase class name as its shortcode tag', function (): void {
    expect(Alert::get_shortcode_tag())->toBe('alert');
});

it('renders an info alert with the info icon by default', function (): void {
    $html = Alert::callback(['customclass' => ''], 'Body');

    expect($html)
        ->toContain('class="uams-alert alert alert-info ')
        ->toContain('role="alert"')
        ->toContain('<div class="alert-body"><p>Body</p></div>')
        ->not->toContain('data-dismiss');
});

it('falls back to info for a type that is not allowed', function (): void {
    expect(Alert::callback(['type' => 'bogus', 'customclass' => ''], 'x'))->toContain('alert-info');
});

it('falls back to info for an empty type', function (): void {
    expect(Alert::callback(['type' => '', 'customclass' => ''], 'x'))->toContain('alert-info');
});

it('keeps each allowed type', function (string $type): void {
    expect(Alert::callback(['type' => $type, 'customclass' => ''], 'x'))->toContain('alert-'.$type);
})->with(['success', 'info', 'help', 'warning', 'danger']);

it('inlines the shipped SVG for each type', function (string $type, string $file): void {
    $svg = file_get_contents(UAMS_SHORTCAKES_PATH.'assets/images/'.$file);

    expect($svg)->toStartWith('<')
        ->and(Alert::callback(['type' => $type, 'customclass' => ''], 'x'))
        ->toContain('<div class="alert-icon"><span aria-hidden="true">'.$svg.'</span></div>');
})->with([
    ['success', 'glyphicon/checkbox.svg'],
    ['info', 'dashicons/info.svg'],
    ['help', 'glyphicon/question.svg'],
    ['warning', 'dashicons/warning.svg'],
    ['danger', 'glyphicon/x.svg'],
]);

it('ignores an icon attribute, so the SVG path comes from the type alone', function (string $icon): void {
    $svg = file_get_contents(UAMS_SHORTCAKES_PATH.'assets/images/dashicons/info.svg');
    $html = Alert::callback(['icon' => $icon, 'customclass' => ''], 'x');

    expect($html)->toContain('<span aria-hidden="true">'.$svg.'</span>')
        ->not->toContain('<?php');
})->with([
    'glyphicon-../../../../composer.json',
    'dashicons-../../../uamswp-shortcodes.php',
    "glyphicon-x\0",
    'svg-..%2F..%2Fcomposer.json',
]);

it('adds a dismiss button when dismiss is set', function (): void {
    expect(Alert::callback(['dismiss' => true, 'customclass' => ''], 'x'))
        ->toContain('<button type="button" class="close" data-dismiss="alert"');
});

it('adds a dismiss button for the string values a shortcode attribute carries', function (string $dismiss): void {
    expect(Alert::callback(['dismiss' => $dismiss, 'customclass' => ''], 'x'))
        ->toContain('data-dismiss="alert"');
})->with(['1', 'true']);

it('reads the SVG from the plugin path whatever the working directory is', function (): void {
    $svg = file_get_contents(UAMS_SHORTCAKES_PATH.'assets/images/dashicons/info.svg');
    $directory = getcwd();
    chdir(sys_get_temp_dir());

    try {
        $html = Alert::callback(['customclass' => ''], 'x');
    } finally {
        chdir($directory);
    }

    expect($html)->toContain('<span aria-hidden="true">'.$svg.'</span>');
});

it('sanitizes each comma-separated custom class', function (): void {
    expect(Alert::callback(['customclass' => 'one,t wo!'], 'x'))->toContain('alert-info one two"');
});

it('describes its Shortcake attributes', function (): void {
    $attrs = array_column(Alert::get_attributes(), 'attr');

    expect($attrs)->toBe(['type', 'dismiss', 'customclass'])
        ->and(Alert::get_shortcode_ui_args()['attrs'])->toBe(Alert::get_attributes());
});
