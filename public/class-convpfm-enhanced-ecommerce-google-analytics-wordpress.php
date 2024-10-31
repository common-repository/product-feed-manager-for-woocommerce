<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       conversios.io
 * @since      1.0.0
 *
 * @package    Convpfm_Enhanced_Ecommerce_Google_Analytics
 * @subpackage Convpfm_Enhanced_Ecommerce_Google_Analytics/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * @package    Convpfm_Enhanced_Ecommerce_Google_Analytics
 * @subpackage Convpfm_Enhanced_Ecommerce_Google_Analytics/public
 * @author     Conversios
 */
class Convpfm_Enhanced_Ecommerce_Google_Analytics_Wordpress 
{
  /**
   * Init and hook in the integration.
   *
   * @access public
   * @return void
   */
  //set plugin version
  protected $plugin_name;
  protected $version;
  /**
   * Convpfm_Enhanced_Ecommerce_Google_Analytics_Public constructor.
   * @param $plugin_name
   * @param $version
   */

  public function __construct($plugin_name, $version)
  {
    // parent::__construct();
    //$this->gtm = new Con_GTM_Tracking($plugin_name, $version);
    $this->Convpfm_TVC_Admin_Helper = new Convpfm_TVC_Admin_Helper();
    $this->plugin_name = sanitize_text_field($plugin_name);
    $this->version  = sanitize_text_field($version);
    $this->convpfm_call_hooks();
    //$this->fb_page_view_event_id = $this->get_fb_event_id();

    /*
     * start tvc_options
     */
    $current_user = wp_get_current_user();
    //$current_user ="";
    $user_id = "";
    $user_type = "guest_user";
    if (isset($current_user->ID) && $current_user->ID != 0) {
      $user_id = $current_user->ID;
      $current_user_type = 'register_user';
    }
    // add_action("wp_enqueue_scripts", array($this, "convpfm_store_meta_data"));
    
  }

  public function convpfm_call_hooks()
  {
    add_action("wp_enqueue_scripts", array($this, "convpfm_store_meta_data"));
  }
  function convpfm_store_meta_data()
  {
    //only on home page
    global $woocommerce;
    $google_detail = $this->Convpfm_TVC_Admin_Helper->get_convpfm_api_data();
    $googleDetail = array();
    if (isset($google_detail['setting'])) {
      $googleDetail = $google_detail['setting'];
    }
    $tvc_sMetaData = array(
      'convpfm_wcv' => isset($woocommerce->version) ? esc_js($woocommerce->version) : '',
      'convpfm_wpv' => esc_js(get_bloginfo('version')),
      'convpfm_eev' => esc_js(PLUGIN_CONVPFM_VERSION),      
      'convpfm_sub_data' => array(
        'sub_id' => esc_js(isset($googleDetail->id) ? sanitize_text_field($googleDetail->id) : ""),
        'cu_id' => esc_js(isset($googleDetail->customer_id) ? sanitize_text_field($googleDetail->customer_id) : ""),
        'pl_id' => esc_js(isset($googleDetail->plan_id) ? sanitize_text_field($googleDetail->plan_id) : ""),        
        'ga_ads_id' => esc_js(isset($googleDetail->google_ads_id) ? sanitize_text_field($googleDetail->google_ads_id) : ""),
        'ga_gmc_id' => esc_js(isset($googleDetail->google_merchant_center_id) ? sanitize_text_field($googleDetail->google_merchant_center_id) : ""),
        'ga_gmc_merchant_id' => esc_js(isset($googleDetail->merchant_id) ? sanitize_text_field($googleDetail->merchant_id) : ""),        
        'gmc_is_site_verified' => esc_js(isset($googleDetail->is_site_verified) ? sanitize_text_field($googleDetail->is_site_verified) : ""),
        'gmc_is_domain_claim' => esc_js(isset($googleDetail->is_domain_claim) ? sanitize_text_field($googleDetail->is_domain_claim) : ""),        
      )
    );
    $this->wc_version_compare("convpfm_smd=" . wp_json_encode($tvc_sMetaData) . ";");
  }
  function wc_version_compare($codeSnippet)
  {
    wp_add_inline_script('enhanced-ecommerce-google-analytics', $codeSnippet);
  }
}
