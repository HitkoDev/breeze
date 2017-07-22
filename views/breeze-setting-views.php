<?php
$tabs = array(
    'basic' => __('Basic options', 'breeze'),
    'advanced' => __('Advanced options', 'breeze'),
    'database' => __('Database', 'breeze'),
    'cdn' => __('CDN', 'breeze'),
    'varnish' => __('Varnish', 'breeze'),
);
?>
<?php if (isset($_REQUEST['database-cleanup']) && $_REQUEST['database-cleanup'] == 'success'): ?>
    <div id="message-save-settings" class="notice notice-success" style="margin: 10px 0px 10px 0;padding: 10px;"><strong><?php _e('Database cleanup successful', 'breeze'); ?></strong></div>
<?php endif; ?>
<!--save settings successfull message-->
<?php if (isset($_REQUEST['save-settings']) && $_REQUEST['save-settings'] == 'success'): ?>
     <div id="message-save-settings" class="notice notice-success" style="margin: 10px 0px 10px 0;padding: 10px;"><strong><?php _e('Configuration settings saved', 'breeze'); ?></strong></div>
<?php endif; ?>
<div class="wrap breeze-main">
    <div class="breeze-header" style="display: none"><a  href="https://www.cloudways.com" target="_blank">
        <div class="breeze-logo"></div>
        <label class="breeze-logo-title"><?php _e('CLOUDWAYS', 'breeze'); ?></label>
        </a>
    </div>
    <div style="clear: both"></div>
    <h1 style="padding: 0;font-size: 30px"><?php _e("Breeze - WordPress Cache Settings", 'breeze'); ?></h1>

    <ul id="breeze-tabs" class="nav-tab-wrapper">
        <?php
        foreach ($tabs as $key => $name) {
            echo '<a id="tab-' . $key . '" class="nav-tab" href="#tab-' . $key . '" data-tab-id="' . $key . '"> ' . $name . ' </a> ';
        }
        ?>
    </ul>

    <div id="breeze-tabs-content" class="tab-content">
        <?php
        foreach ($tabs as $key => $name) {
            echo '<div id="tab-content-' . $key . '" class="tab-pane">';
            echo '<form class="breeze-form" method="post" action="">';
            echo '<div class="tab-child">';
            echo '<input type="hidden" name="breeze_'.$key.'_action" value="breeze_'.$key.'_settings">';
            wp_nonce_field('breeze_settings_' . $key, 'breeze_settings_' . $key . '_nonce');
            Breeze_Admin::render($key);
            echo '</div>';
            if ($key == 'database'){
                echo '<p class="submit">
                 <input type="submit" class="button button-primary" value="Optimize"/>
                     </p>';
            }else{
                echo '<p class="submit">
                 <input type="submit" class="button button-primary" value="Save Changes"/>
                     </p>';
            }
            echo '</form>';
            echo '</div>';

        }
        ?>
    </div>
</div>