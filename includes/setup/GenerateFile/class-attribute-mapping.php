<?php
$attributes_obj     = new Convpfm_Attributes();
$attribute_dropdown = $attributes_obj->get_product_attributes();

if (array_key_exists('project_hash', $_GET)) {
    $project        = Convpfm_UpdateProject::get_project_data(sanitize_text_field($_GET['project_hash']));
    $channel_data   = Convpfm_UpdateProject::get_channel_data(sanitize_text_field($_GET['channel_hash']));
    $count_mappings     = count($project['attributes']);
    $manage_project = 'yes';

    if (isset($project['WPML'])) {
        if ((is_plugin_active('sitepress-multilingual-cms')) || (function_exists('icl_object_id'))) {
            if (!class_exists('Polylang')) {
                // Get WPML language
                global $sitepress;
                $lang = $project['WPML'];
                $sitepress->switch_lang($lang);
            }
        }
    }
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
    if (isset($project['WPML'])) {
        if ((is_plugin_active('sitepress-multilingual-cms')) || (function_exists('icl_object_id'))) {
            if (!class_exists('Polylang')) {
                // Get WPML language
                global $sitepress;
                $lang = $project['WPML'];
                $sitepress->switch_lang($lang);
            }
        }
    }
}

/**
 * Determine next step in configuration flow
 */
$currency = get_woocommerce_currency();
if (isset($project['WCML'])) {
    $currency = $project['WCML'];
}
$step = 4;
if ($channel_data['taxonomy'] != 'none') {
    $step = 1;
}
require plugin_dir_path(__FILE__) . '/classes/channels/class-' . $channel_data['fields'] . '.php';
$obj        = 'Convpfm_' . $channel_data['fields'];
$fields_obj = new $obj();
$attributes = $fields_obj->get_channel_attributes();
$nonce = wp_create_nonce('convpfm_ajax_nonce');
?>
<style>
    .image {
        width: 27.5px;
        height: 24.5px;
        top: 6.25px;
        left: 1.25px;
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

    .heading-wrapper {
        width: 100%;
        height: 80px;
        gap: 1px;

    }

    .convpfm-product-feed-attribute-form {
        max-width: 100%;
        padding: 20px 12px 10px 20px;
        font: 13px Arial, Helvetica, sans-serif;
    }

    .convpfm-attribute-mapping-table {
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

    .convpfm-attr-heading {
        width: 98%;
        height: 48px;
        padding: 12px 16px;
        gap: 16px;
        background-color: #F5F6F6;
    }

    .convpfm-product-feed-attribute-table {
        width: 100%;
        border: none;
        padding: 0px 0px 8px 0px;
        gap: 16px;
        box-shadow: 2px 2px 4px 0px #00000014;
    }

    .convpfm-attribute-row {
        width: 100%;
        height: 38px;
        border: 0px 0px 1px 0px;
        padding: 0px 16px 0px 16px;
        gap: 16px;
        background-color: white;
    }

    .attr-mapping-table {
        padding: 2px;
        border: 1px solid #CBCDD0;
        border-radius: 8px;
    }

    .feed-attr {
        width: 100% !important;
        max-width: 100% !important;
        height: 38px;
        border-radius: 4px;
        justify-content: space-between;
        padding: 8px 16px 8px 16px;
        border: 1px solid #CBCDD0;
        background-color: white;
    }

    button,
    input,
    optgroup,
    select,
    textarea {
        margin: 5px 0px 0 5px;
        font-family: inherit;
        font-size: inherit;
        line-height: inherit;
    }

    .attr-prefix {
        width: 80%;
        height: 38px;
        border-radius: 4px;
        border: 1px solid #CBCDD0;
        justify-content: space-between;
        padding: 8px 16px 8px 16px;
        background-color: white;
    }

    .button1 {
        width: 168px;
        height: 38px;
        border-radius: 4px;
        border: 1px solid #1967D2;
        padding: 8px 16px;
        gap: 5px;
        background: white;
        color: #1967D2;
    }

    .align {
        display: flex;
        flex-direction: row;
        margin: 10px 5px 10px 5px;
        gap: 5px;
        justify-content: flex-end;
        align-items: center;
    }

    .save {
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
    .rich-blue {
        height: 36px;
    }
</style>
<div id="dialog" title="Basic dialog">
    <p>
    <div id="dialogText"></div>
    </p>
</div>

<div class="wrap">
    <div class="convpfm-product-feed-attribute-form">
        <div class="heading-wrapper">
            <div style="display: inline-block; width: 30px; height: 30px vertical-align: middle">
                <?php echo '<img class = "image" src="' . plugins_url('images/category mapping.png', __FILE__) . '" alt="My Image">'; ?>
            </div>
            <div style="display: inline-block; vertical-align: middle">
                <span class="heading"><?php esc_html_e('Field Mapping', 'product-feed-manager-for-woocommerce'); ?></span>
            </div>
            <div>
                <span class="description"><?php esc_html_e('Manage your product feeds to keep your online store information up-to-date and accurate. This includes adding new products, updating prices, and ensuring all product details meet platform requirements. Effective feed management helps your products appear correctly on shopping channels and improves your visibility to potential customers.
', 'product-feed-manager-for-woocommerce'); ?></span>
            </div>
        </div>
        <div class="convpfm-attribute-mapping-table">
            <ul class="nav nav-pills" id="pills-tab" role="tablist">
                <li style="pointer-events: none;" class="nav-item" role="presentation">
                    <span class="nav-link" id="pills-enter-feed-details-tab" style="color:#1967D2; font-weight: 600;" data-bs-toggle="pill" data-bs-target="#pills-enter-feed-details" role="tab" aria-controls="pills-enter-feed-details" aria-selected="true">Enter File Details &nbsp; ></span>
                </li>
                <li style="pointer-events: none;" class="nav-item" role="presentation">
                    <span class="nav-link active" id="pills-map-product-category-tab" data-bs-toggle="pill" data-bs-target="#pills-map-product-category" type="" role="tab" aria-controls="pills-map-product-category" aria-selected="false">Field Mapping &nbsp; ></span>
                </li>
                <li style="pointer-events: none;" class="nav-item" role="presentation">
                    <span class="nav-link" id="pills-map-product-attribute-tab" data-bs-toggle="pill" data-bs-target="#pills-map-product-attribute" type="" role="tab" aria-controls="pills-map-product-attribute" aria-selected="false">Apply Filters &nbsp; ></span>
                </li>
                <li style="pointer-events: none;" class="nav-item" role="presentation">
                    <span class="nav-link" id="pills-map-product-attribute-tab" data-bs-toggle="pill" data-bs-target="#pills-map-product-attribute" type="" role="tab" aria-controls="pills-map-product-attribute" aria-selected="false">Manage Feeds</span>
                </li>
            </ul>

            <form action="" id="fieldmapping" method="post">
                <?php wp_nonce_field('convpfm_ajax_nonce');
                ?>
                <div style="display: inline-block; vertical-align: middle; margin-top:5px; margin-bottom:2px;">
                    <span class="heading"><?php esc_html_e('Attribute Mapping', 'product-feed-manager-for-woocommerce'); ?></span>
                </div>
                <div class="attr-mapping-table">
                    <table class="convpfm-product-feed-attribute-table" id="convpfm-fieldmapping-table" border="1">
                        <thead class="convpfm-attr-heading">
                            <tr>
                                <th style="width:4%; margin:0px 10px 0px 5px;" class="text-center">
                                <input type="checkbox" name="record" class="checkbox-field" disabled>
                                </th>
                                <th style="width:38%; margin:0px 10px 0px 5px;">
                                    <?php
                                    echo "$channel_data[name] attributes";
                                    ?>
                                </th>
                                <th style="width:10%; margin:0px 10px 0px 5px;"><?php esc_html_e('Prefix', 'product-feed-manager-for-woocommerce'); ?></th>
                                <th style="width:38%; margin:0px 10px 0px 5px;"><?php esc_html_e('Conversios Attribute', 'product-feed-manager-for-woocommerce'); ?></th>
                                <th style="width:10%; margin:0px 10px 0px 5px;"><?php esc_html_e('Suffix', 'product-feed-manager-for-woocommerce'); ?></th>
                            </tr>
                        </thead>

                        <tbody class="woo-product-feed-pro-body">
                            <?php
                            if (!isset($count_mappings)) {
                                $c = 0;
                                foreach ($attributes as $row_key => $row_value) {
                                    foreach ($row_value as $row_k => $row_v) {
                                        if ($row_v['format'] == 'required') {
                            ?>
                                            <tr class="rowCount <?php echo "$c"; ?>" class="convpfm-attribute-row" style="text-align: center;">
                                                <td class="text-center p-1"><input type="hidden" name="attributes[<?php echo "$c"; ?>][rowCount]" value="<?php echo "$c"; ?>">
                                                    <input type="checkbox" name="record" class="checkbox-field">
                                                </td>
                                                <td class="p-1 text-start">
                                                    <select name="attributes[<?php echo "$c"; ?>][attribute]" class="select-field feed-attr ">
                                                        <?php
                                                        foreach ($attributes as $key => $value) {
                                                            echo "<optgroup label='$key'><strong>$key</strong>";

                                                            foreach ($value as $k => $v) {
                                                                if ($v['feed_name'] == $row_v['feed_name']) {
                                                                    if (array_key_exists('name', $v)) {
                                                                        $dialog_value = $v['feed_name'];
                                                                        echo "<option value='$v[feed_name]' selected>$k ($v[name])</option>";
                                                                    } else {
                                                                        echo "<option value='$v[feed_name]' selected>$k</option>";
                                                                    }
                                                                } elseif (array_key_exists('name', $v)) {
                                                                    echo "<option value='$v[feed_name]'>$k ($v[name])</option>";
                                                                } else {
                                                                    echo "<option value='$v[feed_name]'>$k</option>";
                                                                }
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                </td>
                                                <td class="p-1 text-start">
                                                    <?php
                                                    if ($row_v['feed_name'] == 'g:price') {
                                                        echo "<input class='attr-prefix' type='text' name='attributes[$c][prefix]' value='$currency' class='input-field-medium'>";
                                                    } else {
                                                        echo "<input class='attr-prefix' type='text' name='attributes[$c][prefix]' class='input-field-medium'>";
                                                    }
                                                    ?>
                                                </td>
                                                <td class="p-1 text-start">
                                                    <select name="attributes[<?php echo "$c"; ?>][mapfrom]" class="select-field feed-attr">
                                                        <option></option>
                                                        <?php
                                                        foreach ($attribute_dropdown as $drop_key => $drop_value) {
                                                            if (array_key_exists('woo_suggest', $row_v)) {
                                                                if ($row_v['woo_suggest'] == $drop_key) {
                                                                    echo "<option value='$drop_key' selected>$drop_value</option>";
                                                                } else {
                                                                    echo "<option value='$drop_key'>$drop_value</option>";
                                                                }
                                                            } else {
                                                                echo "<option value='$drop_key'>$drop_value</option>";
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                </td>
                                                <td class="p-1 text-start">
                                                    <input type="text" name="attributes[<?php echo "$c"; ?>][suffix]" class="input-field-medium attr-prefix">
                                                </td>
                                            </tr>
                                    <?php
                                            ++$c;
                                        }
                                    }
                                }
                            } else {
                                foreach ($project['attributes'] as $attribute_key => $attribute_array) {
                                    if (isset($project['attributes'][$attribute_key]['prefix'])) {
                                        $prefix = $project['attributes'][$attribute_key]['prefix'];
                                    }
                                    if (isset($project['attributes'][$attribute_key]['suffix'])) {
                                        $suffix = $project['attributes'][$attribute_key]['suffix'];
                                    }
                                    ?>
                                    <tr class="rowCount <?php echo "$attribute_key"; ?>" class="convpfm-attribute-row" style="text-align: center;">
                                        <td class="text-center p-1"><input type="hidden" name="attributes[<?php echo "$attribute_key"; ?>][rowCount]" value="<?php echo "$attribute_key"; ?>">
                                            <input type="checkbox" name="record" class="checkbox-field">
                                        </td>
                                        <td class="p-1 text-start">
                                            <select name="attributes[<?php echo "$attribute_key"; ?>][attribute]" class="select-field feed-attr">
                                                <?php
                                                echo "<option value=\"$attribute_array[attribute]\">$attribute_array[attribute]</option>";
                                                ?>
                                            </select>
                                        </td>
                                        <td class="p-1 text-start">
                                            <input type="text" class="attr-prefix" name="attributes[<?php echo "$attribute_key"; ?>][prefix]" class="input-field-medium" value="<?php echo "$prefix"; ?>">
                                        </td>
                                        <td class="p-1 text-start">

                                            <?php
                                            if (array_key_exists('static_value', $attribute_array)) {
                                                echo "<input type=\"text\" name=\"attributes[$attribute_key][mapfrom]\" class=\"input-field-midsmall\" value=\"$attribute_array[mapfrom]\"><input type=\"hidden\" name=\"attributes[$attribute_key][static_value]\" value=\"true\">";
                                            } else {
                                            ?>
                                                <select name="attributes[<?php echo "$attribute_key"; ?>][mapfrom]" class="select-field feed-attr">
                                                    <option></option>
                                                    <?php
                                                    foreach ($attribute_dropdown as $drop_key => $drop_value) {
                                                        if ($project['attributes'][$attribute_key]['mapfrom'] == $drop_key) {
                                                            echo "<option value='$drop_key' selected>$drop_value</option>";
                                                        } else {
                                                            echo "<option value='$drop_key'>$drop_value</option>";
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            <?php
                                            }
                                            ?>
                                        </td>
                                        <td class="p-1 text-start">
                                            <input type="text" class="attr-prefix" name="attributes[<?php echo "$attribute_key"; ?>][suffix]" class="input-field-medium" value="<?php echo "$suffix"; ?>">
                                        </td>
                                    </tr>
                            <?php
                                }
                            }
                            ?>
                        </tbody>

                        <tr>
                            <td colspan="6">
                                <input type="hidden" id="channel_hash" name="channel_hash" value="<?php echo "$project[channel_hash]"; ?>">
                                <?php
                                if (isset($manage_project)) {
                                ?>
                                    <input type="hidden" name="project_hash" value="<?php echo "$project[project_hash]"; ?>">
                                    <input type="hidden" name="step" value="100">
                                    <input type="hidden" name="addrow" id="addrow" value="1">
                                    <div class="align"><input type="button" class="delete-field-mapping button1" value="- Delete">&nbsp;<input type="button" class="add-field-mapping button1" value="+ Add field mapping">&nbsp;<input type="button" class="add-own-mapping button1" value="+ Add custom field">&nbsp;<input type="submit" id="savebutton" value="Save" class="save" /></div>

                                <?php
                                } else {
                                ?>
                                    <input type="hidden" name="project_hash" value="<?php echo "$project[project_hash]"; ?>">
                                    <input type="hidden" name="step" value="<?php echo "$step"; ?>">
                                    <input type="hidden" name="addrow" id="addrow" value="1">
                                    <div class="align"><input type="button" class="delete-field-mapping button1" value="- Delete">&nbsp;<input type="button" class="add-field-mapping button1" value="+ Add field mapping">&nbsp;<input type="button" class="add-own-mapping button1" value="+ Add custom field">&nbsp;<input type="submit" id="savebutton" value="Save" class="save" /></div>
                                <?php
                                }
                                ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </form>
        </div>
    </div>
</div>