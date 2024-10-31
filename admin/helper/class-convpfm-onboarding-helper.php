<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       
 * @since      1.0.0
 *
 * Woo Order Reports
 */

if(!defined('ABSPATH')){
  exit; // Exit if accessed directly
}
if(!class_exists('Convpfm_Conversios_Onboarding_Helper')):
  class Convpfm_Conversios_Onboarding_Helper{
    protected $apiDomain;
    protected $token;
    public function __construct(){
      $this->req_int();
      // add_action('wp_ajax_save_analytics_data', array($this,'save_analytics_data') );
      //googl_ads
      add_action('wp_ajax_convpfm_list_googl_ads_account', array($this,'convpfm_list_googl_ads_account') );
      add_action('wp_ajax_convpfm_create_google_ads_account', array($this,'convpfm_create_google_ads_account') );  
      
      //google_merchant
      add_action('wp_ajax_convpfm_list_google_merchant_account', array($this,'convpfm_list_google_merchant_account') );
     
      // add_action('wp_ajax_save_merchant_data', array($this,'save_merchant_data') );
      add_action('wp_ajax_link_google_ads_to_merchant_center', array($this,'link_google_ads_to_merchant_center') );

      //get subscription details
      // add_action('wp_ajax_get_subscription_details', array($this,'get_subscription_details') );
      // add_action('wp_ajax_update_setup_time_to_subscription', array($this,'update_setup_time_to_subscription') );
    }

    public function req_int(){
      if (!class_exists('Convpfm_CustomApi')) {
        require_once(CONVPFM_ENHANCAD_PLUGIN_DIR . 'includes/setup/convpfm-customApi.php');
      }
    }
    protected function admin_safe_ajax_call( $nonce, $registered_nonce_name ) {
      // only return results when the user is an admin with manage options
      if ( is_admin() && wp_verify_nonce($nonce,$registered_nonce_name) ) {
        return true;
      } else {
        return false;
      }
    }

    /**
     * Ajax code for list googl ads account.
     * @since    4.0.2
     */
    public function convpfm_list_googl_ads_account(){
      $nonce = (isset($_POST['conversios_onboarding_nonce']))?sanitize_text_field($_POST['conversios_onboarding_nonce']):"";
      if($this->admin_safe_ajax_call($nonce, 'conversios_onboarding_nonce')){ 
        $data = isset($_POST['tvc_data'])?sanitize_text_field($_POST['tvc_data']):"";
        $tvc_data = json_decode(str_replace("&quot;", "\"", $data));
        $customApiObj = new Convpfm_CustomApi();
        $google_detail = $customApiObj->getGoogleAnalyticDetail($tvc_data->subscription_id);
        $access_token = isset($google_detail->data->access_token) ? base64_encode($google_detail->data->access_token) : '';
        $refresh_token = isset($google_detail->data->refresh_token) ? base64_encode($google_detail->data->refresh_token) : '';
        $api_obj = new Convpfm_Conversios_Onboarding_ApiCall(sanitize_text_field($access_token), sanitize_text_field($refresh_token));
        echo json_encode($api_obj->getGoogleAdsAccountList($_POST));
        wp_die();
      }else{
        echo esc_html__("Admin security nonce is not verified.","product-feed-manager-for-woocommerce");
      }
    }
    /**
     * Ajax code for create google ads account.
     * @since    4.0.2
     */
    public function convpfm_create_google_ads_account(){
      $nonce = (isset($_POST['conversios_onboarding_nonce']))?sanitize_text_field($_POST['conversios_onboarding_nonce']):"";
      if($this->admin_safe_ajax_call($nonce, 'conversios_onboarding_nonce')){          
        $customApiObj = new Convpfm_CustomApi();
        echo json_encode($customApiObj->createGoogleAdsAccount($_POST));
        wp_die();
      }else{
        echo esc_html__("Admin security nonce is not verified.","product-feed-manager-for-woocommerce");
      }
    }    

    

    /**
     * Ajax code for list google merchant account.
     * @since    4.0.2
     */
    public function convpfm_list_google_merchant_account(){
      $nonce = (isset($_POST['conversios_onboarding_nonce']))?sanitize_text_field($_POST['conversios_onboarding_nonce']):"";
      if($this->admin_safe_ajax_call($nonce, 'conversios_onboarding_nonce')){ 
        $data = isset($_POST['tvc_data'])?sanitize_text_field($_POST['tvc_data']):"";
        $tvc_data = json_decode(str_replace("&quot;", "\"", $data));
        $customApiObj = new Convpfm_CustomApi();
        $google_detail = $customApiObj->getGoogleAnalyticDetail($tvc_data->subscription_id);
        $access_token = isset($google_detail->data->access_token) ? base64_encode($google_detail->data->access_token) : '';
        $refresh_token = isset($google_detail->data->refresh_token) ? base64_encode($google_detail->data->refresh_token) : '';
        $api_obj = new Convpfm_Conversios_Onboarding_ApiCall(sanitize_text_field($access_token), sanitize_text_field($refresh_token));
        echo json_encode($api_obj->listMerchantCenterAccount($_POST));
        wp_die();
      }else{
        echo esc_html__("Admin security nonce is not verified.","product-feed-manager-for-woocommerce");
      }
    }
    /**
     * Ajax code for link analytic to ads account.
     * @since    4.0.2
     */
    

    /**
     * Ajax code for save merchant data.
     * @since    4.0.2
     */
    public function save_merchant_data(){
      $nonce = (isset($_POST['conversios_onboarding_nonce']))?sanitize_text_field($_POST['conversios_onboarding_nonce']):"";
      if($this->admin_safe_ajax_call($nonce, 'conversios_onboarding_nonce')){ 
        $data = isset($_POST['tvc_data'])?sanitize_text_field($_POST['tvc_data']):"";
        $tvc_data = json_decode(str_replace("&quot;", "\"", $data));
        $api_obj = new Convpfm_Conversios_Onboarding_ApiCall(sanitize_text_field($tvc_data->access_token), sanitize_text_field($tvc_data->refresh_token));
        echo json_encode($api_obj->saveMechantData($_POST));
        wp_die();
      }else{
        echo esc_html__("Admin security nonce is not verified.","product-feed-manager-for-woocommerce");
      }
    }
    
    
    /**
     * Ajax code for link google ads to merchant center.
     * @since    4.0.2
     */
    public function link_google_ads_to_merchant_center(){
      $nonce = (isset($_POST['conversios_onboarding_nonce']))?sanitize_text_field($_POST['conversios_onboarding_nonce']):"";
      if($this->admin_safe_ajax_call($nonce, 'conversios_onboarding_nonce')){ 
        $data = isset($_POST['tvc_data'])?sanitize_text_field($_POST['tvc_data']):"";
        $tvc_data = json_decode(str_replace("&quot;", "\"", $data));
        $api_obj = new Convpfm_Conversios_Onboarding_ApiCall(sanitize_text_field($tvc_data->access_token), sanitize_text_field($tvc_data->refresh_token));
        echo json_encode($api_obj->linkGoogleAdsToMerchantCenter($_POST));
        wp_die();
      }else{
        echo esc_html__("Admin security nonce is not verified.","product-feed-manager-for-woocommerce");
      }
    }
      
  }

endif; // class_exists
new Convpfm_Conversios_Onboarding_Helper();

if(!class_exists('Convpfm_Conversios_Onboarding_ApiCall') ){
  class Convpfm_Conversios_Onboarding_ApiCall {
    protected $apiDomain;
    protected $token;
    protected $merchantId;
    protected $access_token;
    protected $refresh_token;
    public function __construct($access_token, $refresh_token) {
      $merchantInfo = json_decode(file_get_contents(CONVPFM_ENHANCAD_PLUGIN_DIR.'includes/setup/json/merchant-info.json'), true);
      $this->refresh_token = $refresh_token;
      $access_token_value = $this->generateAccessToken(base64_decode($access_token ?? ''), base64_decode($this->refresh_token ?? ''));
      $this->access_token = base64_encode($access_token_value);
      $this->apiDomain = CONVPFM_API_CALL_URL;
      $this->token = 'MTIzNA==';
      $this->merchantId = sanitize_text_field($merchantInfo['merchantId']);
    }
    public function tc_wp_remot_call_post($url, $args){
      try {
        if(!empty($args)){    
          // Send remote request
          $args['timeout']= "1000";
          $request = wp_remote_post($url, $args);

          // Retrieve information
          $response_code = wp_remote_retrieve_response_code($request);

          $response_message = wp_remote_retrieve_response_message($request);
          $response_body = json_decode(wp_remote_retrieve_body($request));

          if ((isset($response_body->error) && $response_body->error == '')) {
            return new WP_REST_Response($response_body->data);
          } else {
              return new WP_Error($response_code, $response_message, $response_body);
          }
        }
      } catch (Exception $e) {
          return $e->getMessage();
      }
    }

    public function getGoogleAdsAccountList($postData) {
      try {
        if($this->refresh_token != ""){
          $url = $this->apiDomain . '/adwords/list';
          $refresh_token = sanitize_text_field(base64_decode($this->refresh_token));
          $args = array(
            'timeout' => 10000,
            'headers' => array(
              'Authorization' => "Bearer MTIzNA==",
              'Content-Type' => 'application/json',
              'RefreshToken' => $refresh_token
            ),
            'body' => ""
          );
          $request = wp_remote_post(esc_url_raw($url), $args);
          
          // Retrieve information
          $response_code = wp_remote_retrieve_response_code($request);
          $response_message = wp_remote_retrieve_response_message($request);
          $response = json_decode(wp_remote_retrieve_body($request));
          $return = new \stdClass();
          if (isset($response->error) && $response->error == '') {
            $return->status = $response_code;
            $return->data = $response->data;
            $return->error = false;
            return $return;
          }else{
            $return->error = true;
            $return->data = $response->data;
            $return->status = $response_code;
            $return->errors = json_encode($response->errors);
            return $return;
          }       
        }else{
          return json_decode(array("error"=>true));
        }
      } catch (Exception $e) {
          return $e->getMessage();
      }
    }

    public function listMerchantCenterAccount() {
      try {
        $url = $this->apiDomain . '/gmc/user-merchant-center/list';
        $header = array("Authorization: Bearer MTIzNA==", "Content-Type" => "application/json");
        $data = [
          'access_token' => sanitize_text_field(base64_decode($this->access_token)),
        ];
        $args = array(
          'timeout' => 10000,
          'headers' =>$header,
          'method' => 'POST',
          'body' => wp_json_encode($data)
        );
        $result = $this->tc_wp_remot_call_post(esc_url_raw($url), $args);
        $return = new \stdClass();
        if($result->status == 200){
          $return->status = $result->status;
          $return->data = $result->data;
          $return->error = false;
          return $return;
        }else{
          $return->error = true;
          $return->data = $result->data;
          $return->status = $result->status;
          $return->errors = json_encode($result->errors);
          return $return;
        }
      } catch (Exception $e) {
        return $e->getMessage();
      }
    }

    
    public function createMerchantAccount($postData) {
      try {
        $url = $this->apiDomain . '/gmc/create';
        $header = array(
            "Authorization: Bearer MTIzNA==",
            "Content-Type" => "application/json"
        );
        $data = [
          'merchant_id' => sanitize_text_field($this->merchantId), 
          'name' => sanitize_text_field($postData['store_name']),
          'website_url' => esc_url_raw(sanitize_text_field($postData['website_url'])),
          'customer_id' => sanitize_text_field($postData['customer_id']),
          'adult_content' => isset($postData['adult_content']) && sanitize_text_field($postData['adult_content']) == 'true' ? true : false,
          'country' => sanitize_text_field($postData['country']),
          'subscription_id' => sanitize_text_field($postData['subscription_id']),
          'users' => [
            [
              "email_address" => sanitize_email($postData['email_address']),
              "admin" => true
            ]
          ],
          'business_information' => [
            'address' => [
                'country' => sanitize_text_field($postData['country'])
            ]
          ]
        ];
        $args = array(
          'timeout' => 10000,
          'headers' =>$header,
          'method' => 'POST',
          'body' => wp_json_encode($data)
        );
        $args['timeout']= "1000";
        $request = wp_remote_post(esc_url_raw($url), $args);
        
        // Retrieve information
        $response_code = wp_remote_retrieve_response_code($request);
        $response_message = wp_remote_retrieve_response_message($request);
        $response_body = json_decode(wp_remote_retrieve_body($request));
        if ((isset($response_body->error) && $response_body->error == '') || (!isset($response_body->error)) ) {
          //create merchant account admin notices
          $Convpfm_TVC_Admin_Helper = new Convpfm_TVC_Admin_Helper();
          $link_title = "Create Performance max campaign now.";
          $content = "Create your first Google Ads performance max campaign using the plugin and get $500 as free credits.";
          $status = "1";
          $created_merchant_id = $response_body->account->id;
          $link = "admin.php?page=conversios-pmax";
          $Convpfm_TVC_Admin_Helper->convpfm_add_admin_notice("created_merchant_account", $content, $status, $link_title, $link, $created_merchant_id,"","7","created_merchant_account");
          return $response_body;
        } else {
          $return = new \stdClass();
          $return->error = true;
          $return->errors = json_encode($response_code->errors);
          //$return->data = $result->data;
          $return->status = $response_code;
          return $return;
          //return new WP_Error($response_code, $response_message, $response_body);
        }
      } catch (Exception $e) {
        return $e->getMessage();
      }
    }

    public function saveMechantData($postData = array()) {
      try {
        $url = $this->apiDomain . '/customer-subscriptions/update-detail';
        $header = array("Authorization: Bearer MTIzNA==", "Content-Type" => "application/json");
        $data = [
          'merchant_id' => sanitize_text_field(($postData['merchant_id'] == 'NewMerchant') ? $this->merchantId: $postData['merchant_id']),
          'subscription_id' => sanitize_text_field((isset($postData['subscription_id']))?$postData['subscription_id'] : ''),
          'google_merchant_center_id' => sanitize_text_field((isset($postData['google_merchant_center']))? $postData['google_merchant_center'] : ''),
          'website_url' => sanitize_text_field($postData['website_url']),
          'customer_id' => sanitize_text_field($postData['customer_id'])
        ];
        $args = array(
          'headers' =>$header,
          'method' => 'POST',
          'body' => wp_json_encode($data)
        );
        $result = $this->tc_wp_remot_call_post(esc_url_raw($url), $args);
        $return = new \stdClass();
        if($result->status == 200){
          $return->status = $result->status;
          $return->data = $result->data;
          $return->error = false;
          return $return;
        }else{
          $return->error = true;
          $return->data = $result->data;
          $return->status = $result->status;
          $return->errors = json_encode($result->errors);
          return $return;
        }
      } catch (Exception $e) {
        return $e->getMessage();
      }
    }

    public function linkAnalyticToAdsAccount($postData) {
      try {
        $url = $this->apiDomain . '/google-analytics/link-ads-to-analytics';
        $access_token = sanitize_text_field(base64_decode($this->access_token));
        $refresh_token = sanitize_text_field(base64_decode($this->refresh_token));
        if ($postData['type'] == "UA") {
          $data = [
            'type' => sanitize_text_field($postData['type']),
            'ads_customer_id' => sanitize_text_field($postData['ads_customer_id']), 
            'analytics_id' => sanitize_text_field($postData['analytics_id']), 
            'web_property_id' => sanitize_text_field($postData['web_property_id']), 
            'profile_id' => sanitize_text_field($postData['profile_id']),
          ];
        } else {
          $data = [
            'type' => sanitize_text_field($postData['type']),
            'ads_customer_id' => sanitize_text_field($postData['ads_customer_id']), 
            'analytics_id' => '', 
            'web_property_id' => sanitize_text_field($postData['web_property_id']), 
            'profile_id' => '', 
            'web_property' => sanitize_text_field($postData['web_property']),
          ];
        }
        
        $args = array(
          'timeout' => 10000,
          'headers' => array(
              'Authorization' => "Bearer $this->token",
              'Content-Type' => 'application/json',
              'AccessToken' => $access_token,
              'RefreshToken' => $refresh_token
          ),
          'method' => 'POST',
          'body' => wp_json_encode($data)
        );
        $request = wp_remote_post(esc_url_raw($url), $args);

        // Retrieve information
        $response_code = wp_remote_retrieve_response_code($request);
        $response_message = wp_remote_retrieve_response_message($request);
        $result = json_decode(wp_remote_retrieve_body($request));
        $return = new \stdClass();
        if($response_code == 200 && isset($result->error) && $result->error == ''){
          $return->status = $response_code;
          $return->data = $result->data;
          $return->error = false;
          return $return;
        }else{
          $return->error = true;
          $return->errors = $result->errors;
          $return->status = $response_code;
          return $return;
        }
      } catch (Exception $e) {
          return $e->getMessage();
      }
    }
    public function linkGoogleAdsToMerchantCenter($postData) {
      try {
        $url = $this->apiDomain . '/adwords/link-ads-to-merchant-center';
        $access_token = sanitize_text_field(base64_decode($this->access_token));
        $data = [
          'merchant_id' => sanitize_text_field(($postData['merchant_id']) == 'NewMerchant' ?  $this->merchantId: $postData['merchant_id']),
          'account_id' => sanitize_text_field($postData['account_id']),
          'adwords_id' => sanitize_text_field($postData['adwords_id']),
          'subscription_id' => sanitize_text_field($postData['subscription_id'])
        ];
        $args = array(
          'timeout' => 10000,
            'headers' => array(
                'Authorization' => "Bearer $this->token",
                'Content-Type' => 'application/json',
                'AccessToken' => $access_token
            ),
            'method' => 'POST',
            'body' => wp_json_encode($data)
        );

        // Send remote request
        $request = wp_remote_post(esc_url_raw($url), $args);

        // Retrieve information
        $response_code = wp_remote_retrieve_response_code($request);
        $response_message = wp_remote_retrieve_response_message($request);
        $result = json_decode(wp_remote_retrieve_body($request));
        $return = new \stdClass();
        if($response_code == 200){
          $return->status = $response_code;
          $return->data = $result->data;
          $return->error = false;
          return $return;
        }else{
          $return->error = true;
          $return->errors = $result->errors;
          $return->status = $response_code;
          return $return;
        }
        
      } catch (Exception $e) {
        return $e->getMessage();
      }
    }
    public function updateSetupTimeToSubscription($postData) {
      try {
        $url = $this->apiDomain . '/customer-subscriptions/update-setup-time';
        $data = [
          'subscription_id' => sanitize_text_field((isset($postData['subscription_id']))?$postData['subscription_id'] : ''),
          'setup_end_time' => date('Y-m-d H:i:s')
        ];
        $args = array(
            'timeout' => 10000,
            'headers' => array(
                'Authorization' => "Bearer $this->token",
                'Content-Type' => 'application/json'
            ),
            'method' => 'POST',
            'body' => wp_json_encode($data)
        );

        // Send remote request
        $request = wp_remote_post(esc_url_raw($url), $args);

        // Retrieve information
        $response_code = wp_remote_retrieve_response_code($request);
        $response_message = wp_remote_retrieve_response_message($request);
        $result = json_decode(wp_remote_retrieve_body($request));
        $return = new \stdClass();
        if($response_code == 200){
          $return->status = $response_code;
          $return->data = $result->data;
          $return->error = false;
          return $return;
        }else{
          $return->error = true;
          // $return->errors = $result->errors;
          $return->status = $response_code;
          $return->errors = json_decode($result->errors[0]);
          $return->errors = json_decode($result->errors[0]);
          return $return;
        }
        
      } catch (Exception $e) {
        return $e->getMessage();
      }
    }
    
    public function generateAccessToken($access_token, $refresh_token) {
      $url = "https://www.googleapis.com/oauth2/v1/tokeninfo?access_token=" . $access_token;
      $request =  wp_remote_get(esc_url_raw($url), array('timeout' => 10000));
      $response_code = wp_remote_retrieve_response_code($request);

      $response_message = wp_remote_retrieve_response_message($request);
      $result = json_decode(wp_remote_retrieve_body($request));
      
      if (isset($result->error) && $result->error) {
          $credentials = json_decode(file_get_contents(CONVPFM_ENHANCAD_PLUGIN_DIR . 'includes/setup/json/client-secrets.json'), true);
          $url = 'https://www.googleapis.com/oauth2/v4/token';
          $header = array("Content-Type" => "application/json");
          $clientId = $credentials['web']['client_id'];
          $clientSecret = $credentials['web']['client_secret'];
          
          $data = [
              "grant_type" => 'refresh_token',
              "client_id" => sanitize_text_field($clientId),
              'client_secret' => sanitize_text_field($clientSecret),
              'refresh_token' => sanitize_text_field($refresh_token),
          ];
          $args = array(
            'timeout' => 10000,
            'headers' =>$header,
            'method' => 'POST',
            'body' => wp_json_encode($data)
          );
          $request = wp_remote_post(esc_url_raw($url), $args);
          // Retrieve information
          $response_code = wp_remote_retrieve_response_code($request);
          $response_message = wp_remote_retrieve_response_message($request);
          $response = json_decode(wp_remote_retrieve_body($request));
          if(isset($response->access_token)){
            $Convpfm_TVC_Admin_Helper = new Convpfm_TVC_Admin_Helper();
            $google_detail = $Convpfm_TVC_Admin_Helper->get_convpfm_api_data();
            $google_detail["setting"]->access_token = base64_encode(sanitize_text_field($response->access_token));
            $Convpfm_TVC_Admin_Helper->set_convpfm_api_data($google_detail);
            return $response->access_token; 
          }else{
              //return $access_token;
          }
      } else {
        return $access_token;
      }
    }//generateAccessToken    
  }
}