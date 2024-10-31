<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       
 * @since      1.0.0
 *
 * Conversios Dashboard
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
if (!class_exists('Convpfm_Dashboard_Helper')) {
	class Convpfm_Dashboard_Helper
	{
		protected $Convpfm_TVC_Admin_Helper;
		protected $CustomApi;
		protected $apiDomain;
		protected $token;
		public function __construct()
		{
			$this->Convpfm_TVC_Admin_Helper = new Convpfm_TVC_Admin_Helper();
			$this->CustomApi = new Convpfm_CustomApi();
		}

		protected function admin_safe_ajax_call($nonce, $registered_nonce_name)
		{
			// only return results when the user is an admin with manage options
			if (is_admin() && wp_verify_nonce($nonce, $registered_nonce_name)) {
				return true;
			} else {
				return false;
			}
		}
	 }
}
new Convpfm_Dashboard_Helper();
