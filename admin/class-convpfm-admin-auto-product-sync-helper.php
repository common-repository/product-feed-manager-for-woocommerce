<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}
if ( ! class_exists( 'Convpfm_Admin_Auto_Product_sync_Helper' ) ) {
  Class Convpfm_Admin_Auto_Product_sync_Helper{
  	protected $Convpfm_TVC_Admin_Helper;
  	protected $Convpfm_Admin_DB_Helper;
    protected $time_space;
    private $apiDomain;
    protected $batch_size;
    protected $customApiObj;
  	public function __construct() {
  		$this->Convpfm_TVC_Admin_Helper = new Convpfm_TVC_Admin_Helper();
  		$this->Convpfm_Admin_DB_Helper = new Convpfm_Admin_DB_Helper();
      $this->apiDomain = CONVPFM_API_CALL_URL;
      $this->includes();
      add_action('admin_init', array($this,'add_table_in_db'));
      $this->customApiObj = new Convpfm_CustomApi();   
    }

    public function includes() {
      require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
      if (!class_exists('Convpfm_CustomApi')) {
        require_once(CONVPFM_ENHANCAD_PLUGIN_DIR . 'includes/setup/convpfm-customApi.php');
      }            
    }

    public function add_table_in_db(){
      global $wpdb;
      /* cteate table for save sync product settings */
      $tablename = esc_sql( $wpdb->prefix ."convpfm_product_sync_data" );  
      if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', '%'.$wpdb->esc_like( $tablename).'%' ) ) === $tablename ) {
        $result = $wpdb->get_row($wpdb->prepare("SHOW COLUMNS FROM {$wpdb->prefix}convpfm_product_sync_data WHERE FIELD = %s", "update_date"));
        if ( isset($result->Type) && $result->Type == 'date') {
          $wpdb->query($wpdb->prepare("ALTER TABLE %i Modify `update_date`  DATETIME NULL",$tablename));
        }

        $sync_result = $wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM {$wpdb->prefix}convpfm_product_sync_data LIKE %s", '%'.$wpdb->esc_like('feedId').'%' ));
        if ($sync_result == '') {
          $wpdb->query($wpdb->prepare("ALTER TABLE %i ADD `feedId` int(11) NULL  AFTER `status`", $tablename));
        }
      }else{     
        $sql_create = "CREATE TABLE `$tablename` ( `id` BIGINT(20) NOT NULL AUTO_INCREMENT , `w_product_id` BIGINT(20) NOT NULL , `w_cat_id` INT(10) NOT NULL , `g_cat_id` INT(10) NOT NULL , `g_attribute_mapping` LONGTEXT NOT NULL , `update_date` DATE NOT NULL , `status` INT(1) NOT NULL DEFAULT '1', `feedId` int(11) NULL, PRIMARY KEY (`id`) );";
        if (maybe_create_table($tablename, $sql_create)) {
        }
      }
      /* cteate table for save auto sync product call */
      $tablename = esc_sql($wpdb->prefix ."convpfm_product_sync_call");   
      if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', '%'.$wpdb->esc_like( $tablename).'%' ) ) === $tablename ) {          
      }else{
        $sql_create = "CREATE TABLE `$tablename` ( `id` BIGINT(20) NOT NULL AUTO_INCREMENT, `sync_product_ids` LONGTEXT NULL, `w_total_product` INT(10) NOT NULL , `total_sync_product` INT(10) NOT NULL ,last_sync  DATETIME NOT NULL, create_sync DATETIME NOT NULL, next_sync DATETIME NOT NULL, `last_sync_product_id` BIGINT(20) NOT NULL, `action_scheduler_id` INT(10) NOT NULL, `status` INT(1) NOT NULL COMMENT '0 failed, 1 completed', PRIMARY KEY (`id`) );";    
        if(!maybe_create_table( $tablename, $sql_create )){ }
      }
     
      /********Create product feed table in DB ******************/
      $tablename = $wpdb->prefix . "convpfm_product_feed";
      $query = $wpdb->prepare('SHOW TABLES LIKE %s', '%'.$wpdb->esc_like($tablename).'%');
      if ($wpdb->get_var($query) === $tablename) {
        $query = $wpdb->prepare("SHOW COLUMNS FROM {$wpdb->prefix}convpfm_product_feed LIKE %s", '%'.$wpdb->esc_like('is_default').'%');
        $result = $wpdb->get_var($query);
        if ($result == '') {
          $wpdb->query($wpdb->prepare("ALTER TABLE %i ADD `is_default` int(11) NOT NULL DEFAULT '0' AFTER `is_delete`", $tablename));
        }

        $query = $wpdb->prepare("SHOW COLUMNS FROM {$wpdb->prefix}convpfm_product_feed LIKE %s", '%'.$wpdb->esc_like('target_country').'%');
        $result = $wpdb->get_var($query);
        if ($result == '') {
          $wpdb->query($wpdb->prepare("ALTER TABLE %i ADD `target_country` varchar(50) DEFAULT NULL  AFTER `is_default`", $tablename));
        }

        $query = $wpdb->prepare("SHOW COLUMNS FROM {$wpdb->prefix}convpfm_product_feed LIKE %s", '%'.$wpdb->esc_like('is_super_feed').'%');
        $result = $wpdb->get_var($query);
        if ($result == '') {
          $wpdb->query($wpdb->prepare("ALTER TABLE %i ADD `is_super_feed` int(11) NOT NULL DEFAULT '0'  AFTER `target_country`", $tablename));
        } 
        $checkTiktokCat = $wpdb->prepare("SHOW COLUMNS FROM {$wpdb->prefix}convpfm_product_feed LIKE %s", '%'.$wpdb->esc_like('tiktok_catalog_id').'%');
        $resultTiktokCat = $wpdb->get_var($checkTiktokCat);
        if ($resultTiktokCat == '') {
          $wpdb->query($wpdb->prepare("ALTER TABLE %i ADD `tiktok_catalog_id` varchar(100) DEFAULT NULL  AFTER `target_country`", $tablename));
        }

        $querytiktok = $wpdb->prepare("SHOW COLUMNS FROM {$wpdb->prefix}convpfm_product_feed LIKE %s", '%'.$wpdb->esc_like('tiktok_status').'%');
        $resulttiktok = $wpdb->get_var($querytiktok);
        if ($resulttiktok == '') {
          $wpdb->query($wpdb->prepare("ALTER TABLE %i ADD `tiktok_status` varchar(200) NULL  AFTER `tiktok_catalog_id`", $tablename));
        }

        $queryfb = $wpdb->prepare("SHOW COLUMNS FROM {$wpdb->prefix}convpfm_product_feed LIKE %s", '%'.$wpdb->esc_like('fb_status').'%');
        $resultfb = $wpdb->get_var($queryfb);
        if ($resultfb == '') {
          $wpdb->query($wpdb->prepare("ALTER TABLE %i ADD `fb_status` varchar(200) NULL  AFTER `tiktok_status`", $tablename));
        }
      } else {
        $sql_create = "CREATE TABLE `$tablename` (  `id` int(11) NOT NULL AUTO_INCREMENT,
                                                    `feed_name` varchar(200) NOT NULL,
                                                    `channel_ids` varchar(200) NOT NULL COMMENT '1 GMC, 2 FB',
                                                    `auto_sync_interval` varchar(200) NOT NULL,
                                                    `auto_schedule` int(11) NOT NULL COMMENT '0 Inactive, 1 Active',
                                                    `categories` LONGTEXT DEFAULT NULL,
                                                    `attributes` LONGTEXT DEFAULT NULL,
                                                    `filters` LONGTEXT DEFAULT NULL,
                                                    `include_product` LONGTEXT DEFAULT NULL,
                                                    `exclude_product` LONGTEXT DEFAULT NULL,
                                                    `created_date` datetime NOT NULL,
                                                    `updated_date` datetime DEFAULT NULL,
                                                    `last_sync_date` datetime DEFAULT NULL,
                                                    `next_schedule_date` datetime NULL,
                                                    `total_product` int(11) Null,
                                                    `status` varchar(200) NOT NULL,
                                                    `is_mapping_update` int(11) Null,
                                                    `is_process_start` int(11) Null,
                                                    `is_auto_sync_start` int(11) Null,
                                                    `product_sync_batch_size` varchar(50) DEFAULT NULL,
                                                    `product_id_prefix` varchar(100) DEFAULT NULL,
                                                    `product_sync_alert` LONGTEXT DEFAULT NULL,
                                                    `is_delete` int(11) Null,
                                                    `is_default` int(11) NOT NULL DEFAULT '0',
                                                    `target_country` varchar(50) DEFAULT NULL,
                                                    `is_super_feed` int(11) NOT NULL DEFAULT '0',
                                                    `tiktok_catalog_id` varchar(100) DEFAULT NULL,
                                                    `tiktok_status` varchar(200) DEFAULT NULL,
                                                    `fb_status` varchar(200) DEFAULT NULL,
                                                    PRIMARY KEY (`id`) 
                                                  );";
        if (maybe_create_table($tablename, $sql_create)) {
        }
      } 
      // Add TikTok Catalog table
      $tablename = $wpdb->prefix . "convpfm_tiktok_catalog";
      $query = $wpdb->prepare('SHOW TABLES LIKE %s', '%'.$wpdb->esc_like($tablename).'%');
      if ($wpdb->get_var($query) === $tablename) {
      } else {
        $sql_create = "CREATE TABLE `$tablename` (  `id` int(11) NOT NULL AUTO_INCREMENT,
                                                      `country` varchar(200) NOT NULL,
                                                      `catalog_id` varchar(200) NOT NULL,
                                                      `catalog_name` varchar(200) NOT NULL,                                                      
                                                      `created_date` datetime NOT NULL,                                                     
                                                      PRIMARY KEY (`id`) );";
        if (maybe_create_table($tablename, $sql_create)) {
        }
      }

      /********Create Pmax Camapign table in DB ******************/
      $tablename = $wpdb->prefix . "convpfm_pmax_campaign";
      $query = $wpdb->prepare('SHOW TABLES LIKE %s', '%'.$wpdb->esc_like($tablename).'%');
      if ($wpdb->get_var($query) === $tablename) {
      } else {
        $sql_create = "CREATE TABLE `$tablename` (  `id` int(11) NOT NULL AUTO_INCREMENT,
                                                      `campaign_name` varchar(200) NOT NULL,
                                                      `daily_budget` varchar(200) NOT NULL,
                                                      `target_country_campaign` varchar(200) NOT NULL, 
                                                      `target_roas` varchar(200) NULL, 
                                                      `start_date` date NOT NULL,
                                                      `end_date` date NOT NULL,
                                                      `status` varchar(50) NOT NULL,
                                                      `feed_id` varchar(100) NOT NULL,
                                                      `request_id` varchar(100) NULL,                                                   
                                                      `created_date` datetime NULL,
                                                      `updated_date` datetime NULL,                                                                                                          
                                                      PRIMARY KEY (`id`) );";
        if (maybe_create_table($tablename, $sql_create)) {
        }
      }
      $this->get_old_user_data();
      // die('hgfjdf');
    }

    public function get_old_user_data() {
      
      if ( ! function_exists( 'is_plugin_active' ) ) {
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
      }
      $plugin = 'enhanced-e-commerce-for-woocommerce-store/enhanced-ecommerce-google-analytics.php';
      if ( is_plugin_active( $plugin ) || is_plugin_active( 'enhanced-e-commerce-pro-for-woocommerce-store/enhanced-ecommerce-pro-google-analytics.php' )) {
        //die('Active');
      } else {
        global $wpdb;
        $table_name = $wpdb->prefix . "convpfm_product_feed";
        $query = $wpdb->prepare('SELECT COUNT(*) as `count` FROM %i', $table_name);
        $count = $wpdb->get_var($query);
        // if count is 0 in convpfm_product_feed then insert data from ee_product_feed
        if( $count == 0 ) {
          $table_name_ee = $wpdb->prefix . "ee_product_feed";
          $query = $wpdb->prepare('SHOW TABLES LIKE %s', '%'.$wpdb->esc_like($table_name_ee).'%');
          if ($wpdb->get_var($query) === $table_name_ee) {
            $query_ee = $wpdb->prepare('SELECT COUNT(*) as `count` FROM %i', $table_name_ee);
            $count_ee = $wpdb->get_var($query_ee);
            if($count_ee > 0) {
              $query_get = $wpdb->prepare('SELECT * FROM %i', $table_name_ee);
              $results = $wpdb->get_results($query_get);            
              as_unschedule_all_actions('init_feed_wise_product_sync_process_scheduler_convpfm');
              foreach($results as $eachFeed) {
                $profile_data = array(
                  'feed_name' => esc_sql($eachFeed->feed_name),
                  'channel_ids' => esc_sql($eachFeed->channel_ids),
                  'auto_sync_interval' => esc_sql($eachFeed->auto_sync_interval),
                  'auto_schedule' => esc_sql($eachFeed->auto_schedule),
                  'categories' => esc_sql(stripslashes($eachFeed->categories)),
                  'attributes' => esc_sql(stripslashes($eachFeed->attributes)),
                  'filters' => esc_sql(stripslashes($eachFeed->filters)),
                  'include_product' => esc_sql($eachFeed->include_product),
                  'exclude_product' => esc_sql($eachFeed->exclude_product),
                  'created_date' => esc_sql($eachFeed->created_date),
                  'updated_date' => esc_sql($eachFeed->updated_date) ? esc_sql($eachFeed->updated_date) : NULL,
                  'last_sync_date' => esc_sql($eachFeed->last_sync_date) ? esc_sql($eachFeed->last_sync_date) : NULL,
                  'next_schedule_date' => esc_sql($eachFeed->next_schedule_date) ? esc_sql($eachFeed->next_schedule_date) : NULL,
                  'total_product' => esc_sql($eachFeed->total_product),
                  'status' => esc_sql($eachFeed->status),
                  'is_mapping_update' => esc_sql($eachFeed->is_mapping_update),
                  'is_process_start' => esc_sql($eachFeed->is_process_start),
                  'is_auto_sync_start' => esc_sql($eachFeed->is_auto_sync_start),
                  'product_sync_batch_size' => esc_sql($eachFeed->product_sync_batch_size),
                  'product_id_prefix' => esc_sql($eachFeed->product_id_prefix),
                  'product_sync_alert' => esc_sql($eachFeed->product_sync_alert),
                  'is_delete' => esc_sql($eachFeed->is_delete),
                  'is_default' => esc_sql($eachFeed->is_default),
                  'target_country' => esc_sql($eachFeed->target_country),
                  'is_super_feed' => esc_sql($eachFeed->is_super_feed),
                  'tiktok_catalog_id' => esc_sql($eachFeed->tiktok_catalog_id),
                  'tiktok_status' => esc_sql($eachFeed->tiktok_status),         
                );
              
                $this->Convpfm_Admin_DB_Helper->tvc_add_row("convpfm_product_feed", $profile_data, array("%s", "%s", "%s", "%d", "%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s", "%d", "%s", "%d", "%d", "%d", "%s", "%s", "%s", "%d", "%d", "%s", "%d", "%s", "%s", "%s"));
                as_unschedule_all_actions('init_feed_wise_product_sync_process_scheduler_ee', array("feedId" => sanitize_text_field($eachFeed->id)));
                as_unschedule_all_actions('auto_feed_wise_product_sync_process_scheduler_ee', array("feedId" => sanitize_text_field($eachFeed->id)));
                
                if($eachFeed->auto_schedule == 1 && $eachFeed->is_delete != 1 && $eachFeed->is_mapping_update == '1') {
                  if (is_null($eachFeed->next_schedule_date) || empty($eachFeed->next_schedule_date)) {
                    continue;
                  } 
                  $current_time = new DateTime();
                  $given_date = new DateTime($eachFeed->next_schedule_date);
                  if($given_date < $current_time) {                
                  } else {
                    as_schedule_single_action($eachFeed->next_schedule_date, 'init_feed_wise_product_sync_process_scheduler_convpfm', array("feedId" => $eachFeed->id), "product_sync");              
                  }
                }
              }
              //truncate ee_product_feed table
              $query = $wpdb->prepare("TRUNCATE TABLE %i", $table_name_ee);
              $wpdb->query($query);          
            }
          }
        }

        $table_name_pmax = $wpdb->prefix . "convpfm_pmax_campaign";
        $query_pmax = $wpdb->prepare('SELECT COUNT(*) as `count` FROM %i', $table_name_pmax);
        $count_pmax = $wpdb->get_var($query_pmax);
        // if count is 0 in convpfm_pmax_campaign then insert data from ee_pmax_campaign
        if( $count_pmax == 0 ) {
          $table_name_pmax_ee = $wpdb->prefix . "ee_pmax_campaign";
          $query = $wpdb->prepare('SHOW TABLES LIKE %s', '%'.$wpdb->esc_like($table_name_pmax_ee).'%');
          if ($wpdb->get_var($query) === $table_name_pmax_ee) {
            $query_ee_pmax = $wpdb->prepare('SELECT COUNT(*) as `count` FROM %i', $table_name_pmax_ee);
            $count_ee_pmax = $wpdb->get_var($query_ee_pmax);          
            if($count_ee_pmax > 0){
              $query_get_pmax = $wpdb->prepare('SELECT * FROM %i', $table_name_pmax_ee);
              $results_pmax = $wpdb->get_results($query_get_pmax);
              foreach($results_pmax as $pmax) {
                $profile_data_pmax = array(
                  'campaign_name' => esc_sql($pmax->campaign_name),
                  'daily_budget' => esc_sql($pmax->daily_budget),
                  'target_country_campaign' => esc_sql($pmax->target_country_campaign),
                  'target_roas' => esc_sql($pmax->target_roas),
                  'start_date' => esc_sql($pmax->start_date),
                  'end_date' => esc_sql($pmax->end_date),
                  'status' => esc_sql($pmax->status),
                  'feed_id' => esc_sql($pmax->feed_id),
                  'request_id' => esc_sql($pmax->request_id),
                  'created_date' => esc_sql($pmax->created_date),
                  'updated_date' => esc_sql($pmax->updated_date) ? esc_sql($pmax->updated_date) : NULL,                        
                );
                $this->Convpfm_Admin_DB_Helper->tvc_add_row("convpfm_pmax_campaign", $profile_data_pmax, array("%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s"));
              }
              $query = $wpdb->prepare("TRUNCATE TABLE %i", $table_name_pmax_ee);
              $wpdb->query($query);
            }      
          }    
        }

        $table_name_tiktok = $wpdb->prefix . "convpfm_tiktok_catalog";
        $query_tiktok = $wpdb->prepare('SELECT COUNT(*) as `count` FROM %i', $table_name_tiktok);
        $count_tiktok = $wpdb->get_var($query_tiktok);
        if( $count_tiktok == 0 ) {
          $table_name_tiktok_ee = $wpdb->prefix . "ee_tiktok_catalog";
          $query = $wpdb->prepare('SHOW TABLES LIKE %s', '%'.$wpdb->esc_like($table_name_tiktok_ee).'%');
          if ($wpdb->get_var($query) === $table_name_tiktok_ee) {
            $query_ee_tiktok = $wpdb->prepare('SELECT COUNT(*) as `count` FROM %i', $table_name_tiktok_ee);
            $count_ee_tiktok = $wpdb->get_var($query_ee_tiktok);
            if($count_ee_tiktok > 0){
              $query_get_tiktok = $wpdb->prepare('SELECT * FROM %i', $table_name_tiktok_ee);
              $results_tiktok = $wpdb->get_results($query_get_tiktok);
              foreach($results_tiktok as $tiktok) {
                $profile_data_tiktok = array(
                  'country' => esc_sql($tiktok->country),
                  'catalog_id' => esc_sql($tiktok->catalog_id),
                  'catalog_name' => esc_sql($tiktok->catalog_name),
                  'created_date' => esc_sql($tiktok->created_date)                       
                );
                $this->Convpfm_Admin_DB_Helper->tvc_add_row("convpfm_tiktok_catalog", $profile_data_pmax, array("%s", "%s", "%s", "%s"));
              }
              $query = $wpdb->prepare("TRUNCATE TABLE %i", $table_name_tiktok_ee);
              $wpdb->query($query);
            }
          }
        }

        // Migrate ee_option and other setting
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

        $convpfm_additional_data = get_option('convpfm_additional_data');
        if(empty($convpfm_additional_data)) {
          $ee_additional_data = get_option('ee_additional_data');
          update_option("convpfm_additional_data", $ee_additional_data);
          update_option("ee_additional_data", '');
        }

        $convpfm_auto_update_id = get_option('convpfm_auto_update_id');
        if(empty($convpfm_auto_update_id)) {
          $ee_auto_update_id = get_option('ee_auto_update_id');
          update_option("convpfm_auto_update_id", $ee_auto_update_id);
          update_option("ee_auto_update_id", '');
        }

        $convpfm_customer_gmail = get_option('convpfm_customer_gmail');
        if(empty($convpfm_customer_gmail)) {
          $ee_customer_gmail = get_option('ee_customer_gmail');
          update_option("convpfm_customer_gmail", $ee_customer_gmail);
          update_option("ee_customer_gmail", '');
        }

        $convpfm_prod_response = get_option('convpfm_prod_response');
        if(empty($convpfm_prod_response)) {
          $ee_prod_response = get_option('ee_prod_response');
          update_option("convpfm_prod_response", $ee_prod_response);
          update_option("ee_prod_response", '');
        }

        $convpfm_prod_mapped_cats = get_option('convpfm_prod_mapped_cats');
        if(empty($convpfm_prod_mapped_cats)) {
          $ee_prod_mapped_cats = get_option('ee_prod_mapped_cats');
          update_option("convpfm_prod_mapped_cats", $ee_prod_mapped_cats);
          update_option("ee_prod_mapped_cats", '');
        }

        $convpfm_prod_mapped_attrs = get_option('convpfm_prod_mapped_attrs');
        if(empty($convpfm_prod_mapped_attrs)) {
          $ee_prod_mapped_attrs = get_option('ee_prod_mapped_attrs');
          update_option("convpfm_prod_mapped_attrs", $ee_prod_mapped_attrs);
          update_option("ee_prod_mapped_attrs", '');
        }        
      }      
    }

    /*
     * update batch wise product sync data in DB table "convpfm_product_sync_data"
     */
    public function update_last_sync_in_db_batch_wise($products, $feedId){
      try {
        $convpfm_prod_mapped_attrs = unserialize(get_option('convpfm_prod_mapped_attrs')); 
        $Convpfm_Admin_DB_Helper = new Convpfm_Admin_DB_Helper();
        $where ='`id` = '.esc_sql($feedId);
        $filed = array('attributes');
        $result = $Convpfm_Admin_DB_Helper->tvc_get_results_in_array("convpfm_product_feed", $where, $filed);
        if( $convpfm_prod_mapped_attrs != "" ){
          global $wpdb; 
          $product_ids = implode(',', array_column($products, 'w_product_id'));  
          $where ='`feedId` in ('.$feedId.') AND `w_product_id` in ('.$product_ids.')';
          $pids = $Convpfm_Admin_DB_Helper->tvc_get_results_in_array('convpfm_product_sync_data', $where, array('w_product_id'), true); 
          foreach($products as $key => $product) {
            $t_data = array(
              'w_product_id'=>esc_sql($product->w_product_id),
              'w_cat_id'=>esc_sql($product->w_cat_id),
              'g_cat_id'=>esc_sql($product->g_cat_id),
              'g_attribute_mapping'=> isset($result[0]['attributes'])? stripslashes($result[0]['attributes']) : $convpfm_prod_mapped_attrs,
              'update_date'=>esc_sql(date('Y-m-d H:i:s', current_time('timestamp'))),
              'status'=> 1,
              'feedId'=> $feedId
            );
            if(!in_array($product->w_product_id, $pids)){
              $Convpfm_Admin_DB_Helper->tvc_add_row('convpfm_product_sync_data', $t_data, array("%d", "%d", "%d", "%s", "%s", "%d") );
            }else{
              $Convpfm_Admin_DB_Helper->tvc_update_row('convpfm_product_sync_data', $t_data, array('w_product_id'=> esc_sql($product->w_product_id), 'feedId'=> esc_sql($feedId) ));
            }
          }    
          wp_reset_postdata();
        }
      } catch (Exception $e) {
        $this->Convpfm_TVC_Admin_Helper->plugin_log($e->getMessage(), 'product_sync');
      }
    }

    public function update_product_status_pre_sync_data_ee($products, $feedId){
      try {
        $conv_prod_mapped_attrs = unserialize(get_option('convpfm_prod_mapped_attrs'));  
        if( $conv_prod_mapped_attrs != "" ){
          foreach($products as $product) {
            $t_data = array(
              'update_date'=>esc_sql(date( 'Y-m-d H:i:s', current_time( 'timestamp') )),
              'status'=>esc_sql(1)
            );
            $this->Convpfm_Admin_DB_Helper->tvc_update_row('convpfm_prouct_pre_sync_data', $t_data, array('w_product_id'=> esc_sql($product->w_product_id), 'feedId'=> esc_sql($feedId) ));
          }
          wp_reset_postdata();
        }
      } catch (Exception $e) {
        $this->Convpfm_TVC_Admin_Helper->plugin_log($e->getMessage(), 'product_sync');
      }    
    }
  }// end Class
}
new Convpfm_Admin_Auto_Product_sync_Helper();
