<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       tatvic.com
 * @since      1.0.0
 *
 * @package    Convpfm_Enhanced_Ecommerce_Google_Analytics
 * @subpackage Convpfm_Enhanced_Ecommerce_Google_Analytics/admin
 * @author     Tatvic
 */

class Convpfm_Enhanced_Ecommerce_Google_Analytics_Admin extends Convpfm_TVC_Admin_Helper 
{

    /**
     * The ID of this plugin.
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.     
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.     
     * @param      string    $google_detail   The version of this plugin.
     */
    protected $google_detail;

    /**
     * construct to call google details 
     */
    public function __construct($plugin_name, $version) 
    {                       
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }//end __construct()
    
    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
      $screen = get_current_screen();
      if ($screen->id === 'toplevel_page_conversiospfm'  || (isset($_GET['page']) === TRUE && strpos(sanitize_text_field(filter_input(INPUT_GET,'page')), 'convpfm') !== false) || (isset($_GET['page']) === TRUE && strpos(sanitize_text_field(filter_input(INPUT_GET,'page')), 'generate-file') !== false)) {
          if(sanitize_text_field(filter_input(INPUT_GET,'page')) === "conversios_onboarding"){
            return;
          }          
          if(is_rtl()){ 
            wp_register_style('convpfm-plugin-bootstrap', esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL.'/includes/setup/plugins/bootstrap/css/bootstrap.rtl.min.css') );
          }else{
            wp_register_style('convpfm-plugin-bootstrap', esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL.'/includes/setup/plugins/bootstrap/css/bootstrap.min.css') );
          }
          wp_enqueue_style('convpfm-plugin-bootstrap');
          wp_register_style('convpfm-plugin-select2', esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/css/select2.css'));
          wp_enqueue_style('convpfm-plugin-select2');
          wp_enqueue_style('convpfm-conversios-header-css', esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL.'/admin/css/header.css'), array(), esc_attr($this->version), 'all' );
          wp_enqueue_style('convpfm-uiuxcss', esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL.'/admin/css/uiux.css'), array(), esc_attr($this->version), 'all' );
          if( $screen->id === CONVPFM_SCREEN_ID."convpfm-google-shopping-feed" ){             
              wp_register_style('convpfm-plugin-steps', esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL.'/includes/setup/plugins/jquery-steps/jquery.steps.css'));
              wp_enqueue_style('convpfm-plugin-steps');
              wp_register_style('convpfm-tvc-dataTables-css', esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL.'/admin/css/dataTables.bootstrap5.min.css'));
              wp_enqueue_style('convpfm-tvc-dataTables-css');
              if(sanitize_text_field(filter_input(INPUT_GET,'tab')) != "product_list"){
                wp_register_style('convpfm-product-feed-list-css', esc_url(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/css/product-feed-list.css'));
                wp_enqueue_style('convpfm-product-feed-list-css');
              }
             
          }else if($this->is_current_tab_in(array("shopping_campaigns_page","add_campaign_page"))){
            wp_register_style('convpfm-tvc-bootstrap-datepicker-css', esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL.'/includes/setup/plugins/datepicker/bootstrap-datepicker.min.css'));
            wp_enqueue_style('convpfm-tvc-bootstrap-datepicker-css');
          }

          
         
          // wp_enqueue_style('dashmain', esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL.'/admin/css/dashmain.css'), array(), esc_attr($this->version), 'all' );
          if($screen->id == CONVPFM_SCREEN_ID.'convpfm-account' || $screen->id == CONVPFM_SCREEN_ID.'convpfm-pricings')
          {
            wp_enqueue_style('convpfm-custom-css', esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL.'/admin/css/custom-style.css'), array(), esc_attr($this->version), 'all' );
            wp_enqueue_style('convpfm-dashmain', esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL.'/admin/css/dashmain.css'), array(), esc_attr($this->version), 'all' );
            wp_enqueue_style(esc_attr($this->plugin_name), esc_url_raw(plugin_dir_url(__FILE__).'css/enhanced-ecommerce-google-analytics-admin.css'), array(), esc_attr($this->version), 'all');
            wp_enqueue_style('convpfm-conversios-pricing-css', esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/css/pricing/pricing.css'), array(), esc_attr($this->version), 'all');
          }
          
          if(isset($_GET['tab']) === TRUE && sanitize_text_field(filter_input(INPUT_GET,'tab')) === "product_list"){
            wp_register_style('convpfm-feedwise-product-list-css', esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL.'/admin/css/feedwise-product-list.css'));
            wp_enqueue_style('convpfm-feedwise-product-list-css');
          } 
          if(isset($_GET['tab']) === TRUE && sanitize_text_field(filter_input(INPUT_GET,'tab')) === "feed_list"){
            wp_register_style('convpfm-product-feed-list-css', esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL.'/admin/css/product-feed-list.css'));
            wp_enqueue_style('convpfm-product-feed-list-css');
          }
          if(isset($_GET['tab']) === TRUE && sanitize_text_field(filter_input(INPUT_GET,'tab')) === "product_mapping"){
            wp_register_style('convpfm-product-mapping-css', esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL.'/admin/css/product-mapping.css'));
            wp_enqueue_style('convpfm-product-mapping-css');
          }
          if($screen->id !== CONVPFM_SCREEN_ID."convpfm-pmax"){
            wp_register_style('convpfm-product-feed-list-css', esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL.'/admin/css/product-feed-list.css'));
            wp_enqueue_style('convpfm-product-feed-list-css');
          }
          
        }
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
      $screen = get_current_screen();
      if ($screen->id === 'toplevel_page_conversiospfm'  || (isset($_GET['page']) === TRUE && strpos(sanitize_text_field(filter_input(INPUT_GET, 'page')), 'convpfm') !== false) || (isset($_GET['page']) === TRUE && strpos(sanitize_text_field(filter_input(INPUT_GET,'page')), 'generate-file') !== false)) {
        if (sanitize_text_field(filter_input(INPUT_GET, 'page')) === "conversios_onboarding") {
          return;
        }

        wp_register_script('convpfm-popper_bootstrap', esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/includes/setup/plugins/bootstrap/js/popper.min.js'));
        wp_enqueue_script('convpfm-popper_bootstrap');
        wp_register_script('convpfm-atvc_bootstrap', esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/includes/setup/plugins/bootstrap/js/bootstrap.min.js'));
        wp_enqueue_script('convpfm-atvc_bootstrap');
        // wp_enqueue_script('tvc-ee-custom-js', esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/js/tvc-ee-custom.js'), array('jquery'), esc_attr($this->version), false);
        wp_enqueue_script('convpfm-tvc-ee-slick-js', esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/js/slick.min.js'), array('jquery'), esc_attr($this->version), false);

        wp_enqueue_script('convpfm-sweetalert', esc_url_raw('https://cdn.jsdelivr.net/npm/sweetalert2@11'));

        wp_register_script('convpfm-plugin-select2', esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/js/select2.min.js'));
        wp_enqueue_script('convpfm-plugin-select2');

        if ($screen->id == CONVPFM_SCREEN_ID . "convpfm-google-shopping-feed" || $screen->id == CONVPFM_SCREEN_ID . "convpfm-pmax") {
          wp_register_script('convpfm-plugin-step-js', esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/includes/setup/plugins/jquery-steps/jquery.steps.js'));
          wp_enqueue_script('convpfm-plugin-step-js');
          wp_enqueue_script('convpfm-tvc-ee-dataTables-js', esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/js/jquery.dataTables.min.js'), array('jquery'), esc_attr($this->version), false);
          wp_enqueue_script('convpfm-tvc-ee-dataTables-v5-js', esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/js/dataTables.bootstrap5.min.js'), array('jquery'), esc_attr($this->version), false);
        }

        if ($this->is_current_tab_in(array("shopping_campaigns_page", "add_campaign_page"))) {
          wp_register_script('convpfm-plugin-chart', esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/js/chart.js'));
          wp_enqueue_script('convpfm-plugin-chart');
          wp_enqueue_script('convpfm-jquery-ui-datepicker');
        }
      }
    }
}
