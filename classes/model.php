<?php
/**
 * The main model for the D3 Word Cloud Widget plugin.
 * 
 * @package    d3-word-cloud-widget
 * @subpackage classes/model
 * @author     Crystal Barton <atrus1701@gmail.com>
 */
if( !class_exists('D3WordCloudWidget_Model') ):
class D3WordCloudWidget_Model
{
	/**
	 * The only instance of the current model.
	 * @var  D3WordCloudWidget_Model
	 */	
	private static $instance = null;
	
	/**
	 * The last error saved by the model.
	 * @var  string
	 */	
	public $last_error = null;
		
	
	/**
	 * Private Constructor.  Needed for a Singleton class.
	 */
	protected function __construct() { }
	
	
	/**
	 * Sets up the "children" models used by this model.
	 */
	protected function setup_models()
	{
	}
	

	/**
	 * Get the only instance of this class.
	 * @return  D3WordCloudWidget_Model  A singleton instance of the model class.
	 */
	public static function get_instance()
	{
		if( self::$instance	=== null )
		{
			self::$instance = new D3WordCloudWidget_Model();
			self::$instance->setup_models();
		}
		return self::$instance;
	}



//========================================================================================
//========================================================================= Log file =====


	/**
	 * Clear the log.
	 */
	public function clear_log()
	{
		file_put_contents( D3_WORD_CLOUD_WIDGET_LOG_FILE );
	}
	

	/**
	 * Write the username followed by a log line.
	 * @param  string  $username  The user's username.
	 * @param  string  $text  The line of text to insert into the log.
	 * @param  bool  $newline  True if a new line character should be inserted after the line, otherwise False.
	 */
	public function write_to_log( $username = '', $text = '', $newline = true )
	{
		$text = print_r( $text, true );
		if( $newline ) $text .= "\n";
		$text = str_pad( $username, 8, ' ', STR_PAD_RIGHT ).' : '.$text;
		file_put_contents( D3_WORD_CLOUD_WIDGET_LOG_FILE, $text, FILE_APPEND );
	}	



//========================================================================================
//========================================================================== Options =====
	
	
	/**
	 * 
	 */
	public function update_cloud( $cloud_name, $settings )
	{
		$settings['errors'] = $this->verify_settings( $cloud_name, $settings );
		if( 0 < count( $settings['errors'] ) ) {
			return $settings;
		}

		$clouds = $this->get_all_clouds();

		if( '' !== $cloud_name )
		{
			$check = array( 
				'post_types', 'taxonomies', 'filterby_taxonomy', 'filterby_terms' 
			);
			foreach( $check as $c )
			{
				if( $clouds[ $cloud_name ][ $c ] != $settings[ $c ] ) {
					$this->remove_cache( $cloud_name );
					break;
				}
			}
		}
		
		if( '' !== $cloud_name && $cloud_name !== $settings['name'] ) {
			unset( $clouds[ $cloud_name ]  );
			$this->rename_cache( $cloud_name, $settings['name'] );
		}
		
		$clouds[ $settings['name'] ] = $settings;
		update_option( D3_WORD_CLOUD_WIDGET_CLOUDS, $clouds );
		
		return $settings;
	}
	
	
	/**
	 * 
	 */
	protected function verify_settings( $cloud_name, $settings )
	{
		$errors = array();
		$clouds = $this->get_all_clouds();
		
		if( empty( $settings['name'] ) )
		{
			$errors['name'] = 'Please specify a cloud name.';
		} 
		else
		{
			if( ( '' === $cloud_name ) || ( $cloud_name !== $settings['name'] ) ) {
				if( array_key_exists( $settings['name'], $clouds ) ) {
					$errors['name'] = $settings['name'] . ' already exists.';
				}
			}
		}
		
		return $errors;
	}
	
	
	/**
	 * 
	 */
	public function delete_cloud( $cloud_name )
	{
		$clouds = $this->get_all_clouds();
		unset( $clouds[ $cloud_name ]  );
		update_option( D3_WORD_CLOUD_WIDGET_CLOUDS, $clouds );
	}	
	
	
	/**
	 * 
	 */
	public function get_cloud_count()
	{
		$clouds = $this->get_all_clouds();
		return count( $clouds );
	}
	
	
	/**
	 * 
	 */
	public function get_clouds( $offset, $limit, $process = false )
	{
		$clouds = $this->get_all_clouds();
		
		if( count( $clouds ) < $offset ) {
			return array();
		}
		
		$clouds = array_splice( $clouds, $offset, $limit );
		
		if( $process ) {
			foreach( $clouds as &$cloud ) {
				$cloud = $this->merge_cloud_settings( $cloud );
			}
		}
		
		return $clouds;
	}
	
	
	/**
	 * 
	 */
	public function get_cloud( $cloud_name )
	{
		$clouds = $this->get_all_clouds();
		
		if( array_key_exists( $cloud_name, $clouds ) ) {
			return $this->merge_cloud_settings( $clouds[ $cloud_name ] );
		}
		
		return false;
	}
	
	
	/**
	 * 
	 */
	public function get_all_clouds( $process = false )
	{
		$clouds = get_option( D3_WORD_CLOUD_WIDGET_CLOUDS, array() );
		
		if( ! $clouds || ! is_array( $clouds ) ) {
			return array();
		}
		
		if( $process ) {
			foreach( $clouds as &$cloud ) {
				$cloud = $this->merge_cloud_settings( $cloud );
			}
		}
		
		return $clouds;
	}
	
	
	/**
	 * 
	 */
	public function merge_cloud_settings( $options )
	{
		if( ! is_array($options) ) {
			return $this->get_default_cloud_settings();
		}
		
		return array_merge( $this->get_default_cloud_settings(), $options );
	}
	
	
	/**
	 * Get the default settings for the cloud.
	 * @return  array  The default settings.
	 */
	public function get_default_cloud_settings()
	{
		$defaults = array();
		
		// name
		$defaults['name'] = '';
		
		// title
		$defaults['title'] = '';

		// post types
		$defaults['post_types'] = array('post');

		// taxonomy types
		$defaults['taxonomies'] = array('post_tag');

		// filter by
		$defaults['filterby'] = array();
		$defaults['filterby_taxonomy'] = '';
		$defaults['filterby_terms'] = '';

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
		
		$defaults['hide_debug'] = 'yes';
		
		return $defaults;
	}
	
	
	/**
	 * 
	 */
	public function get_options()
	{
		$options = array();
		
		$options['all_post_types'] = get_post_types( array(), 'objects' );
		$options['exclude_post_types'] = array( 'attachment', 'revision', 'nav-menu-item' );
		
		$options['all_taxonomies'] = get_taxonomies( array(), 'objects' );
		$options['exclude_taxonomies'] = array( 'nav-menu', 'link-category', 'post-format' );
		
		return $options;
	}
	
	
	/**
	 * 
	 */
	public function print_edit_form( $cloud_name, $cloud = null )
	{
		$cloud = $this->merge_cloud_settings( $cloud );
		
		extract( $cloud );
		extract( $this->get_options() );
		?>
		<input type="hidden" name="name" value="<?php echo $cloud_name; ?>" />
		
		<p>
		<label for="txt_cloud_settings_name"><?php _e( 'Name:' ); ?></label>
		<br/>
		<input type="textbox" name="cloud_settings[name]" value="<?php echo esc_attr( $cloud['name'] ); ?>" />
		<br/>
		</p>
		
		<p>
		<label for="cloud_settings_title"><?php _e( 'Title:' ); ?></label> 
		<br/>
		<input id="cloud_settings_title" name="cloud_settings[title]" type="text" value="<?php echo esc_attr( $title ); ?>" class="widefat">
		<br/>
		</p>
	
		<p>
		<label for="cloud_settings_post_type"><?php _e( 'Post Type:' ); ?></label> 
		<br/>
		<?php foreach( $all_post_types as $pt ): ?>
			<?php
			if( in_array( $pt->name, $exclude_post_types ) ) {
				continue;
			} 
			?>
			<input type="checkbox" name="cloud_settings[post_types][]" value="<?php echo esc_attr( $pt->name ); ?>" <?php checked( in_array( $pt->name, $post_types ) ); ?> />
			<?php echo $pt->label; ?>
			<br/>
		<?php endforeach; ?>
		</p>

		<p>
		<label for="cloud_settings_taxonomies"><?php _e( 'Taxonomies:' ); ?></label>
		<br/>
		<?php foreach( $all_taxonomies as $tax ): ?>
			<?php if( in_array($tax->name, $exclude_taxonomies) ) continue; ?>
			<input type="checkbox" name="cloud_settings[taxonomies][]" value="<?php echo esc_attr( $tax->name ); ?>" <?php checked( in_array( $tax->name, $taxonomies ) ); ?> />
			<?php echo $tax->label; ?>
			<br/>
		<?php endforeach; ?>
		</p>
	
		<p>
		<label for="cloud_settings_filterby_taxonomy"><?php _e( 'Filter By:' ); ?></label>
		<br/>
		<input type="radio" name="cloud_settings[filterby_taxonomy]" value="none" <?php checked( 'none', $filterby_taxonomy ); ?> />
		None
		<br/>
		<?php foreach( $all_taxonomies as $tax ): ?>
			<?php if( in_array($tax->name, $exclude_taxonomies) ) continue; ?>
			<input type="radio" name="cloud_settings[filterby_taxonomy]" value="<?php echo esc_attr( $tax->name ); ?>" <?php checked( $tax->name, $filterby_taxonomy ); ?> />
			<?php echo $tax->label; ?>
			<br/>
		<?php endforeach; ?>
		<label for="cloud_settings_filterby_terms"><?php _e( 'Terms:' ); ?></label>
		<br/>
		<input type="text" name="cloud_settings[filterby_terms]" value="<?php echo esc_attr( $filterby_terms ); ?>" />
		</p>

		<p>
		<label for="cloud_settings_minimum_count"><?php _e( 'Minimum Post Count:' ); ?></label> 
		<input id="cloud_settings_minimum_count" name="cloud_settings[minimum_count]" type="text" value="<?php echo esc_attr( $minimum_count ); ?>">
		</p>
	
		<p>
		<label for="cloud_settings_maximum_words"><?php _e( 'Maximum Words:' ); ?></label> 
		<input id="cloud_settings_maximum_words" name="cloud_settings[maximum_words]" type="text" value="<?php echo esc_attr( $maximum_words ); ?>">
		</p>

		<p>
		<label for="cloud_settings_orientation"><?php _e( 'Words Orientation:' ); ?></label> 
		<br/>
		<input type="radio" name="cloud_settings[orientation]" value="horizontal" <?php checked( $orientation, 'horizontal' ); ?> />
		Horizontal
		<br/>
		<input type="radio" name="cloud_settings[orientation]" value="vertical" <?php checked( $orientation, 'vertical' ); ?> />
		Vertical
		<br/>
		<input type="radio" name="cloud_settings[orientation]" value="mixed" <?php checked( $orientation, 'mixed' ); ?> />
		Mixed Horizontal and Vertical
		<br/>
		<input type="radio" name="cloud_settings[orientation]" value="mostly-horizontal" <?php checked( $orientation, 'mostly-horizontal' ); ?> />
		Mostly Horizontal
		<br/>
		<input type="radio" name="cloud_settings[orientation]" value="mostly-vertical" <?php checked( $orientation, 'mostly-vertical' ); ?> />
		Mostly Vertical
		</p>

		<p>
		<label for="cloud_settings_font_family"><?php _e( 'Font Family:' ); ?></label> 
		<input class="widefat" id="cloud_settings_font_family" name="cloud_settings[font_family]" type="text" value="<?php echo esc_attr( $font_family ); ?>" class="widefat">
		</p>

		<p>
		<label for="cloud_settings_font_size"><?php _e( 'Font Size:' ); ?></label> 
		<br/>
		<input type="radio" name="cloud_settings[font_size_type]" value="single" <?php checked( $font_size_type, 'single' ); ?> />
			Single: 
			<input id="cloud_settings_font_size_single" name="cloud_settings[font_size_single]" type="text" value="<?php echo esc_attr( $font_size_single ); ?>" class="widefat">
		<br/>
		<input type="radio" name="cloud_settings[font_size_type]" value="range" <?php checked( $font_size_type, 'range' ); ?> />
			Range: 
			<input id="cloud_settings_font_size_range_start" name="cloud_settings[font_size_range][start]" type="text" value="<?php echo esc_attr( $font_size_range['start'] ); ?>" class="widefat">
			<input id="cloud_settings_font_size_range_end" name="cloud_settings[font_size_range][end]" type="text" value="<?php echo esc_attr( $font_size_range['end'] ); ?>" class="widefat">
		</p>		

		<p>
		<label for="cloud_settings_font_size"><?php _e( 'Font Color:' ); ?></label> 
		<br/>
		<input type="radio" name="cloud_settings[font_color_type]" value="none" <?php checked( $font_color_type, 'none' ); ?> />
		None
		<br/>
		<input type="radio" name="cloud_settings[font_color_type]" value="single" <?php checked( $font_color_type, 'single' ); ?> />
		Single:
			<input id="cloud_settings_font_color_single" name="cloud_settings[font_color_single]" type="text" value="<?php echo esc_attr( $font_color_single ); ?>" class="widefat">
		<br/>
		<input type="radio" name="cloud_settings[font_color_type]" value="spanning" <?php checked( $font_color_type, 'spanning' ); ?> />
		Spanning:
			<input class="widefat" id="cloud_settings_font_color_spanning" name="cloud_settings[font_color_spanning]" type="text" value="<?php echo esc_attr( $font_color_spanning ); ?>" class="widefat">
		</p>

		<p>
		<label for="cloud_settings_canvas_size"><?php _e( 'Canvas Size:' ); ?></label> 
		<input id="cloud_settings_canvas_size_width" name="cloud_settings[canvas_size][width]" type="text" value="<?php echo esc_attr( $canvas_size['width'] ); ?>" class="widefat">
		<input id="cloud_settings_canvas_size_height" name="cloud_settings[canvas_size][height]" type="text" value="<?php echo esc_attr( $canvas_size['height'] ); ?>" class="widefat">
		</p>

		<p>
		<input type="hidden" name="cloud_settings[hide_debug]" value="no" />
		<input type="checkbox" id="cloud_settings_hide_debug" name="cloud_settings[hide_debug]" value="yes" <?php checked( $hide_debug, 'yes' ); ?> />
		<label for="cloud_settings_hide_debug">Hide Debug Data</label>
		</p>
		
		<?php
	}
	
	
	/**
	 * 
	 */
	public function create_cloud_cache( $cloud_name )
	{
		$cloud = $this->get_cloud( $cloud_name );
		
		if( ! $cloud ) {
			$this->last_error = 'Cloud does not exist.';
			return false;
		}

		
		$all_terms = array();
		
		
		$tax_query = array();
		if( 'none' != $cloud['filterby_taxonomy'] )
		{
			$terms = explode( ';', $cloud['filterby_terms'] );
			
			$tax_query = array(
				array(
					'taxonomy' 	=> $cloud['filterby_taxonomy'],
					'terms' 	=> $terms,
					'field' 	=> 'slug',
					'relation'  => 'OR',
				)
			);
		}
			
		$q = new WP_Query(
			array(
				'post_type' => $cloud['post_types'],
				'tax_query' => $tax_query,
				'posts_per_page' => -1,
			)
		);
		
		while( $q->have_posts() )
		{
			$q->the_post();
			$post_terms = array();
			foreach( $cloud['taxonomies'] as $tax_name )
			{
				$t = get_the_terms( get_the_ID(), $tax_name );
				if( is_array( $t ) ) {
					$post_terms = array_merge( 
						$post_terms, 
						array_map(
							function( $v ) {
								return $v->term_id;
							}, $t 
						)
					);
				}
			}

			$all_terms = array_merge( $all_terms, $post_terms );
			$post_terms = null;
		}

		wp_reset_postdata();			
		
		$all_terms = array_unique( $all_terms );		
		
		
		$cache = array();
		
		$cache['settings'] = array(
			'post_types'        => $cloud['post_types'],
			'taxonomies'        => $cloud['taxonomies'],
			'filterby_taxonomy' => $cloud['filterby_taxonomy'],
			'filterby_terms'    => $cloud['filterby_terms'],
		);
		$cache['datetime'] = null;                   // completed datetime
		$cache['terms'] = array();            // cached terms
		
		$this->update_cache( $cloud_name, $cache );
		
		return $all_terms;
	}
	
	
	/**
	 * 
	 */
	public function update_cache( $cloud_name, $cache )
	{
		update_option( D3_WORD_CLOUD_WIDGET_CACHE . '_' . $cloud_name, $cache );
	}
	
	
	/**
	 * 
	 */
	public function get_cache( $cloud_name, $validate = false )
	{
		$cache = get_option( D3_WORD_CLOUD_WIDGET_CACHE . '_' . $cloud_name, false );
		if( ! $validate ) {
			return $cache;
		}
		
		if( empty( $cache['datetime'] ) ) {
			return false;
		}
		
		$cloud = $this->get_cloud( $cloud_name );
		if( ! $cloud ) {
			return false;
		}
		
		$check = array( 
			'post_types', 'taxonomies', 'filterby_taxonomy', 'filterby_terms' 
		);
		foreach( $check as $c )
		{
			if( $cloud[ $c ] != $cache['settings'][ $c ] ) {
				return false;
			}
		}
		
		return $cache;
	}


	/**
	 * 
	 */
	public function remove_cache( $cloud_name )
	{
		return delete_option( D3_WORD_CLOUD_WIDGET_CACHE . '_' . $cloud_name );
	}
	
	
	/**
	 * 
	 */
	public function rename_cache( $old_cloud_name, $new_cloud_name )
	{
		$cache = $this->get_cache( $old_cloud_name );
		if( ! $cache ) {
			return;
		}
		
		$this->remove_cache( $old_cloud_name );
		$this->update_cache( $new_cloud_name, $cache );
	}
	
	
	/**
	 * 
	 */
	public function update_cache_complete( $cloud_name )
	{
		$cache = $this->get_cache( $cloud_name );
		if( ! $cache )  {
			$this->last_error = 'Cache does not exist.';
			return false;
		}
		
		$cache['datetime'] = date( 'Y-m-d H:i:s' );
		$this->update_cache( $cloud_name, $cache );
	}	

	
	/**
	 * 
	 */
	public function update_cache_term( $cloud_name, $term_id )
	{
		$cache = $this->get_cache( $cloud_name );
		if( ! $cache )  {
			$this->last_error = 'Cache does not exist.';
			return false;
		}
		
		$term = get_term_by( 'term_taxonomy_id', $term_id );
		if( ! $term ) {
			$this->last_error = 'Invalid term id.';
			return false;
		}
		
		$post_types = $cache['settings']['post_types'];
		$taxonomies = $cache['settings']['taxonomies'];
		$filterby_taxonomy = $cache['settings']['filterby_taxonomy'];
		$filterby_terms = $cache['settings']['filterby_terms'];
		
		
		$tax_query = array();
		if( 'none' != $filterby_taxonomy )
		{
			$terms = explode( ';', $filterby_terms );
			
			$tax_query = array(
				'relation'  => 'AND',
				array(
					'taxonomy' 	=> $filterby_taxonomy,
					'terms' 	=> $terms,
					'field' 	=> 'slug',
					'relation'  => 'OR',
				),
			);
		}
		
		$tax_query[] = array(
			'taxonomy'  => $term->taxonomy,
			'terms'     => $term_id,
			'field'     => 'id',
		);
		
		
		$q = new WP_Query(
			array(
				'post_type' => $cloud['post_types'],
				'tax_query' => $tax_query,
				'posts_per_page' => -1,
			)
		);
		
		
		$cache['terms'][ $term_id ] = $q->found_posts;
		$this->update_cache( $cloud_name, $cache );
		
		return $q->found_posts;
	}
	
	
} // class D3WordCloudWidget_Model
endif; // if( !class_exists('D3WordCloudWidget_Model') ):

