<?php
/*
Plugin Name: Banner Garden
Plugin URI: http://www.vaso.hu/blog/banner-garden-plugin-for-wordpress/
Description: Create campaigns, add flash, picture or text Ad code, define places, and rotate the banners.
Version: 0.1.3
Author: Ferenc Vasóczki
Author URI: http://www.vaso.hu/blog/
Disclaimer: Use at your own risk. No warranty expressed or implied is provided.
*/

/* 
Usage:
Please read the readme.txt file.
If you not found it, download this plugin from this site:
http://www.vaso.hu/blog/banner-garden-plugin-for-wordpress/
*/

/*  
License:

		Copyright 2010 Ferenc Vasóczki  (email : vasoczki.ferenc [at] gmail [dot] com )

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define ('BGDS',DIRECTORY_SEPARATOR);
if (is_admin()) {
	//Do it if we are on the dashboard.
	include_once ("bannergarden.class.php");
	include_once ("bannergarden_frontend.class.php");
	include_once ("bannergarden_widget.class.php");
	
	//Do anything only, if the required classes exists
	if (class_exists('BannerGarden') && class_exists('BannerGardenFrontEnd')) {
		$bg = new BannerGarden();
		$bgfe = new BannerGardenFrontEnd();
		add_action('admin_menu', array($bg, 'BannerGardenAdminMain'));
		if (class_exists('BannerGardenWidgetClass')) {
			$bgw = new BannerGardenWidgetClass();
			add_action("widgets_init", array($bgw, 'BannerGardenRegisterWidget'));
		}

		load_plugin_textdomain( 'bannergarden', false, dirname( plugin_basename( __FILE__ )).'/localization' );
		
		register_activation_hook(__FILE__,array($bg, 'BannerGardenInstall'));
		register_deactivation_hook(__FILE__,array($bg, 'BannerGardenUnInstall'));
		
		//Let's init if we are on the admin page and use our Banner Garden Plugin. Otherwise not necesarry.
		if ($_GET['page'] == 'banner_garden_page') {
			$bg->RegisterJavaScripts();
			add_action('init', array($bg, 'BannerGardenInit'));
		}
	}
}	else {
	//Use this if we are on the frontpeage, and let's process the bannergarden shortcodes (not wp shortcodes);
	include_once('bannergarden_frontend.class.php');
	include_once ("bannergarden_widget.class.php");
	
	//Do anything only, if the required class exists
	if (class_exists('BannerGardenFrontend')) {
			$bgfe = new BannerGardenFrontend();
			$bgfe->RegisterJavaScripts();
			
			if (class_exists('BannerGardenWidgetClass')) {
				$bgw = new BannerGardenWidgetClass();
				add_action("widgets_init", array($bgw, 'BannerGardenRegisterWidget'));
			}

			//add_filter('the_content',array($bgfe,'BannerGardenFilter')); //Developer version of bannergarden, to see errors. You can delete this row.
			add_action('wp_head', array($bgfe,'BGBufferStart'));
			add_action('wp_footer', array($bgfe,'BGBufferEnd'));
		}
}
?>