<?php

add_action( 'init', function() {
    /**
     * Register your shortcode.
     */
    function ncsu_button_shortcode( $atts , $content = null ) {
	// Attributes
	extract( shortcode_atts(
		array(
			'color' => 'wolfpackred',
			'btn_content' => '',
			'url' => '#',
			'icon' => '',
			'iconposition' => 'left',
			'size' => 'btn-lg',
			'displayblock' => 'false',
			'target' => 'self',
			'shortcodecustomclass' => '',
		), $atts )
	);
	// Code
		$block = '';
		$blockwrap = '';
        if ($displayblock == 'true') {
               $block = 'btn-block';
               $blockwrap = 'block';
        } else {
               $block = '';
        }
        
        if ($target == null || $target == '') {
        	$target = 'self';
        }
        
        $iconstring = '';
        if ( $icon != '' ) {
        	$iconstring = '<i class="ncsu-icons '. $iconposition .'" aria-hidden="true">'. $icon .'</i>';
        } else {}
        
	return '<div class="legacy-shortcode"><div class="ncsu-button-wrapper '. $shortcodecustomclass .' '. $blockwrap .'"><a class="btn ncsu-button '. $color .' '. $size .' '. $block .'" href="'. $url .'" target="_'. $target .'">'. $btn_content .' '. $iconstring .'</a></div><!-- .ncsu-button-wrapper --></div>';
	}
	add_shortcode( 'ncsu-button', 'ncsu_button_shortcode' );
    
} );

?>
