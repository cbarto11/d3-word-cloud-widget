<?php
/**
 * Controls the admin page "Cloud" when in edit user mode.
 * 
 * @package    d3-word-cloud-widget
 * @subpackage admin-pages/tabs/users
 * @author     Crystal Barton <atrus1701@gmail.com>
 */
if( !class_exists('D3WordCloudWidget_CloudsEditTabAdminPage') ):
class D3WordCloudWidget_CloudsEditTabAdminPage extends APL_TabAdminPage
{
	/**
	 * The main model for the D3 Word Cloud Widget.
	 * @var  D3WordCloudWidget_Model
	 */	
	private $model = null;
	
	
	/**
	 * Controller.
	 */
	public function __construct(
		$parent,
		$name = 'edit', 
		$tab_title = 'Edit', 
		$page_title = 'Edit User' )
	{
		parent::__construct( $parent, $name, $tab_title, $page_title );
		$this->model = D3WordCloudWidget_Model::get_instance();
		$this->display_tab = false;
	}
	
	
	/**
	 * Enqueues all the scripts or styles needed for the admin page. 
	 */
	public function enqueue_scripts()
	{
		wp_enqueue_script( 'd3-cache-terms', D3_WORD_CLOUD_WIDGET_PLUGIN_URL . '/admin-pages/scripts/clouds.js', array( 'jquery' ) );
	}
	
	
	/**
	 * Processes the current admin page.
	 */
	public function process()
	{
		if( empty($_REQUEST['action']) ) return;
		
		$name = $_REQUEST['name'];
		
		switch( $_REQUEST['action'] )
		{
			case 'update':
				$cloud = $this->model->update_cloud( $name, $_REQUEST['cloud_settings'] );
				if( 0 < count( $cloud['errors'] ) ) {
					foreach( $cloud['errors'] as $k => $v ) {
						$this->add_error( $k . ': ' . $v, true );
					}
					$_SESSION['cloud_settings'] = $cloud;
				}
				else {
					$this->add_notice( 'Cloud updated.', true );
					$name = $_REQUEST['cloud_settings']['name'];
				}
				
				wp_redirect( 
					$this->get_page_url( 
						array( 
							'tab' => 'edit', 
							'name' => $name,
						) 
					) 
				);
				exit;
				break;
		}
	}	
		

	/**
	 * Displays the current admin page.
	 */
	public function display()
	{
		?>
		<a href="<?php echo esc_attr( $this->get_page_url( array( 'tab' => 'list' ) ) ); ?>">
			Return to List
		</a>
		<?php
		
		if( empty($_REQUEST['name']) ) {
			?><p class="no-name">No name provided.</p><?php
			return;
		}
		
		$name = $_REQUEST['name'];
		
		if( ! empty( $_SESSION['cloud_settings'] ) ) {
			$cloud = $_SESSION['cloud_settings'];
			unset( $_SESSION['cloud_settings'] );
		} else {
			$cloud = $this->model->get_cloud( $name );
		}
		
		if( ! $cloud ) {
			?><p class="no-cloud">The name does not match a current cloud.</p><?php
			return;
		}
		
		$this->form_start( 'update', null, 'update', array( 'name' => $name ) );
			submit_button( 'Update' );
			$this->model->print_edit_form( $name, $cloud );
			submit_button( 'Update' );
		$this->form_end();
		
		?>
		<div id="cache-terms-last-update">
			<?php
			$cache = $this->model->get_cache( $name, true );
			if( ! $cache ) {
				echo 'No complete cache found.';
			} else {
				echo $cache['datetime'];
			}
			?>
		</div>
		<?php
		
		$this->form_start_get( 'cache-post-count', null, 'cache-post-count' );
			?><input type="hidden" name="cloud_name" value="<?php echo esc_attr( $name ); ?>" /><?php
			$this->create_ajax_submit_button(
				'Cache Post Count',
				'cache-all-terms',
				null,
				null,
				'cache_all_terms_start',
				'cache_all_terms_end',
				'cache_all_terms_loop_start',
				'cache_all_terms_loop_end'
			);
		$this->form_end();
		?>
		<div id="cache-terms-status"></div>
		<div id="cache-terms-substatus"></div>
		<div id="cache-terms-results"></div>
		<?php
		
		apl_print( $cache );
	}


	/**
	 * Processes and displays the output of an ajax request.
	 * @param  string  $action  The AJAX action.
	 * @param  array  $input  The AJAX input array.
	 * @param  int  $count  When multiple AJAX calls are made, the current count.
	 * @param  int  $total  When multiple AJAX calls are made, the total count.
	 */
	public function ajax_request( $action, $input, $count, $total )
	{
		switch( $action )
		{
			case 'cache-all-terms':
				if( ! isset( $input['cloud_name'] ) ) {
					$this->ajax_failed( 'No Cloud Name given.' );
					return;
				}
				
				$terms = $this->model->create_cloud_cache( $input['cloud_name'] );
				$message = ( is_array( $terms ) ? 'OK' : $this->model->last_error );
				$status = ( is_array( $terms ) ? 'success' : 'failure' );
				
				if( is_array( $terms ) )
				{
					array_push( $terms, -1 );
					foreach( $terms as &$term )
					{
						$term = array(
							'term_id' => $term,
						);
					}
					$this->ajax_set_items(
						'cache-term',
						array_values( $terms ),
						'cache_term_start',
						'cache_term_end',
						'cache_term_loop_start',
						'cache_term_loop_end'
					);
				}
				
				$this->ajax_set( 'status', $status );
				$this->ajax_set( 'message', $message );
				break;
				
			case 'cache-term':
				if( ! isset( $input['cloud_name'] ) ) {
					$this->ajax_failed( 'No Cloud Name given.' );
					return;
				}
				if( ! isset( $input['term_id'] ) ) {
					$this->ajax_failed( 'No term id given.' );
					return;
				}
				
				$term_id = intval( $input['term_id'] );
				
				if( -1 != $term_id )
				{
					$status = $this->model->update_cache_term( $input['cloud_name'], $term_id );
					$message = ( false !== $status ? 'OK' : $this->model->last_error );
					$status = ( false !== $status ? 'success' : 'failure' );
				}
				else
				{
					$this->model->update_cache_complete( $input['cloud_name'] );
					$message = ( false !== $status ? 'OK' : $this->model->last_error );
					$status = ( false !== $status ? 'success' : 'failure' );
				}
				
				$this->ajax_set( 'status', $status );
				$this->ajax_set( 'message', $message );
				break;
				
			default:
				$this->ajax_failed( 'No valid action was given.' );
				break;
		}
	}
	
	
} // class D3WordCloudWidget_CloudsEditTabAdminPage extends APL_TabAdminPage
endif; // if( !class_exists('D3WordCloudWidget_CloudsEditTabAdminPage') )

