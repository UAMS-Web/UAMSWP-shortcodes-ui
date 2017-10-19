<?php

// Add Shortcode - ServiceNow Knowledge Base Article with Shortcake UI
add_action( 'init', function() {
    /**
     * Register your shortcode.
     */
    function ncsu_kb_article_shortcode( $atts , $content = null ) {
	// Attributes
	extract( shortcode_atts(
		array(
			'kbid' => '',
			'size' => 'full',
			'align' => 'center',
			'title' => '',
			'description' => '',
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

	$titlestring = '';
	if ($title != '') { $titlestring = '<h2>'. $title .'</h2>'; }
	$descstring = '';
	if ($description != '') { $descstring = '<p>'. $description .'</p><hr />'; }

$kb = '';
$url = 'https://ncsu.service-now.com/kb_view_customer.do?sysparm_article=REM-'. $kbid;

//if ( ! empty( file_get_contents($url) ) ) {
//	$content = file_get_contents($url);
//	$first_step = explode( '<td valign="top" class="kb_text">' , $content );
//	$second_step = explode("</td>" , $first_step[1] );

//	$kb = $second_step[0];

//} else {}

//	$article = file_get_contents('https://ncsu.service-now.com/kb_view_customer.do?sysparm_article=REM-KB0003779');
//	$article_output = strip_tags($article, '<em><strong><h1><h2><h3><h4><img><a><p><br>');


	return '
	<div class="legacy-shortcode">
	<div class="ncsu-servicenow-kb-article '. $shortcodecustomclass .'">
		'. $titlestring .'
		'. $descstring .'
		'. $kb .'
		<a href="'. $url .'" target="_blank">View in Service Now.</a>
	</div> <!-- .ncsu-servicenow-kb-article -->
	</div>
	';
	}
	add_shortcode( 'ncsu-kb-article', 'ncsu_kb_article_shortcode' );
    
} );

?>