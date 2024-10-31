<?php

/**
 * @since      4.0.2
 * Description: Conversios Onboarding page, It's call while active the plugin
 */
if (!class_exists('Convpfm_Footer')) {
	class Convpfm_Footer
	{
		protected $Convpfm_TVC_Admin_Helper="";
		public function __construct()
		{
			add_action('add_convpfm_footer', array($this, 'before_end_footer'));
			add_action('add_convpfm_footer', array($this, 'before_end_footer_add_script'));
			$this->Convpfm_TVC_Admin_Helper = new Convpfm_TVC_Admin_Helper();
		}
		public function before_end_footer()
		{
?>
			<div class="tvc_footer_links">
			</div>
			<?php
			$licenceInfoArr = array(
				"Plan Type:" => "Free",
				"Plan Price:" => "Not Available",
				"Active License Key:" => "Not Available",
				"Subscription ID:" => "Not Available",
				"Active License Key:" => "Not Available",
				"Last Bill Date:" => "Not Available",
				"Next Bill Date:" => "Not Available",
			);
			?>


			<div class="modal fade" id="convLicenceInfoMod" tabindex="-1" aria-labelledby="convLicenceInfoModLabel" aria-hidden="true">
				<div class="modal-dialog modal-lg modal-dialog-centered" style="width: 700px;">
					<div class="modal-content">
						<div class="modal-header badge-dark-blue-bg text-white">
							<h5 class="modal-title text-white" id="convLicenceInfoModLabel">
								<?php esc_html_e("My Subscription", "product-feed-manager-for-woocommerce"); ?>
							</h5>
							<button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
						</div>
						<div class="modal-body">
							<div class="container-fluid">
								<div class="row">
									<?php foreach ($licenceInfoArr as $key => $value) { ?>
										<div class="<?php echo $key == "Connected with:" ? "col-md-12" : "col-md-6"; ?> py-2 px-0">
											<span class="fw-bold">
												<?php esc_html_e($key, "product-feed-manager-for-woocommerce"); ?>
											</span>
											<span class="ps-2">
												<?php esc_html_e($value, "product-feed-manager-for-woocommerce"); ?>
											</span>
										</div>
									<?php  } ?>
								</div>
							</div>
						</div>
						<div class="modal-footer justify-content-center">
							<div class="fs-6">
								<span><?php esc_html_e("You are currently using our free plugin, no license needed! Happy Analyzing.", "product-feed-manager-for-woocommerce"); ?></span>
								<span><?php esc_html_e("To unlock more features of Google Products ", "product-feed-manager-for-woocommerce"); ?></span>
								<?php echo $this->Convpfm_TVC_Admin_Helper->get_conv_pro_link_adv("planpopup", "globalheader", "conv-link-blue", "anchor", "Upgrade to Pro Version"); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		<?php
		}

		public function before_end_footer_add_script()
		{
			// $Convpfm_TVC_Admin_Helper = new Convpfm_TVC_Admin_Helper();
			// $subscriptionId =  sanitize_text_field($Convpfm_TVC_Admin_Helper->get_subscriptionId());
		?>
			<script type="text/javascript">
				
			</script>
			<script>
				window.fwSettings = {
					'widget_id': 81000001743
				};
				! function() {
					if ("function" != typeof window.FreshworksWidget) {
						var n = function() {
							n.q.push(arguments)
						};
						n.q = [], window.FreshworksWidget = n
					}
				}()
			</script>
			<script type='text/javascript' src='https://ind-widget.freshworks.com/widgets/81000001743.js' async defer></script>
<?php
		}
	}
}
new Convpfm_Footer();
