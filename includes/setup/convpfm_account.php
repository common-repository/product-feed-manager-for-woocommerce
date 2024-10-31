<?php
class Convpfm_Account {
  protected $Convpfm_TVC_Admin_Helper="";
  protected $url = "";
  protected $subscriptionId = "";
  protected $google_detail;
  protected $customApiObj;
  public function __construct() {
    $this->Convpfm_TVC_Admin_Helper = new Convpfm_TVC_Admin_Helper();
    $this->customApiObj = new Convpfm_CustomApi();
    $this->subscriptionId = $this->Convpfm_TVC_Admin_Helper->get_subscriptionId(); 
    $this->google_detail = $this->Convpfm_TVC_Admin_Helper->get_convpfm_api_data();    
    // $this->Convpfm_TVC_Admin_Helper->add_spinner_html();     
    $this->create_form();
  }

  public function create_form() {
    $message = ""; $class="";        
    $googleDetail = [];
    $plan_name =  esc_html__("Free Plan","product-feed-manager-for-woocommerce");
    $plan_price = esc_html__("Free","product-feed-manager-for-woocommerce");
    $api_licence_key=""; 
    $paypal_subscr_id = "";   
    $product_sync_max_limit ="100";    
    $activation_date = "";
    $next_payment_date = "";
    $subcrption_id = "";
    //$subscription_type = "";
    if(isset($this->google_detail['setting'])){
      if ($this->google_detail['setting']) {
        $googleDetail = $this->google_detail['setting'];        
      }
      $api_licence_key=$googleDetail->licence_key;
      $subcrption_id = $googleDetail->id;
    }    
    ?>

<style>
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
          background: linear-gradient(to right, #db3434, #f5388c, #387ef5, #387ef5); /* Gradient colors */
          animation: loading 1.5s linear infinite; /* Animation properties */
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
  <div class="bg-white rounded-3">
    <div class="p-4 bg-primary rounded-top topNavBar">
      <label for="" class="text-white">Account Summery</label>
    </div>
    <div class="col-12 p-4">
      <div class="row">
        <div class="col-6 mb-3">
          <label class="fs-14 fw-600">Plan Name:</label>
          <label class="fs-14 fw-400"><?php echo esc_attr($plan_name); ?></label>
        </div>
        <div class="col-6 mb-3">
          <label class="fs-14 fw-600">Plan Price:</label>
          <label class="fs-14 fw-400"><?php echo esc_attr($plan_price); ?></label>
        </div>
        <div class="col-6 mb-3">
          <label class="fs-14 fw-600">Active License Key:</label>
          <label class="fs-14 fw-400"><?php echo esc_attr($api_licence_key); ?></label>
        </div>
        <div class="col-6 mb-3">
          <label class="fs-14 fw-600">Subcription ID:</label>
          <label class="fs-14 fw-400"><?php echo esc_attr($subcrption_id); ?></label>
        </div>
        <div class="col-6 mb-3">
          <label class="fs-14 fw-600">Product Sync Limit:</label>
          <label class="fs-14 fw-400">100 Products</label>
        </div>
        <div class="col-6 mb-3">
          <label class="fs-14 fw-600">Next Bill Date:</label>
          <label class="fs-14 fw-400">NA</label>
        </div>
        <div class="col-6 mb-3">
          <label class="fs-14 fw-600">Refresh Subscription Details:</label>
          <button class="btn btn-soft-primary fs-14 fw-400" id="refresh_sub">Refresh Subcription Details</button>
        </div>
        <div class="col-8">
          <label class="fs-14 fw-400">You are currently using our free plugin, with the product sync limit upto 100 products only. To increase the product sync limit and unlock more features. 
            <a class="" href="<?php echo esc_url_raw('https://www.conversios.io/pricing?plugin_name=pfm&utm_source=in_app&utm_medium=top_menu&utm_campaign=help_center'); ?>" target="_blank"><b>Upgrade to Pro</b>
          </label>
        </div>
      </div>

    </div>
  </div>
</div>
<script>
jQuery(document).ready(function () {
  jQuery("#refresh_sub").click(function() {
          var data = {
            action: "convpfm_call_subscription_refresh",
            conv_licence_nonce: "<?php echo wp_create_nonce('conv_lic_nonce'); ?>"
          };
          jQuery.ajax({
            type: "POST",
            dataType: "json",
            url: convpfm_ajax_url,
            data: data,
            beforeSend: function() {
              jQuery(".topNavBar").addClass("loading-row")
            },
            success: function(response) {
              jQuery(".topNavBar").removeClass("loading-row")
              location.reload();
            }
          });
        });
});
</script>
<?php
    }
}
?>