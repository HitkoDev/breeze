=== Breeze - WordPress Cache Plugin ===
Contributors: Cloudways
Tags: cache,caching, performance, wp-cache, cdn, combine, compress, speed plugin, database cache,gzip, http compression, js cache, minify, optimize, page cache, performance, speed, expire headers
Requires at least: 4.5
Tested up to: 5.8
Stable tag: 1.2.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Breeze is a WordPress Caching Plugin developed by Cloudways. Breeze uses advance caching systems to improve WordPress loading times exponentially.

== Description ==

Breeze is a free, simple (yet powerful) and user-friendly WordPress Caching Plugin developed by the Cloudways team. It offers various options to optimize WordPress performance at various levels. It works equally great with WordPress, WordPress with WooCommerce and WordPress Multisite.

Breeze excels in the following areas:

* **Performance:** Breeze improves website speed and resource optimization. Other features include file level cache system, database cleanup, minification, support for Varnish cache and simplified CDN integration options.

* **Convenience:** Breeze is easy to install and configure directly from WordPress. Configuring Breeze is easy and most of the default options work well right out of the box. The recommended settings should work on all your WordPress websites seamlessly.

* **Simplicity:** Breeze is designed to be simple for all users. Just install and activate the plugin and you'll see the results instantaneously.

What makes Breeze WordPress Cache Plugin awesome is that it comes with builtin support for Varnish. If Varnish is not installed on your servers, Breeze will utilize its internal cache mechanism to boost up your WordPress site performance.

**Support:** We love to provide support! Post your questions on the WordPress.org support forums, or if you are a Cloudways Customer you may ask questions on the <a href="https://community.cloudways.com/">Cloudways Community Forum</a>.

**Special Thanks:** We would like to give special mention to WP Speed Of Light for being an inspiration for Breeze.

== Installation ==

= To install the plugin via WordPress Dashboard: =
* In the WordPress admin panel, navigate to Plugin > Add new
* Search for Breeze
* Click install and wait for the installation to finish. Next, click the activate link

= To install the plugin manually: =
* Download and unzip the plugin package - breeze.1.0.0.zip
* Upload the breeze to /wp-content/plugins/
* Activate the plugin through the 'Plugins' menu in WordPress
* Access Breeze from WordPress Admin > Settings > Breeze

== Frequently Asked Questions ==

= Installation Instructions

To install the plugin via WordPress Dashboard
1. In the WordPress admin panel, Menu > Plugin > Add new
2. Search for Breeze
3. Click on install and wait for the installation to finish. Next, then click on the activate link

To install the plugin manually
1. Download and unzip the plugin package - breeze.1.0.0.zip
2. Upload the /breeze to /wp-content/plugins/
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Access Breeze from WordPress Admin > Settings > Breeze

= Does Breeze support Varnish and to what extent? =

Breeze, by default, supports Varnish. It has been tested to be fully compatible with Cloudways Servers that come with Varnish pre-installed. If you are using hosting providers other than Cloudways, we suggest you confirm Varnish support with your hosting provider

= Does Breeze support WooCommerce? =

Breeze is fully compatible with WooCommerce, out of the box. It does not require any special configurations.

= Does Breeze support WordPress Multisite? =

Breeze is fully compatible with WordPress Multisite without the need for any extra configuration.

= How does Breeze handle WordPress multisite? =

Breeze handles all WordPress multisite instances globally. All the settings for multisite are now handled on the network level.

= Is Breeze compatible with other WordPress Cache plugins? =

We DO NOT recommend using two WordPress cache plugins at the same time on any WordPress website.
We strongly recommend that you use Breeze as the only cache plugin for your website. If there are any other cache plugins installed, please ensure that you have disabled them prior to proceeding with the Breeze installation.


= Is Breeze compatible with HTTPS? =

Breeze does not require any special configuration to work with HTTP or HTTPS pages.

= Does Breeze have compatibility issues with other known plugins? =

Breeze has been tested with popular plugins available on WordPress.org. Please feel free to report any incompatibilities on the WordPress Support Forums or on <a href="https://community.cloudways.com/">Cloudways Community Forum</a>.

= Does Breeze support CDN? =

Breeze supports CDN integration. It allows all static assets (such as images, CSS and JS files) to be served via CDN.

= What does Breeze's Database Optimization feature do? =

WordPress databases are notorious for storing information like post revisions, spam comments and much more. Over time, databases l become bloated and it is a good practice to clear out unwanted information to reduce database size and improve optimization.

Breeze's database optimization cleans out unwanted information in a single click.

= Will comments and other dynamic parts of my blog appear immediately? =

Comments will appear upon moderation as per the comment system (or policy) set in place by the blog owner. Other dynamic changes such as any modifications in files will require a full cache purge.

= Can I exclude URLs of individual files and pages from cache? =

You can exclude a file by mentioning its URL or file type (by mentioning file extension) in the exclude fields (available in the Breeze settings). Exclude will not let the cache impact that URL or file type.

If Varnish is active, you will need to exclude URLs and file type(s) in the Varnish configuration. If you are hosting WordPress websites on Cloudways servers, follow <a href="https://support.cloudways.com/how-to-exclude-url-from-varnish/">this KB to exclude URLs from the Varnish cache</a>.

= Does it work with all hosting providers? =

Breeze has been tested to work with all major hosting providers. In addition, major Breeze options such as Gzip, browser cache, minification, grouping, database optimization. CDN integration will work as expected on all hosting providers.

= Where can I get support for Breeze? =

You can get your questions answered on the WordPress support forums. If you are a Cloudways customer, please feel free to start a discussion at <a href="https://community.cloudways.com/">Cloudways Community Forum</a>.

= How can I test and verify the results? =

You will be able to see the impact of the Breeze Cache Plugin almost immediately. We also recommend using the following tools for generating metrics:
<a href="https://developers.google.com/speed/pagespeed/" target="_blank">Google Page Speed</a>
<a href="https://www.webpagetest.org/test" target="_blank">WebPagetest</a>
<a href="https://tools.pingdom.com/" target="_blank">Pingdom</a>

= Does Breeze plugin work with Visual Builder? =

Yes, Breeze Plugin is compatible with Visual Builder.

= What popular CDN are supported by Breeze Plugin? =

Breeze supports the following three popular CDNs:
<a href="https://support.cloudways.com/how-to-use-breeze-with-maxcdn/" target="_blank">MaxCDN</a>
<a href="https://support.cloudways.com/how-to-use-breeze-with-keycdn/" target="_blank">KeyCDN</a>
<a href="https://support.cloudways.com/how-to-use-breeze-with-amazon-cloudfront/" target="_blank">Amazon Cloudfront</a>

= Does Breeze support Push CDN? =

No, Breeze does not support Push CDN. However, you could use Breeze with Push CDNs using third party plugins.

= Does Breeze Work With CloudFlare? =

Yes. The process of setting up CloudFlare with Breeze is easy. Check out the following <a href="https://support.cloudways.com/can-i-use-cloudflare-cdn/" target="_blank">KnowledgeBase article</a> for details.

= How Breeze cache uses Gzip? =

Using Gzip, Breeze compresses the request files, further reducing the size of the download files and speeding up the user experience.

== Changelog ==

= 1.2.6 =

 * Add: Varnish cache will be clear while plugin deactivate.
 * Add: Enable cache for specific user role.
 * Add: Disable Emojis to reduce request
 * Add: Delete breeze options values from database on plugin deletion.
 * Fix: Compatibility issue of map short codes with GeoDirectory plugin.
 * Fix: Compatibility issue with Ad Inserter plugin.
 * Fix: Compatibility issue of minification  with Beaver Builder Plugin.
 * Fix: Compatibility issue of JS scripts with AMP Plugin.
 * Fix: Reduce cookie life time span while user posts a comment.
 * Fix: HTML elements filtered from RestAPI end point of lazy-load scripts.
 * Fix: Config file of each subsite save with appropriate ID in advance cache file.
 * Fix: Google Analytics script exclude from Minification.


= 1.2.5 =


 * Add: URLs containing query strings will not be cached by default.
 * Add: Ignore specific query strings while serving the cache to improve performance.
 * Add: Ability to cache URLs with specific query strings variables.
 * Add: Cache handling of URLs having multiple parameters in one query string.
 * Add: Exceptional Cache handling for case where permalink is set to PLAIN, which includes links for POST, PAGES, ATTACHMENTS, CATEGORIES, ARCHIVES.


= 1.2.4 =


 * Add: Functionality to clear ALL cache via Command Line Interface (wp-cli).
 * Add: Functionality to clear Varnish cache via Command Line Interface (wp-cli).
 * Add: Functionality to clear Internal cache via Command Line Interface (wp-cli).
 * Add: While the file Permission is not correct, the warning message has been added.
 * Fix: Compatibility with Coming Soon Page, Maintenance Mode & Landing Pages by SeedProd.
 * Fix: improve the handling of warning undefine array key of delay JS script while enable debug mode.



= 1.2.3 =


 * Add: Addition in Media assets rules for browser cacheable objects.
 * Add: Addition in Font assets rules for browser cacheable object.
 * Add: Addition in Data interchange rules for browser cacheable objects.
 * Add: Addition in Manifest files rules for browser cacheable object.
 * Add: Addition in Gzip compression rules.
 * Fix: Improvise the handling of the Request header while the varnish is disable
 * Fix: Improvise the condition of Option "Enable cache for logged-in users"
 


= 1.2.2 =

 * Add: Export settings via Command Line Interface (wp-cli).
 * Add: Import settings via Command Line Interface(wp-cli).


= 1.2.1 =

 * Fix: improve the handling of warning undefine index of lazy load image while enable debug mode.
 * Add: Enable/Disable option of Delay JS inline scripts.

= 1.2.0 =

 * Add: “noreferrer noopener” attributes tag on external links when process HTML for caching.
 * Add: Preload fonts allow to text remain visible during webfont load.
 * Add: Preload key request of fonts OR CSS file which load fonts from local resource.
 * Add: Preload links allow to enable preload next page of application.
 * Add: lazy load display images on a page only when they are visible to the user.
 * Add: Minimize the impact of third-party code.

= 1.1.11 =

* Fix: Improved handling of forms using nonce in  Permalinks and Options pages.

= 1.1.10 =

* Fix: Apply deferred loading at individual file.
* Fix: exclude feed url generated by plugin “Facebook for WooCommerce”.
* Fix: purge site cache in subfolder.
* Fix: Inventory stock now updated at the Cart page.
* Fix: Improved Support for the new version of the WooCommerce Booking Calendar plugin.
* Add: Compatible with EDD and cartflow plugins.
* Add: pages include shortcode has been exclude by Breeze.

= 1.1.9 =

Add: Improved handling of minification for Query stirng based exclusion in Never Cache These URLs option.
Add: Increase compatibility with Multilingual .


= 1.1.8 =
* Fix: Cache refresh issue when Varnish is disabled.
* Fix: Replaced functions deprecated in WordPress 5.5 that were causing warning messages.
* Fix: Replaced deprecated minification libraries to improve compatibility with PHP 7.x onward.
* Fix: resolved the warning generated by the Query Monitor plugin.
* Add: compatibility with PHP 7.4

= 1.1.7 =
* Fix: Add HTTP and HTTPS for validation of CDN integration.
* Fix: Custom settings for multisite will be reapplied after Breeze reactivation.
* Fix: General improvements to improve support for the WooCommerce Booking Calendar plugin.
* Fix: Improved handling of minification for Wildcard based exclusion in Never Cache These URLs option.


= 1.1.6 =
* Add: Wildcard (.*) based exclusion of pattern URL strings in Never Cache These URLs option.
* Fix: Improved validation for CDN integration.
* Fix: General improvements to support for Elementor Forms/Elementor Pro and CDN integration.

= 1.1.5 =
* Fix: Revised duration for browser cacheable objects

= 1.1.4 =
* Fix: PHP Fatal error while running commands through WP_CLI

= 1.1.3 =
* Fix: Undefine error for inline JS when JS Group file is enabled.
* Fix: Several files displayed when Group Files was enabled.
* Fix: Varnish auto purge slowed down admin area while varnish is not running.
* Fix: PDF files are not downloadable with CDN enabled.
* Fix: miscellaneous UI issues.
* Add: The Google Analytics script/tag is now excluded form Minification.
* Add: Option to enable cache for admin user.
* Add: Handling of  404 error of JS/CSS/HTML when cache files are not writeable.
* Add: Exclude @import directive from CSS Minification.


= 1.1.2 =
* Fix: Improved handling of exclusion of CSS and JS while Minification and Group Files options are enabled.
* Fix: Allow wildcard (.*) based exclusion of pattern files/URIs in exclude JS and exclude CSS fields.
* Fix: Increase the duration for leverage browser cacheable objects

= 1.1.1 =
* Fix: Removed the use of remote JS. Now uses built-in version of jQuery Libraries.

= 1.1.0 =
* Add: Optional separate cache settings for subsites.

= 1.0.13 =
* Fix: Validation of nonce.
* Fix: Remove duplication of calls in Varnish purge requests.

= 1.0.12 =
* Fix: Deprecated create_function

= 1.0.11 =
* Fix: Change wp_redirect to wp_safe_redirect to fix redirect vulnerability of URL

= 1.0.10 =
* Add: Allow Purge Cache for Editors role.

= 1.0.9 =
* Add: Option to move JS file to footer during minification
* Add: Option to deffer loading for JS files
* Add: Option to include inline CSS
* Add: Option to include inline JS

= 1.0.8 =
* Fix: Cache exclusion for pages that returns status code other than 200

= 1.0.7 =
* Fix: Grouping and Minification issues for PHP 7.1
* Fix: Cache purge after version update issue
* Fix: Increase in cache file size issue.
* Fix: Server not found error notification
* Fix: Default WP comments display not require cache purge

= 1.0.6 =
* Fix: All Multisite are now handled globally with settings being handled at network level

= 1.0.5 =
* Fix: Issue with JS minification

= 1.0.4 =
* Fix: Browser Cache issues with WooCommerce session
* Fix: Clearing Breeze rules from .htaccess upon deactivating of GZIP/Broswer Cache
* Fix: Regex fix for accepting source url's without quotes while enabling minifcation
* Add: FAQ section added

= 1.0.3-beta =
* Fix : Disabled browser cache for WooCommerce cart, shop and account pages
* Fix : Removal of htaccess when disabling browser cache and gzip compression options
* Fix : CDN issues of not serving all the configured contents from CDN service

= 1.0.2-beta =
* Fix : Compatibility issues of WooCommerce

= 1.0.1-beta =
* Fix : Purging issue to allow only admin users to Purge
* Add : Feedback link

= 1.0.0 =
* Add : First Beta release


== Upgrade Notice ==

Update Breeze through WordPress Admin > Dashboard >Updates. The settings will remain intact after the update.

== Screenshots ==


== Requirements ==

PHP 7.x ,PHP 7.4 recommended for better performance, WordPress 4.5+
