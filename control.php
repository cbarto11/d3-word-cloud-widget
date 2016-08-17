<?php

require_once( __DIR__.'/widget-shortcode-control.php' );
require_once( D3_WORD_CLOUD_WIDGET_PLUGIN_PATH . '/classes/model.php' );


/**
 * The D3WordCloud_WidgetShortcodeControl class for the "D3 Word Cloud" plugin.
 * Derived from the official WP RSS widget.
 * 
 * Shortcode Example:
 * [d3_word_cloud title="My Word Cloud" post_types="post,connection" taxonomies="connection-group,connection-link" minimum_count="1" maximum_words="250" orientation="horizontal" font_family="Georgia" font_size="10,100" font_color="green,blue,black" canvas_size="500,500"]
 * 
 * @package    clas-buttons
 * @author     Crystal Barton <atrus1701@gmail.com>
 */
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
		
		$model = D3WordCloudWidget_Model::get_instance();
		$clouds = $model->get_all_clouds( true );
		
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'name' ); ?>">
			<?php _e( 'Cloud Name:' ); ?>
		</label> 
		<br/>
		<?php
		
		if( empty( $clouds ) ):
			echo 'No clouds found.';
		else:
			?>
			<select name="<?php echo $this->get_field_name( 'name' ); ?>">
			<?php
			foreach( $clouds as $cloud ):
				?>
				<option value="<?php echo esc_attr( $cloud['name'] ); ?>" <?php selected( $cloud['name'], $name ); ?>>
					<?php echo $cloud['name']; ?>
				</option>
				<?php
			endforeach;
			?>
			</select>
			<?php
		endif;
		
		?>
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
		$default['name'] = array();
		return $defaults;
	}
	
	
	/**
	 * Echo the widget or shortcode contents.
	 * @param   array  $options  The current settings for the control.
	 * @param   array  $args     The display arguments.
	 */
	public function print_control( $options, $args = null )
	{
		$options = $this->merge_options( $options );
		extract( $options );
		
		if( empty( $name ) ) {
			return;
		}
		
		$model = D3WordCloudWidget_Model::get_instance();
		
		$cloud = $model->get_cloud( $name );
		if( ! $cloud ) {
			return;
		}
		
		$cache = $model->get_cache( $name, true );
		if( ! $cache ) {
			return;
		}
		
		extract( $cloud );
		
		
		$terms = array();
		foreach( $cache['terms'] as $term_id => $term_count )
		{
			$term_object = get_term_by( 'term_taxonomy_id', $term_id );
			$terms[] = array(
				'name' => $term_object->name,
				'count' => $term_count,
				'url' => get_term_link( $term_id ),
			);
		}

		echo $args['before_widget'];
		echo '<div id="d3-word-cloud-control-' . self::$index . '" class="wscontrol d3-word-cloud-control">';
		
		if( ! empty( $title ) )
		{
			echo $args['before_title'] . $title . $args['after_title'];
		}
		
		echo '<input type="hidden" class="orientation" value="' . esc_attr( $orientation ) . '" />';
		echo '<input type="hidden" class="font-family" value="' . esc_attr( $font_family ) . '" />';
		
		switch( $font_size_type )
		{
			case( "range" ):
				$font_size = $font_size_range['start'] . ',' . $font_size_range['end'];
				break;
				
			case( "custom" ):
				break;
				
			case( "single" ):
			default:
				$font_size = $font_size_single;
				break;
		}
		
		echo '<input type="hidden" class="font-size" value="' . esc_attr( $font_size ) . '" />';
		
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
		
		echo '<input type="hidden" class="font-color" value="' . esc_attr( $font_color ) . '" />';
		echo '<input type="hidden" class="tags" value="' . esc_attr( json_encode( $terms ) ) . '" />';
		echo '<input type="hidden" class="hide-debug" value="' . esc_attr( $hide_debug ) . '" />';
		echo '<svg width="' . $canvas_size['width'] . '" height="' . $canvas_size['height'] . '"></svg>';
		
		echo '</div>';
		echo $args['after_widget'];
	}
}
endif;

