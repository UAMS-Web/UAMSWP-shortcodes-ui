<?php

/**
 * RSS_Posts shortcode: feed rendering with a faked feed object, no network.
 *
 * @see inc/shortcodes/class-rss-posts.php
 */

declare(strict_types=1);

use Brain\Monkey\Functions;
use UAMS_Shortcakes\Shortcodes\RSS_Posts;

use function Brain\Monkey\Functions\when;

beforeEach(function (): void {
    stubWordPress();

    // The class includes ABSPATH.WPINC.'/feed.php'; point it at an empty file.
    if (! defined('ABSPATH')) {
        $dir = sys_get_temp_dir().'/uams-rss-posts-test/';
        if (! is_dir($dir.'wp-includes')) {
            mkdir($dir.'wp-includes', 0777, true);
        }

        file_put_contents($dir.'wp-includes/feed.php', "<?php\n");
        define('ABSPATH', $dir);
        define('WPINC', 'wp-includes');
    }

    when('is_wp_error')->alias(fn ($thing): bool => $thing instanceof WP_Error_Stub);
    // The real function rejects anything but an http(s) URL to a public host; these tests pass it short names.
    when('wp_http_validate_url')->alias(fn ($url) => $url);
});

/**
 * Stand-in for WP_Error so is_wp_error() can tell a failed fetch apart.
 */
class WP_Error_Stub {}

/**
 * Build a SimplePie-like feed with the given items.
 *
 * @param  list<array<string, string>>  $items
 */
function rssFeed(array $items): object
{
    $objects = array_map(fn (array $item): object => new class($item)
    {
        public function __construct(private array $item) {}

        public function get_permalink(): string
        {
            return $this->item['link'];
        }

        public function get_date(string $format): string
        {
            return $this->item['date'].'|'.$format;
        }

        public function get_title(): string
        {
            return $this->item['title'];
        }

        public function get_content(): string
        {
            return $this->item['content'];
        }

        public function get_category(): object
        {
            $label = $this->item['category'];

            return new class($label)
            {
                public function __construct(private string $label) {}

                public function get_label(): string
                {
                    return $this->label;
                }
            };
        }

        public function get_author(): object
        {
            $name = $this->item['author'];

            return new class($name)
            {
                public function __construct(private string $name) {}

                public function get_name(): string
                {
                    return $this->name;
                }
            };
        }
    }, $items);

    return new class($objects)
    {
        public ?int $requested = null;

        public function __construct(private array $items) {}

        public function get_item_quantity(string|int $max): int
        {
            $this->requested = (int) $max;

            return min(count($this->items), (int) $max);
        }

        public function get_items(int $start, int $count): array
        {
            return array_slice($this->items, $start, $count);
        }
    };
}

/**
 * Two sample feed items.
 *
 * @return list<array<string, string>>
 */
function rssSampleItems(): array
{
    return [
        ['link' => 'https://example.test/a', 'date' => '2026-01-02', 'title' => 'First', 'content' => 'Alpha', 'category' => 'News', 'author' => 'Ann'],
        ['link' => 'https://example.test/b', 'date' => '2026-01-03', 'title' => 'Second', 'content' => 'Beta', 'category' => 'Events', 'author' => 'Bob'],
    ];
}

it('uses the lowercase class name as its shortcode tag', function (): void {
    expect(RSS_Posts::get_shortcode_tag())->toBe('rss_posts');
});

it('says there are no items when the feed fetch fails', function (): void {
    Functions\expect('fetch_feed')->once()->with('https://example.test/feed')->andReturn(new WP_Error_Stub);

    expect(RSS_Posts::callback(['feed' => 'https://example.test/feed', 'author' => '', 'customclass' => '']))
        ->toBe('<div class="recent-posts row grid ">No items</div>');
});

it('says there are no items when the feed is empty', function (): void {
    when('fetch_feed')->justReturn(rssFeed([]));

    expect(RSS_Posts::callback(['feed' => 'f', 'author' => '', 'customclass' => ''], ''))->toContain('>No items</div>');
});

it('renders each item as a grid card by default', function (): void {
    when('fetch_feed')->justReturn(rssFeed(rssSampleItems()));
    $arrow = file_get_contents(UAMS_SHORTCAKES_PATH.'assets/images/glyphicon/thin-arrow-right.svg');

    $html = RSS_Posts::callback(['feed' => 'f', 'author' => '', 'customclass' => '']);

    expect($html)
        ->toStartWith('<div class="recent-posts row grid ">')
        ->toContain('<a href="https://example.test/a" class="recent-post col-lg-4 col-md-4 col-sm-12 col-xs-12">')
        ->toContain('<p class="recent-post-meta">2026-01-02|M j, Y <span class="rss-posts-meta-sep" aria-hidden="true">|</span> <span class="rss-posts-cat">News</span></p>')
        ->toContain('<p class="recent-post-title">First</p><p class="recent-post-excerpt">Alpha <span class="right-arrow" aria-hidden="true">'.$arrow.'</span></p></a>')
        ->toContain('https://example.test/b')
        ->not->toContain('recent-post-byline');
});

it('renders full-width cards for the list display', function (): void {
    when('fetch_feed')->justReturn(rssFeed(rssSampleItems()));

    $html = RSS_Posts::callback(['feed' => 'f', 'display' => 'list', 'author' => '', 'customclass' => '']);

    expect($html)->toStartWith('<div class="recent-posts row list ">')
        ->toContain('class="recent-post col-md-12"')
        ->not->toContain('col-lg-4');
});

it('adds the author byline when author is truthy', function (): void {
    when('fetch_feed')->justReturn(rssFeed(rssSampleItems()));

    expect(RSS_Posts::callback(['feed' => 'f', 'author' => '1', 'customclass' => '']))
        ->toContain('<p class="recent-post-byline">By Ann</p>')
        ->toContain('<p class="recent-post-byline">By Bob</p>');
});

it('limits the items to the requested number and passes it to the feed', function (): void {
    $feed = rssFeed(rssSampleItems());
    when('fetch_feed')->justReturn($feed);

    $html = RSS_Posts::callback(['feed' => 'f', 'number' => '1', 'author' => '', 'customclass' => '']);

    expect($feed->requested)->toBe(1)
        ->and($html)->toContain('https://example.test/a')
        ->and($html)->not->toContain('https://example.test/b');
});

it('falls back to three items when number is not all digits', function (string $number): void {
    $feed = rssFeed(rssSampleItems());
    when('fetch_feed')->justReturn($feed);

    RSS_Posts::callback(['feed' => 'f', 'number' => $number, 'author' => '', 'customclass' => '']);

    expect($feed->requested)->toBe(3);
})->with(['abc', '-2', '2.5', '']);

it('sanitizes the display class but leaves the column class unset for an unknown display', function (): void {
    when('fetch_feed')->justReturn(rssFeed(rssSampleItems()));
    $messages = [];
    set_error_handler(function (int $no, string $str) use (&$messages): bool {
        $messages[] = $str;

        return true;
    });

    try {
        $html = RSS_Posts::callback(['feed' => 'f', 'display' => 'table', 'author' => '', 'customclass' => '']);
    } finally {
        restore_error_handler();
    }

    expect($html)->toStartWith('<div class="recent-posts row grid ">')
        ->toContain('class="recent-post "')
        ->and($messages)->toContain('Undefined variable $col');
});

it('sanitizes each comma-separated custom class', function (): void {
    when('fetch_feed')->justReturn(rssFeed([]));

    expect(RSS_Posts::callback(['feed' => 'f', 'author' => '', 'customclass' => 'one,t wo!']))
        ->toStartWith('<div class="recent-posts row grid one two">');
});

it('does not fetch when no feed is given', function (): void {
    Functions\expect('fetch_feed')->never();
    set_error_handler(fn (): bool => true);

    try {
        $html = RSS_Posts::callback([]);
    } finally {
        restore_error_handler();
    }

    expect($html)->toContain('>No items</div>');
});

it('does not fetch a feed that wp_http_validate_url rejects', function (): void {
    when('wp_http_validate_url')->justReturn(false);
    Functions\expect('fetch_feed')->never();

    expect(RSS_Posts::callback(['feed' => 'http://127.0.0.1/feed', 'author' => '', 'customclass' => '']))
        ->toBe('<div class="recent-posts row grid ">No items</div>');
});

it('fetches the url wp_http_validate_url returns', function (): void {
    when('wp_http_validate_url')->justReturn('https://validated.test/feed');
    Functions\expect('fetch_feed')->once()->with('https://validated.test/feed')->andReturn(rssFeed([]));

    RSS_Posts::callback(['feed' => 'https://example.test/feed', 'author' => '', 'customclass' => '']);
});

it('raises a warning when author is not given with items present', function (): void {
    when('fetch_feed')->justReturn(rssFeed(rssSampleItems()));
    $messages = [];
    set_error_handler(function (int $no, string $str) use (&$messages): bool {
        $messages[] = $str;

        return true;
    });

    try {
        RSS_Posts::callback(['feed' => 'f', 'customclass' => '']);
    } finally {
        restore_error_handler();
    }

    expect($messages)->toContain('Undefined array key "author"');
});

it('throws a TypeError when WordPress passes an empty string for attributes', function (): void {
    expect(fn (): string => RSS_Posts::callback('', ''))->toThrow(TypeError::class);
});

it('describes its Shortcake attributes', function (): void {
    $args = RSS_Posts::get_shortcode_ui_args();

    expect(array_column(RSS_Posts::get_attributes(), 'attr'))->toBe(['feed', 'number', 'author', 'display', 'customclass'])
        ->and($args['attrs'])->toBe(RSS_Posts::get_attributes())
        ->and($args['label'])->toBe('RSS Posts')
        ->and($args['listItemImage'])->toBe('dashicons-rss');
});
