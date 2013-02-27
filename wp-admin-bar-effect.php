<?php
/**
 * @package WP Admin Bar Effect (wabe)
 * @version 2.2
 */
/*
Plugin Name: WP Admin Bar Effect (wabe)
Plugin URI: http://wordpress.org/extend/plugins/wp-admin-bar-effect/
Description: Add effect slideDown to desktop top bar 
Author: Sergio P.A. ( 23r9i0 )
Version: 2.2
Author URI: http://dsergio.com/
*/
/*  Copyright 2011-2013  Sergio Prieto Alvarez  (email : info@dsergio.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if( !class_exists( 'wabe' ) ) : 
class wabe {
	private 
		$wabe_version = '2.2',
		$wabe_options,
		$wabe_options_defaults = array(
			'actlink'		=> 'on',
			'icolink'		=> '',
			'speed'			=> '3000',
			'sensitivity'	=> '4',
			'interval'		=> '50',
			'timeout'		=> '200'
		),
		$wabe_values;
	public function __construct(){
		add_action( 'init', array( $this, 'wabe_init' ) );
		add_action( 'admin_init', array( $this, 'wabe_admin_options' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'wabe_global_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'wabe_img_style' ) );
		add_action( 'admin_menu', array( $this, 'wabe_page_menu' ) );
		add_action( 'admin_menu', array( $this, 'add_menu_wabe' ) );
		add_filter( 'plugin_action_links', array( $this, 'wabe_plugin_action_links' ), 10, 2 );
		register_activation_hook( __FILE__, array( $this, 'wabe_activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'wabe_deactivate' ) );
		$this->wabe_values = get_option( 'wabe-options' );
	}
	public function wabe_init(){
		global $wp_version;
		if ( version_compare( $wp_version, '3.5', '< ' ) ){
			wp_die( __( 'This plugin requires WordPress 3.5 or greater.', 'wabelang' ) );
		}
  		load_plugin_textdomain( 'wabelang', false, dirname( plugin_basename(__FILE__) ) . '/languages/' );
	}
	public function wabe_activate(){	
		add_option( 'wabe-options', $this->wabe_options_defaults );
	}
	public function wabe_deactivate(){
		delete_option( 'wabe-options' );
	}
	public function wabe_admin_options(){
		register_setting( 'wabe-register', 'wabe-options', array($this, 'wabe_validate' ) );
		add_settings_section( 'wabe_general', '', '__return_false', 'wabe_settings' );
		add_settings_field( 'act_link', __( 'Active link:', 'wabelang' ), array($this, 'setting_act_link' ), 'wabe_settings', 'wabe_general', array( 'label_for' => 'wabe-actlink' ) );
		add_settings_field( 'ico_link', __( 'Custom Icon to link:', 'wabelang' ), array($this, 'setting_ico_link' ), 'wabe_settings', 'wabe_general', array( 'label_for' => 'wabe-ico' ) );
		add_settings_field( 'wabe_speed', __( 'Speed:', 'wabelang' ), array($this, 'setting_speed' ), 'wabe_settings', 'wabe_general', array( 'label_for' => 'wabe-speed' ) );
		add_settings_field( 'wabe_header',  __('<strong>Advanced Settings</strong>','wabelang'), '__return_false', 'wabe_settings', 'wabe_general' );
		add_settings_field( 'wabe_sensitivity', __( 'Sensitivity:', 'wabelang' ), array($this, 'setting_sensitivity' ), 'wabe_settings', 'wabe_general', array( 'label_for' => 'wabe-sensitivity' ) );
		add_settings_field( 'wabe_interval', __( 'Interval:', 'wabelang' ), array($this, 'setting_interval' ), 'wabe_settings', 'wabe_general', array( 'label_for' => 'wabe-interval' ) );
		add_settings_field( 'wabe_timeout', __( 'Timeout:', 'wabelang' ), array($this, 'setting_timeout' ), 'wabe_settings', 'wabe_general', array( 'label_for' => 'wabe-timeout' ) );
	}
	public function wabe_validate($output){
		if ( isset( $_POST['reset'] ) ){
			add_settings_error( 'wabe-error', 'restore_defaults', __( 'Default options restored.', 'wabelang' ), 'updated fade' );
			$output = $this->wabe_options_defaults;
		} elseif ( isset( $_POST['update'] ) ){
			$output = $output;
		} elseif( isset( $_POST['reset-img'] ) ){
			add_settings_error( 'wabe-error-img', 'restore_img_default', __( 'Default icon restored.', 'wabelang' ), 'updated fade' );
			$output['icolink'] = '';
			$this->remove_image_iconlink();
			
		}
		return $output;
	}
	public function remove_image_iconlink(){
		global $wpdb;
		$prefix = $wpdb->prefix;
		$id = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM " . $prefix . "posts" . " WHERE guid='" . $this->wabe_values['icolink'] . "';" ) );
		wp_delete_attachment( $id[0], true );
	}
	public function setting_act_link(){
		echo '<input type="checkbox" id="wabe-actlink" name="wabe-options[actlink]"' . checked('on', $this->wabe_values['actlink'], false) . '/>';
	}
	public function setting_ico_link(){
		echo '
		<p><input type="text" id="wabe-ico" size="60" name="wabe-options[icolink]" value="' . $this->wabe_values['icolink'] . '" /></p>
		';
		submit_button( __( 'Update icon', 'wabelang' ), 'secondary', 'submit-img', false );
		echo '<span>&nbsp;</span>';
		submit_button( __( 'Restore icon', 'wabelang' ), 'delete', 'reset-img', false );
		echo '
		<p class="description">' . __( 'Current: ', 'wabelang' ) . '<img align="absmiddle" src="' . $this->select_icolink() . '" /></p>
		';
	}
	public function setting_speed(){
		echo'
		<input type="text" id="wabe-speed" name="wabe-options[speed]" value="' . $this->wabe_values['speed'] . '" />
    	<span class="description">' . __( 'Default: 3000', 'wabelang' ) . '</span>
		';
	}
	public function setting_sensitivity(){
		echo '
		<input type="text" id="wabe-sensitivity" name="wabe-options[sensitivity]" value="' . $this->wabe_values['sensitivity'] . '" />
		<span class="description">' . __( 'Default: 4', 'wabelang' ) . '</span>
		';
	}
	public function setting_interval(){
		echo '
		<input type="text" id="wabe-interval" name="wabe-options[interval]" value="' . $this->wabe_values['interval'] . '" />
		<span class="description">' . __( 'Default: 50', 'wabelang' ) . '</span>
		';
	}
	public function setting_timeout(){
		echo '
		<input type="text" id="wabe-timeout" name="wabe-options[timeout]" value="' . $this->wabe_values['timeout'] . '" />
		<span class="description">' . __( 'Default: 200', 'wabelang' ) . '</span>
		';
	}
	public function wabe_plugin_action_links( $links, $file ){
    	if ( $file == plugin_basename(__FILE__) ){
      		$setting_link = '<a href="' . admin_url( 'options-general.php?page=wp-admin-bar-effect' ) . '">' . __( 'Options', 'wabelang' ) . '</a>';
	  		array_unshift( $links, $setting_link );
		}
    	return $links;
	}
	public function wabe_page_menu(){
		global $page;
		$page = add_options_page( 'WP Admin Bar Effect', 'wabe', 'manage_options', 'wp-admin-bar-effect', array( $this, 'wabe_page' ) );
		add_action( 'admin_print_styles-' . $page, array( $this, 'wabe_script_page' ) );
		add_action( 'load-' . $page, array( $this, 'wabe_add_help_tab' ) );
	}
	public function wabe_script_page(){
		wp_enqueue_media();
	}
	public function wabe_add_help_tab(){
    	global $page;
    	$screen = get_current_screen();
    	if ( $screen->id != $page )
			return;
    	$screen->add_help_tab( array( 'id' => 'wabe-help-one', 'title' => __( 'General options', 'wabelang' ), 'content' => '', 'callback' => array( $this, 'general_options' ) ) );
		$screen->add_help_tab( array( 'id' => 'wabe-help-two', 'title' => __( 'Advanced options', 'wabelang' ), 'content' => '', 'callback' => array( $this, 'hover_intent' ) ) );
	}
	public function general_options(){
		echo '
		<h5>' . __( 'Active link', 'wabelang' ) . '</h5>
		<p>' . __( 'Decide if it shows a link in the sidebar to your site.', 'wabelang' ) . '</p>
		<h5>' . __( 'Custom icon to link', 'wabelang' ) . '</h5>
		<p>' . __( 'Add custom icon on the link, is stored in the folder uploads if not using from url. ', 'wabelang' ) . '</p>
		<p>' . __( 'The image can not be greater than 30 by 32 pixels', 'wabelang' ) . '</p>
		<h5>' . __( 'Speed', 'wabelang' ) . '</h5>
		<p>' . __( 'Speed ​​is the option to change the speed of effect once the hover.', 'wabelang' ) . '</p>
		';
    }
    public function hover_intent(){
		echo '
		<h5>' . __( 'Sensitivity', 'wabelang' ) . '</h5>
		<p>' . __( 'If the mouse travels fewer than this number of pixels between polling intervals, then the "over" function will be called. With the minimum sensitivity threshold of 1, the mouse must not move between polling intervals. With higher sensitivity thresholds you are more likely to receive a false positive.', 'wabelang' ) . '</p>
		<h5>' . __( 'Interval', 'wabelang' ) . '</h5>
		<p>' . __( 'The number of milliseconds hoverIntent waits between reading/comparing mouse coordinates. When the user\'s mouse first enters the element its coordinates are recorded. The soonest the "over" function can be called is after a single polling interval. Setting the polling interval higher will increase the delay before the first possible "over" call, but also increases the time to the next point of comparison.', 'wabelang' ) . '</p>
		<h5>' . __( 'Timeout', 'wabelang' ) . '</h5>
		<p>' . __( 'A simple delay, in milliseconds, before the "out" function is called. If the user mouses back over the element before the timeout has expired the "out" function will not be called (nor will the "over" function be called). This is primarily to protect against sloppy/human mousing trajectories that temporarily (and unintentionally) take the user off of the target element... giving them time to return.', 'wabelang' ) . '</p>
		';
    }
	public function wabe_global_scripts(){
		//wp_register_script( 'wp_admin_bar_effect', plugins_url( 'javascript/dev/jquery.wabe.js', __FILE__), array( 'jquery', 'hoverIntent' ), $this->wabe_version );
		wp_register_script( 'wp_admin_bar_effect', plugins_url( 'javascript/jquery.wabe.min.js', __FILE__), array( 'jquery', 'hoverIntent' ), $this->wabe_version );
		wp_localize_script( 'wp_admin_bar_effect', 'wabe', array(
																'speed' => $this->wabe_values['speed'],
																'sensitivity' => $this->wabe_values['sensitivity'],
																'interval' => $this->wabe_values['interval'],
																'timeout' => $this->wabe_values['timeout'],
																'media_title' => __( 'Upload or Choose Your Icon File', 'wabelang' ),
																'media_button' => __( 'Insert Icon', 'wabelang' )
																)
		);
		wp_enqueue_script( 'wp_admin_bar_effect' );
		
	}
	public function wabe_img_style(){
		echo '
		<style type="text/css">
		#wabe div.wp-menu-image { background: url(' . $this->select_icolink() . ') no-repeat;}
		</style>
		';
	}
	public function add_menu_wabe(){
		global $menu;
		$name = get_bloginfo( 'name', 'display' );
		$urlw = home_url();
		if ( isset($this->wabe_values['actlink']) ) {
    		$menu['0'] = array( $name, 'read', $urlw, '', 'open-if-no-js menu-top menu-icon-wabe', 'wabe', 'div' );
		} else {
			return false;
		}
	}
	public function select_icolink(){
		$icolink = plugins_url( '/icon/wordpress.png', __FILE__);
		if( $this->wabe_values['icolink'] != '' )
			$icolink = $this->wabe_values['icolink'];
		return $icolink;
	}
	public function wabe_page(){
		echo '<div class="wrap">';
		screen_icon();
		echo '<h2>' . sprintf(__( 'WP Admin Bar Effect (wabe) Version: %s', 'wabelang' ), $this->wabe_version ) . '</h2>';
		echo '<form method="post" action="options.php">';
		settings_fields( 'wabe-register' ); 
		do_settings_sections( 'wabe_settings' );
		echo '<p>';
		submit_button( '', 'primary', 'submit', false );
		echo '<span>&nbsp;</span>';
		submit_button( __( 'Restore','wabelang' ), 'delete', 'reset', false );
		echo '</p>';
		echo '</form>';
		echo '</div>';
	}
}
$wabe = new wabe();
endif;
?>