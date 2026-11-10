<?php

/**
 * The shortcode class autoloader: how a class name becomes a file under inc/shortcodes/.
 *
 * Each case runs in a child PHP process on the test bootstrap, because a class
 * already loaded in the suite process would never reach the autoloader.
 *
 * @see inc/class-uams-shortcodes.php
 */

declare(strict_types=1);

use UAMS_Shortcakes\Shortcodes\Image_Comparison;
use UAMS_Shortcakes\Shortcodes\Lead;

/**
 * Ask a fresh PHP process to load a class and report what it did.
 *
 * @return array{loaded: bool, files: list<string>}
 */
function autoloadInChild(string $class): array
{
    $root = dirname(__DIR__, 2);
    $code = 'require '.var_export($root.'/tests/bootstrap.php', true).';'
        .'$before = get_included_files();'
        .'$loaded = class_exists('.var_export($class, true).');'
        .'echo json_encode(["loaded" => $loaded, "files" => array_values(array_map("basename", array_diff(get_included_files(), $before)))]);';

    $script = tempnam(sys_get_temp_dir(), 'autoload');
    file_put_contents($script, '<?php '.$code);
    $output = shell_exec(escapeshellarg(PHP_BINARY).' '.escapeshellarg($script).' 2>&1');
    unlink($script);

    return json_decode((string) $output, true, 512, JSON_THROW_ON_ERROR);
}

it('requires the file for a class under the shortcode namespace that is not loaded yet', function (): void {
    expect(autoloadInChild(Lead::class))
        ->toBe(['loaded' => true, 'files' => ['class-lead.php', 'class-shortcode.php']]);
});

it('maps an underscore in the class name to a hyphen in the file name', function (): void {
    expect(autoloadInChild(Image_Comparison::class))
        ->toBe(['loaded' => true, 'files' => ['class-image-comparison.php', 'class-shortcode.php']]);
});

it('maps a class name with a leading backslash to its file', function (): void {
    $loader = (new ReflectionClass(UAMS_Shortcakes::class))->newInstanceWithoutConstructor();

    expect($loader->shortcode_class_file('\\'.Image_Comparison::class))
        ->toEndWith('/shortcodes/class-image-comparison.php')
        ->and($loader->shortcode_class_file('Other\Thing'))->toBeNull();
});

it('requires nothing for a class outside the namespace', function (): void {
    expect(autoloadInChild('Other\Thing'))->toBe(['loaded' => false, 'files' => []]);
});

it('requires nothing for a class under the namespace that has no file', function (): void {
    expect(autoloadInChild('UAMS_Shortcakes\Shortcodes\Missing_One'))->toBe(['loaded' => false, 'files' => []]);
});
