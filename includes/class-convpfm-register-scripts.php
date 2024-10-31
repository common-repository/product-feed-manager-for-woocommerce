<?php
/**
 * TVC Register Scripts Class.
 *
 * @package TVC Product Feed Manager/Classes
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
if ( ! class_exists( 'Convpfm_Register_Scripts' ) ) :
  /**
   * Register Scripts Class
   */
  class Convpfm_Register_Scripts {
    public function __construct() {    
        // only load the next hooks when on the Settings page
      if ( isset($_GET['page']) && strpos(sanitize_text_field($_GET['page']), 'convpfm') !== false) {
        add_action( 'admin_enqueue_scripts', array( $this, 'convpfm_register_required_options_page_scripts' ) );
      }
    } 
    
    /**
     * Registers all required java scripts for the feed manager Settings page.
     */
    public function convpfm_register_required_options_page_scripts() {
      // enqueue notice handling script
      ?>
      <script>
        var convpfm_ajax_url = '<?php echo esc_js(admin_url( 'admin-ajax.php' )); ?>';
      </script>
      <?php
    }
  }
// End of TVC_Register_Scripts class
endif;
$my_ajax_registration_class = new Convpfm_Register_Scripts();
?>