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
		extract( $this->get_instance_variables($instance) );
		$terms = get_terms( $taxonomies, 
			array( 
				'orderby' => 'count', 
				'order' => 'ASC', 
				'hide_empty' => 1, 
				'number' => +maximum_words
			)
		);
		
		$tags = array();
		foreach( $terms as $term )
		{
			if( +$term->count > +$minimum_count )
			{
				$tags[] = array(
					'name' => $term->name,
					'count' => +$term->count,
					'url' => get_term_link( $term ),
				);
			}
		}
		?>
		
		<?php echo $args['before_widget']; ?>

		<?php if( !empty($instance['title']) ): ?>
			<?php echo $args['before_title'].$instance['title'].$args['after_title']; ?>
		<?php endif; ?>

		<div id="<?php echo $this->id; ?>" class="d3-word-cloud-container">

		<?php //D3_WordCloud_Widget::$id++; ?>

		<input type="hidden" class="font-family" value="<?php echo esc_attr($font_family); ?>" />
		
		<?php 
		$font_size = '';
		switch( $font_size_type )
		{
			case( "range" ):
				$font_size = $font_size_range['start'].','.$font_size_range['end'];
				break;
				
			case( "single" ):
			default:
				$font_size = $font_size_single;
				break;
			
		}
		?>
		<input type="hidden" class="font-size" value="<?php echo esc_attr($font_size); ?>" />
		
		<?php
		$font_color = '';
		switch( $font_color_type )
		{
			case( "spanning" ):
				$font_color = $font_color_spanning;
				break;
				
			case( "single" ):
				$font_color = $font_color_single;
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

		<?php echo $args['after_widget']; ?>
		<?php
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
		<label for="<?php echo $this->get_field_id( 'font-size' ); ?>"><?php _e( 'Font Size:' ); ?></label> 
		<br/>
		<input type="radio" name="<?php echo $this->get_field_name( 'font-color-type' ); ?>" value="none" <?php echo ( $font_color_type == "none" ? 'checked' : '' ); ?> />
		None:
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
		$options = array();
		
		// title
		$options['title'] = ( isset($instance['title']) ? $instance['title'] : '' );
			
		// post types
		$options['all_post_types'] = get_post_types( array(), 'objects' );
		$options['exclude_post_types'] = array( 'attachment', 'revision', 'nav_menu_item' );
		$options['post_types'] = ( isset($instance['post-types']) ? $instance['post-types'] : array('post') );

		// taxonomy types
		$options['all_taxonomies'] = get_taxonomies( array(), 'objects' );
		$options['exclude_taxonomies'] = array( 'nav_menu', 'link_category', 'post_format' );
		$options['taxonomies'] = ( isset($instance['taxonomies']) ? $instance['taxonomies'] : array('post_tag') );

		// minimum count
		$options['minimum_count'] = ( isset($instance['minimum-count']) ? $instance['minimum-count'] : 1 );

		// max words (# or none)
		$options['maximum_words'] = ( isset($instance['maximum-words']) ? $instance['maximum-words'] : 250 );
		
		// font-family
		$options['font_family'] = ( isset($instance['font-family']) ? $instance['font-family'] : 'Arial' );

		// font-size (range or single)
		$options['font_size_type'] = ( isset($instance['font-size-type']) ? $instance['font-size-type'] : 'range' );
		$options['font_size_range'] = ( isset($instance['font-size-range']) ?  $instance['font-size-range'] : array('start' => 10, 'end' => 100) );
		$options['font_size_single'] = ( isset($instance['font-size-single']) ? $instance['font-size-single'] : 60 );

		// color (spanning, single color, none)
		$options['font_color_type'] = ( isset($instance['font-color-type']) ? $instance['font-color-type'] : 'none' );
		$options['font_color_single'] = ( isset($instance['font-color-single']) ? $instance['font-color-single'] : '' );
		$options['font_color_spanning'] = ( isset($instance['font-color-spanning']) ? $instance['font-color-spanning'] : '' );

		// canvas size (height and width)
		$options['canvas_size'] = ( isset($instance['canvas-size']) ? $instance['canvas-size'] : array('width' => 960, 'height' => 420) );
		
		return $options;
	}

}

