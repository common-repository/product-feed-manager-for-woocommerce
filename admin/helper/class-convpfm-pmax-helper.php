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
if(!class_exists('Convpfm_PMax_Helper')){
	class Convpfm_PMax_Helper{
		protected $Convpfm_TVC_Admin_Helper;
		protected $CustomApi;
		protected $apiDomain;
		protected $token;
		public function __construct(){
			$this->req_int();
			$this->apiDomain = CONVPFM_API_CALL_URL;
			$this->token = 'MTIzNA==';
			$this->Convpfm_TVC_Admin_Helper = new Convpfm_TVC_Admin_Helper();
      $this->CustomApi = new Convpfm_CustomApi();
			//add_action('wp_ajax_get_google_ads_reports_chart', array($this,'get_google_ads_reports_chart') );
			add_action('wp_ajax_convpfm_get_pmax_campaign_list', array($this,'get_pmax_campaign_list') );
			add_action('wp_ajax_create_pmax_campaign', array($this,'create_pmax_campaign') );
			add_action('wp_ajax_edit_pmax_campaign', array($this,'edit_pmax_campaign') );
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
		public function get_pmax_campaign_list(){
			$nonce = (isset($_POST['conversios_nonce']))?sanitize_text_field($_POST['conversios_nonce']):"";
			$return = array();
			if($this->admin_safe_ajax_call($nonce, 'conversios_nonce')){
				
				$customer_id =(isset($_POST['google_ads_id']))?sanitize_text_field($_POST['google_ads_id']):"";
				$page_size =(isset($_POST['page_size']))?sanitize_text_field($_POST['page_size']):"10";
				$page_token =(isset($_POST['page_token']))?sanitize_text_field($_POST['page_token']):"";
				$page =(isset($_POST['page']))?sanitize_text_field($_POST['page']):"";
				if($customer_id != ""){
					$api_rs = $this->campaign_pmax_list($customer_id, $page_size, $page_token, $page);  
					if (isset($api_rs->error) && $api_rs->error == '') {
	        	if(isset($api_rs->data) && $api_rs->data != ""){
              $totalRecords = count($api_rs->data->results);
              $data = array();
              foreach ($api_rs->data->results as $result) {
                $status = $result->campaign->status == 'ENABLED' ? 'Enabled':'Paused';
                $dailyBudget = number_format((float)$result->campaignBudget->amountMicros / 1000000, 2, '.', '');
                $sales = $result->metrics->conversionsValue;
                $data[] = array(
                  'campaignName' => $result->campaign->name,
                  'status' => $status,
                  'dailyBudget' => $dailyBudget,
                  'click' => $result->metrics->clicks,
                  'cost' => number_format((float)$result->metrics->costMicros/1000000, 2, '.', ''),
                  'conversion' => $result->metrics->conversions,
                  'sale' => $sales,
                  'more' => '<label class="text-primary" onclick="editCampaign('.$result->campaign->id.')"><span class="material-symbols-outlined">edit</span>Edit</label>',
                );
              }
	        		// $return = array('error'=>false, 'data'=>$api_rs->data);
              $result = array(
                'draw' => sanitize_text_field($_POST['draw']),
                'recordsTotal' => sanitize_text_field($totalRecords),
                'recordsFiltered' => sanitize_text_field($totalRecords),
                'data' => $data
              );
              echo wp_json_encode($result);
	        	}
	        }else{
	        	$errormsg= isset($api_rs->errors[0])?$api_rs->errors[0]:"";
	        	$return = array('error'=>true,'errors'=>$errormsg,  'status' => $api_rs->status);
	        }
				}				
			}else{
      	$return = array('error'=>true,'errors'=>esc_html__("Admin security nonce is not verified.","product-feed-manager-for-woocommerce"));
      }
      echo json_encode($return);
			wp_die();
		}
		public function create_pmax_campaign(){
			$nonce = (isset($_POST['conversios_nonce']))?sanitize_text_field($_POST['conversios_nonce']):"";
			$return = array();
			if($this->admin_safe_ajax_call($nonce, 'conversios_nonce')){
				$data = isset($_POST['tvc_data'])?$_POST['tvc_data']:"";				
		    parse_str($data, $form_array);    
		    if(!empty($form_array)){
		      foreach ($form_array as $key => $value) {
		        $form_array[$key] = sanitize_text_field($value);
		      }
		    }
		    unset($form_array["site_key"]);
		    unset($form_array["site_url"]);
		    if($form_array["target_roas"] != ""){
		    	$form_array["target_roas"] = $form_array["target_roas"]/100;
		    }else{
		    	unset($form_array["target_roas"]);
		    }
		    
		    $require_fields = array("customer_id","merchant_id","campaign_name","budget","target_country");
		    foreach($require_fields as $val){
		    	if(isset($form_array[$val]) && $form_array[$val] ==""){
		    		$return = array('error'=>true, 'message'=>esc_html__(str_replace("_"," ",$val)." is required field.","product-feed-manager-for-woocommerce"));
		    	}
		    }
		    if(!empty($return)){
		    	echo json_encode($return);
					wp_die();
		    }else	if(isset($form_array["customer_id"]) ){
					$api_rs = $this->create_pmax_campaign_callapi($form_array);					
					if (isset($api_rs->error) && $api_rs->error == '') {
	        	if(isset($api_rs->data->results[0]->resourceName) && $api_rs->data != ""){
	        		$resource_name = $api_rs->data->results[0]->resourceName;
	        		$return = array('error'=>false, 'message'=> "Campaign Created Successfully with resource name - ".$resource_name);
	        	}else if(isset($api_rs->data)){
	        		$return = array('error'=>false, 'data' => $api_rs->data);
	        	}
	        }else{
	        	$errormsg = "";
	        	if(!is_array($api_rs->errors) && is_string($api_rs->errors)){
	        		$errormsg = $api_rs->errors;
	        	}else{
	        		$errormsg= isset($api_rs->errors[0])?$api_rs->errors[0]:"";
	        	}
	        	$return = array('error'=>true, 'message'=>$errormsg,  'status' => $api_rs->status);
	        }
				}				
			}else{
      	$return = array('error'=>true, 'message' => esc_html__("Admin security nonce is not verified.","product-feed-manager-for-woocommerce"));
      }
      echo json_encode($return);
			wp_die();
		}
		public function edit_pmax_campaign(){
			$nonce = (isset($_POST['conversios_nonce']))?sanitize_text_field($_POST['conversios_nonce']):"";
			$return = array();
			if($this->admin_safe_ajax_call($nonce, 'conversios_nonce')){
				$data = isset($_POST['tvc_data'])?$_POST['tvc_data']:"";
		    parse_str($data, $form_array);    
		    if(!empty($form_array)){
		      foreach ($form_array as $key => $value) {
		        $form_array[$key] = sanitize_text_field($value);
		      }
		    }		    
		    $require_fields = array("customer_id","merchant_id","campaign_name","budget","target_country","campaign_budget_resource_name","resource_name");
		    foreach($require_fields as $val){
		    	if(isset($form_array[$val]) && $form_array[$val] ==""){
		    		$return = array('error'=>true, 'message'=>esc_html__(str_replace("_"," ",$val)." is required field.","product-feed-manager-for-woocommerce"));
		    	}
		    }
		    if(!empty($return)){
		    	echo json_encode($return);
					wp_die();
		    }else	if(isset($form_array["customer_id"]) ){
		    	$api_rs = "";
		    	if($form_array["status"] == "REMOVED"){
		    		$api_rs = $this->delete_pmax_campaign_callapi( array("customer_id"=>$form_array["customer_id"], "resource_name"=>$form_array["resource_name"]) );
		    	}else{
						$api_rs = $this->edit_pmax_campaign_callapi($form_array);	
					}				
					if (isset($api_rs->error) && $api_rs->error == '') {
	        	if(isset($api_rs->data->results[0]->resourceName) && $api_rs->data != ""){
	        		$resource_name = $api_rs->data->results[0]->resourceName;
	        		if($form_array["status"] == "REMOVED"){
	        			$return = array('error'=>false, 'message'=> "Campaign Removed Successfully with resource name - ".$resource_name);
	        		}else{
	        			$return = array('error'=>false, 'message'=> "Campaign Edit Successfully with resource name - ".$resource_name);
	        		}
	        	}else if(isset($api_rs->data)){
	        		$return = array('error'=>false, 'data' => $api_rs->data);
	        	}
	        }else{
	        	$errormsg = "";
	        	if(!is_array($api_rs->errors) && is_string($api_rs->errors)){
	        		$errormsg = $api_rs->errors;
	        	}else{
	        		$errormsg= isset($api_rs->errors[0])?$api_rs->errors[0]:"";
	        	}
	        	$return = array('error'=>true, 'message'=>$errormsg,  'status' => $api_rs->status);
	        }
				}				
			}else{
      	$return = array('error'=>true, 'message' => esc_html__("Admin security nonce is not verified.","product-feed-manager-for-woocommerce"));
      }
      echo json_encode($return);
			wp_die();
		}
		

		/*API CALL*/
		public function campaign_pmax_detail($customer_id, $campaign_id) {
      try {
        $url = $this->apiDomain . '/pmax/detail';        
        $data = [
          'customer_id' => $customer_id,
          'campaign_id' => $campaign_id,
          'access_token' => $this->CustomApi->generateAccessToken( $this->CustomApi->get_tvc_access_token(), $this->CustomApi->get_tvc_refresh_token() )
        ];
        $header = array(
          "Authorization: Bearer $this->token",
          "Content-Type" => "application/json"
        );
        $args = array(
        	'timeout' => 10000,
          'headers' =>$header,
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
        if( isset($result->error) && isset($result->data) && $result->error == '' ) {
          $return->data = (isset($result->data))?$result->data:"";
          $return->error = false;
          return $return;
        }else{
          $return->error = true;
          $return->data = (isset($result->data))?$result->data:"";
          $return->errors = $result->errors;
          $return->status = $response_code;
          return $return;
        }
          
      } catch (Exception $e) {
          return $e->getMessage();
      }
    }
		public function campaign_pmax_list($customer_id, $page_size, $page_token, $page) {
      try {
        $url = $this->apiDomain . '/pmax/list';        
        $data = [
          'customer_id' => $customer_id,
          'page_size' => $page_size,
          'page_token' => $page_token,
          'access_token' => $this->CustomApi->generateAccessToken( $this->CustomApi->get_tvc_access_token(), $this->CustomApi->get_tvc_refresh_token() )
        ];
        $header = array(
          "Authorization: Bearer $this->token",
          "Content-Type" => "application/json"
        );
        $args = array(
        	'timeout' => 10000,
          'headers' =>$header,
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
        if( isset($result->error) && isset($result->data) && $result->error == '' ) {
          $return->data = (isset($result->data))?$result->data:"";          
          $return->error = false;          
          return $return;
        }else{
          $return->error = true;
          $return->data = (isset($result->data))?$result->data:"";
          $return->errors = $result->errors;
          $return->status = $response_code;
          return $return;
        }
          
      } catch (Exception $e) {
          return $e->getMessage();
      }
    }

    public function create_pmax_campaign_callapi($post_data) {
      try {
        $url = $this->apiDomain . '/pmax/create';
        $post_data["access_token"] =$this->CustomApi->generateAccessToken( $this->CustomApi->get_tvc_access_token(), $this->CustomApi->get_tvc_refresh_token() );
     
        $header = array(
          "Authorization: Bearer $this->token",
          "Content-Type" => "application/json"
        );
        $args = array(
        	'timeout' => 10000,
          'headers' =>$header,
          'method' => 'POST',
          'body' => wp_json_encode($post_data)
        );
        // Send remote request
        $request = wp_remote_post(esc_url_raw($url), $args);

        // Retrieve information
        $response_code = wp_remote_retrieve_response_code($request);
        $response_message = wp_remote_retrieve_response_message($request);
        $result = json_decode(wp_remote_retrieve_body($request));
        $return = new \stdClass();
        if( isset($result->error) && isset($result->data) && $result->error == '' ) {
          $return->data = (isset($result->data))?$result->data:"";
          $return->error = false;
          return $return;
        }else{
          $return->error = true;
          $return->data = (isset($result->data))?$result->data:"";      
        	$result->errors = (array)$result->errors;
      		if(!empty($result->errors) ){
            if(count($result->errors) != count($result->errors, COUNT_RECURSIVE)){
              $return->errors = implode(" & ",array_map(function($a) {return implode("~",$a);},$result->errors));
            }else{
              $return->errors = implode(" ",$result->errors);
            }
          }else{
            $return->errors = $result->errors;
          }
          $return->status = $response_code;
          return $return;
        }
          
      } catch (Exception $e) {
          return $e->getMessage();
      }
    }

    public function edit_pmax_campaign_callapi($post_data) {
      try {
        $url = $this->apiDomain . '/pmax/update';
        $post_data["access_token"] =$this->CustomApi->generateAccessToken( $this->CustomApi->get_tvc_access_token(), $this->CustomApi->get_tvc_refresh_token() );
       
        $header = array(
          "Authorization: Bearer $this->token",
          "Content-Type" => "application/json"
        );
        $args = array(
        	'timeout' => 10000,
          'headers' =>$header,
          'method' => 'POST',
          'body' => wp_json_encode($post_data)
        );
        
        // Send remote request
        $request = wp_remote_post(esc_url_raw($url), $args);

        // Retrieve information
        $response_code = wp_remote_retrieve_response_code($request);
        $response_message = wp_remote_retrieve_response_message($request);
        $result = json_decode(wp_remote_retrieve_body($request));
        $return = new \stdClass();
        if( isset($result->error) && isset($result->data) && $result->error == '' ) {
          $return->data = (isset($result->data))?$result->data:"";
          $return->error = false;
          return $return;
        }else{
          $return->error = true;
          $return->data = (isset($result->data))?$result->data:"";      
        	$result->errors = (array)$result->errors;
      		if(!empty($result->errors) ){
            if(count($result->errors) != count($result->errors, COUNT_RECURSIVE)){
              $return->errors = implode(" & ",array_map(function($a) {return implode("~",$a);},$result->errors));
            }else{
              $return->errors = implode(" ",$result->errors);
            }
          }else{
            $return->errors = $result->errors;
          }
          $return->status = $response_code;
          return $return;
        }
          
      } catch (Exception $e) {
          return $e->getMessage();
      }
    }

    public function delete_pmax_campaign_callapi($post_data) {
      try {
        $url = $this->apiDomain . '/pmax/delete';
        $post_data["access_token"] =$this->CustomApi->generateAccessToken( $this->CustomApi->get_tvc_access_token(), $this->CustomApi->get_tvc_refresh_token() );
        $header = array(
          "Authorization: Bearer $this->token",
          "Content-Type" => "application/json"
        );
        $args = array(
        	'timeout' => 10000,
          'headers' =>$header,
          'method' => 'POST',
          'body' => wp_json_encode($post_data)
        );
        // Send remote request
        $request = wp_remote_post(esc_url_raw($url), $args);

        // Retrieve information
        $response_code = wp_remote_retrieve_response_code($request);
        $response_message = wp_remote_retrieve_response_message($request);
        $result = json_decode(wp_remote_retrieve_body($request));
        $return = new \stdClass();
        if( isset($result->error) && isset($result->data) && $result->error == '' ) {
          $return->data = (isset($result->data))?$result->data:"";
          $return->error = false;
          return $return;
        }else{
          $return->error = true;
          $return->data = (isset($result->data))?$result->data:"";      
        	$result->errors = (array)$result->errors;
      		if(!empty($result->errors) ){
            if(count($result->errors) != count($result->errors, COUNT_RECURSIVE)){
              $return->errors = implode(" & ",array_map(function($a) {return implode("~",$a);},$result->errors));
            }else{
              $return->errors = implode(" ",$result->errors);
            }
          }else{
            $return->errors = $result->errors;
          }
          $return->status = $response_code;
          return $return;
        }
          
      } catch (Exception $e) {
          return $e->getMessage();
      }
    }

    public function get_campaign_currency_code($customer_id) {
      try {
        $url = $this->apiDomain . '/pmax/currency-code';        
        $data = [
          'customer_id' => $customer_id,
          'access_token' => $this->CustomApi->generateAccessToken( $this->CustomApi->get_tvc_access_token(), $this->CustomApi->get_tvc_refresh_token() )
        ];
        $header = array(
          "Authorization: Bearer $this->token",
          "Content-Type" => "application/json"
        );
        $args = array(
        	'timeout' => 10000,
          'headers' =>$header,
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
        if( isset($result->error) && isset($result->data) && $result->error == '' ) {
          $return->data = (isset($result->data))?$result->data:"";          
          $return->error = false;
          return $return;
        }else{
          $return->error = true;
          $return->data = (isset($result->data))?$result->data:"";
          $return->errors = (isset($result->errors))?$result->errors:"";
          $return->status = $response_code;
          return $return;
        }
          
      } catch (Exception $e) {
          return $e->getMessage();
      }
    }
		
	}
}
new Convpfm_PMax_Helper();