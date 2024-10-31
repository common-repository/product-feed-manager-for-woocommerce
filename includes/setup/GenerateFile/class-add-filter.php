<?php

/**
 * Create product attribute object
 */
$attributes_obj = new Convpfm_Attributes();
$attributes     = $attributes_obj->get_filter_product_attributes_array();

/**
 * Update or get project configuration
 */
$nonce = wp_create_nonce('convpfm_ajax_nonce');
/**
 * Update or get project configuration
 */
if (array_key_exists('project_hash', $_GET)) {
    $project      = Convpfm_UpdateProject::get_project_data(sanitize_text_field($_GET['project_hash']));
    $channel_data = Convpfm_UpdateProject::get_channel_data(sanitize_text_field($_GET['channel_hash']));
    $count_rules  = 0;
    if (isset($project['rules'])) {
        $count_rules = count($project['rules']);
    }

    $count_rules2 = 0;
    if (isset($project['rules2'])) {
        $count_rules2 = count($project['rules2']);
    }
    $manage_project = 'yes';
} else {
    // Sanitize values in multi-dimensional POST array
    if (is_array($_POST)) {
        foreach ($_POST as $p_key => $p_value) {
            if (is_array($p_value)) {
                foreach ($p_value as $pp_key => $pp_value) {
                    if (is_array($pp_value)) {
                        foreach ($pp_value as $ppp_key => $ppp_value) {
                            $_POST[$p_key][$pp_key][$ppp_key] = sanitize_text_field($ppp_value);
                        }
                    }
                }
            } else {
                $_POST[$p_key] = sanitize_text_field($p_value);
            }
        }
    } else {
        $_POST = array();
    }

    $project          = Convpfm_UpdateProject::update_project($_POST);
    $channel_data = Convpfm_UpdateProject::get_channel_data(sanitize_text_field($_POST['channel_hash']));
    $count_rules      = 0;
    $count_rules2     = 0;
}
?>
<style>
    .convpfm-product-feed-filter-form {
        max-width: 100%;
        padding: 20px 12px 10px 20px;
        font: 13px Arial, Helvetica, sans-serif;
    }

    .heading-wrapper {
        width: 100%;
        height: 80px;
        gap: 1px;

    }

    .image {
        width: 22.5px;
        height: 15px;
        top: 7.5px;
        left: 3.75px;
    }

    .heading {
        width: 100%;
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

    .convpfm-filter-table {
        max-width: 100%;
        background-color: white;
        border-radius: 8px;
        padding: 16px 16px;
        gap: 8px;
        box-shadow: 0px 1px 0px 0px #1A1A1A12;
        box-shadow: 0px 1px 0px 0px #CCCCCC80 inset;
        box-shadow: 0px -1px 0px 0px #0000002B inset;
        box-shadow: -1px 0px 0px 0px #00000021 inset;
        box-shadow: 1px 0px 0px 0px #00000021 inset;
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

    .filter-mapping-table {
        padding: 2px;
        border: 1px solid #CBCDD0;
        border-radius: 8px;
    }

    .convpfm-product-feed-filter-table {
        width: 100%;
        border: none;
        padding: 0px 0px 8px 0px;
        gap: 16px;
        box-shadow: 2px 2px 4px 0px #00000014;
    }

    .convpfm-filter-heading {
        width: 98%;
        height: 48px;
        padding: 12px 16px;
        gap: 16px;
        background-color: #F5F6F6;
    }

    .convpfm-filter-row {
        width: 100%;
        height: 38px;
        border: 0px 0px 1px 0px;
        padding: 0px 16px 0px 16px;
        gap: 16px;
        background-color: white;
    }

    button,
    input,
    optgroup,
    select,
    textarea,
    text {
        margin: 5px 0px 0 5px;
        font-family: inherit;
        font-size: inherit;
        line-height: inherit;
    }

    .filter-option,
    .filter-condition,
    .filter-value,
    .filter-then {
        width: 100%;
        max-width: 100% !important;
        height: 38px;
        border-radius: 4px;
        border: 1px solid #CBCDD0;
        justify-content: space-between;
        padding: 8px 16px 8px 16px;
        background-color: white;
    }
    .filter-delete , .filter-add{
        width: 110px;
        height: 38px;
        border-radius: 4px;
        border: 1px solid #1967D2;
        padding: 8px 16px;
        gap: 5px;
        background: white;
        color: #1967D2;
    }
    .filter-save{
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
        margin-right: 1.5%;
    }
    .align {
        display: flex;
        flex-direction: row;
        margin: 10px -20px 10px 5px;
        gap: 3px;
        justify-content: flex-end;
        align-items: center;
    }
    .rich-blue {
        height: 36px;
    }
</style>
<div class="wrap">
    <div class="convpfm-product-feed-filter-form">
        <div class="heading-wrapper">
            <div style="display: inline-block; width: 30px; height: 30px; vertical-align: middle">
                <?php echo '<img class = "image" src="' . plugins_url('images/filter.png', __FILE__) . '" alt="My Image">'; ?>
            </div>
            <div style="display: inline-block; vertical-align: middle">
                <span class="heading"><?php esc_html_e('Feed Filters & Rules', 'product-feed-manager-for-woocommerce'); ?></span>
            </div>
            <div>
                <span class="description"><?php esc_html_e('Manage your product feeds to keep your online store information up-to-date and accurate. This includes adding new products, updating prices, and ensuring all product details meet platform requirements. Effective feed management helps your products appear correctly on shopping channels and improves your visibility to potential customers.
', 'product-feed-manager-for-woocommerce'); ?></span>
            </div>
        </div>
        <div class="convpfm-filter-table">
            <ul class="nav nav-pills" id="pills-tab" role="tablist">
                <li style="pointer-events: none;" class="nav-item" role="presentation">
                    <span class="nav-link" id="pills-enter-feed-details-tab" style="color:#1967D2; font-weight: 600;" data-bs-toggle="pill" data-bs-target="#pills-enter-feed-details" role="tab" aria-controls="pills-enter-feed-details" aria-selected="true">Enter File Details &nbsp; ></span>
                </li>
                <li style="pointer-events: none;" class="nav-item" role="presentation">
                    <span class="nav-link" id="pills-map-product-category-tab" style="color:#1967D2; font-weight: 600;" data-bs-toggle="pill" data-bs-target="#pills-map-product-category" type="" role="tab" aria-controls="pills-map-product-category" aria-selected="false">Field Mapping &nbsp; ></span>
                </li>
                <li style="pointer-events: none;" class="nav-item" role="presentation">
                    <span class="nav-link active" id="pills-map-product-attribute-tab" data-bs-toggle="pill" data-bs-target="#pills-map-product-attribute" type="" role="tab" aria-controls="pills-map-product-attribute" aria-selected="false">Apply Filters &nbsp; ></span>
                </li>
                <li style="pointer-events: none;" class="nav-item" role="presentation">
                    <span class="nav-link" id="pills-map-product-attribute-tab" data-bs-toggle="pill" data-bs-target="#pills-map-product-attribute" type="" role="tab" aria-controls="pills-map-product-attribute" aria-selected="false">Manage Feeds</span>
                </li>
            </ul>
            <form id="rulesandfilters" method="post">
                <?php wp_nonce_field('convpfm_ajax_nonce'); ?>
                <div style="display: inline-block; vertical-align: middle; margin-top:5px; margin-bottom:2px;">
                    <span class="heading"><?php esc_html_e('Apply Filters', 'product-feed-manager-for-woocommerce'); ?></span>
                </div>
                <div class="filter-mapping-table">
                    <table class="convpfm-product-feed-filter-table" id="convpfm-ajax-table" border="1">
                        <thead class="convpfm-filter-heading">
                            <tr>
                                <th style="width:5%; margin:0px 10px 0px 5px;" class="text-center">
                                    <input type="checkbox" name="record" class="checkbox-field" disabled>
                                </th>
                                <th style="width:38%; margin:0px 10px 0px 5px;"><?php esc_html_e('IF', 'product-feed-manager-for-woocommerce'); ?></th>
                                <th style="width:19%; margin:0px 10px 0px 5px;"><?php esc_html_e('Condition', 'product-feed-manager-for-woocommerce'); ?></th>
                                <th style="width:19%; margin:0px 10px 0px 5px;"><?php esc_html_e('Value', 'product-feed-manager-for-woocommerce'); ?></th>
                                <th style="width:19%; margin:0px 10px 0px 5px;"><?php esc_html_e('Then', 'product-feed-manager-for-woocommerce'); ?></th>
                            </tr>
                        </thead>

                        <?php
                        // if(isset($project['rules'])){
                        print '<tbody class="woo-product-feed-pro-body">';
                        if (isset($project['rules'])) {
                            foreach ($project['rules'] as $rule_key => $rule_array) {

                                if (isset($project['rules'][$rule_key]['criteria'])) {
                                    $criteria = $project['rules'][$rule_key]['criteria'];
                                } else {
                                    $criteria = '';
                                }
                        ?>
                                <tr class="rowCount convpfm-filter-row" style="text-align: center; margin: 5px 0px 0 5px;">
                                    <td class="text-center p-1"><input type="hidden" name="rules[<?php echo "$rule_key"; ?>][rowCount]" value="<?php echo "$rule_key"; ?>"><input type="checkbox" name="record" class="checkbox-field text-center p-1"></td>
                                    <td class="p-1 text-start">
                                        <select name="rules[<?php echo "$rule_key"; ?>][attribute]" class="select-field filter-option">
                                            <option></option>
                                            <?php
                                            foreach ($attributes as $k => $v) {
                                                if (isset($project['rules'][$rule_key]['attribute']) && ($project['rules'][$rule_key]['attribute'] == $k)) {
                                                    echo "<option value=\"$k\" selected>$v</option>";
                                                } else {
                                                    echo "<option value=\"$k\">$v</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </td>
                                    <td class="p-1 text-start">
                                        <select name="rules[<?php echo "$rule_key"; ?>][condition]" class="select-field filter-condition">
                                            <?php
                                            if (isset($project['rules'][$rule_key]['condition']) && ($project['rules'][$rule_key]['condition'] == 'contains')) {
                                                print '<option value="contains" selected>contains</option>';
                                            } else {
                                                print '<option value="contains">contains</option>';
                                            }

                                            if (isset($project['rules'][$rule_key]['condition']) && ($project['rules'][$rule_key]['condition'] == 'containsnot')) {
                                                echo "<option value=\"containsnot\" selected>doesn't contain</option>";
                                            } else {
                                                echo "<option value=\"containsnot\">doesn't contain</option>";
                                            }

                                            if (isset($project['rules'][$rule_key]['condition']) && ($project['rules'][$rule_key]['condition'] == '=')) {
                                                print '<option value="=" selected>is equal to</option>';
                                            } else {
                                                print '<option value="=">is equal to</option>';
                                            }

                                            if (isset($project['rules'][$rule_key]['condition']) && ($project['rules'][$rule_key]['condition'] == '!=')) {
                                                print '<option value="!=" selected>is not equal to</option>';
                                            } else {
                                                print '<option value="!=">is not equal to</option>';
                                            }

                                            if (isset($project['rules'][$rule_key]['condition']) && ($project['rules'][$rule_key]['condition'] == '>')) {
                                                print '<option value=">" selected>is greater than</option>';
                                            } else {
                                                print '<option value=">">is greater than</option>';
                                            }

                                            if (isset($project['rules'][$rule_key]['condition']) && ($project['rules'][$rule_key]['condition'] == '>=')) {
                                                print '<option value=">=" selected>is greater or equal to</option>';
                                            } else {
                                                print '<option value=">=">is greater or equal to</option>';
                                            }

                                            if (isset($project['rules'][$rule_key]['condition']) && ($project['rules'][$rule_key]['condition'] == '<')) {
                                                print '<option value="<" selected>is less than</option>';
                                            } else {
                                                print '<option value="<">is less than</option>';
                                            }

                                            if (isset($project['rules'][$rule_key]['condition']) && ($project['rules'][$rule_key]['condition'] == '=<')) {
                                                print '<option value="=<" selected>is less or equal to</option>';
                                            } else {
                                                print '<option value="=<">is less or equal to</option>';
                                            }

                                            if (isset($project['rules'][$rule_key]['condition']) && ($project['rules'][$rule_key]['condition'] == 'empty')) {
                                                print '<option value="empty" selected>is empty</option>';
                                            } else {
                                                print '<option value="empty">is empty</option>';
                                            }

                                            if (isset($project['rules'][$rule_key]['condition']) && ($project['rules'][$rule_key]['condition'] == 'notempty')) {
                                                print '<option value="notempty" selected>is not empty</option>';
                                            } else {
                                                print '<option value="notempty">is not empty</option>';
                                            }
                                            ?>
                                        </select>
                                    </td>
                                    <td class="p-1 text-start">
                                        <div style="display: block;">
                                            <input type="text" id="rulevalue" name="rules[<?php echo "$rule_key"; ?>][criteria]" class="input-field-large filter-value" value='<?php print $criteria; ?>'>
                                        </div>
                                    </td>
                                    <td class="p-1 text-start">
                                        <select name="rules[<?php echo "$rule_key"; ?>][than]" class="select-field filter-then">
                                            <optgroup label='Action'>Action:
                                                <?php
                                                if (isset($project['rules'][$rule_key]['than']) && ($project['rules'][$rule_key]['than'] == 'exclude')) {
                                                    print '<option value="exclude" selected> Exclude</option>';
                                                } else {
                                                    print '<option value="exclude"> Exclude</option>';
                                                }

                                                if (isset($project['rules'][$rule_key]['than']) && ($project['rules'][$rule_key]['than'] == 'include_only')) {
                                                    print '<option value="include_only" selected> Include only</option>';
                                                } else {
                                                    print '<option value="include_only"> Include only</option>';
                                                }
                                                ?>
                                            </optgroup>
                                        </select>
                                    </td>
                                    <td>&nbsp;</td>
                                </tr>
                        <?php
                            }
                        }
                        print '</tbody>';
                        // }
                        ?>
                        <tbody>
                            <tr class="rules-buttons">
                                <td colspan="8">

                                    <input type="hidden" id="channel_hash" name="channel_hash" value="<?php echo "$project[channel_hash]"; ?>">
                                    <?php
                                    if (isset($manage_project)) {
                                    ?>
                                        <input type="hidden" name="project_hash" value="<?php echo "$project[project_hash]"; ?>">
                                        <input type="hidden" name="convpfm_page" value="filters_rules">
                                        <input type="hidden" name="step" value="100">
                                        <div class="align"><input type="button" class="delete-row filter-delete" value="- Delete">&nbsp;<input type="button" class="add-filter filter-add" value="+ Add filter">&nbsp;<input type="submit" id="savebutton" value="Save" class="filter-save"></div>
                                    <?php
                                    } else {
                                    ?>
                                        <input type="hidden" name="project_hash" value="<?php echo "$project[project_hash]"; ?>">
                                        <input type="hidden" name="step" value="101">
                                        <div class="align"><input type="button" class="delete-row filter-delete" value="- Delete">&nbsp;<input type="button" class="add-filter filter-add" value="+ Add filter">&nbsp;<input type="submit" id="savebutton" value="Continue" class="filter-save"></div>
                                    <?php
                                    }
                                    ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
jQuery(document).ready(function() {
    function validateFields() {
        let isValid = true;

        // Iterate through each convpfm-filter-row
        jQuery('.convpfm-filter-row').each(function() {
            // Find the filter-option and filter-value within this row
            const filterOption = jQuery(this).find('.filter-option').val().trim();
            const filterValue = jQuery(this).find('.filter-value').val().trim();

            // If either filter-option or filter-value is empty, set isValid to false and exit the loop
            if (filterOption === '' || filterValue === '') {
                isValid = false;
                return false; // Exit the loop early if a condition fails
            }
        });

        // Enable or disable the save button based on validation
        jQuery('#savebutton').prop('disabled', !isValid);
    }

    // Validate fields on page load
    validateFields();

    // Validate fields whenever there's a change in any .filter-option or .filter-value fields
    jQuery(document).on('input', '.filter-option, .filter-value', function() {
        validateFields();
    });

    // Re-validate fields when rows are added or removed
    jQuery(document).on('click', '.delete-row, .add-filter', function() {
        validateFields();
    });
});
</script>


