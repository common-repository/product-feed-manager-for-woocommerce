<?php

/**
 * Class for generating the actual feeds
 */
class convpfm_GetProducts
{

	private $feedback;
	public $feed_config;
	private $products = array();
	private $utm = array();
	public $utm_part;
	public $project_config;
	private $upload_dir;
	private $base;
	private $path;
	private $file;
	protected $get_products;
	protected $childID;
	protected $parentID;

	public function __construct()
	{
		$this->get_products = array();
	}

	/**
	 * Function to add CDATA brackets to title, short_description and description attributes
	 */
	protected function convpfm_append_cdata($string)
	{
		if (!empty($string)) {
			return "<![CDATA[$string]]>";
		}
	}

	/**
	 * Check if a plugin is active
	 */
	public function convpfm_is_plugin_active($plugin)
	{
		return in_array($plugin, (array) get_option('active_plugins', array()));
	}

	/**
	 * An improved function for the strip_tags
	 * Removing tags but replacing them with spaces instead of just removing them
	 */
	public function rip_tags($string)
	{
		// ----- remove HTML TAGs ----- 
		$string = preg_replace('/<[^>]*>/', ' ', $string);

		// ----- remove control characters ----- 
		$string = str_replace("\r", '', $string);    // --- replace with empty space
		$string = str_replace("\n", ' ', $string);   // --- replace with space
		$string = str_replace("\t", ' ', $string);   // --- replace with space
		$string = str_replace("%", '%25', $string);   // --- replace with space

		// ----- remove multiple spaces ----- 
		$string = trim(preg_replace('/ {2,}/', ' ', $string));

		return $string;
	}

	
	/**
	 * Strip unwanted UTF chars from string
	 */
	public function convpfm_utf8_for_xml($string)
	{
		$string = html_entity_decode($string);
		return preg_replace('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', ' ', $string);
	}

	/**
	 * Function that will create an append with Google Analytics UTM parameters
	 * Removes UTM paramaters that are left blank
	 */


	/**
	 * Converts an ordinary xml string into a CDATA string
	 */
	public function convpfm_convert_to_cdata($string)
	{
		return "<![CDATA[ $string ]]>";
	}

	/**
	 * Get number of variation sales for a product variation
	 */
	private function convpfm_get_nr_orders_variation($variation_id)
	{
		global $wpdb;

		$nr_sales = 0;

		if (is_numeric($variation_id)) {
			// Getting all Order Items with that variation ID
			$nr_sales = $wpdb->get_col($wpdb->prepare("
        			SELECT count(*) AS nr_sales
        			FROM {$wpdb->prefix}woocommerce_order_itemmeta 
        			WHERE meta_value = %s
    			", $variation_id));
		}
		return $nr_sales;
	}

	/**
	 * Get custom attribute names for a product
	 */
	private function get_custom_attributes($productId)
	{
		global $wpdb;
		$list = array();

		$sql = "SELECT meta.meta_id, meta.meta_key as name, meta.meta_value as type FROM " . $wpdb->prefix . "postmeta" . " AS meta, " . $wpdb->prefix . "posts" . " AS posts WHERE meta.post_id=" . $productId . " AND meta.post_id = posts.id GROUP BY meta.meta_key ORDER BY meta.meta_key ASC";
		$data = $wpdb->get_results($sql);

		if (count($data)) {
			foreach ($data as $key => $value) {
				$value_display = str_replace("_", " ", $value->name);

				if (!preg_match("/_product_attributes/i", $value->name)) {
					$list[$value->name] = ucfirst($value_display);

					// Adding support for the Yoast WooCommerce SEO unique identifiers
					if ($value->name == "wpseo_global_identifier_values") {
						$type_expl = explode("\";", $value->type);
						$yoast_gtin8_value = @explode(":\"", $type_expl[1]);
						$yoast_gtin12_value = @explode(":\"", $type_expl[3]);
						$yoast_gtin13_value = @explode(":\"", $type_expl[5]);
						$yoast_gtin14_value = @explode(":\"", $type_expl[7]);
						$yoast_isbn_value = @explode(":\"", $type_expl[9]);
						$yoast_mpn_value = @explode(":\"", $type_expl[11]);

						if (isset($yoast_gtin8_value[1])) {
							$list["yoast_gtin8"] = $yoast_gtin8_value[1];
						}
						if (isset($yoast_gtin12_value[1])) {
							$list["yoast_gtin12"] = $yoast_gtin12_value[1];
						}
						if (isset($yoast_gtin13_value[1])) {
							$list["yoast_gtin13"] = $yoast_gtin13_value[1];
						}
						if (isset($yoast_gtin14_value[1])) {
							$list["yoast_gtin14"] = $yoast_gtin14_value[1];
						}
						if (isset($yoast_isbn_value[1])) {
							$list["yoast_isbn"] = $yoast_isbn_value[1];
						}
						if (isset($yoast_mpn_value[1])) {
							$list["yoast_mpn"] = $yoast_mpn_value[1];
						}
					}

					// Adding support SEOpress unique identifiers
					if ($value->name == "seopress_barcode") {
						$list["seopress_barcode"] = $value->type;
					}
				} else {
					$product_attr = unserialize($value->type);

					if ((!empty($product_attr)) && (is_array($product_attr))) {
						foreach ($product_attr as $key_inner => $arr_value) {
							if (is_array($arr_value)) {
								if (!array_key_exists('name', $arr_value)) {
									$value_display = @str_replace("_", " ", $arr_value['name']);
									$list[$key_inner] = ucfirst($value_display);
								}
							}
						}
					}
				}
			}
			return $list;
		}
		return false;
	}

	/**
	 * Get orders for given time period used in filters
	 */
	public function convpfm_get_orders($project_config)
	{

		$allowed_products = array();

		if (isset($project_config['total_product_orders_lookback'])) {

			if ($project_config['total_product_orders_lookback'] > 0) {

				$query_args = array(
					'post_type'      => wc_get_order_types(),
					'post_status'    => array_keys(wc_get_order_statuses()),
					'posts_per_page' => 999999999999,
				);
				$all_orders      = get_posts($query_args);

				$today = date("Y-m-d");
				$today_limit = date('Y-m-d', strtotime('-' . $project_config['total_product_orders_lookback'] . ' days', strtotime($today)));

				foreach ($all_orders as $orders) {
					$order = wc_get_order($orders->ID);
					$order_data = $order->get_data();

					$order_date_created = $order_data['date_created']->date('Y-m-d');

					if ($order_date_created >= $today_limit) {
						foreach ($order->get_items() as $item_key => $item_values) {
							$order_product_id = $item_values->get_product_id();
							$order_variation_id = $item_values->get_variation_id();

							// When a variation was sold, add the variation	
							if ($order_variation_id > 0) {
								$order_product_id = $order_variation_id;
							}

							// Only for existing products
							if ($order_product_id > 0) {

								// Only add products that are not in the array yet
								if (!in_array($order_product_id, $allowed_products)) {
									$allowed_products[] = $order_product_id;
								}
							}
						}
					}
				}
			}
		}
		return $allowed_products;
	}

	/**
	 * Get category path (needed for Prisjakt)
	 */
	public function convpfm_get_term_parents($id, $taxonomy, string $link = null, $project_taxonomy, $nicename = false, $visited = array())
	{
		// Only add Home to the beginning of the chain when we start buildin the chain
		if (empty($visited)) {
			$chain = 'Home';
		} else {
			$chain = '';
		}

		$parent = get_term($id, $taxonomy);
		$separator = ' &gt; ';

		if ($project_taxonomy == "Prisjakt") {
			$separator = ' / ';
		}

		if (is_wp_error($parent))
			return $parent;

		if ($parent) {
			if ($nicename) {
				$name = $parent->slug;
			} else {
				$name = $parent->name;
			}

			if ($parent->parent && ($parent->parent != $parent->term_id) && !in_array($parent->parent, $visited, TRUE)) {
				$visited[] = $parent->parent;
				$chain .= $this->convpfm_get_term_parents($parent->parent, $taxonomy, $link, $separator, $nicename, $visited);
			}

			if ($link) {
				$chain .= $separator . $name;
			} else {
				$chain .= $separator . $name;
			}
		}
		return $chain;
	}

	/**
	 * Create a floatval for prices
	 */
	public function convpfm_floatvalue($val)
	{
		$val = str_replace(",", ".", $val);
		$val = preg_replace('/\.(?=.*\.)/', '', $val);
		return floatval($val);
	}

	/**
	 * Get all configured shipping zones
	 */
	public function convpfm_get_shipping_zones()
	{
		if (class_exists('WC_Shipping_Zones')) {
			$all_zones = WC_Shipping_Zones::get_zones();
			return $all_zones;
		}
		return false;
	}

	/**
	 * Get installment for product
	 */
	public function convpfm_get_installment($project_config, $productId)
	{
		$installment = "";
		$currency = get_woocommerce_currency();
		if (isset($project_config['WCML'])) {
			$currency = $project_config['WCML'];
		}
		$installment_months = get_post_meta($productId, '_convpfm_installment_months', true);
		$installment_amount = get_post_meta($productId, '_convpfm_installment_amount', true);

		if (!empty($installment_amount)) {
			$installment = $installment_months . ":" . $installment_amount . " " . $currency;
		}
		return $installment;
	}

	/**
	 * COnvert country name to two letter code
	 */
	public function convpfm_country_to_code($country)
	{

		$countryList = array(
			'AF' => 'Afghanistan',
			'AX' => 'Aland Islands',
			'AL' => 'Albania',
			'DZ' => 'Algeria',
			'AS' => 'American Samoa',
			'AD' => 'Andorra',
			'AO' => 'Angola',
			'AI' => 'Anguilla',
			'AQ' => 'Antarctica',
			'AG' => 'Antigua and Barbuda',
			'AR' => 'Argentina',
			'AM' => 'Armenia',
			'AW' => 'Aruba',
			'AU' => 'Australia',
			'AT' => 'Austria',
			'AZ' => 'Azerbaijan',
			'BS' => 'Bahamas the',
			'BH' => 'Bahrain',
			'BD' => 'Bangladesh',
			'BB' => 'Barbados',
			'BY' => 'Belarus',
			'BE' => 'Belgium',
			'BZ' => 'Belize',
			'BJ' => 'Benin',
			'BM' => 'Bermuda',
			'BT' => 'Bhutan',
			'BO' => 'Bolivia',
			'BA' => 'Bosnia and Herzegovina',
			'BW' => 'Botswana',
			'BV' => 'Bouvet Island (Bouvetoya)',
			'BR' => 'Brazil',
			'IO' => 'British Indian Ocean Territory (Chagos Archipelago)',
			'VG' => 'British Virgin Islands',
			'BN' => 'Brunei Darussalam',
			'BG' => 'Bulgaria',
			'BF' => 'Burkina Faso',
			'BI' => 'Burundi',
			'KH' => 'Cambodia',
			'CM' => 'Cameroon',
			'CA' => 'Canada',
			'CV' => 'Cape Verde',
			'KY' => 'Cayman Islands',
			'CF' => 'Central African Republic',
			'TD' => 'Chad',
			'CL' => 'Chile',
			'CN' => 'China',
			'CX' => 'Christmas Island',
			'CC' => 'Cocos (Keeling) Islands',
			'CO' => 'Colombia',
			'KM' => 'Comoros the',
			'CD' => 'Congo',
			'CG' => 'Congo the',
			'CK' => 'Cook Islands',
			'CR' => 'Costa Rica',
			'CI' => 'Cote d\'Ivoire',
			'HR' => 'Croatia',
			'CU' => 'Cuba',
			'CY' => 'Cyprus',
			'CZ' => 'Czech Republic',
			'DK' => 'Denmark',
			'DJ' => 'Djibouti',
			'DM' => 'Dominica',
			'DO' => 'Dominican Republic',
			'EC' => 'Ecuador',
			'EG' => 'Egypt',
			'SV' => 'El Salvador',
			'GQ' => 'Equatorial Guinea',
			'ER' => 'Eritrea',
			'EE' => 'Estonia',
			'ET' => 'Ethiopia',
			'FO' => 'Faroe Islands',
			'FK' => 'Falkland Islands',
			'FJ' => 'Fiji the Fiji Islands',
			'FI' => 'Finland',
			'FR' => 'France',
			'GF' => 'French Guiana',
			'PF' => 'French Polynesia',
			'TF' => 'French Southern Territories',
			'GA' => 'Gabon',
			'GM' => 'Gambia the',
			'GE' => 'Georgia',
			'DE' => 'Germany',
			'GH' => 'Ghana',
			'GI' => 'Gibraltar',
			'GR' => 'Greece',
			'GL' => 'Greenland',
			'GD' => 'Grenada',
			'GP' => 'Guadeloupe',
			'GU' => 'Guam',
			'GT' => 'Guatemala',
			'GG' => 'Guernsey',
			'GN' => 'Guinea',
			'GW' => 'Guinea-Bissau',
			'GY' => 'Guyana',
			'HT' => 'Haiti',
			'HM' => 'Heard Island and McDonald Islands',
			'VA' => 'Holy See',
			'HN' => 'Honduras',
			'HK' => 'Hong Kong',
			'HU' => 'Hungary',
			'IS' => 'Iceland',
			'IN' => 'India',
			'ID' => 'Indonesia',
			'IR' => 'Iran',
			'IQ' => 'Iraq',
			'IE' => 'Ireland',
			'IM' => 'Isle of Man',
			'IL' => 'Israel',
			'IT' => 'Italy',
			'JM' => 'Jamaica',
			'JP' => 'Japan',
			'JE' => 'Jersey',
			'JO' => 'Jordan',
			'KZ' => 'Kazakhstan',
			'KE' => 'Kenya',
			'KI' => 'Kiribati',
			'KP' => 'Korea',
			'KR' => 'Korea',
			'KW' => 'Kuwait',
			'KG' => 'Kyrgyz Republic',
			'LA' => 'Lao',
			'LV' => 'Latvia',
			'LB' => 'Lebanon',
			'LS' => 'Lesotho',
			'LR' => 'Liberia',
			'LY' => 'Libyan Arab Jamahiriya',
			'LI' => 'Liechtenstein',
			'LT' => 'Lithuania',
			'LU' => 'Luxembourg',
			'MO' => 'Macao',
			'MK' => 'Macedonia',
			'MG' => 'Madagascar',
			'MW' => 'Malawi',
			'MY' => 'Malaysia',
			'MV' => 'Maldives',
			'ML' => 'Mali',
			'MT' => 'Malta',
			'MH' => 'Marshall Islands',
			'MQ' => 'Martinique',
			'MR' => 'Mauritania',
			'MU' => 'Mauritius',
			'YT' => 'Mayotte',
			'MX' => 'Mexico',
			'FM' => 'Micronesia',
			'MD' => 'Moldova',
			'MC' => 'Monaco',
			'MN' => 'Mongolia',
			'ME' => 'Montenegro',
			'MS' => 'Montserrat',
			'MA' => 'Morocco',
			'MZ' => 'Mozambique',
			'MM' => 'Myanmar',
			'NA' => 'Namibia',
			'NR' => 'Nauru',
			'NP' => 'Nepal',
			'AN' => 'Netherlands Antilles',
			'NL' => 'Netherlands',
			'NC' => 'New Caledonia',
			'NZ' => 'New Zealand',
			'NI' => 'Nicaragua',
			'NE' => 'Niger',
			'NG' => 'Nigeria',
			'NU' => 'Niue',
			'NF' => 'Norfolk Island',
			'MP' => 'Northern Mariana Islands',
			'NO' => 'Norway',
			'OM' => 'Oman',
			'PK' => 'Pakistan',
			'PW' => 'Palau',
			'PS' => 'Palestinian Territory',
			'PA' => 'Panama',
			'PG' => 'Papua New Guinea',
			'PY' => 'Paraguay',
			'PE' => 'Peru',
			'PH' => 'Philippines',
			'PN' => 'Pitcairn Islands',
			'PL' => 'Poland',
			'PT' => 'Portugal',
			'PR' => 'Puerto Rico',
			'QA' => 'Qatar',
			'RE' => 'Reunion',
			'RO' => 'Romania',
			'RU' => 'Russian Federation',
			'RW' => 'Rwanda',
			'BL' => 'Saint Barthelemy',
			'SH' => 'Saint Helena',
			'KN' => 'Saint Kitts and Nevis',
			'LC' => 'Saint Lucia',
			'MF' => 'Saint Martin',
			'PM' => 'Saint Pierre and Miquelon',
			'VC' => 'Saint Vincent and the Grenadines',
			'WS' => 'Samoa',
			'SM' => 'San Marino',
			'ST' => 'Sao Tome and Principe',
			'SA' => 'Saudi Arabia',
			'SN' => 'Senegal',
			'RS' => 'Serbia',
			'SC' => 'Seychelles',
			'SL' => 'Sierra Leone',
			'SG' => 'Singapore',
			'SK' => 'Slovakia',
			'SI' => 'Slovenia',
			'SB' => 'Solomon Islands',
			'SO' => 'Somalia, Somali Republic',
			'ZA' => 'South Africa',
			'GS' => 'South Georgia and the South Sandwich Islands',
			'ES' => 'Spain',
			'LK' => 'Sri Lanka',
			'SD' => 'Sudan',
			'SR' => 'Suriname',
			'SJ' => 'Svalbard & Jan Mayen Islands',
			'SZ' => 'Swaziland',
			'SE' => 'Sweden',
			'CH' => 'Switzerland',
			'SY' => 'Syrian Arab Republic',
			'TW' => 'Taiwan',
			'TJ' => 'Tajikistan',
			'TZ' => 'Tanzania',
			'TH' => 'Thailand',
			'TL' => 'Timor-Leste',
			'TG' => 'Togo',
			'TK' => 'Tokelau',
			'TO' => 'Tonga',
			'TT' => 'Trinidad and Tobago',
			'TN' => 'Tunisia',
			'TR' => 'Turkey',
			'TM' => 'Turkmenistan',
			'TC' => 'Turks and Caicos Islands',
			'TV' => 'Tuvalu',
			'UG' => 'Uganda',
			'UA' => 'Ukraine',
			'AE' => 'United Arab Emirates',
			'GB' => 'United Kingdom',
			'US' => 'United States',
			'UM' => 'United States Minor Outlying Islands',
			'VI' => 'United States Virgin Islands',
			'UY' => 'Uruguay, Eastern Republic of',
			'UZ' => 'Uzbekistan',
			'VU' => 'Vanuatu',
			'VE' => 'Venezuela',
			'VN' => 'Vietnam',
			'WF' => 'Wallis and Futuna',
			'EH' => 'Western Sahara',
			'YE' => 'Yemen',
			'ZM' => 'Zambia',
			'ZW' => 'Zimbabwe'
		);

		return (array_search($country, $countryList));
	}

	/**
	 * Get shipping cost for product
	 */
	public function convpfm_get_shipping_cost($class_cost_id, $project_config, $price, $tax_rates, $fullrate, $shipping_zones, $product_id, $item_group_id)
	{
		$shipping_cost = "";
		$shipping_arr = array();
		$zone_count = 0;
		$nr_shipping_zones = count($shipping_zones);
		$zone_details = array();
		$from_currency = get_woocommerce_currency();
		$add_all_shipping = "no";
		$add_all_shipping = get_option('add_all_shipping');

		// Normal shipping set-up
		$zone_count = count($shipping_arr) + 1;

		foreach ($shipping_zones as $zone) {
			// Start with a clean shipping zone
			$zone_details = array();
			$zone_details['country'] = "";

			// Start with a clean postal code
			$postal_code = array();

			foreach ($zone['zone_locations'] as $zone_type) {
				$code_from_config = $this->convpfm_country_to_code($project_config['countries']);

				// Only add shipping zones to the feed for specific feed country
				$ship_found = strpos($zone_type->code, $code_from_config);

				if (($ship_found !== false) || ($add_all_shipping == "yes")) {
					if ($zone_type->type == "country") {
						// This is a country shipping zone
						$zone_details['country'] = $zone_type->code;
					} elseif ($zone_type->type == "code") {
						// This is a country shipping zone
						$zone_details['country'] = $zone_type->code;
					} elseif ($zone_type->type == "state") {
						// This is a state shipping zone, split of country
						$zone_expl = explode(":", $zone_type->code);
						$zone_details['country'] = $zone_expl[0];

						// Adding a region is only allowed for these countries
						$region_countries = array('US', 'JP', 'AU');
						if (in_array($zone_details['country'], $region_countries)) {
							$zone_details['region'] = $zone_expl[1];
						}
					} elseif ($zone_type->type == "postcode") {
						// Create an array of postal codes so we can loop over it later
						if ($project_config['taxonomy'] == 'google_shopping') {
							$zone_type->code = str_replace("...", "-", $zone_type->code);
						}
						array_push($postal_code, $zone_type->code);
					} else {
						// Unknown shipping zone type
					}

					// Get the g:services and g:prices, because there could be multiple services the $shipping_arr could multiply again
					// g:service = "Method title - Shipping class costs"
					// for example, g:service = "Estimated Shipping - Heavy shipping". g:price would be 180			
					$shipping_methods     = $zone['shipping_methods'];

					foreach ($shipping_methods as $k => $v) {
						$method = $v->method_title;
						$method_id = $v->id;
						$shipping_rate_id = $v->instance_id;

						if ($v->enabled == "yes") {
							if (empty($zone_details['country'])) {
								$zone_details['service'] = $zone['zone_name'] . " " . $v->title;
							} else {
								$zone_details['service'] = $zone['zone_name'] . " " . $v->title . " " . $zone_details['country'];
							}
							$taxable = $v->tax_status;

							if (isset($v->instance_settings['cost'])) {
								$shipping_cost = $v->instance_settings['cost'];
								$shipping_cost = str_replace("* [qty]", "", $shipping_cost);
								$shipping_cost = trim($shipping_cost);     // trim white spaces

								if ($shipping_cost > 0) {
									// Do we need to convert the shipping costs with the Aelia Currency Switcher
									if ((isset($project_config['AELIA'])) && (!empty($GLOBALS['woocommerce-aelia-currencyswitcher'])) && (get_option('add_aelia_support') == "yes")) {

										if (!array_key_exists('base_currency', $project_config)) {
											$from_currency = get_woocommerce_currency();
										} else {
											$from_currency = $project_config['base_currency'];
										}

										// Get Aelia currency conversion prices
										$shipping_cost = apply_filters('wc_aelia_cs_convert', $shipping_cost, $from_currency, $project_config['AELIA']);
									}

									if ($taxable == "taxable") {
										foreach ($tax_rates as $k_inner => $w) {
											if ((isset($w['shipping'])) && ($w['shipping'] == "yes")) {
												$rate = (($fullrate) / 100);

												$shipping_cost = str_replace(",", ".", $shipping_cost);
												if (!is_string($shipping_cost)) {
													$shipping_cost = $shipping_cost * $rate;
													$shipping_cost = round($shipping_cost, 2);
												}
												$shipping_cost = wc_format_localized_price($shipping_cost);
											}
										}
									}
								}
							}

							// WooCommerce Table Rate - Bolder Elements
							if ($method_id == "table_rate" or $method_id == "betrs_shipping" or $method_id = "fish_n_ships") {
								// Set shipping cost variable
								$shipping_cost = 0;

								if (!empty($product_id)) {
									// Add product to cart
									if ((isset($product_id)) && ($product_id > 0)) {
										$quantity = 1;
										if (!empty($code_from_config)) {
											defined('WC_ABSPATH') || exit;

											// Load cart functions which are loaded only on the front-end.
											include_once WC_ABSPATH . 'includes/wc-cart-functions.php';
											include_once WC_ABSPATH . 'includes/class-wc-cart.php';

											wc_load_cart();

											WC()->customer->set_shipping_country($zone_details['country']);

											if (isset($zone_details['region'])) {
												WC()->customer->set_shipping_state(wc_clean($zone_details['region']));
											}

											if (isset($zone_details['postal_code'])) {
												WC()->customer->set_shipping_postcode(wc_clean($zone_details['postal_code']));
											}

											if (is_numeric($product_id)) {
												if (!is_bool($product_id)) {
													WC()->cart->add_to_cart($product_id, $quantity);
												}
											}

											// Read cart and get schipping costs
											foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
												$total_cost = WC()->cart->get_total();
												$shipping_cost = WC()->cart->get_shipping_total();
												$shipping_tax = WC()->cart->get_shipping_tax();
												$shipping_cost = ($shipping_cost + $shipping_tax);
												$shipping_cost = wc_format_localized_price($shipping_cost);
											}

											// Make sure to empty the cart again
											WC()->cart->empty_cart();
										}
									}
								}
							}

							// Official WooCommerce Table Rate plugin
							if ($method_id == "table_rate") {
								if ($this->convpfm_is_plugin_active('woocommerce-table-rate-shipping/woocommerce-table-rate-shipping.php')) {
									// Set shipping cost
									$shipping_cost = 0;

									if (!empty($product_id)) {
										// Add product to cart
										if ((isset($product_id)) && ($product_id > 0)) {
											$quantity = 1;
											if (!empty($code_from_config)) {
												defined('WC_ABSPATH') || exit;

												// Load cart functions which are loaded only on the front-end.
												include_once WC_ABSPATH . 'includes/wc-cart-functions.php';
												include_once WC_ABSPATH . 'includes/class-wc-cart.php';

												wc_load_cart();

												WC()->customer->set_shipping_country($zone_details['country']);

												if (isset($zone_details['region'])) {
													WC()->customer->set_shipping_state(wc_clean($zone_details['region']));
												}

												if (isset($zone_details['postal_code'])) {
													WC()->customer->set_shipping_postcode(wc_clean($zone_details['postal_code']));
												}

												if (is_numeric($product_id)) {
													WC()->cart->add_to_cart($product_id, $quantity);
												}

												// Read cart and get schipping costs
												foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
													$total_cost = WC()->cart->get_total();
													$shipping_cost = WC()->cart->get_shipping_total();
													$shipping_tax = WC()->cart->get_shipping_tax();
													$shipping_cost = ($shipping_cost + $shipping_tax);
													$shipping_cost = wc_format_localized_price($shipping_cost);
												}

												// Make sure to empty the cart again
												WC()->cart->empty_cart();
											}
										}
									}
								}
							}

							// CLASS SHIPPING COSTS
							if ((isset($v->instance_settings[$class_cost_id])) && ($class_cost_id != "no_class_cost")) {
								if (is_numeric($v->instance_settings[$class_cost_id])) {
									$shipping_cost = $v->instance_settings[$class_cost_id];

									// Do we need to convert the shipping costswith the Aelia Currency Switcher
									if ((isset($project_config['AELIA'])) && (!empty($GLOBALS['woocommerce-aelia-currencyswitcher'])) && (get_option('add_aelia_support') == "yes")) {
										if (!array_key_exists('base_currency', $project_config)) {
											// Get the WooCommerce base currency
											$from_currency = get_woocommerce_currency();
										} else {
											$from_currency = $project_config['base_currency'];
										}

										// Get Aelia currency conversion prices
										$shipping_cost = apply_filters('wc_aelia_cs_convert', $shipping_cost, $from_currency, $project_config['AELIA']);
									}

									if ($taxable == "taxable") {
										foreach ($tax_rates as $k_inner => $w) {
											if ((isset($w['shipping'])) && ($w['shipping'] == "yes")) {
												$rate = (($w['rate'] + 100) / 100);
												$shipping_cost = $shipping_cost * $rate;
												$shipping_cost = round($shipping_cost, 2);
												$shipping_cost = wc_format_localized_price($shipping_cost);
											}
										}
									}
								} else {
									$shipping_cost = $v->instance_settings[$class_cost_id];
									$shipping_cost = str_replace("[qty]", "1", $shipping_cost);
									$ship_piece = explode(" ", $shipping_cost);
									$shipping_cost = $ship_piece[0];
									$mathString = trim($shipping_cost);     // trim white spaces

									if (preg_match("/fee percent/", $mathString)) {
										$shipcost_piece = explode("+", $mathString);
										$mathString = trim($shipcost_piece[0]);

										$fee_percent = preg_match("/percent=\"([0-9]+)\"/", $shipcost_piece[1], $matches);
										$add_on = ($matches[1] / 100) * ceil($price);
										$mathString = $mathString + $add_on;
									}
									$mathString = str_replace('..', '.', $mathString);    // remove input mistakes from users using shipping formula's
									$mathString = str_replace(',', '.', $mathString);    // remove input mistakes from users using shipping formula's
									$mathString = preg_replace('[^0-9\+-\*\/\(\)]', '', $mathString);    // remove any non-numbers chars; exception for math operators
									$mathString = str_replace(array('\'', '"', ','), '', $mathString);

									if (!empty($mathString)) {
										$shipping_cost = $mathString;
										if ($taxable == "taxable") {
											foreach ($tax_rates as $k_inner => $w) {
												if ((isset($w['shipping'])) && ($w['shipping'] == "yes")) {
													$rate = (($w['rate'] + 100) / 100);
													if (is_numeric($shipping_cost)) {
														$shipping_cost = $shipping_cost * $rate;
														$shipping_cost = round($shipping_cost, 2);
														$shipping_cost = wc_format_localized_price($shipping_cost);
													}
												}
											}
										}
									}
									// Do we need to convert the shipping costswith the Aelia Currency Switcher
									if ((isset($project_config['AELIA'])) && (!empty($GLOBALS['woocommerce-aelia-currencyswitcher'])) && (get_option('add_aelia_support') == "yes")) {
										if (!array_key_exists('base_currency', $project_config)) {
											$from_currency = get_woocommerce_currency();
										} else {
											$from_currency = $project_config['base_currency'];
										}
										// Get Aelia currency conversion prices
										$shipping_cost = apply_filters('wc_aelia_cs_convert', $shipping_cost, $from_currency, $project_config['AELIA']);
									}
								}

								// Set shipping cost
								if (!empty($product_id)) {
									// Add product to cart
									if (isset($product_id)) {
										$quantity = 1;

										if (!empty($code_from_config)) {
											defined('WC_ABSPATH') || exit;

											// Load cart functions which are loaded only on the front-end.
											include_once WC_ABSPATH . 'includes/wc-cart-functions.php';
											include_once WC_ABSPATH . 'includes/class-wc-cart.php';

											wc_load_cart();

											WC()->cart->empty_cart();
											WC()->customer->set_shipping_country($zone_details['country']);

											if (isset($zone_details['region'])) {
												WC()->customer->set_shipping_state(wc_clean($zone_details['region']));
											}

											if (isset($zone_details['postal_code'])) {
												WC()->customer->set_shipping_postcode(wc_clean($zone_details['postal_code']));
											}

											if ((is_numeric($product_id)) && ($product_id > 0) && (!empty($product_id))) {
												WC()->cart->empty_cart();
												WC()->cart->add_to_cart($product_id, $quantity);
											}

											$shipping_cost = 0;
											// Read cart and get schipping costs
											foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
												$total_cost = WC()->cart->get_total();
												$shipping_cost = WC()->cart->get_shipping_total();
												$shipping_tax = round(WC()->cart->get_shipping_tax(), 2);
												$shipping_cost = ($shipping_cost + $shipping_tax);
												$shipping_cost = wc_format_localized_price($shipping_cost);
											}
											// Make sure to empty the cart again
											WC()->cart->empty_cart();
										}
									}
								}
							}

							// Check if we need to remove the local pick-up shipping method from the product feed
							if ($v->id == "local_pickup") {
								$remove_local_pickup = "no";
								$remove_local_pickup = get_option('local_pickup_shipping');

								if ($remove_local_pickup == "yes") {
									unset($zone_details);
									unset($shipping_cost);
								}
							}

							// Check if we need to remove the wholesale shipping method from the product feed
							if ($this->convpfm_is_plugin_active('woocommerce-wholesale-prices/woocommerce-wholesale-prices.bootstrap.php')) {
								// Check if we need to remove some wholesale shipping methods from the product feed
								$wwpp_settings_mapped_methods_for_wholesale_users_only = get_option("wwpp_settings_mapped_methods_for_wholesale_users_only");

								// Only remove the shipping method from feed when user explicitly configured so	
								if (isset($wwpp_settings_mapped_methods_for_wholesale_users_only) && ($wwpp_settings_mapped_methods_for_wholesale_users_only == "yes")) {
									$wwpp_wholesale_shipping_methods = get_option("wwpp_option_wholesale_role_shipping_zone_method_mapping");
									if (is_array($wwpp_wholesale_shipping_methods)) {
										foreach ($wwpp_wholesale_shipping_methods as $wwpp_k => $wwpp_v) {
											if ($wwpp_v['shipping_method'] == $v->instance_id) {
												unset($zone_details);
												unset($shipping_cost);
											}
										}
									}
								}
							}

							// Free shipping costs if minimum fee has been reached
							if ($v->id == "free_shipping") {
								$minimum_fee = $v->min_amount;

								if (!array_key_exists('base_currency', $project_config)) {
									$currency = get_woocommerce_currency();
								} else {
									$currency = $project_config['base_currency'];
								}

								if (isset($project_config['WCML'])) {
									$currency = $project_config['WCML'];
								}
								if (isset($project_config['AELIA'])) {
									$currency = $project_config['AELIA'];

									// convert minimum fee
									$minimum_fee = apply_filters('wc_aelia_cs_convert', $minimum_fee, $from_currency, $project_config['AELIA']);
								}

								// Set type to double otherwise the >= doesn't work
								settype($price, "double");
								settype($minimum_fee, "double");

								// Only Free Shipping when product price is over or equal to minimum order fee	
								if ($price >= $minimum_fee) {
									$shipping_cost = 0;
									$zone_details['price'] = trim($currency . " " . $shipping_cost);
									$zone_details['free'] = "yes";
								} else {
									// There are no free shipping requirements
									if ($v->requires == "") {
										$shipping_cost = 0;
										$zone_details['price'] = trim($currency . " " . $shipping_cost);
										$zone_details['free'] = "yes";
									} else {
										// No Free Shipping Allowed for this product
										//unset($zone_details);
										unset($zone_details['service']);
										unset($zone_details['price']);
										unset($shipping_cost);
									}
								}

								// User do not want to have free shipping in their feed	
								$remove_free_shipping = "no";
								$remove_free_shipping = get_option('remove_free_shipping');

								if ($remove_free_shipping == "yes") {
									unset($zone_details['service']);
									unset($zone_details['price']);
									unset($shipping_cost);
								}
							}

							if (isset($zone_details)) {
								$currency = get_woocommerce_currency();
								if (isset($project_config['WCML'])) {
									$currency = $project_config['WCML'];
								} else {
									if (isset($project_config['AELIA'])) {
										$currency = $project_config['AELIA'];
									} else {
										if (!array_key_exists('base_currency', $project_config)) {
											$currency = get_woocommerce_currency();
										} else {
											$currency = $project_config['base_currency'];
										}
									}
								}

								// For Heureka remove currency
								if ($project_config['fields'] == "heureka") {
									$currency = "";
								}

								if (isset($shipping_cost)) {
									if (strlen($shipping_cost) > 0) {
										if ($project_config['ship_suffix'] == "false") {
											$zone_details['price'] = trim($currency . " " . $shipping_cost);
										} else {
											$zone_details['price'] = trim($shipping_cost);
										}
									} else {
										if (isset($shipping_cost)) {
											$zone_details['price'] = trim($currency . " " . $shipping_cost);
										}
									}
								}
							}

							// This shipping zone has postal codes so multiply the zone details
							$nr_postals = count($postal_code);
							if ($nr_postals > 0) {
								for ($x = 0; $x <= count($postal_code);) {
									$zone_count++;
									if (!empty($postal_code[$x])) {
										$zone_details['postal_code'] = $postal_code[$x];
										$shipping_arr[$zone_count] = $zone_details;
									}
									$x++;
								}
							} else {
								if (isset($zone_details)) {
									$zone_count++;
									$shipping_arr[$zone_count] = $zone_details;
								}
							}
						}
					}
				}
			}
		}

		// Remove other shipping classes when free shipping is relevant		
		$free_check = "yes";

		if (in_array($free_check, array_column($shipping_arr, 'free'))) { // search value in the array
			foreach ($shipping_arr as $k => $v) {
				if (!in_array($free_check, $v)) {

					// User do not want to have free shipping in their feed 
					// Only remove the other shipping classes when free shipping is not being removed
					$remove_free_shipping = "no";
					$remove_free_shipping = get_option('remove_free_shipping');

					if ($remove_free_shipping == "no") {
						unset($shipping_arr[$k]);
					}
				}
			}
		}

		// Fix empty services and country
		foreach ($shipping_arr as $k => $v) {
			if (empty($v['service'])) {
				unset($shipping_arr[$k]);
			}
			if (empty($v['country'])) {
				unset($shipping_arr[$k]);
			}
		}
		return $shipping_arr;
	}

	/**
	 * Log queries, used for debugging errors
	 */
	public function convpfm_create_query_log($query, $filename)
	{
		$upload_dir = wp_upload_dir();

		$base = $upload_dir['basedir'];
		$path = $base . "/conversios-product-feed/logs";
		$file = $path . "/" . $filename . "." . "log";

		// External location for downloading the file   
		$external_base = $upload_dir['baseurl'];
		$external_path = $external_base . "/conversios-product-feed/logs";
		$external_file = $external_path . "/" . $filename . "." . "log";

		// Check if directory in uploads exists, if not create one      
		if (!file_exists($path)) {
			wp_mkdir_p($path);
		}

		// Log timestamp
		$today = "\n";
		$today .= date("F j, Y, g:i a");                 // March 10, 2001, 5:16 pm
		$today .= "\n";

		$fp = fopen($file, 'a+');
		fwrite($fp, $today);
		fwrite($fp, print_r($query, TRUE));
		fclose($fp);
	}

	/**
	 * Creates XML root and header for productfeed
	 */
	public function convpfm_create_xml_feed($products, $feed_config, $header)
	{
		$upload_dir = wp_upload_dir();
		$base = $upload_dir['basedir'];
		$path = $base . "/conversios-product-feed/" . $feed_config['fileformat'];
		$file = $path . "/" . sanitize_file_name($feed_config['filename']) . "_tmp." . $feed_config['fileformat'];

		// External location for downloading the file	
		$external_base = $upload_dir['baseurl'];
		$external_path = $external_base . "/conversios-product-feed/" . $feed_config['fileformat'];
		$external_file = $external_path . "/" . sanitize_file_name($feed_config['filename']) . "." . $feed_config['fileformat'];

		// Check if directory in uploads exists, if not create one	
		if (!file_exists($path)) {
			wp_mkdir_p($path);
		}

		// Check if file exists, if it does: delete it first so we can create a new updated one
		if (file_exists($file) && $header == "true" && $feed_config['nr_products_processed'] == 0) {
			unlink($file);
		}

		// Check if there is a channel feed class that we need to use
		if ($feed_config['fields'] != 'standard') {
			if (!class_exists('Convpfm_' . $feed_config['fields'])) {
				require plugin_dir_path(__FILE__) . '/channels/class-' . $feed_config['fields'] . '.php';
				$channel_class = "Convpfm_" . $feed_config['fields'];
				$channel_attributes = $channel_class::get_channel_attributes();
				update_option('convpfm_channel_attributes', $channel_attributes, 'yes');
			} else {
				$channel_attributes = get_option('convpfm_channel_attributes');
			}
		}

		// Some channels need their own feed config and XML namespace declarations (such as Google shopping)
		if ($feed_config['taxonomy'] == 'google_shopping') {
			$namespace = array('g' => 'http://base.google.com/ns/1.0');
			if (($header == "true") && ($feed_config['nr_products_processed'] == 0)) {
				$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><rss xmlns:g="http://base.google.com/ns/1.0"></rss>');
				$xml->addAttribute('version', '2.0');
				$xml->addChild('channel');

				
				if ($feed_config['fields'] == "facebook_drm") {
					$xml->channel->addChild('metadata');
					$xml->channel->metadata->addChild('ref_application_id', '160825592398259');
					$xml->channel->metadata->addChild('ref_asset_id', $feed_config['project_hash']);
				}
				// End Facebook ID's

				$xml->channel->addChild('title', htmlspecialchars($feed_config['projectname']));
				$xml->channel->addChild('link', home_url());
				$xml->channel->addChild('description', 'WooCommerce Product Feed PRO - This product feed is created with the Conversios.io - Product Feed Manager for WooCommerce. For all your support questions check out our FAQ on https://www.conversios.io/contact-us/ or e-mail to: support@conversios.io ');
				$xml->asXML($file);
			} else {
				$xml = simplexml_load_file($file, 'SimpleXMLElement', LIBXML_NOCDATA);
				$aantal = count($products);

				if (($xml !== FALSE) && ($aantal > 0)) {
					foreach ($products as $key => $value) {

						if (is_array($value)) {
							if (!empty($value)) {
								$product = $xml->channel->addChild('item');
								foreach ($value as $k => $v) {
									if ($k == "g:shipping") {
										$ship = explode("||", $v);
										foreach ($ship as $kk => $vv) {
											$sub_count = substr_count($vv, '##');
											$shipping = $product->addChild($k, '', htmlspecialchars($namespace['g']));
											$ship_split = explode(":", $vv);

											foreach ($ship_split as $ship_piece) {

												$piece_value = explode("##", $ship_piece);
												if (preg_match("/CONVPFM_COUNTRY/", $ship_piece)) {
													$shipping_country = $shipping->addChild('g:country', $piece_value[1], $namespace['g']);
												} elseif (preg_match("/CONVPFM_REGION/", $ship_piece)) {
													$shipping_region = $shipping->addChild('g:region', $piece_value[1], $namespace['g']);
												} elseif (preg_match("/CONVPFM_POSTAL_CODE/", $ship_piece)) {
													$shipping_price = $shipping->addChild('g:postal_code', $piece_value[1], $namespace['g']);
												} elseif (preg_match("/CONVPFM_SERVICE/", $ship_piece)) {
													$shipping_service = $shipping->addChild('g:service', $piece_value[1], $namespace['g']);
												} elseif (preg_match("/CONVPFM_PRICE/", $ship_piece)) {
													$shipping_price = $shipping->addChild('g:price', trim($piece_value[1]), $namespace['g']);
												} else {
													// DO NOT ADD ANYTHING
												}
											}
										}
										// Fix issue with additional images for Google Shopping
									} elseif (preg_match("/g:additional_image_link/i", $k)) {
										// First replace spaces from additional image URL
										$v = str_replace(" ", "", $v);
										$link = $product->addChild('g:additional_image_link', $v, $namespace['g']);
										//$product->$k = $v;
									} elseif (preg_match("/g:product_highlight/i", $k)) {
										$v = preg_replace('/&/', '&#38;', $v);
										$product_highlight = $product->addChild('g:product_highlight', $v, $namespace['g']);
									} elseif (preg_match("/g:included_destination/i", $k)) {
										$v = preg_replace('/&/', '&#38;', $v);
										$product_included_destination = $product->addChild('g:included_destination', $v, $namespace['g']);
									} elseif (preg_match("/g:shopping_ads_excluded_country/i", $k)) {
										$exclude_country = $product->addChild('g:shopping_ads_excluded_country', $v, $namespace['g']);
									} elseif (preg_match("/g:promotion_id/i", $k)) {
										$promotion_id = $product->addChild('g:promotion_id', $v, $namespace['g']);
									} elseif (preg_match("/g:product_detail/i", $k)) {
										if (!empty($v)) {
											$product_detail_split = explode("#", $v);
											$detail_complete = count($product_detail_split);
											if (($detail_complete == 2) && (!empty($product_detail_split[1]))) {
												$product_detail = $product->addChild('g:product_detail', '', $namespace['g']);
												$name = str_replace("_", " ", $product_detail_split[0]);
												$section_name = explode(":", $name);
												$section_name_start = ucfirst($section_name[0]);

												if (preg_match("/||/i", $product_detail_split[0])) {
													$product_detail_value_exp = explode("||", $product_detail_split[0]);
													$product_detail_name = $product_detail_value_exp[0];
													$product_detail_value = $product_detail_split[1];
													$section_name_start = str_replace($product_detail_value_exp[0], "", $section_name_start);
													$section_name_start = trim(str_replace("||", "", $section_name_start));
												} else {
													$product_detail_name = "General";
													$product_detail_value = $product_detail_split[0];
												}

												$section_name = $product_detail->addChild('g:section_name', $product_detail_name, $namespace['g']);
												$section_name_start = str_replace("Pa ", "", $section_name_start);
												$section_name_start = str_replace("pa ", "", $section_name_start);
												$section_name_start = str_replace("-", " ", $section_name_start);
												$section_name_start = str_replace("Custom attributes ", "", $section_name_start);
												$product_detail_name = $product_detail->addChild('g:attribute_name', ucfirst($section_name_start), $namespace['g']);
												$product_detail_value = $product_detail->addChild('g:attribute_value', $product_detail_value, $namespace['g']);
											}
										}
									} elseif (preg_match("/g:consumer_notice/i", $k)) {
										if (!empty($v)) {
											$notice = $product->addChild("consumer_notice", '', $namespace['g']);
											if (strpos($v, "prop 65") !== false) {
												$notice_type = $notice->addChild('g:notice_type', "prop 65", $namespace['g']);
												$v = trim(str_replace("prop 65", "", $v));
											} elseif (strpos($v, "safety warning") !== false) {
												$notice_type = $notice->addChild('g:notice_type', "safety warning", $namespace['g']);
												$v = trim(str_replace("safety warning", "", $v));
											} elseif (strpos($v, "legal disclaimer") !== false) {
												$notice_type = $notice->addChild('g:notice_type', "legal disclaimer", $namespace['g']);
												$v = trim(str_replace("legal disclaimer", "", $v));
											} else {
												// No notice type set so we assume it is a safety warning
												$notice_type = $notice->addChild('g:notice_type', "safety warning", $namespace['g']);
											}
											$notice_type = $notice->addChild('g:notice_message', $v, $namespace['g']);
										}
									} elseif ($k == "g:installment") {
										if (!empty($v)) {
											$installment_split = explode(":", $v);
											$installment = $product->addChild($k, '', $namespace['g']);
											$installment_months = $installment->addChild('g:months', $installment_split[0], $namespace['g']);
											$installment_amount = $installment->addChild('g:amount', $installment_split[1], $namespace['g']);
										}
									} elseif ($k == "g:color" || $k == "g:size" || $k == "g:material") {
										if (!empty($v)) {
											$attr_split = explode(",", $v);
											$nr_attr = count($attr_split) - 1;
											$attr_value = "";

											for ($x = 0; $x <= $nr_attr; $x++) {
												$attr_value .= trim($attr_split[$x]) . "/";
											}
											$attr_value = rtrim($attr_value, "/");
											$product->$k = rawurldecode($attr_value);
										}
									} else {
										$product->$k = rawurldecode($v);
									}
								}
							}
						}
					}
				}

				if (is_object($xml)) {
					// Revert to DOM to preserve XML whitespaces and line-breaks
					$dom = dom_import_simplexml($xml)->ownerDocument;
					$dom->formatOutput = true;
					$dom->preserveWhiteSpace = false;
					$dom->loadXML($dom->saveXML());
					$dom->save($file);
					unset($dom);
				}
				unset($products);
			}
			unset($xml);
		} else {
			if (($header == "true") && ($feed_config['nr_products_processed'] == 0) || !file_exists($file)) {

				if ($feed_config['name'] == "Yandex") {
					$main_currency = get_woocommerce_currency();

					$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><yml_catalog></yml_catalog>');
					$xml->addAttribute('date', date('Y-m-d H:i'));
					$shop = $xml->addChild('shop');
					$shop->addChild('name', htmlspecialchars($feed_config['projectname']));
					$shop->addChild('company', get_bloginfo());
					$shop->addChild('url', home_url());
					//$shop->addChild('platform', 'WooCommerce');
					$currencies = $shop->addChild('currencies');
					$currency = $currencies->addChild('currency');
					$currency->addAttribute('id', $main_currency);
					$currency->addAttribute('rate', '1');

					// Switch to configured WPML language
					if (isset($feed_config['WPML'])) {
						if (function_exists('icl_object_id')) {
							if (!class_exists('Polylang')) {
								global $sitepress;
								$original_lang = ICL_LANGUAGE_CODE; // Save the current language
								$new_lang = $feed_config['WPML']; // The language in which you want to get the terms
								$sitepress->switch_lang($new_lang); // Switch to new language
							}
						}
					}

					$args = array(
						'taxonomy'   => "product_cat",
					);
					$product_categories = get_terms('product_cat', $args);

					$count = count($product_categories);
					if ($count > 0) {
						$categories = $shop->addChild('categories');

						foreach ($product_categories as $product_category) {
							$category = $categories->addChild('category', htmlspecialchars($product_category->name));
							$category->addAttribute('id', $product_category->term_id);
							if ($product_category->parent > 0) {
								$category->addAttribute('parentId', $product_category->parent);
							}
						}
					}

					$shop->addChild('agency', 'conversios.io');
					$shop->addChild('email', 'support@conversios.io');
					$xml->asXML($file);
				} elseif ($feed_config['name'] == "Zbozi.cz") {
					$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><SHOP></SHOP>');
					$xml->addAttribute('xmlns', 'http://www.zbozi.cz/ns/offer/1.0');
					$xml->asXML($file);
				} elseif ($feed_config['name'] == "Bestprice") {
					$xml = new SimpleXMLElement('<?xml version="1.0" encoding="ISO-8859-7"?><store></store>');
					$xml->addChild('date', date('Y-m-d H:i:s'));
					$xml->asXML($file);
				} elseif ($feed_config['name'] == "Shopflix") {
					$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><MPITEMS></MPITEMS>');
					$xml->addChild('created_at', date('Y-m-d H:i:s'));
					$xml->asXML($file);
				} elseif ($feed_config['name'] == "Glami.gr") {
					$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><SHOP></SHOP>');
					$xml->asXML($file);
				} elseif ($feed_config['name'] == "Glami.sk") {
					$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><SHOP></SHOP>');
					$xml->asXML($file);
				} elseif ($feed_config['name'] == "Glami.cz") {
					$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><SHOP></SHOP>');
					$xml->asXML($file);
				} elseif ($feed_config['name'] == "Vivino") {
					$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><vivino-product-list></vivino-product-list>');
					$xml->asXML($file);
				} elseif ($feed_config['name'] == "Pricecheck.co.za") {
					$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><Offers></Offers>');
					$xml->asXML($file);
				} elseif ($feed_config['name'] == "Pinterest RSS Board") {
					$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><rss></rss>');
					$xml->addAttribute('xmlns:content', 'http://purl.org/rss/1.0/modules/content/');
					$xml->addAttribute('xmlns:wfw', 'http://wellformedweb.org/CommentAPI/');
					$xml->addAttribute('xmlns:dc', 'http://purl.org/dc/elements/1.1/');
					$xml->addAttribute('xmlns:sy', 'http://purl.org/rss/1.0/modules/syndication/');
					$xml->addAttribute('xmlns:slash', 'http://purl.org/rss/1.0/modules/slash/');
					$xml->addAttribute('version', '2.0');
					$xml->asXML($file);
				} elseif ($feed_config['name'] == "Heureka.cz") {
					$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><SHOP></SHOP>');
					$xml->addAttribute('xmlns', 'http://www.heureka.cz/ns/offer/1.0');
					$xml->asXML($file);
				} elseif ($feed_config['name'] == "Mall.sk") {
					$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8" standalone="yes"?><ITEMS></ITEMS>');
					$xml->asXML($file);
				} elseif ($feed_config['name'] == "Mall.sk availability") {
					$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8" standalone="yes"?><AVAILABILITIES></AVAILABILITIES>');
					$xml->asXML($file);
				} elseif ($feed_config['name'] == "Heureka.sk") {
					$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><SHOP></SHOP>');
					$xml->addAttribute('xmlns', 'http://www.heureka.sk/ns/offer/1.0');
					$xml->asXML($file);
				} elseif ($feed_config['name'] == "Zap.co.il") {
					$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><STORE></STORE>');
					$xml->addChild('datetime', date('Y-m-d H:i:s'));
					$xml->addChild('title', htmlspecialchars($feed_config['projectname']));
					$xml->addChild('link', home_url());
					$xml->addChild('description', 'WooCommerce Product Feed PRO - This product feed is created with the free Advanced Product Feed PRO for WooCommerce plugin from Conversios.io. For all your support questions check out our FAQ on https://www.conversios.io/ or e-mail to: support@conversios.io ');
					$xml->addChild('agency', 'Conversios.io');
					$xml->addChild('email', 'support@conversios.io');
					$xml->asXML($file);
				} elseif ($feed_config['name'] == "Salidzini.lv") {
					$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><root></root>');
					$xml->addChild('datetime', date('Y-m-d H:i:s'));
					$xml->addChild('title', htmlspecialchars($feed_config['projectname']));
					$xml->addChild('link', home_url());
					$xml->addChild('description', 'WooCommerce Product Feed PRO - This product feed is created with the free Advanced Product Feed PRO for WooCommerce plugin from Conversios.io. For all your support questions check out our FAQ on https://www.conversios.io/ or e-mail to: support@conversios.io ');
					$xml->addChild('agency', 'Conversios.io');
					$xml->addChild('email', 'support@conversios.io');
					$xml->asXML($file);
				} elseif ($feed_config['name'] == "Google Product Review") {
					$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><feed></feed>');
					$xml->addAttribute("xmlns:xmlns:vc", 'http://www.w3.org/2007/XMLSchema-versioning');
					$xml->addAttribute("xmlns:xmlns:xsi", 'http://www.w3.org/2001/XMLSchema-instance');
					$xml->addAttribute("xsi:xsi:noNamespaceSchemaLocation", 'http://www.google.com/shopping/reviews/schema/product/2.3/product_reviews.xsd');
					$xml->addChild('version', '2.3');
					$aggregator = $xml->addChild('aggregator');
					$aggregator->addChild('name', htmlspecialchars($feed_config['projectname']));
					$publisher = $xml->addChild('publisher');
					$publisher->addChild('name', get_bloginfo('name'));
					$publisher->addChild('favicon', get_site_icon_url());
					$xml->asXML($file);
				} elseif ($feed_config['name'] == "Fruugo.nl") {
					$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><Products></Products>');
					$xml->asXML($file);
				} elseif ($feed_config['name'] == "Fruugo.co.uk") {
					$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><Products></Products>');
					$xml->asXML($file);
				} else {
					$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><products></products>');

					if (!preg_match("/fruugo|pricerunner/i", $feed_config['fields'])) {
						$xml->addAttribute('version', '1.0');
						$xml->addAttribute('standalone', 'yes');
					}

					if ($feed_config['name'] == "Skroutz") {
						$xml->addChild('created_at', date('Y-m-d H:i'));
					}
					$xml->asXML($file);
				}
			} else {
				$xml = simplexml_load_file($file);
				$aantal = count($products);

				if ($aantal > 0) {

					// For Yandex template
					if (($feed_config['name'] == "Yandex") && ($feed_config['nr_products_processed'] == 0)) {
						$shop = $xml->shop->addChild('offers');
					}

					// For Bestprice template
					if (($feed_config['name'] == "Bestprice") && ($feed_config['nr_products_processed'] == 0)) {
						$productz = $xml->addChild('products');
					}

					// For Shopflix template
					if (($feed_config['name'] == "Shopflix") && ($feed_config['nr_products_processed'] == 0)) {
						$productz = $xml->addChild('products');
					}

					// For ZAP template
					if (($feed_config['name'] == "Zap.co.il") && ($feed_config['nr_products_processed'] == 0)) {
						$productz = $xml->addChild('PRODUCTS');
					}

					// For Pinterest RSS Board template
					if (($feed_config['name'] == "Pinterest RSS Board") && (empty($xml->channel))) {
						$productz = $xml->addChild('channel');
						$productz = $xml->channel->addChild('title', get_bloginfo('name'));
						$productz = $xml->channel->addChild('description', htmlspecialchars($feed_config['projectname']));
						$productz = $xml->channel->addChild('lastBuildDate', date('Y-m-d H:i:s'));
						$productz = $xml->channel->addChild('generator', 'Conversios.io - Product Feed Manager for WooCommerce');
					}

					// For Google Product review template
					if (($feed_config['name'] == "Google Product Review") && (empty($xml->channel))) {

						if (!is_bool($xml)) {
							$product = $xml->addChild('reviews');

							foreach ($products as $key => $value) {

								$expl = "||";

								if (array_key_exists('reviews', $value)) {
									$review_data = explode("||", $value['reviews']);
									foreach ($review_data as $rk => $rv) {

										$review_comp = explode(":::", $rv);
										$nr_reviews = count($review_comp);

										if ($nr_reviews > 1) {
											$productz = $xml->reviews->addChild('review');

											foreach ($review_comp as $rck => $rcv) {
												$nodes = explode("##", $rcv);
												$nodes = str_replace("::", "", $nodes);

												if ($nodes[0] == "REVIEW_RATINGS") {
													// Do nothing
												} elseif ($nodes[0] == "REVIEW_URL") {
													$rev_url = $productz->addChild(strtolower($nodes[0]), htmlspecialchars($nodes[1]));
													$rev_url->addAttribute('type', 'singleton');
												} elseif (($nodes[0] == "REVIEWER_NAME") || ($nodes[0] == "REVIEWER_ID")) {
													if (isset($productz->reviewer)) {
														if ($nodes[0] == "REVIEWER_NAME") {
															$name = $nodes[1];
															if (empty($name)) {
																$reviewer->addChild('name', 'Anonymous');
																$reviewer->name->addAttribute('is_anonymous', 'true');
															} else {
																$reviewer->addChild('name', $name);
															}
														} else {
															if (is_numeric($nodes[1])) {
																$reviewer->addChild('reviewer_id', $nodes[1]);
															}
														}
													} else {
														$reviewer = $productz->addChild('reviewer');
														if ($nodes[0] == "REVIEWER_NAME") {
															$name = $nodes[1];
															if (empty($name)) {
																$reviewer->addChild('name', 'Anonymous');
																$reviewer->name->addAttribute('is_anonymous', 'true');
															} else {
																$reviewer->addChild('name', htmlspecialchars($name));
																//$reviewer->addChild('name',$name);
															}
														} else {
															if (is_numeric($nodes[1])) {
																$reviewer->addChild('reviewer_id', $nodes[1]);
															}
														}
													}
												} else {
													if (isset($nodes[1])) {
														$content = html_entity_decode($nodes[1]);
														$content = htmlspecialchars($content);
														$rev = $productz->addChild(strtolower($nodes[0]), $content);
													}
												}
											}

											foreach ($review_comp as $rck => $rcv) {
												$nodes = explode("##", $rcv);
												$nodes = str_replace("::", "", $nodes);

												if ($nodes[0] == "REVIEW_RATINGS") {
													$rev = $productz->addChild('ratings');
													$over = $productz->ratings->addChild('overall', $nodes[1]);
													$over->addAttribute('min', '1');
													$over->addAttribute('max', '5');
												}
											}

											$yo = $productz->addChild('products');
											$po = $yo->addChild('product');

											$identifiers = array("gtin", "mpn", "sku", "brand");

											// Start determining order of product_ids in the Google review feed	
											$proper_order = array("product_name", "gtin", "mpn", "sku", "brand", "product_url", "review_url", "reviews");
											$order_sorted = array();
											foreach ($proper_order as &$order_value) {
												if (isset($value[$order_value])) {
													$order_sorted[$order_value] = $value[$order_value];
												}
											}
											// End										

											foreach ($order_sorted as $k => $v) {
												if (($k != "product_name") && ($k != "product_url")) {
													if (!in_array($k, $identifiers)) {
														if (($k != "reviews") && ($k != "review_url")) {
															if ($k != "product_url") {
																$v = str_replace("&", "and", $v);
															}
															$poa = $po->addChild($k, htmlspecialchars($v));
														}
													} else {
														if (isset($po->product_ids)) {
															if ($k == "gtin") {
																$poig = $poi->addChild('gtins');
																$poig->$k = $v;
															} elseif ($k == "mpn") {
																$poim = $poi->addChild('mpns');
																$poim->$k = $v;
															} elseif ($k == "sku") {
																$poix = $poi->addChild('skus');
																$poix->$k = $v;
															} elseif ($k == "brand") {
																$poib = $poi->addChild('brands');
																$poib->$k = $v;
															} else {
																// Do nothing
															}
														} else {
															$poi = $po->addChild('product_ids');
															if ($k == "gtin") {
																$poig = $poi->addChild('gtins');
																$poig->$k = $v;
															} elseif ($k == "mpn") {
																$poim = $poi->addChild('mpns');
																$poim->$k = $v;
															} elseif ($k == "sku") {
																$poix = $poi->addChild('skus');
																$poix->$k = $v;
															} elseif ($k == "brand") {
																$poib = $poi->addChild('brands');
																$poib->$k = $v;
															} else {
																// Do nothing
															}
														}
													}
												}
											}

											// foreach for product name and product url as order seems to mather to Google
											foreach ($value as $k => $v) {
												if (($k == "product_name") || ($k == "product_url")) {
													if (!in_array($k, $identifiers)) {
														if (($k != "reviews") && ($k != "review_url")) {
															if ($k != "product_url") {
																$v = str_replace("&", "and", $v);
															}
															$poa = $po->addChild($k, htmlspecialchars($v));
														}
													} else {
														if (isset($po->product_ids)) {
															if ($k == "gtin") {
																$poig = $poi->addChild('gtins');
																$poig->$k = $v;
															} elseif ($k == "mpn") {
																$poim = $poi->addChild('mpns');
																$poim->$k = $v;
															} elseif ($k == "sku") {
																$poix = $poi->addChild('skus');
																$poix->$k = $v;
															} elseif ($k == "brand") {
																$poib = $poi->addChild('brands');
																$poib->$k = $v;
															} else {
																// Do nothing
															}
														} else {
															$poi = $po->addChild('product_ids');
															if ($k == "gtin") {
																$poig = $poi->addChild('gtins');
																$poig->$k = $v;
															} elseif ($k == "mpn") {
																$poim = $poi->addChild('mpns');
																$poim->$k = $v;
															} elseif ($k == "sku") {
																$poix = $poi->addChild('skus');
																$poix->$k = $v;
															} elseif ($k == "brand") {
																$poib = $poi->addChild('brands');
																$poib->$k = $v;
															} else {
																// Do nothing
															}
														}
													}
												}
											}
										}
									}
								}
							}
						}
					}

					foreach ($products as $key => $value) {
						if ((is_array($value)) && (!empty($value))) {
							if ($feed_config['name'] == "Yandex") {
								$product = $xml->shop->offers->addChild('offer');
							} elseif ($feed_config['name'] == "Heureka.cz" || $feed_config['name'] == "Heureka.sk" || $feed_config['name'] == "Zbozi.cz" || $feed_config['name'] == "Glami.gr" || $feed_config['name'] == "Glami.sk" || $feed_config['name'] == "Glami.cz") {
								$product = $xml->addChild('SHOPITEM');
							} elseif ($feed_config['name'] == "Zap.co.il") {
								$product = $xml->PRODUCTS->addChild('PRODUCT');
							} elseif ($feed_config['name'] == "Bestprice") {
								$product = $xml->products->addChild('product');
							} elseif ($feed_config['name'] == "Shopflix") {
								$product = $xml->products->addChild('product');
							} elseif ($feed_config['name'] == "Salidzini.lv") {
								$product = $xml->addChild('item');
							} elseif ($feed_config['name'] == "Mall.sk") {
								$product = $xml->addChild('ITEM');
							} elseif ($feed_config['name'] == "Mall.sk availability") {
								$product = $xml->addChild('AVAILABILITY');
							} elseif ($feed_config['name'] == "Trovaprezzi.it") {
								$product = $xml->addChild('Offer');
							} elseif ($feed_config['name'] == "Pricecheck.co.za") {
								$product = $xml->addChild('Offer');
							} elseif ($feed_config['name'] == "Pinterest RSS Board") {
								$product = $xml->channel->addChild('item');
							} elseif ($feed_config['name'] == "Fruugo.nl") {
								$product = $xml->addChild('Product');
							} elseif ($feed_config['name'] == "Fruugo.co.uk") {
								$product = $xml->addChild('Product');
							} elseif ($feed_config['name'] == "Google Product Review") {
							} else {
								if (count($value) > 0) {
									if (is_object($xml)) {
										$product = $xml->addChild('product');
									}
								}
							}

							foreach ($value as $k => $v) {
								$v = trim($v);
								$k = trim($k);

								if (($k == "id") && ($feed_config['name'] == "Yandex")) {
									if (isset($product)) {
										if (!empty($v)) {
											$product->addAttribute('id', trim($v));
										}
									}
								}

								if ((preg_match("/picture/i", $k)) && ($feed_config['name'] == "Yandex")) {
									if (isset($product)) {
										if (!empty($v)) {
											$additional_picture_link = $product->addChild('picture', $v);
										}
									}
								}

								if (($k == "item_group_id") && ($feed_config['name'] == "Yandex")) {
									$product->addAttribute('group_id', trim($v));
								}

								if (($k == "color") && ($feed_config['name'] == "Skroutz")) {
									if (preg_match('/,/', $v)) {
										$cls = explode(",", $v);

										if (is_array($cls)) {
											foreach ($cls as $kkx => $vvx) {
												if (!empty($vvx)) {
													$additional_color = $product->addChild('color', trim($vvx));
												}
											}
										}
									} elseif (preg_match("/\\s/", $v)) {
										//$clp = explode(" ",$v);

										//if (is_array ( $clp ) ) {
										//        foreach ($clp as $kkx => $vvx){
										//		if(!empty($vvx)){
										if (!is_null($product)) {
											$additional_color = $product->addChild('color', trim($v));
											//$additional_color = $product->addChild('color',trim($vvx));
										}
										//               }
										//       }
										//}
									}
								}

								if (($k == "available") && ($feed_config['name'] == "Yandex")) {
									if ($v == "in stock") {
										$v = "true";
									} else {
										$v = "false";
									}
									$product->addAttribute('available', $v);
								}

								/**
								 * Check if a product resides in multiple categories
								 * id so, create multiple category child nodes
								 */
								if ($k == "categories") {
									if ((!isset($product->categories)) && (isset($product))) {
										$category = $product->addChild('categories');
										$cat = explode("||", $v);

										if (is_array($cat)) {
											foreach ($cat as $kk => $vv) {
												$child = "category";
												$category->addChild("$child", htmlspecialchars($vv));
											}
										}
									}
								} elseif (preg_match('/^additionalimage/', $k)) {
									$additional_image_link = $product->addChild('additionalimage', $v);
								} elseif (preg_match('/^additional_imageurl/', $k)) {
									$additional_image_link = $product->addChild('additional_imageurl', $v);
								} elseif ($k == "shipping") {
									$expl = "||";
									if (strpos($v, $expl)) {
										$ship = explode("||", $v);
										foreach ($ship as $kk => $vv) {
											$ship_zone = $product->addChild('shipping');
											$ship_split = explode(":", $vv);

											foreach ($ship_split as $ship_piece) {
												$piece_value = explode("##", $ship_piece);
												if (preg_match("/CONVPFM_COUNTRY/", $ship_piece)) {
													$shipping_country = $ship_zone->addChild('country', htmlspecialchars($piece_value[1]));
												} elseif (preg_match("/CONVPFM_REGION/", $ship_piece)) {
													$shipping_region = $ship_zone->addChild('region', htmlspecialchars($piece_value[1]));
												} elseif (preg_match("/CONVPFM_POSTAL_CODE/", $ship_piece)) {
													$postal_code = $ship_zone->addChild('postal_code', htmlspecialchars($piece_value[1]));
												} elseif (preg_match("/CONVPFM_SERVICE/", $ship_piece)) {
													$shipping_service = $ship_zone->addChild('service', htmlspecialchars($piece_value[1]));
												} elseif (preg_match("/CONVPFM_PRICE/", $ship_piece)) {
													$shipping_price = $ship_zone->addChild('price', htmlspecialchars($piece_value[1]));
												} else {
													// DO NOT ADD ANYTHING
												}
											}
										}
									} else {
										$child = "shipping";
										$product->$k = $v;
									}
								} elseif ($k == "category_link") {
									$category = $product->addChild('category_links');
									$cat_links = explode("||", $v);
									if (is_array($cat_links)) {
										foreach ($cat_links as $kk => $vv) {
											$child = "category_link";
											$category->addChild("$child", htmlspecialchars($vv));
										}
									}
								} elseif ($k == "categoryId") {

									if ($feed_config['name'] == "Yandex") {
										$args = array(
											'taxonomy'   => "product_cat",
										);

										//$category = $product->addChild('categories');
										$product_categories = get_terms('product_cat', $args);
										$count = count($product_categories);
										$cat = explode("||", $v);

										if (is_array($cat)) {
											foreach ($cat as $kk => $vv) {
												if ($count > 0) {
													foreach ($product_categories as $product_category) {
														if ($vv == $product_category->name) {
															$product->addChild("$k", htmlspecialchars($product_category->term_id));
														}
													}
												}
											}
										}
									}
								} elseif (($k == "id" || $k == "item_group_id" || $k == "available") && ($feed_config['name'] == "Yandex")) {
									// Do not add these nodes to Yandex product feeds
								} elseif ($k == "CATEGORYTEXT") {
									$v = str_replace("||", " | ", $v);
									$product->addChild("$k");
									$product->$k = $v;
								} else {
									if (($feed_config['fields'] != 'standard') && ($feed_config['fields'] != "customfeed")) {
										$k = $this->get_alternative_key($channel_attributes, $k);
									}

									if (!empty($k)) {
										/**
										 * Some Zbozi, Mall and Heureka attributes need some extra XML nodes
										 */
										$zbozi_nodes = "PARAM_";

										if ((($feed_config['name'] == "Zbozi.cz") || ($feed_config['name'] == "Mall.sk") || ($feed_config['name'] == "Glami.gr") || ($feed_config['name'] == "Glami.sk") || ($feed_config['name'] == "Glami.cz") || ($feed_config['name'] == "Heureka.cz") || ($feed_config['name'] == "Heureka.sk")) && (preg_match("/$zbozi_nodes/i", $k))) {
											$pieces = explode("_", $k, 2);
											$productp = $product->addChild('PARAM');
											if ($feed_config['name'] == "Mall.sk") {
												$productp->addChild("NAME", $pieces[1]);
												$productp->addChild("VALUE", $v);
											} else {
												$productp->addChild("PARAM_NAME", $pieces[1]);
												$productp->addChild("VAL", $v);
											}
										} elseif (($feed_config['name'] == "Mall.sk") && ($k == "VARIABLE_PARAMS")) {
											if (isset($value['ITEMGROUP_ID'])) {
												$productvp = $product->addChild('VARIABLE_PARAMS');
												$product_variations = new WC_Product_Variation($value['ID']);
												if (is_object($product_variations)) {
													$variations = $product_variations->get_variation_attributes(false);
													foreach ($variations as $k => $v) {
														$k = str_replace("pa_", "", $k);
														$productvp->addChild("PARAM", $k);
													}
												}
											}
										} elseif (($feed_config['name'] == "Mall.sk") && ($k == "MEDIA")) {
											$productp = $product->addChild('MEDIA');
											$productp->addChild("URL", $v);
											$productp->addChild("MAIN", "true");
										} elseif (($feed_config['name'] == "Mall.sk") && ($k == "MEDIA_1")) {
											$productp = $product->addChild('MEDIA');
											$productp->addChild("URL", $v);
											$productp->addChild("MAIN", "false");
										} elseif (($feed_config['name'] == "Mall.sk") && ($k == "MEDIA_2")) {
											$productp = $product->addChild('MEDIA');
											$productp->addChild("URL", $v);
											$productp->addChild("MAIN", "false");
										} elseif (($feed_config['name'] == "Mall.sk") && ($k == "MEDIA_3")) {
											$productp = $product->addChild('MEDIA');
											$productp->addChild("URL", $v);
											$productp->addChild("MAIN", "false");
										} elseif (($feed_config['name'] == "Mall.sk") && ($k == "MEDIA_4")) {
											$productp = $product->addChild('MEDIA');
											$productp->addChild("URL", $v);
											$productp->addChild("MAIN", "false");
										} elseif (($feed_config['name'] == "Mall.sk") && ($k == "MEDIA_5")) {
											$productp = $product->addChild('MEDIA');
											$productp->addChild("URL", $v);
											$productp->addChild("MAIN", "false");
										} elseif ((($feed_config['name'] == "Zbozi.cz") || ($feed_config['name'] == "Heureka.cz")) && ($k == "DELIVERY")) {
											$delivery = $product->addChild('DELIVERY');
											$delivery_split = explode("##", $v);
											$nr_split = count($delivery_split);

											$zbozi_delivery_id = array(
												0 => "CESKA_POSTA_BALIKOVNA",
												1 => "CESKA_POSTA_NA_POSTU",
												2 => "DPD_PICKUP",
												3 => "GEIS_POINT",
												4 => "GLS_PARCELSHOP",
												5 => "PPL_PARCELSHOP",
												6 => "TOPTRANS_DEPO",
												7 => "WEDO_ULOZENKA",
												8 => "ZASILKOVNA",
												9 => "VLASTNI_VYDEJNI_MISTA",
												10 => "CESKA_POSTA",
												11 => "DB_SCHENKER",
												12 => "DPD",
												13 => "DHL",
												14 => "DSV",
												15 => "FOFR",
												16 => "GEBRUDER_WEISS",
												17 => "GEIS",
												18 => "GLS",
												19 => "HDS",
												20 => "WEDO_HOME",
												21 => "MESSENGER",
												22 => "PPL",
												23 => "TNT",
												24 => "TOPTRANS",
												25 => "UPS",
												26 => "FEDEX",
												27 => "RABEN_LOGISTICS",
												28 => "RHENUS",
												29 => "ZASILKOVNA_NA_ADRESU",
												30 => "VLASTNI_PREPRAVA"
											);

											if ($nr_split == 7) {
												$delivery_id_split = explode(" ", $delivery_split[2]);
												$delivery_price_split = explode("||", $delivery_split[3]);
												$delivery_id = $delivery->addChild('DELIVERY_ID', htmlspecialchars($delivery_id_split[0]));

												$delivery_price_split[0] = str_replace("EUR", "", $delivery_price_split[0]);
												$delivery_price_split[0] = str_replace("CZK", "", $delivery_price_split[0]);

												$delivery_price = $delivery->addChild('DELIVERY_PRICE', trim(htmlspecialchars($delivery_price_split[0])));
												$delivery_price_cod = $delivery->addChild('DELIVERY_PRICE_COD', trim(htmlspecialchars($delivery_split[6])));
											} else {
												if ($nr_split > 1) {
													$zbozi_split = explode(" ", $delivery_split[2]);
													foreach ($zbozi_split as $zbozi_id) {
														if (in_array($zbozi_id, $zbozi_delivery_id)) {
															$delivery_split[2] = $zbozi_id;
														}
													}

													$delivery_split[3] = str_replace("EUR", "", $delivery_split[3]);
													$delivery_split[3] = str_replace("CZK", "", $delivery_split[3]);

													$delivery_id = $delivery->addChild('DELIVERY_ID', htmlspecialchars($delivery_split[2]));
													$del_price_split = explode(" ", trim($delivery_split[3]));
													$delivery_id = $delivery->addChild('DELIVERY_PRICE', trim(htmlspecialchars($delivery_split[3])));
												}
											}
										} elseif (($feed_config['name'] == "Yandex") && (preg_match("/picture/i", $k))) {
											// do nothing, was added already
										} elseif (($feed_config['name'] == "Yandex") && (preg_match("/$zbozi_nodes/i", $k))) {
											$pieces = explode("_", $k);
											$p = "param";
											$productp = $product->addChild($p, $v);
											$productp->addAttribute('name', $pieces[1]);
										} elseif ($feed_config['name'] == "Google Product Review") {
										} elseif ($feed_config['name'] == "Vivino") {
											$extra_arr = array('ean', 'jan', 'upc', 'producer', 'wine-name', 'appellation', 'vintage', 'country', 'color', 'image', 'description', 'alcohol', 'producer-address', 'importer-address', 'varietal', 'ageing', 'closure', 'winemaker', 'production-size', 'residual-sugar', 'acidity', 'ph', 'contains-milk-allergens', 'contains-egg-allergens', 'non-alcoholic');
											$unit_arr = array('production-size', 'residual-sugar', 'acidity');

											if (in_array($k, $extra_arr)) {
												if (!isset($product->extras)) {
													$productp = $product->addChild('extras');
												}

												// Add units to it	
												if (in_array($k, $unit_arr)) {
													$productk = $productp->addChild($k, $v);
													if ($k == "acidity") {
														$productk->addAttribute('unit', 'g/l');
													}
													if ($k == "production-size") {
														$productk->addAttribute('unit', 'bottles');
													}
													if ($k == "residual-sugar") {
														$productk->addAttribute('unit', 'g/l');
													}
												} else {
													$productp->$k = $v;
												}
											} else {
												$product->addChild("$k");
												$product->$k = $v;
											}
										} elseif ($feed_config['name'] == "Fruugo.nl") {
											$desc_arr = array('Language', 'Title', 'Description');
											$price_arr = array('Currency', 'NormalPriceWithoutVAT', 'NormalPriceWithVAT', 'VATRate');

											if (in_array($k, $desc_arr)) {
												if (!isset($product->Description)) {
													$productd = $product->addChild('Description');
												}
												$productd->$k = $v;
											} elseif (in_array($k, $price_arr)) {
												if (!isset($product->Price)) {
													$productp = $product->addChild('Price');
												}
												$productp->$k = $v;
											} else {
												$product->addChild("$k");
												$product->$k = $v;
											}
										} elseif ($feed_config['name'] == "Fruugo.co.uk") {
											$desc_arr = array('Language', 'Title', 'Description');
											$price_arr = array('Currency', 'NormalPriceWithoutVAT', 'NormalPriceWithVAT', 'VATRate');

											if (in_array($k, $desc_arr)) {
												if (!isset($product->Description)) {
													$productd = $product->addChild('Description');
												}
												$productd->$k = $v;
											} elseif (in_array($k, $price_arr)) {
												if (!isset($product->Price)) {
													$productp = $product->addChild('Price');
												}
												$productp->$k = $v;
											} else {
												$product->addChild("$k");
												$product->$k = $v;
											}
										} else {
											if (is_object($product)) {
												if (!isset($product->$k)) {
													$product->addChild("$k");
													$product->$k = $v;
												}
											}
										}
									}
								}
							}
						}
					}

					if (is_object($xml)) {
						//$xml = html_entity_decode($xml->asXML());
						$xml->asXML($file);
					}
					unset($product);
				}
				unset($products);
			}
			unset($xml);
		}
	}

	/**
	 * Actual creation of CSV/TXT file
	 * Returns relative and absolute file path
	 */
	public function convpfm_create_csvtxt_feed($products, $feed_config, $header)
	{

		$upload_dir = wp_upload_dir();
		$base = $upload_dir['basedir'];
		$path = $base . "/conversios-product-feed/" . $feed_config['fileformat'];
		$file = $path . "/" . sanitize_file_name($feed_config['filename']) . "_tmp." . $feed_config['fileformat'];

		// External location for downloading the file	
		$external_base = $upload_dir['baseurl'];
		$external_path = $external_base . "/conversios-product-feed/" . $feed_config['fileformat'];
		$external_file = $external_path . "/" . sanitize_file_name($feed_config['filename']) . "." . $feed_config['fileformat'];

		// Check if directory in uploads exists, if not create one	
		if (!file_exists($path)) {
			wp_mkdir_p($path);
		}

		// Check if file exists, if it does: delete it first so we can create a new updated one
		if ((file_exists($file)) && ($feed_config['nr_products_processed'] == 0) && ($header == "true")) {
			@unlink($file);
		}

		// Check if there is a channel feed class that we need to use
		if (empty($feed_config['fields'])) {
			$feed_config['fields'] = "google_shopping";
		}

		if ($feed_config['fields'] != 'standard') {
			if (!class_exists('Convpfm_' . $feed_config['fields'])) {
				require plugin_dir_path(__FILE__) . 'channels/class-' . $feed_config['fields'] . '.php';
				$channel_class = "Convpfm_" . $feed_config['fields'];
				$channel_attributes = $channel_class::get_channel_attributes();
				update_option('convpfm_channel_attributes', $channel_attributes, 'yes');
			} else {
				$channel_attributes = get_option('convpfm_channel_attributes');
			}
		}

		// Append or write to file
		$fp = fopen($file, 'a+');

		// Set proper UTF encoding BOM for CSV files
		if ($header == "true") {
			if (!preg_match("/fruugo/i", $feed_config['fields'])) {
				fputs($fp, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF));
			}
		}

		// Write each row of the products array
		foreach ($products as $row) {

			foreach ($row as $k => $v) {
				$pieces = explode("','", $v);
				$pieces = str_replace("'", "", $pieces);

				foreach ($pieces as $k_inner => $v) {
					if (($feed_config['fields'] != 'standard') && ($feed_config['fields'] != "customfeed")) {
						$v = $this->get_alternative_key($channel_attributes, $v);
					}

					// For CSV fileformat the keys need to get stripped of the g:
					if (($feed_config['fileformat'] == "csv") || ($feed_config['fileformat'] == "txt")) {
						$v = str_replace("g:", "", $v);
					}

					$v = trim($v, "'");
					$pieces[$k_inner] = $v;
				}

				// Convert tab delimiter
				if ($feed_config['delimiter'] == "tab") {
					$csv_delimiter = "\t";
				} else {
					$csv_delimiter = $feed_config['delimiter'];
				}

				if ($feed_config['fields'] == "google_local") {
					$tab_line = "";

					if ($header == "false") {
						// Get the store codes
						foreach ($feed_config['attributes'] as $k => $v) {
							if (preg_match('/\|/', $k)) {
								$stores_local = $k;
							}
						}

						$store_ids = explode("|", $stores_local);
						if (is_array($store_ids)) {

							foreach ($store_ids as $store_key => $store_value) {
								$pieces[1] = $store_value;

								if (!empty($store_value)) {
									foreach ($pieces as $t_key => $t_value) {
										$tab_line .= $t_value . "$csv_delimiter";
									}
									$tab_line = rtrim($tab_line, $csv_delimiter);
									$tab_line .= PHP_EOL;
								}
							}
							fwrite($fp, $tab_line);
						} else {
							// Only one store code entered
							foreach ($pieces as $t_key => $t_value) {
								$tab_line .= $t_value . "$csv_delimiter";
							}

							$tab_line = rtrim($tab_line, $csv_delimiter);
							$tab_line .= PHP_EOL;
							fwrite($fp, $tab_line);
						}
					} else {
						foreach ($pieces as $t_key => $t_value) {
							$tab_line .= $t_value . "$csv_delimiter";
						}
						$tab_line = rtrim($tab_line, $csv_delimiter);
						$tab_line .= PHP_EOL;
						fwrite($fp, $tab_line);
					}
				} else {
					$pieces = array_map('trim', $pieces);
					$tofile = fputcsv($fp, $pieces, $csv_delimiter, '"');
				}
			}
		}
		// Close the file
		fclose($fp);

		// Return external location of feed
		return $external_file;
	}

	/**
	 * Get products that are eligable for adding to the file
	 */
	public function convpfm_GetProducts($project_config)
	{
		$nr_products_processed = $project_config['nr_products_processed'];
		$count_products = wp_count_posts('product', 'product_variation');

		// User would like to see a preview of their feed
		// Retrieve only 5 products
		if (array_key_exists('preview_feed', $project_config)) {
			if ($project_config['preview_feed'] == "on") {
				$count_products = 5;
			}
		}

		if (isset($project_config['product_variations'])) {
			$count_single = wp_count_posts('product');
			$count_variation = wp_count_posts('product_variation');
			$published_single = (int) $count_single->publish;
			$published_variation = (int) $count_variation->publish;
			$published_products = $published_single + $published_variation;
		} else {
			$count_single = wp_count_posts('product');
			$published_products = (int) $count_single->publish;
		}

		$versions = array(
			"PHP" => (float)phpversion(),
			"Wordpress" => get_bloginfo('version'),
			"WooCommerce" => WC()->version,
			// "Plugin" => WOOCOMMERCESEA_PLUGIN_VERSION
		);

		// User would like to see a preview of their feed
		// Retrieve only 5 products
		if (array_key_exists('preview_feed', $project_config)) {
			if ($project_config['preview_feed'] == "on") {
				$published_products = 5;
			}
		}

		/**
		 * Do not change these settings, they are here to prevent running into memory issues
		 */
		if ($versions['PHP'] < 5.6) {
			// Old version, process a maximum of 50 products per batch
			$nr_batches = ceil($published_products / 50);
		} elseif ($versions['PHP'] == 5.6) {
			// Old version, process a maximum of 100 products per batch
			$nr_batches = ceil($published_products / 200);
		} else {
			// Fast PHP version, process a 750 products per batch
			$nr_batches = ceil($published_products / 750);

			if ($published_products > 50000) {
				$nr_batches = ceil($published_products / 2500);
			} else {
				$nr_batches = ceil($published_products / 750);
			}
		}

		/**
		 * User set his own batch size
		 */
		$convpfm_batch_size = get_option('convpfm_batch_size');
		if (!empty($convpfm_batch_size)) {
			if (is_numeric($convpfm_batch_size)) {
				$nr_batches = ceil($published_products / $convpfm_batch_size);
			}
		}

		$offset_step_size = (0 < $published_products && 0 < $nr_batches) ? ceil($published_products / $nr_batches) : 0;

		/**
		 * Check if the [attributes] array in the project_config is of expected format.
		 * For channels that have mandatory attribute fields (such as Google shopping) we need to rebuild the [attributes] array
		 * Only add fields to the file that the user selected
		 * Construct header line for CSV ans TXT files, for XML create the XML root and header
		 */
		if ($project_config['fileformat'] != 'xml') {
			if ($project_config['fields'] != 'standard') {
				foreach ($project_config['attributes'] as $key => $value) {
					foreach ($value as $k => $v) {
						if (($k == "attribute") && (strlen($v) > 0)) {
							if (!isset($attr)) {
								$attr = "'$v'";
							} else {
								$attr .= ",'$v'";
							}
						}
					}
				}
			} else {
				foreach (array_keys($project_config['attributes']) as $attribute_key) {
					if (!isset($attr)) {
						if (strlen($attribute_key) > 0) {
							$attr = "'$attribute_key'";
						}
					} else {
						if (strlen($attribute_key) > 0) {
							$attr .= ",'$attribute_key'";
						}
					}
				}
			}
			$attr = trim($attr, "'");
			$products[] = array($attr);
			if ($nr_products_processed == 0) {
				$file = $this->convpfm_create_csvtxt_feed($products, $project_config, 'true');
			}
		} else {
			$products[] = array();
			$file = $this->convpfm_create_xml_feed($products, $project_config, 'true');
		}
		$xml_piece = "";

		// Get taxonomies
		$no_taxonomies = array("element_category", "template_category", "portfolio_category", "portfolio_skills", "portfolio_tags", "faq_category", "slide-page", "category", "post_tag", "nav_menu", "link_category", "post_format", "product_type", "product_visibility", "product_cat", "product_shipping_class", "product_tag");
		$taxonomies = get_taxonomies();
		$diff_taxonomies = array_diff($taxonomies, $no_taxonomies);
		//unset($taxonomies);
		//unset($no_taxonomies);

		// Check if we need to get just products or also product variations
		if (isset($project_config['product_variations'])) {
			$post_type = array('product', 'product_variation');
		} else {
			$post_type = array('product');
		}

		// Check shipping currency location
		$project_config['ship_suffix'] = "false";
		if (!empty($project_config['attributes'])) {
			foreach ($project_config['attributes'] as $attr_key => $attr_value) {

				if ($attr_value['mapfrom'] == "shipping") {

					if (!empty($attr_value['suffix'])) {
						$project_config['ship_suffix'] = "true";
					}
				}
			}
		}

		// Pinteres RSS feeds need different sorting
		if ($project_config['fields'] == "pinterest_rss") {
			$orderby = "ASC";
		} else {
			$orderby = "DESC";
		}

		// Switch to configured WPML language
		if (isset($project_config['WPML'])) {
			if (function_exists('icl_object_id')) {
				if (!class_exists('Polylang')) {
					global $sitepress;
					$lang = $project_config['WPML'];
					$sitepress->switch_lang($lang);
				}
			}
		}

		// Get Orders
		if (isset($project_config['total_product_orders_lookback'])) {
			if ($project_config['total_product_orders_lookback'] > 0) {
				$allowed_product_orders = $this->convpfm_get_orders($project_config);
			}
		}

		// Get WooCommerce categories
		// $product_categories = get_terms( 'product_cat', 'hide_empty=0' );
		// $prod_cats = array();
		// if ( ! empty( $product_categories ) && ! is_wp_error( $product_categories ) ) {
		//	foreach ( $product_categories as $category ) {
		//		$prod_cats[$category->slug] = $category->name;
		//	}	
		//}
		//unset($product_categories);

		// Get Fiters on categories
		//$prod_cats_slugs = array();
		//if(isset($project_config['rules'])){
		//	foreach ($project_config['rules'] as $rule){
		//		if(($rule['attribute'] == "categories") && ($rule['condition'] == "=")){
		//			$slug = array_search($rule['criteria'], $prod_cats);
		//			array_push($prod_cats_slugs, $slug);
		//		}
		//	}
		//}

		//$product_cat = "";
		//if(count($prod_cats_slugs) > 0){
		//	foreach ( $prod_cats_slugs as $cat_slug ){
		//		$product_cat .= $cat_slug .",";
		//	}
		//}	
		unset($prods);

		// Construct WP query
		$wp_query = array(
			'post_type' 			=> $post_type,
			'posts_per_page' 		=> $offset_step_size,
			'offset' 			=> $nr_products_processed,
			'post_status' 			=> 'publish',
			'orderby'                	=> 'date',
			'order'                  	=> 'desc',
			'fields' 			=> 'ids',
			'no_found_rows' 		=> true,
			'cache_results'          	=> false,
			'update_post_term_cache' 	=> false,
			'update_post_meta_cache' 	=> false,
			//				'product_cat' 			=> $product_cat
		);

		$prods = new WP_Query($wp_query);

		// SHIPPING ZONES IS BIG, TAKES TOO MUCH MEMORY
		$shipping_zones = $this->convpfm_get_shipping_zones();

		// Log some information to the WooCommerce logs
		$add_convpfm_logging = get_option('add_convpfm_logging');
		if ($add_convpfm_logging == "yes") {
			$logger = new WC_Logger();
			$logger->add('Conversios.io - Product Feed Manager for WooCommerce', '<!-- Start new QUERY -->');
			$logger->add('Conversios.io - Product Feed Manager for WooCommerce', print_r($wp_query, TRUE));
			$logger->add('Conversios.io - Product Feed Manager for WooCommerce', '<!-- START new QUERY -->');
		}

		
		while ($prods->have_posts()) : $prods->the_post();
			global $product;
			$attr_line = "";
			$catname = "";
			$catlink = "";
			$xml_product = array();

			$this->childID = get_the_ID();
			$this->parentID = wp_get_post_parent_id($this->childID);
			$post = get_post($this->parentID);

			// When Wordpress user is an admin and runs the process of creating product feeds also products are put in the feed
			// with a status other than published. This is unwanted behaviour so we skip all products that are not on publish.
			$status = get_post_status($this->parentID);
			if ($status != "publish") {
				continue;
			}

			//if ( ! is_object( $product ) || ! $product->is_visible() ) {
			//	continue;
			//}

			$product_data['id'] = get_the_ID();

			// Add to the prevent duplication array
			// if(!in_array($product_data['id'], $prevent_duplicates)){
			//	array_push($prevent_duplicates, $product_data['id']);
			//} else {
			//	// duplicate product, do not add
			//	continue;
			//}

			// Only products that have been sold are allowed to go through
			if (isset($project_config['total_product_orders_lookback'])) {
				if ($project_config['total_product_orders_lookback'] > 0) {
					if (!in_array($product_data['id'], $allowed_product_orders)) {
						continue;
					}
				}
			}
			$product_data['title'] = $product->get_title();
			$product_data['title'] = $this->convpfm_utf8_for_xml($product_data['title']);
			$product_data['title_slug'] = $product->get_slug();
			$product_data['mother_title'] = $product->get_title();
			$product_data['mother_title'] = $this->convpfm_utf8_for_xml($product_data['mother_title']);
			$product_data['title_hyphen'] = $product_data['title'];
			$product_data['sku'] = $product->get_sku();
			$product_data['sku_id'] = $product_data['id'];
			$product_data['wc_post_id_product_id'] = "wc_post_id_" . $product_data['id'];
			$product_data['publication_date'] = date("F j, Y, G:i a");
			$product_data['add_to_cart_link'] = get_site_url() . "/shop/?add-to-cart=" . $product_data['id'];
			$product_data['cart_link'] = get_site_url() . "/cart/?add-to-cart=" . $product_data['id'];

			// Get product creation date
			if (!empty($product->get_date_created())) {
				$datetime_created  = $product->get_date_created(); // Get product created datetime
				$timestamp_created = $datetime_created->getTimestamp(); // product created timestamp
				$datetime_now      = new WC_DateTime(); // Get now datetime (from Woocommerce datetime object)
				$timestamp_now     = $datetime_now->getTimestamp(); // Get now timestamp
				$time_delta        = $timestamp_now - $timestamp_created; // Difference in seconds
				$product_data['days_back_created'] = round($time_delta / 86400);
				$product_data['product_creation_date'] = $datetime_created;
			}

			// Start product visibility logic
			$product_data['exclude_from_catalog'] = "no";
			$product_data['exclude_from_search'] = "no";
			$product_data['exclude_from_all'] = "no";
			$product_data['featured'] = "no";

			// Get product tax details
			$product_data['tax_status'] = $product->get_tax_status();
			$product_data['tax_class'] = $product->get_tax_class();

			// End product visibility logic
			$product_data['item_group_id'] = $this->parentID;

			// Get number of orders for this product
			$product_data['total_product_orders'] = 0;
			$product_data['total_product_orders'] = get_post_meta($product_data['id'], 'total_sales', true);

			if ($product_data['item_group_id'] > 0) {
				$visibility_list = wp_get_post_terms($product_data['item_group_id'], 'product_visibility', array("fields" => "all"));
			} else {
				$visibility_list = wp_get_post_terms(get_the_ID(), 'product_visibility', array("fields" => "all"));
			}

			foreach ($visibility_list as $visibility_single) {
				if ($visibility_single->slug == "exclude-from-catalog") {
					$product_data['exclude_from_catalog'] = "yes";
				}
				if ($visibility_single->slug == "exclude-from-search") {
					$product_data['exclude_from_search'] = "yes";
				}
				if ($visibility_single->slug == "featured") {
					$product_data['featured'] = "yes";
				}
			}
			//unset($visibility_list);

			if (($product_data['exclude_from_search'] == "yes") && ($product_data['exclude_from_catalog'] == "yes")) {
				$product_data['exclude_from_all'] = "yes";
			}

			if (!empty($product_data['sku'])) {
				$product_data['sku_id'] = $product_data['sku'] . "_" . $product_data['id'];

				if ($project_config['fields'] == "facebook_drm") {
					if ($product_data['item_group_id'] > 0) {
						$product_data['sku_item_group_id'] = $product_data['sku'] . "_" . $product_data['item_group_id'];
					} else {
						$product_data['sku_item_group_id'] = $product_data['sku'] . "_" . $product_data['id'];
					}
				}
			}

			$cat_alt = array();
			$cat_term = "";
			$categories = array();

			if ($product_data['item_group_id'] > 0) {
				$cat_obj = get_the_terms($product_data['item_group_id'], 'product_cat');
			} else {
				$cat_obj = get_the_terms($product_data['id'], 'product_cat');
			}

			if ($cat_obj) {
				foreach ($cat_obj as $cat_term) {
					$cat_alt[] = $cat_term->term_id;
				}
			}
			$cat_order = "";
			$categories = $cat_alt;

			// Determine real category hierarchy
			$cat_order = array();
			foreach ($categories as $key => $value) {
				$product_cat = get_term($value, 'product_cat');

				// Not in array so we can add it
				if (!in_array($value, $cat_order)) {

					$parent_cat = $product_cat->parent;
					// Check if parent is in array
					if (in_array($parent_cat, $cat_order)) {
						// Parent is in array, now determine position
						$position = array_search($parent_cat, $cat_order);

						// Use array splice to add it in the right position in array
						$new_position = $position + 1;

						// Insert on new position in array
						array_splice($cat_order, $new_position, 0, $value);
					} else {
						// Parent is not in array
						if ($parent_cat > 0) {
							if (in_array($parent_cat, $categories)) {
								$cat_order[] = $parent_cat;
							}
							$cat_order[] = $value;
						} else {
							// This is the MAIN cat so should be in front
							array_unshift($cat_order, $value);
						}
					}
				}
			}
			$categories = $cat_order;

			// This is a category fix for Yandex, probably needed for all channels
			// When Yoast is not installed and a product is linked to multiple categories
			// The ancestor categoryId does not need to be in the feed
			$double_categories = array(
				0 => "Yandex",
				1 => "Prisjakt.se",
				2 => "Pricerunner.se",
				3 => "Pricerunner.dk",
			);

			if (in_array($project_config['name'], $double_categories, TRUE)) {
				$cat_alt = array();
				if ($product_data['item_group_id'] > 0) {
					$cat_terms = get_the_terms($product_data['item_group_id'], 'product_cat');
				} else {
					$cat_terms = get_the_terms($product_data['id'], 'product_cat');
				}

				if ($cat_terms) {
					foreach ($cat_terms as $cat_term) {
						$cat_alt[] = $cat_term->term_id;
					}
				}
				$categories = $cat_alt;
				//unset($cat_alt);
			}

			$product_data['category_path'] = "";

			// Sort categories so the category with the highest category ID being used for the category path attributes
			asort($categories);

			foreach ($categories as $key => $value) {
				$product_cat = get_term($value, 'product_cat');

				// Check if there are mother categories
				if (!empty($product_cat)) {
					$category_path = $this->convpfm_get_term_parents($product_cat->term_id, 'product_cat', $link = false, $project_taxonomy = $project_config['taxonomy'], $nicename = false, $visited = array());

					if (!is_object($category_path)) {
						$category_path_skroutz = preg_replace('/&gt;/', '>', $category_path);
						$product_data['category_path'] = $category_path;
						$product_data['category_path_skroutz'] = $category_path_skroutz;
						$product_data['category_path_skroutz'] = str_replace("Home >", "", $product_data['category_path_skroutz']);
						$product_data['category_path_skroutz'] = str_replace("&amp;", "&", $product_data['category_path_skroutz']);
					}

					$parent_categories = get_ancestors($product_cat->term_id, 'product_cat');
					foreach ($parent_categories as $category_id) {
						$parent = get_term_by('id', $category_id, 'product_cat');
						$parent_name = $parent->name;
					}

					if (isset($product_cat->name)) {
						$catname .= "||" . $product_cat->name;
						$catlink .= "||" . get_term_link($value, 'product_cat');
					}
				}
			}

			// Get the Yoast primary category (if exists)
			if (class_exists('WPSEO_Primary_Term')) {

				// Show the post's 'Primary' category, if this Yoast feature is available, & one is set
				$item_id = $product_data['id'];
				if ($product_data['item_group_id'] > 0) {
					$item_id = $product_data['item_group_id'];
				}
				$wpseo_primary_term = new WPSEO_Primary_Term('product_cat', $item_id);
				$prm_term = $wpseo_primary_term->get_primary_term();
				$prm_cat = get_term($prm_term, 'product_cat');

				if (!is_wp_error($prm_cat)) {
					if (!empty($prm_cat->name)) {
						$product_data['category_path'] = $this->convpfm_get_term_parents($prm_cat->term_id, 'product_cat', $link = false, $project_taxonomy = $project_config['taxonomy'], $nicename = false, $visited = array());
						$product_data['one_category'] = $prm_cat->name;
					}
				}

				unset($prm_cat);
				unset($prm_term);
				unset($wpseo_primary_term);
			}

			// Get the RankMath primary category
			if ($this->convpfm_is_plugin_active('seo-by-rank-math/rank-math.php')) {
				$item_id = $product_data['id'];
				if ($product_data['item_group_id'] > 0) {
					$item_id = $product_data['item_group_id'];
				}
				$primary_cat_id = get_post_meta($item_id, 'rank_math_primary_product_cat', true);
				if ($primary_cat_id) {
					$product_cat = get_term($primary_cat_id, 'product_cat');

					if (!empty($product_cat->name)) {
						$product_data['category_path'] = $this->convpfm_get_term_parents($product_cat->term_id, 'product_cat', $link = false, $project_taxonomy = $project_config['taxonomy'], $nicename = false, $visited = array());
						$product_data['one_category'] = $product_cat->name;
					}
				}
				unset($primary_cat_id);
			}

			$product_data['category_path_short'] = str_replace("Home &gt;", "", $product_data['category_path']);
			$product_data['category_path_short'] = str_replace("&gt;", ">", $product_data['category_path_short']);
			$product_data['category_link'] = $catlink;
			$product_data['raw_categories'] = ltrim($catname, "||");
			$product_data['categories'] = $catname;

			// Raw descriptions, unfiltered
			$product_data['raw_description'] = do_shortcode(wpautop($post->post_content));
			$product_data['raw_short_description'] = do_shortcode(wpautop($post->post_excerpt));
			$product_data['description'] = html_entity_decode((str_replace("\r", "", $post->post_content)), ENT_QUOTES | ENT_XML1, 'UTF-8');
			$product_data['short_description'] = html_entity_decode((str_replace("\r", "", $post->post_excerpt)), ENT_QUOTES | ENT_XML1, 'UTF-8');

			// Strip HTML from (short) description
			$product_data['description'] = $this->rip_tags($product_data['description']);
			$product_data['short_description'] = $this->rip_tags($product_data['short_description']);

			// Strip out Visual Composer short codes, including the Visual Composer Raw HTML
			$product_data['description'] = preg_replace('/\[vc_raw_html.*\[\/vc_raw_html\]/', '', $product_data['description']);
			$product_data['description'] = preg_replace('/\[(.*?)\]/', ' ', $product_data['description']);
			$product_data['short_description'] = preg_replace('/\[vc_raw_html.*\[\/vc_raw_html\]/', '', $product_data['short_description']);
			$product_data['short_description'] = preg_replace('/\[(.*?)\]/', ' ', $product_data['short_description']);

			// Strip out the non-line-brake character
			$product_data['description'] = str_replace("&#xa0;", "", $product_data['description']);
			$product_data['short_description'] = str_replace("&#xa0;", "", $product_data['short_description']);

			// Strip strange UTF chars
			$product_data['description'] = trim($this->convpfm_utf8_for_xml($product_data['description']));
			$product_data['short_description'] = trim($this->convpfm_utf8_for_xml($product_data['short_description']));

			// Truncate description on 5000 characters for Google Shopping
			if ($project_config['fields'] == "google_shopping") {
				$product_data['description'] = mb_substr($product_data['description'], 0, 5000);
				$product_data['short_description'] = mb_substr($product_data['short_description'], 0, 5000);
			}

			// Truncate to maximum 5000 characters
			$product_data['raw_description'] = mb_substr($product_data['raw_description'], 0, 5000);
			$product_data['raw_short_description'] = mb_substr($product_data['raw_short_description'], 0, 5000);

			// Parent variable description
			$product_data['mother_description'] = $product_data['description'];
			$product_data['mother_short_description'] = $product_data['short_description'];

			/**
			 * Check of we need to add Google Analytics UTM parameters
			 */
			if (isset($project_config['utm_on'])) {
				$utm_part = $this->convpfm_append_utm_code($project_config, get_the_ID(), $this->parentID, get_permalink($product_data['id']));
			} else {
				$utm_part = "";
			}

			$product_data['link'] = get_permalink($product_data['id']) . "$utm_part";
			$product_data['link_no_tracking'] = get_permalink($product_data['id']);
			$variable_link = htmlspecialchars(get_permalink($product_data['id']));
			$vlink_piece = explode("?", $variable_link);
			$qutm_part = ltrim($utm_part, "&amp;");
			$qutm_part = ltrim($qutm_part, "amp;");
			$qutm_part = ltrim($qutm_part, "?");
			if ($qutm_part) {
				$product_data['variable_link'] = $vlink_piece[0] . "?" . $qutm_part;
				$product_data['link_no_tracking'] = $vlink_piece[0];
			} else {
				$product_data['variable_link'] = $vlink_piece[0];
				$product_data['link_no_tracking'] = $vlink_piece[0];
			}

			$product_data['condition'] = ucfirst(get_post_meta($product_data['id'], '_convpfm_condition', true));
			$product_data['purchase_note'] = get_post_meta($product_data['id'], '_purchase_note');

			if (empty($product_data['condition']) || $product_data['condition'] == "Array") {
				$product_data['condition'] = "New";
			}

			// get_stock only works as of WC 5 and higher?
			$product_data['availability'] = $this->get_stock($this->childID);

			/**
			 * When 'Enable stock management at product level is active
			 * availability will always return out of stock, even when the stock quantity > 0
			 * Therefor, we need to check the stock_status and overwrite te availability value
			 */
			if (!is_bool($product)) {
				$stock_status = $product->get_stock_status();
			} else {
				$stock_status = "instock";
			}
			$product_data['stock_status'] = $stock_status;

			if ($stock_status == "outofstock") {
				$product_data['availability'] = "out of stock";
				if (($project_config['taxonomy'] == "google_shopping") && ($project_config['fields'] == "google_shopping")) {
					$product_data['availability'] = "out_of_stock";
					if ($project_config['name'] == "Twitter") {
						$product_data['availability'] = "out of stock";
					}
				}
				if (preg_match("/fruugo/i", $project_config['fields'])) {
					$product_data['availability'] = "OUTOFSTOCK";
				}
			} elseif ($stock_status == "onbackorder") {
				$product_data['availability'] = "on backorder";
				if (($project_config['taxonomy'] == "google_shopping") && ($project_config['fields'] == "google_shopping")) {
					$product_data['availability'] = "backorder";
					if ($project_config['name'] == "Twitter") {
						$product_data['availability'] = "available for order";
					}
				}
				if (preg_match("/fruugo/i", $project_config['fields'])) {
					$product_data['availability'] = "OUTOFSTOCK";
				}
			} else {
				$product_data['availability'] = "in stock";
				if (($project_config['taxonomy'] == "google_shopping") && ($project_config['fields'] == "google_shopping")) {
					$product_data['availability'] = "in_stock";
					if ($project_config['name'] == "Twitter") {
						$product_data['availability'] = "in stock";
					}
				}
				if (preg_match("/fruugo/i", $project_config['fields'])) {
					$product_data['availability'] = "INSTOCK";
				}
			}

			// Create future availability dates
			if ($stock_status == "onbackorder") {
				$product_data['availability_date_plus1week'] = date('Y-m-d', strtotime('+1 week'));
				$product_data['availability_date_plus2week'] = date('Y-m-d', strtotime('+2 week'));
				$product_data['availability_date_plus3week'] = date('Y-m-d', strtotime('+3 week'));
				$product_data['availability_date_plus4week'] = date('Y-m-d', strtotime('+4 week'));
				$product_data['availability_date_plus5week'] = date('Y-m-d', strtotime('+5 week'));
				$product_data['availability_date_plus6week'] = date('Y-m-d', strtotime('+6 week'));
				$product_data['availability_date_plus7week'] = date('Y-m-d', strtotime('+7 week'));
				$product_data['availability_date_plus8week'] = date('Y-m-d', strtotime('+8 week'));
			}

			$product_data['author'] = get_the_author();
			$product_data['quantity'] = $this->clean_quantity($this->childID, "_stock");
			if (is_object($product)) {
				$product_data['visibility'] = $product->get_catalog_visibility();
			}
			$download = $product->is_downloadable();

			if ($download == 1) {
				$product_data['downloadable'] = "yes";
			} else {
				$product_data['downloadable'] = "no";
			}
			unset($download);

			$virtual = $product->is_virtual();
			if ($virtual == 1) {
				$product_data['virtual'] = "yes";
			} else {
				$product_data['virtual'] = "no";
			}
			unset($virtual);

			$product_data['menu_order'] =  get_post_field('menu_order', $product_data['id']);
			$product_data['currency'] = get_woocommerce_currency();
			if (isset($project_config['WCML'])) {
				$product_data['currency'] = $project_config['WCML'];
			}

			$sales_price_from = get_post_meta($product_data['id'], '_sale_price_dates_from', true);
			$sales_price_to   = get_post_meta($product_data['id'], '_sale_price_dates_to', true);

			if (!empty($sales_price_from) and !empty($sales_price_to)) {
				if (!empty($sales_price_from)) {
					$sales_price_date_from = date("Y-m-d", intval($sales_price_from));
					$sales_price_date_to   = date("Y-m-d", intval($sales_price_to));
					$product_data['sale_price_effective_date'] = $sales_price_date_from . "/" . $sales_price_date_to;
				} else {
					$product_data['sale_price_effective_date'] = "";
				}
			} else {
				$product_data['sale_price_effective_date'] = "";
			}

			$product_data['image'] = wp_get_attachment_url($product->get_image_id());
			$non_local_image = wp_get_attachment_image_src(get_post_thumbnail_id($product_data['id']), 'single-post-thumbnail');
			if (is_array($non_local_image)) {
				$product_data['non_local_image'] = $non_local_image[0];
			}
			unset($non_local_image);

			$product_data['image_all'] = $product_data['image'];
			$product_data['all_images'] = $product_data['image'];
			$product_data['all_gallery_images'] = "";
			$product_data['product_type'] = $product->get_type();

			// Get the number of active variations that are on stock for variable products
			if (($product_data['item_group_id'] > 0) && ($product_data['product_type'] == "variation")) {
				$parent_product = wc_get_product($product_data['item_group_id']);

				if (is_object($parent_product)) {
					$current_products = $parent_product->get_children();
					$product_data['nr_variations'] = count($current_products);
					$vcnt = 0;

					foreach ($current_products as $ckey => $cvalue) {
						$stock_value = get_post_meta($cvalue, "_stock_status", true);
						if ($stock_value == "instock") {
							$vcnt++;
						}
					}
					//unset($current_products);
					$product_data['nr_variations_stock'] = $vcnt;
				} else {
					$product_data['nr_variations'] = 9999;
					$product_data['nr_variations_stock'] = 9999;
				}
				//unset($parent_product);
			} else {
				$product_data['nr_variations'] = 9999;
				$product_data['nr_variations_stock'] = 9999;
			}

			// For variable products I need to get the product gallery images of the simple mother product
			if ($product_data['item_group_id'] > 0) {
				$parent_product = wc_get_product($product_data['item_group_id']);

				if (is_object($parent_product)) {
					$gallery_ids = $parent_product->get_gallery_image_ids();
					$product_data['image_all'] = wp_get_attachment_url($parent_product->get_image_id());
					$gal_id = 1;
					foreach ($gallery_ids as $gallery_key => $gallery_value) {
						$product_data["image_" . $gal_id] = wp_get_attachment_url($gallery_value);
						$product_data['all_images'] .= "," . wp_get_attachment_url($gallery_value);
						$product_data['all_gallery_images'] .= "," . wp_get_attachment_url($gallery_value);
						$gal_id++;
					}
				}
				//unset($parent_product);
			} else {
				$gallery_ids = $product->get_gallery_image_ids();
				$gal_id = 1;
				foreach ($gallery_ids as $gallery_key => $gallery_value) {
					$product_data["image_" . $gal_id] = wp_get_attachment_url($gallery_value);
					$product_data['all_images'] .= "," . wp_get_attachment_url($gallery_value);
					$product_data['all_gallery_images'] .= "," . wp_get_attachment_url($gallery_value);
					$gal_id++;
				}
				//unset($gallery_ids);
			}

			$product_data['all_images'] = ltrim($product_data['all_images'], ',');
			$product_data['all_images_kogan'] = preg_replace('/,/', '|', $product_data['all_images']);
			$product_data['all_gallery_images'] = ltrim($product_data['all_gallery_images'], ',');

			$product_data['content_type'] = "product";
			if ($product_data['product_type'] == "variation") {
				$product_data['content_type'] = "product_group";
			}
			$product_data['rating_total'] = $product->get_rating_count();
			$product_data['rating_average'] = $product->get_average_rating();

			// When a product has no reviews than remove the 0 rating	
			if ($product_data['rating_average'] == 0) {
				unset($product_data['rating_average']);
			}

			$product_data['shipping'] = 0;
			$tax_rates = WC_Tax::get_base_tax_rates($product->get_tax_class());
			$all_standard_taxes = WC_Tax::get_rates_for_tax_class('');
			$shipping_class_id = $product->get_shipping_class_id();
			//$shipping_class= $product->get_shipping_class();

			$class_cost_id = "class_cost_" . $shipping_class_id;
			if ($class_cost_id == "class_cost_0") {
				$class_cost_id = "no_class_cost";
			}
			//unset($shipping_class_id);

			$product_data['shipping_label'] = $product->get_shipping_class();
			$term = get_term_by('slug', $product->get_shipping_class(), 'product_shipping_class');
			if (is_object($term)) {
				$product_data['shipping_label_name'] = $term->name;
			}

			// Get product prices
			$product_data['price'] = wc_get_price_including_tax($product, array('price' => $product->get_price()));
			$product_data['price'] = wc_format_decimal($product_data['price'], 2);

			$args = array(
				'ex_tax_label'       => false,
				'currency'           => '',
				'decimal_separator'  => wc_get_price_decimal_separator(),
				'thousand_separator' => wc_get_price_thousand_separator(),
				'decimals'           => wc_get_price_decimals(),
				'price_format'       => get_woocommerce_price_format(),
			);

			$dec_price = wc_price($product_data['price'], $args);
			preg_match('/<bdi>(.*?)&nbsp;/', $dec_price, $matches);
			if (isset($matches[1])) {
				$product_data['separator_price'] = $matches[1];
			}
			//unset($dec_price);

			$product_data['sale_price'] = wc_get_price_including_tax($product, array('price' => $product->get_sale_price()));
			$product_data['sale_price'] = wc_format_decimal($product_data['sale_price'], 2);
			$product_data['regular_price'] = wc_get_price_including_tax($product, array('price' => $product->get_regular_price()));
			$product_data['regular_price'] = wc_format_decimal($product_data['regular_price'], 2);

			$dec_regular_price = wc_price($product_data['regular_price'], $args);
			preg_match('/<bdi>(.*?)&nbsp;/', $dec_regular_price, $matches_reg);
			if (isset($matches_reg[1])) {
				$product_data['separator_regular_price'] = $matches_reg[1];
			}
			//unset($dec_regular_price);

			$dec_sale_price = wc_price($product_data['sale_price'], $args);
			preg_match('/<bdi>(.*?)&nbsp;/', $dec_sale_price, $matches_sale);
			if (isset($matches_sale[1])) {
				$product_data['separator_sale_price'] = $matches_sale[1];
			}
			//unset($dec_sale_price);

			// Untouched raw system pricing - DO NOT CHANGE THESE
			$float_system_net_price = floatval(wc_get_price_excluding_tax($product));
			$product_data['system_net_price'] = round($float_system_net_price, 2);
			$product_data['system_net_price'] = wc_format_decimal($product_data['system_net_price'], 2);
			$product_data['system_net_sale_price'] = wc_format_decimal(wc_get_price_excluding_tax($product, array('price' => $product->get_sale_price())), 2);
			$product_data['system_net_regular_price'] = wc_format_decimal(wc_get_price_excluding_tax($product, array('price' => $product->get_regular_price())), 2);

			// System regular price
			$float_system_regular_price = floatval($product->get_regular_price());
			$product_data['system_regular_price'] = round($float_system_regular_price, 2);
			$product_data['system_regular_price'] = wc_format_decimal($product_data['system_regular_price'], 2);

			// System sale price
			$float_system_sale_price = floatval($product->get_sale_price());
			if ($float_system_sale_price > 0) {
				$product_data['system_sale_price'] = round($float_system_sale_price, 2);
				$product_data['system_sale_price'] = wc_format_decimal($product_data['system_sale_price'], 2);
				$sale_price = $product_data['system_sale_price'];
			}

			$code_from_config = $this->convpfm_country_to_code($project_config['countries']);
			$nr_standard_rates = count($all_standard_taxes);

			if (!empty($all_standard_taxes) && ($nr_standard_rates > 1)) {
				foreach ($all_standard_taxes as $rate) {
					$rate_arr = get_object_vars($rate);
					if ($rate_arr['tax_rate_country'] == $code_from_config) {
						$tax_rates[1]['rate'] = $rate_arr['tax_rate'];
					}
					unset($rate_arr);
				}
			} else {
				if (!empty($tax_rates)) {
					foreach ($tax_rates as $tk => $tv) {
						if ($tv['rate'] > 0) {
							$tax_rates[1]['rate'] = $tv['rate'];
						} else {
							$tax_rates[1]['rate'] = 0;
						}
					}
				} else {
					$tax_rates[1]['rate'] = 0;
				}
			}

			if (empty($tax_rates[1]['rate'])) {
				if (!empty($all_standard_taxes) && ($nr_standard_rates > 1)) {
					foreach ($all_standard_taxes as $rate) {
						$rate_arr = get_object_vars($rate);
						if ($rate_arr['tax_rate_country'] == "") {
							$tax_rates[1]['rate'] = $rate_arr['tax_rate'];
						}
						unset($rate_arr);
					}
				}
			}
			//unset($all_standard_taxes);

			$tax_rates_first = reset($tax_rates);
			$fullrate = 100 + $tax_rates_first['rate'];

			if (array_key_exists(1, $tax_rates)) {
				$product_data['vat'] = $tax_rates[1]['rate'];
			}

			// Override price when bundled or composite product
			if (($product->get_type() == "bundle") || ($product->get_type() == "composite")) {
				$meta = get_post_meta($product_data['id']);

				if ($product->get_type() == "bundle") {
					if ($this->convpfm_is_plugin_active('woocommerce-product-bundles/woocommerce-product-bundles.php')) {
						if (!empty($product->get_bundle_price())) {
							$product_data['price'] = $product->get_bundle_price_including_tax();
							$product_data['price_forced'] = $product->get_bundle_price_including_tax();
							$product_data['regular_price'] = $product->get_bundle_regular_price();
							$product_data['regular_price_forced'] = $product->get_bundle_regular_price_including_tax();

							if ($product_data['price'] != $product_data['regular_price']) {
								$product_data['sale_price'] = $product->get_bundle_price();
								$product_data['sale_price_forced'] = $product->get_bundle_price_including_tax();
							}

							// Unset sale price when it is 0.00	
							if ($product_data['sale_price'] == "0.00") {
								unset($product_data['sale_price']);
							}
						}
					}
				} else {
					// Composite product
					if ($this->convpfm_is_plugin_active('woocommerce-composite-products/woocommerce-composite-products.php')) {
						if (!empty($product->get_composite_price())) {
							$product_data['price'] = $product->get_composite_price_including_tax();
							$product_data['price_forced'] = $product->get_composite_price_including_tax();
							$product_data['regular_price'] = $product->get_composite_regular_price();
							$product_data['regular_price_forced'] = $product->get_composite_regular_price_including_tax();

							if ($product_data['price'] != $product_data['regular_price']) {
								$product_data['sale_price'] = $product->get_composite_price();
								$product_data['sale_price_forced'] = $product->get_composite_price_including_tax();
							}

							// Unset sale price when it is 0.00	
							if ($product_data['sale_price'] == "0.00") {
								unset($product_data['sale_price']);
							}
						}
					}
				}
			}

			// Override price when WCML price is different than the non-translated price	
			if (isset($project_config['WCML'])) {
				$product_data['price'] = apply_filters('wcml_raw_price_amount', $product_data['price'], $project_config['WCML']);
				$product_data['regular_price'] = apply_filters('wcml_raw_price_amount', $product_data['regular_price'], $project_config['WCML']);
				$product_data['sale_price'] = apply_filters('wcml_raw_price_amount', $product_data['sale_price'], $project_config['WCML']);

				// Make sure the product ID is not NULL either
				global $woocommerce_wpml;
				if (!is_null($project_config['WCML'])) {
					$firstKey = array_key_first($tax_rates);
					$product_data['non_geo_wcml_price'] = wc_format_decimal(get_post_meta($product_data['id'], '_regular_price', true), 2);
					$product_data['non_geo_wcml_price'] = wc_format_localized_price($product_data['non_geo_wcml_price']);
					$product_data['non_geo_wcml_price_net_price'] = $product_data['non_geo_wcml_price'] / ((100 + $tax_rates[$firstKey]['rate']) / 100);

					$non_geo_sale = get_post_meta($product_data['id'], '_sale_price', true);
					if (!empty($non_geo_sale)) {
						$product_data['non_geo_wcml_sale_price'] = wc_format_decimal(get_post_meta($product_data['id'], '_sale_price', true), 2);
						$product_data['non_geo_wcml_sale_price'] = wc_format_localized_price($product_data['non_geo_wcml_sale_price']);
						$product_data['non_geo_wcml_sale_price_net_price'] = $product_data['non_geo_wcml_sale_price'] / ((100 - $tax_rates[$firstKey]['rate']) / 100);
					}
				}

				// When WCML manual prices have been entered
				if (($this->convpfm_is_plugin_active('sitepress-multilingual-cms')) || (function_exists('icl_object_id'))) {
					$custom_prices = $woocommerce_wpml->get_multi_currency()->custom_prices->get_product_custom_prices($product_data['id'], $project_config['WCML']);

					if ($custom_prices['_price'] > 0) {
						$product_data['price'] = $custom_prices['_price'];
					}

					if ($custom_prices['_regular_price'] > 0) {
						$product_data['regular_price'] = $custom_prices['_regular_price'];
					}

					if (isset($custom_prices['_sale_price'])) {
						if ($custom_prices['_sale_price'] > 0) {
							$product_data['sale_price'] = $custom_prices['_sale_price'];
						} else {
							// Unset these values as WCML prices have been filled in manually
							unset($product_data['sale_price']);
							unset($product_data['system_regular_price']);
							unset($product_data['system_price']);
							unset($product_data['system_sale_price']);
						}
					}
				}
			}

			if (array_key_exists('sale_price', $product_data)) {
				if ($product_data['regular_price'] == $product_data['sale_price']) {
					$product_data['sale_price'] = "";
				}
			}

			// Determine the gross prices of products
			$tax_rates_first = $tax_rates_first['rate'];
			if ($product->get_price()) {
				$product_data['price_forced'] = round(wc_get_price_excluding_tax($product, array('price' => $product->get_price())) * (100 + $tax_rates_first) / 100, 2);
			}

			if ($product->get_regular_price()) {
				$product_data['regular_price_forced'] = round(wc_get_price_excluding_tax($product, array('price' => $product->get_regular_price())) * (100 + $tax_rates_first) / 100, 2);
				$product_data['net_regular_price'] = round(wc_get_price_excluding_tax($product, array('price' => $product->get_regular_price())), 2);
			}

			if ($product->get_sale_price()) {
				$product_data['sale_price_forced'] = round(wc_get_price_excluding_tax($product, array('price' => $product->get_sale_price())) * (100 + $tax_rates_first) / 100, 2);
				$product_data['net_sale_price'] = round(wc_get_price_excluding_tax($product, array('price' => $product->get_sale_price())), 2);

				// We do not want to have 0 sale price values in the feed	
				if ($product_data['net_sale_price'] == 0) {
					$product_data['net_sale_price'] = "";
				}
			}

			$float_net_price = floatval(wc_get_price_excluding_tax($product));
			$product_data['net_price'] = round($float_net_price, 2);
			$product_data['net_price'] = wc_format_decimal($product_data['net_price'], 2);

			$price = wc_get_price_including_tax($product, array('price' => $product->get_price()));

			if (array_key_exists('sale_price', $product_data)) {
				if ($product_data['sale_price'] > 0) {
					$price = $product_data['sale_price'];
				}
			}


			// Is the Discount Rules for WooCommerce by FlyCart plugin active, check for sale prices
			if ($this->convpfm_is_plugin_active('woo-discount-rules/woo-discount-rules.php')) {
				$discount = apply_filters('advanced_woo_discount_rules_get_product_discount_price_from_custom_price', false, $product, 1, $product_data['sale_price'], 'discounted_price', true, true);
				if ($discount !== false) {
					// round discounted price on proper decimals
					$decimals = wc_get_price_decimals();
					if ($decimals < 1) {
						$discount = round($discount, 0);
						$product_data['sale_price'] = round($discount, 0);
						$product_data['price'] = $discount;
						$product_data['regular_price'] = round($product_data['regular_price'], 0);
					} else {
						$discount = round($discount, 2);
						$product_data['sale_price'] = @number_format($discount, 2);
						$product_data['price'] = $discount;
					}

					$price_incl_tax = get_option('woocommerce_prices_include_tax');
					if ($price_incl_tax == "yes") {
						$product_data['price_forced'] = $product_data['price'] * ($fullrate / 100);
						$product_data['price_forced_rounded'] = round($product_data['price_forced'], 0);
						$product_data['net_price'] = $product_data['price'] / ($fullrate / 100);
						$product_data['net_price_rounded'] = round($product_data['net_price']); // New Nov. 1st 2023
						$product_data['net_regular_price'] = $product_data['regular_price'] / ($fullrate / 100);
						$product_data['net_regular_price_rounded'] = round($product_data['net_regular_price'], 0); // New Nov. 1st 2023
						$product_data['net_sale_price'] = ($discount / $fullrate) * 100; // New Nov. 1st 2023
						$product_data['net_sale_price_rounded'] = round($product_data['net_sale_price'], 0); // New Nov. 1st 2023
						$product_data['sale_price_forced'] = $discount * ($fullrate / 100);
						$product_data['sale_price_forced_rounded'] = round($product_data['sale_price_forced'], 0);
					} else {
						$product_data['net_sale_price'] = $discount;
						$product_data['sale_price_forced'] = round($discount * ($fullrate / 100), 2);
						$product_data['sale_price_forced_rounded'] = round($product_data['sale_price_forced'], 0);
					}

					$thousand_separator = wc_get_price_thousand_separator();
					if ($thousand_separator != ',') {
						$replaceWith = '';
						$product_data['price'] = preg_replace('/,/', $replaceWith, $product_data['price'], 1);
						$product_data['regular_price'] = preg_replace('/,/', $replaceWith, $product_data['regular_price'], 1);
						if ($product_data['sale_price'] > 0) {
							$product_data['sale_price'] = preg_replace('/,/', $replaceWith, $product_data['sale_price'], 1);
						}
					}
				}
				//unset($discount);
			}

			// Do we need to convert all of the above prices with the Aelia Currency Switcher
			if ((isset($project_config['AELIA'])) && (!empty($GLOBALS['woocommerce-aelia-currencyswitcher'])) && (get_option('add_aelia_support') == "yes")) {
				if (!array_key_exists('base_currency', $project_config)) {
					$from_currency = get_woocommerce_currency();
				} else {
					$from_currency = $project_config['base_currency'];
				}

				$product_data['price'] = apply_filters('wc_aelia_cs_convert', $product_data['price'], $from_currency, $project_config['AELIA']);
				$product_data['regular_price'] = apply_filters('wc_aelia_cs_convert', $product_data['regular_price'], $from_currency, $project_config['AELIA']);
				$product_data['sale_price'] = apply_filters('wc_aelia_cs_convert', $product_data['sale_price'], $from_currency, $project_config['AELIA']);

				// Bypass caching issues
				$product_data['price'] = do_shortcode('[aelia_cs_product_price product_id="' . $product_data['id'] . '" formatted="0" currency="' . $project_config['AELIA'] . '"]');
				$product_data['regular_price'] = do_shortcode('[aelia_cs_product_price product_id="' . $product_data['id'] . '" formatted="0" currency="' . $project_config['AELIA'] . '"]');
				//$product_data['sale_price'] = apply_filters('wc_aelia_cs_convert', $custom_value['sale_price']['p'], $from_currency, $project_config['AELIA']);

				if (isset($product_data['price_forced'])) {
					$product_data['price_forced'] = apply_filters('wc_aelia_cs_convert', $product_data['price_forced'], $from_currency, $project_config['AELIA']);
				}

				if (isset($product_data['regular_price_forced'])) {
					$product_data['regular_price_forced'] = apply_filters('wc_aelia_cs_convert', $product_data['regular_price_forced'], $from_currency, $project_config['AELIA']);
				}
				if ($product->get_sale_price()) {
					$product_data['sale_price_forced'] = apply_filters('wc_aelia_cs_convert', $product_data['sale_price_forced'], $from_currency, $project_config['AELIA']);
				}

				$product_data['net_price'] = apply_filters('wc_aelia_cs_convert', $product_data['net_price'], $from_currency, $project_config['AELIA']);
				if (isset($product_data['net_regular_price'])) {
					$product_data['net_regular_price'] = apply_filters('wc_aelia_cs_convert', $product_data['net_regular_price'], $from_currency, $project_config['AELIA']);
				}
				if (isset($product_data['net_sale_price'])) {
					$product_data['net_sale_price'] = apply_filters('wc_aelia_cs_convert', $product_data['net_sale_price'], $from_currency, $project_config['AELIA']);
				}

				// Get Aelia manually inserted currency prices
				if ($product->is_type('simple')) {
					$regular_aelia_prices = get_post_meta($product_data['id'], '_regular_currency_prices', true);
				} else {
					$regular_aelia_prices = get_post_meta($product_data['id'], 'variable_regular_currency_prices', true);
				}

				$regular_aelia_prices = trim($regular_aelia_prices, "}");
				$regular_aelia_prices = trim($regular_aelia_prices, "{");

				if (strlen($regular_aelia_prices) > 2) {
					$regular_aelia_pieces = explode(",", $regular_aelia_prices);
					foreach ($regular_aelia_pieces as $rap_k => $rap_v) {
						$regulars = explode(":", $rap_v);
						$reg_cur = trim($regulars[0], "\"");
						$reg_val = trim($regulars[1], "\"");

						if ($reg_cur == $project_config['AELIA']) {
							$product_data['price'] = $reg_val;
							$product_data['regular_price'] = $reg_val;
						}
					}
				}

				// Is the Aelia rounding plugin active
				if (class_exists('WC_Aelia_CS_Custom_Rounding')) {

					// Do not round the base currency
					if ($from_currency != $project_config['AELIA']) {
						$product_data['price'] = round($product_data['price'], 0) - 0.05;
						$product_data['regular_price'] = round($product_data['regular_price'], 0) - 0.05;
						$product_data['sale_price'] = round($product_data['sale_price'], 0) - 0.05;
						$product_data['price_forced'] = round($product_data['price_forced'], 0) - 0.05;
					}
				}

				if ($product->is_type('simple')) {
					$sale_aelia_prices = get_post_meta($product_data['id'], '_sale_currency_prices', true);
				} else {
					$sale_aelia_prices = get_post_meta($product_data['id'], 'variable_sale_currency_prices', true);
				}
				$sale_aelia_prices = trim($sale_aelia_prices, "}");
				$sale_aelia_prices = trim($sale_aelia_prices, "{");

				if (strlen($sale_aelia_prices) > 2) {
					$sale_aelia_pieces = explode(",", $sale_aelia_prices);
					foreach ($sale_aelia_pieces as $sap_k => $sap_v) {
						$sales = explode(":", $sap_v);
						$sale_cur = trim($sales[0], "\"");
						$sale_val = trim($sales[1], "\"");
						if ($sale_cur == $project_config['AELIA']) {
							$product_data['sale_price'] = $sale_val;
						}
					}
				}
			}

			// Is the Mix and Match plugin active
			if ($this->convpfm_is_plugin_active('woocommerce-mix-and-match-products/woocommerce-mix-and-match-products.php')) {
				if ($product->is_type('mix-and-match')) {
					if ($product_data['price'] == "0.00") {
						$product_data['price'] = "";
						$product_data['regular_price'] = "";
					}

					// Get minimum prices
					$product_data['mm_min_price'] = wc_format_localized_price($product->get_mnm_price());
					$product_data['mm_min_regular_price'] = wc_format_localized_price($product->get_mnm_regular_price());

					// Get maximum prices
					$product_data['mm_max_price'] = wc_format_localized_price($product->get_mnm_price('max'));
					$product_data['mm_max_regular_price'] = wc_format_localized_price($product->get_mnm_regular_price('max'));
				}
			}

			// Localize the price attributes
			$decimal_separator = wc_get_price_decimal_separator();
			$product_data['price'] = wc_format_localized_price($product_data['price']);
			$product_data['regular_price'] = wc_format_localized_price($product_data['regular_price']);

			if (array_key_exists('sale_price', $product_data)) {
				$product_data['sale_price'] = wc_format_localized_price($product_data['sale_price']);
			}

			if ($product->get_price()) {
				$product_data['price_forced'] = wc_format_localized_price($product_data['price_forced']);
			}
			if ($product->get_regular_price()) {
				$product_data['regular_price_forced'] = wc_format_localized_price($product_data['regular_price_forced']);
			}
			if ($product->get_sale_price()) {
				$product_data['sale_price_forced'] = wc_format_localized_price($product_data['sale_price_forced']);
			}
			$product_data['net_price'] = wc_format_localized_price($product_data['net_price']);

			if (isset($product_data['net_regular_price'])) {
				$product_data['net_regular_price'] = wc_format_localized_price($product_data['net_regular_price']);
			}

			if (isset($product_data['net_sale_price'])) {
				$product_data['net_sale_price'] = wc_format_localized_price($product_data['net_sale_price']);
				$product_data['net_sale_price'] = wc_format_localized_price($product_data['net_sale_price']);
				$product_data['net_sale_price'] = wc_format_localized_price($product_data['net_sale_price']);
			}

			if (!empty($product_data['system_price'])) {
				$product_data['system_price'] = wc_format_localized_price($product_data['system_price']);
			}

			if (!empty($product_data['system_net_price'])) {
				$product_data['system_net_price'] = wc_format_localized_price($product_data['system_net_price']);
			}

			if (!empty($product_data['system_net_sale_price'])) {
				$product_data['system_net_sale_price'] = wc_format_localized_price($product_data['system_net_sale_price']);
			}

			if (!empty($product_data['system_net_regular_price'])) {
				$product_data['system_net_regular_price'] = wc_format_localized_price($product_data['system_net_regular_price']);
			}

			if (!empty($product_data['system_regular_price'])) {
				$product_data['system_regular_price'] = wc_format_localized_price($product_data['system_regular_price']);
			}

			if (!empty($product_data['system_sale_price'])) {
				$product_data['system_sale_price'] = wc_format_localized_price($product_data['system_sale_price']);
			}

			// Add rounded price options
			$decimal_separator = wc_get_price_decimal_separator();
			$float_price = floatval($product_data['price']);
			$float_regular_price = floatval($product_data['regular_price']);
			$float_sale_price = floatval($product_data['sale_price']);

			if ($decimal_separator == ",") {
				$product_data['rounded_price'] = str_replace(',', '.', $product_data['price']);
				$product_data['rounded_price'] = round(number_format($product_data['rounded_price'], 2, '.', ''));
				$product_data['rounded_regular_price'] = str_replace(',', '.', $product_data['regular_price']);
				$product_data['rounded_regular_price'] = round(number_format($product_data['rounded_regular_price'], 2, '.', ''));

				if ($product_data['sale_price'] > 0) {
					$product_data['rounded_sale_price'] = str_replace(',', '.', $product_data['sale_price']);
					$product_data['rounded_sale_price'] = round(number_format($float_sale_price, 2, '.', ''));
				}
			} else {
				//$product_data['rounded_price'] = round($float_price,0);
				//$product_data['rounded_regular_price'] = (string) round($float_regular_price,0);
				//$product_data['rounded_sale_price'] = round($float_sale_price,0);
				$product_data['rounded_regular_price'] = $float_regular_price;
				$product_data['rounded_sale_price'] = $float_sale_price;
			}

			// Calculate discount percentage
			if (isset($product_data['rounded_sale_price'])) {
				if ($product_data['rounded_regular_price'] > 0) {
					$disc = round(($product_data['rounded_sale_price'] * 100) / $product_data['rounded_regular_price'], 0);
					$product_data['discount_percentage']  = 100 - $disc;
					//$product_data['discount_percentage'] = round(100-(($product_data['sale_price']/$product_data['regular_price'])*100),2);
				}
			}

			if (!empty($project_config['attributes'])) {
				foreach ($project_config['attributes'] as $attr_key => $attr_arr) {
					if (is_array($attr_arr)) {
						if ($attr_arr['attribute'] == "g:shipping") {
							if ($product_data['price'] > 0) {
								$product_data['shipping'] =  $this->convpfm_get_shipping_cost($class_cost_id, $project_config, $product_data['price'], $tax_rates, $fullrate, $shipping_zones, $product_data['id'], $product_data['item_group_id']);
								$shipping_str = $product_data['shipping'];
							}
						}
					}
				}

				if ((array_key_exists('shipping', $project_config['attributes'])) || (array_key_exists('lowest_shipping_costs', $project_config['attributes'])) || (array_key_exists('shipping_price', $project_config['attributes'])) || ($project_config['fields'] == "trovaprezzi") || ($project_config['fields'] == "idealo") || ($project_config['fields'] == "customfeed")) {
					$product_data['shipping'] =  $this->convpfm_get_shipping_cost($class_cost_id, $project_config, $product_data['price'], $tax_rates, $fullrate, $shipping_zones, $product_data['id'], $product_data['item_group_id']);
					$shipping_str = $product_data['shipping'];
				}
			}

			//unset($tax_rates);

			// Get only shipping costs
			if (!empty($shipping_str)) {
				$product_data['shipping_price'] = 0;
			}
			$lowest_shipping_price = array();
			$shipping_arr = $product_data['shipping'];

			if (is_array($shipping_arr)) {
				foreach ($shipping_arr as $akey => $arr) {
					//$product_data['shipping_price'] = $arr['price'];
					$pieces_ship = explode(" ", $arr['price']);
					if (isset($pieces_ship['1'])) {
						$product_data['shipping_price'] = $pieces_ship['1'];
						$lowest_shipping_price[] = $pieces_ship['1'];
					}
				}

				// Check if we need to add a region
				foreach ($shipping_arr as $akey => $arr) {
					if (isset($arr['country'])) {
						if (preg_match("/:/i", $arr['country'])) {
							$region_split = explode(":", $arr['country']);
							$sgipping_arr[$akey]['region'] = $region_split[1];
						}
					}
				}
			}

			// Get the lowest shipping costs
			if (!empty($lowest_shipping_price)) {
				$decimal_separator = wc_get_price_decimal_separator();
				if ($decimal_separator == ",") {
					$numeric_lowest_shipping_price = array();
					foreach ($lowest_shipping_price as &$value) {
						$number = str_replace(',', '.', $value);
						if (is_numeric($number)) {
							$value = number_format($number, 2, '.', '');
							$numeric_lowest_shipping_price[] = $value;
						}
					}
					$lowest_shipping_price = $numeric_lowest_shipping_price;
					//unset($value);
				}

				$nr_in = count($lowest_shipping_price);
				if ($nr_in > 0) {
					$product_data['lowest_shipping_costs'] = min($lowest_shipping_price);

					if ($decimal_separator == ",") {
						$product_data['lowest_shipping_costs'] = str_replace('.', ',', $product_data['lowest_shipping_costs']);
					}
				}
			}

			// Google Dynamic Remarketing feeds require the English price notation
			if ($project_config['name'] == "Google Remarketing - DRM") {
				$thousand_separator = wc_get_price_thousand_separator();

				if ($thousand_separator != ',') {
					$product_data['price'] = floatval(str_replace(',', '.', str_replace('.', '', $product_data['price'])));
					$product_data['regular_price'] = floatval(str_replace(',', '.', str_replace('.', '', $product_data['regular_price'])));
					if ($product_data['sale_price'] > 0) {
						$product_data['sale_price'] = floatval(str_replace(',', '.', str_replace('.', '', $product_data['sale_price'])));
					}
					if (isset($product_data['regular_price_forced'])) {
						$product_data['regular_price_forced'] = floatval(str_replace(',', '.', str_replace('.', '', $product_data['regular_price_forced'])));
					}
					if ($product->get_sale_price()) {
						$product_data['sale_price_forced'] = floatval(str_replace(',', '.', str_replace('.', '', $product_data['sale_price_forced'])));
					}
					if ($product_data['net_price'] > 0) {
						$product_data['net_price'] = floatval(str_replace(',', '.', str_replace('.', '', $product_data['net_price'])));
					}
					$product_data['net_regular_price'] = @floatval(str_replace(',', '.', str_replace('.', '', $product_data['net_regular_price'])));
					$product_data['net_sale_price'] = @floatval(str_replace(',', '.', str_replace('.', '', $product_data['net_sale_price'])));

					$product_data['vivino_price'] = $product_data['price'];
					$product_data['vivino_sale_price'] = $product_data['sale_price'];
					$product_data['vivino_regular_price'] = $product_data['regular_price'];
					$product_data['vivino_net_price'] = $product_data['net_price'];
					$product_data['vivino_net_sale_price'] = $product_data['net_sale_price'];
					$product_data['vivino_net_regular_price'] = $product_data['net_regular_price'];
				}
			}

			// Vivino prices
			$product_data['vivino_price'] = floatval(str_replace(',', '.', str_replace(',', '.', $product_data['price'])));
			$product_data['vivino_regular_price'] = floatval(str_replace(',', '.', str_replace(',', '.', $product_data['regular_price'])));
			if ($product_data['sale_price'] > 0) {
				$product_data['vivino_sale_price'] = floatval(str_replace(',', '.', str_replace(',', '.', $product_data['sale_price'])));
				if (isset($product_data['net_sale_price'])) {
					$product_data['vivino_net_sale_price'] = floatval(str_replace(',', '.', str_replace(',', '.', $product_data['net_sale_price'])));
				}
			}
			$product_data['vivino_net_price'] = floatval(str_replace(',', '.', str_replace(',', '.', $product_data['net_price'])));
			if (isset($product_data['net_regular_price'])) {
				$product_data['vivino_net_regular_price'] = floatval(str_replace(',', '.', str_replace(',', '.', $product_data['net_regular_price'])));
			}

			$product_data['installment'] = $this->convpfm_get_installment($project_config, $product_data['id']);
			$product_data['weight'] = ($product->get_weight()) ? $product->get_weight() : false;
			$product_data['height'] = ($product->get_height()) ? $product->get_height() : false;
			$product_data['length'] = ($product->get_length()) ? $product->get_length() : false;
			$product_data['width'] = ($product->get_width()) ? $product->get_width() : false;

			// Featured Image
			if (has_post_thumbnail($product_data['id'])) {
				$image = wp_get_attachment_image_src(get_post_thumbnail_id($product_data['id']), 'single-post-thumbnail');
				if (!empty($image[0])) {
					$product_data['feature_image'] = $this->get_image_url($image[0]);
					//unset($image);
				}
			} else {
				$product_data['feature_image'] = $this->get_image_url($product_data['image']);
			}

			/**
			 * Do we need to add Dynamic Attributes?
			 */
			$project_config['attributes_original'] = $project_config['attributes'] ?? array();

			if (!empty($project_config['attributes']) && $project_config['fields'] != 'standard') {
				//$project_config['attributes_original'] = array();
				foreach ($project_config['attributes'] as $stand_key => $stand_val) {
					if ((isset($stand_val['mapfrom'])) && (strlen($stand_val['mapfrom']) > 0)) {
						$project_config['attributes_original'][$stand_val['mapfrom']] = "true";
					}
				}
			}

			foreach ($diff_taxonomies as $taxo) {
				$term_value = get_the_terms($product_data['id'], $taxo);
				$product_data["$taxo"] = "";

				if (is_array($term_value)) {
					// Do not add variation values to the feed when they are out of stock
					if ($project_config['fields'] == "skroutz") {
						if (($product->is_type('variable')) && ($product_data['item_group_id'] == 0)) {
							$product_skroutz = wc_get_product($product_data['id']);
							$variations = $product_skroutz->get_available_variations();
							$variations_id = wp_list_pluck($variations, 'variation_id');
							$skroutz_att_array = array();

							foreach ($variations_id as $var_id) {
								$stock_value = get_post_meta($var_id, "_stock_status", true);
								if ($stock_value == "instock") {
									foreach ($term_value as $term) {
										$attr_value = get_post_meta($var_id, "attribute_" . $term->taxonomy, true);
										if (!in_array($attr_value, $skroutz_att_array)) {
											array_push($skroutz_att_array, $attr_value);
										}
										//unset($attr_value);
									}
									$product_data[$taxo] = ltrim($product_data[$taxo], ',');
									$product_data[$taxo] = rtrim($product_data[$taxo], ',');
								}
							}

							foreach ($skroutz_att_array as $skrtz_value) {
								$product_data[$taxo] .= "," . $skrtz_value;
							}
							$product_data[$taxo] = ltrim($product_data[$taxo], ',');
							$product_data[$taxo] = rtrim($product_data[$taxo], ',');
						} else {
							// Simple Skroutz product
							foreach ($term_value as $term) {
								$product_data[$taxo] .= "," . $term->name;
							}
							$product_data[$taxo] = ltrim($product_data[$taxo], ',');
							$product_data[$taxo] = rtrim($product_data[$taxo], ',');
						}
					} else {
						foreach ($term_value as $term) {
							$product_data[$taxo] .= "," . $term->name;
						}
						$product_data[$taxo] = ltrim($product_data[$taxo], ',');
						$product_data[$taxo] = rtrim($product_data[$taxo], ',');
					}
				}

				//unset($term_value);
			}

			/*
			 * Add product tags to the product data array
			 */
			$product_tags = get_the_terms($product_data['id'], "product_tag");
			if (is_array($product_tags)) {
				foreach ($product_tags as $term) {
					if (!array_key_exists("product_tag", $product_data)) {
						$product_data["product_tag"] = array($term->name);
						$product_data["product_tag_space"] = array($term->name);
					} else {
						array_push($product_data["product_tag"], $term->name);
						array_push($product_data["product_tag_space"], $term->name);
					}
				}
			} else {
				$product_data["product_tag"] = array();
				$product_data["product_tag_space"] = array();
			}
			//unset($product_tags);

			/**
			 * Get Custom Attributes for Single, Bundled and Composite products
			 */
			if (($product->is_type('simple')) || ($product->is_type('woosb')) || ($product->is_type('mix-and-match')) || ($product->is_type('external')) || ($product->is_type('bundle')) || ($product->is_type('composite')) || ($product_data['product_type'] == "variable") || ($product_data['product_type'] == "auction") || ($product->is_type('subscription') || ($product->is_type('grouped')))) {
				$custom_attributes = $this->get_custom_attributes($product_data['id']);

				if (is_array($custom_attributes)) {
					if (!in_array("convpfm optimized title", $custom_attributes)) {
						$convpfm_opt = array(
							"_convpfm_optimized_title" =>  "convpfm optimized title",
						);
						$custom_attributes = array_merge($custom_attributes, $convpfm_opt);
					}

					if (class_exists('All_in_One_SEO_Pack')) {
						$custom_attributes['_aioseop_title'] = "All in one seo pack title";
						$custom_attributes['_aioseop_description'] = "All in one seo pack description";
					}

					if (class_exists('Yoast_WooCommerce_SEO')) {
						if (array_key_exists("yoast_gtin8", $custom_attributes)) {
							$product_data["yoast_gtin8"] = $custom_attributes["yoast_gtin8"];
						}
						if (array_key_exists("yoast_gtin12", $custom_attributes)) {
							$product_data["yoast_gtin12"] = $custom_attributes["yoast_gtin12"];
						}
						if (array_key_exists("yoast_gtin13", $custom_attributes)) {
							$product_data["yoast_gtin13"] = $custom_attributes["yoast_gtin13"];
						}
						if (array_key_exists("yoast_gtin14", $custom_attributes)) {
							$product_data["yoast_gtin14"] = $custom_attributes["yoast_gtin14"];
						}
						if (array_key_exists("yoast_isbn", $custom_attributes)) {
							$product_data["yoast_isbn"] = $custom_attributes["yoast_isbn"];
						}
						if (array_key_exists("yoast_mpn", $custom_attributes)) {
							$product_data["yoast_mpn"] = $custom_attributes["yoast_mpn"];
						}
					}

					foreach ($custom_attributes as $custom_kk => $custom_vv) {
						$custom_value = get_post_meta($product_data['id'], $custom_kk, true);
						$new_key = "custom_attributes_" . $custom_kk;

						// This is a ACF image field (PLEASE NOTE: the ACF field needs to contain image or bild in the name)
						if (preg_match("/image|bild|immagine/i", $custom_kk)) {
							if (class_exists('ACF') && ($custom_value > 0)) {
								$image = wp_get_attachment_image_src($custom_value, "large");

								if (isset($image[0])) {
									$custom_value = $image[0];
								}
							}
						}

						// Just to make sure the title is never empty
						if (($custom_kk == "_aioseop_title") && ($custom_value == "")) {
							$custom_value = $product_data['title'];
						}

						// Just to make sure the description is never empty
						if (($custom_kk == "_aioseop_description") && ($custom_value == "")) {
							$custom_value = $product_data['description'];
						}

						// Just to make sure product names are never empty
						if (($custom_kk == "_convpfm_optimized_title") && ($custom_value == "")) {
							$custom_value = $product_data['title'];
						}

						// Just to make sure the condition field is never empty
						if (($custom_kk == "_convpfm_condition") && ($custom_value == "")) {
							$custom_value = $product_data['condition'];
						}

						// Need to clean up the strange price Rightpress is returning
						if ($this->convpfm_is_plugin_active('wc-dynamic-pricing-and-discounts/wc-dynamic-pricing-and-discounts.php')) {
							if ($custom_kk == "rp_wcdpd_price_cache") {
								if ((isset($project_config['AELIA'])) && (!empty($GLOBALS['woocommerce-aelia-currencyswitcher'])) && (get_option('add_aelia_support') == "yes")) {
									$product_data['price'] = do_shortcode('[aelia_cs_product_price product_id="' . $product_data['id'] . '" formatted="0" currency="' . $project_config['AELIA'] . '"]');
									$product_data['sale_price'] = apply_filters('wc_aelia_cs_convert', $custom_value['sale_price']['p'], $from_currency, $project_config['AELIA']);
								} else {
									if (array_key_exists("price", $custom_value)) {
										$product_data['price'] = $custom_value['price']['p'];
									}

									if (array_key_exists("sale_price", $custom_value)) {
										$product_data['sale_price'] = $custom_value['sale_price']['p'];
									}
								}
							}
						}
						$product_data[$new_key] = $custom_value;
					}
				}
				//unset($custom_attributes);

				/**
				 * We need to check if this product has individual custom product attributes
				 */
				global $wpdb;
				$sql = "SELECT meta.meta_id, meta.meta_key as name, meta.meta_value as type FROM " . $wpdb->prefix . "postmeta" . " AS meta, " . $wpdb->prefix . "posts" . " AS posts WHERE meta.post_id=" . $product_data['id'] . " AND meta.post_id = posts.id GROUP BY meta.meta_key ORDER BY meta.meta_key ASC";
				$data = $wpdb->get_results($sql);
				if (count($data)) {
					foreach ($data as $key => $value) {
						$value_display = str_replace("_", " ", $value->name);
						if (preg_match("/_product_attributes/i", $value->name)) {
							$product_attr = unserialize($value->type);

							if (!empty($product_attr)) {
								foreach ($product_attr as $key => $arr_value) {
									$new_key = "custom_attributes_" . $key;
									if (!empty($arr_value['value'])) {
										$product_data[$new_key] = $arr_value['value'];
									}
								}
							}
						}
					}
				}
				//unset($data);	
			}

			/**
			 * Get Product Attributes for Single products 
			 * These are the attributes users create themselves in WooCommerce
			 */
			if (($product->is_type('simple')) || ($product->is_type('external')) || ($product->is_type('woosb')) || ($product->is_type('mix-and-match')) || ($product->is_type('bundle')) || ($product->is_type('composite')) || ($product->is_type('auction') || ($product->is_type('subscription')) || ($product->is_type('variable')))) {
				$single_attributes = $product->get_attributes();
				foreach ($single_attributes as $attribute) {
					$attr_name = strtolower($attribute->get_name());
					$attr_value = $product->get_attribute($attr_name);
					$product_data[$attr_name] = $attr_value;
				}
				//unset($single_attributes);
			}

			// Check if user would like to use the mother main image for all variation products
			$add_mother_image = get_option('convpfm_add_mother_image');
			if (($add_mother_image == "yes") && ($product_data['item_group_id'] > 0)) {
				$mother_image = wp_get_attachment_image_src(get_post_thumbnail_id($product_data['item_group_id']), 'full');
				if (isset($mother_image[0])) {
					$product_data['image'] = $mother_image[0];
				}
				//unset($mother_image);
			}

			/**
			 * Versioned products need a seperate approach
			 * Get data for these products based on the mother products item group id 
			 */
			$variation_pass = "true";

			if (($product_data['item_group_id'] > 0) && (is_object(wc_get_product($product_data['item_group_id']))) && (($product_data['product_type'] == "variation") || ($product_data['product_type'] == "subscription_variation"))) {
				$product_variations = new WC_Product_Variation($product_data['id']);
				$variations = $product_variations->get_variation_attributes();

				// For Skroutz and Bestprice apparal products we can only append colours to the product name
				// When a product has both a size and color attribute we assume its an apparal product
				if (($project_config['fields'] == "skroutz") || ($project_config['fields'] == "bestprice")) {
					$size_found = "no";
					$color_found = "no";

					foreach ($project_config['attributes'] as $ky => $vy) {
						if (isset($vy['attribute'])) {
							if ($vy['attribute'] == "size") {
								$size_found = "yes";
								$sz_attribute = $vy['mapfrom'];
							}
							if ($vy['attribute'] == "color") {
								$color_found = "yes";
								$clr_attribute = $vy['mapfrom'];
							}
						}
					}

					// Remove size from variations array
					if (($size_found == "yes") && ($color_found == "yes")) {
						update_option('skroutz_apparel', "yes");
						update_option('skroutz_clr', $clr_attribute, 'no');
						update_option('skroutz_sz', $sz_attribute, 'no');
					}

					$skroutz_apparal = get_option('skroutz_apparel');

					if ($skroutz_apparal == "yes") {
						if (isset($clr_attribute)) {
							$skroutz_color = get_post_meta($product_data['id'], "attribute_" . $clr_attribute, true);
						}

						if (isset($sz_attribute)) {
							$skroutz_size = get_post_meta($product_data['id'], "attribute_" . $sz_attribute, true);
						}

						if ((!empty($skroutz_color)) && (!empty($skroutz_size))) {
							foreach ($variations as $kvar => $vvar) {
								// Does this product have a color value
								$var_key = get_option('skroutz_clr');
								$var_key = "attribute_" . $var_key;
								$skroutz_color = get_post_meta($product_data['id'], $var_key, true);

								// Does this color have a size value
								$var_key_sz = get_option('skroutz_sz');
								$var_key_sz = "attribute_" . $var_key_sz;
								$skroutz_size = get_post_meta($product_data['id'], $var_key_sz, true);

								if ($kvar == $var_key) {
									if (!isset($skroutz_clr_array)) {
										if (!empty($skroutz_color)) {
											$skroutz_clr_array = array($skroutz_color);
										}
									} else {
										if (!empty($skroutz_color)) {
											if (!in_array($skroutz_color, $skroutz_clr_array)) {
												array_push($skroutz_clr_array, $skroutz_color);
												$variation_pass = "true";
											} else {
												$variation_pass = "false";
											}
										}
									}
								} else {
									unset($variations[$kvar]);
								}
							}
						} else {
							// This is not an apparal product so a color variation is not allowed
							$variation_pass = "true";
						}
					} else {
						$variation_pass = "true";
					}
				}

				if ((isset($project_config['lowest_price_variations'])) || (isset($project_config['default_variations']))) {
					// Determine the default variation product
					if (($product_data['item_group_id'] > 0) && (is_object(wc_get_product($product_data['item_group_id']))) && (($product_data['product_type'] == "variation") || ($product_data['product_type'] == "subscription_variation"))) {
						$mother_product = new WC_Product_Variable($product_data['item_group_id']);
						//$mother_product = wc_get_product($product_data['item_group_id']);
						$def_attributes = $mother_product->get_default_attributes();

						if (isset($project_config['lowest_price_variations'])) {

							// Determine lowest priced variation
							$variation_min_price = $mother_product->get_variation_price('min');
							$variation_min_price = wc_format_decimal($variation_min_price, 2);
							$variation_min_price = wc_format_localized_price($variation_min_price);
							$var_price = get_post_meta($product_data['id'], '_price', true);
							$var_price = wc_format_decimal($var_price, 2);
							$var_price = wc_format_localized_price($var_price);
							$variation_prices = $mother_product->get_variation_prices();
							$variation_prices_price = array_values($variation_prices['price']);
							if (!empty($variation_prices_price)) {
								$lowest_price = min($variation_prices_price);
							} else {
								$lowest_price = 0;
							}

							if (($var_price == $lowest_price) || ($var_price == $variation_min_price) || ($product_data['system_regular_price'] == $variation_min_price) || ($product_data['system_net_price'] == $variation_min_price)) {
								$variation_pass = "true";
							} else {
								$variation_pass = "false";
							}
						}
					}
					// Get review rating and count for parent product
					$product_data['rating_total'] = $mother_product->get_rating_count();
					$product_data['rating_average'] = $mother_product->get_average_rating();

					if (isset($project_config['default_variations'])) {
						$diff_result = array_diff($variations, $def_attributes);

						if (!empty($diff_result)) {
							// Only when a variant has no attributes selected we will let it pass
							if (count(array_filter($variations)) == 0) {
								$variation_pass = "true";
							} else {
								$variation_pass = "false";
							}
						}
					}
				}

				$append = "";

				$variable_description = get_post_meta($product_data['id'], '_variation_description', true);
				$product_data['parent_sku'] = get_post_meta($product_data['item_group_id'], '_sku', true);

				/**
				 * When there is a specific description for a variation product than override the description of the mother product
				 */
				if (!empty($variable_description)) {
					$product_data['description'] = html_entity_decode((str_replace("\r", "", $variable_description)), ENT_QUOTES | ENT_XML1, 'UTF-8');
					//$product_data['short_description'] = html_entity_decode((str_replace("\r", "", $variable_description)), ENT_QUOTES | ENT_XML1, 'UTF-8');

					// Strip HTML from (short) description
					$product_data['description'] = $this->rip_tags($product_data['description']);
					//$product_data['short_description'] = $this->rip_tags($product_data['short_description']);

					// Strip out Visual Composer short codes
					$product_data['description'] = preg_replace('/\[(.*?)\]/', ' ', $product_data['description']);
					//$product_data['short_description'] = preg_replace( '/\[(.*?)\]/', ' ', $product_data['short_description'] );

					// Strip out the non-line-brake character
					$product_data['description'] = str_replace("&#xa0;", "", $product_data['description']);
					//$product_data['short_description'] = str_replace("&#xa0;", "", $product_data['short_description']);

					// Strip unwanted UTF8 chars
					$product_data['description'] = $this->convpfm_utf8_for_xml($product_data['description']);
					//$product_data['short_description'] = $this->convpfm_utf8_for_xml( $product_data['short_description'] );
				}

				/**
				 * Add the product visibility values for variations based on the simple mother product
				 */
				$product_data['exclude_from_catalog'] = "no";
				$product_data['exclude_from_search'] = "no";
				$product_data['exclude_from_all'] = "no";

				// Get number of orders for this product
				// First check if user added this field or created a rule or filter on it
				$ruleset = "false";
				if (array_key_exists('rules', $project_config)) {
					foreach ($project_config['rules'] as $rkey => $rvalue) {
						if (in_array('total_product_orders', $rvalue)) {
							$ruleset = "true";
						}
					}
				}

				if (array_key_exists('rules2', $project_config)) {
					foreach ($project_config['rules2'] as $rkey => $rvalue) {
						if (in_array('total_product_orders', $rvalue)) {
							$ruleset = "true";
						}
					}
				}

				if ((array_key_exists('total_product_orders', $project_config['attributes'])) || ($ruleset == "true")) {
					$product_data['total_product_orders'] = 0;
					$sales_array = $this->convpfm_get_nr_orders_variation($product_data['id']);
					$product_data['total_product_orders'] = $sales_array[0];
				}

				$visibility_list = wp_get_post_terms($product_data['item_group_id'], 'product_visibility', array("fields" => "all"));

				if (!empty($visibility_list)) {
					foreach ($visibility_list as $visibility_single) {
						if ($visibility_single->slug == "exclude-from-catalog") {
							$product_data['exclude_from_catalog'] = "yes";
						}
						if ($visibility_single->slug == "exclude-from-search") {
							$product_data['exclude_from_search'] = "yes";
						}
					}
				}
				//unset($visibility_list);

				if (($product_data['exclude_from_search'] == "yes") && ($product_data['exclude_from_catalog'] == "yes")) {
					$product_data['exclude_from_all'] = "yes";
				}

				/**
				 * Although this is a product variation we also need to grap the Product attributes belonging to the simple mother product
				 */
				$mother_attributes = get_post_meta($product_data['item_group_id'], '_product_attributes');

				if (!empty($mother_attributes)) {
					foreach ($mother_attributes as $attribute) {
						foreach ($attribute as $key => $attr) {
							$attr_name = $attr['name'];

							if (!empty($attr_name)) {
								$terms = get_the_terms($product_data['item_group_id'], $attr_name);
								if (is_array($terms)) {
									foreach ($terms as $term) {
										$attr_value = $term->name;
									}
									$product_data[$attr_name] = $attr_value;
								} else {
									// Add the variable parent attributes	
									// When the attribute was not set for variations
									if ($attr['is_variation'] == 0) {
										$new_key = "custom_attributes_" . $key;
										$product_data[$new_key] = $attr['value'];
									}
								}
							}
						}
					}
				}
				//unset($mother_attributes);

				/**
				 * Although this is a product variation we also need to grap the Dynamic attributes belonging to the simple mother prodict
				 */
				$stock_value = get_post_meta($product_data['id'], "_stock_status", true);
				//if($stock_value == "instock"){	
				foreach ($diff_taxonomies as $taxo) {
					$term_value = get_the_terms($product_data['item_group_id'], $taxo);
					unset($product_data[$taxo]);
					if (is_array($term_value)) {
						foreach ($term_value as $term) {
							if (empty($product_data[$taxo])) {
								$product_data[$taxo] = $term->name;
							} else {
								$product_data[$taxo] .= "," . $term->name;
								//$product_data[$taxo] .= " ".$term->name; // October 3th 2023
							}
						}
					}
				}

				/**
				 * Add product tags to the product data array
				 */
				$product_tags = get_the_terms($product_data['item_group_id'], "product_tag");
				if (is_array($product_tags)) {

					foreach ($product_tags as $term) {

						if (!array_key_exists("product_tag", $product_data)) {
							$product_data["product_tag"] = array($term->name);
						} else {
							array_push($product_data["product_tag"], $term->name);
						}
					}
				}

				// Add attribute values to the variation product names to make them unique
				$product_data['title_hyphen'] = $product_data['title'] . " - ";
				$product_data['mother_title_hyphen'] = $product_data['mother_title'] . " - ";

				foreach ($variations as $kk => $vv) {
					$custom_key = $kk;

					if (isset($project_config['product_variations']) && ($project_config['product_variations'] == "on")) {
						$taxonomy = str_replace("attribute_", "", $kk);

						$term = get_term_by('slug', $vv, $taxonomy);

						if ($term && $term->name) {
							$vv = $term->name;
						}

						if ($vv) {
							$append = ucfirst($vv);
							$append = rawurldecode($append);

							// Prevent duplicate attribute values from being added to the product name
							if (!preg_match("/" . preg_quote($product_data['title'], '/') . "/", $append)) {
								$product_data['title'] = $product_data['title'] . " " . $append;
								$product_data['title_hyphen'] = $product_data['title_hyphen'] . " " . $append;
							}
						}
					}

					$custom_key = str_replace("attribute_", "", $custom_key);
					$product_data[$custom_key] = $vv;
					$append = "";
				}

				/**
				 * Get Custom Attributes for this variable product
				 */
				$custom_attributes = $this->get_custom_attributes($product_data['id']);

				if (is_array($custom_attributes)) {
					if (!in_array("convpfm optimized title", $custom_attributes)) {
						$convpfm_opt = array(
							"_convpfm_optimized_title" =>  "convpfm optimized title",
						);
						$custom_attributes = array_merge($custom_attributes, $convpfm_opt);
					}
				}

				if (class_exists('All_in_One_SEO_Pack')) {
					$custom_attributes['_aioseop_title'] = "All in one seo pack title";
					$custom_attributes['_aioseop_description'] = "All in one seo pack description";
				}

				if (class_exists('Yoast_WooCommerce_SEO')) {
					$yoast_identifiers = get_post_meta($product_data['id'], 'wpseo_variation_global_identifiers_values');

					if (!empty($yoast_identifiers[0])) {
						if (array_key_exists("gtin8", $yoast_identifiers[0])) {
							$product_data["yoast_gtin8"] = $yoast_identifiers[0]["gtin8"];
						}
						if (array_key_exists("gtin12", $yoast_identifiers[0])) {
							$product_data["yoast_gtin12"] = $yoast_identifiers[0]["gtin12"];
						}
						if (array_key_exists("gtin13", $yoast_identifiers[0])) {
							$product_data["yoast_gtin13"] = $yoast_identifiers[0]["gtin13"];
						}
						if (array_key_exists("gtin14", $yoast_identifiers[0])) {
							$product_data["yoast_gtin14"] = $yoast_identifiers[0]["gtin14"];
						}
						if (array_key_exists("isbn", $yoast_identifiers[0])) {
							$product_data["yoast_isbn"] = $yoast_identifiers[0]["isbn"];
						}
						if (array_key_exists("mpn", $yoast_identifiers[0])) {
							$product_data["yoast_mpn"] = $yoast_identifiers[0]["mpn"];
						}
					}
				}

				foreach ($custom_attributes as $custom_kk => $custom_vv) {
					$custom_value = get_post_meta($product_data['id'], $custom_kk, true);

					// Product variant brand is empty, grap that of the mother product
					if (($custom_kk == "_convpfm_brand") && ($custom_value == "")) {
						$custom_value = get_post_meta($product_data['item_group_id'], $custom_kk, true);
					}

					// Just to make sure the title is never empty
					if (($custom_kk == "_aioseop_title") && ($custom_value == "")) {
						$custom_value = $product_data['title'];
					}

					// Just to make sure the description is never empty
					if (($custom_kk == "_aioseop_description") && ($custom_value == "")) {
						$custom_value = $product_data['description'];
					}

					// Product variant optimized title is empty, grap the mother product title
					if (($custom_kk == "_convpfm_optimized_title") && ($custom_value == "")) {
						$custom_value = $product_data['title'];
					}

					if (!is_array($custom_value)) {
						$custom_kk = str_replace("attribute_", "", $custom_kk);
						$new_key = "custom_attributes_" . $custom_kk;

						// In order to make the mapping work again, replace var by product
						$new_key = str_replace("var", "product", $new_key);
						if (!empty($custom_value)) {
							$product_data[$new_key] = $custom_value;
						}
					}
				}

				/**
				 * We need to check if this product has individual custom product attributes
				 */
				global $wpdb;
				$sql = "SELECT meta.meta_id, meta.meta_key as name, meta.meta_value as type FROM " . $wpdb->prefix . "postmeta" . " AS meta, " . $wpdb->prefix . "posts" . " AS posts WHERE meta.post_id=" . $product_data['id'] . " AND meta.post_id = posts.id GROUP BY meta.meta_key ORDER BY meta.meta_key ASC";
				$data = $wpdb->get_results($sql);
				if (count($data)) {
					foreach ($data as $key => $value) {
						$value_display = str_replace("_", " ", $value->name);
						if (preg_match("/_product_attributes/i", $value->name)) {
							$product_attr = unserialize($value->type);
							if ((!empty($product_attr)) && (is_array($product_attr))) {
								foreach ($product_attr as $key => $arr_value) {
									$new_key = "custom_attributes_" . $key;
									$product_data[$new_key] = $arr_value['value'];
								}
							}
						}
					}
				}

				/**
				 * We also need to make sure that we get the custom attributes belonging to the simple mother product
				 */
				$custom_attributes_mother = $this->get_custom_attributes($product_data['item_group_id']);

				foreach ($custom_attributes_mother as $custom_kk_m => $custom_value_m) {

					if (!array_key_exists($custom_kk_m, $product_data)) {
						$custom_value_m = get_post_meta($product_data['item_group_id'], $custom_kk_m, true);
						$new_key_m = "custom_attributes_" . $custom_kk_m;

						if (!is_array($custom_value_m)) {
							// In order to make the mapping work again, replace var by product
							// $new_key_m = str_replace("var","product",$new_key_m);
							if (!key_exists($new_key_m, $product_data) && (!empty($custom_value_m))) {
								if (is_array($custom_value_m)) {
									// determine what to do with this later 
								} else {
									// This is most likely a ACF field
									if (class_exists('ACF') && ($custom_value_m > 0)) {
										$image = wp_get_attachment_image_src($custom_value_m, "large");
										if (isset($image[0])) {
											$custom_value = $image[0];
											$product_data[$new_key_m] = $custom_value;
										} else {
											$product_data[$new_key_m] = $custom_value_m;
										}
									} else {
										$product_data[$new_key_m] = $custom_value_m;
									}
								}
							}
						} else {
							$arr_value = "";
							foreach ($custom_value_m as $key => $value) {
								// not for multidimensional arrays
								if (is_string($value)) {
									if (is_string($value)) {
										$arr_value .= $value . ",";
									}
								}
							}
							$arr_value = rtrim($arr_value, ",");
							$product_data[$new_key_m] = $arr_value;
						}
					}
				}
				//unset($custom_attributes_mother);
				//unset($product_variations);
				//unset($variations);
			}
			// END VARIABLE PRODUCT CODE

			/**
			 * In order to prevent XML formatting errors in Google's Merchant center
			 * we will add CDATA brackets to the title and description attributes
			 */
			$product_data['title_lc'] = ucfirst(strtolower($product_data['title']));
			$product_data['title_lcw'] = ucwords(strtolower($product_data['title']));			

			/**
			 * Check if individual products need to be excluded
			 */
			$product_data = $this->convpfm_exclude_individual($product_data);

			/**
			 * Check if we need to add category taxonomy mappings (Google Shopping)
			 */
			if ((array_key_exists('mappings', $project_config)) && ($project_config['taxonomy'] == 'google_shopping')) {
				if (isset($product_data['id'])) {
					$product_data = $this->woocommerce_sea_mappings($project_config['mappings'], $product_data);
				}
			} elseif ((!array_key_exists('mappings', $project_config)) && ($project_config['taxonomy'] == 'google_shopping')) {
				if (isset($product_data['id'])) {
					$product_data['categories'] = "";
				}
			}

			/**
			 * Do final check on Skroutz out of stock sizes
			 * When a size is not on stock remove it
			 */
			if ($project_config['fields'] == "skroutz") {

				if (isset($product_data['id'])) {

					foreach ($project_config['attributes'] as $ky => $vy) {
						if (isset($vy['attribute'])) {
							if ($vy['attribute'] == "size") {
								$size_found = "yes";
								$sz_attribute = $vy['mapfrom'];
							}
							if ($vy['attribute'] == "color") {
								$color_found = "yes";
								$clr_attribute = $vy['mapfrom'];
							}
						}
					}
					$stock_value = get_post_meta($product_data['id'], "_stock_status", true);

					if (!empty($clr_attribute)) {
						$clr_attr_value = get_post_meta($product_data['id'], "attribute_" . $clr_attribute, true);
					} else {
						$clr_attr_value = "";
					}

					if (isset($product_data['item_group_id']) && ($product_data['product_type'] == "variation")) {
						if ($product_data['item_group_id'] > 0) {
							$product_skroutz = wc_get_product($product_data['item_group_id']);
							if (is_object($product_skroutz)) {
								$skroutz_product_type = $product_skroutz->get_type();
							}

							if (($product_skroutz) && ($skroutz_product_type == "variable")) {
								$variations = $product_skroutz->get_available_variations();
								$variations_id = wp_list_pluck($variations, 'variation_id');
								$total_quantity = 0;
								$quantity_variation = 0;

								$sizez = array();
								foreach ($variations_id as $var_id_s) {
									$taxonomy = 'pa_size';
									$sizez_variation = get_post_meta($var_id_s, 'attribute_' . $taxonomy, true);
									$sizez_term = get_term_by('slug', $sizez_variation, $taxonomy);

									if (!in_array($sizez_term->name, $sizez)) {
										array_push($sizez, $sizez_term->name);
									}
								}

								foreach ($variations_id as $var_id) {
									if (isset($clr_attribute)) {
										//$clr_variation = get_post_meta( $product_data['id'], "attribute_".$clr_attribute, true );
										$clr_variation = get_post_meta($var_id, "attribute_" . $clr_attribute, true);
									} else {
										$clr_variation = "";
									}

									// Sum quantity of variations for apparel products
									if (array_key_exists("pa_size", $product_data) && array_key_exists("pa_color", $product_data)) {
										$quantity_variation = $this->get_attribute_value($var_id, "_stock");
										if (!empty($quantity_variation)) {
											$total_quantity += $quantity_variation;
										}
										$product_data['quantity'] = $total_quantity;
									}

									if (isset($sz_attribute)) {
										$size_variation = ucfirst(get_post_meta($var_id, "attribute_" . $sz_attribute, true));
									}
									$stock_variation = get_post_meta($var_id, "_stock_status", true);

									if ($clr_variation == $clr_attr_value) {
										if ($stock_variation == "outofstock") {
											// Remove this size as it is not on stock
											$size_variation_new = $size_variation . ",";
											$size_variation_new = str_replace('-', ' ', $size_variation_new);
											$size_variation = str_replace("-", ' ', $size_variation);

											if (isset($sz_attribute)) {
												if (array_key_exists($sz_attribute, $product_data)) {
													$product_data[$sz_attribute] = str_replace(ucfirst($size_variation), "", $product_data[$sz_attribute]);
													$product_data[$sz_attribute] = rtrim($product_data[$sz_attribute], " ");
													$product_data[$sz_attribute] = trim($product_data[$sz_attribute], ",");
												}
											}
										} else {
											// Add comma's in the size field and put availability on stock as at least one variation is on stock
											if (isset($size_variation)) {

												if (isset($sz_attribute)) {
													$product_data[$sz_attribute] = implode(",", $sizez);
													$product_data['availability'] = "in stock";
												}
											}
										}
									}
								}
							}
						} else {
							// This is a parent variable product
							if ($product_data['product_type'] == "variable") {
								$product_skroutz = wc_get_product($product_data['id']);
								$variations = $product_skroutz->get_available_variations();
								$variations_id = wp_list_pluck($variations, 'variation_id');

								foreach ($variations_id as $var_id) {
									//$clr_variation = get_post_meta( $var_id, "attribute_".$clr_attribute, true );
									$size_variation = get_post_meta($var_id, "attribute_" . $sz_attribute, true);
									$stock_variation = get_post_meta($var_id, "_stock_status", true);

									if ($stock_variation == "outofstock") {
										// Remove this size as it is not on stock
										if (array_key_exists($sz_attribute, $product_data)) {
											$product_data[$sz_attribute] = str_replace(ucfirst($size_variation), "", $product_data[$sz_attribute]);
											$product_data[$sz_attribute] = str_replace(", , ", ",", $product_data[$sz_attribute]);
											$product_data[$sz_attribute] = rtrim($product_data[$sz_attribute], " ");
											$product_data[$sz_attribute] = rtrim($product_data[$sz_attribute], ",");
										}
									}
								}
							}
						}
					} else {
						if ($product_data['product_type'] == "variable") {
							$product_skroutz = wc_get_product($product_data['id']);
							$variations = $product_skroutz->get_available_variations();
							$variations_id = wp_list_pluck($variations, 'variation_id');

							$size_array_raw = @explode(",", $product_data[$sz_attribute]);
							$size_array = array_map('trim', $size_array_raw);
							$enabled_sizes = array();
							foreach ($variations_id as $var_id) {
								if (isset($sz_attribute)) {
									$size_variation = strtoupper(get_post_meta($var_id, "attribute_" . $sz_attribute, true));
									$enabled_sizes[] = $size_variation;
								}
							}

							$new_size = "";
							foreach ($enabled_sizes as $siz) {
								$siz = trim($siz, " ");
								$size_variation = trim($size_variation, " ");
								$new_size .= " " . $siz . ",";
							}

							if (isset($sz_attribute)) {
								$product_data[$sz_attribute] = $new_size;
								$product_data[$sz_attribute] = str_replace(", , ", ",", $product_data[$sz_attribute]);
								$product_data[$sz_attribute] = rtrim($product_data[$sz_attribute], " ");
								$product_data[$sz_attribute] = rtrim($product_data[$sz_attribute], ",");
								$product_data[$sz_attribute] = ltrim($product_data[$sz_attribute], ",");
							}

							foreach ($variations_id as $var_id) {
								if (isset($sz_attribute)) {
									$size_variation = get_post_meta($var_id, "attribute_" . $sz_attribute, true);
									$product_excluded = ucfirst(get_post_meta($var_id, '_convpfm_exclude_product', true));

									if ($product_excluded == "Yes") {
										// Remove this size as it is has been set to be excluded from feeds
										if (array_key_exists($sz_attribute, $product_data)) {
											$new_size = "";
											foreach ($enabled_sizes as $siz) {
												$siz = trim($siz, " ");
												$size_variation = trim($size_variation, " ");
												if ($siz != strtoupper($size_variation)) {
													$new_size .= " " . $siz . ",";
												}
											}
											$product_data[$sz_attribute] = $new_size;
											$product_data[$sz_attribute] = str_replace(", , ", ",", $product_data[$sz_attribute]);
											$product_data[$sz_attribute] = rtrim($product_data[$sz_attribute], " ");
											$product_data[$sz_attribute] = rtrim($product_data[$sz_attribute], ",");
											$product_data[$sz_attribute] = ltrim($product_data[$sz_attribute], ",");
										}
									}
								}
							}
						}
					}
				}
			}

			/**
			 * Rules execution
			 */
			if (array_key_exists('rules2', $project_config)) {
				if (is_array($product_data)) {
					$product_data = $this->woocommerce_sea_rules($project_config['rules2'], $product_data);
				}
			}

			/**
			 * Field manipulation
			 */
			if (array_key_exists('field_manipulation', $project_config)) {
				if (is_array($product_data)) {
					$product_data = $this->woocommerce_field_manipulation($project_config['field_manipulation'], $product_data);
				}
			}

			/**
			 * Check if we need to exclude Wholesale products
			 * WooCommerce Wholesale Prices by Rymera Web Co
			 */
			if ($this->convpfm_is_plugin_active('woocommerce-wholesale-prices/woocommerce-wholesale-prices.bootstrap.php')) {
				if (is_array($product_data)) {
					if (isset($project_config['field_manipulation'])) {
						$product_data = $this->woocommerce_wholesale_check($project_config['field_manipulation'], $product_data);
					}
				}
			}

			/**
			 * Filter execution
			 */
			if (array_key_exists('rules', $project_config)) {
				if (is_array($product_data)) {
					$product_data = $this->woocommerce_sea_filters($project_config['rules'], $product_data);
				}
			}

			if (isset($product_data['title_lcw'])) {
				$product_data['title_lcw'] = ucwords($product_data['title_lcw']);
			}

			// Check if the sale price is effective        
			if (isset($product_data['sale_price_start_date'])) {
				if ((strtotime($product_data['sale_price_start_date'])) && (strtotime($product_data['sale_price_end_date']))) {
					$current_date = date('Y-m-d');
					if (($current_date < $product_data['sale_price_start_date'])) {
						unset($product_data['sale_price']);
					}

					if (($current_date > $product_data['sale_price_end_date'])) {
						unset($product_data['sale_price']);
					}
				}
			}

			/**
			 * When a product is a variable product we need to delete the original product from the feed, only the originals are allowed
			 */
			// For these channels parent products are allowed
			$allowed_channel_parents = array(
				"skroutz",
				//"bestprice",
				"google_dsa",
				"google_product_review",
			);

			if (array_key_exists('fields', $project_config)) {
				if (is_array($allowed_channel_parents)) {
					if (!in_array($project_config['fields'], $allowed_channel_parents)) {
						if (($product->is_type('variable')) && (isset($product_data['item_group_id']))) {
							//if($product_data['item_group_id'] > 0){
							$product_data = array();
							$product_data = null;
							//}
						}
					}
				}
			}

			/**
			 * Remove variation products that are not THE default variation product
			 */
			if ((isset($variation_pass)) && ($variation_pass == "false")) {
				$product_data = array();
				$product_data = null;
			}

			/**
			 * And item_group_id is not allowed for simple products, prevent users from adding this to the feedd
			 */
			if (($product->is_type('simple')) || ($product->is_type('external')) || ($product->is_type('woosb')) || ($product->is_type('mix-and-match')) || ($product->is_type('bundle')) || ($product->is_type('composite')) || ($product->is_type('auction') || ($product->is_type('subscription')) || ($product->is_type('variable')))) {
				unset($product_data['item_group_id']);
			}

			/**
			 * Truncate length of product title when it is over 150 characters (requirement for Google Shopping, Pinterest and Facebook
			 */
			if (isset($product_data['title'])) {
				$length_title = strlen($product_data['title']);
				if ($length_title > 149) {
					$product_data['title'] = mb_substr($product_data['title'], 0, 150);
				}
			}

			/**
			 * When product has passed the filter rules it can continue with the rest
			 */
			if (!empty($product_data)) {
				/**
				 * Determine what fields are allowed to make it to the csv and txt productfeed
				 */
				if (($project_config['fields'] != "standard") && (!isset($tmp_attributes)) && is_array($project_config['attributes']) && !empty($project_config['attributes'])) {
					$old_attributes_config = $project_config['attributes'];
					$tmp_attributes = array();
					foreach ($project_config['attributes'] as $key => $value) {
						if (strlen($value['mapfrom']) > 0) {
							$tmp_attributes[$value['mapfrom']] = "true";
						}
					}
					$project_config['attributes'] = $tmp_attributes;
				}

				if (isset($old_attributes_config) && is_array($old_attributes_config)) {
					$identifier_positions = array();
					$loop_count = 0;

					foreach ($old_attributes_config as $attr_key => $attr_value) {
						if (!$attr_line) {
							if (array_key_exists('static_value', $attr_value)) {
								if (strlen($attr_value['mapfrom'])) {
									$attr_line = "'" . $attr_value['prefix'] . "" . $attr_value['mapfrom'] . "" . $attr_value['suffix'] . "'";
								} else {
									$attr_line = "''";
								}
							} else {
								if ((strlen($attr_value['mapfrom'])) && (array_key_exists($attr_value['mapfrom'], $product_data))) {
									if (($attr_value['attribute'] == "URL") || ($attr_value['attribute'] == "g:link") || ($attr_value['attribute'] == "g:link_template") || ($attr_value['attribute'] == "g:image_link") || ($attr_value['attribute'] == "link") || ($attr_value['attribute'] == "Final URL") || ($attr_value['attribute'] == "SKU") || ($attr_value['attribute'] == "g:itemid")) {
										$attr_line = "'" . $attr_value['prefix'] . "" . $product_data[$attr_value['mapfrom']] . "" . $attr_value['suffix'] . "'";
									} else {
										$attr_line = "'" . $attr_value['prefix'] . "" . $product_data[$attr_value['mapfrom']] . "" . $attr_value['suffix'] . "'";
									}
								} else {
									$attr_line = "''";
								}
							}
						} else {
							if (array_key_exists('static_value', $attr_value)) {
								$attr_line .= ",'" . $attr_value['prefix'] . "" . $attr_value['mapfrom'] . "" . $attr_value['suffix'] . "'";
							} else {
								// Determine position of identifiers in CSV row
								if ($attr_value['attribute'] == "g:brand" || $attr_value['attribute'] == "g:gtin" || $attr_value['attribute'] == "g:mpn" || $attr_value['attribute'] == "g:identifier_exists") {
									$arr_pos = array($attr_value['attribute'] => $loop_count);
									$identifier_positions = array_merge($identifier_positions, $arr_pos);
								}

								if (array_key_exists($attr_value['mapfrom'], $product_data)) {
									if (is_array($product_data[$attr_value['mapfrom']])) {
										if ($attr_value['mapfrom'] == "product_tag") {
											$product_tag_str = "";
											foreach ($product_data['product_tag'] as $key => $value) {
												$product_tag_str .= ",";
												$product_tag_str .= "$value";
											}
											$product_tag_str = rtrim($product_tag_str, ",");
											$product_tag_str = ltrim($product_tag_str, ",");

											$attr_line .= ",'" . $product_tag_str . "'";
										} elseif ($attr_value['mapfrom'] == "reviews") {
											$review_str = "";
											foreach ($product_data[$attr_value['mapfrom']] as $key => $value) {
												$review_str .= "||";
												foreach ($value as $k => $v) {
													$review_str .= ":$v";
												}
											}
											$review_str = ltrim($review_str, "||");
											$review_str = rtrim($review_str, ":");
											$review_str = ltrim($review_str, ":");
											$review_str = str_replace("||:", "||", $review_str);
											$review_str .= "||";
											$attr_line .= ",'" . $review_str . "'";
										} else {
											$shipping_str = "";
											foreach ($product_data[$attr_value['mapfrom']] as $key => $value) {
												$shipping_str .= "||";
												if (is_array($value)) {
													foreach ($value as $k => $v) {
														if (preg_match('/[0-9]/', $v)) {
															$shipping_str .= ":$attr_value[prefix]" . $v . "$attr_value[suffix]";
															//	$shipping_str .= ":$attr_value[prefix]".$v."$attr_value[suffix]";
														} else {
															$shipping_str .= ":$v";
														}
													}
												}
											}
											$shipping_str = ltrim($shipping_str, "||");
											$shipping_str = rtrim($shipping_str, ":");
											$shipping_str = ltrim($shipping_str, ":");
											$shipping_str = str_replace("||:", "||", $shipping_str);

											$attr_line .= ",'" . $shipping_str . "'";
										}
									} else {
										if (isset($product_data[$attr_value['mapfrom']])) {

											if (($attr_value['attribute'] == "URL") || ($attr_value['attribute'] == "g:link") || ($attr_value['attribute'] == "g:link_template") || ($attr_value['attribute'] == "g:image_link") || ($attr_value['attribute'] == "link") || ($attr_value['attribute'] == "Final URL") || ($attr_value['attribute'] == "SKU") || ($attr_value['attribute'] == "g:itemid")) {
												if (($product_data['product_type'] == "variation") && (preg_match("/aelia_cs_currency/", $attr_value['suffix']))) {
													$attr_value['suffix'] = str_replace("?", "&", $attr_value['suffix']);
													$attr_line .= ",'" . $attr_value['prefix'] . "" . $product_data[$attr_value['mapfrom']] . "" . $attr_value['suffix'] . "'";
												} elseif (($product_data['product_type'] == "variation") && (preg_match("/currency/", $attr_value['suffix']))) {
													$attr_value['suffix'] = str_replace("?", "&", $attr_value['suffix']);
													$attr_line .= ",'" . $attr_value['prefix'] . "" . $product_data[$attr_value['mapfrom']] . "" . $attr_value['suffix'] . "'";
												} else {
													$attr_line .= ",'" . $attr_value['prefix'] . "" . $product_data[$attr_value['mapfrom']] . "" . $attr_value['suffix'] . "'";
												}
											} elseif ($product_data[$attr_value['mapfrom']] !== '') {
												$attr_line .= ",'" . $attr_value['prefix'] . " " . $product_data[$attr_value['mapfrom']] . " " . $attr_value['suffix'] . "'";
											} else {
												$attr_line .= ",''";
											}
										} else {
											$attr_line .= ",''";
										}
									}
								} else {
									$attr_line .= ",''";
								}
							}
						}
						$loop_count++;
					}

					$pieces_row = explode("','", $attr_line);
					$pieces_row = array_map('trim', $pieces_row);

					if ($project_config['fields'] == "google_shopping") {
						foreach ($identifier_positions as $id_key => $id_value) {
							if ($id_key != "g:identifier_exists") {
								if ($pieces_row[$id_value]) {
									$identifier_exists = "yes";
								}
							} else {
								$identifier_position = $id_value;
							}
						}

						if ((isset($identifier_exists)) && ($identifier_exists == "yes")) {
							$pieces_row[$id_value] = $identifier_exists;
						} else {
							if (isset($id_value)) {
								$pieces_row[$id_value] = "no";
							}
						}
					}
					$attr_line = implode("','", $pieces_row);
					$products[] = array($attr_line);
				} else {
					$attr_line = "";
					if (!empty($project_config['attributes'])) {
						foreach (array_keys($project_config['attributes']) as $attribute_key) {
							if (array_key_exists($attribute_key, $product_data)) {
								if (!$attr_line) {
									$attr_line = "'" . $product_data[$attribute_key] . "'";
								} else {
									$attr_line .= ",'" . $product_data[$attribute_key] . "'";
								}
							}
						}
					}
					$attr_line = trim($attr_line, "'");
					$products[] = array($attr_line);
				}

				/**
				 * Build an array needed for the adding Childs in the XML productfeed
				 */
				$ga = 0;
				$ca = 0;

				if (!empty($project_config['attributes'])) {
					foreach (array_keys($project_config['attributes']) as $attribute_key) {
						if (!is_numeric($attribute_key)) {
							if (!isset($old_attributes_config)) {
								if (!$xml_product) {
									$xml_product = array(
										$attribute_key => $product_data[$attribute_key]
									);
								} elseif (isset($product_data[$attribute_key])) {
									$xml_product = array_merge($xml_product, array($attribute_key => $product_data[$attribute_key]));
								}
							} else {
								foreach ($old_attributes_config as $attr_key => $attr_value) {
									// Static attribute value was set by user
									if (array_key_exists('static_value', $attr_value)) {
										if (!isset($xml_product)) {
											$xml_product = array(
												$attr_value['attribute'] => "$attr_value[prefix] " . $attr_value['mapfrom'] . " $attr_value[suffix]"
											);
										} else {
											$xml_product[$attr_value['attribute']] = "$attr_value[prefix] " . $attr_value['mapfrom'] . " $attr_value[suffix]";
										}
									} elseif ($attr_value['mapfrom'] == $attribute_key) {
										if (!isset($xml_product)) {
											$xml_product = array(
												$attr_value['attribute'] => "$attr_value[prefix] " . $product_data[$attr_value['mapfrom']] . " $attr_value[suffix]"
											);
										} elseif (key_exists($attr_value['mapfrom'], $product_data)) {
											if (is_array($product_data[$attr_value['mapfrom']])) {
												if ($attr_value['mapfrom'] == "product_tag") {
													$product_tag_str = "";

													foreach ($product_data['product_tag'] as $key => $value) {
														$product_tag_str .= ",";
														$product_tag_str .= "$value";
													}
													$product_tag_str = ltrim($product_tag_str, ",");
													$product_tag_str = rtrim($product_tag_str, ",");

													$xml_product[$attr_value['attribute']] = "$product_tag_str";
												} elseif ($attr_value['mapfrom'] == "product_tag_space") {
													$product_tag_str_space = "";

													foreach ($product_data['product_tag'] as $key => $value) {
														$product_tag_str_space .= ", ";
														$product_tag_str_space .= "$value";
													}
													$product_tag_str_space = ltrim($product_tag_str_space, " ,");
													$product_tag_str_space = rtrim($product_tag_str_space, ", ");
													$xml_product[$attr_value['attribute']] = "$product_tag_str_space";
												} elseif ($attr_value['mapfrom'] == "reviews") {
													$review_str = "";

													foreach ($product_data[$attr_value['mapfrom']] as $key => $value) {
														$review_str .= "||";

														foreach ($value as $k => $v) {
															if ($k == "review_product_id") {
																$review_str .= ":::REVIEW_PRODUCT_ID##$v";
															} elseif ($k == "reviewer_image") {
																$review_str .= ":::REVIEWER_IMAGE##$v";
															} elseif ($k == "review_ratings") {
																$review_str .= ":::REVIEW_RATINGS##$v";
															} elseif ($k == "review_id") {
																$review_str .= ":::REVIEW_ID##$v";
															} elseif ($k == "reviewer_name") {
																$review_str .= ":::REVIEWER_NAME##$v";
															} elseif ($k == "reviewer_id") {
																$review_str .= ":::REVIEWER_ID##$v";
															} elseif ($k == "review_timestamp") {
																$v = str_replace(" ", "T", $v);
																$v .= "Z";
																$review_str .= ":::REVIEW_TIMESTAMP##$v";
															} elseif ($k == "review_url") {
																$review_str .= ":::REVIEW_URL##$v";
															} elseif ($k == "title") {
																$review_str .= ":::TITLE##$v";
															} elseif ($k == "content") {
																$review_str .= ":::CONTENT##$v";
															} elseif ($k == "pros") {
																$review_str .= ":::PROS##$v";
															} elseif ($k == "cons") {
																$review_str .= ":::CONS##$v";
															} else {
																// UNKNOWN, DO NOT ADD
															}
														}
													}
													$review_str = ltrim($review_str, "||");
													$review_str = rtrim($review_str, ":");
													$review_str = ltrim($review_str, ":");
													$review_str = str_replace("||:", "||", $review_str);

													$review_str .= "||";

													$xml_product[$attr_value['attribute']] = "$review_str";
												} elseif ($attr_value['mapfrom'] == "shipping") {
													$shipping_str = "";
													foreach ($product_data[$attr_value['mapfrom']] as $key => $value) {
														$shipping_str .= "||";

														foreach ($value as $k => $v) {
															if ($k == "country") {
																$shipping_str .= ":CONVPFM_COUNTRY##$v";
															} elseif ($k == "region") {
																$shipping_str .= ":CONVPFM_REGION##$v";
															} elseif ($k == "service") {
																$shipping_str .= ":CONVPFM_SERVICE##$v";
															} elseif ($k == "postal_code") {
																$shipping_str .= ":CONVPFM_POSTAL_CODE##$v";
															} elseif ($k == "price") {
																$shipping_str .= ":CONVPFM_PRICE##$attr_value[prefix] $v $attr_value[suffix]";
															} else {
																// UNKNOWN, DO NOT ADD
															}
														}
													}
													$shipping_str = ltrim($shipping_str, "||");
													$shipping_str = rtrim($shipping_str, ":");
													$shipping_str = ltrim($shipping_str, ":");
													$shipping_str = str_replace("||:", "||", $shipping_str);

													$xml_product[$attr_value['attribute']] = "$shipping_str";
												} else {
													// Array is returned and add to feed
													$arr_return = "";
													if (isset($product_data[$attr_value['mapfrom']]) && is_array($product_data[$attr_value['mapfrom']])) {
														foreach ($product_data[$attr_value['mapfrom']] as $key => $value) {
															$arr_return .= $value . ',';
														}
													}
													$arr_return = rtrim($arr_return, ",");
													$xml_product[$attr_value['attribute']] = $arr_return;
												}
											} else {
												$ga++;
												if (array_key_exists($attr_value['attribute'], $xml_product)) {
													$ca = explode("_", $attr_value['mapfrom']);
													$ca_extra = end($ca);

													// Google Shopping Actions, allow multiple product highlights in feed
													if (($attr_value['attribute'] == "g:product_highlight") || ($attr_value['attribute'] == "g:included_destination")) {
														$xml_product[$attr_value['attribute'] . "_$ga"] = "$attr_value[prefix] " . $product_data[$attr_value['mapfrom']] . " $attr_value[suffix]";
													} elseif ($attr_value['attribute'] == "g:consumer_notice") {
														$xml_product[$attr_value['attribute'] . "_$ga"] = "$attr_value[prefix] " . $product_data[$attr_value['mapfrom']] . " $attr_value[suffix]";
													} elseif ($attr_value['attribute'] == "g:product_detail") {
														$xml_product[$attr_value['attribute'] . "_$ga"] = "$attr_value[prefix]|| " . $attr_value['mapfrom'] . "#" . $product_data[$attr_value['mapfrom']] . " $attr_value[suffix]";
													} else {
														$xml_product[$attr_value['attribute'] . "_$ca_extra"] = "$attr_value[prefix] " . $product_data[$attr_value['mapfrom']] . " $attr_value[suffix]";
													}
												} elseif (isset($product_data[$attr_value['mapfrom']])) {
													if (($attr_value['attribute'] == "URL") || ($attr_value['attribute'] == "g:link") || ($attr_value['attribute'] == "link") || ($attr_value['attribute'] == "g:link_template")) {
														if (($product_data['product_type'] == "variation") && (preg_match("/aelia_cs_currency/", $attr_value['suffix']))) {
															$attr_value['suffix'] = str_replace("?", "&", $attr_value['suffix']);
															$xml_product[$attr_value['attribute']] = "$attr_value[prefix]" . $product_data[$attr_value['mapfrom']] . "$attr_value[suffix]";
														} elseif (($product_data['product_type'] == "variation") && (preg_match("/currency/", $attr_value['suffix']))) {
															$attr_value['suffix'] = str_replace("?", "&", $attr_value['suffix']);
															$xml_product[$attr_value['attribute']] = "$attr_value[prefix]" . $product_data[$attr_value['mapfrom']] . "$attr_value[suffix]";
														} else {
															$xml_product[$attr_value['attribute']] = "$attr_value[prefix]" . $product_data[$attr_value['mapfrom']] . "$attr_value[suffix]";
														}
													} elseif (($attr_value['attribute'] == "g:image_link") || (str_contains($attr_value['attribute'], 'g:additional_image_link')) || ($attr_value['attribute'] == "image_link")) {
														$xml_product[$attr_value['attribute']] = "$attr_value[prefix]" . $product_data[$attr_value['mapfrom']] . "$attr_value[suffix]";
													} elseif (($attr_value['attribute'] == "g:id") || ($attr_value['attribute'] == "id") || ($attr_value['attribute'] == "g:item_group_id") || ($attr_value['attribute'] == "g:itemid")) {
														$xml_product[$attr_value['attribute']] = "$attr_value[prefix]" . $product_data[$attr_value['mapfrom']] . "$attr_value[suffix]";
													} elseif ($attr_value['attribute'] == "g:consumer_notice") {
														$xml_product[$attr_value['attribute']] = "$attr_value[prefix] " . $product_data[$attr_value['mapfrom']] . " $attr_value[suffix]";
													} elseif ($attr_value['attribute'] == "g:product_detail") {
														$xml_product[$attr_value['attribute']] = "$attr_value[prefix]|| " . $attr_value['mapfrom'] . "#" . $product_data[$attr_value['mapfrom']] . " $attr_value[suffix]";
													} elseif (($attr_value['attribute'] == "g:product_highlight") || ($attr_value['attribute'] == "g:included_destination")) {
														$xml_product[$attr_value['attribute'] . "_$ga"] = "$attr_value[prefix] " . $product_data[$attr_value['mapfrom']] . " $attr_value[suffix]";
													} elseif ($product_data[$attr_value['mapfrom']] !== '') {
														$xml_product[$attr_value['attribute']] = "$attr_value[prefix] " . $product_data[$attr_value['mapfrom']] . " $attr_value[suffix]";
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}

				// Do we need to do some calculation on attributes for Google Shopping
				$xml_product = $this->convpfm_calculate_value($project_config, $xml_product);

				foreach ($xml_product as $key_product => $value_product) {
					if (preg_match("/custom_attributes_attribute_/", $key_product)) {
						$pieces = explode("custom_attributes_attribute_", $key_product);
						unset($xml_product[$key_product]);
						$xml_product[$pieces[1]] = $value_product;
					} elseif (preg_match("/product_attributes_/", $key_product)) {
						$pieces = explode("product_attributes_", $key_product);
						unset($xml_product[$key_product]);
						$xml_product[$pieces[1]] = $value_product;
					}
				}

				if (!$xml_piece) {
					$xml_piece = array($xml_product);
					unset($xml_product);
				} else {
					array_push($xml_piece, $xml_product);
					unset($xml_product);
				}
				unset($product_data);
			}
		endwhile;
		wp_reset_query();

		// Add processed products to array
		//if(get_option('convpfm_duplicates')){
		//        update_option($channel_duplicates, $prevent_duplicates, 'no');
		//}

		/**
		 * Write row to CSV/TXT or XML file
		 */
		if ($project_config['fileformat'] != 'xml') {
			unset($products[0]);
			$file = $this->convpfm_create_csvtxt_feed(array_filter($products), $project_config, 'false');
		} else {
			if (is_array($xml_piece)) {
				$file = $this->convpfm_create_xml_feed(array_filter($xml_piece), $project_config, 'false');
				unset($xml_piece);
			}
			unset($products);
		}

		/**
		 * Update processing status of project
		 */
		$project_updated = $this->convpfm_project_update($project_config['project_hash'], $offset_step_size);

		/**
		 * Ready creating file, clean up our feed configuration mess now
		 */
		delete_option('convpfm_attributes_dropdown');
		delete_option('convpfm_channel_attributes');
	}

	/**
	 * Update processing statistics of batched projects 
	 */
	public function convpfm_project_update($project_hash, $offset_step_size)
	{
		$feed_config = get_option('convpfm_cron_files');
		$nr_projects = 0;
		if (!empty($feed_config)) {
			$nr_projects = count($feed_config);
		}

		// Information for debug log
		$count_variation = wp_count_posts('product_variation');
		$count_single = wp_count_posts('product');
		$published_single = $count_single->publish;
		$published_variation = $count_variation->publish;
		$published_products = $published_single + $published_variation;

		$product_numbers = array(
			"Single products" => $published_single,
			"Variation products" => $published_variation,
			"Total products" => $published_products,
			"Number projects" => count($feed_config)
		);

		$versions = array(
			"PHP" => (float)phpversion(),
			"Wordpress" => get_bloginfo('version'),
			"WooCommerce" => WC()->version,
			// "Plugin" => WOOCOMMERCESEA_PLUGIN_VERSION
		);

		// Get the sales from created product feeds
		global $wpdb;

		foreach ($feed_config as $key => $val) {

			if (isset($val['product_variations'])) {
				$count_variation = wp_count_posts('product_variation');
				$count_single = wp_count_posts('product');
				$published_single = $count_single->publish;
				$published_variation = $count_variation->publish;
				$published_products = $published_single + $published_variation;
			} else {
				$count_single = wp_count_posts('product');
				$published_products = $count_single->publish;
			}

			// User would like to see a preview of their feed
			// Retrieve only 5 products
			if (array_key_exists('preview_feed', $feed_config[$key])) {
				if ($feed_config[$key]['preview_feed'] == "on") {
					$published_products = 5;
				}
			}

			if (isset($val['project_hash']) && ($val['project_hash'] == $project_hash)) {
				$nrpr = $feed_config[$key]['nr_products_processed'];
				$nr_prods_processed = $nrpr + $offset_step_size;

				//				if(is_array($xml_piece)){
				// End of processing batched feed
				if ($nrpr >= $published_products) {

					// Set counters back to 0
					$feed_config[$key]['nr_products_processed'] = 0;
					$feed_config[$key]['nr_products'] = $published_products;

					// Set processing status on ready
					$feed_config[$key]['running'] = "ready";
					$project_data['last_updated'] = date("d M Y H:i");
					$upload_dir = wp_upload_dir();
					$base = $upload_dir['basedir'];
					$path = $base . "/conversios-product-feed/" . $feed_config[$key]['fileformat'];
					$tmp_file = $path . "/" . sanitize_file_name($feed_config[$key]['filename']) . "_tmp." . $feed_config[$key]['fileformat'];
					$new_file = $path . "/" . sanitize_file_name($feed_config[$key]['filename']) . "." . $feed_config[$key]['fileformat'];

					if (!copy($tmp_file, $new_file)) {
					} else {
						unlink($tmp_file);
					}

					$batch_project = "convpfm_batch_file_" . $feed_config[$key]['project_hash'];
					delete_option($batch_project);
					delete_option('convpfm_allow_updation');

					// Make sure this option is set to no again
					// Feed should only refresh when product details have changed
					update_option('convpfm_allow_updation', 'no');

					// Delete processed product array for preventing duplicates
					// $channel_duplicates = "convpfm_duplicates_".$project_hash;
					// delete_option($channel_duplicates);

					// In 2 minutes from now check the amount of products in the feed and update the history count
					wp_schedule_single_event(time() + 120, 'convpfm_UpdateProject_stats', array($val['project_hash']));
				} else {
					$feed_config[$key]['nr_products_processed'] = $nr_prods_processed;
					$feed_config[$key]['running'] = "processing";
					$feed_config[$key]['nr_products'] = $published_products;

					// Update current processing status and numbers
					$val['nr_products_processed'] = $nr_prods_processed;
					$val['running'] = "processing";
					$val['nr_products'] = $published_products;

					// Set new scheduled event for next batch in 2 seconds
					if ($offset_step_size < $published_products) {
						if (!wp_next_scheduled('convpfm_create_batch_event', array($feed_config[$key]['project_hash']))) {
							wp_schedule_single_event(time() + 2, 'convpfm_create_batch_event', array($feed_config[$key]['project_hash']));
							$batch_project = "convpfm_batch_file_" . $feed_config[$key]['project_hash'];
							update_option($batch_project, $val, 'no');
						}
					} else {
						// No batch is needed, already done processing all products
						// Set counters back to 0
						$feed_config[$key]['nr_products_processed'] = 0;
						$feed_config[$key]['nr_products'] = $published_products;

						$upload_dir = wp_upload_dir();
						$base = $upload_dir['basedir'];
						$path = $base . "/conversios-product-feed/" . $feed_config[$key]['fileformat'];
						$tmp_file = $path . "/" . sanitize_file_name($feed_config[$key]['filename']) . "_tmp." . $feed_config[$key]['fileformat'];
						$new_file = $path . "/" . sanitize_file_name($feed_config[$key]['filename']) . "." . $feed_config[$key]['fileformat'];

						if (!copy($tmp_file, $new_file)) {
							error_log("Copy of file failed - small file");
						} else {
							unlink($tmp_file);
						}
						// END

						// Set processing status on ready
						$feed_config[$key]['running'] = "ready";
						$project_data['last_updated'] = date("d M Y H:i");

						$batch_project = "convpfm_batch_file_" . $feed_config[$key]['project_hash'];
						delete_option($batch_project);
						delete_option('convpfm_allow_updation');

						// Make sure this option is set to no again
						// Feed should only refresh when product details have changed
						update_option('convpfm_allow_updation', 'no');

						// Delete processed product array for preventing duplicates
						// $channel_duplicates = "convpfm_duplicates_".$project_hash;
						// delete_option($channel_duplicates);

						// In 2 minutes from now check the amount of products in the feed and update the history count
						wp_schedule_single_event(time() + 120, 'convpfm_UpdateProject_stats', array($val['project_hash']));
					}
				}
			}
		}
		$nr_projects_cron = count(get_option('convpfm_cron_files'));

		/**
		 * Only update the cron_project when no new project was created during the batched run otherwise the new project will be overwritten and deleted
		 */
		if ($nr_projects == $nr_projects_cron) {
			update_option('convpfm_cron_files', $feed_config, 'no');
		}
	}

	/**
	 * Calculate the value of an attribute
	 */
	public function convpfm_calculate_value($project_config, $xml_product)
	{
		// trim whitespaces from attribute values
		$xml_product = array_map('trim', $xml_product);

		// Check for new products in the Google Shopping feed if we need to 'calculate' the identifier_exists attribute value
		if (($project_config['taxonomy'] == "google_shopping") && (isset($xml_product['g:condition'])) && (!isset($xml_product['g:identifier_exists']))) {
			$identifier_exists = "no"; // default value is no

			if (array_key_exists("g:brand", $xml_product) && ($xml_product['g:brand'] != "")) {
				// g:gtin exists and has a value
				if ((array_key_exists("g:gtin", $xml_product)) && ($xml_product['g:gtin'] != "")) {
					$identifier_exists = "yes";
					// g:mpn exists and has a value
				} elseif ((array_key_exists("g:mpn", $xml_product)) && ($xml_product['g:mpn'] != "")) {
					$identifier_exists = "yes";
					// g:brand is empty and so are g:gtin and g:mpn, so no identifier exists
				} else {
					$identifier_exists = "no";
				}
			} else {
				// g:gtin exists and has a value but brand is empty
				if ((array_key_exists("g:gtin", $xml_product)) && ($xml_product['g:gtin'] != "")) {
					$identifier_exists = "no";
					// g:mpn exists and has a value but brand is empty
				} elseif ((array_key_exists("g:mpn", $xml_product)) && ($xml_product['g:mpn'] != "")) {
					$identifier_exists = "no";
					// g:brand is empty and so are g:gtin and g:mpn, so no identifier exists
				} else {
					$identifier_exists = "no";
				}
			}
			// New policy of Google, only when the value is yes add it to the feed
			// 28 October 2019
			if (array_key_exists('calculated', $project_config['attributes'])) {
				$xml_product['g:identifier_exists'] = $identifier_exists;
			}
		}

		if ($project_config['name'] == "Mall.sk") {
			if (array_key_exists('calculated', $project_config['attributes'])) {
				$xml_product['VARIABLE_PARAMS'] = "calculated";
			}
		}
		return $xml_product;
	}

	/**
	 * Check if the channel requires unique key/field names and change when needed
	 */
	private function get_alternative_key($channel_attributes, $original_key)
	{
		$alternative_key = $original_key;

		if (!empty($channel_attributes)) {
			foreach ($channel_attributes as $k => $v) {
				foreach ($v as $key => $value) {
					if (array_key_exists("woo_suggest", $value)) {
						if ($original_key == $value['woo_suggest']) {
							$alternative_key = $value['feed_name'];
						}
					}
				}
			}
		}
		return $alternative_key;
	}

	/**
	 * Make product quantity readable
	 */
	public function clean_quantity($id, $name)
	{
		$quantity = $this->get_attribute_value($id, $name);
		if ($quantity) {
			if (is_numeric($quantity)) {
				return $quantity + 0;
			}
		}
		return "0";
	}

	/**
	 * Return product price
	 *
	 * @author Carlos Rodríguez <carlos.rodriguez@yourinspiration.it>
	 * @since 1.0.3
	 */
	public function get_product_price($product, $price)
	{
		$product_price = $this->convpfm_get_price_including_tax($product, 1, $price);
		return $product_price;
	}


	/**
	 * @param WC_Product $product
	 * @param int        $qty
	 * @param string     $price
	 *
	 * @return float|string
	 */
	public function convpfm_get_price_excluding_tax($product, $qty = 1, $price = '')
	{
		if (version_compare(WC()->version, '2.7.0', '>=')) {
			$price = wc_get_price_excluding_tax($product, array('qty' => $qty, 'price' => $price));
		} else {
			$price = $product->get_price_excluding_tax($qty, $price);
		}
		return $price;
	}

	/**
	 * @param WC_Product $product
	 * @param int        $qty
	 * @param string     $price
	 *
	 * @return float|string
	 */
	public function convpfm_get_price_including_tax($product, $qty = 1, $price = '')
	{
		if (version_compare(WC()->version, '2.7.0', '>=')) {
			$price = wc_get_price_including_tax($product, array('qty' => $qty, 'price' => $price));
		} else {
			$price = $product->get_price_including_tax($qty, $price);
		}
		return $price;
	}

	/**
	 * Make start and end sale date readable
	 */
	public function get_sale_date($id, $name)
	{
		$date = $this->get_attribute_value($id, $name);
		if ($date) {
			if (is_int($date)) {
				return date("Y-m-d", $date);
			}
		}
		return false;
	}

	/**
	 * Get product stock
	 */
	public function get_stock($id)
	{
		$status = $this->get_attribute_value($id, "_stock_status");
		if ($status) {
			if ($status == 'instock') {
				return "in stock";
			} elseif ($status == 'outofstock') {
				return "out of stock";
			}
		}
		return "out of stock";
	}

	/**
	 * Create proper format image URL's
	 */
	public function get_image_url($image_url = "")
	{
		if (!empty($image_url)) {
			if (substr(trim($image_url), 0, 4) === "http" || substr(trim($image_url), 0, 5) === "https" || substr(trim($image_url), 0, 3) === "ftp" || substr(trim($image_url), 0, 4) === "sftp") {
				return rtrim($image_url, "/");
			} else {
				$base = get_site_url();
				$image_url = $base . $image_url;
				return rtrim($image_url, "/");
			}
		}
		return $image_url;
	}

	/**
	 * Get attribute value
	 */
	public function get_attribute_value($id, $name)
	{
		if (strpos($name, 'attribute_pa') !== false) {
			$taxonomy = str_replace("attribute_", "", $name);
			$meta = get_post_meta($id, $name, true);
			$term = get_term_by('slug', $meta, $taxonomy);
			return $term->name;
		} else {
			return get_post_meta($id, $name, true);
		}
	}
	/**
	 * Execute category taxonomy mappings
	 */
	private function woocommerce_sea_mappings($project_mappings, $product_data)
	{
		$original_cat = $product_data['categories'];
		$original_cat = preg_replace('/&amp;/', '&', $original_cat);
		$original_cat = preg_replace('/&gt;/', '>', $original_cat);
		$original_cat = ltrim($original_cat, "||");

		$tmp_cat = "";
		$match = "false";

		foreach ($project_mappings as $pm_key => $pm_array) {
			// Strip slashes
			$pm_array['criteria'] = str_replace("\\", "", $pm_array['criteria']);
			$pm_array['criteria'] = str_replace("/", "", $pm_array['criteria']);
			$pm_array['criteria'] = trim($pm_array['criteria']);
			$original_cat = str_replace("\\", "", $original_cat);
			$original_cat = str_replace("/", "", $original_cat);
			$original_cat = trim($original_cat);

			// First check if there is a category mapping for this specific product
			if ((preg_match('/' . $pm_array['criteria'] . '/', $original_cat))) {
				if (!empty($pm_array['map_to_category'])) {
					$prod_id_cat = $product_data['id'];
					if ($product_data['product_type'] == "variation") {
						$prod_id_cat = $product_data['item_group_id'];
					}

					$product_cats_ids = wc_get_product_term_ids($prod_id_cat, 'product_cat');
					if (in_array($pm_array['categoryId'],  $product_cats_ids)) {
						$category_pieces = explode("-", $pm_array['map_to_category']);
						$tmp_cat = $category_pieces[0];
						$match = "true";
					}
				}
			} elseif ($pm_array['criteria'] == $original_cat) {
				$category_pieces = explode("-", $pm_array['map_to_category']);
				$tmp_cat = $category_pieces[0];
				$match = "true";
			} else {
				// Do nothing
			}
		}

		if ($match == "true") {
			if (array_key_exists('id', $product_data)) {
				$product_data['categories'] = $tmp_cat;
			}
		} else {
			// No mapping found so make google_product_category empty
			$product_data['categories'] = "";
		}

		return $product_data;
	}

	/**
	 * Execute field manipulations
	 */
	private function woocommerce_field_manipulation($field_manipulation, $product_data)
	{
		$aantal_prods = count($product_data);
		$product_type_data = $product_data['product_type'];

		if ($aantal_prods > 0) {
			foreach ($field_manipulation as $manipulation_key => $manipulation_array) {
				foreach ($manipulation_array as $ma_k => $ma_v) {

					if ($ma_k == "attribute") {
						$alter_field = $ma_v;
					} elseif ($ma_k == "rowCount") {
						$rowCount = $ma_v;
					} elseif ($ma_k == "product_type") {
						$product_type = $ma_v;
					} else {
						$becomes = $ma_v;
						$value = "";

						if ($product_type == "variable") {
							$product_type = "variation";
						}

						// Field manipulation only for the product_types that were determined
						if (($product_type == $product_type_data) || ($product_type == "all")) {
							foreach ($becomes as $bk => $bv) {
								foreach ($bv as $bkk => $bvv) {
									if ($bkk == "attribute") {
										if (isset($product_data[$bvv])) {
											// product tags and categories are arrays
											if (is_array($product_data[$bvv])) {
												foreach ($product_data[$bvv] as $ka => $va) {
													$value .= $va . " ";
												}
											} else {
												// These are numeric values, user probably want to add those together
												if ($bvv == "price" || $bvv == "shipping_price" || $bvv == "sale_price" || $bvv == "regular_price") {
													$old_format_price = $product_data[$bvv];
													$product_data[$bvv] = wc_format_decimal($product_data[$bvv]);
													settype($product_data[$bvv], "double");
													settype($value, "double");
													$value = ($value + $product_data[$bvv]);
													$product_data[$bvv] = $old_format_price;
													$value = wc_format_decimal($value, 2);
													$value = wc_format_localized_price($value);
												} else {
													$value .= $product_data[$bvv] . " ";
												}
											}
										}
									}
								}
							}
							$product_data[$alter_field] = $value;
						}
					}
				}
			}
		}
		return $product_data;
	}

	/**
	 * Wholesale removal of products and shipping methods
	 */
	private function woocommerce_wholesale_check($project_config, $product_data)
	{
		// Check if a wholesale discount has been set on categories
		$product_cats_ids = wc_get_product_term_ids($product_data['id'], 'product_cat');

		foreach ($product_cats_ids as $ky => $cat_id) {
			$cat_discount_setting = get_option("taxonomy_" . $cat_id);

			// When a wholesale category discount has been set the product needs to be removed from the product feed
			if (!empty($cat_discount_setting['wholesale_customer_wholesale_discount'])) {

				// Products can be excluded from the Wholesale category discount, those still need to make it to product feeds
				if (@metadata_exists('post', $product_data['id'], 'wwpp_ignore_cat_level_wholesale_discount')) {

					$wwpp_ignore_cat_level_wholesale_discount = get_post_meta($product_data['id'], 'wwpp_ignore_cat_level_wholesale_discount');
					if (!in_array("yes", $wwpp_ignore_cat_level_wholesale_discount)) {
						$product_data = array();
						$product_data = null;
					}
				}
			}
		}

		// Check if manual wholesale prices have been set for wholesale users only
		$wholesale_customer_have_wholesale_price = get_post_meta($product_data['id'], 'wwpp_product_wholesale_visibility_filter');

		// When manual product wholesale price has been set for wholesale users only, remove from product feed
		if (is_array($wholesale_customer_have_wholesale_price)) {
			if (!in_array("all", $wholesale_customer_have_wholesale_price)) {
				$product_data = array();
				$product_data = null;
			}
		}
		return $product_data;
	}

	/**
	 * Execute project rules
	 */
	private function woocommerce_sea_rules($project_rules2, $product_data)
	{
		$aantal_prods = count($product_data);
		if ($aantal_prods > 0) {

			foreach ($project_rules2 as $pr_key => $pr_array) {

				foreach ($product_data as $pd_key => $pd_value) {

					// Check is there is a rule on specific attributes
					if ($pd_key == $pr_array['attribute']) {
						// This is because for data manipulation the than attribute is empty
						if (!array_key_exists('than_attribute', $pr_array)) {
							$pr_array['than_attribute'] = $pd_key;
						}

						// Check if a rule has been set for Google categories
						if (!empty($product_data['categories']) && ($pr_array['than_attribute'] == "google_category") && ($product_data[$pr_array['attribute']] == $pr_array['criteria'])) {

							$pr_array['than_attribute'] = "categories";
							$category_id = explode("-", $pr_array['newvalue']);
							$pr_array['newvalue'] = $category_id[0];
							$product_data['categories'] = $pr_array['newvalue'];
						}

						// Make sure that rules on numerics are on true numerics
						if (!is_array($pd_value) && (!preg_match('/[A-Za-z]/', $pd_value))) {
							$pd_value = strtr($pd_value, ',', '.');
						}

						// Make sure the price or sale price is numeric
						if (($pr_array['attribute'] == "sale_price") || ($pr_array['attribute'] == "price")) {
							settype($pd_value, "double");
						}

						if (((is_numeric($pd_value)) && ($pr_array['than_attribute'] != "shipping"))) {
							// Rules for numeric values
							switch ($pr_array['condition']) {
								case ($pr_array['condition'] = "contains"):
									if ((preg_match('/' . $pr_array['criteria'] . '/', $pd_value))) {
										$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
									}
									break;
								case ($pr_array['condition'] = "containsnot"):
									if ((!preg_match('/' . $pr_array['criteria'] . '/', $pd_value))) {
										$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
									}
									break;
								case ($pr_array['condition'] = "="):
									if (($pd_value == $pr_array['criteria'])) {
										$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
									}
									break;
								case ($pr_array['condition'] = "!="):
									if (($pd_value != $pr_array['criteria'])) {
										$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
									}
									break;
								case ($pr_array['condition'] = ">"):
									if (($pd_value > $pr_array['criteria'])) {
										$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
									}
									break;
								case ($pr_array['condition'] = ">="):
									if (($pd_value >= $pr_array['criteria'])) {
										$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
									}
									break;
								case ($pr_array['condition'] = "<"):
									if (($pd_value < $pr_array['criteria'])) {
										$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
									}
									break;
								case ($pr_array['condition'] = "=<"):
									if (($pd_value <= $pr_array['criteria'])) {
										$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
									}
									break;
								case ($pr_array['condition'] = "empty"):
									if (empty($product_data[$pr_array['attribute']])) {
										if ((strlen($pd_value) < 1)) {
											$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
										} else {
											$product_data[$pr_array['attribute']] = $product_data[$pr_array['than_attribute']];
										}
									}
									break;
								case ($pr_array['condition'] = "multiply"):
									$pr_array['criteria'] = strtr($pr_array['criteria'], ',', '.');
									$convert_back = "false";
									$pos = strpos($pd_value, ',');
									if ($pos !== false) {
										$convert_back = "true";
									}
									$pd_value = strtr($pd_value, ',', '.');
									$newvalue = $pd_value * $pr_array['criteria'];
									$newvalue = round($newvalue, 2);
									if ($convert_back == "true") {
										$newvalue = strtr($newvalue, '.', ',');
									}

									$decimal_separator = wc_get_price_decimal_separator();
									$price_array = array("price", "regular_price", "sale_price");
									if (in_array($pr_array['attribute'], $price_array, TRUE)) {
										if ($decimal_separator == ",") {
											$newvalue = strtr($newvalue, '.', ',');
										}
									}
									$product_data[$pr_array['attribute']] = $newvalue;
									break;
								case ($pr_array['condition'] = "divide"):
									$newvalue = ($pd_value / $pr_array['criteria']);
									$newvalue = round($newvalue, 2);
									$newvalue = strtr($newvalue, '.', ',');
									$product_data[$pr_array['attribute']] = $newvalue;
									break;
								case ($pr_array['condition'] = "plus"):
									$newvalue = ($pd_value + $pr_array['criteria']);
									$product_data[$pr_array['attribute']] = $newvalue;
									break;
								case ($pr_array['condition'] = "minus"):
									$newvalue = ($pd_value - $pr_array['criteria']);
									$product_data[$pr_array['attribute']] = $newvalue;
									break;
								case ($pr_array['condition'] = "findreplace"):
									if (strpos($pd_value, $pr_array['criteria']) !== false) {
										// Make sure that a new value has been set
										if (!empty($pr_array['newvalue'])) {
											// Find and replace only work on same attribute field, otherwise create a contains rule
											if ($pr_array['attribute'] == $pr_array['than_attribute']) {
												$newvalue = str_replace($pr_array['criteria'], $pr_array['newvalue'], $pd_value);
												$product_data[$pr_array['than_attribute']] = ucfirst($newvalue);
											}
										}
									}
									break;
								default:
									break;
							}
						} elseif (is_array($pd_value)) {

							// For now only shipping details are in an array
							foreach ($pd_value as $k => $v) {
								if (is_array($v)) {
									foreach ($v as $kk => $vv) {
										// Only shipping detail rule can be on price for now
										if ($kk == "price") {
											switch ($pr_array['condition']) {
												case ($pr_array['condition'] = "contains"):
													if ((preg_match('/' . $pr_array['criteria'] . '/', $vv))) {
														$pd_value[$k]['price'] = str_replace($pr_array['criteria'], $pr_array['newvalue'], $vv);
														$product_data[$pr_array['than_attribute']] = $pd_value;
													}
													break;
												case ($pr_array['condition'] = "containsnot"):
													if ((!preg_match('/' . $pr_array['criteria'] . '/', $vv))) {
														$pd_value[$k]['price'] = $pr_array['newvalue'];
														$product_data[$pr_array['than_attribute']] = $pd_value;
													}
													break;
												case ($pr_array['condition'] = "="):
													if (($vv == $pr_array['criteria'])) {
														$pd_value[$k]['price'] = $pr_array['newvalue'];
														$product_data[$pr_array['than_attribute']] = $pd_value;
													}
													break;
												case ($pr_array['condition'] = "!="):
													if (($vv != $pr_array['criteria'])) {
														$pd_value[$k]['price'] = $pr_array['newvalue'];
														$product_data[$pr_array['than_attribute']] = $pd_value;
													}
													break;
												case ($pr_array['condition'] = ">"):
													if (($vv > $pr_array['criteria'])) {
														$pd_value[$k]['price'] = $pr_array['newvalue'];
														$product_data[$pr_array['than_attribute']] = $pd_value;
													}
													break;
												case ($pr_array['condition'] = ">="):
													if (($vv >= $pr_array['criteria'])) {
														$pd_value[$k]['price'] = $pr_array['newvalue'];
														$product_data[$pr_array['than_attribute']] = $pd_value;
													}
													break;
												case ($pr_array['condition'] = "<"):
													if (($vv < $pr_array['criteria'])) {
														$pd_value[$k]['price'] = $pr_array['newvalue'];
														$product_data[$pr_array['than_attribute']] = $pd_value;
													}
													break;
												case ($pr_array['condition'] = "=<"):
													if (($vv <= $pr_array['criteria'])) {
														$pd_value[$k]['price'] = $pr_array['newvalue'];
														$product_data[$pr_array['than_attribute']] = $pd_value;
													}
													break;
												case ($pr_array['condition'] = "empty"):
													if ((strlen($vv) < 1)) {
														$pd_value[$k]['price'] = $pr_array['newvalue'];
														$product_data[$pr_array['than_attribute']] = $pd_value;
													}
													break;
												case ($pr_array['condition'] = "multiply"):
													// Only shipping array
													if (is_array($pd_value)) {
														$pr_array['criteria'] = strtr($pr_array['criteria'], ',', '.');
														foreach ($pd_value as $ship_a_key => $shipping_arr) {
															foreach ($shipping_arr as $ship_key => $ship_value) {
																if ($ship_key == "price") {
																	$ship_pieces = explode(" ", $ship_value);
																	if (array_key_exists('1', $ship_pieces)) {
																		$pd_value = strtr($ship_pieces[1], ',', '.');
																		$newvalue = $pd_value * $pr_array['criteria'];
																		$newvalue = round($newvalue, 2);
																		$newvalue = strtr($newvalue, '.', ',');
																		$newvalue = $ship_pieces[0] . " " . $newvalue;
																		$product_data[$pr_array['than_attribute']][$ship_a_key]['price'] = $newvalue;
																	}
																}
															}
														}
													}
													break;
												default:
													break;
											}
										}
									}
								} else {
									// Rules on product tags
									foreach ($pd_value as $k => $v) {

										// Rules for string values
										if (!array_key_exists('cs', $pr_array)) {
											$v = strtolower($v);
											$pr_array['criteria'] = strtolower($pr_array['criteria']);
										}

										switch ($pr_array['condition']) {
											case ($pr_array['condition'] = "contains"):
												if ((preg_match('/' . $pr_array['criteria'] . '/', $v))) {
													$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
												}
												break;
											case ($pr_array['condition'] = "containsnot"):
												if ((!preg_match('/' . $pr_array['criteria'] . '/', $v))) {
													$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
												}
												break;
											case ($pr_array['condition'] = "="):
												if (($v == $pr_array['criteria'])) {
													$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
												}
												break;
											case ($pr_array['condition'] = "!="):
												if (($v != $pr_array['criteria'])) {
													$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
												}
												break;
											case ($pr_array['condition'] = ">"):
												if (($v > $pr_array['criteria'])) {
													$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
												}
												break;
											case ($pr_array['condition'] = ">="):
												if (($v >= $pr_array['criteria'])) {
													$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
												}
												break;
											case ($pr_array['condition'] = "<"):
												if (($v < $pr_array['criteria'])) {
													$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
												}
												break;
											case ($pr_array['condition'] = "=<"):
												if (($v <= $pr_array['criteria'])) {
													$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
												}
												break;
											case ($pr_array['condition'] = "empty"):
												if ((strlen($v) < 1)) {
													$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
												}
												break;
											case ($pr_array['condition'] = "multiply"):
												// Only shipping array
												if (is_array($v)) {
													$pr_array['criteria'] = strtr($pr_array['criteria'], ',', '.');
													foreach ($v as $ship_a_key => $shipping_arr) {
														foreach ($shipping_arr as $ship_key => $ship_value) {
															if ($ship_key == "price") {
																$ship_pieces = explode(" ", $ship_value);
																$pd_value = strtr($ship_pieces[1], ',', '.');
																$newvalue = $pd_value * $pr_array['criteria'];
																$newvalue = round($newvalue, 2);
																$newvalue = strtr($newvalue, '.', ',');
																$newvalue = $ship_pieces[0] . " " . $newvalue;
																$product_data[$pr_array['than_attribute']][$ship_a_key]['price'] = $newvalue;
															}
														}
													}
												}
												break;
											default:
												break;
										}
									}
								}
							}
						} else {

							// Rules for string values
							if (!array_key_exists('cs', $pr_array)) {
								if ($pr_array['attribute'] != "image") {
									$pd_value = strtolower($pd_value);
									$pr_array['criteria'] = strtolower($pr_array['criteria']);
								}
							}

							switch ($pr_array['condition']) {
								case ($pr_array['condition'] = "contains"):
									if ((preg_match('/' . $pr_array['criteria'] . '/', $pd_value))) {
										// Specifically for shipping price rules
										if (!empty($product_data[$pr_array['than_attribute']])) {
											if (is_array($product_data[$pr_array['than_attribute']])) {
												$arr_size = (count($product_data[$pr_array['than_attribute']]) - 1);
												for ($x = 0; $x <= $arr_size; $x++) {
													$product_data[$pr_array['than_attribute']][$x]['price'] = $pr_array['newvalue'];
												}
											} else {
												$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
											}
										} else {
											// This attribute value is empty for this product
											$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
										}
									}
									break;
								case ($pr_array['condition'] = "containsnot"):
									if ((!preg_match('/' . $pr_array['criteria'] . '/', $pd_value))) {
										// Specifically for shipping price rules
										if (isset($pr_array['than_attribute'])) {
											if (is_array($product_data[$pr_array['than_attribute']])) {
												$arr_size = (count($product_data[$pr_array['than_attribute']]) - 1);
												for ($x = 0; $x <= $arr_size; $x++) {
													$product_data[$pr_array['than_attribute']][$x]['price'] = $pr_array['newvalue'];
												}
											} else {
												$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
											}
										}
									}
									break;
								case ($pr_array['condition'] = "="):
									if (($pr_array['criteria'] == "$pd_value")) {
										// Specifically for shipping price rules
										if (isset($product_data[$pr_array['than_attribute']])) {
											if (is_array($product_data[$pr_array['than_attribute']])) {
												$arr_size = (count($product_data[$pr_array['than_attribute']]) - 1);
												for ($x = 0; $x <= $arr_size; $x++) {
													$product_data[$pr_array['than_attribute']][$x]['price'] = $pr_array['newvalue'];
												}
											} else {
												$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
											}
										}
									}
									$ship = $product_data['shipping'];
									break;
								case ($pr_array['condition'] = "!="):
									if (($pr_array['criteria'] != "$pd_value")) {
										// Specifically for shipping price rules
										if (is_array($product_data[$pr_array['than_attribute']])) {
											$arr_size = (count($product_data[$pr_array['than_attribute']]) - 1);
											for ($x = 0; $x <= $arr_size; $x++) {
												$product_data[$pr_array['than_attribute']][$x]['price'] = $pr_array['newvalue'];
											}
										} else {
											$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
										}
									}
									break;
								case ($pr_array['condition'] = ">"):
									// Use a lexical order on relational string operators
									if (($pd_value > $pr_array['criteria'])) {
										// Specifically for shipping price rules
										if (is_array($product_data[$pr_array['than_attribute']])) {
											$arr_size = (count($product_data[$pr_array['than_attribute']]) - 1);
											for ($x = 0; $x <= $arr_size; $x++) {
												$product_data[$pr_array['than_attribute']][$x]['price'] = $pr_array['newvalue'];
											}
										} else {
											$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
										}
									}
									break;
								case ($pr_array['condition'] = ">="):
									// Use a lexical order on relational string operators
									if (($pd_value >= $pr_array['criteria'])) {
										// Specifically for shipping price rules
										if (is_array($product_data[$pr_array['than_attribute']])) {
											$arr_size = (count($product_data[$pr_array['than_attribute']]) - 1);
											for ($x = 0; $x <= $arr_size; $x++) {
												$product_data[$pr_array['than_attribute']][$x]['price'] = $pr_array['newvalue'];
											}
										} else {
											$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
										}
									}
									break;
								case ($pr_array['condition'] = "<"):
									// Use a lexical order on relational string operators
									if (($pd_value < $pr_array['criteria'])) {
										// Specifically for shipping price rules
										if (isset($product_data[$pr_array['than_attribute']]) && (is_array($product_data[$pr_array['than_attribute']]))) {
											$arr_size = (count($product_data[$pr_array['than_attribute']]) - 1);
											for ($x = 0; $x <= $arr_size; $x++) {
												$product_data[$pr_array['than_attribute']][$x]['price'] = $pr_array['newvalue'];
											}
										} else {
											$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
										}
									}
									break;
								case ($pr_array['condition'] = "=<"):
									// Use a lexical order on relational string operators
									if (($pd_value <= $pr_array['criteria'])) {
										// Specifically for shipping price rules
										if (is_array($product_data[$pr_array['than_attribute']])) {
											$arr_size = (count($product_data[$pr_array['than_attribute']]) - 1);
											for ($x = 0; $x <= $arr_size; $x++) {
												$product_data[$pr_array['than_attribute']][$x]['price'] = $pr_array['newvalue'];
											}
										} else {
											$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
										}
									}
									break;

								case ($pr_array['condition'] = "empty"):
									if (empty($product_data[$pr_array['attribute']])) {
										if (empty($product_data[$pr_array['than_attribute']])) {
											$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
										} else {
											$product_data[$pr_array['attribute']] = $product_data[$pr_array['than_attribute']];
										}
									}
									break;
								case ($pr_array['condition'] = "replace"):
									$product_data[$pr_array['than_attribute']] = str_replace($pr_array['criteria'], $pr_array['newvalue'], $product_data[$pr_array['than_attribute']]);
									break;
								case ($pr_array['condition'] = "findreplace"):
									if (strpos($pd_value, $pr_array['criteria']) !== false) {
										// Make sure that a new value has been set
										if (!empty($pr_array['newvalue'])) {
											// Find and replace only work on same attribute field, otherwise create a contains rule
											if ($pr_array['attribute'] == $pr_array['than_attribute']) {
												$newvalue = str_replace($pr_array['criteria'], $pr_array['newvalue'], $pd_value);
												//$product_data[$pr_array['than_attribute']] = ucfirst($newvalue);
												$product_data[$pr_array['than_attribute']] = $newvalue;
											}
										}
									}
									break;
								default:
									break;
							}
						}
					} else {
						// When a rule has been set on an attribute that is not in product_data
						// Add the newvalue to product_data
						if (!array_key_exists($pr_array['attribute'], $product_data)) {
							if (!empty($pr_array['newvalue'])) {
								if ($pr_array['condition'] == "empty") {
									$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
								}
							} else {
								if (!empty($pr_array['than_attribute'])) {
									if (array_key_exists($pr_array['than_attribute'], $product_data)) {
										$product_data[$pr_array['attribute']] = $product_data[$pr_array['than_attribute']];
									}
								}
							}
						}
					}
				}
			}
		}
		return $product_data;
	}

	/**
	 * Function to exclude products based on individual product exclusions
	 */
	private function convpfm_exclude_individual($product_data)
	{
		$allowed = 1;

		// Check if product was already excluded from the feed
		$product_excluded = ucfirst(get_post_meta($product_data['id'], '_convpfm_exclude_product', true));

		if ($product_excluded == "Yes") {
			$allowed = 0;
		}

		if ($allowed < 1) {
			$product_data = array();
			$product_data = null;
		} else {
			return $product_data;
		}
	}

	

	/**
	 * Execute project filters (include / exclude)
	 */
	private function woocommerce_sea_filters($project_rules, $product_data)
	{
		$allowed = 1;

		// Check if product was already excluded from the feed
		$product_excluded = ucfirst(get_post_meta($product_data['id'], '_convpfm_exclude_product', true));

		if ($product_excluded == "Yes") {
			$allowed = 0;
		}

		if (array_key_exists("categories", $product_data)) {
			$product_data['google_category'] = $product_data['categories'];
		}

		foreach ($project_rules as $pr_key => $pr_array) {

			if ($pr_array['attribute'] == "categories") {
				$pr_array['attribute'] = "raw_categories";
			}

			if (!array_key_exists($pr_array['attribute'], $product_data)) {
				$product_data[$pr_array['attribute']] = "";  // Sets an empty postmeta value in place of a missing one.
			}

			foreach ($product_data as $pd_key => $pd_value) {
				// Check is there is a rule on specific attributes
				if (in_array($pd_key, $pr_array, TRUE)) {

					if ($pd_key == "price" || $pd_key == "regular_price") {
						//$pd_value = @number_format($pd_value,2);
						$pd_value = wc_format_decimal($pd_value);
					}

					if (is_numeric($pd_value)) {
						$old_value = $pd_value;
						if ($pd_key == "price" || $pd_key == "regular_price") {
							$pd_value = @number_format($pd_value, 2);
						}

						// Rules for numeric values
						switch ($pr_array['condition']) {
							case ($pr_array['condition'] = "contains"):
								if ((preg_match('/' . $pr_array['criteria'] . '/', $pd_value)) && ($pr_array['than'] == "exclude")) {
									$allowed = 0;
								} elseif ((!preg_match('/' . $pr_array['criteria'] . '/', $pd_value)) && ($pr_array['than'] == "include_only")) {
									$allowed = 0;
								}
								break;
							case ($pr_array['condition'] = "containsnot"):
								if ((!preg_match('/' . $pr_array['criteria'] . '/', $pd_value)) && ($pr_array['than'] == "exclude")) {
									$allowed = 0;
								} elseif ((preg_match('/' . $pr_array['criteria'] . '/', $pd_value)) && ($pr_array['than'] == "include_only")) {
									$allowed = 0;
								}
								break;
							case ($pr_array['condition'] = "="):
								if (($old_value == $pr_array['criteria']) && ($pr_array['than'] == "exclude")) {
									$allowed = 0;
								} elseif (($old_value != $pr_array['criteria']) && ($pr_array['than'] == "include_only")) {
									$allowed = 0;
								}
								break;
							case ($pr_array['condition'] = "!="):
								if (($old_value == $pr_array['criteria']) && ($pr_array['than'] == "exclude")) {
									if ($allowed != 0) {
										$allowed = 1;
									}
								} elseif (($old_value == $pr_array['criteria']) && ($pr_array['than'] == "include_only")) {
									$allowed = 0;
								}
								break;
							case ($pr_array['condition'] = ">"):
								if (($old_value > $pr_array['criteria']) && ($pr_array['than'] == "exclude")) {
									$allowed = 0;
								} elseif (($old_value <= $pr_array['criteria']) && ($pr_array['than'] == "include_only")) {
									$allowed = 0;
								}
								break;
							case ($pr_array['condition'] = ">="):
								if (($old_value >= $pr_array['criteria']) && ($pr_array['than'] == "exclude")) {
									$allowed = 0;
								} elseif (($old_value < $pr_array['criteria']) && ($pr_array['than'] == "include_only")) {
									$allowed = 0;
								}
								break;
							case ($pr_array['condition'] = "<"):
								if (($old_value < $pr_array['criteria']) && ($pr_array['than'] == "exclude")) {
									$allowed = 0;
								} elseif (($old_value > $pr_array['criteria']) && ($pr_array['than'] == "include_only")) {
									$allowed = 0;
								}
								break;
							case ($pr_array['condition'] = "=<"):
								if (($old_value <= $pr_array['criteria']) && ($pr_array['than'] == "exclude")) {
									$allowed = 0;
								} elseif (($old_value > $pr_array['criteria']) && ($pr_array['than'] == "include_only")) {
									$allowed = 0;
								}
								break;
							case ($pr_array['condition'] = "empty"):
								if ((strlen($pd_value) < 1) && ($pr_array['than'] == "exclude")) {
									$allowed = 0;
								} elseif ((strlen($pd_value > 0)) && ($pr_array['than'] == "include_only")) {
									$allowed = 0;
								}
								break;
							case ($pr_array['condition'] = "notempty"):
								if ((strlen($pd_value) > 1) && ($pr_array['than'] == "exclude")) {
									$allowed = 0;
								} elseif ((strlen($pd_value < 0)) && ($pr_array['than'] == "include_only")) {
									$allowed = 0;
								}
								break;
							default:
								break;
						}
					} elseif (is_array($pd_value)) {
						// This can either be a shipping or product_tag array
						if (($pr_array['attribute'] == "product_tag") || ($pr_array['attribute'] == "purchase_note")) {
							$in_tag_array = "not";

							foreach ($pd_value as $pt_key => $pt_value) {
								// Rules for string values
								if (!array_key_exists('cs', $pr_array)) {
									$pt_value = strtolower($pt_value);
									$pr_array['criteria'] = strtolower($pr_array['criteria']);
								}

								if (preg_match('/' . $pr_array['criteria'] . '/', $pt_value)) {
									$in_tag_array = "yes";
								}
							}

							if ($in_tag_array == "yes") {
								//if(in_array($pr_array['criteria'], $pd_value, TRUE)) {
								$v = $pr_array['criteria'];
								switch ($pr_array['condition']) {
									case ($pr_array['condition'] = "contains"):
										if ((preg_match('/' . $pr_array['criteria'] . '/', $v))) {
											if ($pr_array['than'] == "include_only") {
												if ($allowed != 0) {
													$allowed = 1;
												}
											} else {
												$allowed = 0;
											}
										} else {
											$allowed = 0;
										}
										break;
									case ($pr_array['condition'] = "containsnot"):
										if ((!preg_match('/' . $pr_array['criteria'] . '/', $v))) {
											if ($pr_array['than'] == "include_only") {
												if ($allowed != 0) {
													$allowed = 1;
												}
											} else {
												$allowed = 0;
											}
										} else {
											$allowed = 0;
										}
										break;
									case ($pr_array['condition'] = "="):
										if (($v == $pr_array['criteria'])) {
											if ($pr_array['than'] == "include_only") {
												if ($allowed != 0) {
													$allowed = 1;
												}
											} else {
												$allowed = 0;
											}
										} else {
											$allowed = 0;
										}
										break;
									case ($pr_array['condition'] = "!="):
										if (($v != $pr_array['criteria'])) {
											if ($pr_array['than'] == "include_only") {
												if ($allowed != 0) {
													$allowed = 1;
												}
											} else {
												$allowed = 0;
											}
										}
										break;
									case ($pr_array['condition'] = ">"):
										if (($v > $pr_array['criteria'])) {
											if ($pr_array['than'] == "include_only") {
												if ($allowed != 0) {
													$allowed = 1;
												}
											} else {
												$allowed = 0;
											}
										}
										break;
									case ($pr_array['condition'] = ">="):
										if (($v >= $pr_array['criteria'])) {
											if ($pr_array['than'] == "include_only") {
												if ($allowed != 0) {
													$allowed = 1;
												}
											} else {
												$allowed = 0;
											}
										}
										break;
									case ($pr_array['condition'] = "<"):
										if (($v < $pr_array['criteria'])) {
											if ($pr_array['than'] == "include_only") {
												if ($allowed != 0) {
													$allowed = 1;
												}
											} else {
												$allowed = 0;
											}
										}
										break;
									case ($pr_array['condition'] = "=<"):
										if (($v <= $pr_array['criteria'])) {
											if ($pr_array['than'] == "include_only") {
												if ($allowed != 0) {
													$allowed = 1;
												}
											} else {
												$allowed = 0;
											}
										}
										break;
									case ($pr_array['condition'] = "empty"):
										if (strlen($v) < 1) {
											if ($pr_array['than'] == "include_only") {
												if ($allowed != 0) {
													$allowed = 1;
												}
											} else {
												if (!empty($pt_value)) {
													$allowed = 1;
												} else {
													$allowed = 0;
												}
											}
										}
										break;
									case ($pr_array['condition'] = "notempty"):
										if (strlen($v) > 1) {
											if ($pr_array['than'] == "include_only") {
												if ($allowed != 0) {
													$allowed = 1;
												}
											} else {
												if (!empty($pt_value)) {
													$allowed = 1;
												} else {
													$allowed = 0;
												}
											}
										}
										break;
									default:
										break;
								}
							} else {

								switch ($pr_array['condition']) {
									case ($pr_array['condition'] = "contains"):
										if ($pr_array['than'] == "include_only") {
											$allowed = 0;
										} else {
											if ($allowed != 0) {
												$allowed = 1;
											}
										}
										break;
									case ($pr_array['condition'] = "containsnot"):
										if ($pr_array['than'] == "include_only") {
											if ($allowed != 0) {
												$allowed = 1;
											}
										} else {
											$allowed = 0;
										}
										break;
									case ($pr_array['condition'] = "="):
										if ($pr_array['than'] == "include_only") {
											$allowed = 0;
										} else {
											if ($allowed != 0) {
												$allowed = 1;
											}
										}
										break;
									case ($pr_array['condition'] = "!="):
										if ($pr_array['than'] == "include_only") {
											if ($allowed != 0) {
												$allowed = 1;
											}
										} else {
											$allowed = 0;
										}
										break;
									case ($pr_array['condition'] = ">"):
										if ($pr_array['than'] == "include_only") {
											$allowed = 0;
										} else {
											$allowed = 0;
										}
										break;
									case ($pr_array['condition'] = ">="):
										if ($pr_array['than'] == "include_only") {
											$allowed = 0;
										} else {
											$allowed = 0;
										}
										break;
									case ($pr_array['condition'] = "<"):
										if ($pr_array['than'] == "include_only") {
											$allowed = 0;
										} else {
											$allowed = 0;
										}
										break;
									case ($pr_array['condition'] = "=<"):
										if ($pr_array['than'] == "include_only") {
											$allowed = 0;
										} else {
											$allowed = 0;
										}
										break;
									case ($pr_array['condition'] = "empty"):
										if ($pr_array['than'] == "include_only") {
											if ($allowed != 0) {
												$allowed = 1;
											}
										} else {
											$allowed = 0;
										}
										break;
									case ($pr_array['condition'] = "notempty"):
										if ($pr_array['than'] == "include_only") {
											if ($allowed != 0) {
												$allowed = 0;
											}
										} else {
											$allowed = 1;
										}
										break;
									default:
										break;
								}
							}
						} else {
							// For now only shipping details are in an array
							foreach ($pd_value as $k => $v) {
								foreach ($v as $kk => $vv) {
									// Only shipping detail rule can be on price for now
									if ($kk == "price") {
										switch ($pr_array['condition']) {
											case ($pr_array['condition'] = "contains"):
												if ((preg_match('/' . $pr_array['criteria'] . '/', $vv))) {
													$allowed = 0;
												}
												break;
											case ($pr_array['condition'] = "containsnot"):
												if ((!preg_match('/' . $pr_array['criteria'] . '/', $vv))) {
													$allowed = 0;
												}
												break;
											case ($pr_array['condition'] = "="):
												if (($vv == $pr_array['criteria'])) {
													$allowed = 0;
												}
												break;
											case ($pr_array['condition'] = "!="):
												if (($vv != $pr_array['criteria'])) {
													$allowed = 0;
												}
												break;
											case ($pr_array['condition'] = ">"):
												if (($vv > $pr_array['criteria'])) {
													$allowed = 0;
												}
												break;
											case ($pr_array['condition'] = ">="):
												if (($vv >= $pr_array['criteria'])) {
													$allowed = 0;
												}
												break;
											case ($pr_array['condition'] = "<"):
												if (($vv < $pr_array['criteria'])) {
													$allowed = 0;
												}
												break;
											case ($pr_array['condition'] = "=<"):
												if (($vv <= $pr_array['criteria'])) {
													$allowed = 0;
												}
												break;
											case ($pr_array['condition'] = "empty"):
												if (strlen($vv) < 1) {
													$allowed = 0;
												}
												break;
											case ($pr_array['condition'] = "notempty"):
												if (strlen($vv) > 1) {
													$allowed = 0;
												}
												break;
											default:
												break;
										}
									} else {
										// These are filters on reviews
										switch ($pr_array['condition']) {
											case ($pr_array['condition'] = "contains"):
												if ((preg_match('/' . $pr_array['criteria'] . '/', $vv))) {
													if ($pr_array['than'] == "include_only") {
														$allowed = 1;
													} else {
														$allowed = 0;
													}
												}
												break;
											case ($pr_array['condition'] = "containsnot"):
												if ((!preg_match('/' . $pr_array['criteria'] . '/', $vv))) {
													if ($pr_array['than'] == "include_only") {
														$allowed = 0;
													} else {
														$allowed = 1;
													}
												}
												break;
											case ($pr_array['condition'] = "="):
												if (($vv == $pr_array['criteria'])) {
													if ($pr_array['than'] == "include_only") {
														$allowed = 1;
													} else {
														$allowed = 0;
													}
												}
												break;
											case ($pr_array['condition'] = "!="):
												if (($vv != $pr_array['criteria'])) {
													if ($pr_array['than'] == "include_only") {
														$allowed = 0;
													} else {
														$allowed = 1;
													}
												}
												break;
											default:
												break;
										}
									}
								}
							}
						}
					} else {
						// Filters for string values
						// If case-sensitve is off than lowercase both the criteria and attribute value
						if (array_key_exists('cs', $pr_array)) {
							if ($pr_array['cs'] != "on") {
								$pd_value = strtolower($pd_value);
								$pr_array['criteria'] = strtolower($pr_array['criteria']);
							}
						}

						$pos = strpos($pd_value, '&amp;');
						$pos_slash_back = strpos($pr_array['criteria'], '\\');
						$pos_slash_forward = strpos($pr_array['criteria'], '/');

						if ($pos !== false) {
							$pd_value = str_replace("&amp;", "&", $pd_value);
						}
						if ($pos_slash_back !== false) {
							$pr_array['criteria'] = str_replace("\\", "", $pr_array['criteria']);
						}
						if ($pos_slash_forward !== false) {
							$pr_array['criteria'] = str_replace("/", "", $pr_array['criteria']);
							$pd_value = str_replace("/", "", $pd_value);
						}

						//						if(!empty($pd_value)){
						switch ($pr_array['condition']) {
							case ($pr_array['condition'] = "contains"):
								if ((preg_match('/' . $pr_array['criteria'] . '/', $pd_value)) && ($pr_array['than'] == "exclude")) {
									$allowed = 0;
								} elseif ((!preg_match('/' . $pr_array['criteria'] . '/', $pd_value)) && ($pr_array['than'] == "include_only")) {
									$allowed = 0;
								} elseif ((preg_match('/' . $pr_array['criteria'] . '/', $pd_value)) && ($pr_array['than'] == "include_only")) {
									if ($allowed != 0) {
										$allowed = 1;
									}
								}
								break;
							case ($pr_array['condition'] = "containsnot"):
								if ((!preg_match('/' . $pr_array['criteria'] . '/', $pd_value)) && ($pr_array['than'] == "exclude")) {
									$allowed = 0;
								} elseif ((preg_match('/' . $pr_array['criteria'] . '/', $pd_value)) && ($pr_array['than'] == "include_only")) {
									$allowed = 0;
								}
								break;
							case ($pr_array['condition'] = "="):
								if (($pr_array['criteria'] == "$pd_value") && ($pr_array['than'] == "exclude")) {
									$allowed = 0;
								} elseif (($pr_array['criteria'] != "$pd_value") && ($pr_array['than'] == "include_only")) {
									$found = strpos($pd_value, $pr_array['criteria']);
									if ($found !== false) {
										//for category mapping check if its an array
										if ($pr_array['attribute'] == "raw_categories") {
											$raw_cats_arr = explode("||", $pd_value);
											if (is_array($raw_cats_arr)) {
												if (in_array($pr_array['criteria'], $raw_cats_arr, TRUE)) {
													if ($allowed != 0) {
														$allowed = 1;
													}
												} else {
													$allowed = 0;
												}
											}
										} else {
											if ($allowed != 0) {
												$allowed = 1;
											}
										}
									} else {
										$allowed = 0;
									}
								} elseif (($pr_array['criteria'] == "$pd_value") && ($pr_array['than'] == "include_only")) {
									if ($allowed != 0) {
										$allowed = 1;
									}
								} elseif ((preg_match('/' . $pr_array['criteria'] . '/', $pd_value)) && ($pr_array['than'] == "exclude")) {
									//                                                                      	$allowed = 0;
								} elseif ((preg_match('/' . $pr_array['criteria'] . '/', $pd_value)) && ($pr_array['than'] == "include_only")) {
									$allowed = 1;
								} else {
									//										$allowed = 1; // Change made on February 24th 2021
								}
								break;
							case ($pr_array['condition'] = "!="):
								if (($pr_array['criteria'] == "$pd_value") && ($pr_array['than'] == "exclude")) {
									if ($allowed != 0) {
										$allowed = 1;
									}
								} elseif (($pr_array['criteria'] == "$pd_value") && ($pr_array['than'] == "include_only")) {
									$allowed = 0;
								} elseif (($pr_array['criteria'] != "$pd_value") && ($pr_array['than'] == "exclude")) {
									$allowed = 0;
								}
								break;
							case ($pr_array['condition'] = ">"):
								// Use a lexical order on relational string operators
								if (($pd_value > $pr_array['criteria']) && ($pr_array['than'] == "exclude")) {
									$allowed = 0;
								} elseif (($pd_value < $pr_array['criteria']) && ($pr_array['than'] == "include_only")) {
									$allowed = 0;
								}
								break;
							case ($pr_array['condition'] = ">="):
								// Use a lexical order on relational string operators
								if (($pd_value >= $pr_array['criteria']) && ($pr_array['than'] == "exclude")) {
									$allowed = 0;
								} elseif (($pd_value < $pr_array['criteria']) && ($pr_array['than'] == "include_only")) {
									$allowed = 0;
								}
								break;
							case ($pr_array['condition'] = "<"):
								// Use a lexical order on relational string operators
								if (($pd_value < $pr_array['criteria']) && ($pr_array['than'] == "exclude")) {
									$allowed = 0;
								} elseif (($pd_value > $pr_array['criteria']) && ($pr_array['than'] == "include_only")) {
									$allowed = 0;
								}
								break;
							case ($pr_array['condition'] = "=<"):
								// Use a lexical order on relational string operators
								if (($pd_value <= $pr_array['criteria']) && ($pr_array['than'] == "exclude")) {
									$allowed = 0;
								} elseif (($pd_value > $pr_array['criteria']) && ($pr_array['than'] == "include_only")) {
									$allowed = 0;
								}
								break;
							case ($pr_array['condition'] = "empty"):
								if ((strlen($pd_value) < 1) && ($pr_array['than'] == "exclude")) {
									$allowed = 0;
								} elseif ((strlen($pd_value) > 0) && ($pr_array['than'] == "exclude")) {
									if ($allowed != 0) {
										$allowed = 1;
									}
								} elseif ((strlen($pd_value) > 0) && ($pr_array['than'] == "include_only")) {
									$allowed = 0;
								}
								break;
							case ($pr_array['condition'] = "notempty"):
								if ((strlen($pd_value) > 0) && ($pr_array['than'] == "exclude")) {
									$allowed = 0;
								} elseif ((strlen($pd_value) < 1) && ($pr_array['than'] == "exclude")) {
									if ($allowed != 0) {
										$allowed = 1;
									}
								} elseif ((strlen($pd_value) < 1) && ($pr_array['than'] == "include_only")) {
									$allowed = 0;
								}
								break;
							default:
								break;
						}
						//						}
					}
				}
			}
		}

		if ($allowed < 1) {
			$product_data = array();
			$product_data = null;
		} else {
			return $product_data;
		}
	}
}
