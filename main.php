<?php
/*
Plugin Name: D3 Word Cloud
Plugin URI: 
Description: Displays a word cloud of terms using D3.js Word Cloud.
Version: 1.0.0
Author: Crystal Barton
Author URI: 
*/


require_once( dirname(__FILE__).'/control.php' );
D3WordCloud_WidgetShortcodeControl::register_widget();
D3WordCloud_WidgetShortcodeControl::register_shortcode();

