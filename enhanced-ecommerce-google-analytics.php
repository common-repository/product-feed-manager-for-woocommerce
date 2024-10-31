<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              conversios.io
 * @since             1.0.0
 * @package           Enhanced E-commerce for Woocommerce store
 *
 * @wordpress-plugin
 * Plugin Name:       Conversios.io - Product Feed Manager for WooCommerce
 * Plugin URI:        https://www.conversios.io/
 * Description:       Product Feed Manager for WooCommerce lets you share your WooCommerce products to your Google Merchant account and TikTok Business center account using APIs and automate the product sync for running high-performing Google Shopping and TikTok ads campaigns for your WooCommerce products to boost ROAS (Revenue on Ad Spends). One-click integration with your Google Merchant Center and TikTok Business Center account and you are set to have your products on Google and TikTok Ads channels. You can schedule your product sync for unlimited products from your WooCommerce shop.
 * Version:           4.0.0
 * Author:            Conversios
 * Author URI:        conversios.io
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       product-feed-manager-for-woocommerce
 * Domain Path:       /languages
 * WC requires at least: 3.5.0
 * WC tested up to: 8.9.1
 */

/**
 * If this file is called directly, abort.
 */
if (!defined('WPINC')) {
    die;
}
/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */


add_action('before_woocommerce_init', function () {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-convpfm-enhanced-ecommerce-google-analytics-activator.php
 */

function activate_convpfm_enhanced_ecommerce_google_analytics()
{    
    require_once plugin_dir_path(__FILE__) . 'includes/class-convpfm-enhanced-ecommerce-google-analytics-activator.php';
    Convpfm_Enhanced_Ecommerce_Google_Analytics_Activator::activate();    
    set_transient('_conversios_activation_redirect', true, 20);
}

add_action('admin_init', 'my_plugin_redirect');
/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-convpfm-enhanced-ecommerce-google-analytics-deactivator.php
 */
function deactivate_convpfm_enhanced_ecommerce_google_analytics()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-convpfm-enhanced-ecommerce-google-analytics-deactivator.php';
    Convpfm_Enhanced_Ecommerce_Google_Analytics_Deactivator::deactivate();
    if (is_plugin_active_for_network('woocommerce/woocommerce.php') || in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
        as_unschedule_all_actions('init_feed_wise_product_sync_process_scheduler_convpfm');
        as_unschedule_all_actions('auto_feed_wise_product_sync_process_scheduler_convpfm');
    }
}
register_activation_hook(__FILE__, 'activate_convpfm_enhanced_ecommerce_google_analytics');
register_deactivation_hook(__FILE__, 'deactivate_convpfm_enhanced_ecommerce_google_analytics');


define('PLUGIN_CONVPFM_VERSION', '4.0.0');
$fullName = plugin_basename(__FILE__);
$dir = str_replace('/enhanced-ecommerce-google-analytics.php', '', $fullName);

//APP ID
if (!defined('CONVPFM_APP_ID')) {
    define('CONVPFM_APP_ID', 4);
}
//Screen ID
if (!defined('CONVPFM_SCREEN_ID')) {
    define('CONVPFM_SCREEN_ID', 'product-feed_page_');
}
//Top Menu
if (!defined('CONVPFM_TOP_MENU')) {
    define('CONVPFM_TOP_MENU', 'Product Feed');
}
//Menu Slug
if (!defined('CONVPFM_MENU_SLUG')) {
    define('CONVPFM_MENU_SLUG', 'conversiospfm');
}

if (!defined('CONPFM_ENHANCAD_PLUGIN_NAME')) {
    define('CONPFM_ENHANCAD_PLUGIN_NAME', $dir);
}
// Store the directory of the plugin
if (!defined('CONVPFM_ENHANCAD_PLUGIN_DIR')) {
    define('CONVPFM_ENHANCAD_PLUGIN_DIR', plugin_dir_path(__FILE__));
}
// Store the url of the plugin
if (!defined('CONVPFM_ENHANCAD_PLUGIN_URL')) {
    define('CONVPFM_ENHANCAD_PLUGIN_URL', plugins_url() . '/' . CONPFM_ENHANCAD_PLUGIN_NAME);
}

if (!defined('CONVPFM_API_CALL_URL')) {
    define('CONVPFM_API_CALL_URL', 'https://connect.tatvic.com/laravelapi/public/api');
}
if (!defined('CONVPFM_API_CALL_URL_TEMP')) {
    define('CONVPFM_API_CALL_URL_TEMP', 'https://connect.tatvic.com/laravelapi/public');
}
if (!defined('CONV_AUTH_CONNECT_URL')) {
    define('CONV_AUTH_CONNECT_URL', 'conversios.io');
}

if (!defined('Convpfm_TVC_Admin_Helper')) {
    include(CONVPFM_ENHANCAD_PLUGIN_DIR . '/admin/class-convpfm-tvc-admin-helper.php');
}

if (!defined('CONVPFM_LOG')) {
    define('CONVPFM_LOG', CONVPFM_ENHANCAD_PLUGIN_DIR . 'logs/');
}

add_action('upgrader_process_complete', 'convpfm_upgrade_function', 10, 2);

function convpfm_upgrade_function($upgrader_object, $options)
{
    $fullName = plugin_basename(__FILE__);
    if ($options['action'] == 'update' && $options['type'] == 'plugin' && is_array($options['plugins'])) {
        foreach ($options['plugins'] as $each_plugin) {
            if ($each_plugin == $fullName) {
                $Convpfm_TVC_Admin_Helper = new Convpfm_TVC_Admin_Helper();
                $Convpfm_TVC_Admin_Helper->update_app_status();
            }
        }
    }
    $plugin = 'enhanced-e-commerce-for-woocommerce-store/enhanced-ecommerce-google-analytics.php';
        //is_plugin_active('enhanced-e-commerce-pro-for-woocommerce-store/enhanced-ecommerce-pro-google-analytics.php')
    if ( !is_plugin_active( $plugin ) && !is_plugin_active('enhanced-e-commerce-pro-for-woocommerce-store/enhanced-ecommerce-pro-google-analytics.php')) {
        $convpfm_options = get_option('convpfm_options');
        if(empty($convpfm_options)) {
            $ee_options = get_option('ee_options');
            update_option("convpfm_options", $ee_options);
            update_option("ee_options", '');
        }
        $convpfm_api_data = get_option('convpfm_api_data');
        if(empty($convpfm_api_data)) {
            $ee_api_data = get_option('ee_api_data');
            update_option("convpfm_api_data", $ee_api_data);
            update_option("ee_api_data", '');
        }
    }
    if (!class_exists('Convpfm_Activation')) {  
        require_once CONVPFM_ENHANCAD_PLUGIN_DIR . 'includes/setup/GenerateFile/classes/class-activate.php';
        \Convpfm_Activation::activate_checks();
    }
}


/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-convpfm-enhanced-ecommerce-google-analytics.php';

function my_plugin_redirect() {
    // Check if the transient is set
    if (get_transient('_conversios_activation_redirect')) {
        // Delete the transient to prevent the redirect from happening again
        delete_transient('_conversios_activation_redirect');

        // Ensure this is not a multisite network admin page
        if (is_multisite() && !is_network_admin()) {
            return;
        }
        if (!class_exists('Convpfm_Activation')) {  
            require_once CONVPFM_ENHANCAD_PLUGIN_DIR . 'includes/setup/GenerateFile/classes/class-activate.php';
            \Convpfm_Activation::activate_checks();
        }
        // Redirect to the Channel config page
        wp_safe_redirect(admin_url('admin.php?page=conversiospfm'));
        exit;
    }
    
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */

function run_convpfm_enhanced_ecommerce_google_analytics()
{
    $plugin = new Convpfm_Enhanced_Ecommerce_Google_Analytics();
    $plugin->run();
}
run_convpfm_enhanced_ecommerce_google_analytics();