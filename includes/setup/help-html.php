<?php
function get_connect_google_popup_html_to_active_licence(){
  $Convpfm_TVC_Admin_Helper = new Convpfm_TVC_Admin_Helper();
  return '<div class="modal fade popup-modal overlay" id="tvc_google_connect_active_licence">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-body">
          <h5 class="modal-title" id="staticBackdropLabel">'.esc_html__("Connect Tatvic with your website to active licence key", "product-feed-manager-for-woocommerce").'</h5>
          <button type="button" id="tvc_google_connect_active_licence_close" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
          </button>
          <br>              
          <p>'.esc_html__("Make sure you sign in with the google account that has all privileges to access google analytics, google ads and google merchant center account.", "product-feed-manager-for-woocommerce").'</p>
        </div>
        <div class="modal-footer">
          <a class="ee-oauth-container btn darken-4 white black-text" href="'. esc_url_raw($Convpfm_TVC_Admin_Helper->get_onboarding_page_url()).'" style="text-transform:none; margin: 0 auto;">
            <p style="font-size: inherit; margin-top:5px;"><img width="20px" style="margin-right:8px" alt="Google sign-in" src="'.esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL."/admin/images/g-logo.png").'" />'.esc_html__("Sign In With Google", "product-feed-manager-for-woocommerce").'</p>
          </a>
        </div>
      </div>
    </div>
  </div>';
}