<?php
require plugin_dir_path(__FILE__)  . 'classes/class-get-products.php';
require plugin_dir_path(__FILE__)  . 'classes/class-attributes.php';
require plugin_dir_path(__FILE__)  . 'classes/class-update-channel.php';
require plugin_dir_path(__FILE__)  . 'classes/class-cron.php';
require plugin_dir_path( __FILE__ ) . 'classes/class-caching.php';

add_action('admin_enqueue_scripts', 'convpfm_styles');
add_action('wp_ajax_convpfm_channel', 'convpfm_channel');
add_action('admin_enqueue_scripts', 'enqueue_convpfm_scripts');
add_action('wp_ajax_convpfm_ajax', 'convpfm_ajax');
add_action('wp_ajax_convpfm_fieldmapping_dropdown', 'convpfm_fieldmapping_dropdown');


function enqueue_convpfm_scripts($hook)
{

    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-ui-dialog');
    wp_enqueue_script('jquery-ui-calender');
    wp_enqueue_script('jquery-ui-datepicker');

    wp_register_script('typeahead-js', plugin_dir_url(__FILE__) . 'js/Convpfm_typeahead.js');
    wp_enqueue_script('typeahead-js');

    wp_register_script('convpfm_autocomplete-js', plugin_dir_url(__FILE__) . 'js/Convpfm_autocomplete.js');
    wp_enqueue_script('convpfm_autocomplete-js');

    wp_register_script('convpfm_rules-js', plugin_dir_url(__FILE__) . 'js/Convpfm_rules.js');
    wp_enqueue_script('convpfm_rules-js');

    wp_enqueue_script('convpfm-field-mapping-js', plugin_dir_url(__FILE__) . 'js/Convpfm_field_mapping.js', array('jquery', 'jquery-ui-dialog'));
    wp_enqueue_script('convpfm-field-mapping-js');

    wp_register_script('convpfm_channel-js', plugin_dir_url(__FILE__) . 'js/Convpfm_channel.js');
    wp_enqueue_script('convpfm_channel-js');

    wp_register_script('convpfm_manage-js', plugin_dir_url(__FILE__) . 'js/Convpfm_manage.js');
    wp_enqueue_script('convpfm_manage-js');
    
}

function convpfm_styles($hook)
{
    $fullName = (plugin_basename(__FILE__));
    // $dir = str_replace('/includes/setup/GenerateFile/activator.php', '', $fullName);
    wp_register_style('convpfm_admin-css', plugins_url('/css/generatefile_admin.css', __FILE__));
    wp_enqueue_style('convpfm_admin-css');

    wp_register_style('convpfm_jquery_ui-css', plugins_url('/css/jquery-ui.css', __FILE__));
    wp_enqueue_style('convpfm_jquery_ui-css');

    wp_register_style('convpfm_jquery_typeahead-css', plugins_url('/css/jquery.typeahead.css', __FILE__));
    wp_enqueue_style('convpfm_jquery_typeahead-css');
    // wp_enqueue_style('convpfm-conversios-header-css', esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL.'/admin/css/header.css'), array(), esc_attr($this->version), 'all' );

}
function convpfm_channel()
{
    if (!wp_verify_nonce($_REQUEST['security'], 'convpfm_ajax_nonce')) {
        wp_send_json_error(__('Nonce verification failed', 'product-feed-manager-for-woocommerce'));
    }

    $country     = sanitize_text_field($_POST['country']);
    $channel_obj = new Convpfm_Attributes();
    $data        = $channel_obj->get_channels($country);

    echo json_encode($data);
    wp_die();
}

function convpfm_fieldmapping_dropdown()
{
    if (!wp_verify_nonce($_REQUEST['security'], 'convpfm_ajax_nonce')) {
        wp_send_json_error(__('Nonce verification failed', 'product-feed-manager-for-woocommerce'));
    }
    $user          = wp_get_current_user();
    $allowed_roles = array('administrator');

    if (array_intersect($allowed_roles, $user->roles)) {
        $channel_hash = sanitize_text_field($_POST['channel_hash']);
        $rowCount     = absint(esc_attr(sanitize_text_field($_POST['rowCount'])));
        $channel_data = Convpfm_UpdateProject::get_channel_data($channel_hash);

        require plugin_dir_path(__FILE__) . '/classes/channels/class-' . $channel_data['fields'] . '.php';
        $obj           = 'Convpfm_' . $channel_data['fields'];
        $fields_obj    = new $obj();
        $attributes    = $fields_obj->get_channel_attributes();
        $field_options = '<option selected></option>';

        foreach ($attributes as $key => $value) {
            $field_options .= '<option></option>';
            $field_options .= "<optgroup label='$key'><strong>$key</strong>";
            foreach ($value as $k => $v) {
                $field_options .= "<option value='$v[feed_name]'>$k ($v[name])</option>";
            }
        }

        $attributes_obj     = new Convpfm_Attributes();
        $attribute_dropdown = $attributes_obj->get_product_attributes();
        $attribute_options  = '<option selected></option>';

        foreach ($attribute_dropdown as $drop_key => $drop_value) {
            $attribute_options .= "<option value='$drop_key'>$drop_value</option>";
        }

        $data = array(
            'field_options'     => $field_options,
            'attribute_options' => $attribute_options,
        );

        echo json_encode($data);
        wp_die();
    }
}

function convpfm_ajax()
{
    check_ajax_referer('convpfm_ajax_nonce', 'security');

    $user          = wp_get_current_user();
    $allowed_roles = array('administrator');

    if (array_intersect($allowed_roles, $user->roles)) {
        $rowCount = absint(esc_attr(sanitize_text_field($_POST['rowCount'])));

        $attributes_dropdown = get_option('convpfm_attributes_dropdown');
        if (!is_array($attributes_dropdown)) {
            $attributes_obj      = new Convpfm_Attributes();
            $attributes_dropdown = $attributes_obj->get_filter_product_attributes();
            update_option('convpfm_attributes_dropdown', $attributes_dropdown, 'yes');
        }

        $data = array(
            'rowCount' => $rowCount,
            'dropdown' => $attributes_dropdown,
        );

        echo json_encode($data);
        wp_die();
    }
}
// }



$tvc_admin_helper = new Convpfm_TVC_Admin_Helper();
add_action('wp_ajax_convpfm_check_processing', 'convpfm_check_processing');
add_action('wp_ajax_convpfm_project_cancel', 'convpfm_project_cancel');
add_action('wp_ajax_convpfm_project_processing_status', 'convpfm_project_processing_status');
add_action('wp_ajax_convpfm_project_copy', 'convpfm_project_copy');
add_action('wp_ajax_convpfm_project_refresh', 'convpfm_project_refresh');
add_action('wp_ajax_convpfm_project_status', 'convpfm_project_status');
add_action('wp_ajax_convpfm_project_delete', 'convpfm_project_delete');
add_action('convpfm_cron_hook', 'convpfm_create_all_feeds');
add_action('convpfm_UpdateProject_stats', 'convpfm_UpdateProject_history', 1, 1);
add_action('convpfm_create_batch_event', array($tvc_admin_helper, 'convpfm_continue_batch'), 10, 1);
function convpfm_project_delete()
{
    check_ajax_referer('convpfm_ajax_nonce', 'security');

    $user          = wp_get_current_user();
    $allowed_roles = array('administrator');

    if (array_intersect($allowed_roles, $user->roles)) {

        $project_hash = sanitize_text_field($_POST['project_hash']);
        $feed_config  = get_option('convpfm_cron_files');
        $found        = false;

        foreach ($feed_config as $key => $val) {
            if (isset($val['project_hash']) && ($val['project_hash'] == $project_hash)) {
                $found      = true;
                $found_key  = $key;
                $upload_dir = wp_upload_dir();
                $base       = $upload_dir['basedir'];
                $path       = $base . '/conversios-product-feed/' . $val['fileformat'];
                $file       = $path . '/' . sanitize_file_name($val['filename']) . '.' . $val['fileformat'];
            }
        }

        if ($found == 'true') {
            // Remove project from project array.
            unset($feed_config[$found_key]);

            // Update cron.
            update_option('convpfm_cron_files', $feed_config, 'no');

            // Remove project file.
            @unlink($file);
        }
    }
}
function convpfm_check_processing()
{
    check_ajax_referer('convpfm_ajax_nonce', 'security');

    $user          = wp_get_current_user();
    $allowed_roles = array('administrator', 'editor', 'author');

    if (array_intersect($allowed_roles, $user->roles)) {
        $processing  = 'false';
        $feed_config = get_option('convpfm_cron_files');
        $found       = false;

        foreach ($feed_config as $key => $val) {
            if (array_key_exists('running', $val)) {
                if (in_array($val['running'], array('true', 'processing', 'stopped', 'not run yet'))) {
                    $processing = 'true';
                }
            }
        }

        $data = array(
            'processing' => $processing,
        );

        echo json_encode($data);
        wp_die();
    }
}
function convpfm_project_cancel()
{
    check_ajax_referer('convpfm_ajax_nonce', 'security');

    $user          = wp_get_current_user();
    $allowed_roles = array('administrator');

    if (array_intersect($allowed_roles, $user->roles)) {

        $project_hash = sanitize_text_field($_POST['project_hash']);
        $feed_config  = get_option('convpfm_cron_files');

        foreach ($feed_config as $key => $val) {
            if ($val['project_hash'] == $project_hash) {

                $batch_project = 'convpfm_batch_file_' . $project_hash;
                delete_option($batch_project);

                $feed_config[$key]['nr_products_processed'] = 0;

                // Set processing status on ready.
                $feed_config[$key]['running']      = 'stopped';
                $feed_config[$key]['last_updated'] = date('d M Y H:i');

                // Delete processed product array for preventing duplicates.
                delete_option('convpfm_duplicates');

                // In 1 minute from now check the amount of products in the feed and update the history count.
                wp_schedule_single_event(time() + 60, 'convpfm_UpdateProject_stats', array($val['project_hash']));
            }
        }

        update_option('convpfm_cron_files', $feed_config, 'no');
    }
}
function convpfm_project_processing_status()
{
    check_ajax_referer('convpfm_ajax_nonce', 'security');

    $user          = wp_get_current_user();
    $allowed_roles = array('administrator', 'editor', 'author');

    if (array_intersect($allowed_roles, $user->roles)) {

        $project_hash = sanitize_text_field($_POST['project_hash']);
        $feed_config  = get_option('convpfm_cron_files');
        $proc_perc    = 0;

        foreach ($feed_config as $key => $val) {
            if (isset($val['project_hash']) && ($val['project_hash'] === $project_hash)) {
                $this_feed = $val;
            }
        }

        if ($this_feed['running'] == 'ready') {
            $proc_perc = 100;
        } elseif ($this_feed['running'] == 'not run yet') {
            $proc_perc = 999;
        } elseif (isset($this_feed['nr_products'])) {
            if ($this_feed['nr_products'] > 0) {
                $proc_perc = round(($this_feed['nr_products_processed'] / $this_feed['nr_products']) * 100);
            }
        }

        $data = array(
            'project_hash' => $project_hash,
            'running'      => $this_feed['running'],
            'proc_perc'    => $proc_perc,
        );

        echo json_encode($data);
        wp_die();
    }
}
function convpfm_project_copy()
{
    check_ajax_referer('convpfm_ajax_nonce', 'security');

    $user          = wp_get_current_user();
    $allowed_roles = array('administrator');

    if (array_intersect($allowed_roles, $user->roles)) {

        $project_hash  = sanitize_text_field($_POST['project_hash']);
        $feed_config   = get_option('convpfm_cron_files');
        $max_key       = max(array_keys($feed_config));
        $add_project   = array();
        $upload_dir    = wp_upload_dir();
        $external_base = $upload_dir['baseurl'];

        foreach ($feed_config as $key => $val) {
            if ($val['project_hash'] == $project_hash) {
                $val['projectname'] = 'Copy ' . $val['projectname'];

                // New code to create the project hash so dependency on openSSL is removed.
                $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $pieces   = array();
                $length   = 32;
                $max      = mb_strlen($keyspace, '8bit') - 1;

                for ($i = 0; $i < $length; ++$i) {
                    $pieces[] = $keyspace[random_int(0, $max)];
                }

                $val['project_hash'] = implode('', $pieces);
                $val['filename']     = $val['project_hash'];
                // $val['utm_campaign'] = 'Copy ' . $val['utm_campaign'];
                $val['last_updated'] = '';
                $val['running']      = 'not run yet';

                // Construct product feed URL.
                $external_path                = $external_base . '/conversios-product-feed/' . $val['fileformat'];
                $val['external_file'] = $external_path . '/' . sanitize_file_name($val['filename']) . '.' . $val['fileformat'];

                // To build the new project row on the manage feed page.
                $projecthash   = $val['project_hash'];
                $projectname   = $val['projectname'];
                $channel       = $val['name'];
                $fileformat    = $val['fileformat'];
                $interval      = $val['cron'];
                $external_file = $val['external_file'];

                // Save the copied project.
                $new_key                 = $max_key + 1;
                $add_project[$new_key] = $val;

                array_push($feed_config, $add_project[$new_key]);
                update_option('convpfm_cron_files', $feed_config, 'no');

                // Do not start processing, user wants to make changes to the copied project.
                $copy_status = 'true';
            }
        }

        $data = array(
            'project_hash'  => $projecthash,
            'channel'       => $channel,
            'projectname'   => $projectname,
            'fileformat'    => $fileformat,
            'interval'      => $interval,
            'external_file' => $external_file,
            'copy_status'   => $copy_status,
        );

        echo json_encode($data);
        wp_die();
    }
}

function convpfm_project_refresh()
{
    $TVC_Admin_Helper = new Convpfm_TVC_Admin_Helper();
    check_ajax_referer('convpfm_ajax_nonce', 'security');

    // Force garbage collection dump.
    gc_enable();
    gc_collect_cycles();

    $project_hash  = sanitize_text_field($_POST['project_hash']);
    $feed_config   = get_option('convpfm_cron_files');
    $user          = wp_get_current_user();
    $allowed_roles = array('administrator', 'editor', 'author');

    if (array_intersect($allowed_roles, $user->roles)) {

        // Make sure content of feeds is not being cached.

        // Make sure feeds are not being cached.
        $no_caching = new Convpfm_Caching();

        // LiteSpeed Caching.
        if (class_exists('LiteSpeed\Core') || defined('LSCWP_DIR')) {
            $no_caching->litespeed_cache();
        }

        // WP Fastest Caching.
        if (class_exists('WpFastestCache')) {
            $no_caching->wp_fastest_cache();
        }

        // WP Super Caching.
        if (function_exists('wpsc_init')) {
            $no_caching->wp_super_cache();
        }

        // Breeze Caching.
        if (class_exists('Breeze_Admin')) {
            $no_caching->breeze_cache();
        }

        // WP Optimize Caching.
        if (class_exists('WP_Optimize')) {
            $no_caching->wp_optimize_cache();
        }

        // Cache Enabler.
        if (class_exists('Cache_Enabler')) {
            $no_caching->cache_enabler_cache();
        }

        // Swift Performance Lite.
        if (class_exists('Swift_Performance_Lite')) {
            $no_caching->swift_performance_cache();
        }

        // Comet Cache.
        if (is_plugin_active('comet-cache/comet-cache.php')) {
            $no_caching->comet_cache();
        }

        // HyperCache.
        if (class_exists('HyperCache')) {
            $no_caching->hyper_cache();
        }

        foreach ($feed_config as $key => $val) {
            if (isset($val['project_hash']) && ($val['project_hash'] == $project_hash)) {
                $batch_project = 'convpfm_batch_file_' . $project_hash;

                if (!get_option($batch_project)) {
                    update_option($batch_project, $val, 'no');
                    $final_creation = $TVC_Admin_Helper->convpfm_continue_batch($project_hash);
                } else {
                    $final_creation = $TVC_Admin_Helper->convpfm_continue_batch($project_hash);
                }
            }
        }
    }
}
function convpfm_project_status()
{
    check_ajax_referer('convpfm_ajax_nonce', 'security');

    $user          = wp_get_current_user();
    $allowed_roles = array('administrator', 'editor', 'author');

    if (array_intersect($allowed_roles, $user->roles)) {
        $project_hash = sanitize_text_field($_POST['project_hash']);
        $active       = sanitize_text_field($_POST['active']);
        $feed_config  = get_option('convpfm_cron_files');
        $number_feeds = count($feed_config);

        if ($number_feeds > 0) {
            foreach ($feed_config as $key => $val) {
                if ($val['project_hash'] == $project_hash) {
                    $feed_config[$key]['active'] = $active;
                    $upload_dir                    = wp_upload_dir();
                    $base                          = $upload_dir['basedir'];
                    $path                          = $base . '/conversios-product-feed/' . $val['fileformat'];
                    $file                          = $path . '/' . sanitize_file_name($val['filename']) . '.' . $val['fileformat'];
                }
            }
        }

        // When project is put on inactive, delete the product feed.
        if ($active == 'false') {
            @unlink($file);
        }

        // Regenerate product feed.
        if ($active == 'true') {
            $update_project = convpfm_project_refresh($project_hash);
        }

        // Update cron with new project status.
        update_option('convpfm_cron_files', $feed_config, 'no');
    }
}
function convpfm_create_all_feeds()
{
    $TVC_Admin_Helper = new Convpfm_TVC_Admin_Helper();
    // $TVC_Admin_Helper->plugin_log('xml generation log', 'generate xml');
    $feed_config = array();
    $feed_config = get_option('convpfm_cron_files');

    if (empty($feed_config)) {
        $nr_projects = 0;
    } else {
        $nr_projects = count($feed_config);
    }

    $cron_start_date = date('d M Y H:i');
    $cron_start_time = time();
    $hour            = date('H');

    // Update project configurations with the latest amount of live products.
    $count_products = wp_count_posts('product', 'product_variation');
    $nr_products    = $count_products->publish;

    // Determine if changes where made to products or new orders where placed.
    // Only update the feed(s) when such a change occured.
    $products_changes = 'no'; // default value.
    $products_changes = get_option('convpfm_allow_updation');

    // Make sure content of feeds is not being cached.
    // Make sure feeds are not being cached.
    $no_caching = new Convpfm_Caching();

    // LiteSpeed Caching.
    if (class_exists('LiteSpeed\Core') || defined('LSCWP_DIR')) {
        $no_caching->litespeed_cache();
    }

    // WP Fastest Caching.
    if (class_exists('WpFastestCache')) {
        $no_caching->wp_fastest_cache();
    }

    // WP Super Caching.
    if (function_exists('wpsc_init')) {
        $no_caching->wp_super_cache();
    }

    // Breeze Caching.
    if (class_exists('Breeze_Admin')) {
        $no_caching->breeze_cache();
    }

    // WP Optimize Caching.
    if (class_exists('WP_Optimize')) {
        $no_caching->wp_optimize_cache();
    }

    // Cache Enabler.
    if (class_exists('Cache_Enabler')) {
        $no_caching->cache_enabler_cache();
    }

    // Swift Performance Lite.
    if (class_exists('Swift_Performance_Lite')) {
        $no_caching->swift_performance_cache();
    }

    // Comet Cache.
    if (is_plugin_active('comet-cache/comet-cache.php')) {
        $no_caching->comet_cache();
    }

    // HyperCache.
    if (class_exists('HyperCache')) {
        $no_caching->hyper_cache();
    }

    if (!empty($feed_config)) {
        foreach ($feed_config as $key => $val) {
            // When no products changed and user enabled the option to only update the feed when products changed.
            $update_this_feed = 'yes';
            if ((isset($val['products_changed'])) && ($products_changes == 'no')) {
                $update_this_feed = 'no';
            }

            // Force garbage collection dump.
            gc_enable();
            gc_collect_cycles();

            // Only process projects that are active.
            if (($val['active'] == 'true') && (!empty($val)) && ($update_this_feed == 'yes') && (isset($val['cron']))) {

                if (($val['cron'] == 'daily') && ($hour == 07)) {
                    $batch_project = 'convpfm_batch_file_' . $val['project_hash'];
                    // $TVC_Admin_Helper->plugin_log($batch_project."xml file generation inside daily", 'generate xml');
                    if (!get_option($batch_project)) {
                        update_option($batch_project, $val, 'no');
                        $start_project = $TVC_Admin_Helper->convpfm_continue_batch($val['project_hash']);
                    } else {
                        $start_project = $TVC_Admin_Helper->convpfm_continue_batch($val['project_hash']);
                    }

                    unset($start_project);
                } elseif (($val['cron'] == 'twicedaily') && ($hour == 19 || $hour == 07)) {
                    $batch_project = 'convpfm_batch_file_' . $val['project_hash'];
                    // $TVC_Admin_Helper->plugin_log($batch_project."xml file generation cron inside twice daily", 'generate xml');
                    if (!get_option($batch_project)) {
                        update_option($batch_project, $val, 'no');
                        $start_project = $TVC_Admin_Helper->convpfm_continue_batch($val['project_hash']);
                    } else {
                        $start_project = $TVC_Admin_Helper->convpfm_continue_batch($val['project_hash']);
                    }

                    unset($start_project);
                } elseif (($val['cron'] == 'twicedaily' || $val['cron'] == 'daily') && ($val['running'] == 'processing')) {
                    // Re-start daily and twicedaily projects that are hanging.
                    $batch_project = 'convpfm_batch_file_' . $val['project_hash'];
                    // $TVC_Admin_Helper->plugin_log($batch_project."xml file generation cron inside twice daily daily processing", 'generate xml');
                    if (!get_option($batch_project)) {
                        update_option($batch_project, $val, 'no');
                        $start_project = $TVC_Admin_Helper->convpfm_continue_batch($val['project_hash']);
                    } else {
                        $start_project = $TVC_Admin_Helper->convpfm_continue_batch($val['project_hash']);
                    }

                    unset($start_project);
                } elseif (($val['cron'] == 'no refresh') && ($hour == 26)) {
                    // It is never hour 26, so this project will never refresh.
                } elseif ($val['cron'] == 'hourly') {
                    $batch_project = 'convpfm_batch_file_' . $val['project_hash'];
                    // $TVC_Admin_Helper->plugin_log($batch_project."xml file generation cron inside hourly", 'generate xml');
                    if (!get_option($batch_project)) {
                        update_option($batch_project, $val, 'no');
                        $start_project = $TVC_Admin_Helper->convpfm_continue_batch($val['project_hash']);
                    } else {
                        $start_project = $TVC_Admin_Helper->convpfm_continue_batch($val['project_hash']);
                    }

                    unset($start_project);
                }
            }
        }
    }

    // set products update flag back to no.
    update_option('convpfm_allow_updation', 'no');
}
function convpfm_UpdateProject_history($project_hash)
{
    $feed_config = get_option( 'convpfm_cron_files', array() );
    if ( ! is_array( $feed_config ) ) {
        return;
    }

    // Filter the amount of history products in the system report.
    $max_history_products = apply_filters( 'woosea_max_history_products', 10 );

    foreach ( $feed_config as $key => $project ) {
        if ( $project['project_hash'] !== $project_hash ) {
            continue;
        }

        $nr_products = 0;
        $upload_dir  = wp_upload_dir();
            $base    = $upload_dir['basedir'];
            $path    = $base . '/conversios-product-feed/' . $project['fileformat'];
            $file    = $path . '/' . sanitize_file_name( $project['filename'] ) . '.' . $project['fileformat'];

            if ( file_exists( $file ) ) {
                if ( ( $project['fileformat'] == 'csv' ) || ( $project['fileformat'] == 'txt' ) ) {
                    $fp              = file( $file );
                    $raw_nr_products = count( $fp );
                    $nr_products     = $raw_nr_products - 1; // header row of csv.
                } else {
                    $xml = simplexml_load_file( $file, 'SimpleXMLElement', LIBXML_NOCDATA );

                if ( $project['name'] == 'Yandex' ) {
                    if ( isset( $xml->offers->offer ) ) {
                                    $nr_products = count( $xml->offers->offer );
                    }
                } elseif ( $project['taxonomy'] == 'none' ) {
                    if ( is_countable( $xml->product ) ) {
                        $nr_products = count( $xml->product );
                    }
                } else {
                    $nr_products = count( $xml->channel->item );
                }
            }
        }

        $count_timestamp = date( 'd M Y H:i' );
        $number_run      = array(
            $count_timestamp => $nr_products,
        );

        $feed_config = get_option( 'convpfm_cron_files' );

        foreach ( $feed_config as $key => $val ) {
            if ( ( $val['project_hash'] == $project['project_hash'] ) && ( $val['running'] == 'ready' ) ) {
                if ( array_key_exists( 'history_products', $feed_config[ $key ] ) ) {
                    $feed_config[ $key ]['history_products'][ $count_timestamp ] = $nr_products;
                } else {
                    $feed_config[ $key ]['history_products'] = $number_run;
                }

                // Limit the amount of history products.
                if ( count( $feed_config[ $key ]['history_products'] ) > $max_history_products ) {
                    $feed_config[ $key ]['history_products'] = array_slice( $feed_config[ $key ]['history_products'], - $max_history_products, null, true );
                }
            }
        }

        update_option( 'convpfm_cron_files', $feed_config, false );
    }
}
function convpfm_categories_dropdown()
{
    $rowCount          = absint(esc_attr(sanitize_text_field($_POST['rowCount'])));
    $user              = wp_get_current_user();
    $allowed_roles = array('administrator', 'editor', 'author');

    if (array_intersect($allowed_roles, $user->roles)) {
        $orderby    = 'name';
        $order      = 'asc';
        $hide_empty = false;
        $cat_args   = array(
            'orderby'    => $orderby,
            'order'      => $order,
            'hide_empty' => $hide_empty,
        );

        $categories_dropdown = "<select class=\"filter-value\" name=\"rules[$rowCount][criteria]\" id=\"criteria_$rowCount\">";
        $product_categories  = get_terms('product_cat', $cat_args);

        foreach ($product_categories as $key => $category) {
            $categories_dropdown .= "<option value=\"$category->name\">$category->name ($category->slug)</option>";
        }
        $categories_dropdown .= '</select>';

        $data = array(
            'rowCount' => $rowCount,
            'dropdown' => $categories_dropdown,
        );
        echo json_encode($data);
        wp_die();
    }
}
add_action('wp_ajax_convpfm_categories_dropdown', 'convpfm_categories_dropdown');

function convpfm_recursive_sanitize_text_field($array)
{
    foreach ($array as $key => &$value) {
        if (is_array($value)) {
            $value = convpfm_recursive_sanitize_text_field($value);
        } else {
            $value = sanitize_text_field($value);
        }
    }
    return $array;
}
function convpfm_add_mass_cat_mapping()
{
    check_ajax_referer('convpfm_ajax_nonce', 'security');

    $user          = wp_get_current_user();
    $allowed_roles = array('administrator');

    if (array_intersect($allowed_roles, $user->roles)) {
        $project_hash = sanitize_text_field($_POST['project_hash']);
        $catMappings  = convpfm_recursive_sanitize_text_field($_POST['catMappings']);

        // I need to sanitize the catMappings Array.
        $mappings = array();
        foreach ($catMappings as $mKey => $mVal) {
            $mKey                      = sanitize_text_field($mKey);
            $mVal                      = sanitize_text_field($mVal);
            $piecesVal                 = explode('||', $mVal);
            $mappings[$piecesVal[1]] = array(
                'rowCount'        => $piecesVal[1],
                'categoryId'      => $piecesVal[1],
                'criteria'        => $piecesVal[0],
                'map_to_category' => $piecesVal[2],
            );
        }

        $project = Convpfm_UpdateProject::get_project_data(sanitize_text_field($project_hash));
        // This happens during configuration of a new feed.
        if (empty($project)) {
            $project_temp = get_option('convpfm_channel_files');
            if (array_key_exists('mappings', $project_temp)) {
                $project_temp['mappings'] = $mappings + $project_temp['mappings'];
            } else {
                $project_temp['mappings'] = $mappings;
            }
            update_option('convpfm_channel_files', $project_temp, 'yes');
        } else {
            // Only update the ones that changed.
            foreach ($mappings as $categoryId => $catArray) {
                if (is_array($project['mappings'])) {
                    if (array_key_exists($categoryId, $project['mappings'])) {
                        $project['mappings'][$categoryId] = $catArray;
                    } else {
                        $project['mappings'][$categoryId] = $catArray;
                    }
                }
            }
            $project_updated = Convpfm_UpdateProject::update_project_data($project);
        }
        $data = array(
            'status_mapping' => 'true',
        );
        echo json_encode($data);
        wp_die();
    }
}
add_action('wp_ajax_convpfm_add_mass_cat_mapping', 'convpfm_add_mass_cat_mapping');

function convpfm_add_cat_mapping()
{
    $rowCount        = absint(esc_attr(sanitize_text_field($_POST['rowCount'])));
    $className       = sanitize_text_field($_POST['className']);
    $map_to_category = sanitize_text_field($_POST['map_to_category']);
    $project_hash    = sanitize_text_field($_POST['project_hash']);
    $criteria        = sanitize_text_field($_POST['criteria']);
    $status_mapping  = 'false';
    $project         = Convpfm_UpdateProject::get_project_data(sanitize_text_field($project_hash));

    // This is during the configuration of a new feed.
    if (empty($project)) {
        $project_temp = get_option('convpfm_channel_files');

        $project_temp['mappings'][$rowCount]['rowCount']        = $rowCount;
        $project_temp['mappings'][$rowCount]['categoryId']      = $rowCount;
        $project_temp['mappings'][$rowCount]['criteria']        = $criteria;
        $project_temp['mappings'][$rowCount]['map_to_category'] = $map_to_category;

        update_option('convpfm_channel_files', $project_temp, 'yes');
        $status_mapping = 'true';
        // This is updating an existing product feed.
    } else {
        $project['mappings'][$rowCount]['rowCount']        = $rowCount;
        $project['mappings'][$rowCount]['categoryId']      = $rowCount;
        $project['mappings'][$rowCount]['criteria']        = $criteria;
        $project['mappings'][$rowCount]['map_to_category'] = $map_to_category;

        $project_updated = Convpfm_UpdateProject::update_project_data($project);
        $status_mapping  = 'true';
    }

    $data = array(
        'rowCount'        => $rowCount,
        'className'       => $className,
        'map_to_category' => $map_to_category,
        'status_mapping'  => $status_mapping,
    );

    echo json_encode($data);
    wp_die();
}
add_action('wp_ajax_convpfm_add_cat_mapping', 'convpfm_add_cat_mapping');

function convpfm_shipping_zones()
{
    $shipping_options = '';
    $shipping_zones   = WC_Shipping_Zones::get_zones();

    $shipping_options = '<option value="all_zones">All zones</option>';

    foreach ($shipping_zones as $zone) {
        $shipping_options .= "<option value=\"$zone[zone_id]\">$zone[zone_name]</option>";
    }

    $data = array(
        'dropdown' => $shipping_options,
    );

    echo json_encode($data);
    wp_die();
}
add_action('wp_ajax_convpfm_shipping_zones', 'convpfm_shipping_zones');

function convpfm_add_attributes()
{
    check_ajax_referer('convpfm_ajax_nonce', 'security');

    $user          = wp_get_current_user();
    $allowed_roles = array('administrator');

    if (array_intersect($allowed_roles, $user->roles)) {
        $attribute_name  = sanitize_text_field($_POST['attribute_name']);
        $attribute_value = sanitize_text_field($_POST['attribute_value']);
        $active          = sanitize_text_field($_POST['active']);

        if (!get_option('convpfm_extra_attributes')) {
            if ($active == 'true') {
                $extra_attributes = array(
                    $attribute_value => $attribute_name,
                );
                update_option('convpfm_extra_attributes', $extra_attributes, 'no');
            }
        } else {
            $extra_attributes = get_option('convpfm_extra_attributes');

            if (!in_array($attribute_name, $extra_attributes, true)) {
                if ($active == 'true') {
                    $add_attribute    = array(
                        $attribute_value => $attribute_name,
                    );
                    $extra_attributes = array_merge($extra_attributes, $add_attribute);
                    update_option('convpfm_extra_attributes', $extra_attributes, 'no');
                }
            } elseif ($active == 'false') {
                // remove from extra attributes array.
                $extra_attributes = array_diff($extra_attributes, array($attribute_value => $attribute_name));
                update_option('convpfm_extra_attributes', $extra_attributes, 'no');
            }
        }

        $extra_attributes = get_option('convpfm_extra_attributes');
    }
}
add_action('wp_ajax_convpfm_add_attributes', 'convpfm_add_attributes');


function convpfm_add_mother_image()
{
    check_ajax_referer('convpfm_ajax_nonce', 'security');

    $user          = wp_get_current_user();
    $allowed_roles = array('administrator');

    if (array_intersect($allowed_roles, $user->roles)) {
        $status = sanitize_text_field($_POST['status']);

        if ($status == 'off') {
            update_option('convpfm_add_mother_image', 'no', 'yes');
        } else {
            update_option('convpfm_add_mother_image', 'yes', 'yes');
        }
    }
}
add_action('wp_ajax_convpfm_add_mother_image', 'convpfm_add_mother_image');

/**
 * This function enables the setting to use Shipping costs for all countries.
 */
function convpfm_add_all_shipping()
{
    check_ajax_referer('convpfm_ajax_nonce', 'security');

    $user          = wp_get_current_user();
    $allowed_roles = array('administrator');

    if (array_intersect($allowed_roles, $user->roles)) {
        $status = sanitize_text_field($_POST['status']);

        if ($status == 'off') {
            update_option('add_all_shipping', 'no', 'yes');
        } else {
            update_option('add_all_shipping', 'yes', 'yes');
        }
    }
}
add_action('wp_ajax_convpfm_add_all_shipping', 'convpfm_add_all_shipping');

/**
 * This function enables the setting to respect the free shipping class.
 */
function convpfm_free_shipping()
{
    check_ajax_referer('convpfm_ajax_nonce', 'security');

    $user          = wp_get_current_user();
    $allowed_roles = array('administrator');

    if (array_intersect($allowed_roles, $user->roles)) {
        $status = sanitize_text_field($_POST['status']);

        if ($status == 'off') {
            update_option('free_shipping', 'no', 'yes');
        } else {
            update_option('free_shipping', 'yes', 'yes');
        }
    }
}
add_action('wp_ajax_convpfm_free_shipping', 'convpfm_free_shipping');

/**
 * This function enables the setting to remove local pickup shipping zones.
 */
function convpfm_local_pickup_shipping()
{
    check_ajax_referer('convpfm_ajax_nonce', 'security');

    $user          = wp_get_current_user();
    $allowed_roles = array('administrator');

    if (array_intersect($allowed_roles, $user->roles)) {
        $status = sanitize_text_field($_POST['status']);

        if ($status == 'off') {
            update_option('local_pickup_shipping', 'no', 'yes');
        } else {
            update_option('local_pickup_shipping', 'yes', 'yes');
        }
    }
}
add_action('wp_ajax_convpfm_local_pickup_shipping', 'convpfm_local_pickup_shipping');

/**
 * This function enables the setting to remove free shipping zones.
 */
function convpfm_remove_free_shipping()
{
    check_ajax_referer('convpfm_ajax_nonce', 'security');

    $user          = wp_get_current_user();
    $allowed_roles = array('administrator');

    if (array_intersect($allowed_roles, $user->roles)) {
        $status = sanitize_text_field($_POST['status']);

        if ($status == 'off') {
            update_option('remove_free_shipping', 'no', 'yes');
        } else {
            update_option('remove_free_shipping', 'yes', 'yes');
        }
    }
}
add_action('wp_ajax_convpfm_remove_free_shipping', 'convpfm_remove_free_shipping');

function convpfm_autocomplete_dropdown()
{
    $rowCount = absint(esc_attr(sanitize_text_field($_POST['rowCount'])));

    $mapping_obj      = new Convpfm_Attributes();
    $mapping_dropdown = $mapping_obj->get_mapping_attributes_dropdown();

    $data = array(
        'rowCount' => $rowCount,
        'dropdown' => $mapping_dropdown,
    );

    echo json_encode($data);
    wp_die();
}
add_action('wp_ajax_convpfm_autocomplete_dropdown', 'convpfm_autocomplete_dropdown');

function convpfm_before_product_save($post_id)
{
    $post_type = get_post_type($post_id);
    if ($post_type == 'product') {
        $product = wc_get_product($post_id);

        if (is_object($product)) {
            $product_data = $product->get_data();

            $before = array(
                'product_id'        => $post_id,
                'type'              => $product->get_type(),
                'name'              => $product->get_name(),
                'slug'              => $product->get_slug(),
                'status'            => $product->get_status(),
                'featured'          => $product->get_featured(),
                'visibility'        => $product->get_catalog_visibility(),
                'description'       => $product->get_description(),
                'short_description' => $product->get_short_description(),
                'sku'               => $product->get_sku(),
                'price'             => $product->get_price(),
                'regular_price'     => $product->get_regular_price(),
                'sale_price'        => $product->get_sale_price(),
                'total_sales'       => $product->get_total_sales(),
                'tax_status'        => $product->get_tax_status(),
                'tax_class'         => $product->get_tax_class(),
                'manage_stock'      => $product->get_manage_stock(),
                'stock_quantity'    => $product->get_stock_quantity(),
                'stock_status'      => $product->get_stock_status(),
                'backorders'        => $product->get_backorders(),
                'weight'            => $product->get_weight(),
                'length'            => $product->get_length(),
                'width'             => $product->get_width(),
                'height'            => $product->get_height(),
                'parent_id'         => $product->get_parent_id(),
            );

            if (!get_option('product_changes')) {
                update_option('product_changes', $before, '', 'yes');
            }
        }
    }
}
add_action('pre_post_update', 'convpfm_before_product_save');

function convpfm_on_product_save($product_id)
{
    $product = wc_get_product($product_id);

    if (is_object($product)) {
        $product_data = $product->get_data();

        $after = array(
            'product_id'        => $product_id,
            'type'              => $product->get_type(),
            'name'              => $product->get_name(),
            'slug'              => $product->get_slug(),
            'status'            => $product->get_status(),
            'featured'          => $product->get_featured(),
            'visibility'        => $product->get_catalog_visibility(),
            'description'       => $product->get_description(),
            'short_description' => $product->get_short_description(),
            'sku'               => $product->get_sku(),
            'price'             => $product->get_price(),
            'regular_price'     => $product->get_regular_price(),
            'sale_price'        => $product->get_sale_price(),
            'total_sales'       => $product->get_total_sales(),
            'tax_status'        => $product->get_tax_status(),
            'tax_class'         => $product->get_tax_class(),
            'manage_stock'      => $product->get_manage_stock(),
            'stock_quantity'    => $product->get_stock_quantity(),
            'stock_status'      => $product->get_stock_status(),
            'backorders'        => $product->get_backorders(),
            'sold_individually' => $product->get_sold_individually(),
            'weight'            => $product->get_weight(),
            'length'            => $product->get_length(),
            'width'             => $product->get_width(),
            'height'            => $product->get_height(),
            'parent_id'         => $product->get_parent_id(),
        );

        if (is_array($product_data)) {
            if (get_option('product_changes')) {
                $before = get_option('product_changes');
                $diff   = array_diff($after, $before);

                if (!$diff) {
                    $diff['product_id'] = $product_id;
                } else {
                    // Enable the product changed flag.
                    update_option('convpfm_allow_updation', 'no');
                }

                delete_option('product_changes');
            } else {
                // Enable the product changed flag.
                update_option('convpfm_allow_updation', 'no');
            }
        }
    }
}
add_action('woocommerce_update_product', 'convpfm_on_product_save', 10, 1);

