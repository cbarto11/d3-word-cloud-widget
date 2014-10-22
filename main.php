<?php
/*
Plugin Name: D3 Word Cloud
Plugin URI: 
Description: 
Version: 0.0.1
Author: Crystal Barton
Author URI: 
*/


/*
Shortcode Example:

[d3-word-cloud title="My Word Cloud" post-types="post,connection" taxonomies="connection-group,connection-link" minimum-count="1" maximum-words="250" orientation="horizontal" font-family="Georgia" font-size="10,100" font-color="green,blue,black" canvas-size="500,500"]
*/


require_once( dirname(__FILE__).'/widget.php' );
add_action( 'wp_enqueue_scripts', array( 'D3_WordCloud', 'enqueue_scripts' ) );
add_filter( 'the_content', array('D3_WordCloud', 'process_content_shortcode') );


class D3_WordCloud
{
	
	private static $index = 0;
	
	public static function enqueue_scripts()
	{
		wp_enqueue_script( 'd3-library', plugins_url( '/scripts/d3.min.js' , __FILE__ ), '3.0.min' );
		wp_enqueue_script( 'd3-layout-cloud', plugins_url( '/scripts/d3.layout.cloud.js' , __FILE__ ), array('d3-library'), '1.0.5' );
		wp_enqueue_script( 'd3-word-cloud', plugins_url( '/scripts/cloud.js' , __FILE__ ), array('d3-library', 'd3-layout-cloud'), '1.0.1' );
	}
	
	
	public static function process_content_shortcode( $content )
	{
		$matches = NULL;
		$num_matches = preg_match_all("/\[d3-word-cloud(.*)\]/", $content, $matches, PREG_SET_ORDER);

		if( ($num_matches !== FALSE) && ($num_matches > 0) )
		{
			for( $i = 0; $i < $num_matches; $i++ )
			{
				self::$index = $i;
				$content = str_replace($matches[$i][0], self::get_content( $matches[$i][0] ), $content);
			}
		}
		
		return $content;
	}
	
	
	private static function get_content( $shortcode )
	{
		//
		// default settings
		//
		$defaults = self::get_defaults();
		$options = $defaults;


		$matches = NULL;
		if( preg_match("/title=\"([^\"]+)\"/", $shortcode, $matches) )
			$options['title'] = trim($matches[1]);
			
		$matches = NULL;
		if( preg_match("/post-types=\"([^\"]+)\"/", $shortcode, $matches) )
			$options['post_types'] = explode( ",", trim($matches[1]) );

		$matches = NULL;
		if( preg_match("/taxonomies=\"([^\"]+)\"/", $shortcode, $matches) )
			$options['taxonomies'] = explode( ",", trim($matches[1]) );
			
		$matches = NULL;
		if( preg_match("/minimum-count=\"([^\"]+)\"/", $shortcode, $matches) )
			$options['minimum_count'] = is_int( $matches[1] ) ? intval( $matches[1] ) : $defaults['minimum_count'];

		$matches = NULL;
		if( preg_match("/maximum-words=\"([^\"]+)\"/", $shortcode, $matches) )
			$options['maximum_words'] = is_int( $matches[1] ) ? intval( $matches[1] ) : $defaults['maximum_words'];

		$matches = NULL;
		if( preg_match("/orientation=\"([^\"]+)\"/", $shortcode, $matches) )
			$options['orientation'] = $matches[1];

		$matches = NULL;
		if( preg_match("/font-family=\"([^\"]+)\"/", $shortcode, $matches) )
			$options['font_family'] = trim($matches[1]);

		$matches = NULL;
		if( preg_match("/font-size=\"([^\"]+)\"/", $shortcode, $matches) )
		{
			$options['font_size_type'] = 'custom';
			$options['font_size'] = trim($matches[1]);
		}
		
		$matches = NULL;
		if( preg_match("/font-color=\"([^\"]+)\"/", $shortcode, $matches) )
		{
			$options['font_color_type'] = 'custom';
			$options['font_color'] = trim($matches[1]);
		}

		$matches = NULL;
		if( preg_match("/canvas-size=\"([^\"]+)\"/", $shortcode, $matches) )
		{
			$value = explode( ",", trim($matches[1]) );
			if( count($value) >= 2 )
				$options['canvas_size'] = array( 'width' => $value[0], 'height' => $value[1] );
		}
		
		$options['title'] = ( !empty($options['title']) ? '<h2>'.$options['title'].'</h2>' : '' );
		
		ob_start();
		echo '<div class="d3-word-cloud">';
		self::create_word_cloud( 'word-cloud-shortcode-'.self::$index, $options );
		echo '</div>';
		$buffer = ob_get_contents();
		ob_end_clean();
		
		return $buffer;
	}
	
	
	
	public static function get_defaults()
	{
		$defaults = array();

		// title
		$defaults['title'] = '';

		// post types
		$defaults['all_post_types'] = get_post_types( array(), 'objects' );
		$defaults['exclude_post_types'] = array( 'attachment', 'revision', 'nav_menu_item' );
		$defaults['post_types'] = array('post');

		// taxonomy types
		$defaults['all_taxonomies'] = get_taxonomies( array(), 'objects' );
		$defaults['exclude_taxonomies'] = array( 'nav_menu', 'link_category', 'post_format' );
		$defaults['taxonomies'] = array('post_tag');

		// minimum count
		$defaults['minimum_count'] = 1;

		// max words (# or none)
		$defaults['maximum_words'] = 250;
		
		// words orientation
		$defaults['orientation'] = 'horizontal';

		// font-family
		$defaults['font_family'] = 'Arial';

		// font-size (range or single)
		$defaults['font_size_type'] = 'range';
		$defaults['font_size_range'] = array('start' => 10, 'end' => 100);
		$defaults['font_size_single'] = 60;

		// color (spanning, single color, none)
		$defaults['font_color_type'] = 'none';
		$defaults['font_color_single'] = '';
		$defaults['font_color_spanning'] = '';

		// canvas size (height and width)
		$defaults['canvas_size'] = array('width' => 960, 'height' => 420);
		
		return $defaults;
	}
	
	
	public static function create_word_cloud( $id, $options )
	{
		extract($options);
		
		$terms = get_terms( $taxonomies, 
			array( 
				'orderby' => 'count', 
				'order' => 'DESC', 
				'number' => intval($maximum_words),
			)
		);
		
		$tags = array();
		foreach( $terms as $term )
		{
			if( $term->count >= intval($minimum_count) )
			{
				$tags[] = array(
					'name' => $term->name,
					'count' => $term->count,
					'url' => get_term_link( $term ),
				);
			}
		}
		
		?>
		
		<?php echo $title ?>

		<div id="<?php echo $id; ?>" class="d3-word-cloud-container">

		<?php //D3_WordCloud_Widget::$id++; ?>
		
		<input type="hidden" class="orientation" value="<?php echo esc_attr($orientation); ?>" />
		<input type="hidden" class="font-family" value="<?php echo esc_attr($font_family); ?>" />
		
		<?php 
// 		$font_size = '';
		switch( $font_size_type )
		{
			case( "range" ):
				$font_size = $font_size_range['start'].','.$font_size_range['end'];
				break;
				
			case( "custom" ):
				break;
				
			case( "single" ):
			default:
				$font_size = $font_size_single;
				break;

		}
		?>
		<input type="hidden" class="font-size" value="<?php echo esc_attr($font_size); ?>" />
		
		<?php
// 		$font_color = '';
		switch( $font_color_type )
		{
			case( "spanning" ):
				$font_color = $font_color_spanning;
				break;
				
			case( "single" ):
				$font_color = $font_color_single;
				break;
			
			case( "custom" ): 
				break;
			
			case( "none" ):
			default:
				$font_color = 'black';
				break;
		}
		?>
		<input type="hidden" class="font-color" value="<?php echo esc_attr($font_color); ?>" />

		<input type="hidden" class="tags" value="<?php echo esc_attr(json_encode($tags)); ?>" />
		
		<svg width="<?php echo $canvas_size['width']; ?>" height="<?php echo $canvas_size['height']; ?>"></svg>
		
		</div>
		<?php
	}
	
}


