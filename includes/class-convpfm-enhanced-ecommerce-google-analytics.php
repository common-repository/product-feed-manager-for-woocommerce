<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       tatvic.com
 * @since      1.0.0
 *
 * @package    Convpfm_Enhanced_Ecommerce_Google_Analytics
 * @subpackage Convpfm_Enhanced_Ecommerce_Google_Analytics/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Convpfm_Enhanced_Ecommerce_Google_Analytics
 * @subpackage Convpfm_Enhanced_Ecommerce_Google_Analytics/includes
 * @author     Tatvic
 */
class Convpfm_Enhanced_Ecommerce_Google_Analytics
{

  /**
   * The loader that's responsible for maintaining and registering all hooks that power
   * the plugin.
   *
   * @since    1.0.0
   * @access   protected
   * @var      Convpfm_Enhanced_Ecommerce_Google_Analytics_Loader    $loader    Maintains and registers all hooks for the plugin.
   */
  protected $loader;

  /**
   * The unique identifier of this plugin.
   *
   * @since    1.0.0
   * @access   protected
   * @var      string    $plugin_name    The string used to uniquely identify this plugin.
   */
  protected $plugin_name;

  /**
   * The current version of the plugin.
   *
   * @since    1.0.0
   * @access   protected
   * @var      string    $version    The current version of the plugin.
   */
  protected $version;

  /**
   * Define the core functionality of the plugin.
   *
   * Set the plugin name and the plugin version that can be used throughout the plugin.
   * Load the dependencies, define the locale, and set the hooks for the admin area and
   * the public-facing side of the site.
   *
   * @since    1.0.0
   */
  public function __construct()
  {
    if (defined('PLUGIN_CONVPFM_VERSION')) {
      $this->version = PLUGIN_CONVPFM_VERSION;
    } else {
      $this->version = '2.0';
    }
    $this->plugin_name = 'enhanced-ecommerce-google-analytics';
    $this->load_dependencies();
    $this->set_locale();
    $this->define_admin_hooks();
    //$this->define_public_hooks();
    add_action('init', array($this, 'define_public_hooks'));
    $this->check_dependency();
    add_filter('plugin_action_links_' . plugin_basename(plugin_dir_path(__DIR__) . $this->plugin_name . '.php'), array($this, 'tvc_plugin_action_links'), 10);
  }

  /**
   * Load the required dependencies for this plugin.
   *
   * Include the following files that make up the plugin:
   *
   * - Convpfm_Enhanced_Ecommerce_Google_Analytics_Loader. Orchestrates the hooks of the plugin.
   * - Convpfm_Enhanced_Ecommerce_Google_Analytics_i18n. Defines internationalization functionality.
   * - Convpfm_Enhanced_Ecommerce_Google_Analytics_Admin. Defines all hooks for the admin area.
   * - Convpfm_Enhanced_Ecommerce_Google_Analytics_Public. Defines all hooks for the public side of the site.
   *
   * Create an instance of the loader which will be used to register the hooks
   * with WordPress.
   *
   * @since    1.0.0
   * @access   private
   */
  private function load_dependencies()
  {
    /**
     * The class responsible for orchestrating the actions and filters of the
     * core plugin.
     */
    require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-convpfm-enhanced-ecommerce-google-analytics-loader.php';

    /**
     * The class responsible for defining internationalization functionality
     * of the plugin.
     */
    require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-convpfm-enhanced-ecommerce-google-analytics-i18n.php';
    require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-convpfm-admin-db-helper.php';

    require_once plugin_dir_path(dirname(__FILE__)) . 'includes/data/class-convpfm-ajax-calls.php';
    require_once plugin_dir_path(dirname(__FILE__)) . 'includes/data/class-convpfm-ajax-file.php';
    require_once plugin_dir_path(dirname(__FILE__)) . 'includes/data/class-convpfm-taxonomies.php';

    require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-convpfm-register-scripts.php';
    /**
     * The class responsible for defining all actions that occur in the admin area.
     */
    require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-convpfm-enhanced-ecommerce-google-analytics-admin.php';

    /**
     * New conversios UI file list
     */
    require_once plugin_dir_path(dirname(__FILE__)) . 'admin/helper/class-convpfm-onboarding-helper.php';
    require_once plugin_dir_path(dirname(__FILE__)) . 'admin/helper/class-convpfm-dashboard-helper.php';
    require_once plugin_dir_path(dirname(__FILE__)) . 'admin/helper/class-convpfm-reports-helper.php';
    require_once plugin_dir_path(dirname(__FILE__)) . 'admin/helper/class-convpfm-pmax-helper.php';
    require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-convpfm-admin.php';
    /**
     * End New conversios UI file list
     */
    require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-convpfm-admin-auto-product-sync-helper.php';
    require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-convpfm-survey.php';

    /**
     * The class responsible for defining all actions that occur in the public-facing
     * side of the site.
     */
    if (!function_exists('is_plugin_active_for_network')) {
      require_once(ABSPATH . '/wp-admin/includes/woocommerce.php');
    }
    if ( is_plugin_active_for_network( 'woocommerce/woocommerce.php') || in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) )  {
      $Convpfm_TVC_Admin_Helper = new Convpfm_TVC_Admin_Helper();
      require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-convpfm-enhanced-ecommerce-google-analytics-public.php';
    } else {
      $Convpfm_TVC_Admin_Helper = new Convpfm_TVC_Admin_Helper();
      require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-convpfm-enhanced-ecommerce-google-analytics-wordpress.php';
    }
    $this->loader = new Convpfm_Enhanced_Ecommerce_Google_Analytics_Loader();
  }

  /**
   * Define the locale for this plugin for internationalization.
   *
   * Uses the  Convpfm_Enhanced_Ecommerce_Google_Analytics_i18n class in order to set the domain and to register the hook
   * with WordPress.
   *
   * @since    1.0.0
   * @access   private
   */
  private function set_locale()
  {
    $plugin_i18n = new Convpfm_Enhanced_Ecommerce_Google_Analytics_i18n();
    $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
  }

  /**
   * Register all of the hooks related to the admin area functionality
   * of the plugin.
   *
   * @since    1.0.0
   * @access   private
   */
  private function define_admin_hooks()
  {
    $plugin_admin = new Convpfm_Enhanced_Ecommerce_Google_Analytics_Admin($this->get_plugin_name(), $this->get_version());
    $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
    $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
    $this->loader->add_action('admin_notice_message', $plugin_admin, 'convpfm_add_admin_notice');
    // $this->loader->add_action('admin_notices', $plugin_admin, 'tvc_display_admin_notices');
    $this->loader->add_action('admin_notices', $plugin_admin, 'convpfm_add_data_admin_notice');


    if (is_admin()) {
      new Convpfm_Survey(esc_html__("Product Feed Manager for woocommerce"), CONPFM_ENHANCAD_PLUGIN_NAME);
    }
  }

  /**
   * Register all of the hooks related to the public-facing functionality
   * of the plugin.
   *
   * @since    1.0.0
   * @access   private
   */
  public function define_public_hooks()
  {
    if (is_plugin_active_for_network('woocommerce/woocommerce.php') || in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
      new Convpfm_Enhanced_Ecommerce_Google_Analytics_Public($this->get_plugin_name(), $this->get_version());
    } else {
      new Convpfm_Enhanced_Ecommerce_Google_Analytics_Wordpress($this->get_plugin_name(), $this->get_version());
    }
  }

  /**
   * Run the loader to execute all of the hooks with WordPress.
   *
   * @since    1.0.0
   */
  public function run()
  {
    $this->loader->run();
    if ( is_plugin_active_for_network( 'woocommerce/woocommerce.php') || in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ){
        add_action('woocommerce_init' , function (){
            $this->loader->run();
        });
    }
    else{
      include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
      if ( is_plugin_active( 'woocommerce/woocommerce.php' ) || is_plugin_active_for_network( 'woocommerce/woocommerce.php') ) {
        $this->loader->run();
      }else if( is_admin() && !is_network_admin() && is_plugin_active( 'product-feed-manager-for-woocommerce/enhanced-ecommerce-google-analytics.php' ) ){
        $this->loader->run();
      }
    }
  }

  /**
   * The name of the plugin used to uniquely identify it within the context of
   * WordPress and to define internationalization functionality.
   *
   * @since     1.0.0
   * @return    string    The name of the plugin.
   */
  public function get_plugin_name()
  {
    return $this->plugin_name;
  }

  /**
   * The reference to the class that orchestrates the hooks with the plugin.
   *
   * @since     1.0.0
   * @return     Convpfm_Enhanced_Ecommerce_Google_Analytics_Loader    Orchestrates the hooks of the plugin.
   */

  public function get_loader()
  {
    return $this->loader;
  }

  /**
   * Retrieve the version number of the plugin.
   *
   * @since     1.0.0
   * @return    string    The version number of the plugin.
   */

  public function get_version()
  {
    return $this->version;
  }

  public function tvc_plugin_action_links($links)
  {
    $deactivate_link = $links['deactivate'];
    unset($links['deactivate']);
    $setting_url = esc_url_raw('admin.php?page=conversiospfm');
    $links[] = '<a href="' . get_admin_url(null, $setting_url) . '">' . esc_html__("Configuration", "product-feed-manager-for-woocommerce") . '</a>';
    $links[] = '<a href="' . esc_url_raw("https://wordpress.org/plugins/enhanced-e-commerce-for-woocommerce-store/#faq") . '" target="_blank">' . esc_html__("FAQ", "product-feed-manager-for-woocommerce") . '</a>';
    $links[] = '<a href="' . esc_url_raw("https://www.conversios.io/docs/how-to-set-up-the-plugin/?utm_source=documentation&utm_medium=pluginlisting&utm_campaign=howtosetup") . '" target="_blank">' . esc_html__("Documentation", "product-feed-manager-for-woocommerce") . '</a>';
    $links[] = '<a href="' . esc_url_raw("https://conversios.io/pricings/?utm_source=EE+Plugin+User+Interface&utm_medium=Plugins+Listing+Page+Upgrade+to+Premium&utm_campaign=Upsell+at+Conversios") . '" target="_blank"><b>' . esc_html__("Upgrade to Premium", "product-feed-manager-for-woocommerce") . '</b></a>';
    $links['deactivate'] = $deactivate_link;
    return $links;
  }

  /**
   * Check Enhance E-commerce Plugin is Activated
   * Free Plugin
   */

  public function check_dependency()
  {
    if (function_exists('run_actionable_google_analytics')) {
      printf('<div class="error"><p><strong>%s</strong>%s</p></div>', esc_html__("Note: ", "product-feed-manager-for-woocommerce"), esc_html__("It seems Actionable Google Analytics Plugin is active on your store. Kindly deactivate it in order to avoid data duplication in GA.", "product-feed-manager-for-woocommerce"));
      die();
    }
  }
}
