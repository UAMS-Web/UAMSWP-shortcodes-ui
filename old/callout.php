<?php 

/* Callout image sizes */
add_image_size( 'callout-1500', 1500, 530, true );
add_image_size( 'callout-992', 992, 400, true );
add_image_size( 'callout-768', 768, 350, true );
add_image_size( 'callout-480', 480, 300, true );
add_image_size( 'callout-320', 320, 250, true );





add_action( 'init', function() {

// Add Shortcode

	function ncsu_callout_shortcode( $atts , $inner_content = null ) {

		// Attributes
	
		extract( shortcode_atts(
			array(
				'callout_content' => '',
				'header' => '',
				'desturl' => '',
				'font' => '',
				'headerfont' => '',
				'textalign' => '',
				'color' => 'wolfpackred',
				'size' => 'full',
				'align' => 'center',
				'img' => '',
				'imgdisplay' => '',
				'textblockwidth' => '',
				'textblockplacement' => '',
				'displaycaption' => '',
				'shortcodecustomclass' => '',
			), $atts )
		);

		// Code

			$sizestring = '';
			$closecontainer = '';
			$opencontainer = '';

			if ($size == 'full') {
				$sizestring = 'col-lg-12 col-md-12 col-xs-12 col-sm-12';
			} elseif ($size == 'half') {
				$sizestring = 'col-lg-6 col-md-6 col-xs-12 col-sm-6';
			} elseif ($size == 'third') {
				$sizestring = 'col-lg-4 col-md-4 col-xs-12 col-sm-4';
			} elseif ($size == 'browser') {
				$sizestring = 'container-fluid col-lg-12 col-md-12 col-xs-12 col-sm-12';
				$closecontainer = '</div><!-- .container -->';
				$opencontainer = '<div class="container">';
			}

			$alignstring = '';

			if ($align == 'left') {
				$alignstring = 'element-left';
			} elseif ($align == 'right') {
				$alignstring = 'element-right';
			} else {
				$alignstring = 'element-center';
			}

			$urlopen = '';
			$urlclose = '';
			$urlarrow = '';
			if ( $desturl != '' ) {
				$urlopen = '<a href="'. $desturl .'" class="ncsu-callout-link">';
				$urlclose = '</a>';
				$urlarrow = '<span class="ncsu-icons url-arrow" aria-hidden="true"></span>';
			} else {
			
			}

			/* Get URLs for callout images */
			$imgsrc_1500 = wp_get_attachment_image_src( $img, "callout-1500" );
			$imgsrc_992 = wp_get_attachment_image_src( $img, "callout-992" );
			$imgsrc_768 = wp_get_attachment_image_src( $img, "callout-768" );
			$imgsrc_480 = wp_get_attachment_image_src( $img, "callout-480" );
			$imgsrc_320 = wp_get_attachment_image_src( $img, "callout-320" );
			
			$imgtext = get_post_meta($img, '_wp_attachment_image_alt', true);

			$picture = $urlopen .'<picture class="ncsu-callout-img '. $imgdisplay .' '. $size .'" alt="'. $imgtext .'">
							
							<source srcset="'. $imgsrc_768[0] .'" media="(min-width: 1500px)">
							<source srcset="'. $imgsrc_480[0] .'" media="(min-width: 1200px)">
							<source srcset="'. $imgsrc_480[0] .'" media="(min-width: 992px)">
							<source srcset="'. $imgsrc_768[0] .'" media="(min-width: 768px)">
							<source srcset="'. $imgsrc_768[0] .'" media="(min-width: 480px)">
							<source srcset="'. $imgsrc_768[0] .'" media="(min-width: 320px)">
							<source srcset="'. $imgsrc_768[0] .'">
							
							<img src="'. $imgsrc_1500[0] .'" class="img-responsive" alt="'. $imgtext .'" />
						</picture>'. $urlclose;
											
			$withimg = '';
			$imgstring = '';
			if ( $img != '' && $imgdisplay != 'bgimg' ) {
				$withimg = 'ncsu-callout-withimg';
				$imgstring = $picture;
			} else {}
			
			$bgposition = '';
			if ( $imgdisplay == 'bgimg' ) {
			
				if ( $textblockplacement == 'textbottomleft' ) {
					$bgposition = 'background-position: center bottom;';
				} elseif ( $textblockplacement == 'textbottomright' ) {
					$bgposition = 'background-position: center bottom;';
				} elseif ( $textblockplacement == 'texttopleft' ) {
					$bgposition = 'background-position: center bottom;';
				} elseif ( $textblockplacement == 'texttopright' ) {
					$bgposition = 'background-position: center top;';
				}
			
			}
			
			$withbg = '';
			$bgimgstring = '';
			if ( $img != '' && $imgdisplay == 'bgimg' ) {
				$withbg = 'ncsu-callout-withbg';
				$bgimgstring = '<img src="'. $imgsrc_1500[0] .'" class="callout-bg-img" alt="'. $imgtext .'" />';
//				$bgimgstring = 'style="background: url('. $imgsrc_1500[0] .'); '. $bgposition .'"';
			}

			$imgtitle = get_the_title($img);
			$imgcaption = get_post_field('post_excerpt', $img);
			$imgdatastring = '';
			
			if ( $img != '' && $displaycaption == 'true' ) {
				$imgdatastring = '<div class="container ncsu-callout-image-data">
									<strong>'. $imgtitle .'</strong><br />
									'. $imgcaption .'
								</div><!-- .ncsu-callout-image-data -->';
			} else {}

			$headerstring = '';
			
			if ( $header != '' ) {
				$headerstring = '<h2 class="ncsu-callout-header '. $headerfont .'">'. $header .'</h2>';
			} else {}

			$bodystring = '';
			
			if ( $callout_content != '' ) {
				$bodystring = '<p class="ncsu-callout-body">'. $callout_content .' '. $urlarrow .'</p>';
			} else {}
			
			
			$textblockwidthstring = '';
			if ( $imgdisplay == 'bgimg' ) {
			
				if ( $textblockwidth == 'textwidth25' ) {
					$textblockwidthstring = 'col-lg-3 col-md-6 col-sm-11 col-xs-11';
				} elseif ( $textblockwidth == 'textwidth33' ) {
					$textblockwidthstring = 'col-lg-4 col-md-6 col-sm-11 col-xs-11';
				} elseif ( $textblockwidth == 'textwidth50' ) {
					$textblockwidthstring = 'col-lg-6 col-md-8 col-sm-11 col-xs-11';
				} elseif ( $textblockwidth == 'textwidth66' ) {
					$textblockwidthstring = 'col-lg-8 col-md-11 col-sm-11 col-xs-11';
				} elseif ( $textblockwidth == 'textwidth75' ) {
					$textblockwidthstring = 'col-lg-9 col-md-11 col-sm-11 col-xs-11';
				} elseif ( $textblockwidth == 'textwidth100' ) {
					$textblockwidthstring = 'col-lg-11 col-md-11 col-sm-11 col-xs-11';
				}
			
			} else {}	
			

			$callout_output = '';
			
			$callout_output .= '<div class="legacy-shortcode"><div class="ncsu-callout-wrapper '. $shortcodecustomclass .' '. $sizestring .' '. $alignstring .' '. $color .'">';
			$callout_output .= $bgimgstring;
			$callout_output .= '<div class="ncsu-callout container '. $font .' '. $textalign .' '. $withbg .' '. $size .'">';
			$callout_output .= $imgstring;
			
			if ($headerstring != '' || $bodystring != '') {
				$callout_output .= '<div class="ncsu-callout-content '. $withimg .' '. $textblockwidthstring .' '. $textblockplacement .' '. $color .'">';
				$callout_output .= $urlopen;
				$callout_output .= $headerstring;
				$callout_output .= $bodystring;
				$callout_output .= $urlclose;
				$callout_output .= '</div><!-- .ncsu-callout-content -->';
			}
			
			$callout_output .= '</div><!-- .ncsu-callout -->';
			$callout_output .= '</div><!-- .ncsu-callout-wrapper -->';
			$callout_output .= $imgdatastring . '</div>';
	
		// Output
	
		return $callout_output;
	
	}

	add_shortcode( 'ncsu-callout', 'ncsu_callout_shortcode' );


// End Shortcode
} );

?>
