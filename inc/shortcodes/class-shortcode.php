<?php

namespace UAMS_Shortcakes\Shortcodes;

/**
 * Base class for all shortcodes to extend
 * Ensures each shortcode implements a consistent pattern
 */
abstract class Shortcode
{
    /**
     * Get the "tag" used for the shortcode. This will be stored in post_content
     *
     * @return string
     */
    public static function get_shortcode_tag()
    {
        $parts = explode('\\', get_called_class());
        $shortcode_tag = array_pop($parts);
        $shortcode_tag = strtolower($shortcode_tag); // Original strtolower(str_replace('_', '-', $shortcode_tag));
        return apply_filters('uams_shortcodes_shortcode_tag', $shortcode_tag, get_called_class());
    }

    /**
     * Make string something that can be used as a CSS class or id
     * 
     * @return string
     */
    public static function text_to_css_friendly($string)
    {
        //Lower case everything
        $string = strtolower($string);
        //Make alphanumeric (removes all other characters)
        $string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
        //Clean up multiple dashes or whitespaces
        $string = preg_replace("/[\s-]+/", " ", $string);
        //Convert whitespaces and underscore to dash
        $string = preg_replace("/[\s_]/", "-", $string);
        return $string;
    }

    /**
     * Get SVG file contents
     * 
     * @return string
     */
    public static function uams_shortcodes_get_contents($url)
    {
        if ( file_get_contents( $url, FILE_USE_INCLUDE_PATH ) ) {
            return file_get_contents( $url, FILE_USE_INCLUDE_PATH );
        } else {
            return null;
        }
    }   

    /**
    * Get UAMS typography collection (css class names)
    *
    * @return array
    */
    // public static function get_uams_typography()
    // {
    //     return array(
    //         'universlight' => 'UniversLight',
    //         'universroman' => 'UniversRoman',
    //         'universcondensed' => 'UniversCondensed',
    //         'universlightcondensed' => 'UniversLightCondensed',
    //         'glypha' => 'Glypha',
    //     );
    // }

    /**
    * Get icon collection
    *
    * @return array
    */
    public static function get_icon_collectiOn()
    {
        return array(
            'noicon'                   => 'No Icon',
            'ic-address-book'          => 'Address-book',
            'ic-administration'        => 'Administration',
            'ic-book'                  => 'Book',
            'ic-bookmark'              => 'Bookmark',
            'ic-briefcase'             => 'Briefcase',
            'ic-calendar'              => 'Calendar',
            'ic-camera'                => 'Camera',
            'ic-capitol'               => 'Capitol',
            'ic-check'                 => 'Check',
            'ic-clipboard'             => 'Clipboard',
            'ic-close'                 => 'Close',
            'ic-compass'               => 'Compass',
            'ic-computer'              => 'Computer',
            'ic-directions'            => 'Directions',
            'ic-eating'                => 'Eating',
            'ic-flag'                  => 'Flag',
            'ic-globe'                 => 'Globe',
            'ic-globe2'                => 'Globe2',
            'ic-handshake'             => 'Handshake',
            'ic-heart'                 => 'Heart',
            'ic-home'                  => 'Home',
            'ic-key'                   => 'Key',
            'ic-letter'                => 'Letter',
            'ic-list'                  => 'List',
            'ic-mail'                  => 'Mail',
            'ic-map-marker'            => 'Map-marker',
            'ic-map'                   => 'Map',
            'ic-marker'                => 'Marker',
            'ic-minus'                 => 'Minus',
            'ic-music'                 => 'Music',
            'ic-page'                  => 'Page',
            'ic-page2'                 => 'Page2',
            'ic-passport'              => 'Passport',
            'ic-pause'                 => 'Pause',
            'ic-pencil'                => 'Pencil',
            'ic-person'                => 'Person',
            'ic-picture'               => 'Picture',
            'ic-plane'                 => 'Plane',
            'ic-play'                  => 'Play',
            'ic-plus'                  => 'Plus',
            'ic-podium'                => 'Podium',
            'ic-ribbon'                => 'Ribbon',
            'ic-right-arrow-full'      => 'Right-arrow-full',
            'ic-right-arrow'           => 'Right-arrow',
            'ic-search'                => 'Search',
            'ic-settings'              => 'Settings',
            'ic-social'                => 'Social',
            'ic-stop'                  => 'Stop',
            'ic-suitcase'              => 'Suitcase',
            'ic-ticket'                => 'Ticket',
            'ic-tools'                 => 'Tools',
            'ic-trash'                 => 'Trash',
            'ic-umbrella'              => 'Umbrella',
            'ic-view'                  => 'View',
            'ic-watch'                 => 'Watch',
            'i-1-hour'                 => '1-hour',
            'i-15-min'                 => '15-min',
            'i-30-min'                 => '30-min',
            'i-45-min'                 => '45-min',
            'i-911'                    => '911',
            'i-accessibility'          => 'Accessibility',
            'i-aid-kit-2'              => 'Aid-kit-2',
            'i-aid-kit'                => 'Aid-kit',
            'i-airport'                => 'Airport',
            'i-ambulance'              => 'Ambulance',
            'i-android'                => 'Android',
            'i-apple'                  => 'Apple',
            'i-at-symbol'              => 'At-symbol',
            'i-atom'                   => 'Atom',
            'i-bacteria-1'             => 'Bacteria-1',
            'i-bacteria-2'             => 'Bacteria-2',
            'i-bag'                    => 'Bag',
            'i-bank'                   => 'Bank',
            'i-basket'                 => 'Basket',
            'i-batman'                 => 'Batman',
            'i-bed'                    => 'Bed',
            'i-bike'                   => 'Bike',
            'i-biohazzard'             => 'Biohazzard',
            'i-blood-drop'             => 'Blood-drop',
            'i-books-1'                => 'Books-1',
            'i-box'                    => 'Box',
            'i-brain'                  => 'Brain',
            'i-briefcase'              => 'Briefcase',
            'i-building-1'             => 'Building-1',
            'i-building-2'             => 'Building-2',
            'i-building-3'             => 'Building-3',
            'i-building-4'             => 'Building-4',
            'i-building-5'             => 'Building-5',
            'i-building-6'             => 'Building-6',
            'i-building-7'             => 'Building-7',
            'i-building-8'             => 'Building-8',
            'i-building-9'             => 'Building-9',
            'i-building-10'            => 'Building-10',
            'i-building-11'            => 'Building-11',
            'i-building-12'            => 'Building-12',
            'i-bullhorn'               => 'Bullhorn',
            'i-bus-station'            => 'Bus-station',
            'i-bus'                    => 'Bus',
            'i-business-man'           => 'Business-man',
            'i-business-woman'         => 'Business-woman',
            'i-calendar'               => 'Calendar',
            'i-camera'                 => 'Camera',
            'i-capitol'                => 'Capitol',
            'i-cart'                   => 'Cart',
            'i-certificate'            => 'Certificate',
            'i-chat-bubbles'           => 'Chat-bubbles',
            'i-church'                 => 'Church',
            'i-clipboard-pencil'       => 'Clipboard-pencil',
            'i-close'                  => 'Close',
            'i-cloudy'                 => 'Cloudy',
            'i-coffee-cup'             => 'Coffee-cup',
            'i-compass'                => 'Compass',
            'i-credit-card'            => 'Credit-card',
            'i-darth-vader'            => 'Darth-vader',
            'i-directions'             => 'Directions',
            'i-dispatcher-female'      => 'Dispatcher-female',
            'i-dispatcher-male'        => 'Dispatcher-male',
            'i-dna'                    => 'Dna',
            'i-dollar'                 => 'Dollar',
            'i-donate-blood'           => 'Donate-blood',
            'i-excel'                  => 'Excel',
            'i-favorite'               => 'Favorite',
            'i-female'                 => 'Female',
            'i-first-aid'              => 'First-aid',
            'i-flask-holder'           => 'Flask-holder',
            'i-flickr'                 => 'Flickr',
            'i-forward'                => 'Forward',
            'i-fountain'               => 'Fountain',
            'i-fuel-station'           => 'Fuel-station',
            'i-gift'                   => 'Gift',
            'i-github'                 => 'Github',
            'i-glasses'                => 'Glasses',
            'i-globe'                  => 'Globe',
            'i-graduation-cap'         => 'Graduation-cap',
            'i-gurney'                 => 'Gurney',
            'i-gus'                    => 'Gus',
            'i-hand-washing'           => 'Hand-washing',
            'i-headset'                => 'Headset',
            'i-heart-beat'             => 'Heart-beat',
            'i-heart'                  => 'Heart',
            'i-helicopter'             => 'Helicopter',
            'i-hospital-alt'           => 'Hospital-alt',
            'i-hospital'               => 'Hospital',
            'i-hot-drink'              => 'Hot-drink',
            'i-hotspot-mobile'         => 'Hotspot-mobile',
            'i-house-1'                => 'House-1',
            'i-house-2'                => 'House-2',
            'i-house-3'                => 'House-3',
            'i-house-4'                => 'House-4',
            'i-house-5'                => 'House-5',
            'i-house-6'                => 'House-6',
            'i-house-7'                => 'House-7',
            'i-house-8'                => 'House-8',
            'i-hydrant'                => 'Hydrant',
            'i-id-employee-female'     => 'Id-employee-female',
            'i-id-employee-male'       => 'Id-employee-male',
            'i-id-student-female'      => 'Id-student-female',
            'i-id-student-male'        => 'Id-student-male',
            'i-inbox'                  => 'Inbox',
            'i-information'            => 'Information',
            'i-infusion'               => 'Infusion',
            'i-instagram'              => 'Instagram',
            'i-intranet'               => 'Intranet',
            'i-keyboard'               => 'Keyboard',
            'i-kidneys'                => 'Kidneys',
            'i-Lamp'                   => 'Lamp',
            'i-library-1'              => 'Library-1',
            'i-library-2'              => 'Library-2',
            'i-light-bulb'             => 'Light-bulb',
            'i-lightning'              => 'Lightning',
            'i-linkedin'               => 'Linkedin',
            'i-litter'                 => 'Litter',
            'i-liver'                  => 'Liver',
            'i-lock'                   => 'Lock',
            'i-loudspeaker'            => 'Loudspeaker',
            'i-loyalty-card'           => 'Loyalty-card',
            'i-lungs'                  => 'Lungs',
            'i-mail'                   => 'Mail',
            'i-male'                   => 'Male',
            'i-man-woman'              => 'Man-woman',
            'i-map-alt'                => 'Map-alt',
            'i-map-pin'                => 'Map-pin',
            'i-map-screen'             => 'Map-screen',
            'i-map'                    => 'Map',
            'i-medic'                  => 'Medic',
            'i-medical-bag'            => 'Medical-bag',
            'i-medical-symbol'         => 'Medical-symbol',
            'i-medicine-mixing'        => 'Medicine-mixing',
            'i-medicine'               => 'Medicine',
            'i-microscope-1'           => 'Microscope-1',
            'i-mobile-map'             => 'Mobile-map',
            'i-molecule'               => 'Molecule',
            'i-no-phone'               => 'No-phone',
            'i-no-smoking'             => 'No-smoking',
            'i-nuclear-symbol'         => 'Nuclear-symbol',
            'i-nurse'                  => 'Nurse',
            'i-observatory'            => 'Observatory',
            'i-open'                   => 'Open',
            'i-org-tree'               => 'Org-tree',
            'i-paper-clip'             => 'Paper-clip',
            'i-park'                   => 'Park',
            'i-pause'                  => 'Pause',
            'i-pharmaceutical-symbol'  => 'Pharmaceutical-symbol',
            'i-pills'                  => 'Pills',
            'i-pinterest'              => 'Pinterest',
            'i-plant'                  => 'Plant',
            'i-play'                   => 'Play',
            'i-police'                 => 'Police',
            'i-policeman'              => 'Policeman',
            'i-post-office'            => 'Post-office',
            'i-powerpoint'             => 'Powerpoint',
            'i-presentation'           => 'Presentation',
            'i-raining'                => 'Raining',
            'i-recycle'                => 'Recycle',
            'i-reddie-alt'             => 'Reddie-alt',
            'i-reddie'                 => 'Reddie',
            'i-reddie'                 => 'Reddie',
            'i-rewind'                 => 'Rewind',
            'i-rss'                    => 'Rss',
            'i-sale'                   => 'Sale',
            'i-satellite'              => 'Satellite',
            'i-school'                 => 'School',
            'i-screen-1'               => 'Screen-1',
            'i-screen-2'               => 'Screen-2',
            'i-server'                 => 'Server',
            'i-settings'               => 'Settings',
            'i-seven-segment-0'        => 'Seven-segment-0',
            'i-seven-segment-1'        => 'Seven-segment-1',
            'i-seven-segment-2'        => 'Seven-segment-2',
            'i-seven-segment-3'        => 'Seven-segment-3',
            'i-seven-segment-4'        => 'Seven-segment-4',
            'i-seven-segment-5'        => 'Seven-segment-5',
            'i-seven-segment-6'        => 'Seven-segment-6',
            'i-seven-segment-7'        => 'Seven-segment-7',
            'i-seven-segment-8'        => 'Seven-segment-8',
            'i-seven-segment-9'        => 'Seven-segment-9',
            'i-share'                  => 'Share',
            'i-skull'                  => 'Skull',
            'i-skype'                  => 'Skype',
            'i-snow-flake'             => 'Snow-flake',
            'i-snow-rain'              => 'Snow-rain',
            'i-snowing'                => 'Snowing',
            'i-stairs-down'            => 'Stairs-down',
            'i-stairs-up'              => 'Stairs-up',
            'i-stethoscope'            => 'Stethoscope',
            'i-stomach'                => 'Stomach',
            'i-student'                => 'Student',
            'i-sun'                    => 'Sun',
            'i-surveillance'           => 'Surveillance',
            'i-syringe'                => 'Syringe',
            'i-tasks'                  => 'Tasks',
            'i-termometer'             => 'Termometer',
            'i-test-flask'             => 'Test-flask',
            'i-test-tube'              => 'Test-tube',
            'i-thumbs-up'              => 'Thumbs-up',
            'i-thunderstorm'           => 'Thunderstorm',
            'i-ticket'                 => 'Ticket',
            'i-Tools'                  => 'Tools',
            'i-tooth'                  => 'Tooth',
            'i-tornado'                => 'Tornado',
            'i-trafic-light'           => 'Trafic-light',
            'i-trash-can'              => 'Trash-can',
            'i-twitter'                => 'Twitter',
            'i-unlock'                 => 'Unlock',
            'i-video-camera'           => 'Video-camera',
            'i-vimeo'                  => 'Vimeo',
            'i-virus'                  => 'Virus',
            'i-vote'                   => 'Vote',
            'i-war-memorial'           => 'War-memorial',
            'i-weight-libra'           => 'Weight-libra',
            'i-wheelchair'             => 'Wheelchair',
            'i-wind'                   => 'Wind',
            'i-winter-temperature'     => 'Winter-temperature',
            'i-word'                   => 'Word',
            'i-wordpress'              => 'Wordpress',
            'i-youtube-1'              => 'Youtube-1',
            'i-youtube-2'              => 'Youtube-2',
        );
    }

    /**
    * Get ignored icon names
    *
    * @return array
    */
    public static function get_ignored_icon_names()
    {
        return array(
            'noicon',
            'commonicons',
            'uamsicons',
            'misc',
            'allicons',
            'uamsglyph',
            'wpdash',
            'numbers',
        );
    }

    /**
     * Get UAMS brand color collection (css class names)
     *
     * @return array
     */
    public static function get_uams_brand_colors()
    {
        return array(
            'white' => 'White',
            'gray10' => '10% Gray',
            'gray25' => '25% Gray',
            'gray60' => '60% Gray',
            'gray90' => '90% Gray',
            'black' => 'Black',
            'uamsred' => 'Cardinal Red (Primary)',
            'accentgray' => 'Accent Gray',
            'plum' => 'Plum',
            'bluegray' => 'Blue Gray',
            'darkblue' => 'Dark Blue',
            'orange' => 'Orange',
            'lightblue' => 'Light Blue'
        );
    }

    public static function get_uams_brand_colors_plus()
    {
        return array(
            'transparentwhite' => 'Transparent (White Text)',
            'transparentblack' => 'Transparent (Black Text)',
            'transparentred' => 'Transparent (Red Text)',
            'uamsred' => 'Cardinal Red (Primary)',
            'white' => 'White',
            'gray10' => '10% Gray',
            'gray25' => '25% Gray',
            'gray60' => '60% Gray',
            'gray90' => '90% Gray',
            'black' => 'Black',
            'accentgray' => 'Accent Gray',
            'accentgray' => 'Accent Gray',
            'plum' => 'Plum',
            'bluegray' => 'Blue Gray',
            'darkblue' => 'Dark Blue',
            'orange' => 'Orange',
            'lightblue' => 'Light Blue'
        );
    }

    public static function get_accessible_text_brand_colors()
    {
        return array(
            'wolfpackred' => 'Wolfpack Red',
            'gray90' => '90% Gray',
            'black' => 'Black',
            'reynoldsred' => 'Reynolds Red',
            'pyromanflame' => 'Pyroman Flame',
            'genomicgreen' => 'Genomic Green',
            'innovationblue' => 'Innovation Blue',
            'bioindigo' => 'Bio-Indigo',
        );
    }

    /**
     * Allow subclasses to register their own action
     * Fires after the shortcode has been registered on init
     *
     * @return null
     */
    public static function setup_actions()
    {
        // No base actions are necessary
    }

    /**
     * Get ShortCake attributes
     * @return array
     */
    public static function get_attributes()
    {
        return array();
    }

    /**
     * @return array
     */
    public static function get_shortcode_ui_args()
    {
        return array();
    }

    /**
     * Turn embed code into a proper shortcode
     *
     * @param string $content
     * @return string $content
     */
    public static function reversal($content)
    {
        return $content;
    }

    /**
     * Render the shortcode. Remember to always return, not echo
     *
     * @param array $attrs Shortcode attributes
     * @param string $content Any inner content for the shortcode (optional)
     * @return string
     */
    public static function callback($attrs, $content = '')
    {
        return '';
    }

    /** 
     * Added from Shortcake Bakery  
     */
    /**
     * parse_url(), fully-compatible with protocol-less URLs and PHP 5.3
     *
     * @param string $url
     * @param int $component
     * @return mixed
     */
    protected static function parse_url( $url, $component = -1 ) {
        $added_protocol = false;
        if ( 0 === strpos( $url, '//' ) ) {
            $url = 'http:' . $url;
            $added_protocol = true;
        }
        // @codingStandardsIgnoreStart
        $ret = parse_url( $url, $component );
        // @codingStandardsIgnoreEnd
        if ( $added_protocol && $ret ) {
            if ( -1 === $component && isset( $ret['scheme'] ) ) {
                unset( $ret['scheme'] );
            } elseif ( PHP_URL_SCHEME === $component ) {
                $ret = '';
            }
        }
        return $ret;
    }

    /**
     * Parse a string of content for a given tag name.
     *
     * @param string $content
     * @param string $tag_name
     * @return array|false
     */
    private static function parse_closed_tags( $content, $tag_name ) {

        if ( false === stripos( $content, '<' . $tag_name ) ) {
            return false;
        }

        if ( preg_match_all( '#(.+\r?\n?)?(<' . $tag_name . '([^>]+)>([^<]+)?</' . $tag_name . '>)(\r?\n?.+)?#', $content, $matches ) ) {
            $tags = array();
            foreach ( $matches[0] as $key => $value ) {
                $tag = new \stdClass;
                $tag->original = $matches[2][ $key ];
                $tag->before = $matches[1][ $key ];
                $tag->attrs = array(
                    'src' => '',
                );
                $tag->inner = $matches[4][ $key ];
                $tag->after = $matches[5][ $key ];
                $tag->attrs = self::parse_tag_attributes( $matches[3][ $key ] );
                $tags[] = $tag;
            }
            return $tags;
        } else {
            return false;
        }
    }

    /**
     * Parse iframes from a string, if there are any
     *
     * @param string $content
     * @return array|false
     */
    protected static function parse_iframes( $content ) {
        return self::parse_closed_tags( $content, 'iframe' );
    }

    /**
     * Parse script tags from a string, if there are any
     *
     * @param string $content
     * @return array|false
     */
    protected static function parse_scripts( $content ) {
        return self::parse_closed_tags( $content, 'script' );
    }

    /**
     * Parse an attribute string into it's HTML attributes.
     *
     * Uses the regexes defined by WordPress core in `shortcode_parse_atts`.
     *
     * @param str $text list of attributes
     * @return array
     */
    protected static function parse_tag_attributes( $text ) {
        $pattern = '/([\w-]+)\s*=\s*"([^"]*)"(?:\s|$)|([\w-]+)\s*=\s*\'([^\']*)\'(?:\s|$)|([\w-]+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
        $text = preg_replace( "/[\x{00a0}\x{200b}]+/u", ' ', $text );
        $atts = array();

        if ( preg_match_all( $pattern, $text, $match, PREG_SET_ORDER ) ) {
            foreach ( $match as $m ) {
                if ( ! empty( $m[1] ) ) {
                    $atts[ $m[1] ] = stripcslashes( $m[2] );
                } elseif ( ! empty( $m[3] ) ) {
                    $atts[ $m[3] ] = stripcslashes( $m[4] );
                } elseif ( ! empty( $m[5] ) ) {
                    $atts[ $m[5] ] = stripcslashes( $m[6] );
                } elseif ( isset( $m[7] ) && strlen( $m[7] ) ) {
                    $atts[ $m[7] ] = null;
                } elseif ( isset( $m[8] ) ) {
                    $atts[ $m[8] ] = null;
                }
            }
        }

        return $atts;
    }

    /**
     * Make replacements on the string, provided an array of potential replacements
     *
     * @param string $content
     * @param array $replacements
     * @return string
     */
    protected static function make_replacements_to_content( $content, $replacements ) {
        if ( empty( $replacements ) ) {
            return $content;
        }
        return str_replace( array_keys( $replacements ), array_values( $replacements ), $content );
    }

}
