<?php
/**
 * This class sets the wp_cron with intervals
 */
class Convpfm_Cron {
        /**
         * Function for setting a cron job for regular creation of the feed
         * Will create a new event when an old one exists, which will be deleted first
         */
        function convpfm_cron_scheduling ( $scheduling ) {
                if (!wp_next_scheduled( 'convpfm_cron_hook' ) ) {
                        wp_schedule_event ( time(), 'hourly', 'convpfm_cron_hook');
                } else {
                        wp_schedule_event ( time(), 'hourly', 'convpfm_cron_hook');
                }
        }
}
