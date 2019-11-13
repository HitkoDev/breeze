<?php
defined( 'ABSPATH' ) or die;

$advanced = breeze_get_option( 'advanced_settings', true );

$excluded_css_check           = true;
$excluded_js_check            = true;
$excluded_css_check_extension = true;
$excluded_js_check_extension  = true;
if ( isset( $advanced['breeze-exclude-css'] ) && ! empty( $advanced['breeze-exclude-css'] ) ) {
	$excluded_css_check = breeze_validate_urls( $advanced['breeze-exclude-css'] );
	if ( true === $excluded_css_check ) {
		$excluded_css_check_extension = breeze_validate_the_right_extension( $advanced['breeze-exclude-css'], 'css' );
	}
}

if ( isset( $advanced['breeze-exclude-js'] ) && ! empty( $advanced['breeze-exclude-js'] ) ) {
	$excluded_js_check = breeze_validate_urls( $advanced['breeze-exclude-js'] );
	if ( true === $excluded_js_check ) {
		$excluded_js_check_extension = breeze_validate_the_right_extension( $advanced['breeze-exclude-js'], 'js' );
	}
}
?>
<table cellspacing="15">
	<tr>
		<td>
			<label for="exclude-urls" class="breeze_tool_tip"><?php _e( 'Never Cache these URLs', 'breeze' ); ?></label>
		</td>
		<td>
			<?php
			$css_output = '';
			if ( ! empty( $advanced['breeze-exclude-urls'] ) ) {
				$output     = implode( "\n", $advanced['breeze-exclude-urls'] );
				$css_output = esc_textarea( $output );
			}
			?>
			<textarea cols="100" rows="7" id="exclude-urls" name="exclude-urls"><?php echo $css_output; ?></textarea>
			<br/>
			<span class="breeze_tool_tip"><b>Note:&nbsp;</b><?php _e( 'Add the URLs of the pages (one per line) you wish to exclude from the WordPress internal cache. To exclude URLs from the Varnish cache, please refer to this ', 'breeze' ); ?><a
						href="https://support.cloudways.com/how-to-exclude-url-from-varnish/"
						target="_blank"><?php _e( 'Knowledge Base', 'breeze' ); ?></a><?php _e( ' article.', 'breeze' ); ?> </span>
		</td>
	</tr>
	<tr>
		<td>
			<label class="breeze_tool_tip"><?php _e( 'Group Files', 'breeze' ); ?></label>
		</td>
		<td>
			<ul>
				<li>
					<input type="checkbox" name="group-css" id="group-css"
						   value="1" <?php checked( $advanced['breeze-group-css'], '1' ); ?>/>
					<label class="breeze_tool_tip" for="group-css"><?php _e( 'CSS', 'breeze' ); ?></label>
				</li>
				<li>
					<input type="checkbox" name="group-js" id="group-js"
						   value="1" <?php checked( $advanced['breeze-group-js'], '1' ); ?>/>
					<label class="breeze_tool_tip" for="group-js"><?php _e( 'JS', 'breeze' ); ?></label>
				</li>
				<li>
					<span class="breeze_tool_tip">
						<b>Note:&nbsp;</b><?php _e( 'Group CSS and JS files to combine them into a single file. This will reduce the number of HTTP requests to your server.', 'breeze' ); ?><br>
						<b><?php _e( 'Important: Enable Minification to use this option.', 'breeze' ); ?></b>
					</span>
				</li>
			</ul>
		</td>
	</tr>
	<tr>
		<td>
			<label for="exclude-css" class="breeze_tool_tip"><?php _e( 'Exclude CSS', 'breeze' ); ?></label>
		</td>
		<td>
			<?php
			$css_output = '';
			if ( ! empty( $advanced['breeze-exclude-css'] ) ) {
				$output     = implode( "\n", $advanced['breeze-exclude-css'] );
				$css_output = esc_textarea( $output );
			}
			?>
			<textarea cols="100" rows="7" id="exclude-css" name="exclude-css"><?php echo $css_output; ?></textarea>
			<?php if ( false === $excluded_css_check_extension ) { ?>
				<br/><span class="breeze_tool_tip"
						   style="color: #ff0000"><?php _e( 'One (or more) URL is incorrect. Please confirm that all URLs have the .css extension', 'breeze' ); ?></span>
			<?php } ?>
			<?php if ( false === $excluded_css_check ) { ?>
				<br/><span class="breeze_tool_tip" style="color: #ff0000"><?php _e( 'One (or more) URL is invalid. Please check and correct the entry.', 'breeze' ); ?></span>
			<?php } ?>
			<br/>
			<span class="breeze_tool_tip"><b>Note:&nbsp;</b><?php _e( 'Use this option to exclude CSS files from Minification and Grouping. Enter the URLs of CSS files on each line.', 'breeze' ); ?></span>
		</td>
	</tr>
	<tr>
		<td>
			<label for="exclude-js" class="breeze_tool_tip"><?php _e( 'Exclude JS', 'breeze' ); ?></label>
		</td>
		<td>
			<?php
			$js_output = '';
			if ( ! empty( $advanced['breeze-exclude-js'] ) ) {
				$output    = implode( "\n", $advanced['breeze-exclude-js'] );
				$js_output = esc_textarea( $output );
			}
			?>
			<textarea cols="100" rows="7" id="exclude-js" name="exclude-js"><?php echo $js_output; ?></textarea>
			<?php if ( false === $excluded_js_check_extension ) { ?>
				<br/><span class="breeze_tool_tip"
						   style="color: #ff0000"><?php _e( 'One (or more) URL is incorrect. Please confirm that all URLs have the .js extension', 'breeze' ); ?></span>
			<?php } ?>
			<?php if ( false === $excluded_js_check ) { ?>
				<br/><span class="breeze_tool_tip" style="color: #ff0000"><?php _e( 'One (or more) URL is invalid. Please check and correct the entry.', 'breeze' ); ?></span>
			<?php } ?>
			<br/>
			<span class="breeze_tool_tip"><b>Note:&nbsp;</b><?php _e( 'Use this option to exclude JS files from Minification and Grouping. Enter the URLs of JS files on each line.', 'breeze' ); ?></span>
		</td>
	</tr>
	<tr>
		<td>
			<label for="move-to-footer-js" class="breeze_tool_tip"><?php _e( 'Move JS files to footer', 'breeze' ); ?></label>
		</td>
		<td>
			<div class="breeze-list-url">
				<?php if ( ! empty( $advanced['breeze-move-to-footer-js'] ) ) : ?>
					<?php foreach ( $advanced['breeze-move-to-footer-js'] as $js_url ) : ?>
						<div class="breeze-input-group">
					<span class="sort-handle">
						<span class="dashicons dashicons-arrow-up moveUp"></span>
						<span class="dashicons dashicons-arrow-down moveDown"></span>
					</span>
							<input type="text" size="98"
								   class="breeze-input-url"
								   name="move-to-footer-js[]"
								   placeholder="<?php _e( 'Enter URL...', 'breeze' ); ?>"
								   value="<?php echo esc_html( $js_url ); ?>"/>
							<span class="dashicons dashicons-no item-remove" title="<?php _e( 'Remove', 'breeze' ); ?>"></span>
						</div>
					<?php endforeach; ?>
				<?php else : ?>
					<div class="breeze-input-group">
					<span class="sort-handle">
						<span class="dashicons dashicons-arrow-up moveUp"></span>
						<span class="dashicons dashicons-arrow-down moveDown"></span>
					</span>
						<input type="text" size="98"
							   class="breeze-input-url"
							   id="move-to-footer-js"
							   name="move-to-footer-js[]"
							   placeholder="<?php _e( 'Enter URL...', 'breeze' ); ?>"
							   value=""/>
						<span class="dashicons dashicons-no" title="<?php _e( 'Remove', 'breeze' ); ?>"></span>
					</div>
				<?php endif; ?>
			</div>
			<div style="margin: 10px 0">
				<button type="button" class="button add-url" id="add-move-to-footer-js">
					<?php _e( 'Add URL', 'breeze' ); ?>
				</button>
			</div>
			<div>
				<span class="breeze_tool_tip">
					<b>Note:&nbsp;</b>
					<?php _e( 'Enter the complete URLs of JS files to be moved to the footer during minification process.', 'breeze' ); ?>
				</span>
				<span class="breeze_tool_tip">
					<?php _e( 'You should add the URL of original files as URL of minified files are not supported.', 'breeze' ); ?>
				</span>
			</div>
		</td>
	</tr>
	<tr>
		<td>
			<label for="defer-js" class="breeze_tool_tip"><?php _e( 'JS files with deferred loading', 'breeze' ); ?></label>
		</td>
		<td>
			<div class="breeze-list-url">
				<?php if ( ! empty( $advanced['breeze-defer-js'] ) ) : ?>
					<?php foreach ( $advanced['breeze-defer-js'] as $js_url ) : ?>
						<div class="breeze-input-group">
							<span class="sort-handle">
								<span class="dashicons dashicons-arrow-up moveUp"></span>
								<span class="dashicons dashicons-arrow-down moveDown"></span>
							</span>
							<input type="text" size="98"
								   class="breeze-input-url"
								   name="defer-js[]"
								   placeholder="<?php _e( 'Enter URL...', 'breeze' ); ?>"
								   value="<?php echo esc_html( $js_url ); ?>"/>
							<span class="dashicons dashicons-no item-remove" title="<?php _e( 'Remove', 'breeze' ); ?>"></span>
						</div>
					<?php endforeach; ?>
				<?php else : ?>
					<div class="breeze-input-group">
						<span class="sort-handle">
							<span class="dashicons dashicons-arrow-up moveUp"></span>
							<span class="dashicons dashicons-arrow-down moveDown"></span>
						</span>
						<input type="text" size="98"
							   class="breeze-input-url"
							   name="defer-js[]"
							   id="defer-js"
							   placeholder="<?php _e( 'Enter URL...', 'breeze' ); ?>"
							   value=""/>
						<span class="dashicons dashicons-no item-remove" title="<?php _e( 'Remove', 'breeze' ); ?>"></span>
					</div>
				<?php endif; ?>
			</div>
			<div style="margin: 10px 0">
				<button type="button" class="button add-url" id="add-defer-js">
					<?php _e( 'Add URL', 'breeze' ); ?>
				</button>
			</div>
			<div>
				<span class="breeze_tool_tip">
					<b>Note:&nbsp;</b>
					<?php _e( 'You should add the URL of original files as URL of minified files are not supported.', 'breeze' ); ?>
				</span>
			</div>
		</td>
	</tr>
</table>
