<?php

/**
 * Fired during plugin deactivation
 *
 * @link       test.com
 * @since      1.0.0
 *
 * @package    Convpfm_Enhanced_Ecommerce_Google_Analytics
 * @subpackage Convpfm_Enhanced_Ecommerce_Google_Analytics/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Convpfm_Enhanced_Ecommerce_Google_Analytics
 * @subpackage Convpfm_Enhanced_Ecommerce_Google_Analytics/includes
 * @author     Tatvic
 */
class Convpfm_Enhanced_Ecommerce_Google_Analytics_Deactivator
{

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate()
	{
		if (!current_user_can('activate_plugins')) {
			return;
		}
		$Convpfm_TVC_Admin_Helper = new Convpfm_TVC_Admin_Helper();
		$Convpfm_TVC_Admin_Helper->update_app_status("0");
		$Convpfm_TVC_Admin_Helper->app_activity_detail("deactivate");
		wp_clear_scheduled_hook( 'convpfm_cron_hook' );
	}
}
