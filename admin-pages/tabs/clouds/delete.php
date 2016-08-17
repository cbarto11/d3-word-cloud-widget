<?php
/**
 * Controls the admin page "Cloud" when in delete cloud mode.
 * 
 * @package    d3-word-cloud-widget
 * @subpackage admin-pages/tabs/clouds
 * @author     Crystal Barton <atrus1701@gmail.com>
 */
if( !class_exists('D3WordCloudWidget_CloudsDeleteTabAdminPage') ):
class D3WordCloudWidget_CloudsDeleteTabAdminPage extends APL_TabAdminPage
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
		$name = 'delete', 
		$tab_title = 'Delete', 
		$page_title = 'Delete User' )
	{
		parent::__construct( $parent, $name, $tab_title, $page_title );
		$this->model = D3WordCloudWidget_Model::get_instance();
		$this->display_tab = false;
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
			case 'delete':
				if( $_REQUEST['confirm'] == 'Yes' )
				{
					$this->model->delete_cloud( $name );
					$this->add_notice( $name . ': Cloud deleted.', true );
					wp_redirect( 
						$this->get_page_url( 
							array( 
								'tab' => 'delete', 
								'name' => $name, 
							) 
						) 
					);
					exit;
				}
				
				wp_redirect( 
					$this->get_page_url( 
						array( 
							'tab' => 'list', 
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
		
		if( ! empty( $_REQUEST['action'] ) && ( $_REQUEST['action'] == 'deleted' ) ) {
			return;
		}
		
		if( empty($_REQUEST['name']) ) {
			?><p class="no-name">No name provided.</p><?php
			return;
		}
		
		$name = $_REQUEST['name'];
		
		if( ! empty( $_REQUEST['cloud_settings'] ) ) {
			$cloud = $_REQUEST['cloud_settings'];
		} else {
			$cloud = $this->model->get_cloud( $name );
		}

		if( ! $cloud ) {
			?><p class="no-cloud">The name does not match a current cloud.</p><?php
			return;
		}
		
		$this->form_start( 'delete', null, 'delete', array( 'name' => $name ) );
		?>
		
		<input type="hidden" name="cloud_settings[name]" value="<?php echo esc_attr( $name ); ?>" />
		<p>Are you sure you want to delete '<?php echo $name; ?>'?</p>
		<input type="submit" name="confirm" value="Yes" />
		<input type="submit" name="confirm" value="NO" />
		
		<?php 
		$this->form_end();
	}

} // class D3WordCloudWidget_CloudsDeleteTabAdminPage extends APL_TabAdminPage
endif; // if( !class_exists('D3WordCloudWidget_CloudsDeleteTabAdminPage') )

