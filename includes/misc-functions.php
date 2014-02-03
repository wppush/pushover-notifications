<?php
/**
 * Get the core settings - This array can be supplemented by extensions using the 'ckpn_options_defaults' filter;
 * 
 * @return array The options used for Pushover Notifications
 */
function ckpn_get_options() {
	static $current_options = NULL;

	if ( $current_options !== NULL )
		return $current_options;

	$defaults = array(
			'application_key' 		 => false,
			'api_key'				 => false,
			'new_user'				 => false,
			'new_post'				 => false,
			'post_notification_role' => 'administrator',
			'new_comment'			 => false,
			'notify_authors'	 	 => false,
			'password_reset'		 => false,
			'plugin_updates'		 => false,
			'core_update'			 => false,
			'sslverify'				 => false,
			'multiple_keys'			 => false,
			'logging'				 => false
		);

	$defaults = apply_filters( 'ckpn_options_defaults', $defaults );
	
	$options = wp_parse_args( get_option( 'ckpn_pushover_notifications_settings' ), $defaults );

	return $options;
}

/**
 * Get a core setting - This array can be supplemented by extensions using the 'ckpn_options_defaults' filter;
 * 
 * @return mixed The value from the main settings array
 */
function ckpn_get_option( $setting = NULL ) {
	$options = ckpn_get_options();

	if ( is_null( $setting ) || !isset( $options[$setting] ) )
		return false;

	return $options[$setting];
}

/**
 * Items to run during activation process and admin_init to verify we're properly configured
 * @return void
 */
function ckpn_activation_hook() {
	if ( false === get_option( '_ckpn_users_with_keys') ) {
		global $wpdb;
		$user_keys_array = array();
		$users = $wpdb->get_results( "SELECT user_id, meta_value FROM $wpdb->usermeta WHERE meta_key = 'ckpn_user_key' AND meta_value != ''", ARRAY_A );
		foreach ( $users as $user ) {
			extract( $user );
			$meta_value = trim( $meta_value );
			if ( !empty( $meta_value ) )
				$user_keys_array[$user_id] = $meta_value;
		}

		add_option( '_ckpn_users_with_keys', $user_keys_array, '', 'no' );
	}
}

/**
 * Get the extension licenses that have been added on the filter 'ckpn_licenses_array'
 * @return array The extensions with license options
 */
function ckpn_get_licenses() {
	$licenses = array();
	$licenses = apply_filters( 'ckpn_licenses_array', $licenses );

	return $licenses;
}

/**
 * Get all the application keys
 * @return array Contains an array of all the application keys
 *
 * The array format is
 * [id (default_key for primary key, numeric increment for additional keys)]
 * 		['name']	=> <App Key Name>
 * 		['app_key']	=> <application key from Pushover.net>
 */
function ckpn_get_application_keys() {
	$basic_options = ckpn_get_options();
	$additional_keys = get_option( '_ckpn_additional_app_keys' );

	$all_keys = array( 'default_key' => array( 'name' => 'Default Key', 'app_key' => $basic_options['application_key'] ) );

	if ( !empty( $additional_keys ) )
		$all_keys = $all_keys + $additional_keys;

	return $all_keys;
}

/**
 * Given the additional key id, return the application key
 * @param  string $id The application key id from _ckpn_additional_app_keys
 * @return string     The application key to send the notification with
 */
function ckpn_get_application_key_by_id( $id = 'default_key' ) {
	$application_key = false;

	$all_keys = ckpn_get_application_keys();

	if ( !isset( $all_keys[$id] ) )
		return false;

	return $all_keys[$id]['app_key'];
}

/**
 * Given the setting name, return the application key
 * @param  string $setting The setting name associated with the notification being sent
 * @return string          The application key to send the notification with
 */
function ckpn_get_application_key_by_setting( $setting = NULL ) {
	if ( empty( $setting ) )
		return false;

	$current_mappings = get_option( '_ckpn_additional_key_mapping' );
	$mapped_key = $current_mappings[$setting];

	$all_keys = ckpn_get_application_keys();

	if ( !isset( $all_keys[$mapped_key] ) )
		return false;

	return $all_keys[$mapped_key]['app_key'];
}

/**
 * Format the log entry uniformly
 * @param  string $message The message to log
 * @return string          Formatted correctly with the Date and new line wrapping the message
 */
function ckpn_log_entry_format( $message = '' ) {
	if ( $message == '' )
		return $message;

	return date( 'm-d-Y H:i:s' ) . ' -- '. $message . "\n";
}