<?php
/**
 * Controls the admin page "Clouds".
 * 
 * @package    d3-word-cloud-widget
 * @subpackage admin-pages/pages
 * @author     Crystal Barton <atrus1701@gmail.com>
 */

if( !class_exists('D3WordCloudWidget_CloudsAdminPage') ):
class D3WordCloudWidget_CloudsAdminPage extends APL_AdminPage
{
	
	/**
	 * Creates an OrgHub_UsersAdminPage object.
	 */
	public function __construct(
		$name = 'clouds',
		$menu_title = 'Clouds',
		$page_title = 'Clouds',
		$capability = 'administrator' )
	{
		parent::__construct( $name, $menu_title, $page_title, $capability );
	
		$this->display_page_tab_list = false;
		$this->add_tab( new D3WordCloudWidget_CloudsListTabAdminPage($this) );
		$this->add_tab( new D3WordCloudWidget_CloudsAddTabAdminPage($this) );
		$this->add_tab( new D3WordCloudWidget_CloudsEditTabAdminPage($this) );
		$this->add_tab( new D3WordCloudWidget_CloudsDeleteTabAdminPage($this) );
	}
	
} // class D3WordCloudWidget_CloudsAdminPage extends APL_AdminPage
endif; // if( !class_exists('D3WordCloudWidget_CloudsAdminPage') )

