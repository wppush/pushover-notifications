<?php
/**
 * The main function to send notifications
 * @param  array $pushover_args Holds the API Arguments used for Pushover.net
 * @return void
 */
function ckpn_send_notification( $pushover_args ) {
	$logentry = '';
	$invalid  = false;
	$options  = ckpn_get_options();

	$defaults = array(
		'token' => ckpn_get_application_key_by_id(),
		'user'  => NULL,
	);

	$api_args = wp_parse_args( $pushover_args, $defaults );

	// Check that we have the required elements
	if ( ! isset( $api_args['title'] ) ) {
		$logentry .= ckpn_log_entry_format( '****** Missing Title ******' );
		$invalid   = true;
	}

	if ( ! isset( $api_args['message'] ) ) {
		$logentry .= ckpn_log_entry_format( '****** Missing Message ******' );
		$invalid   = true;
	}

	if ( $api_args['user'] == NULL ) { // Use the default key if none was provided
		$api_args['user'] = $options['api_key'];
	}

	if ( ! isset( $api_args['user'] ) ) {
		$logentry .= ckpn_log_entry_format( '****** Missing User Key ******' );
		$invalid   = true;
	}

	if ( ! isset( $api_args['token'] ) ) {
		$logentry .= ckpn_log_entry_format( '****** Missing Application Key ******' );
		$invalid   = true;
	}

	if ( ! $invalid ) {

		//Clean house a bit to meet the guidelines of the api
		//https://pushover.net/api#limits
		// Title + Message Limit: 512 Characters
		if ( strlen( $api_args['message'] ) + strlen( $api_args['title'] ) > 512 ) {

			$total_len           = strlen( $api_args['message'] ) + strlen( $api_args['title'] );
			$overage             = $total_len - 520;
			$api_args['message'] = substr( $api_args['message'], 0, -$overage );
			$last_space          = strrpos( $api_args['message'], ' ' );
			$api_args['message'] = substr( $api_args['message'], 0, $last_space ) . '&hellip;';

			$logentry .= ckpn_log_entry_format( 'Title & Message too long, truncating message' );

		}

		// Let's also get entities ready
		$api_args['title']   = html_entity_decode( $api_args['title'], ENT_QUOTES, 'UTF-8' );
		$api_args['message'] = html_entity_decode( $api_args['message'], ENT_QUOTES, 'UTF-8' );

		// URL limit: 500 Characters
		if ( isset( $api_args['url'] ) && strlen( $api_args['url'] ) > 500 ) {
			unset ( $api_args['url'], $api_args['url_title'] );
			$logentry .= ckpn_log_entry_format( 'URL was over 500 characters. Removed URL and URL Title' );
		}

		// URL Title limit: 50 Characters
		if ( isset( $api_args['url_title'] ) && strlen( $api_args['url_title'] ) > 50 ) {
			unset ( $api_args['url_title'] );
			$logentry .= ckpn_log_entry_format( 'URL Title was over 50 characters. Removed URL Title' );
		}

		// Crate the request
		$body = '';
		foreach ( $api_args as $key => $value ) {
			$body .= ( $body == '' ? '' : '&' );
			$body .= urlencode( $key ) . '=' . urlencode( $value );
		}

		$req_args = array( 'body'=>$body );

		if ( $options['sslverify'] == false ) {
			$req_args['sslverify'] = false;
		}

		if ( $options['logging'] == false ) {
			$req_args['blocking'] = false;
		}

		// Where the magic happens
		$response = wp_remote_post( 'https://api.pushover.net/1/messages.json', $req_args );

		// From here on out it's just managing logging (if enabled)
		if ( $options['logging'] == 1 ) {
			// If the API Fails for some reason send it via the email system of Pushover
			// This can only be done with logging enabled as it would require a blocking request
			if ( is_wp_error( $response ) ) {
				$to = $user_key.'@api.pushover.net';

				if ( wp_mail( $to, $subject, $message ) ) {
					$logentry .= ckpn_log_entry_format( 'Email - ' . $api_args['user'] . ' - Subject: ' . $api_args['title'] );
				} else {
					$logentry .= ckpn_log_entry_format( '******Email Notification Failed******' );
				}

				$logentry .= ckpn_log_entry_format( '******API Notification Failed******' );

			} elseif ( $response['response']['code'] == 500 ) {

				$logentry .= ckpn_log_entry_format( 'API Response - Internal Server Error 500' );
				$logentry .= ckpn_log_entry_format( 'Typically occurs with non-entitied characters' );

			} else {

				$logentry .= ckpn_log_entry_format( 'API - ' . $api_args['user'] . ' | ' . $api_args['token'] . ' - ' . $api_args['title'] );

			}
		}
	}

	if ( $options['logging'] == 1 ) {

		$current_logs = get_option( 'ckpn_logs' );
		$new_logs = $logentry . $current_logs;

		$logs_array = explode( "\n", $new_logs );

		if ( count( $logs_array ) > 100 ) {
			$logs_array = array_slice( $logs_array, 0, 100 );
			$new_logs   = implode( "\n", $logs_array );
		}

		update_option( 'ckpn_logs', $new_logs );
	}
}

/**
 * Send Notifications for plugin/theme upgrades
 * @return void
 */
function ckpn_plugin_update_checks() {
	$options = ckpn_get_options();
	require_once ( ABSPATH . 'wp-admin/includes/update.php' );
	require_once ( ABSPATH . 'wp-admin/includes/admin.php' );

	// Default to nothing
	$plugin_count = 0;
	$theme_count  = 0;
	$core_update  = false;

	if ( $options['plugin_updates'] ) {
		// Force an update check
		wp_plugin_update_rows();
		$plugin_updates = get_site_transient( 'update_plugins' );
		$plugin_count   = count( $plugin_updates->response );

		wp_theme_update_rows();
		$theme_updates = get_site_transient( 'update_themes' );
		$theme_count   =  count( $theme_updates->response );
	}

	if ( $options['core_update'] ) {
		$core_info      = get_site_transient( 'update_core' );
		$core_update    = version_compare( $core_info->version_checked, $core_info->updates[0]->current, '<' );
	}

	if ( empty( $plugin_count ) && empty( $theme_count ) && ! $core_update ) {
		return false;
	}

	$title = get_bloginfo( 'name' ) . ': ' . __( 'Updates Available', CKPN_CORE_TEXT_DOMAIN ) ;
	$title = apply_filters( 'ckpn_plugin_update_title', $title );

	$message   = '';
	$core_text = __( 'WordPress update available', CKPN_CORE_TEXT_DOMAIN );
	$message  .= ( $core_update ) ? $core_text . "\n" : '';

	$plugin_text = _n( 'Plugin has', 'Plugins have', $plugin_count, CKPN_CORE_TEXT_DOMAIN );
	$message    .= ( !empty( $plugin_count ) ) ? sprintf( __( '%d %s updates', CKPN_CORE_TEXT_DOMAIN ), $plugin_count, $plugin_text ) . "\n" : '';

	$theme_text  = _n( 'Theme has', 'Themes have', $theme_count, CKPN_CORE_TEXT_DOMAIN );
	$message    .= ( !empty( $theme_count ) ) ? sprintf( __( '%d %s updates', CKPN_CORE_TEXT_DOMAIN ), $theme_count, $theme_text ) : '';

	$priority  = '1';
	$url       = admin_url( 'update-core.php' );
	$url_title = 'Update Now';

	$args = array( 'title' => $title, 'message' => $message, 'priority' => $priority, 'url' => $url, 'url_title' => $url_title );

	if ( $options['multiple_keys'] ) {
		$args['token'] = ckpn_get_application_key_by_setting( 'plugin_updates' );
	}

	ckpn_send_notification( $args );
}

/**
 * Send Notifications for new user registrations
 * @param  int $user_id User ID of the new registered account, passed from the action
 * @return void
 */
function ckpn_user_registration( $user_id ) {
	$options = ckpn_get_options();

	$title = get_bloginfo( 'name' ) . ': ' . __( 'New User', CKPN_CORE_TEXT_DOMAIN );
	$title = apply_filters( 'ckpn_newuser_subject', $title, $user_id);


	$user_data = get_userdata( $user_id );
	$message   = sprintf( __( '%s created an account.', CKPN_CORE_TEXT_DOMAIN ), $user_data->user_login );
	$message   = apply_filters( 'ckpn_newuser_message', $message, $user_id);

	if ( $title === false || $message === false ) {
		return;
	}

	$args = array( 'title' => $title, 'message' => $message );
	$args = apply_filters( 'ckpn_newuser_args', $args, $user_id );


	if ( $options['multiple_keys'] ) {
		$args['token'] = ckpn_get_application_key_by_setting( 'new_user' );
	}

	ckpn_send_notification( $args );
}

/**
 * Send Notifications for new comments
 * @param  int $comment_id The ID of the newly submitted comment
 * @return void
 */
function ckpn_new_comment( $comment_id ) {
	$options      = ckpn_get_options();
	$comment_data = get_comment( $comment_id );

	// This is not the comment we're looking for. Move Along.
	if ( $comment_data->comment_approved == 'spam' ) {
		return;
	}

	switch ( $comment_data->comment_type ) {
		case 'pingback':
			$comment_type = __( 'pingback', CKPN_CORE_TEXT_DOMAIN );
			break;
		case 'trackback':
			$comment_type = __( 'trackback', CKPN_CORE_TEXT_DOMAIN );
			break;
		case 'comment':
		default:
			$comment_type = __( 'comment', CKPN_CORE_TEXT_DOMAIN );
			if ( $comment_data->comment_approved != 0 ){
				$url = get_comment_link( $comment_data );
				$url_title = 'View Comment';
			}
			break;
	}

	$title = get_bloginfo( 'name' ) . ': ' . ucfirst( $comment_type );
	$title = apply_filters( 'ckpn_newcomment_subject', $title, $comment_id );

	$post_data = get_post( $comment_data->comment_post_ID );
	$message   = sprintf( __( 'by %1$s on %2$s', CKPN_CORE_TEXT_DOMAIN ), $comment_data->comment_author, $post_data->post_title );
	$message   = apply_filters( 'ckpn_newcomment_message', $message, $comment_id );


	// Notify the Admin User
	$args = array( 'title' => $title, 'message' => $message );

	if ( isset( $url ) && isset( $url_title ) ) {
		$args['url'] = $url;
		$args['url_title'] = $url_title;
	}

	if ( $options['multiple_keys'] ) {
		$args['token'] = ckpn_get_application_key_by_setting( 'new_comment' );
	}

	ckpn_send_notification( $args );

	// Check if we should notify the author as well
	if ( $options['notify_authors'] ) {
		$author_user_key = get_user_meta( $post_data->post_author, 'ckpn_user_key', true );
		if ( $author_user_key != '' && $author_user_key != $options['api_key'] ) { // Only send if the user has a key and it's not the same as the admin key
			// Notify the Author their post has a comment
			$args['user'] = $author_user_key;

			if ( $options['multiple_keys'] ) {
				$args['token'] = ckpn_get_application_key_by_setting( 'notify_authors' );
			}

			ckpn_send_notification( $args );
		}
	}
}

/**
 * Send notifications for lost password requests
 * @return void
 */
function ckpn_lost_password_request() {
	if ( !empty( $_POST['user_login'] ) ) {
		if ( strpos( $_POST['user_login'], '@' ) ) {
			$user_data = get_user_by( 'email', trim( $_POST['user_login'] ) );
		} else {
			$login = trim( $_POST['user_login'] );
			$user_data = get_user_by( 'login', $login );
		}
	}

	if ( !empty( $user_data ) ) {
		$user_pushover_key = get_user_meta( $user_data->data->ID, 'ckpn_user_key', true );
		if ( $user_pushover_key != '' ) {
			$options = ckpn_get_options();
			$title   = get_bloginfo( 'name' ) . ': ' . __( 'Password Reset Request', CKPN_CORE_TEXT_DOMAIN ) ;
			$title   = apply_filters( 'ckpn_password_request_subject', $title );

			$message = sprintf( __( 'A password reset request was made for your account. If this was not you pelase verify your account is secure.', CKPN_CORE_TEXT_DOMAIN ) );
			$message = apply_filters( 'ckpn_password_request_message', $message, $login, $user_data);

			$args = array( 'title' => $title, 'message' => $message, 'user' => $user_pushover_key, 'priority' => 1 );

			if ( $options['multiple_keys'] ) {
				$args['token'] = ckpn_get_application_key_by_setting( 'password_reset' );
			}

			ckpn_send_notification( $args );
		}
	}
}

/**
 * Fires when a new blog post is moved into the published status
 * @param  string $new_status Status the blog post is moving to
 * @param  string $old_status Previous post status
 * @param  object $post       The Post Object
 * @return void
 */
function ckpn_post_published( $new_status, $old_status, $post ) {
	// By default only send on post and page publishing
	$allowed_post_types = apply_filters( 'ckpn_post_publish_types', array( 'post', 'page' ) );

	// Only do this when a post transitions to being published
	if ( in_array( $post->post_type, $allowed_post_types ) && $new_status == 'publish' && $old_status != 'publish' ) {
		$user_keys = array();

		$title = get_bloginfo( 'name' ) . ': ' . __( 'New Post', CKPN_CORE_TEXT_DOMAIN);
		$title = apply_filters( 'ckpn_new_post_title', $title, $post );

		$author_data = get_userdata( $post->post_author );
		$author_name = $author_data->display_name;

		$message = get_the_title( $post->ID ) . __( ' by ', CKPN_CORE_TEXT_DOMAIN ) . $author_name;
		$message = apply_filters( 'ckpn_new_post_message', $message, $post);

		$url       = get_permalink( $post->ID );
		$url_title = __( 'View Post', CKPN_CORE_TEXT_DOMAIN );

		$args = array( 'title' => $title, 'message' => $message, 'url' => $url, 'url_title' => $url_title );

		$new_post_roles = ckpn_get_option( 'new_post_roles' );
		$user_array     = array();

		foreach ( $new_post_roles as $role => $value ) {
			$user_args = array( 'role' => $role, 'fields' => 'ID' );
			$users = get_users( $user_args );

			$user_array = array_unique( array_merge( $users, $user_array ) );
		}

		$super_admins = array();
		if ( defined( 'MULTISITE' ) && MULTISITE ) {
			$super_admin_logins = get_super_admins();
			foreach ( $super_admin_logins as $super_admin_login ) {
				$user = get_user_by( 'login', $super_admin_login );
				if ( $user ) {
					$super_admins[] = $user->ID;
				}
			}
		}

		$users_to_alert = array_unique( array_merge( $user_array, $super_admins ) );
		$current_user = wp_get_current_user();
		// Unset the Post Author for non-scheduled posts
		if ( $old_status !== 'future' && ( $key = array_search( $post->post_author, $users_to_alert ) ) !== false && $current_user->ID == $post->post_author ) {
			unset( $users_to_alert[$key] );
		}

		$options = ckpn_get_options();
		// Add the default admin key from settings if it's different than the authors
		if ( get_user_meta( $post->post_author, 'ckpn_user_key', true ) !== $options['api_key'] ) {
			$user_keys = array( $options['api_key'] );
		}

		$users_with_keys = ckpn_get_users_with_keys();
		// Search the users for their Keys and send the posts
		foreach ( $users_to_alert as $user ) {
			$selected = get_user_meta( $user, 'ckpn_user_notify_posts', true );
			if ( $selected && array_key_exists( $user, $users_with_keys ) ) {
				$user_keys[] = $users_with_keys[$user];
			}
		}

		$user_keys = array_unique( $user_keys );

		foreach ( $user_keys as $user ) {
			$args['user'] = $user;
			ckpn_send_notification( $args );
		}
	}
}
