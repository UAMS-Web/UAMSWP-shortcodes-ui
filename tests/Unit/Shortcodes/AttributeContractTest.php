<?php

/**
 * The Shortcake field definitions every shortcode class hands to the editor.
 *
 * Each class's `get_shortcode_ui_args()` and `get_attributes()` are compared with a stored copy, so a label,
 * attribute name, field type, option or default that changes does so on purpose.
 * The copy lives in `tests/Fixtures/shortcode-attributes.json`; edit it in the same change as the field.
 *
 * @see inc/shortcodes
 */

declare(strict_types=1);

/**
 * The shortcode classes that define their own field list, keyed by class name.
 *
 * @return array<string, class-string>
 */
function shortcodeClassesWithAttributes(): array
{
    $classes = [];

    foreach (glob(dirname(__DIR__, 3).'/inc/shortcodes/class-*.php') ?: [] as $file) {
        if (preg_match('/^class (\w+) extends Shortcode/m', (string) file_get_contents($file), $match) !== 1) {
            continue;
        }

        $class = 'UAMS_Shortcakes\\Shortcodes\\'.$match[1];

        if (! method_exists($class, 'get_attributes') || ! method_exists($class, 'get_shortcode_ui_args')) {
            continue;
        }

        $classes[$match[1]] = $class;
    }

    ksort($classes);

    return $classes;
}

/**
 * The stored field lists, keyed by class name.
 *
 * @return array<string, mixed>
 */
function storedShortcodeAttributes(): array
{
    $stored = json_decode((string) file_get_contents(dirname(__DIR__, 2).'/Fixtures/shortcode-attributes.json'), true, 512, JSON_THROW_ON_ERROR);

    expect($stored)->toBeArray();

    return $stored;
}

beforeEach(function (): void {
    stubWordPress();
});

it('stores a field list for exactly the classes that define one', function (): void {
    expect(array_keys(storedShortcodeAttributes()))->toBe(array_keys(shortcodeClassesWithAttributes()));
});

it('keeps each class label, icon, inner content and field list as stored', function (string $name, string $class): void {
    $actual = json_decode(json_encode([
        'ui_args' => $class::get_shortcode_ui_args(),
        'attributes' => $class::get_attributes(),
    ], JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);

    expect($actual)->toBe(storedShortcodeAttributes()[$name]);
})->with(fn (): array => array_map(
    fn (string $class, string $name): array => [$name, $class],
    shortcodeClassesWithAttributes(),
    array_keys(shortcodeClassesWithAttributes()),
));
