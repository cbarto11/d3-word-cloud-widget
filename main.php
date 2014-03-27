<?php
/*
Plugin Name: D3 Word Cloud
Plugin URI: 
Description: 
Version: 0.0.1
Author: Crystal Barton
Author URI: 
*/


require_once( dirname(__FILE__).'/widget.php' );
add_action( 'wp_enqueue_scripts', array( 'D3_WordCloud_Main', 'enqueue_scripts' ) );

class D3_WordCloud_Main
{
	
	public static function enqueue_scripts()
	{
		wp_enqueue_script( 'd3-library', plugins_url( '/scripts/d3.min.js' , __FILE__ ) );
		wp_enqueue_script( 'd3-layout-cloud', plugins_url( '/scripts/d3.layout.cloud.js' , __FILE__ ) );
		wp_enqueue_script( 'd3-word-cloud', plugins_url( '/scripts/cloud.js' , __FILE__ ) );
	}
	
}






