<?php

/**
 * Map Shortcode
 *
 * Generates a map
 * from maps.uams.edu
 */

namespace UAMS_Shortcakes\Shortcodes;

/**
 * Class Map
 * @package UAMS_Shortcakes\Shortcodes
 */
class Map extends Shortcode
{

    /**
     * declare default attributes
     *
     * @var array
     */
    private static $defaults = array(
        'building' => null,
        'width' => '100%',
        'height' => '480px',
        'customclass' => null
    );

     private static $buildingcode = array('127','116','117','118','119','120','121','122','123','124','125','128','129','126','131','130','132','133','134','135','136','137','138','139','141','142','143','144','145','146','147','148','149','150','151','152','153','154','155','2','3','4','7','6');

    const URL = '//maps.uams.edu/full-screen/?markerid=';

    /**
     * gets the arguments needed for shortcake
     *
     * @return array
     */
    public static function get_shortcode_ui_args()
    {
        return array(
            'label' => esc_html__('UAMS Map', 'uams_shortcodes'),
            'listItemImage' => 'dashicons-location-alt',
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
                'label' => esc_html__('Building', 'uams_shortcodes'),
                'attr' => 'building',
                'type' => 'select',
                'description' => '',
                'encode' => false,
                'options' => array(
                    '127' => '12th St. Clinic',
                    '116' => 'Administration West (ADMINW)',
                    '117' => 'Barton Research (BART)',
                    '118' => 'Biomedical Research Center I (BMR1)',
                    '119' => 'Biomedical Research Center II (BMR2)',
                    '120' => 'Bioventures (BVENT)',
                    '121' => 'Boiler House (BH)',
                    '122' => 'Central Building (CENT)',
                    '123' => 'College of Public Health (COPH)',
                    '124' => 'Computer Building (COMP)',
                    '125' => 'Cottage 3 (C3)',
                    '128' => 'Distribution Center (DIST)',
                    '129' => 'Donald W. Reynolds Institute on Aging (RIOA)',
                    '126' => 'Ear Nose Throat (ENT)',
                    '131' => 'Education Building South (EDS)',
                    '130' => 'Education II (EDII)¬†',
                    '132' => 'Family Medical Center (FMC)',
                    '133' => 'Freeway Medical Tower (FWAY)',
                    '134' => 'Harvey and Bernice Jones Eye Institute (JEI)',
                    '135' => 'Hospital (HOSP)',
                    '136' => 'I. Dodd Wilson Education Building (IDW)',
                    '137' => 'Jackson T. Stephens Spine Institute (JTSSI)',
                    '138' => 'Magnetic Resonance Imaging (MRI)',
                    '139' => 'Mediplex Apartments (1 unit) (MEDPX)',
                    '141' => 'Outpatient Center (OPC)',
                    '142' => 'Outpatient Diagnostic Center (OPDC)',
                    '143' => 'Paint Shop & Flammable Storage (PAINT)',
                    '144' => 'PET (PET)',
                    '145' => 'Physical Plant (PP)',
                    '146' => 'Psychiatric Research Institute (PRI)',
                    '147' => 'Radiation Oncology [ROC] (RADONC)',
                    '148' => 'Residence Hall Complex (RHC)',
                    '149' => 'Ricks Armory',
                    '150' => 'Walker Annex (ANNEX)',
                    '151' => 'Ward Tower (WARD)',
                    '152' => 'West Central Energy Plant (WCEP)',
                    '153' => 'Westmark (WESTM)',
                    '154' => 'Winston K. Shorey Building (SHOR)',
                    '155' => 'Winthrop P. Rockefeller Cancer Institute (WPRCI)',
                    '2' => 'Parking Deck 1 Entrance',
                    '7' => 'Parking Deck 1 Entrance',
                    '3' => 'Parking Deck 2 Entrance',
                    '4' => 'Parking Deck 3 Entrance',
                    '6' => 'Stephens Institute Valet Parking',
                )
            ),
            array(
                'label' => esc_html__('Width', 'uams_shortcodes'),
                'attr' => 'width',
                'type' => 'text',
                'description' => 'Width in % or px (Default 100%)',
                'encode' => false,
                'meta' => array(
                    'placeholder' => '100%'
                )
            ),
            array(
                'label' => esc_html__('Height', 'uams_shortcodes'),
                'attr' => 'height',
                'type' => 'text',
                'description' => 'Height in px (Defualt 480px)',
                'meta' => array(
                    'placeholder' => '480px'
                )
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

    /**
     * return (not echo) our button element
     *
     * @param array $attrs
     * @param string $content
     * @return string
     */
    public static function callback($attrs, $content = '')
    {
        $attrs = (is_array($attrs))
            ? array_merge(self::$defaults, $attrs)
            : self::$defaults;
        
        $customclasses = array();

        if (isset($attrs['building'])){
            $building = esc_attr($attrs['building']);
            if (!in_array($building, self::$buildingcode)){
                return sprintf('Building "%s" is not supported', $building);
            }
        }
        else {
            return 'required attribute "building" missing';
        }

        $width = '100%';
        if (isset($attrs['width'])){
            $width = $attrs['width'];
        }

        $height = '480px';
        if (isset($attrs['height'])){
            $height = $attrs['height'];
        }

        if (!empty($attrs['customclass'])) {
            $customclasses = explode( ",", $attrs['customclass'] );
        }

        $return = '<div class="uams-campus-map '. implode(' ', $customclasses) .'">
                  <iframe width="'. $width .'" height="'. $height .'" src="'.self::URL.$building.'" frameborder="0"></iframe>
                  <a href="https://maps.uams.edu/map-mashup/?markerid='.$building.'" target="_blank">View full map</a>
                </div>';

        return $return;

        

        // return sprintf(
        //     "<a class=\"uams-btn %s %s\" href=\"%s\" target=\"%s\">%s</a>%s",
        //     implode(' ', $button_classes),
        //     implode(' ', $customclasses),
        //     esc_url($attrs['url']),
        //     $attrs['target'],
        //     $attrs['text'],
        //     $newline
        // );

        //return  $shortcode;//'<div class="uams-button '. esc_attr( $customclass ) .'">' . do_shortcode($shortcode) . '</div>';
    }
}
