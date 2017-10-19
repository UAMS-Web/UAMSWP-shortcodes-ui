<?php 

// Add Shortcode - 3 Text Columns with Shortcake UI
add_action( 'init', function() {
    /**
     * Register your shortcode.
     */
    function ncsu_textcolumn_shortcode( $atts , $content = null ) {
	// Attributes
	extract( shortcode_atts(
		array(
			'text1' => '',
			'text2' => '',
			'text3' => '',
			'title1' => '',
			'title2' => '',
			'title3' => '',
			'url1' => '',
			'url2' => '',
			'url3' => '',
			'newtab' => 'no',
			'shortcodecustomclass' => '',
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
	}
	$alignstring = '';
	if ($align == 'left') {
		$alignstring = 'element-left';
	} elseif ($align == 'right') {
		$alignstring = 'element-right';
	} else {
		$alignstring = 'element-center';
	}
	$newtabstring = '';
	if ($newtab != 'no') { $newtabstring = ' target="_blank" '; }
	$titlestring1 = '';
	if ($title1 != '') { $titlestring1 = '<h3>'. $title1 .'</h3>'; }
	$urlstring1open = '';
	$urlstring1close = '';
	if ($url1 != '') { $urlstring1open = '<a href="'. $url1 .'" '. $newtabstring .'>';
			  $urlstring1close = '</a>'; }
	$titlestring1 = '';
	if ($title1 != '') { $titlestring1 = '<h3>'. $title1 .'</h3>'; }
	$urlstring1open = '';
	$urlstring1close = '';
	if ($url1 != '') { $urlstring1open = '<a href="'. $url1 .'" '. $newtabstring .'>';
			  $urlstring1close = '</a>'; }
	$titlestring2 = '';
	if ($title2 != '') { $titlestring2 = '<h3>'. $title2 .'</h3>'; }
	$urlstring2open = '';
	$urlstring2close = '';
	if ($url2 != '') { $urlstring2open = '<a href="'. $url2 .'" '. $newtabstring .'>';
			  $urlstring2close = '</a>'; }
	$titlestring3 = '';
	if ($title3 != '') { $titlestring3 = '<h3>'. $title3 .'</h3>'; }
	$urlstring3open = '';
	$urlstring3close = '';
	if ($url3 != '') { $urlstring3open = '<a href="'. $url3 .'" '. $newtabstring .'>';
			  $urlstring3close = '</a>'; }
	return '
	<div class="legacy-shortcode">
	<div class="container '. $shortcodecustomclass .'">
	<div class="row">
	<div class="shortcode-textcolumn col-lg-4 col-md-4 col-xs-12 col-sm-4">
		'. $urlstring1open . $titlestring1 . $urlstring1close .'
		<p>'. $text1 .'</p>
	</div> <!-- .shortcode-textcolumn -->
	<div class="shortcode-textcolumn col-lg-4 col-md-4 col-xs-12 col-sm-4">
		'. $urlstring2open . $titlestring2 . $urlstring2close .'
		<p>'. $text2 .'</p>
	</div> <!-- .shortcode-textcolumn -->
	<div class="shortcode-textcolumn col-lg-4 col-md-4 col-xs-12 col-sm-4">
		'. $urlstring3open . $titlestring3 . $urlstring3close .'
		<p>'. $text3 .'</p>
	</div> <!-- .shortcode-textcolumn -->
	</div><!-- .row -->
	</div><!-- .container -->
	</div>
	';
	}
	add_shortcode( 'ncsu-textcolumn', 'ncsu_textcolumn_shortcode' );
} );

?>
