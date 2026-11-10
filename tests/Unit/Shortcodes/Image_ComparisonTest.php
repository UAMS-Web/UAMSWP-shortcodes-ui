<?php

/**
 * Image_Comparison shortcode: validation, slider positions, markup and hooks.
 *
 * @see inc/shortcodes/class-image-comparison.php
 */

declare(strict_types=1);

use Brain\Monkey\Functions;
use UAMS_Shortcakes\Shortcodes\Image_Comparison;

use function Brain\Monkey\Actions\expectAdded;
use function Brain\Monkey\Functions\stubs;
use function Brain\Monkey\Functions\when;

beforeEach(function (): void {
    stubWordPress();
    stubs([
        'esc_url' => fn ($url) => $url,
        'current_user_can' => false,
        'wp_get_attachment_image_src' => fn ($id): array => ['https://img.test/'.$id.'.jpg', 100, 100],
        'get_post_field' => fn ($field, $id): string => 'Caption '.$id,
        'wp_get_attachment_metadata' => fn ($id): array => ['image_meta' => ['credit' => 'Credit '.$id]],
    ]);
    $this->enqueued = [];
    when('wp_enqueue_script')->alias(function (string $handle): void {
        $this->enqueued[] = 'script:'.$handle;
    });
    when('wp_enqueue_style')->alias(function (string $handle): void {
        $this->enqueued[] = 'style:'.$handle;
    });
});

it('uses the lowercase class name as its shortcode tag', function (): void {
    expect(Image_Comparison::get_shortcode_tag())->toBe('image_comparison');
});

it('renders nothing for visitors when an image is missing', function (): void {
    expect(Image_Comparison::callback(['left' => '1']))->toBe('')
        ->and(Image_Comparison::callback(['right' => '2']))->toBe('')
        ->and(Image_Comparison::callback([]))->toBe('');
});

it('shows an error box to editors when an image is missing', function (): void {
    when('current_user_can')->justReturn(true);

    expect(Image_Comparison::callback(['left' => '1']))
        ->toBe('<div class="uams-shortcodes-error"><p>Two images required for image comparison.</p></div>');
});

it('renders both images centered by default and enqueues the assets', function (): void {
    $html = Image_Comparison::callback(['left' => '1', 'right' => '2']);

    expect($this->enqueued)->toBe(['script:juxtapose-js', 'style:juxtapose-css'])
        ->and($html)->toBe(
            '<section class="image-comparison"><div class="juxtapose" data-startingposition="50" data-showlabels="true" data-showcredits="true" data-animate="true">'
        .'<img src="https://img.test/1.jpg" data-label="Caption 1" data-credit="Credit 1">'
        .'<img src="https://img.test/2.jpg" data-label="Caption 2" data-credit="Credit 2">'
        .'</div></section>'
        );
});

it('maps each named position to a percentage', function (string $position, string $percent): void {
    expect(Image_Comparison::callback(['left' => '1', 'right' => '2', 'position' => $position]))
        ->toContain('data-startingposition="'.$percent.'"');
})->with([
    ['center', '50'],
    ['mostlyleft', '10'],
    ['mostlyright', '90'],
    ['33', '33'],
    ['bogus', 'bogus'],
]);

it('returns null when an attachment has no image source', function (): void {
    when('wp_get_attachment_image_src')->alias(fn ($id): array|false => $id === '2' ? false : ['https://img.test/1.jpg']);

    expect(Image_Comparison::callback(['left' => '1', 'right' => '2']))->toBeNull();
});

it('does not enqueue the assets when an attachment has no image source', function (): void {
    when('wp_get_attachment_image_src')->justReturn(false);

    Image_Comparison::callback(['left' => '1', 'right' => '2']);

    expect($this->enqueued)->toBe([]);
});

it('raises a warning when attachment metadata has no image_meta credit (current behavior)', function (): void {
    when('wp_get_attachment_metadata')->justReturn(false);
    $errors = [];
    set_error_handler(function (int $no, string $str) use (&$errors): bool {
        $errors[] = $str;

        return true;
    });

    try {
        Image_Comparison::callback(['left' => '1', 'right' => '2']);
    } finally {
        restore_error_handler();
    }

    expect($errors)->not->toBeEmpty();
});

it('registers its scripts and styles', function (): void {
    Functions\expect('wp_register_script')->once()->with('juxtapose-js', UAMS_SHORTCAKES_URL_ROOT.'assets/js/juxtapose.js', ['jquery']);
    Functions\expect('wp_register_style')->once()->with('juxtapose-css', UAMS_SHORTCAKES_URL_ROOT.'assets/css/juxtapose.css');

    Image_Comparison::action_init_register_scripts();
});

it('hooks registration and the editor preview assets', function (): void {
    expectAdded('wp_enqueue_scripts')->once()->with(Image_Comparison::class.'::action_init_register_scripts');
    expectAdded('shortcode_ui_after_do_shortcode')->once();

    Image_Comparison::setup_actions();
});

it('prints the preview assets only for its own shortcode', function (): void {
    $callback = null;
    expectAdded('shortcode_ui_after_do_shortcode')->whenHappen(function ($added) use (&$callback): void {
        $callback = $added;
    });
    Image_Comparison::setup_actions();

    ob_start();
    $callback('[image_comparison left="1" right="2"]');
    $with = ob_get_clean();

    ob_start();
    $callback('[alert]');
    $without = ob_get_clean();

    expect($with)
        ->toContain('<link rel="stylesheet" href="'.UAMS_SHORTCAKES_URL_ROOT.'assets/css/juxtapose.css">')
        ->toContain('<script type="text/javascript" src="'.UAMS_SHORTCAKES_URL_ROOT.'assets/js/juxtapose.js"></script>')
        ->and($without)->toBe('');
});

it('describes its Shortcake attributes', function (): void {
    $args = Image_Comparison::get_shortcode_ui_args();

    expect($args['label'])->toBe('Image Comparison')
        ->and(array_column($args['attrs'], 'attr'))->toBe(['left', 'right', 'position'])
        ->and(array_keys($args['attrs'][2]['options']))->toBe(['center', 'mostlyleft', 'mostlyright']);
});

it('asks for the large size of each image, not the icon', function (): void {
    $calls = [];
    when('wp_get_attachment_image_src')->alias(function (...$args) use (&$calls): array {
        $calls[] = $args;

        return ['https://img.test/'.$args[0].'.jpg', 100, 100];
    });

    Image_Comparison::callback(['left' => '1', 'right' => '2']);

    expect($calls)->toBe([['1', 'large', false], ['2', 'large', false]]);
});
