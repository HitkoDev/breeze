<?php
defined( 'ABSPATH' ) or die;

$advanced = breeze_get_option( 'advanced_settings', true );

$excluded_css_check           = true;
$excluded_js_check            = true;
$excluded_css_check_extension = true;
$excluded_js_check_extension  = true;
$excluded_url_list            = true;
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

if ( isset( $advanced['breeze-exclude-urls'] ) && ! empty( $advanced['breeze-exclude-urls'] ) ) {
	$excluded_url_list = breeze_validate_urls( $advanced['breeze-exclude-urls'] );
}

if ( ! isset( $advanced['breeze-preload-links'] ) ) {
	$advanced['breeze-preload-links'] = '0';
}

if ( ! isset( $advanced['breeze-lazy-load'] ) ) {
	$advanced['breeze-lazy-load'] = '0';
}

if ( ! isset( $advanced['breeze-lazy-load-native'] ) ) {
	$advanced['breeze-lazy-load-native'] = '0';
}

if ( ! isset( $advanced['breeze-group-css'] ) ) {
	$advanced['breeze-group-css'] = '0';
}

if ( ! isset( $advanced['breeze-group-js'] ) ) {
	$advanced['breeze-group-js'] = '0';
}

if ( ! isset( $advanced['breeze-enable-js-delay'] ) ) {
	$advanced['breeze-enable-js-delay'] = '0';
}

$js_inline_enable = filter_var( $advanced['breeze-enable-js-delay'], FILTER_VALIDATE_BOOLEAN );
?>
<table cellspacing="15" id="advanced-options-tab">
	<tr>
		<td><label for="bz-lazy-load" class="breeze_tool_tip"><?php _e( 'Lazy-Load images', 'breeze' ); ?></label></td>
		<td>
			<?php
			$disabled = 'disabled';

			if ( class_exists( 'DOMDocument' ) && class_exists( 'DOMXPath' ) ) {
				$disabled = '';
			}
			?>
			<input type="checkbox" id="bz-lazy-load" name="bz-lazy-load"
				   value='1' <?php checked( $advanced['breeze-lazy-load'], '1' ); ?> <?php echo $disabled; ?>/>
			<span class="breeze_tool_tip">
				<?php _e( 'Images will begin to load before being displayed on screen.', 'breeze' ); ?>
			</span>
			<?php
			if ( ! empty( $disabled ) ) {
				?>
				<br/>
				<span class="breeze_tool_tip" style="color: #ff0000">
					<?php _e( 'This option requires the library PHP DOMDocument and PHP DOMXPath', 'breeze' ); ?>
				</span>
				<br/>
				<?php
			} else {
				echo '<br/>';
			}


			$is_checked_lazy = checked( $advanced['breeze-lazy-load'], '1', false );
			if ( ! empty( $is_checked_lazy ) ) {
				if ( ! empty( $disabled ) ) {
					$hide = ' style="display:none"';
				} else {
					$hide = '';
				}
			} else {
				$hide = ' style="display:none"';
			}

			?>
			<br/>
			<span <?php echo $hide; ?> id="native-lazy-option">
			<input type="checkbox" id="bz-lazy-load-nat" name="bz-lazy-load-nat"
				   value='1' <?php checked( $advanced['breeze-lazy-load-native'], '1' ); ?>/>
				<span class="breeze_tool_tip">
					<strong><?php _e( 'Enable native browser lazy load', 'breeze' ); ?></strong><br/>
					<?php _e( '<strong>Note</strong>: This is not supported by all browsers.', 'breeze' ); ?>
				</span>
			</span>
		</td>
	</tr>
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
			<?php if ( false === $excluded_url_list ) { ?>
				<br/>
				<span class="breeze_tool_tip" style="color: #ff0000">
					<?php _e( 'One (or more) URL is invalid. Please check and correct the entry.', 'breeze' ); ?>
				</span>
			<?php } ?>
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
			<label class="breeze_tool_tip"><?php _e( 'Preload links', 'breeze' ); ?></label>
		</td>
		<td>
			<input type="checkbox" name="preload-links" id="preload-links"
				   value="1" <?php checked( $advanced['breeze-preload-links'], '1' ); ?>/>
			<label class="breeze_tool_tip" for="preload-links"><?php _e( 'Activate preload links feature', 'breeze' ); ?></label>
			<br/>
			<span class="breeze_tool_tip">
						<b>Note:&nbsp;</b><?php _e( 'When users hover over links, the cache is created in advance. The page will load faster upon link visiting.', 'breeze' ); ?><br/>
						<b><?php _e( 'Important: This feature is supported by Chromium based browsers (Chrome, Opera, Microsoft Edge Chromium, Brave...)', 'breeze' ); ?>;</b>
					</span>
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
			<label for="breeze-preload-font" class="breeze_tool_tip"><?php _e( 'Preload your webfonts', 'breeze' ); ?></label>
		</td>
		<td>
			<div class="breeze-list-url">
				<?php if ( ! empty( $advanced['breeze-preload-fonts'] ) ) : ?>
					<?php foreach ( $advanced['breeze-preload-fonts'] as $font_url ) : ?>
						<div class="breeze-input-group">
					<span class="sort-handle">
						<span class="dashicons dashicons-arrow-up moveUp"></span>
						<span class="dashicons dashicons-arrow-down moveDown"></span>
					</span>
							<input type="text" size="98"
								   class="breeze-input-url"
								   name="breeze-preload-font[]"
								   placeholder="<?php _e( 'Enter Font/CSS URL...', 'breeze' ); ?>"
								   value="<?php echo esc_html( $font_url ); ?>"/>
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
							   id="breeze-preload-font"
							   name="breeze-preload-font[]"
							   placeholder="<?php _e( 'Enter Font/CSS URL...', 'breeze' ); ?>"
							   value=""/>
						<span class="dashicons dashicons-no" title="<?php _e( 'Remove', 'breeze' ); ?>"></span>
					</div>
				<?php endif; ?>
			</div>
			<div style="margin: 10px 0">
				<button type="button" class="button add-url" id="add-breeze-preload-fonts">
					<?php _e( 'Add URL', 'breeze' ); ?>
				</button>
			</div>
			<div>
				<span class="breeze_tool_tip">
					<b>Note:&nbsp;</b>
					<?php _e( 'Specify the local font URL or the URL for the CSS file which loads only fonts.', 'breeze' ); ?>
				</span>
				<span class="breeze_tool_tip">
					<?php _e( 'Load WOFF format fonts for the best performance.', 'breeze' ); ?>
				</span>
				<span class="breeze_tool_tip">
					<?php _e( 'Do not preload the whole website CSS file as it will slow down your website.', 'breeze' ); ?>
				</span>
				<span class="breeze_tool_tip">
					<?php _e( 'Do not add Google Fonts links as those already use preload.', 'breeze' ); ?>
				</span>
				<br/>
				<span class="breeze_tool_tip">
					<?php $theme_url = get_template_directory_uri() . '/assets/fonts/my-font.woff'; ?>
					<?php _e( 'Example:<code>' . $theme_url . '</code>', 'breeze' ); ?>
				</span>
			</div>
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
	<tr>
		<td>
			<label for="delay-js-scripts" class="breeze_tool_tip"><?php _e( 'Delay JS inline scripts', 'breeze' ); ?></label>
		</td>
		<td>
			<input type="checkbox" name="enable-js-delay" id="enable-js-delay"
				   value="1" <?php checked( $advanced['breeze-enable-js-delay'], '1' ); ?>/>
			<label class="breeze_tool_tip" for="enable-js-delay"><?php _e( 'Enable delay inline JavaScript', 'breeze' ); ?></label>
			<br/>
			<br/>
			<?php
			$js_output = '';
			if ( ! empty( $advanced['breeze-delay-js-scripts'] ) ) {
				$output    = implode( "\n", $advanced['breeze-delay-js-scripts'] );
				$js_output = esc_textarea( $output );
			}

			$display_text_area = 'style="display:none"';
			if ( true === $js_inline_enable ) {
				$display_text_area = 'style="display:block"';
			}
			?>
			<div <?php echo $display_text_area; ?> id="breeze-delay-js-scripts-div">
				<textarea cols="100" rows="7" id="delay-js-scripts" name="delay-js-scripts"><?php echo $js_output; ?></textarea>
				<br/>
				<span class="breeze_tool_tip">
					<strong>Notes:&nbsp;</strong> <br/>
					<?php _e( 'You can add specififc keywords to identify the inline JavaScript to be delayed. Each script identifying keyword must be added on a new line.', 'breeze' ); ?>
					<a href="https://www.cloudways.com/blog/breeze-1-2-version-released/" target="_blank"><?php _e( 'More info here', 'breeze' ); ?></a><br/>
					<span style="color: #ff0000">
						 <?php _e( 'Please clear Varnish after applying the new settings.', 'breeze' ); ?>
					</span>
				</span>
			</div>
        </td>
    </tr>
    <tr>
        <td>
            <label for="cache-query-str" class="breeze_tool_tip"><?php _e( 'Cache query strings', 'breeze' ); ?></label>
        </td>
        <td>
			<?php
			$cached_query_strings = '';
			if ( isset( $advanced['cached-query-strings'] ) && ! empty( $advanced['cached-query-strings'] ) ) {
				$output               = implode( "\n", $advanced['cached-query-strings'] );
				$cached_query_strings = esc_textarea( $output );
			}

			?>
            <textarea cols="100" rows="7" id="cache-query-str" name="cache-query-str"><?php echo $cached_query_strings; ?></textarea>
            <br/>
            <span class="breeze_tool_tip">
                    <strong>Note:&nbsp;</strong> <br/>
                    <span>
                         <?php _e( 'Pages that contain the query strings added here, will be cached. Each entry must be added in a new line.', 'breeze' ); ?>
                    </span>
                </span>

		</td>
	</tr>
</table>
