<?php

/**
 * LiveWhale calendar shortcode: the container markup, the globals that feed the
 * footer script, the enqueued scripts and the Shortcake arguments.
 *
 * @see inc/shortcodes/class-livewhale.php
 */

declare(strict_types=1);

use UAMS_Shortcakes\Shortcodes\LiveWhale;

use function Brain\Monkey\Actions\expectAdded;
use function Brain\Monkey\Functions\when;

beforeEach(function (): void {
    stubWordPress();

    when('wp_enqueue_script')->justReturn(true);

    // The class communicates through globals; start each test from a blank slate.
    foreach (['defaultview', 'group', 'type', 'audience', 'tags', 'excludetags', 'starred', 'repeats', 'max', 'width', 'height', 'customclasses'] as $name) {
        unset($GLOBALS[$name]);
    }
});

/**
 * Run the callback while collecting PHP warnings instead of failing the test.
 *
 * @param  mixed  $attrs
 * @return array{0: string, 1: list<string>}
 */
function liveWhaleCapture($attrs): array
{
    $errors = [];
    set_error_handler(function (int $no, string $message) use (&$errors): bool {
        $errors[] = $message;

        return true;
    });

    try {
        $html = LiveWhale::callback($attrs, '');
    } finally {
        restore_error_handler();
    }

    return [$html, $errors];
}

it('uses the lowercase class name as its shortcode tag', function (): void {
    expect(LiveWhale::get_shortcode_tag())->toBe('livewhale');
});

it('renders the calendar container without the view selector for the default list view', function (): void {
    $html = LiveWhale::callback(['include_repeats' => false]);

    expect($html)
        ->toContain('<div id="lw_cal">')
        ->toContain('<div id="lw_cal_header"></div>')
        ->toContain('<div id="lw_cal_events"></div>')
        ->not->toContain('lw_cal_view_selector');
});

it('adds the view selector for any view other than all', function (string $view): void {
    expect(LiveWhale::callback(['view' => $view, 'include_repeats' => false]))
        ->toContain('<div id="lw_cal_view_selector"></div>');
})->with(['day', 'week', 'month']);

it('registers the footer script and enqueues both scripts', function (): void {
    $enqueued = [];
    when('wp_enqueue_script')->alias(function (...$args) use (&$enqueued): bool {
        $enqueued[] = $args;

        return true;
    });
    expectAdded('wp_footer')->once();

    LiveWhale::callback(['include_repeats' => false]);

    expect($enqueued)->toBe([
        ['googlemaps-api', 'https://maps.googleapis.com/maps/api/js?sensor=false', [], false, false],
        ['livewhale-js', 'https://uams-dev.lwcal.com/livewhale/scripts/cors-calendar.js', [], false, false],
    ]);
});

it('stores the escaped view and group in globals', function (): void {
    LiveWhale::callback(['view' => 'week', 'group' => 'Main Calendar', 'type' => 'a,b', 'audience' => 'Staff', 'include_repeats' => false]);

    expect($GLOBALS['defaultview'])->toBe('week')
        ->and($GLOBALS['group'])->toBe('Main Calendar')
        ->and($GLOBALS['type'])->toBe('a,b')
        ->and($GLOBALS['audience'])->toBe('Staff');
});

it('does not set the group global when group is null', function (): void {
    LiveWhale::callback(['include_repeats' => false]);

    expect(isset($GLOBALS['group']))->toBeFalse()
        ->and($GLOBALS['defaultview'])->toBe('all');
});

it('falls back to the defaults when the attributes are not an array', function (): void {
    [$html, $errors] = liveWhaleCapture('');

    expect($html)->toContain('<div id="lw_cal">')
        ->and($GLOBALS['defaultview'])->toBe('all')
        ->and($errors)->toBe([]);
});

it('builds the tag filter literal from comma-separated tags', function (): void {
    LiveWhale::callback(['tags' => 'one,two', 'exclude_tags' => 'x', 'include_repeats' => false]);

    expect($GLOBALS['tags'])->toBe("tag: ['one','two'],")
        ->and($GLOBALS['excludetags'])->toBe("exclude_tag: ['x'],");
});

it('escapes each tag for the single-quoted script string it lands in', function (): void {
    when('esc_js')->alias(fn ($text): string => 'JS['.$text.']');

    LiveWhale::callback(['tags' => "o'ne,two", 'exclude_tags' => 'x', 'include_repeats' => false]);

    expect($GLOBALS['tags'])->toBe("tag: ['JS[o'ne]','JS[two]'],")
        ->and($GLOBALS['excludetags'])->toBe("exclude_tag: ['JS[x]'],");
});

it('leaves the tag filters null when none are given', function (): void {
    LiveWhale::callback(['include_repeats' => false]);

    expect($GLOBALS['tags'])->toBeNull()
        ->and($GLOBALS['excludetags'])->toBeNull();
});

it('maps only_starred to the string true or false', function (): void {
    LiveWhale::callback(['include_repeats' => false]);
    expect($GLOBALS['starred'])->toBe('false');

    LiveWhale::callback(['only_starred' => true, 'include_repeats' => false]);
    expect($GLOBALS['starred'])->toBe('true');
});

it('treats a truthy string flag like a boolean for only_starred and include_repeats', function (): void {
    LiveWhale::callback(['only_starred' => '1', 'include_repeats' => '1']);

    expect($GLOBALS['starred'])->toBe('true')
        ->and($GLOBALS['repeats'])->toBe('false');
});

it('uses the width attribute when the height is empty and the default width when both are', function (): void {
    LiveWhale::callback(['width' => 300, 'height' => 0, 'include_repeats' => false]);
    expect($GLOBALS['width'])->toBe(300);

    LiveWhale::callback(['width' => 0, 'height' => 0, 'include_repeats' => false]);
    expect($GLOBALS['width'])->toBe('200');
});

it('registers the footer script at priority 30 under its own callable name', function (): void {
    expectAdded('wp_footer')->once()->with(LiveWhale::class.'::lw_wp_footer', 30);

    LiveWhale::callback(['include_repeats' => false]);
});

it('keeps the opening wrapper ahead of the view selector and closes it after the container', function (): void {
    [$withClass] = liveWhaleCapture(['customclass' => 'one', 'view' => 'week', 'include_repeats' => false]);
    $without = LiveWhale::callback(['view' => 'week', 'include_repeats' => false]);

    expect($withClass)->toStartWith('<div class="one"><!-- The Day, Week, Month, All Upcoming Events tabs -->')
        ->and(substr_count($withClass, '</div>'))->toBe(substr_count($without, '</div>') + 1)
        ->and($withClass)->toEndWith('</div></div>')
        ->and($without)->not->toEndWith('</div></div>');
});

it('inverts include_repeats into the hide_repeats flag', function (): void {
    LiveWhale::callback(['include_repeats' => false]);
    expect($GLOBALS['repeats'])->toBe('true');

    LiveWhale::callback(['include_repeats' => true]);
    expect($GLOBALS['repeats'])->toBe('false');
});

it('hides repeats without a warning when include_repeats is not passed', function (): void {
    [$html, $errors] = liveWhaleCapture([]);

    expect($errors)->toBe([])
        ->and($GLOBALS['repeats'])->toBe('true')
        ->and($html)->toContain('lw_cal');
});

it('builds the max literal from an integer cast of max', function (): void {
    LiveWhale::callback(['max' => '12abc', 'include_repeats' => false]);

    expect($GLOBALS['max'])->toBe('max: 12,');
});

it('defaults max to 5', function (): void {
    LiveWhale::callback(['include_repeats' => false]);

    expect($GLOBALS['max'])->toBe('max: 5,');
});

it('does not touch the max global in month view', function (): void {
    $GLOBALS['max'] = 'sentinel';

    LiveWhale::callback(['view' => 'month', 'max' => 9, 'include_repeats' => false]);

    expect($GLOBALS['max'])->toBe('sentinel');
});

it('keeps a width attribute when no height is given', function (): void {
    LiveWhale::callback(['width' => '320px', 'include_repeats' => false]);

    expect($GLOBALS['width'])->toBe(320)
        ->and($GLOBALS['height'])->toBe(200);
});

it('stores the height value in the height global and the width value in the width global', function (): void {
    LiveWhale::callback(['width' => 300, 'height' => 150, 'include_repeats' => false]);

    expect($GLOBALS['width'])->toBe(300)
        ->and($GLOBALS['height'])->toBe(150);
});

it('leaves the container unwrapped when no custom class is given', function (): void {
    $html = LiveWhale::callback(['include_repeats' => false]);

    expect($GLOBALS['customclasses'])->toBe([])
        ->and($html)->not->toContain('<div class="');
});

it('wraps in a div carrying the sanitized custom classes', function (): void {
    [$html, $errors] = liveWhaleCapture(['customclass' => 'one,two<x>', 'include_repeats' => false]);

    expect($GLOBALS['customclasses'])->toBe(['one', 'twox'])
        ->and($html)->toStartWith('<div class="one twox">')
        ->and($html)->toEndWith('</div>')
        ->and($errors)->toBe([]);
});

it('prints the footer script from the globals the callback set', function (): void {
    LiveWhale::callback(['view' => 'week', 'tags' => 'a', 'exclude_tags' => 'b', 'only_starred' => true, 'include_repeats' => true, 'max' => 3]);

    ob_start();
    (new LiveWhale)->lw_wp_footer();
    $script = (string) ob_get_clean();

    expect($script)
        ->toContain("host: 'https://calendar.uams.edu'")
        ->toContain("default_view: 'week'")
        ->toContain('hide_repeats: false')
        ->toContain('only_starred: true')
        ->toContain('thumb_width: 200')
        ->toContain('max: 3,')
        ->toContain("tag: ['a'],")
        ->toContain("exclude_tag: ['b'],");
});

it('has a public URL constant for the calendar host', function (): void {
    expect(LiveWhale::URL)->toBe('https://calendar.uams.edu');
});

it('describes its Shortcake attributes', function (): void {
    $attrs = array_column(LiveWhale::get_attributes(), 'attr');

    expect($attrs)->toBe(['view', 'group', 'type', 'audience', 'tags', 'exclude_tags', 'only_starred', 'include_repeats', 'max', 'customclass']);

    $args = LiveWhale::get_shortcode_ui_args();
    expect($args['label'])->toBe('UAMS Calendar')
        ->and($args['listItemImage'])->toBe('dashicons-calendar-alt')
        ->and($args['attrs'])->toBe(LiveWhale::get_attributes());

    $view = LiveWhale::get_attributes()[0];
    expect(array_keys($view['options']))->toBe(['all', 'day', 'week', 'month']);
});
