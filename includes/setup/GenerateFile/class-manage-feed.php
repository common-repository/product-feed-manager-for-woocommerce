<?php
$cron_projects            = get_option('convpfm_cron_files');
$plugin_data              = get_plugin_data(__FILE__);
$nonce = wp_create_nonce('convpfm_ajax_nonce');
?>
<style>
    .convpfm-product-manage-feed-form {
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
        width: 24.99px;
        height: 22.5px;
        top: 8.5px;
        left: 3.75px;
        margin-left: 3px;
        margin-top: 2px;
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

    .convpfm-manage-feed-table {
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

    .manage-feed-table {
        padding: 2px;
        border: 1px solid #CBCDD0;
        border-radius: 8px;
    }

    .table-pro {
        width: 100%;
        border: none;
        padding: 0px 0px 8px 0px;
        gap: 16px;
        box-shadow: 2px 2px 4px 0px #00000014;
    }

    .manage-feed-theading {
        width: 98%;
        height: 48px;
        padding: 12px 16px;
        gap: 16px;
        background-color: #F5F6F6;
    }

    .manage-feed-row {
        width: 100%;
        height: 38px;
        border: 0px 0px 1px 0px;
        padding: 0px 16px 0px 16px;
        background-color: white;
    }

    .manage-feed-settings {
        width: 90%;
        margin-left: 4px;
        height: 170px;
        border: none;
        padding: 16px;
        gap: 16px;
    }
    .rich-blue {
        height: 36px;
    }
</style>
<div class="wrap">
    <div class="convpfm-product-manage-feed-form">
        <tbody class="woo-product-feed-pro-body">
            <?php
            if (array_key_exists('debug', $_GET)) {

                // KILL SWITCH, THIS WILL REMOVE ALL YOUR FEED PROJECTS
                // delete_option( 'cron_projects');

            } elseif (array_key_exists('force-active', $_GET)) {
                // Force active all feeds
                foreach ($cron_projects as $key => $value) {
                    $cron_projects[$key]['active'] = 'true';
                }
                update_option('convpfm_cron_files', $cron_projects, 'no');
            } elseif (array_key_exists('force-clean', $_GET)) {
                if (current_user_can('manage_options')) {
                    // Forcefully remove all feed and plugin configurations
                    delete_option('convpfm_cron_files');
                    delete_option('convpfm_channels');
                    wp_clear_scheduled_hook('convpfm_cron_hook');
                }
            } elseif (array_key_exists('force-deduplication', $_GET)) {
                // Force deduplication
                foreach ($cron_projects as $key => $value) {
                    $channel_hash       = $cron_projects[$key]['channel_hash'];
                    $channel_duplicates = 'convpfm_duplicates_' . $channel_hash;
                    delete_option($channel_duplicates);
                }
            }

            // Double check if the convpfm_cron_hook is there, when it is not create a new one
            if (!wp_next_scheduled('convpfm_cron_hook')) {
                wp_schedule_event(time(), 'hourly', 'convpfm_cron_hook');
            }
            ?>

            <div class="heading-wrapper">
                <div style="display: inline-block; width: 30px; height: 30px; vertical-align: middle">
                    <?php echo '<img class = "image" src="' . plugins_url('images/Manage-feed.png', __FILE__) . '" alt="My Image">'; ?>
                </div>
                <div style="display: inline-block; vertical-align: middle">
                    <span class="heading"><?php esc_html_e('Manage Feeds', 'product-feed-manager-for-woocommerce'); ?></span>
                </div>
                <div>
                    <span class="description"><?php esc_html_e('Manage your product feeds to keep your online store information up-to-date and accurate. This includes adding new products, updating prices, and ensuring all product details meet platform requirements. Effective feed management helps your products appear correctly on shopping channels and improves your visibility to potential customers.
', 'product-feed-manager-for-woocommerce'); ?></span>
                </div>
            </div>
            <div class="convpfm-manage-feed-table">
                <div class="woo-product-feed-pro-table-left">
                    <div class="manage-feed-table">
                        <table id="convpfm_main_table" class="table-pro">
                            <tr class="manage-feed-theading">
                                <td style="width:7%; margin:0px 10px 0px 5px;" class="p-1 text-center"><strong><?php esc_html_e('Active', 'product-feed-manager-for-woocommerce'); ?></strong></td>
                                <td style="width:15%; margin:0px 10px 0px 5px;"><strong><?php esc_html_e('Projects', 'product-feed-manager-for-woocommerce'); ?></strong></td>
                                <td style="width:15%; margin:0px 10px 0px 5px;"><strong><?php esc_html_e('Channel', 'product-feed-manager-for-woocommerce'); ?></strong></td>
                                <td style="width:15%; margin:0px 10px 0px 5px;"><strong><?php esc_html_e('Format', 'product-feed-manager-for-woocommerce'); ?></strong></td>
                                <td style="width:15%; margin:0px 10px 0px 5px;"><strong><?php esc_html_e('Refresh interval', 'product-feed-manager-for-woocommerce'); ?></strong></td>
                                <td style="width:15%; margin:0px 10px 0px 5px;"><strong><?php esc_html_e('Status', 'product-feed-manager-for-woocommerce'); ?></strong></td>
                                <td style="width:33%; margin:0px 10px 0px 5px;"><strong><?php esc_html_e('Actions', 'product-feed-manager-for-woocommerce'); ?></strong></td>
                            </tr>

                            <?php
                            if ($cron_projects) {
                                $toggle_count = 1;
                                $class        = '';

                                foreach ($cron_projects as $key => $val) {
                                    if (isset($val['active']) && ($val['active'] == 'true')) {
                                        $checked = 'checked';
                                        $class   = '';
                                    } else {
                                        $checked = '';
                                    }

                                    if (isset($val['filename'])) {
                                        $projectname = ucfirst($val['projectname']);
                            ?>
                                        <form action="" method="post">
                                            <?php wp_nonce_field('convpfm_ajax_nonce'); ?>
                                            <tr class="<?php echo "$class"; ?> manage-feed-row" style="text-align: center; margin: 5px 0px 0 5px;">
                                                <td class="text-center p-1">
                                                    <label class="woo-product-feed-pro-switch">
                                                        <input type="hidden" name="manage_record" value="<?php echo "$val[project_hash]"; ?>"><input type="checkbox" name="project_active[]" class="checkbox-field" value="<?php echo "$val[project_hash]"; ?>" <?php echo "$checked"; ?>>
                                                        <div class="woo-product-feed-pro-slider round"></div>
                                                    </label>
                                                </td>
                                                <td class="p-1 text-start"><span><?php echo "$projectname</span>"; ?></span></td>
                                                <td class="p-1 text-start"><span><?php echo "<span>$val[name]</span>"; ?></span></td>
                                                <td class="p-1 text-start"><span><?php echo "$val[fileformat]"; ?></span></td>
                                                <td class="p-1 text-start"><span><?php echo "$val[cron]"; ?></span></td>
                                                <?php
                                                if ($val['running'] == 'processing') {
                                                    $proc_perc = round(($val['nr_products_processed'] / $val['nr_products']) * 100);
                                                    echo "<td class='text-start p-1'><span class=\"woo-product-feed-pro-blink_me\" id=\"convpfm_proc_$val[project_hash]\">$val[running] ($proc_perc%)</span></td>";
                                                } else {
                                                    echo "<td class='text-start p-1'><span class=\"woo-product-feed-pro-blink_off_$val[project_hash]\" id=\"convpfm_proc_$val[project_hash]\">$val[running]</span></td>";
                                                }
                                                ?>
                                                <td class="p-1 text-start">
                                                    <div class="actions">
                                                        <span class="gear dashicons dashicons-admin-generic" id="gear_<?php echo "$val[project_hash]"; ?>" title="project settings" style="display: inline-block;"></span>
                                                        <?php
                                                        if ($val['running'] != 'processing') {
                                                        ?>
                                                            <?php
                                                            if ($val['active'] == 'true') {
                                                                echo "<span class=\"dashicons dashicons-admin-page\" id=\"convpfm-copy_$val[project_hash]\" title=\"copy project\" style=\"display: inline-block;\"></span>";
                                                                echo "<span class=\"dashicons dashicons-update\" id=\"convpfm-refresh_$val[project_hash]\" title=\"manually refresh productfeed\" style=\"display: inline-block;\"></span>";

                                                                if ($val['running'] != 'not run yet') {
                                                                    echo "<a href=\"$val[external_file]\" target=\"_blank\" class=\"dashicons dashicons-download\" id=\"download\" title=\"download productfeed\" style=\"display: inline-block\"></a>";
                                                                }
                                                            }
                                                            ?>
                                                            <span class="trash dashicons dashicons-trash" id="convpfm-trash_<?php echo "$val[project_hash]"; ?>" title="delete project and productfeed" style="display: inline-block;"></span>
                                                            <?php
                                                            if ($val['fields'] == 'google_shopping') {
                                                            ?>
                                                                <!--    
                                                                            <a href="admin.php?page=woo-product-feed-pro&action=edit_project&step=11&project_hash=<?php echo "$val[project_hash]"; ?>&channel_hash=<?php echo "$val[channel_hash]"; ?>" class="dashicons dashicons-warning" id="warning_<?php echo "$val[project_hash]"; ?>" title="check notifications" style="display: inline-block;" target="_blank"></a>
                                        -->
                                                            <?php
                                                            }
                                                            ?>
                                                        <?php
                                                        } else {
                                                            echo "<span class=\"dashicons dashicons-dismiss\" id=\"convpfm-cancel_$val[project_hash]\" title=\"cancel processing productfeed\" style=\"display: inline-block;\"></span>";
                                                        }
                                                        ?>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td id="manage_inline" colspan="8" class="text-start">
                                                    <div style="border:1px solid #CBCDD0; border-radius: 8px; width:97%; margin-left:33px">
                                                        <table class="manage-feed-settings">

                                                            <?php
                                                            if (($val['running'] == 'ready') || ($val['running'] == 'stopped') || ($val['running'] == 'not run yet')) {
                                                            ?>
                                                                <tr>
                                                                    <td class="text-start p-1">
                                                                        <strong style="font-weight:600;font-size: 16px;line-height:24px;"><?php esc_html_e('Settings', 'product-feed-manager-for-woocommerce'); ?></strong><br />
                                                                        <span class="dashicons dashicons-arrow-right" style="display: inline-block;"></span> <a href="admin.php?page=convpfm-generate-file&action=edit_project&step=0&project_hash=<?php echo "$val[project_hash]"; ?>&channel_hash=<?php echo "$val[channel_hash]"; ?>"><?php esc_html_e('General feed settings', 'product-feed-manager-for-woocommerce'); ?></a><br />
                                                                        <?php
                                                                        if ($val['fields'] == 'standard') {
                                                                            echo "<span class=\"dashicons dashicons-arrow-right\" style=\"display: inline-block;\"></span> <a href=\"admin.php?page=convpfm-generate-file&action=edit_project&step=2&project_hash=$val[project_hash]&channel_hash=$val[channel_hash]\">";
                                                                            esc_html_e('Attribute selection', 'product-feed-manager-for-woocommerce');
                                                                            print '</a></br/>';
                                                                        } else {
                                                                            echo "<span class=\"dashicons dashicons-arrow-right\" style=\"display: inline-block;\"></span> <a href=\"admin.php?page=convpfm-generate-file&action=edit_project&step=7&project_hash=$val[project_hash]&channel_hash=$val[channel_hash]\">";
                                                                            esc_html_e('Attribute mapping', 'product-feed-manager-for-woocommerce');
                                                                            print '</a><br/>';
                                                                        }

                                                                        if ($val['taxonomy'] != 'none') {
                                                                            echo "<span class=\"dashicons dashicons-arrow-right\" style=\"display: inline-block;\"></span> <a href=\"admin.php?page=convpfm-generate-file&action=edit_project&step=1&project_hash=$val[project_hash]&channel_hash=$val[channel_hash]\">";
                                                                            esc_html_e('Category mapping', 'product-feed-manager-for-woocommerce');
                                                                            print '</a><br/>';
                                                                        }
                                                                        ?>

                                                                        <?php
                                                                        if ((isset($add_manipulation_support)) && ($add_manipulation_support == 'yes')) {
                                                                        ?>
                                                                            <span class="dashicons dashicons-arrow-right" style="display: inline-block;"></span> <a href="admin.php?page=convpfm-generate-file&action=edit_project&step=9&project_hash=<?php echo "$val[project_hash]"; ?>&channel_hash=<?php echo "$val[channel_hash]"; ?>"><?php esc_html_e('Product data manipulation', 'product-feed-manager-for-woocommerce'); ?></a><br />
                                                                        <?php
                                                                        }
                                                                        ?>
                                                                        <span class="dashicons dashicons-arrow-right" style="display: inline-block;"></span> <a href="admin.php?page=convpfm-generate-file&action=edit_project&step=4&project_hash=<?php echo "$val[project_hash]"; ?>&channel_hash=<?php echo "$val[channel_hash]"; ?>"><?php esc_html_e('Apply filters', 'product-feed-manager-for-woocommercewoo-product-feed-pro'); ?></a><br />
                                                                    </td>
                                                                </tr>
                                                            <?php
                                                            }
                                                            ?>
                                                            <tr>
                                                                <td>
                                                                    <strong style="font-weight:600;font-size: 16px;line-height:24px;"><?php esc_html_e('Feed URL', 'product-feed-manager-for-woocommerce'); ?></strong><br />
                                                                    <?php
                                                                    if (($val['active'] == 'true') && ($val['running'] != 'not run yet')) {
                                                                        echo "<span class=\"dashicons dashicons-arrow-right\" style=\"display: inline-block;\"></span> <a href=\"$val[external_file]\" target=\"_blank\">$val[external_file]</a>";
                                                                    } else {
                                                                        print '<span class="dashicons dashicons-warning"></span> Whoops, there is no active product feed for this project as the project has been disabled or did not run yet.';
                                                                    }
                                                                    ?>
                                                                </td>
                                                            </tr>

                                                        </table>
                                                    </div>
                                                </td>
                                            </tr>
                                        </form>
                                <?php
                                        ++$toggle_count;
                                    } else {
                                        // Removing this partly configured feed as it results in PHP warnings
                                        unset($cron_projects[$key]);
                                        update_option('convpfm_cron_files', $cron_projects, 'no');
                                    }
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="6"><br />
                                        <span class="dashicons dashicons-warning"></span> <?php esc_html_e("You haven't configured a product feed yet", 'product-feed-manager-for-woocommerce'); ?>,
                                        <a href="admin.php?page=convpfm-generate-file">
                                            <?php
                                            printf(
                                                // translators: %s: close <a> tag
                                                esc_html__('please create one first%s or read our tutorial on', 'product-feed-manager-for-woocommerce'),
                                                '</a>',
                                            );
                                            ?>
                                    </td>
                                </tr>
                            <?php
                            }
                            ?>
                        </table>
                    </div>
                </div>
        </tbody>
    </div>
</div>