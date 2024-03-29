<?php
defined('ABSPATH') or exit;

$varnish = breeze_get_option('varnish_cache', true);
$check_varnish = is_varnish_cache_started();

if (!isset($varnish['auto-purge-varnish'])) {
    $varnish['auto-purge-varnish'] = '0';
}
?>
<div class="breeze-top-notice">
    <p class="breeze_tool_tip"><?php _e('By default Varnish is enabled on all WordPress websites hosted on Cloudways.', 'breeze'); ?>
    </p>
</div>
<table cellspacing="15">
    <tr>
        <td>
            <label for="auto-purge-varnish"
                class="breeze_tool_tip"><?php _e('Auto Purge Varnish', 'breeze'); ?></label>
        </td>
        <td>
            <input type="checkbox"
                id="auto-purge-varnish"
                name="auto-purge-varnish"
                value="1"
                <?php checked($varnish['auto-purge-varnish'], '1'); ?>/>
            <span class="breeze_tool_tip"><?php _e('Keep this option enabled to automatically purge Varnish cache on actions like publishing new blog posts, pages and comments.', 'breeze'); ?></span>
            <br>
            <?php if (!$check_varnish) { ?>
            <span><b>Note:&nbsp;</b>
                <span style="color: #ff0000"><?php _e('Seems Varnish is disabled on your Application. Please refer to ', 'breeze'); ?><a href="https://support.cloudways.com/most-common-varnish-issues-and-queries/"
                        target="_blank"><?php _e('this KB', 'breeze'); ?></a><?php _e(' and learn how to enable it.', 'breeze'); ?> </span>
            </span>
            <?php } ?>
        </td>
    </tr>
    <tr>
        <td>
            <label for="varnish-server-ip"
                class="breeze_tool_tip"><?php _e('Varnish server', 'breeze'); ?></label>
        </td>
        <td>
            <input type="text"
                id="varnish-server-ip"
                size="20"
                name="varnish-server-ip"
                value='<?php echo !empty($varnish['breeze-varnish-server-ip']) ? esc_html($varnish['breeze-varnish-server-ip']) : '127.0.0.1'; ?>' />
            <br />
            <span class="breeze_tool_tip"><strong><?php _e('Note: Keep this default if you are a Cloudways customer. Otherwise ask your hosting provider on what to set here.', 'breeze'); ?></strong></span>
        </td>
    </tr>
    <tr>
        <td style="vertical-align: middle">
            <label class="breeze_tool_tip"><?php _e('Purge Varnish Cache', 'breeze'); ?></label>
        </td>
        <td>
            <input type="button"
                id="purge-varnish-button"
                class="button"
                value="<?php _e('Purge', 'breeze'); ?>" />
            <span style="vertical-align: bottom; margin-left: 5px"><?php _e('Use this option to instantly Purge Varnish Cache on entire website. ', 'breeze'); ?></span>
        </td>
    </tr>
</table>
