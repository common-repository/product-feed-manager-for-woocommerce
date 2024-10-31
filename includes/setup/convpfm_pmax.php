<?php
class Convpfm_PMax
{
  protected $Convpfm_TVC_Admin_Helper;
  protected $PMax_Helper;
  protected $subscription_id;
  protected $google_detail;
  protected $site_url;
  protected $google_ads_id;
  protected $currency_code;
  protected $subscription_data;
  protected $currency_symbol;
  protected $google_merchant_id;
  protected $store_id;
  public function __construct()
  {
    $this->includes();
    $this->Convpfm_TVC_Admin_Helper = new Convpfm_TVC_Admin_Helper();
    $this->PMax_Helper = new Convpfm_PMax_Helper();
    $this->subscription_id = $this->Convpfm_TVC_Admin_Helper->get_subscriptionId();
    $this->google_detail = $this->Convpfm_TVC_Admin_Helper->get_convpfm_api_data();
    $this->subscription_data = $this->Convpfm_TVC_Admin_Helper->get_user_subscription_data();
    $this->google_merchant_id = $this->subscription_data->google_merchant_center_id;
    $this->store_id = $this->google_detail['setting']->store_id;
    $this->site_url = "admin.php?page=convpfm_pmax&tab=";    
    $this->Convpfm_TVC_Admin_Helper = new Convpfm_TVC_Admin_Helper();
    if (isset($this->subscription_data->google_ads_id) && $this->subscription_data->google_ads_id != "") {
      $this->google_ads_id = sanitize_text_field($this->subscription_data->google_ads_id);     
    }
    $currency_code_rs = $this->PMax_Helper->get_campaign_currency_code($this->google_ads_id);
    if (isset($currency_code_rs->data->currencyCode)) {
      $this->currency_code = sanitize_text_field($currency_code_rs->data->currencyCode);
    }
    $this->currency_symbol = $this->Convpfm_TVC_Admin_Helper->get_currency_symbols($this->currency_code);    
    if ($this->google_ads_id) {   
      $this->load_html();
    } else {
      $this->current_connect_google_ads_html();
    }
  }

  public function includes()
  {
    if (!class_exists('Convpfm_PMax_Helper')) {
      require_once(CONVPFM_ENHANCAD_PLUGIN_DIR . 'admin/helper/class-convpfm-pmax-helper.php');
    }
  }

  public function load_html()
  {
    do_action('conversios_start_html_' . sanitize_text_field($_GET['page']));
    $this->current_html();
    // $this->current_js();
    do_action('conversios_end_html_' . sanitize_text_field($_GET['page']));
  }
  public function current_connect_google_ads_html()
  {
?>
    <div class="section-campaignlisting dashbrdpage-wrap">
      <div class="mt24 whiteroundedbx dshreport-sec" style="box-shadow: 0px 4px 10px rgb(0 0 0 / 25%);">
        <div class="row dsh-reprttop">

        </div>
        <div class="google-account-analytics">

          <div class="row mb-3">
            <div class="col-12 col-md-12 col-lg-12 text-right">
              <p class="ga-text" style="margin:0; padding:20px 40px;">
                <span>
                  <a href="<?php echo esc_url_raw('admin.php?page=conversiospfm'); ?>" id="google-add-pop-up" class="btn btn-warning fs-14" style="background: #1967D2; color: #fff; font-size: 14px; font-weight: 600 !important;border-color: #1967D2;">
                    <img style="width:20px; height: 20px; " src="<?php echo esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/logos/conv_gads_logo.png'); ?>">
                    <?php echo esc_html__('Connect Google Ads', 'product-feed-manager-for-woocommerce'); ?>
                  </a>
                </span>
              </p>
            </div>
          </div>

        </div>


      </div>
    </div>
  <?php


  }
  public function current_html()
  { 
    $conv_data = $this->Convpfm_TVC_Admin_Helper->get_store_data();   
    $campaign_data = $this->Convpfm_TVC_Admin_Helper->convpfm_get_results('ee_pmax_campaign');
    $results = $this->PMax_Helper->campaign_pmax_list($this->google_ads_id, '10000', '', '');
    $allresult = array();
      if (isset($results->error) && $results->error == '') {
        if(isset($results->data) && $results->data != ""){
          $allresult = $results->data->results;
        }
      }  
      $getCountris = file_get_contents(__DIR__ . "/json/countries.json");
      $contData = json_decode($getCountris);
      global $wpdb;
      $table_name = $wpdb->prefix . 'convpfm_product_feed';
      $query = $wpdb->prepare(
        "SELECT `id`, `feed_name` FROM $table_name WHERE status = %s AND channel_ids = %d",
        'synced',
        1
      );
      $feed_results = $wpdb->get_results($query);
    ?>
    <style>
      .w-96 {
        width: 96%;
      }
      body { background: #f0f0f1; }
      
      input[type=radio]:checked::before {
        content: "";
        border-radius: 50%;
        width: .5rem;
        height: .5rem;
        margin: 0.1875rem;
        background-color: #ffff !important;
        line-height: 1.14285714;
      }
      .form-check .form-check-input {
          float: none;
          margin-left: -1.5em;
      }
      .imgChannel {
          width: 40px;
          height: 40px;
      }
      .select2-selection--multiple {
        height: 40px;
        border: 1px solid #cccccc !important;
      }
      .errorInput {
        border: 1.3px solid #ef1717 !important ;
      }
      .loading-row {
        position: relative;
        overflow: hidden; /* Prevent overflow from the animated border */
      }

      .loading-row::after {
          content: "";
          position: absolute;
          bottom: 0;
          left: 0;
          width: 50%;
          height: 3px; /* Height of the border */
          background: linear-gradient(to right, #3498db, #387ef5, #387ef5, #387ef5); /* Gradient colors */
          animation: loading 2s linear infinite; /* Animation properties */
      }
      @-webkit-keyframes loading {
        0% { left: 0; }
        100% { left: 100%; }
      }
      @-moz-keyframes loading {
        0% { left: 0; }
        100% { left: 100%; }
      }
    </style>


    <div class="container-fluid mt-4 w-96">
      <span class="fw-bold text-dark fs-20">
          <img class="imgChannel" src="<?php echo esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/web_store.png'); ?>" alt="">
          <?php esc_html_e("Campaign Management","product-feed-manager-for-woocommerce")?>
      </span>
      <p class="text-grey fs-16 fw-400">
          <?php esc_html_e("Manage your Performance Max (PMax) campaigns to reach customers across all Google channels. This involves setting up, optimizing, and monitoring your campaigns to ensure they are effectively driving conversions.
      ","product-feed-manager-for-woocommerce")?>
      </p>
      <nav class="navbar navbar-light bg-white shadow-sm topNavBar" style="border-top-left-radius:8px;border-top-right-radius:8px;">            
          <div class="col-12">   
              <div class="row ms-0 p-1">         
                  <div class="col-6 mt-2">
                    <div class="form-check form-check-inline all-campign">
                      <input class="form-check-input" type="radio" name="inlineRadioOptions" id="inlineRadio1" value="option1" checked>
                      <label class="form-check-label" for="inlineRadio1">All Campaigns</label>
                    </div>
                    <div class="form-check form-check-inline fail-campign ">
                      <input class="form-check-input" type="radio" name="inlineRadioOptions" id="inlineRadio2" value="option2">
                      <label class="form-check-label" for="inlineRadio2">Failed Campaigns</label>
                    </div>
                  </div>
                  <div class="col-6 d-flex justify-content-end">                    
                      <button
                          class="createCampaign btn btn-soft-primary fs-14 me-2 campaignClass create-campaign pointer"
                          title="Select Feed from below to create performance max campaign in Google Ads." style="pointer-events: auto !important">
                          <?php esc_html_e("Create Campaign", "product-feed-manager-for-woocommerce"); ?> 
                      </button>    
                      
                  </div>
              </div>            
          </div>        
      </nav>
      <div class="table-responsive shadow-sm convo-table-manegment p-1" style="border-bottom-left-radius:8px;border-bottom-right-radius:8px;">
        <?php
          $current_currency = get_woocommerce_currency();
          $currency_symbol = get_woocommerce_currency_symbol($current_currency);
        ?>       
          <table class="table" id="all_campagin_list_table" style="width:100%">
            <thead>
              <tr class="heading-row">
                <th scope="col">Campaign Name</th>
                <th scope="col status-bar">Status</th>
                <th scope="col" class="text-end">Daily Budget</th>
                <th scope="col" class="text-end">Clicks</th>
                <th scope="col" class="text-end">Cost (<?php echo $currency_symbol; ?>)</th>
                <th scope="col" class="text-end">Conversions</th>
                <th scope="col" class="text-end">sales</th>
                <th scope="col" class="text-center">More</th>
              </tr>
            </thead>
            <tbody class="table-body bg-white">
              <?php 
                foreach ($allresult as $result) { 
                  $status = $result->campaign->status == 'ENABLED' ? 'Enabled':'Paused';
                  $dailyBudget = number_format((float)$result->campaignBudget->amountMicros / 1000000, 2, '.', '');
                  $sales = $result->metrics->conversionsValue;
                ?>
                  <tr >   
                    <td scope="row" data-sort="<?php echo esc_html($result->campaign->id) ?>">
                      <div class="selling-head"><?php echo esc_html($result->campaign->name) ?></div>
                    </td>

                    <td class="status-class">
                      <div class="status-text">
                      <?php echo esc_html($status) ?> </div>
                    </td>
                    <td class="text-end"><?php echo esc_html($dailyBudget) ?></td>
                    <td class="text-end"><?php echo esc_html($result->metrics->clicks) ?></td>
                    <td class="text-end"><?php echo esc_html(number_format((float)$result->metrics->costMicros/1000000, 2, '.', '')) ?></td>
                    <td class="text-end"><?php echo esc_html($result->metrics->conversions) ?></td>
                    <td class="text-end"><?php echo esc_html($sales) ?></td>
                    <td data-id="<?php echo esc_html($result->campaign->id) ?>" class="text-center">
                      <span class="d-none"><?php// echo esc_html($result->campaign->id)?></span>
                      <label class="text-primary pointer" onclick="editCampaign(<?php echo esc_html($result->campaign->id)?>)"><span class="material-symbols-outlined">edit</span>Edit</label>
                    </td>
                  </tr>
                <?php }
              ?>                 
            </tbody>
          </table>
      </div>
      <div class="table-responsive shadow-sm convo-fail-campign p-1" style="border-bottom-left-radius:8px;border-bottom-right-radius:8px;"> 
          <table class="pmax-table" style="width:100%">
            <thead>
              <tr class="heading-row">
                <th scope="col">Campaign Name</th>
                <th scope="col status-bar">status</th>
                <th scope="col daily_budget">Daily Budget</th>
                <th scope="col">Target country</th>
                <th scope="col">Start Date</th>
                <th scope="col">End Date</th>
                <th scope="col">More</th>
              </tr>

            </thead>
            <tbody class="table-body bg-white">
              <?php

              $campaign_data = $this->Convpfm_TVC_Admin_Helper->convpfm_get_results('ee_pmax_campaign');

              if (empty($campaign_data) === FALSE) {
                $subscriptionId = $this->subscription_id;



                $store_id = $this->google_detail['setting']->store_id;
                $customObj = new Convpfm_CustomApi();
                foreach ($campaign_data as $value) {

                  $request_id = sanitize_text_field($value->request_id);
                  $campaign_name = sanitize_text_field($value->campaign_name);
                  $daily_budget = sanitize_text_field($value->daily_budget);
                  $target_country_campaign = sanitize_text_field($value->target_country_campaign);
                  $start_date = sanitize_text_field($value->start_date);
                  $target_roas = sanitize_text_field($value->target_roas);
                  $end_date = sanitize_text_field($value->end_date);
                  $status = sanitize_text_field($value->status);
                  $feed_id = sanitize_text_field($value->feed_id);
                  $updated_date = sanitize_text_field($value->updated_date);

                  $data = ['request_id' => $request_id, 'subscription_id' => $subscriptionId, 'store_id' => $store_id];
                  $pmaxStatus = $customObj->pMaxRetailStatus($data);

                  $pStatus = '';
                  if ($pmaxStatus->data->request_status == 1 || $pmaxStatus->data->request_status == 0) {
                    $pStatus = 'Created Successfully';
                  } else {
                    $pStatus = 'Failed'; ?>
                    <tr>
                      <td scope="row">
                        <div class="selling-head"><?php echo $campaign_name; ?></div>
                      </td>

                      <td class="status-class">
                        <div class="status-text">
                          <?php echo $status; ?></div>
                      </td>
                      <td><?php echo $daily_budget; ?></td>
                      <td><?php echo $target_country_campaign; ?></td>
                      <td><?php echo $start_date; ?></td>
                      <td><?php echo $end_date ?></td>

                      <td>
                        <span class="material-symbols-outlined">more_horiz
                        </span>
                      </td>
                    </tr>
                  <?php }
                }
              }
              ?>                  
            </tbody>
          </table>
      </div>  
    </div>

    <!-- Model POp-Up layout start here  -->
      <!-- <div class="create-campaign-pop-up"> -->
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
                    <div class="col-12 mb-4">
                      <select id="selecetdCampaign" multiple="multiple" class="form-control" name="selecetdCampaign[]" placeholder="Enter Campaign Name" style="width: 100%;" aria-labelledby="dropdownMenuButton">
                        
                        <?php foreach ($feed_results as $row) {
                          $feed_name = sanitize_text_field($row->feed_name);
                        ?>
                          <option value="<?php echo esc_attr($row->id); ?>"><?php echo esc_html($feed_name); ?></option>
                        <?php } ?>
                      </select>
                    </div>
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
                  </div>
                </div>
              </div>

              <div class="modal-footer border-0 p-0">  
              </div>
            </div>
          </div>
        </div>
      <!-- </div> -->
      <!-- <div class="Edit-campaign-pop-up"> -->
        <div class="modal fade" id="edit-campign-pop-up" tabindex="-1" aria-labelledby="exitModalLabel" aria-hidden="false">
          <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
            <div id="loadingbar_blue_modal_editcampign" class="progress-materializecss d-none ps-2 pe-2" style="width:100%">
                    <div class="indeterminate"></div>
                </div>
              <div class="modal-header border-0 pb-0" style="justify-content:flex-start">
                <img class="imgChannel" src="<?php echo esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/google_modal.png'); ?>" alt="">
                <h5 class="fw-600 fs-20 ms-2" style="margin-left: 1px">Performance Max Campaign</h5>
              </div>
              <div class="modal-body max-campaign pb-0">
                <div class="container-fluid p-0">
                <span class="otherError text-danger"></span>
                <form id="#edit-pmax-campaign-form">
                  <div class="row mb-4">                    
                    <div class="col-6">
                      <input type="text" id="edit_campaignName" name="edit_campaignName" class="form-control" placeholder="Enter Campaign Name" readonly>
                    </div>
                    <div class="col-6">
                      <input type="text" id="edit_daily_budget" name="edit_daily_budget" class="form-control" placeholder="Enter Daily Budget">
                    </div>
                  </div>
                  <div class="row mb-4">
                    <div class="col-6">
                      <select id="edit_target_country_campaign" name="edit_target_country_campaign" class="form-control" style="width:100%" disabled>
                        <option value="">Select Country</option>
                        <?php
                        $selecetdCountry = $conv_data['user_country']; 
                        foreach($contData as $country) { ?>
                            <option value="<?php echo esc_html($country->code) ?>" <?php echo $selecetdCountry === $country->code ? 'selected = "selecetd"' : '' ?> ><?php echo esc_html($country->name) ?></option>
                        <?php }
                        ?>
                      </select>
                      <input type="hidden" name="edit_country_campaign" id="edit_country_campaign">
                    </div>
                    <div class="col-6">
                      <input type="text" id="edit_target_roas" name="edit_target_roas" class="form-control" placeholder="Add Target ROAS (%)"><span class="fs-10">Formula: Conversion value ÷ ad spend x 100% = target ROAS percentage</span>
                    </div>
                  </div>
                  <div class="row mb-4">
                    <div class="col-6">
                      <input type="date" id="edit_start_date" name="edit_start_date" class="form-control" placeholder="Start Date" readonly>
                      <span class="startDateError text-danger fs-10"></span>
                    </div>
                    <div class="col-6">                    
                      <input type="date" id="edit_end_date" name="edit_end_date" class=form-control placeholder="End Date">
                      <span class="endDateError text-danger fs-10"></span>
                    </div>
                  </div>
                  <div class="row mb-4">
                    <div class="col-6">
                      <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="edit_status" id="inlineRadio1" value="ENABLED" checked>
                        <label class="form-check-label" for="inlineRadio1">Enable</label>
                      </div>
                      <div class="form-check form-check-inline ml-2">
                        <input class="form-check-input" type="radio" name="edit_status" id="inlineRadio2" value="PAUSED">
                        <label class="form-check-label" for="inlineRadio2">Pause</label>
                      </div>
                      <div class="form-check form-check-inline ml-2">
                        <input class="form-check-input" type="radio" name="edit_status" id="inlineRadio2" value="REMOVED">
                        <label class="form-check-label" for="inlineRadio2">Remove</label>
                      </div>
                    </div>
                    <div class="col-6 d-flex justify-content-end">
                      <button type="button" class="btn btn-soft-primary fs-14 fw-400" id="submitEditedCampaign">Submit Campaign</button>
                    </div>
                    <input type="hidden" name="campaignBudget" id="campaignBudget">
                    <input type="hidden" name="resourceName" id="resourceName">
                    <input type="hidden" name="campaign_id" id="campaign_id">
                  </div>
                  </form>
                </div>
              </div>

              <div class="modal-footer border-0 p-0">  
              </div>
            </div>
          </div>
        </div>
      <!-- </div> -->
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
            <div class="modal-footer d-flex justify-content-center" style="border-top:none">
              <button type="button" class="btn btn-dark" data-bs-dismiss="modal" onclick="location.reload();">Close</button>
            </div>
          </div>
        </div>
      </div>
    <!-- </div> -->
    <script>
      jQuery(document).ready(function() {
        jQuery('.table').DataTable({
          "order": [[ 0, "desc" ]],   
          columnDefs: [
                      { orderable: true, targets: 0 },
                      // { orderable: true, targets: 7 },
                      { orderable: false, targets: '_all' },
                    ],
        });
        jQuery('.pmax-table').DataTable();

        jQuery('.create-campaign').on('click', function(event) {
          jQuery('.otherError').html( '');
          jQuery('span, input').removeClass('errorInput')
          jQuery('#campign-pop-up').modal('show');
          jQuery('#target_country_campaign').select2({
            'dropdownParent' :jQuery('#campign-pop-up')
          })
          jQuery('#selecetdCampaign').select2({
            "dropdownParent": jQuery('#campign-pop-up'),
            placeholder: 'Select Feed Name',
          });
        });
        
        jQuery(".close").click(function() {
          jQuery("#campign-pop-up").modal('hide');
        });    

        jQuery(".convo-fail-campign").hide();
        jQuery('#inlineRadio1').on('change', function() {
          if (jQuery(this).is(':checked')) {
            jQuery(".convo-fail-campign").hide();
            jQuery(".convo-table-manegment").show();
          }
        });
        jQuery('#inlineRadio2').on('change', function() {
          if (jQuery(this).is(':checked')) {
            jQuery(".convo-fail-campign").show();
            jQuery(".convo-table-manegment").hide();
          }
        });        
      });
      /***************************Submit Campaign start ****************************************************************/
    jQuery(document).on('click', '#submitCampaign', function () {
      var feed_id = jQuery('#selecetdCampaign option:selected').map(function() {
                      return $(this).val();
                    }).get();
      var feed_ids = feed_id.join(', ');
     
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
      if(feed_ids == '') {
        jQuery('#selecetdCampaign').next('span').find('.select2-selection--multiple').addClass('errorInput')
        hasError = true
      }
      
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
      let subscriptionId = "<?php echo esc_js($this->subscription_id) ?>";
      let google_merchant_center_id = "<?php echo esc_js($this->google_merchant_id) ?>";
      let google_ads_id = "<?php echo esc_js($this->google_ads_id) ?>";
      let store_id = "<?php echo esc_js($this->store_id) ?>";
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
        subscription_id: "<?php echo esc_js($this->subscription_id) ?>",
        google_merchant_id: "<?php echo esc_js($this->google_merchant_id) ?>",
        google_ads_id: "<?php echo esc_js($this->google_ads_id) ?>",
        sync_item_ids: feed_ids,
        domain: "<?php echo get_site_url() ?>",
        store_id: "<?php echo esc_js($this->store_id) ?>",
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
            html += '<div class="text-danger">Failed! Your operation was failed.</div>';    
            html += '<div class="text-dark fs-12 mt-2">'+response.message+'</div>';
            jQuery('.infoBody').html(html)
              jQuery('#infoModal').modal('show')
          }else {
            var html = '<img class="" src="<?php echo esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/successImg.png'); ?>" alt="" style="width:150px; height:150px;">';
                html += '<div class="text-success">Success! Your operation was completed.</div>';
                html += '<div class="text-dark fs-12 mt-2">Exciting things are happening behind the scenes! We\'re crafting your Pmax campaign for Google Ads with precision. Your products are gearing up to shine. Sit tight, and get ready for an amplified reach and increased sales.</div>';
        
              jQuery('.infoBody').html(html)
              jQuery('#infoModal').modal('show')
          }            
        }
      });

    })
    /***************************Submit Campaign end ******************************************************************/
    /***************************Remove Error on input change satrt ***************************************************/
    jQuery(document).on('keyup change', '.errorInput', function() {
      jQuery(this).removeClass('errorInput')
      jQuery(this).next('span').find('.select2-selection--multiple').removeClass('errorInput')
      jQuery(this).next('span').find('.select2-selection--single').removeClass('errorInput')
      jQuery('.endDateError').html('')
      jQuery('.startDateError').html('')
    })
    jQuery(document).on('keyup change', '#selecetdCampaign', function() {
      jQuery(this).next('span').find('.select2-selection--multiple').removeClass('errorInput')
    })
    /***************************Remove Error on input change end *****************************************************/
    jQuery(document).on('keydown', 'input[name="daily_budget"], input[name="target_roas"],input[name="edit_daily_budget"], input[name="edit_target_roas"]', function () {
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
    
    function editCampaign(id) {
      var conv_onboarding_nonce = "<?php echo esc_html(wp_create_nonce('conv_onboarding_nonce')); ?>";
      var data = {
          action: "convpfm_editPmaxCampaign",
          id: id,
          google_ads_id: "<?php echo esc_js($this->google_ads_id) ?>",
          conv_onboarding_nonce: conv_onboarding_nonce
        }
      jQuery.ajax({
        type: "POST",
        dataType: "json",
        url: convpfm_ajax_url,
        data: data,
        beforeSend: function () {
          jQuery('.topNavBar').addClass('loading-row')
            jQuery("#wpbody").css("pointer-events", "none");
        },
        error: function (err, status) {
          jQuery('.topNavBar').removeClass('loading-row')
          jQuery("#wpbody").css("pointer-events", "auto");
        },
        success: function (response) {
          jQuery("#wpbody").css("pointer-events", "auto");
          jQuery('.topNavBar').removeClass('loading-row')
          jQuery('#edit_campaignName').val(response.result['campaignName'])              
          jQuery('#edit_daily_budget').val(response.result['budget'])
          jQuery('#edit_target_country_campaign').val(response.result['sale_country'])
          jQuery('#edit_country_campaign').val(response.result['sale_country'])
          jQuery('#edit_target_roas').val(response.result['target_roas'])
          jQuery('#edit_start_date').val(response.result['startDate'])
          jQuery('#edit_end_date').val(response.result['endDate'])
          jQuery('input[name=edit_status][value="'+response.result['status']+'"]').val()
          jQuery('#resourceName').val(response.result['resourceName'])
          jQuery('#campaignBudget').val(response.result['campaignBudget'])
          jQuery('#campaign_id').val(id)
          jQuery('#edit-campign-pop-up').modal('show')
        }
      });
    }
    jQuery(document).on('click','#submitEditedCampaign', function(event){
      event.preventDefault(); 
      //check validation start
      let arrValidate = ['edit_daily_budget', 'edit_end_date'];
      let hasError = false;
      jQuery.each(arrValidate, function(i, v) {
        if(jQuery('#'+v).val() == '') {
          jQuery('#'+v).addClass('errorInput');
          hasError = true
        }            
      })
        
      if(hasError == true) {
        return false;
      }
      var todayDate = new Date();
      var eDate = new Date(jQuery('#edit_end_date').val());
      var sDate = new Date(jQuery('#edit_start_date').val());
      if(sDate > eDate)
      {
        jQuery('#end_date').addClass('errorInput');
        jQuery('.endDateError').html('Check End Date.')
        return false;
      }
      let subscriptionId = "<?php echo esc_js($this->subscription_id) ?>";
      let google_merchant_center_id = "<?php echo esc_js($this->google_merchant_id) ?>";
      let google_ads_id = "<?php echo esc_js($this->google_ads_id) ?>";
      let store_id = "<?php echo esc_js($this->store_id) ?>";
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
      var conversios_nonce = "<?php echo esc_html(wp_create_nonce('conversios_nonce')); ?>";
      var data = {
        action: "convpfm_update_PmaxCampaign",
        campaign_name: jQuery('#edit_campaignName').val(),
        budget: jQuery('#edit_daily_budget').val(),
        target_country: jQuery('#edit_country_campaign').val(),
        start_date: jQuery('#edit_start_date').val(),
        end_date: jQuery('#edit_end_date').val(),
        target_roas: jQuery('#edit_target_roas').val() == '' ? 0 : jQuery('#edit_target_roas').val() ,
        status: jQuery('input[name=edit_status]:checked').val(),    
        merchant_id: "<?php echo esc_js($this->google_merchant_id) ?>",
        customer_id: "<?php echo esc_js($this->google_ads_id) ?>",
        resource_name: jQuery('#resourceName').val(),
        campaign_budget_resource_name: jQuery('#campaignBudget').val(),
        campaign_id: jQuery('#campaign_id').val(), 
        conversios_nonce: conversios_nonce
      }
      jQuery.ajax({
        type: "POST",
        dataType: "json",
        url: convpfm_ajax_url,
        data: data,
        beforeSend: function () {
          jQuery("#loadingbar_blue_modal_editcampign").removeClass('d-none');
          jQuery("#wpbody").css("pointer-events", "none");
          jQuery('#submitCampaign').attr('disabled', true);
        },
        error: function (err, status) {
          jQuery("#loadingbar_blue_modal_editcampign").addClass('d-none');
          jQuery("#wpbody").css("pointer-events", "auto");
          jQuery('#submitEditedCampaign').attr('disabled', false);
        },
        success: function (response) {
          jQuery("#loadingbar_blue_modal_editcampign").addClass('d-none');
          jQuery("#wpbody").css("pointer-events", "auto");
          jQuery('#submitEditedCampaign').attr('disabled', false);
          jQuery('#edit-campign-pop-up').modal('hide');
          if(response.error == true) {
            var html = '<img class="" src="<?php echo esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/errorImg.png'); ?>" alt="" style="width:150px; height:150px;">';
              html += '<div class="text-danger">Failed! Your operation was failed.</div>';  
              html += '<div class="text-dark fs-12 mt-2">'+response.message+'</div>';
              jQuery('.infoBody').html(html)
              jQuery('#infoModal').modal('show')
          }else {
            var html = '<img class="" src="<?php echo esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/new_logo/successImg.png'); ?>" alt="" style="width:150px; height:150px;">';
                html += '<div class="text-success">Success! Your operation was completed.</div>';
                html += '<div class="text-dark fs-12 mt-2">'+response.message+'</div>';
        
              jQuery('.infoBody').html(html)
              jQuery('#infoModal').modal('show')
          }                
        }
      });
    })
    </script>

    <!-- daterage script -->
    <?php
  }
}
?>