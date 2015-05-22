<?php
/*
Shortcode Example:
[d3-word-cloud title="My Word Cloud" post-types="post,connection" taxonomies="connection-group,connection-link" minimum-count="1" maximum-words="250" orientation="horizontal" font-family="Georgia" font-size="10,100" font-color="green,blue,black" canvas-size="500,500"]
*/


require_once( dirname(__FILE__).'/widget-shortcode-control.php' );


if( !class_exists('D3WordCloud_WidgetShortcodeControl') ):
class D3WordCloud_WidgetShortcodeControl extends WidgetShortcodeControl
{
	
	/**
	 * 
	 */
	public function __construct()
	{
		$widget_ops = array(
			'description'	=> 'Creates a D3 word cloud using selected categories and tags.',
		);
		
		parent::__construct( 'd3-word-cloud', 'D3 Word Cloud', $widget_ops );
	}
	
	
	/**
	 * 
	 */
	public function enqueue_scripts()
	{
		wp_enqueue_script( 'd3-library', plugins_url( '/scripts/d3.min.js' , __FILE__ ), '3.0.min' );
		wp_enqueue_script( 'd3-layout-cloud', plugins_url( '/scripts/d3.layout.cloud.js' , __FILE__ ), array('d3-library'), '1.0.5' );
		wp_enqueue_script( 'd3-word-cloud', plugins_url( '/scripts/cloud.js' , __FILE__ ), array('d3-library', 'd3-layout-cloud'), '1.0.1' );
	}
	
	
	/**
	 *
	 */
	public function print_widget_form( $options )
	{
		$options = $this->merge_options( $options );
		extract( $options );
		
		?>
		
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<br/>
		<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" class="widefat">
		<br/>
		</p>
		
		<p>
		<label for="<?php echo $this->get_field_id( 'post-type' ); ?>"><?php _e( 'Post Type:' ); ?></label> 
		<br/>
		<?php foreach( $all_post_types as $pt ): ?>
			<?php if( in_array($pt->name, $exclude_post_types) ) continue; ?>
			<input type="checkbox" name="<?php echo $this->get_field_name( 'post-types' ); ?>[]" value="<?php echo esc_attr( $pt->name ); ?>" <?php echo ( in_array($pt->name, $post_types) ? 'checked' : '' ); ?> />
			<?php echo $pt->label; ?>
			<br/>
		<?php endforeach; ?>
		</p>

		<p>
		<label for="<?php echo $this->get_field_id( 'taxonomies' ); ?>"><?php _e( 'Taxonomies:' ); ?></label>
		<br/>
		<?php foreach( $all_taxonomies as $tax ): ?>
			<?php if( in_array($tax->name, $exclude_taxonomies) ) continue; ?>
			<input type="checkbox" name="<?php echo $this->get_field_name( 'taxonomies' ); ?>[]" value="<?php echo esc_attr( $tax->name ); ?>" <?php echo ( in_array($tax->name, $taxonomies) ? 'checked' : '' ); ?> />
			<?php echo $tax->label; ?>
			<br/>
		<?php endforeach; ?>
		</p>

		<p>
		<label for="<?php echo $this->get_field_id( 'minimum-count' ); ?>"><?php _e( 'Minimum Post Count:' ); ?></label> 
		<input id="<?php echo $this->get_field_id( 'minimum-count' ); ?>" name="<?php echo $this->get_field_name( 'minimum-count' ); ?>" type="text" value="<?php echo esc_attr( $minimum_count ); ?>">
		</p>
		
		<p>
		<label for="<?php echo $this->get_field_id( 'maximum-words' ); ?>"><?php _e( 'Maximum Words:' ); ?></label> 
		<input id="<?php echo $this->get_field_id( 'maximum-words' ); ?>" name="<?php echo $this->get_field_name( 'maximum-words' ); ?>" type="text" value="<?php echo esc_attr( $maximum_words ); ?>">
		</p>

		<p>
		<label for="<?php echo $this->get_field_id( 'orientation' ); ?>"><?php _e( 'Words Orientation:' ); ?></label> 
		<br/>
		<input type="radio" name="<?php echo $this->get_field_name( 'orientation' ); ?>" value="horizontal" <?php echo ( $orientation == "horizontal" ? 'checked' : '' ); ?> />
		Horizontal
		<br/>
		<input type="radio" name="<?php echo $this->get_field_name( 'orientation' ); ?>" value="vertical" <?php echo ( $orientation == "vertical" ? 'checked' : '' ); ?> />
		Vertical
		<br/>
		<input type="radio" name="<?php echo $this->get_field_name( 'orientation' ); ?>" value="mixed" <?php echo ( $orientation == "mixed" ? 'checked' : '' ); ?> />
		Mixed Horizontal and Vertical
		<br/>
		<input type="radio" name="<?php echo $this->get_field_name( 'orientation' ); ?>" value="mostly-horizontal" <?php echo ( $orientation == "mostly-horizontal" ? 'checked' : '' ); ?> />
		Mostly Horizontal
		<br/>
		<input type="radio" name="<?php echo $this->get_field_name( 'orientation' ); ?>" value="mostly-vertical" <?php echo ( $orientation == "mostly-vertical" ? 'checked' : '' ); ?> />
		Mostly Vertical
		</p>

		<p>
		<label for="<?php echo $this->get_field_id( 'font-family' ); ?>"><?php _e( 'Font Family:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'font-family' ); ?>" name="<?php echo $this->get_field_name( 'font-family' ); ?>" type="text" value="<?php echo esc_attr( $font_family ); ?>" class="widefat">
		</p>

		<p>
		<label for="<?php echo $this->get_field_id( 'font-size' ); ?>"><?php _e( 'Font Size:' ); ?></label> 
		<input type="radio" name="<?php echo $this->get_field_name( 'font-size-type' ); ?>" value="single" <?php echo ( $font_size_type == "single" ? 'checked' : '' ); ?> />
			Single: 
			<input id="<?php echo $this->get_field_id( 'font-size-single' ); ?>" name="<?php echo $this->get_field_name( 'font-size-single' ); ?>" type="text" value="<?php echo esc_attr( $font_size_single ); ?>" class="widefat">
		<input type="radio" name="<?php echo $this->get_field_name( 'font-size-type' ); ?>" value="range" <?php echo ( $font_size_type == "range" ? 'checked' : '' ); ?> />
			Range: 
			<input id="<?php echo $this->get_field_id( 'font-size-range' ); ?>[start]" name="<?php echo $this->get_field_name( 'font-size-range' ); ?>[start]" type="text" value="<?php echo esc_attr( $font_size_range['start'] ); ?>" class="widefat">
			<input id="<?php echo $this->get_field_id( 'font-size-range' ); ?>[end]" name="<?php echo $this->get_field_name( 'font-size-range' ); ?>[end]" type="text" value="<?php echo esc_attr( $font_size_range['end'] ); ?>" class="widefat">
		</p>		

		<p>
		<label for="<?php echo $this->get_field_id( 'font-size' ); ?>"><?php _e( 'Font Color:' ); ?></label> 
		<input type="radio" name="<?php echo $this->get_field_name( 'font-color-type' ); ?>" value="none" <?php echo ( $font_color_type == "none" ? 'checked' : '' ); ?> />
		None
		<input type="radio" name="<?php echo $this->get_field_name( 'font-color-type' ); ?>" value="single" <?php echo ( $font_color_type == "single" ? 'checked' : '' ); ?> />
		Single:
			<input id="<?php echo $this->get_field_id( 'font-color-single' ); ?>" name="<?php echo $this->get_field_name( 'font-color-single' ); ?>" type="text" value="<?php echo esc_attr( $font_color_single ); ?>" class="widefat">
		<input type="radio" name="<?php echo $this->get_field_name( 'font-color-type' ); ?>" value="spanning" <?php echo ( $font_color_type == "spanning" ? 'checked' : '' ); ?> />
		Spanning:
			<input class="widefat" id="<?php echo $this->get_field_id( 'font-color-spanning' ); ?>" name="<?php echo $this->get_field_name( 'font-color-spanning' ); ?>" type="text" value="<?php echo esc_attr( $font_color_spanning ); ?>" class="widefat">
		</p>

		<p>
		<label for="<?php echo $this->get_field_id( 'canvas-size' ); ?>"><?php _e( 'Canvas Size:' ); ?></label> 
		<input id="<?php echo $this->get_field_id( 'canvas-size' ); ?>[width]" name="<?php echo $this->get_field_name( 'canvas-size' ); ?>[width]" type="text" value="<?php echo esc_attr( $canvas_size['width'] ); ?>" class="widefat">
		<input id="<?php echo $this->get_field_id( 'canvas-size' ); ?>[height]" name="<?php echo $this->get_field_name( 'canvas-size' ); ?>[height]" type="text" value="<?php echo esc_attr( $canvas_size['height'] ); ?>" class="widefat">
		</p>
		
		<?php
	}
	
	
	/**
	 *
	 */
	public function get_default_options()
	{
		$defaults = array();

		// title
		$defaults['title'] = '';

		// post types
		$defaults['all-post-types'] = get_post_types( array(), 'objects' );
		$defaults['exclude-post-types'] = array( 'attachment', 'revision', 'nav-menu-item' );
		$defaults['post-types'] = array('post');

		// taxonomy types
		$defaults['all-taxonomies'] = get_taxonomies( array(), 'objects' );
		$defaults['exclude-taxonomies'] = array( 'nav-menu', 'link-category', 'post-format' );
		$defaults['taxonomies'] = array('post-tag');

		// minimum count
		$defaults['minimum-count'] = 1;

		// max words (# or none)
		$defaults['maximum-words'] = 250;
		
		// words orientation
		$defaults['orientation'] = 'horizontal';

		// font-family
		$defaults['font-family'] = 'Arial';

		// font-size (range or single)
		$defaults['font-size-type'] = 'range';
		$defaults['font-size-range'] = array('start' => 10, 'end' => 100);
		$defaults['font-size-single'] = 60;

		// color (spanning, single color, none)
		$defaults['font-color-type'] = 'none';
		$defaults['font-color-single'] = '';
		$defaults['font-color-spanning'] = '';

		// canvas size (height and width)
		$defaults['canvas-size'] = array('width' => 960, 'height' => 420);
		
		return $defaults;
	}
	
	
	/**
	 * 
	 */
	public function process_shortcode_options( $options )
	{
		foreach( $options as $k => &$v )
		{
			$v = trim( $v );
		}
		
		if( array_key_exists('post-types', $options) ) 
			$options['post-types'] = explode( ',', $options['post-types'] );
		
		if( array_key_exists('taxonomies', $options) ) 
			$options['taxonomies'] = explode( ',', $options['taxonomies'] );
		
		if( array_key_exists('minimum-count', $options) && is_int($options['minimum-count']) )
			$options['minimum-count'] = intval( $options['minimum-count'] );
		
		if( array_key_exists('maximum-words', $options) && is_int($options['maximum-words']) )
			$options['maximum-words'] = intval( $options['maximum-words'] );
		
		if( array_key_exists('font-size', $options) )		
			$options['font-size-type'] = 'custom';

		if( array_key_exists('font-color', $options) )		
			$options['font-color-type'] = 'custom';

		if( array_key_exists('canvas-size', $options) )
		{
			$value = explode( ",", $options['canvas-size'] );
			if( count($value) >= 2 )
				$options['canvas-size'] = array( 'width' => $value[0], 'height' => $value[1] );
		}
		
		return $options;
	}
	
	
	/**
	 * 
	 */
	public function merge_options( $options )
	{
		$options = array_merge( $this->get_default_options(), $options );
		
		$opts = array();
		foreach( $options as $k => $v )
		{
			$opts[ str_replace('-', '_', $k) ] = $v;
		}
		
		return $opts;
	}


	/**
	 * 
	 * @param   string  $shortcode  The shortcode found the post's content.
	 * @return  string  The converted shortcode.
	 */
	public function print_control( $options, $args = null )
	{
		$options = $this->merge_options( $options );
		if( !$args ) $args = $this->get_args();
		
		extract( $options );
		
		$terms = get_terms(
			$taxonomies, 
			array( 
				'orderby'	=> 'count', 
				'order'		=> 'DESC', 
				'number'	=> intval($maximum_words),
			)
		);
		
		$tags = array();
		foreach( $terms as $term )
		{
			if( $term->count >= intval($minimum_count) )
			{
				$tags[] = array(
					'name'	=> $term->name,
					'count'	=> $term->count,
					'url'	=> get_term_link( $term ),
				);
			}
		}
		
		echo $args['before_widget'];
		
		if( !empty($title) )
		{
			echo $args['before_title'] . $title . $args['after_title'];
		}
		
		echo '<input type="hidden" class="orientation" value="'.esc_attr($orientation).'" />';
		echo '<input type="hidden" class="font-family" value="'.esc_attr($font_family).'" />';
		
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
		
		echo '<input type="hidden" class="font-size" value="'.esc_attr($font_size).'" />';
		
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
		
		echo '<input type="hidden" class="font-color" value="'.esc_attr($font_color).'" />';
		echo '<input type="hidden" class="tags" value="'.esc_attr(json_encode($tags)).'" />';
		echo '<svg width="'.$canvas_size['width'].'" height="'.$canvas_size['height'].'"></svg>';
		
		echo $args['after_widget'];		
	}
	
}
endif;

