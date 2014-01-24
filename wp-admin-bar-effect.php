<?php
/*
Plugin Name: WP Admin Bar Effect (wabe)
Plugin URI: http://wordpress.org/extend/plugins/wp-admin-bar-effect/
Description: Add effect slideDown to desktop top bar
Author: Sergio P.A. ( 23r9i0 )
Version: 2.5
Author URI: http://dsergio.com/
*/
/*  Copyright 2014  Sergio Prieto Alvarez  ( email : info@dsergio.com )

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

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'plugins_loaded', array( 'wabe', 'get_instance' ) );
register_activation_hook( __FILE__, array( 'wabe', 'activation' ) );
register_deactivation_hook( __FILE__, array( 'wabe', 'deactivation' ) );

class wabe {

	private static $instance = null;

	private $wabe_version = '2.5';

	private $wabe_options = array();

	private $hook = null;

	public static function get_instance() {
		if ( !  isset( self::$instance ) )
			self::$instance = new self;

		return self::$instance;
	}

	public static function activation() {
		$doptions = array(
			'wabe_active_link' => '2', 'wabe_target_link' => '1','wabe_icon_link' => '',
			'wabe_speed'=> '3000', 'wabe_sensitivity'=> '4','wabe_interval' => '50', 'wabe_timeout' => '200'
		);
		$options = get_option( 'wabe-options' );
		if ( $options ) {
			if ( isset( $options['icolink'] ) ) { // Version < 2.4
				foreach( $options as $option => $value ) {
					foreach( $doptions as $doption => $dvalue ) {
						if ( 'wabe_' == substr( $doption, 0, 5 ) ) {
							$doptions[$doption] = $options[$option];
						} else {
							if ( ! isset( $options['actlink'] ) )
								$doptions['wabe_active_link'] = '1';

							$doptions['wabe_icon_link'] = $options['icolink'];
						}
					}
				}
				update_option( 'wabe-options', $doptions );
			} else {
				$update = wp_parge_args( $doptions, $options );
				update_option( 'wabe-options', $update );
			}
		} else {
			add_option( 'wabe-options', $doptions );
		}
	}

	public static function deactivation() {
		delete_option( 'wabe-options' );
	}

	private	function __construct() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		if ( ! wp_is_mobile() )
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		add_filter( 'plugin_action_links', array( $this, 'plugin_action_links' ), 10, 2 );

		// Current Options
		$this->wabe_options = get_option( 'wabe-options' );

		// Languages
		load_plugin_textdomain( 'wabelang', false, dirname( plugin_basename( __FILE__ ) ) . '/include/languages/' );
	}

	public function register_settings() {
		register_setting(
			'wabe-register',
			'wabe-options',
			array( $this, 'validate_settings' )
		);
		add_settings_section(
			'wabe_general',
			'',
			'__return_false',
			'wabe_settings'
		);
		add_settings_field(
			'wabe_active_link',
			__( 'Link:', 'wabelang' ),
			array( $this, 'setting_active_link' ),
			'wabe_settings',
			'wabe_general',
			array( 'label_for' => 'wabe-active-link' )
		);
		add_settings_field(
			'wabe_target_link',
			__( 'Open in new tab or window:', 'wabelang' ),
			array( $this, 'setting_target_link' ),
			'wabe_settings',
			'wabe_general',
			array( 'label_for' => 'wabe-target-link' )
		);
		add_settings_field(
			'wabe_icon_link',
			__( 'Icon of the link:', 'wabelang' ),
			array( $this, 'setting_icon_link' ),
			'wabe_settings',
			'wabe_general',
			array( 'label_for' => 'wabe-icon' )
		);
		add_settings_field(
			'wabe_speed',
			__( 'Speed:', 'wabelang' ),
			array( $this, 'setting_speed' ),
			'wabe_settings',
			'wabe_general',
			array( 'label_for' => 'wabe-speed' )
		);
		add_settings_field(
			'wabe_header',
			__( '<strong>Advanced Settings</strong>','wabelang' ),
			'__return_false',
			'wabe_settings',
			'wabe_general'
		);
		add_settings_field(
			'wabe_sensitivity',
			__( 'Sensitivity:', 'wabelang' ),
			array( $this, 'setting_sensitivity' ),
			'wabe_settings',
			'wabe_general',
			array( 'label_for' => 'wabe-sensitivity' )
		);
		add_settings_field(
			'wabe_interval',
			__( 'Interval:', 'wabelang' ),
			array( $this, 'setting_interval' ),
			'wabe_settings',
			'wabe_general',
			array( 'label_for' => 'wabe-interval' )
		);
		add_settings_field(
			'wabe_timeout',
			__( 'Timeout:', 'wabelang' ),
			array( $this, 'setting_timeout' ),
			'wabe_settings',
			'wabe_general',
			array( 'label_for' => 'wabe-timeout' )
		);
	}

	public function admin_menu() {
		$this->hook = add_options_page(
			'WP Admin Bar Effect',
			'wabe',
			'manage_options',
			'wabe',
			array( $this, 'page_settings' )
		);
		add_action( 'load-' . $this->hook, array( $this, 'add_help' ) );

		global $menu;
		$name = get_bloginfo( 'name', 'display' );
		$urlw = home_url();
		$icon = ( $this->get_icon_url() ) ? $this->get_icon_url() : 'dashicons-wordpress';
		if ( '1' != $this->wabe_options['wabe_active_link'] )
    		$menu['-999999998'] = array(
				'0' => $name, '1' => 'read', '2' => $urlw,
				'3' => '', '4' => 'menu-top menu-top-first menu-icon-wabe',
				'5' => 'wabe', '6' => $icon
			);

		if ( '2' === $this->wabe_options['wabe_active_link'] )
			$menu['-999999999'] = array(
				'0' => '', '1' => 'read',
				'2' => 'separator-999999999', '3' => '',
				'4' => 'wp-menu-separator'
			);
	}

	public function admin_enqueue_scripts( $hook ) {
		if ( $this->hook == $hook )
			wp_enqueue_media();

		$dev = ( defined( 'DSWPDEV' ) && DSWPDEV ) ? '' : '.min';
		wp_register_script( 'wabe-script', plugins_url( 'include/javascript/jquery.wabe' . $dev . '.js', __FILE__ ), array( 'jquery', 'hoverIntent' ), $this->wabe_version );

		wp_enqueue_script( 'wabe-script' );

		wp_localize_script( 'wabe-script', 'wabe', array(
			'speed' => $this->wabe_options['wabe_speed'],
			'sensitivity' => $this->wabe_options['wabe_sensitivity'],
			'interval' => $this->wabe_options['wabe_interval'],
			'timeout' => $this->wabe_options['wabe_timeout'],
			'media_title' => __( 'Upload or Choose Your Icon File', 'wabelang' ),
			'media_button' => __( 'Insert Icon', 'wabelang' ),
			'target_link' => $this->wabe_options['wabe_target_link']
		) );

		wp_register_style( 'wabe-style', plugins_url( 'include/css/wabe' . $dev . '.css', __FILE__ ), false, $this->wabe_version );
		wp_enqueue_style( 'wabe-style' );

	}

	public function plugin_action_links( $links, $file ) {
    	if ( $file == plugin_basename( __FILE__ ) ) {
      		$link_settings = sprintf( '<a href="%s">%s</a>',
				admin_url( 'options-general.php?page=wabe' ),
				__( 'Options', 'wabelang' )
			);
	  		array_unshift( $links, $link_settings );
		}
    	return $links;
	}

	public function validate_settings( $input ) {
		$output = array();

		if ( isset( $_POST['reset-img'] ) ) {
			if ( ! empty( $input['wabe_icon_link'] ) ) {
				$upload_dir = wp_upload_dir();
				if ( false !== strpos( $input['wabe_icon_link'], $upload_dir['baseurl'] ) ) {
					global $wpdb;
					$guid = $this->wabe_options['wabe_icon_link'];
					$id = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE guid='$guid'" );
					if ( isset( $id ) ) {
						wp_delete_attachment( $id, true );
						add_settings_error( 'wabe-restore-img', 'restore_img_default', __( 'Default icon restored.', 'wabelang' ), 'updated' );
						$input['wabe_icon_link'] = '';
					} else {
						add_settings_error( 'wabe-delete-img', 'wabe_delete_img', __( 'Undefined id of the url image.', 'wabelang' ), 'error' );
					}
				} else {
					add_settings_error( 'wabe-restore-img', 'restore_img_default', __( 'Default icon restored.', 'wabelang' ), 'updated' );
					$input['wabe_icon_link'] = '';
				}
			}
		}

		$doptions = array(
			'wabe_active_link' => '2', 'wabe_target_link' => '1','wabe_icon_link' => '',
			'wabe_speed'=> '3000', 'wabe_sensitivity'=> '4','wabe_interval' => '50', 'wabe_timeout' => '200'
		);

		foreach ( $doptions as $option => $value ) {
			if ( isset( $input[$option] ) && ! empty( $input[$option] ) ) {
				if ( is_numeric( $input[$option] ) )
					$output[$option] = $input[$option];
				else
					$output[$option] = $value;
			} else {
				$output[$option] = $value;
			}

			if ( 'wabe_icon_link' === $option )
				if ( ! empty( $input[$option] ) )
					$output[$option] = $input[$option];
				else
					$output[$option] = $value;

			if ( 'wabe_target_link' === $option )
				if ( isset( $input[$option] ) )
					$output[$option] = $input[$option];
				else
					$output[$option] = 0;
		}

		return apply_filters( 'validate_settings', $output, $input );
	}

	public function page_settings() {
		echo '<div class="wrap wabe-settings">';
		echo '<h2>' . __( 'WP Admin Bar Effect - (wabe)' ) . '</h2>';
		echo '<form method="post" action="options.php">';
			settings_fields( 'wabe-register' );
			do_settings_sections( 'wabe_settings' );
			submit_button();
		echo '</form></div>';
	}

	public function add_help() {
    	$screen = get_current_screen();
    	if ( $screen->id != $this->hook )
			return;

    	$screen->add_help_tab( array( 'id' => 'wabe-help-one', 'title' => __( 'General options', 'wabelang' ), 'content' => '', 'callback' => array( $this, 'general_options' ) ) );
		$screen->add_help_tab( array( 'id' => 'wabe-help-two', 'title' => __( 'Advanced options', 'wabelang' ), 'content' => '', 'callback' => array( $this, 'hover_intent' ) ) );
	}

	public function general_options() {
		echo '<h5>' . __( 'Link', 'wabelang' ) . '</h5>';
		echo '<p>' . __( 'Decide if it shows a link in the admin sidebar to your site.', 'wabelang' ) . '</p>';
		echo '<p>' . __( 'Add Link with Separator decide if it shows a separator after link.', 'wabelang' ) . '</p>';
		echo '<h5>' . __( 'Open in new tab or window', 'wabelang' ) . '</h5>';
		echo '<p>' . __( 'Insert with javascript target tag for open in new tab or window.', 'wabelang' ) . '</p>';
		echo '<h5>' . __( 'Icon of the link', 'wabelang' ) . '</h5>';
		echo '<p>' . __( 'You can upload a image, enter a external url or use Dashicons.', 'wabelang' ) . '</p>';
		echo '<p>' . __( 'The image can not be greater than 16x16 pixels', 'wabelang' ) . '</p>';
		echo '<h5>' . __( 'Speed', 'wabelang' ) . '</h5>';
		echo '<p>' . __( 'Is the option to change the speed of the effect is activated once hovering.', 'wabelang' ) . '</p>';
    }

	public function hover_intent() {
		echo '<h5>' . __( 'Sensitivity', 'wabelang' ) . '</h5>';
		echo '<p>' . __( 'If the mouse travels fewer than this number of pixels between polling intervals, then the "over" function will be called. With the minimum sensitivity threshold of 1, the mouse must not move between polling intervals. With higher sensitivity thresholds you are more likely to receive a false positive.', 'wabelang' ) . '</p>';
		echo '<h5>' . __( 'Interval', 'wabelang' ) . '</h5>';
		echo '<p>' . __( 'The number of milliseconds hoverIntent waits between reading/comparing mouse coordinates. When the user\'s mouse first enters the element its coordinates are recorded. The soonest the "over" function can be called is after a single polling interval. Setting the polling interval higher will increase the delay before the first possible "over" call, but also increases the time to the next point of comparison.', 'wabelang' ) . '</p>';
		echo '<h5>' . __( 'Timeout', 'wabelang' ) . '</h5>';
		echo '<p>' . __( 'A simple delay, in milliseconds, before the "out" function is called. If the user mouses back over the element before the timeout has expired the "out" function will not be called (nor will the "over" function be called). This is primarily to protect against sloppy/human mousing trajectories that temporarily (and unintentionally) take the user off of the target element... giving them time to return.', 'wabelang' ) . '</p>';
    }

	private function get_icon_url( $echo = false ) {
		$image = $this->wabe_options['wabe_icon_link'];
		if ( '' === $image )
			return false;
		
		if ( ! $echo )
			return $image;
		
		if ( false !== strpos( $image, 'dashicons-' ) )
			return '<span class="dashicons ' . $image . '"></span>';
		else
			return '<span><img src="' . $image . '"/></span>';
	}

	public function setting_active_link() {
?>
<p>
  <label>
    <input id="wabe-disabled" class="wabe-radio" type="radio" value="1" name="wabe-options[wabe_active_link]" <?php checked( '1', $this->wabe_options['wabe_active_link'], true ); ?> />
    <span>
    <?php _e( 'Disabled', 'wabelang' ); ?>
    </span> </label>
</p>
<p>
  <label>
    <input class="wabe-radio" type="radio" value="3" name="wabe-options[wabe_active_link]" <?php checked( '3', $this->wabe_options['wabe_active_link'], true ); ?> />
    <span>
    <?php _e( 'Enabled', 'wabelang' ); ?>
    </span> </label>
</p>
<p>
  <label>
    <input class="wabe-radio" type="radio" value="2" name="wabe-options[wabe_active_link]" <?php checked( '2', $this->wabe_options['wabe_active_link'], true ); ?> />
    <span>
    <?php _e( 'Enabled with Separator', 'wabelang' ); ?>
    </span> </label>
</p>
<?php
	}

	public function setting_target_link() {
?>
<input type="checkbox" id="wabe-target-link" class="wabe-toggle" name="wabe-options[wabe_target_link]" value="1" <?php checked( '1', $this->wabe_options['wabe_target_link'], true ); ?> />
<?php
	}

	public function setting_icon_link() {
?>
<p>
  <input type="text" id="wabe-icon" class="wabe-toggle" size="60" name="wabe-options[wabe_icon_link]" value="<?php echo $this->wabe_options['wabe_icon_link']; ?>" />
</p>
<span class="hide-if-no-js">
<?php submit_button( __( 'Update icon', 'wabelang' ), 'secondary button-small', 'submit-img', false ); ?>
&nbsp;</span>
<?php submit_button( __( 'Restore icon', 'wabelang' ), 'secondary button-small', 'reset-img', false ); ?>
<p class="description">
  <?php _e( 'Default: ', 'wabelang' ); ?>
  <span class="dashicons dashicons-wordpress"></span> &nbsp;
  <?php _e( 'Current: ', 'wabelang' ); ?>
  <?php if ( false !== $this->get_icon_url() ) :
  			echo $this->get_icon_url(true);
  		else: ?>
  <span class="dashicons dashicons-wordpress"></span>
  <?php endif; ?>
  </p>
<?php
	}

	public function setting_speed() {
?>
<input type="text" id="wabe-speed" name="wabe-options[wabe_speed]" value="<?php echo $this->wabe_options['wabe_speed']; ?>" />
<span class="description">
<?php _e( 'Default: 3000', 'wabelang' ); ?>
</span>
<?php
	}

	public function setting_sensitivity() {
?>
<input type="text" id="wabe-sensitivity" name="wabe-options[wabe_sensitivity]" value="<?php echo $this->wabe_options['wabe_sensitivity']; ?>" />
<span class="description">
<?php _e( 'Default: 4', 'wabelang' ); ?>
</span>
<?php
	}

	public function setting_interval() {
?>
<input type="text" id="wabe-interval" name="wabe-options[wabe_interval]" value="<?php echo $this->wabe_options['wabe_interval']; ?>" />
<span class="description">
<?php _e( 'Default: 50', 'wabelang' ); ?>
</span>
<?php
	}

	public function setting_timeout() {
?>
<input type="text" id="wabe-timeout" name="wabe-options[wabe_timeout]" value="<?php echo $this->wabe_options['wabe_timeout']; ?>" />
<span class="description">
<?php _e( 'Default: 200', 'wabelang' ); ?>
</span>
<?php
	}
}
?>
