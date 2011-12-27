<?php
/**
 * @package WP Admin Bar Effect (wabe)
 * @version 1.0
 */
/*
Plugin Name: WP Admin Bar Effect (wabe)
Plugin URI: http://wordpress.org/extend/plugins/wp-admin-bar-effect/
Description: Add effect slideDown to desktop top bar
Author: 23r9i0
Version: 1.0
Author URI: http://dsergio.com/
*/
/*  Copyright 2011  Sergio Prieto Alvarez  (email : 23r9i0@gmail.com)

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
if( !class_exists('wabe') ) : 
class wabe {
	private $wabe_version = '1.0';
	private $wabe_options;
	private $wabe_options_defaults = array( 'actlink' => 'true', 'speed' => '3000', 'sensitivity' => '4', 'interval' => '50', 'timeout' => '200', 'uninstall'	=> '' );
	public function wabe() {
		add_action('init', array( &$this, 'wabe_init' ) );
		add_action( 'admin_init', array( &$this, 'wabe_admin_options' ) );
		add_action( 'admin_menu', array( &$this, 'wabe_page_menu' ) );
		add_action( 'admin_footer', array( &$this, 'wabe_global_scripts' ) );
		add_filter( 'plugin_action_links', array( &$this, 'wabe_plugin_action_links' ), 10, 2 );
		register_activation_hook( __FILE__, array( &$this, 'wabe_activate' ) );
		register_deactivation_hook( __FILE__, array( &$this, 'wabe_deactivate' ) );
	}
	public function wabe_activate() {
		
		if ( get_option( 'wabe-version' ) == false ) {
			$defaults_array = $this->wabe_options_defaults;
			$version = $this->wabe_version;			
			add_option( 'wabe-options', $defaults_array );
			add_option( 'wabe-version', $version );
		}
	}
	public function wabe_deactivate() {
		$wabe_values = get_option( 'wabe-options' );
			if ( isset($wabe_values['uninstall']) && $wabe_values['uninstall'] )	
				delete_option( 'wabe-options' );
				delete_option( 'wabe-version' );
	}
	public function wabe_init() {
		global $wp_version;
		if (version_compare($wp_version, '3.3', '< ')) {
			wp_die( __('This plugin requires WordPress 3.3 or greater.', 'wabelang') );
		}
		if (version_compare(PHP_VERSION, '5.0.0', '<')) {
			wp_die( __('This plugin requires PHP 5 or greater.', 'wabelang') );
		}
  		load_plugin_textdomain( 'wabelang', false, dirname( plugin_basename(__FILE__) ) . '/languages/' );
	}
	public function wabe_admin_options() {
		$wabe_values = get_option( 'wabe-options' );
		if ( isset($_GET['page']) && $_GET['page'] == 'wp-admin-bar-effect' ) {
			if ( isset($_REQUEST['action']) && 'update' == $_REQUEST['action'] ) {
				$wabe_values = stripslashes_deep( $_POST['wabe-options'] );
				$wabe_values = array_map( 'convert_chars', $wabe_values );	
				update_option( 'wabe-options', $wabe_values );
				wp_safe_redirect( add_query_arg('updated', 'true') );
				die;
			} else if ( isset($_REQUEST['action']) && 'reset' == $_REQUEST['action'] ) {
				$defaults_array = $this->wabe_options_defaults;
				update_option( 'wabe-options', $defaults_array );
				wp_safe_redirect( add_query_arg('reset', 'true') );
				die;
			}
		}
		register_setting( 'wabe-register', 'wabe-options');
	}
	public function wabe_plugin_action_links( $links, $file ) {
    	if ( $file == plugin_basename(__FILE__) ) {
      		$setting_link = '<a href="' . admin_url( 'options-general.php?page=wp-admin-bar-effect' ) . '">' . __('Options', 'wabelang') . '</a>';
	  		array_unshift( $links, $setting_link );
		}
    	return $links;
	}
	public function wabe_page_menu () {
		global $page;
		$page = add_options_page( 'WP Admin Bar Effect', 'wabe', 'manage_options', 'wp-admin-bar-effect', array( &$this, 'wabe_page' ) );
		add_action( 'admin_print_scripts-' . $page, array( &$this, 'wabe_scripts_page' ) );
		add_action( 'admin_print_styles-' . $page, array( &$this, 'wabe_styles_page' ) );
		add_action( 'load-' . $page, array( &$this, 'wabe_add_help_tab' ) );
	}
	public function wabe_add_help_tab () {
    	global $page;
    	$screen = get_current_screen();
    	if ( $screen->id != $page )
			return;
    	$screen->add_help_tab( array( 'id' => 'wabe-help-one', 'title' => __('General options', 'wabelang'), 'content' => '', 'callback' => array( &$this, 'general_options' ) ) );
		$screen->add_help_tab( array( 'id' => 'wabe-help-two', 'title' => __('HoverIntent options', 'wabelang'), 'content' => '', 'callback' => array( &$this, 'hover_intent' ) ) );
		$screen->add_help_tab( array( 'id' => 'wabe-help-three', 'title' => __('Uninstall option', 'wabelang'), 'content' => '', 'callback' => array( &$this, 'help_uninstall' ) ) );
	}
	public function general_options() {
?>
    		<div class="wabe-border">
    			<h5><?php _e('Active link', 'wabelang'); ?></h5>
    			<p><?php _e('Decide if it shows a link in the sidebar to your site.', 'wabelang'); ?></p>
    		</div>
    		<div class="wabe-border">
    			<h5><?php _e('Speed', 'wabelang'); ?></h5>
    			<p><?php _e('Speed ​​is the option to change the speed of effect once the hover.', 'wabelang'); ?></p>
    		</div>
<?php
     }
     public function hover_intent() {
?>
    		<div class="wabe-border">
    			<h5><?php _e('Sensitivity', 'wabelang'); ?></h5>
    			<p><?php _e('If the mouse travels fewer than this number of pixels between polling intervals, then the "over" function will be called. With the minimum sensitivity threshold of 1, the mouse must not move between polling intervals. With higher sensitivity thresholds you are more likely to receive a false positive.', 'wabelang'); ?></p>
    		</div>
    		<div class="wabe-border">
    			<h5><?php _e('Interval', 'wabelang'); ?></h5>
    			<p><?php _e('The number of milliseconds hoverIntent waits between reading/comparing mouse coordinates. When the user\'s mouse first enters the element its coordinates are recorded. The soonest the "over" function can be called is after a single polling interval. Setting the polling interval higher will increase the delay before the first possible "over" call, but also increases the time to the next point of comparison.', 'wabelang'); ?></p>
    		</div>
    		<div class="wabe-border">
    			<h5><?php _e('Timeout', 'wabelang'); ?></h5>
    			<p><?php _e('A simple delay, in milliseconds, before the "out" function is called. If the user mouses back over the element before the timeout has expired the "out" function will not be called (nor will the "over" function be called). This is primarily to protect against sloppy/human mousing trajectories that temporarily (and unintentionally) take the user off of the target element... giving them time to return.', 'wabelang'); ?></p>
    		</div>
<?php
     }
     public function help_uninstall() {
?>
    		<div class="wabe-border">
    			<h5><?php _e('Uninstall', 'wabelang'); ?></h5>
    			<p><?php _e('Like many other plugins, WP Admin Bar Effect (wabe) stores its settings on your WordPress options database table. Actually, these settings are not using more than a couple of kilobytes of space, but if you want to completely uninstall this plugin, check the option below, then save changes, and <strong>when you deactivate the plugin</strong>, all its settings will be removed from the database.', 'wabelang'); ?></p>
    		</div>
<?php
	 }
	public function wabe_global_scripts () {
		wp_enqueue_script('wp_admin_bar_effect', plugins_url('javascript/jquery.wabe.min.js', __FILE__), array('jquery', 'hoverIntent'), $this->wabe_version, false );
		$this->wabe_loading_options();
	}
	public function wabe_scripts_page () {
		wp_enqueue_script( 'wabe-tabs', plugins_url('javascript/jquery.ui.wabe.tabs.js', __FILE__ ), array( 'jquery', 'jquery-ui-core', 'jquery-ui-tabs' ), $this->wabe_version, false );
	}
	public function wabe_styles_page () {
		wp_enqueue_style( 'wabe-style', plugins_url('css/wabe-style.css', __FILE__), '', $this->wabe_version, 'all' );
		wp_enqueue_style( 'wabe-tabs-style', plugins_url('css/tabs-ui/jquery.ui.1.8.6.wabe.tabs.css', __FILE__), '', '1.8.6', 'all' );
	}
	public function wabe_link () {
		printf( __( '%1$s Go to %2$s%3$s', 'wabelang' ),
'<a class="menu-top" href="' .esc_url( get_home_url( '/' ) ) . '" title="',	esc_attr( get_bloginfo( 'name', 'display' ) ), '" target="_blank" rel="bookmark"><span id="wabe-icon">' . esc_attr( get_bloginfo( 'name', 'display' ) ) . '</span></a>'
		);
	}
	public function wabe_loading_options() {
		$wabe_values = get_option( 'wabe-options' );
?>
<!--
	Añadido por el plugin WP Admin Bar Effect (wabe)
/*****************************************************/
	 Inserted by plugin WP Admin Bar Effect (wabe)  
-->
<style type="text/css">
	#wpadminbar{top: -24px;}
	div.quicklinks{display:none;}
	body.admin-bar #wpcontent, body.admin-bar #adminmenu{padding-top: 4px;}
	#wabe-icon { background: url(<?php echo esc_url( get_home_url( '/' ) ); ?>/wp-content/plugins/wp-admin-bar-effect/css/images/wordpress.png) no-repeat left center; padding-left: 22px; }
</style>
<script type="text/javascript">
	/* <![CDATA[ */				
	jQuery(document).ready(function($){
		$('body, html').wp_admin_bar_effect({
			wabe_active_link: <?php if ( isset($wabe_values['actlink']) && $wabe_values['actlink'] ) { echo 'true'; } else { echo 'false'; } ?>, 
			wabe_url: '<?php $this->wabe_link(); ?>',
			wabe_speed: <?php echo $wabe_values['speed']; ?>,
			wabe_sensitivity: <?php echo $wabe_values['sensitivity']; ?>,
			wabe_interval: <?php echo $wabe_values['interval']; ?>,
			wabe_timeout: <?php echo $wabe_values['timeout']; ?>	
		});
	});
	/* ]]> */
</script>
<!--
	Inserted by plugin WP Admin Bar Effect (wabe)
/*****************************************************/
	Añadido por el plugin WP Admin Bar Effect (wabe)
-->
<?php
	}
	public function wabe_page () {
		$wabe_values = get_option( 'wabe-options' );
?>
	<div class="wrap">
    <div id="icon-options-general" class="icon32"></div>
  	<h2><?php _e('WP Admin Bar Effect (wabe) version:', 'wabelang'); ?> <?php echo $this->wabe_version; ?></h2>
<?php            
	if ( isset($_REQUEST['reset']) && $_REQUEST['reset'] ) {
		printf(  __('%1$sWP Admin Bar Effect (wabe) settings have been reset.%2$s', 'wabelang'), '<div id="message" class="updated fade"><p><strong>', '</strong></p></div>' );
	}
	if( isset($wabe_values['uninstall']) && $wabe_values['uninstall'] ){ 
		printf( __('%1$s Uninstall WP Admin Bar Effect (wabe) %2$s', 'wabelang'), '<div id="message" class="updated fade"><p><strong><a href="'. esc_url( get_home_url( '/' ) ) . '/wp-admin/plugins.php">', '</a></strong></p></div>' );
	}
?>
  	<div id="tabs" class="wrap-content">
        <form method="post" id="wabe-form" action="">
        	<?php settings_fields( 'wabe-register' ); ?>
    	<ul>
    		<li><a href="#options"><?php _e('Options', 'wabelang'); ?></a></li>
        	<li><a href="#uninstall"><?php _e('Uninstall', 'wabelang'); ?></a></li>
        	<li><a href="#info"><?php _e('Info', 'wabelang'); ?></a></li>
    	</ul>
    	<div id="options">
    		<table class="wabe-table-form"><tbody>
            <tr>
            <th scope="row">
            <label for="wabe-actlink"><?php _e('Active link to site:', 'wabelang'); ?></label>
            </th>
            <td>
			<input type="checkbox" name="wabe-options[actlink]" id="wabe-actlink"<?php if ( isset($wabe_values['actlink'] ) && $wabe_values['actlink'] ) echo ' checked="yes"'; ?> />
            </td>
            <td>
            <span><?php _e('Default: on <small>(Checked)</small>', 'wabelang'); ?></span>
            </td>
            </tr>
            <tr>
            <th scope="row">
			<label for="wabe-speed"><?php _e('Change the Speed:', 'wabelang'); ?></label>
            </th>
            <td>
			<input type="text" id="wabe-speed" name="wabe-options[speed]" value="<?php echo $wabe_values['speed']; ?>" />
            </td>
            <td>
            <span><?php _e('Default: 3000', 'wabelang'); ?></span>
            </td>
            </tr>
            <tr>
            <th scope="row">
			<label for="wabe-sensitivity"><?php _e('Change the Sensitivity:', 'wabelang'); ?></label>
            </th>
            <td>
			<input type="text" id="wabe-sensitivity" name="wabe-options[sensitivity]" value="<?php echo $wabe_values['sensitivity']; ?>" />
            </td>
            <td>
            <span><?php _e('Default: 4', 'wabelang'); ?></span>
            </td>
            </tr>
            <tr>
            <th scope="row">
			<label for="wabe-interval"><?php _e('Change the Interval:', 'wabelang'); ?></label>
            </th>
            <td>
			<input type="text" id="wabe-interval" name="wabe-options[interval]" value="<?php echo $wabe_values['interval']; ?>" />
            </td>
            <td>
            <span><?php _e('Default: 50', 'wabelang'); ?></span>
            </td>
            </tr>
            <tr>
            <th scope="row">
			<label for="wabe-timeout"><?php _e('Change the Timeout:', 'wabelang'); ?></label>
            </th>
            <td>
			<input type="text" id="wabe-timeout" name="wabe-options[timeout]" value="<?php echo $wabe_values['timeout']; ?>" />
        	</td>
            <td>
            <span><?php _e('Default: 200', 'wabelang'); ?></span>
            </td>
            </tr>
            </tbody></table>
    	</div>
    	<!-- // options -->
    	<div id="uninstall">
    		<label for="wabe-uninstall"><?php _e('Delete options within the database to disable the plugin in the plugins page', 'wabelang'); ?></label>	
			<p><input type="checkbox" name="wabe-options[uninstall]" id="wabe-uninstall"<?php if ( isset($wabe_values['uninstall']) && $wabe_values['uninstall'] ) echo ' checked="yes"';?> /><span><?php _e('Default: off <small>(UnChecked)</small>', 'wabelang'); ?></span></p>
    	</div>
    	<!-- // uninstall -->
    	<div id="info">
    		<div class="wabe-border">
            	<p><?php _e('You need some help active help tab.', 'wabelang'); ?></p>
    			<p><?php _e('Sorry for bugs in translations.', 'wabelang'); ?></p>
    			<p><?php _e('To help in the translation send an email to 23r9i0<span class="hidden">no-bot</span>@gmail.com', 'wabelang'); ?></p>
    		</div>
    	</div>
    	<!-- // info -->
    	<p class="wabe-padding">
			<input type="submit" name="wabe_update" id="update" class="button-primary" value="<?php _e('Save Changes', 'wabelang') ?>" />
        </p>
        </form>
        <form method="post" action="">
        	<p class="wabe-padding"><input type="submit" name="wabe_update" id="reset" onClick="return wabe_alert_reset();" class="button-secondary" value="<?php _e( 'Restore', 'wabelang' ); ?>" /></p>
			<input type="hidden" name="action" value="reset" />
		</form>
	</div>
    <!-- // tabs --> 
    <script type="text/javascript">
		var alert_reset = '<?php _e('Are you sure you want to restore WP Admin Bar Effect (wabe) to default settings?','wabelang'); ?>';
		function wabe_alert_reset(){if(confirm(alert_reset)==true){return true;}else{return false;}}
	</script>
    </div>
	<!-- // wrap -->
<?php
	}
} $__wabe = new wabe();
endif;
?>