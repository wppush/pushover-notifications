<?php
/*
Plugin Name: Pushover Notifications for WordPress
Plugin URI: http://www.wp-push.com
Description: Pushover Notifications allows your WordPress blog to send push notifications for events happening on your blog straight to your iOS device with the Pushover app. This plugin is not associated with the Pushover Notifications team or Superblock.
Version: 1.9.4
Author: Chris Klosowski
Author URI: http://www.wp-push.com
License: GPLv2
*/

define( 'CKPN_CORE_TEXT_DOMAIN', 'ckpn' );
define( 'CKPN_PATH', plugin_dir_path( __FILE__ ) );
define( 'CKPN_VERSION', '1.9.4' );
define( 'CKPN_FILE', plugin_basename( __FILE__ ) );
define( 'CKPN_URL', plugins_url( 'pushover-notifications', 'pushover-notifications.php' ) );

class CKPushoverNotifications {
	private static $ckpn_instance;

	private function __construct() {
		require_once( CKPN_PATH . '/includes/misc-functions.php' );
		require_once( CKPN_PATH . '/includes/notification-functions.php' );
		require_once( CKPN_PATH . '/includes/user-functions.php' );
		$options = ckpn_get_options();

		// Call the function to setup settings for activation and 'upgrade'
		register_activation_hook( __FILE__, 'ckpn_activation_hook' );
		add_action( 'admin_init', 'ckpn_activation_hook' );

		add_action( 'init', array( $this, 'ckpn_loaddomain', ) );
		add_action( 'cron_schedules', array( $this, 'add_cron_schedule' ) );
		add_action( 'init', array( $this, 'determine_cron_schedule' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_cusom_js' ) );

		if ( $options['new_user'] ) {
			add_action( 'user_register', 'ckpn_user_registration' );
		}

		if ( $options['new_comment'] ) {
			add_action( 'comment_post', 'ckpn_new_comment' );
		}

		if ( $options['password_reset'] ) {
			add_action( 'lostpassword_post', 'ckpn_lost_password_request' );
		}

		if ( $options['new_post'] ) {
			add_action( 'transition_post_status', 'ckpn_post_published', 10, 3 );
		}

		if ( is_admin() ) {
			require_once( CKPN_PATH . '/includes/admin/admin-pages.php' );
			require_once( CKPN_PATH . '/includes/admin/admin-functions.php' );
			add_action( 'admin_notices', array( $this, 'ckpn_edd_missing_nag' ) );

			/** Settings Pages **/
			add_action( 'admin_init', array( $this, 'ckpn_register_settings' ), 1000, 0 );
			add_action( 'admin_menu', array( $this, 'ckpn_setup_admin_menu' ), 1000, 0 );
			add_filter( 'plugin_action_links', array( $this, 'plugin_settings_links' ), 10, 2 );

			/** User Profile Settings **/
			add_filter( 'user_contactmethods', 'ckpn_add_contact_item', 10, 1 );

			add_action( 'personal_options_update', 'ckpn_save_profile_settings', 10, 1 );
			add_action( 'edit_user_profile_update', 'ckpn_save_profile_settings', 10, 1 );

			if ( $options['new_post'] ) {
				add_action( 'show_user_profile', 'ckpn_add_profile_settings', 10, 1 );
				add_action( 'edit_user_profile', 'ckpn_add_profile_settings', 10, 1 );

			}

			if ( $options['multiple_keys'] ) {
				add_action( 'ckpn_register_additional_settings', 'ckpn_register_additional_key_mappings' );
				add_filter( 'ckpn_settings_page_options', 'ckpn_enable_additional_key_mappings');
			}
		}

		if ( is_multisite() ) {
			// These will work for when users are added from within the site using Pushover.
			// If the user is added at the site level and Pushover is not Network Activated, it will not add the keys
			// until the user saves their profile data
			add_action( 'add_user_to_blog', 'ckpn_multisite_add_user', 10, 3 );
			add_action( 'remove_user_from_blog', 'ckpn_multisite_remove_user', 10, 3 );
		}
	}

	/**
	 * Get the singleton instance of our plugin
	 * @return class The Instance
	 * @access public
	 */
	public static function getInstance() {
		if ( !self::$ckpn_instance ) {
			self::$ckpn_instance = new CKPushoverNotifications();
		}

		return self::$ckpn_instance;
	}

	/**
	 * Add a 12 hour cron schedule
	 * @param array $schedules The current list of cron schedules
	 * @access public
	 */
	public function add_cron_schedule( $schedules ) {
		$schedules['twicedaily'] = array(
								'interval'  => 43200,
								'display'	=> __( 'Twice Daily', CKPN_CORE_TEXT_DOMAIN ) );

		return $schedules;
	}

	/**
	 * Determine when to schedule the cron
	 * @return void
	 * @access public
	 */
	public function determine_cron_schedule() {
		$current_options = ckpn_get_options();
		if ( $current_options['plugin_updates'] || $current_options['core_update'] ) {
			if ( !wp_next_scheduled( 'ckpn_plugin_update_check' ) ) {
				$next_run = time();
				wp_schedule_event( $next_run, 'twicedaily', 'ckpn_plugin_update_check' );
			}
			add_action( 'ckpn_plugin_update_check', 'ckpn_plugin_update_checks' );
		}
	}

	/**
	 * Queue up the JavaScript file for the admin page, only on our admin page
	 * @param  string $hook The current page in the admin
	 * @return void
	 * @access public
	 */
	public function load_cusom_js( $hook ) {
		if ( 'settings_page_pushover-notifications' != $hook )
			return;

		wp_enqueue_script( 'ckpn_core_custom_js', CKPN_URL.'/includes/scripts/ckpn_custom.js', 'jquery', CKPN_VERSION, true );
	}

	/**
	 * Send notifications
	 * @param  array $passed_args The arguments used by the Pushover API
	 * @return void
	 * @access public
	 * @deprecated Deprecated since 1.8 - Use the non-class function ckpn_send_notification with the same arguments
	 */
	public function ckpn_send_notification( $passed_args ) {
		// Back compat
		ckpn_send_notification( $passed_args );
	}

	/**
	 * Adds the Settings and Pushover Link to the Settings page list
	 * @param  array $links The current list of links
	 * @param  string $file The plugin file
	 * @return array        The new list of links, with our additional ones added
	 * @access public
	 */
	public function plugin_settings_links( $links, $file ) {
		if ( $file != CKPN_FILE )
			return $links;

		$settings_link = sprintf( '<a href="%s">%s</a>', admin_url( 'options-general.php?page=pushover-notifications' ), __( 'Settings', CKPN_CORE_TEXT_DOMAIN ) );
		$pushover_link = sprintf( '<a href="http://www.pushover.net" target="_blank">%s</a>', __( 'Visit Pushover', CKPN_CORE_TEXT_DOMAIN ) );

		array_unshift( $links, $settings_link );
		$links[] = $pushover_link;

		return $links;
	}

	/**
	 * Add the Pushover Notifications item to the Settings menu
	 * @return void
	 * @access public
	 */
	public function ckpn_setup_admin_menu() {
		add_options_page( __( 'Pushover Notifications', CKPN_CORE_TEXT_DOMAIN ), __( 'Pushover Notifications', CKPN_CORE_TEXT_DOMAIN ), 'administrator', 'pushover-notifications', array( $this, 'determine_tab' ) );
	}

	/**
	 * Determines what tab is being displayed, and executes the display of that tab
	 * @return void
	 * @access public
	 */
	public function determine_tab() {
		$settings = ckpn_get_options();
		?>
		<div id="icon-options-general" class="icon32"></div><h2><?php _e( 'Pushover Notifications for WordPress', CKPN_CORE_TEXT_DOMAIN ); ?></h2>
		<?php
		$current = ( !isset( $_GET['tab'] ) ) ? 'general' : $_GET['tab'];
		$default_tabs = array(
				'general' => __( 'Settings', CKPN_CORE_TEXT_DOMAIN ),
				'logs' => __( 'Logs', CKPN_CORE_TEXT_DOMAIN ),
				'sysinfo' => __( 'System Info', CKPN_CORE_TEXT_DOMAIN )
			);

		// If multiple keys is enabled, show the tab
		if ( $settings['multiple_keys'] ) {
			$default_tabs = array_slice( $default_tabs, 0, 1, true ) +
							array( 'additional_keys' => __( 'Additional Keys', CKPN_CORE_TEXT_DOMAIN ) ) +
							array_slice( $default_tabs, 1, count( $default_tabs ) -1, true ) ;

		}

		// If any extensions have hooked into the licenses settings, show the tab
		$licenses = ckpn_get_licenses();
		if ( !empty( $licenses ) ) {
			$default_tabs['licenses'] = __( 'Licenses', CKPN_CORE_TEXT_DOMAIN );
		}

		// Add the Exetensions listing Last
		$default_tabs['extensions'] = __( 'Get Extensions', CKPN_CORE_TEXT_DOMAIN );

		$tabs = apply_filters( 'ckpn_settings_tabs', $default_tabs );

		?><h2 class="nav-tab-wrapper"><?php
		foreach( $tabs as $tab => $name ){
			$class = ( $tab == $current ) ? ' nav-tab-active' : '';
			echo "<a class='nav-tab$class' href='?page=pushover-notifications&tab=$tab'>$name</a>";
		}
		?>
		</h2>
		<div class="wrap">
		<?php
		if ( !isset( $_GET['tab'] ) || $_GET['tab'] == 'general' ) {
			ckpn_admin_page();
		} else {
			// Extension Devs - Your function that shows the tab content needs to be prefaced with 'ckpn_display_' in order to work here.
			$tab_function = 'ckpn_display_'.$_GET['tab'];
			$tab_function();
		}
		?>
		</div>
		<?php
	}

	/**
	 * Register/Whitelist our settings on the settings page, allow extensions and other plugins to hook into this
	 * @return void
	 * @access public
	 */
	public function ckpn_register_settings() {
		register_setting( 'ckpn-update-options', 'ckpn_pushover_notifications_settings' );
		do_action( 'ckpn_register_additional_settings' );
	}

	/**
	 * Display a warning if the user doesn't have the required plugins or versions active. Also notify them of available extensions
	 * @return void
	 * @access public
	 */
	public function ckpn_edd_missing_nag() {
		if ( !isset($_REQUEST['page'] ) || $_REQUEST['page'] != 'pushover-notifications' )
			return;

		if ( is_plugin_active( 'easy-digital-downloads/easy-digital-downloads.php' ) &&
			! is_plugin_active( 'pushover-notifications-edd-ext/pushover-notifications-edd-ext.php' ) ) {
			printf( '<div class="error"> <p> %s </p> </div>', __( 'Get Pushover Notifications for your Easy Digital Downloads Sales with the <a href="https://easydigitaldownloads.com/extension/pushover-notifications/?ref=371" target="_blank">Pushover Notifications Extension</a>.', CKPN_CORE_TEXT_DOMAIN ) );
		}
		if ( is_plugin_active( 'bbpress/bbpress.php' ) &&
			! is_plugin_active( 'pushover-notifications-bbp-ext/pushover-notifications-bbp-ext.php' ) ) {
			printf( '<div class="error"> <p> %s </p> </div>', __( 'Get Pushover Notifications for bbPress with the <a href="https://wp-push.com/extensions/bbpress-extension/" target="_blank">Pushover Notifications Extension</a>.', CKPN_CORE_TEXT_DOMAIN ) );
		}
	}

	/**
	 * Load the Text Domain for i18n
	 * @return void
	 * @access public
	 */
	public function ckpn_loaddomain() {
		load_plugin_textdomain(CKPN_CORE_TEXT_DOMAIN, false, '/pushover-notifications/languages/' );
	}

	/**
	 * Original get_options unifier
	 * @return array List of options
	 * @deprecated as of 1.5
	 * @access public
	 */
	public function get_options() {
		return ckpn_get_options();
	}

}

$ckpn_loaded = CKPushoverNotifications::getInstance();
