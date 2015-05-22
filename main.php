<?php
/*
Plugin Name: D3 Word Cloud
Plugin URI: 
Description: 
Version: 0.0.1
Author: Crystal Barton
Author URI: 
*/


require_once( dirname(__FILE__).'/control.php' );
D3WordCloud_WidgetShortcodeControl::register_widget();
D3WordCloud_WidgetShortcodeControl::register_shortcode();

