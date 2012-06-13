<?php
/**
 * @package WP Admin Bar Effect (wabe)
 * @version 2.1.1
 */
/*
Plugin Name: WP Admin Bar Effect (wabe)
Plugin URI: http://wordpress.org/extend/plugins/wp-admin-bar-effect/
Description: Add effect slideDown to desktop top bar 
Author: Sergio P.A. ( 23r9i0 )
Version: 2.1.1
Author URI: http://dsergio.com/
*/
/*  Copyright 2011  Sergio Prieto Alvarez  (email : info@dsergio.com)

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
	private $wabe_version = '2.1.1';
	private $wabe_options;
	private $wabe_options_defaults = array( 'actlink' => '1', 'icolink' => '', 'speed' => '3000', 'sensitivity' => '4', 'interval' => '50', 'timeout' => '200' );
	public function wabe(){
		add_action( 'init', array( &$this, 'wabe_init' ) );
		add_action( 'admin_init', array( &$this, 'wabe_admin_options' ) );
		add_action( 'admin_menu', array( &$this, 'wabe_page_menu' ) );
		add_action( 'admin_footer', array( &$this, 'wabe_global_scripts' ) );
		add_filter( 'plugin_action_links', array( &$this, 'wabe_plugin_action_links' ), 10, 2 );
		register_activation_hook( __FILE__, array( &$this, 'wabe_activate' ) );
		register_deactivation_hook( __FILE__, array( &$this, 'wabe_deactivate' ) );
		add_action( 'admin_menu', array( &$this, 'add_menu_wabe' ) );
	}
	public function wabe_activate(){
		
		if ( get_option( 'wabe-version' ) == false ){
			$defaults_array = $this->wabe_options_defaults;
			$version = $this->wabe_version;			
			add_option( 'wabe-options', $defaults_array );
			add_option( 'wabe-version', $version );
		}
	}
	public function wabe_deactivate(){
				delete_option( 'wabe-options' );
				delete_option( 'wabe-version' );
	}
	public function wabe_init(){
		global $wp_version;
		if ( version_compare( $wp_version, '3.3', '< ' ) ){
			wp_die( __( 'This plugin requires WordPress 3.3 or greater.', 'wabelang' ) );
		}
  		load_plugin_textdomain( 'wabelang', false, dirname( plugin_basename(__FILE__) ) . '/languages/' );
	}
	public function wabe_admin_options(){
		register_setting( 'wabe-register', 'wabe-options', array(&$this, 'wabe_validate' ) );
		add_settings_section( 'wabe_general', '', '__return_false', 'wabe_settings' );
		add_settings_field( 'act_link', __( 'Active link:', 'wabelang' ), array(&$this, 'setting_act_link' ), 'wabe_settings', 'wabe_general', array( 'label_for' => 'wabe-actlink' ) );
		add_settings_field( 'ico_link', __( 'Custom Icon to link:', 'wabelang' ), array(&$this, 'setting_ico_link' ), 'wabe_settings', 'wabe_general', array( 'label_for' => 'wabe-ico' ) );
		add_settings_field( 'wabe_speed', __( 'Speed:', 'wabelang' ), array(&$this, 'setting_speed' ), 'wabe_settings', 'wabe_general', array( 'label_for' => 'wabe-speed' ) );
		add_settings_field( 'wabe_sensitivity', __( 'Sensitivity:', 'wabelang' ), array(&$this, 'setting_sensitivity' ), 'wabe_settings', 'wabe_general', array( 'label_for' => 'wabe-sensitivity' ) );
		add_settings_field( 'wabe_interval', __( 'Interval:', 'wabelang' ), array(&$this, 'setting_interval' ), 'wabe_settings', 'wabe_general', array( 'label_for' => 'wabe-interval' ) );
		add_settings_field( 'wabe_timeout', __( 'Timeout:', 'wabelang' ), array(&$this, 'setting_timeout' ), 'wabe_settings', 'wabe_general', array( 'label_for' => 'wabe-timeout' ) );
	}
	public function wabe_validate($output){
		$defaults = $this->wabe_options_defaults;
		if ( isset( $_POST['reset'] ) ){
			add_settings_error( 'wabe-error', 'restore_defaults', __( 'Default options restored.', 'wabelang' ), 'updated fade' );
			$output = $defaults;
		} elseif ( isset( $_POST['update'] ) ){
			$output = $output;
		} elseif( isset( $_POST['reset-img'] ) ){
			add_settings_error( 'wabe-error-img', 'restore_img_default', __( 'Default icon restored.', 'wabelang' ), 'updated fade' );
			$output['icolink'] = $defaults['icolink'];
		}
		return $output;
	}
	public function setting_act_link(){
	$wabe_values = get_option( 'wabe-options' );
?>
	<input type="checkbox" id="wabe-actlink" name="wabe-options[actlink]" <?php if ( isset($wabe_values['actlink'] ) && $wabe_values['actlink'] ) echo ' checked="yes"'; ?> />
<?php
	}
	public function setting_ico_link(){
	$wabe_values = get_option( 'wabe-options' );
?>
	<input type="text" id="wabe-ico" size="60" name="wabe-options[icolink]" value="<?php echo $wabe_values['icolink']; ?>" />
	<input id="wabe-img-button" type="button" value="<?php _e( 'Update image', 'wabelang' ); ?>" class="button-secondary" />
	<input id="reset_img" type="submit" name="reset-img" value="<?php _e( 'Restore', 'wabelang' ); ?>" class="button-secondary delete" />
	<p><span class="description"><?php _e( 'Current: ', 'wabelang' ); ?><img align="absmiddle" src="<?php echo $this->select_icolink(); ?>" /></span></p>
<?php
	}
	public function setting_speed(){
	$wabe_values = get_option( 'wabe-options' );
?>
	<input type="text" id="wabe-speed" name="wabe-options[speed]" value="<?php echo $wabe_values['speed']; ?>" />
    <span class="description"><?php _e( 'Default: 3000', 'wabelang' ); ?></span>
<?php
	}
	public function setting_sensitivity(){
	$wabe_values = get_option( 'wabe-options' );
?>
<input type="text" id="wabe-sensitivity" name="wabe-options[sensitivity]" value="<?php  echo $wabe_values['sensitivity']; ?>" />
<span class="description"><?php _e( 'Default: 4', 'wabelang' ); ?></span>
<?php
	}
	public function setting_interval(){
	$wabe_values = get_option( 'wabe-options' );
?>
<input type="text" id="wabe-interval" name="wabe-options[interval]" value="<?php echo $wabe_values['interval']; ?>" />
<span class="description"><?php _e( 'Default: 50', 'wabelang' ); ?></span>
<?php
	}
	public function setting_timeout(){
	$wabe_values = get_option( 'wabe-options' );
?>
<input type="text" id="wabe-timeout" name="wabe-options[timeout]" value="<?php echo $wabe_values['timeout']; ?>" />
<span class="description"><?php _e( 'Default: 200', 'wabelang' ); ?></span>
<?php
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
		$page = add_options_page( 'WP Admin Bar Effect', 'wabe', 'manage_options', 'wp-admin-bar-effect', array( &$this, 'wabe_page' ) );
		add_action( 'admin_print_styles-' . $page, array( &$this, 'wabe_styles_page' ) );
		add_action( 'load-' . $page, array( &$this, 'wabe_add_help_tab' ) );
	}
	public function wabe_add_help_tab(){
    	global $page;
    	$screen = get_current_screen();
    	if ( $screen->id != $page )
			return;
    	$screen->add_help_tab( array( 'id' => 'wabe-help-one', 'title' => __( 'General options', 'wabelang' ), 'content' => '', 'callback' => array( &$this, 'general_options' ) ) );
		$screen->add_help_tab( array( 'id' => 'wabe-help-two', 'title' => __( 'Advanced options', 'wabelang' ), 'content' => '', 'callback' => array( &$this, 'hover_intent' ) ) );
	}
	public function general_options(){
?>
<h5><?php _e( 'Active link', 'wabelang' ); ?></h5>
<p><?php _e( 'Decide if it shows a link in the sidebar to your site.', 'wabelang' ); ?></p>
<h5><?php _e( 'Custom icon to link', 'wabelang' ); ?></h5>
<p><?php _e( 'Add custom icon on the link, is stored in the folder uploads if not using from url. ', 'wabelang' ); ?></p>
<p><?php _e( 'The image can not be greater than 30 by 32 pixels', 'wabelang' ); ?></p>
<p><?php _e( 'If you want to completely remove the image should delete from the library.', 'wabelang' ); ?></p>
<h5><?php _e( 'Speed', 'wabelang' ); ?></h5>
<p><?php _e( 'Speed ​​is the option to change the speed of effect once the hover.', 'wabelang' ); ?></p>
<?php
    }
    public function hover_intent(){
?>
<h5><?php _e( 'Sensitivity', 'wabelang' ); ?></h5>
<p><?php _e( 'If the mouse travels fewer than this number of pixels between polling intervals, then the "over" function will be called. With the minimum sensitivity threshold of 1, the mouse must not move between polling intervals. With higher sensitivity thresholds you are more likely to receive a false positive.', 'wabelang' ); ?></p>
<h5><?php _e( 'Interval', 'wabelang' ); ?></h5>
<p><?php _e( 'The number of milliseconds hoverIntent waits between reading/comparing mouse coordinates. When the user\'s mouse first enters the element its coordinates are recorded. The soonest the "over" function can be called is after a single polling interval. Setting the polling interval higher will increase the delay before the first possible "over" call, but also increases the time to the next point of comparison.', 'wabelang' ); ?></p>
<h5><?php _e( 'Timeout', 'wabelang' ); ?></h5>
<p><?php _e( 'A simple delay, in milliseconds, before the "out" function is called. If the user mouses back over the element before the timeout has expired the "out" function will not be called (nor will the "over" function be called). This is primarily to protect against sloppy/human mousing trajectories that temporarily (and unintentionally) take the user off of the target element... giving them time to return.', 'wabelang' ); ?></p>
<?php
    }
	public function wabe_global_scripts(){
		wp_enqueue_script( 'wp_admin_bar_effect', plugins_url( 'javascript/jquery.wabe.min.js', __FILE__), array( 'jquery', 'hoverIntent', 'media-upload', 'thickbox' ), $this->wabe_version, false );
		$this->wabe_loading_options();
	}
	public function wabe_styles_page(){
		wp_enqueue_style( 'thickbox' );
	}
	public function add_menu_wabe(){
		$wabe_values = get_option( 'wabe-options' );
		global $menu;
		$name = get_bloginfo( 'name', 'display' );
		$urlw = home_url();
		if ( isset($wabe_values['actlink']) && $wabe_values['actlink'] ) {
    	$menu['0'] = array( $name, 'read', $urlw, '', 'open-if-no-js menu-top menu-icon-wabe', 'wabe', 'div' );	
		} else {
			return false;
		}
	}
	public function select_icolink(){
		$wabe_values = get_option( 'wabe-options' );
		if( empty($wabe_values['icolink']) ):
			$icolink = plugins_url( '/icon/wordpress.png', __FILE__);
		else :
			$icolink = $wabe_values['icolink'];
		endif;
		return $icolink;
	}
	public function wabe_loading_options(){
		$wabe_values = get_option( 'wabe-options' );
?>
	<!-- Inserted by plugin WP Admin Bar Effect (wabe) /*****************************************************/	Añadido por el plugin WP Admin Bar Effect (wabe) -->
	<style type="text/css">#wpadminbar{top:-24px}#wpwrap{top:-28px}div.quicklinks{display:none}body.admin-bar #wpcontent,body.admin-bar #adminmenu{padding-top:4px}#wabe .wp-menu-image {background:url(<?php echo $this->select_icolink(); ?> ) no-repeat}</style>
	<script>jQuery(document).ready(function($){$( 'body' ).wp_admin_bar_effect({wabe_speed:<?php echo $wabe_values['speed'];?>,wabe_sensitivity:<?php echo $wabe_values['sensitivity'];?>,wabe_interval:<?php echo $wabe_values['interval'];?>,wabe_timeout:<?php echo $wabe_values['timeout'];?>});});</script> 
	<!-- Inserted by plugin WP Admin Bar Effect (wabe) /*****************************************************/	Añadido por el plugin WP Admin Bar Effect (wabe) -->
<?php
	}
	public function wabe_page(){
	settings_errors();
?>
<div class="wrap">
	<?php screen_icon(); ?>
	<h2><?php _e( 'WP Admin Bar Effect (wabe) version:', 'wabelang' ); ?><?php echo $this->wabe_version; ?></h2>
<form method="post" action="options.php">
	<?php settings_fields( 'wabe-register' ); 
	do_settings_sections( 'wabe_settings' );
	echo '<p class="submit">';
	submit_button( '', 'primary', 'submit', false );
	submit_button(__( 'Restore','wabelang' ),'secondary','reset',false,'onClick="return wabe_alert_reset();"' );
	echo '</p>';
?>
</form>
<script type="text/javascript">
	var alert_reset='<?php _e( 'Are you sure you want to restore WP Admin Bar Effect (wabe) to default settings?','wabelang' ); ?>';
	function wabe_alert_reset(){if(confirm(alert_reset)==true){return true;}else{return false;}}
</script> 
</div>
<?php
	}
} $__wabe = new wabe();
endif;
?>