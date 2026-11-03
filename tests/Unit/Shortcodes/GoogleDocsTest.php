<?php

/**
 * Google Docs shortcode: URL parsing, embed type detection, host validation and
 * the reversal of Google iframes into shortcodes.
 *
 * @see inc/shortcodes/class-googledocs.php
 */

declare(strict_types=1);

use UAMS_Shortcakes\Shortcodes\GoogleDocs;

use function Brain\Monkey\Functions\when;

beforeEach(function (): void {
    stubWordPress();

    $this->addQueryArgCalls = [];
    $calls = &$this->addQueryArgCalls;

    // Same contract as WordPress' add_query_arg() for the two forms the class uses.
    when('add_query_arg')->alias(function (...$args) use (&$calls): string {
        $calls[] = $args;

        if (is_array($args[0])) {
            [$params, $url] = $args;
        } else {
            [$key, $value, $url] = $args;
            $params = [$key => $value];
        }

        return $url.(str_contains($url, '?') ? '&' : '?').http_build_query($params);
    });
    when('sanitize_key')->alias(fn ($key): string => strtolower((string) $key));
});

/**
 * Call the private URL parser.
 *
 * @return array<string, mixed>|null
 */
function googleDocsParse(string $url): ?array
{
    $method = new ReflectionMethod(GoogleDocs::class, 'parse_from_url');

    return $method->invoke(null, $url);
}

it('uses the lowercase class name as its shortcode tag', function (): void {
    expect(GoogleDocs::get_shortcode_tag())->toBe('googledocs');
});

it('splits a plain document url into type and embed id', function (): void {
    $parts = googleDocsParse('https://docs.google.com/document/d/ABC');

    expect($parts)->toMatchArray(['subdomain' => 'docs', 'doc_type' => 'document', 'embed_id' => 'ABC']);
});

it('splits the view name and query string from an embed url', function (): void {
    $parts = googleDocsParse('https://docs.google.com/spreadsheets/d/ABC/pubhtml?widget=true&headers=false');

    expect($parts)->toMatchArray([
        'doc_type' => 'spreadsheets',
        'embed_id' => 'ABC',
        'view_name' => 'pubhtml',
        'query_string' => 'widget=true&headers=false',
    ]);
});

it('parses a maps url with the verb in the embed id slot', function (): void {
    $parts = googleDocsParse('https://www.google.com/maps/d/embed?mid=XYZ');

    expect($parts)->toMatchArray(['subdomain' => 'www', 'doc_type' => 'maps', 'embed_id' => 'embed', 'query_string' => 'mid=XYZ']);
});

it('returns null from the parser for a host that is not Google', function (): void {
    expect(googleDocsParse('https://example.com/document/d/ABC'))->toBeNull();
});

it('returns null from the parser when the url does not match the pattern', function (): void {
    expect(googleDocsParse('https://docs.google.com/document?x=1/2'))->toBeNull();
});

it('renders nothing for a host that is not Google', function (): void {
    expect(GoogleDocs::callback(['url' => 'https://example.com/document/d/ABC']))->toBe('');
});

it('renders nothing when the url has no document type', function (): void {
    expect(GoogleDocs::callback(['url' => 'https://docs.google.com/']))->toBe('');
});

it('renders nothing for an empty url', function (): void {
    expect(GoogleDocs::callback(['url' => '']))->toBe('');
});

it('renders nothing for a document type it does not embed', function (): void {
    expect(GoogleDocs::callback(['url' => 'https://docs.google.com/drawings/d/ABC']))->toBe('');
});

it('currently throws a TypeError for every embeddable type because the attribute reducer starts from null', function (string $url): void {
    expect(fn (): string => GoogleDocs::callback(['url' => $url]))->toThrow(TypeError::class);
})->with([
    'document' => 'https://docs.google.com/document/d/ABC',
    'spreadsheet' => 'https://docs.google.com/spreadsheets/d/ABC',
    'presentation' => 'https://docs.google.com/presentation/d/ABC',
    'form' => 'https://docs.google.com/forms/d/ABC',
    'map' => 'https://www.google.com/maps/d/embed?mid=XYZ',
    'fusiontable' => 'https://www.google.com/fusiontables/embedviz?q=1',
]);

it('builds the document embed url before failing', function (): void {
    try {
        GoogleDocs::callback(['url' => 'https://docs.google.com/document/d/ABC']);
    } catch (TypeError) {
    }

    expect($this->addQueryArgCalls)->toBe([[['embedded' => 'true'], 'https://docs.google.com/document/d/ABC/pub']]);
});

it('builds the spreadsheet embed url with headers off by default and on when asked', function (): void {
    foreach ([[], ['headers' => '1']] as $extra) {
        try {
            GoogleDocs::callback(['url' => 'https://docs.google.com/spreadsheets/d/ABC'] + $extra);
        } catch (TypeError) {
        }
    }

    expect($this->addQueryArgCalls)->toBe([
        [['widget' => 'true', 'headers' => 'false'], 'https://docs.google.com/spreadsheets/d/ABC/pubhtml'],
        [['widget' => 'true', 'headers' => 'true'], 'https://docs.google.com/spreadsheets/d/ABC/pubhtml'],
    ]);
});

it('builds the presentation embed url with start, loop and a 3000 ms default delay', function (): void {
    foreach ([[], ['start' => '1', 'loop' => '1', 'delayms' => '1500abc']] as $extra) {
        try {
            GoogleDocs::callback(['url' => 'https://docs.google.com/presentation/d/ABC'] + $extra);
        } catch (TypeError) {
        }
    }

    expect($this->addQueryArgCalls)->toBe([
        [['start' => 'false', 'loop' => 'false', 'delayms' => '3000'], 'https://docs.google.com/presentation/d/ABC/embed'],
        [['start' => 'true', 'loop' => 'true', 'delayms' => 1500], 'https://docs.google.com/presentation/d/ABC/embed'],
    ]);
});

it('builds the form embed url from the viewform view', function (): void {
    try {
        GoogleDocs::callback(['url' => 'https://docs.google.com/forms/d/ABC']);
    } catch (TypeError) {
    }

    expect($this->addQueryArgCalls)->toBe([[['embedded' => 'true'], 'https://docs.google.com/forms/d/ABC/viewform']]);
});

it('does not touch the query arguments for maps and fusion tables', function (): void {
    foreach (['https://www.google.com/maps/d/embed?mid=XYZ', 'https://www.google.com/fusiontables/embedviz?q=1'] as $url) {
        try {
            GoogleDocs::callback(['url' => $url]);
        } catch (TypeError) {
        }
    }

    expect($this->addQueryArgCalls)->toBe([]);
});

it('currently warns about an undefined url key when the attribute is missing', function (): void {
    $errors = [];
    set_error_handler(function (int $no, string $message) use (&$errors): bool {
        $errors[] = $message;

        return true;
    });

    try {
        $html = GoogleDocs::callback([]);
    } finally {
        restore_error_handler();
    }

    expect($html)->toBe('')
        ->and($errors[0])->toBe('Undefined array key "url"');
});

it('describes its Shortcake attributes', function (): void {
    $args = GoogleDocs::get_shortcode_ui_args();

    expect($args['label'])->toBe('Google Docs')
        ->and($args['listItemImage'])->toBe('<img src="https://example.test/wp-content/plugins/uamswp-shortcodes/assets/images/svg/icon-googledocs.svg" />')
        ->and(array_column($args['attrs'], 'attr'))->toBe(['url', 'height', 'width', 'disableresponsiveness', 'headers', 'start', 'loop', 'delayms'])
        ->and(end($args['attrs'])['default'])->toBe(3000);
});

it('turns a document iframe into a shortcode with its size', function (): void {
    $content = '<iframe src="https://docs.google.com/document/d/ABC/pub?embedded=true" width="640" height="480"></iframe>';

    expect(GoogleDocs::reversal($content))->toBe('[googledocs url="https://docs.google.com/document/d/ABC" height=480 width=640]');
});

it('omits the size when the iframe has none', function (): void {
    $content = '<iframe src="https://docs.google.com/forms/d/ABC/viewform?embedded=true"></iframe>';

    expect(GoogleDocs::reversal($content))->toBe('[googledocs url="https://docs.google.com/forms/d/ABC"]');
});

it('keeps the headers flag from a spreadsheet iframe unless it is false', function (): void {
    $on = '<iframe src="https://docs.google.com/spreadsheets/d/ABC/pubhtml?widget=true&amp;headers=true"></iframe>';
    $off = '<iframe src="https://docs.google.com/spreadsheets/d/ABC/pubhtml?widget=true&amp;headers=false"></iframe>';

    expect(GoogleDocs::reversal($on))->toBe('[googledocs url="https://docs.google.com/spreadsheets/d/ABC" headers="true"]')
        ->and(GoogleDocs::reversal($off))->toBe('[googledocs url="https://docs.google.com/spreadsheets/d/ABC"]');
});

it('keeps start, loop and delay from a presentation iframe', function (): void {
    $content = '<iframe src="https://docs.google.com/presentation/d/ABC/embed?start=true&amp;loop=true&amp;delayms=2000x" width="100"></iframe>';

    expect(GoogleDocs::reversal($content))
        ->toBe('[googledocs url="https://docs.google.com/presentation/d/ABC" width=100 start="true" loop="true" delayms=2000]');
});

it('drops a presentation start flag that is false', function (): void {
    $content = '<iframe src="https://docs.google.com/presentation/d/ABC/embed?start=false&amp;loop=false"></iframe>';

    expect(GoogleDocs::reversal($content))->toBe('[googledocs url="https://docs.google.com/presentation/d/ABC"]');
});

it('turns a form iframe into a shortcode', function (): void {
    $content = '<iframe src="https://docs.google.com/forms/d/ABC/viewform?embedded=true" height="900"></iframe>';

    expect(GoogleDocs::reversal($content))->toBe('[googledocs url="https://docs.google.com/forms/d/ABC" height=900]');
});

it('turns a map iframe into a shortcode with the mid argument', function (): void {
    $content = '<iframe src="https://www.google.com/maps/d/embed?mid=XYZ" width="500"></iframe>';

    expect(GoogleDocs::reversal($content))->toBe('[googledocs url="https://www.google.com/maps/d/embed?mid=XYZ" width=500]');
});

it('currently returns null from reversal when a map iframe has no mid', function (): void {
    $content = '<iframe src="https://www.google.com/maps/d/embed?foo=bar"></iframe>';

    expect(GoogleDocs::reversal($content))->toBeNull();
});

it('turns a fusion table iframe into a shortcode keeping its query', function (): void {
    $content = '<iframe src="https://www.google.com/fusiontables/embedviz?q=1&amp;viz=GVIZ"></iframe>';

    expect(GoogleDocs::reversal($content))->toBe('[googledocs url="https://www.google.com/fusiontables/embedviz?q=1&viz=GVIZ"]');
});

it('leaves iframes from other hosts alone', function (): void {
    $content = '<iframe src="https://example.com/document/d/ABC"></iframe>';

    expect(GoogleDocs::reversal($content))->toBe($content);
});

it('leaves a Google iframe whose url does not parse alone', function (): void {
    $content = '<iframe src="https://docs.google.com/document?x=1/2"></iframe>';

    expect(GoogleDocs::reversal($content))->toBe($content);
});

it('leaves a Google iframe of an unsupported type alone', function (): void {
    $content = '<iframe src="https://docs.google.com/drawings/d/ABC/pub"></iframe>';

    expect(GoogleDocs::reversal($content))->toBe($content);
});

it('returns content without iframes unchanged', function (): void {
    expect(GoogleDocs::reversal('<p>Plain</p>'))->toBe('<p>Plain</p>');
});
