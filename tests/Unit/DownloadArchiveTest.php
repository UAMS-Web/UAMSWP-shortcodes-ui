<?php

/**
 * What a GitHub download of this repository holds.
 *
 * `.gitattributes` marks every top-level entry `export-ignore` except the plugin entry file and
 * the `inc`, `admin` and `assets` folders, so `git archive` on HEAD holds only what the plugin
 * ships. The top-level entries are recorded as they are; a new top-level file or folder in the
 * download fails the test until it is added to the list.
 */

declare(strict_types=1);

/**
 * Build the archive of HEAD and return the entry names it holds.
 *
 * @return list<string>
 */
function downloadArchiveEntries(): array
{
    $root = dirname(__DIR__, 2);
    $zip = tempnam(sys_get_temp_dir(), 'archive');
    exec('git -C '.escapeshellarg($root).' archive --format=zip -o '.escapeshellarg($zip).' HEAD 2>&1', $output, $status);
    expect($status)->toBe(0, implode("\n", $output));

    $archive = new ZipArchive;
    expect($archive->open($zip))->toBeTrue();
    $names = [];
    for ($i = 0; $i < $archive->numFiles; $i++) {
        $names[] = (string) $archive->getNameIndex($i);
    }

    $archive->close();
    unlink($zip);

    return $names;
}

it('holds these top-level entries', function (): void {
    $names = downloadArchiveEntries();
    // Reported before anything is asserted, so an empty archive is not read as a clean one.
    fwrite(STDERR, 'download archive holds '.count($names)." entries\n");
    expect(count($names))->toBeGreaterThan(0);

    $top = array_values(array_unique(array_map(fn (string $name): string => explode('/', $name)[0], $names)));
    sort($top);

    expect($top)->toBe([
        'admin',
        'assets',
        'inc',
        'uamswp-shortcodes.php',
    ]);
});

it('holds the plugin entry file and leaves the development files out', function (): void {
    $names = downloadArchiveEntries();

    expect($names)->toContain('uamswp-shortcodes.php')
        ->and($names)->not->toContain('AGENTS.md')
        ->and(array_filter($names, fn (string $name): bool => str_starts_with($name, '.claude/')))->toBeEmpty();
});
