<?php

/**
 * UAMS_Shortcakes, the loader: registration, dispatch and the admin helpers.
 *
 * WordPress is mocked with Brain Monkey; the object is built without its
 * constructor so no hooks fire on creation.
 *
 * @see inc/class-uams-shortcodes.php
 */

declare(strict_types=1);

use Brain\Monkey\Functions;
use UAMS_Shortcakes\Shortcodes\Alert;
use UAMS_Shortcakes\Shortcodes\Shortcode;

use function Brain\Monkey\Filters\expectApplied;
use function Brain\Monkey\Functions\when;

/**
 * A shortcode class that records what the loader asked of it.
 */
class FakeLoaderShortcode extends Shortcode
{
    public static array $calls = [];

    public static function setup_actions(): void
    {
        self::$calls[] = 'setup_actions';
    }

    public static function get_shortcode_ui_args(): array
    {
        return ['label' => 'Fake'];
    }

    public static function reversal($content): string
    {
        return str_replace('<fake/>', '[fakeloadershortcode]', $content);
    }

    public static function callback($attrs, $content = '', $shortcode_tag = null): string
    {
        return 'fake:'.json_encode($attrs).':'.$content.':'.$shortcode_tag;
    }
}

/**
 * A shortcode class with no Shortcake UI.
 */
class FakeNoUiLoaderShortcode extends FakeLoaderShortcode
{
    public static function get_shortcode_ui_args(): array
    {
        return [];
    }
}

/**
 * Set a private property on the loader.
 */
function setLoaderProperty(UAMS_Shortcakes $loader, string $property, mixed $value): void
{
    (new ReflectionProperty(UAMS_Shortcakes::class, $property))->setValue($loader, $value);
}

beforeEach(function (): void {
    FakeLoaderShortcode::$calls = [];
    // get_instance() would register hooks and enqueue assets, so build the
    // object without it. No shortcode class is registered on it.
    $this->shortcakes = (new ReflectionClass(UAMS_Shortcakes::class))->newInstanceWithoutConstructor();
});

it('passes content through the kses filter when no shortcode is registered', function (): void {
    $content = '<p>[button]Apply[/button]</p>';

    expect($this->shortcakes->filter_pre_kses($content))->toBe($content);
});

it('runs every registered class reversal over the content in order', function (): void {
    setLoaderProperty($this->shortcakes, 'registered_shortcode_classes', [FakeLoaderShortcode::class, Alert::class]);

    expect($this->shortcakes->filter_pre_kses('a <fake/> b'))->toBe('a [fakeloadershortcode] b');
});

it('renders nothing for an unregistered shortcode', function (): void {
    Functions\expect('wp_enqueue_script')->never();

    expect($this->shortcakes->do_shortcode_callback([], '', 'not-registered'))->toBe('');
});

it('enqueues the bakery script and hands a registered tag to its class', function (): void {
    define('UAMS_SHORTCAKES_VERSION', '9.9');
    setLoaderProperty($this->shortcakes, 'registered_shortcodes', ['fake' => FakeLoaderShortcode::class]);
    Functions\expect('wp_enqueue_script')
        ->once()
        ->with('shortcake-bakery', UAMS_SHORTCAKES_URL_ROOT.'assets/js/shortcake-bakery.js', ['jquery'], '9.9');

    expect($this->shortcakes->do_shortcode_callback(['a' => 1], 'inner', 'fake'))->toBe('fake:{"a":1}:inner:fake');
});

it('declares all three shortcode callback parameters as required', function (): void {
    // An optional parameter ahead of a required one raises E_DEPRECATED on PHP 8 when the file compiles.
    $parameters = (new ReflectionMethod(UAMS_Shortcakes::class, 'do_shortcode_callback'))->getParameters();

    expect(array_map(fn (ReflectionParameter $parameter): string => $parameter->getName(), $parameters))
        ->toBe(['attrs', 'content', 'shortcode_tag'])
        ->and(array_filter($parameters, fn (ReflectionParameter $parameter): bool => $parameter->isOptional()))
        ->toBeEmpty();
});

it('registers each class under its tag, runs its setup and its Shortcake UI', function (): void {
    stubWordPress();
    Functions\expect('add_shortcode')->once()->with('fakeloadershortcode', Mockery::type(Closure::class));
    Functions\expect('shortcode_ui_register_for_shortcode')->once()->with('fakeloadershortcode', ['label' => 'Fake']);
    setLoaderProperty($this->shortcakes, 'internal_shortcode_classes', [FakeLoaderShortcode::class]);

    $this->shortcakes->action_init_register_shortcodes();

    expect(FakeLoaderShortcode::$calls)->toBe(['setup_actions']);
});

it('registers no Shortcake UI for a class that has none', function (): void {
    stubWordPress();
    when('add_shortcode')->justReturn();
    Functions\expect('shortcode_ui_register_for_shortcode')->never();
    setLoaderProperty($this->shortcakes, 'internal_shortcode_classes', [FakeNoUiLoaderShortcode::class]);

    $this->shortcakes->action_init_register_shortcodes();

    expect(FakeLoaderShortcode::$calls)->toBe(['setup_actions']);
});

it('prints the admin dependencies only in the admin', function (): void {
    stubWordPress();
    when('includes_url')->alias(fn ($path): string => 'https://example.test/wp-includes/'.$path);

    when('is_admin')->justReturn(false);

    expect(UAMS_Shortcakes::get_shortcake_admin_dependencies())->toBeNull();

    when('is_admin')->justReturn(true);
    expect(UAMS_Shortcakes::get_shortcake_admin_dependencies())
        ->toBe('<script src="https://example.test/wp-includes/js/jquery/jquery.js"></script>'
            .'<script type="text/javascript" src="'.UAMS_SHORTCAKES_URL_ROOT.'assets/js/shortcake-bakery.js"></script>');
});

it('echoes the admin dependencies after a Shortcake preview renders', function (): void {
    stubWordPress();
    when('includes_url')->alias(fn ($path): string => 'https://example.test/wp-includes/'.$path);
    when('is_admin')->justReturn(true);

    $callbacks = [];
    when('add_action')->alias(function (string $hook, callable $callback) use (&$callbacks): void {
        $callbacks[$hook][] = $callback;
    });

    // get_instance() is a singleton another test creates first, so register this instance's actions directly.
    (new ReflectionMethod(UAMS_Shortcakes::class, 'setup_actions'))->invoke($this->shortcakes);
    expect($callbacks['shortcode_ui_after_do_shortcode'] ?? [])->toHaveCount(1);

    // Shortcake keeps what a callback echoes and drops what it returns.
    ob_start();
    $returned = $callbacks['shortcode_ui_after_do_shortcode'][0]('[button]');
    $printed = ob_get_clean();

    expect($returned)->toBeNull()
        ->and($printed)->toBe(UAMS_Shortcakes::get_shortcake_admin_dependencies());
});

it('enqueues the front-end style and script', function (): void {
    Functions\expect('wp_enqueue_script')->once()->with('shortcodes_scripts');
    Functions\expect('wp_enqueue_style')->once()->with('shortcodes_styles');

    $this->shortcakes->action_enqueue_scripts();
});

it('enqueues the stylesheet on request', function (): void {
    Functions\expect('wp_enqueue_style')->once()->with('shortcodes_styles');

    $this->shortcakes->uams_shortcodes_add_styles();
});

it('adds the embed media frame and localizes the tag map when embeds are shown', function (): void {
    stubWordPress();
    when('plugin_dir_url')->justReturn('https://example.test/plugin/');
    when('wp_create_nonce')->justReturn('nonce-1');
    Functions\expect('wp_enqueue_script')
        ->once()
        ->with('shortcake-bakery-add-embed-media-frame', 'https://example.test/plugin/assets/js/build/shortcake-bakery-add-embed-media-frame.js', ['media-views', 'shortcode-ui']);
    Functions\expect('wp_localize_script')
        ->once()
        ->withArgs(fn (string $handle, string $name, array $strings): bool => $handle === 'shortcake-bakery-add-embed-media-frame'
            && $name === 'ShortcakeBakery'
            && $strings['nonces'] === ['customEmbedReverse' => 'nonce-1']
            && $strings['shortcodes'] === ['FakeLoaderShortcode' => 'fake']
            && $strings['text'] === [
                'addEmbed' => 'Insert Embed Code',
                'insertButton' => 'Insert embed',
                'customEmbedLabel' => 'Paste any custom embed code here. If it matches a known post element, that post element will be used rather than the embed code.',
                'noReversalMatches' => "The embed code provided doesn't match any known post elements. This means that it may not display as expected.",
            ]);
    setLoaderProperty($this->shortcakes, 'registered_shortcodes', ['fake' => 'FakeLoaderShortcode']);

    $this->shortcakes->action_admin_enqueue_scripts();
});

it('adds nothing when the filter hides the embed button', function (): void {
    expectApplied('shortcake_bakery_show_add_embed')->once()->andReturn(false);
    Functions\expect('wp_enqueue_script')->never();
    Functions\expect('wp_localize_script')->never();

    $this->shortcakes->action_admin_enqueue_scripts();
});

it('reverses embed code into shortcodes when a reversal changed the text', function (): void {
    $regex = '\[(\[?)(fakeloadershortcode)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
    when('get_shortcode_regex')->justReturn($regex);
    Functions\expect('shortcode_parse_atts')->once()->with(' id=5')->andReturn(['id' => '5']);
    expectApplied('pre_kses')->once()->andReturn('[fakeloadershortcode id=5]x[/fakeloadershortcode]');

    $result = $this->shortcakes->reverse_embed('<fake/>');

    expect($result['success'])->toBeTrue()
        ->and($result['reversal'])->toBe('[fakeloadershortcode id=5]x[/fakeloadershortcode]')
        ->and($result['shortcodes'])->toHaveCount(1)
        ->and($result['shortcodes'][0])->toMatchArray([
            'shortcode' => 'fakeloadershortcode',
            'attributes' => ['id' => '5'],
            'inner_content' => 'x',
            'raw' => '[fakeloadershortcode id=5]x[/fakeloadershortcode]',
        ]);
});

it('reports no success when the filter leaves the embed code unchanged', function (): void {
    expectApplied('pre_kses')->once()->andReturnUsing(fn ($content) => $content);

    expect($this->shortcakes->reverse_embed('<iframe></iframe>'))
        ->toBe(['success' => false, 'reversal' => '<iframe></iframe>', 'shortcodes' => []]);
});

it('autoloads nothing for a class outside the shortcode namespace', function (): void {
    expect($this->shortcakes->autoload_shortcode_classes('Other\\Thing'))->toBeNull()
        ->and(class_exists('Other\\Thing', false))->toBeFalse();
});

it('loads nothing for a shortcode class that has no file', function (): void {
    expect($this->shortcakes->autoload_shortcode_classes('\\UAMS_Shortcakes\\Shortcodes\\Missing_One'))->toBeNull()
        ->and(class_exists('UAMS_Shortcakes\\Shortcodes\\Missing_One', false))->toBeFalse();
});

it('loads a class in a sub-directory from a lower-case folder and a hyphenated file name', function (): void {
    $folder = dirname(__DIR__, 2).'/inc/shortcodes/nestedprobe';
    $file = $folder.'/class-probe-thing.php';
    mkdir($folder);

    try {
        file_put_contents($file, "<?php\nnamespace UAMS_Shortcakes\\Shortcodes\\NestedProbe;\nclass Probe_Thing {}\n");

        $this->shortcakes->autoload_shortcode_classes('UAMS_Shortcakes\\Shortcodes\\NestedProbe\\Probe_Thing');

        expect(class_exists('UAMS_Shortcakes\\Shortcodes\\NestedProbe\\Probe_Thing', false))->toBeTrue();
    } finally {
        if (is_file($file)) {
            unlink($file);
        }

        rmdir($folder);
    }
});

it('registers its hooks and assets when the instance is first created', function (): void {
    $hooks = [];
    when('add_action')->alias(function (string $hook) use (&$hooks): void {
        $hooks[] = $hook;
    });
    when('add_filter')->alias(function (string $hook) use (&$hooks): void {
        $hooks[] = $hook;
    });
    when('plugin_dir_url')->justReturn('https://example.test/plugin/');
    Functions\expect('wp_register_style')->once()->with('shortcodes_styles', 'https://example.test/plugin/assets/css/main.css');
    Functions\expect('wp_register_script')->once()->with('shortcodes_scripts', false);

    $first = UAMS_Shortcakes::get_instance();

    expect(UAMS_Shortcakes::get_instance())->toBe($first)
        ->and($hooks)->toContain('init', 'pre_kses', 'widget_text', 'wp_ajax_shortcake_bakery_embed_reverse');
});

it('registers every action the loader listens to, and its class autoloader', function (): void {
    $hooks = [];
    when('add_action')->alias(function (string $hook) use (&$hooks): void {
        $hooks[] = $hook;
    });
    $before = spl_autoload_functions();

    (new ReflectionMethod(UAMS_Shortcakes::class, 'setup_actions'))->invoke($this->shortcakes);
    $registered = array_values(array_filter(
        spl_autoload_functions(),
        fn (callable $loader): bool => $loader instanceof Closure && (new ReflectionFunction($loader))->getClosureThis() === $this->shortcakes
    ));
    foreach ($registered as $loader) {
        spl_autoload_unregister($loader);
    }

    expect($hooks)->toBe([
        'init',
        'shortcode_ui_after_do_shortcode',
        'shortcode_ui_loaded_editor',
        'wp_ajax_shortcake_bakery_embed_reverse',
        'enqueue_shortcode_ui',
        'wp_enqueue_scripts',
    ])
        ->and($registered)->toHaveCount(1)
        ->and(spl_autoload_functions())->toBe($before);
});
