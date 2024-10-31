<?php
class Convpfm_Pricings
{
    protected $Convpfm_TVC_Admin_Helper = "";
    protected $url = "";
    protected $subscriptionId = "";
    protected $google_detail;
    protected $customApiObj;
    protected $pro_plan_site;
    protected $convositeurl;

    public function __construct()
    {
        $this->Convpfm_TVC_Admin_Helper = new Convpfm_TVC_Admin_Helper();
        $this->customApiObj = new Convpfm_CustomApi();
        $this->subscriptionId = $this->Convpfm_TVC_Admin_Helper->get_subscriptionId();
        $this->google_detail = $this->Convpfm_TVC_Admin_Helper->get_convpfm_api_data();
        // $this->Convpfm_TVC_Admin_Helper->add_spinner_html();
        $this->pro_plan_site = $this->Convpfm_TVC_Admin_Helper->get_pro_plan_site();
        $this->convositeurl = "http://conversios.io";
        $this->create_form();
    }

    public function create_form()
    {
        $close_icon = esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/close.png');
        $check_icon = esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/check.png');
?>


        <div class="convo-global">
            <div class="convo-pricingpage">
                <!-- pricing timer -->
                <div class="pricing-timer d-none">
                    <div class="container">
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="timer-box">
                                            <div id="time"> <span id="min">00</span>:<span id="sec">00</span></div>
                                        </div>
                                        <h5 class="card-title">
                                            <?php esc_html_e("Wait! Get 10% Off", "product-feed-manager-for-woocommerce"); ?>
                                        </h5>
                                        <p class="card-text">
                                            <?php esc_html_e("Purchase any yearly plan in next 10 minutes with coupon code", "product-feed-manager-for-woocommerce"); ?>
                                            <strong>
                                                <?php esc_html_e("FIRSTBUY10", "product-feed-manager-for-woocommerce"); ?>
                                            </strong>
                                            <?php esc_html_e("and get additional 10% off.", "product-feed-manager-for-woocommerce"); ?>
                                        </p>
                                        <a class="btn btn-secondary common-btn" target="_blank" href="<?php echo esc_url_raw($this->convositeurl . "/checkout/?pid=planD_1_y&utm_source=in_app&utm_medium=freevspro&utm_campaign=freeplugin"); ?>">
                                            <?php esc_html_e(" Get It Now", "product-feed-manager-for-woocommerce"); ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- business area -->
                <div class="business-area">
                    <div class="container">
                        <div class="row">
                            <div class="col-12">
                                <div class="title-text">
                                    <h2 data-aos="flip-up" data-aos-duration="1000" class="aos-init aos-animate"> <?php esc_html_e("Scale Your
                                Business Faster with Conversios", "product-feed-manager-for-woocommerce"); ?></h2>
                                    <h3> <?php esc_html_e("Get", "product-feed-manager-for-woocommerce"); ?><strong>
                                            <?php esc_html_e("15 days money back guarantee", "product-feed-manager-for-woocommerce"); ?></strong>
                                        <?php esc_html_e("On any plan you choose.", "product-feed-manager-for-woocommerce"); ?>
                                    </h3>
                                </div>
                            </div>
                        </div>
                        <div class="myplan-wholebox">
                            <div class="row align-items-end">
                                <div class="col-auto me-auto">
                                    <div class="myplan-box">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" checked type="checkbox" role="switch" id="yearmonth_checkbox">
                                        </div>
                                        <!-- <p>Monthly | <span>Yearly</span> Get Flat 50% off on all yearly plans. </p> -->
                                    </div>
                                </div>
                                <div class="col-auto ms-auto">
                                    <div class="domain-box">
                                        <p><?php esc_html_e("Select Number Of Domains", "product-feed-manager-for-woocommerce"); ?>
                                        </p>
                                        <div class="choose-domainbox">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="inlineRadioOptions" id="inlineRadio1" value="1" checked>
                                                <label class="form-check-label" for="inlineRadio1"><?php esc_html_e("1", "product-feed-manager-for-woocommerce"); ?></label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="inlineRadioOptions" id="inlineRadio2" value="5">
                                                <label class="form-check-label" for="inlineRadio2"><?php esc_html_e("5", "product-feed-manager-for-woocommerce"); ?></label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="inlineRadioOptions" id="inlineRadio3" value="10">
                                                <label class="form-check-label" for="inlineRadio3"><?php esc_html_e("10", "product-feed-manager-for-woocommerce"); ?></label>
                                            </div>
                                            <!-- <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="inlineRadioOptions"
                                            id="inlineRadio4" value="10+">
                                        <label class="form-check-label" for="inlineRadio4">10+</label>
                                    </div> -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="pricingcard-wholebox">
                            <div class="row">
                                <div class="col-xl-4 col-lg-4 col-md-6 col-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <?php esc_html_e("Enterprise", "product-feed-manager-for-woocommerce"); ?>
                                            </h5>

                                            <div class="dynamicprice_box" boxperiod="yearly" boxdomain="1">
                                                <p class="card-text">
                                                    <?php esc_html_e("1 Active Website", "product-feed-manager-for-woocommerce"); ?>
                                                </p>
                                                <div class="card-price">
                                                    <?php esc_html_e("$99.00/", "product-feed-manager-for-woocommerce"); ?>
                                                    <span><?php esc_html_e(" year", "product-feed-manager-for-woocommerce"); ?></span>
                                                </div>
                                                <div class="slash-price">
                                                    <?php esc_html_e(" Regular Price:", "product-feed-manager-for-woocommerce"); ?><span><?php esc_html_e("$198.00/year", "product-feed-manager-for-woocommerce"); ?>
                                                    </span></div>
                                                <div class="offer-price">
                                                    <?php esc_html_e("50% Off", "product-feed-manager-for-woocommerce"); ?>
                                                </div>
                                                <div class="getstarted-btn">
                                                    <a class="btn btn-secondary common-btn" target="_blank" href="<?php echo esc_url_raw($this->convositeurl . "/checkout/?pid=planDpf_1_y&utm_source=in_app&utm_medium=freevspro&utm_campaign=freeplugin"); ?>">
                                                        <?php esc_html_e("BUY NOW", "product-feed-manager-for-woocommerce"); ?>
                                                    </a>
                                                </div>
                                            </div>

                                            <div class="dynamicprice_box d-none" boxperiod="yearly" boxdomain="5">
                                                <p class="card-text">
                                                    <?php esc_html_e("5 Active Website", "product-feed-manager-for-woocommerce"); ?>
                                                </p>
                                                <div class="card-price">
                                                    <?php esc_html_e("$199.00/", "product-feed-manager-for-woocommerce"); ?>
                                                    <span><?php esc_html_e(" year", "product-feed-manager-for-woocommerce"); ?></span>
                                                </div>
                                                <div class="slash-price">
                                                    <?php esc_html_e(" Regular Price:", "product-feed-manager-for-woocommerce"); ?><span><?php esc_html_e("$398.00/year", "product-feed-manager-for-woocommerce"); ?>
                                                    </span></div>
                                                <div class="offer-price">
                                                    <?php esc_html_e("50% Off", "product-feed-manager-for-woocommerce"); ?>
                                                </div>

                                                <div class="getstarted-btn">
                                                    <a class="btn btn-secondary common-btn" target="_blank" href="<?php echo esc_url_raw($this->convositeurl . "/checkout/?pid=planDpf_2_y&utm_source=in_app&utm_medium=freevspro&utm_campaign=freeplugin"); ?>">
                                                        <?php esc_html_e("BUY NOW", "product-feed-manager-for-woocommerce"); ?>
                                                    </a>
                                                </div>
                                            </div>

                                            <div class="dynamicprice_box d-none" boxperiod="yearly" boxdomain="10">
                                                <p class="card-text">
                                                    <?php esc_html_e("10 Active Website", "product-feed-manager-for-woocommerce"); ?>
                                                </p>
                                                <div class="card-price">
                                                    <?php esc_html_e("$299.00/", "product-feed-manager-for-woocommerce"); ?>
                                                    <span><?php esc_html_e(" year", "product-feed-manager-for-woocommerce"); ?></span>
                                                </div>
                                                <div class="slash-price">
                                                    <?php esc_html_e(" Regular Price:", "product-feed-manager-for-woocommerce"); ?><span><?php esc_html_e("$598 .00/year", "product-feed-manager-for-woocommerce"); ?>
                                                    </span></div>
                                                <div class="offer-price">
                                                    <?php esc_html_e("50% Off", "product-feed-manager-for-woocommerce"); ?>
                                                </div>

                                                <div class="getstarted-btn">
                                                    <a class="btn btn-secondary common-btn" target="_blank" href="<?php echo esc_url_raw($this->convositeurl . "/checkout/?pid=planDpf_3_y&utm_source=in_app&utm_medium=freevspro&utm_campaign=freeplugin"); ?>">
                                                        <?php esc_html_e(" BUY NOW", "product-feed-manager-for-woocommerce"); ?>
                                                    </a>
                                                </div>
                                            </div>
                                            <ul class="feature-listing custom-scrollbar">
                                                <li>
                                                    <div class="tooltip-box">
                                                        <button type="button" class="btn btn-secondary tooltipc remove" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-custom-class="custom-tooltip">
                                                            <?php esc_html_e("Everything in Professional", "product-feed-manager-for-woocommerce"); ?>
                                                        </button>
                                                    </div>
                                                </li>
                                                <span>&#43;</span>                                               
                                                <li>
                                                    <div class="tooltip-box">
                                                        <button type="button" class="btn btn-secondary tooltipc remove" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-custom-class="custom-tooltip">
                                                            <?php esc_html_e("Unlimited number of product ync", "product-feed-manager-for-woocommerce"); ?>
                                                        </button>
                                                    </div>
                                                </li>
                                                
                                            </ul>
                                            <div class="features-link">
                                                <a href="#seeallfeatures"><?php esc_html_e("Compare All Features", "product-feed-manager-for-woocommerce"); ?></a>
                                            </div>
                                            <div class="dynamicprice_box" boxperiod="yearly" boxdomain="1">
                                                <div class="getstarted-btn">
                                                    <a class="btn btn-secondary common-btn" target="_blank" href="<?php echo esc_url_raw($this->convositeurl . "/checkout/?pid=planDpf_1_y&utm_source=in_app&utm_medium=freevspro&utm_campaign=freeplugin"); ?>">
                                                        <?php esc_html_e("BUY NOW", "product-feed-manager-for-woocommerce"); ?>
                                                    </a>
                                                </div>
                                            </div>

                                            <div class="dynamicprice_box d-none" boxperiod="yearly" boxdomain="5">
                                                <div class="getstarted-btn">
                                                    <a class="btn btn-secondary common-btn" target="_blank" href="<?php echo esc_url_raw($this->convositeurl . "/checkout/?pid=planDpf_2_y&utm_source=in_app&utm_medium=freevspro&utm_campaign=freeplugin"); ?>">
                                                        <?php esc_html_e("BUY NOW", "product-feed-manager-for-woocommerce"); ?>
                                                    </a>
                                                </div>
                                            </div>

                                            <div class="dynamicprice_box d-none" boxperiod="yearly" boxdomain="10">
                                                <div class="getstarted-btn">
                                                    <a class="btn btn-secondary common-btn" target="_blank" href="<?php echo esc_url_raw($this->convositeurl . "/checkout/?pid=planDpf_3_y&utm_source=in_app&utm_medium=freevspro&utm_campaign=freeplugin"); ?>">
                                                        <?php esc_html_e("BUY NOW", "product-feed-manager-for-woocommerce"); ?>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="popular-plan">
                                                <p><?php esc_html_e("Most Popular", "product-feed-manager-for-woocommerce"); ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-4 col-lg-4 col-md-6 col-12">
                                    <div class="card active">
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <?php esc_html_e("Professional", "product-feed-manager-for-woocommerce"); ?>
                                            </h5>

                                            <div class="dynamicprice_box" boxperiod="yearly" boxdomain="1">
                                                <p class="card-text">
                                                    <?php esc_html_e("1 Active Website", "product-feed-manager-for-woocommerce"); ?>
                                                </p>
                                                <div class="card-price">
                                                    <?php esc_html_e("$69.00/ ", "product-feed-manager-for-woocommerce"); ?><span><?php esc_html_e("year", "product-feed-manager-for-woocommerce"); ?></span>
                                                </div>
                                                <div class="slash-price">
                                                    <?php esc_html_e("Regular Price: ", "product-feed-manager-for-woocommerce"); ?><span><?php esc_html_e("$138.00/year", "product-feed-manager-for-woocommerce"); ?></span>
                                                </div>
                                                <div class="offer-price">
                                                    <?php esc_html_e("50% Off", "product-feed-manager-for-woocommerce"); ?>
                                                </div>
                                                <div class="getstarted-btn">
                                                    <a class="btn btn-secondary common-btn" target="_blank" href="<?php echo esc_url_raw($this->convositeurl . "/checkout/?pid=wpPFM_PY1&utm_source=in_app&utm_medium=freevspro&utm_campaign=freeplugin"); ?>">
                                                        <?php esc_html_e("BUY NOW", "product-feed-manager-for-woocommerce"); ?>
                                                    </a>
                                                </div>
                                            </div>

                                            <div class="dynamicprice_box d-none" boxperiod="yearly" boxdomain="5">
                                                <p class="card-text">
                                                    <?php esc_html_e("5 Active Website", "product-feed-manager-for-woocommerce"); ?>
                                                </p>
                                                <div class="card-price">
                                                    <?php esc_html_e("$149.00/", "product-feed-manager-for-woocommerce"); ?><span><?php esc_html_e("year", "product-feed-manager-for-woocommerce"); ?></span>
                                                </div>
                                                <div class="slash-price">
                                                    <?php esc_html_e("Regular Price: ", "product-feed-manager-for-woocommerce"); ?><span><?php esc_html_e("$298.00/year", "product-feed-manager-for-woocommerce"); ?></span>
                                                </div>
                                                <div class="offer-price">
                                                    <?php esc_html_e("50% Off", "product-feed-manager-for-woocommerce"); ?>
                                                </div>

                                                <div class="getstarted-btn">
                                                    <a class="btn btn-secondary common-btn" target="_blank" href="<?php echo esc_url_raw($this->convositeurl . "/checkout/?pid=wpPFM_PY5&utm_source=in_app&utm_medium=freevspro&utm_campaign=freeplugin"); ?>">
                                                        <?php esc_html_e("BUY NOW", "product-feed-manager-for-woocommerce"); ?>
                                                    </a>
                                                </div>
                                            </div>

                                            <div class="dynamicprice_box d-none" boxperiod="yearly" boxdomain="10">
                                                <p class="card-text">
                                                    <?php esc_html_e("10 Active Website", "product-feed-manager-for-woocommerce"); ?>
                                                </p>
                                                <div class="card-price">
                                                    <?php esc_html_e("$249.00/", "product-feed-manager-for-woocommerce"); ?><span><?php esc_html_e("year", "product-feed-manager-for-woocommerce"); ?></span>
                                                </div>
                                                <div class="slash-price">
                                                    <?php esc_html_e("Regular Price: ", "product-feed-manager-for-woocommerce"); ?><span><?php esc_html_e("$498.00/year", "product-feed-manager-for-woocommerce"); ?></span>
                                                </div>
                                                <div class="offer-price">
                                                    <?php esc_html_e("50% Off", "product-feed-manager-for-woocommerce"); ?>
                                                </div>

                                                <div class="getstarted-btn">
                                                    <a class="btn btn-secondary common-btn" target="_blank" href="<?php echo esc_url_raw($this->convositeurl . "/checkout/?pid=wpPFM_PY10&utm_source=in_app&utm_medium=freevspro&utm_campaign=freeplugin"); ?>">
                                                        <?php esc_html_e("BUY NOW", "product-feed-manager-for-woocommerce"); ?>
                                                    </a>
                                                </div>
                                            </div>
                                            <ul class="feature-listing custom-scrollbar">
                                                <li>
                                                    <div class="tooltip-box">
                                                        <button type="button" class="btn btn-secondary tooltipc remove" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-custom-class="custom-tooltip">
                                                            <?php esc_html_e(" Everything in Starter", "product-feed-manager-for-woocommerce"); ?>
                                                        </button>
                                                    </div>
                                                </li>
                                                <span>&#43;</span>
                                                <li>
                                                    <div class="tooltip-box">
                                                        <button type="button" class="btn btn-secondary tooltipc pointer" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-custom-class="custom-tooltip" data-bs-title="Allows unlimited products sync.">
                                                            <?php esc_html_e(" upto 5000 products sync", "product-feed-manager-for-woocommerce"); ?>
                                                        </button>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="tooltip-box">
                                                        <button type="button" class="btn btn-secondary tooltipc pointer" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-custom-class="custom-tooltip" data-bs-title="Compatible with 50+ plugins so that you can sync any attribute you want. Reach out if you don't find specific attributes.">
                                                            <?php esc_html_e(" 50+ plugins compatibility", "product-feed-manager-for-woocommerce"); ?>
                                                        </button>
                                                    </div>
                                                </li>
                                                <span>&#43;</span>
                                                <li>
                                                    <div class="tooltip-box">
                                                        <button type="button" class="btn btn-secondary tooltipc pointer" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-custom-class="custom-tooltip" data-bs-title="A dedicated customer success manager ensures that everything is set up accurately and helps you solve any issue that you may face.">
                                                            <?php esc_html_e(" Dedicated Customer Success Manager", "product-feed-manager-for-woocommerce"); ?>
                                                        </button>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="tooltip-box">
                                                        <button type="button" class="btn btn-secondary tooltipc remove" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-custom-class="custom-tooltip">
                                                            <?php esc_html_e(" Priority support", "product-feed-manager-for-woocommerce"); ?>
                                                        </button>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="tooltip-box">
                                                        <button type="button" class="btn btn-secondary tooltipc pointer" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-custom-class="custom-tooltip" data-bs-title="Analytics and Ads management becomes complicated some time. Our team of expert helps you in set up everything and performs audit so that you focus on the things that matter for your business.">
                                                            <?php esc_html_e(" Free setup and audit", "product-feed-manager-for-woocommerce"); ?>
                                                        </button>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="tooltip-box">
                                                        <button type="button" class="btn btn-secondary tooltipc pointer" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-custom-class="custom-tooltip" data-bs-title="Worrying about lower ROAS or how to get started? Our team helps you define the right strategy for your business.">
                                                            <?php esc_html_e("Free consultation for campaign management", "product-feed-manager-for-woocommerce"); ?>
                                                        </button>
                                                    </div>
                                                </li>
                                            </ul>
                                            <div class="features-link">
                                                <a href="#seeallfeatures"><?php esc_html_e("Compare All Features", "product-feed-manager-for-woocommerce"); ?></a>
                                            </div>

                                            <div class="dynamicprice_box" boxperiod="yearly" boxdomain="1">
                                                <div class="getstarted-btn">
                                                    <a class="btn btn-secondary common-btn" target="_blank" href="<?php echo esc_url_raw($this->convositeurl . "/checkout/?pid=wpPFM_PY1&utm_source=in_app&utm_medium=freevspro&utm_campaign=freeplugin"); ?>">
                                                        <?php esc_html_e("BUY NOW", "product-feed-manager-for-woocommerce"); ?>
                                                    </a>
                                                </div>
                                            </div>

                                            <div class="dynamicprice_box d-none" boxperiod="yearly" boxdomain="5">
                                                <div class="getstarted-btn">
                                                    <a class="btn btn-secondary common-btn" target="_blank" href="<?php echo esc_url_raw($this->convositeurl . "/checkout/?pid=wpPFM_PY5&utm_source=in_app&utm_medium=freevspro&utm_campaign=freeplugin"); ?>">
                                                        <?php esc_html_e("BUY NOW", "product-feed-manager-for-woocommerce"); ?>
                                                    </a>
                                                </div>
                                            </div>

                                            <div class="dynamicprice_box d-none" boxperiod="yearly" boxdomain="10">
                                                <div class="getstarted-btn">
                                                    <a class="btn btn-secondary common-btn" target="_blank" href="<?php echo esc_url_raw($this->convositeurl . "/checkout/?pid=wpPFM_PY10&utm_source=in_app&utm_medium=freevspro&utm_campaign=freeplugin"); ?>">
                                                        <?php esc_html_e("BUY NOW", "product-feed-manager-for-woocommerce"); ?>
                                                    </a>
                                                </div>
                                            </div>
                                            <!-- <div class="dynamicprice_box d-none" boxperiod="yearly" boxdomain="10+">
                                        <p class="card-text contactus">
                                           
                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#staticBackdrop">
                                                Contact Us
                                            </button>
                                        </p>
                                    </div> -->
                                            <div class="popular-plan">
                                                <p><?php esc_html_e("Most Popular", "product-feed-manager-for-woocommerce"); ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-4 col-lg-4 col-md-6 col-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <?php esc_html_e("Starter", "product-feed-manager-for-woocommerce"); ?>
                                            </h5>

                                            <div class="dynamicprice_box" boxperiod="yearly" boxdomain="1">
                                                <p class="card-text">
                                                    <?php esc_html_e("1 Active Website", "product-feed-manager-for-woocommerce"); ?>
                                                </p>
                                                <div class="card-price">
                                                    <?php esc_html_e("$49.00/ ", "product-feed-manager-for-woocommerce"); ?><span><?php esc_html_e("year", "product-feed-manager-for-woocommerce"); ?></span>
                                                </div>
                                                <div class="slash-price">
                                                    <?php esc_html_e("Regular Price: ", "product-feed-manager-for-woocommerce"); ?><span><?php esc_html_e("$98.00/year", "product-feed-manager-for-woocommerce"); ?></span>
                                                </div>
                                                <div class="offer-price">
                                                    <?php esc_html_e("50% Off", "product-feed-manager-for-woocommerce"); ?>
                                                </div>

                                                <div class="getstarted-btn">
                                                    <a class="btn btn-secondary common-btn" target="_blank" href="<?php echo esc_url_raw($this->convositeurl . "/checkout/?pid=wpPFM_SY1&utm_source=in_app&utm_medium=freevspro&utm_campaign=freeplugin"); ?>">
                                                        <?php esc_html_e("BUY NOW", "product-feed-manager-for-woocommerce"); ?>
                                                    </a>
                                                </div>
                                            </div>

                                            <div class="dynamicprice_box d-none" boxperiod="yearly" boxdomain="5">
                                                <p class="card-text">
                                                    <?php esc_html_e("5 Active Website", "product-feed-manager-for-woocommerce"); ?>
                                                </p>
                                                <div class="card-price">
                                                    <?php esc_html_e("$99.00/", "product-feed-manager-for-woocommerce"); ?><span><?php esc_html_e("year", "product-feed-manager-for-woocommerce"); ?></span>
                                                </div>
                                                <div class="slash-price">
                                                    <?php esc_html_e("Regular Price: ", "product-feed-manager-for-woocommerce"); ?><span><?php esc_html_e("$198.00/year", "product-feed-manager-for-woocommerce"); ?></span>
                                                </div>
                                                <div class="offer-price">
                                                    <?php esc_html_e("50% Off", "product-feed-manager-for-woocommerce"); ?>
                                                </div>

                                                <div class="getstarted-btn">
                                                    <a class="btn btn-secondary common-btn" target="_blank" href="<?php echo esc_url_raw($this->convositeurl . "/checkout/?pid=wpPFM_SY5&utm_source=in_app&utm_medium=freevspro&utm_campaign=freeplugin"); ?>">
                                                        <?php esc_html_e("BUY NOW", "product-feed-manager-for-woocommerce"); ?>
                                                    </a>
                                                </div>
                                            </div>

                                            <div class="dynamicprice_box d-none" boxperiod="yearly" boxdomain="10">
                                                <p class="card-text">
                                                    <?php esc_html_e("10 Active Website", "product-feed-manager-for-woocommerce"); ?>
                                                </p>
                                                <div class="card-price">
                                                    <?php esc_html_e("$199.00/ ", "product-feed-manager-for-woocommerce"); ?><span><?php esc_html_e("year", "product-feed-manager-for-woocommerce"); ?></span>
                                                </div>
                                                <div class="slash-price">
                                                    <?php esc_html_e("Regular Price: ", "product-feed-manager-for-woocommerce"); ?><span><?php esc_html_e("$398.00/year", "product-feed-manager-for-woocommerce"); ?></span>
                                                </div>
                                                <div class="offer-price">
                                                    <?php esc_html_e("50% Off", "product-feed-manager-for-woocommerce"); ?>
                                                </div>

                                                <div class="getstarted-btn">
                                                    <a class="btn btn-secondary common-btn" target="_blank" href="<?php echo esc_url_raw($this->convositeurl . "/checkout/?pid=wpPFM_SY10&utm_source=in_app&utm_medium=freevspro&utm_campaign=freeplugin"); ?>">
                                                        <?php esc_html_e("BUY NOW", "product-feed-manager-for-woocommerce"); ?>
                                                    </a>
                                                </div>
                                            </div>
                                            <!-- <div class="dynamicprice_box d-none" boxperiod="yearly" boxdomain="10+">
                                        <p class="card-text contactus">
                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#staticBackdrop">
                                                <?php esc_html_e("Contact Us", "product-feed-manager-for-woocommerce"); ?>
                                            </button>
                                        </p>
                                    </div> -->

                                            <ul class="feature-listing custom-scrollbar">
                                                <li>
                                                    <div class="tooltip-box">
                                                        <button type="button" class="btn btn-secondary tooltipc remove" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-custom-class="custom-tooltip">
                                                            <?php esc_html_e("Everything in Free", "product-feed-manager-for-woocommerce"); ?>
                                                        </button>
                                                    </div>
                                                </li>
                                                <span>&#43;</span>
                                                
                                                <li>
                                                    <div class="tooltip-box">
                                                        <button type="button" class="btn btn-secondary tooltipc pointer" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-custom-class="custom-tooltip" data-bs-title="Set up high quality product feed for Ad Channels like Google, Facebook and Tiktok.">
                                                            <?php esc_html_e(" Product feed for 3 Ad Channels", "product-feed-manager-for-woocommerce"); ?>
                                                        </button>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="tooltip-box">
                                                        <button type="button" class="btn btn-secondary tooltipc pointer" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-custom-class="custom-tooltip" data-bs-title="Allows upto 500 product sync.">
                                                            <?php esc_html_e(" Upto 500 products sync limit", "product-feed-manager-for-woocommerce"); ?>
                                                        </button>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="tooltip-box">
                                                        <button type="button" class="btn btn-secondary tooltipc pointer" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-custom-class="custom-tooltip" data-bs-title="Keep your product details up to date in Google Merchant Center, Facebook Catalog and TikTok Catalog. Set time interval for auto product sync.">
                                                            <?php esc_html_e(" Schedule product sync", "product-feed-manager-for-woocommerce"); ?>
                                                        </button>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="tooltip-box">
                                                        <button type="button" class="btn btn-secondary tooltipc pointer" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-custom-class="custom-tooltip" data-bs-title="Create and manage campaigns based on feeds directly in Google Ads.">
                                                            <?php esc_html_e(" Feed based Camapign Management", "product-feed-manager-for-woocommerce"); ?>
                                                        </button>
                                                    </div>
                                                </li>
                                                <span>&#43;</span>                                                
                                                <li>
                                                    <div class="tooltip-box">
                                                        <button type="button" class="btn btn-secondary tooltipc pointer" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-custom-class="custom-tooltip" data-bs-title="Create and manage Google Ads performance max campaigns and increase ROAS.">
                                                            <?php esc_html_e(" Product Ads Campaign Management", "product-feed-manager-for-woocommerce"); ?>
                                                        </button>
                                                    </div>
                                                </li>
                                            </ul>
                                            <div class="features-link">
                                                <a href="#seeallfeatures"><?php esc_html_e("Compare All Features", "product-feed-manager-for-woocommerce"); ?></a>
                                            </div>

                                            <div class="dynamicprice_box" boxperiod="yearly" boxdomain="1">

                                                <div class="getstarted-btn">
                                                    <a class="btn btn-secondary common-btn" target="_blank" href="<?php echo esc_url_raw($this->convositeurl . "/checkout/?pid=wpPFM_SY1&utm_source=in_app&utm_medium=freevspro&utm_campaign=freeplugin"); ?>">
                                                        <?php esc_html_e("BUY NOW", "product-feed-manager-for-woocommerce"); ?>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="dynamicprice_box d-none" boxperiod="yearly" boxdomain="5">

                                                <div class="getstarted-btn">
                                                    <a class="btn btn-secondary common-btn" target="_blank" href="<?php echo esc_url_raw($this->convositeurl . "/checkout/?pid=wpPFM_SY5&utm_source=in_app&utm_medium=freevspro&utm_campaign=freeplugin"); ?>">
                                                        <?php esc_html_e("BUY NOW", "product-feed-manager-for-woocommerce"); ?>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="dynamicprice_box d-none" boxperiod="yearly" boxdomain="10">

                                                <div class="getstarted-btn">
                                                    <a class="btn btn-secondary common-btn" target="_blank" href="<?php echo esc_url_raw($this->convositeurl . "/checkout/?pid=wpPFM_SY10&utm_source=in_app&utm_medium=freevspro&utm_campaign=freeplugin"); ?>">
                                                        <?php esc_html_e("BUY NOW", "product-feed-manager-for-woocommerce"); ?>
                                                    </a>
                                                </div>
                                            </div>
                                            <!-- <div class="dynamicprice_box d-none" boxperiod="yearly" boxdomain="10+">
                                        <p class="card-text contactus">
                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#staticBackdrop">
                                                Contact Us
                                            </button>
                                        </p>
                                    </div> -->
                                            <div class="popular-plan">
                                                <p><?php esc_html_e("Most Popular", "product-feed-manager-for-woocommerce"); ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                            </div>
                        </div>


                    </div>
                    <div class="moneyback-badge" data-aos="fade-right" data-aos-delay="50">
                        <img src="<?php echo esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/money-back.png'); ?>" alt="Money Back Badge" class="img-fluid">
                    </div>
                </div>
                <!-- one stop section -->
                <div class="onestop-area">
                    <div class="container">
                        <div class="row">
                            <div class="col-12">
                                <div class="title-text">
                                    <p> <?php esc_html_e("50,000+ E-commerce Businesses Use Conversios To Scale Faster as One Stop Solution to", "product-feed-manager-for-woocommerce"); ?>
                                        <?php esc_html_e(" Save Time, Efforts & Costs", "product-feed-manager-for-woocommerce"); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Compare feature -->
                <div class="comparefeature-wholebox" id="seeallfeatures">
                    <div class="comparefeature-area space">
                        <div class="container-full">
                            <div class="row">
                                <div class="col-12">
                                    <div class="title-text">
                                        <h2> <strong><?php esc_html_e("Comprehensive Feature", "product-feed-manager-for-woocommerce"); ?></strong><?php esc_html_e(" Comparison", "product-feed-manager-for-woocommerce"); ?>
                                        </h2>
                                        <h3><?php esc_html_e("Explore our solutions all features in detail", "product-feed-manager-for-woocommerce"); ?>
                                        </h3>
                                    </div>
                                </div>
                            </div>
                            <div class="comparetable-box">

                                <div class="row">
                                    <div class="col-12">
                                        <div class="table-responsive custom-scrollbar">
                                            <table id="sticky-header-tbody-id" class="feature-table table ">
                                                <thead id="con_stick_this">
                                                    <tr>
                                                        <th scope="col" class="th-data">
                                                            <div class="feature-box">
                                                                <div class="card">
                                                                    <div class="card-body">
                                                                        <div class="card-icon">
                                                                            <img src="<?php echo esc_url_raw(CONVPFM_ENHANCAD_PLUGIN_URL . '/admin/images/pricing-privacy.png'); ?>" class="img-fluid">
                                                                        </div>
                                                                        <h5 class="card-title">
                                                                            <?php esc_html_e("100% No Risk ", "product-feed-manager-for-woocommerce"); ?><br>
                                                                            <?php esc_html_e("Moneyback Gurantee", "product-feed-manager-for-woocommerce"); ?>
                                                                        </h5>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </th>
                                                        <th scope="col" class="thd-data">
                                                            <div class="feature-box">
                                                                <div class="dynamicprice_box" boxperiod="yearly" boxdomain="1">
                                                                    <div class="title card-title">
                                                                        <?php esc_html_e("Enterprise", "product-feed-manager-for-woocommerce"); ?>
                                                                    </div>
                                                                    <p class="sub-title card-text">
                                                                        <?php esc_html_e("1 Active Website", "product-feed-manager-for-woocommerce"); ?>
                                                                    </p>
                                                                    <div class="strike-price">
                                                                        <?php esc_html_e("Regular Price: ", "product-feed-manager-for-woocommerce"); ?><span>
                                                                            <?php esc_html_e("$198.00", "product-feed-manager-for-woocommerce"); ?></span>
                                                                    </div>
                                                                    <div class="price">
                                                                        <?php esc_html_e("$99.00/ ", "product-feed-manager-for-woocommerce"); ?><span><?php esc_html_e("year", "product-feed-manager-for-woocommerce"); ?></span>
                                                                    </div>
                                                                    <div class="offer-price">
                                                                        <?php esc_html_e("Flat 50% Off Applied ", "product-feed-manager-for-woocommerce"); ?>
                                                                    </div>

                                                                    <div class="getstarted-btn get-it-now">
                                                                        <a class="label btn btn-secondary common-btn" target="_blank" href="<?php echo esc_url_raw($this->convositeurl . "/checkout/?pid=planDpf_1_y&utm_source=in_app&utm_medium=freevspro&utm_campaign=freeplugin"); ?>">
                                                                            <?php esc_html_e("Get It Now", "product-feed-manager-for-woocommerce"); ?>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                                <div class="dynamicprice_box d-none" boxperiod="yearly" boxdomain="5">
                                                                    <div class="title card-title">
                                                                        <?php esc_html_e("Enterprise", "product-feed-manager-for-woocommerce"); ?>
                                                                    </div>
                                                                    <p class="sub-title card-text">
                                                                        <?php esc_html_e("5 Active Website", "product-feed-manager-for-woocommerce"); ?>
                                                                    </p>
                                                                    <div class="strike-price">
                                                                        <?php esc_html_e("Regular Price: ", "product-feed-manager-for-woocommerce"); ?><span>
                                                                            <?php esc_html_e("$398.00", "product-feed-manager-for-woocommerce"); ?></span>
                                                                    </div>
                                                                    <div class="price">
                                                                        <?php esc_html_e("$199.00/", "product-feed-manager-for-woocommerce"); ?><span><?php esc_html_e("year", "product-feed-manager-for-woocommerce"); ?></span>
                                                                    </div>
                                                                    <div class="offer-price">
                                                                        <?php esc_html_e("Flat 50% Off Applied ", "product-feed-manager-for-woocommerce"); ?>
                                                                    </div>


                                                                    <div class="getstarted-btn get-it-now">
                                                                        <a class="label btn btn-secondary common-btn" target="_blank" href="<?php echo esc_url_raw($this->convositeurl . "/checkout/?pid=planDpf_2_y&utm_source=in_app&utm_medium=freevspro&utm_campaign=freeplugin"); ?>">
                                                                            <?php esc_html_e("Get It Now", "product-feed-manager-for-woocommerce"); ?>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                                <div class="dynamicprice_box d-none" boxperiod="yearly" boxdomain="10">
                                                                    <div class="title card-title">
                                                                        <?php esc_html_e("Enterprise", "product-feed-manager-for-woocommerce"); ?>
                                                                    </div>
                                                                    <p class="sub-title card-text">
                                                                        <?php esc_html_e("10 Active Website", "product-feed-manager-for-woocommerce"); ?>
                                                                    </p>
                                                                    <div class="strike-price">
                                                                        <?php esc_html_e("Regular Price: ", "product-feed-manager-for-woocommerce"); ?><span>
                                                                            <?php esc_html_e("$598.00", "product-feed-manager-for-woocommerce"); ?></span>
                                                                    </div>
                                                                    <div class="price">
                                                                        <?php esc_html_e("$299.00/", "product-feed-manager-for-woocommerce"); ?><span><?php esc_html_e("year", "product-feed-manager-for-woocommerce"); ?></span>
                                                                    </div>
                                                                    <div class="offer-price">
                                                                        <?php esc_html_e("Flat 50% Off Applied ", "product-feed-manager-for-woocommerce"); ?>
                                                                    </div>

                                                                    <div class="getstarted-btn get-it-now">
                                                                        <a class="label btn btn-secondary common-btn" target="_blank" href="<?php echo esc_url_raw($this->convositeurl . "/checkout/?pid=planDpf_3_y&utm_source=in_app&utm_medium=freevspro&utm_campaign=freeplugin"); ?>">
                                                                            <?php esc_html_e("Get It Now", "product-feed-manager-for-woocommerce"); ?>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                                <!-- <div class="dynamicprice_box d-none" boxperiod="yearly"
                                                                boxdomain="10+">
                                                                <div class="title card-title">Enterprise</div>
                                                                <p class="card-text contactus">
                                                                  
                                                                    <button type="button" class="btn btn-primary"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#staticBackdrop">
                                                                        Contact Us
                                                                    </button>
                                                                </p>

                                                            </div> -->
                                                            </div>
                                                        </th>
                                                        <th scope="col" class="thd-data">
                                                            <div class="feature-box">
                                                                <div class="dynamicprice_box" boxperiod="yearly" boxdomain="1">
                                                                    <div class="title card-title">
                                                                        <?php esc_html_e("Professional", "product-feed-manager-for-woocommerce"); ?>
                                                                    </div>
                                                                    <p class="sub-title card-text">
                                                                        <?php esc_html_e("1 Active Website", "product-feed-manager-for-woocommerce"); ?>
                                                                    </p>
                                                                    <div class="strike-price">
                                                                        <?php esc_html_e("Regular Price: ", "product-feed-manager-for-woocommerce"); ?><span>
                                                                            <?php esc_html_e("$198.00", "product-feed-manager-for-woocommerce"); ?></span>
                                                                    </div>
                                                                    <div class="price">
                                                                        <?php esc_html_e("$69.00/ ", "product-feed-manager-for-woocommerce"); ?><span><?php esc_html_e("year", "product-feed-manager-for-woocommerce"); ?></span>
                                                                    </div>
                                                                    <div class="offer-price">
                                                                        <?php esc_html_e("Flat 50% Off Applied ", "product-feed-manager-for-woocommerce"); ?>
                                                                    </div>
                                                                    <div class="getstarted-btn get-it-now">
                                                                        <a class="label btn btn-secondary common-btn" target="_blank" href="<?php echo esc_url_raw($this->convositeurl . "/checkout/?pid=wpPFM_PY1&utm_source=in_app&utm_medium=freevspro&utm_campaign=freeplugin"); ?>">
                                                                            <?php esc_html_e("Get It Now", "product-feed-manager-for-woocommerce"); ?>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                                <div class="dynamicprice_box d-none" boxperiod="yearly" boxdomain="5">

                                                                    <div class="title card-title">
                                                                        <?php esc_html_e("Professional", "product-feed-manager-for-woocommerce"); ?>
                                                                    </div>
                                                                    <p class="sub-title card-text">
                                                                        <?php esc_html_e("5 Active Website", "product-feed-manager-for-woocommerce"); ?>
                                                                    </p>
                                                                    <div class="strike-price">
                                                                        <?php esc_html_e("Regular Price: ", "product-feed-manager-for-woocommerce"); ?><span>
                                                                            <?php esc_html_e("$298.00", "product-feed-manager-for-woocommerce"); ?></span>
                                                                    </div>
                                                                    <div class="price">
                                                                        <?php esc_html_e("$149.00/ ", "product-feed-manager-for-woocommerce"); ?><span><?php esc_html_e("year", "product-feed-manager-for-woocommerce"); ?></span>
                                                                    </div>
                                                                    <div class="offer-price">
                                                                        <?php esc_html_e("Flat 50% Off Applied ", "product-feed-manager-for-woocommerce"); ?>
                                                                    </div>
                                                                    <div class="getstarted-btn get-it-now">
                                                                        <a class="label btn btn-secondary common-btn" target="_blank" href="<?php echo esc_url_raw($this->convositeurl . "/checkout/?pid=wpPFM_PY5&utm_source=in_app&utm_medium=freevspro&utm_campaign=freeplugin"); ?>">
                                                                            <?php esc_html_e("Get It Now", "product-feed-manager-for-woocommerce"); ?>
                                                                        </a>
                                                                    </div>


                                                                </div>
                                                                <div class="dynamicprice_box d-none" boxperiod="yearly" boxdomain="10">

                                                                    <div class="title card-title">
                                                                        <?php esc_html_e("Professional", "product-feed-manager-for-woocommerce"); ?>
                                                                    </div>
                                                                    <p class="sub-title card-text">
                                                                        <?php esc_html_e("10 Active Website", "product-feed-manager-for-woocommerce"); ?>
                                                                    </p>
                                                                    <div class="strike-price">
                                                                        <?php esc_html_e("Regular Price: ", "product-feed-manager-for-woocommerce"); ?><span>
                                                                            <?php esc_html_e("$498.00", "product-feed-manager-for-woocommerce"); ?></span>
                                                                    </div>
                                                                    <div class="price">
                                                                        <?php esc_html_e("$249.00/", "product-feed-manager-for-woocommerce"); ?><span><?php esc_html_e("year", "product-feed-manager-for-woocommerce"); ?></span>
                                                                    </div>
                                                                    <div class="offer-price">
                                                                        <?php esc_html_e("Flat 50% Off Applied ", "product-feed-manager-for-woocommerce"); ?>
                                                                    </div>
                                                                    <div class="getstarted-btn get-it-now">
                                                                        <a class="label btn btn-secondary common-btn" target="_blank" href="<?php echo esc_url_raw($this->convositeurl . "/checkout/?pid=wpPFM_PY10&utm_source=in_app&utm_medium=freevspro&utm_campaign=freeplugin"); ?>">
                                                                            <?php esc_html_e("Get It Now", "product-feed-manager-for-woocommerce"); ?>
                                                                        </a>
                                                                    </div>

                                                                </div>
                                                                <!-- <div class="dynamicprice_box d-none" boxperiod="yearly"
                                                                boxdomain="10+">
                                                                <div class="title card-title">Professional</div>
                                                                <p class="card-text contactus">
                                                                   
                                                                    <button type="button" class="btn btn-primary"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#staticBackdrop">
                                                                        Contact Us
                                                                    </button>
                                                                </p>

                                                            </div> -->
                                                            </div>
                                                        </th>
                                                        <th scope="col" class="thd-data">
                                                            <div class="feature-box">
                                                                <div class="dynamicprice_box" boxperiod="yearly" boxdomain="1">

                                                                    <div class="title card-title">
                                                                        <?php esc_html_e("Starter", "product-feed-manager-for-woocommerce"); ?>
                                                                    </div>
                                                                    <p class="sub-title card-text">
                                                                        <?php esc_html_e("1 Active Website", "product-feed-manager-for-woocommerce"); ?>
                                                                    </p>
                                                                    <div class="strike-price">
                                                                        <?php esc_html_e("Regular Price: ", "product-feed-manager-for-woocommerce"); ?><span>
                                                                            <?php esc_html_e("$98.00", "product-feed-manager-for-woocommerce"); ?></span>
                                                                    </div>
                                                                    <div class="price">
                                                                        <?php esc_html_e("$49.00/", "product-feed-manager-for-woocommerce"); ?><span><?php esc_html_e("year", "product-feed-manager-for-woocommerce"); ?></span>
                                                                    </div>
                                                                    <div class="offer-price">
                                                                        <?php esc_html_e("Flat 50% Off Applied ", "product-feed-manager-for-woocommerce"); ?>
                                                                    </div>
                                                                    <div class="getstarted-btn get-it-now">
                                                                        <a class="label btn btn-secondary common-btn" target="_blank" href="<?php echo esc_url_raw($this->convositeurl . "/checkout/?pid=wpPFM_SY1&utm_source=in_app&utm_medium=freevspro&utm_campaign=freeplugin"); ?>">
                                                                            <?php esc_html_e("Get It Now", "product-feed-manager-for-woocommerce"); ?>
                                                                        </a>
                                                                    </div>


                                                                </div>
                                                                <div class="dynamicprice_box d-none" boxperiod="yearly" boxdomain="5">

                                                                    <div class="title card-title">
                                                                        <?php esc_html_e("Starter", "product-feed-manager-for-woocommerce"); ?>
                                                                    </div>
                                                                    <p class="sub-title card-text">
                                                                        <?php esc_html_e("5 Active Website", "product-feed-manager-for-woocommerce"); ?>
                                                                    </p>
                                                                    <div class="strike-price">
                                                                        <?php esc_html_e("Regular Price: ", "product-feed-manager-for-woocommerce"); ?><span>
                                                                            <?php esc_html_e("$198.00", "product-feed-manager-for-woocommerce"); ?></span>
                                                                    </div>
                                                                    <div class="price">
                                                                        <?php esc_html_e("$99.00/", "product-feed-manager-for-woocommerce"); ?><span><?php esc_html_e("year", "product-feed-manager-for-woocommerce"); ?></span>
                                                                    </div>
                                                                    <div class="offer-price">
                                                                        <?php esc_html_e("Flat 50% Off Applied ", "product-feed-manager-for-woocommerce"); ?>
                                                                    </div>
                                                                    <div class="getstarted-btn get-it-now">
                                                                        <a class="label btn btn-secondary common-btn" target="_blank" href="<?php echo esc_url_raw($this->convositeurl . "/checkout/?pid=wpPFM_SY5&utm_source=in_app&utm_medium=freevspro&utm_campaign=freeplugin"); ?>">
                                                                            <?php esc_html_e("Get It Now", "product-feed-manager-for-woocommerce"); ?>
                                                                        </a>
                                                                    </div>

                                                                </div>
                                                                <div class="dynamicprice_box d-none" boxperiod="yearly" boxdomain="10">

                                                                    <div class="title card-title">
                                                                        <?php esc_html_e("Starter", "product-feed-manager-for-woocommerce"); ?>
                                                                    </div>
                                                                    <p class="sub-title card-text">
                                                                        <?php esc_html_e("10 Active Website", "product-feed-manager-for-woocommerce"); ?>
                                                                    </p>
                                                                    <div class="strike-price">
                                                                        <?php esc_html_e("Regular Price: ", "product-feed-manager-for-woocommerce"); ?><span>
                                                                            <?php esc_html_e("$398.00", "product-feed-manager-for-woocommerce"); ?></span>
                                                                    </div>
                                                                    <div class="price">
                                                                        <?php esc_html_e("$199.00/", "product-feed-manager-for-woocommerce"); ?><span><?php esc_html_e("year", "product-feed-manager-for-woocommerce"); ?></span>
                                                                    </div>
                                                                    <div class="offer-price">
                                                                        <?php esc_html_e("Flat 50% Off Applied ", "product-feed-manager-for-woocommerce"); ?>
                                                                    </div>
                                                                    <div class="getstarted-btn get-it-now">
                                                                        <a class="label btn btn-secondary common-btn" target="_blank" href="<?php echo esc_url_raw($this->convositeurl . "/checkout/?pid=wpPFM_SY10&utm_source=in_app&utm_medium=freevspro&utm_campaign=freeplugin"); ?>">
                                                                            <?php esc_html_e("Get It Now", "product-feed-manager-for-woocommerce"); ?>
                                                                        </a>
                                                                    </div>

                                                                </div>
                                                                <!-- <div class="dynamicprice_box d-none" boxperiod="yearly"
                                                                boxdomain="10+">
                                                                <div class="title card-title">Starter</div>
                                                                <p class="card-text contactus">
                                                                  
                                                                    <button type="button" class="btn btn-primary"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#staticBackdrop">
                                                                        Contact Us
                                                                    </button>
                                                                </p>

                                                            </div> -->
                                                            </div>
                                        </div>
                                        </th>
                                        <th scope="col" class="thd-data">
                                            <div class="feature-box">
                                                <div class="dynamicprice_box" boxperiod="yearly" boxdomain="1">

                                                    <div class="title card-title">
                                                        <?php esc_html_e("Free", "product-feed-manager-for-woocommerce"); ?>
                                                    </div>
                                                    <p class="sub-title card-text">
                                                        <?php esc_html_e("1 Active Website", "product-feed-manager-for-woocommerce"); ?>
                                                    </p>
                                                    <div class="strike-price">
                                                        <?php esc_html_e("Regular Price: ", "product-feed-manager-for-woocommerce"); ?><span>
                                                            <?php esc_html_e("$00.00", "product-feed-manager-for-woocommerce"); ?></span>
                                                    </div>
                                                    <div class="price">
                                                        <?php esc_html_e("$00.00/", "product-feed-manager-for-woocommerce"); ?><span><?php esc_html_e("year", "product-feed-manager-for-woocommerce"); ?></span>
                                                    </div>
                                                    <div class="offer-price" style="opacity:0">
                                                        <?php esc_html_e("Flat 50% Off Applied ", "product-feed-manager-for-woocommerce"); ?>
                                                    </div>
                                                    <div class="getstarted-btn get-it-now">
                                                        <a class="label btn btn-secondary common-btn" target="_blank" href="<?php echo esc_url_raw("https://wordpress.org/plugins/product-feed-manager-for-woocommerce/"); ?>">
                                                            <?php esc_html_e("Get It Now", "product-feed-manager-for-woocommerce"); ?>
                                                        </a>
                                                    </div>


                                                </div>
                                                <div class="dynamicprice_box d-none" boxperiod="yearly" boxdomain="5">
                                                    <div class="title card-title">
                                                        <?php esc_html_e("Free", "product-feed-manager-for-woocommerce"); ?>
                                                    </div>
                                                    <p class="sub-title card-text">
                                                        <?php esc_html_e("5 Active Website", "product-feed-manager-for-woocommerce"); ?>
                                                    </p>
                                                    <div class="strike-price">
                                                        <?php esc_html_e("Regular Price: ", "product-feed-manager-for-woocommerce"); ?><span>
                                                            <?php esc_html_e("$00.00", "product-feed-manager-for-woocommerce"); ?></span>
                                                    </div>
                                                    <div class="price">
                                                        <?php esc_html_e("$00.00/", "product-feed-manager-for-woocommerce"); ?><span><?php esc_html_e("year", "product-feed-manager-for-woocommerce"); ?></span>
                                                    </div>
                                                    <div class="offer-price" style="opacity:0">
                                                        <?php esc_html_e("Flat 50% Off Applied ", "product-feed-manager-for-woocommerce"); ?>
                                                    </div>
                                                    <div class="getstarted-btn get-it-now">
                                                        <a class="label btn btn-secondary common-btn" target="_blank" href="<?php echo esc_url_raw("https://wordpress.org/plugins/product-feed-manager-for-woocommerce/"); ?>">
                                                            <?php esc_html_e("Get It Now", "product-feed-manager-for-woocommerce"); ?>
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="dynamicprice_box d-none" boxperiod="yearly" boxdomain="10">
                                                    <div class="title card-title">
                                                        <?php esc_html_e("Free", "product-feed-manager-for-woocommerce"); ?>
                                                    </div>
                                                    <p class="sub-title card-text">
                                                        <?php esc_html_e("10 Active Website", "product-feed-manager-for-woocommerce"); ?>
                                                    </p>
                                                    <div class="strike-price">
                                                        <?php esc_html_e("Regular Price: ", "product-feed-manager-for-woocommerce"); ?><span>
                                                            <?php esc_html_e("$00.00", "product-feed-manager-for-woocommerce"); ?></span>
                                                    </div>
                                                    <div class="price">
                                                        <?php esc_html_e("$00.00/", "product-feed-manager-for-woocommerce"); ?><span><?php esc_html_e("year", "product-feed-manager-for-woocommerce"); ?></span>
                                                    </div>
                                                    <div class="offer-price" style="opacity:0">
                                                        <?php esc_html_e("Flat 50% Off Applied ", "product-feed-manager-for-woocommerce"); ?>
                                                    </div>
                                                    <div class="getstarted-btn get-it-now">
                                                        <a class="label btn btn-secondary common-btn" target="_blank" href="<?php echo esc_url_raw("https://wordpress.org/plugins/product-feed-manager-for-woocommerce/"); ?>">
                                                            <?php esc_html_e("Get It Now", "product-feed-manager-for-woocommerce"); ?>
                                                        </a>
                                                    </div>
                                                </div>
                                                <!-- <div class="dynamicprice_box d-none" boxperiod="yearly" boxdomain="10+">
                                                <div class="title card-title">Free</div>
                                                <p class="card-text contactus">
                                                   
                                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                                        data-bs-target="#staticBackdrop">
                                                        Contact Us
                                                    </button>
                                                </p>

                                            </div> -->
                                            </div>
                                        </th>




                                        </tr>
                                        </thead>
                                        <tbody>
                                           
                                            <!-- Product Feed Manager  -->
                                            <!-- 0 -->
                                            <tr class="title-row" data-title="hello">
                                                <td colspan="5" class="data">
                                                    <div class="feature-title">
                                                        <?php esc_html_e("Product Feed Manager", "product-feed-manager-for-woocommerce"); ?>
                                                    </div>
                                                </td>
                                            </tr>
                                            <!-- 1 -->
                                            <tr>
                                                <th class="th-data" scope="row">
                                                    <div class="tooltip-box">
                                                        <button type="button" class="btn btn-secondary tooltipc pointer" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-custom-class="custom-tooltip" data-bs-title="Total number of WooCommerce product sync limit.">
                                                            <?php esc_html_e("Number of products", "product-feed-manager-for-woocommerce"); ?>
                                                        </button>
                                                    </div>
                                                </th>
                                                <td>
                                                    <div class="feature-data">
                                                        <div class="items">
                                                            <b><?php esc_html_e("Unlimited", "product-feed-manager-for-woocommerce"); ?></b>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="feature-data">
                                                        <div class="items">
                                                            <b><?php esc_html_e("Upto 5000", "product-feed-manager-for-woocommerce"); ?></b>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="feature-data">
                                                        <div class="items">
                                                            <b><?php esc_html_e("Upto 500", "product-feed-manager-for-woocommerce"); ?></b>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="feature-data">
                                                        <div class="items">
                                                            <b><?php esc_html_e("Upto 100", "product-feed-manager-for-woocommerce"); ?></b>
                                                        </div>
                                                    </div>
                                                </td>




                                            </tr>

                                            <!-- 2 -->
                                            <tr>
                                                <th class="th-data" scope="row">
                                                    <div class="tooltip-box">
                                                        <button type="button" class="btn btn-secondary tooltipc remove" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-custom-class="custom-tooltip">
                                                            <?php esc_html_e("Google Shopping Feed", "product-feed-manager-for-woocommerce"); ?>
                                                        </button>
                                                    </div>
                                                </th>
                                                <td>
                                                    <div class="feature-data">
                                                        <div class="items">
                                                            <span>&#10003;</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="feature-data">
                                                        <div class="items">
                                                            <span>&#10003;</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="feature-data">
                                                        <div class="items">
                                                            <span>&#10003;</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="feature-data">
                                                        <div class="items">
                                                            <span>&#10003;</span>
                                                        </div>
                                                    </div>
                                                </td>

                                            </tr>

                                            <!-- 3 -->
                                            <!-- <tr>
                                                <th class="th-data" scope="row">
                                                    <div class="tooltip-box">
                                                        <button type="button" class="btn btn-secondary tooltipc remove" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-custom-class="custom-tooltip">
                                                            <?php esc_html_e("Facebook Catalog Feed", "product-feed-manager-for-woocommerce"); ?>
                                                        </button>
                                                    </div>
                                                </th>
                                                <td>
                                                    <div class="feature-data">
                                                        <div class="items">
                                                            <span>&#10003;</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="feature-data">
                                                        <div class="items">
                                                            <span>&#10003;</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="feature-data">
                                                        <div class="items">
                                                            <span>&#10003;</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="feature-data">
                                                        <div class="items">
                                                            <span>&#10003;</span>
                                                        </div>
                                                    </div>
                                                </td>

                                            </tr> -->

                                            <!-- 4 -->
                                            <tr>
                                                <th class="th-data" scope="row">
                                                    <div class="tooltip-box">
                                                        <button type="button" class="btn btn-secondary tooltipc remove" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-custom-class="custom-tooltip">
                                                            <?php esc_html_e("TikTok Catalog Feed", "product-feed-manager-for-woocommerce"); ?>
                                                        </button>
                                                    </div>
                                                </th>
                                                <td>
                                                    <div class="feature-data">
                                                        <div class="items">
                                                            <span>&#10003;</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="feature-data">
                                                        <div class="items">
                                                            <span>&#10003;</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="feature-data">
                                                        <div class="items">
                                                            <span>&#10003;</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="feature-data">
                                                        <div class="items">
                                                            <span>&#10003;</span>
                                                        </div>
                                                    </div>
                                                </td>

                                            </tr>

                                            <!-- 5 -->
                                            <tr>
                                                <th class="th-data" scope="row">
                                                    <div class="tooltip-box">
                                                        <button type="button" class="btn btn-secondary tooltipc pointer" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-custom-class="custom-tooltip" data-bs-title="Auto schedule product updates in ad channels.">
                                                            <?php esc_html_e("Schedule auto product sync", "product-feed-manager-for-woocommerce"); ?>
                                                        </button>
                                                    </div>
                                                </th>
                                                <td>
                                                    <div class="feature-data">
                                                        <div class="items">
                                                            <span>&#10003;</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="feature-data">
                                                        <div class="items">
                                                            <span>&#10003;</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="feature-data">
                                                        <div class="items">
                                                            <span>&#10003;</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="feature-data">
                                                        <div class="items">
                                                            <span>&#10003;</span>
                                                        </div>
                                                    </div>
                                                </td>

                                            </tr>

                                            <!-- 6 -->
                                            <!-- 7 -->
                                            <tr>
                                                <th class="th-data" scope="row">
                                                    <div class="tooltip-box">
                                                        <button type="button" class="btn btn-secondary tooltipc pointer" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-custom-class="custom-tooltip" data-bs-title="Filter your WooCommerce product to create feed.">
                                                            <?php esc_html_e("Advanced filters", "product-feed-manager-for-woocommerce"); ?>
                                                        </button>
                                                    </div>
                                                </th>
                                                <td>
                                                    <div class="feature-data">
                                                        <div class="items">
                                                            <span>&#10003;</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="feature-data">
                                                        <div class="items">
                                                            <span>&#10003;</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="feature-data">
                                                        <div class="items">
                                                            <span>&#10003;</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="feature-data">
                                                        <div class="items">
                                                            <span>&#10003;</span>
                                                        </div>
                                                    </div>
                                                </td>

                                            </tr>

                                            <!-- 8-->
                                            <tr>
                                                <th class="th-data" scope="row">
                                                    <div class="tooltip-box">
                                                        <button type="button" class="btn btn-secondary tooltipc" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-custom-class="custom-tooltip" data-bs-title="Sync handpicked products from the product grid.">
                                                            <?php esc_html_e("Select specific WooCommerce products", "product-feed-manager-for-woocommerce"); ?>
                                                        </button>
                                                    </div>
                                                </th>
                                                <td>
                                                    <div class="feature-data">
                                                        <div class="items">
                                                            <span>&#10003;</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="feature-data">
                                                        <div class="items">
                                                            <span>&#10003;</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="feature-data">
                                                        <div class="items">
                                                            <span>&#10003;</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="feature-data">
                                                        <div class="items">
                                                            <span>&#10003;</span>
                                                        </div>
                                                    </div>
                                                </td>

                                            </tr>

                                            <!--9-->
                                            <tr>
                                                <th class="th-data" scope="row">
                                                    <div class="tooltip-box">
                                                        <button type="button" class="btn btn-secondary tooltipc pointer" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-custom-class="custom-tooltip" data-bs-title="Sync product attributes from 50+ product plugins.">
                                                            <?php esc_html_e("Compatibility with 50+ product plugins", "product-feed-manager-for-woocommerce"); ?>
                                                        </button>
                                                    </div>
                                                </th>
                                                <td>
                                                    <div class="feature-data">
                                                        <div class="items">
                                                            <span>&#10003;</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="feature-data">
                                                        <div class="items">
                                                            <span>&#10003;</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="feature-data">
                                                        <div class="items">
                                                            <span>&#10003;</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="feature-data">
                                                        <div class="items">
                                                            <span>&#10003;</span>
                                                        </div>
                                                    </div>
                                                </td>

                                            </tr>

                                            <!-- Reporting & Campaing Management  -->
                                            <!-- 0 -->
                                            <tr class="title-row" data-title="hello">
                                                <td colspan="5" class="data">
                                                    <div class="feature-title">
                                                        <?php esc_html_e("Campaing Management", "product-feed-manager-for-woocommerce"); ?>
                                                    </div>
                                                </td>
                                            </tr>
                                            <!-- 1 -->

                                            <!-- 5 -->
                                            <tr>
                                                <th class="th-data" scope="row">
                                                    <div class="tooltip-box">
                                                        <button type="button" class="btn btn-secondary tooltipc pointer" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-custom-class="custom-tooltip" data-bs-title="Create and manage Google Ads performance max campaigns and increase ROAS. Create and manage campaigns based on feeds.">
                                                            <?php esc_html_e("Product Ads Campaign Management", "product-feed-manager-for-woocommerce"); ?>
                                                        </button>
                                                    </div>
                                                </th>
                                                <td>
                                                    <div class="feature-data">
                                                        <div class="items">
                                                            <span>&#10003;</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="feature-data">
                                                        <div class="items">
                                                            <span>&#10003;</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="feature-data">
                                                        <div class="items">
                                                            <span>&#10003;</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="feature-data">
                                                        <div class="items">
                                                            <span>&#10003;</span>
                                                        </div>
                                                    </div>
                                                </td>

                                            </tr>

                                            <!-- 18 buttons -->
                                            <tr>
                                                <th class="th-data" scope="row" style="border: 0px;"></th>
                                                <td style="border: 0px;">
                                                    <div class="feature-data">
                                                        <div class="dynamicprice_box" boxperiod="yearly" boxdomain="1">
                                                            <div class="getnow-btn">
                                                                <a class="btn btn-secondary getnow" index='1' target="_blank" href="<?php echo esc_url_raw($this->convositeurl . "/checkout/?pid=planDpf_1_y&utm_source=in_app&utm_medium=freevspro&utm_campaign=freeplugin"); ?>">GET
                                                                    NOW</a>
                                                            </div>
                                                        </div>
                                                        <div class="dynamicprice_box d-none" boxperiod="yearly" boxdomain="5">
                                                            <div class="getnow-btn">
                                                                <a class="btn btn-secondary getnow" index='1' target="_blank" href="<?php echo esc_url_raw($this->convositeurl . "/checkout/?pid=planDpf_2_y&utm_source=in_app&utm_medium=freevspro&utm_campaign=freeplugin"); ?>">GET
                                                                    NOW</a>
                                                            </div>
                                                        </div>
                                                        <div class="dynamicprice_box d-none" boxperiod="yearly" boxdomain="10">
                                                            <div class="getnow-btn">
                                                                <a class="btn btn-secondary getnow" index='1' target="_blank" href="<?php echo esc_url_raw($this->convositeurl . "/checkout/?pid=planDpf_3_y&utm_source=in_app&utm_medium=freevspro&utm_campaign=freeplugin"); ?>">GET
                                                                    NOW</a>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </td>
                                                <td style="border: 0px;">
                                                    <div class="feature-data">
                                                        <div class="dynamicprice_box" boxperiod="yearly" boxdomain="1">
                                                            <div class="getnow-btn">
                                                                <a class="btn btn-secondary getnow" index='1' target="_blank" href="<?php echo esc_url_raw($this->convositeurl . "/checkout/?pid=wpPFM_PY1&utm_source=in_app&utm_medium=freevspro&utm_campaign=freeplugin"); ?>">GET
                                                                    NOW</a>
                                                            </div>
                                                        </div>
                                                        <div class="dynamicprice_box d-none" boxperiod="yearly" boxdomain="5">
                                                            <div class="getnow-btn">
                                                                <a class="btn btn-secondary getnow" index='1' target="_blank" href="<?php echo esc_url_raw($this->convositeurl . "/checkout/?pid=wpPFM_PY5&utm_source=in_app&utm_medium=freevspro&utm_campaign=freeplugin"); ?>">GET
                                                                    NOW</a>
                                                            </div>
                                                        </div>
                                                        <div class="dynamicprice_box d-none" boxperiod="yearly" boxdomain="10">
                                                            <div class="getnow-btn">
                                                                <a class="btn btn-secondary getnow" index='1' target="_blank" href="<?php echo esc_url_raw($this->convositeurl . "/checkout/?pid=wpPFM_PY10&utm_source=in_app&utm_medium=freevspro&utm_campaign=freeplugin"); ?>">GET
                                                                    NOW</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td style="border: 0px;">
                                                    <div class="feature-data">
                                                        <div class="dynamicprice_box" boxperiod="yearly" boxdomain="1">
                                                            <div class="getnow-btn">
                                                                <a class="btn btn-secondary getnow" index='1' target="_blank" href="<?php echo esc_url_raw($this->convositeurl . "/checkout/?pid=wpPFM_SY1&utm_source=in_app&utm_medium=freevspro&utm_campaign=freeplugin"); ?>">GET
                                                                    NOW</a>
                                                            </div>
                                                        </div>
                                                        <div class="dynamicprice_box d-none" boxperiod="yearly" boxdomain="5">
                                                            <div class="getnow-btn">
                                                                <a class="btn btn-secondary getnow" index='1' target="_blank" href="<?php echo esc_url_raw($this->convositeurl . "/checkout/?pid=wpPFM_SY5&utm_source=in_app&utm_medium=freevspro&utm_campaign=freeplugin"); ?>">GET
                                                                    NOW</a>
                                                            </div>
                                                        </div>
                                                        <div class="dynamicprice_box d-none" boxperiod="yearly" boxdomain="10">
                                                            <div class="getnow-btn">
                                                                <a class="btn btn-secondary getnow" index='1' target="_blank" href="<?php echo esc_url_raw($this->convositeurl . "/checkout/?pid=wpPFM_SY10&utm_source=in_app&utm_medium=freevspro&utm_campaign=freeplugin"); ?>">GET
                                                                    NOW</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td style="border: 0px;">
                                                    <div class="feature-data">
                                                        <div class="dynamicprice_box" boxperiod="yearly" boxdomain="1">
                                                            <div class="getnow-btn">
                                                                <a class="btn btn-secondary getnow" index='1' target="_blank" href="<?php echo esc_url_raw("https://wordpress.org/plugins/product-feed-manager-for-woocommerce/"); ?>">GET
                                                                    NOW</a>
                                                            </div>
                                                        </div>
                                                        <div class="dynamicprice_box d-none" boxperiod="yearly" boxdomain="5">
                                                            <div class="getnow-btn">
                                                                <a class="btn btn-secondary getnow" index='1' target="_blank" href="<?php echo esc_url_raw("https://wordpress.org/plugins/product-feed-manager-for-woocommerce/"); ?>">GET
                                                                    NOW</a>
                                                            </div>
                                                        </div>
                                                        <div class="dynamicprice_box d-none" boxperiod="yearly" boxdomain="10">
                                                            <div class="getnow-btn">
                                                                <a class="btn btn-secondary getnow" index='1' target="_blank" href="<?php echo esc_url_raw("https://wordpress.org/plugins/product-feed-manager-for-woocommerce/"); ?>">GET
                                                                    NOW</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>


                                            </tr>
                                        </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>

        </div>

        <script>
            function checkperiod_domain() {

                jQuery(".dynamicprice_box").addClass("d-none");

                var yearmonth_checkbox = "monthly";
                if (jQuery("#yearmonth_checkbox").is(":checked")) {
                    yearmonth_checkbox = "yearly"
                }
                var domain_num = jQuery('input[name=inlineRadioOptions]:checked').val()
                jQuery(".dynamicprice_box").each(function() {
                    var boxperiod = jQuery(this).attr("boxperiod");
                    var boxdomain = jQuery(this).attr("boxdomain");

                    if (boxperiod == yearmonth_checkbox && boxdomain == domain_num) {
                        jQuery(this).removeClass("d-none");
                    }
                });
            }

            jQuery(function() {
                jQuery("#yearmonth_checkbox").click(function() {
                    checkperiod_domain();
                });

                jQuery("input[name=inlineRadioOptions]").change(function() {
                    checkperiod_domain();
                });

                var distance = jQuery('#con_stick_this').offset().top;
                var convpwindow = jQuery(window);
                convpwindow.scroll(function() {
                    if (convpwindow.scrollTop() >= 2040 && convpwindow.scrollTop() <= 3650) {

                        jQuery("#con_stick_this").addClass("sticky-header");
                        jQuery("#sticky-header-tbody-id").addClass("sticky-header-tbody");
                    } else {
                        jQuery("#con_stick_this").removeClass("sticky-header");
                        jQuery("#sticky-header-tbody-id").removeClass("sticky-header-tbody");
                    }
                });
            });
        </script>

        <script>
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
            const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
        </script>

<?php
    }
}
?>