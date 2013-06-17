<?php
/*
Plugin Name: WP Admin Bar Effect (wabe)
Plugin URI: http://wordpress.org/extend/plugins/wp-admin-bar-effect/
Description: Add effect slideDown to desktop top bar 
Author: Sergio P.A. ( 23r9i0 )
Version: 2.4
Author URI: http://dsergio.com/
*/
/*  Copyright 2013  Sergio Prieto Alvarez  ( email : info@dsergio.com )

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General License as published by
    the Free Software Foundation; either version 2 of the License, or
    ( at your option ) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General License for more details.

    You should have received a copy of the GNU General License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if( !class_exists( 'WP_ADMIN_BAR_EFFECT' ) ) : 
class WP_ADMIN_BAR_EFFECT {
	private 
		$wabe_version = '2.4',
		$wabe_options,
		$wabe_options_defaults = array(
			'wabe_active_link'		=> '2',
			'wabe_target_link' 		=> '1',
			'wabe_icon_link'		=> '',
			'wabe_speed'			=> '3000',
			'wabe_sensitivity'		=> '4',
			'wabe_interval'			=> '50',
			'wabe_timeout'			=> '200'
		);
	function __construct(){
		add_action( 'init', array( $this, 'wabe_init' ) );
		add_action( 'admin_init', array( $this, 'wabe_admin_options' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'wabe_global_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'wabe_global_styles' ) );
		add_action( 'admin_menu', array( $this, 'wabe_page_menu' ) );
		add_action( 'admin_menu', array( $this, 'add_menu_wabe' ) );
		add_filter( 'plugin_action_links', array( $this, 'wabe_plugin_action_links' ), 10, 2 );
		register_activation_hook( __FILE__, array( $this, 'wabe_activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'wabe_deactivate' ) );
		$this->wabe_options = get_option( 'wabe-options' );
	}
	function wabe_activate(){
		if( isset( $this->wabe_options['icolink'] ) ){
			foreach( $this->wabe_options as $option => $value ){
				foreach( $this->wabe_options_defaults as $doption => $dvalue ){
					if( $option == substr( $doption, 5 ) ){
						$this->wabe_options_defaults[$doption] = $this->wabe_options[$option];
					} else {
						if( !isset( $this->wabe_options['actlink'] ) )
							$this->wabe_options_defaults['wabe_active_link'] = '1';		
						$this->wabe_options_defaults['wabe_icon_link'] = $this->wabe_options['icolink'];
					}
				}
			}
			update_option( 'wabe-options', $this->wabe_options_defaults );
		} else {
			add_option( 'wabe-options', $this->wabe_options_defaults );
		}
	}
	function wabe_deactivate(){
		delete_option( 'wabe-options' );
	}
	function wabe_init(){
		global $wp_version;
		if ( version_compare( $wp_version, '3.5', '< ' ) )
			wp_die( __( 'This plugin requires WordPress 3.5 or greater.', 'wabelang' ) );

  		load_plugin_textdomain( 'wabelang', false, dirname( plugin_basename(__FILE__) ) . '/include/languages/' );
	}
	function wabe_plugin_action_links( $links, $file ){
    	if ( $file == plugin_basename(__FILE__) ){
      		$setting_wabe_link = '<a href="' . admin_url( 'options-general.php?page=wp-admin-bar-effect' ) . '">' . __( 'Options', 'wabelang' ) . '</a>';
	  		array_unshift( $links, $setting_wabe_link );
		}
    	return $links;
	}
	function wabe_admin_options(){
		register_setting( 'wabe-register', 'wabe-options', array($this, 'wabe_validate' ) );
		add_settings_section( 'wabe_general', '', '__return_false', 'wabe_settings' );
		add_settings_field( 'wabe_active_link', __( 'Active link:', 'wabelang' ), array($this, 'setting_wabe_active_link' ), 'wabe_settings', 'wabe_general', array( 'label_for' => 'wabe-active-link' ) );
		add_settings_field( 'wabe_target_link', __( 'Define target link:', 'wabelang' ), array($this, 'setting_wabe_target_link' ), 'wabe_settings', 'wabe_general', array( 'label_for' => 'wabe-target-link' ) );
		add_settings_field( 'wabe_icon_link', __( 'Custom Icon to link:', 'wabelang' ), array($this, 'setting_wabe_icon_link' ), 'wabe_settings', 'wabe_general', array( 'label_for' => 'wabe-icon' ) );
		add_settings_field( 'wabe_speed', __( 'Speed:', 'wabelang' ), array($this, 'setting_wabe_speed' ), 'wabe_settings', 'wabe_general', array( 'label_for' => 'wabe-speed' ) );
		add_settings_field( 'wabe_header',  __('<strong>Advanced Settings</strong>','wabelang'), '__return_false', 'wabe_settings', 'wabe_general' );
		add_settings_field( 'wabe_sensitivity', __( 'Sensitivity:', 'wabelang' ), array($this, 'setting_wabe_sensitivity' ), 'wabe_settings', 'wabe_general', array( 'label_for' => 'wabe-sensitivity' ) );
		add_settings_field( 'wabe_interval', __( 'Interval:', 'wabelang' ), array($this, 'setting_wabe_interval' ), 'wabe_settings', 'wabe_general', array( 'label_for' => 'wabe-interval' ) );
		add_settings_field( 'wabe_timeout', __( 'Timeout:', 'wabelang' ), array($this, 'setting_wabe_timeout' ), 'wabe_settings', 'wabe_general', array( 'label_for' => 'wabe-timeout' ) );
	}
	function wabe_validate( $input ){
		$output = array();
		foreach( $input as $key => $value ){
			if( isset( $input[$key] ) )
				$output[$key] = strip_tags( stripslashes( $input[$key] ) );
		}
		if( !isset($output['wabe_target_link']) )
			$output['wabe_target_link'] = '0';
		if( isset( $_POST['reset-img'] ) ){
			add_settings_error( 'wabe-error-img', 'restore_img_default', __( 'Default icon restored.', 'wabelang' ), 'updated fade' );
			if($output['wabe_icon_link'] != '')
				$this->remove_image_iconlink();
			$output['wabe_icon_link'] = '';
		}		
		return apply_filters( 'wabe_validate', $output, $input );
	}
	function remove_image_iconlink(){
		if( $this->wabe_options['wabe_icon_link'] != '' ){
			global $wpdb;
			$id = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE guid='%s'", $this->wabe_options['wabe_icon_link'] ) );
			wp_delete_attachment( $id[0], true );
		}
	}
	function setting_wabe_active_link(){
?>
<p>
  <label>
    <input id="r-disable" class="wabe-checked" type="radio" value="1" name="wabe-options[wabe_active_link]" <?php checked( '1', $this->wabe_options['wabe_active_link'], true ); ?> />
    <span>
    <?php _e( 'Disabled', 'wabelang' ); ?>
    </span> </label>
</p>
<p>
  <label>
    <input id="r-default" class="wabe-checked" type="radio" value="2" name="wabe-options[wabe_active_link]" <?php checked( '2', $this->wabe_options['wabe_active_link'], true ); ?> />
    <span>
    <?php _e( 'Add Link and Separator', 'wabelang' ); ?>
    </span> </label>
</p>
<p>
  <label>
    <input id="r-sep" class="wabe-checked" type="radio" value="3" name="wabe-options[wabe_active_link]" <?php checked( '3', $this->wabe_options['wabe_active_link'], true ); ?> />
    <span>
    <?php _e( 'Add only Link', 'wabelang' ); ?>
    </span> </label>
</p>
<?php
	}
	function setting_wabe_target_link(){
?>
<input type="checkbox" id="wabe-target-link" name="wabe-options[wabe_target_link]" value="1" <?php checked( '1', $this->wabe_options['wabe_target_link'], true ); ?> />
<?php
	}
	function setting_wabe_icon_link(){
?>
<p>
  <input type="text" id="wabe-icon" size="60" name="wabe-options[wabe_icon_link]" value="<?php echo $this->wabe_options['wabe_icon_link']; ?>" />
</p>
<?php submit_button( __( 'Update icon', 'wabelang' ), 'secondary', 'submit-img', false ); ?>
<span>&nbsp;</span>
<?php submit_button( __( 'Restore icon', 'wabelang' ), 'delete', 'reset-img', false ); ?>
<p class="description">
  <?php _e( 'Default: ', 'wabelang' ); ?>
  <img align="absmiddle" src="<?php echo plugins_url( 'include/icon/wordpress.png', __FILE__); ?>"/> &nbsp;
  <?php _e( 'Current: ', 'wabelang' ); ?>
  <img align="absmiddle" src="<?php echo $this->select_wabe_icon_link(); ?>"/> </p>
<?php
	}
	function setting_wabe_speed(){
?>
<input type="text" id="wabe-speed" name="wabe-options[wabe_speed]" value="<?php echo $this->wabe_options['wabe_speed']; ?>" />
<span class="description">
<?php _e( 'Default: 3000', 'wabelang' ); ?>
</span>
<?php
	}
	function setting_wabe_sensitivity(){
?>
<input type="text" id="wabe-sensitivity" name="wabe-options[wabe_sensitivity]" value="<?php echo $this->wabe_options['wabe_sensitivity']; ?>" />
<span class="description">
<?php _e( 'Default: 4', 'wabelang' ); ?>
</span>
<?php
	}
	function setting_wabe_interval(){
?>
<input type="text" id="wabe-interval" name="wabe-options[wabe_interval]" value="<?php echo $this->wabe_options['wabe_interval']; ?>" />
<span class="description">
<?php _e( 'Default: 50', 'wabelang' ); ?>
</span>
<?php
	}
	function setting_wabe_timeout(){
?>
<input type="text" id="wabe-timeout" name="wabe-options[wabe_timeout]" value="<?php echo $this->wabe_options['wabe_timeout']; ?>" />
<span class="description">
<?php _e( 'Default: 200', 'wabelang' ); ?>
</span>
<?php
	}
	function wabe_page_menu(){
		global $wabe_page;
		$wabe_page = add_options_page( 'WP Admin Bar Effect', 'wabe', 'manage_options', 'wp-admin-bar-effect', array( $this, 'wabe_page' ) );
		add_action( 'admin_print_styles-' . $wabe_page, array( $this, 'wabe_script_page' ) );
		add_action( 'load-' . $wabe_page, array( $this, 'wabe_add_help_tab' ) );
	}
	function wabe_script_page(){
		wp_enqueue_media();
	}
	function wabe_global_scripts(){
		//wp_register_script( 'wp_admin_bar_effect', plugins_url( 'include/javascript/dev/jquery.wabe.js', __FILE__), array( 'jquery', 'hoverIntent' ), $this->wabe_version );
		wp_register_script( 'wp_admin_bar_effect', plugins_url( 'include/javascript/jquery.wabe.min.js', __FILE__ ), array( 'jquery', 'hoverIntent' ), $this->wabe_version );
		wp_localize_script(
			'wp_admin_bar_effect',
			'wabe', 
			array( 
				'speed' => $this->wabe_options['wabe_speed'],
				'sensitivity' => $this->wabe_options['wabe_sensitivity'],
				'interval' => $this->wabe_options['wabe_interval'],
				'timeout' => $this->wabe_options['wabe_timeout'],
				'media_title' => __( 'Upload or Choose Your Icon File', 'wabelang' ),
				'media_button' => __( 'Insert Icon', 'wabelang' ),
				'target_link' => $this->wabe_options['wabe_target_link']
			)
		);
		wp_enqueue_script( 'wp_admin_bar_effect' );		
	}
	function wabe_global_styles(){
?>
	<style type="text/css">
		#wabe div.wp-menu-image { background: url(<?php echo $this->select_wabe_icon_link(); ?>) no-repeat center center;}
		body.wp-admin.js #wpwrap {margin-top:-24px; padding-bottom:24px;}
		body.js div.quicklinks { display:none}
		body.js #wpadminbar { height: 4px}
	</style>
<?php
	}
	function add_menu_wabe(){
		global $menu;
		$name = get_bloginfo( 'name', 'display' );
		$urlw = home_url();
		if ( $this->wabe_options['wabe_active_link'] != '1' )
		// Define menu -999999998 prevent overwrite other customs menu
    		$menu['-999999998'] = array( '0' => $name, '1' => 'read', '2' => $urlw, '3' => '', '4' => 'menu-top menu-top-first menu-icon-wabe', '5' => 'wabe', '6' => 'div' );
		if ( $this->wabe_options['wabe_active_link'] === '2' )
		// Define menu -999999999 prevent overwrite other customs menu
			$menu['-999999999'] = array( '0' => '', '1' => 'read', '2' => 'separator-999999999', '3' => '', '4' => 'wp-menu-separator' );
	}
	function select_wabe_icon_link(){
		$wabe_icon_link = plugins_url( 'include/icon/wordpress.png', __FILE__);
		if( $this->wabe_options['wabe_icon_link'] != '' )
			$wabe_icon_link = $this->wabe_options['wabe_icon_link'];
		return $wabe_icon_link;
	}
	function wabe_page(){
?>
<div class="wrap">
  <?php screen_icon(); ?>
  <h2> <?php printf(__( 'WP Admin Bar Effect (wabe) Version: %s', 'wabelang' ), $this->wabe_version ); ?> </h2>
  <noscript><span style="color:#F00"><?php _e('Enable javascript to work plugin options!!!', 'wabelang' ); ?></span></noscript>
  <form method="post" action="options.php">
    <?php 
		settings_fields( 'wabe-register' ); 
		do_settings_sections( 'wabe_settings' );
		submit_button( '', 'primary', 'submit' );
		?>
  </form>
</div>
<?php
	}
	function wabe_add_help_tab(){
		global $wabe_page;
    	$screen = get_current_screen();
    	if ( $screen->id != $wabe_page )
			return;
    	$screen->add_help_tab( array( 'id' => 'wabe-help-one', 'title' => __( 'General options', 'wabelang' ), 'content' => '', 'callback' => array( $this, 'general_options' ) ) );
		$screen->add_help_tab( array( 'id' => 'wabe-help-two', 'title' => __( 'Advanced options', 'wabelang' ), 'content' => '', 'callback' => array( $this, 'hover_intent' ) ) );
	}
	function general_options(){
?>
<h5>
  <?php _e( 'Active link', 'wabelang' ); ?>
</h5>
<p>
  <?php _e( 'Decide if it shows a link in the sidebar to your site.', 'wabelang' ); ?>
</p>
<p>
  <?php _e( 'Add Link and Separator decide if it shows a separator after link or Add only Link show link without separator', 'wabelang' ); ?>
</p>
<h5>
  <?php _e( 'Define target link', 'wabelang' ); ?>
</h5>
<p>
  <?php _e( 'Define target _blank or none. Default _blank', 'wabelang' ); ?>
</p>
<h5>
  <?php _e( 'Custom icon to link', 'wabelang' ); ?>
</h5>
<p>
  <?php _e( 'Add custom icon on the link, is stored in the folder uploads if not using from url. ', 'wabelang' ); ?>
</p>
<p>
  <?php _e( 'The image can not be greater than 16 by 16 pixels', 'wabelang' ); ?>
</p>
<h5>
  <?php _e( 'Speed', 'wabelang' ); ?>
</h5>
<p>
  <?php _e( 'Speed ​​is the option to change the speed of effect once the hover.', 'wabelang' ); ?>
</p>
<?php
    }
    function hover_intent(){
?>
<h5>
  <?php _e( 'Sensitivity', 'wabelang' ); ?>
</h5>
<p>
  <?php _e( 'If the mouse travels fewer than this number of pixels between polling intervals, then the "over" function will be called. With the minimum sensitivity threshold of 1, the mouse must not move between polling intervals. With higher sensitivity thresholds you are more likely to receive a false positive.', 'wabelang' ); ?>
</p>
<h5>
  <?php _e( 'Interval', 'wabelang' ); ?>
</h5>
<p>
  <?php _e( 'The number of milliseconds hoverIntent waits between reading/comparing mouse coordinates. When the user\'s mouse first enters the element its coordinates are recorded. The soonest the "over" function can be called is after a single polling interval. Setting the polling interval higher will increase the delay before the first possible "over" call, but also increases the time to the next point of comparison.', 'wabelang' ); ?>
</p>
<h5>
  <?php _e( 'Timeout', 'wabelang' ); ?>
</h5>
<p>
  <?php _e( 'A simple delay, in milliseconds, before the "out" function is called. If the user mouses back over the element before the timeout has expired the "out" function will not be called (nor will the "over" function be called). This is primarily to protect against sloppy/human mousing trajectories that temporarily (and unintentionally) take the user off of the target element... giving them time to return.', 'wabelang' ); ?>
</p>
<?php
    }
}
$wabe = new WP_ADMIN_BAR_EFFECT;
endif;
?>
