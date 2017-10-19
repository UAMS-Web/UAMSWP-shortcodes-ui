<?php 

add_action( 'init', function() {

// Add Shortcode

	function ncsu_recentposts_shortcode( $atts , $inner_content = null ) {

		// Attributes
	
		extract( shortcode_atts(
			array(
				'numberofposts' => '',
				'header' => '',
			), $atts )
		);

		// Code

			$sizestring = '';

			if ($size == 'full') {
				$sizestring = 'col-lg-12 col-md-12 col-xs-12 col-sm-12';
			} elseif ($size == 'half') {
				$sizestring = 'col-lg-6 col-md-6 col-xs-12 col-sm-6';
			} elseif ($size == 'third') {
				$sizestring = 'col-lg-4 col-md-4 col-xs-12 col-sm-4';
			} else {}

			$alignstring = '';

			if ($align == 'left') {
				$alignstring = 'element-left';
			} elseif ($align == 'right') {
				$alignstring = 'element-right';
			} else {
				$alignstring = 'element-center';
			}

			$headerstring = '';
			if ( $header != '' ) {
				$headerstring = '<h3>'. $header .'</h3>';
			} else {}

			
			$recentposts_output = '';
			
			$callout_output .= '<div class="ncsu-recentposts-wrapper '. $sizestring .' '. $alignstring .'">';
			$callout_output .= $headerstring;
			$callout_output .= '</div><!-- .ncsu-recentposts-wrapper -->';
	
		// Output
	
		return '<div class="legacy-shortcode">'. $recentposts_output .'</div>';
	
	}

	add_shortcode( 'ncsu-recentposts', 'ncsu_recentposts_shortcode' );


// End Shortcode

} );

?>