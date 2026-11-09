<?php

/**
 * Map shortcode: building validation, iframe markup and the Shortcake arguments.
 *
 * @see inc/shortcodes/class-map.php
 */

declare(strict_types=1);

use UAMS_Shortcakes\Shortcodes\Map;

beforeEach(function (): void {
    stubWordPress();
});

it('uses the lowercase class name as its shortcode tag', function (): void {
    expect(Map::get_shortcode_tag())->toBe('map');
});

it('requires a building', function (): void {
    expect(Map::callback([]))->toBe('required attribute "building" missing')
        ->and(Map::callback(['building' => null]))->toBe('required attribute "building" missing')
        ->and(Map::callback('', 'x'))->toBe('required attribute "building" missing');
});

it('rejects a building code that is not supported', function (): void {
    expect(Map::callback(['building' => '999']))->toBe('Building "999" is not supported')
        ->and(Map::callback(['building' => '']))->toBe('Building "" is not supported')
        ->and(Map::callback(['building' => '12']))->toBe('Building "12" is not supported');
});

it('renders the campus map iframe with the default size', function (): void {
    $html = Map::callback(['building' => '135']);

    expect(preg_replace('/>\s+</', '><', trim($html)))->toBe(
        '<div class="uams-campus-map "><iframe width="100%" height="480px" src="//maps.uams.edu/full-screen/?marker=135" frameborder="0"></iframe><a href="https://maps.uams.edu/map-mashup/?markerid=135" target="_blank">View full map</a></div>'
    );
});

it('accepts a numeric building code that is in the supported list', function (): void {
    expect(Map::callback(['building' => '7']))->toContain('?marker=7"');
});

it('uses a custom width and height', function (): void {
    expect(Map::callback(['building' => '135', 'width' => '50%', 'height' => '300px']))
        ->toContain('<iframe width="50%" height="300px" ');
});

it('falls back to the default size when width or height is explicitly null', function (): void {
    expect(Map::callback(['building' => '135', 'width' => null, 'height' => null]))
        ->toContain('<iframe width="100%" height="480px" ');
});

it('accepts a number with or without a CSS unit as the width and height', function (string $length): void {
    expect(Map::callback(['building' => '135', 'width' => $length, 'height' => $length]))
        ->toContain('<iframe width="'.$length.'" height="'.$length.'" ');
})->with(['320', '50%', '300px', '20em', '1.5rem', '100vw', '80vh']);

it('falls back to the default size for a width or height that is not a CSS length', function (string $length): void {
    expect(Map::callback(['building' => '135', 'width' => $length, 'height' => $length]))
        ->toContain('<iframe width="100%" height="480px" ')
        ->not->toContain('onload');
})->with(['1" onload="x', 'auto', '10 px', '-5px', '', 'calc(100% - 1px)']);

it('sanitizes each comma-separated custom class', function (): void {
    expect(Map::callback(['building' => '135', 'customclass' => 'one,t wo!']))->toContain('<div class="uams-campus-map one two">');
});

it('lists the same building codes in the UI as the callback accepts', function (): void {
    $property = new ReflectionProperty(Map::class, 'buildingcode');
    $accepted = $property->getValue();
    $options = array_map(strval(...), array_keys(array_column(Map::get_attributes(), null, 'attr')['building']['options']));

    sort($accepted);
    sort($options);

    expect($options)->toBe($accepted)->and($accepted)->toHaveCount(44);
});

it('describes its Shortcake attributes', function (): void {
    $args = Map::get_shortcode_ui_args();

    expect(array_column(Map::get_attributes(), 'attr'))->toBe(['building', 'width', 'height', 'customclass'])
        ->and($args['attrs'])->toBe(Map::get_attributes())
        ->and($args['label'])->toBe('UAMS Map')
        ->and($args['listItemImage'])->toBe('dashicons-location-alt')
        ->and(Map::URL)->toBe('//maps.uams.edu/full-screen/?marker=');
});
