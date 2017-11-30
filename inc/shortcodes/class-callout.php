<?php

/**
 * Callout shortcode
 *
 * It would be good to extract out some of the functionality
 * that is currently bloating the callout, that way the actual
 * callout isn't quite so large and hard to digest
 */

namespace UAMS_Shortcakes\Shortcodes;

class Callout extends Shortcode
{
    private static $defaults = array(
        'heading' => null,
        'headingtype' => 'p',
        'headingicon' => null,
        'url' => null,
        'target' => false,
        'textalign' => 'textcenter',
        'type' => 'basic',
        'bgcolor' => 'uamsred',
        'img' => null,
        'imgcaption' => false,
        'vidtype' => null,
        'youtube' => null,
        'vimeo' => null,
        'vidsource' => null,
        'autoplay' => null,
        'fallbackimg' => null,
        'imgoverlay' => null,
        'textbgcolor' => null,
        'mediaposition' => null,
        'textposition' => null,
        'textwidth' => null,
        'margin' => 'normal',
        'customclass' => null,
    );

    public static function get_shortcode_ui_args()
    {
        return array(
            'label' => esc_html__('Callout', 'uams_shortcodes'),
            'listItemImage' => '<img width="100px" height="100px" src="' . esc_url( UAMS_SHORTCAKES_URL_ROOT . 'admin/images/icon-callout.png' ) . '" />',
            'inner_content' => array(
                'label' => esc_html__('Callout Body', 'uams_shortcodes'),
                'description' => esc_html__('Main content of your callout. Text and basic HTML tags can be added here.', 'uams_shortcodes'),
            ),
            'attrs' => self::get_attributes()
        );
    }

    /**
     * provide UI items for shortcake
     *
     * @return array
     */
    public static function get_attributes()
    {
        return array(

            array(
                'label' => esc_html__('Callout Heading', 'uams_shortcodes'),
                'attr' => 'heading',
                'type' => 'text',
                'description' => 'Optional',
                'encode' => false,
            ),

            array(
                'label' => esc_html__('Heading Type', 'uams_shortcodes'),
                'attr' => 'headingtype',
                'type' => 'radio',
                'description' => 'Headings <a href="https://accessibility.oit.ncsu.edu/training/accessibility-handbook/headings.html" target="_blank">help determine page structure</a>, and are used by screen reading software as landmarks for navigating page content. If you are using this callout as a page title or heading for a section on the page, choose the appropriate heading. Otherwise, choose "Paragraph."',
                'encode' => false,
                'options' => array(
                    'p' => 'Paragraph',
                    'h3' => 'Heading 3',
                    'h2' => 'Heading 2',
                    'h1' => 'Heading 1',
                ),
            ),

            array(
                'label' => esc_html__('Heading Icon', 'uams_shortcodes'),
                'attr' => 'headingicon',
                'type' => 'select',
                'description' => 'Select any <a href="https://brand.ncsu.edu/bootstrap/components/" target="_blank">Bootstrap Glyphicon</a> or <a href="https://developer.wordpress.org/resource/dashicons/" target="_blank">WordPress Dashicon</a>.',
                'encode' => false,
                'options' => self::get_icon_collection(),
            ),

            array(
                'label' => esc_html__('Text Alignment', 'uams_shortcodes'),
                'attr' => 'textalign',
                'type' => 'radio',
                'encode' => false,
                'options' => array(
                    'textleft' => 'Left',
                    'textcenter' => 'Center',
                    'textright' => 'Right',
                ),
            ),

            array(
                'label' => esc_html__('URL', 'uams_shortcodes'),
                'attr' => 'url',
                'type' => 'url',
                'description' => 'Optional. Enter a URL to make your callout a clickable hyperlink.',
                'encode' => false,
                'meta' => array(
                    'placeholder' => 'http://www.uams.edu/'
                )
            ),

            array(
                'label' => esc_html__('Open the link in a new window', 'uams_shortcodes'),
                'attr' => 'target',
                'type' => 'checkbox',
                'encode' => false,
            ),

            array(
                'label' => esc_html__('Callout Type', 'uams_shortcodes'),
                'attr' => 'type',
                'type' => 'radio',
                'description' => 'See examples of <a href="https://design.oit.ncsu.edu/docs/ncsu-shortcodes/callouts/" target="_blank">each type of callout</a> on the <a href="https://design.oit.ncsu.edu/docs/" target="_blank">OIT Design documentation site</a>.',
                'encode' => false,
                'options' => array(
                    'basic' => 'Basic Callout',
                    'img' => 'Callout with Image',
                    'vid' => 'Callout with Video',
                    'bgimg' => 'Callout with Background Image',
                    'bgvid' => 'Callout with Background Video',
                ),
            ),

            array(
                'label' => esc_html__('Callout Background Color', 'uams_shortcodes'),
                'attr' => 'bgcolor',
                'type' => 'radio',
                'description' => 'Review the <a href="https://brand.ncsu.edu/color/" target="_blank">NC State Brand color palette</a> for proper usage. Default font is <strong>UAMS Red</strong>.',
                'encode' => false,
                'options' => self::get_uams_brand_colors(),
            ),

            array(
                'label' => esc_html__('Image', 'uams_shortcodes'),
                'attr' => 'img',
                'type' => 'attachment',
                'description' => 'See examples and <a href="https://design.oit.ncsu.edu/docs/ncsu-shortcodes/callouts#img" target="_blank">recommended dimensions</a> on the <a href="https://design.oit.ncsu.edu/docs/" target="_blank">OIT Design documentation site</a>.',
            ),

            array(
                'label' => 'Display Image Caption?',
                'attr' => 'imgcaption',
                'description' => 'Check to display the caption field from the media file just beneath the callout. (<a href="https://design.oit.ncsu.edu/docs/ncsu-shortcodes/callouts#img" target="_blank">Learn more.</a>)',
                'type' => 'checkbox',
            ),

            array(
                'label' => esc_html__('Video Type', 'uams_shortcodes'),
                'attr' => 'vidtype',
                'type' => 'radio',
                'description' => 'See examples and <a href="https://design.oit.ncsu.edu/docs/ncsu-shortcodes/callouts#vid" target="_blank">notes about video hosting</a> on the <a href="https://design.oit.ncsu.edu/docs/" target="_blank">OIT Design documentation site</a>.',
                'encode' => false,
                'options' => array(
                    'youtube' => 'YouTube',
                    'vimeo' => 'Vimeo',
                    'other' => 'Other',
                ),
            ),

            array(
                'label' => esc_html__('YouTube URL', 'uams_shortcodes'),
                'attr' => 'youtube',
                'type' => 'url',
                'description' => '',
                'encode' => false,
                'meta' => array(
                    'placeholder' => 'https://www.youtube.com/watch?v=oi1t_jSWZN8'
                )
            ),

            array(
                'label' => esc_html__('Vimeo URL', 'uams_shortcodes'),
                'attr' => 'vimeo',
                'type' => 'url',
                'description' => '',
                'encode' => false,
                'meta' => array(
                    'placeholder' => 'https://vimeo.com/60686233'
                )
            ),

            array(
                'label' => esc_html__('Video Source URL', 'uams_shortcodes'),
                'attr' => 'vidsource',
                'type' => 'url',
                'description' => 'Note: This player supports only <strong>.mp4</strong> file types. (<a href="https://design.oit.ncsu.edu/docs/ncsu-shortcodes/callouts#vid" target="_blank">Learn more.</a>)',
                'encode' => false,
            ),

            array(
                'label' => esc_html__('Autoplay Video', 'uams_shortcodes'),
                'attr' => 'autoplay',
                'type' => 'checkbox',
                'description' => 'Note: If video is set to autoplay, audio will be disabled. (<a href="https://design.oit.ncsu.edu/docs/ncsu-shortcodes/callouts#vid" target="_blank">Learn more.</a>)',
            ),

            array(
                'label' => esc_html__('Fallback Image', 'uams_shortcodes'),
                'attr' => 'fallbackimg',
                'type' => 'attachment',
                'description' => 'See examples and <a href="https://design.oit.ncsu.edu/docs/ncsu-shortcodes/callouts#vid" target="_blank">recommended dimensions</a> on the <a href="https://design.oit.ncsu.edu/docs/" target="_blank">OIT Design documentation site</a>.',
            ),

            array(
                'label' => esc_html__('Image Adjustments', 'uams_shortcodes'),
                'attr' => 'imgoverlay',
                'type' => 'radio',
                'description' => 'See examples and <a href="" target="_blank">notes about video hosting</a> on the <a href="/docs/" target="_blank">Webteam documentation site</a>.',
                'encode' => false,
                'options' => array(
                    '' => 'None',
                    'darken' => 'Darken (Dark overlay for light text)',
                    'lighten' => 'Lighten (Light overlay for dark text)',
                ),
            ),

            array(
                'label' => esc_html__('Text Background Color', 'uams_shortcodes'),
                'attr' => 'textbgcolor',
                'type' => 'radio',
                'description' => 'Review the <a href="https://brand.ncsu.edu/color/" target="_blank">NC State Brand color palette</a> for proper usage.',
                'encode' => false,
                'options' => self::get_uams_brand_colors_plus(),
            ),

            array(
                'label' => esc_html__('Media Position', 'uams_shortcodes'),
                'attr' => 'mediaposition',
                'type' => 'radio',
                'description' => '',
                'encode' => false,
                'options' => array(
                    'left' => 'Left',
                    'right' => 'Right',
                ),
            ),

            array(
                'label' => esc_html__('Text Position', 'uams_shortcodes'),
                'attr' => 'textposition',
                'type' => 'radio',
                'description' => '',
                'encode' => false,
                'options' => array(
                    'top left' => 'Top Left',
                    'top center' => 'Top Center',
                    'top right' => 'Top Right',
                    'middle left' => 'Middle Left',
                    'middle center' => 'Middle Center',
                    'middle right' => 'Middle Right',
                    'bottom left' => 'Bottom Left',
                    'bottom center' => 'Bottom Center',
                    'bottom right' => 'Bottom Right',
                ),
            ),

            array(
                'label' => esc_html__('Text Box Width', 'uams_shortcodes'),
                'attr' => 'textwidth',
                'type' => 'radio',
                'description' => '',
                'encode' => false,
                'options' => array(
                    'col-lg-3 col-md-3 col-sm-12 col-xs-12' => 'One-Quarter Width',
                    'col-lg-4 col-md-4 col-sm-12 col-xs-12' => 'One-Third Width',
                    'col-lg-6 col-md-6 col-sm-12 col-xs-12' => 'One-Half Width',
                    'col-lg-8 col-md-8 col-sm-12 col-xs-12' => 'Two-Thirds Width',
                    'col-lg-9 col-md-9 col-sm-12 col-xs-12' => 'Three-Quarters Width',
                    'col-lg-12 col-md-12 col-sm-12 col-xs-12' => 'Full-Width',
                ),
            ),

            array(
                'label' => esc_html__('Margins', 'uams_shortcodes'),
                'attr' => 'margin',
                'type' => 'radio',
                'encode' => false,
                'options' => array(
                    'normal' => 'Normal',
                    'thinmargin' => 'Thin',
                    'nomargin' => 'None',
                ),
            ),

            array(
                'label' => esc_html__('Fullsreen?', 'uams_shortcodes'),
                'attr' => 'fullscreen',
                'type' => 'checkbox',
                'description' => '<strong>Note:</strong> This option only works on full width page template (No sidebar). You may get unintended results on pages with sidebar.',
            ),

            array(
                'label' => esc_html__('Custom CSS classes', 'uams_shortcodes'),
                'attr' => 'customclass',
                'type' => 'text',
                'description' => '',
                'encode' => false,
                'meta' => array(
                    'placeholder' => 'Comma-separated list'
                )
            )
        );
    }

    public static function callback($attrs, $content = '')
    {
        $attrs = (is_array($attrs))
            ? array_merge(self::$defaults, $attrs)
            : self::$defaults;

        $type = $attrs['type'];

        $basic_classes = array('uams-callout', $attrs['bgcolor'], $attrs['textalign'], $attrs['margin']);
        $textbox_classes = array('uams-callout', $attrs['textbgcolor'], $attrs['textalign'], $attrs['margin'], $attrs['textwidth'], $attrs['textposition']);

        $body_classes = array();

        $ignored_icons = self::get_ignored_icon_names();

        if (!empty($attrs['headingicon']) && !in_array($attrs['headingicon'], $ignored_icons)) {
            $icon_name = $attrs['headingicon'];

            $icon = '<span class="'. $icon_name .'" aria-hidden="true"></span>';            
        } else {
            $icon = null;
        }

        if (!empty($attrs['heading'])) {
            $heading = '<' . $attrs['headingtype'] . ' class="callout-heading">' . $icon . $attrs['heading'] . '</' . $attrs['headingtype'] . '>';
        } else {
            $heading = null;
        }

        if (!empty($attrs['url'])) {
            if ($attrs['target'] === true) {
                $target = 'target="_blank"';
            } else {
                $target = null;
            }

            $urlopen = '<a href="' . esc_url( $attrs['url'] ) . '" ' . $target . '>';
            $urlclose = '</a>';
            $arrow = '<span class="right-arrow" aria-hidden="true">'. self::uams_shortcodes_get_contents( esc_url(UAMS_SHORTCAKES_PATH . 'assets/images/glyphicon/bold-arrow-right.svg' ) ) .'</span>';
        } else {
            $urlopen = '<div>';
            $urlclose = '</div>';
            $arrow = null;
        }

        if (!empty($attrs['customclass'])) {
            $customclasses = explode(",", $attrs['customclass']);
            $basic_classes = array_merge($basic_classes, $customclasses);
            $customclass = implode(" ", array_map( 'sanitize_html_class', explode( ",", $attrs['customclass'] ) ) );
        }

        if ( $attrs['fullscreen'] == true ) {
            $fullscreen = "uams-full-width";
            //$basic_classes = array_push($basic_classes, $fullscreen);
            $customclass = $customclass . " " . $fullscreen;
        } else {
            $fullscreen = null;
        }

        if (!empty($attrs['img'])) {
            $img = wp_get_attachment_image_srcset($attrs['img'], 'large');
            $alt = get_post_meta($attrs['img'], '_wp_attachment_image_alt', true);
        } else {
            $img = null;
            $alt = null;
        }

        if ($attrs['mediaposition'] == 'left') {
            $mediaposition = 'media-left';
        } elseif ($attrs['mediaposition'] == 'right') {
            $mediaposition = 'media-right';
        }

        if ($attrs['imgcaption'] == true) {
            $caption = get_post_field('post_excerpt', $attrs['img']);
            $imgcap = '<div class="callout-img-caption">' . $caption . '</div>';
        } else {
            $imgcap = null;
        }

        if (!empty($attrs['fallbackimg'])) {
            $fallbackimg = wp_get_attachment_image_srcset($attrs['fallbackimg']);
            $fallbackalt = get_post_meta($attrs['fallbackimg'], '_wp_attachment_image_alt', true);
            $poster = wp_get_attachment_image_src($attrs['fallbackimg'], 'full')[0];

        } else {
            $fallbackimg = null;
            $fallbackalt = null;
        }

        if (!empty($attrs['imgoverlay'])) {
            $overlay = esc_attr($attrs['imgoverlay']);
        } else {
            $overlay = null;
        }



        $vidsrc = null;
        $vidembed = null;

        if ($attrs['type'] === 'vid') {

            if (!empty($attrs['youtube']) && ($attrs['vidtype'] == 'youtube')) {

                if (preg_match("/youtu.be\/[a-z1-9.-_]+/", $attrs['youtube'])) {
                    preg_match("/youtu.be\/([a-z1-9.-_]+)/", $attrs['youtube'], $matches);
                    if (isset($matches[1])) {
                        $vidsrc = 'http://www.youtube.com/embed/' . $matches[1] . '?modestbranding=0&autohide=1&showinfo=0';
                        $vidembed = '<iframe class="embed-responsive-item ' . $mediaposition . '" src="' . esc_url( $vidsrc ) . '"></iframe>';
                    }
                } else if (preg_match("/youtube.com(.+)v=([^&]+)/", $attrs['youtube'])) {
                    preg_match("/v=([^&]+)/", $attrs['youtube'], $matches);
                    if (isset($matches[1])) {
                        $vidsrc = 'http://www.youtube.com/embed/' . $matches[1] . '?modestbranding=0&autohide=1&showinfo=0';
                        $vidembed = '<iframe class="embed-responsive-item ' . $mediaposition . '" src="' . esc_url( $vidsrc ) . '"></iframe>';
                    }
                }
            } else if (!empty($attrs['vimeo']) && ($attrs['vidtype'] == 'vimeo')) {
                if (preg_match("/vimeo.com\/[1-9.-_]+/", $attrs['vimeo'])) {
                    preg_match("/vimeo.com\/([1-9.-_]+)/", $attrs['vimeo'], $matches);
                    if (isset($matches[1])) {
                        $vidsrc = 'http://player.vimeo.com/video/' . $matches[1] . '?badge=0&byline=0&portrait=0&title=0';
                        $vidembed = '<iframe class="embed-responsive-item ' . $mediaposition . '" src="' . esc_url( $vidsrc ) . '"></iframe>';
                    }
                }
            } else if (!empty($attrs['vidsource']) && ($attrs['vidtype'] == 'other' && !empty($attrs['fallbackimg']))) {
                if ($attrs['autoplay'] == true) {
                    $autoplay = 'autoplay loop muted';
                }

                $vidembed = '<video class="callout-media-element" poster="' . esc_url( $poster ) . '" controls ' . $autoplay . '>
                        <source src="' . esc_url( $attrs['vidsource'] ) . '" type="video/mp4">
                        <img srcset="' . $fallbackimg . '" alt="' . $fallbackalt . '" />
                        Your browser does not support the <pre>video</pre> tag.
                        </video>';
            }

            $video = $vidembed;
        }

        if ($attrs['type'] === 'bgimg') {
            $bgimg = wp_get_attachment_image_src($attrs['img'], 'full')[0];
            $calloutbgimg = "<div class=\"bgimg\" style=\"background: url(" . esc_url( $bgimg ) . ");\"></div> <!-- .bgimg -->";
        } else {
            $bgimg = null;
        }


        if ($attrs['type'] === 'bgvid') {

            $bgimg = wp_get_attachment_image_src($attrs['img'], 'full')[0];
            $calloutbgimg = "<div class=\"bgimg\" style=\"background: url(" . esc_url( $poster ) . ");\"></div> <!-- .bgimg -->";

            if (!empty($attrs['vidsource']) && !empty($attrs['fallbackimg'])) {

                $vidembed = $calloutbgimg . '<video poster="' . $poster . '" controls autoplay loop muted>
                            <source src="' . esc_url( $attrs['vidsource'] ) . '" type="video/mp4">
                            
                            Your browser does not support the <pre>video</pre> tag.
                            </video>';
            }

            $video = sprintf(
                "<div class=\"embed-responsive\">%s</div><!-- .embed-responsive -->",
                $vidembed
            );

        }

        switch ($type) {
            case 'basic':
                return sprintf(
                    "<div class=\"%s %s\"><div>%s%s%s<p class=\"%s\">%s</p></div></div><!-- .uams-callout -->",
                    implode(' ', $basic_classes),
                    esc_attr( $customclass ),
                    $urlopen,
                    $heading,
                    //$arrow,
                    $urlclose,
                    implode(' ', $body_classes),
                    wpautop( do_shortcode($content) )
                );
                break;

            case 'img':

                return sprintf(
                    "<div class=\"%s callout-media callout-media-img %s\"><div class=\"callout-img %s\"><img class=\"img-responsive\" srcset=\"%s\" sizes=\"(min-width: 36em) 33.3vw, 100vw\" alt=\"%s\" />%s</div><div class=\"callout-content %s\">%s%s%s<p class=\"%s\">%s</p></div></div><!-- .uams-callout -->",
                    implode(' ', $basic_classes),
                    esc_attr( $customclass ),
                    $mediaposition,
                    $img,
                    $alt,
                    $imgcap,
                    $mediaposition,
                    $urlopen,
                    $heading,
                    //$arrow,
                    $urlclose,
                    implode(' ', $body_classes),
                    wpautop( do_shortcode($content) )
                );
                break;


            case 'vid':

                return sprintf(
                    "<div class=\"%s callout-media callout-video %s\">%s<div class=\"callout-content %s\">%s%s%s<p class=\"%s\">%s</p></div></div><!-- .uams-callout -->",
                    implode(' ', $basic_classes),
                    esc_attr( $customclass ),
                    $video,
                    $mediaposition,
                    $urlopen,
                    $heading,
                    //$arrow,
                    $urlclose,
                    implode(' ', $body_classes),
                    wpautop( do_shortcode($content) )
                );
                break;

            case 'bgimg':

                return sprintf(
                    "<div class=\"uams-callout-bgimg %s %s\">
                        <div class=\"callout-media-wrapper callout-img\">%s%s</div>
                        <div class=\"callout-content-container container %s\">
                            <div class=\"callout-content %s\">%s%s%s<p class=\"%s\">%s</p></div>
                        </div><!-- .callout-img -->
                    </div><!-- .uams-callout-bgimg -->",
                    $attrs['margin'],
                    esc_attr( $customclass ),
                    $calloutbgimg,
                    $imgcap,
                    $overlay,
                    implode(' ', $textbox_classes),
                    $urlopen,
                    $heading,
                    //$arrow,
                    $urlclose,
                    implode(' ', $body_classes),
                    wpautop( do_shortcode($content) )
                );
                break;

            case 'bgvid':

                return sprintf(
                    "<div class=\"uams-callout-bgvid %s\">
                        <div class=\"callout-media-wrapper callout-video\">%s</div>
                        <div class=\"callout-content-container container %s\">
                            <div class=\"callout-content %s\">%s%s%s<p class=\"%s\">%s</p></div>
                        </div><!-- .callout-video -->
                    </div><!-- .uams-callout-bgvig -->",
                    esc_attr( $customclass ),
                    $video,
                    $overlay,
                    implode(' ', $textbox_classes),
                    $urlopen,
                    $heading,
                    //$arrow,
                    $urlclose,
                    implode(' ', $body_classes),
                    wpautop( do_shortcode($content) )
                );
                break;

            default:
                return sprintf(
                    "<div class=\"%s %s\"><div>%s%s%s<p class=\"%s\">%s</p></div></div>",
                    implode(' ', $basic_classes),
                    esc_attr( $customclass ),
                    $urlopen,
                    $heading,
                    //$arrow,
                    $urlclose,
                    implode(' ', $body_classes),
                    wpautop( do_shortcode($content) )
                );
                break;
        }
    }
}




