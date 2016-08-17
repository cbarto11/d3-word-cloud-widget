<?php
/*
Plugin Name: D3 Word Cloud
Plugin URI: https://github.com/atrus1701/d3-word-cloud-widget
Description: Displays a word cloud of terms using D3.js.
Version: 2.0.0
Author: Crystal Barton
Author URI: https://www.linkedin.com/in/crystalbarton
*/


if( !defined('D3_WORD_CLOUD_WIDGET') ):

/**
 * The full title of the D3 Word Cloud Widget plugin.
 * @var  string
 */
define( 'D3_WORD_CLOUD_WIDGET', 'D3 Word Cloud Widget' );

/**
 * True if debug is active, otherwise False.
 * @var  bool
 */
define( 'D3_WORD_CLOUD_WIDGET_DEBUG', false );

/**
 * The path to the plugin.
 * @var  string
 */
define( 'D3_WORD_CLOUD_WIDGET_PLUGIN_PATH', __DIR__ );

/**
 * The url to the plugin.
 * @var  string
 */
define( 'D3_WORD_CLOUD_WIDGET_PLUGIN_URL', plugins_url('', __FILE__) );

/**
 * The version of the plugin.
 * @var  string
 */
define( 'D3_WORD_CLOUD_WIDGET_VERSION', '2.0.0' );

/**
 * The database version of the plugin.
 * @var  string
 */
define( 'D3_WORD_CLOUD_WIDGET_DB_VERSION', '2.0' );

/**
 * The database options key for the D3 Word Cloud Widget version.
 * @var  string
 */
define( 'D3_WORD_CLOUD_WIDGET_VERSION_OPTION', 'd3-word-cloud-widget-version' );

/**
 * The database options key for the D3 Word Cloud Widget database version.
 * @var  string
 */
define( 'D3_WORD_CLOUD_WIDGET_DB_VERSION_OPTION', 'd3-word-cloud-widget-db-version' );

/**
 * The database options key for the D3 Word Cloud Widget options.
 * @var  string
 */
define( 'D3_WORD_CLOUD_WIDGET_CLOUDS', 'd3wcw-clouds' );
define( 'D3_WORD_CLOUD_WIDGET_CACHE', 'd3wcw-cache' );

/**
 * The full path to the log file used for debugging.
 * @var  string
 */
define( 'D3_WORD_CLOUD_WIDGET_LOG_FILE', __DIR__.'/log.txt' );

endif;


/* Add widget and shortcode */
require_once( __DIR__ . '/control.php' );
D3WordCloud_WidgetShortcodeControl::register_widget();
D3WordCloud_WidgetShortcodeControl::register_shortcode();


if( is_admin() ):
 	add_action( 'wp_loaded', 'd3wordcloudwidget_load' );
endif;


/**
 * Setup the site admin pages.
 */
if( !function_exists('d3wordcloudwidget_load') ):
function d3wordcloudwidget_load()
{
	require_once( __DIR__.'/admin-pages/require.php' );
	
	$d3_pages = new APL_Handler( false );

	$d3_pages->add_page( new D3WordCloudWidget_CloudsAdminPage() );
	$d3_pages->setup();
	
	if( $d3_pages->controller )
	{
//		add_action( 'admin_enqueue_scripts', 'd3wordcloudwidget_enqueue_scripts' );
		add_action( 'admin_menu', 'd3wordcloudwidget_update', 5 );
	}
}
endif;


/**
 * Update the database if a version change.
 */
if( !function_exists('d3wordcloudwidget_update') ):
function d3wordcloudwidget_update()
{
	update_option( D3_WORD_CLOUD_WIDGET_VERSION_OPTION, D3_WORD_CLOUD_WIDGET_VERSION );
	update_option( D3_WORD_CLOUD_WIDGET_DB_VERSION_OPTION, D3_WORD_CLOUD_WIDGET_DB_VERSION );
}
endif;




