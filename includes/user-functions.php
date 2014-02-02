<?php
/**
 * Get the list of users with API keys in their profile
 * @return array An array of the list of users [id] => key
 */
function ckpn_get_users_with_keys() {
	$users_with_keys = get_option( '_ckpn_users_with_keys', array() );

	return apply_filters( 'ckpn_get_users_with_key', $users_with_keys );
}

/**
 * Add/Remove a user from the list of users
 * @param  int/boolean $user_id  The User ID to add/update
 * @param  string/boolean $user_key The user's Pushover API Key
 * @return boolean  If the status was updated successfully
 */
function ckpn_update_user_to_keys_list( $user_id = false, $user_key = false ) {
	if ( false === $user_id || false === $user_key )
		return;

	$current_users_with_keys = ckpn_get_users_with_keys();

	if ( empty( $user_key ) ) {
		unset( $current_users_with_keys[$user_id] );
	} else {
		$new_user[$user_id] = $user_key;
		// Using + instead of array_merge to preserve keys, takes left key over right in the case of duplicates
		$current_users_with_keys = $new_user + $current_users_with_keys;
	}

	return update_option( '_ckpn_users_with_keys', apply_filters( 'ckpn_update_user_to_keys_list', $current_users_with_keys ) );
}

/**
 * Multisite User Addition
 * @param  int $user_id The User ID being added to a blog
 * @param  string $role The Role of the  user (unnecessary for our use)
 * @param  int $blog_id The Blog ID the user is being added to
 * @return void
 */
function ckpn_multisite_add_user( $user_id, $role, $blog_id ) {
	$user_key = get_user_meta( $user_id, 'ckpn_user_key', true );

	if ( !empty( $user_key ) ) {
		$users_with_keys = get_blog_option( $blog_id, '_ckpn_users_with_keys' );
		$users_with_keys = array( $user_id => $user_key ) + $users_with_keys;

		update_blog_option( $blog_id, '_ckpn_users_with_keys',  $users_with_keys );
	}
}

/**
 * Multisite User Removal
 * @param  int $user_id The User ID being removed
 * @param  int $blog_id The Blog ID the user is being removed from
 * @return void
 */
function ckpn_multisite_remove_user( $user_id, $blog_id ) {
	$current_users_with_keys = get_blog_option( $blog_id, '_ckpn_users_with_keys' );
	unset( $current_users_with_keys[$user_id] );

	update_blog_option( $blog_id, '_ckpn_users_with_keys',  $current_users_with_keys );
}