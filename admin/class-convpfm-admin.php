<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       tatvic.com
 * @since      1.0.0
 *
 * @package    Convpfm_Enhanced_Ecommerce_Google_Analytics
 * @subpackage Convpfm_Enhanced_Ecommerce_Google_Analytics/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Convpfm_Enhanced_Ecommerce_Google_Analytics
 * @subpackage Convpfm_Enhanced_Ecommerce_Google_Analytics/admin
 * @author     Tatvic
 */
if (class_exists('Convpfm_Admin') === FALSE) {
    class Convpfm_Admin extends Convpfm_TVC_Admin_Helper
    {

      //Google Detail variable
      protected $google_detail;

      //Url onboarding
      protected $url;

      //Version variable
      protected $version;
      public function __construct()
      {
        $this->version = PLUGIN_CONVPFM_VERSION;
        $this->includes();
        $this->url = $this->get_onboarding_page_url(); // use in setting page
        $this->google_detail = $this->get_convpfm_api_data();
        add_action('admin_menu', array($this, 'add_admin_pages'));
        add_action('admin_init', array($this, 'init'));
        add_action("admin_print_styles", [$this, 'dequeue_css']);
      }

      /********Include Header and Footer **************************/
      public function includes()
      {
        if (class_exists('Convpfm_Header') === FALSE) {
          require_once(CONVPFM_ENHANCAD_PLUGIN_DIR.'admin/partials/class-convpfm-header.php');
        }

        if (class_exists('Convpfm_Footer') === FALSE) {
          require_once(CONVPFM_ENHANCAD_PLUGIN_DIR.'admin/partials/class-convpfm-footer.php');
        }

      }

      /*******Add scripts and styles to every page *******************/
      public function init()
      {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
      }

      function dequeue_css()
      {
        $screen = get_current_screen();
        global $wp_styles;
        if(str_contains($screen->id, CONVPFM_SCREEN_ID) || $screen->id === 'toplevel_page_conversiospfm') {      
          $not_allowed_css = array('porto_admin', 'flashy', 'uiuxcss', 'animate.min.css', 'dashmain', 'plugin-select2', 'enhanced-ecommerce-google-analytics', 'conversios-responsive-css', 'conversios-header-css');
          foreach ($wp_styles->queue as $key => $value) {
            if (in_array($value, $not_allowed_css)) {
              wp_deregister_style($value);
              wp_dequeue_style($value);
            }
          }
        }
        
      }
      
      /**
       * Register the stylesheets for the admin area.
       *
       * @since    4.1.4
       */
      public function enqueue_styles()
      {
        $screen = get_current_screen();
        if ($screen->id === 'toplevel_page_conversiospfm'  || (isset($_GET['page']) === TRUE && strpos(sanitize_text_field($_GET['page']), 'convpfm') !== false)) {
          //developres hook to custom css
          do_action('add_conversios_css_' . sanitize_text_field($_GET['page']));
          //conversios page css
          // if (sanitize_text_field(wp_unslash(filter_input(INPUT_GET, 'page'))) == "conversios-analytics-reports" || sanitize_text_field(wp_unslash(filter_input(INPUT_GET, 'page'))) == "conversios") {
          //   wp_register_style('conversios-slick-css', esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/css/slick.css'));
          //   wp_enqueue_style('conversios-slick-css');
          //   wp_register_style('conversios-daterangepicker-css', esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/css/daterangepicker.css'));
          //   wp_enqueue_style('conversios-daterangepicker-css');
          // } else if (sanitize_text_field($_GET['page']) === "conversios-pmax") {
          //   wp_register_style('jquery-ui', esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/includes/setup/plugins/datepicker/jquery-ui.css'));
          //   wp_enqueue_style('jquery-ui');
          // }
          if ($screen->id != "conversios_page_convpfm-google-shopping-feed") {
            // wp_enqueue_style('conversios-style-css', esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/css/style.css'), array(), esc_attr($this->version), 'all');
          }

          //pricingcss
          if ($screen->id == "product-feed_page_convpfm-pricings") {
            
            // wp_enqueue_style('conversios-pricing-css', esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/css/pricing/pricing.css'), array(), esc_attr($this->version), 'all');
          }
          //all conversios page css        
          wp_enqueue_style('convpfm-conversios-responsive-css', esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/css/responsive.css'), array(), esc_attr($this->version), 'all');
          if (isset($_GET['page']) === TRUE && sanitize_text_field($_GET['page']) === "convpfm-pmax") {
            wp_register_style('convpfm-convpfm-dataTables-css', esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/css/dataTables.bootstrap5.min.css'));
            wp_enqueue_style('convpfm-convpfm-dataTables-css');
          }        
        }
      }

      /**
       * Register the JavaScript for the admin area.
       *
       * @since    4.1.4
       */
      public function enqueue_scripts()
      {
        $screen = get_current_screen();

        if (sanitize_text_field(wp_unslash(filter_input(INPUT_GET, 'wizard'))) == "campaignManagement" || isset($_GET['page']) === TRUE && sanitize_text_field($_GET['page']) === "convpfm-pmax") {
          wp_enqueue_script('convpfm-conversios-pmax-js', esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/js/pmax-custom.js'), array('jquery'), esc_attr($this->version), false);
          wp_register_script('convpfm-tvc-bootstrap-datepicker-js', esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/includes/setup/plugins/datepicker/bootstrap-datepicker.min.js'));
          wp_enqueue_script('convpfm-tvc-bootstrap-datepicker-js');
          wp_enqueue_script('convpfm-jquery-ui-datepicker');
          wp_enqueue_script('convpfm-ee-dataTables-js', esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/js/jquery.dataTables.min.js'), array('jquery'), esc_attr($this->version), false);
          wp_enqueue_script('convpfm-ee-dataTables-v5-js', esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/js/dataTables.bootstrap5.min.js'), array('jquery'), esc_attr($this->version), false);
        } else if (isset($_GET['page']) === TRUE && sanitize_text_field($_GET['page']) === "conversios") {
          wp_enqueue_script('convpfm-conversios-moment-js', esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/js/moment.min.js'));
        }      
      }
      
      /**
       * Display Admin Page.
       *
       * @since    4.1.4
       */
      public function add_admin_pages()
      {
        $google_detail = $this->google_detail;
        if (isset($google_detail['setting'])) {
          $googleDetail = $google_detail['setting'];
        }
        $icon = CONVPFM_ENHANCAD_PLUGIN_URL."/admin/images/offer.png";
        $freevspro = CONVPFM_ENHANCAD_PLUGIN_URL."/admin/images/freevspro.png";
        add_menu_page(
          esc_html__(CONVPFM_TOP_MENU, 'product-feed-manager-for-woocommerce'),
          esc_html__(CONVPFM_TOP_MENU, 'product-feed-manager-for-woocommerce') . '',
          'manage_options',
          CONVPFM_MENU_SLUG,
          array($this, 'showPage'),
          esc_url_raw(plugin_dir_url(__FILE__).'images/tatvic_logo.png'),
          26
        );
        if (!function_exists('is_plugin_active_for_network')) {
          require_once(ABSPATH . '/wp-admin/includes/woocommerce.php');
        }
        if (is_plugin_active_for_network('woocommerce/woocommerce.php') || in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
          
          add_submenu_page(
            CONVPFM_MENU_SLUG,
            esc_html__('Channel Configuration', 'product-feed-manager-for-woocommerce'),
            esc_html__('Channel Configuration', 'product-feed-manager-for-woocommerce'),
            'manage_options',
            'conversiospfm',
            '',
            0
          );
          add_submenu_page(
            CONVPFM_MENU_SLUG,
            esc_html__('Manage Feeds (API)', 'product-feed-manager-for-woocommerce'),
            esc_html__('Manage Feeds (API)', 'product-feed-manager-for-woocommerce'),
            'manage_options',
            'convpfm-google-shopping-feed',
            array($this, 'showPage'),
            1
          );
          add_submenu_page(
            CONVPFM_MENU_SLUG,
            esc_html__('Create Feeds (File)', 'enhanced-e-commerce-for-woocommerce-store'),
            esc_html__('Create Feeds (File)', 'enhanced-e-commerce-for-woocommerce-store'),
            'manage_options',
            'convpfm-generate-file',
            array($this, 'showPage'),
            2
          );
  
          add_submenu_page(
            CONVPFM_MENU_SLUG,
            esc_html__('Manage File', 'enhanced-e-commerce-for-woocommerce-store'),
            esc_html__('Manage File', 'enhanced-e-commerce-for-woocommerce-store'),
            'manage_options',
            'convpfm-manage-file',
            array($this, 'showPage'),
            3
          );
          add_submenu_page(
            CONVPFM_MENU_SLUG,
            esc_html__('Manage Campaign', 'product-feed-manager-for-woocommerce'),
            esc_html__('Manage Campaign', 'product-feed-manager-for-woocommerce'),
            'manage_options',
            'convpfm-pmax',
            array($this, 'showPage'),
            4
          );
          add_submenu_page(
            CONVPFM_MENU_SLUG,
            esc_html__('Account Summary', 'product-feed-manager-for-woocommerce'),
            esc_html__('Account Summary', 'product-feed-manager-for-woocommerce'),
            'manage_options',
            'convpfm-account',
            array($this, 'showPage'),
            5
          );
          
          add_submenu_page(
            CONVPFM_MENU_SLUG,
            esc_html__('Free Vs Pro', 'product-feed-manager-for-woocommerce'),
            esc_html__('Free Vs Pro', 'product-feed-manager-for-woocommerce') . '<img style="position: absolute; height: 30px;bottom: 5px; right: 10px;" src="' . esc_url_raw($freevspro) . '">',
            'manage_options',
            'convpfm-pricings',
            array($this, 'showPage'),
            6
          );
        }
      }

      /**
       * Display page.
       *
       * @since    4.1.4
       */
      public function showPage()
      {
        do_action('add_convpfm_header');
        if (!empty(sanitize_text_field($_GET['page']))) {
          $get_action = str_replace("-", "_", sanitize_text_field($_GET['page']));
        } else {
          $get_action = "conversios";
        }
        if (method_exists($this, $get_action)) {
          $this->$get_action();
        }        
        do_action('add_convpfm_footer');
      }

      public function conversiospfm()
      {
        $is_inner_page = isset($_GET['tab']) ? sanitize_text_field(wp_unslash(filter_input(INPUT_GET, 'tab'))) : "";
        $is_inner_page = str_replace("-", "_", sanitize_text_field($is_inner_page));
        if ($is_inner_page != "") {
          $this->$is_inner_page();
        } else {          
          require_once(CONVPFM_ENHANCAD_PLUGIN_DIR . 'includes/setup/class-convpfm-dashboard.php');
        }
      }

      public function convpfm_pricings()
      {
        require_once(CONVPFM_ENHANCAD_PLUGIN_DIR.'admin/partials/convpfm-pricings.php');
        new Convpfm_Pricings();
      }
      public function convpfm_account()
      {
        require_once(CONVPFM_ENHANCAD_PLUGIN_DIR.'includes/setup/help-html.php');
        require_once(CONVPFM_ENHANCAD_PLUGIN_DIR.'includes/setup/convpfm_account.php');
        new Convpfm_Account();
      }
      
      public function convpfm_pmax()
      {
        $action_tab = (isset($_GET['tab']) === TRUE) ? sanitize_text_field($_GET['tab']) : "";
        if ($action_tab != "") {
          $this->$action_tab();
        } else {
          require_once(CONVPFM_ENHANCAD_PLUGIN_DIR.'includes/setup/convpfm_pmax.php');
          new Convpfm_PMax();
        }
      }
      public function pmax_add()
      {
        require_once(CONVPFM_ENHANCAD_PLUGIN_DIR.'includes/setup/pmax-add.php');
        new TVC_PMaxAdd();
      }
      public function pmax_edit()
      {
        require_once(CONVPFM_ENHANCAD_PLUGIN_DIR.'includes/setup/pmax-edit.php');
        new TVC_PMaxEdit();
      }
      public function convpfm_google_shopping_feed()
      {
        $action_tab = (isset($_GET['tab'])) ? sanitize_text_field(wp_unslash(filter_input(INPUT_GET,'tab'))) : "";
        if ($action_tab != "") {
          $this->$action_tab();
        } else {
          //new GoogleShoppingFeed();
          require_once CONVPFM_ENHANCAD_PLUGIN_DIR.'includes/setup/help-html.php';
          require_once CONVPFM_ENHANCAD_PLUGIN_DIR.'admin/class-convpfm-tvc-admin-helper.php';
          require_once CONVPFM_ENHANCAD_PLUGIN_DIR.'includes/setup/convpfm-customApi.php';  
          require_once CONVPFM_ENHANCAD_PLUGIN_DIR.'includes/setup/convpfm-category-wrapper.php';  
          require_once CONVPFM_ENHANCAD_PLUGIN_DIR.'includes/setup/class-convpfm-product-sync-helper.php';  
          require_once 'partials/convpfm-product-feed-list.php';
        }
      }      
      public function feed_list()
      {
        require_once CONVPFM_ENHANCAD_PLUGIN_DIR.'includes/setup/help-html.php';
        require_once CONVPFM_ENHANCAD_PLUGIN_DIR.'admin/class-convpfm-tvc-admin-helper.php';
        require_once CONVPFM_ENHANCAD_PLUGIN_DIR.'includes/setup/convpfm-customApi.php';  
        require_once CONVPFM_ENHANCAD_PLUGIN_DIR.'includes/setup/convpfm-category-wrapper.php';  
        require_once CONVPFM_ENHANCAD_PLUGIN_DIR.'includes/setup/class-convpfm-product-sync-helper.php';  
        if(isset($_GET['edit']) == TRUE) {
          require_once CONVPFM_ENHANCAD_PLUGIN_DIR.'admin/class-convpfm-admin-db-helper.php';
          require_once 'partials/convpfm-edit-product-feed-list.php';
        } else {
          require_once 'partials/convpfm-product-feed-list.php';
        }
        

      }
      public function product_list()
      {
        require_once CONVPFM_ENHANCAD_PLUGIN_DIR.'includes/setup/help-html.php';
        require_once CONVPFM_ENHANCAD_PLUGIN_DIR.'admin/class-convpfm-tvc-admin-helper.php';
        require_once CONVPFM_ENHANCAD_PLUGIN_DIR.'includes/setup/convpfm-customApi.php';
        require_once CONVPFM_ENHANCAD_PLUGIN_DIR.'admin/class-convpfm-admin-db-helper.php';
        require_once CONVPFM_ENHANCAD_PLUGIN_DIR.'includes/setup/convpfm-category-wrapper.php';
        require_once CONVPFM_ENHANCAD_PLUGIN_DIR.'includes/setup/class-convpfm-product-sync-helper.php';
        require_once 'partials/convpfm-feedwise-product-list.php';

      }

      public function convpfm_generate_file()
    {
      require_once(CONVPFM_ENHANCAD_PLUGIN_DIR . 'includes/setup/GenerateFile/activator.php');
      function stripslashes_recursive_pfm($object)
      {
        return is_array($object) ? array_map('stripslashes_recursive_pfm', $object) : stripslashes($object);
      }

      if (!$_POST) {
        $generate_step = 0;
      } else {
        $from_post     = stripslashes_recursive_pfm($_POST);
        $channel_hash  = sanitize_text_field($_POST['channel_hash']);
        $step          = sanitize_text_field($_POST['step']);
        $generate_step = $step;
      }

      if (array_key_exists('step', $_GET)) {
        if (array_key_exists('step', $_POST)) {
          $generate_step = $step;
        } else {
          $generate_step = sanitize_text_field($_GET['step']);
        }
      }

      if (isset($_GET['channel_hash'])) {
        $channel_hash = sanitize_text_field($_GET['channel_hash']);
      }

      // Get channel information.
      if ($generate_step) {
        $channel_data = Convpfm_UpdateProject::get_channel_data($channel_hash);
      }

      // Determing if we need to do field mapping or attribute picking after step 0.
      if ($generate_step == 99) {
        $generate_step = 7;
      } elseif ($generate_step == 100) {
        // Update existing feed configuration with new values from previous step.
        $project = Convpfm_UpdateProject::reconfigure_project($from_post);
      } elseif ($generate_step == 101) {
        // Update project configuration.
        $project_data = Convpfm_UpdateProject::update_project($from_post);
        // Set some last project configs.
        $project_data['active']                = true;
        $project_data['last_updated']          = date('d M Y H:i');
        $project_data['running']               = 'processing';
        $count_variation                       = wp_count_posts('product_variation');
        $count_single                          = wp_count_posts('product');
        $published_single                      = $count_single->publish;
        $published_variation                   = $count_variation->publish;
        $published_products                    = $published_single + $published_variation;
        $project_data['nr_products']           = $published_products;
        $project_data['nr_products_processed'] = 0;
        $add_to_cron                           = Convpfm_UpdateProject::add_project_cron($project_data, 'donotdo');
        $batch_project                         = 'convpfm_batch_file_' . $project_data['project_hash'];
        if (!get_option($batch_project)) {
          // Batch project hook expects a multidimentional array.
          update_option($batch_project, $project_data, 'no');
          $final_creation = $this->convpfm_continue_batch($project_data['project_hash']);
        } else {
          $final_creation = $this->convpfm_continue_batch($project_data['project_hash']);
        }
        $google_detail = $this->get_convpfm_api_data();        
        $middleware_feed_data = array(
          "feed_id" => esc_sql(sanitize_text_field($project_data['project_hash'])),
          "store_id" => sanitize_text_field($google_detail['setting']->store_id),
          "customer_subscription_id" => sanitize_text_field($this->get_subscriptionId()),
          "file_name" => esc_sql(sanitize_text_field($project_data['filename'])),
          "file_type" => esc_sql(sanitize_text_field($project_data['fileformat'])),
          "channel_name" => esc_sql(sanitize_text_field($project_data['name'])),
          "country" => esc_sql(sanitize_text_field($project_data['countries'])),
        );
        if (!class_exists('Convpfm_CustomApi')) {
          require_once(CONVPFM_ENHANCAD_PLUGIN_DIR . 'includes/setup/convpfm-customApi.php');
        } 
        $customObj = new Convpfm_CustomApi();
        $middle_result = $customObj->export_feed_middleware($middleware_feed_data);
      }
      
      // Switch to determing what template to use during feed configuration.
      switch ($generate_step) {
        case 0:
          load_template(CONVPFM_ENHANCAD_PLUGIN_DIR . 'includes/setup/GenerateFile/class-generate-file.php');
          break;
        case 1:
          load_template(CONVPFM_ENHANCAD_PLUGIN_DIR . 'includes/setup/GenerateFile/class-category-mapping.php');
          break;
        case 4:
          load_template(CONVPFM_ENHANCAD_PLUGIN_DIR . 'includes/setup/GenerateFile/class-add-filter.php');
          break;
        case 7:
          load_template(CONVPFM_ENHANCAD_PLUGIN_DIR . 'includes/setup/GenerateFile/class-attribute-mapping.php');
          break;
        case 100:
          load_template(CONVPFM_ENHANCAD_PLUGIN_DIR . 'includes/setup/GenerateFile/class-manage-feed.php');
          break;
        case 101:
          load_template(CONVPFM_ENHANCAD_PLUGIN_DIR . 'includes/setup/GenerateFile/class-manage-feed.php');
          break;
        default:
          load_template(CONVPFM_ENHANCAD_PLUGIN_DIR . 'includes/setup/GenerateFile/class-manage-feed.php');
          break;
      }
    }

    public function convpfm_manage_file()
    {
      require_once(CONVPFM_ENHANCAD_PLUGIN_DIR . 'includes/setup/GenerateFile/class-manage-feed.php');
    }
    }
}
if (is_admin()) {
  new Convpfm_Admin();
}
