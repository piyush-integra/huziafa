<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       http://www.intolap.com
 * @since      1.2
 *
 * @package    Country_Code_Selector
 * @subpackage Country_Code_Selector/public/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<script>
    var input = document.querySelector("#billing_phone");
    window.intlTelInput(input, {
        hiddenInput: "billing_phone",
        separateDialCode: true,
        utilsScript: "<?php echo esc_url( plugin_dir_url( __FILE__ ) .'/public/assets/js/wc-utils.js' );?>",
    });
</script>