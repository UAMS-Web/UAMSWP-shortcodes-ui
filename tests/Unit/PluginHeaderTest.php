<?php

/**
 * The plugin header and the version constant must state the same release, and
 * the header must name the repository.
 *
 * @see uamswp-shortcodes.php
 */

declare(strict_types=1);

/**
 * Read the main plugin file.
 */
function plugin_header_source(): string
{
    $source = file_get_contents(dirname(__DIR__, 2).'/uamswp-shortcodes.php');

    expect($source)->toBeString();

    return (string) $source;
}

test('the header names the repository as the plugin URI', function (): void {
    expect(preg_match('/^ \* Plugin URI: (\S+)$/m', plugin_header_source(), $matches))->toBe(1)
        ->and($matches[1])->toBe('https://github.com/UAMS-Web/UAMSWP-shortcodes-ui');
});

test('the header version is a three-segment release number', function (): void {
    expect(preg_match('/^ \* Version: (\S+)$/m', plugin_header_source(), $matches))->toBe(1)
        ->and($matches[1])->toMatch('/^\d+\.\d+\.\d+$/');
});

test('the version constant equals the header version', function (): void {
    $source = plugin_header_source();

    expect(preg_match('/^ \* Version: (\S+)$/m', $source, $header))->toBe(1)
        ->and(preg_match("/define\\('UAMS_SHORTCAKES_VERSION', '([^']+)'\\)/", $source, $constant))->toBe(1)
        ->and($constant[1])->toBe($header[1]);
});
