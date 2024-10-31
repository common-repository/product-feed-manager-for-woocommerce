<?php
/**
 * @since      4.1.4
 * Description: Conversios Onboarding page, It's call while active the plugin
 */
if (class_exists('Convpfm_Dashboard') === FALSE) {
    class Convpfm_Dashboard
    {
        protected $convpfm_options;
        protected $Convpfm_TVC_Admin_Helper;
        protected $CustomApi;
        protected $Convpfm_Admin_DB_Helper;
        protected $convpfm_api_data;
        public function __construct()
        {
            $this->Convpfm_TVC_Admin_Helper = new Convpfm_TVC_Admin_Helper();
            $this->Convpfm_Admin_DB_Helper = new Convpfm_Admin_DB_Helper();
            $this->CustomApi = new Convpfm_CustomApi();            
            $this->convpfm_options = unserialize(get_option('convpfm_options')); 
            $this->convpfm_api_data = unserialize(get_option('convpfm_api_data'));
            $this->convpfm_current_html();
        }      
                
        public function convpfm_current_html()
        { 
            /***update convpfm_customer_gmail after gmail google auth ***********/
            if(isset($_GET['g_mail']) && isset($_GET['subpage']) && $_GET['subpage'] == 'gmcsettings') {
                update_option('convpfm_customer_gmail', sanitize_email($_GET['g_mail']));
                $this->Convpfm_TVC_Admin_Helper->update_subscription_details_api_to_db();
            }

            if(isset($_GET['g_mail']) && isset($_GET['subpage']) && $_GET['subpage'] == 'gadsettings') {
                update_option('convpfm_customer_gmail', sanitize_email($_GET['g_mail']));
                $this->Convpfm_TVC_Admin_Helper->update_subscription_details_api_to_db();
            }
            /***************End *********************************************/
            $subscriptionId = '';
            if (isset($this->convpfm_options['subscription_id']) === TRUE && $this->convpfm_options['subscription_id'] !== '') {
                $subscriptionId = esc_html($this->convpfm_options['subscription_id']);
            }

            $tvc_data = $this->Convpfm_TVC_Admin_Helper->get_store_data();
            $g_mail = get_option('convpfm_customer_gmail');
            $tvc_data['g_mail'] = "";
            if ($g_mail) {
                $tvc_data['g_mail'] = sanitize_email($g_mail);
            }
            
            /*************Google Settings ********************8888*/
            $google_merchant_center_id = '';
            if (isset($this->convpfm_options['google_merchant_id']) === TRUE && $this->convpfm_options['google_merchant_id'] !== '') {
                $google_merchant_center_id = esc_html($this->convpfm_options['google_merchant_id']);
            }  
             $merchan_id = '';      
            if(isset($this->convpfm_options['merchant_id']) && $this->convpfm_options['merchant_id'] !== ''){
                $merchan_id = isset($this->convpfm_options['merchant_id']) ? esc_html($this->convpfm_options['merchant_id']) : '';
            }  
            $google_ads_id = '';
            if (isset($this->convpfm_options['google_ads_id']) === TRUE && $this->convpfm_options['google_ads_id'] !== '') {
                $google_ads_id = esc_html($this->convpfm_options['google_ads_id']);
            } 
            $get_site_domain = unserialize(get_option('convpfm_api_data'));
            $tvc_data['store_id'] = (isset($get_site_domain['setting']->store_id)) ? $get_site_domain['setting']->store_id : '';
            
            $is_domain_claim = (isset($get_site_domain['setting']->is_domain_claim)) ? esc_html($get_site_domain['setting']->is_domain_claim) : 0;
            $is_site_verified = (isset($get_site_domain['setting']->is_site_verified)) ? esc_html($get_site_domain['setting']->is_site_verified) : 0;
            $google_connect_url = $this->Convpfm_TVC_Admin_Helper->get_custom_connect_url_subpage(admin_url() . 'admin.php?page=conversiospfm', "gmcsettings");
            $gads_connect_url = $this->Convpfm_TVC_Admin_Helper->get_custom_connect_url_subpage(admin_url() . 'admin.php?page=conversiospfm', "gadsettings"); 
            
            /********Fb Setttings ***************/
            $fb_mail = isset($this->convpfm_options['facebook_setting']['fb_mail']) === TRUE ? esc_html($this->convpfm_options['facebook_setting']['fb_mail']) : '';
            if (isset($_GET['g_mail']) == TRUE && $_GET['subpage'] == 'metasettings') {
                $fb_mail = sanitize_email($_GET['g_mail']);
            }
            $facebook_business_account = '';
            if (isset($this->convpfm_options['facebook_setting']['fb_business_id']) === TRUE && $this->convpfm_options['facebook_setting']['fb_business_id'] !== '') {
                $facebook_business_account = esc_html($this->convpfm_options['facebook_setting']['fb_business_id']);
            }
            $facebook_catalog_id = '';
            if (isset($this->convpfm_options['facebook_setting']['fb_catalog_id']) === TRUE && $this->convpfm_options['facebook_setting']['fb_catalog_id'] !== '') {
                $facebook_catalog_id = esc_html($this->convpfm_options['facebook_setting']['fb_catalog_id']);
            }           
            
            $store_country = get_option('woocommerce_default_country');
            $store_country = explode(":",$store_country);
            if($store_country[0]){
                $country = esc_html($store_country[0]);
            }else{
                $country = '';
            }
            
            $woo_currency = get_option('woocommerce_currency');
            $timezone = get_option('timezone_string');
            $confirm_url = "admin.php?page=conversiospfm&subpage=metasettings";
            $subscriptionId = isset($_GET['subscription_id']) ? esc_html($_GET['subscription_id']) : esc_html($subscriptionId);
            $facebook_auth_url = CONVPFM_API_CALL_URL_TEMP . '/auth/facebook?domain='.esc_url_raw(get_site_url()).'&app_id='.CONVPFM_APP_ID.'&country='.$country.'&user_currency='.$woo_currency.'&subscription_id='.$subscriptionId.'&confirm_url='.admin_url().$confirm_url.'&timezone='.$timezone.'&scope=productFeed' ;
            $businessId = '';
            $catalogId = '';
            $data = array(
                "customer_subscription_id" => esc_html($subscriptionId)
            );
            if($fb_mail) {
                $businessId =  $this->CustomApi->getUserBusinesses($data); 
            }                     
            
            if($facebook_business_account !== ''){
                $cat_data = array(
                    "customer_subscription_id" => esc_html($subscriptionId),
                    "business_id" => esc_html($facebook_business_account),
                );
                $catalogId = $this->CustomApi->getCatalogList($cat_data);
            }            
            /************Tiktok Settings *******************/
            $catalogData = $this->Convpfm_Admin_DB_Helper->tvc_get_results('convpfm_tiktok_catalog');
            $catalogCountry = array();
            $catalog_business_id = array();
            if (is_array($catalogData) && !empty($catalogData)) {
                foreach ($catalogData as $key => $value) {
                    $catalogCountry[$key] = esc_html($value->country);
                    $catalog_business_id[$key] = esc_html($value->catalog_id);
                }
            }
            $tiktok_mail = isset($this->convpfm_options['tiktok_setting']['tiktok_mail']) === TRUE ? esc_html($this->convpfm_options['tiktok_setting']['tiktok_mail']) : '';
            $tiktok_user_id = isset($this->convpfm_options['tiktok_setting']['tiktok_user_id']) === TRUE ? esc_html($this->convpfm_options['tiktok_setting']['tiktok_user_id']) : '';
            $tiktok_business_name = isset($this->convpfm_options['tiktok_setting']['tiktok_business_name']) === TRUE ? esc_html($this->convpfm_options['tiktok_setting']['tiktok_business_name']) : '';
            $tiktok_business_account = '';
            if (isset($this->convpfm_options['tiktok_setting']['tiktok_business_id']) === TRUE && $this->convpfm_options['tiktok_setting']['tiktok_business_id'] !== '') {
                $tiktok_business_account = esc_html($this->convpfm_options['tiktok_setting']['tiktok_business_id']);
            }
            if (isset($_GET['tiktok_mail']) == TRUE) {
                $tiktok_mail = esc_html($_GET['tiktok_mail']);
            }
            $tiktok_business_list = '';
            if (isset($_GET['tiktok_user_id']) == TRUE) {
                $tiktok_user_id = esc_html($_GET['tiktok_user_id']);
            }  
            if($tiktok_user_id != ''){
                $tiktok_business_list = $this->CustomApi->get_tiktok_business_account($data);
            }
            
            $tiktok_connect_url = "admin.php?page=conversiospfm&subpage=tiktoksettings";
            $state = ['confirm_url' => admin_url() . $tiktok_connect_url, 'subscription_id' => $subscriptionId];
            $tiktok_auth_url = "https://ads.tiktok.com/marketing_api/auth?app_id=7233778425326993409&redirect_uri=https://connect.tatvic.com/laravelapi/public/auth/tiktok/callback&rid=q6uerfg9osn&state=" . urlencode(json_encode($state));
            $site_url = "admin.php?page=convpfm-google-shopping-feed&tab=feed_list&create-feed=New-Feed";            
        ?>
            <style>
                .card {
                    max-width: 100%;
                    margin: 10px 0px;
                    padding: 0;
                    box-shadow: 0px 3px 9px rgba(0, 0, 0, 0.13);
                    border-radius: 8px;
                }
                .imgChannel {
                    width: 40px;
                    height: 40px;
                }
                .bg-success_ {
                    border: 1px solid #09BD83;
                    color: #09BD83;
                    background-color: #FFF;
                    border-radius: 8px;
                    font-size: 12px;
                    padding: 0px 0px 0px 8px;
                    width:89px;
                    height: 28px;
                    font-weight: 500;
                }
                .bg-success_:hover {
                    color:#FFF;
                    background-color: #09BD83;
                }
                .bg-success_:focus {
                    box-shadow: none;
                }
                .bg-warnings {
                    border: 1px solid #EF4D2F;
                    color: #EF4D2F;
                    background-color: #FFF;
                    border-radius: 8px;
                    font-size: 12px;
                    padding: 0px 0px 0px 8px;
                    width:109px;
                    height: 28px;
                    font-weight: 500;
                }
                .bg-warnings:hover {
                    color:#FFF;
                    background-color: #EF4D2F;
                }
                .bg-warnings:focus {
                    box-shadow: none;
                }
                .modal-content {
                    border-radius: 8px;
                }
                .tvc_fb_signinbtn {
                    cursor: pointer;
                    background-color: transparent;
                    box-shadow: none !important;
                }
                .fb-btn {
                    margin: 0 auto;
                    width: 190px;
                    height: 42px;
                    border-radius: 2px;
                    margin-bottom: 15px;
                }
                .tvc_tiktok_signinbtn {
                    cursor: pointer;
                    background-color: transparent;
                    box-shadow: none !important;
                }
                .tiktok-btn {
                    margin: 0 auto;
                    width: 190px;
                    height: 42px;
                    border-radius: 2px;
                    margin-bottom: 15px;
                }
                ::-webkit-scrollbar {
                    width: 3px;
                    height: 5px;
                }

                ::-webkit-scrollbar-track {
                    background: #fff;
                }

                ::-webkit-scrollbar-thumb {
                    background: #d4e6f6;
                    border-radius: 5px;
                }

                ::-webkit-scrollbar-thumb:hover {
                    background: #d4e6f6;
                }
                .table-wrapper {
                    border: 1px solid #ccc; /* Outer border color */
                    border-radius: 8px; /* Rounded corners */
                    overflow: hidden; /* Ensure border-radius applies correctly */
                }

                .table-rounded {
                    width: 100%;
                    border-collapse: separate;
                    border-spacing: 0;
                }

                .table-rounded th,
                .table-rounded td {
                    padding: 8px; /* Adjust padding as needed */
                    text-align: left;
                    border: none; /* Remove inner borders */
                }

                /* Optional: Styling for table headers */
                .table-rounded thead th {
                    background-color: #f0f0f0; /* Header background color */
                    border-bottom: 0px solid #ccc; /* Header bottom border */
                }
                
                .hoverEffect {
                    border: 1px solid #1967D2;
                    background-color: #fff;
                    box-shadow: 0px 3px 9px rgba(0, 0, 0, 0.36) !important;
                }
                .card-title {
                    color: #2A2D2F;
                }
                .modal_popup_logo_success {
                    color: #09BD83;
                }
                .modal_popup_logo_error {
                    color: #ff3333;
                }
                .modal-lg {
                    max-width: 697px; /* Adjust as needed */
                    width: 100%; /* Ensure it takes up full width on small screens */
                }

                @media (min-width: 576px) {
                    .modal-lg {
                        max-width: 697px; /* Adjust as needed */
                    }
                }

                @media (min-width: 992px) {
                    .modal-lg {
                        max-width: 697px; /* Adjust as needed */
                    }
                }

                @media (min-width: 1200px) {
                    .modal-lg {
                        max-width: 697px; /* Adjust as needed */
                    }
                }
            </style>
            <section style="max-width: 1200px;">
                <div class="dash-convo">
                    <div class="container">
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="dash-area">
                                    <div class="dashwhole-box ps-4">
                                        <div class="head-title d-flex justify-content-between">
                                            <span class="fw-bold text-dark fs-20">
                                                <img class="imgChannel" src="<?php echo esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/web_store.png'); ?>" alt="">
                                                <?php esc_html_e("Configure Your Multi-Auth Settings", "product-feed-manager-for-woocommerce"); ?>
                                            </span>                                            
                                        </div>
                                        <p class="text-grey fs-16 fw-400">To access your Catalog ID and Google Merchant Center (GMC) ID, you need to authenticate your account. This involves verifying your identity through a secure process, ensuring that only authorized users can view or manage these important identifiers.
                                            </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="welcome-wholebox">
                            <div class="row ps-4" style="gap: 16px;">
                                <input type="hidden" id="g_mail-value" value="<?php echo esc_attr($g_mail)?>">
                                <input type="hidden" id="google_merchant_center_id-value" value="<?php echo esc_attr($google_merchant_center_id)?>">
                                <input type="hidden" id="tiktok_business_account-value" value="<?php echo esc_attr($tiktok_business_account)?>">
                                <input type="hidden" id="facebook_business_account-value" value="<?php echo esc_attr($facebook_business_account)?>">
                                <input type="hidden" id="facebook_catalog_id-value" value="<?php echo esc_attr($facebook_catalog_id)?>">
                                <input type="hidden" id="fb_mail" value="<?php echo esc_attr($fb_mail) ?>" />
                                <input type="hidden" id="tiktok_mail" value=<?php echo esc_attr($tiktok_mail) ?>>
                                <input type="hidden" id="tiktok_user_id" value=<?php echo esc_attr($tiktok_user_id) ?>> 
                                <div class="card card-channel text-center gmc-card" style="width: 346.67px; height:174px;">
                                    <div class="card-body">
                                        <img class="imgChannel" src="<?php echo esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/google_shopping_icon.png'); ?>" alt="">
                                        <h5 class="card-title fw-600 fs-16 mt-3">Google Merchant Center</h5>
                                        <p class="card-text gmcId fw-500 fs-12 text-grey"></p>
                                        <p class="card-text gmc-status fw-500 fs-14 status"></p>                                        
                                    </div>
                                </div>
                                <div class="card card-channel text-center fb-card" style="width: 346.67px; height:174px;">
                                    <div class="card-body">
                                        <img class="imgChannel" src="<?php echo esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/fb-icon.png'); ?>" alt="">
                                        <h5 class="card-title fw-600 fs-16 mt-3">Facebook Catalog</h5>
                                        <p class="card-text fbId fs-12 text-grey"></p>
                                        <p class="card-text fb-status fw-500 fs-14 status"></p>                                        
                                    </div>
                                </div>
                                <div class="card card-channel text-center tiktok-card" style="width: 346.67px; height:174px;">
                                    <div class="card-body">
                                        <img class="imgChannel" src="<?php echo esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/conv_tiktok_logo.png'); ?>" alt="">
                                        <h5 class="card-title fw-600 fs-16 mt-3">Tiktok Catalog</h5>
                                        <p class="card-text tiktokId fs-12 text-grey"></p>
                                        <p class="card-text tiktok-status fw-500 fs-14 status"></p>                                        
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                        <div class="row mt-6">
                            <div class="col-12">
                                <div class="dash-area">
                                    <div class="dashwhole-box ps-4">
                                        <div class="head-title d-flex justify-content-between">
                                            <span class="fw-bold text-dark fs-20">
                                                <img class="imgChannel" src="<?php echo esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/add_channel.png'); ?>" alt="">
                                                <?php esc_html_e("Ads Channels", "product-feed-manager-for-woocommerce"); ?>
                                            </span>                                            
                                        </div>
                                        <p class="text-grey fs-16 fw-400">To link your Google Merchant Center (GMC) account with Google Ads and create campaigns, you need your Google Ads ID. This unique identifier helps connect your GMC data with your Google Ads account, allowing you to run shopping campaigns and manage your ad settings effectively.
                                            </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="welcome-wholebox">
                            <div class="row ps-4" style="gap: 16px;">
                                <input type="hidden" id="google_ads_id-value" value="<?php echo esc_attr($google_ads_id)?>">
                                <div class="card card-channel text-center gads-card" style="width: 346.67px; height:174px;">
                                    <div class="card-body">
                                        <img class="imgChannel" src="<?php echo esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/google ads_icon.png'); ?>" alt="">
                                        <h5 class="card-title fw-600 fs-16 mt-3">Google Ads</h5>
                                        <p class="card-text gadsId fw-500 fs-12 text-grey"></p>
                                        <p class="card-text gads-status fw-500 fs-14 status"></p>                                        
                                    </div>
                                </div>                                
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- All Modals -->
            <div class="modal fade" id="GMC-modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
                aria-labelledby="staticBackdropLabel" aria-hidden="true">                
                <div class="modal-dialog modal-dialog-centered">                
                    <div class="modal-content d-flex">
                        <div id="gmc_modal_loader" class="progress-materializecss d-none ps-2 pe-2" style="width:100%">
                            <div class="indeterminate"></div>
                        </div>
                        <div class="modal-header col-11 mx-auto border-0 pb-0">
                            <img class="imgChannel col-1" src="<?php echo esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/google_modal.png'); ?>" alt="">
                            <h5 class="fw-600 fs-20 col-10" style="margin-left: 1px">Google Merchant Center</h5>
                        </div>
                        
                        <div class="modal-body col-11 mx-auto">                        
                            <?php if ($g_mail != "") { ?>
                                <h5 class="fw-normal mb-1">
                                    <span class="fw-600 fs-16"><?php esc_html_e("Successfully signed in with account:", "product-feed-manager-for-woocommerce"); ?></span>
                                    <br><span class="fw-400 fs-14"><?php echo (isset($g_mail) && esc_attr($subscriptionId)) ? esc_attr($g_mail) : ""; ?></span>
                                    <span class="conv-link-blue ps-0 tvc_google_signinbtn fw-600 fs-14 ms-2">
                                        <?php esc_html_e(" Change", "product-feed-manager-for-woocommerce"); ?>
                                    </span>
                                </h5>
                            <?php } else { ?>
                                <div class="tvc_google_signinbtn_box" style="width: 185px;">
                                    <div class="tvc_google_signinbtn google-btn">
                                        <button class="btn bg-primary text-white fw-600 fs-14" style="padding: 3px">
                                            <img style="width: 30px; height: 30px" src="<?php echo esc_url(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/g-logo.png'); ?>" />
                                            <?php esc_html_e("Sign in with google", "product-feed-manager-for-woocommerce"); ?>
                                        </button>
                                    </div>
                                </div>
                            <?php } ?>
                            <div class="mt-2">
                                <span class="fw-400 fs-12">Do Not Have GMC Account? <b class="text-primary createNewGMC pointer">Create Now</b></span>
                            </div>
                            <div class="row mt-4">
                                <div class="col-10">
                                    <select id="google_merchant_center_id" name="google_merchant_center_id" class="form-select col-8 mx-auto" style="width: 100%" disabled>
                                        <option value="">Select Google Merchant Center Account</option>
                                        <?php if (!empty($google_merchant_center_id)) { ?>
                                            <option value="<?php echo esc_attr($google_merchant_center_id); ?>" selected data-merchant_id = "<?php echo esc_attr($merchan_id); ?>"><?php echo esc_attr($google_merchant_center_id); ?></option>
                                        <?php } ?>                                                        
                                    </select>
                                </div>
                                <div class="col-2">
                                    <span class="fs-14 text-primary pointer getGMCList"><span class="material-symbols-outlined md-18" style="margin-top: 8px;">edit</span>Edit</span>
                                </div>
                                <div>
                                    <?php if (!empty($google_merchant_center_id)) { ?>
                                        <div class="alert alert-warning mt-2" role="alert">
                                            Changing the GMC Id will affect your existing product feed.
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>                            
                            <span class="text-success fs-12 d-none new-gmc-text">Your New GMC Id <b class="new-gmc-id"></b> Has Been Created</span>
                            <p class="mt-4">
                                <span class="inner-text fw-600 d-flex" style="width: auto"><?php esc_html_e("Site Verification ", "product-feed-manager-for-woocommerce"); ?> 
                                    <span class="material-symbols-outlined fs-6 ms-1 pointer" data-bs-toggle="tooltip"
                                        data-bs-placement="right"
                                        title="Specify the target country for your product feed. Select the country where you intend to promote and sell your products.">
                                        info
                                    </span>    
                                    <button type="button" style="width: 100px;" class="btn <?php echo $is_site_verified == '1' ? 'bg-success_ verifySite pointer' : 'bg-warnings verifySite pointer' ?> d-flex align-items-center ms-2">                                     
                                        <?php echo $is_site_verified == '1' ? 'Verified' : 'Verify Now' ?> &nbsp;<span class="material-symbols-outlined mx-auto" style="font-size: 18px;">
                                        <?php echo $is_site_verified == '1' ? 'verified' : 'autorenew' ?>
                                        </span> 
                                    </button>
                                </span>
                            </p>
                            <p>
                                <span class="inner-text fw-600 d-flex" style="width: auto"><?php esc_html_e("Domain Claim ", "product-feed-manager-for-woocommerce"); ?> 
                                <span class="material-symbols-outlined fs-6 ms-1 pointer" data-bs-toggle="tooltip"
                                        data-bs-placement="right"
                                        title="Specify the target country for your product feed. Select the country where you intend to promote and sell your products.">
                                        info
                                    </span>    
                                &nbsp;&nbsp;<button type="button" style="width: 100px;" class="btn <?php echo $is_domain_claim == '1' ? 'bg-success_ verifySite pointer' : 'bg-warnings verifyDomain pointer' ?> d-flex align-items-center float-end ms-2">                                     
                                <?php echo $is_domain_claim == '1' ? 'Verified' : 'Claim Now' ?> &nbsp;<span class="material-symbols-outlined mx-auto" style="font-size: 18px;">
                                        <?php echo $is_domain_claim == '1' ? 'verified' : 'autorenew' ?>
                                        </span>
                                    </button>
                                </span>
                            </p>   
                        </div>
                        <div class="modal-footer col-12 mx-auto border-0 pb-4 mb-1 ps-0 pe-0">                                
                            <button class="btn m-auto me-1 bg-default text-primary border border-primary fs-14" data-bs-dismiss="modal" style="width: 200px;">Cancel</button>
                            <button class="btn m-auto ms-1 bg-primary text-white saveGMC fs-14" style="width: 200px;">Connect Now</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="create-gmc-modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
                aria-labelledby="staticBackdropLabel" aria-hidden="true">                
                <div class="modal-dialog modal-dialog-centered modal-lg">                
                    <div class="modal-content d-flex">
                        <div id="create_gmc_modal_loader" class="progress-materializecss d-none ps-2 pe-2" style="width:100%">
                            <div class="indeterminate"></div>
                        </div>
                        <div class="modal-header border-0 pb-0" style="padding-left: 5.0rem!important">
                            <div class="col-12">
                                <img class="imgChannel float-start me-3" src="<?php echo esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/google_modal.png'); ?>" alt="">
                                <h5 class="fw-600 fs-20">Create New Google Merchant Center Account</h5>
                            </div>                            
                        </div>                        
                        <div class="modal-body col-11 mx-auto">                              
                            <div class="alert d-flex align-items-cente p-0" role="alert">
                                <div class="text-light bg-primary rounded-start d-flex">
                                    <span class="p-2 material-symbols-outlined align-self-center">info</span>
                                </div>
                                <div class="p-2 w-100 rounded-end border border-start-0 shadow-sm conv-notification-alert bg-white">
                                    <div class="">
                                        <?php echo esc_html("To upload your product data, it is necessary to go through a process of verifying and calming your store’s website URL. This step of claiming your website URL links it with your Google Merchant Center Account."); ?>
                                    </div>
                                </div>
                            </div>   
                            <div id="create_gmc_error" class="alert alert-danger d-none" role="alert">
                                <small></small>
                            </div>
                            <form id="conv_form_new_gmc">
                                <div class="mb-3">
                                    <input class="form-control mb-4" type="text" id="gmc_website_url" name="website_url"
                                        value="<?php echo esc_attr($tvc_data['user_domain']); ?>" placeholder="Enter Website"
                                        required>

                                    <input class="form-control mb-4" type="text" id="gmc_email_address"
                                        name="email_address"
                                        value="<?php echo isset($tvc_data['g_mail']) === TRUE ? esc_attr($tvc_data['g_mail']) : ""; ?>"
                                        placeholder="Enter email address" required>                                   

                                    <input class="form-control mb-0" type="text" id="gmc_store_name" name="store_name"
                                        value="" placeholder="Enter Store Name" required>
                                    <small class="mb-4">
                                        <?php esc_html_e("This name will appear in your Shopping Ads.", "product-feed-manager-for-woocommerce"); ?>
                                    </small>

                                    <div class="mb-3 mt-3" id="conv_create_gmc_selectthree">
                                        <select id="gmc_country" name="country"
                                            class="form-select form-select-lg mb-3 selectthree" style="width: 100%"
                                            placeholder="Select Country" required>
                                            <option value="">Select Country</option>
                                            <?php
                                            $getCountris = file_get_contents(CONVPFM_ENHANCAD_PLUGIN_DIR . "includes/setup/json/countries.json");
                                            $contData = json_decode($getCountris);
                                            foreach ($contData as $key => $value) {
                                                ?>
                                                <option value="<?php echo esc_attr($value->code) ?>"
                                                    <?php echo $tvc_data['user_country'] === $value->code ? 'selected = "selecetd"' :
                                                        '' ?>>
                                                    <?php echo esc_attr($value->name) ?>
                                                </option>"
                                                <?php
                                            }

                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-check mb-4">
                                        <input class="form-check-input ms-1" type="checkbox" id="gmc_adult_content"
                                            name="adult_content" value="1" style="float:none">
                                        <label class="form-check-label" for="flexCheckDefault">
                                            <?php esc_html_e("My site contain", "product-feed-manager-for-woocommerce"); ?>
                                            <b>
                                                <?php esc_html_e("Adult Content", "product-feed-manager-for-woocommerce"); ?>
                                            </b>
                                        </label>
                                    </div>
                                    <div class="form-check mb-4">
                                        <input id="gmc_concent" name="concent" class="form-check-input ms-1" type="checkbox"
                                            value="1" required style="float:none">
                                        <label class="form-check-label" for="concent">
                                            <?php esc_html_e("I accept the", "product-feed-manager-for-woocommerce"); ?>
                                            <a target="_blank" href="<?php echo esc_url_raw("
                                                https://support.google.com/merchants/answer/160173?hl=en"); ?>">
                                                <?php esc_html_e("terms & conditions", "product-feed-manager-for-woocommerce"); ?>
                                            </a>
                                        </label>
                                    </div>

                                </div>

                            </form>                                                     
                        </div>
                        <div class="modal-footer col-11 mx-auto border-0 pb-4 mb-1 ps-0 pe-0">                                
                            <button class="btn m-auto me-1 bg-default text-primary border border-primary fs-14" data-bs-dismiss="modal" style="width:303px;">Cancel</button>
                            <button class="btn m-auto ms-1 bg-primary text-white createGMC fs-14" style="width:303px;">Create Now</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="auth-success-modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
                aria-labelledby="staticBackdropLabel" aria-hidden="true">                
                <div class="modal-dialog modal-dialog-centered">                
                    <div class="modal-content d-flex">
                        <div id="success_modal_loader" class="progress-materializecss d-none ps-2 pe-2" style="width:100%">
                            <div class="indeterminate"></div>
                        </div>
                        <div class="modal-header col-10 mx-auto border-0 pb-0">
                            <img class="" src="<?php echo esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/conv_modal_img_highfive.png'); ?>" alt="">
                        </div>
                        
                        <div class="modal-body col-10 mx-auto">                              
                            <div class="account-message fs-12 fw-400 text-center"></div>  
                            <div class="account-id fs-12 fw-700 text-center"></div>                                                         
                        </div>
                        <div class="modal-footer col-10 mx-auto border-0 pb-4 mb-1 ps-0 pe-0">                                
                            <button class="btn col-5 bg-default text-primary border border-primary fs-14" data-bs-dismiss="modal">Add Another Channel</button>
                            <a class="btn col-5 bg-primary text-white createFeed fs-14" href="<?php echo esc_url($site_url ); ?>">Create Feed</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="FB-modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
                aria-labelledby="staticBackdropLabel" aria-hidden="true">                
                <div class="modal-dialog modal-dialog-centered modal-lg">                
                    <div class="modal-content d-flex">
                        <div id="fb_modal_loader" class="progress-materializecss d-none ps-2 pe-2" style="width:100%">
                            <div class="indeterminate"></div>
                        </div>
                        <div class="modal-header border-0 pb-0" style="padding-left: 3.0rem!important">
                            <div class="col-12">
                                <img class="imgChannel float-start me-3" src="<?php echo esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/fb_modal.png'); ?>" alt="">
                                <h5 class="fw-600 fs-20">Facebook Catalog</h5>
                            </div>                            
                        </div>
                        
                        <div class="modal-body col-11 mx-auto">                        
                            <?php if ($fb_mail != "") { ?>
                                <h5 class="fw-normal mb-1">
                                    <span class="fw-600 fs-16"><?php esc_html_e("Successfully signed in with account:", "product-feed-manager-for-woocommerce"); ?></span>
                                    <br><span class="fw-400 fs-14"><?php echo (isset($fb_mail) && esc_attr($subscriptionId)) ? esc_attr($fb_mail) : ""; ?></span>
                                    <span class="conv-link-blue ps-0 tvc_fb_signinbtn fw-600 fs-14 ms-2" onclick="window.open('<?php echo $facebook_auth_url ?>','MyWindow','width=800,height=700,left=300, top=150'); return false;" href="#">
                                        <?php esc_html_e(" Change", "product-feed-manager-for-woocommerce"); ?>
                                    </span>
                                </h5>
                            <?php } else { ?>
                                <div class="tvc_google_signinbtn_box" style="width: 185px;">
                                    <div class="tvc_fb_signinbtn fb-btn">
                                        <button class="btn bg-primary text-white fw-600 fs-14" style="padding: 3px" onclick="window.open('<?php echo $facebook_auth_url ?>','MyWindow','width=800,height=700,left=300, top=150'); return false;" href="#">
                                            <img style="width: 30px; height: 30px" src="<?php echo esc_url(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/fb-logo.png'); ?>" />
                                            <?php esc_html_e("Sign in with Facebook", "product-feed-manager-for-woocommerce"); ?>
                                        </button>
                                    </div>
                                </div>
                            <?php } ?>
                            
                            <div class="row mt-4">
                                <div class="col-5">
                                    <select id="fb_business_id" name="fb_business_id" class="form-select col-8 mx-auto" style="width: 100%" disabled>
                                        <option value="">Select Facebook Business ID</option>                                        
                                        <?php 
                                            if(isset($businessId) && $businessId != ''){
                                                foreach($businessId as $key => $businessVal){ 
                                                    ?>
                                                        <option value="<?php echo $key ?>" <?php echo isset($this->convpfm_options['facebook_setting']['fb_business_id']) && $this->convpfm_options['facebook_setting']['fb_business_id'] == $key ?  "selected" : '' ?> ><?php echo esc_html($businessVal) ?></option>
                                                <?php 
                                                }
                                            }
                                        ?>                                                      
                                    </select>
                                </div>
                                <div class="col-5">
                                    <select id="fb_catalog_id" name="fb_catalog_id" class="form-select col-8 mx-auto" style="width: 100%" disabled>
                                        <option value="">Select Facebook Catalog ID</option>
                                        <?php 
                                            if(isset($catalogId->data)){
                                                foreach($catalogId->data as $key => $catalogVal){                                                     
                                                    ?>                                                                                             
                                                        <option value="<?php echo $catalogVal->id ?>" <?php echo isset($this->convpfm_options['facebook_setting']['fb_catalog_id']) && $this->convpfm_options['facebook_setting']['fb_catalog_id'] == $catalogVal->id ?  "selected" : '' ?> ><?php echo esc_html($catalogVal->id).'-'.esc_html($catalogVal->name) ?></option>
                                                    <?php 
                                                }
                                            }
                                        ?>                                                       
                                    </select>
                                </div>
                                <?php if ($fb_mail != "") { ?>
                                <div class="col-2">
                                    <span class="fs-14 text-primary pointer getFBList"><span class="material-symbols-outlined md-18" style="margin-top: 8px;">edit</span>Edit</span>
                                </div>
                                <?php } ?>
                                <div>
                                    <?php if (isset($this->convpfm_options['facebook_setting']['fb_catalog_id'])) { ?>
                                        <div class="alert alert-warning mt-2" role="alert">
                                            Changing the Catalog Id will affect your existing product feed.
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>                             
                        </div>
                        <div class="modal-footer col-11 mx-auto border-0 pb-4 mb-1 ps-0 pe-0">                                
                            <button class="btn m-auto me-1 bg-default text-primary border border-primary fs-14" data-bs-dismiss="modal" style="margin-left:15px; width:303px">Cancel</button>
                            <button class="btn m-auto ms-1 bg-primary text-white saveFB fs-14" style="width:303px">Connect Now</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="GADS-modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
                aria-labelledby="staticBackdropLabel" aria-hidden="true">                
                <div class="modal-dialog modal-dialog-centered">                
                    <div class="modal-content d-flex">
                        <div id="gads_modal_loader" class="progress-materializecss d-none ps-2 pe-2" style="width:100%">
                            <div class="indeterminate"></div>
                        </div>
                        <div class="modal-header col-11 mx-auto border-0 pb-0">
                            <img class="imgChannel col-1" src="<?php echo esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/ads_modal.png'); ?>" alt="">
                            <h5 class="fw-600 fs-20 col-10" style="margin-left: 1px">Google Ads</h5>
                        </div>
                        
                        <div class="modal-body col-11 mx-auto">                        
                            <?php if ($g_mail != "") { ?>
                                <h5 class="fw-normal mb-1">
                                    <span class="fw-600 fs-16"><?php esc_html_e("Successfully signed in with account:", "product-feed-manager-for-woocommerce"); ?></span>
                                    <br><span class="fw-400 fs-14"><?php echo (isset($g_mail) && esc_attr($subscriptionId)) ? esc_attr($g_mail) : ""; ?></span>
                                    <span class="conv-link-blue ps-0 tvc_google_signinbtn_ga fw-600 fs-14 ms-2">
                                        <?php esc_html_e(" Change", "product-feed-manager-for-woocommerce"); ?>
                                    </span>
                                </h5>
                            <?php } else { ?>
                                <div class="tvc_google_signinbtn_box" style="width: 185px;">
                                    <div class="tvc_google_signinbtn_ga google-btn">
                                        <button class="btn bg-primary text-white fw-600 fs-14" style="padding: 3px">
                                            <img style="width: 30px; height: 30px" src="<?php echo esc_url(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/g-logo.png'); ?>" />
                                            <?php esc_html_e("Sign in with google", "product-feed-manager-for-woocommerce"); ?>
                                        </button>
                                    </div>
                                </div>
                            <?php } ?>
                            <div class="mt-2">
                                <span class="fw-400 fs-12">Do Not Have GAds Account? <b class="text-primary createNewGAds pointer">Create Now</b></span>
                            </div>
                            <div class="row mt-4">
                                <div class="col-10">
                                    <select id="google_ads_id" name="google_ads_id" class="form-select col-8 mx-auto" style="width: 100%" disabled>
                                        <option value="">Select Google Ads Id</option>
                                        <?php if (!empty($google_ads_id)) { ?>
                                            <option value="<?php echo esc_attr($google_ads_id); ?>" selected ><?php echo esc_html($google_ads_id); ?></option>
                                        <?php } ?>                                                        
                                    </select>
                                </div>
                                <div class="col-2">
                                    <span class="fs-14 text-primary pointer getGAdsList"><span class="material-symbols-outlined md-18" style="margin-top: 8px;">edit</span>Edit</span>
                                </div>
                                <div>
                                    <?php if (!empty($google_ads_id)) {?>
                                        <div class="alert alert-warning mt-2" role="alert">
                                            Changing the GAds Id will affect your existing Feed Campaign.
                                        </div>
                                    <?php } ?>
                                </div>
                            </div> 
                            <div class="mt-2">
                                <input class="form-check-input check-height fs-14" type="checkbox" value="" id="ga_GMC" name="ga_GMC" <?php echo isset($this->convpfm_options['ga_GMC']) && $this->convpfm_options['ga_GMC'] == '1' ? "checked" : "" ?>>
                                <label class="fs-12 fw-normal text-grey" for="">Link Google merchant center with Google ads</label>
                                <br/><span class="fs-12 text-danger errorGMC_GAds"></span>
                            </div>                           
                        </div>
                        <div class="modal-footer col-12 mx-auto border-0 pb-4 mb-1 ps-0 pe-0">                                
                            <button class="btn m-auto me-1 bg-default text-primary border border-primary fs-14" data-bs-dismiss="modal" style="width:200px;">Cancel</button>
                            <button class="btn m-auto ms-1 bg-primary text-white saveGAds fs-14" style="width:200px;">Connect Now</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="TITOK-modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
                aria-labelledby="staticBackdropLabel" aria-hidden="true">                
                <div class="modal-dialog modal-dialog-centered modal-lg">                
                    <div class="modal-content d-flex">
                        <div id="tiktok_modal_loader" class="progress-materializecss d-none ps-2 pe-2" style="width:100%">
                            <div class="indeterminate"></div>
                        </div>
                        <div class="modal-header border-0 pb-0" style="padding-left: 3.0rem!important">
                            <div class="col-12">
                                <img class="imgChannel float-start me-3" src="<?php echo esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/tiktok_modal.png'); ?>" alt="">
                                <h5 class="fw-600 fs-20">TikTok Catalog</h5>
                            </div>                            
                        </div>
                        
                        <div class="modal-body col-11 mx-auto">                        
                            <?php if ($tiktok_mail != "" && $tiktok_user_id != '') { ?>
                                <h5 class="fw-normal mb-1">
                                    <span class="fw-600 fs-16"><?php esc_html_e("Successfully signed in with account:", "product-feed-manager-for-woocommerce"); ?></span>
                                    <br><span class="fw-400 fs-14"><?php echo ($tiktok_mail . ', <b>User Id: </b>' . $tiktok_user_id . ' '); ?></span>
                                    <span class="conv-link-blue ps-0 tvc_tiktok_signinbtn fw-600 fs-14 ms-2" onclick='window.open("<?php echo $tiktok_auth_url ?>","MyWindow","width=800,height=700,left=300, top=150"); return false;'
                                    href="#">
                                        <?php esc_html_e(" Change", "product-feed-manager-for-woocommerce"); ?>
                                    </span>
                                </h5>
                            <?php } else { ?>
                                <div class="tvc_google_signinbtn_box" style="width: 185px;">
                                    <div class="tvc_tiktok_signinbtn tiktok-btn">
                                        <button class="btn bg-dark text-white fw-600 fs-14" style="padding: 3px" onclick='window.open("<?php echo $tiktok_auth_url ?>","MyWindow","width=800,height=700,left=300, top=150"); return false;'
                                        href="#">
                                            <img style="width: 30px; height: 30px" src="<?php echo esc_url(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/tiktok-logo.png'); ?>" />
                                            <?php esc_html_e("Sign in with TikTok", "product-feed-manager-for-woocommerce"); ?>
                                        </button>
                                    </div>
                                </div>
                            <?php } ?>                            
                            <div class="row mt-4">
                                <div class="col-10">
                                    <select id="tiktok_business_id" name="tiktok_business_id" class="form-select col-8 mx-auto" style="width: 100%" disabled>
                                        <option value="">Select Tiktok Business Account Id</option>                                        
                                        <?php 
                                            if(isset($tiktok_user_id) && $tiktok_user_id != '' && isset($tiktok_business_list->data)){
                                                foreach($tiktok_business_list->data as $tiktokVal){ 
                                                    if ($tiktokVal->bc_info->status === 'ENABLE') {
                                                    ?>
                                                        <option value="<?php echo esc_attr($tiktokVal->bc_info->bc_id) ?>" <?php echo $tiktok_business_account == $tiktokVal->bc_info->bc_id ?  "selected" : '' ?> data-business_name = <?php echo esc_html($tiktokVal->bc_info->name) ?> ><?php echo esc_html($tiktokVal->bc_info->bc_id).' - '. esc_html($tiktokVal->bc_info->name) ?></option>
                                                <?php }
                                                }
                                            }
                                        ?>                                                      
                                    </select>
                                </div>  
                                <?php if ($tiktok_mail != "" && $tiktok_user_id != '') { ?>                              
                                <div class="col-2">
                                    <span class="fs-14 text-primary pointer gettiktokList"><span class="material-symbols-outlined md-18" style="margin-top: 8px;">edit</span>Edit</span>
                                </div>
                               <?php }?>
                               <div>
                                    <?php if (isset($tiktok_business_account)) { ?>
                                        <div class="alert alert-warning mt-2" role="alert">
                                            Changing the Catalog Id will affect your existing product feed.
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="row mt-4">
                                <div class="col-12">
                                    <span class="inner-text fw-600 fs-14">
                                        <?php esc_html_e(" Map Catalog For Target Country", "product-feed-manager-for-woocommerce"); ?>
                                    </span>
                                    <div class="table-wrapper" style="overflow-y: scroll; max-height:450px;">
                                        <table class="table table-rounded" id="map_catalog_table" style="width:100%">
                                            <thead>
                                                <tr class="">
                                                    <th scope="col" class="text-start fw-500 fs-14" width="25%">
                                                        <?php esc_html_e("Target Country", "product-feed-manager-for-woocommerce"); ?>
                                                    </th>
                                                    <th scope="col" class="text-start fw-500 fs-14" width="75%">
                                                        <?php esc_html_e("Catalog Id", "product-feed-manager-for-woocommerce"); ?>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody id="table-body">

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="modal-footer col-11 mx-auto border-0 pb-4 mb-1 ps-0 pe-0">                                
                            <button class="btn m-auto me-1 bg-default text-primary border border-primary fs-14" data-bs-dismiss="modal" style="margin-left:15px;width:303px;">Cancel</button>
                            <button class="btn m-auto ms-1 bg-primary text-white saveTitkok fs-14" style="width:303px;">Connect Now</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Create New Ads Account Modal -->
            <div class="modal fade" id="conv_create_gads_new" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="staticBackdropLabel">
                                <span id="before_gadsacccreated_title" class="before-ads-acc-creation"><?php esc_html_e("Enable Google Ads Account", "product-feed-manager-for-woocommerce"); ?></span>
                                <span id="after_gadsacccreated_title" class="d-none after-ads-acc-creation"><?php esc_html_e("Account Created", "product-feed-manager-for-woocommerce"); ?></span>
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-start">
                            <span id="before_gadsacccreated_text" class="mb-1 lh-lg fs-6 before-ads-acc-creation">
                                <?php esc_html_e("You’ll receive an invite from Google on your email. Accept the invitation to enable your Google Ads Account.", "product-feed-manager-for-woocommerce"); ?>
                            </span>

                            <div class="onbrdpp-body alert alert-primary text-start d-none after-ads-acc-creation" id="new_google_ads_section">
                                <p>
                                    <?php esc_html_e("Your Google Ads Account has been created", "product-feed-manager-for-woocommerce"); ?>
                                    <strong>
                                        (<b><span id="new_google_ads_id"></span></b>).
                                    </strong>
                                </p>
                                <h6>
                                    <?php esc_html_e("Steps to claim your Google Ads Account:", "product-feed-manager-for-woocommerce"); ?>
                                </h6>
                                <ol>
                                    <li>
                                        <?php esc_html_e("Accept invitation mail from Google Ads sent to your email address", "product-feed-manager-for-woocommerce"); ?>
                                        <em><?php echo (isset($tvc_data['g_mail'])) ? esc_attr($tvc_data['g_mail']) : ""; ?></em>
                                        <span id="invitationLink">
                                            <br>
                                            <em><?php esc_html_e("OR", "product-feed-manager-for-woocommerce"); ?></em>
                                            <?php esc_html_e("Open", "product-feed-manager-for-woocommerce"); ?>
                                            <a href="" target="_blank" id="ads_invitationLink"><?php esc_html_e("Invitation Link", "product-feed-manager-for-woocommerce"); ?></a>
                                        </span>
                                    </li>
                                    <li><?php esc_html_e("Log into your Google Ads account and set up your billing preferences", "product-feed-manager-for-woocommerce"); ?></li>
                                </ol>
                            </div>
                            <div class="error_gads d-none">

                            </div>

                        </div>
                        <div class="modal-footer">
                            <button id="ads-continue" class="btn btn-soft-primary m-auto text-white before-ads-acc-creation">
                                <span id="gadsinviteloader" class="spinner-grow spinner-grow-sm d-none" role="status" aria-hidden="true"></span>
                                <?php esc_html_e("Send Invite", "product-feed-manager-for-woocommerce"); ?>
                            </button>

                            <button id="ads-continue-close" class="btn btn-secondary m-auto text-white d-none after-ads-acc-creation" data-bs-dismiss="modal">
                                <?php esc_html_e("Ok, close", "product-feed-manager-for-woocommerce"); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="conv_modal_popup" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
                aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">            
                        <div class="modal-body text-center p-2 pt-4">
                            <span class="material-symbols-outlined modal_popup_logo" style="font-size: 60px;">
                                check_circle
                            </span>
                            <h3 class="fw-normal pt-3 conv_popup_txt"></h3>
                            <span id="conv_popup_txt_msg" class="mb-1 lh-lg"></span>
                        </div>
                        <div class="modal-footer border-0 pb-4 mb-1">
                            <button class="btn btn-primary m-auto text-white" data-bs-dismiss="modal">Ok, Done</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End -->
            <script>
                var convpfm_ajax_url = '<?php echo esc_js(admin_url( 'admin-ajax.php' )); ?>';
                jQuery(document).ready(function () {    
                    jQuery(document).on('mouseenter', '.not-connected', function() {
                        jQuery(this).addClass('hoverEffect');
                        jQuery(this).find('.status').removeClass('text-danger')
                        jQuery(this).find('.status').addClass('text-primary')                   
                        jQuery(this).find('.status').text('Connect Now')
                    }) 
                    jQuery(document).on('mouseleave', '.not-connected', function() {
                        jQuery(this).removeClass('hoverEffect');
                        jQuery(this).find('.status').removeClass('text-primary')
                        jQuery(this).find('.status').addClass('text-danger')
                        jQuery(this).find('.status').text('Not Connected')
                    }) 

                    jQuery(document).on('mouseenter', '.connected', function() {
                        jQuery(this).addClass('hoverEffect');
                        jQuery(this).find('.status').removeClass('text-danger')
                        jQuery(this).find('.status').addClass('text-primary')                   
                        jQuery(this).find('.status').html('<span class="material-symbols-outlined" style="font-size: 14px;">edit</span>Edit')
                    }) 
                    jQuery(document).on('mouseleave', '.connected', function() {
                        jQuery(this).removeClass('hoverEffect');
                        jQuery(this).find('.status').removeClass('text-primary')
                        jQuery(this).find('.status').addClass('text-success')
                        jQuery(this).find('.status').text('Connected')
                    })
                   
                    var tvc_data = "<?php echo esc_js(wp_json_encode($tvc_data)); ?>";
                    checkChannelConnection()
                    /************All Google Auth call here start **********************/
                    <?php if(isset($_GET['g_mail']) && isset($_GET['subpage']) && $_GET['subpage'] == 'gmcsettings') { ?>
                        jQuery('.getGMCList').addClass('d-none')
                        jQuery('#google_merchant_center_id').attr('disabled', true)
                        jQuery('.saveGMC').prop('disabled', false)
                        jQuery('#GMC-modal').modal('show')
                        jQuery('#google_merchant_center_id').select2({
                            dropdownParent: jQuery("#GMC-modal")
                        })
                        list_google_merchant_account(tvc_data);                        
                    <?php } ?>

                    <?php if(isset($_GET['g_mail']) && isset($_GET['subpage']) && $_GET['subpage'] == 'gadsettings') { ?>
                        jQuery('.getGAdsList').addClass('d-none')                       
                        jQuery('.saveGAds').prop('disabled', false)                        
                        jQuery('#GADS-modal').modal('show')
                        list_google_ads_account(tvc_data);                         
                        jQuery('#google_ads_id').select2({
                            dropdownParent: jQuery("#GADS-modal")
                        })                       
                    <?php } ?>

                    jQuery(document).on('click', '.gmc-card', function() {
                        jQuery('#google_merchant_center_id').val(jQuery('#google_merchant_center_id-value').val()).change()
                        jQuery('#google_merchant_center_id').attr('disabled', true)
                        jQuery('.getGMCList').removeClass('d-none')
                        jQuery('.new-gmc-text').addClass('d-none')
                        jQuery('.saveGMC').prop('disabled', false)
                        jQuery('#GMC-modal').modal('show')
                        jQuery('#google_merchant_center_id').select2({
                            dropdownParent: jQuery("#GMC-modal")
                        })
                        jQuery('#google_merchant_center_id_').select2({
                            dropdownParent: jQuery("#GMC-modal")
                        })
                    })                   

                    // Call GMC list
                    jQuery(document).on('click', '.getGMCList', function() {
                        jQuery('.getGMCList').addClass('d-none')
                        list_google_merchant_account(tvc_data);
                    })

                    jQuery(document).on('click', '.createNewGMC', function() {
                        jQuery("#create_gmc_error").addClass("d-none");
                        jQuery('#create_gmc_error small').text('')
                        jQuery('#gmc_country').select2({
                            dropdownParent: jQuery("#create-gmc-modal")
                        })
                        jQuery('#create-gmc-modal').modal('show')
                    })

                    jQuery(".createGMC").on("click", function () {
                        var is_valide = true;
                        var website_url = jQuery("#gmc_website_url").val();
                        var email_address = jQuery("#gmc_email_address").val();
                        var store_name = jQuery("#gmc_store_name").val();
                        var country = jQuery("#gmc_country").val();
                        var customer_id = '<?php echo esc_js($this->convpfm_api_data['setting']->customer_id); ?>';
                        var adult_content = jQuery("#gmc_adult_content").is(':checked');

                        if (website_url == "") {
                            jQuery("#create_gmc_error").removeClass("d-none");
                            jQuery("#create_gmc_error small").text("Missing value of website url");
                            is_valide = false;
                        } else if (email_address == "") {
                            jQuery("#create_gmc_error").removeClass("d-none");
                            jQuery("#create_gmc_error small").text("Missing value of email address.");
                            is_valide = false;
                        } else if (store_name == "") {
                            jQuery("#create_gmc_error").removeClass("d-none");
                            jQuery("#create_gmc_error small").text("Missing value of store name.");
                            is_valide = false;
                        } else if (country == "") {
                            jQuery("#create_gmc_error").removeClass("d-none");
                            jQuery("#create_gmc_error small").text("Missing value of country.");
                            is_valide = false;
                        } else if (jQuery('#gmc_concent').prop('checked') == false) {
                            jQuery("#create_gmc_error").removeClass("d-none");
                            jQuery("#create_gmc_error small").text("Please accept the terms and conditions.");
                            is_valide = false;
                        }

                        if (is_valide == true) {
                            var data = {
                                action: "convpfm_create_google_merchant_center_account",
                                website_url: website_url,
                                email_address: email_address,
                                store_name: store_name,
                                country: country,
                                concent: 1,
                                adult_content: adult_content,
                                customer_id: customer_id,
                                subscription_id: "<?php echo esc_html($subscriptionId); ?>",
                                conversios_onboarding_nonce: "<?php echo esc_html(wp_create_nonce('conversios_onboarding_nonce')); ?>"
                            };
                            jQuery.ajax({
                                type: "POST",
                                dataType: "json",
                                url: convpfm_ajax_url,
                                data: data,
                                beforeSend: function () {
                                    jQuery('#createGMC').addClass('disabled')
                                    manageAllLoader('create_gmc_modal_loader', 'show')
                                },
                                success: function (response, status) {
                                    jQuery('#createGMC').removeClass('disabled');
                                    manageAllLoader('create_gmc_modal_loader', 'hide')
                                    if(response.error === true) {
                                        var error_msg = 'Merchant center account is not getting created.';
                                        jQuery("#create_gmc_error").removeClass("d-none")
                                    } else if(response.account.id) {
                                        jQuery('.new-gmc-text').removeClass('d-none')
                                        jQuery('.new-gmc-id').text(response.account.id)
                                        list_google_merchant_account(tvc_data, response.account.id, response.merchant_id);
                                        jQuery('#create-gmc-modal').modal('hide')
                                    } 
                                }
                            });
                        }
                    });
                    // gOOGLE AUTH
                    jQuery(".tvc_google_signinbtn").on("click", function() {
                        const w = 600;
                        const h = 650;
                        const dualScreenLeft = window.screenLeft !== undefined ? window.screenLeft : window.screenX;
                        const dualScreenTop = window.screenTop !== undefined ? window.screenTop : window.screenY;

                        const width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
                        const height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

                        const systemZoom = width / window.screen.availWidth;
                        const left = (width - w) / 2 / systemZoom + dualScreenLeft;
                        const top = (height - h) / 2 / systemZoom + dualScreenTop;
                        var url = '<?php echo esc_url_raw($google_connect_url); ?>';
                        url = url.replace(/&amp;/g, '&');
                        const newWindow = window.open(url, "newwindow", config = `scrollbars=yes,
                            width=${w / systemZoom}, 
                            height=${h / systemZoom}, 
                            top=${top}, 
                            left=${left},toolbar=no,menubar=no,scrollbars=no,resizable=no,location=no,directories=no,status=no
                            `);
                        if (window.focus) newWindow.focus();
                    });

                    jQuery(document).on('click', '.saveGMC', function() {
                        saveChannel('GMC')
                    });

                    jQuery(document).on('click', '.gads-card', function() {
                        jQuery('#google_ads_id').select2({
                            dropdownParent: jQuery("#GADS-modal")
                        })
                        jQuery('#GADS-modal').modal('show')
                    })

                    jQuery(document).on('click', '.getGAdsList', function() {
                        jQuery('.getGAdsList').addClass('d-none')
                        list_google_ads_account(tvc_data); 
                    })

                    jQuery(document).on('click', '.saveGAds', function() {
                        saveChannel('gAds')
                    });

                    jQuery(document).on('click', '.createNewGAds', function() {
                        jQuery(".before-ads-acc-creation").removeClass("d-none");
                        jQuery(".after-ads-acc-creation").addClass("d-none");
                        jQuery(".error_gads").addClass('d-none');
                        jQuery(".error_gads").html('');
                        jQuery('#conv_create_gads_new').modal('show')
                    })
                    /************All Google Auth call here end **********************/
                    /************All Facebook auth Call here Start ******************/
                    jQuery(document).on('click', '.fb-card', function() {
                        jQuery('#fb_business_id').attr('disabled', true)
                        jQuery('#fb_catalog_id').attr('disabled', true)
                        jQuery('#fb_business_id').val(jQuery('#facebook_business_account-value').val()).trigger('change.select2', {trigger: false})
                        jQuery('#fb_catalog_id').val(jQuery('#facebook_catalog_id-value').val()).trigger('change.select2', {trigger: false})
                        jQuery('#google_merchant_center_id').attr('disabled', true)
                        jQuery('.getFBList').removeClass('d-none')
                        jQuery('.saveFb').prop('disabled', false)
                        jQuery('#FB-modal').modal('show')
                        jQuery('#fb_business_id').select2({
                            dropdownParent: jQuery("#FB-modal")
                        })
                        jQuery('#fb_catalog_id').select2({
                            dropdownParent: jQuery("#FB-modal")
                        })
                    })

                    jQuery(document).on('click', '.getFBList', function() {
                        jQuery('#fb_business_id').removeAttr('disabled')
                        jQuery('#fb_catalog_id').removeAttr('disabled')
                        jQuery('.getFBList').addClass('d-none')
                    })

                    <?php if(isset($_GET['g_mail']) && isset($_GET['subpage']) && $_GET['subpage'] == 'metasettings') { ?>
                        jQuery('.getFBList').addClass('d-none')
                        jQuery('#fb_business_id').removeAttr('disabled')
                        jQuery('#fb_catalog_id').removeAttr('disabled')
                        jQuery('#FB-modal').modal('show')
                        jQuery('#fb_business_id').select2({
                            dropdownParent: jQuery("#FB-modal")
                        })
                        jQuery('#fb_catalog_id').select2({
                            dropdownParent: jQuery("#FB-modal")
                        })               
                    <?php } ?>

                    jQuery(document).on('change', '#fb_business_id', function() {
                        get_fb_catalog_data()
                    })

                    jQuery(document).on('click', '.saveFB', function() {
                        saveChannel('FB')
                    });

                    // Gads AUTH
                    jQuery(".tvc_google_signinbtn_ga").on("click", function() {
                        const w = 600;
                        const h = 650;
                        const dualScreenLeft = window.screenLeft !== undefined ? window.screenLeft : window.screenX;
                        const dualScreenTop = window.screenTop !== undefined ? window.screenTop : window.screenY;

                        const width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
                        const height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

                        const systemZoom = width / window.screen.availWidth;
                        const left = (width - w) / 2 / systemZoom + dualScreenLeft;
                        const top = (height - h) / 2 / systemZoom + dualScreenTop;
                        var url = '<?php echo esc_url_raw($gads_connect_url); ?>';
                        url = url.replace(/&amp;/g, '&');
                        const newWindow = window.open(url, "newwindow", config = `scrollbars=yes,
                            width=${w / systemZoom}, 
                            height=${h / systemZoom}, 
                            top=${top}, 
                            left=${left},toolbar=no,menubar=no,scrollbars=no,resizable=no,location=no,directories=no,status=no
                            `);
                        if (window.focus) newWindow.focus();
                    });
                    /************All Facebook auth Call here End ********************/
                    /************All Tiktok auth Call here Start ********************/
                    jQuery(document).on('click', '.tiktok-card', function() {
                        var tiktok_business_account_value = jQuery('#tiktok_business_account-value').val()
                        jQuery('.gettiktokList').removeClass('d-none')
                        jQuery('#tiktok_business_id').attr('disabled', true)                        
                        if(tiktok_business_account_value != '') {
                            jQuery('#tiktok_business_id').val(tiktok_business_account_value).change()
                        }                        
                        jQuery('#TITOK-modal').modal('show')
                        jQuery('#tiktok_business_id').select2({
                            dropdownParent: jQuery("#TITOK-modal")
                        }) 
                    })
                    <?php if(isset($_GET['tiktok_mail']) && isset($_GET['subpage']) && $_GET['subpage'] == 'tiktoksettings') { ?>
                        var tiktok_business_account_value = jQuery('#tiktok_business_account-value').val()
                        if(tiktok_business_account_value != '') {
                            get_tiktok_user_catalogs()
                        }   
                        jQuery('#tiktok_business_id').attr('disabled', false)
                        jQuery('.gettiktokList').addClass('d-none')                   
                        jQuery('#TITOK-modal').modal('show')
                        jQuery('#tiktok_business_id').select2({
                            dropdownParent: jQuery("#TITOK-modal")
                        })                       
                    <?php } ?>

                    jQuery(document).on('change', '#tiktok_business_id', function() {
                        get_tiktok_user_catalogs()
                    })

                    jQuery(document).on('click', '.saveTitkok', function() {
                        saveChannel('Tiktok')
                    })
                    jQuery(document).on('click', '.gettiktokList', function() {
                        jQuery('#tiktok_business_id').attr('disabled', false)
                        jQuery('.gettiktokList').addClass('d-none')
                    })
                    /************All Tiktok auth Call here End **********************/
                    jQuery("#ads-continue").on('click', function(e) {
                        e.preventDefault();
                        create_google_ads_account(tvc_data);
                        jQuery('.ggladspp').removeClass('showpopup');
                    });
                    jQuery(document).on('click', '.verifySite', function() {
                        call_site_verified()
                    })
                    jQuery(document).on('click', '.verifyDomain', function() {
                        call_domain_claim()
                    })
                });
                function call_site_verified() {
                    jQuery("#wpbody").css("pointer-events", "none");
                    manageAllLoader('gmc_modal_loader', 'show')
                    jQuery.post(convpfm_ajax_url, {
                        action: "convpfm_tvc_call_site_verified",
                        SiteVerifiedNonce: "<?php echo wp_create_nonce('tvc_call_site_verified-nonce'); ?>"
                    }, function (response) {
                        manageAllLoader('gmc_modal_loader', 'hide')
                        jQuery("#wpbody").css("pointer-events", "auto");
                        var rsp = JSON.parse(response);
                        if (rsp.status == "success") {
                            var html ='Verified &nbsp;<span class="material-symbols-outlined mx-auto" style="font-size: 18px;">verified</span>';
                            jQuery('.verifySite').removeClass('bg-warnings')
                            jQuery('.verifySite').addClass('bg-success_')
                            jQuery('.verifySite').html(html)
                            jQuery(".modal_popup_logo").html('check_circle')
                            jQuery('.modal_popup_logo').removeClass('modal_popup_logo_error')
                            jQuery('.modal_popup_logo').addClass('modal_popup_logo_success')
                            jQuery('.conv_popup_txt').text('Congratulations')
                            jQuery('#conv_popup_txt_msg').text('Site is verified')
                            jQuery('#conv_modal_popup').modal('show')
                        } else {           
                            jQuery(".modal_popup_logo").html('cancel')      
                            jQuery('.modal_popup_logo').removeClass('modal_popup_logo_success')
                            jQuery('.modal_popup_logo').addClass('modal_popup_logo_error')
                            jQuery('.conv_popup_txt').text('Error')
                            jQuery('#conv_popup_txt_msg').text(rsp.message)
                            jQuery('#conv_modal_popup').modal('show')  
                        }
                    });
                }
                function call_domain_claim() {
                    jQuery("#wpbody").css("pointer-events", "none");
                    manageAllLoader('gmc_modal_loader', 'show')
                    jQuery.post(convpfm_ajax_url, {
                        action: "convpfm_tvc_call_domain_claim",
                        apiDomainClaimNonce: "<?php echo wp_create_nonce('tvc_call_domain_claim-nonce'); ?>"
                    }, function (response) {
                        manageAllLoader('gmc_modal_loader', 'hide')
                        jQuery("#wpbody").css("pointer-events", "auto");
                        var rsp = JSON.parse(response);
                        if (rsp.status == "success") {                
                            var html ='<span class="material-symbols-outlined" style="font-size: 18px;">verified</span>Verified';
                            jQuery('.verifyDomain').removeClass('bg-warnings')
                            jQuery('.verifyDomain').addClass('bg-success_')
                            jQuery('.verifyDomain').html(html)
                            jQuery(".modal_popup_logo").html('check_circle')
                            jQuery('.modal_popup_logo').removeClass('modal_popup_logo_error')
                            jQuery('.modal_popup_logo').addClass('modal_popup_logo_success')
                            jQuery('.conv_popup_txt').text('Congratulations')
                            jQuery('#conv_popup_txt_msg').text('Domain claim successfull')
                            jQuery('#conv_modal_popup').modal('show')
                        } else {
                            jQuery(".modal_popup_logo").html('cancel')
                            jQuery('.modal_popup_logo').removeClass('modal_popup_logo_success')
                            jQuery('.modal_popup_logo').addClass('modal_popup_logo_error')
                            jQuery('.conv_popup_txt').text('Error')
                            jQuery('#conv_popup_txt_msg').text(rsp.message)
                            jQuery('#conv_modal_popup').modal('show')                
                        }
                    });
                }
                function saveChannel(Channel) {
                    var selected_vals = {};
                    var conv_options_type = [];
                    var data = {};
                    if(Channel == 'GMC') {
                        var google_merchant_center_id = "<?php echo $google_merchant_center_id ?>";
                        if (jQuery("#google_merchant_center_id").val() === '') {
                            jQuery('.selection').find("[aria-labelledby='select2-google_merchant_center_id-container']").addClass('selectError');
                            return false;
                        }
                        var update_site_domain = '';
                        if(google_merchant_center_id != jQuery("#google_merchant_center_id").val()) {
                            update_site_domain = 'update';
                        }
                        conv_options_type = ["eeoptions", "eeapidata", "middleware"];
                        selected_vals["subscription_id"] = "<?php echo $subscriptionId ?>";
                        selected_vals["google_merchant_center_id"] = jQuery("#google_merchant_center_id").find(":selected").val();
                        selected_vals["google_merchant_id"] = jQuery("#google_merchant_center_id").find(":selected").val();
                        selected_vals["merchant_id"] = jQuery('#google_merchant_center_id').find(':selected').data('merchant_id');
                        selected_vals["website_url"] = "<?php echo get_site_url(); ?>";
                        var google_ads_id = jQuery('#google_ads_id-value').val();
                        if(google_ads_id !== '' && google_ads_id !== undefined){
                            selected_vals["google_ads_id"] = google_ads_id;
                            selected_vals["ga_GMC"] = '1';
                        }
                        data = {
                            action: "convpfm_save_pixel_data", 
                            pix_sav_nonce: "<?php echo wp_create_nonce('pix_sav_nonce_val'); ?>",
                            conv_options_data: selected_vals,
                            conv_options_type: conv_options_type,
                            update_site_domain: update_site_domain,
                        }
                    }

                    if(Channel == 'FB') {
                        var facebook_data = {};
                        facebook_data["fb_mail"] = jQuery('#fb_mail').val();
                        facebook_data["fb_business_id"] = jQuery('#fb_business_id').find(":selected").val();
                        facebook_data["fb_catalog_id"] = jQuery('#fb_catalog_id').find(":selected").val();
                        selected_vals["facebook_setting"] = facebook_data;
                        if (facebook_data["fb_business_id"] === '') {
                            jQuery('.selection').find("[aria-labelledby='select2-fb_business_id-container']").addClass('selectError');
                            return false;
                        }
                        if (facebook_data["fb_catalog_id"] === '') {
                            jQuery('.selection').find("[aria-labelledby='select2-fb_catalog_id-container']").addClass('selectError');
                            return false;
                        }
                        conv_options_type = ["eeoptions", "middleware", "facebookmiddleware", "facebookcatalog"];
                        data = {
                            action: "convpfm_save_pixel_data", 
                            pix_sav_nonce: "<?php echo wp_create_nonce('pix_sav_nonce_val'); ?>",
                            conv_options_data: selected_vals,
                            conv_options_type: conv_options_type,
                            customer_subscription_id: "<?php echo esc_html($subscriptionId) ?>",
                        }
                    }

                    if(Channel == 'gAds') {
                        var google_merchant_center_id = jQuery("#google_merchant_center_id-value").val();
                        var google_ads_id = jQuery("#google_ads_id").find(":selected").val();
                        selected_vals["ga_GMC"] = '0';
                        if ( google_ads_id === '' ) {
                            jQuery('.selection').find("[aria-labelledby='select2-google_ads_id-container']").addClass('selectError');
                            return false;
                        }
                        if (!jQuery('#ga_GMC').is(":checked")) {
                            jQuery('#ga_GMC').css('border', '1px solid red');
                            return false;
                        }
                        
                        if( google_merchant_center_id == "" ) {
                            if(jQuery('#ga_GMC').is(":checked")) {
                                jQuery('.errorGMC_GAds').text('Google merchant account is required to link Google Ads')
                                jQuery('#ga_GMC').prop("checked", false)
                                return false;
                            }
                        }
                        if( google_merchant_center_id !== "" ) {
                            selected_vals["subscription_id"] = "<?php echo $subscriptionId ?>";
                            selected_vals["google_merchant_center_id"] = google_merchant_center_id;
                            selected_vals["google_merchant_id"] = google_merchant_center_id;
                            selected_vals["merchant_id"] = jQuery('#google_merchant_center_id').find(':selected').data('merchant_id');
                            selected_vals["website_url"] = "<?php echo get_site_url(); ?>";
                            if(jQuery('#ga_GMC').is(":checked")) {
                                selected_vals["ga_GMC"] = '1';
                            }
                        }
                        selected_vals["google_ads_id"] = google_ads_id;
                        conv_options_type = ["eeoptions", "eeapidata", "middleware"];
                        data = {
                            action: "convpfm_save_pixel_data", 
                            pix_sav_nonce: "<?php echo wp_create_nonce('pix_sav_nonce_val'); ?>",
                            conv_options_data: selected_vals,
                            conv_options_type: conv_options_type,
                        }                        
                    }

                    if(Channel == 'Tiktok') {
                        var tiktok_data = {};
                        tiktok_data["tiktok_mail"] = jQuery('#tiktok_mail').val();
                        tiktok_data["tiktok_user_id"] = jQuery('#tiktok_user_id').val();
                        tiktok_data["tiktok_business_id"] = jQuery('#tiktok_business_id').find(":selected").val();
                        tiktok_data["tiktok_business_name"] = jQuery('#tiktok_business_id').find(":selected").data("business_name")
                        selected_vals["tiktok_setting"] = tiktok_data;
                        if (tiktok_data["tiktok_business_id"] === '') {
                            jQuery('.selection').find("[aria-labelledby='select2-tiktok_business_id-container']").addClass('selectError');
                            return false;
                        }
                        var catalogData = {};
                        jQuery('.catalogId').each(function () {
                            catalogData[jQuery(this).find(":selected").data("catalog_country")] = [jQuery(this).find(":selected").val(), jQuery(this).find(":selected").data("catalog_name"), jQuery(this).find(":selected").data("catalog_country")];
                        })
                        conv_options_type = ["eeoptions", "middleware", "tiktokmiddleware", "tiktokcatalog"];
                        data = {
                            action: "convpfm_save_pixel_data", 
                            pix_sav_nonce: "<?php echo wp_create_nonce('pix_sav_nonce_val'); ?>",
                            conv_options_data: selected_vals,
                            conv_options_type: conv_options_type,
                            customer_subscription_id: "<?php echo $subscriptionId ?>",
                            conv_catalogData: catalogData,
                        }
                    }
                    
                    jQuery.ajax({
                        type: "POST",
                        dataType: "json",
                        url: convpfm_ajax_url,
                        data: data,
                        beforeSend: function () { 
                            if(Channel == 'GMC'){
                                manageAllLoader('gmc_modal_loader', 'show')
                                jQuery(".verifySite, .verifyDomain, .createNewGMC, .saveGMC, .tvc_google_signinbtn").css("pointer-events", "none")
                                jQuery('.saveGMC').prop('disabled', true)
                                jQuery('#google_merchant_center_id-value').val(jQuery("#google_merchant_center_id").find(":selected").val())   
                                jQuery('.account-message').text('')
                                jQuery('.account-id').text('')           
                            } 
                            if(Channel == 'FB'){
                                manageAllLoader('fb_modal_loader', 'show')
                                jQuery(".saveFB, .tvc_fb_signinbtn").css("pointer-events", "none");
                                jQuery('.saveFB').prop('disabled', true)
                                jQuery('#facebook_business_account-value').val(jQuery('#fb_business_id').find(":selected").val()) 
                                jQuery('#facebook_catalog_id-value').val(jQuery('#fb_catalog_id').find(":selected").val())   
                                jQuery('.account-message').text('')
                                jQuery('.account-id').text('')           
                            }
                            if(Channel == 'gAds'){
                                manageAllLoader('gads_modal_loader', 'show')
                                jQuery(".saveGAds, .tvc_google_signinbtn_ga").css("pointer-events", "none");
                                jQuery('.saveGAds').prop('disabled', true)
                                jQuery('#google_ads_id-value').val(jQuery('#google_ads_id').find(":selected").val()) 
                                jQuery('.account-message').text('')
                                jQuery('.account-id').text('')           
                            }  
                            if(Channel == 'Tiktok'){
                                manageAllLoader('tiktok_modal_loader', 'show')
                                jQuery(".tvc_tiktok_signinbtn, #tiktok_business_id, .saveTitkok").css("pointer-events", "none")
                                jQuery('.saveTiktok').prop('disabled', true)
                                jQuery('#tiktok_business_account-value').val(jQuery('#tiktok_business_id').find(":selected").val())
                                jQuery('.account-message').text('')
                                jQuery('.account-id').text('')                           
                            }                    
                        },
                        success: function (response) {
                            if(Channel == 'GMC'){
                                jQuery(".verifySite, .verifyDomain, .createNewGMC, .saveGMC, .tvc_google_signinbtn").css("pointer-events", "auto");                               
                                jQuery('#google_merchant_center_id').attr('disabled', true)              
                                jQuery('.getGMCList').removeClass('d-none')  
                                jQuery('.saveGMC').prop('disabled', false)
                                checkChannelConnection()
                                manageAllLoader('gmc_modal_loader', 'hide')                                
                                jQuery('.account-message').text('You have successfully connected your Google Merchant Center with')
                                jQuery('.account-id').text('Account ID: '+jQuery("#google_merchant_center_id").val())
                                jQuery('#GMC-modal').modal('hide')
                                jQuery('#auth-success-modal').modal('show')                            
                            } 
                            if(Channel == 'FB'){
                                jQuery(".saveFB, .tvc_fb_signinbtn").css("pointer-events", "auto");  
                                jQuery('.saveFB').attr('disabled', false)                         
                                jQuery('#fb_business_id').attr('disabled', true)  
                                jQuery('#fb_catalog_id').attr('disabled', true)            
                                jQuery('.getFBList').removeClass('d-none')  
                                checkChannelConnection()
                                manageAllLoader('fb_modal_loader', 'hide')                                
                                jQuery('.account-message').text('You have successfully connected your Facebook with')
                                jQuery('.account-id').text('Account ID: '+jQuery("#fb_catalog_id").val())
                                jQuery('#FB-modal').modal('hide')
                                jQuery('#auth-success-modal').modal('show')                            
                            } 
                            if(Channel == 'gAds'){                                
                                jQuery(".saveGAds, .tvc_google_signinbtn_ga").css("pointer-events", "auto");
                                jQuery('.saveGAds').prop('disabled', false) 
                                jQuery("#google_ads_id").attr('disabled', true)
                                jQuery('.getGAdsList').removeClass('d-none')
                                checkChannelConnection()
                                manageAllLoader('gads_modal_loader', 'hide')
                                jQuery('.account-message').text('You have successfully connected your Google Ads with')
                                jQuery('.account-id').text('Account ID: '+jQuery("#google_ads_id").val())  
                                jQuery('#GADS-modal').modal('hide')
                                jQuery('#auth-success-modal').modal('show')        
                            }
                            if(Channel == 'Tiktok'){
                                jQuery(".tvc_tiktok_signinbtn, #tiktok_business_id, .saveTitkok").css("pointer-events", "auto");
                                jQuery('.saveTiktok').prop('disabled', false)
                                jQuery('#tiktok_business_id').attr('disabled', true) 
                                jQuery('.gettitokList').removeClass('d-none')
                                checkChannelConnection()
                                manageAllLoader('tiktok_modal_loader', 'hide')
                                jQuery('.account-message').text('You have successfully connected your TikTok with')
                                jQuery('.account-id').text('Account ID: '+jQuery('#tiktok_business_id').find(":selected").val())  
                                jQuery('#TITOK-modal').modal('hide')
                                jQuery('#auth-success-modal').modal('show') 
                            }                        
                        }
                    });
                }
                function checkChannelConnection() {
                    let google_merchant_center_id = jQuery('#google_merchant_center_id-value').val();                    
                    let facebook_business_account = jQuery('#facebook_business_account-value').val();
                    let facebook_catalog_id = jQuery('#facebook_catalog_id-value').val();
                    let tiktok_business_account = jQuery('#tiktok_business_account-value').val();
                    let google_ads_id = jQuery('#google_ads_id-value').val();

                    if(google_merchant_center_id) {
                        jQuery('.gmc-card').addClass('connected')
                        jQuery('.gmc-card').removeClass('not-connected')
                        jQuery('.gmcId').text('GMC - '+google_merchant_center_id)
                        jQuery('.gmc-status').text('Connected')
                        jQuery('.gmc-status').addClass('text-success')
                    } else {
                        jQuery('.gmc-card').removeClass('connected')
                        jQuery('.gmc-card').addClass('not-connected')
                        jQuery('.gmcId').text('GMC - ')
                        jQuery('.gmc-status').text('Not Connected')
                        jQuery('.gmc-status').addClass('text-danger')
                    }
                    if(facebook_catalog_id) {
                        jQuery('.fb-card').addClass('connected')
                        jQuery('.fb-card').removeClass('not-connected')
                        jQuery('.fbId').text('FB Account - '+facebook_catalog_id)
                        jQuery('.fb-status').text('Connected')
                        jQuery('.fb-status').addClass('text-success')
                    } else {
                        jQuery('.fb-card').removeClass('connected')
                        jQuery('.fb-card').addClass('not-connected')
                        jQuery('.fbId').text('FB Account - ')
                        jQuery('.fb-status').text('Not Connected')
                        jQuery('.fb-status').addClass('text-danger')
                    }
                    if(tiktok_business_account) {
                        jQuery('.tiktok-card').addClass('connected')
                        jQuery('.tiktok-card').removeClass('not-connected')
                        jQuery('.tiktokId').text('TikTok Account - '+tiktok_business_account)
                        jQuery('.tiktok-status').text('Connected')
                        jQuery('.tiktok-status').addClass('text-success')
                    } else {
                        jQuery('.tiktok-card').removeClass('connected')
                        jQuery('.tiktok-card').addClass('not-connected')
                        jQuery('.tiktokId').text('Tiktok Account - ')
                        jQuery('.tiktok-status').text('Not Connected')
                        jQuery('.tiktok-status').addClass('text-danger')
                    }
                    if(google_ads_id) {
                        jQuery('.gads-card').addClass('connected')
                        jQuery('.gads-card').removeClass('not-connected')
                        jQuery('.gadsId').text('GAds Account - '+google_ads_id)
                        jQuery('.gads-status').text('Connected')
                        jQuery('.gads-status').addClass('text-success')
                    } else {
                        jQuery('.gads-card').removeClass('connected')
                        jQuery('.gads-card').addClass('not-connected')
                        jQuery('.gadsId').text('GAds Account - ')
                        jQuery('.gads-status').text('Not Connected')
                        jQuery('.gads-status').addClass('text-danger')
                    }
                }
                function list_google_merchant_account(tvc_data, new_gmc_id = "", new_merchant_id = "") {
                    let google_merchant_center_id = jQuery('#google_merchant_center_id').val();
                    var conversios_onboarding_nonce = "<?php echo wp_create_nonce('conversios_onboarding_nonce'); ?>";        
                    jQuery.ajax({
                        type: "POST",
                        dataType: "json",
                        url: convpfm_ajax_url,
                        data: {
                            action: "convpfm_list_google_merchant_account",
                            tvc_data: tvc_data,
                            conversios_onboarding_nonce: conversios_onboarding_nonce
                        },
                        beforeSend: function(){ 
                            manageAllLoader('gmc_modal_loader', 'show')
                        },
                        success: function (response) {                            
                            jQuery('#google_merchant_center_id').removeAttr('disabled')
                            if (response.error === false) {
                                var error_msg = 'null';                    
                                jQuery('#google_merchant_center_id').empty();
                                jQuery('#google_merchant_center_id').append(jQuery('<option>', {
                                    value: "",
                                    text: "Select Google Merchant Center Account"
                                }));
                                if (response.data.length > 0) {
                                    jQuery.each(response.data, function (key, value) {
                                        jQuery('#google_merchant_center_id').append(jQuery('<option>', {
                                            value: value.account_id,
                                            "data-merchant_id": value.merchant_id,
                                            text: value.account_id,
                                            selected: (value.account_id === google_merchant_center_id)
                                        }));
                                    });

                                    if (new_gmc_id != "" && new_gmc_id != undefined) {
                                        jQuery('#google_merchant_center_id').append(jQuery('<option>', {
                                            value: new_gmc_id,
                                            "data-merchant_id": new_merchant_id,
                                            text: new_gmc_id,
                                            selected: "selected"
                                        }));
                                        jQuery('.getGMCList').addClass('d-none')
                                        jQuery('.saveGMC').prop('disabled', false)
                                    }
                                } else {
                                    if (new_gmc_id != "" && new_gmc_id != undefined) {
                                        jQuery('#google_merchant_center_id').append(jQuery('<option>', {
                                            value: new_gmc_id,
                                            "data-merchant_id": new_merchant_id,
                                            text: new_gmc_id,
                                            selected: "selected"
                                        }));
                                        jQuery('.getGMCList').addClass('d-none')
                                        jQuery('.saveGMC').prop('disabled', false)
                                    }                        
                                    console.log("error", "There are no Google merchant center accounts associated with email.");
                                }
                                manageAllLoader('gmc_modal_loader', 'hide')
                            } else {
                                var error_msg = response.errors;
                                console.log("error", "There are no Google merchant center  accounts associated with email.");
                                manageAllLoader('gmc_modal_loader', 'hide')
                            }
                        }
                    });
                }
                function get_fb_catalog_data() {        
                    var fb_business = jQuery('#fb_business_id').find(":selected").val();
                    if(fb_business != ''){
                        var data = {
                            action: "convpfm_get_fb_catalog_data",
                            customer_subscription_id: <?php echo esc_html($subscriptionId) ?>,
                            fb_business_id: fb_business,
                            fb_business_nonce: "<?php echo wp_create_nonce('fb_business_nonce'); ?>"
                        }
                        jQuery.ajax({
                            type: "POST",
                            dataType: "json",
                            url: convpfm_ajax_url,
                            data: data,
                            beforeSend: function(){ 
                                manageAllLoader('fb_modal_loader', 'show')
                                jQuery("#fb_catalog_id").attr("disabled", true);
                            },
                            success: function(response){ 
                                var cat_id = jQuery('#facebook_catalog_id-value').val();                      
                                var html = '<option value="">Select Catalog Id</option>';
                                jQuery.each(response, function(index, value){
                                    var selected = (value.id == cat_id ) ? 'selected' : '';                        
                                    html +='<option value="'+value.id+'" '+selected+'>'+value.id+'-'+value.name+'</option>';
                                });
                                jQuery('#fb_catalog_id').html(html);
                                manageAllLoader('fb_modal_loader', 'hide')  
                                jQuery("#fb_catalog_id").attr("disabled", false);            
                            }
                        });
                    } else {
                        var html = '<option value="">Select Catalog Id</option>';
                        jQuery('#fb_catalog_id').html(html);
                        manageAllLoader('fb_modal_loader', 'hide')
                    }
                }

                function list_google_ads_account(tvc_data, new_ads_id = "") {   
                    var gads = jQuery('#google_ads_id-value').val(); 
                    manageAllLoader('gads_modal_loader', 'show')   
                    var selectedValue = jQuery("#google_ads_id").val();
                    var conversios_onboarding_nonce = "<?php echo wp_create_nonce('conversios_onboarding_nonce'); ?>";
                    jQuery.ajax({
                        type: "POST",
                        dataType: "json",
                        url: convpfm_ajax_url,
                        data: {
                            action: "convpfm_list_googl_ads_account",
                            tvc_data: tvc_data,
                            conversios_onboarding_nonce: conversios_onboarding_nonce
                        },
                        success: function(response) {
                            manageAllLoader('gads_modal_loader', 'hide') 
                            if (response.error === false) {
                                var error_msg = 'null';
                                if (response.data.length == 0) {
                                    add_message("warning", "There are no Google ads accounts associated with email.");
                                } else {
                                    if (response.data.length > 0) {
                                        jQuery('#google_ads_id').empty();
                                        jQuery('#google_ads_id').append(jQuery('<option>', {
                                            value: "",
                                            text: "Select Google Ads Id"
                                        }));
                                        
                                        if (new_ads_id != "" && new_ads_id != undefined) {
                                            jQuery('#google_ads_id').append(jQuery('<option>', {
                                                value: new_ads_id,
                                                text: new_ads_id,
                                                selected: (new_ads_id === new_ads_id)
                                            }));
                                        }
                                        
                                        jQuery.each(response.data, function (key, value) {
                                            jQuery('#google_ads_id').append(jQuery('<option>', {
                                                value: value,
                                                text: value,
                                                selected: (value === gads)
                                            }));
                                        });  
                                        jQuery('#google_ads_id').attr('disabled', false)                                   
                                    }
                                }
                            } else {
                                var error_msg = response.errors;
                            }
                        }
                    });
                }

                function get_tiktok_user_catalogs() {
                    var catalogCountry = <?php echo json_encode($catalogCountry) ?>;
                    var catalog_business_id = <?php echo json_encode($catalog_business_id) ?>;
                    var conversios_onboarding_nonce = "<?php echo wp_create_nonce('conversios_onboarding_nonce'); ?>";
                    var business_id = jQuery('#tiktok_business_id').find(":selected").val();
                    jQuery('.selection').find("[aria-labelledby='select2-tiktok_business_id-container']").removeClass('selectError');
                    jQuery('#table-body').empty();
                    jQuery.ajax({
                        type: "POST",
                        dataType: "json",
                        url: convpfm_ajax_url,
                        data: {
                            action: "convpfm_get_tiktok_user_catalogs",
                            customer_subscription_id: "<?php echo $subscriptionId ?>",
                            business_id: business_id,
                            conversios_onboarding_nonce: conversios_onboarding_nonce
                        },
                        beforeSend: function(){ 
                            manageAllLoader('tiktok_modal_loader', 'show')
                        },
                        success: function (response) {
                            if (response.error === false) {
                                if (response.data) {                        
                                    var tableBody = '';
                                    jQuery.each(response.data, function (key, value) {                            
                                        tableBody += '<tr>';
                                        tableBody += '<td class="align-middle text-start fw-400 fs-14">' + key + '</td>';
                                        tableBody += '<td class="align-middle text-start"><select id="" name="catalogId[]" class="form-select form-select-lg mb-3 catalogId" style="width: 100%" >';
                                        jQuery.each(value, function (valKey, ValValue) {
                                            var selected = "";
                                            if (jQuery.inArray(valKey, catalog_business_id) !== -1 && catalog_business_id.length > 0) {
                                                var selected = 'selected="selected"';
                                            }
                                            tableBody += '<option value="' + valKey + '"  data-catalog_country="' + key + '" data-catalog_name="' + ValValue + '" ' + selected + '>' + valKey + ' - ' + ValValue + '</option>';
                                        })
                                        tableBody += '</select></td></tr>';
                                    });
                                    jQuery('#table-body').html(tableBody); 
                                    jQuery('.catalogId').select2({
                                        dropdownParent: jQuery("#TITOK-modal"),
                                        dropdownCssClass: "fs-12"
                                    })                               
                                }
                            }
                            manageAllLoader('tiktok_modal_loader', 'hide')
                        }
                    })
                }
                
                // GMC loader
                function manageAllLoader(id, display = "show") {
                    if(display == "show") {
                        jQuery('#'+id).removeClass('d-none')
                        jQuery(".verifySite, .verifyDomain, .createNewGMC, .saveGMC").css("pointer-events", "none");
                    } else {
                        jQuery(".verifySite, .verifyDomain, .createNewGMC, .saveGMC").css("pointer-events", "auto");
                        jQuery('#'+id).addClass('d-none')
                    }
                }

                function create_google_ads_account(tvc_data) {
                    var conversios_onboarding_nonce = "<?php echo esc_js(wp_create_nonce('conversios_onboarding_nonce')); ?>";
                    var error_msg = 'null';
                    var btn_cam = 'create_new';
                    var ename = 'conversios_onboarding';
                    var event_label = 'ads';
                    //user_tracking_data(btn_cam, error_msg,ename,event_label);   
                    jQuery.ajax({
                        type: "POST",
                        dataType: "json",
                        url: convpfm_ajax_url,
                        data: {
                            action: "convpfm_create_google_ads_account",
                            tvc_data: tvc_data,
                            conversios_onboarding_nonce: conversios_onboarding_nonce
                        },
                        beforeSend: function() {
                            jQuery("#gadsinviteloader").removeClass('d-none');
                            jQuery("#ads-continue").addClass('disabled');
                        },
                        success: function(response) {
                            if (response.error == false) {
                                error_msg = 'null';
                                var btn_cam = 'complate_new';
                                var ename = 'conversios_onboarding';
                                var event_label = 'ads';

                                //add_message("success", response.data.message);
                                jQuery("#new_google_ads_id").text(response.data.adwords_id);
                                if (response.data.invitationLink != "") {
                                    jQuery("#ads_invitationLink").attr("href", response.data.invitationLink);
                                } else {
                                    jQuery("#invitationLink").html("");
                                }
                                jQuery(".before-ads-acc-creation").addClass("d-none");
                                jQuery(".after-ads-acc-creation").removeClass("d-none");
                                //localStorage.setItem("new_google_ads_id", response.data.adwords_id);
                                var tvc_data = "<?php echo esc_js(wp_json_encode($tvc_data)); ?>";
                                list_google_ads_account(tvc_data, response.data.adwords_id);

                            } else {
                                jQuery(".error_gads").removeClass('d-none');
                                jQuery(".error_gads").html('<p>Error in GAds account creation. Re-Auth Google Sign In.</p>')
                                jQuery("#gadsinviteloader").addClass('d-none');
                                jQuery("#ads-continue").removeClass('disabled');
                            }
                            //user_tracking_data(btn_cam, error_msg,ename,event_label);   
                        }
                    });
                }

            </script>
            <?php
        }
    }
}
new Convpfm_Dashboard();
