<?php

/**
 * Manages registered shortcodes
 */
class UAMS_Shortcakes
{
    private static $instance;

    private $shortcake_bakery_embeds = true;

    private $internal_shortcode_classes = array(
        /* UAMS Originals */
        'UAMS_Shortcakes\Shortcodes\Button',
        'UAMS_Shortcakes\Shortcodes\IconBox',
        'UAMS_Shortcakes\Shortcodes\Map',
        //'UAMS_Shortcakes\Shortcodes\TextBox',
        /* Originally from NCSU */
        'UAMS_Shortcakes\Shortcodes\Alert',
        'UAMS_Shortcakes\Shortcodes\BaseButton',
        'UAMS_Shortcakes\Shortcodes\Callout',
        'UAMS_Shortcakes\Shortcodes\Pullquote',
        //'UAMS_Shortcakes\Shortcodes\Panel',
        'UAMS_Shortcakes\Shortcodes\ProgressBar',
        'UAMS_Shortcakes\Shortcodes\Lead',
        'UAMS_Shortcakes\Shortcodes\TwoColumn',
        'UAMS_Shortcakes\Shortcodes\ThreeColumn',
        /* Originally from Shortcode Bakery */
        'UAMS_Shortcakes\Shortcodes\Flickr',
        'UAMS_Shortcakes\Shortcodes\Image_Comparison',
        'UAMS_Shortcakes\Shortcodes\PDF',
        'UAMS_Shortcakes\Shortcodes\SoundCloud',
        'UAMS_Shortcakes\Shortcodes\Twitter',
        'UAMS_Shortcakes\Shortcodes\Vimeo',
        'UAMS_Shortcakes\Shortcodes\YouTube',
        /* Unused from Shortcode Bakery */
        // 'UAMS_Shortcakes\Shortcodes\Facebook',
        // 'UAMS_Shortcakes\Shortcodes\GoogleDocs',
        // 'UAMS_Shortcakes\Shortcodes\Iframe',
        // 'UAMS_Shortcakes\Shortcodes\Instagram',
        // 'UAMS_Shortcakes\Shortcodes\Scribd',
        // 'UAMS_Shortcakes\Shortcodes\Script',
    );

    private $registered_shortcode_classes = array();
    private $registered_shortcodes = array();

    public static function get_instance()
    {
        if (!isset(self::$instance)) {
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
    public function autoload_shortcode_classes($class)
    {
        $class = ltrim($class, '\\');
        if (0 !== stripos($class, 'UAMS_Shortcakes\\Shortcodes')) {
            return;
        }

        $parts = explode('\\', $class);

        array_shift($parts);
        array_shift($parts);
        $last = array_pop($parts);
        $last = 'class-' . $last . '.php';
        $parts[] = $last;
        $file = dirname(__FILE__) . '/shortcodes/' . str_replace('_', '-', strtolower(implode($parts, '/')));
        if (file_exists($file)) {
            require $file;
        }
    }

    /**
     * Set up shortcode actions
     */
    private function setup_actions()
    {
        spl_autoload_register(array($this, 'autoload_shortcode_classes'));
        add_action('init', array($this, 'action_init_register_shortcodes'));
        add_action('shortcode_ui_after_do_shortcode', function ($shortcode) {
            return $this::get_shortcake_admin_dependencies();
        });

        add_action( 'shortcode_ui_loaded_editor', array( $this, 'action_admin_enqueue_scripts' ) );
        //add_action( 'media_buttons', array( $this, 'action_media_buttons' ) ); // Button for Embed Media
        add_action( 'wp_ajax_shortcake_bakery_embed_reverse', array( $this, 'action_ajax_shortcake_bakery_embed_reverse' ) );

        add_action('enqueue_shortcode_ui', array($this, 'action_enqueue_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'action_enqueue_scripts'));
    }

    /**
     * Set up shortcode filters
     */
    private function setup_filters()
    {
        add_filter('pre_kses', array($this, 'filter_pre_kses'));
        add_filter('widget_text', 'do_shortcode');
    }

    /**
     * Register all of the shortcodes
     */
    public function action_init_register_shortcodes()
    {

        $this->registered_shortcode_classes = apply_filters('uams_shortcodes_shortcode_classes',
            $this->internal_shortcode_classes);
        foreach ($this->registered_shortcode_classes as $class) {
            $shortcode_tag = $class::get_shortcode_tag();
            $this->registered_shortcodes[$shortcode_tag] = $class;
            add_shortcode($shortcode_tag, array($this, 'do_shortcode_callback'));
            $class::setup_actions();
            $ui_args = $class::get_shortcode_ui_args();
            if (!empty($ui_args) && function_exists('shortcode_ui_register_for_shortcode')) {
                shortcode_ui_register_for_shortcode($shortcode_tag, $ui_args);
            }
        }
    }

    function uams_shortcodes_add_styles()
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
    public function do_shortcode_callback($attrs, $content = '', $shortcode_tag)
    {
        if (empty($this->registered_shortcodes[$shortcode_tag])) {
            return '';
        }

        wp_enqueue_script('shortcake-bakery', UAMS_SHORTCAKES_URL_ROOT . 'assets/js/shortcake-bakery.js',
            array('jquery'), UAMS_SHORTCAKES_VERSION);

        $class = $this->registered_shortcodes[$shortcode_tag];
        return $class::callback($attrs, $content, $shortcode_tag);
    }

    /**
     * Admin dependencies.
     * Scripts required to make shortcake previews work correctly in the admin.
     *
     * @return string
     */
    public static function get_shortcake_admin_dependencies()
    {
        if (!is_admin()) {
            return;
        }
        $r = '<script src="' . esc_url(includes_url('js/jquery/jquery.js')) . '"></script>';
        $r .= '<script type="text/javascript" src="' . esc_url(UAMS_SHORTCAKES_URL_ROOT . 'assets/js/shortcake-bakery.js') . '"></script>';
        return $r;
    }

    public function action_enqueue_scripts()
    {
        wp_enqueue_script( 'shortcodes_scripts' );
        wp_enqueue_style( 'shortcodes_styles' );
    }

    private function setup_depencies()
    {
        wp_register_style('shortcodes_styles', plugin_dir_url(dirname(__FILE__)) . 'assets/css/main.css');
        wp_register_script('shortcodes_scripts', plugin_dir_url(dirname(__FILE__)) . 'assets/js/build/main.js');
    }


    /*
     * Originally from Shortcake Bakery Plugin - Todd McKee, MEd
     */
    // Shortcake Bakery embed code
    public function action_admin_enqueue_scripts() {
        if ( apply_filters( 'shortcake_bakery_show_add_embed', $this->shortcake_bakery_embeds ) ) {
            wp_enqueue_script( 'shortcake-bakery-add-embed-media-frame', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/build/shortcake-bakery-add-embed-media-frame.js', array( 'media-views', 'shortcode-ui' ) );
            $strings = array(
                'text' => array(
                    'addEmbed'           => __( 'Insert Embed Code', 'shortcake-bakery' ),
                    'insertButton'       => __( 'Insert embed', 'shortcake-bakery' ),
                    'customEmbedLabel'   => __( 'Paste any custom embed code here. If it matches a known post element, that post element will be used rather than the embed code.', 'shortcake-bakery' ),
                    'noReversalMatches'  => __( 'The embed code provided doesn\'t match any known post elements. This means that it may not display as expected.', 'shortcake-bakery' ),
                ),
                'nonces' => array(
                    'customEmbedReverse' => wp_create_nonce( 'embed-reverse' ),
                ),
                'shortcodes' => array_flip( $this->registered_shortcodes ),
            );
            wp_localize_script( 'shortcake-bakery-add-embed-media-frame', 'ShortcakeBakery', $strings );
        }

        // wp_enqueue_style( 'shortcake-bakery', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/css/shortcake-bakery.css' );

    }


    /**
     * Output the "Add embed" button with WP media buttons.
     *
     * @return void
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

    public function action_ajax_shortcake_bakery_embed_reverse() {
        if ( empty( $_POST['_wpnonce'] ) || empty( $_POST['custom_embed_code'] ) ) {
            exit;
        }
        check_ajax_referer( 'embed-reverse', '_wpnonce' );
        $post_id = intval( $_POST['post_id'] );
        $provided_embed_code = wp_unslash( $_POST['custom_embed_code'] );
        $result = $this->reverse_embed( $provided_embed_code );

        /**
         * Hook to transform the embed reversal response before returning it to the editor.
         *
         * @param array Return value of `reverse_embed()`
         * @param string Original string provided
         * @param int Post ID
         */
        $result = apply_filters( 'shortcake_bakery_embed_reversal', $result, $provided_embed_code, $post_id );

        /*
         * Fired whenever an embed code is reversed through Ajax action.
         *
         * @param array Return value of `reverse_embed()`
         * @param string Original string provided
         * @param int Post ID
         */
        do_action( 'shortcake_bakery_reversed_embed', $result, $provided_embed_code, $post_id );

        wp_send_json( $result );
        exit;
    }

    /**
     * Given a string containing embed codes, returns any shortcodes which
     * could be extracted from that code by a reversal process.
     *
     * @param string
     * @return array Array to send in a JSON response {
     *    @val bool "success" Whether any shortcodes were found in the reversal process
     *    @val string "reversal" Output string after reversals performed
     *    @val array "shortcodes" Array of matched shortcodes
     * }
     */
    public function reverse_embed( $provided_embed_code ) {
        $success = false;
        $shortcodes = array();
        $reversal = apply_filters( 'pre_kses', $provided_embed_code );
        if ( $reversal !== $provided_embed_code && preg_match_all( '/' . get_shortcode_regex() . '/s', $reversal, $matched_shortcodes, PREG_SET_ORDER ) ) {
            $success = true;

            foreach ( $matched_shortcodes as $matched_shortcode ) {
                $shortcodes[] = array(
                    'shortcode' => $matched_shortcode[2],
                    'attributes' => shortcode_parse_atts( $matched_shortcode[3] ),
                    'inner_content' => $matched_shortcode[5],
                    'raw' => $matched_shortcode[0],
                );
            }
        }
        return array(
            'success' => $success,
            'reversal' => $reversal,
            'shortcodes' => $shortcodes,
        );
    }
}
