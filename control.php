<?php
/**
 * D3WordCloud_WidgetShortcodeControl
 * 
 * The D3WordCloud_WidgetShortcodeControl class for the "D3 Word Cloud" plugin.
 * Derived from the official WP RSS widget.
 * 
 * Shortcode Example:
 * [d3_word_cloud title="My Word Cloud" post_types="post,connection" taxonomies="connection-group,connection-link" minimum_count="1" maximum_words="250" orientation="horizontal" font_family="Georgia" font-size="10,100" font-color="green,blue,black" canvas_size="500,500"]
 * 
 * @package    clas-buttons
 * @author     Crystal Barton <cbarto11@uncc.edu>
 */


require_once( dirname(__FILE__).'/widget-shortcode-control.php' );


if( !class_exists('D3WordCloud_WidgetShortcodeControl') ):
class D3WordCloud_WidgetShortcodeControl extends WidgetShortcodeControl
{
	
	/**
	 * Constructor.
	 * Setup the properties and actions.
	 */
	public function __construct()
	{
		$widget_ops = array(
			'description'	=> 'Creates a D3 word cloud using selected categories and tags.',
		);
		
		parent::__construct( 'd3-word-cloud', 'D3 Word Cloud', $widget_ops );
	}
	
	
	/**
	 * Enqueues the scripts or styles needed for the control in the site frontend.
	 */
	public function enqueue_scripts()
	{
		wp_enqueue_script( 'd3-library', plugins_url( '/scripts/d3.min.js' , __FILE__ ), '3.0.min' );
		wp_enqueue_script( 'd3-layout-cloud', plugins_url( '/scripts/d3.layout.cloud.js' , __FILE__ ), array('d3-library'), '1.0.5' );
		wp_enqueue_script( 'd3-word-cloud', plugins_url( '/scripts/cloud.js' , __FILE__ ), array('d3-library', 'd3-layout-cloud'), '1.0.1' );
	}
	
	
	/**
	 * Output the widget form in the admin.
	 * Use this function instead of form.
	 * @param   array   $options  The current settings for the widget.
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
		<label for="<?php echo $this->get_field_id( 'post_type' ); ?>"><?php _e( 'Post Type:' ); ?></label> 
		<br/>
		<?php foreach( $all_post_types as $pt ): ?>
			<?php if( in_array($pt->name, $exclude_post_types) ) continue; ?>
			<input type="checkbox" name="<?php echo $this->get_field_name( 'post_types' ); ?>[]" value="<?php echo esc_attr( $pt->name ); ?>" <?php echo ( in_array($pt->name, $post_types) ? 'checked' : '' ); ?> />
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
		<label for="<?php echo $this->get_field_id( 'minimum_count' ); ?>"><?php _e( 'Minimum Post Count:' ); ?></label> 
		<input id="<?php echo $this->get_field_id( 'minimum_count' ); ?>" name="<?php echo $this->get_field_name( 'minimum_count' ); ?>" type="text" value="<?php echo esc_attr( $minimum_count ); ?>">
		</p>
		
		<p>
		<label for="<?php echo $this->get_field_id( 'maximum_words' ); ?>"><?php _e( 'Maximum Words:' ); ?></label> 
		<input id="<?php echo $this->get_field_id( 'maximum_words' ); ?>" name="<?php echo $this->get_field_name( 'maximum_words' ); ?>" type="text" value="<?php echo esc_attr( $maximum_words ); ?>">
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
		<label for="<?php echo $this->get_field_id( 'font_family' ); ?>"><?php _e( 'Font Family:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'font_family' ); ?>" name="<?php echo $this->get_field_name( 'font_family' ); ?>" type="text" value="<?php echo esc_attr( $font_family ); ?>" class="widefat">
		</p>

		<p>
		<label for="<?php echo $this->get_field_id( 'font_size' ); ?>"><?php _e( 'Font Size:' ); ?></label> 
		<br/>
		<input type="radio" name="<?php echo $this->get_field_name( 'font_size_type' ); ?>" value="single" <?php echo ( $font_size_type == "single" ? 'checked' : '' ); ?> />
			Single: 
			<input id="<?php echo $this->get_field_id( 'font_size_single' ); ?>" name="<?php echo $this->get_field_name( 'font_size_single' ); ?>" type="text" value="<?php echo esc_attr( $font_size_single ); ?>" class="widefat">
		<br/>
		<input type="radio" name="<?php echo $this->get_field_name( 'font_size_type' ); ?>" value="range" <?php echo ( $font_size_type == "range" ? 'checked' : '' ); ?> />
			Range: 
			<input id="<?php echo $this->get_field_id( 'font_size_range' ); ?>[start]" name="<?php echo $this->get_field_name( 'font_size_range' ); ?>[start]" type="text" value="<?php echo esc_attr( $font_size_range['start'] ); ?>" class="widefat">
			<input id="<?php echo $this->get_field_id( 'font_size_range' ); ?>[end]" name="<?php echo $this->get_field_name( 'font_size_range' ); ?>[end]" type="text" value="<?php echo esc_attr( $font_size_range['end'] ); ?>" class="widefat">
		</p>		

		<p>
		<label for="<?php echo $this->get_field_id( 'font_size' ); ?>"><?php _e( 'Font Color:' ); ?></label> 
		<br/>
		<input type="radio" name="<?php echo $this->get_field_name( 'font_color_type' ); ?>" value="none" <?php echo ( $font_color_type == "none" ? 'checked' : '' ); ?> />
		None
		<br/>
		<input type="radio" name="<?php echo $this->get_field_name( 'font_color_type' ); ?>" value="single" <?php echo ( $font_color_type == "single" ? 'checked' : '' ); ?> />
		Single:
			<input id="<?php echo $this->get_field_id( 'font_color_single' ); ?>" name="<?php echo $this->get_field_name( 'font_color_single' ); ?>" type="text" value="<?php echo esc_attr( $font_color_single ); ?>" class="widefat">
		<br/>
		<input type="radio" name="<?php echo $this->get_field_name( 'font_color_type' ); ?>" value="spanning" <?php echo ( $font_color_type == "spanning" ? 'checked' : '' ); ?> />
		Spanning:
			<input class="widefat" id="<?php echo $this->get_field_id( 'font_color_spanning' ); ?>" name="<?php echo $this->get_field_name( 'font_color_spanning' ); ?>" type="text" value="<?php echo esc_attr( $font_color_spanning ); ?>" class="widefat">
		</p>

		<p>
		<label for="<?php echo $this->get_field_id( 'canvas_size' ); ?>"><?php _e( 'Canvas Size:' ); ?></label> 
		<input id="<?php echo $this->get_field_id( 'canvas_size' ); ?>[width]" name="<?php echo $this->get_field_name( 'canvas_size' ); ?>[width]" type="text" value="<?php echo esc_attr( $canvas_size['width'] ); ?>" class="widefat">
		<input id="<?php echo $this->get_field_id( 'canvas_size' ); ?>[height]" name="<?php echo $this->get_field_name( 'canvas_size' ); ?>[height]" type="text" value="<?php echo esc_attr( $canvas_size['height'] ); ?>" class="widefat">
		</p>
		</p>
		
		<?php
	}
	
	
	/**
	 * Get the default settings for the widget or shortcode.
	 * @return  array  The default settings.
	 */
	public function get_default_options()
	{
		$defaults = array();

		// title
		$defaults['title'] = '';

		// post types
		$defaults['all_post_types'] = get_post_types( array(), 'objects' );
		$defaults['exclude_post_types'] = array( 'attachment', 'revision', 'nav-menu-item' );
		$defaults['post_types'] = array('post');

		// taxonomy types
		$defaults['all_taxonomies'] = get_taxonomies( array(), 'objects' );
		$defaults['exclude_taxonomies'] = array( 'nav-menu', 'link-category', 'post-format' );
		$defaults['taxonomies'] = array('post_tag');

		// minimum count
		$defaults['minimum_count'] = 1;

		// max words (# or none)
		$defaults['maximum_words'] = 250;
		
		// words orientation
		$defaults['orientation'] = 'horizontal';

		// font_family
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
		
		$defaults['hide_debug'] = 'true';
		
		return $defaults;
	}
	
	
	/**
	 * Process options from the database or shortcode.
	 * Designed to convert options from strings or sanitize output.
	 * @param   array   $options  The current settings for the widget or shortcode.
	 * @return  array   The processed settings.
	 */
	public function process_options( $options )
	{
		if( $this->control_type == 'widget' ) return $options;
		
		foreach( $options as $k => &$v )
		{
			if( is_string($v) ) $v = trim( $v );
		}
		
		if( array_key_exists('post_types', $options) ) 
			$options['post_types'] = explode( ',', $options['post_types'] );
		
		if( array_key_exists('taxonomies', $options) ) 
			$options['taxonomies'] = explode( ',', $options['taxonomies'] );
		
		if( array_key_exists('minimum_count', $options) && is_int($options['minimum_count']) )
			$options['minimum_count'] = intval( $options['minimum_count'] );
		
		if( array_key_exists('maximum_words', $options) && is_int($options['maximum_words']) )
			$options['maximum_words'] = intval( $options['maximum_words'] );
		
		if( array_key_exists('font_size', $options) )		
			$options['font_size_type'] = 'custom';

		if( array_key_exists('font_color', $options) )		
			$options['font_color_type'] = 'custom';

		if( array_key_exists('canvas_size', $options) )
		{
			$value = explode( ",", $options['canvas_size'] );
			if( count($value) >= 2 )
				$options['canvas_size'] = array( 'width' => $value[0], 'height' => $value[1] );
		}
		
		return $options;
	}
	
	
	/**
	 * Echo the widget or shortcode contents.
	 * @param   array  $options  The current settings for the control.
	 * @param   array  $args     The display arguments.
	 */
	public function print_control( $options, $args = null )
	{
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
		if( $terms && !is_wp_error($terms) )
		{
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
		}
		
		echo $args['before_widget'];
		echo '<div id="d3-word-cloud-control-'.self::$index.'" class="wscontrol d3-word-cloud-control">';
		
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
		
		echo '</div>';
		echo $args['after_widget'];		
	}
	
}
endif;

