<?php   
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
    require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
    /*****************Redirect if no channel configured Start********************/
    $convpfm_options = unserialize(get_option('convpfm_options')); 
    $google_merchant_center_id = '';
    if (isset($convpfm_options['google_merchant_id']) === TRUE && $convpfm_options['google_merchant_id'] !== '') {
        $google_merchant_center_id = esc_html($convpfm_options['google_merchant_id']);
    }

    $tiktok_business_account = '';
    if (isset($convpfm_options['tiktok_setting']['tiktok_business_id']) === TRUE && $convpfm_options['tiktok_setting']['tiktok_business_id'] !== '') {
        $tiktok_business_account = esc_html($convpfm_options['tiktok_setting']['tiktok_business_id']);
    }
    $facebook_business_account = '';
    if (isset($convpfm_options['facebook_setting']['fb_business_id']) === TRUE && $convpfm_options['facebook_setting']['fb_business_id'] !== '') {
        $facebook_business_account = esc_html($convpfm_options['facebook_setting']['fb_business_id']);
    }
    $facebook_catalog_id = '';
    if (isset($convpfm_options['facebook_setting']['fb_catalog_id']) === TRUE && $convpfm_options['facebook_setting']['fb_catalog_id'] !== '') {
        $facebook_catalog_id = esc_html($convpfm_options['facebook_setting']['fb_catalog_id']);
    }
    if ($google_merchant_center_id === '' && $tiktok_business_account === '' && $facebook_catalog_id === '') {
        wp_safe_redirect("admin.php?page=conversiospfm");
        exit;
    }    
    /*****************Redirect if no channel configured End********************/
    $Convpfm_TVC_Admin_Helper = new Convpfm_TVC_Admin_Helper();
    $subscriptionId = $Convpfm_TVC_Admin_Helper->get_subscriptionId();

    $feed_data = $Convpfm_TVC_Admin_Helper->convpfm_get_results('convpfm_product_feed');
    $feed_count = count($feed_data);
    $status_feed = $Convpfm_TVC_Admin_Helper->get_feed_status();
    
    $filesystem = new WP_Filesystem_Direct( true );
    $getCountris = $filesystem->get_contents(CONVPFM_ENHANCAD_PLUGIN_DIR."includes/setup/json/countries.json");
    $contData = json_decode($getCountris);

    $conv_data = $Convpfm_TVC_Admin_Helper->get_store_data();
    $category_wrapper_obj = new Convpfm_Category_Wrapper();
    $gmcAttributes = $Convpfm_TVC_Admin_Helper->get_gmcAttributes();
    $convpfm_mapped_attrs = unserialize(get_option('convpfm_prod_mapped_attrs'));
    $tempAddAttr = $convpfm_mapped_attrs;    
    $convpfm_prod_mapped_cats = unserialize(get_option('convpfm_prod_mapped_cats'));
    $Convpfm_ProductSyncHelper = new Convpfm_ProductSyncHelper();
    $wooCommerceAttributes = array_map("unserialize", array_unique(array_map("serialize", $Convpfm_ProductSyncHelper->wooCommerceAttributes())));
    $site_url = "admin.php?page=convpfm-google-shopping-feed&tab=";

    $subscription_id = $Convpfm_TVC_Admin_Helper->get_subscriptionId();
    $subscription_data = $Convpfm_TVC_Admin_Helper->get_user_subscription_data();
    $google_merchant_id = $subscription_data->google_merchant_center_id;
    $google_ads_id = $subscription_data->google_ads_id;
    $google_detail = $Convpfm_TVC_Admin_Helper->get_convpfm_api_data();
    $store_id = esc_html($google_detail['setting']->store_id);
?>
<style>
    .search-input {
        /* padding: 10px 20px 10px 40px; */
        font-size: 12px;
        background: url(<?php echo esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/search-icon.png'); ?>) no-repeat 10px center; /* URL of your icon */
        background-size: 18px 18px; 
        padding: 5px 4px 5px 40px !important;
    }
</style>
<div class="container-fluid mt-4 w-96 feed-list-div <?php echo $feed_count > 0 && isset($_GET['create-feed']) == FALSE ? '' : 'd-none' ?>">
    <span class="fw-bold text-dark fs-20">
        <img class="imgChannel" src="<?php echo esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/web_store.png'); ?>" alt="">
        <?php esc_html_e("Feed Management","product-feed-manager-for-woocommerce")?>
    </span>
    <p class="text-grey fs-16 fw-400">
        <?php esc_html_e("Manage your product feeds to keep your online store information up-to-date and accurate. This includes adding new products, updating prices, and ensuring all product details meet platform requirements. Effective feed management helps your products appear correctly on shopping channels and improves your visibility to potential customers.
","product-feed-manager-for-woocommerce")?>
    </p>
    <nav class="navbar navbar-light bg-white shadow-sm topNavBar" style="border-top-left-radius:8px;border-top-right-radius:8px;">            
        <div class="col-12">   
            <div class="row ms-0 p-1">         
                <div class="col-4">
                    <input type="search" class="form-control border from-control-width empty search-input" placeholder=""
                            aria-label="Search" name="search_feed" id="search_feed" aria-controls="feed_list_table" oninput="removeIcon(this)">
                </div>  
                <div class="col-8 d-flex justify-content-end">                    
                    <button
                        class="createCampaign btn btn-outline-primary fs-14 me-2 disabled campaignClass"
                        title="Select Feed from below to create performance max campaign in Google Ads." style="width:180px;height:38px;">
                        <?php esc_html_e("Create Campaign", "product-feed-manager-for-woocommerce"); ?> 
                    </button>     
                    <button class="btn btn-soft-primary fs-14 me-2" name="create_new_feed" id="create_new_feed"  style="pointer-events: auto !important;width:180px;height:38px;">
                        <?php esc_html_e("Create New Feed", "product-feed-manager-for-woocommerce"); ?>
                    </button>
                </div>
            </div>            
        </div>        
    </nav>
    <div class="table-responsive shadow-sm" style="border-bottom-left-radius:8px;border-bottom-right-radius:8px;">
        <table class="table" id="feed_list_table" style="width:100%">
            <thead>
                <tr class="align-middle">
                    <th scope="col" class="text-dark text-start" width="3%">
                        <div class="form-check form-check-custom" style="padding-left: 2.5rem !important;padding-top: 0.5rem!important;">
                            <!-- <input class="form-check-input checkbox fw-500 fs-17" type="checkbox" name="selectAll" id="selectAll" value="selectAll"> -->
                        </div>
                    </th>
                    <th scope="col" class="text-dark text-start fw-500 fs-12 text-header">
                        <?php esc_html_e("Feed Name", "product-feed-manager-for-woocommerce"); ?>
                    </th>
                    <th scope="col" class="text-dark text-center fw-500 fs-12 text-header">
                        <?php esc_html_e("Target Country", "product-feed-manager-for-woocommerce"); ?>
                    </th>
                    <th scope="col" class="text-dark text-center fw-500 fs-12 text-header">
                        <?php esc_html_e("Channels", "product-feed-manager-for-woocommerce"); ?>
                    </th>
                    <!-- <th scope="col" class="text-dark text-center fw-500 fs-12 text-header">
                        <?php// esc_html_e("Total Product", "product-feed-manager-for-woocommerce"); ?>
                    </th> -->
                    <th scope="col" class="text-dark text-center fw-500 fs-12 text-header">
                        <?php esc_html_e("Auto Sync", "product-feed-manager-for-woocommerce"); ?>
                    </th>
                    <th scope="col" class="text-dark text-center fw-500 fs-12 text-header">
                        <?php esc_html_e("Date Created", "product-feed-manager-for-woocommerce"); ?>
                    </th>
                    <th scope="col" class="text-dark text-center fw-500 fs-12 text-header">
                        <?php esc_html_e("Last Sync", "product-feed-manager-for-woocommerce"); ?>
                    </th>
                    <th scope="col" class="text-dark text-center fw-500 fs-12 text-header">
                        <?php esc_html_e("Next Sync", "product-feed-manager-for-woocommerce"); ?>
                    </th>
                    <th scope="col" class="text-dark text-center fw-500 fs-12 text-header">
                        <?php esc_html_e("Status", "product-feed-manager-for-woocommerce"); ?>
                    </th>
                    <th scope="col" class="text-dark text-center fw-500 fs-12 text-header">
                        <?php esc_html_e("More", "product-feed-manager-for-woocommerce"); ?>
                    </th>
                </tr>
            </thead>
            <tbody id="table-body" class="table-body bg-white">
            <?php
                    $feedIdArr = []; 
                    if (empty($feed_data) === FALSE) {
                    foreach ($feed_data as $value) {
                        $channel_id = explode(',', $value->channel_ids);
                        if($value->status == 'Synced') {
                            array_push($feedIdArr, $value->id);
                        }
                        
                        ?>
                        <tr class="height">
                            <td class="align-middle text-start">
                                <div class="form-check form-check-custom" style="padding-left: 2.5rem !important;padding-top: 0.5rem!important;">
                                    <input class="form-check-input checkbox_feed fs-17" <?php echo $value->status == 'Synced' ? '' : 'disabled="disabled"' ?> type="checkbox" name="" id="checkFeed_<?php echo esc_attr($value->id); ?>" value="<?php echo esc_attr($value->id); ?>">
                                </div>
                            </td>
                            <td class="align-middle text-start fw-400 fs-12 text-grey">
                                <?php if ($value->is_delete === '1') { ?>
                                    <span style="cursor: no-drop;">
                                        <?php echo esc_html(str_replace('\\', '',$value->feed_name)); ?>
                                    </span>
                                <?php } else { ?>
                                    <span class="pointer">
                                        <a title="Go to feed wise product list"
                                            href="<?php echo esc_url($site_url . 'product_list&id=' . $value->id); ?>"><?php echo esc_html(str_replace('\\', '',$value->feed_name)); ?></a>
                                    </span>
                                <?php } ?>

                            </td>
                            <td class="align-middle text-center fw-400 fs-12 text-grey">
                                <?php
                                foreach ($contData as $key => $country) {
                                    if ($value->target_country === $country->code) { ?>
                                        <?php echo esc_html($country->name); ?>
                                    <?php }
                                }
                                ?>
                            </td>
                            <td class="align-middle text-center fw-400 fs-12 text-grey">
                                <?php foreach ($channel_id as $val) {
                                    if ($val === '1') { ?>
                                        <img class="imgChannel-table"
                                            src="<?php echo esc_url(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/google_shopping_icon.png'); ?>" /> GMC
                                    <?php } elseif ($val === '2') { ?>
                                        <img class="imgChannel-table"
                                            src="<?php echo esc_url(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/fb-icon.png'); ?>" /> Facebook
                                    <?php } elseif ($val === '3') { ?>
                                        <img class="imgChannel-table"
                                            src="<?php echo esc_url(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/conv_tiktok_logo.png'); ?>" /> TikTok
                                    <?php }
                                } ?>
                            </td>
                            <!-- <td class="align-middle text-center fw-400 fs-12 text-grey">
                                <?php //echo esc_html(number_format_i18n($value->total_product ? $value->total_product : 0)); ?>
                            </td> -->
                            <td class="align-middle text-center fw-400 fs-12 text-grey">
                                <span class="dot <?php echo $value->auto_schedule === '1' ? 'dot-green' : 'dot-red'; ?>"></span>
                                <span class="<?php echo $value->auto_schedule === '1' ? 'green-dot' : 'red-dot'; ?>">                                    
                                </span>
                                <span class="fs-10 mb-0">
                                    <?php echo $value->auto_sync_interval !== 0 && $value->auto_schedule === '1' ? 'Every ' . esc_html($value->auto_sync_interval) . ' Days' : 'In Active'; ?>
                                </span>
                            </td>
                            <td class="align-middle text-center fw-400 fs-12 text-grey" data-sort='" <?php echo esc_html(strtotime($value->created_date)) ?> "'>
                                <span>
                                    <?php echo esc_html(date_format(date_create($value->created_date), "d/m/y")); ?>
                                </span>
                                <p class="fs-10 mb-0 text-primary">
                                    <?php echo esc_html(date_format(date_create($value->created_date), "H:i a")); ?>
                                </p>
                            </td>
                            <td class="align-middle text-center fw-400 fs-12 text-grey" data-sort='" <?php echo !empty($value->last_sync_date) ? esc_html(strtotime($value->last_sync_date)) : '' ?> "'>
                                <span>
                                    <?php echo $value->last_sync_date && $value->last_sync_date != '0000-00-00 00:00:00' ? esc_html(date_format(date_create($value->last_sync_date), "d/m/y")) : 'NA'; ?>
                                </span>
                                <p class="fs-10 mb-0 text-primary">
                                    <?php echo $value->last_sync_date && $value->last_sync_date != '0000-00-00 00:00:00' ? esc_html(date_format(date_create($value->last_sync_date), "H:i a")) : ''; ?>
                                </p>
                            </td>
                            <td class="align-middle text-center fw-400 fs-12 text-grey"
                                data-sort='" <?php echo !empty($value->next_schedule_date) ? esc_html(strtotime($value->next_schedule_date)) : '' ?> "'>                                
                                <span>
                                    <?php echo $value->next_schedule_date && $value->next_schedule_date != '0000-00-00 00:00:00' && $value->next_schedule_date != '1970-01-26 00:00:00' ? esc_html(date_format(date_create($value->next_schedule_date), "d/m/y")) : 'NA'; ?>
                                </span>
                                <p class="fs-10 mb-0 text-primary">
                                    <?php echo $value->next_schedule_date && $value->next_schedule_date != '0000-00-00 00:00:00' && $value->next_schedule_date != '1970-01-26 00:00:00' ? esc_html(date_format(date_create($value->next_schedule_date), "H:i a")) : ''; ?>
                                </p>
                            </td>
                            <td class="align-middle text-center fw-400 fs-12 text-grey">
                                <?php if ($value->is_delete === '1') { ?>
                                    <span class="badgebox rounded-pill  fs-10 deleted">
                                        Deleted
                                    </span>
                                <?php } else {
                                    $draft = 0;
                                    $inprogress = 0;
                                    $synced = 0;
                                    $failed = 0;
                                    switch ($value->status) {
                                        case 'Draft':
                                            $draft++;
                                            break;

                                        case 'In Progress':
                                            $inprogress++;
                                            break;

                                        case 'Synced':
                                            $synced++;
                                            break;

                                        case 'Failed':
                                            $failed++;
                                            break;
                                    }

                                    switch ($value->tiktok_status) {
                                        case 'Draft':
                                            $draft++;
                                            break;

                                        case 'In Progress':
                                            $inprogress++;
                                            break;

                                        case 'Synced':
                                            $synced++;
                                            break;

                                        case 'Failed':
                                            $failed++;
                                            break;
                                    }

                                    switch ($value->fb_status) {
                                        case 'Draft':
                                            $draft++;
                                            break;

                                        case 'In Progress':
                                            $inprogress++;
                                            break;

                                        case 'Synced':
                                            $synced++;
                                            break;

                                        case 'Failed':
                                            $failed++;
                                            break;
                                    }

                                    if ($draft !== 0) { ?>
                                        <div class="badgebox draft" data-bs-toggle="popover" data-bs-placement="left"
                                            data-bs-content="Left popover" data-bs-trigger="hover focus">
                                            <?php echo esc_html('Draft'); ?>
                                        </div>
                                    <?php }
                                    if ($inprogress !== 0) { ?>
                                        <div class="badgebox inprogress" data-bs-toggle="popover" data-bs-placement="left"
                                            data-bs-content="Left popover" data-bs-trigger="hover focus">
                                            <?php echo esc_html('In Progress'); ?>
                                        </div>                                        
                                    <?php }
                                    if ($synced !== 0) { ?>
                                        <div class="badgebox synced" data-bs-toggle="popover" data-bs-placement="left"
                                            data-bs-content="Left popover" data-bs-trigger="hover focus">
                                            <?php echo esc_html('Synced'); ?>
                                        </div>                                        
                                    <?php }
                                    if ($failed !== 0) { ?>
                                        <div class="badgebox failed" data-bs-toggle="popover" data-bs-placement="left"
                                            data-bs-content="Left popover" data-bs-trigger="hover focus">
                                            <?php echo esc_html('Failed'); ?>
                                        </div>
                                    <?php }
                                } //end if ?>
                            </td>
                            <td class="align-middle text-center fw-400 fs-12 text-grey">
                                <div class="dropdown position-static">
                                    <?php if ($value->is_delete === '1') { ?>
                                        <button class="btn" type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                            style="cursor: no-drop;">
                                            <span class="material-symbols-outlined">
                                                more_horiz
                                            </span>
                                        </button>
                                    <?php } else { ?>
                                        <button class="btn action_btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <span class="material-symbols-outlined">
                                                more_horiz
                                            </span>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-dark bg-white">
                                            <li class="mb-0 pointer"><a class="dropdown-item text-secondary border-bottom fs-12"
                                                    onclick="editFeed(<?php echo esc_html($value->id); ?>)">Edit</a>
                                            </li>
                                            <li class="mb-0 pointer"><a class="dropdown-item text-secondary border-bottom fs-12 "
                                                    onclick="duplicateFeed(<?php echo esc_html($value->id); ?>)">Duplicate</a>
                                            </li>
                                            <li class="mb-0 pointer"><a class="dropdown-item text-secondary fs-12"
                                                    onclick="deleteFeed(<?php echo esc_html($value->id); ?>)">Delete</a></li>
                                        </ul>
                                    <?php } //end if
                                            ?>
                                </div>
                            </td>
                        </tr>
                    <?php } //end foreach
                } //end if
                $feedIdString = implode(",",$feedIdArr);
                ?>
            </tbody>
        </table>
    </div>
</div>
<div class="container-fluid containerheight bg-white mt-4 w-96 no-feed-div <?php echo $feed_count == 0 && isset($_GET['create-feed']) == FALSE ? '' : 'd-none' ?>">
    <div style="padding-top:100px">
        <div class="centerElement">
            <img class="" src="<?php echo esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/no-feed.png'); ?>" alt="">
        </div>
        <div class="centerElement mt-1">            
            <span class="fs-14 fw-600"><?php esc_html_e("No Feed's Found.","product-feed-manager-for-woocommerce")?></span>
        </div>
        <div class="centerElement mt-1">
            <span class="fs-12 fw-400" ><?php esc_html_e("Currently you have not created any feed from your account.","product-feed-manager-for-woocommerce")?></span>
        </div>
        <div class="centerElement mt-2">
            <button class="btn btn-primary fs-12 createNewFeedButton" ><?php esc_html_e("Create New Feed","product-feed-manager-for-woocommerce")?></button>
        </div>
    </div>
</div>
<div class="container-fluid containerheight mt-4 w-96 create-feed-div <?php echo isset($_GET['create-feed']) && $_GET['create-feed'] == 'New-Feed' ? '' : 'd-none' ?>">
    <span class="fs-20 fw-500 text-header">
        <img class="imgChannel" src="<?php echo esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/web_store.png'); ?>" alt="">
        <?php esc_html_e("Feed Management","product-feed-manager-for-woocommerce")?>
    </span>
    <div class="text-grey fs-16 fw-400">
        <?php esc_html_e("Manage your product feeds to keep your online store information up-to-date and accurate. This includes adding new products, updating prices, and ensuring all product details meet platform requirements. Effective feed management helps your products appear correctly on shopping channels and improves your visibility to potential customers.
","product-feed-manager-for-woocommerce")?>
    </div>
   
    <div class="row mt-2">        
        <div class="container-fluid bg-white mt-2 rounded-6" >    
            <div id="save_feed_loader" class="progress-materializecss d-none" style="width:100%">
                <div class="indeterminate"></div>
            </div>        
            <ul class="nav nav-pills" id="pills-tab" role="tablist">
                <li style="pointer-events: none;" class="nav-item" role="presentation">
                    <span class="nav-link active" id="pills-enter-feed-details-tab" data-bs-toggle="pill" data-bs-target="#pills-enter-feed-details" role="tab" aria-controls="pills-enter-feed-details" aria-selected="true">Enter Feed Details &nbsp;  ></span>
                </li>
                <li style="pointer-events: none;" class="nav-item" role="presentation">
                    <span class="nav-link" id="pills-map-product-category-tab" data-bs-toggle="pill" data-bs-target="#pills-map-product-category" type="" role="tab" aria-controls="pills-map-product-category" aria-selected="false">Map Product Category &nbsp; ></span>
                </li>
                <li style="pointer-events: none;" class="nav-item" role="presentation">
                    <span class="nav-link" id="pills-map-product-attribute-tab" data-bs-toggle="pill" data-bs-target="#pills-map-product-attribute" type="" role="tab" aria-controls="pills-map-product-attribute" aria-selected="false">Map Product Attribute</span>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content mt-3 " id="pills-tabContent">
                <div class="tab-pane fade show active mb-3" id="pills-enter-feed-details" role="tabpanel" aria-labelledby="pills-enter-feed-details-tab">
                    <div class="">
                        <input type="text" autocomplete="off" class="form-control form-control-sm" name="feedName" id="feedName" placeholder="Feed Name" style="width:30%"/>
                    </div>     
                    <div class="mt-4">
                        <label class="fw-600 fs-14">
                            Auto Sync :
                        </label>
                        <div class="form-check form-check-inline ms-2">
                            <input class="form-check-input" type="radio" name="autoSync" id="auto-sync-on" value="1">
                            <label class="form-check-label" for="auto-sync-on">ON</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="autoSync" id="auto-sync-off" value="0">
                            <label class="form-check-label" for="auto-sync-off">OFF</label>
                        </div>
                    </div>   
                    <div class="mt-4 d-flex align-items-center">
                        <label class="fw-600 fs-14 float-start me-2">
                            Sync Interval :                            
                        </label>
                        <input type="text" class="form-control form-control-sm float-start rounded" name="autoSyncIntvl" id="autoSyncIntvl" readonly value="25" style="width:5%"/>
                        <label class="ms-2">Days</label>
                        <span class="ms-2">
                            <a target="_blank" href="<?php echo esc_attr($Convpfm_TVC_Admin_Helper->get_conv_pro_link_adv("pop_up", "auto_sync", "", "linkonly")) ?>">
                                <img class="imgChannel" src="<?php echo esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/upgrade_badge.png'); ?>" alt="">
                                <b> Available In Pro</b>
                            </a>
                        </span>
                    </div> 
                    <div class="mt-4">
                        <select class="select2 form-select form-select-sm mb-3" aria-label="form-select-sm example" style="width: 30%" name="target_country" id="target_country">
                                <option value="">Select Target Country</option>
                                <?php
                                $selecetdCountry = $conv_data['user_country'];
                                foreach ($contData as $key => $value) {
                                    ?>
                                    <option value="<?php echo esc_attr($value->code) ?>" <?php echo $selecetdCountry === $value->code ? 'selected = "selecetd"' : '' ?>><?php echo esc_html($value->name) ?></option>"
                                    <?php
                                }
                                ?>
                        </select>
                    </div> 
                    <div class="mt-4">
                        <label class="fw-600 fs-14">
                            Select Channel
                        </label> 
                        <div class="mt-2">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="channel" id="gmc_id" value="1" <?php echo $google_merchant_center_id !== '' ? "" : 'disabled' ?> >
                                <label class="form-check-label" for="gmc_id">
                                    <img style="width: 24px" src="<?php echo esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/google_shopping_icon.png'); ?>" alt="">
                                    Google Merchant Center
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="channel" id="fb_id" value="2" <?php echo $facebook_catalog_id !== '' ? "" : 'disabled' ?> >
                                <label class="form-check-label" for="fb_id">
                                    <img style="width: 24px" src="<?php echo esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/metaChannel.png'); ?>" alt="">                            
                                    Facebook Catalog
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="channel" id="tiktok_catalog_id" value="3" <?php echo $tiktok_business_account !== '' ? "" : 'disabled' ?> >
                                <label class="form-check-label" for="tiktok_catalog_id">
                                    <img style="width: 24px" src="<?php echo esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/conv_tiktok_logo.png'); ?>" alt="">
                                    TikTok Catalog
                                </label>
                                <input type="hidden" name="tiktok_catalog_id-value" id="tiktok_catalog_id-value" value="">
                            </div>
                        </div>
                        <span class="text-soft-danger tiktok-error fw-600 fs-12"></span>
                    </div>  
                    <div class="mt-4">
                        <label class="fw-600 fs-14 me-1">
                            Batch Size :
                        </label>
                        <select id="product_batch_size" class="form-select" style="width: auto !important">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option selected="selected" value="100">100</option>
                            <option value="200">200</option>
                        </select>
                    </div>
                    <div class="mt-4 d-flex justify-content-end">
                        <button class="btn btn-outline-secondary col-2 cancelFeed me-2" style="z-index: 2147483650">Cancel</button>
                        <button class="btn btn-primary col-2 createFeedBtn" disabled style="z-index: 2147483650">Next</button>
                    </div>        
                </div>
                <div class="tab-pane fade" id="pills-map-product-category" role="tabpanel" aria-labelledby="pills-map-product-category-tab">
                    <span class="fw-600 fs-18">Category Mapping</span>
                    <div class="conv-light-grey-bg rounded-top" >
                        <div class="col-12">  
                            <div class="row">
                                <div class="col-6 p-2 ps-4" >
                                    <span class="fs-14 fw-normal text-grey">
                                        <img src="<?php echo esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/woocommerce_logo.png'); ?>" />
                                        <?php esc_html_e("WooCommerce Product Category", "product-feed-manager-for-woocommerce") ?>
                                    </span>
                                </div>
                                <div class="col-6 p-2">
                                    <span class="ps-0 fs-14 fw-normal text-grey">
                                        <img src="<?php echo esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/conversios_logo.png'); ?>" />
                                        <?php esc_html_e("Conversios Product Category", "product-feed-manager-for-woocommerce") ?></span>
                                </div>
                                
                            </div>                            
                        </div>
                    </div>
                    <div class="mb-2 rounded-bottom border-end border-bottom border-start" style="overflow-y: scroll; overflow-x: hidden; max-height: calc(100vh - 160px); position: relative; @media (max-width: 576px) { max-height: calc(100vh - 120px); } @media (min-width: 576px) and (max-width: 768px) { max-height: calc(100vh - 140px); } @media (min-width: 768px) and (max-width: 992px) { max-height: calc(100vh - 160px); } @media (min-width: 992px) and (max-width: 1200px) { max-height: calc(100vh - 180px); } @media (min-width: 1200px) { max-height: calc(100vh - 200px); }">
                    <!-- <div class="mb-2 rounded-bottom border-end border-bottom border-start" style="overflow-y: scroll; overflow-x: hidden; max-height:450px; position: relative"> -->
                        <form id="category_mapping" action="">
                        <?php 
                            $category_html = $category_wrapper_obj->category_table_content(0, 0, 'mapping', $convpfm_prod_mapped_cats); 
                            echo wp_kses($category_html, array(
                                    "div" => array(
                                        'class' => array(),
                                        'style' => array(),
                                        'id' => array(),
                                        'title' => array(),
                                    ),
                                    "button" => array(
                                        'type' => array(),
                                        'class' => array(),
                                        'style' => array(),
                                        'id' => array(),
                                        'title' => array(),
                                    ),
                                    "select" => array(
                                        'name' => array(),
                                        'class' => array(),
                                        'id' => array(),   
                                        'style' => array('display'),    
                                        'catid' => array(),
                                        'onchange' => array(),
                                        'iscategory' => array(),
                                        'tabindex' => array(),
                                    ),
                                    "option" => array(
                                        'value' => array(),
                                        'selected' => array(),
                                    ),
                                    "span" => array(
                                        'class' => array(),
                                        'style' => array(),
                                        'id' => array(),
                                        'title' => array(),
                                        'data-bs-toggle' => array(),
                                        'data-bs-placement' => array(),
                                        'data-cat-id' => array(),
                                        'data-id' => array(),
                                    ),
                                    "input" => array(
                                        'type' => array(),
                                        'name' => array(),
                                        'class' => array(),
                                        'id' => array(),
                                        'placeholder' => array(),
                                        'style' => array(),
                                        'value' => array(),
                                    ),                                        
                                    "label" => array(
                                        'class' => array(),
                                        'id' => array(),
                                        'style' => array(),
                                    ),
                                    "small" => array(),
                                )
                            );
                        ?>
                        </form>
                    </div>
                    <div class="mt-4 d-flex justify-content-end mb-2">
                        <button class="btn btn-outline-secondary col-2 backToCreateFeed me-2" style="z-index: 2147483650">Back</button>
                        <button class="btn btn-primary col-2 categoryBtn" style="z-index: 2147483650">Next</button>
                    </div>
                </div>
                <div class="tab-pane fade" id="pills-map-product-attribute" role="tabpanel" aria-labelledby="pills-map-product-attribute-tab">
                    <span class="fw-600 fs-18">Category Mapping</span>
                    <div class="conv-light-grey-bg rounded-top" >
                        <div class="col-12">  
                            <div class="row">                                
                                <div class="col-6 p-2 ps-4">
                                    <span class="ps-0 fs-14 fw-normal text-grey">
                                        <img src="<?php echo esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/conversios_logo.png'); ?>" />
                                        <?php esc_html_e("Conversios Product Attribute", "product-feed-manager-for-woocommerce") ?></span>
                                </div>
                                <div class="col-6 p-2" >
                                    <span class="fs-14 fw-normal text-grey">
                                        <img src="<?php echo esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/woocommerce_logo.png'); ?>" />
                                        <?php esc_html_e("WooCommerce Product Attribute", "product-feed-manager-for-woocommerce") ?>
                                    </span>
                                </div>
                            </div>  
                        </div>   
                    </div>  
                    <div class="mb-2 attributeDiv rounded-bottom border-end border-bottom border-start" style="overflow-y: scroll; overflow-x: hidden; max-height: calc(100vh - 450px); position: relative;">
                    <!-- <div class="mb-2 attributeDiv rounded-bottom border-end border-bottom border-start" style="overflow-y: scroll; overflow-x: hidden; max-height:450px; position: relative"> -->
                        <form id="attribute_mapping" class="row">
                        <?php foreach ($gmcAttributes as $key => $attribute) { 
                            if (is_array($tempAddAttr) && !empty($tempAddAttr)) {
                                unset($tempAddAttr[$attribute["field"]]);
                            }
                            
                            $sel_val = ""; ?>
                            <div class="row mt-1 attributehoverEffect">
                                <div class="col-6 P-2 PS-4 d-flex align-items-center">
                                    <span class="ps-3 font-weight-400 text-color fs-12">
                                    <?php echo esc_attr($attribute["field"])." ".(isset($attribute["required"]) && esc_attr($attribute["required"]) === '1' ? '<span class="text-color fs-6"> *</span>' : ""); ?>
                                        <span class="material-symbols-outlined fs-6 pointer" data-bs-toggle="tooltip"
                                            data-bs-placement="right"
                                            title="<?php echo (isset($attribute['desc']) ? esc_attr($attribute['desc']) : ''); ?>">
                                            info
                                        </span>                                                        
                                    </span>
                                    <div class="float-end mt-2 mx-auto">
                                    <?php 
                                    if($attribute["field"]=='id'){ ?>
                                        <input type="text" class="form-control" name="product_id_prefix" id="product_id_prefix" placeholder="Add Prefix" value="" >
                                    <?php } ?>
                                    </div>
                                </div>
                                <div  class="col-5 mt-2">
                                    <?php
                                    $convpfm_select_option = $Convpfm_TVC_Admin_Helper->add_additional_option_in_tvc_select($wooCommerceAttributes, $attribute["field"]);
                                    $require = (isset($attribute['required']) && $attribute['required']) ? true : false;
                                    $sel_val_def = (isset($attribute['wAttribute'])) ? $attribute['wAttribute'] : "";
                                    if ($attribute["field"] === 'link') {
                                            "product link";
                                    } else if ($attribute["field"] === 'shipping') {
                                        $sel_val = (isset($convpfm_mapped_attrs[$attribute["field"]])) ? $convpfm_mapped_attrs[$attribute["field"]] : $sel_val_def;
                                            $Convpfm_TVC_Admin_Helper->tvc_text($attribute["field"], 'number', '', esc_html__('Add shipping flat rate', 'product-feed-manager-for-woocommerce'), $sel_val, $require);
                                    } else if ($attribute["field"] === 'tax') {
                                        $sel_val = (isset($convpfm_mapped_attrs[$attribute["field"]])) ? esc_attr($convpfm_mapped_attrs[$attribute["field"]]) : esc_attr($sel_val_def);
                                            $Convpfm_TVC_Admin_Helper->tvc_text($attribute["field"], 'number', '', 'Add TAX flat (%)', $sel_val, $require);
                                    } else if ($attribute["field"] === 'content_language') {
                                            $Convpfm_TVC_Admin_Helper->tvc_language_select($attribute["field"], 'content_language', esc_html__('Please Select Attribute', 'product-feed-manager-for-woocommerce'), 'en', $require);
                                    } else if ($attribute["field"] === 'target_country') {
                                            $Convpfm_TVC_Admin_Helper->tvc_countries_select($attribute["field"], 'target_country', esc_html__('Please Select Attribute', 'product-feed-manager-for-woocommerce'), $require);
                                    } else {
                                        if (isset($attribute['fixed_options']) && $attribute['fixed_options'] !== "") {
                                            $convpfm_select_option_t = explode(",", $attribute['fixed_options']);
                                            $convpfm_select_option = [];
                                            foreach ($convpfm_select_option_t as $o_val) {
                                                $convpfm_select_option[]['field'] = esc_attr($o_val);
                                            }
                                            $sel_val = $sel_val_def;
                                            $Convpfm_TVC_Admin_Helper->tvc_select($attribute["field"], $attribute["field"], esc_html__('Please Select Attribute', 'product-feed-manager-for-woocommerce'), $sel_val, $require, $convpfm_select_option);
                                        } else {
                                            $sel_val = (isset($convpfm_mapped_attrs[$attribute["field"]])) ? $convpfm_mapped_attrs[$attribute["field"]] : $sel_val_def;
                                            $Convpfm_TVC_Admin_Helper->tvc_select($attribute["field"], $attribute["field"], esc_html__('Please Select Attribute', 'product-feed-manager-for-woocommerce'), $sel_val, $require, $convpfm_select_option);
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        <?php } ?>
                        
                        <div class="col-12 additinal_attr_main_div">                                            
                        <?php
                            $cnt = 0;
                            if(!empty($tempAddAttr)) {
                                $additionalAttribute = array('condition','shipping_weight','product_weight','gender','sizes','color','age_group','additional_image_links', 'sale_price_effective_date','material',
                                                            'pattern','product_types','availability_date','expiration_date','adult', 'ads_redirect',
                                                            'shipping_length','shipping_width', 'shipping_height','custom_label_0',
                                                            'custom_label_1','custom_label_2',
                                                            'custom_label_3','custom_label_4','mobile_link','energy_efficiency_class',
                                                            'is_bundle','loyalty_points','unit_pricing_measure','unit_pricing_base_measure',
                                                            'promotion_ids','shipping_label','excluded_destinations','included_destinations','tax_category',
                                                            'multipack','installment','min_handling_time','max_handling_time','min_energy_efficiency_class',
                                                            'max_energy_efficiency_class','identifier_exists','cost_of_goods_sold');                                               
                                $count_arr = count($additionalAttribute);
                                foreach($tempAddAttr as $key => $value){ 
                                    $options = '<option>Please Select Attribute</option>';
                                    foreach($additionalAttribute as $val ) { 
                                        $selected = "";
                                        $disabled = "";                                                       
                                        if($val == $key) {
                                            $selected = "selected";
                                        }else{
                                            if(array_key_exists($val, $tempAddAttr)) {
                                                $disabled = "disabled"; 
                                            }
                                        }
                                        
                                        $options .= '<option value="'.$val.'" '.$selected.' '.$disabled.'>'.esc_html($val).'</option>';
                                        } 
                                        $option1 = '<option>Please Select Attribute</option>';
                                        $fixed_att_select_list = ["gender", "age_group", "condition", "adult", "is_bundle", "identifier_exists"];
                                        if(in_array($key, $fixed_att_select_list)) {
                                        if($key == 'gender') {    
                                            $gender = ['male' => 'Male', 'female' => 'Female', 'unisex' => 'Unisex'];
                                            foreach($gender as $genKey => $genVal) {
                                            $selected = "";
                                            if($genKey == $value) {
                                                $selected = "selected";
                                            }
                                            $option1 .= '<option value="'.$genKey.'" '.$selected.'>'.esc_html($genVal).'</option>';
                                            }
                                        }
                                        if($key == 'condition') {
                                            $conArr = ['new' => 'New', 'refurbished' => 'Refurbished', 'used' => 'Used'];
                                            foreach($conArr as $conKey => $conVal) {
                                            $selected = "";
                                            if($conKey == $value) {
                                                $selected = "selected";
                                            }
                                            $option1 .= '<option value="'.$conKey.'" '.$selected.'>'.esc_html($conVal).'</option>';
                                            }
                                        }
                                        if($key == 'age_group') {    
                                            $ageArr = ['newborn' => 'Newborn', 'infant' => 'Infant', 'toddler' => 'Toddler', 'kids' => 'Kids', 'adult' => 'Adult'];            
                                            foreach($ageArr as $ageKey => $ageVal) {
                                            $selected = "";
                                            if($ageKey == $value) {
                                                $selected = "selected";
                                            }
                                            $option1 .= '<option value="'.$ageKey.'" '.$selected.'>'.$ageVal.'</option>';
                                            }                                                        
                                        }
                                        if ($key == 'adult' || $key == 'is_bundle' || $key == 'identifier_exists') {
                                            $boolArr = ['yes' => 'Yes', 'no' => 'No'];
                                            foreach ($boolArr as $boolKey => $boolVal) {
                                                $selected = "";
                                                if ($boolKey == $value) {
                                                $selected = "selected";
                                                }
                                                $option1 .= '<option value="' . $boolKey . '" ' . $selected . '>' . esc_html($boolVal) . '</option>';
                                            }
                                        }
                                        }else {
                                        foreach($wooCommerceAttributes as $valattr ) { 
                                            $selected = "";
                                            if($valattr['field'] == $value) {
                                                $selected = "selected";
                                            }
                                            $option1 .= '<option value="'.$valattr['field'].'" '.$selected.'>'.$valattr['field'].'</option>';
                                        } 
                                    }
                                    ?>
                                <div class="row mt-1 attributehoverEffect additinal_attr_div m-0 p-0" >
                                    <div class="col-6 mt-2">
                                        <select style="width:98%" id="<?php echo esc_attr($cnt++) ?>" name="additional_attr_[]" class="selectAttr additinal_attr fw-light text-secondary fs-6 form-control form-select-sm">
                                        <?php 
                                            echo wp_kses($options, array(                                                            
                                                "option" => array(
                                                'value' => array(),
                                                'selected' => array(),
                                                ),
                                            ));
                                        ?>
                                        </select>
                                    </div>
                                    <div class="col-5 mt-2" style="padding-left: 0px" >
                                        <select style="width:98%" id="" name="additional_attr_value_[]" class="selectAttr additional_attr_value fw-light text-secondary fs-6 form-control form-select-sm">
                                        <?php 
                                            echo wp_kses($option1, array(                                                            
                                                "option" => array(
                                                'value' => array(),
                                                'selected' => array(),
                                                ),
                                            ));
                                        ?>    
                                        </select>
                                    </div>
                                    <div class="col-1 mt-2">                                                        
                                        <span class="material-symbols-outlined text-danger remove_additional_attr fs-5 mt-2 pointer" title="Add Additional Attribute" style="cursor: pointer; margin-right:35px;">
                                        delete
                                        </span>
                                    </div>
                                </div>
                            <?php }
                        } ?>
                        </div>
                        </form>
                        <div class="row add_additional_attr_div m-0 p-0 mb-2" >
                            <div class="add_additional_attr_div mt-2" style="display: flex; justify-content: start">
                                <button type="button" class="fs-12 btn btn-soft-primary add_additional_attr pointer" title="Add Attribute"> Add Attributes
                                </button>                                                    
                            </div>
                        </div>
                    </div>  
                    
                    <div class="mt-4 d-flex justify-content-end mb-2">
                        <button class="btn btn-outline-secondary col-2 backTocategory me-2" style="z-index: 2147483650">Back</button>
                        <button class="btn btn-primary col-2 finishBtn" style="z-index: 2147483650">Finish</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Nav Pills -->
        
    </div>

</div>
<div class="modal fade" id="campign-pop-up" tabindex="-1" aria-labelledby="exitModalLabel" aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div id="loadingbar_blue_modal_campign" class="progress-materializecss d-none ps-2 pe-2" style="width:100%">
                <div class="indeterminate"></div>
            </div>
            <div class="modal-header border-0 pb-0" style="justify-content:flex-start">
                <img class="imgChannel" src="<?php echo esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/google_modal.png'); ?>" alt="">
                <h5 class="fw-600 fs-20 ms-2" style="margin-left: 1px">Performance Max Campaign</h5>                
            </div>
            <div class="modal-body max-campaign pb-0">
                <div class="alert alert-warning mt-2" role="alert">
                You can create maximum 1000 product campaign at once. Please select appropriate Feed.
                </div>
                <div class="container-fluid p-0">
                    <span class="otherError text-danger"></span>
                    <div class="row mb-4">                        
                        <div class="col-6">
                            <input type="text" id="campaignName" name="campaignName" class="form-control" placeholder="Enter Campaign Name">
                        </div>
                        <div class="col-6">
                            <input type="text" id="daily_budget" name="daily_budget" class="form-control" placeholder="Enter Daily Budget">
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-6">
                            <select id="target_country_campaign" name="target_country_campaign" class="form-control" style="width:100%">
                            <option value="">Select Country</option>
                            <?php
                            $selecetdCountry = $conv_data['user_country']; 
                            foreach($contData as $country) { ?>
                                <option value="<?php echo esc_html($country->code) ?>" <?php echo $selecetdCountry === $country->code ? 'selected = "selecetd"' : '' ?> ><?php echo esc_html($country->name) ?></option>
                            <?php }
                            ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <input type="text" id="target_roas" name="target_roas" class="form-control" placeholder="Add Target ROAS (%)"><span class="fs-10">Formula: Conversion value ÷ ad spend x 100% = target ROAS percentage</span>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-6">
                            <input type="date" id="start_date" name="start_date" class="form-control" placeholder="Start Date">
                            <span class="startDateError text-danger fs-10"></span>
                        </div>
                        <div class="col-6">                    
                            <input type="date" id="end_date" name="end_date" class=form-control placeholder="End Date">
                            <span class="endDateError text-danger fs-10"></span>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-6">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="status" id="inlineRadio1" value="ENABLED" checked>
                                <label class="form-check-label" for="inlineRadio1">Enable</label>
                            </div>
                            <div class="form-check form-check-inline ml-2">
                                <input class="form-check-input" type="radio" name="status" id="inlineRadio2" value="PAUSED">
                                <label class="form-check-label" for="inlineRadio2">Pause</label>
                            </div>
                        </div>
                        <div class="col-6 d-flex justify-content-end">
                            <button type="button" class="btn btn-soft-primary fs-14 fw-400" id="submitCampaign">Create Campaign</button>
                        </div>
                        <!-- <input type="hidden" id="campaign_feed_id" name="campaign_feed_id" value="<?php //echo esc_attr(filter_input(INPUT_GET,'id')) ?>"> -->
                        <input type="hidden" id="selecetdCampaign" name="selecetdCampaign" value="">
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0 p-0">  
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="infoModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center infoBody">
                <img class="" src="<?php echo esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/successImg.png'); ?>" alt="" style="width:150px; height:150px;">
                <div class="text-success">
                    Success! Your operation was completed.
                </div>
                <div class="text-dark fs-12 mt-2">
                Exciting things are happening behind the scenes! We're crafting your Pmax campaign for Google Ads with precision. Your products are gearing up to shine. Sit tight, and get ready for an amplified reach and increased sales.
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-center infoFooter" style="border-top:none">
                
            </div>
        </div>
    </div>
</div>
<?php 
$fpath = CONVPFM_ENHANCAD_PLUGIN_DIR . 'includes/setup/json/category.json';
$filesystem = new WP_Filesystem_Direct( true );
$str = $filesystem->get_contents($fpath);
$str = json_decode($str);
?>
<script>
    var cat_json = <?php echo wp_json_encode($str) ?>;
    jQuery(document).ready(function () {  
        jQuery('#feed_list_table').DataTable({            
            order: [[5, 'desc']],           
            rowReorder: true,
            columnDefs: [
                { orderable: true, targets: 1 },
                { orderable: true, targets: 2 },
                { orderable: true, targets: 4 },
                { orderable: true, targets: 5 },
                { orderable: true, targets: 6 },
                { orderable: true, targets: 7 },
                // { orderable: true, targets: 8 },
                { orderable: false, targets: '_all' },

            ],

            initComplete: function () {
                jQuery('#search_feed').on('input', function () {
                    jQuery('#feed_list_table').DataTable().search(jQuery(this).val()).draw();
                });
            }
        });
        jQuery('.dataTables_filter').addClass('d-none');
        jQuery('#target_country').select2()
        jQuery('.categorySelect').select2()        
        jQuery('.attributeClass').select2()
        jQuery('.selectAttr').select2()
        jQuery('#product_batch_size').select2()
        var tempArr = <?php echo json_encode($tempAddAttr) ?> 
        var arr = Object.keys(tempArr).map(function (key) { return key; });                                           
        selected = arr;
        
        jQuery(document).on('change, input','#feedName, input[name="autoSync"], #target_country, input[name="channel"]', function(event) {
            jQuery(this).removeClass('errorInput')
            if(this.name == 'channel') {
                jQuery(".tiktok-error").html('');
                jQuery('#tiktok_catalog_id-value').val('');
                jQuery('input[name="channel"]').removeClass('errorInput')
            }
            if(this.name == 'autoSync') {
                jQuery('input[name="autoSync"]').removeClass('errorInput')
            }
            checkFeedInputs()            
            if(this.id === 'tiktok_catalog_id') {                
                getCatalogId(jQuery('#target_country').find(":selected").val())
            }
            if(this.id === 'target_country' && jQuery('input[name="channel"]:checked').val() == '3') {
                getCatalogId(jQuery('#target_country').find(":selected").val())
            }
        })
        jQuery(document).on('click', '.createFeedBtn', function() {  
            jQuery('#pills-enter-feed-details-tab').addClass('mousehoverEffect')      
            jQuery('[data-bs-target="#pills-map-product-category"]').trigger('click')            
        })
        jQuery(document).on('click', '.categoryBtn', function() {       
            jQuery('#pills-map-product-category-tab').addClass('mousehoverEffect') 
            jQuery('[data-bs-target="#pills-map-product-attribute"]').trigger('click') 
        })
        jQuery(document).on('click', '.backToCreateFeed', function() {        
            jQuery('[data-bs-target="#pills-enter-feed-details"]').trigger('click')  
            checkFeedInputs()          
        })
        jQuery(document).on('click', '.backTocategory', function() {        
            jQuery('[data-bs-target="#pills-map-product-category"]').trigger('click')            
        })
        jQuery(document).on('click', '.select2-selection.select2-selection--single', function (e) {
            var iscatMapped = jQuery(this).parent().parent().prev().attr('iscategory')
            var selectId = jQuery(this).parent().parent().prev().attr('id')
            var toAppend = '';           
            if (iscatMapped == 'false') {
                jQuery(this).parent().parent().prev().attr('iscategory', 'true')
                jQuery.each(cat_json, function (i, o) {
                    toAppend += '<option value="' + o.id + '">' + o.name + '</option>';
                });
                jQuery('#' + selectId).append(toAppend)
                jQuery('#' + selectId).select2()
                jQuery('#' + selectId).select2('open')
            }
        });
        jQuery(document).on('mouseenter', '.catTermId', function() {
            jQuery(this).addClass('mousehoverEffect')
            jQuery(this).find('.select2-selection--single').addClass('mousehoverBorder')
        })
        jQuery(document).on('mouseleave', '.catTermId', function() {
            jQuery(this).removeClass('mousehoverEffect')
            jQuery(this).find('.select2-selection--single').removeClass('mousehoverBorder')
        })
        jQuery(document).on('mouseenter', '.attributehoverEffect', function() {
            jQuery(this).addClass('mousehoverEffect')
            jQuery(this).find('.select2-selection--single').addClass('mousehoverBorder')
            jQuery(this).find('input').addClass('mousehoverBorder')
        })
        jQuery(document).on('mouseleave', '.attributehoverEffect', function() {
            jQuery(this).removeClass('mousehoverEffect')
            jQuery(this).find('.select2-selection--single').removeClass('mousehoverBorder')
            jQuery(this).find('input').removeClass('mousehoverBorder')
        })
        jQuery(document).on('click', '.finishBtn', function() {
            let feedName = jQuery('#feedName').val()
            var hasError = false ;
            if (feedName === '') {                
                jQuery('#feedName').addClass('errorInput')
                hasError = true
            }
            
            let autoSync = jQuery('input[name="autoSync"]:checked').val()
            if (autoSync === undefined) {                
                jQuery('input[name="autoSync"]').addClass('errorInput')
                hasError = true
            }

            let target_country = jQuery('#target_country').find(":selected").val()
            if (target_country === "") {
                jQuery('.select2-selection').css('border', '1px solid #ef1717')
                hasError = true
            }
            let channel = jQuery('input[name="channel"]:checked').val()
            if (channel === undefined) {
                jQuery('input[name="channel"]').addClass('errorInput')               
                hasError = true
            }

            if(hasError == true) {
                jQuery('[data-bs-target="#pills-enter-feed-details"]').trigger('click')
                return false;
            }

            /****additional Attribute validation start*********/
                var attrValidation = false;
                jQuery(".additinal_attr").each(function () {
                    if (this.selectedIndex === 0) {
                        jQuery(this).parent().find('.select2-selection--single').addClass('errorInput');
                        attrValidation = true;                        
                    }
                })
                
                jQuery(".additional_attr_value").each(function () {
                    if (this.selectedIndex === 0) {
                        jQuery(this).parent().find('.select2-selection--single').addClass('errorInput');
                        attrValidation = true;
                    }
                })
                if(attrValidation === true) {
                    return false;
                }
            /****additional Attribute validation end*********/
            createFeed();
        })

        jQuery(document).on('click', '#create_new_feed, .createNewFeedButton', function() {
            jQuery('.feed-list-div').addClass('d-none')
            jQuery('.no-feed-div').addClass('d-none')
            jQuery('.create-feed-div').removeClass('d-none')
        })
        jQuery(document).on('click', '.cancelFeed', function() { 
            window.location.replace("<?php echo esc_url_raw($site_url.'feed_list'); ?>");
        })
        jQuery(document).on('change', '.additinal_attr', function() {
            var fixed_att_select_list = ["gender", "age_group", "condition", "adult", "is_bundle", "identifier_exists"];
            var attr = jQuery(this).val();
            if(jQuery.inArray( attr, fixed_att_select_list ) !== -1){
                var option1 = '<option value="">Please Select Attribute</option>';
                if(attr == 'gender') {
                    option1 += '<option value="male">Male</option><option value="female">Female</option><option value="unisex">Unisex</option>'
                }
                if(attr == 'condition') {
                    option1 += '<option value="new">New</option><option value="refurbished">Refurbished</option><option value="used">Used</option>'
                }
                if(attr == 'age_group') {
                    option1 += '<option value="newborn">Newborn</option><option value="infant">Infant</option><option value="toddler">Toddler</option><option value="kids">Kids</option><option value="adult">Adult</option>'
                }
                if(attr == 'adult' || attr == 'is_bundle' || attr == 'identifier_exists') {
                    option1 += '<option value="yes">Yes</option><option value="no">No</option>'
                }
                jQuery(this).parent().next().find('.additional_attr_value').html(option1)
            } else {
                var wooCommerceAttributes = <?php echo wp_json_encode($wooCommerceAttributes); ?>;
                var option1 = '<option value="">Please Select Attribute</option>';
                jQuery.each(wooCommerceAttributes, function (index, value) {
                    option1 += '<option value="'+value.field+'">'+value.field+'</option>'
                });
                jQuery(this).parent().next().find('.additional_attr_value').html(option1)
            }
        }) 
        jQuery(document).on('change', '.additional_attr_value', function() { 
            jQuery(this).parent().removeClass('errorInput');
        });

        var selected = Array();
        var cnt = <?php echo $cnt ?>;
        jQuery(document).on('click', '.add_additional_attr', function() {                                                
            var additionalAttribute=[
                {"field":"condition"},{"field":"shipping_weight"},{"field":"product_weight"},
                {"field":"gender"},{"field":"sizes"},{"field":"color"},{"field":"age_group"},
                {"field":"additional_image_links"},{"field":"sale_price_effective_date"},
                {"field":"material"},{"field":"pattern"},{"field":"availability_date"},{"field":"expiration_date"},
                {"field":"product_types"},{"field":"ads_redirect"},{"field":"adult"},{"field":"shipping_length"},
                {"field":"shipping_width"},{"field":"shipping_height"},{"field":"custom_label_0"},{"field":"custom_label_1"},
                {"field":"custom_label_2"},{"field":"custom_label_3"},{"field":"custom_label_4"},{"field":"mobile_link"},
                {"field":"energy_efficiency_class"},{"field":"is_bundle"},{"field":"promotion_ids"},{"field":"loyalty_points"},
                {"field":"unit_pricing_measure"},{"field":"unit_pricing_base_measure"},{"field":"shipping_label"},
                {"field":"excluded_destinations"},{"field":"included_destinations"},{"field":"tax_category"},
                {"field":"multipack"},{"field":"installment"},{"field":"min_handling_time"},{"field":"max_handling_time"},
                {"field":"min_energy_efficiency_class"},{"field":"max_energy_efficiency_class"},{"field":"identifier_exists"},
                {"field":"cost_of_goods_sold"}];

            var count = Object.keys(additionalAttribute).length;
            var option = '<option value="">Please Select Attribute</option>';
            jQuery.each(additionalAttribute, function (index, value) {
                /*****Check for selected option to disabled start*******/
                var disabled = "";                                                    
                if(jQuery.inArray(value.field, selected) !== -1){
                    disabled = "disabled";
                }
                /*****Check for selected option to disabled end*******/                                              
                option += '<option value="'+value.field+'" '+ disabled +'>'+value.field+'</option>'
            });
            var wooCommerceAttributes = <?php echo wp_json_encode($wooCommerceAttributes); ?>;
            var option1 = '<option value="">Please Select Attribute</option>';
            jQuery.each(wooCommerceAttributes, function (index, value) {
                option1 += '<option value="'+value.field+'">'+value.field+'</option>'
            });

            var html = '';
            html += '<div class="row mt-1 attributehoverEffect additinal_attr_div m-0 p-0" ><div class="col-6 mt-2">';
            html += '<select style="width:98%" id="'+ cnt++ +'" name="additional_attr_[]" class="selectAttr additinal_attr fw-light text-secondary fs-6 form-control form-select-sm select2 select2-hidden-accessible">';
            html += option;
            html += '</select></div>';
            html += '<div class="col-5 mt-2" style="padding-left: 0px;">';
            html += '<select style="width:98%" id="" name="additional_attr_value_[]" class="selectAttr additional_attr_value fw-light text-secondary fs-6 form-control form-select-sm select2 select2-hidden-accessible">';
            html += option1;
            html += '</select></div>';
            html += '<div class="col-1 mt-2">';
            html += '<span class="material-symbols-outlined text-danger remove_additional_attr fs-5 mt-2 pointer" title="Add Additional Attribute" style="cursor: pointer; margin-right:35px;">';
            html += 'delete';
            html += '</span>';                                               
            html += '</div></div>';
            jQuery('.additinal_attr_main_div').append(html)
            jQuery('.selectAttr').select2()      
            jQuery('.add_additional_attr')[0].scrollIntoView(true)
            var div_count = jQuery('.additinal_attr_div').length;
            if(count == div_count){
                jQuery('.add_additional_attr').addClass('d-none')
            }
        });
        jQuery(document).on('click', '.remove_additional_attr', function() {
            jQuery('.remove_additional_attr *').addClass('disabled');
            //get deleted selected tag value
            var deleted = jQuery(this).parent().parent('.additinal_attr_div').find('.additinal_attr').find(':selected').val()
            if(deleted != ''){
                //Remove value from array
                selected = jQuery.grep(selected, function(value) {
                            return value != deleted
                        });
            //Enable deleted value to other selecet tag
            jQuery(".additinal_attr option").each(function() {
                    var $thisOption = jQuery(this)
                    var valueToCompare = deleted
                    if($thisOption.val() == valueToCompare) {
                        $thisOption.removeAttr("disabled")
                    }
                });  
            }
                                                                                        
            jQuery(this).parent().parent('.additinal_attr_div').remove()
            jQuery('.add_additional_attr').removeClass('d-none')
            jQuery('.remove_additional_attr *').removeClass('disabled')
        });
        jQuery(document).on('change', '.additional_attr_value', function() { 
            jQuery(this).parent().find('.select2-selection--single').removeClass('errorInput');
        });
        jQuery(document).on('change', '.additinal_attr', function() {  
            selected = []
            jQuery(this).parent().find('.select2-selection--single').removeClass('errorInput');                                           
            var sel =  jQuery(this).find(":selected").val()
            var id = jQuery(this).attr("id")     
            //All empty select add more used, it will add disable attribute to selected value
            jQuery(".additinal_attr:not(#"+id+") option").each(function() {
                var $thisOption = jQuery(this)
                var valueToCompare = sel
                if($thisOption.val() == valueToCompare) {
                    $thisOption.attr("disabled", "disabled")
                }
            });  
            var attr_choices = jQuery(".additinal_attr option:selected")
            jQuery(attr_choices).each(function(i, v) {
                selected.push(attr_choices.eq(i).val())
            })     
            disableOptions() 
        });
        jQuery(document).on('click','.checkbox_feed', function() {
            if(jQuery(this).prop('checked')) {
                let arr = Array();
                let thisVal = jQuery(this).val();
                let feedstr = jQuery('#selecetdCampaign').val();
                if(feedstr !== '') {
                    arr = feedstr.split(',');
                }            
                arr.push(thisVal);
                arr.join(',');
                jQuery('#selecetdCampaign').val(arr);
                jQuery('.campaignClass').removeClass('disabled');
            } else {
                let arr = Array();
                let thisVal = jQuery(this).val();
                let feedstr = jQuery('#selecetdCampaign').val();
                arr = feedstr.split(',');
                arr = jQuery.grep(arr, function(value) {
                        return value != thisVal;
                    });
                jQuery('#selecetdCampaign').val(arr);
                // jQuery("#selectAll").prop('checked', false)
                if(jQuery('#selecetdCampaign').val() == '') {
                    jQuery('.campaignClass').addClass('disabled');  
                }
            }
        })
        jQuery(document).on('click', '.createCampaign', function() {
            jQuery('.otherError').html( '');
            jQuery('span, input').removeClass('errorInput')
            jQuery('#campign-pop-up').modal('show');
            jQuery('#target_country_campaign').select2({
                'dropdownParent' :jQuery('#campign-pop-up')
            })
            jQuery('#campign-pop-up').modal('show')
        })
        jQuery(document).on('click', '#submitCampaign', function () {     
            //check validation start
            let arrValidate = ['campaignName', 'daily_budget', 'target_country_campaign', 'start_date', 'end_date'];
            let hasError = false;
            jQuery.each(arrValidate, function(i, v) {
                if(jQuery('#'+v).val() == '' && v !== 'target_country_campaign') {
                    jQuery('#'+v).addClass('errorInput');
                    hasError = true
                }
                if(v == 'target_country_campaign' && jQuery('select[name="' + v + '"] option:selected').val() == '') {
                    jQuery('select[name="' + v + '"]').addClass('errorInput');
                    jQuery('select[name="' + v + '"]').next('span').find('.select2-selection--single').addClass('errorInput');
                    hasError = true
                }
            })
            
            
            var todayDate = new Date();
            var eDate = new Date(jQuery('#end_date').val());
            var sDate = new Date(jQuery('#start_date').val());
            if(new Date(sDate.toDateString()) < new Date(todayDate.toDateString())) {
                jQuery('#start_date').addClass('errorInput');
                jQuery(".startDateError").html("Start date is less than today's date.")
                hasError = true
            }
            if(sDate > eDate)
            {
                jQuery('#end_date').addClass('errorInput');
                jQuery('.endDateError').html('Check End Date.')
                return false;
            }
            if(hasError == true) {
                return false;
            }
            let subscriptionId = "<?php echo $subscription_id ?>";
            let google_merchant_center_id = "<?php echo $google_merchant_id ?>";
            let google_ads_id = "<?php echo $google_ads_id ?>";
            let store_id = "<?php echo $store_id ?>";
            if(subscriptionId == '' || google_merchant_center_id == '' || google_ads_id == '' || store_id == '') {
                let missingVal = '';
                if(subscriptionId == '')
                    missingVal = ' Subscription Id is missing';

                if(google_merchant_center_id == '')
                    missingVal = ' Google Merchant Center Id is missing';

                if(google_ads_id == '')
                    missingVal = ' Google Ads Id is missing';

                if(store_id == '')
                    missingVal = ' Store Id is missing';

                jQuery('.otherError').html( missingVal);
                return false;
            }
            //check validation end
            var conv_onboarding_nonce = "<?php echo esc_html(wp_create_nonce('conv_onboarding_nonce')); ?>";
            var data = {
                action: "convpfm_createPmaxCampaign",
                campaign_name: jQuery('#campaignName').val(),
                budget: jQuery('#daily_budget').val(),
                target_country: jQuery('#target_country_campaign').find(":selected").val(),
                start_date: jQuery('#start_date').val(),
                end_date: jQuery('#end_date').val(),
                target_roas: jQuery('#target_roas').val() == '' ? 0 : jQuery('#target_roas').val() ,
                status: jQuery('input[name=status]:checked').val(),       
                subscription_id: "<?php echo esc_js($subscription_id) ?>",
                google_merchant_id: "<?php echo esc_js($google_merchant_id) ?>",
                google_ads_id: "<?php echo esc_js($google_ads_id) ?>",
                sync_item_ids: jQuery('#selecetdCampaign').val(),
                domain: "<?php echo get_site_url() ?>",
                store_id: "<?php echo esc_js($store_id) ?>",
                sync_type: "feed",
                conv_onboarding_nonce: conv_onboarding_nonce
            }
                
            jQuery.ajax({
                type: "POST",
                dataType: "json",
                url: convpfm_ajax_url,
                data: data,
                beforeSend: function () {
                jQuery("#loadingbar_blue_modal_campign").removeClass('d-none');
                jQuery("#wpbody").css("pointer-events", "none");
                jQuery('#submitCampaign').attr('disabled', true);
                },
                error: function (err, status) {
                jQuery("#loadingbar_blue_modal_campign").addClass('d-none');
                jQuery("#wpbody").css("pointer-events", "auto");
                jQuery('#submitCampaign').attr('disabled', false);
                },
                success: function (response) {
                jQuery("#loadingbar_blue_modal_campign").addClass('d-none');
                jQuery("#wpbody").css("pointer-events", "auto");
                jQuery('#submitCampaign').attr('disabled', false);
                jQuery('#campign-pop-up').modal('hide');
                if(response.error == true) {
                    var html = '<img class="" src="<?php echo esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/errorImg.png'); ?>" alt="" style="width:150px; height:150px;">';
                        html += '<div class="text-danger">'+response.message+'</div>';
                    var btn = '<button type="button" class="btn btn-dark" data-bs-dismiss="modal" onclick="location.reload();">Close</button>'
                    jQuery('.infoBody').html(html)
                    jQuery('.infoFooter').html(btn)
                    jQuery('#infoModal').modal('show')
                }else {
                    var html = '<img class="" src="<?php echo esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/successImg.png'); ?>" alt="" style="width:150px; height:150px;">';
                        html += '<div class="text-success">Success! Your operation was completed.</div>';
                        html += '<div class="text-dark fs-12 mt-2">Exciting things are happening behind the scenes! We\'re crafting your Pmax campaign for Google Ads with precision. Your products are gearing up to shine. Sit tight, and get ready for an amplified reach and increased sales.</div>';
                    var btn = '<button type="button" class="btn btn-dark" data-bs-dismiss="modal" onclick="location.reload();">Close</button>'
                    jQuery('.infoBody').html(html)
                    jQuery('.infoFooter').html(btn)
                    jQuery('#infoModal').modal('show')
                }            
                }
            });

        })
        jQuery(document).on('keyup change', '.errorInput', function() {
            jQuery(this).removeClass('errorInput')
            jQuery(this).next('span').find('.select2-selection--single').removeClass('errorInput')
            jQuery('.endDateError').html('')
            jQuery('.startDateError').html('')
        })
        jQuery(document).on('keydown', 'input[name="daily_budget"], input[name="target_roas"]', function () {
            if (event.shiftKey == true) {
                event.preventDefault();
            }
            if ((event.keyCode >= 48 && event.keyCode <= 57) || 
                (event.keyCode >= 96 && event.keyCode <= 105) || 
                event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 37 || 
                event.keyCode == 39 || event.keyCode == 46 || event.keyCode == 190) {

            } else {
                event.preventDefault();
            }

            if (jQuery(this).val().indexOf('.') !== -1 && event.keyCode == 190)
                event.preventDefault();
        })
        jQuery(document).on('show.bs.dropdown', '.dropdown', function () {
            jQuery('.paginate_button').css('z-index', '0');
        });
        jQuery(document).on('hide.bs.dropdown', '.dropdown', function () {
            jQuery('.paginate_button').css('z-index', '2147483650');
        });
    });
    /****************Function to check Feed mandatory field ****************************/
    function checkFeedInputs() {
        let feedName = jQuery('#feedName').val()
        let autoSync = jQuery('input[name="autoSync"]:checked').val()
        let target_country = jQuery('#target_country').find(":selected").val()
        let channel = jQuery('input[name="channel"]:checked').val()
        
        if(feedName != '' && autoSync != undefined && channel != undefined && target_country != '') {
            jQuery('.createFeedBtn').attr('disabled', false)
        } else {
            jQuery('.createFeedBtn').attr('disabled', true)
        }
    }
    /****************Function to check Feed mandatory field ****************************/
    /****************Function to get tiktok catalog id country wise ****************************/
    function getCatalogId(countryCode) {
        var conv_country_nonce = "<?php echo esc_html(wp_create_nonce('conv_country_nonce')); ?>";
        var data = {
            action: "convpfm_getCatalogId",
            countryCode: countryCode,
            conv_country_nonce: conv_country_nonce
        }
        jQuery.ajax({
            type: "POST",
            dataType: "json",
            url: convpfm_ajax_url,
            data: data,
            beforeSend: function () {
                jQuery('.createFeedBtn').css("pointer-events", "none")
            },
            error: function (err, status) {
                jQuery('.createFeedBtn').css("pointer-events", "auto")
            },
            success: function (response) {
                jQuery('#tiktok_catalog_id-value').empty()
                if (response.error == false) {
                    if (response.data.catalog_id !== '') {
                        jQuery('#tiktok_catalog_id-value').val(response.data.catalog_id)
                    } else {
                        jQuery('#tiktok_catalog_id-value').val('Create New')
                    }
                }
                jQuery('.createFeedBtn').css("pointer-events", "auto")
            }
        });
    } 
    /****************Function to get tiktok catalog id country wise ****************************/
    /*************Function to disable selected attribute from additional attrbute dropdown ***********/
    function disableOptions() {
        jQuery('.additinal_attr *').removeAttr("disabled");
        jQuery(selected).each(function(i, v) {
            jQuery(".additinal_attr option").each(function() {
                var $thisOption = jQuery(this)
                var valueToCompare = v
                if(jQuery(this).parent().find(':selected').val() != v) {
                    if($thisOption.val() == valueToCompare) {
                        $thisOption.attr("disabled", "disabled")
                    }
                }                
            })
        })
    }
    /*************Function to disable selected attribute from additional attrbute dropdown ***********/
    /*********************Function to add selected category *****************************/
    function selectSubCategory(thisObj) {
        selectId = thisObj.id;
        wooCategoryId = jQuery(thisObj).attr("catid");
        var selvalue = jQuery('#' + selectId).find(":selected").val();
        var seltext = jQuery('#' + selectId).find(":selected").text();
        jQuery("#category-" + wooCategoryId).val(selvalue);
        jQuery("#category-name-" + wooCategoryId).val(seltext);
    }
    /*********************Function to add selected category *****************************/
    /**************** function to Save Feed data in DB **********************************/
    function createFeed() { 
        var data = {
            action: "convpfm_save_feed_data",
            feedName: jQuery('#feedName').val(),
            autoSync: jQuery('input[name="autoSync"]:checked').val(),
            syncInterval: 25,
            target_country: jQuery('#target_country').find(":selected").val(),
            channel: jQuery('input[name="channel"]:checked').val(),
            batchsize: jQuery('#product_batch_size').find(":selected").val(),
            cat_data: jQuery("#category_mapping").find("input[value!=''], select:not(:empty), input[type='number']").serialize(),
            attr_data: jQuery("#attribute_mapping").find("input[value!=''], select:not(:empty), input[type='number']").serialize(),
            product_id_prefix: jQuery("#product_id_prefix").val(),
            tiktok_catalog_id: jQuery('#tiktok_catalog_id-value').val(),
            customer_subscription_id: "<?php echo esc_js($subscriptionId) ?>",
            tiktok_business_account: "<?php echo esc_js($tiktok_business_account) ?>",
            conv_save_feed: "<?php echo esc_html(wp_create_nonce('conv_save_feed_nonce')); ?>"
        };
        jQuery.ajax({
            type: "POST",
            dataType: "json",
            url: convpfm_ajax_url,
            data: data,
            beforeSend: function () {
                jQuery('#save_feed_loader').removeClass('d-none')
                jQuery('.finishBtn, .backTocategory').css("pointer-events", "none")
            },
            error: function (err, status) {
                jQuery('#save_feed_loader').addClass('d-none')
                jQuery('.finishBtn, .backTocategory').css("pointer-events", "auto")
            },
            success: function (response) {
                jQuery('#save_feed_loader').addClass('d-none')               
                if (response.id) {
                    window.location.replace("<?php echo esc_url_raw($site_url.'product_list&id='); ?>"+response.id);
                } else if(response.errorType == 'tiktok'){
                    jQuery(".tiktok-error").html('Tiktok - '+response.message);
                    jQuery('[data-bs-target="#pills-enter-feed-details"]').trigger('click')
                    jQuery('.finishBtn, .backTocategory').css("pointer-events", "auto")
                }
                
            }
        });
    }
    /**************** function to Save Feed data in DB **********************************/    
    /**************** Function to Edit Feed *********************************************/
    function editFeed(id) {
        window.location.replace("<?php echo esc_url_raw($site_url.'feed_list&edit='); ?>"+id);
    }
    /**************** Function to Edit Feed *********************************************/
    /*******Remove search icon from input search **********************/
    function removeIcon(input) {
      if (input.value.length > 0) {
        input.style.background = 'none'; /* Remove the icon */
      } else {
        input.style.background = "url(<?php echo esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/search-icon.png'); ?>) no-repeat 10px center"; /* Restore the icon */
        input.style.backgroundSize = '20px 20px'; /* Adjust size of the icon */
      }
    }
    /*******Remove search icon from input search **********************/
    /************ Duplicate Feed Function start ************************/
    function duplicateFeed(feedId) {
        var conv_onboarding_nonce = "<?php echo esc_html(wp_create_nonce('conv_onboarding_nonce')); ?>"
        var data = {
            action: "convpfm_duplicate_feed_data_by_id",
            id: feedId,
            conv_onboarding_nonce: conv_onboarding_nonce
        }
        jQuery.ajax({
            type: "POST",
            dataType: "json",
            url: convpfm_ajax_url,
            data: data,
            beforeSend: function () {
                jQuery('.topNavBar').addClass('loading-row')
            },
            error: function (err, status) {
                jQuery('.topNavBar').removeClass('loading-row')
            },
            success: function (response) {
                jQuery('.topNavBar').removeClass('loading-row')
                if (response.error === false) {                    
                    location.reload(true);
                }
            }
        });
    }
    /************ Duplicate Feed Function end ************************/
    /*************************************DELETE Feed Data Satrt**********************************************************************/
    function deleteFeed($id) {
        if (confirm("Alert! Deleting this feed will remove its products from the Ads channels of the feed, affecting your campaigns. Make sure it aligns with your strategy. Questions? We're here!")) {
           
            var conv_onboarding_nonce = "<?php echo esc_html(wp_create_nonce('conv_onboarding_nonce')); ?>"
            var data = {
                action: "convpfm_delete_feed_data_by_id",
                id: $id,
                conv_onboarding_nonce: conv_onboarding_nonce
            }
            jQuery.ajax({
                type: "POST",
                dataType: "json",
                url: convpfm_ajax_url,
                data: data,
                beforeSend: function () {
                    jQuery('.topNavBar').addClass('loading-row')
                },
                error: function (err, status) {
                    jQuery('.topNavBar').removeClass('loading-row')
                },
                success: function (response) {
                    jQuery('.topNavBar').removeClass('loading-row')
                    setTimeout(function () {
                        location.reload(true);
                    }, 1000);
                }
            });
        }
    }
    /*************************************Delete Feed Data End*************************************************************************/
</script>

















