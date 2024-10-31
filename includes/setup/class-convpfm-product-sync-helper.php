<?php
if (!class_exists('Convpfm_ProductSyncHelper')) {
	class Convpfm_ProductSyncHelper
	{
		protected $merchantId;
		protected $accountId;
		protected $currentCustomerId;
		protected $subscriptionId;
		protected $country;
		protected $site_url;
		protected $category_wrapper_obj;
		protected $Convpfm_TVC_Admin_Helper;
		protected $Convpfm_Admin_DB_Helper;
		public function __construct()
		{
			$this->includes();
			$this->add_table_in_db();
			$this->Convpfm_TVC_Admin_Helper = new Convpfm_TVC_Admin_Helper();
			$this->Convpfm_Admin_DB_Helper = new Convpfm_Admin_DB_Helper();
			$this->category_wrapper_obj = new Convpfm_Category_Wrapper();
			$this->merchantId = $this->Convpfm_TVC_Admin_Helper->get_merchantId();
			$this->accountId = $this->Convpfm_TVC_Admin_Helper->get_main_merchantId();
			$this->currentCustomerId = 1; //$this->Convpfm_TVC_Admin_Helper->get_currentCustomerId();
			$this->subscriptionId = $this->Convpfm_TVC_Admin_Helper->get_subscriptionId();
			$this->country = $this->Convpfm_TVC_Admin_Helper->get_woo_country();
			$this->site_url = "admin.php?page=convpfm-google-shopping-feed&tab=";
			add_action('admin_init', array($this, 'add_table_in_db'));
		}
		public function includes()
		{
			if (!class_exists('Convpfm_Category_Wrapper')) {
				require_once(__DIR__ . '/convpfm-category-wrapper.php');
			}
		}
		/*
		 * careate table batch wise for product sync
		 */
		public function add_table_in_db()
		{
			global $wpdb;
			$tablename = esc_sql($wpdb->prefix . "convpfm_product_sync_profile");
			$query = $wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($tablename));
			if ($wpdb->get_var($query) === $tablename) {
				$queryDataType = $wpdb->prepare("SHOW COLUMNS FROM `$tablename` WHERE FIELD = %s", "update_date");
				$result = $wpdb->get_row($queryDataType);
				if ($result->Type == 'date') {
					$wpdb->query("ALTER TABLE $tablename Modify `update_date`  DATETIME NULL");
				}
			} else {
				$sql_create = "CREATE TABLE `$tablename` ( `id` BIGINT(20) NOT NULL AUTO_INCREMENT , `profile_title` VARCHAR(100) NULL , `g_cat_id` INT(10) NULL , `g_attribute_mapping` LONGTEXT NOT NULL , `update_date` DATETIME NULL , `status` INT(1) NOT NULL DEFAULT '1', PRIMARY KEY (`id`) );";
				if (maybe_create_table($tablename, $sql_create)) {
				}
			}

			$tablename = esc_sql($wpdb->prefix . "convpfm_prouct_pre_sync_data");
			$query = $wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($tablename));
			if ($wpdb->get_var($query) === $tablename) {
				$queryDataType = $wpdb->prepare("SHOW COLUMNS FROM `$tablename` WHERE FIELD = %s", "update_date");
				$result = $wpdb->get_row($queryDataType);
				if ($result->Type == 'date') {
					$wpdb->query("ALTER TABLE $tablename Modify `update_date`  DATETIME NULL");
				}

				$pre_sync_query = $wpdb->prepare("SHOW COLUMNS FROM " . $tablename . " LIKE %s", $wpdb->esc_like('feedId'));
				$pre_sync_result = $wpdb->get_var($pre_sync_query);
				if ($pre_sync_result == '') {
					$wpdb->query("ALTER TABLE $tablename ADD `feedId` int(11) NULL  AFTER `status`");
				}

			} else {
				$sql_create = "CREATE TABLE `$tablename` ( `id` BIGINT(20) NOT NULL AUTO_INCREMENT , `w_product_id` BIGINT(20) NOT NULL , `w_cat_id` INT(10) NOT NULL , `g_cat_id` INT(10) NOT NULL , `product_sync_profile_id` INT(10) NOT NULL ,`create_date` DATETIME NULL DEFAULT CURRENT_TIMESTAMP, `update_date` DATETIME NULL , `status` INT(1) NOT NULL DEFAULT '0', `feedId` int(11) NULL , PRIMARY KEY (`id`) );";
				if (maybe_create_table($tablename, $sql_create)) {
				}
			}
		}

		public function get_product_category($product_id)
		{
			$output = [];
			$terms_ids = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'ids'));
			// Loop though terms ids (product categories)
			foreach ($terms_ids as $term_id) {
				$term_names = [];
				// Loop through product category ancestors
				foreach (get_ancestors($term_id, 'product_cat') as $ancestor_id) {
					$term_names[] = get_term($ancestor_id, 'product_cat')->name;
					if (isset($output[$ancestor_id]) && $output[$ancestor_id] != "") {
						unset($output[$ancestor_id]);
					}
				}
				$term_names[] = get_term($term_id, 'product_cat')->name;
				// Add the formatted ancestors with the product category to main array
				$output[$term_id] = implode(' > ', $term_names);
			}
			$output = array_values($output);
			return $output;
		}
		/*
		 * careate products object for product sync
		 */
		public function tvc_get_map_product_attribute($products, $tvc_currency, $merchantId, $product_batch_size = 100)
		{
			try {
				if (!empty($products)) {
					global $wpdb;
					$tve_table_prefix = $wpdb->prefix;
					$items = [];
					$validProducts = [];
					$skipProducts = [];
					$product_ids = [];
					$deletedIds = [];
					$batchId = time();
					$sync_profile = $this->Convpfm_Admin_DB_Helper->tvc_get_results('convpfm_product_sync_profile');
					// set profile id in array key
					$sync_profile_data = array();
					if (!empty($sync_profile)) {
						foreach ($sync_profile as $key => $value) {
							$sync_profile_data[$value->id] = $value;
						}
					}
					if (empty($sync_profile_data)) {
						return array("error" => true, "message" => esc_html__("No product sync profiles find.", "product-feed-manager-for-woocommerce"));
					}
					if (empty($products)) {
						return array("error" => true, "message" => esc_html__("Products not found.", "product-feed-manager-for-woocommerce"));
					}
					$products_sync = 0;
					foreach ($products as $postkey => $postvalue) {
						$product_ids[] = $postvalue->w_product_id;
						$postmeta = [];
						$postmeta = $this->Convpfm_TVC_Admin_Helper->tvc_get_post_meta($postvalue->w_product_id);
						$prd = wc_get_product($postvalue->w_product_id);
						$postObj = (object) array_merge((array) get_post($postvalue->w_product_id), (array) $postmeta);
						$permalink = esc_url_raw(get_permalink($postvalue->w_product_id));
						$product = array(
							//'offer_id'=>sanitize_text_field($postvalue->w_product_id),
							'channel' => 'online',
							'link' => esc_url_raw(get_permalink($postvalue->w_product_id)),
							'google_product_category' => sanitize_text_field($postvalue->g_cat_id)
						);

						$temp_product = array();
						$fixed_att_select_list = array("gender", "age_group", "shipping", "tax", "content_language", "target_country", "condition");
						$formArray = "";
						if (isset($sync_profile_data[$postvalue->product_sync_profile_id]) && $sync_profile_data[$postvalue->product_sync_profile_id]->g_attribute_mapping) {
							$g_attribute_mapping = $sync_profile_data[$postvalue->product_sync_profile_id]->g_attribute_mapping;
							$formArray = json_decode($g_attribute_mapping, true);
						}

						if (empty($formArray)) {
							return array("error" => true, "message" => esc_html__("Product sync profile not found.", "product-feed-manager-for-woocommerce"));
						}
						//$formArray = json_decode($postvalue->g_attribute_mapping, true);
						foreach ($fixed_att_select_list as $fixed_key) {
							if (isset($formArray[$fixed_key]) && $formArray[$fixed_key] != "") {
								if ($fixed_key == "shipping" && $formArray[$fixed_key] != "") {
									$temp_product[$fixed_key]['price']['value'] = sanitize_text_field($formArray[$fixed_key]);
									$temp_product[$fixed_key]['price']['currency'] = sanitize_text_field($tvc_currency);
									$temp_product[$fixed_key]['country'] = sanitize_text_field($formArray['target_country']);
								} else if ($fixed_key == "tax" && $formArray[$fixed_key] != "") {
									$temp_product['taxes']['rate'] = sanitize_text_field($formArray[$fixed_key]);
									$temp_product['taxes']['country'] = sanitize_text_field($formArray['target_country']);
								} else if ($formArray[$fixed_key] != "") {
									$temp_product[$fixed_key] = sanitize_text_field($formArray[$fixed_key]);
								}
							}
							unset($formArray[$fixed_key]);
						}

						$product = array_merge($temp_product, $product);

						if ($prd->get_type() == "variable") {
							/*$variation_attributes = $prd->get_variation_attributes();*/
							//$p_variations = $prd->get_available_variations(); 
							$p_variations = $prd->get_children();
							if (!empty($p_variations)) {
								foreach ($p_variations as $v_key => $variation_id) {
									$variation = wc_get_product($variation_id);
									if (empty($variation)) {
										continue;
									}
									$variation_description = wc_format_content($variation->get_description());
									unset($product['customAttributes']);
									$postmeta_var = (object) $this->Convpfm_TVC_Admin_Helper->tvc_get_post_meta($variation_id);
									$formArray_val = $formArray['title'];
									$product['title'] = (isset($postObj->$formArray_val)) ? sanitize_text_field($postObj->$formArray_val) : get_the_title($postvalue->w_product_id);
									$tvc_temp_desc_key = $formArray['description'];
									if ($tvc_temp_desc_key == 'post_excerpt' || $tvc_temp_desc_key == 'post_content' || $tvc_temp_desc_key == '') {
										$product['description'] = ($variation_description != "") ? sanitize_text_field($variation_description) : sanitize_text_field($postObj->$tvc_temp_desc_key);
									} else {
										$product['description'] = sanitize_text_field($postObj->$tvc_temp_desc_key);
									}
									$product['item_group_id'] = $postvalue->w_product_id;
									$productTypes = $this->get_product_category($postvalue->w_product_id);
									if (!empty($productTypes)) {
										$product['productTypes'] = $productTypes;
									}
									$image_id = $variation->get_image_id();
									$variation_permalink = esc_url_raw(get_permalink($variation_id));
									$product['link'] = $variation_permalink != '' ? $variation_permalink : $permalink;
									$product['image_link'] = esc_url_raw(wp_get_attachment_image_url($image_id, 'full'));
									$variation_attributes = $variation->get_variation_attributes();

									if (!empty($variation_attributes)) {
										foreach ($variation_attributes as $va_key => $va_value) {
											$va_key = str_replace("_", " ", $va_key);
											if (strpos($va_key, 'color') !== false) {
												$product['color'] = $va_value;
											} else if (strpos($va_key, 'size') !== false) {
												$product['sizes'] = $va_value;
											} else {
												$va_key = str_replace("attribute", "", $va_key);
												$product['customAttributes'][] = array("name" => $va_key, "value" => $va_value);
											}
										}
									}

									foreach ($formArray as $key => $value) {
										if ($key == 'id') {
											$product[$key] = isset($postmeta_var->$value) ? $postmeta_var->$value : $variation_id;
											$product['offer_id'] = isset($postmeta_var->$value) ? $postmeta_var->$value : $variation_id;
										} elseif ($key == 'gtin' && (isset($postmeta_var->$value) || isset($postObj->$value))) {
											$product[$key] = isset($postmeta_var->$value) ? $postmeta_var->$value : $postObj->$value;
										} elseif ($key == 'mpn' && (isset($postmeta_var->$value) || isset($postObj->$value))) {
											$product[$key] = isset($postmeta_var->$value) ? $postmeta_var->$value : $postObj->$value;
										} elseif ($key == 'price') {
											if (isset($postmeta_var->$value) && $postmeta_var->$value > 0) {
												$product[$key]['value'] = $postmeta_var->$value;
											} else if (isset($postmeta_var->_regular_price) && $postmeta_var->_regular_price && $postmeta_var->_regular_price > 0) {
												$product[$key]['value'] = $postmeta_var->_regular_price;
											} else if (isset($postmeta_var->_price) && $postmeta_var->_price && $postmeta_var->_price > 0) {
												$product[$key]['value'] = $postmeta_var->_price;
											} else if (isset($postmeta_var->_sale_price) && $postmeta_var->_sale_price && $postmeta_var->_sale_price > 0) {
												$product[$key]['value'] = $postmeta_var->_sale_price;
											} else {
												unset($product[$key]);
											}
											if (isset($product[$key]['value']) && $product[$key]['value'] > 0) {
												$product[$key]['currency'] = sanitize_text_field($tvc_currency);
											} else {
												$skipProducts[$postmeta_var->ID] = $postmeta_var;
											}
										} else if ($key == 'sale_price') {
											if (isset($postmeta_var->$value) && $postmeta_var->$value > 0) {
												$product[$key]['value'] = $postmeta_var->$value;
											} else if (isset($postmeta_var->_sale_price) && $postmeta_var->_sale_price && $postmeta_var->_sale_price > 0) {
												$product[$key]['value'] = $postmeta_var->_sale_price;
											} else {
												unset($product[$key]);
											}
											if (isset($product[$key]['value']) && $product[$key]['value'] > 0) {
												$product[$key]['currency'] = sanitize_text_field($tvc_currency);
											}
										} else if ($key == 'availability') {
											$tvc_find = array("instock", "outofstock", "onbackorder");
											$tvc_replace = array("in stock", "out of stock", "preorder");
											if (isset($postmeta_var->$value) && $postmeta_var->$value != "") {
												$stock_status = $postmeta_var->$value;
												$stock_status = str_replace($tvc_find, $tvc_replace, $stock_status);
												$product[$key] = sanitize_text_field($stock_status);
											} else {
												$stock_status = $postmeta_var->_stock_status;
												$stock_status = str_replace($tvc_find, $tvc_replace, $stock_status);
												$product[$key] = sanitize_text_field($stock_status);
											}
										} else if (in_array($key, array("brand"))) { //list of cutom option added (Pro user only)                    
											$product_brand = "";
											$is_custom_attr_brand = false;
											$woo_attr_list = json_decode(json_encode($this->Convpfm_TVC_Admin_Helper->getTableData($tve_table_prefix . 'woocommerce_attribute_taxonomies', ['attribute_name'])), true);
											if (!empty($woo_attr_list)) {
												foreach ($woo_attr_list as $key_attr => $value_attr) {
													if (isset($value_attr['field']) && $value_attr['field'] == $value) {
														$is_custom_attr_brand = true;
														$product_brand = $this->Convpfm_TVC_Admin_Helper->get_custom_taxonomy_name($postvalue->w_product_id, "pa_" . $value);
													}
												}
											}
											if ($is_custom_attr_brand == false && $product_brand == "") {
												$product_brand = $this->Convpfm_TVC_Admin_Helper->add_additional_option_val_in_map_product_attribute($key, $postvalue->w_product_id);
											}
											if ($product_brand != "") {
												$product[$key] = sanitize_text_field($product_brand);
											}
										} else if (isset($postmeta_var->$value) && $postmeta_var->$value != "") {
											$product[$key] = sanitize_text_field($postmeta_var->$value);
										}
									}
									$item = [
										'merchant_id' => sanitize_text_field($merchantId),
										'batch_id' => sanitize_text_field(++$batchId),
										'method' => 'insert',
										'product' => $product
									];
									$items[] = $item;
									$validProducts[] = $postvalue;
								}
							} else {
								//Delete the variant product which does not have children
								$deletedIds[] = $postvalue->w_product_id;
							}

						} else {							//simpleproduct: 
							$image_id = $prd->get_image_id();
							$product['image_link'] = esc_url_raw(wp_get_attachment_image_url($image_id, 'full'));
							$productTypes = $this->get_product_category($postvalue->w_product_id);
							if (!empty($productTypes)) {
								$product['productTypes'] = $productTypes;
							}
							
							//$product['productTypes'] = "Apparel & Accessories";   
							foreach ($formArray as $key => $value) {
								if ($key == 'id') {
									$product[$key] = isset($postObj->$value) ? $postObj->$value : $postvalue->w_product_id;
									$product['offer_id'] = isset($postObj->$value) ? $postObj->$value : $postvalue->w_product_id;
								} elseif ($key == 'price') {
									if (isset($postObj->$value) && $postObj->$value > 0) {
										$product[$key]['value'] = $postObj->$value;
									} else if (isset($postObj->_regular_price) && $postObj->_regular_price && $postObj->_regular_price > 0) {
										$product[$key]['value'] = $postObj->_regular_price;
									} else if (isset($postObj->_price) && $postObj->_price && $postObj->_price > 0) {
										$product[$key]['value'] = $postObj->_price;
									} else if (isset($postObj->_sale_price) && $postObj->_sale_price && $postObj->_sale_price > 0) {
										$product[$key]['value'] = $postObj->_sale_price;
									}
									if (isset($product[$key]['value']) && $product[$key]['value'] > 0) {
										$product[$key]['currency'] = sanitize_text_field($tvc_currency);
									} else {
										$skipProducts[$postObj->ID] = $postObj;
									}
								} else if ($key == 'sale_price') {
									if (isset($postObj->$value) && $postObj->$value > 0) {
										$product[$key]['value'] = $postObj->$value;
									} else if (isset($postObj->_sale_price) && $postObj->_sale_price && $postObj->_sale_price > 0) {
										$product[$key]['value'] = $postObj->_sale_price;
									}
									if (isset($product[$key]['value']) && $product[$key]['value'] > 0) {
										$product[$key]['currency'] = sanitize_text_field($tvc_currency);
									}
								} else if ($key == 'availability') {
									$tvc_find = array("instock", "outofstock", "onbackorder");
									$tvc_replace = array("in stock", "out of stock", "preorder");
									if (isset($postObj->$value) && $postObj->$value != "") {
										$stock_status = $postObj->$value;
										$stock_status = str_replace($tvc_find, $tvc_replace, $stock_status);
										$product[$key] = sanitize_text_field($stock_status);
									} else {
										$stock_status = $postObj->_stock_status;
										$stock_status = str_replace($tvc_find, $tvc_replace, $stock_status);
										$product[$key] = sanitize_text_field($stock_status);
									}
								} else if (in_array($key, array("brand"))) {
									//list of cutom option added
									$product_brand = "";
									$is_custom_attr_brand = false;
									$woo_attr_list = json_decode(json_encode($this->Convpfm_TVC_Admin_Helper->getTableData($tve_table_prefix . 'woocommerce_attribute_taxonomies', ['attribute_name'])), true);
									if (!empty($woo_attr_list)) {
										foreach ($woo_attr_list as $key_attr => $value_attr) {
											if (isset($value_attr['field']) && $value_attr['field'] == $value) {
												$is_custom_attr_brand = true;
												$product_brand = $this->Convpfm_TVC_Admin_Helper->get_custom_taxonomy_name($postvalue->w_product_id, "pa_" . $value);
											}
										}
									}
									if ($is_custom_attr_brand == false && $product_brand == "") {
										$product_brand = $this->Convpfm_TVC_Admin_Helper->add_additional_option_val_in_map_product_attribute($key, $postvalue->w_product_id);
									}
									if ($product_brand != "") {
										$product[$key] = sanitize_text_field($product_brand);
									}
								} else if (isset($postObj->$value) && $postObj->$value != "") {
									$product[$key] = $postObj->$value;
								}

							}
							$item = [
								'merchant_id' => sanitize_text_field($merchantId),
								'batch_id' => sanitize_text_field(++$batchId),
								'method' => 'insert',
								'product' => $product
							];
							$items[] = $item;
							$validProducts[] = $postvalue;
						}

						$products_sync++;
						if (count($items) >= $product_batch_size) {
							return array('error' => false, 'items' => $items, 'valid_products' => $validProducts, 'deleted_products' => $deletedIds, 'skip_products' => $skipProducts, 'product_ids' => $product_ids, 'last_sync_product_id' => $postvalue->id, 'products_sync' => $products_sync);
						}
					}
					return array('error' => false, 'items' => $items, 'valid_products' => $validProducts, 'deleted_products' => $deletedIds, 'skip_products' => $skipProducts, 'product_ids' => $product_ids, 'last_sync_product_id' => $postvalue->id, 'products_sync' => $products_sync);
				}
			} catch (Exception $e) {
				$this->Convpfm_TVC_Admin_Helper->plugin_log($e->getMessage(), 'product_sync');
			}
		}
		
		
		public function wooCommerceAttributes()
		{
			global $wpdb;
			$tve_table_prefix = $wpdb->prefix;
			$column1 = json_decode(json_encode($this->Convpfm_TVC_Admin_Helper->getTableColumns($tve_table_prefix . 'posts')), true);
			$column2 = json_decode(json_encode($this->Convpfm_TVC_Admin_Helper->getTableData($tve_table_prefix . 'postmeta', ['meta_key'])), true);
			$column3 = json_decode(json_encode($this->Convpfm_TVC_Admin_Helper->getTableData($tve_table_prefix . 'woocommerce_attribute_taxonomies', ['attribute_name'])), true);

			return array_merge($column1, $column2, $column3);
		}

		public function call_batch_wise_auto_sync_product_feed_convpfm($feedId)
		{
			$conv_additional_data = $this->Convpfm_TVC_Admin_Helper->get_convpfm_additional_data();
			$this->Convpfm_TVC_Admin_Helper->plugin_log("Convpfm - Preparing to get batch wise product", 'product_sync');
			$google_detail = $this->Convpfm_TVC_Admin_Helper->get_convpfm_api_data();
			$convpfm_options = $this->Convpfm_TVC_Admin_Helper->get_convpfm_options_settings();
			if (isset($google_detail['setting'])) {
				if ($google_detail['setting']) {
					$googleDetail = $google_detail['setting'];
				}
			}
			try {
				global $wpdb;
				$startTime = new DateTime();
				if (!class_exists('Convpfm_CustomApi')) {
					require_once CONVPFM_ENHANCAD_PLUGIN_DIR . 'includes/setup/convpfm-customApi.php';
				}
				$CustomApi = new Convpfm_CustomApi();
				$tablename = esc_sql($wpdb->prefix . "convpfm_prouct_pre_sync_data");
				$product_count = $wpdb->get_var("SELECT COUNT(*) as a FROM $tablename where `feedId` = $feedId AND `status` = 0");

				if ($product_count > 0) {
					$Convpfm_Admin_DB_Helper = new Convpfm_Admin_DB_Helper();
					$where = '`id` = ' . esc_sql($feedId);
					$filed = array(
						'attributes',
						'channel_ids',
						'product_id_prefix',
						'tiktok_catalog_id',
						'target_country'
					);
					$result = $Convpfm_Admin_DB_Helper->tvc_get_results_in_array("convpfm_product_feed", $where, $filed);
					// $this->Convpfm_TVC_Admin_Helper->plugin_log(json_encode(stripslashes($result)), 'product_sync');
					$product_batch_size = (isset($conv_additional_data['product_sync_batch_size']) && $conv_additional_data['product_sync_batch_size']) ? $conv_additional_data['product_sync_batch_size'] : 100;
					$tvc_currency = sanitize_text_field($this->Convpfm_TVC_Admin_Helper->get_woo_currency());
					$merchantId = sanitize_text_field($this->merchantId);
					$accountId = sanitize_text_field($this->accountId);
					$subscriptionId = sanitize_text_field($this->subscriptionId);
					$product_batch_size = esc_sql(intval($product_batch_size));

					$products = $wpdb->get_results($wpdb->prepare("select * from  `$tablename` where `feedId` = %d AND `status` = 0 LIMIT %d", [$feedId, $product_batch_size]), OBJECT);
					$feed_attribute = json_decode(stripslashes($result[0]['attributes']));
					if(isset($feed_attribute->target_country) === false) {
						$feed_attribute->target_country = $result[0]['target_country'];
					}
					
					if (!empty($products)) {						
						$p_map_attribute = $this->convpfm_get_feed_wise_map_product_attribute($products, $tvc_currency, $merchantId, $product_batch_size, $feed_attribute, $result[0]['product_id_prefix']);
						//Delete the variant product which does not have children
						if (!empty($p_map_attribute) && isset($p_map_attribute['deleted_products']) && !empty($p_map_attribute['deleted_products'])) {
							$dids = esc_sql(implode(', ', $p_map_attribute['deleted_products']));
							$wpdb->query("DELETE FROM $tablename where `w_product_id` in ($dids)");
						}
						
						$Convpfm_Admin_Auto_Product_sync_Helper = new Convpfm_Admin_Auto_Product_sync_Helper();
						$Convpfm_Admin_Auto_Product_sync_Helper->update_last_sync_in_db_batch_wise($p_map_attribute['valid_products'], $feedId); //Add data in sync product database
						if (!empty($p_map_attribute) && isset($p_map_attribute['items']) && !empty($p_map_attribute['items'])) {
							// call product sync API
							$data = [
								'merchant_id' => sanitize_text_field($accountId),
								'account_id' => sanitize_text_field($merchantId),
								'subscription_id' => sanitize_text_field($subscriptionId),
								'store_feed_id' => sanitize_text_field($feedId),
								'is_on_gmc' => strpos($result[0]['channel_ids'], '1') !== false ? true : false,
								'is_on_tiktok' => strpos($result[0]['channel_ids'], '3') !== false ? true : false,
								'tiktok_catalog_id' => $result[0]['tiktok_catalog_id'],
								'tiktok_business_id' => sanitize_text_field($this->Convpfm_TVC_Admin_Helper->get_tiktok_business_id()),
								'is_on_facebook' => strpos($result[0]['channel_ids'], '2') !== false ? true : false,
								'business_id' =>  strpos($result[0]['channel_ids'], '2') !== false ? $convpfm_options['facebook_setting']['fb_business_id'] : '',
								'catalog_id' =>  strpos($result[0]['channel_ids'], '2') !== false ? $convpfm_options['facebook_setting']['fb_catalog_id'] : '',
								'entries' => $p_map_attribute['items']
							];
							$count = 0 ;
							if(is_array($p_map_attribute['items'])) {
								$count = count($p_map_attribute['items']);
							}						

							/**************************** API Call to GMC ****************************************************************************/
							// Update status in pre sync product database
							$Convpfm_Admin_Auto_Product_sync_Helper->update_product_status_pre_sync_data_ee($p_map_attribute['valid_products'], $feedId);
							$this->Convpfm_TVC_Admin_Helper->plugin_log(" Convpfm - Before product sync API Call for ".$count." products", 'product_sync');	
							$response = $CustomApi->feed_wise_products_sync($data);
							$endTime = new DateTime();
							$diff = $endTime->diff($startTime);
							$this->Convpfm_TVC_Admin_Helper->plugin_log("Convpfm - Products sync API duration time " . $diff->i . " minutes" . $diff->s . " seconds", 'product_sync');
							$responseData['time_duration'] = $diff;
							update_option("convpfm_prod_response", serialize($responseData));	

							if ($response->error == false) {								
								$products_sync = $p_map_attribute['products_sync'];

								$conv_additional_data['product_sync_alert'] = NULL;
								$this->Convpfm_TVC_Admin_Helper->set_convpfm_additional_data($conv_additional_data);
								$feed_data = array(
									"product_sync_alert" => NULL,
								);
								$Convpfm_Admin_DB_Helper->tvc_update_row("convpfm_product_feed", $feed_data, array("id" => $feedId));
								return array('error' => false, 'message' => esc_attr("Sync successfully"), "products" => $products, "p_map_attribute" => $p_map_attribute, 'products_sync' => $products_sync, 'skip_products' => $p_map_attribute['skip_products']);
							} else {
								if (isset($response->message) && $response->message != "") {
									$this->Convpfm_TVC_Admin_Helper->plugin_log($response->message, 'product_sync');
									$conv_additional_data['product_sync_alert'] = $response->message;
									$this->Convpfm_TVC_Admin_Helper->set_convpfm_additional_data($conv_additional_data);
									$feed_data = array(
										"product_sync_alert" => $response->message,
									);
									$Convpfm_Admin_DB_Helper->tvc_update_row("convpfm_product_feed", $feed_data, array("id" => $feedId));
								}
								return array('error' => true, 'message' => isset($response->message) ? esc_attr($response->message) : "", "products" => $products, "p_map_attribute" => $p_map_attribute);
							}
						} else if (!empty($p_map_attribute['message'])) {
							return array('error' => true, 'message' => esc_attr($p_map_attribute['message']));
						}
					} else {
						return array('error' => false, 'message' => esc_attr("Sync successfully"));
					}
				} else {
					// add scheduled cron job					
					as_unschedule_all_actions('auto_feed_wise_product_sync_process_scheduler_convpfm', array("feedId" => $feedId));
				}
			} catch (Exception $e) {
				$feed_data = array(
					"product_sync_alert" => $e->getMessage(),
				);
				$Convpfm_Admin_DB_Helper->tvc_update_row("convpfm_product_feed", $feed_data, array("id" => $feedId));
				$conv_additional_data['product_sync_alert'] = $e->getMessage();
				$this->Convpfm_TVC_Admin_Helper->set_convpfm_additional_data($conv_additional_data);
				$this->Convpfm_TVC_Admin_Helper->plugin_log($e->getMessage(), 'product_sync');
			}

		}
		
		public function convpfm_get_feed_wise_map_product_attribute($products, $tvc_currency, $merchantId, $product_batch_size = 100, $feed_attribute = '', $prefix = '')
		{
			try {				
				if (!empty($products)) {					
					global $wpdb;
					$tve_table_prefix = $wpdb->prefix;
					$plan_id = sanitize_text_field($this->Convpfm_TVC_Admin_Helper->get_plan_id());
					$items = [];
					$validProducts = [];
					$skipProducts = [];
					$product_ids = [];
					$deletedIds = [];
					$batchId = time();
					// $sync_profile = $this->Convpfm_Admin_DB_Helper->tvc_get_results('convpfm_product_sync_profile');
					// set profile id in array key
					// $sync_profile_data = array();
					// if (!empty($sync_profile)) {
					// 	foreach ($sync_profile as $key => $value) {
					// 		$sync_profile_data[$value->id] = $value;
					// 	}
					// }
					
					// if (empty($sync_profile_data)) {
					// 	return array("error" => true, "message" => esc_html__("No product sync profiles find.", "product-feed-manager-for-woocommerce"));
					// }
					// if (empty($products)) {
					// 	return array("error" => true, "message" => esc_html__("Products not found.", "product-feed-manager-for-woocommerce"));
					// }
					$products_sync = 0;
					$last_sync_id = 0;		
								
					foreach ($products as $postkey => $postvalue) {
						$last_sync_id = $postvalue->w_product_id;
						$product_ids[] = $postvalue->w_product_id;
						$postmeta = [];
						$postmeta = $this->Convpfm_TVC_Admin_Helper->tvc_get_post_meta($postvalue->w_product_id);
						$prd = wc_get_product($postvalue->w_product_id);
						$postObj = (object) array_merge((array) get_post($postvalue->w_product_id), (array) $postmeta);
						$permalink = esc_url_raw(get_permalink($postvalue->w_product_id));
						$product = array(
							'channel' => 'online',
							'link' => esc_url_raw(get_permalink($postvalue->w_product_id)),
							'google_product_category' => sanitize_text_field($postvalue->g_cat_id) != '0' ? sanitize_text_field($postvalue->g_cat_id) : ''
						);

						$temp_product = array();
						$fixed_att_select_list = array("gender", "age_group", "shipping", "tax", "content_language", "target_country", "condition", "adult", "is_bundle", "identifier_exists");
						$formArray = (array) $feed_attribute;

						if (empty($formArray)) {
							return array("error" => true, "message" => esc_html__("Feed wise Product attribute not found.", "product-feed-manager-for-woocommerce"));
						}
						foreach ($fixed_att_select_list as $fixed_key) {
							if (isset($formArray[$fixed_key]) && $formArray[$fixed_key] != "") {
								if ($fixed_key == "shipping" && $formArray[$fixed_key] != "") {
									$temp_product[$fixed_key]['price']['value'] = sanitize_text_field($formArray[$fixed_key]);
									$temp_product[$fixed_key]['price']['currency'] = sanitize_text_field($tvc_currency);
									$temp_product[$fixed_key]['country'] = sanitize_text_field($formArray['target_country']);
								} else if ($fixed_key == "tax" && $formArray[$fixed_key] != "") {
									$temp_product['taxes']['rate'] = sanitize_text_field($formArray[$fixed_key]);
									$temp_product['taxes']['country'] = sanitize_text_field($formArray['target_country']);
								} else if ($formArray[$fixed_key] != "") {
									$temp_product[$fixed_key] = sanitize_text_field($formArray[$fixed_key]);
								}
							}
							unset($formArray[$fixed_key]);
						}

						$product = array_merge($temp_product, $product);
						$conv_additional_data = $this->Convpfm_TVC_Admin_Helper->get_convpfm_additional_data();
						$product_id_prefix = $prefix;

						if ($prd->get_type() == "variable") {
							$p_variations = $prd->get_children();
							if (!empty($p_variations)) {
								foreach ($p_variations as $v_key => $variation_id) {
									$variation = wc_get_product($variation_id);
									if (empty($variation)) {
										continue;
									}									
									if($variation->get_stock_status() == 'outofstock'){
										//Delete outstock product 
										$deletedIds[] = $postvalue->w_product_id;
										continue;
									  }
									$variation_description = wc_format_content($variation->get_description());
									unset($product['customAttributes']);
									$postmeta_var = (object) $this->Convpfm_TVC_Admin_Helper->tvc_get_post_meta($variation_id);
									$formArray_val = $formArray['title'];
									$product['title'] = (isset($postObj->$formArray_val)) ? sanitize_text_field($postObj->$formArray_val) : get_the_title($postvalue->w_product_id);
									$tvc_temp_desc_key = $formArray['description'];
									if ($tvc_temp_desc_key == 'post_excerpt' || $tvc_temp_desc_key == 'post_content') {
										$product['description'] = ($variation_description != "") ? sanitize_text_field($variation_description) : sanitize_text_field($postObj->$tvc_temp_desc_key);
									} else {
										$product['description'] = sanitize_text_field($postObj->$tvc_temp_desc_key);
									}

									$product['item_group_id'] = $postvalue->w_product_id;
									$productTypes = $this->get_product_category($postvalue->w_product_id);
									if (!empty($productTypes)) {
										$product['productTypes'] = $productTypes;
									}
									$image_id = $variation->get_image_id();
									$product['image_link'] = esc_url_raw(wp_get_attachment_image_url($image_id, 'full'));
									$variation_permalink = esc_url_raw(get_permalink($variation_id));
									$product['link'] = $variation_permalink != '' ? $variation_permalink : $permalink;
									$variation_attributes = $variation->get_variation_attributes();
									if (!empty($variation_attributes)) {
										foreach ($variation_attributes as $va_key => $va_value) {
											$va_key = str_replace("_", " ", $va_key);
											if (strpos($va_key, 'color') !== false) {
												$product['color'] = $va_value;
											} else if (strpos($va_key, 'size') !== false) {
												$product['sizes'] = $va_value;
											} else {
												$va_key = str_replace("attribute", "", $va_key);
												$product['customAttributes'][] = array("name" => $va_key, "value" => $va_value);
											}
										}
									}

									foreach ($formArray as $key => $value) {
										if ($key == 'id') {
											if (!empty($product_id_prefix && $product_id_prefix != '')) {
												$product[$key] = sanitize_text_field(isset($postmeta_var->$value) ? $product_id_prefix . $postmeta_var->$value : $product_id_prefix . $variation_id);
												$product['offer_id'] = sanitize_text_field(isset($postmeta_var->$value) ? $product_id_prefix . $postmeta_var->$value : $product_id_prefix . $variation_id);
											} else {
												$product[$key] = sanitize_text_field(isset($postmeta_var->$value) ? $postmeta_var->$value : $variation_id);
												$product['offer_id'] = sanitize_text_field(isset($postmeta_var->$value) ? $postmeta_var->$value : $variation_id);
											}
										} elseif ($key == 'gtin' && (isset($postmeta_var->$value) || isset($postObj->$value))) {
											$product[$key] = sanitize_text_field(isset($postmeta_var->$value) ? $postmeta_var->$value : $postObj->$value);
										} elseif ($key == 'mpn' && (isset($postmeta_var->$value) || isset($postObj->$value))) {
											$product[$key] = sanitize_text_field(isset($postmeta_var->$value) ? $postmeta_var->$value : $postObj->$value);
										} elseif ($key == 'price') {
											if (isset($postmeta_var->$value) && $postmeta_var->$value > 0) {
												$product[$key]['value'] = sanitize_text_field($postmeta_var->$value);
											} else if (isset($postmeta_var->_regular_price) && $postmeta_var->_regular_price && $postmeta_var->_regular_price > 0) {
												$product[$key]['value'] = sanitize_text_field($postmeta_var->_regular_price);
											} else if (isset($postmeta_var->_price) && $postmeta_var->_price && $postmeta_var->_price > 0) {
												$product[$key]['value'] = sanitize_text_field($postmeta_var->_price);
											} else if (isset($postmeta_var->_sale_price) && $postmeta_var->_sale_price && $postmeta_var->_sale_price > 0) {
												$product[$key]['value'] = sanitize_text_field($postmeta_var->_sale_price);
											} else {
												unset($product[$key]);
											}
											if (isset($product[$key]['value']) && $product[$key]['value'] > 0) {
												$product[$key]['currency'] = sanitize_text_field($tvc_currency);
											} 
											// else {
											// 	$skipProducts[$product->ID] = $postmeta_var;
											// }
										} else if ($key == 'sale_price') {
											if (isset($postmeta_var->$value) && $postmeta_var->$value > 0) {
												$product[$key]['value'] = $postmeta_var->$value;
											} else if (isset($postmeta_var->_sale_price) && $postmeta_var->_sale_price && $postmeta_var->_sale_price > 0) {
												$product[$key]['value'] = $postmeta_var->_sale_price;
											} else {
												unset($product[$key]);
											}
											if (isset($product[$key]['value']) && $product[$key]['value'] > 0) {
												$product[$key]['currency'] = sanitize_text_field($tvc_currency);
											}
										} else if ($key == 'availability') {
											$tvc_find = array("instock", "outofstock", "onbackorder");
											$tvc_replace = array("in stock", "out of stock", "preorder");
											if (isset($postmeta_var->$value) && $postmeta_var->$value != "") {
												$stock_status = $postmeta_var->$value;
												$stock_status = str_replace($tvc_find, $tvc_replace, $stock_status);
												$product[$key] = sanitize_text_field($stock_status);
											} else {
												$stock_status = $postmeta_var->_stock_status;
												$stock_status = str_replace($tvc_find, $tvc_replace, $stock_status);
												$product[$key] = sanitize_text_field($stock_status);
											}
										} else if (in_array($key, array("brand"))) { //list of cutom option added (Pro user only)                    
											$product_brand = "";
											$is_custom_attr_brand = false;
											$woo_attr_list = json_decode(json_encode($this->Convpfm_TVC_Admin_Helper->getTableData($tve_table_prefix . 'woocommerce_attribute_taxonomies', ['attribute_name'])), true);
											if (!empty($woo_attr_list)) {
												foreach ($woo_attr_list as $key_attr => $value_attr) {
													if (isset($value_attr['field']) && $value_attr['field'] == $value) {
														$is_custom_attr_brand = true;
														$product_brand = $this->Convpfm_TVC_Admin_Helper->get_custom_taxonomy_name($postvalue->w_product_id, "pa_" . $value);
													}
												}
											}
											if ($is_custom_attr_brand == false && $product_brand == "") {
												$product_brand = $this->Convpfm_TVC_Admin_Helper->add_additional_option_val_in_map_product_attribute($key, $postvalue->w_product_id);
											}
											if ($product_brand != "") {
												$product[$key] = sanitize_text_field($product_brand);
											}
											if($value == 'product_tag') {
												$product_tags = wp_get_post_terms($postvalue->w_product_id, 'product_tag');
												$tag_names = array();
												foreach ($product_tags as $tag) {
													$tag_names[] = $tag->name;
												}

												// Concatenate the tag names into a single string
												$tag_string = implode(', ', $tag_names);
												$product[$key] = sanitize_text_field($tag_string);
											}
											if($value == 'product_cat') {
												$product[$key] = sanitize_text_field($productTypes[0]);
											} else {
												$product[$key] = sanitize_text_field($postmeta_var->$value);
											}
										} else if($key == 'product_weight'){
											if(isset($postmeta_var->$value) && $postmeta_var->$value != ""){
												$product[$key]['value'] = sanitize_text_field($postmeta_var->$value);
												$product[$key]['unit'] = get_option('woocommerce_weight_unit');
											}
											
										} else if($key == 'shipping_weight'){
											if(isset($postmeta_var->$value) && $postmeta_var->$value != ""){
											  $product[$key]['value'] = sanitize_text_field($postmeta_var->$value);
											  $product[$key]['unit'] = get_option('woocommerce_weight_unit');
											}
											
										} else if (isset($postmeta_var->$value) && $postmeta_var->$value != "") {
											$product[$key] = sanitize_text_field($postmeta_var->$value);
										}
									}
									$category_mapping = array(
										'google_product_category' => $postvalue->g_cat_id != 0 ? sanitize_text_field($postvalue->g_cat_id) : '',
										'facebook_product_category' => $postvalue->g_cat_id != 0 ? sanitize_text_field($postvalue->g_cat_id) : '',
									);
									$item = [
										'merchant_id' => sanitize_text_field($merchantId),
										'batch_id' => sanitize_text_field(++$batchId),
										'method' => sanitize_text_field('insert'),
										'product' => $product,
										'category_mapping' => $category_mapping,
									];
									$items[] = $item;

								}
								$validProducts[] = $postvalue;
							} else {
								//Delete the variant product which does not have children
								$deletedIds[] = $postvalue->w_product_id;
							}

						} else {
							//simpleproduct: 
							if($prd->get_stock_status() == 'outofstock'){
								//Delete outstock product 
								$deletedIds[] = $postvalue->w_product_id;
								continue;
							}
							$image_id = $prd->get_image_id();
							$product['image_link'] = esc_url_raw(wp_get_attachment_image_url($image_id, 'full'));
							$productTypes = $this->get_product_category($postvalue->w_product_id);
							if (!empty($productTypes)) {
								$product['productTypes'] = $productTypes;
							}
							foreach ($formArray as $key => $value) {
								if ($key == 'id') {
									if (!empty($product_id_prefix)) {
										$product[$key] = sanitize_text_field(isset($postObj->$value) ? $product_id_prefix . $postObj->$value : $product_id_prefix . $postvalue->w_product_id);
										$product['offer_id'] = sanitize_text_field(isset($postObj->$value) ? $product_id_prefix . $postObj->$value : $product_id_prefix . $postvalue->w_product_id);
									} else {
										$product[$key] = sanitize_text_field(isset($postObj->$value) ? $postObj->$value : $postvalue->w_product_id);
										$product['offer_id'] = sanitize_text_field(isset($postObj->$value) ? $postObj->$value : $postvalue->w_product_id);
									}
								} elseif ($key == 'price') {
									if (isset($postObj->$value) && $postObj->$value > 0) {
										$product[$key]['value'] = sanitize_text_field($postObj->$value);
									} else if (isset($postObj->_regular_price) && $postObj->_regular_price && $postObj->_regular_price > 0) {
										$product[$key]['value'] = sanitize_text_field($postObj->_regular_price);
									} else if (isset($postObj->_price) && $postObj->_price && $postObj->_price > 0) {
										$product[$key]['value'] = sanitize_text_field($postObj->_price);
									} else if (isset($postObj->_sale_price) && $postObj->_sale_price && $postObj->_sale_price > 0) {
										$product[$key]['value'] = sanitize_text_field($postObj->_sale_price);
									}
									if (isset($product[$key]['value']) && $product[$key]['value'] > 0) {
										$product[$key]['currency'] = sanitize_text_field($tvc_currency);
									} 
									// else {
									// 	$skipProducts[$postObj->ID] = $postObj;
									// }
								} else if ($key == 'sale_price') {
									if (isset($postObj->$value) && $postObj->$value > 0) {
										$product[$key]['value'] = $postObj->$value;
									} else if (isset($postObj->_sale_price) && $postObj->_sale_price && $postObj->_sale_price > 0) {
										$product[$key]['value'] = $postObj->_sale_price;
									}
									if (isset($product[$key]['value']) && $product[$key]['value'] > 0) {
										$product[$key]['currency'] = sanitize_text_field($tvc_currency);
									}
								} else if ($key == 'availability') {
									$tvc_find = array("instock", "outofstock", "onbackorder");
									$tvc_replace = array("in stock", "out of stock", "preorder");
									if (isset($postObj->$value) && $postObj->$value != "") {
										$stock_status = $postObj->$value;
										$stock_status = str_replace($tvc_find, $tvc_replace, $stock_status);
										$product[$key] = sanitize_text_field($stock_status);
									} else {
										$stock_status = $postObj->_stock_status;
										$stock_status = str_replace($tvc_find, $tvc_replace, $stock_status);
										$product[$key] = sanitize_text_field($stock_status);
									}
								} else if (in_array($key, array("brand"))) {
									//list of cutom option added
									$product_brand = "";
									$is_custom_attr_brand = false;
									$woo_attr_list = json_decode(json_encode($this->Convpfm_TVC_Admin_Helper->getTableData($tve_table_prefix . 'woocommerce_attribute_taxonomies', ['attribute_name'])), true);
									if (!empty($woo_attr_list)) {
										foreach ($woo_attr_list as $key_attr => $value_attr) {
											if (isset($value_attr['field']) && $value_attr['field'] == $value) {
												$is_custom_attr_brand = true;
												$product_brand = $this->Convpfm_TVC_Admin_Helper->get_custom_taxonomy_name($postvalue->w_product_id, "pa_" . $value);
											}
										}
									}
									if ($is_custom_attr_brand == false && $product_brand == "") {
										$product_brand = $this->Convpfm_TVC_Admin_Helper->add_additional_option_val_in_map_product_attribute($key, $postvalue->w_product_id);
									}
									if ($product_brand != "") {
										$product[$key] = sanitize_text_field($product_brand);
									}
									if($value == 'product_tag') {
										$product_tags = wp_get_post_terms($postvalue->w_product_id, 'product_tag');
										$tag_names = array();
										foreach ($product_tags as $tag) {
											$tag_names[] = $tag->name;
										}

										// Concatenate the tag names into a single string
										$tag_string = implode(', ', $tag_names);
										$product[$key] = sanitize_text_field($tag_string);
									}
									if($value == 'product_cat') {
										$product[$key] = sanitize_text_field($productTypes[0]);
									} else {
										$product[$key] = sanitize_text_field($postObj->$value);
									}
								} else if($key == 'product_weight'){
									if(isset($postObj->$value) && $postObj->$value != ""){
										$product[$key]['value'] = sanitize_text_field($postObj->$value);
										$product[$key]['unit'] = get_option('woocommerce_weight_unit');
									}
									
								} else if($key == 'shipping_weight'){
									if(isset($postObj->$value) && $postObj->$value != ""){
									  $product[$key]['value'] = sanitize_text_field($postObj->$value);
									  $product[$key]['unit'] = get_option('woocommerce_weight_unit');
									}
									
								} else if($key == 'product_shipping_class') {
									$product[$key] = $prd->get_shipping_class();
								} else if (isset($postObj->$value) && $postObj->$value != "") {
									$product[$key] = $postObj->$value;
								}
							}
							$category_mapping = array(
								'google_product_category' => $postvalue->g_cat_id != 0 ? sanitize_text_field($postvalue->g_cat_id) : '',
								'facebook_product_category' => $postvalue->g_cat_id != 0 ? sanitize_text_field($postvalue->g_cat_id) : '',
							);
							$item = [
								'merchant_id' => sanitize_text_field($merchantId),
								'batch_id' => sanitize_text_field(++$batchId),
								'method' => sanitize_text_field('insert'),
								'product' => $product,
								'category_mapping' => $category_mapping
							];
							$items[] = $item;
							$validProducts[] = $postvalue;
						}

						$products_sync++;
						if (count($items) >= $product_batch_size) {
							return array('error' => false, 'items' => $items, 'valid_products' => $validProducts, 'deleted_products' => $deletedIds, 'skip_products' => $skipProducts, 'product_ids' => $product_ids, 'last_sync_product_id' => $postvalue->id, 'products_sync' => $products_sync);
						}
					}
					return array('error' => false, 'items' => $items, 'valid_products' => $validProducts, 'deleted_products' => $deletedIds, 'skip_products' => $skipProducts, 'product_ids' => $product_ids, 'last_sync_product_id' => $last_sync_id, 'products_sync' => $products_sync);
				}
			} catch (Exception $e) {
				$this->Convpfm_TVC_Admin_Helper->plugin_log($e->getMessage(), 'product_sync');
			}
		}
		public function call_batch_wise_auto_sync_product_feed_ee($feedId)
		{
			$conv_additional_data = $this->Convpfm_TVC_Admin_Helper->get_convpfm_additional_data();
			$this->Convpfm_TVC_Admin_Helper->plugin_log("EE call_batch_wise_auto_sync_product_feed", 'product_sync');
			$google_detail = $this->Convpfm_TVC_Admin_Helper->get_convpfm_api_data();
			$convpfm_options = $this->Convpfm_TVC_Admin_Helper->get_convpfm_options_settings();
			if (isset($google_detail['setting'])) {
				if ($google_detail['setting']) {
					$googleDetail = $google_detail['setting'];
				}
			}
			try {
				global $wpdb;
				$startTime = new DateTime();
				if (!class_exists('Convpfm_CustomApi')) {
					require_once CONVPFM_ENHANCAD_PLUGIN_DIR . 'includes/setup/convpfm-customApi.php';
				}
				$CustomApi = new Convpfm_CustomApi();
				$tablename = esc_sql($wpdb->prefix . "convpfm_prouct_pre_sync_data");
				$product_count = $wpdb->get_var("SELECT COUNT(*) as a FROM $tablename where `feedId` = $feedId AND `status` = 0");

				if ($product_count > 0) {
					$Convpfm_Admin_DB_Helper = new Convpfm_Admin_DB_Helper();
					$where = '`id` = ' . esc_sql($feedId);
					$filed = array(
						'attributes',
						'channel_ids',
						'product_id_prefix',
						'tiktok_catalog_id',
						'target_country'
					);
					$result = $Convpfm_Admin_DB_Helper->tvc_get_results_in_array("convpfm_product_feed", $where, $filed);
					$product_batch_size = (isset($conv_additional_data['product_sync_batch_size']) && $conv_additional_data['product_sync_batch_size']) ? $conv_additional_data['product_sync_batch_size'] : 100;
					$tvc_currency = sanitize_text_field($this->Convpfm_TVC_Admin_Helper->get_woo_currency());
					$merchantId = sanitize_text_field($this->merchantId);
					$accountId = sanitize_text_field($this->accountId);
					$subscriptionId = sanitize_text_field($this->subscriptionId);
					$product_batch_size = esc_sql(intval($product_batch_size));

					$products = $wpdb->get_results($wpdb->prepare("select * from  `$tablename` where `feedId` = %d AND `status` = 0 LIMIT %d", [$feedId, $product_batch_size]), OBJECT);
					$feed_attribute = json_decode(stripslashes($result[0]['attributes']));
					$feed_attribute->target_country = $result[0]['target_country'];
					if (!empty($products)) {						
						$p_map_attribute = $this->conv_get_feed_wise_map_product_attribute($products, $tvc_currency, $merchantId, $product_batch_size, $feed_attribute, $result[0]['product_id_prefix']);
						//Delete the variant product which does not have children
						if (!empty($p_map_attribute) && isset($p_map_attribute['deleted_products']) && !empty($p_map_attribute['deleted_products'])) {
							$dids = esc_sql(implode(', ', $p_map_attribute['deleted_products']));
							$wpdb->query("DELETE FROM $tablename where `w_product_id` in ($dids)");
						}
						$Convpfm_Admin_Auto_Product_sync_Helper = new Convpfm_Admin_Auto_Product_sync_Helper();
						$Convpfm_Admin_Auto_Product_sync_Helper->update_last_sync_in_db_batch_wise($p_map_attribute['valid_products'], $feedId); //Add data in sync product database
						if (!empty($p_map_attribute) && isset($p_map_attribute['items']) && !empty($p_map_attribute['items'])) {
							// call product sync API
							$data = [
								'merchant_id' => sanitize_text_field($accountId),
								'account_id' => sanitize_text_field($merchantId),
								'subscription_id' => sanitize_text_field($subscriptionId),
								'store_feed_id' => sanitize_text_field($feedId),
								'is_on_gmc' => strpos($result[0]['channel_ids'], '1') !== false ? true : false,
								'is_on_tiktok' => strpos($result[0]['channel_ids'], '3') !== false ? true : false,
								'tiktok_catalog_id' => $result[0]['tiktok_catalog_id'],
								'tiktok_business_id' => sanitize_text_field($this->Convpfm_TVC_Admin_Helper->get_tiktok_business_id()),
								'is_on_facebook' => strpos($result[0]['channel_ids'], '2') !== false ? true : false,
								'business_id' =>  strpos($result[0]['channel_ids'], '2') !== false ? $convpfm_options['facebook_setting']['fb_business_id'] : '',
								'catalog_id' =>  strpos($result[0]['channel_ids'], '2') !== false ? $convpfm_options['facebook_setting']['fb_catalog_id'] : '',
								'entries' => $p_map_attribute['items']
							];

							$this->Convpfm_TVC_Admin_Helper->plugin_log("Convpfm - Before product sync API Call for " . is_array($p_map_attribute['items']) ? count($p_map_attribute['items']) : 0 . " products", 'product_sync');

							/**************************** API Call to GMC ****************************************************************************/
							/***
							 * check API One value for count is hard written, Check with Chirag before deploying very important.
							 * 
							 * 
							 * Important
							 * 
							 */
							$response = $CustomApi->feed_wise_products_sync($data);
							$endTime = new DateTime();
							$diff = $endTime->diff($startTime);
							$this->Convpfm_TVC_Admin_Helper->plugin_log("Convpfm - Products sync API duration time " . $diff->i . " minutes" . $diff->s . " seconds", 'product_sync');
							$responseData['time_duration'] = $diff;
							update_option("convpfm_prod_response", serialize($responseData));
							
							// Update status in pre sync product database
							$Convpfm_Admin_Auto_Product_sync_Helper->update_product_status_pre_sync_data_ee($products, $feedId);

							if ($response->error == false) {								
								$products_sync = $p_map_attribute['products_sync'];

								$conv_additional_data['product_sync_alert'] = NULL;
								$this->Convpfm_TVC_Admin_Helper->set_convpfm_additional_data($conv_additional_data);
								$feed_data = array(
									"product_sync_alert" => NULL,
								);
								$Convpfm_Admin_DB_Helper->tvc_update_row("convpfm_product_feed", $feed_data, array("id" => $feedId));
								return array('error' => false, 'message' => esc_attr("Sync successfully"), "products" => $products, "p_map_attribute" => $p_map_attribute, 'products_sync' => $products_sync, 'skip_products' => $p_map_attribute['skip_products']);
							} else {
								if (isset($response->message) && $response->message != "") {
									$this->Convpfm_TVC_Admin_Helper->plugin_log($response->message, 'product_sync');
									$conv_additional_data['product_sync_alert'] = $response->message;
									$this->Convpfm_TVC_Admin_Helper->set_convpfm_additional_data($conv_additional_data);
									$feed_data = array(
										"product_sync_alert" => $response->message,
									);
									$Convpfm_Admin_DB_Helper->tvc_update_row("convpfm_product_feed", $feed_data, array("id" => $feedId));
								}
								return array('error' => true, 'message' => isset($response->message) ? esc_attr($response->message) : "", "products" => $products, "p_map_attribute" => $p_map_attribute);
							}
						} else if (!empty($p_map_attribute['message'])) {
							return array('error' => true, 'message' => esc_attr($p_map_attribute['message']));
						}
					}
				} else {
					// add scheduled cron job					
					as_unschedule_all_actions('auto_feed_wise_product_sync_process_scheduler_convpfm', array("feedId" => $feedId));
				}
			} catch (Exception $e) {
				$feed_data = array(
					"product_sync_alert" => $e->getMessage(),
				);
				$Convpfm_Admin_DB_Helper->tvc_update_row("convpfm_product_feed", $feed_data, array("id" => $feedId));
				$conv_additional_data['product_sync_alert'] = $e->getMessage();
				$this->Convpfm_TVC_Admin_Helper->set_convpfm_additional_data($conv_additional_data);
				$this->Convpfm_TVC_Admin_Helper->plugin_log($e->getMessage(), 'product_sync');
			}

		}

		public function conv_get_feed_wise_map_product_attribute($products, $tvc_currency, $merchantId, $product_batch_size = 100, $feed_attribute = '', $prefix = '')
		{
			try {
				if (!empty($products)) {					
					global $wpdb;
					$tve_table_prefix = $wpdb->prefix;
					$plan_id = sanitize_text_field($this->Convpfm_TVC_Admin_Helper->get_plan_id());
					$items = [];
					$validProducts = [];
					$skipProducts = [];
					$product_ids = [];
					$deletedIds = [];
					$batchId = time();
					// $sync_profile = $this->Convpfm_Admin_DB_Helper->tvc_get_results('convpfm_product_sync_profile');
					// // set profile id in array key
					// $sync_profile_data = array();
					// if (!empty($sync_profile)) {
					// 	foreach ($sync_profile as $key => $value) {
					// 		$sync_profile_data[$value->id] = $value;
					// 	}
					// }
					// if (empty($sync_profile_data)) {
					// 	return array("error" => true, "message" => esc_html__("No product sync profiles find.", "product-feed-manager-for-woocommerce"));
					// }
					// if (empty($products)) {
					// 	return array("error" => true, "message" => esc_html__("Products not found.", "product-feed-manager-for-woocommerce"));
					// }
					$products_sync = 0;
					$last_sync_id = 0;
					foreach ($products as $postkey => $postvalue) {
						$last_sync_id = $postvalue->w_product_id;
						$product_ids[] = $postvalue->w_product_id;
						$postmeta = [];
						$postmeta = $this->Convpfm_TVC_Admin_Helper->tvc_get_post_meta($postvalue->w_product_id);
						$prd = wc_get_product($postvalue->w_product_id);
						$postObj = (object) array_merge((array) get_post($postvalue->w_product_id), (array) $postmeta);
						$permalink = esc_url_raw(get_permalink($postvalue->w_product_id));
						$product = array(
							'channel' => 'online',
							'link' => esc_url_raw(get_permalink($postvalue->w_product_id)),
							'google_product_category' => sanitize_text_field($postvalue->g_cat_id) != '0' ? sanitize_text_field($postvalue->g_cat_id) : ''
						);

						$temp_product = array();
						$fixed_att_select_list = array("gender", "age_group", "shipping", "tax", "content_language", "target_country", "condition", "adult", "is_bundle", "identifier_exists");
						$formArray = (array) $feed_attribute;

						if (empty($formArray)) {
							return array("error" => true, "message" => esc_html__("Feed wise Product attribute not found.", "product-feed-manager-for-woocommerce"));
						}
						foreach ($fixed_att_select_list as $fixed_key) {
							if (isset($formArray[$fixed_key]) && $formArray[$fixed_key] != "") {
								if ($fixed_key == "shipping" && $formArray[$fixed_key] != "") {
									$temp_product[$fixed_key]['price']['value'] = sanitize_text_field($formArray[$fixed_key]);
									$temp_product[$fixed_key]['price']['currency'] = sanitize_text_field($tvc_currency);
									$temp_product[$fixed_key]['country'] = sanitize_text_field($formArray['target_country']);
								} else if ($fixed_key == "tax" && $formArray[$fixed_key] != "") {
									$temp_product['taxes']['rate'] = sanitize_text_field($formArray[$fixed_key]);
									$temp_product['taxes']['country'] = sanitize_text_field($formArray['target_country']);
								} else if ($formArray[$fixed_key] != "") {
									$temp_product[$fixed_key] = sanitize_text_field($formArray[$fixed_key]);
								}
							}
							unset($formArray[$fixed_key]);
						}

						$product = array_merge($temp_product, $product);
						$conv_additional_data = $this->Convpfm_TVC_Admin_Helper->get_convpfm_additional_data();
						$product_id_prefix = $prefix;

						if ($prd->get_type() == "variable") {
							$p_variations = $prd->get_children();
							if (!empty($p_variations)) {
								foreach ($p_variations as $v_key => $variation_id) {
									$variation = wc_get_product($variation_id);
									if (empty($variation)) {
										continue;
									}									
									if($variation->get_stock_status() == 'outofstock'){
										//Delete outstock product 
										$deletedIds[] = $postvalue->w_product_id;
										continue;
									  }
									$variation_description = wc_format_content($variation->get_description());
									unset($product['customAttributes']);
									$postmeta_var = (object) $this->Convpfm_TVC_Admin_Helper->tvc_get_post_meta($variation_id);
									$formArray_val = $formArray['title'];
									$product['title'] = (isset($postObj->$formArray_val)) ? sanitize_text_field($postObj->$formArray_val) : get_the_title($postvalue->w_product_id);
									$tvc_temp_desc_key = $formArray['description'];
									if ($tvc_temp_desc_key == 'post_excerpt' || $tvc_temp_desc_key == 'post_content') {
										$product['description'] = ($variation_description != "") ? sanitize_text_field($variation_description) : sanitize_text_field($postObj->$tvc_temp_desc_key);
									} else {
										$product['description'] = sanitize_text_field($postObj->$tvc_temp_desc_key);
									}

									$product['item_group_id'] = $postvalue->w_product_id;
									$productTypes = $this->get_product_category($postvalue->w_product_id);
									if (!empty($productTypes)) {
										$product['productTypes'] = $productTypes;
									}
									$image_id = $variation->get_image_id();
									$product['image_link'] = esc_url_raw(wp_get_attachment_image_url($image_id, 'full'));
									$variation_permalink = esc_url_raw(get_permalink($variation_id));
									$product['link'] = $variation_permalink != '' ? $variation_permalink : $permalink;
									$variation_attributes = $variation->get_variation_attributes();
									if (!empty($variation_attributes)) {
										foreach ($variation_attributes as $va_key => $va_value) {
											$va_key = str_replace("_", " ", $va_key);
											if (strpos($va_key, 'color') !== false) {
												$product['color'] = $va_value;
											} else if (strpos($va_key, 'size') !== false) {
												$product['sizes'] = $va_value;
											} else {
												$va_key = str_replace("attribute", "", $va_key);
												$product['customAttributes'][] = array("name" => $va_key, "value" => $va_value);
											}
										}
									}

									foreach ($formArray as $key => $value) {
										if ($key == 'id') {
											if (!empty($product_id_prefix && $product_id_prefix != '')) {
												$product[$key] = sanitize_text_field(isset($postmeta_var->$value) ? $product_id_prefix . $postmeta_var->$value : $product_id_prefix . $variation_id);
												$product['offer_id'] = sanitize_text_field(isset($postmeta_var->$value) ? $product_id_prefix . $postmeta_var->$value : $product_id_prefix . $variation_id);
											} else {
												$product[$key] = sanitize_text_field(isset($postmeta_var->$value) ? $postmeta_var->$value : $variation_id);
												$product['offer_id'] = sanitize_text_field(isset($postmeta_var->$value) ? $postmeta_var->$value : $variation_id);
											}
										} elseif ($key == 'gtin' && (isset($postmeta_var->$value) || isset($postObj->$value))) {
											$product[$key] = sanitize_text_field(isset($postmeta_var->$value) ? $postmeta_var->$value : $postObj->$value);
										} elseif ($key == 'mpn' && (isset($postmeta_var->$value) || isset($postObj->$value))) {
											$product[$key] = sanitize_text_field(isset($postmeta_var->$value) ? $postmeta_var->$value : $postObj->$value);
										} elseif ($key == 'price') {
											if (isset($postmeta_var->$value) && $postmeta_var->$value > 0) {
												$product[$key]['value'] = sanitize_text_field($postmeta_var->$value);
											} else if (isset($postmeta_var->_regular_price) && $postmeta_var->_regular_price && $postmeta_var->_regular_price > 0) {
												$product[$key]['value'] = sanitize_text_field($postmeta_var->_regular_price);
											} else if (isset($postmeta_var->_price) && $postmeta_var->_price && $postmeta_var->_price > 0) {
												$product[$key]['value'] = sanitize_text_field($postmeta_var->_price);
											} else if (isset($postmeta_var->_sale_price) && $postmeta_var->_sale_price && $postmeta_var->_sale_price > 0) {
												$product[$key]['value'] = sanitize_text_field($postmeta_var->_sale_price);
											} else {
												unset($product[$key]);
											}
											if (isset($product[$key]['value']) && $product[$key]['value'] > 0) {
												$product[$key]['currency'] = sanitize_text_field($tvc_currency);
											} 
											// else {
											// 	$skipProducts[$product->ID] = $postmeta_var;
											// }
										} else if ($key == 'sale_price') {
											if (isset($postmeta_var->$value) && $postmeta_var->$value > 0) {
												$product[$key]['value'] = $postmeta_var->$value;
											} else if (isset($postmeta_var->_sale_price) && $postmeta_var->_sale_price && $postmeta_var->_sale_price > 0) {
												$product[$key]['value'] = $postmeta_var->_sale_price;
											} else {
												unset($product[$key]);
											}
											if (isset($product[$key]['value']) && $product[$key]['value'] > 0) {
												$product[$key]['currency'] = sanitize_text_field($tvc_currency);
											}
										} else if ($key == 'availability') {
											$tvc_find = array("instock", "outofstock", "onbackorder");
											$tvc_replace = array("in stock", "out of stock", "preorder");
											if (isset($postmeta_var->$value) && $postmeta_var->$value != "") {
												$stock_status = $postmeta_var->$value;
												$stock_status = str_replace($tvc_find, $tvc_replace, $stock_status);
												$product[$key] = sanitize_text_field($stock_status);
											} else {
												$stock_status = $postmeta_var->_stock_status;
												$stock_status = str_replace($tvc_find, $tvc_replace, $stock_status);
												$product[$key] = sanitize_text_field($stock_status);
											}
										} else if (in_array($key, array("brand"))) { //list of cutom option added (Pro user only)                    
											$product_brand = "";
											$is_custom_attr_brand = false;
											$woo_attr_list = json_decode(json_encode($this->Convpfm_TVC_Admin_Helper->getTableData($tve_table_prefix . 'woocommerce_attribute_taxonomies', ['attribute_name'])), true);
											if (!empty($woo_attr_list)) {
												foreach ($woo_attr_list as $key_attr => $value_attr) {
													if (isset($value_attr['field']) && $value_attr['field'] == $value) {
														$is_custom_attr_brand = true;
														$product_brand = $this->Convpfm_TVC_Admin_Helper->get_custom_taxonomy_name($postvalue->w_product_id, "pa_" . $value);
													}
												}
											}
											if ($is_custom_attr_brand == false && $product_brand == "") {
												$product_brand = $this->Convpfm_TVC_Admin_Helper->add_additional_option_val_in_map_product_attribute($key, $postvalue->w_product_id);
											}
											if ($product_brand != "") {
												$product[$key] = sanitize_text_field($product_brand);
											}
										} else if($key == 'product_weight'){
											if(isset($postmeta_var->$value) && $postmeta_var->$value != ""){
												$product[$key]['value'] = sanitize_text_field($postmeta_var->$value);
												$product[$key]['unit'] = get_option('woocommerce_weight_unit');
											}
											
										} else if($key == 'shipping_weight'){
											if(isset($postmeta_var->$value) && $postmeta_var->$value != ""){
											  $product[$key]['value'] = sanitize_text_field($postmeta_var->$value);
											  $product[$key]['unit'] = get_option('woocommerce_weight_unit');
											}
											
										} else if (isset($postmeta_var->$value) && $postmeta_var->$value != "") {
											$product[$key] = sanitize_text_field($postmeta_var->$value);
										}
									}
									$category_mapping = array(
										'google_product_category' => $postvalue->g_cat_id != 0 ? sanitize_text_field($postvalue->g_cat_id) : '',
										'facebook_product_category' => $postvalue->g_cat_id != 0 ? sanitize_text_field($postvalue->g_cat_id) : '',
									);
									$item = [
										'merchant_id' => sanitize_text_field($merchantId),
										'batch_id' => sanitize_text_field(++$batchId),
										'method' => sanitize_text_field('insert'),
										'product' => $product,
										'category_mapping' => $category_mapping,
									];
									$items[] = $item;

								}
								$validProducts[] = $postvalue;
							} else {
								//Delete the variant product which does not have children
								$deletedIds[] = $postvalue->w_product_id;
							}

						} else {
							//simpleproduct: 
							if($prd->get_stock_status() == 'outofstock'){
								//Delete outstock product 
								$deletedIds[] = $postvalue->w_product_id;
								continue;
							}
							$image_id = $prd->get_image_id();
							$product['image_link'] = esc_url_raw(wp_get_attachment_image_url($image_id, 'full'));
							$productTypes = $this->get_product_category($postvalue->w_product_id);
							if (!empty($productTypes)) {
								$product['productTypes'] = $productTypes;
							}
							foreach ($formArray as $key => $value) {
								if ($key == 'id') {
									if (!empty($product_id_prefix)) {
										$product[$key] = sanitize_text_field(isset($postObj->$value) ? $product_id_prefix . $postObj->$value : $product_id_prefix . $postvalue->w_product_id);
										$product['offer_id'] = sanitize_text_field(isset($postObj->$value) ? $product_id_prefix . $postObj->$value : $product_id_prefix . $postvalue->w_product_id);
									} else {
										$product[$key] = sanitize_text_field(isset($postObj->$value) ? $postObj->$value : $postvalue->w_product_id);
										$product['offer_id'] = sanitize_text_field(isset($postObj->$value) ? $postObj->$value : $postvalue->w_product_id);
									}
								} elseif ($key == 'price') {
									if (isset($postObj->$value) && $postObj->$value > 0) {
										$product[$key]['value'] = sanitize_text_field($postObj->$value);
									} else if (isset($postObj->_regular_price) && $postObj->_regular_price && $postObj->_regular_price > 0) {
										$product[$key]['value'] = sanitize_text_field($postObj->_regular_price);
									} else if (isset($postObj->_price) && $postObj->_price && $postObj->_price > 0) {
										$product[$key]['value'] = sanitize_text_field($postObj->_price);
									} else if (isset($postObj->_sale_price) && $postObj->_sale_price && $postObj->_sale_price > 0) {
										$product[$key]['value'] = sanitize_text_field($postObj->_sale_price);
									}
									if (isset($product[$key]['value']) && $product[$key]['value'] > 0) {
										$product[$key]['currency'] = sanitize_text_field($tvc_currency);
									} 
									// else {
									// 	$skipProducts[$postObj->ID] = $postObj;
									// }
								} else if ($key == 'sale_price') {
									if (isset($postObj->$value) && $postObj->$value > 0) {
										$product[$key]['value'] = $postObj->$value;
									} else if (isset($postObj->_sale_price) && $postObj->_sale_price && $postObj->_sale_price > 0) {
										$product[$key]['value'] = $postObj->_sale_price;
									}
									if (isset($product[$key]['value']) && $product[$key]['value'] > 0) {
										$product[$key]['currency'] = sanitize_text_field($tvc_currency);
									}
								} else if ($key == 'availability') {
									$tvc_find = array("instock", "outofstock", "onbackorder");
									$tvc_replace = array("in stock", "out of stock", "preorder");
									if (isset($postObj->$value) && $postObj->$value != "") {
										$stock_status = $postObj->$value;
										$stock_status = str_replace($tvc_find, $tvc_replace, $stock_status);
										$product[$key] = sanitize_text_field($stock_status);
									} else {
										$stock_status = $postObj->_stock_status;
										$stock_status = str_replace($tvc_find, $tvc_replace, $stock_status);
										$product[$key] = sanitize_text_field($stock_status);
									}
								} else if (in_array($key, array("brand"))) {
									//list of cutom option added
									$product_brand = "";
									$is_custom_attr_brand = false;
									$woo_attr_list = json_decode(json_encode($this->Convpfm_TVC_Admin_Helper->getTableData($tve_table_prefix . 'woocommerce_attribute_taxonomies', ['attribute_name'])), true);
									if (!empty($woo_attr_list)) {
										foreach ($woo_attr_list as $key_attr => $value_attr) {
											if (isset($value_attr['field']) && $value_attr['field'] == $value) {
												$is_custom_attr_brand = true;
												$product_brand = $this->Convpfm_TVC_Admin_Helper->get_custom_taxonomy_name($postvalue->w_product_id, "pa_" . $value);
											}
										}
									}
									if ($is_custom_attr_brand == false && $product_brand == "") {
										$product_brand = $this->Convpfm_TVC_Admin_Helper->add_additional_option_val_in_map_product_attribute($key, $postvalue->w_product_id);
									}
									if ($product_brand != "") {
										$product[$key] = sanitize_text_field($product_brand);
									}
								} else if($key == 'product_weight'){
									if(isset($postObj->$value) && $postObj->$value != ""){
										$product[$key]['value'] = sanitize_text_field($postObj->$value);
										$product[$key]['unit'] = get_option('woocommerce_weight_unit');
									}
									
								} else if($key == 'shipping_weight'){
									if(isset($postObj->$value) && $postObj->$value != ""){
									  $product[$key]['value'] = sanitize_text_field($postObj->$value);
									  $product[$key]['unit'] = get_option('woocommerce_weight_unit');
									}
									
								} else if($key == 'product_shipping_class') {
									$product[$key] = $prd->get_shipping_class();
								} else if (isset($postObj->$value) && $postObj->$value != "") {
									$product[$key] = $postObj->$value;
								}
							}
							$category_mapping = array(
								'google_product_category' => $postvalue->g_cat_id != 0 ? sanitize_text_field($postvalue->g_cat_id) : '',
								'facebook_product_category' => $postvalue->g_cat_id != 0 ? sanitize_text_field($postvalue->g_cat_id) : '',
							);
							$item = [
								'merchant_id' => sanitize_text_field($merchantId),
								'batch_id' => sanitize_text_field(++$batchId),
								'method' => sanitize_text_field('insert'),
								'product' => $product,
								'category_mapping' => $category_mapping
							];
							$items[] = $item;
							$validProducts[] = $postvalue;
						}
						
						$products_sync++;
						if (count($items) >= $product_batch_size) {
							return array('error' => false, 'items' => $items, 'valid_products' => $validProducts, 'deleted_products' => $deletedIds, 'skip_products' => $skipProducts, 'product_ids' => $product_ids, 'last_sync_product_id' => $postvalue->id, 'products_sync' => $products_sync);
						}
					}
					
					return array('error' => false, 'items' => $items, 'valid_products' => $validProducts, 'deleted_products' => $deletedIds, 'skip_products' => $skipProducts, 'product_ids' => $product_ids, 'last_sync_product_id' => $last_sync_id, 'products_sync' => $products_sync);
				}
			} catch (Exception $e) {
				$this->Convpfm_TVC_Admin_Helper->plugin_log($e->getMessage(), 'product_sync');
			}
		}

	}
}