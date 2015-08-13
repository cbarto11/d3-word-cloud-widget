<?php
/*
Plugin Name: D3 Word Cloud
Plugin URI: https://github.com/atrus1701/d3-word-cloud-widget
Description: Displays a word cloud of terms using D3.js.
Version: 1.0.0
Author: Crystal Barton
Author URI: https://www.linkedin.com/in/crystalbarton
*/


require_once( __DIR__.'/control.php' );
D3WordCloud_WidgetShortcodeControl::register_widget();
D3WordCloud_WidgetShortcodeControl::register_shortcode();

