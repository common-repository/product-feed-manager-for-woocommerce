<?php
$nonce = wp_create_nonce('convpfm_ajax_nonce');

if (array_key_exists('project_hash', $_GET)) {
    $project        = Convpfm_UpdateProject::get_project_data(sanitize_text_field($_GET['project_hash']));
    $channel_data   = Convpfm_UpdateProject::get_channel_data(sanitize_text_field($_GET['channel_hash']));
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
    $project      = Convpfm_UpdateProject::update_project($_POST);
    $channel_data = Convpfm_UpdateProject::get_channel_data(sanitize_text_field($_POST['channel_hash']));
}
function convpfm_hierarchical_term_tree($category, $prev_mapped)
{
    $r = '';

    $args = array(
        'parent'        => $category,
        'hide_empty'    => false,
        'no_found_rows' => true,
    );

    $next          = get_terms('product_cat', $args);
    $nr_categories = count($next);
    $yo            = 0;

    if ($next) {
        foreach ($next as $sub_category) {
            ++$yo;
            $x               = $sub_category->term_id;
            $woo_category    = $sub_category->name;
            $woo_category_id = $sub_category->term_id;

            $mapped_category     = '';
            $mapped_active_class = 'input-field-large';
            $woo_category        = preg_replace('/&amp;/', '&', $woo_category);
            $woo_category        = preg_replace('/"/', '&quot;', $woo_category);

            // Check if mapping is in place
            if ((array_key_exists($x, $prev_mapped)) || (array_key_exists($woo_category, $prev_mapped))) {
                if (array_key_exists($x, $prev_mapped)) {
                    $mapped_category = $prev_mapped[$x];
                } elseif (array_key_exists($woo_category, $prev_mapped)) {
                    $mapped_category = $prev_mapped[$x];
                } else {
                    $mapped_category = $woo_category;
                }
                $mapped_active_class = 'input-field-large-active';
            }

            // These are main categories
            if ($sub_category->parent == 0) {
                $args = array(
                    'parent'        => $sub_category->term_id,
                    'hide_empty'    => false,
                    'no_found_rows' => true,
                );

                $subcat     = get_terms('product_cat', $args);
                $nr_subcats = count($subcat);

                $r .= '<tr class="catmapping">';
                $r .= "<td class=\"p-2 text-start fw-600\"><input type=\"hidden\" name=\"mappings[$x][rowCount]\" value=\"$x\"><input type=\"hidden\" name=\"mappings[$x][categoryId]\" value=\"$woo_category_id\"><input type=\"hidden\" name=\"mappings[$x][criteria]\" class=\"input-field-large\" id=\"$woo_category_id\" value=\"$woo_category\">$woo_category ($sub_category->count)</td>";
                $r .= "<td class=\"p-2 text-center\"><div id=\"the-basics-$x\"><input type=\"text\" name=\"mappings[$x][map_to_category]\" class=\"$mapped_active_class js-typeahead js-autosuggest autocomplete_$x cat-attr\" value=\"$mapped_category\"></div></td>";
                if (($yo == $nr_categories) && ($nr_subcats == 0)) {
                    $r .= "<td></td>";
                } elseif ($nr_subcats > 0) {
                    $r .= "<td></td>";
                } else {
                    $r .= "<td></td>";
                }
                $r .= '</tr>';
            } else {
                $r .= '<tr class="catmapping">';
                $r .= "<td class=\"p-2 text-start fw-600\"><input type=\"hidden\" name=\"mappings[$x][rowCount]\" value=\"$x\"><input type=\"hidden\" name=\"mappings[$x][categoryId]\" value=\"$woo_category_id\"><input type=\"hidden\" name=\"mappings[$x][criteria]\" class=\"input-field-large\" id=\"$woo_category_id\" value=\"$woo_category\">-- $woo_category ($sub_category->count)</td>";
                $r .= "<td class=\"p-2 text-center\"><div id=\"the-basics-$x\"><input type=\"text\" name=\"mappings[$x][map_to_category]\" class=\"$mapped_active_class js-typeahead js-autosuggest autocomplete_$x mother_$sub_category->parent cat-attr\" value=\"$mapped_category\"></div></td>";
                $r .= "<td><span class=\"copy_category_$x\" style=\"display: inline-block;\" title=\"Copy this category to all others\"></span></td>";
                $r .= '</tr>';
            }
            $r .= $sub_category->term_id !== 0 ? convpfm_hierarchical_term_tree($sub_category->term_id, $prev_mapped) : null;
        }
    }
    return wp_kses_normalize_entities($r);
}
?>
<style>
    .convpfm-product-feed-category-form {
        max-width: 100%;
        padding: 20px 12px 10px 20px;
        font: 13px Arial, Helvetica, sans-serif;
    }

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

    .attr-mapping-table {
        padding: 2px;
        border: 1px solid #CBCDD0;
        border-radius: 8px;
    }

    .convpfm-product-feed-attribute-table {
        width: 100%;
        border: none;
        padding: 0px 0px 8px 0px;
        gap: 16px;
        box-shadow: 2px 2px 4px 0px #00000014;
    }

    .convpfm-attr-heading {
        width: 98%;
        height: 48px;
        padding: 12px 16px;
        gap: 16px;
        background-color: #F5F6F6;
    }

    .convpfm-attribute-row {
        width: 100%;
        height: 38px;
        border: 0px 0px 1px 0px;
        padding: 0px 16px 0px 16px;
        gap: 16px;
        background-color: white;
    }

    .cat-attr {
        width: 96% !important;
        max-width: 100% !important;
        height: 38px;
        border-radius: 4px;
        justify-content: space-between;
        padding: 8px 16px 8px 16px;
        border: 1px solid #CBCDD0;
        background-color: white;
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

    .align {
        display: flex;
        flex-direction: row;
        margin: 10px 5px 10px 5px;
        gap: 5px;
        justify-content: flex-end;
        align-items: center;
    }
    .rich-blue {
        height: 36px;
    }
</style>
<div class="wrap">
    <div class="convpfm-product-feed-category-form">
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

            <div style="display: inline-block; vertical-align: middle; margin-top:5px; margin-bottom:2px;">
                <span class="heading"><?php esc_html_e('Category Mapping', 'product-feed-manager-for-woocommerce'); ?></span>
            </div>
            <div class="attr-mapping-table">
                <table id="convpfm-ajax-mapping-table" class="convpfm-product-feed-attribute-table" border="1">
                    <thead class="convpfm-attr-heading p-1">
                        <tr>
                            <th class="text-start" style="width:40%;padding-left:10px"><?php esc_html_e('Conversios category', 'product-feed-manager-for-woocommerce'); ?> <i>(<?php esc_html_e('Number of products', 'product-feed-manager-for-woocommerce'); ?>)</i></th>
                            <th class="text-start" style="width:60%;padding-left:26px"><?php echo "$channel_data[name]"; ?> <?php esc_html_e('category', 'product-feed-manager-for-woocommerce'); ?></th>
                            <th></th>
                        </tr>
                    </thead>

                    <tbody class="woo-product-feed-pro-body">
                        <?php
                        // Get already mapped categories
                        $prev_mapped = array();
                        if (isset($project['mappings'])) {
                            foreach ($project['mappings'] as $map_key => $map_value) {
                                if (strlen($map_value['map_to_category']) > 0) {
                                    $map_value['criteria']                   = str_replace('\\', '', $map_value['criteria']);
                                    $prev_mapped[$map_value['categoryId']] = $map_value['map_to_category'];
                                    // $prev_mapped[$map_value['criteria']] = $map_value['map_to_category'];
                                }
                            }
                        }
                        // Display mapping form
                        echo convpfm_hierarchical_term_tree(0, $prev_mapped);
                        ?>
                    </tbody>

                    <form action="" method="post">
                        <?php wp_nonce_field('convpfm_ajax_nonce'); ?>

                        <tr class="convpfm-attribute-row">
                            <td colspan="3">
                                <input type="hidden" id="channel_hash" name="channel_hash" value="<?php echo "$project[channel_hash]"; ?>">
                                <?php
                                if (isset($manage_project)) {
                                ?>
                                    <input type="hidden" name="project_update" id="project_update" value="yes" />
                                    <input type="hidden" id="project_hash" name="project_hash" value="<?php echo "$project[project_hash]"; ?>">
                                    <input type="hidden" name="step" value="100">
                                    <div class="align"><input type="submit" value="Save mappings" class="save" /></div>
                                <?php
                                } else {
                                ?>
                                    <input type="hidden" id="project_hash" name="project_hash" value="<?php echo "$project[project_hash]"; ?>">
                                    <input type="hidden" name="step" value="4">
                                    <div class="align"><input type="submit" value="Save mappings" class="save" /></div>
                                <?php
                                }
                                ?>
                            </td>
                        </tr>

                    </form>

                </table>
            </div>
        </div>
    </div>
</div>