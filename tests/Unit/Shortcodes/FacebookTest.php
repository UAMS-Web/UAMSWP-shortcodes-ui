<?php

/**
 * Facebook shortcode: URL validation, embed markup, reversal and hooks.
 *
 * @see inc/shortcodes/class-facebook.php
 */

declare(strict_types=1);

use Brain\Monkey\Functions;
use UAMS_Shortcakes\Shortcodes\Facebook;

use function Brain\Monkey\Actions\expectAdded;
use function Brain\Monkey\Functions\stubs;
use function Brain\Monkey\Functions\when;

beforeEach(function (): void {
    stubWordPress();
    stubs([
        'esc_url' => fn ($url) => $url,
    ]);
});

it('uses the lowercase class name as its shortcode tag', function (): void {
    expect(Facebook::get_shortcode_tag())->toBe('facebook');
});

it('describes a single url attribute for Shortcake', function (): void {
    $args = Facebook::get_shortcode_ui_args();

    expect($args['label'])->toBe('Facebook')
        ->and($args['listItemImage'])->toBe('dashicons-facebook')
        ->and(array_column($args['attrs'], 'attr'))->toBe(['url'])
        ->and($args['attrs'][0]['type'])->toBe('text');
});

it('returns an empty string when the url is missing or empty', function (): void {
    expect(Facebook::callback([]))->toBe('')
        ->and(Facebook::callback(['url' => '']))->toBe('');
});

it('renders the responsive embed for a valid post url and enqueues the SDK', function (): void {
    Functions\expect('wp_enqueue_script')->once()->with('facebook-api', '//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.0', [], false, true);
    expectAdded('wp_footer')->once();

    $html = Facebook::callback(['url' => 'https://www.facebook.com/UAMS/posts/12345']);

    expect($html)->toBe('<div class="fb-post uams-shortcodes-responsive" data-href="https://www.facebook.com/UAMS/posts/12345" data-width="350px" data-true-height="550px" data-true-width="350px"><div class="fb-xfbml-parse-ignore"></div></div>');
});

it('does not register the footer action twice', function (): void {
    when('wp_enqueue_script')->justReturn();
    when('has_action')->justReturn(true);

    expectAdded('wp_footer')->never();

    expect(Facebook::callback(['url' => 'https://www.facebook.com/UAMS/posts/1']))->toContain('fb-post');
});

it('undoes the &amp; that kses adds to the url', function (): void {
    when('wp_enqueue_script')->justReturn();
    when('has_action')->justReturn(true);

    $html = Facebook::callback(['url' => 'https://www.facebook.com/permalink.php?story_fbid=11&amp;id=22']);

    expect($html)->toContain('data-href="https://www.facebook.com/permalink.php?story_fbid=11&id=22"');
});

it('accepts each supported url pattern', function (string $url): void {
    when('wp_enqueue_script')->justReturn();
    when('has_action')->justReturn(true);

    expect(Facebook::callback(['url' => $url]))->toContain('class="fb-post');
})->with([
    'post' => 'https://www.facebook.com/page/posts/123',
    'video.php' => 'https://www.facebook.com/video.php?v=123',
    'videos with id' => 'https://www.facebook.com/page/videos/123/',
    'permalink' => 'https://www.facebook.com/permalink.php?story_fbid=1&id=2',
    'photos' => 'https://www.facebook.com/page/photos/a.1/2/',
    'videos named' => 'https://www.facebook.com/page/videos/vb.1/2/',
    'group permalink' => 'https://www.facebook.com/groups/12/permalink/34/',
]);

it('shows editors an error for an invalid url', function (): void {
    when('current_user_can')->justReturn(true);
    Functions\expect('wp_enqueue_script')->never();

    expect(Facebook::callback(['url' => 'https://example.com/nope']))
        ->toBe('<div class="uams-shortcodes-error"><p>Invalid Facebook URL: https://example.com/nope</p></div>');
});

it('shows visitors nothing for an invalid url', function (): void {
    when('current_user_can')->justReturn(false);

    expect(Facebook::callback(['url' => 'https://example.com/nope']))->toBe('');
});

it('prints the responsive footer script', function (): void {
    ob_start();
    Facebook::action_wp_footer();
    $out = ob_get_clean();

    expect($out)->toContain('<div id="fb-root"></div>')
        ->toContain('uams-shortcodes-responsive-resize')
        ->toContain('FB.XFBML.parse()');
});

it('prints the SDK script after a shortcode render that contains the tag', function (): void {
    $callback = null;
    expectAdded('shortcode_ui_after_do_shortcode')->once()->whenHappen(function ($cb) use (&$callback): void {
        $callback = $cb;
    });

    Facebook::setup_actions();

    ob_start();
    $callback('[facebook url="x"]');
    $hit = ob_get_clean();
    ob_start();
    $callback('[alert]');
    $miss = ob_get_clean();

    expect($hit)->toBe('<script src="//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.0"></script>')
        ->and($miss)->toBe('');
});

describe('reversal', function (): void {
    it('returns content without embeds unchanged', function (): void {
        expect(Facebook::reversal('<p>Hello</p>'))->toBe('<p>Hello</p>');
    });

    it('turns a script-based post embed into a shortcode', function (): void {
        $embed = '<div id="fb-root"></div><script>var a = 1;</script><div class="fb-post" data-href="https://www.facebook.com/u/posts/12" data-width="500"><div class="fb-xfbml-parse-ignore"></div></div>';

        expect(Facebook::reversal('Before '.$embed.' after'))
            ->toBe('Before [facebook url="https://www.facebook.com/u/posts/12"] after');
    });

    it('turns a script-based video embed into a shortcode with the host added', function (): void {
        $embed = '<div id="fb-root"></div><script>var a = 1;</script><div class="fb-video" data-width="500" data-href="/u/videos/1/"><div class="fb-xfbml-parse-ignore"><blockquote cite="/u/videos/1/"><a href="/u/videos/1/">T</a></blockquote></div></div>';

        expect(Facebook::reversal($embed))->toBe('[facebook url="https://www.facebook.com/u/videos/1/"]');
    });

    it('leaves a video embed alone when data-href is not the last attribute', function (): void {
        $embed = '<div id="fb-root"></div><script>var a = 1;</script><div class="fb-video" data-href="/u/videos/1/" data-width="500"><div class="fb-xfbml-parse-ignore"><blockquote cite="/u/videos/1/"><a href="/u/videos/1/">T</a></blockquote></div></div>';

        expect(Facebook::reversal($embed))->toBe($embed);
    });

    it('turns an iframe embed into a shortcode using the decoded href', function (): void {
        $iframe = '<iframe src="https://www.facebook.com/plugins/post.php?href=https%3A%2F%2Fwww.facebook.com%2Fu%2Fposts%2F12&width=500" width="500"></iframe>';

        expect(Facebook::reversal('<p>'.$iframe.'</p>'))
            ->toBe('<p>[facebook url="https://www.facebook.com/u/posts/12"]</p>');
    });

    it('leaves iframes from other hosts alone', function (): void {
        $iframe = '<iframe src="https://example.com/embed" width="500"></iframe>';

        expect(Facebook::reversal($iframe))->toBe($iframe);
    });

    it('leaves a facebook iframe whose href points elsewhere alone', function (): void {
        $iframe = '<iframe src="https://www.facebook.com/plugins/post.php?href=https%3A%2F%2Fexample.com%2Fx" width="500"></iframe>';

        expect(Facebook::reversal($iframe))->toBe($iframe);
    });
});
