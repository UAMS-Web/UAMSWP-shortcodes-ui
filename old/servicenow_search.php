<?php

// Add Shortcode - Search Bar with Shortcake UI
add_action( 'init', function() {
    /**
     * Register your shortcode.
     */
    function ncsu_servicenow_search_shortcode( $atts , $content = null ) {
	// Attributes
	extract( shortcode_atts(
		array(
			'size' => 'full',
			'background' => 'white',
			'align' => 'center',
			'textalign' => 'center',
			'title' => '',
			'description' => '',
			'workgroup' => '',
			'shortcodecustomclass' => '',
		), $atts )
	);
	// Code

	$closecontainer = '';
	$opencontainer = '';

	$sizestring = '';
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

	$textalignstring = '';
	if ($textalign == 'left') {
		$textalignstring = 'text-left';
	} elseif ($textalign == 'right') {
		$textalignstring = 'text-right';
	} elseif ($textalign == 'center') {
		$textalignstring = 'text-center';
	}

	$titlestring = '';
	if ($title != '') { $titlestring = '<h2>'. $title .'</h2>'; }
	$descstring = '';
	if ($description != '') { $descstring = '<p>'. $description .'</p>'; }

	$searchform = '<form role="search" method="post" class="search-form form-inline" action="https://ncsu.service-now.com/kb_find.do" method="get" target="_blank">
	<label class="sr-only">'. _x( 'Search for:', 'label' ) .'</label>
	<div class="input-group">
		<input type="hidden" name="sysparm_base_form" value="ui_page_render">
		<input type="hidden" name="sysparm_operator" value="IR_AND_OR_QUERY">
	    <input type="hidden" name="workgroup" value="'. $workgroup .'">
	    <input type="hidden" name="maxhits" value="10">
    	<input type="text" class="search-field form-control" name="sysparm_search" value="">
    	<span class="input-group-btn">
	    	<button type="submit" class="search-submit btn btn-primary" name="sa"><span class="glyphicon glyphicon-search">
	    	</span></button>
    	</span>
    </div>
</form>';

	return '
	<div class="legacy-shortcode">
	<div class="shortcode-search '. $shortcodecustomclass .' '. $sizestring .' '. $alignstring .' '. $textalignstring .' '. $background .'">
		'. $titlestring .'
		'. $descstring .'
		'. $searchform .'
	</div> <!-- .shortcode-search -->
	</div>
	';
	}
	add_shortcode( 'ncsu-servicenow-search', 'ncsu_servicenow_search_shortcode' );
    
} );

?>
