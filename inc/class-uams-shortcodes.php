<?php

use UAMS_Shortcakes\Shortcodes\Alert;
use UAMS_Shortcakes\Shortcodes\BaseButton;
use UAMS_Shortcakes\Shortcodes\Button;
use UAMS_Shortcakes\Shortcodes\Callout;
use UAMS_Shortcakes\Shortcodes\Flickr;
use UAMS_Shortcakes\Shortcodes\IconBox;
use UAMS_Shortcakes\Shortcodes\Image_Comparison;
use UAMS_Shortcakes\Shortcodes\Lead;
use UAMS_Shortcakes\Shortcodes\LiveWhale;
use UAMS_Shortcakes\Shortcodes\LiveWhale_Widget;
use UAMS_Shortcakes\Shortcodes\Map;
use UAMS_Shortcakes\Shortcodes\PDF;
use UAMS_Shortcakes\Shortcodes\ProgressBar;
use UAMS_Shortcakes\Shortcodes\Pullquote;
use UAMS_Shortcakes\Shortcodes\SoundCloud;
use UAMS_Shortcakes\Shortcodes\ThreeColumn;
use UAMS_Shortcakes\Shortcodes\Twitter;
use UAMS_Shortcakes\Shortcodes\TwoColumn;
use UAMS_Shortcakes\Shortcodes\Vimeo;
use UAMS_Shortcakes\Shortcodes\YouTubeUI;

/**
 * Manages registered shortcodes
 */
class UAMS_Shortcakes
{
    private static ?UAMS_Shortcakes $instance = null;

    private bool $shortcake_bakery_embeds = true;

    private array $internal_shortcode_classes = [
        /* UAMS Originals */
        Button::class,
        IconBox::class,
        Map::class,
        // 'UAMS_Shortcakes\Shortcodes\TextBox',
        /* Originally from NCSU */
        Alert::class,
        BaseButton::class,
        Callout::class,
        Pullquote::class,
        // 'UAMS_Shortcakes\Shortcodes\Panel',
        ProgressBar::class,
        Lead::class,
        TwoColumn::class,
        ThreeColumn::class,
        /* Originally from Shortcode Bakery */
        Flickr::class,
        Image_Comparison::class,
        PDF::class,
        SoundCloud::class,
        Twitter::class,
        Vimeo::class, // Updated to new shortcode
        // 'UAMS_Shortcakes\Shortcodes\YouTube', //Original
        YouTubeUI::class, // Updated to new shortcode
        /* Unused from Shortcode Bakery */
        // 'UAMS_Shortcakes\Shortcodes\Facebook',
        // 'UAMS_Shortcakes\Shortcodes\GoogleDocs',
        // 'UAMS_Shortcakes\Shortcodes\Iframe',
        // 'UAMS_Shortcakes\Shortcodes\Instagram',
        // 'UAMS_Shortcakes\Shortcodes\Scribd',
        // 'UAMS_Shortcakes\Shortcodes\Script',
        LiveWhale::class,
        LiveWhale_Widget::class,
    ];

    private $registered_shortcode_classes = [];

    private $registered_shortcodes = [];

    public static function get_instance(): UAMS_Shortcakes
    {
        if (! isset(self::$instance)) {
            self::$instance = new UAMS_Shortcakes;
            self::$instance->setup_depencies();
            self::$instance->setup_actions();
            self::$instance->setup_filters();
        }

        return self::$instance;
    }

    /**
     * Autoload any of our shortcode classes
     */
    public function autoload_shortcode_classes($class): void
    {
        $class = ltrim($class, '\\');
        if (stripos($class, 'UAMS_Shortcakes\\Shortcodes') !== 0) {
            return;
        }

        $parts = explode('\\', $class);

        array_shift($parts);
        array_shift($parts);
        $last = array_pop($parts);
        $last = 'class-'.$last.'.php';
        $parts[] = $last;
        $file = __DIR__.'/shortcodes/'.str_replace('_', '-', strtolower(implode('/', $parts)));
        if (file_exists($file)) {
            require $file;
        }
    }

    /**
     * Set up shortcode actions
     */
    private function setup_actions(): void
    {
        spl_autoload_register($this->autoload_shortcode_classes(...));
        add_action('init', $this->action_init_register_shortcodes(...));
        add_action('shortcode_ui_after_do_shortcode', $this->action_print_admin_dependencies(...));

        add_action('shortcode_ui_loaded_editor', $this->action_admin_enqueue_scripts(...));
        // add_action( 'media_buttons', array( $this, 'action_media_buttons' ) ); // Button for Embed Media
        add_action('wp_ajax_shortcake_bakery_embed_reverse', $this->action_ajax_shortcake_bakery_embed_reverse(...));

        add_action('enqueue_shortcode_ui', $this->action_enqueue_scripts(...));
        add_action('wp_enqueue_scripts', $this->action_enqueue_scripts(...));
    }

    /**
     * Set up shortcode filters
     */
    private function setup_filters(): void
    {
        add_filter('pre_kses', $this->filter_pre_kses(...));
        add_filter('widget_text', 'do_shortcode');
    }

    /**
     * Register all of the shortcodes
     */
    public function action_init_register_shortcodes(): void
    {

        $this->registered_shortcode_classes = apply_filters(
            'uams_shortcodes_shortcode_classes',
            $this->internal_shortcode_classes
        );
        foreach ($this->registered_shortcode_classes as $class) {
            $shortcode_tag = $class::get_shortcode_tag();
            $this->registered_shortcodes[$shortcode_tag] = $class;
            add_shortcode($shortcode_tag, $this->do_shortcode_callback(...));
            $class::setup_actions();
            $ui_args = $class::get_shortcode_ui_args();
            if (! empty($ui_args) && function_exists('shortcode_ui_register_for_shortcode')) {
                shortcode_ui_register_for_shortcode($shortcode_tag, $ui_args);
            }
        }
    }

    public function uams_shortcodes_add_styles(): void
    {
        wp_enqueue_style('shortcodes_styles');
    }

    /**
     * Modify post content before kses is applied
     * Used to trans
     */
    public function filter_pre_kses($content)
    {
        foreach ($this->registered_shortcode_classes as $shortcode_class) {
            $content = $shortcode_class::reversal($content);
        }

        return $content;
    }

    /**
     * Do the shortcode callback
     */
    public function do_shortcode_callback($attrs, $content, $shortcode_tag)
    {
        if (empty($this->registered_shortcodes[$shortcode_tag])) {
            return '';
        }

        wp_enqueue_script(
            'shortcake-bakery',
            UAMS_SHORTCAKES_URL_ROOT.'assets/js/shortcake-bakery.js',
            ['jquery'],
            UAMS_SHORTCAKES_VERSION
        );

        $class = $this->registered_shortcodes[$shortcode_tag];

        return $class::callback($attrs, $content, $shortcode_tag);
    }

    /**
     * Admin dependencies.
     * Scripts required to make shortcake previews work correctly in the admin.
     */
    public static function get_shortcake_admin_dependencies(): ?string
    {
        if (! is_admin()) {
            return null;
        }

        $r = '<script src="'.esc_url(includes_url('js/jquery/jquery.js')).'"></script>';
        $r .= '<script type="text/javascript" src="'.esc_url(UAMS_SHORTCAKES_URL_ROOT.'assets/js/shortcake-bakery.js').'"></script>';

        return $r;
    }

    /**
     * Print the admin dependencies after a shortcode renders in a Shortcake preview.
     * Shortcake captures what this action echoes and discards what a callback returns.
     */
    public function action_print_admin_dependencies(): void
    {
        echo self::get_shortcake_admin_dependencies(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- both tags are built with esc_url().
    }

    public function action_enqueue_scripts(): void
    {
        wp_enqueue_script('shortcodes_scripts');
        wp_enqueue_style('shortcodes_styles');
    }

    private function setup_depencies(): void
    {
        wp_register_style('shortcodes_styles', plugin_dir_url(__DIR__).'assets/css/main.css');
        wp_register_script('shortcodes_scripts', plugin_dir_url(__DIR__).'assets/js/build/main.js');
    }

    /*
     * Originally from Shortcake Bakery Plugin - Todd McKee, MEd
     */
    // Shortcake Bakery embed code
    public function action_admin_enqueue_scripts(): void
    {
        if (apply_filters('shortcake_bakery_show_add_embed', $this->shortcake_bakery_embeds)) {
            wp_enqueue_script('shortcake-bakery-add-embed-media-frame', plugin_dir_url(__DIR__).'assets/js/build/shortcake-bakery-add-embed-media-frame.js', ['media-views', 'shortcode-ui']);
            $strings = [
                'text' => [
                    'addEmbed' => __('Insert Embed Code', 'shortcake-bakery'),
                    'insertButton' => __('Insert embed', 'shortcake-bakery'),
                    'customEmbedLabel' => __('Paste any custom embed code here. If it matches a known post element, that post element will be used rather than the embed code.', 'shortcake-bakery'),
                    'noReversalMatches' => __("The embed code provided doesn't match any known post elements. This means that it may not display as expected.", 'shortcake-bakery'),
                ],
                'nonces' => [
                    'customEmbedReverse' => wp_create_nonce('embed-reverse'),
                ],
                'shortcodes' => array_flip($this->registered_shortcodes),
            ];
            wp_localize_script('shortcake-bakery-add-embed-media-frame', 'ShortcakeBakery', $strings);
        }

        // wp_enqueue_style( 'shortcake-bakery', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/css/shortcake-bakery.css' );

    }

    /**
     * Output the "Add embed" button with WP media buttons.
     */
    // public function action_media_buttons( $editor_id ) {
    //     if ( ! apply_filters( 'shortcake_bakery_show_add_embed', $this->shortcake_bakery_embeds ) ) {
    //         return false;
    //     }
    //     printf( '<button type="button" class="button insert-embed shortcake-bakery-insert-embed" data-editor="%s"><span class="dashicons dashicons-editor-code"></span> %s</button>',
    //         esc_attr( $editor_id ),
    //         esc_html__( 'Add Embed', 'shortcake-bakery' )
    //     );
    // }
    public function action_ajax_shortcake_bakery_embed_reverse(): void
    {
        if (empty($_POST['_wpnonce']) || empty($_POST['custom_embed_code'])) {
            exit;
        }

        check_ajax_referer('embed-reverse', '_wpnonce');
        $post_id = intval($_POST['post_id']);
        $provided_embed_code = wp_unslash($_POST['custom_embed_code']);
        $result = $this->reverse_embed($provided_embed_code);

        /**
         * Hook to transform the embed reversal response before returning it to the editor.
         *
         * @param  array  $result  Return value of `reverse_embed()`
         * @param  string  $provided_embed_code  Original string provided
         * @param  int  $post_id  Post ID
         */
        $result = apply_filters('shortcake_bakery_embed_reversal', $result, $provided_embed_code, $post_id);

        /*
         * Fired whenever an embed code is reversed through Ajax action.
         *
         * @param array Return value of `reverse_embed()`
         * @param string Original string provided
         * @param int Post ID
         */
        do_action('shortcake_bakery_reversed_embed', $result, $provided_embed_code, $post_id);

        wp_send_json($result);
        exit;
    }

    /**
     * Given a string containing embed codes, returns any shortcodes which
     * could be extracted from that code by a reversal process.
     *
     * @param string
     * @return array Array to send in a JSON response {
     *
     *    @val bool "success" Whether any shortcodes were found in the reversal process
     *    @val string "reversal" Output string after reversals performed
     *    @val array "shortcodes" Array of matched shortcodes
     * }
     */
    public function reverse_embed($provided_embed_code): array
    {
        $success = false;
        $shortcodes = [];
        $reversal = apply_filters('pre_kses', $provided_embed_code);
        if ($reversal !== $provided_embed_code && preg_match_all('/'.get_shortcode_regex().'/s', $reversal, $matched_shortcodes, PREG_SET_ORDER)) {
            $success = true;

            foreach ($matched_shortcodes as $matched_shortcode) {
                $shortcodes[] = [
                    'shortcode' => $matched_shortcode[2],
                    'attributes' => shortcode_parse_atts($matched_shortcode[3]),
                    'inner_content' => $matched_shortcode[5],
                    'raw' => $matched_shortcode[0],
                ];
            }
        }

        return [
            'success' => $success,
            'reversal' => $reversal,
            'shortcodes' => $shortcodes,
        ];
    }
}
