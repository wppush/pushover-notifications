<?php
/**
 * Whitelist the mapping setting to the general settings tab
 * @return void
 */
function ckpn_register_additional_key_mappings() {
	register_setting( 'ckpn-update-options', '_ckpn_additional_key_mapping' );
}

/**
 * Add the mapping setting to the list of page_options on the general settings tab
 * @param  array $settings The current list of settings on the page, supplied by the filter: ckpn_settings_page_options
 * @return array           The settings list with mapping added
 */
function ckpn_enable_additional_key_mappings( $settings ) {
	$settings[] = '_ckpn_additional_key_mapping';

	return $settings;
}

/**
 * Shows the additional application key drop down, given a setting name
 * @param  string $setting_name The corresponding setting name (should match that of the options name for sending notifications
 * to keep things simple)
 * @return void                Echo's out the drop down, no return
 */
function ckpn_display_application_key_dropdown( $setting_name = false ) {
	$options = ckpn_get_options();
	if ( !( $options['multiple_keys'] ) )
		return false;

	if ( empty( $setting_name ) )
		return false;

	$all_keys = ckpn_get_application_keys();
	$current_mappings = get_option( '_ckpn_additional_key_mapping' );

	?><select name="_ckpn_additional_key_mapping[<?php echo $setting_name; ?>]"><?php
	foreach ( $all_keys as $id => $key ) {
		$currently_mapped_option = ( isset( $current_mappings[$setting_name] ) ) ? $current_mappings[$setting_name] : false;
		?><option value="<?php echo $id; ?>" <?php selected( $id, $currently_mapped_option, true ); ?>><?php echo $key['name']; ?></option><?php
	}
	?></select><?php
}

/**
 * Saves the User Profile Settings
 * @param  int $user_id The User ID being saved
 * @return void         Saves to Usermeta
 */
function ckpn_save_profile_settings( $user_id ) {
	if ( !current_user_can( 'edit_user', $user_id ) )
		return false;

	$user_key = sanitize_text_field( $_POST['ckpn_user_key'] );
	ckpn_update_user_to_keys_list( $user_id, $user_key );

	$options = ckpn_get_options();
	if ( $options['new_post'] )
		update_user_meta( $user_id, 'ckpn_user_notify_posts', $_POST['ckpn_user_notify_posts'] );
}

function ckpn_wp_dropdown_roles( $selected = false ) {
	var_dump($selected);
	$p = '';
	$r = '';
	$editable_roles = get_editable_roles();
	foreach ( $editable_roles as $role => $details ) {
		$name = translate_user_role($details['name'] );
		if ( $selected == $role ) // preselect specified role
			$p = "\n\t<option selected='selected' value='" . esc_attr($role) . "'>$name</option>";
		else
			$r .= "\n\t<option value='" . esc_attr($role) . "'>$name</option>";
	}
	echo $p . $r;
}