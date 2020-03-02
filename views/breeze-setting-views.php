<?php

defined('ABSPATH') or die;

$tabs = array(
    'basic' => __('BASIC OPTIONS', 'breeze'),
    'advanced' => __('ADVANCED OPTIONS', 'breeze'),
    'database' => __('DATABASE', 'breeze'),
    'cdn' => __('CDN', 'breeze'),
    'varnish' => __('VARNISH', 'breeze'),
    'faq' => __('FAQs', 'breeze'),
);

$global_tabs = array(
	'faq',
);

?>
<?php if (isset($_REQUEST['database-cleanup']) && $_REQUEST['database-cleanup'] == 'success'): ?>
    <div id="message-save-settings" class="notice notice-success is-dismissible" style="margin: 10px 0px 10px 0;padding: 10px;"><strong><?php _e('Database cleanup successful', 'breeze'); ?></strong></div>
<?php endif; ?>
<!--save settings successfull message-->
<?php if (isset($_REQUEST['save-settings']) && $_REQUEST['save-settings'] == 'success'): ?>
     <div id="message-save-settings" class="notice notice-success is-dismissible" style="margin: 10px 0px 10px 0;padding: 10px;"><strong><?php _e('Configuration settings saved', 'breeze'); ?></strong></div>
<?php endif; ?>
<div class="wrap breeze-main">
    <div class="breeze-header">
        <a  href="https://www.cloudways.com" target="_blank">
        <div class="breeze-logo"></div>
        </a>
    </div>

	<h1 style="clear: both"></h1>

	<?php

	$show_tabs  = true;
	$is_subsite = is_multisite() && get_current_screen()->base !== 'settings_page_breeze-network';

	if ( $is_subsite ) {
		// Show settings inherit option.
		$inherit_settings = get_option( 'breeze_inherit_settings', 1 );
		if ( 0 != $inherit_settings ) {
			$inherit_settings = 1;
			$show_tabs        = false;
		}
		?>
		<form id="breeze-inherit-settings-toggle" class="breeze-form" method="post" action="">
			<div class="radio-field<?php echo $inherit_settings == 1 ? ' active' : ''; ?>">
				<label>
					<input type="radio" id="inherit-settings" name="inherit-settings" value="1" <?php checked( $inherit_settings, 1 ); ?>>
					<strong><?php esc_html_e( 'Use Network Level Settings for this site', 'breeze' ); ?>:</strong>
				</label>
				<small><?php esc_html_e( 'This option allows the subsite to inherit all the cache settings from network. To modify/update the settings please go to network site.', 'breeze' ); ?></small>
			</div>
			<div class="radio-field<?php echo $inherit_settings == 0 ? ' active' : ''; ?>">
				<label>
					<input type="radio" id="inherit-settings" name="inherit-settings" value="0" <?php checked( $inherit_settings, 0 ); ?>>
					<strong><?php esc_html_e( 'Use Custom Settings for this site', 'breeze' ); ?>:</strong>
				</label>
				<small><?php esc_html_e( 'This option allows subsite to have different settings/configuration from the network level. Use this option only if you wish to have separate settings for this subsite.', 'breeze' ); ?></small>
			</div>

			<p class="disclaimer"><?php esc_html_e( 'To apply your changes, please click on the Save Changes button.', 'breeze' ); ?></p>

			<?php wp_nonce_field( 'breeze_inherit_settings', 'breeze_inherit_settings_nonce' ); ?>
		</form>

		<h1 style="clear: both"></h1>
		<?php
	}
	?>

	<ul id="breeze-tabs" class="nav-tab-wrapper <?php echo ! $show_tabs ? 'tabs-hidden' : ''; ?>">
		<?php
		foreach ( $tabs as $key => $name ) {
			$is_inactive = ! $show_tabs && ! in_array( $key, $global_tabs );
			echo '<a id="tab-' . $key . '" class="nav-tab' . ( $is_inactive ? ' inactive' : '' ) . '" href="#tab-' . $key . '" data-tab-id="' . $key . '"> ' . $name . ' </a> ';
		}
		?>
	</ul>

    <div id="breeze-tabs-content" class="tab-content <?php echo ! $show_tabs ? 'tabs-hidden' : ''; ?>">
        <?php
		foreach ( $tabs as $key => $name ) {
			$is_inactive = ! $show_tabs && ! in_array( $key, $global_tabs );
            echo '<div id="tab-content-' . $key . '" class="tab-pane' . ( $is_inactive ? ' inactive' : '' ) . '">';
            echo '<form class="breeze-form" method="post" action="">';
            echo '<div class="tab-child">';
            echo '<input type="hidden" name="breeze_'.$key.'_action" value="breeze_'.$key.'_settings">';
            wp_nonce_field('breeze_settings_' . $key, 'breeze_settings_' . $key . '_nonce');
            Breeze_Admin::render($key);
            echo '</div>';

			if (
				$key != 'faq' &&
				( $key != 'database' || ( is_multisite() && ! is_network_admin() ) )
			) {
				if ( is_multisite() && is_network_admin() ) {
					echo '<p class="multisite-inherit-disclaimer">' . __( '* Any change here will also be applied to all the sub-sites that are using Network level settings.', 'wpr' ) . '</p>';
				}
				echo '<p class="submit">' . PHP_EOL .
					'<input type="submit" class="button button-primary breeze-submit-btn" value="'. __('Save Changes', 'breeze') .'"/>' . PHP_EOL .
				'</p>';
			}
			if ( ! in_array( $key, $global_tabs ) ) {
				echo '<span class="hidden-text">' . esc_attr__( 'When Network Level Settings is selected, modifications/updates can only be done from the main Network site.', 'breeze' ) . '</span>';
			}
            echo '</form>';
            echo '</div>';
        }
        ?>

        <!--Right-side content-->
        <div id="breeze-and-cloudways" class="rs-block">
            <h3 class="rs-title"><?php _e('Want to Experience Better Performance?', 'breeze') ?></h3>
            <div class="rs-content">
                <p><?php _e('Take advantage of powerful features by deploying WordPress and Breeze on Cloudways.', 'breeze') ?></p>
                <ul>
                    <li><?php _e('Fully Compatible with Varnish', 'breeze') ?></li>
                    <li><?php _e('One-Click setup of CloudwaysCDN', 'breeze') ?></li>
                    <li><?php _e('24/7 Expert Human Support', 'breeze') ?></li>
                    <li><?php _e('WooCommerce Compatible', 'breeze') ?></li>
                </ul>
                <button class="button button-primary">
                    <a href="https://www.cloudways.com/en/wordpress-cloud-hosting.php?utm_source=breeze-plugin&utm_medium=breeze&utm_campaign=breeze" target="_blank"><?php _e('Find Out More', 'breeze') ?></a>
                </button>
            </div>
            <div class="rs-content">
                <h4><?php _e('Rate Breeze', 'breeze') ?></h4>
                <p><?php _e('If you are satisfied with Breeze\'s performance, <a href="https://wordpress.org/plugins/breeze#reviews" target="_blank">drop us a rating here.</a>', 'breeze') ?></p>
            </div>
        </div>
    </div>
</div>
