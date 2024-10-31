<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       
 * @since      1.0.0
 *
 * Woo Order Reports
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
if (!class_exists('Convpfm_Reports_Helper')) {
	class Convpfm_Reports_Helper
	{
		
	}
}
new Convpfm_Reports_Helper();