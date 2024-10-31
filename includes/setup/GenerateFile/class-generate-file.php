<?php
$default           = wc_get_base_location();
$locale            = apply_filters('woocommerce_countries_base_country', $default['country']);
$channel_configs = get_option('convpfm_channels');
$channel_obj = new Convpfm_Attributes();
$countries   = $channel_obj->get_channel_countries();
$channels    = $channel_obj->get_channels($locale);
if (array_key_exists('project_hash', $_GET)) {
    $project        = Convpfm_UpdateProject::get_project_data(sanitize_text_field($_GET['project_hash']));
    $manage_project = 'yes';
}
$nonce = wp_create_nonce('convpfm_ajax_nonce');
?>
<style>
    .convpfm-product-feed-form {
        max-width: 100%;
        padding: 20px 12px 10px 20px;
        font: 13px Arial, Helvetica, sans-serif;
    }

    .image {
        width: 20px;
        height: 25px;
        top: 2.5px;
        left: 5px;
    }

    .heading {
        width: 119px;
        height: 30px;
        font-weight: 600;
        font-size: 20px;
        line-height: 30px;
        color: #2A2D2F;
        gap: 2px;
    }

    .description {
        width: 100%;
        height: 48px;
        font-size: 16px;
        font-weight: 400;
        line-height: 24px;
        color: #5F6368;
    }

    .heading-wrapper {
        width: 100%;
        height: 80px;
        gap: 1px;

    }

    .cancel {
        width: 180px;
        height: 38px;
        border-radius: 4px;
        border: 1px solid #1967D2;
        padding: 8px 16px;
        gap: 10px;
        background: white;
        color: #1967D2;
    }

    .save-cont {
        color: #FFFFFF;
        font-weight: 400;
        font-size: 14px;
        line-height: 22px;
        text-align: center;
        width: 180px;
        height: 38px;
        border-radius: 4px;
        padding: 8px 16px;
        gap: 10px;
        background-color: #1967D2;
        border: 1px solid #1967D2;
    }

    .action-button {
        gap: 10px;
    }

    .convpfm-container {
        width: 100%;
        border-radius: 8px;
        padding: 16px 24px;
        gap: 8px;
        box-shadow: 0px 1px 0px 0px #1A1A1A12 !important;
        box-shadow: 0px 1px 0px 0px #CCCCCC80 inset !important;
        box-shadow: 0px -1px 0px 0px #0000002B inset !important;
        box-shadow: -1px 0px 0px 0px #00000021 inset !important;
        box-shadow: 1px 0px 0px 0px #00000021 inset !important;
        /* background-color: white; */
        background: var(--Color-bg-surface-surface, #FFFFFF);

    }

    td {
        background-color: white;
    }

    #wpbody {
        background-color: rgb(241, 241, 241);
    }

    .convpfm-label {
        font-weight: 600 !important;
        font-size: 14px !important;
        line-height: 22px !important;
        color: #2A2D2F !important;
    }

    .product-feed_page_generate-file #wpbody-content {
        background-color: rgb(241, 241, 241);
    }

    #projectname {
        width: 365px;
        height: 40px;
        border-radius: 4px;
        border: 1px solid #CBCDD0;
        padding: 12px 16px;
        gap: 8px;
        background-color: white;
    }

    #countries,
    #channel_hash {
        width: 365px;
        height: 40px;
        border-radius: 4px;
        border: 1px solid #CBCDD0;
        padding: 8px 16px;
        background-color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    #fileformat,
    #refreshinterval {
        width: 128px;
        height: 40px;
        border-radius: 4px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: white;
        border: 1px solid #CBCDD0;
    }

    .nav-link.active {
        margin-left: 8px;
        margin-bottom: 8px;
        font-weight: 600;
        size: 15px;
        /* line-height: 22px; */
        min-width: 106px;
        height: 22px;
        color: #2A2D2F;
    }

    .nav-link {
        min-width: 88px;
        height: 22px;
        font-weight: 400;
        size: 14px;
        line-height: 22px;
        color: #5F6368;
    }

    .woo-product-feed-pro-table tr {
        border: none;
    }
    .rich-blue {
        height: 36px;
    }
</style>


<div class="wrap">
    <!-- <div class="container"></div> -->
    <div class="convpfm-product-feed-form">
        <div class="heading-wrapper">
            <div style="display: inline-block; width: 30px; height: 30px vertical-align: middle">
                <?php echo '<img class = "image" src="' . plugins_url('images/Vector.png', __FILE__) . '" alt="My Image">'; ?>
            </div>
            <div style="display: inline-block; vertical-align: middle">
                <span class="heading"><?php esc_html_e('Generate File', 'product-feed-manager-for-woocommerce'); ?></span>
            </div>
            <div>
                <span class="description"><?php esc_html_e('Manage your product feeds to keep your online store information up-to-date and accurate. This includes adding new products, updating prices, and ensuring all product details meet platform requirements. Effective feed management helps your products appear correctly on shopping channels and improves your visibility to potential customers.
', 'product-feed-manager-for-woocommerce'); ?></span>
            </div>
        </div>
        <form action="" id="myForm" method="post" name="myForm">
            <?php wp_nonce_field('convpfm_ajax_nonce'); ?>

            <div class="woo-product-feed-pro-table-wrapper">
                <div class="woo-product-feed-pro-table-left convpfm-container">
                    <ul class="nav nav-pills" id="pills-tab" role="tablist">
                        <li style="pointer-events: none;" class="nav-item" role="presentation">
                            <span class="nav-link active" id="pills-enter-feed-details-tab" data-bs-toggle="pill" data-bs-target="#pills-enter-feed-details" role="tab" aria-controls="pills-enter-feed-details" aria-selected="true">Enter File Details &nbsp; ></span>
                        </li>
                        <li style="pointer-events: none;" class="nav-item" role="presentation">
                            <span class="nav-link" id="pills-map-product-category-tab" data-bs-toggle="pill" data-bs-target="#pills-map-product-category" type="" role="tab" aria-controls="pills-map-product-category" aria-selected="false">Field Mapping &nbsp; ></span>
                        </li>
                        <li style="pointer-events: none;" class="nav-item" role="presentation">
                            <span class="nav-link" id="pills-map-product-attribute-tab" data-bs-toggle="pill" data-bs-target="#pills-map-product-attribute" type="" role="tab" aria-controls="pills-map-product-attribute" aria-selected="false">Apply Filters &nbsp; ></span>
                        </li>
                        <li style="pointer-events: none;" class="nav-item" role="presentation">
                            <span class="nav-link" id="pills-map-product-attribute-tab" data-bs-toggle="pill" data-bs-target="#pills-map-product-attribute" type="" role="tab" aria-controls="pills-map-product-attribute" aria-selected="false">Manage Feeds</span>
                        </li>
                    </ul>
                    <table class="woo-product-feed-pro-table">
                        <tbody class="woo-product-feed-pro-body">
                            <div id="projecterror"></div>
                            <tr>
                                <td width="30%"><span class="convpfm-label"><?php esc_html_e('Project name', 'product-feed-manager-for-woocommerce'); ?>:<span class="required">*</span></span></td>
                                <td>
                                    <div style="display: block;">
                                        <?php
                                        if (isset($project)) {
                                            echo "<input type=\"text\" class=\"input-field\" id=\"projectname\" name=\"projectname\" value=\"$project[projectname]\"/> <div id=\"projecterror\"></div>";
                                        } else {
                                            print '<input type="text" class="input-field" id="projectname" placeholder="Enter your project name" name="projectname"/> <div id="projecterror"></div>';
                                        }
                                        ?>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td><span class="convpfm-label"><?php esc_html_e('Country', 'product-feed-manager-for-woocommerce'); ?>:<span class="required">*</span></span></td>
                                <td>
                                    <?php
                                    if (isset($manage_project)) {
                                        // print"<select name=\"countries\" id=\"countries\" class=\"select-field\" disabled>";
                                        print '<select name="countries" id="countries" class="select-field">';
                                    } else {
                                        print '<select name="countries" id="countries" class="select-field">';
                                    }
                                    ?>
                                    <option><?php esc_html_e('Select a country', 'product-feed-manager-for-woocommerce'); ?></option>
                                    <?php
                                    foreach ($countries as $value) {
                                        if ((isset($project)) && ($value == $project['countries'])) {
                                            echo "<option value=\"$value\" selected>$value</option>";
                                        } else {
                                            echo "<option value=\"$value\">$value</option>";
                                        }
                                    }
                                    ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td><span class="convpfm-label"><?php esc_html_e('Channel', 'product-feed-manager-for-woocommerce'); ?>:<span class="required">*</span></span></td>
                                <td>
                                    <?php
                                    if (isset($manage_project)) {
                                        print '<select name="channel_hash" id="channel_hash" class="select-field" disabled>';
                                        echo "<option value=\"$project[channel_hash]\" selected>$project[name]</option>";
                                        print '</select>';
                                    } else {
                                        $customfeed           = '';
                                        $advertising          = '';
                                        $marketplace          = '';
                                        $shopping             = '';
                                        $optgroup_customfeed  = 0;
                                        $optgroup_advertising = 0;
                                        $optgroup_marketplace = 0;
                                        $optgroup_shopping    = 0;

                                        print '<select name="channel_hash" id="channel_hash" class="select-field">';

                                        foreach ($channels as $key => $val) {
                                            if ($val['type'] == 'Custom Feed') {
                                                if ($optgroup_customfeed == 1) {
                                                    if ((isset($project)) && ($val['channel_hash'] == $project['channel_hash'])) {
                                                        $customfeed .= "<option value=\"$val[channel_hash]\" selected>$key</option>";
                                                    } else {
                                                        $customfeed .= "<option value=\"$val[channel_hash]\">$key</option>";
                                                    }
                                                } else {
                                                    $customfeed = '<optgroup label="Custom Feed">';
                                                    if ((isset($project)) && ($val['channel_hash'] == $project['channel_hash'])) {
                                                        $customfeed .= "<option value=\"$val[channel_hash]\" selected>$key</option>";
                                                    } else {
                                                        $customfeed .= "<option value=\"$val[channel_hash]\">$key</option>";
                                                    }
                                                    $optgroup_customfeed = 1;
                                                }
                                            }

                                            if ($val['type'] == 'Advertising') {
                                                if ($optgroup_advertising == 1) {
                                                    if ((isset($project)) && ($val['channel_hash'] == $project['channel_hash'])) {
                                                        $advertising .= "<option value=\"$val[channel_hash]\" selected>$key</option>";
                                                    } else {
                                                        $advertising .= "<option value=\"$val[channel_hash]\">$key</option>";
                                                    }
                                                } else {
                                                    $advertising = '<optgroup label="Advertising">';
                                                    if ((isset($project)) && ($val['channel_hash'] == $project['channel_hash'])) {
                                                        $advertising .= "<option value=\"$val[channel_hash]\" selected>$key</option>";
                                                    } else {
                                                        $advertising .= "<option value=\"$val[channel_hash]\">$key</option>";
                                                    }
                                                    $optgroup_advertising = 1;
                                                }
                                            }

                                            if ($val['type'] == 'Marketplace') {
                                                if ($optgroup_marketplace == 1) {
                                                    if ((isset($project)) && ($val['channel_hash'] == $project['channel_hash'])) {
                                                        $marketplace .= "<option value=\"$val[channel_hash]\" selected>$key</option>";
                                                    } else {
                                                        $marketplace .= "<option value=\"$val[channel_hash]\">$key</option>";
                                                    }
                                                } else {
                                                    $marketplace = '<optgroup label="Marketplace">';
                                                    if ((isset($project)) && ($val['channel_hash'] == $project['channel_hash'])) {
                                                        $marketplace .= "<option value=\"$val[channel_hash]\" selected>$key</option>";
                                                    } else {
                                                        $marketplace .= "<option value=\"$val[channel_hash]\">$key</option>";
                                                    }
                                                    $optgroup_marketplace = 1;
                                                }
                                            }

                                            if ($val['type'] == 'Comparison shopping engine') {
                                                if ($optgroup_shopping == 0) {
                                                    if ((isset($project)) && ($val['channel_hash'] == $project['channel_hash'])) {
                                                        $shopping .= "<option value=\"$val[channel_hash]\" selected>$key</option>";
                                                    } else {
                                                        $shopping .= "<option value=\"$val[channel_hash]\">$key</option>";
                                                    }
                                                } else {
                                                    $shopping = '<optgroup label="Comparison Shopping Engine">';
                                                    if ((isset($project)) && ($val['channel_hash'] == $project['channel_hash'])) {
                                                        $shopping .= "<option value=\"$val[channel_hash]\">$key</option>";
                                                    } else {
                                                        $shopping .= "<option value=\"$val[channel_hash]\" selected>$key</option>";
                                                    }
                                                    $optgroup_shopping = 1;
                                                }
                                            }
                                        }
                                        echo "$customfeed";
                                        echo "$advertising";
                                        echo "$marketplace";
                                        echo "$shopping";
                                        print '</select>';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr id="product_variations">
                                <td><span class="convpfm-label"><?php esc_html_e('Include product variations', 'product-feed-manager-for-woocommerce'); ?>:</span></td>
                                <td>
                                    <label class="woo-product-feed-pro-switch">
                                        <?php
                                        if (isset($project['product_variations'])) {
                                            print '<input type="checkbox" id="variations" name="product_variations" class="checkbox-field" checked>';
                                        } else {
                                            print '<input type="checkbox" id="variations" name="product_variations" class="checkbox-field">';
                                        }
                                        ?>
                                        <div class="woo-product-feed-pro-slider round"></div>
                                    </label>
                                </td>
                            </tr>
                            <tr id="file">
                                <td><span class="convpfm-label"><?php esc_html_e('File format', 'product-feed-manager-for-woocommerce'); ?>:</span></td>
                                <td>
                                    <select name="fileformat" id="fileformat" class="select-field">
                                        <?php
                                        $format_arr = array('xml', 'csv', 'txt', 'tsv');
                                        foreach ($format_arr as $format) {
                                            $format_upper = strtoupper($format);
                                            if ((isset($project)) && ($format == $project['fileformat'])) {
                                                echo "<option value=\"$format\" selected>$format_upper</option>";
                                            } else {
                                                echo "<option value=\"$format\">$format_upper</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr id="delimiter">
                                <td><span class="convpfm-label"><?php esc_html_e('Delimiter', 'product-feed-manager-for-woocommerce'); ?>:</span></td>
                                <td>
                                    <select name="delimiter_val" id="delimiter" class="select-field" style="width:128px">
                                        <?php
                                        $delimiter_arr = array(',', '|', ';', 'tab', '#');
                                        foreach ($delimiter_arr as $delimiter) {
                                            if ((isset($project)) && (array_key_exists('delimiter', $project)) && ($delimiter == $project['delimiter'])) {
                                                echo "<option value=\"$delimiter\" selected>$delimiter</option>";
                                            } else {
                                                echo "<option value=\"$delimiter\">$delimiter</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td><span class="convpfm-label"><?php esc_html_e('Refresh interval', 'product-feed-manager-for-woocommerce'); ?>:</span></td>
                                <td>
                                    <select name="cron" class="select-field" id="refreshinterval">
                                        <?php
                                        $refresh_arr = array('daily', 'no refresh');
                                        foreach ($refresh_arr as $refresh) {
                                            $refresh_upper = ucfirst($refresh);
                                            if ((isset($project)) && ($refresh == $project['cron'])) {
                                                echo "<option value=\"$refresh\" selected>$refresh_upper</option>";
                                            } else {
                                                echo "<option value=\"$refresh\">$refresh_upper</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr class="action-button">
                                <td colspan="2">

                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div style="display: flex; flex-direction:row; justify-content: flex-end; align-items: center; gap:5px">
                        <button type="button" id="cancel" class="cancel" onclick="window.location.href='<?php echo esc_url(admin_url('admin.php?page=convpfm-manage-file')); ?>'">Cancel</button>
                        <?php
                        if (isset($project)) {
                            echo "<input type=\"hidden\" name=\"project_hash\" id=\"project_hash\" value=\"$project[project_hash]\" />";
                            echo "<input type=\"hidden\" name=\"channel_hash\" id=\"channel_hash\" value=\"$project[channel_hash]\" />";
                            print '<input type="hidden" name="project_update" id="project_update" value="yes" />';
                            print '<input type="hidden" name="step" id="step" value="100" />';
                            print '<input type="submit" id="goforit" value="Save" class = "save-cont"/>';
                        } else {
                            print '<input type="hidden" name="step" id="step" value="99" />';
                            print '<input type="submit" id="goforit" class = "save-cont" value="Save & Continue" />';
                        }
                        ?>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>
    <script>
    jQuery(document).ready(function() {
        jQuery('#countries').select2()
        jQuery('#channel_hash').select2()
        // jQuery('#fileformat').select2()
        // jQuery('#refreshinterval').select2()
        // jQuery('#delimiter_val').select2()
    })

    function checkValidity() {
        const projectname = jQuery('#projectname').val().trim();
        const countries = jQuery('#countries').val();
        const channel_hash = jQuery('#channel_hash').val();

        if (projectname !== "" && countries !== "" && countries !== "Select a country" && channel_hash !== "") {
            jQuery('#goforit').prop('disabled', false);
        } else {
            jQuery('#goforit').prop('disabled', true);
        }
    }

    // Initial check
    checkValidity();

    // Event listeners for the form fields
    jQuery('#projectname, #countries, #channel_hash').on('change keyup', function() {
        checkValidity();
    });
</script>
