<?php

/**
 * @since      4.0.2
 * Description: Conversios Onboarding page, It's call while active the plugin
 */
if (class_exists('Convpfm_Header') === FALSE) {
	class Convpfm_Header extends Convpfm_TVC_Admin_Helper
	{
		// Site Url.
		protected $site_url;

		// Conversios site Url.
		protected $conversios_site_url;

		// Subcription Data.
		protected $subscription_data;

		// Plan id.
		protected $plan_id = 21;

		/** Contruct for Hook */
		public function __construct()
		{
			$this->site_url = "admin.php?page=";
			$this->conversios_site_url = $this->get_conversios_site_url();
			$this->subscription_data = $this->get_user_subscription_data();
			if (isset($this->subscription_data->plan_id) === TRUE && !in_array($this->subscription_data->plan_id, array("21"))) {
				$this->plan_id = $this->subscription_data->plan_id;
			}
			add_action('add_convpfm_header', [$this, 'header_menu']);
		} //end __construct()


		/* add active tab class */
		protected function is_active_menu($page = "")
		{
			if ($page !== "" && isset($_GET['page']) === TRUE && sanitize_text_field($_GET['page']) === $page) {
				return "dark";
			}

			return "secondary";
		}
		public function conversios_menu_list()
		{
			$conversios_menu_arr  = array();
			if (is_plugin_active_for_network('woocommerce/woocommerce.php') || in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
				if (!function_exists('is_plugin_active_for_network')) {
					require_once(ABSPATH . '/wp-admin/includes/woocommerce.php');
				}
				
				$conversios_menu_arr  = array(
					"conversiospfm" => array(
						"page" => "conversiospfm",
						"title" => "Channel Configuration"
					),
					"convpfm-google-shopping-feed" => array(
						"page" => "convpfm-google-shopping-feed&tab=feed_list",
						"title" => "Manage Feeds (API)"
					),
					"convpfm-generate-file" => array(
						"page" => "convpfm-generate-file",
						"title" => "Create Feeds (File)"
					),
					"convpfm-manage-file" => array(
						"page" => "convpfm-manage-file",
						"title" => "Manage File"
					),
					"convpfm-pmax" => array(
						"page" => "convpfm-pmax",
						"title" => "Manage Campaigns"
					),
				);
			} 


			return apply_filters('conversios_menu_list', $conversios_menu_arr, $conversios_menu_arr);
		}

		/**
		 * header menu section
		 *
		 * @since    4.1.4
		 */
		public function header_menu()
		{
			$menu_list = $this->conversios_menu_list();
			if (!empty($menu_list)) {
			?>
				<header id="conversioshead" class="border-bottom bg-white">
					<div class="container-fluid col-12 p-0">
						<nav class="navbar navbar-expand-lg navbar-light bg-light ps-4" style="height:40px;">
							<div class="container-fluid fixedcontainer_conversios">
								<a class="navbar-brand link-dark fs-16 fw-400">
									<img style="width: 150px;" src="<?php echo esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/logo.png'); ?>" />
								</a>
								<div class="collapse navbar-collapse d-felx" id="navbarSupportedContent" style="flex-grow: 0">
									<ul class="navbar-nav me-auto mb-lg-0">
										<?php
										foreach ($menu_list as $key => $value) {
											if (isset($value['title']) && $value['title']) {
												$is_active = $this->is_active_menu($key);
												$active = $is_active != 'secondary' ? 'rich-blue' : '';
												$menu_url = "#";
												if (isset($value['page']) && $value['page'] != "#") {
													$menu_url = $this->site_url . $value['page'];
												}
												$is_parent_menu = "";
												$is_parent_menu_link = "";
												if (isset($value['sub_menus']) && !empty($value['sub_menus'])) {
													$is_parent_menu = "dropdown";
												}
										?>
												<li class="nav-item fs-14 mt-1 fw-400 <?php echo esc_attr($active); ?> <?php echo esc_attr($is_parent_menu); ?>">
													<?php if ($is_parent_menu == "") { ?>
														<a class="nav-link text-<?php esc_attr($is_active); ?> " aria-current="page" href="<?php echo esc_url_raw($menu_url); ?>">
															<?php echo esc_attr($value['title']); ?>
														</a>
													<?php } else { ?>
														<a class="nav-link dropdown-toggle text-<?php esc_attr($is_active); ?> " id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
															<?php echo esc_attr($value['title']); ?>
														</a>
														<ul class="dropdown-menu fs-14 fw-400" aria-labelledby="navbarDropdown">
															<?php
															foreach ($value['sub_menus'] as $sub_key => $sub_value) {
																$sub_menu_url = $this->site_url . $sub_value['page'];
															?>
																<li>
																	<a class="dropdown-item" href="<?php echo esc_url_raw($sub_menu_url); ?>">
																		<?php echo esc_attr($sub_value['title']); ?>
																	</a>
																</li>
															<?php }
															?>
														</ul>
													<?php } ?>

												</li>
										<?php
											}
										} ?>
										<!-- <li class="nav-item fs-14 mt-1 fw-400" >
											<a target="_blank" class="nav-link" href="<?php //echo esc_url_raw('https://www.conversios.io/docs-category/woocommerce-2/?utm_source=in_app&utm_medium=top_menu&utm_campaign=help_center'); ?>">
												<?php //esc_html_e("Contact Support", "product-feed-manager-for-woocommerce"); ?>
											</a>
										</li> -->
									</ul>
									<!-- <div class="d-flex"> -->
										<?php
										//$plan_name = esc_html__("Free Plan", "product-feed-manager-for-woocommerce");
										//$type = 'warning';
										?>
										
										<!-- <button type="button" class="btn btn-<?php //echo esc_attr($type) ?> rounded-pill fs-12 fw-400 me-4 px-2 py-0" data-bs-toggle="modal" data-bs-target="#convLicenceInfoMod">
											<?php //echo esc_attr($plan_name) ?>
										</button> -->
									<!-- </div> -->
								</div>
							</div>
						</nav>
					</div>
				</header>
				<div id="loadingbar_blue_header" class="progress-materializecss d-none ps-2 pe-2" style="width:100%">
					<div class="indeterminate"></div>
				</div>

			<?php
			}
		}
	}
}
new Convpfm_Header();
