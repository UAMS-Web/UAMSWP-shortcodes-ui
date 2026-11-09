<?php

/**
 * The plugin header states the release, the version constant is read from it,
 * and the header names the repository.
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

test('the version constant is read from the header, not written a second time', function (): void {
    expect(plugin_header_source())
        ->toContain("define('UAMS_SHORTCAKES_VERSION', get_file_data(__FILE__, ['Version' => 'Version'])['Version']);")
        ->not->toMatch("/define\('UAMS_SHORTCAKES_VERSION', '/");
});

test('the header version is found by the pattern get_file_data applies', function (): void {
    // The pattern WordPress core's get_file_data() builds for a header field,
    // run over the first 8 KB of the file as core does.
    $head = substr(plugin_header_source(), 0, 8192);

    expect(preg_match('/^(?:[ \t]*<\?php)?[ \t\/*#@]*'.preg_quote('Version', '/').':(.*)$/mi', $head, $matches))->toBe(1)
        ->and(trim($matches[1]))->toMatch('/^\d+\.\d+\.\d+$/');
});
