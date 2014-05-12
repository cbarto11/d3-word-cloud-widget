<?php

add_action('widgets_init',
     create_function('', 'return register_widget("D3_WordCloud_Widget");')
);

class D3_WordCloud_Widget extends WP_Widget
{

//	private static $id = 0;

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct()
	{
		// widget actual processes
		
		//ns_print('construct');
		
		parent::__construct(
			'd3_word_cloud_widget',
			'D3 Word Cloud',
			array( 
				'description' => 'Creates a D3 word cloud using selected categories and tags.', 
			)
		);
	}


	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance )
	{
		$options = $this->get_instance_variables($instance);
		$options['title'] = ( !empty($options['title']) ? $args['before_title'].$options['title'].$args['after_title'] : '' );

		echo $args['before_widget'];
		D3_WordCloud::create_word_cloud( $this->id, $options );
		echo $args['after_widget'];
	}


	/**
	 * Ouputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance )
	{
		extract( $this->get_instance_variables($instance) );
		?>
		
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<br/>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
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
		<br/>
		<input class="widefat" id="<?php echo $this->get_field_id( 'font-family' ); ?>" name="<?php echo $this->get_field_name( 'font-family' ); ?>" type="text" value="<?php echo esc_attr( $font_family ); ?>">
		</p>

		<p>
		<label for="<?php echo $this->get_field_id( 'font-size' ); ?>"><?php _e( 'Font Size:' ); ?></label> 
		<br/>
		<input type="radio" name="<?php echo $this->get_field_name( 'font-size-type' ); ?>" value="single" <?php echo ( $font_size_type == "single" ? 'checked' : '' ); ?> />
			Single: 
			<input id="<?php echo $this->get_field_id( 'font-size-single' ); ?>" name="<?php echo $this->get_field_name( 'font-size-single' ); ?>" type="text" value="<?php echo esc_attr( $font_size_single ); ?>">
			<br/>
		<input type="radio" name="<?php echo $this->get_field_name( 'font-size-type' ); ?>" value="range" <?php echo ( $font_size_type == "range" ? 'checked' : '' ); ?> />
			Range: 
			<br/>
			<input id="<?php echo $this->get_field_id( 'font-size-range' ); ?>[start]" name="<?php echo $this->get_field_name( 'font-size-range' ); ?>[start]" type="text" value="<?php echo esc_attr( $font_size_range['start'] ); ?>">
			<input id="<?php echo $this->get_field_id( 'font-size-range' ); ?>[end]" name="<?php echo $this->get_field_name( 'font-size-range' ); ?>[end]" type="text" value="<?php echo esc_attr( $font_size_range['end'] ); ?>">
			<br/>
		</p>		

		<p>
		<label for="<?php echo $this->get_field_id( 'font-size' ); ?>"><?php _e( 'Font Color:' ); ?></label> 
		<br/>
		<input type="radio" name="<?php echo $this->get_field_name( 'font-color-type' ); ?>" value="none" <?php echo ( $font_color_type == "none" ? 'checked' : '' ); ?> />
		None
		<br/>
		<input type="radio" name="<?php echo $this->get_field_name( 'font-color-type' ); ?>" value="single" <?php echo ( $font_color_type == "single" ? 'checked' : '' ); ?> />
		Single:
			<input id="<?php echo $this->get_field_id( 'font-color-single' ); ?>" name="<?php echo $this->get_field_name( 'font-color-single' ); ?>" type="text" value="<?php echo esc_attr( $font_color_single ); ?>">
			<br/>
		<input type="radio" name="<?php echo $this->get_field_name( 'font-color-type' ); ?>" value="spanning" <?php echo ( $font_color_type == "spanning" ? 'checked' : '' ); ?> />
		Spanning:
		<br/>
			<input class="widefat" id="<?php echo $this->get_field_id( 'font-color-spanning' ); ?>" name="<?php echo $this->get_field_name( 'font-color-spanning' ); ?>" type="text" value="<?php echo esc_attr( $font_color_spanning ); ?>">
		</p>

		<p>
		<label for="<?php echo $this->get_field_id( 'canvas-size' ); ?>"><?php _e( 'Canvas Size:' ); ?></label> 
		<br/>
		<input id="<?php echo $this->get_field_id( 'canvas-size' ); ?>[width]" name="<?php echo $this->get_field_name( 'canvas-size' ); ?>[width]" type="text" value="<?php echo esc_attr( $canvas_size['width'] ); ?>">
		<input id="<?php echo $this->get_field_id( 'canvas-size' ); ?>[height]" name="<?php echo $this->get_field_name( 'canvas-size' ); ?>[height]" type="text" value="<?php echo esc_attr( $canvas_size['height'] ); ?>">
		<br/>
		</p>
		
		<?php
	}


	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance )
	{
		$instance = $new_instance;
		return $instance;		
	}
	
	
	/**
	 * 
	 */
	private function get_instance_variables( $instance )
	{
		$options = D3_WordCloud::get_defaults();
		
		foreach( $instance as $k => $v )
		{
			$options[ str_replace('-', '_', $k) ] = $v;
		}
		
		return $options;
	}

}

