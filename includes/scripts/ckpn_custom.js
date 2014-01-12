jQuery('#ckpn-add-new-key').click(function() {
	var new_id = jQuery('#ckpn-additional-keys-table-body tr.item').length;
	var new_row = '<tr class="item"><td width="5%"><input type="hidden" name="additional_keys[' + new_id + ']" value="' + new_id + '" />' + new_id + '</td><td width="25%"><input type="text" name="additional_keys[' + new_id + '][name]" value="" placeholder="Enter A Name"  id="new-input-' + new_id + '" /></td><td><input type="text" size="75" name="additional_keys[' + new_id + '][app_key]" value="" placeholder="Application Key" /></td><td width="20px"></td></tr>';

	if ( new_id == 0 ) {
		jQuery('#ckpn-additional-keys-table-body tr:last').after(new_row);
	} else {
		jQuery('#ckpn-additional-keys-table-body').append(new_row);
	}
	jQuery('#no-rows-notice').remove();
	jQuery('#new-input-' + new_id).focus();
})

jQuery('.ckpn-delete-item').click(function() {
	jQuery(this).parent().parent().remove();

	jQuery('#ckpn_additional_keys_form').submit();
})

jQuery('#new-post-checkbox').change(function() {
	var checked = jQuery(this).attr('checked');

	if (checked) {
		jQuery('#new-post-roles').show();
	} else {
		jQuery('#new-post-roles').hide();
		jQuery('#new-post-roles > input').removeAttr('checked');
	}
})
