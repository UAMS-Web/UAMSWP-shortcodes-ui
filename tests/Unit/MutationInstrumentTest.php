<?php

/**
 * The mutation run is only honest while the test bootstrap leaves Patchwork to
 * Brain Monkey, and while `composer mutate` exists for the shared CI chain to call.
 *
 * With Patchwork required in tests/bootstrap.php, a mutant child that scans
 * tests/Unit dies on readdir() and is scored as a kill, so a run reports 100%
 * over mutants that never ran (measured on inc/shortcodes/class-button.php:
 * 106 tested at 100.00% with the require, 54 tested and 52 untested at 50.94%
 * without it).
 *
 * @see tests/bootstrap.php
 */

declare(strict_types=1);

/**
 * Read a file from the repository root.
 */
function mutation_instrument_file(string $relative): string
{
    $contents = file_get_contents(dirname(__DIR__, 2).'/'.$relative);

    expect($contents)->toBeString();

    return (string) $contents;
}

test('the test bootstrap does not require Patchwork', function (): void {
    expect(mutation_instrument_file('tests/bootstrap.php'))->not->toMatch('/require(_once)?\b[^;]*patchwork/i');
});

test('composer mutate mutates the plugin source with a relative path and no process timeout', function (): void {
    $composer = json_decode(mutation_instrument_file('composer.json'), true, 512, JSON_THROW_ON_ERROR);

    expect($composer['scripts']['mutate'])->toBe([
        'Composer\\Config::disableProcessTimeout',
        '@php vendor/bin/pest --mutate --covered-only --path=inc',
    ]);
});
