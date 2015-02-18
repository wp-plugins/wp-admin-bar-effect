<?php
/**
 * Plugin Name: WP Admin Bar Effect (wabe)
 * Plugin URI: http://wordpress.org/extend/plugins/wp-admin-bar-effect/
 * Description: Add effect slideDown to desktop top bar
 * Version: 2.5.2
 * Author: Sergio P.A. ( 23r9i0 )
 * Author URI: http://dsergio.com/
 * License: GPL2
 *
 *
 * Copyright 2014-2015  Sergio Prieto Alvarez  ( email : info@dsergio.com )
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'plugins_loaded', array( 'WP_Admin_Bar_Effect', 'get_instance' ), 10 );
register_activation_hook( __FILE__, array( 'WP_Admin_Bar_Effect', 'activation' ) );
register_deactivation_hook( __FILE__, array( 'WP_Admin_Bar_Effect', 'deactivation' ) );

class WP_Admin_Bar_Effect {

	private static $instance = null;

	private $version = '2.5.2';

	private $options = array();

	private $hook = null;

	public static function get_instance() {
		if ( !  isset( self::$instance ) )
			self::$instance = new self;

		return self::$instance;
	}

	public static function activation() {
		$doptions = array(
			'active_link' => '2', 'target_link' => '1','icon_link' => '',
			'speed'=> '3000', 'sensitivity'=> '4','interval' => '50', 'timeout' => '200'
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
								$doptions['active_link'] = '1';

							$doptions['icon_link'] = $options['icolink'];
						}
					}
				}
				update_option( 'wabe-options', $doptions );
			} else {
				$update = wp_parse_args( $doptions, $options );
				update_option( 'wabe-options', $update );
			}
		} else {
			add_option( 'wabe-options', $doptions );
		}
	}

	public static function deactivation() {
		delete_option( 'wabe-options' );
	}

	private function __construct() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		if ( ! wp_is_mobile() )
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );

		// Current Options
		$this->options = get_option( 'wabe-options' );

		// Languages
		load_plugin_textdomain( 'wabelang', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	public function register_settings() {
		register_setting( 'wabe-register', 'wabe-options', array( $this, 'validate_settings' ) );

		add_settings_section(
			'wabe_general', '', '__return_false', 'wabe_settings'
		);
		add_settings_field(
			'wabe_active_link', __( 'Link:', 'wabelang' ), array( $this, 'setting_active_link' ),
			'wabe_settings', 'wabe_general', array( 'label_for' => 'wabe-active-link' )
		);
		add_settings_field(
			'wabe_target_link', __( 'Open in new tab or window:', 'wabelang' ), array( $this, 'setting_target_link' ),
			'wabe_settings', 'wabe_general', array( 'label_for' => 'wabe-target-link' )
		);
		add_settings_field(
			'wabe_icon_link', __( 'Icon of the link:', 'wabelang' ), array( $this, 'setting_icon_link' ),
			'wabe_settings', 'wabe_general', array( 'label_for' => 'wabe-icon' )
		);
		add_settings_field(
			'wabe_speed', __( 'Speed:', 'wabelang' ), array( $this, 'setting_speed' ),
			'wabe_settings', 'wabe_general', array( 'label_for' => 'wabe-speed' )
		);

		add_settings_section(
			'wabe_advanced', __( 'Advanced Settings','wabelang' ), '__return_false', 'wabe_settings'
		);
		add_settings_field(
			'wabe_sensitivity', __( 'Sensitivity:', 'wabelang' ), array( $this, 'setting_sensitivity' ),
			'wabe_settings', 'wabe_advanced', array( 'label_for' => 'wabe-sensitivity' )
		);
		add_settings_field(
			'wabe_interval', __( 'Interval:', 'wabelang' ), array( $this, 'setting_interval' ),
			'wabe_settings', 'wabe_advanced', array( 'label_for' => 'wabe-interval' )
		);
		add_settings_field(
			'wabe_timeout', __( 'Timeout:', 'wabelang' ), array( $this, 'setting_timeout' ),
			'wabe_settings', 'wabe_advanced', array( 'label_for' => 'wabe-timeout' )
		);
	}

	public function admin_menu() {
		$this->hook = add_options_page(
			'WP Admin Bar Effect (wabe)', 'wabe', 'manage_options', 'wp-admin-bar-effect', array( $this, 'page_settings' )
		);
		add_action( 'load-' . $this->hook, array( $this, 'add_help' ) );

		global $menu;
		$name = get_bloginfo( 'name', 'display' );
		$urlw = home_url();
		$icon = ( $this->get_icon_url() ) ? $this->get_icon_url() : 'dashicons-wordpress';
		if ( '1' != $this->options['active_link'] ) {
			$menu['-999999998'] = array(
				'0' => $name, '1' => 'read', '2' => $urlw,
				'3' => '', '4' => 'menu-top menu-top-first menu-icon-wabe',
				'5' => 'wabe', '6' => $icon
			);
		}

		if ( '2' === $this->options['active_link'] ) {
			$menu['-999999999'] = array(
				'0' => '', '1' => 'read',
				'2' => 'separator-999999999', '3' => '',
				'4' => 'wp-menu-separator'
			);
		}
	}

	public function admin_enqueue_scripts( $hook ) {
		if ( $this->hook == $hook )
			wp_enqueue_media();

		wp_register_script( 'wabe-script', plugins_url( 'src/admin/js/wabe.js', __FILE__ ), array( 'jquery', 'hoverIntent' ), $this->version, true );
		wp_localize_script( 'wabe-script', 'wabe', array(
			'speed' => $this->options['speed'],
			'sensitivity' => $this->options['sensitivity'],
			'interval' => $this->options['interval'],
			'timeout' => $this->options['timeout'],
			'media_title' => __( 'Upload or Choose Your Icon File', 'wabelang' ),
			'media_button' => __( 'Insert Icon', 'wabelang' ),
			'target_link' => $this->options['target_link']
		) );
		wp_enqueue_script( 'wabe-script' );

		wp_register_style( 'wabe-style', plugins_url( 'src/admin/css/wabe.css', __FILE__ ), array(), $this->version );
		wp_enqueue_style( 'wabe-style' );

	}

	public function plugin_action_links( $links ) {
		$settings = sprintf(
			'<a href="%s">%s</a>',
			admin_url( 'options-general.php?page=wp-admin-bar-effect' ), __( 'Options', 'wabelang' )
		);

		array_unshift( $links, $link_settings );

		return $links;
	}

	public function validate_settings( $input ) {
		$output = array();

		if ( isset( $_POST['reset-img'] ) ) {
			if ( ! empty( $input['icon_link'] ) ) {
				$upload_dir = wp_upload_dir();
				if ( false !== strpos( $input['icon_link'], $upload_dir['baseurl'] ) ) {
					global $wpdb;
					$guid = $this->options['icon_link'];
					$id = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE guid='$guid'" );
					if ( isset( $id ) ) {
						wp_delete_attachment( $id, true );
						add_settings_error( 'wabe-restore-img', 'restore_img_default', __( 'Default icon restored.', 'wabelang' ), 'updated' );
						$input['icon_link'] = '';
					} else {
						add_settings_error( 'wabe-delete-img', 'delete_img', __( 'Undefined id of the url image.', 'wabelang' ), 'error' );
					}
				} else {
					add_settings_error( 'wabe-restore-img', 'restore_img_default', __( 'Default icon restored.', 'wabelang' ), 'updated' );
					$input['icon_link'] = '';
				}
			}
		}

		$doptions = array(
			'active_link' => '2', 'target_link' => '1','icon_link' => '',
			'speed'=> '3000', 'sensitivity'=> '4','interval' => '50', 'timeout' => '200'
		);

		foreach ( $doptions as $option => $value ) {
			if ( isset( $input[$option] ) && ! empty( $input[$option] ) ) {
				if ( is_numeric( $input[$option] ) ) {
					$output[$option] = $input[$option];
				} else {
					$output[$option] = $value;
				}
			} else {
				$output[$option] = $value;
			}

			if ( 'icon_link' === $option ) {
				if ( ! empty( $input[$option] ) ) {
					$output[$option] = $input[$option];
				} else {
					$output[$option] = $value;
				}
			}

			if ( 'target_link' === $option ) {
				if ( isset( $input[$option] ) ) {
					$output[$option] = $input[$option];
				} else {
					$output[$option] = 0;
				}
			}
		}

		return apply_filters( 'validate_settings', $output, $input );
	}

	public function page_settings() {
		?>
		<div class="wrap wabe-settings">
			<h2>WP Admin Bar Effect - (wabe)</h2>
			<form method="post" action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>">
			<?php settings_fields( 'wabe-register' ); ?>
			<?php do_settings_sections( 'settings' ); ?>
			<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	public function add_help() {
		$screen = get_current_screen();
		if ( $screen->id === $this->hook ) {
			$screen->add_help_tab( array(
				'id' => 'wabe-help-one', 'title' => __( 'General options', 'wabelang' ),
				'content' => '', 'callback' => array( $this, 'general_options' )
			) );
			$screen->add_help_tab( array(
				'id' => 'wabe-help-two', 'title' => __( 'Advanced options', 'wabelang' ),
				'content' => '', 'callback' => array( $this, 'hover_intent' )
			) );
		}
	}

	public function general_options() {
		?>
		<h5><?php _e( 'Link', 'wabelang' ); ?></h5>
		<p><?php _e( 'Decide if it shows a link in the admin sidebar to your site.', 'wabelang' ); ?></p>
		<p><?php _e( 'Add Link with Separator decide if it shows a separator after link.', 'wabelang' ); ?></p>
		<h5><?php _e( 'Open in new tab or window', 'wabelang' ); ?></h5>
		<p><?php _e( 'Insert with javascript target tag for open in new tab or window.', 'wabelang' ); ?></p>
		<h5><?php _e( 'Icon of the link', 'wabelang' ); ?></h5>
		<p><?php _e( 'You can upload a image, enter a external url or use Dashicons.', 'wabelang' ); ?></p>
		<p><?php _e( 'The image can not be greater than 16x16 pixels', 'wabelang' ); ?></p>
		<h5><?php _e( 'Speed', 'wabelang' ); ?></h5>
		<p><?php _e( 'Is the option to change the speed of the effect is activated once hovering.', 'wabelang' ); ?></p>
		<?php
	}

	public function hover_intent() {
		?>
		<h5><?php _e( 'Sensitivity', 'wabelang' ); ?></h5>
		<p><?php _e( 'If the mouse travels fewer than this number of pixels between polling intervals, then the "over" function will be called. With the minimum sensitivity threshold of 1, the mouse must not move between polling intervals. With higher sensitivity thresholds you are more likely to receive a false positive.', 'wabelang' ); ?></p>
		<h5><?php _e( 'Interval', 'wabelang' ); ?></h5>
		<p><?php _e( 'The number of milliseconds hoverIntent waits between reading/comparing mouse coordinates. When the user\'s mouse first enters the element its coordinates are recorded. The soonest the "over" function can be called is after a single polling interval. Setting the polling interval higher will increase the delay before the first possible "over" call, but also increases the time to the next point of comparison.', 'wabelang' ); ?></p>
		<h5><?php _e( 'Timeout', 'wabelang' ); ?></h5>
		<p><?php _e( 'A simple delay, in milliseconds, before the "out" function is called. If the user mouses back over the element before the timeout has expired the "out" function will not be called (nor will the "over" function be called). This is primarily to protect against sloppy/human mousing trajectories that temporarily (and unintentionally) take the user off of the target element... giving them time to return.', 'wabelang' ); ?></p>
		<?php
	}

	private function get_icon_url( $echo = false ) {
		$image = $this->options['icon_link'];

		if ( empty( $image ) )
			return false;

		if ( $echo ) {
			if ( false !== $p = strpos( $image, 'dashicons-' ) ) {
				return sprintf( '<span class="dashicons %s"></span>', $image );
			} else {
				return sprintf( '<span <img src="%s"/></span>', $image );
			}
		} else {
			return $image;
		}
	}

	public function setting_active_link() {
		$name = 'wabe-options[active_link]';
		?>
		<p><label>
			<input id="wabe-disabled" class="wabe-radio" type="radio" value="1" name="<?php echo esc_attr( $name ); ?>"<?php checked( '1', $this->$name ); ?>>
			<span><?php _e( 'Disabled', 'wabelang' ); ?></span>
		</label></p>
		<p><label>
			<input class="wabe-radio" type="radio" value="3" name="<?php echo esc_attr( $name ); ?>"<?php checked( '3', $this->$name ); ?>>
			<span><?php _e( 'Enabled', 'wabelang' ); ?></span>
		</label></p>
		<p><label>
			<input class="wabe-radio" type="radio" value="2" name="<?php echo esc_attr( $name ); ?>"<?php checked( '2', $this->$name ); ?>>
			<span><?php _e( 'Enabled with Separator', 'wabelang' ); ?></span>
		</label></p>
		<?php
	}

	public function setting_target_link() {
		$name = 'wabe-options[target_link]';
		$checked = checked( '1', $this->$name, false );
		?>
		<input type="checkbox" id="wabe-target-link" class="wabe-toggle" name="<?php echo esc_attr( $name ); ?>" value="1"<?php echo esc_attr( $checked ); ?>>
		<?php
	}

	public function setting_icon_link() {
		?>
		<p>
		<input type="text" id="wabe-icon" class="wabe-toggle" size="60" name="wabe-options[icon_link]" value="<?php echo $this->options['icon_link']; ?>">
		</p>
		<p>
		<span class="hide-if-no-js"><?php submit_button( __( 'Update icon', 'wabelang' ), 'secondary button-small', 'submit-img', false ); ?>&nbsp;</span>
		<span><?php submit_button( __( 'Restore icon', 'wabelang' ), 'secondary button-small', 'reset-img', false ); ?></span>
		</p>
		<p class="description">
			<?php
			$default = '<span class="dashicons dashicons-wordpress"></span>';
			printf( '%1$s%2$s %3$s%4$s',
				__( 'Default: ', 'wabelang' ), $default, __( 'Current: ', 'wabelang' ),
				( false !== $this->get_icon_url() ) ? esc_html( $this->get_icon_url( true ) ) : $default
			);
			?>
		</p>
		<?php
	}

	public function setting_speed() {
		?>
		<input type="text" id="wabe-speed" name="wabe-options[speed]" value="<?php echo $this->options['speed']; ?>" />
		<span class="description"><?php _e( 'Default: 3000', 'wabelang' ); ?></span>
		<?php
	}

	public function setting_sensitivity() {
		?>
		<input type="text" id="wabe-sensitivity" name="wabe-options[sensitivity]" value="<?php echo $this->options['sensitivity']; ?>" />
		<span class="description"><?php _e( 'Default: 4', 'wabelang' ); ?></span>
		<?php
	}

	public function setting_interval() {
		?>
		<input type="text" id="wabe-interval" name="wabe-options[interval]" value="<?php echo $this->options['interval']; ?>" />
		<span class="description"><?php _e( 'Default: 50', 'wabelang' ); ?></span>
		<?php
	}

	public function setting_timeout() {
		?>
		<input type="text" id="wabe-timeout" name="wabe-options[timeout]" value="<?php echo $this->options['timeout']; ?>" />
		<span class="description"><?php _e( 'Default: 200', 'wabelang' ); ?></span>
		<?php
	}
}
