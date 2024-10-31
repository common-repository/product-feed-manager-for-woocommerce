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

    $Convpfm_Admin_DB_Helper = new Convpfm_Admin_DB_Helper();
    $where = '`id` = '.esc_sql(filter_input(INPUT_GET,'edit'));
    $filed = ['id', 'feed_name', 'channel_ids', 'auto_sync_interval', 'auto_schedule', 'categories', 'attributes', 'product_id_prefix', 'target_country', 'tiktok_catalog_id', 'product_sync_batch_size', 'is_mapping_update', 'last_sync_date'];
    $result = $Convpfm_Admin_DB_Helper->tvc_get_results_in_array("convpfm_product_feed", $where, $filed);
    if(count($result[0]) == 0) {
        die('Bad Request!!!!');
    } 
    
    $filesystem = new WP_Filesystem_Direct( true );
    $getCountris = $filesystem->get_contents(CONVPFM_ENHANCAD_PLUGIN_DIR."includes/setup/json/countries.json");
    $contData = json_decode($getCountris);
    $conv_data = $Convpfm_TVC_Admin_Helper->get_store_data();
    $category_wrapper_obj = new Convpfm_Category_Wrapper();
    $gmcAttributes = $Convpfm_TVC_Admin_Helper->get_gmcAttributes();
    $convpfm_mapped_attrs = json_decode(stripslashes($result[0]['attributes']), true);    
    $convpfm_prod_mapped_cats = json_decode(stripslashes($result[0]['categories']), true);
    $tempAttr = $convpfm_mapped_attrs;
    $Convpfm_ProductSyncHelper = new Convpfm_ProductSyncHelper();
    $wooCommerceAttributes = array_map("unserialize", array_unique(array_map("serialize", $Convpfm_ProductSyncHelper->wooCommerceAttributes())));
    $site_url = "admin.php?page=convpfm-google-shopping-feed&tab=";
?>

<div class="container-fluid containerheight mt-4 w-96 create-feed-div">
    <span class="fw-bold text-dark fs-20">
        <img class="imgChannel" src="<?php echo esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/web_store.png'); ?>" alt="">
        <?php esc_html_e("Feed Management","product-feed-manager-for-woocommerce")?>
    </span>
    <p class="text-grey fs-16 fw-400">
        <?php esc_html_e("Manage your product feeds to keep your online store information up-to-date and accurate. This includes adding new products, updating prices, and ensuring all product details meet platform requirements. Effective feed management helps your products appear correctly on shopping channels and improves your visibility to potential customers.
","product-feed-manager-for-woocommerce")?>
    </p>
   
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
                        <input type="text" autocomplete="off" class="form-control form-control-sm" name="feedName" id="feedName" placeholder="Feed Name" style="width:30%" value="<?php echo esc_attr(str_replace('\\', '',$result[0]['feed_name'])) ?>"/>
                    </div>     
                    <div class="mt-4">
                        <label class="fw-600 fs-14">
                            Auto Sync :
                        </label>
                        <div class="form-check form-check-inline ms-2">
                            <input class="form-check-input" type="radio" name="autoSync" id="auto-sync-on" value="1" <?php echo $result[0]['auto_schedule'] == 1 ? 'checked' : '' ?> >
                            <label class="form-check-label" for="auto-sync-on">ON</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="autoSync" id="auto-sync-off" value="0" <?php echo $result[0]['auto_schedule'] == 0 ? 'checked' : '' ?>>
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
                                $selecetdCountry = $result[0]['target_country'];
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
                                <input class="form-check-input" type="radio" name="channel" id="gmc_id" value="1" <?php echo $google_merchant_center_id !== '' ? "" : 'disabled' ?> <?php echo $result[0]['channel_ids'] == 1 ? 'checked' : '' ?> >
                                <label class="form-check-label" for="gmc_id">
                                    <img style="width: 24px" src="<?php echo esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/google_shopping_icon.png'); ?>" alt="">
                                    Google Merchant Center
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="channel" id="fb_id" value="2" <?php echo $facebook_catalog_id !== '' ? "" : 'disabled' ?> <?php echo $result[0]['channel_ids'] == 2 ? 'checked' : '' ?>>
                                <label class="form-check-label" for="fb_id">
                                    <img style="width: 24px" src="<?php echo esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/metaChannel.png'); ?>" alt="">                            
                                    Facebook Catalog
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="channel" id="tiktok_catalog_id" value="3" <?php echo $tiktok_business_account !== '' ? "" : 'disabled' ?> <?php echo $result[0]['channel_ids'] == 3 ? 'checked' : '' ?>>
                                <label class="form-check-label" for="tiktok_catalog_id">
                                    <img style="width: 24px" src="<?php echo esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/conv_tiktok_logo.png'); ?>" alt="">
                                    TikTok Catalog
                                </label>
                                <input type="hidden" name="tiktok_catalog_id-value" id="tiktok_catalog_id-value" value="<?php echo esc_attr($result[0]['tiktok_catalog_id']) ?>">
                            </div>
                        </div>
                        <span class="text-soft-danger tiktok-error fw-600 fs-12"></span>
                    </div>  
                    <div class="mt-4">
                        <label class="fw-600 fs-14 me-1">
                            Batch Size :
                        </label>
                        <select id="product_batch_size" class="form-select" style="width: auto !important">
                            <option value="10" <?php echo $result[0]['product_sync_batch_size'] == 10 ? "selected" : '' ?> >10</option>
                            <option value="25" <?php echo $result[0]['product_sync_batch_size'] == 25 ? "selected" : '' ?> >25</option>
                            <option value="50" <?php echo $result[0]['product_sync_batch_size'] == 50 ? "selected" : '' ?> >50</option>
                            <option value="100" <?php echo $result[0]['product_sync_batch_size'] == 100 ? "selected" : '' ?> >100</option>
                            <option value="200" <?php echo $result[0]['product_sync_batch_size'] == 200 ? "selected" : '' ?> >200</option>
                        </select>
                    </div>
                    <div class="mt-4 d-flex justify-content-end">
                        <button class="btn btn-outline-secondary col-2 cancelFeed me-2" style="z-index: 2147483650">Cancel</button>
                        <button class="btn btn-primary col-2 createFeedBtn" style="z-index: 2147483650">Next</button>
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
                    <div class="mb-2 attributeDiv rounded-bottom border-end border-bottom border-start" style="overflow-y: scroll; overflow-x: hidden; max-height: calc(100vh - 300px); position: relative;">
                        <form id="attribute_mapping" class="row">
                        <?php foreach ($gmcAttributes as $key => $attribute) { 
                            unset($tempAttr[$attribute["field"]]);
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
                                        <input type="text" class="form-control" name="product_id_prefix" id="product_id_prefix" placeholder="Add Prefix" value="<?php echo esc_attr($result[0]['product_id_prefix']) ?>" >
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
                            if(!empty($tempAttr)) {
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
                                foreach($tempAttr as $key => $value){ 
                                    $options = '<option>Please Select Attribute</option>';
                                    foreach($additionalAttribute as $val ) { 
                                        $selected = "";
                                        $disabled = "";                                                       
                                        if($val == $key) {
                                            $selected = "selected";
                                        }else{
                                            if(array_key_exists($val, $tempAttr)) {
                                                $disabled = "disabled"; 
                                            }
                                        }
                                        
                                        $options .= '<option value="'.$val.'" '.$selected.' '.$disabled.'>'.$val.'</option>';
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
                                            $option1 .= '<option value="'.$genKey.'" '.$selected.'>'.$genVal.'</option>';
                                            }
                                        }
                                        if($key == 'condition') {
                                            $conArr = ['new' => 'New', 'refurbished' => 'Refurbished', 'used' => 'Used'];
                                            foreach($conArr as $conKey => $conVal) {
                                            $selected = "";
                                            if($conKey == $value) {
                                                $selected = "selected";
                                            }
                                            $option1 .= '<option value="'.$conKey.'" '.$selected.'>'.$conVal.'</option>';
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
                                                $option1 .= '<option value="' . $boolKey . '" ' . $selected . '>' . $boolVal . '</option>';
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
                        <input type="hidden" name="edit" id="edit" value="<?php echo esc_attr($result[0]['id']) ?>">
                        <input type="hidden" name="is_mapping_update" id="is_mapping_update" value="<?php echo esc_attr($result[0]['is_mapping_update']) ?>">
                        <input type="hidden" name="last_sync_date" id="last_sync_date" value="<?php echo esc_attr($result[0]['last_sync_date']) ?>">
                    </div>
                </div>
            </div>
        </div>
        <!-- Nav Pills -->
        
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
        jQuery('#target_country').select2()
        jQuery('.categorySelect').select2()        
        jQuery('.attributeClass').select2()
        jQuery('.selectAttr').select2()
        jQuery('#product_batch_size').select2()       
        
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
        
        jQuery(document).on('click', '.cancelFeed', function() { 
            window.location.replace("<?php echo esc_url_raw($site_url.'feed_list'); ?>");
        })
    });

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
    var selected = Array();
    var arr = Array();
    var cnt = <?php echo $cnt ?>;    
    var tempArr = <?php echo json_encode($tempAttr); ?> 
    if(tempArr) {
        arr = Object.keys(tempArr).map(function (key) { return key; }); 
    }                                              
    selected = arr;
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
    })
    function disableOptions() {
        //remove attr
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
    function selectSubCategory(thisObj) {
        selectId = thisObj.id;
        wooCategoryId = jQuery(thisObj).attr("catid");
        var selvalue = jQuery('#' + selectId).find(":selected").val();
        var seltext = jQuery('#' + selectId).find(":selected").text();
        jQuery("#category-" + wooCategoryId).val(selvalue);
        jQuery("#category-name-" + wooCategoryId).val(seltext);
    }
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
            is_mapping_update: jQuery('#is_mapping_update').val(),
            customer_subscription_id: "<?php echo esc_js($subscriptionId) ?>",
            tiktok_business_account: "<?php echo esc_js($tiktok_business_account) ?>",
            last_sync_date: jQuery('#last_sync_date').val(),
            edit: jQuery('#edit').val(),
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
    
</script>

















