<?php

/**
 * Callout shortcode: the markup `callback()` returns for each type and the Shortcake arguments.
 *
 * The tests collect the PHP errors a render raises through `calloutRender()` and
 * assert that an ordinary render raises none.
 *
 * @see inc/shortcodes/class-callout.php
 */

declare(strict_types=1);

use UAMS_Shortcakes\Shortcodes\Callout;

use function Brain\Monkey\Functions\stubs;
use function Brain\Monkey\Functions\when;

/**
 * Render a callout while collecting the PHP errors it raises.
 *
 * @return array{0: string, 1: list<string>}
 */
function calloutRender(mixed $attrs = [], string $content = 'Body'): array
{
    $errors = [];
    set_error_handler(function (int $no, string $str) use (&$errors): bool {
        $errors[] = $str;

        return true;
    });

    try {
        $html = Callout::callback($attrs, $content);
    } finally {
        restore_error_handler();
    }

    return [$html, $errors];
}

/**
 * Drop the deprecation the Brain Monkey esc_attr stub raises for a null
 * argument, leaving only unexpected errors.
 *
 * @param  list<string>  $errors
 * @return list<string>
 */
function calloutUnexpected(array $errors): array
{
    return array_values(array_diff($errors, [
        'htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated',
    ]));
}

beforeEach(function (): void {
    stubWordPress();
    stubs([
        'wp_get_attachment_image_src' => fn ($id, $size = 'thumbnail'): array => ['https://img.test/'.$id.'-'.$size.'.jpg', 10, 10],
        'wp_get_attachment_image_srcset' => fn ($id, $size = 'medium'): string => 'set-'.$id.'-'.$size,
        'get_post_meta' => fn ($id, $key, $single): string => $single ? 'Alt for '.$id : 'List for '.$id,
        'get_post_field' => fn ($field, $id): string => 'Caption for '.$id,
    ]);
});

it('uses the lowercase class name as its shortcode tag', function (): void {
    expect(Callout::get_shortcode_tag())->toBe('callout');
});

it('describes its Shortcake arguments', function (): void {
    $args = Callout::get_shortcode_ui_args();

    expect($args['label'])->toBe('Callout')
        ->and($args['listItemImage'])->toBe('<img width="100px" height="100px" src="https://example.test/wp-content/plugins/uamswp-shortcodes/admin/images/icon-callout.png" />')
        ->and($args['inner_content']['label'])->toBe('Callout Body')
        ->and($args['inner_content']['description'])->toBe('Main content of your callout. Text and basic HTML tags can be added here.')
        ->and($args['attrs'])->toBe(Callout::get_attributes());
});

it('lists its attributes in editor order', function (): void {
    $attrs = array_column(Callout::get_attributes(), 'attr');

    expect($attrs)->toBe([
        'heading', 'headingtype', 'headingicon', 'textalign', 'url', 'target', 'type', 'bgcolor', 'img',
        'imgcaption', 'imgoverlay', 'textbgcolor', 'mediaposition', 'imgback', 'textposition', 'textwidth',
        'margin', 'padding', 'fullscreen', 'customclass',
    ]);
});

it('gives every attribute a label and a type', function (): void {
    foreach (Callout::get_attributes() as $attribute) {
        expect($attribute)->toHaveKeys(['label', 'attr', 'type']);
    }
});

it('offers the brand colors and the icon collection as option lists', function (): void {
    $byAttr = array_column(Callout::get_attributes(), null, 'attr');

    expect($byAttr['bgcolor']['options'])->toBe(Callout::get_uams_brand_colors())
        ->and($byAttr['textbgcolor']['options'])->toBe(Callout::get_uams_brand_colors_plus())
        ->and($byAttr['headingicon']['options'])->toBe(Callout::get_icon_collection())
        ->and(array_keys($byAttr['headingtype']['options']))->toBe(['p', 'h3', 'h2', 'h1'])
        ->and(array_keys($byAttr['type']['options']))->toContain('basic', 'img', 'bgimg');
});

it('renders the default basic callout', function (): void {
    [$html, $errors] = calloutRender();

    expect($html)->toBe('<div class="uams-callout uamsred text-center normal "><div class="inner-container"><div></div><p class=""><p>Body</p></p></div></div><!-- .uams-callout -->')
        ->and($errors)->toBe([]);
});

it('falls back to the defaults when the attributes are not an array', function (): void {
    [$html] = calloutRender('');

    expect($html)->toContain('class="uams-callout uamsred text-center normal ');
});

it('falls back to the basic markup for an unknown type', function (): void {
    [$html] = calloutRender(['type' => 'nonsense', 'fullscreen' => false]);

    expect($html)->toBe('<div class="uams-callout uamsred text-center normal "><div class="inner-container"><div></div><p class=""><p>Body</p></p></div></div>');
});

it('applies the background color, alignment and margin classes', function (): void {
    [$html] = calloutRender(['bgcolor' => 'maroon', 'textalign' => 'text-left', 'margin' => 'large', 'fullscreen' => false]);

    expect($html)->toContain('<div class="uams-callout maroon text-left large ');
});

it('passes the content through do_shortcode and wpautop', function (): void {
    when('do_shortcode')->alias(fn (string $c): string => strtoupper($c));

    [$html] = calloutRender(['fullscreen' => false], 'inner');

    expect($html)->toContain('<p>INNER</p>');
});

it('renders an empty content as an empty paragraph', function (): void {
    [$html] = calloutRender(['fullscreen' => false], '');

    expect($html)->toContain('<p class=""><p></p></p>');
});

it('renders the heading in the chosen tag', function (): void {
    [$html] = calloutRender(['heading' => 'Hello', 'headingtype' => 'h2', 'fullscreen' => false]);

    expect($html)->toContain('<div><h2 class="callout-heading">Hello</h2></div>');
});

it('defaults the heading tag to p', function (): void {
    [$html] = calloutRender(['heading' => 'Hello', 'fullscreen' => false]);

    expect($html)->toContain('<p class="callout-heading">Hello</p>');
});

it('passes the heading through wp_kses_post', function (): void {
    when('wp_kses_post')->alias(fn ($content): string => strip_tags((string) $content));

    [$html] = calloutRender(['heading' => '<em>Hi</em> & bye<script>x</script>', 'fullscreen' => false]);

    expect($html)->toContain('<p class="callout-heading">Hi & byex</p>')
        ->and($html)->not->toContain('<script>');
});

it('falls back to p for a heading tag that is not h1 to h6', function (string $tag): void {
    [$html] = calloutRender(['heading' => 'Hello', 'headingtype' => $tag, 'fullscreen' => false]);

    expect($html)->toContain('<p class="callout-heading">Hello</p>');
})->with(['div', 'script', 'h7', 'h2 onclick="x"', '']);

it('keeps each allowed heading tag', function (string $tag): void {
    [$html] = calloutRender(['heading' => 'Hello', 'headingtype' => $tag, 'fullscreen' => false]);

    expect($html)->toContain('<'.$tag.' class="callout-heading">Hello</'.$tag.'>');
})->with(['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p']);

it('prefixes the heading with a heading icon span', function (): void {
    [$html] = calloutRender(['heading' => 'Hello', 'headingicon' => 'ic-book', 'fullscreen' => false]);

    expect($html)->toContain('<p class="callout-heading"><span class="ic-book" aria-hidden="true"></span>Hello</p>');
});

it('escapes the heading icon class', function (): void {
    [$html] = calloutRender(['heading' => 'Hello', 'headingicon' => 'ic-book" onclick="x', 'fullscreen' => false]);

    expect($html)->toContain('<span class="ic-book&quot; onclick=&quot;x" aria-hidden="true">')
        ->and($html)->not->toContain('ic-book" onclick');
});

it('escapes the class list built from the color, alignment and margin attributes', function (): void {
    [$html] = calloutRender(['bgcolor' => 'maroon" onclick="x', 'fullscreen' => false]);

    expect($html)->toContain('<div class="uams-callout maroon&quot; onclick=&quot;x text-center normal ')
        ->and($html)->not->toContain('maroon" onclick');
});

it('skips the icon for an ignored icon name', function (string $icon): void {
    [$html] = calloutRender(['heading' => 'Hello', 'headingicon' => $icon, 'fullscreen' => false]);

    expect($html)->toContain('<p class="callout-heading">Hello</p>');
})->with(['noicon', 'misc', 'numbers']);

it('renders no icon when there is an icon but no heading', function (): void {
    [$html] = calloutRender(['headingicon' => 'ic-book', 'fullscreen' => false]);

    expect($html)->not->toContain('ic-book');
});

it('wraps the heading in a link when a url is set', function (): void {
    [$html] = calloutRender(['heading' => 'Hello', 'url' => 'https://example.com/a', 'fullscreen' => false]);

    expect($html)->toContain('<a href="https://example.com/a" ><p class="callout-heading">Hello</p></a>')
        ->and($html)->not->toContain('target=');
});

it('opens the link in a new tab only when target is exactly true', function (): void {
    [$strict] = calloutRender(['url' => 'https://example.com/a', 'target' => true, 'fullscreen' => false]);
    [$loose] = calloutRender(['url' => 'https://example.com/a', 'target' => '1', 'fullscreen' => false]);

    expect($strict)->toContain('<a href="https://example.com/a" target="_blank">')
        ->and($loose)->not->toContain('target="_blank"');
});

it('passes the link url through esc_url', function (): void {
    when('esc_url')->alias(fn (string $url): string => 'ESC['.$url.']');

    [$html] = calloutRender(['url' => 'https://example.com/a', 'fullscreen' => false]);

    expect($html)->toContain('<a href="ESC[https://example.com/a]" >');
});

it('adds only the sanitized custom classes to the basic callout', function (): void {
    [$html, $errors] = calloutRender(['customclass' => 'one,t wo!', 'fullscreen' => false]);

    expect($html)->toStartWith('<div class="uams-callout uamsred text-center normal one two">')
        ->and($html)->not->toContain('wo!')
        ->and($errors)->toBe([]);
});

it('adds the full-width class when fullscreen is set', function (): void {
    [$html] = calloutRender(['customclass' => 'one', 'fullscreen' => true]);

    expect($html)->toStartWith('<div class="uams-callout uamsred text-center normal one uams-full-width">');
});

it('adds the full-width class without custom classes and without a warning', function (): void {
    [$html, $errors] = calloutRender(['fullscreen' => true]);

    expect($html)->toStartWith('<div class="uams-callout uamsred text-center normal  uams-full-width">')
        ->and($errors)->toBe([]);
});

it('renders the image callout with the media position, srcset, alt and caption', function (): void {
    [$html, $errors] = calloutRender([
        'type' => 'img',
        'img' => 7,
        'imgcaption' => true,
        'mediaposition' => 'left',
        'imgback' => 'back',
        'heading' => 'Pic',
        'padding' => 'padding-two',
        'customclass' => 'extra',
        'fullscreen' => false,
    ]);

    expect($html)
        ->toContain('callout-media callout-media-img media-left extra">')
        ->toContain('<div class="callout-img media-left bg-img">')
        ->toContain('<img class="img-responsive" src="https://img.test/7-large.jpg" srcset="set-7-large" sizes="(min-width: 36em) 33.3vw, 100vw" alt="Alt for 7" />')
        ->toContain('<div class="callout-img-caption">Caption for 7</div></div>')
        ->toContain('<div class="callout-content media-left padding-two">')
        ->toContain('<p class="callout-heading">Pic</p>')
        ->toEndWith('<!-- .uams-callout -->')
        ->and(calloutUnexpected($errors))->toBe([]);
});

it('maps each media position to its class', function (string $position, string $class): void {
    [$html] = calloutRender(['type' => 'img', 'img' => 1, 'mediaposition' => $position, 'fullscreen' => false]);

    expect($html)->toContain('<div class="callout-img '.$class);
})->with([
    'left' => ['left', 'media-left'],
    'right' => ['right', 'media-right'],
    'top' => ['top', 'media-top'],
    'bottom' => ['bottom', 'media-bottom'],
]);

it('renders the image callout without an image or caption by default', function (): void {
    [$html, $errors] = calloutRender(['type' => 'img', 'mediaposition' => 'top', 'fullscreen' => false]);

    expect($html)
        ->toContain('<img class="img-responsive" src="" srcset="" sizes="(min-width: 36em) 33.3vw, 100vw" alt="" />')
        ->not->toContain('callout-img-caption')
        ->and(calloutUnexpected($errors))->toBe([]);
});

it('falls back to the padding-one class when the image callout padding is emptied', function (): void {
    [$html] = calloutRender(['type' => 'img', 'mediaposition' => 'top', 'padding' => '', 'fullscreen' => false]);

    expect($html)->toContain('<div class="callout-content media-top padding-one">');
});

it('renders the image callout without a media position class and without a warning when none is chosen', function (): void {
    [$html, $errors] = calloutRender(['type' => 'img', 'fullscreen' => false]);

    expect($html)->toContain('callout-media callout-media-img  ')
        ->and($errors)->toBe([]);
});

it('renders a YouTube embed for the video callout from a watch url', function (): void {
    [$html, $errors] = calloutRender([
        'type' => 'vid',
        'vidtype' => 'youtube',
        'youtube' => 'https://www.youtube.com/watch?v=abc123&t=5',
        'mediaposition' => 'left',
        'fullscreen' => false,
    ]);

    expect($html)
        ->toContain('callout-media callout-video ')
        ->toContain('<iframe class="embed-responsive-item media-left" src="http://www.youtube.com/embed/abc123?modestbranding=0&autohide=1&showinfo=0"></iframe>')
        ->toContain('<div class="callout-content media-left">')
        ->and(calloutUnexpected($errors))->toBe([]);
});

it('renders a YouTube embed from a youtu.be url', function (): void {
    [$html] = calloutRender([
        'type' => 'vid',
        'vidtype' => 'youtube',
        'youtube' => 'https://youtu.be/xyz789',
        'mediaposition' => 'top',
        'fullscreen' => false,
    ]);

    expect($html)->toContain('<iframe class="embed-responsive-item media-top" src="http://www.youtube.com/embed/xyz789?modestbranding=0&autohide=1&showinfo=0"></iframe>');
});

it('renders no video for a YouTube url it cannot read', function (): void {
    [$html] = calloutRender([
        'type' => 'vid',
        'vidtype' => 'youtube',
        'youtube' => 'https://example.com/nothing',
        'mediaposition' => 'top',
        'fullscreen' => false,
    ]);

    expect($html)->not->toContain('<iframe');
});

it('renders a Vimeo embed for the video callout', function (): void {
    [$html] = calloutRender([
        'type' => 'vid',
        'vidtype' => 'vimeo',
        'vimeo' => 'https://vimeo.com/12345',
        'mediaposition' => 'right',
        'fullscreen' => false,
    ]);

    expect($html)->toContain('<iframe class="embed-responsive-item media-right" src="http://player.vimeo.com/video/12345?badge=0&byline=0&portrait=0&title=0"></iframe>');
});

it('renders no Vimeo embed when the url has no numeric id', function (): void {
    [$html] = calloutRender([
        'type' => 'vid',
        'vidtype' => 'vimeo',
        'vimeo' => 'https://vimeo.com/channels/staffpicks',
        'mediaposition' => 'right',
        'fullscreen' => false,
    ]);

    expect($html)->not->toContain('<iframe');
});

it('renders a self-hosted video with a poster, fallback image and autoplay', function (): void {
    [$html, $errors] = calloutRender([
        'type' => 'vid',
        'vidtype' => 'other',
        'vidsource' => 'https://cdn.test/clip.mp4',
        'fallbackimg' => 9,
        'autoplay' => true,
        'mediaposition' => 'left',
        'fullscreen' => false,
    ]);

    expect($html)
        ->toContain('<video class="callout-media-element" poster="https://img.test/9-full.jpg" controls autoplay loop muted>')
        ->toContain('<source src="https://cdn.test/clip.mp4" type="video/mp4">')
        ->toContain('<img src="https://img.test/9-thumbnail.jpg" srcset="set-9-medium" alt="Alt for 9" />')
        ->and(calloutUnexpected($errors))->toBe([]);
});

it('renders a self-hosted video without autoplay and without a warning', function (): void {
    [$html, $errors] = calloutRender([
        'type' => 'vid',
        'vidtype' => 'other',
        'vidsource' => 'https://cdn.test/clip.mp4',
        'fallbackimg' => 9,
        'autoplay' => false,
        'mediaposition' => 'left',
        'fullscreen' => false,
    ]);

    expect($html)->toContain('controls >')
        ->and($errors)->toBe([]);
});

it('renders no video element when the video callout has no video attributes', function (): void {
    [$html, $errors] = calloutRender(['type' => 'vid', 'mediaposition' => 'top', 'fullscreen' => false]);

    expect($html)->toContain('callout-media callout-video "><div class="callout-content media-top">')
        ->and(calloutUnexpected($errors))->toBe([]);
});

it('renders the background image callout', function (): void {
    [$html, $errors] = calloutRender([
        'type' => 'bgimg',
        'img' => 3,
        'imgcaption' => true,
        'imgoverlay' => 'dark',
        'textbgcolor' => 'white',
        'textwidth' => 'half',
        'textposition' => 'left',
        'heading' => 'Hero',
        'customclass' => 'extra',
        'fullscreen' => false,
    ]);

    expect($html)
        ->toContain('<div class="uams-callout-bgimg normal extra">')
        ->toContain('<div class="bgimg" style="background: url(https://img.test/3-full.jpg);"></div> <!-- .bgimg -->')
        ->toContain('<div class="callout-img-caption">Caption for 3</div></div>')
        ->toContain('<div class="callout-content-container container dark">')
        ->toContain('<div class="callout-content uams-callout white text-center normal half left ">')
        ->toContain('<p class="callout-heading">Hero</p>')
        ->toContain('</div><!-- .uams-callout-bgimg -->')
        ->and(calloutUnexpected($errors))->toBe([]);
});

it('renders the background video callout without a video source', function (): void {
    [$html, $errors] = calloutRender(['type' => 'bgvid', 'img' => 3, 'imgoverlay' => 'light', 'fullscreen' => false]);

    expect($html)
        ->toContain('<div class="uams-callout-bgvid ')
        ->toContain('<div class="callout-media-wrapper callout-video"><div class="embed-responsive"></div><!-- .embed-responsive --></div>')
        ->toContain('<div class="callout-content-container container light">')
        ->and($errors)->toBe([]);
});

it('renders the background video with a video element when source and fallback are set', function (): void {
    [$html, $errors] = calloutRender([
        'type' => 'bgvid',
        'img' => 3,
        'vidsource' => 'https://cdn.test/bg.mp4',
        'fallbackimg' => 4,
        'fullscreen' => false,
    ]);

    expect($html)
        ->toContain('<div class="bgimg" style="background: url(https://img.test/4-full.jpg);"></div> <!-- .bgimg --><video poster="https://img.test/4-full.jpg" controls autoplay loop muted>')
        ->toContain('<source src="https://cdn.test/bg.mp4" type="video/mp4">')
        ->and(calloutUnexpected($errors))->toBe([]);
});

it('renders the default overlay and padding values as null and padding-one', function (): void {
    [$html] = calloutRender(['type' => 'bgimg', 'img' => 3, 'fullscreen' => false]);

    expect($html)->toContain('<div class="callout-content-container container ">');
});

it('passes the overlay and padding through esc_attr', function (): void {
    when('esc_attr')->alias(fn ($v): string => 'ATTR['.$v.']');

    [$html] = calloutRender(['type' => 'bgimg', 'img' => 3, 'imgoverlay' => 'dark', 'fullscreen' => false]);

    expect($html)->toContain('container ATTR[dark]">');
});

it('renders no arrow for a linked callout', function (): void {
    [$html, $errors] = calloutRender(['url' => 'https://example.com', 'fullscreen' => false]);

    expect($html)->not->toContain('right-arrow')
        ->and(calloutUnexpected($errors))->toBe([]);
});

it('treats a truthy string like true for fullscreen, the image caption and autoplay', function (): void {
    [$full] = calloutRender(['customclass' => 'one', 'fullscreen' => '1']);
    [$img] = calloutRender(['type' => 'img', 'img' => 7, 'imgcaption' => '1', 'mediaposition' => 'left', 'fullscreen' => false]);
    [$vid] = calloutRender([
        'type' => 'vid',
        'vidtype' => 'other',
        'vidsource' => 'https://cdn.test/clip.mp4',
        'fallbackimg' => 9,
        'autoplay' => '1',
        'mediaposition' => 'left',
        'fullscreen' => false,
    ]);

    expect($full)->toContain('uams-full-width')
        ->and($img)->toContain('<div class="callout-img-caption">Caption for 7</div>')
        ->and($vid)->toContain('controls autoplay loop muted>');
});

it('renders no self-hosted video unless the type is other and a fallback image is set', function (array $extra): void {
    [$html] = calloutRender([
        'type' => 'vid',
        'vidsource' => 'https://cdn.test/clip.mp4',
        'mediaposition' => 'left',
        'fullscreen' => false,
        ...$extra,
    ]);

    expect($html)->not->toContain('<video');
})->with([
    'type other without a fallback image' => [['vidtype' => 'other']],
    'a fallback image with a type that has no source' => [['vidtype' => 'youtube', 'fallbackimg' => 9]],
]);

it('renders no background video unless both the source and the fallback image are set', function (array $extra): void {
    [$html] = calloutRender(['type' => 'bgvid', 'img' => 3, 'fallbackimg' => 4, 'fullscreen' => false, ...$extra]);

    expect($html)->not->toContain('<video');
})->with([
    'a fallback image only' => [[]],
    'a source only' => [['fallbackimg' => '', 'vidsource' => 'https://cdn.test/bg.mp4']],
]);
