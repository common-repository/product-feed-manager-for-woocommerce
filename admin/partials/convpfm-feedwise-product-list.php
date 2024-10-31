<?php 
$Convpfm_Admin_DB_Helper = new Convpfm_Admin_DB_Helper();
$site_url = "admin.php?page=convpfm-google-shopping-feed&tab=feed_list";
$where = '`id` = '.esc_sql(filter_input(INPUT_GET,'id'));
$filed = ['id', 'feed_name', 'channel_ids', 'auto_sync_interval', 'auto_schedule', 
            'categories', 'attributes', 'filters', 'include_product', 'exclude_product', 
            'total_product', 'product_id_prefix', 'status', 'created_date', 'is_mapping_update', 
            'target_country', 'tiktok_status', 'tiktok_catalog_id', 'fb_status'];
$result = $Convpfm_Admin_DB_Helper->tvc_get_results_in_array("convpfm_product_feed", $where, $filed);
if(empty($result)){
    printf('<div class="col-12 d-flex justify-content-center"><div class="row "><b>Bad Request!!!</b></div></div>');die;
}
$getCountris = file_get_contents(CONVPFM_ENHANCAD_PLUGIN_DIR . "includes/setup/json/countries.json");
$contData = json_decode($getCountris, true);
$total_products = (new WP_Query(['post_type' => 'product', 'post_status' => 'publish']))->found_posts;

$Convpfm_TVC_Admin_Helper = new Convpfm_TVC_Admin_Helper();
$status_feed = $Convpfm_TVC_Admin_Helper->get_feed_status();
$conv_data = $Convpfm_TVC_Admin_Helper->get_store_data(); 
$attr = '';
$condition = '';
$value = '';
$p_ids = json_decode(stripslashes($result[0]['attributes']));
$p_id = isset($p_ids->id) ? $p_ids->id : '';
$is_synced = 'draft';
$feed_id = $result[0]['id'];
$facebook_catalog_id = '';
$convpfm_options = unserialize(get_option('convpfm_options')); 
if (isset($convpfm_options['facebook_setting']['fb_catalog_id']) === TRUE && $convpfm_options['facebook_setting']['fb_catalog_id'] !== '') {
    $facebook_catalog_id = $convpfm_options['facebook_setting']['fb_catalog_id'];
}
$tiktok_business_account = '';
if (isset($convpfm_options['tiktok_setting']['tiktok_business_id']) === TRUE && $convpfm_options['tiktok_setting']['tiktok_business_id'] !== '') {
    $tiktok_business_account = $convpfm_options['tiktok_setting']['tiktok_business_id'];
}
$attr = '';
$condition = '';
$value = '';
$html = '';
$filters = isset($result[0]['filters']) && $result[0]['filters'] !== '' ? json_decode(stripslashes($result[0]['filters'])) : '';
if ($filters !== '') {
    $filterAttributes = ['product_cat' => 'Category', 'ID' => 'Product Id', '_stock_status' => 'Stock Status', 'main_image' => 'Main Image'];
    $count = 0;
    foreach ($filters as $val) {
        if($val->attr == '_sku' || $val->attr == '_regular_price' || $val->attr == '_sale_price' || $val->attr == 'post_content' || $val->attr == 'post_excerpt' || $val->attr == 'post_title') {
            continue;
        }
        $attr .= $attr === '' ? $val->attr : ',' . $val->attr;
        $condition .= $condition === '' ? $val->condition : ',' . $val->condition;
        $value .= $value === '' ? $val->value : ',' . $val->value;
        $termitem = '';
        $eachVallue = $val->value;
        if ($val->attr === 'product_cat') {
            $termitem = get_term_by('id', $val->value, 'product_cat');
            $eachVallue = $termitem->name;
        }
        if ($result[0]['is_mapping_update'] == '1') {
            $html .= '<div class="btn-group border rounded mt-1 me-1 disabled"><button class="btn btn-light btn-sm text-secondary fs-12 ps-1 pe-1 pt-0 pb-0" type="button">' . $filterAttributes[$val->attr] . '  <b>' . $val->condition . '</b> ' . $eachVallue . ' </button>
                        <button type="button" class="btn btn-sm btn-grey onhover-close pt-0 pb-0" data-bs-toggle=""
                            aria-expanded="false" style="cursor: no-drop;">
                            <span class="material-symbols-outlined fs-6 pt-1 onhover-close">
                                close
                            </span>
                        </button>
                    </div>';
        } else {
            $html .= '<div class="btn-group border rounded mt-1 me-1 removecardThis" ><button value="' . $count++ . '" class="btn btn-light btn-sm text-secondary fs-12 ps-1 pe-1 pt-0 pb-0" type="button">' . $filterAttributes[$val->attr] . '  <b>' . $val->condition . '</b> ' . $eachVallue . ' </button>
                        <button type="button" class="btn btn-sm btn-grey onhover-close pt-0 pb-0" data-bs-toggle=""
                            aria-expanded="false">
                            <span class="material-symbols-outlined fs-6 pt-1 onhover-close removecard">
                                close
                            </span>
                        </button>
                    </div>';
        }
    } //end foreach

} //end if

$category_wrapper_obj = new Convpfm_Category_Wrapper();
$category = $category_wrapper_obj->get_convpfm_product_cat_list_with_name();
$subscription_id = $Convpfm_TVC_Admin_Helper->get_subscriptionId();
$subscription_data = $Convpfm_TVC_Admin_Helper->get_user_subscription_data();
$google_merchant_id = $subscription_data->google_merchant_center_id;
$google_ads_id = $subscription_data->google_ads_id;
$google_detail = $Convpfm_TVC_Admin_Helper->get_convpfm_api_data();
$store_id = $google_detail['setting']->store_id;

$customApiObj = new Convpfm_CustomApi();
$google_detail_api = $customApiObj->getGoogleAnalyticDetail($subscription_id);
?>
<style>
    
</style>
<div class="container-fluid mt-4 w-96">
    <div class="ps-2">
        <a href="<?php echo esc_url_raw($site_url); ?>" class="">
            <span class="fs-20 fw-600 text-primary">
                <img class="imgChannel" src="<?php echo esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/web_store_blue.png'); ?>" alt="">
                <?php esc_html_e("Feed Management","product-feed-manager-for-woocommerce")?>
            </span>
        </a>
        <span class="fw-600 fs-20 ms-1 text-dark"> ></span>
        <span class="fw-600 fs-20 text-dark ms-1"> <?php echo esc_html(str_replace('\\', '',$result[0]['feed_name'])) ?></span>
    </div>
    <div class="ps-2 mt-2">
        <span class="text-dark fs-14 fw-600 ms-1">Created:</span>
        <span class="text-header fs-14 fw-500 ms-1">
            <?php echo esc_html(date_format(date_create($result[0]['created_date']), "d/m/Y")); ?> 
            - <?php echo esc_html(date_format(date_create($result[0]['created_date']), "H:i a")); ?>
        </span>
        <span class="text-primary ms-1">|</span>
        <span class="text-dark fs-14 fw-600 ms-1">
            <label>Auto Sync:</label>
                <input class="form-check-input" style="margin-top: 0.05rem" type="radio" name="autoSync" id="auto-sync-on" disabled <?php echo $result[0]['auto_schedule'] == 1 ? 'checked' : '' ?> >
        </span>
        <span class="text-primary ms-1">|</span>
        <span class="text-dark fs-14 fw-600 ms-1">Target Country:</span>
        <span class="text-header fs-14 fw-500 ms-1">
            <?php echo esc_html(getCountryNameByCode($contData,$result[0]['target_country'])); ?>
        </span>
        <span class="text-primary ms-1">|</span>
        <span class="text-dark fs-14 fw-600 ms-1">Channel:</span>
        <span class="text-header fs-14 fw-500 ms-1">
            <?php 
            $is_camp = '';
            if ($result[0]['channel_ids'] === '1') { 
                $is_synced = strtolower(str_replace(' ', '', $result[0]['status'])); 
                $is_camp = strtolower(str_replace(' ', '', $result[0]['status']));
            ?>
                <img class="imgChannel-table"
                    src="<?php echo esc_url(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/google_shopping_icon.png'); ?>" /> Google Merchant Center
            <?php } elseif ($result[0]['channel_ids'] === '2') { $is_synced = strtolower(str_replace(' ', '', $result[0]['fb_status'])); ?>
                <img class="imgChannel-table"
                    src="<?php echo esc_url(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/fb-icon.png'); ?>" /> Facebook
            <?php } elseif ($result[0]['channel_ids'] === '3') { $is_synced = strtolower(str_replace(' ', '', $result[0]['tiktok_status'])); ?>
                <img class="imgChannel-table"
                    src="<?php echo esc_url(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/conv_tiktok_logo.png'); ?>" /> TikTok
            <?php }
            ?>
        </span>
        <span class="text-primary ms-1">|</span>
        <span class="text-dark fs-14 fw-600 ms-1">Total Products:</span>
        <span class="text-header fs-14 fw-500 ms-1 feed_total_product"></span>
        <span class="text-primary ms-1">|</span>
        <span class="text-dark fs-14 fw-600 ms-1">Status:</span>
        <button type="button" class="rounded-pill pending fs-7 ps-3 pe-3 pt-0 pb-0 mb-2 status <?php echo $is_synced ?>"><?php echo esc_html(ucfirst($is_synced)) ?></button>
        <div class="row">
            <?php if(isset($google_detail_api->data->product_count) && $google_detail_api->data->product_count > 0) { ?>
            <label class="fs-12 fw-400 text-secondary defaultPointer"> 
                <?php echo esc_html_e("You have reached ".round(($google_detail_api->data->product_count/$google_detail_api->data->max_limit)* 100)."% of your product limit for the product feed, with a maximum allowance of ".$google_detail_api->data->max_limit.".", "enhanced-e-commerce-for-woocommerce-store"); ?>
            </label>
            <?php } ?>
        </div>
    </div>
    <nav class="mt-3 navbar navbar-light bg-white shadow-sm mb-2 topNavBar" style="border-top-left-radius:8px;border-top-right-radius:8px;">            
        <div class="col-12">   
            <div class="row ms-0 p-1">         
                <div class="col-6 d-flex justify-content-start">
                    <button class="btn btn-custom-secondary fs-14 me-2">                        
                        <label class="text-dark fw-500 fs-14"><?php esc_html_e("Total Products :", "product-feed-manager-for-woocommerce"); ?> </label>
                        <label class="text-secondary fw-500 fs-14"><?php echo esc_html(number_format($total_products)); ?></label>
                    </button>
                    <button title="Apply filter on product" class="btn btn-custom-secondary pointer fs-14 me-2 pt-2 <?php echo $result[0]['is_mapping_update'] == '1' ? '' : 'addFilter'; ?>" style="<?php echo $result[0]['is_mapping_update'] == '1' ? 'cursor: no-drop' : 'cursor: pointer'; ?>">                        
                        <span class="material-symbols-outlined fs-20 text-primary" style="<?php echo $result[0]['is_mapping_update'] == '1' ? 'cursor: no-drop' : 'cursor: pointer'; ?>">
                            filter_alt
                        </span>
                    </button>
                </div>
                <div class="col-6 d-flex justify-content-end">                    
                    <button
                        class="createCampaign btn btn-outline-primary fs-14 me-2 campaignClass <?php echo esc_attr($is_camp == 'synced' ? '' : 'disabled') ?>"
                        title="Create performance max campaign in Google Ads for this Feed."  style="pointer-events: auto !important;width:180px;height:38px;">
                        <?php esc_html_e("Create Campaign", "product-feed-manager-for-woocommerce"); ?> 
                    </button>     
                    <button class="btn btn-soft-primary fs-14 me-2" name="syncProduct" id="syncProduct"  style="pointer-events: auto !important;width:180px;height:38px;">
                        <?php esc_html_e("Sync Product", "product-feed-manager-for-woocommerce"); ?>
                    </button>
                </div>
                <div class="col-12 row pe-0">
                <div class="col-8" id="addFiltersCard">
                    <?php echo $html; ?>

                </div>
                <!-- <div class="col-4 filter_count ">

                </div> -->
            </div>
            </div>            
        </div>        
    </nav>
    <div class="table-responsive shadow-sm" style="border-bottom-left-radius:8px;border-bottom-right-radius:8px;">
        <table class="table" id="product_list_table" style="width:100%">
            <thead>
                <tr>
                    <th scope="col" class="padding-start-1 text-start" style="width:3%">
                        <div class="form-check form-check-custom">
                            <input class="form-check-input checkbox fs-17 mt-1" type="checkbox" name="syncAll" id="syncAll" checked
                                value="syncAll">
                        </div>
                    </th>
                    <th scope="col" class="text-dark text-start">
                        <?php esc_html_e("Product Information", "product-feed-manager-for-woocommerce"); ?>
                    </th>
                    <th scope="col" class="text-dark text-start">
                        <?php esc_html_e("Category", "product-feed-manager-for-woocommerce"); ?>
                    </th>
                    <th scope="col" class="text-dark text-start">
                        <?php esc_html_e("Availability", "product-feed-manager-for-woocommerce"); ?>
                    </th>
                    <th scope="col" class="text-dark text-center">
                        <?php esc_html_e("Quantity", "product-feed-manager-for-woocommerce"); ?>
                    </th>
                    <th scope="col" class="text-dark text-center" style="width:10%">
                        <?php esc_html_e("Action", "product-feed-manager-for-woocommerce"); ?>
                    </th>
                </tr>
            </thead>
            <tbody id="table-body" class="table-body bg-white">

            </tbody>
        </table>
    </div>
</div>
<input type="hidden" id="feed_id" value="<?php echo esc_html(sanitize_text_field(filter_input(INPUT_GET,'id'))); ?>">
<input type="hidden" id="strProData" value="<?php echo esc_html(sanitize_text_field($attr)); ?>">
<input type="hidden" id="strConditionData" value="<?php echo esc_html(sanitize_text_field($condition)); ?>">
<input type="hidden" id="strValueData" value="<?php echo esc_html(sanitize_text_field($value)); ?>">
<input type="hidden" id="excludeProductFromSync" value="<?php echo esc_html(sanitize_text_field($result[0]['exclude_product'])); ?>">
<input type="hidden" id="includeProductFromSync" value="<?php echo esc_html(sanitize_text_field($result[0]['include_product'])); ?>">
<input type="hidden" id="includeExtraProductForFeed" value="">
<input type="hidden" id="selectAllunchecked" name="selectAllunchecked" value="">
<input type="hidden" id="totProduct" name="totProduct" value="">
<input type="hidden" id="feed_product_count" name="feed_product_count" value="">
<input type="hidden" name="syncProductCount" id="syncProductCount" value="<?php echo esc_attr($google_detail_api->data->product_count) ?>">

<div class="modal fade" id="filterModal" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content ">
            <div class="modal-header bg-white p-2">
                <h5 class="modal-title fs-6 p-2 col-8">
                    <?php esc_html_e("Apply Filters for Product", "product-feed-manager-for-woocommerce"); ?>
                </h5>
                <span class="col-4">
                    <label class="text-primary float-end p-1 pointer addButton">
                        <?php esc_html_e("Add Filter", "product-feed-manager-for-woocommerce"); ?>
                    </label>
                    <span class="material-symbols-outlined text-primary float-end pointer addButton">
                        add_circle
                    </span>
                </span>
            </div>
            <div class="modal-body ps-2 pt-2" id="">
                <form id="filterForm">
                    <div class="filterRow mb-3 row">
                        <div class="col-11 row">
                            <div class="col-4 productDiv">
                                <select class="product" name="product[]" style="width:100%">
                                    <option value="0"><?php esc_html_e("Select Attribute", "product-feed-manager-for-woocommerce"); ?></option>
                                    <option value="product_cat"><?php esc_html_e("Category", "product-feed-manager-for-woocommerce"); ?></option>
                                    <option value="ID"><?php esc_html_e("Product Id", "product-feed-manager-for-woocommerce"); ?></option>                                   
                                    <option value="_stock_status"><?php esc_html_e("Stock Status", "product-feed-manager-for-woocommerce"); ?></option>
                                    <option value="main_image"><?php esc_html_e("Main image", "product-feed-manager-for-woocommerce"); ?></option>
                                </select>
                            </div>
                            <div class="col-4 conditionDiv">
                                <select class="condition" name="condition[]" style="width:100%">
                                    <option value="0"><?php esc_html_e("Select Conditions", "product-feed-manager-for-woocommerce"); ?></option>
                                </select>
                            </div>
                            <div class="col-4 textValue">
                                <input type="text" class="form-control from-control-overload value" placeholder="Add value" name="value[]">
                            </div>
                        </div>
                        <div class="col-1">
                        </div>
                    </div>
                    <div id="allFilters">
                    </div>
            </div>
            <div class="modal-footer p-2">
                <button type="button" class="btn btn-light btn-sm ps-4 pe-4 border-primary text-primary" id="filterReset">
                    <?php esc_html_e("Clear", "product-feed-manager-for-woocommerce"); ?>
                </button>
                <button type="button" class="btn btn-soft-primary btn-sm ps-4 pe-4" id="filterSubmit">
                    <?php esc_html_e("Apply", "product-feed-manager-for-woocommerce"); ?>
                </button>
            </div>
            </form>
        </div>
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
                                <option value="<?php echo esc_html($country['code']) ?>" <?php echo $selecetdCountry === $country['code'] ? 'selected = "selecetd"' : '' ?> ><?php echo esc_html($country['name']) ?></option>
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
                        <input type="hidden" id="campaign_feed_id" name="campaign_feed_id" value="<?php echo esc_attr(filter_input(INPUT_GET,'id')) ?>">
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
<div class="modal fade" id="conv_save_error_modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true" style="z-index:9999">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
            <h5 class="modal-title"></h5>
                <button type="button" class="btn close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" >&times;</span>
                </button>
            </div>
            <div class="modal-body text-center p-0">
                <img style="width:184px;"
                    src="<?php echo esc_url(CONVPFM_ENHANCAD_PLUGIN_URL.'/admin/images/logos/error_logo.png'); ?>">
                <h3 class="fw-normal pt-3 errorText">Error</h3>
                <span id="conv_save_error_txt" class="mb-1 lh-lg"></span>
            </div>
            <div class="modal-footer border-0 pb-4 mb-1 errorFooter">
                    
            </div>
        </div>
    </div>
</div>
<script>
    let p_id = "<?php echo esc_js($p_id); ?>"  
    let feed_status = "<?php echo esc_js($is_synced); ?>"
    let feed_id = "<?php echo esc_js($feed_id); ?>"
    var totalProduct = 0;
    jQuery(document).ready(function() { 
 /*************************** DataTable init Start *********************************************************************************************/
 var table = jQuery('#product_list_table').DataTable({
            "ordering": false,
            scrollX: false,
            scrolly: true,
            processing: false,
            serverSide: true,
            searching: false,
            columnDefs: [{
                    className: "align-middle text-start",
                    targets: 0
                },
                {
                    className: "align-middle text-start ps-1 pb-1 pt-1",
                    targets: 1,
                    render: function (data) {
                        var html = '<div class="d-flex flex-row">'
                                +'<div><img class="tableImage" src="'+data.product_image_url+'"></div>'
                                +'<div class="ms-1"><div class="line-height"><label class="fw-400 fs-12 text-title">'+data.title+'</label></div>'
                                +'<div class="line-height"><label class="fw-400 fs-12 text-grey">Product Id: '+data.ProductID+'</label></div>'
                                +'<div class="line-height"><label class="fw-400 fs-12 text-grey">'+ (data.sale_price != "" || data.price != "" ? data.woocommerce_currency_symbol : "") + ' '+ (data.sale_price != "" ? data.sale_price : data.price) +'</label>'
                                +'<label class="ms-1 fw-400 fs-12 text-grey price-strike">'+ (data.sale_price != "" ? data.woocommerce_currency_symbol : "") + ' '+(data.sale_price != "" ? data.price : "")+'</label></div>'
                                +'</div></div>';
                        return html;
                    }
                },
                {
                    className: "align-middle text-start",
                    targets: 2
                },
                {
                    className: "align-middle text-start",
                    targets: 3
                },
                {
                    className: "align-middle text-center",
                    targets: 4
                },
                {
                    className: "align-middle text-center details-control",
                    targets: 5,
                    render: function (data) {
                        var action ='<span class="material-symbols-outlined pointer"> arrow_drop_down </span><input type="hidden" class="product_sync_id" value="'+data.proId+'"><input type="hidden" class="woo_product_type" value="'+data.product_type+'"><input type="hidden" class="woo_sku" value="'+data.sku+'"><input type="hidden" class="woo_product_id" value="'+data.product_id+'">';
                        return action;
                    }
                }
            ],
            initComplete: function() {
                // jQuery('#searchName').on('input', function() {
                //     jQuery('#product_list_table').DataTable().search(jQuery(this).val()).draw();
                // });
            },
            "language": {
                processing: false,
            },
            ajax: {
                url: convpfm_ajax_url,
                type: 'POST',
                data: function(d) {
                    //conv_change_loadingbar('show');
                    jQuery('.topNavBar').addClass('loading-row')
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                    return jQuery.extend({}, d, {
                        action: "convpfm_get_product_details_for_table",
                        productData: jQuery("#strProData").val(),
                        conditionData: jQuery("#strConditionData").val(),
                        valueData: jQuery("#strValueData").val(),
                        // searchName: jQuery("#searchName").val(),
                        feed_id: jQuery('#feed_id').val(),
                        p_id: p_id,
                        prefix: "<?php echo $result[0]['product_id_prefix'] ?>",
                        product_details_nonce: "<?php echo esc_html(wp_create_nonce('conv_product_details-nonce')); ?>"
                    });
                },
                dataType: 'JSON',
                error: function(err, status) {

                },
            },
            "drawCallback": function(settings) {
                var total = settings.json.recordsTotal;
                totalProduct = settings.json.recordsTotal;
                var excludeProductFromSync = jQuery('#excludeProductFromSync').val()
                var includeProductFromSync = jQuery('#includeProductFromSync').val()                
                jQuery('.topNavBar').removeClass('loading-row')
                let exclude = [];
                let include = [];
                if (jQuery('#excludeProductFromSync').val() != '') {
                    exclude = jQuery('#excludeProductFromSync').val().split(',');                    
                    jQuery('#syncAll').prop('checked', false)
                    jQuery.each(exclude, function(key, value) {
                        total--
                        jQuery('#sync_' + value).prop('checked', false);
                        jQuery('#attr_' + value).prop('checked', false);
                    });
                }

                if (jQuery('#includeProductFromSync').val() != '') {
                    include = jQuery('#includeProductFromSync').val().split(',');
                    jQuery('#syncAll').prop('checked', false);
                    jQuery('input[name="syncProduct"]').prop('checked', false);
                    jQuery('input[name="attrProduct"]').prop('checked', false);
                    jQuery('#selectAllunchecked').val(1);
                    total = 0;
                    jQuery.each(include, function(key, value) {
                        total++
                        jQuery('#sync_' + value).prop('checked', true);
                        jQuery('#attr_' + value).prop('checked', true);
                    });
                }
                jQuery('.feed_total_product').text(total)
                getrealcheckedcount();
            },
            columns: [{
                    data: 'checkbox'
                },
                {
                    data: 'product'
                },
                {
                    data: 'category'
                },
                {
                    data: 'availability'
                },
                {
                    data: 'quantity'
                },                
                {
                    data: 'action'
                }
            ],

        }).on('draw', function() {

            return true;
        });
        /*************************** DataTable init End *********************************************************************************************/

        jQuery(document).on('click', '#syncAll', function(e) {
            jQuery(".checkbox").prop('checked', jQuery(this).prop('checked'));
            if (jQuery(this).prop("checked")) {
                jQuery('#excludeProductFromSync').val('')
                jQuery('#selectAllunchecked').val('');
                jQuery('#includeProductFromSync').val('');
            } else {
                jQuery('#selectAllunchecked').val(1);
            }
        });
        jQuery(document).on('change', '.checkbox', function(e) {
            let exclude = [];
            let include = [];
            if (jQuery('#excludeProductFromSync').val() != '') {
                exclude = jQuery('#excludeProductFromSync').val().split(',');
            }
            if (jQuery('#includeProductFromSync').val() != '') {
                include = jQuery('#includeProductFromSync').val().split(',');
            }

            if (jQuery('#selectAllunchecked').val() == 1) {
                if (jQuery(this).prop("checked")) {
                    include.push(jQuery(this).val());
                    let uniqueInclude = include.filter((item, i, ar) => ar.indexOf(item) === i);
                    let val = uniqueInclude.join(',');
                    jQuery('#includeProductFromSync').val(val);
                } else {
                    const newArr = include.filter(e => e !== jQuery(this).val());
                    jQuery('#includeProductFromSync').val(newArr.join(','));
                }
            } else {
                if (!jQuery(this).prop("checked")) {
                    jQuery("#syncAll").prop("checked", false);
                    exclude.push(jQuery(this).val());
                    let unique = exclude.filter((item, i, ar) => ar.indexOf(item) === i);
                    let val = unique.join(',');
                    jQuery('#excludeProductFromSync').val(val);
                } else {
                    const newArr = exclude.filter(e => e !== jQuery(this).val());
                    jQuery('#excludeProductFromSync').val(newArr.join(','));
                }
            }
            getrealcheckedcount();
        });    
        jQuery(document).on('click', '.details-control', function(e) {
            let tr = e.target.closest('tr');            
            let product_id = jQuery(this).find('.product_sync_id').val();
            var sku = jQuery(this).find('.woo_sku').val();
            var woo_product_type = jQuery(this).find('.woo_product_type').val();
            var woo_product_id = jQuery(this).find('.woo_product_id').val();
            jQuery('.material-symbols-outlined').html('arrow_drop_down')
            if(jQuery(this).parent().next().attr('class') == 'AddtrRow') {
                jQuery(this).parent().next().remove()
            } else {
                jQuery('.AddtrRow').remove()
                var thisParent = jQuery(this).parent()
                if(feed_status == 'synced'){
                    var product_data = {
                        action: "convpfm_get_product_status",
                        feed_id: feed_id,
                        channel_id: "<?php echo $result[0]['channel_ids'] ?>",
                        tiktok_business_id: "",
                        tiktok_catalog_id: "",
                        product_list: product_id,
                        catalog_id: "<?php echo $facebook_catalog_id?>",
                        tiktok_business_id: "<?php echo $tiktok_business_account ?>",
                        tiktok_catalog_id: "<?php echo $result[0]['tiktok_catalog_id'] ?>",
                        conv_licence_nonce: "<?php echo esc_html(wp_create_nonce('conv_licence-nonce')); ?>"
                    };
                    jQuery.ajax({
                        type: "POST",
                        dataType: "json",
                        url: convpfm_ajax_url,
                        data: product_data,
                        beforeSend: function () {
                            tr.classList.add('loading-row')
                        },
                        success: function(response) {             
                            thisParent.find('.material-symbols-outlined').html('arrow_drop_up')
                            tr.classList.remove('loading-row')
                            if (response != "Product does not exists" && response != "Product not synced") {  
                                thisParent.after('')                              
                                jQuery.each(response, function(key, value) {                                    
                                    var newCells = '<tr class="AddtrRow">';
                                    newCells += '<td colspan="1"></td><td colspan="1"><b>Product Type -</b> "'+woo_product_type+'"</td><td colspan="1"><b> Product Sku -</b> "'+sku+'"</td>';                                    
                                    if (value.googleStatus){
                                        newCells +='<td colspan="1"><ul class="ps-0">';
                                        newCells += '<li><b>Product Offer Id -</b> "'+value.productId+'"</li><li><b>Google Status - </b>"'+value.googleStatus+'"</li></ul><td colspan="1"><ul>';
                                        
                                        var uniqueGoogleIssues = value.googleIssues.filter(function(itm, i, a) {
                                                return i == a.indexOf(itm);
                                            });
                                            newCells += '<b>Google Issue</b>';
                                            jQuery.each(uniqueGoogleIssues, function(key, issue) {
                                                newCells += '<li>' + issue + '</li>';
                                            });                                            
                                            newCells += '</ul></td>';
                                    }
                                    if (value.facebookStatus){
                                        newCells +='<td colspan="1"><ul class="ps-0">';
                                        newCells += '<li><b>Product Offer Id -</b> "'+value.productId+'"</li><li><b>Facebook Status - </b>"'+value.facebookStatus+'"</li></ul><td colspan="1"><ul>';
                                        if(value.facebookIssues != ''){
                                            newCells += '<b>Facebook Issue</b>';
                                            newCells += '<li>' + value.facebookIssues + '</li>';
                                        }
                                        newCells += '</ul></td>';
                                    }
                                    if (value.tiktokStatus){
                                        newCells +='<td colspan="1"><ul class="ps-0">';
                                        newCells += '<li><b>Product Offer Id -</b> "'+value.productId+'"</li><li><b>Facebook Status - </b>"'+value.tiktokStatus+'"</li></ul><td colspan="1"><ul>';
                                        if(value.tiktokStatus != ''){
                                            newCells += '<b>Tiktok Issue</b>';
                                            newCells += '<li>' + value.tiktokStatus + '</li>';
                                        }
                                        newCells += '</ul></td>';
                                    }
                                    newCells += '<td colspan="1" class="text-end"><button type="button" class="btn btn-soft-primary fs-12 sync_this_product mt-2 me-3" data-id="'+woo_product_id+'" value="'+woo_product_id+'" style="width:100px;">Sync Again</button><br/><button type="button" class="btn btn-outline-danger fs-12 delete_this_product mt-2 me-3" data-id="'+woo_product_id+'" value="'+product_id+'" style="width:100px;">Delete</button></td>'; 
                                    newCells += '</tr>';
                                    thisParent.after(newCells)
                                });                                
                                
                            } else {
                                thisParent.after('')
                                var newCells = '<tr class="AddtrRow">';
                                    newCells += '<td colspan="1"></td><td colspan="1"><b>Product Type -</b> "'+woo_product_type+'"</td><td colspan="1"><b> Product Sku -</b> "'+sku+'"</td>'; 
                                    newCells += '<td colspan="4" class="text-end"><button type="button" class="btn btn-soft-primary fs-12 sync_this_product mt-2 me-3 mb-2" data-id="'+woo_product_id+'" value="'+woo_product_id+'" style="width:100px;">Sync Now</button></td>'; 
                                    newCells += '</tr>';
                                thisParent.after(newCells)
                            }                            
                        }
                    });
                } else {
                    thisParent.find('.material-symbols-outlined').html('arrow_drop_up')
                    thisParent.after('')
                    var newCells = '<tr class="AddtrRow">';
                        newCells += '<td colspan="1"></td><td colspan="1"><b>Product Type -</b> "'+woo_product_type+'"</td><td colspan="1"><b> Product Sku -</b> "'+sku+'"</td>'; 
                        newCells += '<td colspan="4" class="text-end"><button type="button" class="btn btn-soft-primary fs-12 sync_this_product mt-2 me-3 mb-2" data-id="'+woo_product_id+'" value="'+woo_product_id+'" style="width:100px;">Sync Now</button></td>'; 
                        newCells += '</tr>';
                    thisParent.after(newCells)
                }                
            }            
        })  

        /*********************Add Filter Show Start***********************************************************************/
        jQuery('.addFilter').on('click', function(events) {
            let attr = jQuery('#strProData').val();
            let condition = jQuery('#strConditionData').val();
            let value = jQuery('#strValueData').val();
            var a = 0;
            if (attr != '' && condition != '' && value != '') {
                let attrArry = attr.split(",");
                let conditionArry = condition.split(",");
                let valueArry = value.split(",");
                jQuery('#allFilters').empty()
                jQuery.each(attrArry, function(i, value) {
                    if (a == 0) {
                        a = 1;
                        jQuery('select[name="product[]"]').val(value).trigger('change');
                        jQuery('select[name="condition[]"]').val(conditionArry[i]).trigger('change');
                        if (value === 'product_cat' || value === '_stock_status' || value === 'main_image') {
                            jQuery('select[name="value[]"]').val(valueArry[i]).trigger('change');
                        } else {
                            jQuery('input[name="value[]"]').val(valueArry[i]);
                        }

                    } else {
                        var conditionDropDown = getConditionDropDown(value, conditionArry[i]);
                        if (value === 'product_cat') {
                            var category = <?php echo json_encode($category); ?>;
                            let option = '<option value="0">Select Category</option>';
                            jQuery.each(category, function(key, values) {
                                option += '<option value="' + key + '" ' + ((key == valueArry[i]) ? "selected" : "") + '>' + values + '</option>';
                            });
                            var html = '<select class="select2" name="value[]">' +
                                option +
                                '</select>';
                        }  else if (value === '_stock_status') {
                            let option = '<option value="0">Select Stock Status</option>' +
                                '<option value="instock" ' + (("instock" == valueArry[i]) ? "selected" : "") + '>In Stock</option>' +
                                '<option value="outofstock" ' + (("outofstock" == valueArry[i]) ? "selected" : "") + '>Out Of Stock</option>';
                            html = '<select class="select2" name="value[]">' + option + '</select>';
                        }  else if (value === 'main_image') {
                            let option = '<option value="0">Select Image State</option>' +
                                '<option value="EXISTS" ' + (("EXISTS" == valueArry[i]) ? "selected" : "") + '>Not Empty</option>';
                            html = '<select class="select2" name="value[]">' + option + '</select>';
                        } else {
                            var html = '<input type="text" class="form-control from-control-overload value" placeholder="Add value" name="value[]" value="' + valueArry[i] + '">';
                        }
                        var newRow = '<div class="filterRow mb-3 row">' +
                            '<div class="col-11 row">' +
                            '<div class="col-4 productDiv">' +
                            '<select class="select2 product" name="product[]">' +
                            '<option value="0">Select Attribute</option>' +
                            '<option value="product_cat" ' + ((value == "product_cat") ? "selected" : "") + '>Category</option>' +
                            '<option value="ID" ' + ((value == "ID") ? "selected" : "") + '>Product Id</option>' +                            
                            '<option value="_stock_status" ' + ((value == "_stock_status") ? "selected" : "") + '>Stock Status</option>'+
                            '<option value="main_image" ' + ((value == "main_image") ? "selected" : "") + '>Main Image</option>' +
                            '</select>' +
                            '</div>' +
                            '<div class="col-4 conditionDiv">' +
                            conditionDropDown +
                            '</div>' +
                            '<div class="col-4 textValue">' +
                            html +
                            '</div>' +
                            '</div>' +
                            '<div class="col-1 pt-2">' +
                            '<span class="material-symbols-outlined deleteButton text-primary" style="cursor: pointer;" title="Remove Filter">remove</span>' +
                            '</div>' +
                            '</div>';
                        jQuery('#allFilters').append(newRow);
                    }
                });
            }
            jQuery('#filterModal').modal('show');
            jQuery('.condition').select2({
                dropdownParent: jQuery("#filterModal")
            });
            jQuery('.product').select2({
                dropdownParent: jQuery("#filterModal")
            });
        });
        /*********************Add Filter Show End************************************************************************/
        /**************** get dependent dropdown product change start******************************************************/
        jQuery(document).on('change', '.product', function(event) {
            var changeValue = jQuery(this).val();
            jQuery(this).parent().parent().children('div').eq(1).empty();
            var conditionDropDown = getConditionDropDown(changeValue);
            jQuery(this).parent().parent().children('div').eq(1).append(conditionDropDown);
            if (changeValue === 'product_cat') {
                var category = <?php echo json_encode($category); ?>;
                let option = '<option value="0">Select Category</option>';
                jQuery.each(category, function(key, value) {
                    option += '<option value="' + key + '">' + value + '</option>';
                });
                jQuery(this).parent().parent().children('.textValue').empty();
                var html = '<select class="category" name="value[]" style="width:100%">' +
                    option +
                    '</select>';
                    jQuery(this).parent().parent().children('.textValue').append(html);
            } else if (changeValue === '_stock_status'){
                jQuery(this).parent().parent().children('.textValue').empty();
                var html = '<select class="category" name="value[]" style="width:100%">'+
                    '<option value="0">Select Stock Status</option>'+
                    '<option value="instock">In Stock</option>'+
                    '<option value="outofstock">Out Of Stock</option>'+
                    '</select>';
                    jQuery(this).parent().parent().children('.textValue').append(html);
            } else if (changeValue === 'main_image') {
                jQuery(this).parent().parent().children('.textValue').empty();
                var html = '<select class="category" name="value[]" style="width:100%">' +
                    '<option value="0">Select Image State</option>' +
                    '<option value="EXISTS">Not Empty</option>' +
                    '</select>';
                jQuery(this).parent().parent().children('.textValue').append(html);
            } else {
                jQuery(this).parent().parent().children('.textValue').empty();
                var html = '<input type="text" class="form-control from-control-overload value" placeholder="Add value" name="value[]" >';
                jQuery(this).parent().parent().children('.textValue').append(html);
            }
            jQuery('.category').select2({
                dropdownParent: jQuery("#filterModal")
            });
            jQuery('.condition').select2({
                dropdownParent: jQuery("#filterModal")
            });
        });
        /**************** get dependent dropdown product change end**********************************************************/
        /**************** Add more filter Start**************************************************************************/
        jQuery(document).on("click", ".addButton", function(event) {
            var newRow = '<div class="filterRow mb-3 row">' +
                '<div class="col-11 row">' +
                '<div class="col-4 productDiv">' +
                '<select class="product" name="product[]" style="width: 100%">' +
                '<option value="0">Select Attribute</option>' +
                '<option value="product_cat">Category</option>' +
                '<option value="ID">Product Id</option>' +                
                '<option value="_stock_status">Stock Status</option>' +
                '<option value="main_image">Main Image</option>' +
                '</select>' +
                '</div>' +
                '<div class="col-4 conditionDiv">' +
                '<select class="condition" name="condition[]" style="width: 100%">' +
                '<option value="0">Select Conditions</option>' +
                '</select>' +
                '</div>' +
                '<div class="col-4 textValue">' +
                '<input type="text" class="form-control from-control-overload value" placeholder="Add value" name="value[]" >' +
                '</div>' +
                '</div>' +
                '<div class="col-1 pt-2">' +
                '<span class="material-symbols-outlined deleteButton text-primary" style="cursor: pointer;" title="Remove Filter">remove</span>' +
                '</div>' +
                '</div>';

                jQuery('#allFilters').append(newRow);
            jQuery('.condition').select2({
                dropdownParent: jQuery("#filterModal")
            });
            jQuery('.product').select2({
                dropdownParent: jQuery("#filterModal")
            });
        });
        /**************** Add more filter End******************************************************************************/
        /**************** Reset Modal filter Start ***************************************/
        jQuery(document).on('click', '#filterReset', function(event) {
            jQuery('#allFilters').empty();
            jQuery("#filterForm")[0].reset();
            jQuery(".product").select2('val', '0');
            jQuery('.product').select2({
                dropdownParent: jQuery("#allFilters")
            });
            jQuery('.condition').select2({
                dropdownParent: jQuery("#allFilters")
            });
        });
        /**************** Reset Modal filter End ******************************************/
        /**************** Delete Add more filed column start*****************************************************************/
        jQuery("body").on("click", ".deleteButton", function() {
            jQuery(this).parents(".filterRow").remove();
        });
        /**************** Delete Add more filed column  end******************************************************************/
        /**************** Get filtered data Start ******************************************/
        jQuery(document).on('click', '#filterSubmit', function(event) {
            let product = jQuery("select[name='product[]'] option:selected").map(function() {
                return jQuery(this).val();
            }).get();
            let producttext = jQuery("select[name='product[]'] option:selected").map(function() {
                return jQuery(this).text();
            }).get();
            let condition = jQuery("select[name='condition[]'] option:selected").map(function() {
                return jQuery(this).val();
            }).get();
            let value = jQuery("input[name='value[]']").map(function() {
                return jQuery(this).val();
            }).get();
            let seltext = jQuery("select[name='value[]'] option:selected").map(function() {
                return jQuery(this).text();
            }).get();
            let selVal = jQuery("select[name='value[]'] option:selected").map(function() {
                return jQuery(this).val() ? jQuery(this).val() : '';
            }).get();
            let flag = 0;
            let valFlag = 0;
            let prodData = Array();
            let conditionData = Array();
            let valueData = Array();
            jQuery('#addFiltersCard').empty();
            jQuery.each(product, function(i, val) {
                if (val != "0" && condition[i] != "0" && (value[valFlag] != "" || selVal[flag] != "")) {
                    if (val === 'product_cat' || val === '_stock_status' || val === 'main_image') {
                        prodData[i] = val;
                        conditionData[i] = condition[i];
                        valueData[i] = selVal[flag];
                        var newCard = '<div class="btn-group border rounded mt-1 me-1 removecardThis" >' +
                            '<button class="btn btn-light btn-sm text-secondary fs-12 ps-1 pe-1 pt-0 pb-0" type="button" value="' + i + '">' + producttext[i] + ' <b>' + condition[i] + '</b> ' + seltext[flag++] + '</button>' +
                            '<button type="button" class="btn btn-sm btn-grey onhover-close pt-0 pb-0" data-bs-toggle="" aria-expanded="false" style="cursor: pointer;">' +
                            '<span class="material-symbols-outlined fs-6 pt-1 onhover-close removecard">close</span></button></div>';
                    } else {

                        prodData[i] = val;
                        conditionData[i] = condition[i];
                        valueData[i] = value[valFlag];
                        var newCard = '<div class="btn-group border rounded mt-1 me-1 removecardThis">' +
                            '<button class="btn btn-light btn-sm text-secondary fs-12 ps-1 pe-1 pt-0 pb-0" type="button" value="' + i + '">' + producttext[i] + ' <b>' + condition[i] + '</b> ' + value[valFlag++] + '</button>' +
                            '<button type="button" class="btn btn-sm btn-grey onhover-close pt-0 pb-0" data-bs-toggle="" aria-expanded="false" style="cursor: pointer;">' +
                            '<span class="material-symbols-outlined fs-6 pt-1 onhover-close removecard">close</span></button></div>';
                    }
                    jQuery('#addFiltersCard').append(newCard);
                    //count++;
                }
            });
            jQuery('#strProData').val('');
            jQuery('#strConditionData').val('');
            jQuery('#strValueData').val('');
            let strProData = prodData.join(',');
            let strConditionData = conditionData.join(',');
            let strValueData = valueData.join(',');
            jQuery('#strProData').val(jQuery('#strProData').val() ? jQuery('#strProData').val() + "," + strProData : strProData);
            jQuery('#strConditionData').val(jQuery('#strConditionData').val() ? jQuery('#strConditionData').val() + "," + strConditionData : strConditionData);
            jQuery('#strValueData').val(jQuery('#strValueData').val() ? jQuery('#strValueData').val() + "," + strValueData : strValueData);
            jQuery('#excludeProductFromSync').val('');
            jQuery('#includeProductFromSync').val('');
            jQuery('#selectAllunchecked').val('');
            jQuery('#includeExtraProductForFeed').val('');
            jQuery('#allFilters').empty();
            jQuery("#filterForm")[0].reset();
            jQuery(".product").select2('val', '0');
            jQuery('#filterDelete').addClass('disabled');
            jQuery('#filterModal').modal('hide');
            table.draw();
        });
        /************************************* Get filtered data End **********************************************************************************/
        /***************************** Remove Cards Startm ******************************************************************************************/
        jQuery(document).on('click', '.removecard', function(event) {
            var ele = jQuery(this).parent();
            var strProData = jQuery('#strProData').val().split(',');
            var strConditionData = jQuery('#strConditionData').val().split(',');
            var strValueData = jQuery('#strValueData').val().split(',');
            var val = ele.prev().val();
            jQuery(ele.parent()).remove();
            strProData.splice(val, 1);
            strConditionData.splice(val, 1);
            strValueData.splice(val, 1);

            jQuery(".removecard").each(function(index, value) {
                jQuery(this).parent().prev().val(index);
            });

            strProData = strProData.join();
            strConditionData = strConditionData.join();
            strValueData = strValueData.join();

            jQuery('#strProData').val(strProData);
            jQuery('#strConditionData').val(strConditionData);
            jQuery('#strValueData').val(strValueData);
            jQuery('#excludeProductFromSync').val('');
            jQuery('#includeProductFromSync').val('');
            jQuery('#selectAllunchecked').val('');
            jQuery('#includeExtraProductForFeed').val('');
            table.draw();
        });
        /****************************** Remove Cards End *********************************************************************************************/
        jQuery(document).on('click', '.delete_this_product', function() {
            var woo_product_id = jQuery(this).attr('data-id');
            var channel_product_id = jQuery(this).attr('value');
            deleteProduct(feed_id, channel_product_id)
        })

        jQuery(document).on('click', '#syncProduct', function() {    
            var is_synced = "<?php echo esc_js($is_synced); ?>";
            var total_product_feed = parseInt(jQuery('#totProduct').val());
            if(total_product_feed > 100 && is_synced == 'draft') {
                jQuery('.errorFooter').empty();
                jQuery('.errorFooter').html('<a class="btn conv-blue-bg m-auto text-white" href="https://www.conversios.io/pricing/?plugin_name=pfm&utm_source=in_app&utm_medium=productlist&utm_campaign=Pricing" target="_blank" ><b>Upgrade to pro<b></a>');
                jQuery('.errorText').html('Oops!');
                jQuery('#conv_save_error_txt').html("<p>According to your plan, the maximum limit for syncing products is 100.</p>"+
                "<p>With our Pro version, you'll benefit with products, priority support, and much more. Upgrade now to unlock the full potential of our platform and take your business to the next level. "+ 
                "<a href='https://www.conversios.io/pricing/?plugin_name=pfm&utm_source=in_app&utm_medium=productlist&utm_campaign=Pricing' target='_blank'>Upgrade to pro.</a></p>");
                jQuery('#conv_save_error_modal').modal('show');
                return false;
            }        
           
            let syncProductCount = parseInt(jQuery('#syncProductCount').val());
            if(total_product_feed > 100 || (total_product_feed + syncProductCount) > 100 && is_synced == 'draft'){
                jQuery('.errorFooter').empty();
                jQuery('.errorFooter').html('<a class="btn conv-blue-bg m-auto text-white" href="https://www.conversios.io/pricing/?plugin_name=pfm&utm_source=in_app&utm_medium=productlist&utm_campaign=Pricing" target="_blank" ><b>Upgrade to pro<b></a>');
                jQuery('.errorText').html('Oops!');
                jQuery('#conv_save_error_txt').html("<p>You've reached the maximum product limit of 100 products for your current plan.</p>"+
                "<p>With our Pro version, you'll benefit with more products, priority support, and much more. Upgrade now to unlock the full potential of our platform and take your business to the next level. "+ 
                "<a href='https://www.conversios.io/pricing/?plugin_name=pfm&utm_source=in_app&utm_medium=productlist&utm_campaign=Pricing' target='_blank'>Upgrade to pro.</a></p>");
                jQuery('#conv_save_error_modal').modal('show');
                return false;
            }
            start_product_sync()
        })

        jQuery(document).on('click', '.sync_this_product', function(){
            var woo_product_id = jQuery(this).attr('value');
            sync_single_product(woo_product_id)
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
    })
    /*************************************Get Condition Dropdown Start******************************************************************/
    function getConditionDropDown(val = '', condition = '') {
        let conditionOption = '<select class="condition" name="condition[]" style="width: 100%"><option value="0">Select Conditions</option>';
        if (val != '0') {
            if (val != '' || condition != '') {
                switch (val) {
                    case 'product_cat':
                    case 'ID':
                        conditionOption += '<option value="=" ' + ((condition == "=") ? "selected" : "") + ' > = </option>' +
                            '<option value="!=" ' + ((condition == "!=") ? "selected" : "") + ' > != </option>';
                        break;
                    case '_stock_status':
                        conditionOption += '<option value="=" ' + ((condition == "=") ? "selected" : "") + ' > = </option>' +
                            '<option value="!=" ' + ((condition == "!=") ? "selected" : "") + ' > != </option>';
                        break; 
                    case 'main_image':
                        conditionOption += '<option value="=" ' + ((condition == "=") ? "selected" : "") + ' > = </option>';
                        break;                   
                }
            }
        }
        conditionOption += '</select>';
        return conditionOption;
    }
    /*************************************Get Condition Dropdown End**********************************************************************/
    function start_product_sync() {
        var data = {
                action: 'convpfm_prepare_feed_to_sync',
                feedId: feed_id,
                productData: jQuery("#strProData").val(),
                conditionData: jQuery("#strConditionData").val(),
                valueData: jQuery("#strValueData").val(),
                include: jQuery('#includeProductFromSync').val(),
                exclude: jQuery('#excludeProductFromSync').val(),
                conv_nonce: "<?php echo esc_js(wp_create_nonce('conv_ajax_product_sync_bantch_wise-nonce')); ?>",
            }
            jQuery.ajax({
                type: "POST",
                dataType: "json",
                url: convpfm_ajax_url,
                data: data,
                beforeSend: function() {
                    jQuery('.topNavBar').addClass('loading-row')
                    jQuery("#wpbody, #syncProduct, .createCampaign").css("pointer-events", "none");
                },
                error: function(err, status) {
                    jQuery('.topNavBar').removeClass('loading-row')
                    jQuery("#wpbody, #syncProduct, .createCampaign").css("pointer-events", "auto");
                },
                success: function(response) {
                    jQuery('.topNavBar').removeClass('loading-row')
                    jQuery("#wpbody, #syncProduct, .createCampaign").css("pointer-events", "auto");
                    if(response.error == false){
                        var html = '<img class="" src="<?php echo esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/successImg.png'); ?>" alt="" style="width:150px; height:150px;">';
                            html += '<div class="text-success">'+response.message+'</div>';
                            html += '<div class="text-dark fs-12 mt-2">Congratulations your products are being synced in your product feed channels. It takes up to 30 minutes for the product data to get reflected in the respective channel\'s dashboards once the product feed process is completed. You will be able to see the status of the products in the feeds.</div>';
                        var btn = '<button type="button" class="btn btn-dark" data-bs-dismiss="modal" >Close</button>'
                        jQuery('.infoBody').html(html)
                        jQuery('.infoFooter').html(btn)
                        jQuery('#infoModal').modal('show')
                        window.location.replace("<?php echo esc_url_raw($site_url); ?>");                        
                    } else {
                        var html = '<img class="" src="<?php echo esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/errorImg.png'); ?>" alt="" style="width:150px; height:150px;">';
                            html += '<div class="text-danger">Error in Product Sync</div>';
                        var btn = '<button type="button" class="btn btn-dark" data-bs-dismiss="modal" onclick="location.reload();">Close</button>'
                        jQuery('.infoBody').html(html)
                        jQuery('.infoFooter').html(btn)
                        jQuery('#infoModal').modal('show')
                    }
                }
            })
    }

    function deleteProduct($id, $product_id = null) {
        $message = 'This product will delete from the feed and the channel. Are you sure you want to delete it? ';
        if (confirm($message)) {
            var conv_onboarding_nonce = "<?php echo esc_html(wp_create_nonce('conv_onboarding_nonce')); ?>"
            var data = {
                action: "convpfm_delete_feed_channel",
                feed_id: $id,
                product_ids: $product_id,
                conv_onboarding_nonce: conv_onboarding_nonce
            }
            jQuery.ajax({
                type: "POST",
                dataType: "json",
                url: convpfm_ajax_url,
                data: data,
                beforeSend: function() {
                    jQuery('.topNavBar').addClass('loading-row')
                    jQuery("#wpbody").css("pointer-events", "none");
                },
                error: function(err, status) {
                    jQuery('.topNavBar').removeClass('loading-row')
                    jQuery("#wpbody").css("pointer-events", "auto");
                },
                success: function(response) {
                    jQuery('.topNavBar').removeClass('loading-row')
                    jQuery("#wpbody").css("pointer-events", "auto");  
                }
            });
        }
    }

    function sync_single_product(woo_product_id) {
        var conv_onboarding_nonce = "<?php echo esc_html(wp_create_nonce('conv_onboarding_nonce')); ?>"
            var data = {
                action: "convpfm_sync_single_product",
                feed_id: feed_id,
                product_ids: woo_product_id,
                conv_onboarding_nonce: conv_onboarding_nonce
            }
            jQuery.ajax({
                type: "POST",
                dataType: "json",
                url: convpfm_ajax_url,
                data: data,
                beforeSend: function() {
                    jQuery('.topNavBar').addClass('loading-row')
                },
                error: function(err, status) {
                    jQuery('.topNavBar').removeClass('loading-row')
                },
                success: function(response) {
                    jQuery('.topNavBar').removeClass('loading-row')
                    if(response.error == false){
                        var html = '<img class="" src="<?php echo esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/successImg.png'); ?>" alt="" style="width:150px; height:150px;">';
                            html += '<div class="text-success">'+response.message+'</div>';
                            html += '<div class="text-dark fs-12 mt-2">Congratulations your products are being synced in your product feed channels. It takes up to 30 minutes for the product data to get reflected in the respective channel\'s dashboards once the product feed process is completed. You will be able to see the status of the products in the feeds.</div>';
                        var btn = '<button type="button" class="btn btn-dark" data-bs-dismiss="modal" >Close</button>'
                        jQuery('.infoBody').html(html)
                        jQuery('.infoFooter').html(btn)
                        jQuery('#infoModal').modal('show')
                        window.location.replace("<?php echo esc_url_raw($site_url); ?>");                        
                    } else {
                        var html = '<img class="" src="<?php echo esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/errorImg.png'); ?>" alt="" style="width:150px; height:150px;">';
                            html += '<div class="text-danger">Error in Product Sync</div>';
                        var btn = '<button type="button" class="btn btn-dark" data-bs-dismiss="modal" onclick="location.reload();">Close</button>'
                        jQuery('.infoBody').html(html)
                        jQuery('.infoFooter').html(btn)
                        jQuery('#infoModal').modal('show')
                    }

                }
            });
    }
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
        subscription_id: "<?php echo $subscription_id ?>",
        google_merchant_id: "<?php echo $google_merchant_id ?>",
        google_ads_id: "<?php echo $google_ads_id ?>",
        sync_item_ids: jQuery('#campaign_feed_id').val(),
        domain: "<?php echo get_site_url() ?>",
        store_id: "<?php echo $store_id ?>",
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
    function getrealcheckedcount() {
        var excludeProductFromSync = jQuery('#excludeProductFromSync').val();
        var includeProductFromSync = jQuery('#includeProductFromSync').val();
        jQuery('#totProduct').val(0);
        if (excludeProductFromSync !== '') {
            var count = excludeProductFromSync.split(',').length;
            jQuery('#totProduct').val(totalProduct - count);
            return true;
        }
        if (includeProductFromSync !== '') {
            var count = includeProductFromSync.split(',').length;
            jQuery('#totProduct').val(count);
            return true;
        }
        if (jQuery("#syncAll").prop("checked")) {
            jQuery('#totProduct').val(totalProduct);
            return true;
        }
        if(excludeProductFromSync == '' && includeProductFromSync == '' && jQuery('#selectAllunchecked').val() != '1') {
            jQuery('#totProduct').val(totalProduct);
            return true;
        }
    }
</script>
<?php 
    function getCountryNameByCode($countries, $code) {
        foreach ($countries as $country) {
            if ($country['code'] === $code) {
                return $country['name'];
            }
        }
        return "NA";
    }
?>