<?php
defined( 'ABSPATH' ) or die;

$basic = breeze_get_option( 'basic_settings', true );
?>
<table cellspacing="15" id="basic-panel">
	<tr>
		<td>
			<label for="cache-system"><?php _e( 'Cache System', 'breeze' ); ?></label>
		</td>
		<td>
			<input type="checkbox" id="cache-system" name="cache-system"
				   value='1' <?php ( isset( $basic['breeze-active'] ) ) ? checked( $basic['breeze-active'], '1' ) : ''; ?> />
			<span class="breeze_tool_tip">
				<?php _e( 'This is the basic cache that we recommend should be kept enabled in all cases. Basic cache will build the internal and static caches for the WordPress websites.', 'breeze' ); ?>
			</span>
		</td>
	</tr>
	<tr>
		<td>
			<label for="safe-cross-origin"><?php _e( 'Cross-origin safe links', 'breeze' ); ?></label>
		</td>
		<td>
			<input type="checkbox" id="safe-cross-origin" name="safe-cross-origin"
				   value='1' <?php ( isset( $basic['breeze-cross-origin'] ) ) ? checked( $basic['breeze-cross-origin'], '1' ) : ''; ?>/>
			<span class="breeze_tool_tip">
				<?php _e( 'Apply "noopener noreferrer" to links which have target"_blank" attribute and the anchor leads to external websites.', 'breeze' ); ?>
			</span>
		</td>
	</tr>
	<tr>
		<td>
			<label for="cache-ttl"><?php _e( 'Purge cache after', 'breeze' ); ?></label>
		</td>
		<td>
			<input type="text" id="cache-ttl" size="5" name="cache-ttl"
				   value='<?php echo( isset( $basic['breeze-ttl'] ) && ! empty( $basic['breeze-ttl'] ) ? (int) $basic['breeze-ttl'] : '1440' ); ?>'/>
			<span class="breeze_tool_tip" style="vertical-align: baseline">
				<?php _e( 'Automatically purge internal cache after X minutes. By default this is set to 1440 minutes (1 day)', 'breeze' ); ?>
			</span>
		</td>
	</tr>
	<tr>
		<td>
			<label class="breeze_tool_tip"><?php _e( 'Minification', 'breeze' ); ?></label>
		</td>
		<td>
			<ul>
				<li>
					<input type="checkbox" name="minification-html" id="minification-html"
						   value="1" <?php ( isset( $basic['breeze-minify-html'] ) ) ? checked( $basic['breeze-minify-html'], '1' ) : ''; ?> />
					<label class="breeze_tool_tip" for="minification-html">
						<?php _e( 'HTML', 'breeze' ); ?>
					</label>
				</li>
				<li>
					<input type="checkbox" name="minification-css" id="minification-css"
						   value="1" <?php ( isset( $basic['breeze-minify-css'] ) ) ? checked( $basic['breeze-minify-css'], '1' ) : ''; ?> />
					<label class="breeze_tool_tip" for="minification-css">
						<?php _e( 'CSS', 'breeze' ); ?>
					</label>
				</li>
				<li id="font-display-swap" style="display: none">
					&nbsp;&nbsp;&nbsp;&nbsp;
					<input type="checkbox" name="font-display" id="font-display"
						   value="1" <?php ( isset( $basic['breeze-font-display-swap'] ) ) ? checked( $basic['breeze-font-display-swap'], '1' ) : ''; ?>/>
					<label class="breeze_tool_tip" for="font-display">
						<?php _e( 'Font remain visible during load', 'breeze' ); ?>
					</label>
				</li>
				<li>
					<input type="checkbox" name="minification-js" id="minification-js"
						   value="1" <?php ( isset( $basic['breeze-minify-js'] ) ) ? checked( $basic['breeze-minify-js'], '1' ) : ''; ?> />
					<label class="breeze_tool_tip" for="minification-js">
						<?php _e( 'JS', 'breeze' ); ?>
					</label>
				</li>
				<li>
					<input type="checkbox" name="include-inline-js" id="include-inline-js"
						   value="1" <?php ( isset( $basic['breeze-include-inline-js'] ) ) ? checked( $basic['breeze-include-inline-js'], '1' ) : ''; ?>/>
					<label class="breeze_tool_tip" for="include-inline-js">
						<?php _e( 'Include inline JS', 'breeze' ); ?>
					</label>
				</li>
				<li>
					<input type="checkbox" name="include-inline-css" id="include-inline-css"
						   value="1" <?php ( isset( $basic['breeze-include-inline-css'] ) ) ? checked( $basic['breeze-include-inline-css'], '1' ) : ''; ?> />
					<label class="breeze_tool_tip" for="include-inline-css">
						<?php _e( 'Include inline CSS', 'breeze' ); ?>
					</label>
				</li>
				<li>
					<span><?php _e( 'Check the above boxes to minify HTML, CSS, or JS files.', 'breeze' ); ?></span>
					<br>
					<span>
						<b><?php esc_html_e( 'Note', 'breeze' ); ?>:&nbsp;</b>
						<span style="color: #ff0000"><?php _e( 'We recommend testing minification on a staging website before deploying it on a live website. Minification is known to cause issues on the frontend.', 'breeze' ); ?></span>
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

    <?php
    if ( ! isset( $basic['breeze-desktop-cache'] ) ) {
	    $basic['breeze-desktop-cache'] = '1';
    }

    if ( ! isset( $basic['breeze-mobile-cache'] ) ) {
	    $basic['breeze-mobile-cache'] = '1';
    }
    ?>
	<tr style="display: none;">
		<td style="vertical-align: middle">
			<label for="desktop-cache" class="breeze_tool_tip"> <?php _e( 'Desktop Cache', 'breeze' ); ?></label>
		</td>
		<td>
			<select id="desktop-cache" name="desktop-cache">
				<option value="1" <?php echo ( $basic['breeze-desktop-cache'] == '1' ) ? 'selected="selected"' : ''; ?>><?php _e( 'Activated', 'breeze' ); ?></option>
				<option value="2" <?php echo ( $basic['breeze-desktop-cache'] == '2' ) ? 'selected="selected"' : ''; ?>><?php _e( 'No cache for desktop', 'breeze' ); ?></option>
			</select>
		</td>
	</tr>

	<tr style="display: none;">
		<td style="vertical-align: middle">
			<label for="mobile-cache" class="breeze_tool_tip"> <?php _e( 'Mobile Cache', 'breeze' ); ?></label>
		</td>
		<td>
			<select id="mobile-cache" name="mobile-cache">
				<option value="1" <?php echo ( $basic['breeze-mobile-cache'] == '1' ) ? 'selected="selected"' : ''; ?>><?php _e( 'Automatic (same as desktop)', 'breeze' ); ?></option>
				<option value="2" <?php echo ( $basic['breeze-mobile-cache'] == '2' ) ? 'selected="selected"' : ''; ?>><?php _e( 'Specific mobile cache', 'breeze' ); ?></option>
				<option value="3" <?php echo ( $basic['breeze-mobile-cache'] == '3' ) ? 'selected="selected"' : ''; ?>><?php _e( 'No cache for mobile', 'breeze' ); ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td>
			<label class="breeze_tool_tip"><?php _e( 'Enable cache for loggedin users', 'breeze' ); ?></label>
		</td>
		<td>
			<ul>
				<li>
					<?php
					global $wp_roles;
					$roles = $wp_roles->roles;

					foreach ( $roles as $user_role => $user_role_data ) {
						$is_checked_role = 0;
						if ( is_array( $basic['breeze-disable-admin'] ) && isset( $basic['breeze-disable-admin'] ) && isset( $basic['breeze-disable-admin'][ $user_role ] ) ) {
							$is_checked_role = (int) $basic['breeze-disable-admin'][ $user_role ];
						}

						?>
						<input type="checkbox" name="breeze-admin-cache[<?php echo esc_attr( $user_role ); ?>]" id="breeze-admin-cache-<?php echo esc_attr( $user_role ); ?>"
							   value="1" <?php ( isset( $is_checked_role ) && 1 === $is_checked_role ) ? checked( $is_checked_role, '1' ) : ''; ?> />
						<label class="breeze_tool_tip" for="breeze-admin-cache-<?php echo esc_attr( $user_role ); ?>">
							<?php echo esc_html( $user_role_data['name'] ); ?>
					</label>
					<br/>
						<?php
					}
					?>
					<span>
						<b><?php esc_html_e( 'Note', 'breeze' ); ?>:&nbsp;</b>
						<span style="color: #ff0000"><?php echo esc_html__( 'This option might not work properly with some page builders.', 'breeze' ); ?></span>
					</span>
				</li>
			</ul>
		</td>
	</tr>

	<tr>
		<td>
            <label class="breeze_tool_tip"><?php _e( 'Disable emoji', 'breeze' ); ?></label>
        </td>
        <td>
            <ul>
                <li>
                    <input type="checkbox" name="breeze-wpjs-emoji" id="breeze-wpjs-emoji" value="1" <?php if(isset($basic['breeze-wp-emoji'])){checked( $basic['breeze-wp-emoji'], '1' ); }?>/>
                    <label class="breeze_tool_tip" for="breeze-wpjs-emoji">
		                <?php _e( 'Disable the emoji library loaded by default in WrodPress to reduce HTTP requests.', 'breeze' ); ?>

                    </label>
                </li>
            </ul>
        </td>
	</tr>
</table>
