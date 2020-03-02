<?php
defined('ABSPATH') or die;

$basic = breeze_get_option( 'basic_settings', true );
?>
<table cellspacing="15">
    <tr>
        <td>
            <label for="cache-system"><?php _e('Cache System', 'breeze'); ?></label>
        </td>
        <td>
            <input type="checkbox" id="cache-system" name="cache-system"
                   value='1' <?php checked($basic['breeze-active'], '1') ?>/>
            <span class="breeze_tool_tip">
                <?php _e('This is the basic cache that we recommend should be kept enabled in all cases. Basic cache will build the internal and static caches for the WordPress websites.', 'breeze') ?>
            </span>
        </td>
    </tr>
    <tr>
        <td>
            <label for="cache-ttl"><?php _e('Purge cache after', 'breeze'); ?></label>
        </td>
        <td>
            <input type="text" id="cache-ttl" size="5" name="cache-ttl"
                   value='<?php echo(!empty($basic['breeze-ttl']) ? (int)$basic['breeze-ttl'] : '1440'); ?>'/>
            <span class="breeze_tool_tip" style="vertical-align: baseline">
                <?php _e('Automatically purge internal cache after X minutes. By default this is set to 1440 minutes (1 day)', 'breeze') ?>
            </span>
        </td>
    </tr>
    <tr>
        <td>
            <label class="breeze_tool_tip"><?php _e('Minification', 'breeze'); ?></label>
        </td>
        <td>
            <ul>
                <li>
                    <input type="checkbox" name="minification-html" id="minification-html"
                           value="1" <?php checked($basic['breeze-minify-html'], '1') ?>/>
                    <label class="breeze_tool_tip" for="minification-html">
                        <?php _e('HTML', 'breeze') ?>
                    </label>
                </li>
                <li>
                    <input type="checkbox" name="minification-css" id="minification-css"
                           value="1" <?php checked($basic['breeze-minify-css'], '1') ?>/>
                    <label class="breeze_tool_tip" for="minification-css">
                        <?php _e('CSS', 'breeze') ?>
                    </label>
                </li>
                <li>
                    <input type="checkbox" name="minification-js" id="minification-js"
                           value="1" <?php checked($basic['breeze-minify-js'], '1') ?>/>
                    <label class="breeze_tool_tip" for="minification-js">
                        <?php _e('JS', 'breeze') ?>
                    </label>
                </li>
                <li>
                    <input type="checkbox" name="include-inline-js" id="include-inline-js"
                           value="1" <?php checked($basic['breeze-include-inline-js'], '1') ?>/>
                    <label class="breeze_tool_tip" for="include-inline-js">
                        <?php _e('Include inline JS', 'breeze') ?>
                    </label>
                </li>
                <li>
                    <input type="checkbox" name="include-inline-css" id="include-inline-css"
                           value="1" <?php checked($basic['breeze-include-inline-css'], '1') ?>/>
                    <label class="breeze_tool_tip" for="include-inline-css">
                        <?php _e('Include inline CSS', 'breeze') ?>
                    </label>
                </li>
                <li>
                    <span><?php _e('Check the above boxes to minify HTML, CSS, or JS files.', 'breeze') ?></span>
                    <br>
                    <span>
						<b><?php esc_html_e( 'Note', 'breeze' ); ?>:&nbsp;</b>
                        <span style="color: #ff0000"><?php _e('We recommend testing minification on a staging website before deploying it on a live website. Minification is known to cause issues on the frontend.', 'breeze') ?></span>
                    </span>
                </li>
            </ul>

        </td>
    </tr>

	<?php

	$htaccess_options = array(
		'gzip-compression' => array(
			'label' => __( 'Gzip Compression', 'breeze' ),
			'desc'  => __( 'Enable this to compress your files making HTTP requests fewer and faster.', 'breeze' ),
		),
		'browser-cache'    => array(
			'label' => __( 'Browser Cache', 'breeze' ),
			'desc'  => __( 'Enable this to add expires headers to static files. This will ask browsers to either request a file from server or fetch from the browser’s cache.', 'breeze' ),
		),
	);

	$supports_conditionals = breeze_is_supported( 'conditional_htaccess' );

	foreach ( $htaccess_options as $fid => $field ) {
		$is_disabled = is_multisite() && ! is_network_admin() && ! $supports_conditionals;
		$is_checked  = isset( $basic[ 'breeze-' . $fid ] ) && '1' === $basic[ 'breeze-' . $fid ] && ! $is_disabled;

		?>
		<tr>
			<td>
				<label for="<?php echo esc_attr( $fid ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
			</td>
			<td>
				<input type="checkbox" id="<?php echo esc_attr( $fid ); ?>" name="<?php echo esc_attr( $fid ); ?>"
					value='1' <?php checked( $is_checked, true ); ?> <?php echo $is_disabled ? 'disabled="disabled"' : ''; ?>/>
				<span class="breeze_tool_tip"><?php echo esc_html( $field['desc'] ); ?></span>
				<?php if ( $is_disabled ) { ?>
					<br>
					<span>
						<b><?php esc_html_e( 'Note', 'breeze' ); ?>:&nbsp;</b>
						<span style="color: #ff0000"><?php printf( esc_html__( 'Enabling/disabling %s for subsites is only available for Apache 2.4 and above. For lower versions, the Network-level settings will apply.', 'breeze' ), $field['label'] ); ?></span>
					</span>
				<?php } ?>
			</td>
		</tr>
		<?php
	}

	?>

    <tr style="display: none;">
        <td style="vertical-align: middle">
            <label for="desktop-cache" class="breeze_tool_tip"> <?php _e('Desktop Cache', 'breeze') ?></label>
        </td>
        <td>
            <select id="desktop-cache" name="desktop-cache">
                <option value="1" <?php echo ($basic['breeze-desktop-cache'] == '1') ? 'selected="selected"' : '' ?>><?php _e('Activated', 'breeze') ?></option>
                <option value="2" <?php echo ($basic['breeze-desktop-cache'] == '2') ? 'selected="selected"' : '' ?>><?php _e('No cache for desktop', 'breeze') ?></option>
            </select>
        </td>
    </tr>

    <tr style="display: none;">
        <td style="vertical-align: middle">
            <label for="mobile-cache" class="breeze_tool_tip"> <?php _e('Mobile Cache', 'breeze') ?></label>
        </td>
        <td>
            <select id="mobile-cache" name="mobile-cache">
                <option value="1" <?php echo ($basic['breeze-mobile-cache'] == '1') ? 'selected="selected"' : '' ?>><?php _e('Automatic (same as desktop)', 'breeze') ?></option>
                <option value="2" <?php echo ($basic['breeze-mobile-cache'] == '2') ? 'selected="selected"' : '' ?>><?php _e('Specific mobile cache', 'breeze') ?></option>
                <option value="3" <?php echo ($basic['breeze-mobile-cache'] == '3') ? 'selected="selected"' : '' ?>><?php _e('No cache for mobile', 'breeze') ?></option>
            </select>
        </td>
    </tr>
    <tr>
    <td>
        <label class="breeze_tool_tip"><?php _e('Enable cache for loggedin users', 'breeze'); ?></label>
    </td>
        <td>
            <ul>
                <li>
                    <input type="checkbox" name="breeze-admin-cache" id="breeze-admin-cache"
                           value="0" <?php checked($basic['breeze-disable-admin'], '0') ?>/>
                    <label class="breeze_tool_tip" for="breeze-admin-cache">
				        <?php _e('Enable cache for WP standard user roles: Administrator, Editor, Author, Contributor.', 'breeze') ?>

                    </label>
                    <br/>
                    <span>
						<b><?php esc_html_e( 'Note', 'breeze' ); ?>:&nbsp;</b>
						<span style="color: #ff0000"><?php echo esc_html__( 'This option might not work properly with some page builders.', 'breeze' ) ?></span>
					</span>
                </li>
            </ul>
        </td>
    </tr>
</table>
