<?php
/**
 * Dynamic Attribute List View
 *
 * @link       https://webappick.com/
 * @since      1.0.0
 *
 * @package    Woo_Feed
 * @subpackage Woo_Feed/admin/partial
 * @author     Ohidul Islam <wahid@webappick.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}
$myListTable = new Woo_Feed_DAttribute_list();
$myListTable->prepare_items();
global $plugin_page;
?>
<div class="wrap">
	<h2><?php esc_html_e( 'Dynamic Attribute List', 'woo-feed' ); ?>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=webappick-manage-feed-attribute&action=add-attribute&_wpnonce=' . wp_create_nonce( 'woo-feed-dynamic-attribute' ) ) ); ?>" class="page-title-action"><?php esc_html_e( 'Add New', 'woo-feed' ); ?></a>
	</h2>
	<?php WPFFWMessage()->displayMessages(); ?>
	<form id="contact-filter" method="post">
		<!-- For plugins, we also need to ensure that the form posts back to our current page -->
		<input type="hidden" name="page" value="<?php echo esc_attr( $plugin_page ); ?>"/>
		<!-- Now we can render the completed list table -->
		<?php $myListTable->display(); ?>
	</form>
</div>

<script type="text/javascript">
	(function($){
		"use strict";
		$(document).ready(function () {
			$(document).on( 'click', '.single-dattribute-del', function ( event ) {
				event.preventDefault();
				if ( confirm( '<?php esc_attr_e( 'Are You Sure to Delete?', 'woo-feed' ); ?>' ) ) {
					window.location.href = jQuery(this).attr('val');
				}
			});
			$('#doaction').on( 'click', function () {
				return confirm('<?php esc_attr_e( 'Are You Sure to Delete?', 'woo-feed' ); ?>' );
			});
			$('#doaction2').on( 'click', function () {
				return confirm( '<?php esc_attr_e( 'Are You Sure to Delete?', 'woo-feed' ); ?>' );
			});
		});
	})(jQuery);
</script>
