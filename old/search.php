<?php

// Add Shortcode - Search Bar with Shortcake UI
add_action( 'init', function() {
    /**
     * Register your shortcode.
     */
    function ncsu_search_shortcode( $atts , $content = null ) {
	// Attributes
	extract( shortcode_atts(
		array(
			'size' => 'full',
			'background' => 'white',
			'align' => 'center',
			'textalign' => 'center',
			'title' => '',
			'description' => '',
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


/*	$searchform = '<form role="search" method="get" class="search-form form-inline" action="'. esc_url( home_url( '/' ) ) .'">
			<label class="sr-only">'. _x( 'Search for:', 'label' ) .'</label>
			<div class="input-group">
			<input type="search" value="'. get_search_query() .'" name="s" class="search-field form-control" placeholder="'. $label .'">
			<span class="input-group-btn">
			<button type="submit" class="search-submit btn btn-primary"><span class="glyphicon glyphicon-search"></span></button>
			</span>
			</div>
		</form>'; */
		
		
	ob_start();
	get_search_form( );
	$form = ob_get_contents();
	ob_end_clean();

	$searchform = $form;


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
	add_shortcode( 'ncsu-search', 'ncsu_search_shortcode' );
    
} );

?>
