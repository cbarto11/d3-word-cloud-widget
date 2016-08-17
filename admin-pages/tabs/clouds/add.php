<?php
/**
 * Controls the admin page "Cloud" when in add cloud mode.
 * 
 * @package    d3-word-cloud-widget
 * @subpackage admin-pages/tabs/clouds
 * @author     Crystal Barton <atrus1701@gmail.com>
 */
if( !class_exists('D3WordCloudWidget_CloudsAddTabAdminPage') ):
class D3WordCloudWidget_CloudsAddTabAdminPage extends APL_TabAdminPage
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
		$name = 'add', 
		$tab_title = 'Add', 
		$page_title = 'Add User' )
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
		
		switch( $_REQUEST['action'] )
		{
			case 'add':
				if( isset($_REQUEST['cloud_settings']) )
				{
					$cloud = $this->model->update_cloud( '', $_REQUEST['cloud_settings'] );
					if( 0 < count( $cloud['errors'] ) )
					{
						foreach( $cloud['errors'] as $k => $v ) {
							$this->add_error( $k . ': ' . $v, true );
						}
						$_SESSION['cloud_settings'] = $cloud;
						wp_redirect( 
							$this->get_page_url( 
								array( 
									'tab' => 'add', 
								) 
							) 
						);
						exit;
					}
					else
					{
						$this->page->add_notice( 'Cloud added.', true );
						wp_redirect( 
							$this->get_page_url( 
								array( 
									'tab' => 'edit', 
									'name' => $cloud['name'], 
								) 
							) 
						);
						exit;
					}
				}
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

		if( ! empty( $_SESSION['cloud_settings'] ) ) {
			$cloud = $_SESSION['cloud_settings'];
			unset( $_SESSION['cloud_settings'] );
		} else {
			$cloud = null;
		}
		
		$this->form_start( 'add', null, 'add' );
			submit_button( 'Add' );
			$this->model->print_edit_form( '', $cloud );
			submit_button( 'Add' );
		$this->form_end();
	}

} // class D3WordCloudWidget_CloudsAddTabAdminPage extends APL_TabAdminPage
endif; // if( !class_exists('D3WordCloudWidget_CloudsAddTabAdminPage') )

