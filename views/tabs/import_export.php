<?php
/**
 * Import / Export the settings view.
 */

$level = '';
if ( is_multisite() ) {
	if ( is_network_admin() ) {
		$level = 'network';
	} else {
		$level = get_current_blog_id();
	}
}

?>
<table cellspacing="15" id="settings-import-export">
    <tr>
        <td>
            <label for="breeze_export_settings" class="breeze_tool_tip"><?php _e( 'Export settings:', 'breeze' ); ?></label>
        </td>
        <td>
            <input type="button" name="breeze_export_settings" id="breeze_export_settings" class="button-primary" value="<?php _e( 'Export JSON', 'breeze' ); ?>">
            <input type="hidden" id="breeze-level" value="<?php echo esc_attr( $level ); ?>">
        </td>
    </tr>
    <tr>
        <td>
            <label for="breeze_import_settings" class="breeze_tool_tip"><?php _e( 'Import settings:', 'breeze' ); ?></label>
        </td>
        <td>
            <input type="file" name="breeze_import_settings" id="breeze_import_settings">
            <input type="button" id="breeze_import_btn" value="<?php _e( 'Import file', 'breeze' ); ?>" class="button-primary"/>
            <p id="file-selected"></p>
            <p id="file-error" class="file_red" style="font-weight: bold"></p>
        </td>
    </tr>
</table>
