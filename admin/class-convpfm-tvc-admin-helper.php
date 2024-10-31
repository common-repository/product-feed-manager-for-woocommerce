<?php
Class Convpfm_TVC_Admin_Helper{
	protected $customApiObj;
	protected $convpfm_api_data = "";
	protected $e_options_settings = "";
	protected $merchantId = "";
	protected $main_merchantId = "";
	protected $subscriptionId = "";
	protected $time_zone = "";
	protected $connect_actual_link = "";
	protected $connect_url = "";
	protected $woo_country = "";
	protected $woo_currency = "";
	protected $currentCustomerId = "";
	protected $user_currency_symbol = "";
	protected $setting_status = "";
	protected $convpfm_additional_data = "";
	protected $Convpfm_Admin_DB_Helper;
	protected $store_data;
	protected $api_subscription_data;
	protected $onboarding_page_url;
	public function __construct() {
    $this->includes();
    $this->customApiObj = new Convpfm_CustomApi();
    $this->Convpfm_Admin_DB_Helper = new Convpfm_Admin_DB_Helper();
    add_action('init',array($this, 'init'));
  }

  public function includes() {
    if (!class_exists('Convpfm_CustomApi')) {
      require_once(CONVPFM_ENHANCAD_PLUGIN_DIR . 'includes/setup/convpfm-customApi.php');
    }   
  }

  public function init(){
  	add_filter('sanitize_option_convpfm_auto_update_id', array($this, 'sanitize_option_convpfm_general'), 10, 2);
    add_filter('sanitize_option_convpfm_api_data', array($this, 'sanitize_option_convpfm_general'), 10, 2);
    add_filter('sanitize_option_convpfm_additional_data', array($this, 'sanitize_option_convpfm_general'), 10, 2);
    add_filter('sanitize_option_convpfm_options', array($this, 'sanitize_option_convpfm_general'), 10, 2);
    add_filter('sanitize_option_convpfm_msg_nofifications', array($this, 'sanitize_option_convpfm_general'), 10, 2);
    add_filter('sanitize_option_convpfm_customer_gmail', array($this, 'sanitize_option_convpfm_email'), 10, 2);
    add_filter('sanitize_option_convpfm_prod_mapped_cats', array($this, 'sanitize_option_convpfm_general'), 10, 2);
    add_filter('sanitize_option_convpfm_prod_mapped_attrs', array($this, 'sanitize_option_convpfm_general'), 10, 2);
  }

  public function sanitize_meta_convpfm_number($value){
    $value = (int) $value;
    if ( $value < -1 ) {
      $value = abs( $value );
    }
    return $value;
  }

 	public function sanitize_option_convpfm_email($value, $option){
  	global $wpdb;
  	$value = $wpdb->strip_invalid_text_for_column( $wpdb->options, 'option_value', $value );
  	if ( is_wp_error( $value ) ) {
      $error = $value->get_error_message();
    } else {
      $value = sanitize_email( $value );
      if ( ! is_email( $value ) ) {
        $error = esc_html__( 'The email address entered did not appear to be a valid email address. Please enter a valid email address.' );
      }
    }
  	if ( ! empty( $error ) ) {
      $value = get_option( $option );
      if ( function_exists( 'add_settings_error' ) ) {
        add_settings_error( $option, "invalid_{$option}", $error );
      }
    }
  	return $value;
  }

  public function sanitize_option_convpfm_general($value, $option){
  	global $wpdb;
  	$value = $wpdb->strip_invalid_text_for_column( $wpdb->options, 'option_value', $value );
  	if ( is_wp_error( $value ) ) {
      $error = $value->get_error_message();
    }
  	if ( ! empty( $error ) ) {
      $value = get_option( $option );
      if ( function_exists( 'add_settings_error' ) ) {
        add_settings_error( $option, "invalid_{$option}", $error );
      }
    }
  	return $value;
  }
  public function tvc_upgrade_function( ) {
    $convpfm_additional_data = $this->get_convpfm_additional_data();
    $ee_p_version = isset($convpfm_additional_data['ee_p_version'])?$convpfm_additional_data['ee_p_version']:"";
    if($ee_p_version == ""){
      $ee_p_version ="1.0.0";
    }
    if( version_compare($ee_p_version , PLUGIN_CONVPFM_VERSION, ">=")){
      return;
    }else{
      $this->update_app_status();
    }
    if(!isset($convpfm_additional_data['ee_p_version']) || empty($convpfm_additional_data)){
      $convpfm_additional_data = array();
    }

    $convpfm_additional_data['ee_p_version'] = PLUGIN_CONVPFM_VERSION;
    $this->set_convpfm_additional_data($convpfm_additional_data);
      
  }
  
  
  /*
   * Check auto update time
   */
  // public function is_need_to_update_api_to_db(){
  // 	if($this->get_subscriptionId() != ""){
  // 		$google_detail = $this->get_convpfm_options_data();
  // 		if(isset($google_detail['sync_time']) && $google_detail['sync_time']){
  // 			$current = sanitize_text_field(current_time( 'timestamp' ));
  // 			$diffrent_hours = floor(( $current - $google_detail['sync_time'])/(60*60));
  // 			if($diffrent_hours > 11){
  // 				return true;
  // 			}
  // 		}else if(empty($google_detail)){
  // 			return true;
  // 		}
  // 	}
  // 	return false;
  // }
  /*
   * if user has subscription id  and if DB data is empty then call update data
   */
  // public function is_convpfm_options_data_empty(){
  // 	if($this->get_subscriptionId() != ""){
  // 		if(empty($this->get_convpfm_options_data())){
  // 			$this->set_update_api_to_db();
  // 		}
  // 	}
  // }
  
	/*
   * Update user only subscription details in DB
   */
	public function update_subscription_details_api_to_db($googleDetail = null){
    $google_detail = $this->customApiObj->getGoogleAnalyticDetail();
    if(property_exists($google_detail,"error") && $google_detail->error == false){
      if(property_exists($google_detail,"data") && $google_detail->data != ""){
        $google_detail->data->access_token = base64_encode(sanitize_text_field($google_detail->data->access_token));
        $google_detail->data->refresh_token = base64_encode(sanitize_text_field($google_detail->data->refresh_token));
        $googleDetail = $google_detail->data;
      }
    }
		if(!empty($googleDetail)){
			$get_convpfm_api_data = $this->get_convpfm_api_data();
			$get_convpfm_api_data["setting"] = $googleDetail;
			$this->set_convpfm_api_data($get_convpfm_api_data);
		}
	}
  
 	/*
   * get API data from DB
   */
	public function get_convpfm_api_data(){
			$this->convpfm_api_data = unserialize(get_option('convpfm_api_data'));
			return $this->convpfm_api_data;
	} 

	
	/*
   * set API data in DB
   */
	public function set_convpfm_api_data($convpfm_api_data){
		update_option("convpfm_api_data", serialize( $convpfm_api_data ));
	}
	/*
   * set additional data in DB
   */
	public function set_convpfm_additional_data($convpfm_additional_data){
		update_option("convpfm_additional_data", serialize($convpfm_additional_data));
	}
	/*
   * get additional data from DB
   */
	public function get_convpfm_additional_data(){		
		$this->convpfm_additional_data = unserialize(get_option('convpfm_additional_data'));
		return $this->convpfm_additional_data;		
	}
	
	public function save_convpfm_options_settings($settings){
    update_option("convpfm_options", serialize( $settings) );
	}
	/*
   * get plugin setting data from DB
   */
	public function get_convpfm_options_settings(){
		if(!empty($this->e_options_settings)){
			return $this->e_options_settings;
		}else{
			$this->e_options_settings = unserialize(get_option('convpfm_options'));
			return $this->e_options_settings;
		}
	}
	/*
   * get subscriptionId
   */
	public function get_subscriptionId(){							
			$convpfm_options_settings = $this->get_convpfm_options_settings();			
			return $this->subscriptionId =(isset($convpfm_options_settings['subscription_id']))?$convpfm_options_settings['subscription_id']:"";
	}
	/*
   * get merchantId
   */
	public function get_merchantId(){
		if(!empty($this->merchantId)){
			return $this->merchantId;
		}else{
			$tvc_merchant = "";
			$google_detail = $this->get_convpfm_api_data();
			return $this->merchantId = (isset($google_detail['setting']->google_merchant_center_id))?$google_detail['setting']->google_merchant_center_id:"";
		}
	}
	/*
   * get main_merchantId
   */
	public function get_main_merchantId(){
		if(!empty($this->main_merchantId)){
			return $this->main_merchantId;
		}else{
			$main_merchantId = "";
			$google_detail = $this->get_convpfm_api_data();
			return $this->main_merchantId = (isset($google_detail['setting']->merchant_id))?$google_detail['setting']->merchant_id:"";
		}		
	}
	/*
   * get admin time zone
   */
	public function get_time_zone(){
		if(!empty($this->time_zone)){
			return $this->time_zone;
		}else{
			$timezone = get_option('timezone_string');
			if($timezone == ""){
	      $timezone = "America/New_York"; 
	    }
			$this->time_zone = $timezone;
			return $this->time_zone;
		}
	}

	public function get_connect_actual_link(){
		if(!empty($this->connect_actual_link)){
			return $this->connect_actual_link;
		}else{
			$this->connect_actual_link = get_site_url();
			return $this->connect_actual_link;
		}
	}
	
  /**
   * Wordpress store information
   */
	public function get_store_data(){
		if(!empty($this->store_data)){
			return $this->store_data;
		}else{
			return $this->store_data = array(
				"subscription_id"=> $this->get_subscriptionId(),
				"user_domain" => $this->get_connect_actual_link(),
				"currency_code" => $this->get_woo_currency(),
				"timezone_string" => $this->get_time_zone(),
				"user_country" => $this->get_woo_country(),
				"app_id" => CONVPFM_APP_ID,
				"time"=> date("d-M-Y h:i:s A")
			);
		}
	}
	public function get_connect_url(){
		if(!empty($this->connect_url)){
			return $this->connect_url;
		}else{
			$this->connect_url = "https://".CONV_AUTH_CONNECT_URL."/config3/ga_rdr_gmc.php?return_url=".CONV_AUTH_CONNECT_URL."/config3/ads-analytics-form.php?domain=" . $this->get_connect_actual_link() . "&amp;country=" . $this->get_woo_country(). "&amp;user_currency=".$this->get_woo_currency()."&amp;subscription_id=" . $this->get_subscriptionId() . "&amp;confirm_url=" . admin_url() . "&amp;timezone=".$this->get_time_zone() . "&amp;app_id=" . CONVPFM_APP_ID;			
      return $this->connect_url;
		}
	}
	public function get_custom_connect_url($confirm_url = ""){
			if($confirm_url == ""){
				$confirm_url = admin_url();
			}
			$this->connect_url = "https://".CONV_AUTH_CONNECT_URL."/config3/ga_rdr_gmc.php?return_url=".CONV_AUTH_CONNECT_URL."/config3/ads-analytics-form.php?domain=" . $this->get_connect_actual_link() . "&amp;country=" . $this->get_woo_country(). "&amp;user_currency=".$this->get_woo_currency()."&amp;subscription_id=" . $this->get_subscriptionId() . "&amp;confirm_url=" . $confirm_url . "&amp;timezone=".$this->get_time_zone() . "&amp;app_id=" . CONVPFM_APP_ID;
			return $this->connect_url;
	}

  public function get_custom_connect_url_subpage($confirm_url = "", $subpage = ""){ 	     
			if($confirm_url == ""){
				$confirm_url = admin_url();
			}     
			$this->connect_url = "https://".CONV_AUTH_CONNECT_URL."/config3/ga_rdr_gmc.php?return_url=".CONV_AUTH_CONNECT_URL."/config3/ads-analytics-form.php?domain=" . $this->get_connect_actual_link() . "&amp;country=" . $this->get_woo_country(). "&amp;user_currency=".$this->get_woo_currency()."&amp;subscription_id=" . $this->get_subscriptionId() . "&amp;confirm_url=" . $confirm_url . "&amp;subpage=" . $subpage . "&amp;timezone=".$this->get_time_zone() . "&amp;app_id=" . CONVPFM_APP_ID;			
      return $this->connect_url;
	}  

	public function get_onboarding_page_url(){
		if(!empty($this->onboarding_page_url)){
			return $this->onboarding_page_url;
		}else{
			$this->onboarding_page_url = admin_url("admin.php?page=conversiospfm");
			return $this->onboarding_page_url;
		}
	}

	public function get_woo_currency(){
		if(!empty($this->woo_currency)){
			return $this->woo_currency;
		}else{			
	    $this->woo_currency = get_option('woocommerce_currency');
	    return $this->woo_currency;
	  }
	}

	public function get_woo_country(){
		if(!empty($this->woo_country)){
			return $this->woo_country;
		}else{
			$store_raw_country = get_option('woocommerce_default_country');
			$country = explode(":", $store_raw_country);
	    $this->woo_country = (isset($country[0]))?$country[0]:"";
	    return $this->woo_country;
	  }
	}
	
	public function get_api_customer_id(){
		$google_detail = $this->get_convpfm_api_data();
		if(isset($google_detail['setting'])){
      $googleDetail = (array) $google_detail['setting'];
			return ((isset($googleDetail['customer_id']))?$googleDetail['customer_id']:"");
		}
	}

	public function get_currentCustomerId(){
		if(!empty($this->currentCustomerId)){
			return $this->currentCustomerId;
		}else{
			$convpfm_options_settings = $this->get_convpfm_options_settings();
			return $this->currentCustomerId = (isset($convpfm_options_settings['google_ads_id']))?$convpfm_options_settings['google_ads_id']:"";
		}
	}
	public function get_user_currency_symbol(){
		if(!empty($this->user_currency_symbol)){
			return $this->user_currency_symbol;
		}else{
			$currency_symbol="";
			$currency_symbol_rs = $this->customApiObj->getCampaignCurrencySymbol(['customer_id' => $this->get_currentCustomerId()]);
      if(isset($currency_symbol_rs->data) && isset($currency_symbol_rs->data['status']) && $currency_symbol_rs->data['status'] == 200){	         
      	$currency_symbol = get_woocommerce_currency_symbol($currency_symbol_rs->data['data']->currency);	            
      }else{
        $currency_symbol = get_woocommerce_currency_symbol("USD");
      }
			$this->user_currency_symbol = $currency_symbol;
			return $this->user_currency_symbol;
		}
	}	
	
	public function get_gmcAttributes() {
    $path = CONVPFM_ENHANCAD_PLUGIN_DIR . 'includes/setup/json/gmc_attrbutes.json';
    $str = file_get_contents($path);
    $attributes = $str ? json_decode($str, true) : [];
    return $attributes;
  }
  public function get_gmc_countries_list() {
    $path = CONVPFM_ENHANCAD_PLUGIN_DIR . 'includes/setup/json/countries.json';
    $str = file_get_contents($path);
    $attributes = $str ? json_decode($str, true) : [];
    return $attributes;
  }
  public function get_gmc_language_list() {
    $path = CONVPFM_ENHANCAD_PLUGIN_DIR . 'includes/setup/json/iso_lang.json';
    $str = file_get_contents($path);
    $attributes = $str ? json_decode($str, true) : [];
    return $attributes;
  }
  /* start display form input*/
  public function tvc_language_select($name, $class_id = "", string $label = "Please Select", string $sel_val = "en", bool $require = false)
  {
    if ($sel_val == "en") {
      $sel_val = get_locale();
      if (strlen($sel_val) > 0) {
        $sel_val = explode('_', $sel_val)[0];
      }
    }
  	if($name){
  		$countries_list = $this->get_gmc_language_list();
	  	?>
	  	<select style="width: 100%" class="attributeClass fw-light text-secondary fs-6 form-control form-select-sm select2 <?php echo esc_attr($class_id); ?> <?php echo ($require == true)?"field-required":""; ?>" name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($class_id); ?>" >
	  		<option value=""><?php echo esc_attr($label); ?></option>
	  		<?php foreach ($countries_list as $Key => $val) {?>
	  			<option value="<?php echo esc_attr($val["code"]);?>" <?php echo($val["code"] == $sel_val)?"selected":""; ?>><?php echo esc_attr($val["name"])." (".esc_attr($val["native_name"]).")";?></option>
	  		<?php
	  		}?>
	  	</select>
	  	<?php
  	}
  }
  public function tvc_countries_select($name, $class_id="", string $label="Please Select", bool $require = false){
  	if($name){
  		$countries_list = $this->get_gmc_countries_list();
  		$sel_val = $this->get_woo_country();
	  	?>
	  	<select style="width: 100%" class="attributeClass fw-light text-secondary fs-6 form-control form-select-sm select2 <?php echo esc_attr($class_id); ?> <?php echo ($require == true)?"field-required":""; ?>" name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($class_id); ?>" >
	  		<option value=""><?php echo esc_attr($label); ?></option>
	  		<?php foreach ($countries_list as $Key => $val) {?>          
	  			<option value="<?php echo esc_attr($val["code"]);?>" <?php echo($val["code"] == $sel_val)?"selected":""; ?>><?php echo esc_attr($val["name"]);?></option>
	  		<?php
	  		}?>
	  	</select>
	  	<?php
  	}
  }
  public function tvc_select($name, $class_id="", string $label="Please Select", string $sel_val = null, bool $require = false, $option_list = array()){
  	if(!empty($option_list) && $name){
	  	?>
	  	<select style="width: 100%" class="attributeClass fw-light text-secondary fs-6 form-control form-select-sm select2 <?php echo esc_attr($class_id); ?> <?php echo ($require == true)?"field-required":""; ?>" name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($class_id); ?>" >
	  		<option value=""><?php echo esc_attr($label); ?></option>
	  		<?php foreach ($option_list as $Key => $val) {?>
	  			<option value="<?php echo esc_attr($val["field"]);?>" <?php echo($val["field"] == $sel_val)?"selected":""; ?>><?php echo esc_attr($val["field"]);?></option>
	  		<?php
	  		}?>
	  	</select>
	  	<?php
  	}
  }

  public function add_additional_option_in_tvc_select($tvc_select_option, $field){    
    if ($field == "brand") {
      $is_plugin = 'yith-woocommerce-brands-add-on/init.php';
      $is_plugin_premium = 'yith-woocommerce-brands-add-on-premium/init.php';
      $woocommerce_brand_is_active = 'woocommerce-brands/woocommerce-brands.php';
      $perfect_woocommerce_brand_is_active = 'perfect-woocommerce-brands/perfect-woocommerce-brands.php';
      $wpc_brands = 'wpc-brands/wpc-brands.php';
      if (is_plugin_active($is_plugin) || is_plugin_active($is_plugin_premium)) {
        $tvc_select_option[]["field"] = "yith_product_brand";
      } else if (in_array($woocommerce_brand_is_active, apply_filters('active_plugins', get_option('active_plugins')))) {
        $tvc_select_option[]["field"] = "woocommerce_product_brand";
      } else if (in_array($perfect_woocommerce_brand_is_active, apply_filters('active_plugins', get_option('active_plugins')))) {
        $tvc_select_option[]["field"] = "perfect_woocommerce_product_brand";
      } else if (in_array($wpc_brands, apply_filters('active_plugins', get_option('active_plugins')))) {
        $tvc_select_option[]["field"] = "wpc-brand";
      }
    }
    return $tvc_select_option;
  }

  public function add_additional_option_val_in_map_product_attribute($key, $product_id){
    if($key != "" && $product_id != ""){
      if($key == "brand"){
        $is_plugin='yith-woocommerce-brands-add-on/init.php';
        $is_plugin_premium='yith-woocommerce-brands-add-on-premium/init.php';
        $woocommerce_brand_is_active = 'woocommerce-brands/woocommerce-brands.php';
        $perfect_woocommerce_brand_is_active = 'perfect-woocommerce-brands/perfect-woocommerce-brands.php';
        $wpc_brands = 'wpc-brands/wpc-brands.php';
        if(is_plugin_active($is_plugin) || is_plugin_active($is_plugin_premium)){
          return $yith_product_brand = $this->get_custom_taxonomy_name($product_id,"yith_product_brand");  
        }else if ( in_array( $woocommerce_brand_is_active, apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
          return $product_brand = $this->get_custom_taxonomy_name($product_id,"product_brand");
        }else if ( in_array( $perfect_woocommerce_brand_is_active, apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
          return $product_brand = $this->get_custom_taxonomy_name($product_id,"pwb-brand");
        } else if (in_array($wpc_brands, apply_filters('active_plugins', get_option('active_plugins')))) {
          return $product_brand = $this->get_custom_taxonomy_name($product_id, "wpc-brand");
        }
      } 
    }   
  }

  public function get_custom_taxonomy_name($product_id, $taxonomy ="product_cat", $separator = ", "){
    $terms_ids = wp_get_post_terms( $product_id, $taxonomy, array('fields' => 'ids') );   
    // Loop though terms ids (product categories)    
    foreach( $terms_ids as $term_id ) {        
        // Loop through product category ancestors
        foreach( get_ancestors( $term_id, $taxonomy) as $ancestor_id ){
          return get_term( $ancestor_id, $taxonomy)->name;
          exit;
        }
        return get_term( $term_id, $taxonomy )->name;
        exit;
        break;
    }    
  }

  public function tvc_text($name, string $type="text", string $class_id="", string $label=null, $sel_val = null, bool $require = false){
  	?>
  	<input style="width:100%;" type="<?php echo esc_attr($type); ?>" <?php echo esc_attr($type) == 'number' ? 'min="0"' : '' ?> name="<?php echo esc_attr($name); ?>" class="form-control tvc-text <?php echo esc_attr($class_id); ?>" id="<?php echo esc_attr($class_id); ?>" placeholder="<?php echo esc_attr($label); ?>" value="<?php echo esc_attr($sel_val); ?>">
  	<?php
  }
 
  /* end from input*/

	public function is_current_tab_in($tabs){
		if(isset($_GET['tab']) && is_array($tabs) && in_array(sanitize_text_field($_GET['tab']), $tabs)){
			return true;
		}else if(isset($_GET['tab']) && sanitize_text_field($_GET['tab']) ==$tabs){
			return true;
		}
		return false;
	}

	// public function get_tvc_product_cat_list(){
	// 	$args = array(
	//     'hide_empty'   => 1,
	//     'taxonomy' => 'product_cat',
	//     'orderby'  => 'term_id'
  //   );
  //   $shop_categories_list = get_categories( $args );
  //   $tvc_cat_id_list = [];
  //   foreach ($shop_categories_list as $key => $value) {
	// 	  $tvc_cat_id_list[]=$value->term_id;
	// 	}
	// 	return json_encode($tvc_cat_id_list);		
	// }
	// public function get_tvc_product_cat_list_with_name(){
	// 	$args = array(
	//     'hide_empty' => 1,
	//     'taxonomy' => 'product_cat',
	//     'orderby'  => 'term_id'
  //   );
  //   $shop_categories_list = get_categories( $args );
  //   $tvc_cat_id_list = [];
  //   foreach ($shop_categories_list as $key => $value) {
	// 	  $tvc_cat_id_list[$value->term_id]=$value->name;
	// 	}
	// 	return $tvc_cat_id_list;		
	// }

	
	public function call_domain_claim(){
		$googleDetail = [];
    $google_detail = $this->get_convpfm_api_data();
    if(isset($google_detail['setting']) && $google_detail['setting']){      
      $googleDetail = $google_detail['setting'];
      if($googleDetail->is_site_verified == '0'){
      	return array('error'=>true, 'msg'=>esc_html__("First need to verified your site. Click on site verification refresh icon to verified your site.","product-feed-manager-for-woocommerce"));
      }else if(property_exists($googleDetail,"is_domain_claim") && $googleDetail->is_domain_claim == '0'){
      	//'website_url' => $googleDetail->site_url,
        $postData = [
		      'merchant_id' => sanitize_text_field($googleDetail->merchant_id),  
		      'website_url' => get_site_url(),
		      'subscription_id' => sanitize_text_field($googleDetail->id),
		      'account_id' => sanitize_text_field($googleDetail->google_merchant_center_id)
		    ];		    
				$claimWebsite = $this->customApiObj->claimWebsite($postData);
		    if(isset($claimWebsite->error) && !empty($claimWebsite->errors)){ 
		    	return array('error'=>true, 'msg'=>$claimWebsite->errors);
		    }else{
		      $this->update_subscription_details_api_to_db();
		      return array('error'=>false, 'msg'=>esc_html__("Domain claimed successfully.", "product-feed-manager-for-woocommerce"));
		    }
		  }else{
		  	return array('error'=>false, 'msg'=>esc_html__("Already domain claimed successfully.", "product-feed-manager-for-woocommerce"));
		  }      
    }		
	}

	
	public function call_site_verified(){
		$googleDetail = [];
    $google_detail = $this->get_convpfm_api_data();
    if(isset($google_detail['setting']) && $google_detail['setting']){      
      $googleDetail = $google_detail['setting'];
      if(property_exists($googleDetail,"is_site_verified") && $googleDetail->is_site_verified == '0'){
        $postData = [
		      'merchant_id' => sanitize_text_field($googleDetail->merchant_id),
		      'website_url' => get_site_url(),		      
		      'subscription_id' => sanitize_text_field($googleDetail->id),
		      'account_id' => sanitize_text_field($googleDetail->google_merchant_center_id)
		    ];
		   	$postData['method']="file"; 
				$siteVerificationToken = $this->customApiObj->siteVerificationToken($postData);

        if(isset($siteVerificationToken->error) && !empty($siteVerificationToken->errors)){
        	return array('error'=>true, 'msg'=> esc_attr($siteVerificationToken->errors));        	
        }else{
          $myFile = ABSPATH.$siteVerificationToken->data->token;
          if(!file_exists($myFile)){
            $fh = fopen($myFile, 'w+');
            chmod($myFile,0777);
            $stringData = "google-site-verification: ".$siteVerificationToken->data->token;
            fwrite($fh, $stringData);
            fclose($fh);
          }
          $postData['method']="file";
          $siteVerification = $this->customApiObj->siteVerification($postData);          
          if(isset($siteVerification->error) && !empty($siteVerification->errors)){
          	//methd using tag
          	$postData['method']="meta";
          	$siteVerificationToken_tag = $this->customApiObj->siteVerificationToken($postData);
          	if(isset($siteVerificationToken_tag->data->token) && $siteVerificationToken_tag->data->token){
          		$convpfm_additional_data = $this->get_convpfm_additional_data();
          		$convpfm_additional_data['add_site_varification_tag']=1;
          		$convpfm_additional_data['site_varification_tag_val']=base64_encode(sanitize_text_field($siteVerificationToken_tag->data->token));

          		$this->set_convpfm_additional_data($convpfm_additional_data);
          		sleep(1);
          		$siteVerification_tag = $this->customApiObj->siteVerification($postData);
          		if(isset($siteVerification_tag->error) && !empty($siteVerification_tag->errors)){
          			return array('error'=>true, 'msg'=>esc_html($siteVerification_tag->errors));
          		}else{
          			$this->update_subscription_details_api_to_db();
          			return array('error'=>false, 'msg'=>esc_html__("Site verification successfully.","product-feed-manager-for-woocommerce"));
          		}
          	}else{
          		return array('error'=>true, 'msg'=> esc_html($siteVerificationToken_tag->errors));
          	}       	
          	// one more try
          }else{
            $this->update_subscription_details_api_to_db();
		      	return array('error'=>false, 'msg'=>esc_html__("Site verification successfully.","product-feed-manager-for-woocommerce"));
          }
        }
		  }else{
		  	return array('error'=>false, 'msg'=>esc_html__("Already site verification successfully.","product-feed-manager-for-woocommerce"));
		  }      
    }		
	}

  public function update_app_status($status = "1"){  
    $this->customApiObj->update_app_status($status);
  }

  public function app_activity_detail($status = ""){  
    $this->customApiObj->app_activity_detail($status);
  }
	
	public function tvc_get_post_meta($post_id){
      $where ="post_id = ".$post_id;
      $rows = $this->Convpfm_Admin_DB_Helper->tvc_get_results_in_array('postmeta', $where, array('meta_key','meta_value'));
      $metas = array();
      if(!empty($rows)){
        foreach($rows as $val){
          $metas[$val['meta_key']] = $val['meta_value'];
        }
      }
      return $metas;
  }

  public function getTableColumns($table) {
  	global $wpdb;
		$table = esc_sql($table);
    return $wpdb->get_results("SELECT column_name as field FROM information_schema.columns WHERE table_name = '$table'");
  }

  public function getTableData($table = null, $columns = array()) {
    global $wpdb;
  	if($table ==""){
  		$table = $wpdb->prefix.'postmeta';
  	}
    $table = esc_sql($table);  	
    $columns = implode('`,`', $columns);
    return $wpdb->get_results("SELECT  DISTINCT `$columns` as field FROM `$table`");
  }
  /* message notification */
  public function set_convpfm_msg_nofification_list($convpfm_msg_list){
		update_option("convpfm_msg_nofifications", serialize( $convpfm_msg_list ));
	}
  public function get_convpfm_msg_nofification_list(){
  	return unserialize(get_option('convpfm_msg_nofifications'));
  }
  
  public function active_licence($licence_key, $subscription_id){
  	if($licence_key != ""){
  		$customObj = new Convpfm_CustomApi();
    	return $customObj->active_licence_Key($licence_key, $subscription_id);
  	}  	
  }

  public function get_pro_plan_site(){
  	return "https://www.conversios.io/pricing";
  }

  public function get_conversios_site_url(){
  	return "https://conversios.io/";
  }

   /*
   * get user plan id
   */
  public function get_plan_id(){
  	if(!empty($this->plan_id)){
			return $this->plan_id;
		}else{
			$plan_id = 21;
			$google_detail = $this->get_convpfm_api_data();
	  	if(isset($google_detail['setting'])){
			  $googleDetail = $google_detail['setting'];
			  if(isset($googleDetail->plan_id) && !in_array($googleDetail->plan_id, array("21"))){
			    $plan_id = $googleDetail->plan_id;
			  }
			}
			return $this->plan_id = $plan_id;
  	}
	}

	/*
   * get user plan id
   */
  public function get_user_subscription_data(){  	
			$google_detail = $this->get_convpfm_api_data();
	  	if(isset($google_detail['setting'])){
			   return $google_detail['setting'];
			}  	
	}
	/*
   * Check refresh tocken status
   */
	// public function is_refresh_token_expire(){
	// 	$access_token = $this->customApiObj->get_tvc_access_token();
	// 	$refresh_token = $this->customApiObj->get_tvc_refresh_token();
	// 	if($access_token != "" && $refresh_token != ""){
	// 		$access_token = $this->customApiObj->generateAccessToken($access_token, $refresh_token);
	// 	}		
	// 	if($access_token != ""){
	// 		return false;
	// 	}else{
	// 		return true;
	// 	}
	// }

  
  //tvc_add_data_admin_notice function for adding the admin notices
  public function convpfm_add_admin_notice($slug, $content, $status, $link_title = null, $link = null, $value = null, $title = null,$priority = "", $key = "" ){
      $convpfm_additional_data = $this->get_convpfm_additional_data();
      if(!isset($convpfm_additional_data['admin_notices'][$slug])){       
        $convpfm_additional_data['admin_notices'][$slug] = array("link_title"=>$link_title,"content"=>$content,"status"=> $status,"title"=>$title, "value"=> $value,"link"=>$link, "priority"=>$priority, "key"=>$key );
        $this->set_convpfm_additional_data($convpfm_additional_data);
    }
  }
  //convpfm_dismiss_admin_notice function for dismissing the admin notices
  public function convpfm_dismiss_admin_notice($slug,$content, $status, $title = null,  $value = null){
    $convpfm_additional_data = $this->get_convpfm_additional_data();
    if(isset($convpfm_additional_data['admin_notices'][$slug])){     
        $convpfm_additional_data['admin_notices'][$slug] = array("title"=>$title,"content"=>$content,"status"=> $status, "value"=> $value);      
        $this->set_convpfm_additional_data($convpfm_additional_data);
    }
  }
  public function convpfm_add_data_admin_notice(){ 
    $convpfm_add_data_admin_notice = $this->get_convpfm_options_settings();    
    $con_subscription_id = $this->get_subscriptionId();
    /*GTM release notice*/
    // $link_title = "Set it up in a single click..!!!!";
    // $link = "admin.php?page=conversios-google-analytics";
    // $content = "NEW FEATURE - Now automate Facebook, Snapchat, Tiktok, Pinterest, Microsoft Ads, Google Ads pixels using Conversios's faster and accurate Google Tag Manager implementation.";
    // $status = "1";
    // $this->convpfm_add_admin_notice("implementation_gatm_tracking",$content, $status, $link_title, $link,"","","8","implementation_gatm_tracking");
    //when user google signed in
    if($con_subscription_id != "" && $con_subscription_id != null ){
      $link_title = "User Manual Guide";
      $content = "You have not linked Google Analytics, Google Ads and Google Merchant Center accounts with Conversios plugin. Set up the conversios plugin now and boost your sales. Refer User Manual guide to get started,";
      $status = "0";
      $this->convpfm_dismiss_admin_notice("no_google_signin",$content, $status,$link_title);      
       //if user has not selected merchant center account.
        if(!isset($convpfm_add_data_admin_notice['google_merchant_id']) || (isset($convpfm_add_data_admin_notice['google_merchant_id']) && $convpfm_add_data_admin_notice['google_merchant_id'] == '')){
              $link_title = "Link Google Merchant account";
              $content = "You have not linked Google Merchant Account account with conversios plugin yet. Increase your sales by linking the Google Merchant Account, Refer the user manual to link the account";
              $status = "1";
              $link = "admin.php?page=conversiospfm";
              $this->convpfm_add_admin_notice("no_merchant_account", $content, $status,$link_title, $link,"","","5","no_merchant_account");
        }else{
              $link_title = "Link Google Merchant account";
              $content = "You have not linked Google Merchant Account account with conversios plugin yet. Increase your sales by linking the Google Merchant Account, Refer the user manual to link the account";
              $status = "0";
              $link = "admin.php?page=conversiospfm";
              $this->convpfm_dismiss_admin_notice("no_merchant_account", $content, $status,$link_title, $link);
        }

        //if user has linked google merchant center account and not synced any product.
        global $wpdb;
        $tablename = esc_sql($wpdb->prefix .'convpfm_product_feed');				
        $sql = $wpdb->prepare("select * from %i ORDER BY id ASC LIMIT 1", $tablename);
        $result = $wpdb->get_results($sql);
        if((isset($convpfm_add_data_admin_notice['google_merchant_id']) && $convpfm_add_data_admin_notice['google_merchant_id'] != '') 
        && is_array($result) && isset(end($result)->feed_name) && end($result)->feed_name != 'Default Feed' 
        && isset(end($result)->is_mapping_update) && end($result)->is_mapping_update == '0'){
              $link_title = "Click here to create.";
              $content = "Attention: Your GMC (Google Merchant Center) account is successfully connected, but it appears that you have not processed your product feed yet.";
              $status = "1";
              $link = "admin.php?page=convpfm-google-shopping-feed&tab=feed_list";
              $this->convpfm_add_admin_notice("no_product_sync", $content, $status,$link_title, $link,"","","9","no_product_sync");
        }else{
              $link_title = "Click here to create.";
              $content = "Attention: Your GMC (Google Merchant Center) account is successfully connected, but it appears that you have not processed your product feed yet.";
              $status = "0";
              $link = "admin.php?page=convpfm-google-shopping-feed&tab=feed_list";
              $this->convpfm_dismiss_admin_notice("no_product_sync", $content, $status,$link_title, $link);
        }       
       
        }else{
          //when user will not do google sign in 
            $link_title = " User Manual Guide";
            $content = "You have not linked Google Analytics, Google Ads and Google Merchant Center accounts with Conversios plugin. Set up the conversios plugin now and boost your sales. Refer User Manual guide to get started,";
            $status = "1";
            $link = "https://conversios.io/help-center/Installation-Manual.pdf";
            $this->convpfm_add_admin_notice("no_google_signin", $content, $status, $link_title, $link,"","","1","no_google_signin");
       }
    }
	/*
   * conver curency code to currency symbols
   */
	public function get_currency_symbols($code){
		$currency_symbols = array(
		    'USD'=>'$', // US Dollar
		    'EUR'=>'€', // Euro
		    'CRC'=>'₡', // Costa Rican Colón
		    'GBP'=>'£', // British Pound Sterling
		    'ILS'=>'₪', // Israeli New Sheqel
		    'INR'=>'₹', // Indian Rupee
		    'JPY'=>'¥', // Japanese Yen
		    'KRW'=>'₩', // South Korean Won
		    'NGN'=>'₦', // Nigerian Naira
		    'PHP'=>'₱', // Philippine Peso
		    'PLN'=>'zł', // Polish Zloty
		    'PYG'=>'₲', // Paraguayan Guarani
		    'THB'=>'฿', // Thai Baht
		    'UAH'=>'₴', // Ukrainian Hryvnia
		    'VND'=>'₫' // Vietnamese Dong
		);
		if(isset($currency_symbols[$code]) && $currency_symbols[$code] != "") {
		  return $currency_symbols[$code];
		}else{
			return $code;
		}
	}
  
  /*
  * Add Plugin logs
  */  
  public function plugin_log($message, $file = 'plugin') { 
    // Get WordPress uploads directory.
    if(is_array($message)) { 
      $message = json_encode($message); 
    }
    $log = new WC_Logger();
    $log->add( 'Conversios Product Sync Log ', $message );
    //error_log($message);
    return true;
  }
  
  function get_conv_pro_link_adv($advance_utm_medium="popup", $advance_utm_campaign="pixel_setting", $advance_linkclass="tvc-pro", $advance_linktype="anchor", $upgradetopro_text_param="Upgrade to Pro")
  {
    $conv_advance_plugin_link = esc_url_raw($this->get_pro_plan_site()."?plugin_name=pfm&utm_source=in_app&utm_medium=".$advance_utm_medium."&utm_campaign=".$advance_utm_campaign);
    $conv_advance_plugin_link_return = "";
    $upgradetopro_text = esc_html__($upgradetopro_text_param,"product-feed-manager-for-woocommerce");
    if($advance_linktype == "anchor")
    {
      $conv_advance_plugin_link_return = "<a href='".$conv_advance_plugin_link."' target='_blank' class='".$advance_linkclass."'> ".$upgradetopro_text."</a>";
    }
    if($advance_linktype == "linkonly")
    {
      $conv_advance_plugin_link_return = $conv_advance_plugin_link;
    }
    return $conv_advance_plugin_link_return;
  }

  public function get_feed_status(){
    $google_detail = $this->get_convpfm_api_data();
    if(isset($google_detail['setting']->store_id)){
      $data = array(
        "store_id" => $google_detail['setting']->store_id,
      );
      $response = $this->customApiObj->get_feed_status_by_store_id($data);
      foreach($response->data as $key => $val){
        $profile_data = array(
          'status' => esc_sql($val->status_name),
          'tiktok_status' => esc_sql($val->tiktok_status_name),
          'fb_status' => esc_sql($val->facebook_status_name),
        );
        $this->Convpfm_Admin_DB_Helper->tvc_update_row("convpfm_product_feed", $profile_data, array("id" => $val->store_feed_id));
      }
    }    
    return true;
  }

  public function convpfm_get_results($table){
    global $wpdb;
    if($table =="" ){
      return;
    }else {
      $tablename = esc_sql($wpdb->prefix .$table);				
      $sql = $wpdb->prepare("SELECT * from %i order by id desc", $tablename);
      return $wpdb->get_results($sql);										
    }
  }

  public function convpfm_get_result_limit($table, $limit){    
    global $wpdb;
    if($table =="" ){
      return;
    }else {
      $tablename = esc_sql($wpdb->prefix .$table);				
      $sql = $wpdb->prepare("SELECT * from %i ORDER BY id DESC LIMIT ".$limit, $tablename);
      return $wpdb->get_results($sql);										
    }
  }
  
  public function get_tiktok_business_id()
  {   
    $tiktok_detail = $this->get_convpfm_options_settings();
    return $tiktok_business_id = (isset($tiktok_detail['tiktok_setting']['tiktok_business_id'])) ? $tiktok_detail['tiktok_setting']['tiktok_business_id'] : "";
    
  }

  function convpfm_continue_batch($project_hash)
  {
    $batch_project = 'convpfm_batch_file_' . $project_hash;
    $val           = get_option($batch_project);

    if ((!empty($val)) && (is_array($val))) {

      $line           = new convpfm_GetProducts();
      $final_creation = $line->convpfm_GetProducts($val);
      $last_updated   = $this->convpfm_last_updated($val['project_hash']);
      // Clean up the single event project configuration.
      unset($line);
      unset($final_creation);
      unset($last_updated);
    }
  }

  function convpfm_last_updated($project_hash)
  {
    $feed_config  = get_option('convpfm_cron_files');
    $last_updated = date('d M Y H:i');

    foreach ($feed_config as $key => $val) {
      if (isset($val['project_hash']) && ($val['project_hash'] == $project_hash)) {
        $upload_dir = wp_upload_dir();
        $base       = $upload_dir['basedir'];
        $path       = $base . '/conversios-product-feed/' . $val['fileformat'];
        $file       = $path . '/' . sanitize_file_name($val['filename']) . '.' . $val['fileformat'];

        $last_updated = date('d M Y H:i');

        if (file_exists($file)) {
          $last_updated                        = date('d M Y H:i', filemtime($file));
          $feed_config[$key]['last_updated'] = date('d M Y H:i', filemtime($file));
        } else {
          $feed_config[$key]['last_updated'] = date('d M Y H:i');
        }
      }
    }

    update_option('convpfm_cron_files', $feed_config, 'no');

    return $last_updated;
  }

}