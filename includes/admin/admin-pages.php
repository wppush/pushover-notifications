<?php
/**
 * Display the General settings tab
 * @return void
 */
function ckpn_admin_page() {
	$current = ckpn_get_options();
	$roles = get_editable_roles();
	if ( isset( $_GET['settings-updated'] ) && $current['plugin_updates'] == false && $timestamp = wp_next_scheduled( 'ckpn_plugin_update_check' ) ) {
		wp_unschedule_event( $timestamp, 'ckpn_plugin_update_check' );
	}
	if ( empty( $current['new_post_roles'] ) )
		$current['new_post_roles'] = array();

	foreach ( $roles as $role_id => $role ) {
		$current['new_post_roles'][$role_id] = isset( $current['new_post_roles'][$role_id] ) ? $current['new_post_roles'][$role_id] : 0;
	}
	?>
	<form method="post" action="options.php">
		<?php wp_nonce_field( 'ckpn-update-options' ); ?>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e( 'Application API Token/Key', CKPN_CORE_TEXT_DOMAIN ); ?><br /><span style="font-size: x-small;"><?php _e( 'Available from within your Pushover Account', CKPN_CORE_TEXT_DOMAIN ); ?></span></th>
				<td>
				<input size="50" type="text" name="ckpn_pushover_notifications_settings[application_key]" placeholder="<?php _e( 'Enter Application Key', CKPN_CORE_TEXT_DOMAIN ); ?>" <?php if ( $current['application_key'] != '' ) {?>value="<?php echo htmlspecialchars( $current['application_key'] ); ?>"<?php ;}?> />
				<br />
				<?php _e( 'To Create an application visit <a href="https://pushover.net/apps/clone/wordpress_plugin" target="_blank">https://pushover.net/apps/clone/wordpress_plugin</a>', CKPN_CORE_TEXT_DOMAIN ); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e( 'Your User Key', CKPN_CORE_TEXT_DOMAIN ); ?><br /><span style="font-size: x-small;"><?php _e( 'Available from within your Pushover Account', CKPN_CORE_TEXT_DOMAIN ); ?></span></th>
				<td>
				<input size="50" type="text" name="ckpn_pushover_notifications_settings[api_key]" placeholder="<?php _e( 'Enter User Key', CKPN_CORE_TEXT_DOMAIN ); ?>" <?php if ( $current['api_key'] != '' ) {?>value="<?php echo htmlspecialchars( $current['api_key'] ); ?>"<?php ;}?> /><br />
				<?php printf( __( 'This is the Administrator User Key. Other users can add their keys by editing their <a href="%s">Profile</a> under the \'Contact Info\' Section.', CKPN_CORE_TEXT_DOMAIN ), admin_url( 'profile.php' ) ); ?>
				<br />
				<?php _e( 'Adding user specific keys allows you to be notified of Password Reset requests and other user specific notifications.', CKPN_CORE_TEXT_DOMAIN ); ?>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row"><?php _e( 'Send Notifications For:', CKPN_CORE_TEXT_DOMAIN ); ?><br /><span style="font-size: x-small;"><?php _e( 'What do you want to be notified of?', CKPN_CORE_TEXT_DOMAIN ); ?></span></th>
				<td>
					<input type="checkbox" name="ckpn_pushover_notifications_settings[new_user]" value="1" <?php checked( $current['new_user'], '1', true ); ?> /> 
					<label for"ckpn_pushover_notifications_settings[new_user]"><?php _e( 'New Users', CKPN_CORE_TEXT_DOMAIN ); ?></label> <?php ckpn_display_application_key_dropdown('new_user'); ?><br />

					<input id="new-post-checkbox" type="checkbox" name="ckpn_pushover_notifications_settings[new_post]" value="1" <?php checked( $current['new_post'], '1', true ); ?> /> 
					<label for"ckpn_pushover_notifications_settings[new_post]"><?php _e( 'New Posts are Published', CKPN_CORE_TEXT_DOMAIN ); ?></label>  <?php ckpn_display_application_key_dropdown('new_post'); ?><br />
					<p id="new-post-roles" <?php if ( $current['new_post'] == 0 ) : ?>style="display: none"<?php endif; ?>>
					&nbsp;&nbsp;&nbsp;&nbsp;<strong><?php _e( 'Roles to Notify', CKPN_CORE_TEXT_DOMAIN ); ?></strong><br />
					<?php foreach( $roles as $role_id => $role ) : ?>
						&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="ckpn_pushover_notifications_settings[new_post_roles][<?php echo $role_id; ?>]" value="1" <?php checked( $current['new_post_roles'][$role_id], '1', true ); ?> /> 
						<label for="ckpn_pushover_notifications_settings[new_post_roles][<?php echo $role_id; ?>]"><?php echo $role['name']; ?></label><br />	
					<?php endforeach; ?>
					<br />
					</p>

					<input type="checkbox" name="ckpn_pushover_notifications_settings[new_comment]" value="1" <?php checked( $current['new_comment'], '1', true ); ?> /> 
					<label for"ckpn_pushover_notifications_settings[new_comment]"><?php _e( 'New Comments', CKPN_CORE_TEXT_DOMAIN ); ?></label>  <?php ckpn_display_application_key_dropdown('new_comment'); ?><br />

					&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="ckpn_pushover_notifications_settings[notify_authors]" value="1" <?php checked( $current['notify_authors'], '1', true ); ?> /> 
					<label for="ckpn_pushover_notifications_settings[notify_authors]"><?php _e( 'Notify the Post Author (for multi-author blogs)', CKPN_CORE_TEXT_DOMAIN ); ?></label>  <?php ckpn_display_application_key_dropdown('notify_authors'); ?><br />
					
					<input type="checkbox" name="ckpn_pushover_notifications_settings[password_reset]" value="1" <?php checked( $current['password_reset'], '1', true ); ?> /> 
					<label for="ckpn_pushover_notifications_settings[password_reset]"><?php _e( 'Notify users when password resets are requested for their accounts?', CKPN_CORE_TEXT_DOMAIN ); ?></label>  <?php ckpn_display_application_key_dropdown('password_reset'); ?><br />

					<input type="checkbox" name="ckpn_pushover_notifications_settings[core_update]" value="1" <?php checked( $current['core_update'], '1', true ); ?> /> 
					<label for="ckpn_pushover_notifications_settings[core_update]"><?php _e( 'WordPress Core Update is Available', CKPN_CORE_TEXT_DOMAIN ); ?></label>  <?php ckpn_display_application_key_dropdown('core_update'); ?><br />

					<input type="checkbox" name="ckpn_pushover_notifications_settings[plugin_updates]" value="1" <?php checked( $current['plugin_updates'], '1', true ); ?> /> 
					<label for="ckpn_pushover_notifications_settings[plugin_updates]"><?php _e( 'Plugin & Theme Updates are Available', CKPN_CORE_TEXT_DOMAIN ); ?></label>  <?php ckpn_display_application_key_dropdown('plugin_updates'); ?><br />
				</td>
			</tr>

			<?php do_action( 'ckpn_notification_checkbox_filter' ); ?>

			<tr valign="top">
				<th scope="row"><?php _e( 'Advanced &amp; Debug Options:', CKPN_CORE_TEXT_DOMAIN ); ?><br /><span style="font-size: x-small;"><?php _e( 'With great power, comes great responsiblity.', CKPN_CORE_TEXT_DOMAIN ); ?></span></th>
				<td>
					<input type="checkbox" name="ckpn_pushover_notifications_settings[multiple_keys]" value="1" <?php checked( $current['multiple_keys'], '1', true ); ?> /> 
					<label for="ckpn_pushover_notifications_settings[multiple_keys]"><?php _e( 'Use Multiple Application Keys', CKPN_CORE_TEXT_DOMAIN ); ?></label><br />
					<small><?php _e( 'Allows you to choose what application key alerts get sent from. Useful for sites that may use all their monthly alerts', CKPN_CORE_TEXT_DOMAIN ); ?></small><br />

					<input type="checkbox" name="ckpn_pushover_notifications_settings[sslverify]" value="1" <?php checked( $current['sslverify'], '1', true ); ?> /> 
					<label for="ckpn_pushover_notifications_settings[sslverify]"><?php _e( 'Verify SSL from api.pushover.net?', CKPN_CORE_TEXT_DOMAIN ); ?></label><br />
					<small><?php _e( 'Disable if you get API Notification Errors in the logs. Caused when CURL cannot verify SSL Certificates', CKPN_CORE_TEXT_DOMAIN ); ?></small><br />
					
					<input type="checkbox" name="ckpn_pushover_notifications_settings[logging]" value="1" <?php checked( $current['logging'], '1', true ); ?> /> 
					<label for="ckpn_pushover_notifications_settings[logging]"><?php _e( 'Enable Logging', CKPN_CORE_TEXT_DOMAIN ); ?></label><br />
					<small><?php _e( 'Enable or Disable Logging', CKPN_CORE_TEXT_DOMAIN ); ?></small><br />
				</td>
			</tr>


			<input type="hidden" name="action" value="update" />
			<?php $page_options = apply_filters( 'ckpn_settings_page_options', array( 'ckpn_pushover_notifications_settings' ) ); ?>
			<input type="hidden" name="page_options" value="<?php echo implode( ',', $page_options ); ?>" />

			<?php settings_fields( 'ckpn-update-options' ); ?>
		</table>
		<input type="submit" class="button-primary" value="<?php _e( 'Save Changes', CKPN_CORE_TEXT_DOMAIN ) ?>" />
	</form>
	<div style="margin-top: 10px;">
		<sup>&dagger;</sup> <a href="#" onClick="jQuery( '#cron-help' ).toggle(); return false;"><?php _e( 'Not receiving reports?', CKPN_CORE_TEXT_DOMAIN ); ?></a><br />
		<div id="cron-help" style="display:none">
			&nbsp;&nbsp;&nbsp;&nbsp;<?php _e( 'This feature uses WP-Cron to run. If your site doesn\'t get much traffic, the scheduled task to send your reports might not execute at the specified time. There are 2 options:', CKPN_CORE_TEXT_DOMAIN ); ?><br />
			&nbsp;&nbsp;&nbsp;&nbsp;<?php printf( __( '1. You may need to use the <a href="%s" target="_blank">Improved Cron</a> plugin to help scheduled tasks run.', CKPN_CORE_TEXT_DOMAIN ), 'http://codecanyon.net/item/improved-cron/176543?ref=cklosowski' ); ?><br />
			&nbsp;&nbsp;&nbsp;&nbsp;<?php _e( '2. If you have access to create cron jobs and know how, you can use the following cron to execute wp-cron.php every hour.', CKPN_CORE_TEXT_DOMAIN ); ?><br />
			&nbsp;&nbsp;&nbsp;&nbsp;<code>0 */1 * * * GET <?php echo home_url(); ?>/wp-cron.php</code>
		</div>
	</div>
	<?php
}

/**
 * Display the Logs tab
 * @return void
 */
function ckpn_display_logs() {
	if ( isset( $_GET['clear_logs'] ) && $_GET['clear_logs'] == 'true' ) {
		check_admin_referer( 'ckpn_clear_logs' );
		update_option( 'ckpn_logs', '' );
		$logs_cleared = true;
	}

	if ( isset( $logs_cleared ) && $logs_cleared ) {
	?><div id="setting-error-settings_updated" class="updated settings-error"><p><strong><?php _e( 'Logs Cleared', CKPN_CORE_TEXT_DOMAIN ); ?></strong></p></div><?php
	}

	$current = ckpn_get_options();
	if ( $current['logging'] == false )
		printf( '<div class="error"> <p> %s </p> </div>', esc_html__( 'Logging currently disabled.', CKPN_CORE_TEXT_DOMAIN ) );
	?>
	<a class="button gray" href="<?php echo wp_nonce_url( '?page=pushover-notifications&tab=logs&clear_logs=true', 'ckpn_clear_logs' ); ?>"><?php _e( 'Clear Logs', CKPN_CORE_TEXT_DOMAIN ); ?></a>
	<h3><?php _e( 'Logs:', CKPN_CORE_TEXT_DOMAIN ); ?></h3>
<pre>
<?php // No indent to preserve formatting
echo get_option( 'ckpn_logs' );
?>
</pre>
	<?php
}

/**
 * Display the Extensions tab
 * @return void
 */
function ckpn_display_extensions() {
	$extension_json_data = null;
	if ( false == ( $extension_json_data = get_transient( '_wp_push_extensions_listing' ) ) ) {
		$current = ckpn_get_options();
		if ( $current['sslverify'] == 0 )
			$req_args['sslverify'] = false;

		$response = wp_remote_get( 'https://wp-push.com/extensions?json=true', $req_args );
		if ( $response['response']['code'] == 200 ) {
			$extension_json_data = $response['body'];
			set_transient( '_wp_push_extensions_listing', $extension_json_data, 60*60*24);
		}
	}

	if ( $extension_json_data == null ) {
		$extension_data = __( 'Error Retreiving Extensions Feed', CKPN_CORE_TEXT_DOMAIN );
	} else {
		$extension_data = json_decode( $extension_json_data );
	}

	if ( is_array( $extension_data ) ) {
		foreach ( $extension_data as $extension ) {
			?>
			<div style="width: 300px; height: 320px; float: left; margin: 0 10px 10px 0">
				<a href="<?php echo $extension->link; ?>" target="_blank"><?php echo $extension->image; ?></a><br />
				<a href="<?php echo $extension->link; ?>" target="_blank"><h3><?php echo $extension->title; ?></h3></a>
				<p>
					<?php echo $extension->excerpt; ?>
				</p>
			</div>
			<?php
		}
	} else {
		echo $extension_data;
	}
}

/**
 * Display the Licenses tab
 * @return void
 */
function ckpn_display_licenses() {
	$licenses_page_options = ckpn_get_licenses();
	if ( isset( $_POST['action'] ) ) {
		foreach ( $licenses_page_options as $license ) {
			$license_key = $_POST[$license];
			update_option( $license, $license_key );
		}
		printf( '<div class="updated settings-error"> <p> %s </p> </div>', __( 'Licenses Saved.', CKPN_CORE_TEXT_DOMAIN ) );
	}
	?>
	<form method="post" action="<?php admin_url( 'options-general.php?page=pushover-notifications&tab=licenses' ); ?>">
		<?php wp_nonce_field( 'ckpn-update-licenses' ); ?>
		<table class="form-table">

			<?php do_action( 'ckpn_notification_licenses_page' ); ?>

			<input type="hidden" name="action" value="update" />
			<input type="hidden" name="page_options" value="<?php echo implode(',', $licenses_page_options ); ?>" />

			<?php settings_fields( 'ckpn-update-licenses' ); ?>

		</table>
		<input type="submit" class="button-primary" value="<?php _e( 'Save Licenses', CKPN_CORE_TEXT_DOMAIN ) ?>" />
	</form>
	<?php
}

/**
 * Display the System Info Tab
 * @return void
 */
function ckpn_display_sysinfo() {
	global $wpdb;
	$options = ckpn_get_options();
	if ( $options['application_key'] != false )
		$options['application_key'] = '[removed for display]';

	if ( $options['api_key'] != false )
		$options['api_key'] = '[removed for display]';

	?>
	<textarea style="font-family: Menlo, Monaco, monospace; white-space: pre" onclick="this.focus();this.select()" readonly cols="150" rows="35">
SITE_URL:                 <?php echo site_url() . "\n"; ?>
HOME_URL:                 <?php echo home_url() . "\n"; ?>

CKPN Version:             <?php echo CKPN_VERSION . "\n"; ?>
WordPress Version:        <?php echo get_bloginfo( 'version' ) . "\n"; ?>

PUSHOVER NOTIFICATIONS SETTINGS:
<?php
foreach ( $options as $name => $value ) {
if ( $value == false )
	$value = 'false';

if ( $value == '1' )
	$value = 'true';

echo $name . ': ' . $value . "\n";
}
?>

ACTIVE PLUGINS:
<?php
$plugins = get_plugins();
$active_plugins = get_option( 'active_plugins', array() );

foreach ( $plugins as $plugin_path => $plugin ) {
	// If the plugin isn't active, don't show it.
	if ( ! in_array( $plugin_path, $active_plugins ) )
		continue;

echo $plugin['Name']; ?>: <?php echo $plugin['Version'] ."\n";

}
?>

CURRENT THEME:
<?php
if ( get_bloginfo( 'version' ) < '3.4' ) {
	$theme_data = get_theme_data( get_stylesheet_directory() . '/style.css' );
	echo $theme_data['Name'] . ': ' . $theme_data['Version'];
} else {
	$theme_data = wp_get_theme();
	echo $theme_data->Name . ': ' . $theme_data->Version;
}
?>


Multi-site:               <?php echo is_multisite() ? 'Yes' . "\n" : 'No' . "\n" ?>

ADVANCED INFO:
PHP Version:              <?php echo PHP_VERSION . "\n"; ?>
MySQL Version:            <?php echo mysql_get_server_info() . "\n"; ?>
Web Server Info:          <?php echo $_SERVER['SERVER_SOFTWARE'] . "\n"; ?>

PHP Memory Limit:         <?php echo ini_get( 'memory_limit' ) . "\n"; ?>
PHP Post Max Size:        <?php echo ini_get( 'post_max_size' ) . "\n"; ?>
PHP Time Limit:           <?php echo ini_get( 'max_execution_time' ) . "\n"; ?>

WP_DEBUG:                 <?php echo defined( 'WP_DEBUG' ) ? WP_DEBUG ? 'Enabled' . "\n" : 'Disabled' . "\n" : 'Not set' . "\n" ?>

WP Table Prefix:          <?php echo "Length: ". strlen( $wpdb->prefix ); echo " Status:"; if ( strlen( $wpdb->prefix )>16 ) {echo " ERROR: Too Long";} else {echo " Acceptable";} echo "\n"; ?>

Show On Front:            <?php echo get_option( 'show_on_front' ) . "\n" ?>
Page On Front:            <?php $id = get_option( 'page_on_front' ); echo get_the_title( $id ) . ' #' . $id . "\n" ?>
Page For Posts:           <?php $id = get_option( 'page_on_front' ); echo get_the_title( $id ) . ' #' . $id . "\n" ?>

Session:                  <?php echo isset( $_SESSION ) ? 'Enabled' : 'Disabled'; ?><?php echo "\n"; ?>
Session Name:             <?php echo esc_html( ini_get( 'session.name' ) ); ?><?php echo "\n"; ?>
Cookie Path:              <?php echo esc_html( ini_get( 'session.cookie_path' ) ); ?><?php echo "\n"; ?>
Save Path:                <?php echo esc_html( ini_get( 'session.save_path' ) ); ?><?php echo "\n"; ?>
Use Cookies:              <?php echo ini_get( 'session.use_cookies' ) ? 'On' : 'Off'; ?><?php echo "\n"; ?>
Use Only Cookies:         <?php echo ini_get( 'session.use_only_cookies' ) ? 'On' : 'Off'; ?><?php echo "\n"; ?>

UPLOAD_MAX_FILESIZE:      <?php if ( function_exists( 'phpversion' ) ) echo ini_get( 'upload_max_filesize' ); ?><?php echo "\n"; ?>
POST_MAX_SIZE:            <?php if ( function_exists( 'phpversion' ) ) echo ini_get( 'post_max_size' ); ?><?php echo "\n"; ?>
WordPress Memory Limit:   <?php echo WP_MEMORY_LIMIT; ?><?php echo "\n"; ?>
DISPLAY ERRORS:           <?php echo ( ini_get( 'display_errors' ) ) ? 'On (' . ini_get( 'display_errors' ) . ')' : 'N/A'; ?><?php echo "\n"; ?>
FSOCKOPEN:                <?php echo ( function_exists( 'fsockopen' ) ) ? __( 'Your server supports fsockopen.', 'edd' ) : __( 'Your server does not support fsockopen.', 'edd' ); ?><?php echo "\n"; ?>
	</textarea>
	<?php
}

/**
 * Display the Additional Application Keys Tab
 * @return void
 */
function ckpn_display_additional_keys() {
	if ( isset( $_POST['action'] ) && $_POST['action'] == 'update' ) {
		$new_keys = array();
		$has_errors = false;
		
		if ( !empty( $_POST['additional_keys'] ) ) {
			foreach ( $_POST['additional_keys'] as $array_key => $key_attrs ) {
				if ( !empty( $key_attrs['name'] ) && !empty( $key_attrs['app_key'] ) ) {
					$new_keys[$array_key] = array( 'name' => $key_attrs['name'], 'app_key' => $key_attrs['app_key'] );
				} else {
					$has_errors = true;
				}
			}
		}

		update_option( '_ckpn_additional_app_keys', $new_keys );
		
		if ( !$has_errors ) {
			printf( '<div class="updated settings-error"> <p> %s </p> </div>', __( 'Application Keys Saved.', CKPN_CORE_TEXT_DOMAIN ) );
		} else {
			printf( '<div class="error settings-error"> <p> %s </p> </div>', __( 'One or more of your keys was not saved. Please verify your Name and Key.', CKPN_CORE_TEXT_DOMAIN ) );
		}
	}
	$additional_keys = get_option( '_ckpn_additional_app_keys' );
	?>
	<p>
		<?php _e( 'If your site uses more than your alloted API calls per month, you can specify multiple application keys here to help disperse your requests to the Pushover API.', CKPN_CORE_TEXT_DOMAIN ); ?>
	</p>
	<p>
		<div id="ckpn-add-new-key" class="button-secondary"><?php _e( 'Add New Key', CKPN_CORE_TEXT_DOMAIN ); ?></div>
		<a id="ckpn-add-new-key" class="button-secondary" href="https://pushover.net/apps/build" target="_blank"><?php _e( 'Create a New Application', CKPN_CORE_TEXT_DOMAIN ); ?></a>
	</p>
	<form method="post" action="<?php admin_url( 'options-general.php?page=pushover-notifications&tab=application_keys' ); ?>" id="ckpn_additional_keys_form">
		<?php do_action( 'ckpn_additional_app_keys_settings_after' ); ?>
		<?php wp_nonce_field( 'ckpn-update-keys' ); ?>
		<table class="wp-list-table widefat fixed posts">
			<thead>
				<tr>
					<th width="5%"><?php _e('ID', CKPN_CORE_TEXT_DOMAIN ); ?></th>
					<th width="25%"><?php _e('Key Name', CKPN_CORE_TEXT_DOMAIN ); ?></th>
					<th><?php _e('Application Key', CKPN_CORE_TEXT_DOMAIN ); ?></th>
					<th width="20px"></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th width="5%"><?php _e('ID', CKPN_CORE_TEXT_DOMAIN ); ?></th>
					<th width="25%"><?php _e('Key Name', CKPN_CORE_TEXT_DOMAIN ); ?></th>
					<th><?php _e('Application Key', CKPN_CORE_TEXT_DOMAIN ); ?></th>
					<th width="20px"></th>
				</tr>
			</tfoot>
			<tbody id="ckpn-additional-keys-table-body">
			<?php
			if( !empty( $additional_keys ) ) :
				foreach( $additional_keys as $id => $addtl_key ) : ?>
					<tr class="item">
						<td width="5%"><input type="hidden" name="additional_keys[<?php echo $id; ?>]" value="<?php echo $id; ?>" /><?php echo $id; ?></td>
						<td width="25%"><input type="text" name="additional_keys[<?php echo $id; ?>][name]" value="<?php echo $addtl_key['name']; ?>" /></td>
						<td><input type="text" size="75" name="additional_keys[<?php echo $id; ?>][app_key]" value="<?php echo $addtl_key['app_key']; ?>" /></td>
						<th width="20px"><div class="ckpn-delete-item" style="background-image: url('/wp-admin/images/no.png');height:16px;width:16px"></div></th>
					</tr>
					<?php
				endforeach;
			else : ?>
				<tr id="no-rows-notice">
					<td colspan="4"><?php _e('No additinal keys found', CKPN_CORE_TEXT_DOMAIN ); ?></td>
				</tr>
				<?php 
			endif; 
			?>	
			</tbody>
		</table>
		<br />
		<?php do_action( 'ckpn_additional_app_keys_settings_after' ); ?>
		<?php settings_fields( apply_filters( 'ckpn_additional_keys_settings_fields', 'ckpn-update-keys' ) ); ?>
		<input type="hidden" name="action" value="update" />

		<?php $page_options = apply_filters( 'ckpn_additional_keys_page_options', array( '_ckpn_additional_app_keys' ) ); ?>
		<input type="hidden" name="page_options" value="<?php echo implode(',', $page_options ); ?> " />
		
		<input type="submit" class="button-primary" value="<?php _e( 'Save Keys', CKPN_CORE_TEXT_DOMAIN ) ?>" />
	</form>
	<?php
}

/**
 * Add the Pushover User Key field to the Profile page
 * @param  array $contact_methods List of contact methods
 * @return array                  The list of contact methods with the pushover key added
 * @access public
 */
function ckpn_add_contact_item( $contact_methods ) {
	$contact_methods['ckpn_user_key'] = 'Pushover User Key';

	return $contact_methods;
}

/**
 * Adds in the Pushover Notifications Preferences Profile Section
 * @param  object $user The User object being viewed
 * @return void         Displays HTML
 */
function ckpn_add_profile_settings( $user ) {
	if ( !get_user_meta( $user->ID, 'ckpn_user_key', true ) )
		return;

	$gets_post_notifications = false;
	$new_post_roles = ckpn_get_option( 'new_post_roles' );
	foreach ( $new_post_roles as $role => $value ) {
		if ( current_user_can( $role ) && !empty( $value ) )
			$gets_post_notifications = true;

		if ( $gets_post_notifications == true )
			continue;
	}
	?>
	<h3><?php _e( 'Pushover Notifications Preferences', CKPN_CORE_TEXT_DOMAIN ); ?></h3>
	<table class="form-table">
		<tr>
			<th><?php _e( 'Notify Me Of &hellip;', CKPN_CORE_TEXT_DOMAIN ); ?></th>
			<td>
			<?php if ( $gets_post_notifications ) : ?>
				<input type="checkbox" name="ckpn_user_notify_posts" id="ckpn_user_notify_posts" value="1" <?php checked( '1', get_user_meta( $user->ID, 'ckpn_user_notify_posts', true ), true ); ?> />
				<label for="ckpn_user_notify_posts"><?php _e( 'New Posts Being Published', CKPN_CORE_TEXT_DOMAIN ); ?></label>
			<?php endif; ?>
			</td>
		</tr>
	</table>
	<?php
}