<?php

/**
 * Fired during plugin activation
 *
 * @link       test.com
 * @since      1.0.0
 *
 * @package    Convpfm_Enhanced_Ecommerce_Google_Analytics_Activator
 * @subpackage Convpfm_Enhanced_Ecommerce_Google_Analytics_Activator/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Convpfm_Enhanced_Ecommerce_Google_Analytics_Activator
 * @subpackage Convpfm_Enhanced_Ecommerce_Google_Analytics_Activator/includes
 * @author     Tatvic
 */

class Convpfm_Enhanced_Ecommerce_Google_Analytics_Activator
{

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate()
    {
        if (!is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
           wp_die(wp_sprintf("%s <br><a href='" . esc_url_raw(admin_url( 'plugins.php' )) . "'>&laquo; %s</a>", esc_html__("Hey, It seems WooCommerce plugin is not active on your wp-admin. Conversios.io - Product Feed Manager for WooCommerce plugin can only be activated if you have active WooCommerce plugin in your wp-admin.","product-feed-manager-for-woocommerce"), esc_html__("Return to Plugins","product-feed-manager-for-woocommerce")));
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
        $convpfm_options_settings = unserialize(get_option('convpfm_options'));
        $subscriptionId = (isset($convpfm_options_settings['subscription_id'])) ? $convpfm_options_settings['subscription_id'] : "";

        $apiDomain = "https://connect.tatvic.com/laravelapi/public/api";   
        $header = array(
            "Authorization: Bearer 'MTIzNA=='",
            "Content-Type" => "application/json"
        );
        
        if (empty($subscriptionId)) {
            $current_user = wp_get_current_user();

            /******** Do customer login API Call Start *****************/ 
            $url = $apiDomain . '/customers/login';
            $header = array("Authorization: Bearer MTIzNA==", "content-type: application/json");
            $data = [
                'first_name' => "",
                'last_name' => "",
                'access_token' => "",
                'refresh_token' => "",
                'email' => $current_user->user_email,
                'sign_in_type' => 1,
                'app_id' => 4,
                'platform_id' => 1
            ];

            $curl_url = $url;
            $data = json_encode($data);
            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_URL => $curl_url, 
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_HTTPHEADER => $header,
                CURLOPT_POSTFIELDS => $data
            ));
            $dologin_response = curl_exec($ch);
            $dologin_response = json_decode($dologin_response);
            /******** Do customer login API Call End *****************/

            /**** Update token to subs Start ************************/ 
            $url = $apiDomain . '/customer-subscriptions/update-token';
            $header = array("Authorization: Bearer MTIzNA==", "content-type: application/json");
            $data = [
                'subscription_id' => "",
                'gmail' => $current_user->user_email,
                'access_token' => "",
                'refresh_token' => "",
                'domain' => get_site_url(),
                'app_id' =>  4,
                'platform_id' => 1
            ];

            $curl_url = $url;
            $data = json_encode($data);
            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_URL => $curl_url, 
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_HTTPHEADER => $header,
                CURLOPT_POSTFIELDS => $data
            ));

            $updatetoken_response = curl_exec($ch);
            $updatetoken_response = json_decode($updatetoken_response);
            /**** Update token to subs End *******************/ 

            /************ Get subscription details Start *****/
            $url = $apiDomain . '/customer-subscriptions/subscription-detail';
            $header = array("Authorization: Bearer MTIzNA==", "content-type: application/json");
            $data = [
                'subscription_id' => $updatetoken_response->data->customer_subscription_id,
                'domain' => get_site_url(),
                'app_id' => 4,
                'platform_id' => 1
            ];
            $curl_url = $url;
            $postData = json_encode($data);
            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_URL => $curl_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_HTTPHEADER => $header,
                CURLOPT_POSTFIELDS => $postData
            ));
            $subsdetails_response = curl_exec($ch);
            $subsdetails_response = json_decode($subsdetails_response);
            $eeapidata = array("setting" => $subsdetails_response->data);
            /************ Get subscription details End *****/
            
            update_option("convpfm_api_data", serialize($eeapidata));

            $subscriptiondata = $subsdetails_response->data;   
            $eeoptions = array();
            $eeoptions["subscription_id"] = (isset($subscriptiondata->id) && $subscriptiondata->id != "") ? sanitize_text_field($subscriptiondata->id) : "";                
            $eeoptions["gm_id"] = (isset($subscriptiondata->measurement_id) && $subscriptiondata->measurement_id != "") ? sanitize_text_field($subscriptiondata->measurement_id) : "";
            $eeoptions["ga_id"] = (isset($subscriptiondata->property_id) && $subscriptiondata->property_id != "") ? sanitize_text_field($subscriptiondata->property_id) : "";
            $eeoptions["google_ads_id"] = (isset($subscriptiondata->google_ads_id) && $subscriptiondata->google_ads_id != "") ? sanitize_text_field($subscriptiondata->google_ads_id) : "";
            $eeoptions["google_merchant_id"] = (isset($subscriptiondata->google_merchant_center_id) && $subscriptiondata->google_merchant_center_id != "") ? sanitize_text_field($subscriptiondata->google_merchant_center_id) : "";
            $eeoptions["google_merchant_center_id"] = (isset($subscriptiondata->google_merchant_center_id) && $subscriptiondata->google_merchant_center_id != "") ? sanitize_text_field($subscriptiondata->google_merchant_center_id) : "";                
            $eeoptions["ga_PrivacyPolicy"] = "on";                
            update_option("convpfm_options", serialize($eeoptions));
                
        }
            $Convpfm_TVC_Admin_Helper = new Convpfm_TVC_Admin_Helper();
            $Convpfm_TVC_Admin_Helper->app_activity_detail("activate");
            $Convpfm_TVC_Admin_Helper->update_app_status();  
            require_once(CONVPFM_ENHANCAD_PLUGIN_DIR  . 'includes/setup/GenerateFile/classes/class-activate.php');
            \Convpfm_Activation::activate_checks();
    }
}
