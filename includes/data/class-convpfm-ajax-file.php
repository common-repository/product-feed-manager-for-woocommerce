<?php

/**
 * TVC Ajax File Class.
 *
 * @package TVC Product Feed Manager/Data/Classes
 */
if (!defined('ABSPATH')) {
  exit;
}

if (!class_exists('Convpfm_Ajax_File')):
  /**
   * Ajax File Class
   */
  class Convpfm_Ajax_File extends Convpfm_Ajax_Calls
  {
    private $apiDomain;
    protected $access_token;
    protected $refresh_token;
    public function __construct()
    {
      parent::__construct();
      $this->apiDomain = CONVPFM_API_CALL_URL;
      // hooks      
      add_action('wp_ajax_convpfm_tvc_call_domain_claim', array($this, 'convpfm_tvc_call_domain_claim'));
      add_action('wp_ajax_convpfm_tvc_call_site_verified', array($this, 'convpfm_tvc_call_site_verified'));
      add_action('wp_ajax_convpfm_tvc_call_notice_dismiss', array($this, 'convpfm_tvc_call_notice_dismiss'));
      add_action('wp_ajax_convpfm_tvc_call_notice_dismiss_trigger', array($this, 'convpfm_tvc_call_notice_dismiss_trigger'));

      add_action('wp_ajax_convpfm_save_pixel_data', array($this, 'convpfm_save_pixel_data'));
      add_action('wp_ajax_convpfm_get_product_details_for_table', [$this, 'convpfm_get_product_details_for_table']);
      add_action('wp_ajax_convpfm_save_feed_data', [$this, 'convpfm_save_feed_data']);
      add_action('wp_ajax_convpfm_get_fb_catalog_data', array($this, 'convpfm_get_fb_catalog_data'));
      add_action('wp_ajax_convpfm_get_tiktok_user_catalogs', [$this, 'convpfm_get_tiktok_user_catalogs']);
      add_action('wp_ajax_convpfm_getCatalogId', [$this, 'convpfm_getCatalogId']);
      add_action('wp_ajax_convpfm_duplicate_feed_data_by_id', [$this, 'convpfm_duplicate_feed_data_by_id']);
      add_action('wp_ajax_convpfm_get_product_status', [$this, 'convpfm_get_product_status']);
      add_action('wp_ajax_convpfm_prepare_feed_to_sync', [$this, 'convpfm_prepare_feed_to_sync']);
      add_action('wp_ajax_convpfm_delete_feed_channel', [$this, 'convpfm_delete_feed_channel']);
      add_action('wp_ajax_convpfm_sync_single_product', [$this, 'convpfm_sync_single_product']);
      add_action('wp_ajax_convpfm_delete_feed_data_by_id', [$this, 'convpfm_delete_feed_data_by_id']);
      
      
      add_action('init_feed_wise_product_sync_process_scheduler_convpfm', [$this, 'convpfm_call_start_feed_wise_product_sync_process']);
      add_action('auto_feed_wise_product_sync_process_scheduler_convpfm', [$this, 'convpfm_call_auto_feed_wise_product_sync_process']);
      add_action('wp_ajax_convpfm_createPmaxCampaign', [$this, 'convpfm_createPmaxCampaign']);
      add_action('wp_ajax_convpfm_call_add_survey', array($this, 'convpfm_call_add_survey'));
      add_action('wp_ajax_convpfm_editPmaxCampaign', [$this, 'convpfm_editPmaxCampaign']);
      add_action('wp_ajax_convpfm_update_PmaxCampaign', [$this, 'convpfm_update_PmaxCampaign']);
      add_action('wp_ajax_convpfm_call_subscription_refresh', array($this, 'convpfm_call_subscription_refresh'));
      add_action('wp_ajax_convpfm_create_google_merchant_center_account', array($this,'convpfm_create_google_merchant_center_account') );

      add_action('wp_ajax_tvc_call_notification_dismiss', array($this, 'tvc_call_notification_dismiss'));
      add_action('wp_ajax_tvc_call_active_licence', array($this, 'tvc_call_active_licence'));

      add_action('wp_ajax_tvc_call_add_customer_feedback', array($this, 'tvc_call_add_customer_feedback'));
      // For new UIUX     
      
      add_action('wp_ajax_convpfm_get_tiktok_business_account', [$this, 'convpfm_get_tiktok_business_account']);
      
      // add_action('wp_ajax_tvc_call_add_customer_featurereq', array($this, 'tvc_call_add_customer_featurereq'));
      add_action('wp_ajax_get_user_businesses', array($this, 'get_user_businesses'));
      
    }


    // Save data in convpfm_options
    public function convpfm_save_data_eeoption($data)
    {
      $convpfm_options = unserialize(get_option('convpfm_options'));
      foreach ($data['conv_options_data'] as $key => $conv_options_data) {        
        $key_name = $key;        
        if (is_array($conv_options_data)) {
          $posted_arr = $conv_options_data;
          $posted_arr_temp = [];
          if (!empty($posted_arr)) {
            $arr = $posted_arr;
            array_walk($arr, function (&$value) {
              $value = sanitize_text_field($value);
            });
            $posted_arr_temp = $arr;
            $convpfm_options[$key_name] = $posted_arr_temp;
          }
        } else {
          $convpfm_options[$key_name] = sanitize_text_field($conv_options_data);
        }
      }
      update_option('convpfm_options', serialize($convpfm_options));
    }

    // Save data in convpfm_options
    public function convpfm_save_data_eeapidata($data)
    {
      $eeapidata = unserialize(get_option('convpfm_api_data'));
      $eeapidata_settings = $eeapidata['setting'];
      if (empty($eeapidata_settings)) {
        $eeapidata_settings = new stdClass();
      }

      foreach ($data['conv_options_data'] as $key => $conv_options_data) {        
        $key_name = $key;
        if (is_array($conv_options_data)) {
          $posted_arr = $conv_options_data;
          $posted_arr_temp = [];
          if (!empty($posted_arr)) {
            $arr = $posted_arr;
            array_walk($arr, function (&$value) {
              $value = sanitize_text_field($value);
            });
            $posted_arr_temp = $arr;
            $eeapidata_settings->$key_name = $posted_arr_temp;
          }
        } else {
          $eeapidata_settings->$key_name = sanitize_text_field($conv_options_data);
          if ($key_name == "google_merchant_center_id") {
            $eeapidata_settings->google_merchant_id = sanitize_text_field($conv_options_data);
          }
        }
      }
      $eeapidata['setting'] = $eeapidata_settings;
      update_option('convpfm_api_data', serialize($eeapidata));
    }

    //Save data in middleware
    public function convpfm_save_data_middleware($postDataFull = array())
    {
      $postData = $postDataFull['conv_options_data'];

      try {
        $url = $this->apiDomain . '/customer-subscriptions/update-detail';
        $header = array("Authorization: Bearer MTIzNA==", "Content-Type" => "application/json");
        $data = array();
        foreach ($postData as $key => $value) {
          $data[$key] = sanitize_text_field((isset($value)) ? $value : '');
        }

        $args = array(
          'headers' => $header,
          'method' => 'POST',
          'body' => wp_json_encode($data)
        );
        $result = wp_remote_request(esc_url_raw($url), $args);
      } catch (Exception $e) {
        return $e->getMessage();
      }
    }

    // All new functions for new UIUX
    public function convpfm_save_pixel_data()
    {
      if ($this->safe_ajax_call(sanitize_text_field(wp_unslash($_POST['pix_sav_nonce'])), 'pix_sav_nonce_val')) {
        if (in_array("eeoptions", $_POST['conv_options_type'])) {
          $this->convpfm_save_data_eeoption($_POST);
          $Convpfm_TVC_Admin_Helper = new Convpfm_TVC_Admin_Helper();
        }
        if (in_array("middleware", $_POST['conv_options_type'])) {
          $this->convpfm_save_data_middleware($_POST);
        }
        if (in_array("eeapidata", $_POST['conv_options_type'])) {
          if(isset($_POST['update_site_domain']) && $_POST['update_site_domain'] === 'update') {
            $_POST['conv_options_data']['is_site_verified'] = '0';
            $_POST['conv_options_data']['is_domain_claim'] = '0';
          }
          $this->convpfm_save_data_eeapidata($_POST);
        }
        if (in_array("tiktokmiddleware", $_POST['conv_options_type'])) {
          $this->convpfm_save_tiktokmiddleware($_POST);
        }
        if (in_array("tiktokcatalog", $_POST['conv_options_type'])) {
          $this->convpfm_save_tiktokcatalog($_POST);
        }

        if (in_array("facebookmiddleware", $_POST['conv_options_type'])) {
          $this->convpfm_save_facebookmiddleware($_POST);
        }
        if (in_array("facebookcatalog", $_POST['conv_options_type'])) {
          $this->convpfm_save_facebookcatalog($_POST);
        }
        if (isset($_POST['conv_options_data']['ga_GMC']) && $_POST['conv_options_data']['ga_GMC'] == '1') {
          $access_token = $this->get_tvc_access_token();
          $refresh_token = $this->get_tvc_refresh_token();
          $api_obj = new Convpfm_Conversios_Onboarding_ApiCall(sanitize_text_field($access_token), sanitize_text_field($refresh_token));
          $postData = ['subscription_id' => $_POST['conv_options_data']['subscription_id'], 'merchant_id' => $_POST['conv_options_data']['merchant_id'], 'account_id' => $_POST['conv_options_data']['google_merchant_id'], 'adwords_id' => $_POST['conv_options_data']['google_ads_id']];
          $api_obj->linkGoogleAdsToMerchantCenter($postData);
        }        
        $Convpfm_TVC_Admin_Helper->update_app_status();
        echo "1";
      } else {
        echo "0";
      }
      exit;
    }
    
    public function tvc_call_add_customer_feedback()
    {
      if (wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['conv_customer_feed_nonce_field'])), 'conv_customer_feed_nonce_field_save')) {
        if (isset($_POST['que_one']) && isset($_POST['que_two']) && isset($_POST['que_three'])) {
          $formdata = array();
          $formdata['business_insights_index'] = sanitize_text_field($_POST['que_one']);
          $formdata['automate_integrations_index'] = sanitize_text_field($_POST['que_two']);
          $formdata['business_scalability_index'] = sanitize_text_field($_POST['que_three']);
          $formdata['subscription_id'] = isset($_POST['subscription_id']) ? sanitize_text_field($_POST['subscription_id']) : "";
          $formdata['customer_id'] = isset($_POST['customer_id']) ? sanitize_text_field($_POST['customer_id']) : "";
          $formdata['feedback'] = isset($_POST['feedback_description']) ? sanitize_text_field($_POST['feedback_description']) : "";
          $customObj = new Convpfm_CustomApi();
          unset($_POST['action']);
          echo json_encode($customObj->record_customer_feedback($formdata));
          exit;
        } else {
          echo json_encode(array("error" => true, "message" => esc_html__("Please answer the required questions", "product-feed-manager-for-woocommerce")));
        }
      } else {
        echo json_encode(array("error" => true, "message" => esc_html__("Admin security nonce is not verified.", "product-feed-manager-for-woocommerce")));
      }
      // IMPORTANT: don't forget to exit
      exit;
    }
    public function convpfm_call_add_survey()
    {
      if (is_admin() && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['tvc_call_add_survey'])), 'tvc_call_add_survey-nonce')) {
        if (!class_exists('Convpfm_CustomApi')) {
          include(ENHANCAD_PLUGIN_DIR . 'includes/setup/convpfm-customApi.php');
        }
        $customObj = new Convpfm_CustomApi();
        unset($_POST['action']);
        $subscription_id = isset($_POST['subscription_id']) ? sanitize_text_field($_POST['subscription_id']) : "";
        $customer_id = isset($_POST['customer_id']) ? sanitize_text_field($_POST['customer_id']) : "";
        $radio_option_val = isset($_POST['radio_option_val']) ? sanitize_text_field($_POST['radio_option_val']) : "";
        $other_reason = isset($_POST['other_reason']) ? sanitize_text_field($_POST['other_reason']) : "";
        $site_url = isset($_POST['site_url']) ? sanitize_text_field($_POST['site_url']) : "";
        $plugin_name = isset($_POST['plugin_name']) ? sanitize_text_field($_POST['plugin_name']) : "";

        $post = array(
          "customer_id" => $customer_id,
          "subscription_id" => $subscription_id,
          "radio_option_val" => $radio_option_val,
          "other_reason" => $other_reason,
          "site_url" => $site_url,
          "plugin_name" => $plugin_name
        );
        echo wp_json_encode($customObj->add_survey_of_deactivate_plugin($post));
      } else {
        echo wp_json_encode(array('error' => true, "is_connect" => false, 'message' => esc_html__("Admin security nonce is not verified.", "product-feed-manager-for-woocommerce")));
      }
      // IMPORTANT: don't forget to exit
      exit;
    }
    //active licence key
    public function tvc_call_active_licence()
    {
      if (is_admin() && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['conv_licence_nonce'])), 'conv_lic_nonce')) {
        $licence_key = isset($_POST['licence_key']) ? sanitize_text_field($_POST['licence_key']) : "";
        $Convpfm_TVC_Admin_Helper = new Convpfm_TVC_Admin_Helper();
        $subscription_id = $Convpfm_TVC_Admin_Helper->get_subscriptionId();
        if ($subscription_id != "" && $licence_key != "") {
          $response = $Convpfm_TVC_Admin_Helper->active_licence($licence_key, $subscription_id);

          if ($response->error == false) {
            $Convpfm_TVC_Admin_Helper->update_subscription_details_api_to_db();
            echo json_encode(array('error' => false, "is_connect" => true, 'message' => esc_html__("The licence key has been activated.", "product-feed-manager-for-woocommerce")));
          } else {
            echo json_encode(array('error' => true, "is_connect" => true, 'message' => $response->message));
          }
        } else if ($licence_key != "") {
          $convpfm_additional_data = $Convpfm_TVC_Admin_Helper->get_convpfm_additional_data();
          $convpfm_additional_data['temp_active_licence_key'] = $licence_key;
          $Convpfm_TVC_Admin_Helper->set_convpfm_additional_data($convpfm_additional_data);
          echo json_encode(array('error' => true, "is_connect" => false, 'message' => ""));
        } else {
          echo json_encode(array('error' => true, "is_connect" => false, 'message' => esc_html__("Licence key is required.", "product-feed-manager-for-woocommerce")));
        }
      } else {
        echo json_encode(array('error' => true, "is_connect" => false, 'message' => esc_html__("Admin security nonce is not verified.", "product-feed-manager-for-woocommerce")));
      }
      exit;
    }
    
    public function tvc_call_notification_dismiss()
    {
      if ($this->safe_ajax_call(sanitize_text_field(wp_unslash($_POST['TVCNonce'])), 'tvc_call_notification_dismiss-nonce')) {
        $convpfm_dismiss_id = isset($_POST['data']['convpfm_dismiss_id']) ? sanitize_text_field($_POST['data']['convpfm_dismiss_id']) : "";
        if ($convpfm_dismiss_id != "") {
          $Convpfm_TVC_Admin_Helper = new Convpfm_TVC_Admin_Helper();
          $convpfm_msg_list = $Convpfm_TVC_Admin_Helper->get_convpfm_msg_nofification_list();
          if (isset($convpfm_msg_list[$convpfm_dismiss_id])) {
            unset($convpfm_msg_list[$convpfm_dismiss_id]);
            $convpfm_msg_list[$convpfm_dismiss_id]["active"] = 0;
            $Convpfm_TVC_Admin_Helper->set_convpfm_msg_nofification_list($convpfm_msg_list);
            echo json_encode(array('status' => 'success', 'message' => ""));
          }
        }
      } else {
        echo json_encode(array('status' => 'error', "message" => esc_html__("Admin security nonce is not verified.", "product-feed-manager-for-woocommerce")));
      }
      // IMPORTANT: don't forget to exit
      exit;
    }
    public function convpfm_tvc_call_notice_dismiss()
    {
      if ($this->safe_ajax_call(sanitize_text_field(wp_unslash($_POST['apiNoticDismissNonce'])), 'tvc_call_notice_dismiss-nonce')) {
        $convpfm_notice_dismiss_id = isset($_POST['data']['convpfm_notice_dismiss_id']) ? sanitize_text_field($_POST['data']['convpfm_notice_dismiss_id']) : "";
        $convpfm_notice_dismiss_id = sanitize_text_field($convpfm_notice_dismiss_id);
        if ($convpfm_notice_dismiss_id != "") {
          $Convpfm_TVC_Admin_Helper = new Convpfm_TVC_Admin_Helper();
          $convpfm_additional_data = $Convpfm_TVC_Admin_Helper->get_convpfm_additional_data();
          $convpfm_additional_data['dismissed_' . $convpfm_notice_dismiss_id] = 1;
          $Convpfm_TVC_Admin_Helper->set_convpfm_additional_data($convpfm_additional_data);
          echo json_encode(array('status' => 'success', 'message' => $convpfm_additional_data));
        }
      } else {
        echo json_encode(array('status' => 'error', "message" => esc_html__("Admin security nonce is not verified.", "product-feed-manager-for-woocommerce")));
      }
      // IMPORTANT: don't forget to exit
      exit;
    }

    public function tvc_call_notice_dismiss_trigger()
    {
      if ($this->safe_ajax_call(sanitize_text_field(wp_unslash(filter_input(INPUT_POST, 'apiNoticDismissNonce'))), 'tvc_call_notice_dismiss-nonce')) {
        $convpfm_notice_dismiss_id_trigger = isset($_POST['data']['convpfm_notice_dismiss_id_trigger']) ? sanitize_text_field($_POST['data']['convpfm_notice_dismiss_id_trigger']) : "";
        $convpfm_notice_dismiss_id_trigger = sanitize_text_field($convpfm_notice_dismiss_id_trigger);
        if ($convpfm_notice_dismiss_id_trigger != "") {
          $Convpfm_TVC_Admin_Helper = new Convpfm_TVC_Admin_Helper();
          $convpfm_additional_data = $Convpfm_TVC_Admin_Helper->get_convpfm_additional_data();
          $slug = $convpfm_notice_dismiss_id_trigger;
          $title = "";
          $content = "";
          $status = "0";
          $Convpfm_TVC_Admin_Helper->convpfm_dismiss_admin_notice($slug, $content, $status, $title);
        }
      } else {
        echo json_encode(array('status' => 'error', "message" => esc_html__("Admin security nonce is not verified.", "product-feed-manager-for-woocommerce")));
      }
      // IMPORTANT: don't forget to exit
      exit;
    }
    
    public function convpfm_tvc_call_site_verified()
    {
      if ($this->safe_ajax_call(sanitize_text_field(wp_unslash(filter_input(INPUT_POST, 'SiteVerifiedNonce'))), 'tvc_call_site_verified-nonce')) {
        $Convpfm_TVC_Admin_Helper = new Convpfm_TVC_Admin_Helper();
        $tvc_rs = [];
        $tvc_rs = $Convpfm_TVC_Admin_Helper->call_site_verified();
        if (isset($tvc_rs['error']) && $tvc_rs['error'] == 1) {
          echo json_encode(array('status' => 'error', 'message' => sanitize_text_field($tvc_rs['msg'])));
        } else {
          echo json_encode(array('status' => 'success', 'message' => sanitize_text_field($tvc_rs['msg'])));
        }
        exit;
      } else {
        echo json_encode(array('status' => 'error', "message" => esc_html__("Admin security nonce is not verified.", "product-feed-manager-for-woocommerce")));
        exit;
      }
    }
    public function convpfm_tvc_call_domain_claim()
    {
      if ($this->safe_ajax_call(sanitize_text_field(wp_unslash(filter_input(INPUT_POST, 'apiDomainClaimNonce'))), 'tvc_call_domain_claim-nonce')) {
        $Convpfm_TVC_Admin_Helper = new Convpfm_TVC_Admin_Helper();
        $tvc_rs = $Convpfm_TVC_Admin_Helper->call_domain_claim();
        if (isset($tvc_rs['error']) && $tvc_rs['error'] == 1) {
          echo json_encode(array('status' => 'error', 'message' => sanitize_text_field($tvc_rs['msg'])));
        } else {
          echo json_encode(array('status' => 'success', 'message' => sanitize_text_field($tvc_rs['msg'])));
        }
        exit;
      } else {
        echo json_encode(array('status' => 'error', "message" => esc_html__("Admin security nonce is not verified.", "product-feed-manager-for-woocommerce")));
        exit;
      }
    }
    public function get_tvc_access_token()
    {
      if (!empty($this->access_token)) {
        return $this->access_token;
      } else {
        $Convpfm_TVC_Admin_Helper = new Convpfm_TVC_Admin_Helper();
        $google_detail = $Convpfm_TVC_Admin_Helper->get_convpfm_api_data();
        $this->access_token = sanitize_text_field(base64_decode($google_detail['setting']->access_token));
        return $this->access_token;
      }
    }

    public function get_tvc_refresh_token()
    {
      if (!empty($this->refresh_token)) {
        return $this->refresh_token;
      } else {
        $Convpfm_TVC_Admin_Helper = new Convpfm_TVC_Admin_Helper();
        $google_detail = $Convpfm_TVC_Admin_Helper->get_convpfm_api_data();
        $this->refresh_token = sanitize_text_field(base64_decode($google_detail['setting']->refresh_token));
        return $this->refresh_token;
      }
    }

    /**
     * function to get Product status by feed_id
     * Hook used wp_ajax_convpfm_get_product_status
     * Request Post
     * API call to get product status
     */
    public function convpfm_get_product_status()
    {
      if ($this->safe_ajax_call(sanitize_text_field(wp_unslash(filter_input(INPUT_POST, 'conv_licence_nonce'))), 'conv_licence-nonce')) {
        $Convpfm_TVC_Admin_Helper = new Convpfm_TVC_Admin_Helper();
        $google_detail = $Convpfm_TVC_Admin_Helper->get_convpfm_api_data();
        //$merchantId = $Convpfm_TVC_Admin_Helper->get_merchantId();
        $data = array(
          "store_id" => $google_detail['setting']->store_id,
          "subscription_id" => $google_detail['setting']->id,
          "store_feed_id" => sanitize_text_field($_POST['feed_id']),
          "product_ids" => sanitize_text_field($_POST['product_list']),
          "channel" => sanitize_text_field($_POST['channel_id']),
          "merchant_id" => $google_detail['setting']->google_merchant_id,
          "catalog_id" => sanitize_text_field($_POST['catalog_id']),
          "tiktok_business_id" => sanitize_text_field($_POST['tiktok_business_id']),
          "tiktok_catalog_id" => sanitize_text_field($_POST['tiktok_catalog_id']),
        );
        $CustomApi = new Convpfm_CustomApi();
        $response = $CustomApi->getProductStatusByChannelId($data);
        if (isset($response->errors)) {
          echo wp_json_encode($response->errors = 'Product does not exists');
        } else {
          echo wp_json_encode(isset($response->data->products) ? $response->data->products : 'Product not synced');
        }
      } else {
        echo wp_json_encode(array("error" => true, "message" => esc_html__("Admin security nonce is not verified.", "product-feed-manager-for-woocommerce")));
      }
      exit;
    }

    /**
     * function to Save and Update Feed data
     * Hook used wp_ajax_convpfm_save_feed_data
     * Request Post
     * DB used convpfm_product_feed
     * Schedule cron set_recurring_auto_sync_product_feed_wise on update for conditions
     */
    public function convpfm_save_feed_data()
    {
      if ($this->safe_ajax_call(sanitize_text_field(wp_unslash(filter_input(INPUT_POST, 'conv_save_feed'))), 'conv_save_feed_nonce')) { 
        $cat_data = isset($_POST['cat_data']) ? $_POST['cat_data'] : "";
        parse_str($cat_data, $formArrayCat);
        if (!empty($formArrayCat)) {
          foreach ($formArrayCat as $key => $value) {
            $formArrayCat[$key] = $value;
          }          
          foreach ($formArrayCat as $key => $value) {
            if (preg_match("/^category-name-/i", $key)) {
              if ($value != '') {
                $keyArray = explode("name-", $key);
                $mappedCatsDB[$keyArray[1]]['name'] = sanitize_text_field($value);
              }
              unset($formArrayCat[$key]);
            } else if (preg_match("/^category-/i", $key)) {
              if ($value != '' && $value > 0) {
                $keyArray = explode("-", $key);
                $mappedCats[$keyArray[1]] = $value;
                $mappedCatsDB[$keyArray[1]]['id'] = sanitize_text_field($value);
              }
              unset($formArrayCat[$key]);
            }
          }    
          update_option("convpfm_prod_mapped_cats", serialize($mappedCatsDB));
        }
        
        $attr_data = isset($_POST['attr_data']) ? $_POST['attr_data'] : "";
        parse_str($attr_data, $formArrayAttr);
        if (!empty($formArrayAttr)) {
          foreach ($formArrayAttr as $key => $value) {
            if ($key == 'additional_attr_') {
              $additional_attr = $value;
              unset($formArrayAttr['additional_attr_']);
            }
            if ($key == 'additional_attr_value_') {
              $additional_attr_value = $value;
              unset($formArrayAttr['additional_attr_value_']);
            }
            if (is_array($value) !== 1) {
              $formArrayAttr[$key] = sanitize_text_field($value);
            }
          }
          unset($formArrayAttr['additional_attr_']);
          unset($formArrayAttr['additional_attr_value_']);
          if (isset($additional_attr)) {
            foreach ($additional_attr as $key => $value) {
              $formArrayAttr[$value] = $additional_attr_value[$key];
            }
          }
          foreach ($formArrayAttr as $key => $value) {
            $mappedAttrs[$key] = sanitize_text_field($value);
          }
          
          //If additional_attr_value_ 
          unset($mappedAttrs['additional_attr_value_']);
          update_option("convpfm_prod_mapped_attrs", serialize($mappedAttrs));          
        }
        $tiktok_catalog_id = '';
        if(isset($_POST['channel']) == 3) {
          $tiktok_catalog_id = sanitize_text_field($_POST['tiktok_catalog_id']);
        }
        if (isset($_POST['tiktok_catalog_id']) === TRUE && $_POST['tiktok_catalog_id'] === 'Create New') {
          $getCountris = @file_get_contents(CONVPFM_ENHANCAD_PLUGIN_DIR . "includes/setup/json/countries_currency.json");
          $contData = json_decode($getCountris);
          $currency_code = '';
          foreach ($contData as $key => $data) {
            if ($data->countryCode === $_POST['target_country']) {
              $currency_code = $data->currencyCode;
            }
          }

          $customer['customer_subscription_id'] = sanitize_text_field($_POST['customer_subscription_id']);
          $customer['business_id'] = sanitize_text_field($_POST['tiktok_business_account']);
          $customer['catalog_name'] = sanitize_text_field($_POST['feedName']);
          $customer['region_code'] = sanitize_text_field($_POST['target_country']);
          $customer['currency'] = $currency_code;
          $customObj = new Convpfm_CustomApi();
          $result = $customObj->createCatalogs($customer);
          if (isset($result->error_data) === TRUE) {
            foreach ($result->error_data as $key => $value) {
              echo json_encode(array("error" => true, "message" => $value->errors[0], "errorType" => "tiktok"));
              exit;
            }
          }

          if (isset($result->status) === TRUE && $result->status === 200) {
            $tiktok_catalog_id = $result->data->catalog_id;
            $values = array();
            $place_holders = array();
            global $wpdb;
            $convpfm_tiktok_catalog = esc_sql($wpdb->prefix . "convpfm_tiktok_catalog");            
            array_push($values, esc_sql($_POST['target_country']), esc_sql($tiktok_catalog_id), esc_sql($_POST['feedName']), date('Y-m-d H:i:s', current_time('timestamp')));
            $place_holders[] = "('%s', '%s', '%s','%s')";
            $query = "INSERT INTO `$convpfm_tiktok_catalog` (country, catalog_id, catalog_name, created_date) VALUES ";
            $query .= implode(', ', $place_holders);
            $wpdb->query($wpdb->prepare($query, $values));

          }
        }
        $Convpfm_Admin_DB_Helper = new Convpfm_Admin_DB_Helper();
        if (isset($_POST['edit']) && $_POST['edit'] != '') {
          $next_schedule_date = NULL;
          as_unschedule_all_actions('init_feed_wise_product_sync_process_scheduler_convpfm', array("feedId" => $_POST['edit']));
          if ($_POST['autoSync'] != 0 && $_POST['is_mapping_update'] == 1) {
            $last_sync_date = $_POST['last_sync_date'];
            $next_schedule_date = date('Y-m-d H:i:s', strtotime('+' . $_POST['syncInterval'] . 'day', strtotime($last_sync_date)));
            // add scheduled cron job
            $time_space = strtotime($_POST['syncInterval'] . " days", 0);
            $timestamp = strtotime($_POST['syncInterval'] . " days");
            as_schedule_recurring_action(esc_attr($timestamp), esc_attr($time_space), 'init_feed_wise_product_sync_process_scheduler_convpfm', array("feedId" => $_POST['edit']), "product_sync");
          }
          $profile_data = array(
            'feed_name' => esc_sql($_POST['feedName']),
            'channel_ids' => esc_sql($_POST['channel']),
            'auto_sync_interval' => esc_sql($_POST['syncInterval']),
            'auto_schedule' => esc_sql($_POST['autoSync']),
            'updated_date' => esc_sql(date('Y-m-d H:i:s', current_time('timestamp'))),
            'next_schedule_date' => $next_schedule_date,
            'target_country' => esc_sql($_POST['target_country']),
            'tiktok_catalog_id' => esc_sql($tiktok_catalog_id),
            'product_sync_batch_size' => esc_sql($_POST['batchsize']),   
            'product_id_prefix' => esc_sql($_POST['product_id_prefix']), 
            'categories' => wp_json_encode($mappedCatsDB),
            'attributes' => wp_json_encode($mappedAttrs),    
          );

          $Convpfm_Admin_DB_Helper->tvc_update_row("convpfm_product_feed", $profile_data, array("id" => $_POST['edit']));
          $result = array(
            'id' => sanitize_text_field($_POST['edit']),
          );
          echo wp_json_encode($result);
          exit;
        } else {
          $profile_data = array(
            'feed_name' => esc_sql($_POST['feedName']),
            'channel_ids' => esc_sql($_POST['channel']),
            'auto_sync_interval' => esc_sql($_POST['syncInterval']),
            'auto_schedule' => esc_sql($_POST['autoSync']),
            'created_date' => esc_sql(date('Y-m-d H:i:s', current_time('timestamp'))),
            'status' => $_POST['channel'] == '1' ? esc_sql('Draft') : '',
            'target_country' => esc_sql($_POST['target_country']),
            'tiktok_catalog_id' => esc_sql($tiktok_catalog_id),
            'tiktok_status' => $_POST['channel'] == '3' ? esc_sql('Draft') : '',
            'fb_status' => $_POST['channel'] == '2' ? esc_sql('Draft') : '',
            'product_sync_batch_size' => esc_sql($_POST['batchsize']),
            'product_id_prefix' => esc_sql($_POST['product_id_prefix']), 
            'categories' => wp_json_encode($mappedCatsDB),
            'attributes' => wp_json_encode($mappedAttrs),

          );    
          $Convpfm_Admin_DB_Helper->tvc_add_row("convpfm_product_feed", $profile_data, array("%s", "%s", "%s", "%d", "%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s"));
          $result = $Convpfm_Admin_DB_Helper->tvc_get_last_row("convpfm_product_feed", array("id"));
          echo wp_json_encode($result);
          exit;          
        }
      } else {
        echo wp_json_encode(array("error" => true, "message" => esc_html__("Admin security nonce is not verified.", "product-feed-manager-for-woocommerce")));
        exit;
      }      
    }

    /**
     * function to Duplicate Feed data by id
     * Hook used wp_ajax_convpfm_duplicate_feed_data_by_id
     * Request Post
     * DB used convpfm_product_feed
     */
    public function convpfm_duplicate_feed_data_by_id()
    {
      if ($this->safe_ajax_call(sanitize_text_field(wp_unslash(filter_input(INPUT_POST, 'conv_onboarding_nonce'))), 'conv_onboarding_nonce')) {
        $Convpfm_Admin_DB_Helper = new Convpfm_Admin_DB_Helper();
        $where = '`id` = ' . esc_sql($_POST['id']);
        $filed = array(
          'feed_name',
          'channel_ids',
          'auto_sync_interval',
          'auto_schedule',
          'categories',
          'attributes',
          'filters',
          'include_product',
          'exclude_product',
          'total_product',
          'target_country',
          'tiktok_catalog_id',
        );
        $result = $Convpfm_Admin_DB_Helper->tvc_get_results_in_array("convpfm_product_feed", $where, $filed);
        $profile_data = array(
          'feed_name' => esc_sql('Copy of - ' . $result[0]['feed_name']),
          'channel_ids' => esc_sql($result[0]['channel_ids']),
          'auto_sync_interval' => esc_sql($result[0]['auto_sync_interval']),
          'auto_schedule' => esc_sql($result[0]['auto_schedule']),
          'categories' => stripslashes($result[0]['categories']),
          'attributes' => stripslashes($result[0]['attributes']),
          'filters' => stripslashes($result[0]['filters']),
          'include_product' => esc_sql($result[0]['include_product']),
          'exclude_product' => esc_sql($result[0]['exclude_product']),
          'created_date' => esc_sql(date('Y-m-d H:i:s', current_time('timestamp'))),
          'status' => strpos($result[0]['channel_ids'], '1') !== false ? esc_sql('Draft') : '',
          'target_country' => esc_sql($result[0]['target_country']),
          'tiktok_catalog_id' => esc_sql($result[0]['tiktok_catalog_id']),
          'tiktok_status' => strpos($result[0]['channel_ids'], '3') !== false ? esc_sql('Draft') : '',
          'fb_status' => strpos($result[0]['channel_ids'], '2') !== false ? esc_sql('Draft') : '',
        );

        $Convpfm_Admin_DB_Helper->tvc_add_row("convpfm_product_feed", $profile_data, array("%s", "%s", "%s", "%d", "%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s"));
        echo wp_json_encode(array("error" => false, "message" => esc_html__("Dupliacte Feed created successfully", "product-feed-manager-for-woocommerce")));

      } else {
        echo wp_json_encode(array("error" => true, "message" => esc_html__("Admin security nonce is not verified.", "product-feed-manager-for-woocommerce")));
      }
      exit;

    }

    /**
     * function to Delete Feed and product from GMC
     * Hook used wp_ajax_convpfm_delete_feed_data_by_id
     * Request Post
     * DB used convpfm_product_feed
     * Delete by id
     * Unschedule set_recurring_auto_sync_product_feed_wise cron 
     * Api Call to delete product from GMC 
     */
    public function convpfm_delete_feed_data_by_id()
    {
      if ($this->safe_ajax_call(sanitize_text_field(wp_unslash(filter_input(INPUT_POST, 'conv_onboarding_nonce'))), 'conv_onboarding_nonce')) {
        $Convpfm_Admin_DB_Helper = new Convpfm_Admin_DB_Helper();        
        as_unschedule_all_actions('init_feed_wise_product_sync_process_scheduler_convpfm', array("feedId" => $_POST['id']));
        /**
         * Api call to delete GMC product
         */
        $Convpfm_TVC_Admin_Helper = new Convpfm_TVC_Admin_Helper();
        $google_detail = $Convpfm_TVC_Admin_Helper->get_convpfm_api_data();
        $merchantId = $Convpfm_TVC_Admin_Helper->get_merchantId();
        $data = array(
          "merchant_id" => $merchantId,
          "store_id" => $google_detail['setting']->store_id,
          "store_feed_id" => sanitize_text_field($_POST['id']),
          "product_ids" => ''
        );
        $CustomApi = new Convpfm_CustomApi();
        $response = $CustomApi->delete_from_channels($data);
        $Convpfm_TVC_Admin_Helper->plugin_log("Convpfm - Delete Feed from GMC" . wp_json_encode($response), 'product_sync');
        $soft_delete_id = array('status' => 'Deleted', 'is_delete' => esc_sql(1), 'auto_schedule' => 0);
        $Convpfm_Admin_DB_Helper->tvc_update_row("convpfm_product_feed", $soft_delete_id, array("id" => $_POST['id']));
        echo wp_json_encode(array("error" => false, "message" => esc_html__("Success", "", "Feed Deleted Successfully.")));

      } else {
        echo wp_json_encode(array("error" => true, "message" => esc_html__("Admin security nonce is not verified.", "product-feed-manager-for-woocommerce")));
      }
      exit;
    }

    /**
     * function to delete Product by product id from GMC
     * Hook used wp_ajax_convpfm_delete_feed_channel
     * DB used convpfm_product_feed
     * Request Post product id and feedId
     * Api Call to delete product from GMC
     */
    public function convpfm_delete_feed_channel()
    {
      if ($this->safe_ajax_call(sanitize_text_field(wp_unslash(filter_input(INPUT_POST, 'conv_onboarding_nonce'))), 'conv_onboarding_nonce')) {
        $Convpfm_Admin_DB_Helper = new Convpfm_Admin_DB_Helper();
        $where = '`id` = ' . esc_sql($_POST['feed_id']);
        $filed = array('exclude_product', 'status', 'include_product', 'total_product', 'product_id_prefix');
        $result = $Convpfm_Admin_DB_Helper->tvc_get_results_in_array("convpfm_product_feed", $where, $filed);
        $totProdRem = $result[0]['total_product'] - 1;
        if ($result[0]['exclude_product'] != '' && $_POST['product_ids'] != '') {
          $allExclude = $result[0]['exclude_product'] . ',' . trim(str_replace($result[0]['product_id_prefix'],'',$_POST['product_ids']));
          $profile_data = array(
            'exclude_product' => esc_sql($allExclude),
            'total_product' => $totProdRem >= 0 ? $totProdRem : 0,
          );
          $Convpfm_Admin_DB_Helper->tvc_update_row("convpfm_product_feed", $profile_data, array("id" => $_POST['feed_id']));
        } else if ($result[0]['include_product'] != '' && $_POST['product_ids'] != '') {
          $include_product = explode(',', $result[0]['include_product']);
          if (($key = array_search(trim(str_replace($result[0]['product_id_prefix'],'',$_POST['product_ids'])), $include_product)) !== false) {
            unset($include_product[$key]);
          }
          $all_include = implode(',', $include_product);
          $profile_data = array(
            'include_product' => esc_sql($all_include),
            'total_product' => $totProdRem >= 0 ? $totProdRem : 0,
          );
          $Convpfm_Admin_DB_Helper->tvc_update_row("convpfm_product_feed", $profile_data, array("id" => sanitize_text_field($_POST['feed_id'])));
        } else {
          $profile_data = array(
            'exclude_product' => esc_sql(trim(str_replace($result[0]['product_id_prefix'],'',sanitize_text_field($_POST['product_ids'])))),
            'total_product' => $totProdRem >= 0 ? $totProdRem : 0,
          );
          $Convpfm_Admin_DB_Helper->tvc_update_row("convpfm_product_feed", $profile_data, array("id" => sanitize_text_field($_POST['feed_id'])));
        }
        if($totProdRem <= 0) {
          as_unschedule_all_actions('init_feed_wise_product_sync_process_scheduler_convpfm', array("feedId" => sanitize_text_field($_POST['feed_id'])));
        }
        $CONV_Admin_Helper = new Convpfm_TVC_Admin_Helper();
        $google_detail = $CONV_Admin_Helper->get_convpfm_api_data();
        $merchantId = $CONV_Admin_Helper->get_merchantId();
        $data = array(
          "merchant_id" => $merchantId,
          "store_id" => $google_detail['setting']->store_id,
          "store_feed_id" => sanitize_text_field($_POST['feed_id']),
          "product_ids" => sanitize_text_field($_POST['product_ids'])
        );
        /**
         * Api Call to delete product from GMC
         */
        $convCustomApi = new Convpfm_CustomApi();
        $response = $convCustomApi->delete_from_channels($data);
        echo wp_json_encode($response);
        exit;

      } else {
        echo wp_json_encode(array("error" => true, "message" => esc_html__("Admin security nonce is not verified.", "product-feed-manager-for-woocommerce")));
      }
      exit;
    }

    /**
     * function to show Feed wise woocommerce product data
     * Hook used wp_ajax_convpfm_get_product_details_for_table
     * Request Post
     * DB used Woo commerce db
     */
    public function convpfm_get_product_details_for_table()
    {
      if ($this->safe_ajax_call(sanitize_text_field(wp_unslash($_POST['product_details_nonce'])), 'conv_product_details-nonce')) {
        $products_per_page = isset($_POST['start']) ? sanitize_text_field(absint($_POST['length'])) : 10;
        $page_number = isset($_POST['start']) ? sanitize_text_field(absint($_POST['start'])) : 1;
        $search = isset($_POST['searchName']) ? sanitize_text_field($_POST['searchName']) : '';
        $productSearch = explode(',', sanitize_text_field($_POST['productData']));
        $conditionSearch = explode(',', sanitize_text_field($_POST['conditionData']));
        $valueSearch = explode(',', sanitize_text_field($_POST['valueData']));
        $in_category_ids = array();
        $not_in_category_ids = array();
        $stock_status_to_fetch = array();
        $not_stock_status_to_fetch = $product_ids_to_exclude = $product_ids_to_include = array();
        $main_image_ids = array();

        foreach ($productSearch as $key => $value) {
          switch ($value) {
            case 'product_cat':
              if ($conditionSearch[$key] == "=") {
                array_push($in_category_ids, $valueSearch[$key]);
              } else if ($conditionSearch[$key] == "!=") {
                array_push($not_in_category_ids, $valueSearch[$key]);
              }
              break;
            case '_stock_status':
              if (!empty($conditionSearch[$key]) && $conditionSearch[$key] == "=") {
                array_push($stock_status_to_fetch, $valueSearch[$key]);
              } else if (!empty($conditionSearch[$key]) && $conditionSearch[$key] == "!=") {
                array_push($not_stock_status_to_fetch, $valueSearch[$key]);
              }
              break;
            case 'main_image':
              if (!empty($conditionSearch[$key]) && $conditionSearch[$key] == "=") {
                array_push($main_image_ids, $valueSearch[$key]);
             }
              break;
            case 'ID':
              if ($conditionSearch[$key] == "=") {
                array_push($product_ids_to_include, $valueSearch[$key]);
              } else if ($conditionSearch[$key] == "!=") {
                array_push($product_ids_to_exclude, $valueSearch[$key]);
              }
              break;
          }
        }
        $tax_query = array();
        if (!empty($in_category_ids)) {
          $tax_query[] = array(
            'taxonomy' => 'product_cat',
            'field'    => 'term_id',
            'terms'    => $in_category_ids,
            'operator' => 'IN', // Retrieve products in any of the specified categories
          );
        }
        if (!empty($not_in_category_ids)) {
          $tax_query[] = array(
            'taxonomy' => 'product_cat',
            'field'    => 'term_id',
            'terms'    => $not_in_category_ids,
            'operator' => 'NOT IN', // Exclude products in any of the specified categories
          );
        }
        if (!empty($in_category_ids) && !empty($not_in_category_ids)) {
          $tax_query = array('relation' => 'AND');
        }
        $meta_query = array();
        if (!empty($stock_status_to_fetch)) {
          $meta_query[] = array(
            'key'     => '_stock_status',
            'value'   => $stock_status_to_fetch,
            'compare' => 'IN', // Include products with these stock statuses
          );
        }

        // Add not_stock_status_to_fetch condition
        if (!empty($not_stock_status_to_fetch)) {
          $meta_query[] = array(
            'key'     => '_stock_status',
            'value'   => $not_stock_status_to_fetch,
            'compare' => 'NOT IN', // Exclude products with these stock statuses
          );
        }

        if (!empty($main_image_ids)) {
          $meta_query[] = [
            'key'     => '_thumbnail_id',
            'compare' => 'EXISTS', // Include products with a main image
          ];
        }

        if (!empty($stock_status_to_fetch) && !empty($not_stock_status_to_fetch)) {
          $meta_query = array('relation' => 'AND');
        }
        if ($_POST['productData'] == "") {
          $pagination_count = (new WP_Query(['post_type' => 'product', 'post_status' => 'publish', 's' => $search]))->found_posts;
          wp_reset_query();
        } else {
          $args = array(
            'post_type'      => 'product',
            'post_status'    => 'publish',
            's'              => $search,
            'tax_query'      => $tax_query, // Dynamic tax query
            'meta_query'     => $meta_query,
            'post__not_in'   => $product_ids_to_exclude,
            'post__in'       => $product_ids_to_include,
          );

          $pagination_count  = (new WP_Query($args))->found_posts;
          wp_reset_query();
        }
        $p_id = sanitize_text_field($_POST['p_id']);
        $args = array(
          'post_type'      => 'product',
          'posts_per_page' => $products_per_page,
          'post_status'    => 'publish',
          'offset'         => $page_number,
          'orderby'        => 'ID',
          'order'          => 'DESC',
          's'              => $search,
          'tax_query'      => $tax_query, // Dynamic tax query
          'meta_query'     => $meta_query,
          'post__not_in'   => $product_ids_to_exclude,
          'post__in'       => $product_ids_to_include,
        );
        $products = new WP_Query($args);
        $syncProductList = array();
        if ($products->have_posts()) {
          while ($products->have_posts()) {
            $products->the_post();
            $product_id =  get_the_ID();
            $product_categories = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'names'));
            // Get product availability (stock status)
            $product_availability = get_post_meta($product_id, '_stock_status', true);

            // Get product quantity
            $product_quantity = get_post_meta($product_id, '_stock', true);
            $product_image_id = get_post_thumbnail_id($product_id);
            $product_image_src = wp_get_attachment_image_src($product_image_id, 'full');
            $product_image_url = isset($product_image_src[0]) ? $product_image_src[0] : "";
            $product_regular_price = get_post_meta($product_id, '_regular_price', true);
            $product_sale_price = get_post_meta($product_id, '_sale_price', true);
            $product_sku = get_post_meta($product_id, '_sku', true);
            $product_type = ucfirst(wc_get_product( $product_id )->get_type());

            if ($p_id == '_sku') {
              $proId = $product_sku;
            } elseif ($p_id == 'ID') {
              $proId = sanitize_text_field($product_id);
            } else {
              $proId = sanitize_text_field($product_id);
            }
            if ($proId == '') {
              $proId = sanitize_text_field($product_id);
            }
            $without_prefix = $proId;
            if (!empty($_POST['prefix'])) {
              $proId = sanitize_text_field($_POST['prefix']) . $proId;
            }
            $type = get_post_meta($product_id, '_product_type', true);;

            $categories = '';
            foreach ($product_categories as $term) {
              $categories .= '<label class="fs-12 fw-400 defaultPointer">' . $term . '</label><br/>';
            }

            $syncProductList[] = array(
              'checkbox' => '<input class="checkbox" hidden type="checkbox" name="attrProduct"  id="attr_' . esc_html($product_id) . '" checked value="' . esc_html($proId) . '">
                                <div class="form-check form-check-custom">
                                <input class="form-check-input checkbox fs-17 syncProduct syncProduct_' . esc_html($without_prefix) . '" name="syncProduct" type="checkbox" value="' . esc_html($product_id) . '" id="sync_' . esc_html($product_id) . '" checked>
                                </div>',
              'product' => ['product_image_url' => $product_image_url, 'title' => get_the_title(), 'price' =>$product_regular_price, 'sale_price' => $product_sale_price, 'ProductID' => $product_id, 'woocommerce_currency_symbol' => get_woocommerce_currency_symbol()],     
              'category' => $categories,
              'availability' => '<label class="fs-12 fw-400 ' . esc_attr(ucfirst($product_availability)) . '">' . esc_html(ucfirst($product_availability)) . '</label>',
              'quantity' => '<label class="fs-12 fw-400">' . esc_html($product_quantity ? $product_quantity : '-') . '</label>',
              'action' => ["proId" => $proId, 'product_type' => $product_type, 'sku' => $product_sku, 'product_id' => $product_id],
            );
          }
        }
        wp_reset_postdata();
        $result = array(
          'draw' => sanitize_text_field($_POST['draw']),
          'recordsTotal' => sanitize_text_field($pagination_count),
          'recordsFiltered' => sanitize_text_field($pagination_count),
          'data' => $syncProductList
        );
        echo wp_json_encode($result);
      } else {
        echo wp_json_encode(array("error" => true, "message" => esc_html__("Admin security nonce is not verified.", "product-feed-manager-for-woocommerce")));
      }
      exit;
    }
    /************************************ All function for Feed Wise Product Sync Start ******************************************************************/

    function convpfm_prepare_feed_to_sync() {
      if ($this->safe_ajax_call(sanitize_text_field(wp_unslash(filter_input(INPUT_POST, 'conv_nonce'))), 'conv_ajax_product_sync_bantch_wise-nonce')) {
        try {
          $Convpfm_TVC_Admin_Helper = new Convpfm_TVC_Admin_Helper();
          $Convpfm_Admin_DB_Helper = new Convpfm_Admin_DB_Helper();
          $Convpfm_TVC_Admin_Helper->plugin_log("Convpfm - Preparing Feed to Sync", 'product_sync');
          $productFilter = isset($_POST['productData']) && $_POST['productData'] != '' ? explode(',', $_POST['productData']) : '';
          $conditionFilter = isset($_POST['conditionData']) && $_POST['conditionData'] != '' ? explode(',', $_POST['conditionData']) : '';
          $valueFilter = isset($_POST['valueData']) && $_POST['valueData'] != '' ? explode(',', $_POST['valueData']) : '';
          $filters = array();
          if (!empty($productFilter)) {
            foreach ($productFilter as $key => $val) {
              $filters[$key]['attr'] = sanitize_text_field($val);
              $filters[$key]['condition'] = sanitize_text_field($conditionFilter[$key]);
              $filters[$key]['value'] = sanitize_text_field($valueFilter[$key]);
            }
          }
          $feed_data = array(
            "filters" => wp_json_encode($filters),
            "include_product" => esc_sql($_POST['include']),
            "exclude_product" => $_POST['include'] == '' ? esc_sql($_POST['exclude']) : '',
            "is_mapping_update" => true,
            "is_process_start" => true,
            "is_auto_sync_start" => false,
            "product_sync_alert" => sanitize_text_field("Product sync settings updated successfully"),
          );
          $Convpfm_Admin_DB_Helper->tvc_update_row("convpfm_product_feed", $feed_data, array("id" => sanitize_text_field($_POST['feedId'])));
          $where = '`id` = '.esc_sql(filter_input(INPUT_POST,'feedId'));
          $filed = ['id', 'channel_ids', 'auto_sync_interval', 'auto_schedule', 'categories', 'attributes', 'filters', 'include_product', 'exclude_product', 
                      'product_id_prefix', 'tiktok_catalog_id'];
          $result = $Convpfm_Admin_DB_Helper->tvc_get_results_in_array("convpfm_product_feed", $where, $filed);
          $google_detail = $Convpfm_TVC_Admin_Helper->get_convpfm_api_data();
          $feed_data_api = array(
            "store_id" => $google_detail['setting']->store_id,
            "store_feed_id" => sanitize_text_field($_POST['feedId']),
            "map_categories" => stripslashes($result[0]['categories']),
            "map_attributes" => stripslashes($result[0]['attributes']),
            "filter" => stripslashes($result[0]['filters']),
            "include" => esc_sql($result[0]['include_product']),
            "exclude" => sanitize_text_field($result[0]['exclude_product']),
            "channel_ids" => sanitize_text_field($result[0]['channel_ids']),
            "interval" => sanitize_text_field($result[0]['auto_sync_interval']),
            "tiktok_catalog_id" => sanitize_text_field($result[0]['tiktok_catalog_id']),
          );
          $Convpfm_TVC_Admin_Helper->plugin_log("Convpfm - Stored Feed related Data into Middleware", 'product_sync'); // Add logs
          $CustomApi = new Convpfm_CustomApi();
          $CustomApi->convpfm_create_product_feed($feed_data_api);
          /*******Update feed data in laravel End *********************/
          //add scheduled cron job
          as_unschedule_all_actions('init_feed_wise_product_sync_process_scheduler_convpfm', array("feedId" => sanitize_text_field($_POST['feedId'])));
          as_unschedule_all_actions('auto_feed_wise_product_sync_process_scheduler_convpfm', array("feedId" => sanitize_text_field($_POST['feedId'])));
          as_enqueue_async_action('init_feed_wise_product_sync_process_scheduler_convpfm', array("feedId" => sanitize_text_field($_POST['feedId'])));
          $Convpfm_TVC_Admin_Helper->plugin_log("Convpfm - Init Cron set.", 'product_sync'); // Add logs
          echo wp_json_encode(array("error" => false, "message" => esc_html__("Product sync in process", "product-feed-manager-for-woocommerce")));
        } catch (Exception $e) {
          $conv_additional_data['product_sync_alert'] = $e->getMessage();
          $Convpfm_TVC_Admin_Helper->set_ee_additional_data($conv_additional_data);
          $Convpfm_TVC_Admin_Helper->plugin_log($e->getMessage(), 'product_sync');
        }
      } else {
        echo wp_json_encode(array("error" => true, "message" => esc_html__("Admin security nonce is not verified.", "product-feed-manager-for-woocommerce")));
      }
      exit;
    }

    function convpfm_call_start_feed_wise_product_sync_process($feedId)
    {
      if(as_has_scheduled_action('auto_feed_wise_product_sync_process_scheduler_convpfm')){
        as_schedule_single_action(time() + 5, 'init_feed_wise_product_sync_process_scheduler_convpfm', array("feedId" => $feedId));

      } else { 
        $Convpfm_TVC_Admin_Helper = new Convpfm_TVC_Admin_Helper();
        $Convpfm_TVC_Admin_Helper->plugin_log("Convpfm - Initialize process to store data in pre sync table " . date('Y-m-d H:i:s', current_time('timestamp')) . " feed Id " . $feedId, 'product_sync'); // Add logs 
        $Convpfm_Admin_DB_Helper = new Convpfm_Admin_DB_Helper();
        try { 

          global $wpdb;
          $where = '`id` = ' . esc_sql($feedId);
          $filed = array(
            'feed_name',
            'channel_ids',
            'auto_sync_interval',
            'auto_schedule',
            'categories',
            'attributes',
            'filters',
            'include_product',
            'exclude_product',
            'is_mapping_update',
            'tiktok_catalog_id'
          );
          $result = $Convpfm_Admin_DB_Helper->tvc_get_results_in_array("convpfm_product_feed", $where, $filed);
          if (!empty($result) && isset($result) && $result[0]['is_mapping_update'] == '1') {
            $prouct_pre_sync_table = esc_sql("convpfm_prouct_pre_sync_data");

            if ($Convpfm_Admin_DB_Helper->tvc_row_count($prouct_pre_sync_table) > 0) {
              $Convpfm_Admin_DB_Helper->tvc_safe_truncate_table($wpdb->prefix . $prouct_pre_sync_table);
              $Convpfm_TVC_Admin_Helper->plugin_log("Convpfm - Empty Pre sync table.", 'product_sync'); // Add logs 
            }
              $product_db_batch_size = 200; // batch size to insert in database
              $batch_count = 0;
              $values = array();
              $place_holders = array();
            if ($result) {
              $Convpfm_TVC_Admin_Helper->plugin_log("Convpfm - Fetched data for Feed ID ".$feedId, 'product_sync'); // Add logs       
              $filters = json_decode(stripslashes($result[0]['filters']));
              $filters_count = is_array($filters) ? count($filters) : '';
              $categories = json_decode(stripslashes($result[0]['categories']));
              $attributes = json_decode(stripslashes($result[0]['attributes']));
              $include = $result[0]['include_product'] != '' ? explode(",", $result[0]['include_product']) : '';
              $exclude = explode(",", $result[0]['exclude_product']);

              $in_category_ids = array();
              $not_in_category_ids = array();
              $stock_status_to_fetch = array();
              $main_image_ids = array();
              $not_stock_status_to_fetch = $product_ids_to_exclude = $product_ids_to_include = $search = array();
              $product_count = 0;
              if ($filters_count != '') {
                for ($i = 0; $i < $filters_count; $i++) {
                  switch ($filters[$i]->attr) {
                    case 'product_cat':
                      if ($filters[$i]->condition == "=") {
                        array_push($in_category_ids, $filters[$i]->value);
                      } else if ($filters[$i]->condition == "!=") {
                        array_push($not_in_category_ids, $filters[$i]->value);
                      }
                      break;
                    case '_stock_status':
                      if (!empty($filters[$i]->condition) && $filters[$i]->condition == "=") {
                        array_push($stock_status_to_fetch, $filters[$i]->value);
                      } else if (!empty($filters[$i]->condition) && $filters[$i]->condition == "!=") {
                        array_push($not_stock_status_to_fetch, $filters[$i]->value);
                      }
                      break;
                    case 'main_image': // Adding image filter case
                      if ($filters[$i]->condition == "=") {
                        array_push($main_image_ids, $filters[$i]->value);
                      }
                      break;
                    case 'ID':
                      if ($filters[$i]->condition == "=") {
                        array_push($product_ids_to_include, $filters[$i]->value);
                      } else if ($filters[$i]->condition == "!=") {
                        array_push($product_ids_to_exclude, $filters[$i]->value);
                      }
                      break;
                  }
                }
              }
              if ($include == '') {
                $tax_query = array();
                if (!empty($in_category_ids)) {
                  $tax_query[] = array(
                    'taxonomy' => 'product_cat',
                    'field'    => 'term_id',
                    'terms'    => $in_category_ids,
                    'operator' => 'IN', // Retrieve products in any of the specified categories
                  );
                }
                if (!empty($not_in_category_ids)) {
                  $tax_query[] = array(
                    'taxonomy' => 'product_cat',
                    'field'    => 'term_id',
                    'terms'    => $not_in_category_ids,
                    'operator' => 'NOT IN', // Exclude products in any of the specified categories
                  );
                }
                if (!empty($in_category_ids) && !empty($not_in_category_ids)) {
                  $tax_query = array('relation' => 'AND');
                }
                $meta_query = array();
                if (!empty($stock_status_to_fetch)) {
                  $meta_query[] = array(
                    'key'     => '_stock_status',
                    'value'   => $stock_status_to_fetch,
                    'compare' => 'IN', // Include products with these stock statuses
                  );
                }

                if (!empty($main_image_ids)) {
                  $meta_query[] = [
                    'key'     => '_thumbnail_id',
                    'compare' => 'EXISTS', // Include products with a main image
                  ];
                }
                
                // Add not_stock_status_to_fetch condition
                if (!empty($not_stock_status_to_fetch)) {
                  $meta_query[] = array(
                    'key'     => '_stock_status',
                    'value'   => $not_stock_status_to_fetch,
                    'compare' => 'NOT IN', // Exclude products with these stock statuses
                  );
                }
                if (!empty($stock_status_to_fetch) && !empty($not_stock_status_to_fetch)) {
                  $meta_query = array('relation' => 'AND');
                }
                if (empty($tax_query) && empty($meta_query) && empty($product_ids_to_exclude) && empty($product_ids_to_include)) {
                  $count = (new WP_Query(['post_type' => 'product', 'post_status' => 'publish', 's' => $search]))->found_posts;
                  wp_reset_query();
                } else {
                  $args = array(
                    'post_type'      => 'product',
                    'post_status'    => 'publish',
                    's'              => $search,
                    'tax_query'      => $tax_query,         
                    'meta_query'     => $meta_query,
                    'post__not_in'   => $product_ids_to_exclude,
                    'post__in'       => $product_ids_to_include,
                  );

                  $count = (new WP_Query($args))->found_posts;
                  wp_reset_query();
                }
                $allowed_count = 200;
                if ($count <= $allowed_count) {
                  $args = array(
                    'post_type'      => 'product',
                    'posts_per_page' => 200,
                    'post_status'    => 'publish',
                    'offset'         => 0,
                    's'              => $search,
                    'tax_query'      => $tax_query,      
                    'meta_query'     => $meta_query,
                    'post__not_in'   => $product_ids_to_exclude,
                    'post__in'       => $product_ids_to_include,
                  );
                  $products  = new WP_Query($args);
                  if ($products->have_posts()) {
                    $all_cat = array();
                    foreach ($categories as $cat_key => $cat_val) {
                      $all_cat[$cat_key] = $cat_key;
                    }
                    while ($products->have_posts()) {
                      $products->the_post();
                      $product_id =  get_the_ID();
                      if (!in_array($product_id, $exclude)) {
                        $product_count++;
                        $product_categories = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'all'));
                        foreach ($product_categories as $term) {
                          $cat_id = $term->term_id;
                          if ($term->term_id == $all_cat[$cat_id]) {
                            $cat_matched_id = $term->term_id;
                            $have_cat = true;
                          }
                        }
                        if ($have_cat == true) {
                          array_push($values, esc_sql($product_id), esc_sql($cat_matched_id), esc_sql($categories->$cat_matched_id->id), 1, gmdate('Y-m-d H:i:s', current_time('timestamp')), $feedId);
                          $place_holders[] = "('%d', '%d', '%d', '%d', '%s', '%d')";
                        } else {
                          array_push($values, esc_sql($product_id), esc_sql($cat_id), '', 1, gmdate('Y-m-d H:i:s', current_time('timestamp')), $feedId);
                          $place_holders[] = "('%d', '%d', '%d', '%d', '%s', '%d')";
                        }
                      }
                    }
                    $query = "INSERT INTO `$wpdb->prefix$prouct_pre_sync_table` (w_product_id, w_cat_id, g_cat_id, product_sync_profile_id, create_date, feedId) VALUES ";
                    $query .= implode(', ', $place_holders);
                    $wpdb->query($wpdb->prepare($query, $values));
                    wp_reset_postdata();
                    $Convpfm_TVC_Admin_Helper->plugin_log("Convpfm - All Data stored in pre sync table at " . gmdate('Y-m-d H:i:s', current_time('timestamp')) . " feed Id " . $feedId, 'product_sync'); // Add logs 
                    as_schedule_single_action(time() + 5, 'auto_feed_wise_product_sync_process_scheduler_convpfm', array("feedId" => $feedId));
                  }
                } else {
                  $oldLimit = ini_get('memory_limit');
                  $old_max_time = ini_get('max_execution_time');
                  if ($old_max_time < 300) {
                    ini_set('memory_limit', '2042M');
                    ini_set('max_execution_time', 300);
                  }

                  $allowed_count = 100;
                  $total_pages = ceil($count / $allowed_count);
                  // $TVC_Admin_Helper->plugin_log('total_pages '.$total_pages, 'product_sync');                  
                  for ($page = 1; $page <= $total_pages; $page++) {
                    $args = array(
                      'post_type'      => 'product',
                      'posts_per_page' => $allowed_count,
                      'post_status'    => 'publish',
                      'paged'          => $page,
                      's'              => $search,
                      'tax_query'      => !empty($tax_query) ? $tax_query : '', // Dynamic tax query
                      'meta_query'     => !empty($meta_query) ? $meta_query : '',
                      'post__not_in'   => !empty($product_ids_to_exclude) ? $product_ids_to_exclude : '',
                      'post__in'       => !empty($product_ids_to_include) ? $product_ids_to_include : '',
                    );

                    $products  = new WP_Query($args);

                    if ($products->have_posts()) {
                      $values = array();
                      $place_holders = array();
                      $all_cat = array();
                      foreach ($categories as $cat_key => $cat_val) {
                        $all_cat[$cat_key] = $cat_key;
                      }
                      // $batch_count = 0;
                      while ($products->have_posts()) {
                        $products->the_post();
                        $product_id =  get_the_ID();
                        if (!in_array($product_id, $exclude)) {
                          $product_count++;
                          $product_categories = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'all'));
                          foreach ($product_categories as $term) {
                            $cat_id = $term->term_id;
                            if ($term->term_id == $all_cat[$cat_id]) {
                              $cat_matched_id = $term->term_id;
                              $have_cat = true;
                            }
                          }
                          if ($have_cat == true) {
                            array_push($values, esc_sql($product_id), esc_sql($cat_matched_id), esc_sql($categories->$cat_matched_id->id), 1, gmdate('Y-m-d H:i:s', current_time('timestamp')), $feedId);
                            $place_holders[] = "('%d', '%d', '%d', '%d', '%s', '%d')";
                          } else {
                            array_push($values, esc_sql($product_id), esc_sql($cat_id), '', 1, gmdate('Y-m-d H:i:s', current_time('timestamp')), $feedId);
                            $place_holders[] = "('%d', '%d', '%d', '%d', '%s', '%d')";
                          }
                        }
                      }

                      $query = "INSERT INTO `$wpdb->prefix$prouct_pre_sync_table` (w_product_id, w_cat_id, g_cat_id, product_sync_profile_id, create_date, feedId) VALUES ";
                      $query .= implode(', ', $place_holders);

                      $wpdb->query($wpdb->prepare($query, $values));                  
                    }

                    $products->reset_postdata();
                    wp_reset_postdata();
                  }

                  if ($old_max_time < 300) {
                    ini_set('memory_limit', $oldLimit);
                    ini_set('max_execution_time', $old_max_time);
                  }
                  $Convpfm_TVC_Admin_Helper->plugin_log("Convpfm - All Data stored in pre sync table at " . gmdate('Y-m-d H:i:s', current_time('timestamp')) . " feed Id " . $feedId, 'product_sync'); // Add logs 
                  as_schedule_single_action(time() + 5, 'auto_feed_wise_product_sync_process_scheduler_convpfm', array("feedId" => $feedId));
                }
              } else {
                $Convpfm_TVC_Admin_Helper->plugin_log("Convpfm - Only include product", 'product_sync'); // Add logs               
                foreach ($include as $val) {
                  $allResult[]['ID'] = $val;
                }
                if (!empty($allResult)) {
                  $all_cat = array();
  
                  foreach ($categories as $cat_key => $cat_val) {
                    $all_cat[$cat_key] = $cat_key;
                  }
                  //$product_count = 0;
                  $a = 0;
                  foreach ($allResult as $postvalue) {
                    $have_cat = false;
                    if (!in_array($postvalue['ID'], $exclude)) {
                      $terms = get_the_terms(sanitize_text_field($postvalue['ID']), 'product_cat');
                      foreach ($terms as $key => $term) {
                        $cat_id = $term->term_id;
                        if ($term->term_id == $all_cat[$cat_id]) {
                          $cat_matched_id = $term->term_id;
                          $have_cat = true;
                        }
                      }
  
                      if ($have_cat == true) {
                        $product_count++;
                        $batch_count++;
                        array_push($values, esc_sql($postvalue['ID']), esc_sql($cat_matched_id), esc_sql($categories->$cat_matched_id->id), 1, gmdate('Y-m-d H:i:s', current_time('timestamp')), $feedId);
                        $place_holders[] = "('%d', '%d', '%d', '%d', '%s', '%d')";
                        $query = "INSERT INTO `$wpdb->prefix$prouct_pre_sync_table` (w_product_id, w_cat_id, g_cat_id, product_sync_profile_id, create_date, feedId) VALUES ";
                        $query .= implode(', ', $place_holders);
                        $wpdb->query($wpdb->prepare($query, $values));
                      } else {
                        $product_count++;
                        array_push($values, esc_sql($postvalue['ID']), esc_sql($cat_id), '', 1, gmdate('Y-m-d H:i:s', current_time('timestamp')), $feedId);
                        $place_holders[] = "('%d', '%d', '%d', '%d', '%s', '%d')";
                        $query = "INSERT INTO `$wpdb->prefix$prouct_pre_sync_table` (w_product_id, w_cat_id, g_cat_id, product_sync_profile_id, create_date, feedId) VALUES ";
                        $query .= implode(', ', $place_holders);
                        $wpdb->query($wpdb->prepare($query, $values));
                      }
                    }
                  } //end product list loop
  
                  $Convpfm_TVC_Admin_Helper->plugin_log("Convpfm - All Data stored in ee_prouct_pre_sync_data table at " . gmdate('Y-m-d H:i:s', current_time('timestamp')) . " feed Id " . $feedId, 'product_sync'); // Add logs 
                  as_schedule_single_action(time() + 5, 'auto_feed_wise_product_sync_process_scheduler_convpfm', array("feedId" => $feedId));
                } // end products if
              }
              $Convpfm_TVC_Admin_Helper->plugin_log("Convpfm - is_process_start", 'product_sync'); // Add logs
              $feed_data = array(
                "total_product" => $product_count,
                "is_process_start" => true,
                "product_sync_alert" => sanitize_text_field("Product sync process is ready to start"),
              );
              $Convpfm_Admin_DB_Helper->tvc_update_row("convpfm_product_feed", $feed_data, array("id" => $feedId));
            } else {
              $Convpfm_TVC_Admin_Helper->plugin_log("Convpfm - Feed Data is missing for Feed Id =". $feedId, 'product_sync'); // Add logs
            }              
          } else {
            $Convpfm_TVC_Admin_Helper->plugin_log("Convpfm - Mapping not updated for feedId =". $feedId, 'product_sync'); // Add logs 
          }        
        } catch (Exception $e) {   

          $feed_data = array(
            "product_sync_alert" => $e->getMessage(),
            "is_process_start" => false,
            "is_auto_sync_start" => false,
            "is_mapping_update" => false,
          );
          $Convpfm_Admin_DB_Helper->tvc_update_row("convpfm_product_feed", $feed_data, array("id" => $feedId));
          $Convpfm_TVC_Admin_Helper->plugin_log($e->getMessage(), 'product_sync');
        }

        return true;
      }
    }

    function convpfm_call_auto_feed_wise_product_sync_process($feedId)
    {
      $Convpfm_TVC_Admin_Helper = new Convpfm_TVC_Admin_Helper();
      $Convpfm_TVC_Admin_Helper->plugin_log("Convpfm - Called Auto Feed wise Product Sync feed id = ". $feedId, 'product_sync');
      $conv_additional_data = $Convpfm_TVC_Admin_Helper->get_convpfm_additional_data();
      $conv_additional_data['product_sync_alert'] = NULL;
      $Convpfm_TVC_Admin_Helper->set_convpfm_additional_data($conv_additional_data);
      $Convpfm_Admin_DB_Helper = new Convpfm_Admin_DB_Helper();
      $feed_data = array(
        "is_auto_sync_start" => true,
        "product_sync_alert" => NULL,
      );
      $Convpfm_Admin_DB_Helper->tvc_update_row("convpfm_product_feed", $feed_data, array("id" => $feedId));

      try {
        global $wpdb;
        $where = '`id` = ' . esc_sql($feedId);
        $filed = array(
          'feed_name',
          'channel_ids',
          'is_process_start',
          'auto_sync_interval',
          'auto_schedule',
          'categories',
          'attributes',
          'filters',
          'include_product',
          'exclude_product',
          'is_mapping_update'
        );
        $result = $Convpfm_Admin_DB_Helper->tvc_get_results_in_array("convpfm_product_feed", $where, $filed);
        $Convpfm_TVC_Admin_Helper->plugin_log("ConvPFM -  auto feed wise product sync process start", 'product_sync');
        if (!empty($result) && isset($result[0]['is_process_start']) && $result[0]['is_process_start'] == true) {
          $Convpfm_TVC_Admin_Helper->plugin_log("ConvPFM - Found product", 'product_sync');
          if (!class_exists('Convpfm_ProductSyncHelper')) {
            include CONVPFM_ENHANCAD_PLUGIN_DIR . 'includes/setup/class-convpfm-product-sync-helper.php';
          }
          $Convpfm_ProductSyncHelper = new Convpfm_ProductSyncHelper();
          // $response = $Convpfm_ProductSyncHelper->call_batch_wise_auto_sync_product_feed_ee($feedId);
          $response = $Convpfm_ProductSyncHelper->call_batch_wise_auto_sync_product_feed_convpfm($feedId);
          if (!empty($response) && isset($response['message'])) {
            $Convpfm_TVC_Admin_Helper->plugin_log("Convpfm -  Batch wise auto sync process response " . $response['message'], 'product_sync');
          }

          $tablename = esc_sql($wpdb->prefix . "convpfm_prouct_pre_sync_data");
          $total_pending_pro = $wpdb->get_var("SELECT COUNT(*) as a FROM $tablename where `feedId` = $feedId AND `status` = 0");
          if ($total_pending_pro == 0) {
            // Truncate pre sync table
            $Convpfm_Admin_DB_Helper->tvc_safe_truncate_table($tablename);

            $conv_additional_data['is_process_start'] = false;
            $conv_additional_data['is_auto_sync_start'] = true;
            $conv_additional_data['product_sync_alert'] = NULL;
            $Convpfm_TVC_Admin_Helper->set_convpfm_additional_data($conv_additional_data);
            $last_sync_date = date('Y-m-d H:i:s', current_time('timestamp'));
            $next_schedule_date = NULL;
            as_unschedule_all_actions('auto_feed_wise_product_sync_process_scheduler_convpfm', array("feedId" => $feedId));
            as_unschedule_all_actions('init_feed_wise_product_sync_process_scheduler_convpfm', array("feedId" => $feedId));
            if ($result[0]['auto_schedule'] == 1) {
              $next_schedule_date = date('Y-m-d H:i:s', strtotime('+' . $result[0]['auto_sync_interval'] . 'day', current_time('timestamp')));              
              $time_space = strtotime($result[0]['auto_sync_interval'] . " days", 0);
              $timestamp = strtotime($result[0]['auto_sync_interval'] . " days");
              as_schedule_recurring_action(esc_attr($timestamp), esc_attr($time_space), 'init_feed_wise_product_sync_process_scheduler_convpfm', array("feedId" => $feedId), "product_sync");
            }
            $feed_data = array(
              "product_sync_alert" => NULL,
              "is_process_start" => false,
              "is_auto_sync_start" => true,
              "last_sync_date" => esc_sql($last_sync_date),
              "next_schedule_date" => $next_schedule_date,
            );
            $Convpfm_Admin_DB_Helper->tvc_update_row("convpfm_product_feed", $feed_data, array("id" => $feedId));
            $Convpfm_TVC_Admin_Helper->plugin_log("Convpfm - Product sync process done", 'product_sync');
          } else {
            // add scheduled cron job    
            as_unschedule_all_actions('auto_feed_wise_product_sync_process_scheduler_convpfm', array("feedId" => $feedId));        
            as_schedule_single_action(time() + 5, 'auto_feed_wise_product_sync_process_scheduler_convpfm', array("feedId" => $feedId));
            $Convpfm_TVC_Admin_Helper->plugin_log("Convpfm - Found product, Recall product sync process", 'product_sync');
          }
        } else {
          // add scheduled cron job
          as_unschedule_all_actions('auto_feed_wise_product_sync_process_scheduler_convpfm', array("feedId" => $feedId));
        }
        echo wp_json_encode(array('status' => 'success', "message" => esc_html__("Feed wise product sync process started successfully")));
        return true;
      } catch (Exception $e) {
        $feed_data = array(
          "product_sync_alert" => $e->getMessage(),
          "is_process_start" => false,
          "is_auto_sync_start" => false,
        );
        $Convpfm_Admin_DB_Helper->tvc_update_row("convpfm_product_feed", $feed_data, array("id" => $feedId));
        $conv_additional_data['product_sync_alert'] = $e->getMessage();
        $Convpfm_TVC_Admin_Helper->set_convpfm_additional_data($conv_additional_data);
        $Convpfm_TVC_Admin_Helper->plugin_log($e->getMessage(), 'product_sync');
        return true;
      }
    }    

    /**
     * Function used for to get TikTok Business Account by subcription id
     * hook used wp_ajax_get_tiktok_business_account
     * Type POST
     * parameter $subcriptionid
     */
    function convpfm_get_tiktok_business_account()
    {
      if ($this->safe_ajax_call(sanitize_text_field(wp_unslash(filter_input(INPUT_POST, 'conversios_onboarding_nonce'))), 'conversios_onboarding_nonce')) {
        if (isset($_POST['subscriptionId']) === TRUE && $_POST['subscriptionId'] !== '') {
          $customer_subscription_id['customer_subscription_id'] = $_POST['subscriptionId'];
          $customObj = new Convpfm_CustomApi();
          $result = $customObj->get_tiktok_business_account($customer_subscription_id);
          if (isset($result->status) && $result->status === 200 && is_array($result->data) && $result->data != '') {
            $tikTokData = [];
            foreach ($result->data as $value) {
              if ($value->bc_info->status === 'ENABLE') {
                $tikTokData[$value->bc_info->bc_id] = $value->bc_info->name;
              }
            }
            echo wp_json_encode(array("error" => false, "data" => $tikTokData));
          } else {
            echo wp_json_encode(array("error" => true, "message" => esc_html__("Error: Business Account not found", "product-feed-manager-for-woocommerce")));
          }

        } else {
          echo wp_json_encode(array("error" => true, "message" => esc_html__("Error: Business Account not found", "product-feed-manager-for-woocommerce")));
        }

      } else {
        echo wp_json_encode(array("error" => true, "message" => esc_html__("Admin security nonce is not verified.", "product-feed-manager-for-woocommerce")));
      }
      exit;
    }

    /**
     * Function used for to get TikTok Catalog Id business id
     * hook used wp_ajax_convpfm_get_tiktok_user_catalogs
     * Type POST
     * parameter $businessId
     */
    function convpfm_get_tiktok_user_catalogs()
    {
      if ($this->safe_ajax_call(sanitize_text_field(wp_unslash(filter_input(INPUT_POST, 'conversios_onboarding_nonce'))), 'conversios_onboarding_nonce')) {
        if (isset($_POST['customer_subscription_id']) === TRUE && $_POST['customer_subscription_id'] !== '' && $_POST['business_id'] !== '') {
          $customer_subscription_id['customer_subscription_id'] = $_POST['customer_subscription_id'];
          $customer_subscription_id['business_id'] = $_POST['business_id'];
          $customObj = new Convpfm_CustomApi();
          $result = $customObj->get_tiktok_user_catalogs($customer_subscription_id);
          if ($result->status === 200 && is_array($result->data) && $result->data != '') {
            $tikTokData = [];
            foreach ($result->data as $key => $value) {
              $tikTokData[$value->catalog_conf->country][$value->catalog_id] = $value->catalog_name;
            }

            foreach ($tikTokData as &$subArray) {
              arsort($subArray);
            }

            echo json_encode(array("error" => false, "data" => $tikTokData));
          }

        } else {
          echo json_encode(array("error" => true, "message" => esc_html__("Error: Business Account not found", "product-feed-manager-for-woocommerce")));
        }

      } else {
        echo json_encode(array("error" => true, "message" => esc_html__("Admin security nonce is not verified.", "product-feed-manager-for-woocommerce")));
      }
      exit;

    }
    public function convpfm_save_tiktokmiddleware($post)
    {
      if (isset($post['customer_subscription_id']) === TRUE && $post['customer_subscription_id'] !== '' && $post['conv_options_data']['tiktok_setting']['tiktok_business_id'] !== '') {
        $customer_subscription_id['customer_subscription_id'] = $_POST['customer_subscription_id'];
        $customer_subscription_id['business_id'] = $post['conv_options_data']['tiktok_setting']['tiktok_business_id'];
        $customObj = new Convpfm_CustomApi();
        $result = $customObj->store_business_center($customer_subscription_id);
        return $result;
      }


    }
    public function convpfm_save_tiktokcatalog($post)
    {
      $catArr = [];
      $i = 0;
      $values = array();
      $place_holders = array();

      foreach ($post['conv_catalogData'] as $key => $value) {
        $catArr[$i]["region_code"] = $key;
        $catArr[$i++]["catalog_id"] = $value[0];
        array_push($values, esc_sql($key), esc_sql($value[0]), esc_sql($value[1]), date('Y-m-d H:i:s', current_time('timestamp')));
        $place_holders[] = "('%s', '%s', '%s','%s')";
      }

      $Convpfm_Admin_DB_Helper = new Convpfm_Admin_DB_Helper();
      global $wpdb;
      $convpfm_tiktok_catalog = esc_sql($wpdb->prefix . "convpfm_tiktok_catalog");

      if ($Convpfm_Admin_DB_Helper->tvc_row_count("convpfm_tiktok_catalog") > 0) {
        $Convpfm_Admin_DB_Helper->tvc_safe_truncate_table($convpfm_tiktok_catalog);
      }
      //Insert tiktok catalog data into db
      $query = "INSERT INTO `$convpfm_tiktok_catalog` (country, catalog_id, catalog_name, created_date) VALUES ";
      $query .= implode(', ', $place_holders);
      $wpdb->query($wpdb->prepare($query, $values));
      if (isset($post['customer_subscription_id']) === TRUE && $post['customer_subscription_id'] !== '' && $post['conv_options_data']['tiktok_setting']['tiktok_business_id'] !== '') {
        $customer_subscription_id['customer_subscription_id'] = $_POST['customer_subscription_id'];
        $customer_subscription_id['business_id'] = $post['conv_options_data']['tiktok_setting']['tiktok_business_id'];
        $customer_subscription_id['catalogs'] = $catArr;
        $customObj = new Convpfm_CustomApi();
        $result = $customObj->store_user_catalog($customer_subscription_id);
        return $result;
      }
    }

    public function convpfm_getCatalogId()
    {
      if ($this->safe_ajax_call(sanitize_text_field(wp_unslash(filter_input(INPUT_POST, 'conv_country_nonce'))), 'conv_country_nonce')) {
        if (isset($_POST['countryCode']) === TRUE && $_POST['countryCode'] !== '') {
          $country_code = $_POST['countryCode'];
          $Convpfm_Admin_DB_Helper = new Convpfm_Admin_DB_Helper();
          $where = '`country` = "' . esc_sql($country_code) . '"';
          $filed = array('catalog_id');
          $result = $Convpfm_Admin_DB_Helper->tvc_get_results_in_array("convpfm_tiktok_catalog", $where, $filed);
          $catalog_id['catalog_id'] = isset($result[0]['catalog_id']) === TRUE && isset($result[0]['catalog_id']) !== '' ? $result[0]['catalog_id'] : '';
          echo json_encode(array("error" => false, "data" => $catalog_id));
        }

      } else {
        echo json_encode(array("error" => true, "message" => esc_html__("Admin security nonce is not verified.", "product-feed-manager-for-woocommerce")));
      }
      exit;
    }

    /**
     * Function used for to create Pmax Campaign
     * hook used wp_ajax_convpfm_createPmaxCampaign
     * Type POST
     * parameter POST value
     */
    public function convpfm_createPmaxCampaign()
    {
      if ($this->safe_ajax_call(sanitize_text_field(wp_unslash(filter_input(INPUT_POST, 'conv_onboarding_nonce'))), 'conv_onboarding_nonce')) {
        if ($_POST['subscription_id'] == '') {
          echo json_encode(array("error" => true, "message" => esc_html__("Subscription Id is missing. Contact plugin Admin", "product-feed-manager-for-woocommerce")));
          exit;
        }
        if ($_POST['google_merchant_id'] == '') {
          echo json_encode(array("error" => true, "message" => esc_html__("Google Merchant Id is missing. Please map Google Merchant Id.", "product-feed-manager-for-woocommerce")));
          exit;
        }
        if ($_POST['google_ads_id'] == '') {
          echo json_encode(array("error" => true, "message" => esc_html__("Google Ads Id is missing. Please map Google Ads Id.", "product-feed-manager-for-woocommerce")));
          exit;
        }
        if ($_POST['store_id'] == '') {
          echo json_encode(array("error" => true, "message" => esc_html__("Store Id is missing. Contact plugin Admin.", "product-feed-manager-for-woocommerce")));
          exit;
        }
        
        $customObj = new Convpfm_CustomApi();
        $result = $customObj->createPmaxCampaign($_POST);
        if (isset($result->data->request_id) && $result->data->request_id !== '') {
          $values = array();
          $place_holders = array();
          global $wpdb;
          $convpfm_pmax_campaign = esc_sql($wpdb->prefix . "convpfm_pmax_campaign");
          $place_holders[] = "('%s', '%s', '%s','%s', '%s', '%s', '%s', '%s', '%s', '%s')";
          array_push($values, esc_sql($_POST['campaign_name']), esc_sql($_POST['budget']), esc_sql($_POST['target_country']), esc_sql($_POST['target_roas']), esc_sql($_POST['start_date']), esc_sql($_POST['end_date']), esc_sql($_POST['status']), esc_sql($_POST['sync_item_ids']), esc_sql($result->data->request_id), date('Y-m-d H:i:s', current_time('timestamp')));
          //Insert Campaign data into db
          $query = "INSERT INTO `$convpfm_pmax_campaign` (campaign_name, daily_budget, target_country_campaign, target_roas, start_date, end_date, status, feed_id, request_id, created_date) VALUES ";
          $query .= implode(', ', $place_holders);
          $wpdb->query($wpdb->prepare($query, $values));
          echo json_encode(array("error" => false, "data" => $result->data));
        } else {
          echo json_encode(array("error" => true, "message" => esc_html__($result->error_data[411]->errors[0])));
        }
      } else {
        echo json_encode(array("error" => true, "message" => esc_html__("Admin security nonce is not verified.", "product-feed-manager-for-woocommerce")));
      }
      exit;
    }

    public function tvc_call_add_customer_featurereq()
    {
      if (wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['feature_req_nonce'])), 'feature_req_nonce_val')) {
        $formdata = array();
        $formdata['feedback'] = sanitize_text_field($_POST['featurereq_message']);
        $formdata['subscription_id'] = isset($_POST['subscription_id']) ? sanitize_text_field($_POST['subscription_id']) : "";
        $customObj = new Convpfm_CustomApi();
        unset($_POST['action']);
        echo json_encode($customObj->record_customer_featurereq($formdata));
        exit;
      } else {
        echo json_encode(array("error" => true, "message" => esc_html__("Admin security nonce is not verified.", "product-feed-manager-for-woocommerce")));
      }
      // IMPORTANT: don't forget to exit
      exit;
    }
    public function get_user_businesses()
    {
      if ($this->safe_ajax_call(sanitize_text_field(wp_unslash($_POST['fb_business_nonce'])), 'fb_business_nonce')) {
        $data = array(
          "customer_subscription_id" => sanitize_text_field($_POST['customer_subscription_id']),
        );
        $convCustomApi = new Convpfm_CustomApi();
        $result = $convCustomApi->getUserBusinesses($data);
        echo json_encode($result);
      } else {
        echo json_encode(array("error" => true, "message" => esc_html__("Admin security nonce is not verified.", "product-feed-manager-for-woocommerce")));
      }
      exit;
    }

    public function convpfm_get_fb_catalog_data()
    {
      if ($this->safe_ajax_call(sanitize_text_field(wp_unslash($_POST['fb_business_nonce'])), 'fb_business_nonce')) {
        $data = array(
          "customer_subscription_id" => sanitize_text_field($_POST['customer_subscription_id']),
          "business_id" => sanitize_text_field($_POST['fb_business_id']),
        );        
        $convCustomApi = new Convpfm_CustomApi();
        $result = $convCustomApi->getCatalogList($data);
        echo json_encode($result->data);
      } else {
        echo json_encode(array("error" => true, "message" => esc_html__("Admin security nonce is not verified.", "product-feed-manager-for-woocommerce")));
      }
      exit;
    }
    public function convpfm_save_facebookmiddleware($post)
    {
      if (isset($post['customer_subscription_id']) === TRUE && $post['customer_subscription_id'] !== '' && isset($post['conv_options_data']['facebook_setting']['fb_business_id']) && $post['conv_options_data']['facebook_setting']['fb_business_id'] !== '') {
        $customer_data['customer_subscription_id'] = sanitize_text_field($_POST['customer_subscription_id']);
        $customer_data['business_id'] = $post['conv_options_data']['facebook_setting']['fb_business_id'];
        $customObj = new Convpfm_CustomApi();
        $result = $customObj->storeUserBusiness($customer_data);
        return $result;
      }
    }

    public function convpfm_save_facebookcatalog($post)
    {
      if (isset($post['customer_subscription_id']) === TRUE && $post['customer_subscription_id'] !== '' && isset($post['conv_options_data']['facebook_setting']['fb_business_id']) && $post['conv_options_data']['facebook_setting']['fb_business_id'] !== '') {
        $customer_data['customer_subscription_id'] = sanitize_text_field($_POST['customer_subscription_id']);
        $customer_data['business_id'] = $post['conv_options_data']['facebook_setting']['fb_business_id'];
        $customer_data['catalog_id'] = $post['conv_options_data']['facebook_setting']['fb_catalog_id'];
        $customObj = new Convpfm_CustomApi();
        $result = $customObj->storeUserCatalog($customer_data);
        return $result;
      }
    }

    public function convpfm_sync_single_product() {
      if ($this->safe_ajax_call(sanitize_text_field(wp_unslash($_POST['conv_onboarding_nonce'])), 'conv_onboarding_nonce')) {
        $Convpfm_Admin_DB_Helper = new Convpfm_Admin_DB_Helper();

        $where = '`id` = '.esc_sql(filter_input(INPUT_POST,'feed_id'));
        $filed = ['id', 'channel_ids', 'auto_sync_interval', 'auto_schedule', 'categories', 'attributes', 'filters', 'include_product', 'exclude_product', 
                    'product_id_prefix', 'tiktok_catalog_id', 'is_mapping_update', 'target_country'];
        $result = $Convpfm_Admin_DB_Helper->tvc_get_results_in_array("convpfm_product_feed", $where, $filed);

        $include_product = $result[0]['include_product'];
        $exclude_product = $result[0]['exclude_product'];
        $include = !empty($include_product) ? explode(",", $include_product) : [];
        $exclude = !empty($exclude_product) ? explode(",", $exclude_product) : [];

        if($include_product == '' && $exclude_product == '' && $result[0]['is_mapping_update'] != 1) {
          $include[] = $_POST['product_ids'];
        } else if($include_product == '' && $exclude_product != '') {
          $key = array_search($_POST['product_ids'], $exclude);
          if ($key !== false) {
            unset($exclude[$key]);
          }
        } else if ($include_product != '' && $exclude_product == '') {
          array_push($include, $_POST['product_ids']);
          $include = array_unique($include);
        }
        $feed_datas = array(
          "include_product" => implode(",", $include),
          "exclude_product" => implode(",", $exclude),
          "product_sync_alert" => NULL,
          "is_mapping_update" => true,
        );
        $Convpfm_Admin_DB_Helper->tvc_update_row("convpfm_product_feed", $feed_datas, array("id" => sanitize_text_field($_POST['feed_id'])));

        $mappedAttrs = json_decode(stripslashes($result[0]['attributes']), true);
        $mappedAttrs['target_country'] = $result[0]['target_country'];
        $mappedCat = json_decode(stripslashes($result[0]['categories']), true);
        $product = wc_get_product($_POST['product_ids']);
        $categories = wp_get_post_terms($_POST['product_ids'], 'product_cat');
        $w_cat_id = '';
        foreach ($categories as $category) {
          $w_cat_id = $category->term_id;
        }
        
        if(isset($mappedCat[$w_cat_id])) {
          $g_cat_id = $mappedCat[$w_cat_id]['id'];
        } else {
          $g_cat_id = 0;
        }
        
        if (!class_exists('Convpfm_ProductSyncHelper')) {
          include CONVPFM_ENHANCAD_PLUGIN_DIR . 'includes/setup/class-convpfm-product-sync-helper.php';
        }
        $Convpfm_TVC_Admin_Helper = new Convpfm_TVC_Admin_Helper();
        $TVCProductSyncHelper = new Convpfm_ProductSyncHelper();
        $convpfm_currency = sanitize_text_field($Convpfm_TVC_Admin_Helper->get_woo_currency());
        $account_id = sanitize_text_field($Convpfm_TVC_Admin_Helper->get_merchantId());
        $merchant_id = sanitize_text_field($Convpfm_TVC_Admin_Helper->get_main_merchantId());
        $subscriptionId = sanitize_text_field(sanitize_text_field($Convpfm_TVC_Admin_Helper->get_subscriptionId()));
        $product_id_prefix = $result[0]['product_id_prefix'];
        $object = array(
          '0' => (object) array(
            'w_product_id' => sanitize_text_field($_POST['product_ids']),
            'w_cat_id' => $w_cat_id,
            'g_cat_id' => $g_cat_id
          )
        );        
        $p_map_attribute = $TVCProductSyncHelper->conv_get_feed_wise_map_product_attribute($object, $convpfm_currency, $account_id, '100', $mappedAttrs, $product_id_prefix);
        $Convpfm_Admin_Auto_Product_sync_Helper = new Convpfm_Admin_Auto_Product_sync_Helper();
        $Convpfm_Admin_Auto_Product_sync_Helper->update_last_sync_in_db_batch_wise($p_map_attribute['valid_products'], sanitize_text_field($_POST['feed_id']));

        if (!empty($p_map_attribute) && isset($p_map_attribute['items']) && !empty($p_map_attribute['items'])) {
          $google_detail = $Convpfm_TVC_Admin_Helper->get_convpfm_api_data();
          $feed_data_api = array(
            "store_id" => $google_detail['setting']->store_id,
            "store_feed_id" => $_POST['feed_id'],
            "map_categories" => stripslashes($result[0]['categories']),
            "map_attributes" => stripslashes($result[0]['attributes']),
            "filter" => stripslashes($result[0]['filters']),
            "include" => esc_sql(implode(",", $include)),
            "exclude" => esc_sql(implode(",", $exclude)),
            "channel_ids" => $result[0]['channel_ids'],
            "interval" => $result[0]['auto_sync_interval'],
            "tiktok_catalog_id" => $result[0]['tiktok_catalog_id'],
          );
          $Convpfm_TVC_Admin_Helper->plugin_log("Convpfm - Stored Feed related Data into Middleware", 'product_sync'); // Add logs
          $CustomApi = new Convpfm_CustomApi();        
          $CustomApi->convpfm_create_product_feed($feed_data_api);
          $convpfm_options = $Convpfm_TVC_Admin_Helper->get_convpfm_options_settings();
          $data = [
            'merchant_id' => isset($merchant_id) === TRUE ? sanitize_text_field($merchant_id) : '',
            'account_id' => isset($account_id) === TRUE ?  sanitize_text_field($account_id) : '',
            'subscription_id' => sanitize_text_field($subscriptionId),
            'store_feed_id' => sanitize_text_field($_POST['feed_id']),
            'is_on_gmc' => strpos(sanitize_text_field($result[0]['channel_ids']), '1') !== false ? true : false,
            'is_on_tiktok' => strpos(sanitize_text_field($result[0]['channel_ids']), '3') !== false ? true : false,
            'tiktok_catalog_id' => sanitize_text_field($result[0]['tiktok_catalog_id']),
            'tiktok_business_id' => sanitize_text_field($Convpfm_TVC_Admin_Helper->get_tiktok_business_id()),
            'is_on_facebook' => strpos(sanitize_text_field($result[0]['channel_ids']), '2') !== false ? true : false,
            'business_id' => strpos(sanitize_text_field($result[0]['channel_ids']), '2') !== false ? $convpfm_options['facebook_setting']['fb_business_id'] : '',
            'catalog_id' => strpos(sanitize_text_field($result[0]['channel_ids']), '2') !== false ? $convpfm_options['facebook_setting']['fb_catalog_id'] : '',
            'entries' => $p_map_attribute['items']
          ];
          $Convpfm_TVC_Admin_Helper->plugin_log("Convpfm - Sending product for sync", 'product_sync'); // Add logs
          $response = $CustomApi->feed_wise_products_sync($data);
        
          if ($response->error == false) {
            $Convpfm_TVC_Admin_Helper->plugin_log("Convpfm - Product sync done", 'product_sync'); // Add logs            
            $syn_data = array(
              'status' => 0
            );
            $Convpfm_Admin_DB_Helper->tvc_update_row("convpfm_product_sync_data", $syn_data, array("feedId" => sanitize_text_field($_POST['feed_id'])));
            echo wp_json_encode(array('error' => false,'message' => 'Your product is getting Sync!!!'));
            exit;
          } else {
            echo wp_json_encode(array('error' => true, 'message' => esc_attr('Error in Sync...')));
          }
        }
      } else {
        echo wp_json_encode(array("error" => true, "message" => esc_html__("Admin security nonce is not verified.", "product-feed-manager-for-woocommerce")));
      }
      exit;
    }
    public function convpfm_editPmaxCampaign() {
      if ($this->safe_ajax_call(sanitize_text_field(wp_unslash(filter_input(INPUT_POST, 'conv_onboarding_nonce'))), 'conv_onboarding_nonce')) {        
        $PMax_Helper = new Convpfm_PMax_Helper();
        $rs = $PMax_Helper->campaign_pmax_detail(sanitize_text_field($_POST['google_ads_id']), sanitize_text_field($_POST['id']));
        if(isset($rs->data)) {
          if(isset($rs->data->campaign)){
            $campaign = $rs->data->campaign;
          }
          if(isset($rs->data->campaign_budget)){
            $campaign_budget = $rs->data->campaign_budget;
          }
          
          $sale_country = isset($campaign->shoppingSetting->feedLabel) ? $campaign->shoppingSetting->feedLabel:"";
          $budget_micro = isset($campaign_budget->amountMicros)? $campaign_budget->amountMicros:"";
          if($budget_micro > 0){
            $budget = $budget_micro / 1000000;
          }
          $maximizeconversionvalue = isset($campaign->maximizeConversionValue)?$campaign->maximizeConversionValue:"";
          $target_roas = $this->object_value($maximizeconversionvalue, "targetRoas")*100;
          $startDate = $this->object_value($campaign, "startDate");
          $endDate = $this->object_value($campaign, "endDate");
          $status = $campaign->status;
          $resourceName = $campaign->resourceName;
          $campaignBudget = $campaign->campaignBudget;
          $campaignName = $this->object_value($campaign, "name");
          $data = array (
            'campaignName' => $campaignName,
            'budget' => $budget,
            'sale_country' => $sale_country,
            'target_roas' => $target_roas,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'status' => $status,
            'resourceName' => $resourceName,
            'campaignBudget' => $campaignBudget,
            'id' => $campaign->id
          );
          echo wp_json_encode(array("error" => false, "result" => $data));
        } else {
          echo wp_json_encode(array("error" => true, "message" => esc_html__("No record found for the selected Campaign.", "product-feed-manager-for-woocommerce")));
        }
        
      } else {
        echo wp_json_encode(array("error" => true, "message" => esc_html__("Admin security nonce is not verified.", "product-feed-manager-for-woocommerce")));
      }
      exit;
    }
    public function object_value($obj, $key){
      if(!empty($obj) && $key && isset($obj->$key)){
        return $obj->$key;
      }
    }

    public function convpfm_update_PmaxCampaign() {
      $nonce = (isset($_POST['conversios_nonce']))?sanitize_text_field($_POST['conversios_nonce']):"";
      $return = array();
      $formArry = array();
      if($this->safe_ajax_call($nonce, 'conversios_nonce')){        
        foreach($_POST as $key => $val) {         
          if($key == 'action' || $key == 'conversios_nonce') {
            continue;
          }
          $formArry[$key] = sanitize_text_field($val);
        }
        if(isset($formArry["customer_id"]) ){
          $Convpfm_Admin_DB_Helper = new Convpfm_Admin_DB_Helper();
          $where = '`campaign_name` = "'.esc_sql($formArry['campaign_name']).'"';
          $filed = [
            'request_id',
          ];
          $result = $Convpfm_Admin_DB_Helper->tvc_get_results_in_array("convpfm_pmax_campaign", $where, $filed);
          if(isset($result[0]['request_id']) &&  $result[0]['request_id'] !== ''){
            $formArry['request_id'] = $result[0]['request_id'];
            $profile_data = array(
                                  "daily_budget" => esc_sql($formArry['budget']), 
                                  "target_country_campaign" => esc_sql($formArry['target_country']),
                                  "target_roas" => esc_sql($formArry['target_roas']),
                                  "start_date" => esc_sql($formArry['start_date']),
                                  "end_date" => esc_sql($formArry['end_date']),
                                  "status" => esc_sql($formArry['status']),
                                  "updated_date" => date('Y-m-d H:i:s', current_time('timestamp')),
                                );    
          $Convpfm_Admin_DB_Helper->tvc_update_row("convpfm_pmax_campaign", $profile_data, array("campaign_name" => $formArry['campaign_name']));
          }
          $PMax_Helper = new Convpfm_PMax_Helper();
          if($formArry["status"] == "REMOVED"){
            $removeArr = array("customer_id"=>$formArry["customer_id"], "resource_name"=>$formArry["resource_name"]);
            if(isset($result[0]['request_id']) &&  $result[0]['request_id'] !== ''){
              $removeArr['request_id'] = $result[0]['request_id'];
            }
		    		$api_rs = $PMax_Helper->delete_pmax_campaign_callapi($removeArr);
		    	}else{
						$api_rs = $PMax_Helper->edit_pmax_campaign_callapi($formArry);	
					}	
          if (isset($api_rs->error) && $api_rs->error == '') {
	        	if(isset($api_rs->data->results[0]->resourceName) && $api_rs->data != ""){
	        		$resource_name = $api_rs->data->results[0]->resourceName;
	        		if($formArry["status"] == "REMOVED"){
	        			echo wp_json_encode(array('error'=>false, 'message'=> "Campaign Removed Successfully with resource name - ".$resource_name));
	        		}else{
	        			echo wp_json_encode(array('error'=>false, 'message'=> "Campaign Edited Successfully with resource name - ".$resource_name));
	        		}
	        	}else if(isset($api_rs->data)){
	        		echo wp_json_encode(array('error'=>false, 'data' => $api_rs->data));
	        	}
	        }else{
	        	$errormsg = "";
	        	if(!is_array($api_rs->errors) && is_string($api_rs->errors)){
	        		$errormsg = $api_rs->errors;
	        	}else{
	        		$errormsg= isset($api_rs->errors[0])?$api_rs->errors[0]:"";
	        	}
	        	echo wp_json_encode(array('error'=>true, 'message'=>$errormsg,  'status' => $api_rs->status));
	        }
				}	
      }else{
        echo wp_json_encode(array('error'=>true, 'message' => esc_html__("Admin security nonce is not verified.","product-feed-manager-for-woocommerce")));
      }
      exit;
    }

    public function convpfm_call_subscription_refresh()
    {
      if (is_admin() && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['conv_licence_nonce'])), 'conv_lic_nonce')) {
        $TVC_Admin_Helper = new Convpfm_TVC_Admin_Helper();
        $TVC_Admin_Helper->update_subscription_details_api_to_db();
        echo json_encode(array('error' => false, "is_connect" => true, 'message' => esc_html__("Subscription refresh", "product-feed-manager-for-woocommerce")));
      } else {
        echo json_encode(array('error' => true, "is_connect" => false, 'message' => esc_html__("Admin security nonce is not verified.", "product-feed-manager-for-woocommerce")));
      }
      wp_die();
    }
    public function convpfm_create_google_merchant_center_account(){
      $nonce = (isset($_POST['conversios_onboarding_nonce']))?sanitize_text_field($_POST['conversios_onboarding_nonce']):"";
      if($this->safe_ajax_call($nonce, 'conversios_onboarding_nonce')){ 
        $customApiObj = new Convpfm_CustomApi();        
        echo json_encode($customApiObj->createMerchantAccount($_POST));
        wp_die();
      }else{
        echo esc_html__("Admin security nonce is not verified.","product-feed-manager-for-woocommerce");
      }
    }
  }
  // End of TVC_Ajax_File_Class
endif;
$tvcajax_file_class = new Convpfm_Ajax_File();