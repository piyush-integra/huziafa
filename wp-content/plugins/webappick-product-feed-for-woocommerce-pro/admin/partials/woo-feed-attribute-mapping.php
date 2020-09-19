<?php
/**
 * Category Mapping List View
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

$wooFeedDropDown = new Woo_Feed_Dropdown_Pro();

$value = [
	'name'    => '',
	'mapping' => [ '' ],
	'glue'    => '',
];

$option_name = '';
if ( isset( $_GET['action'], $_GET['mapping_name'] ) ) { // phpcs:ignore
	$option_name = sanitize_text_field( $_GET['mapping_name'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$value  = get_option( $option_name );
}
?>
<div class="wrap">
    <h2>
    <?php
        if ( ! empty( $option_name ) ) {
		esc_html_e( 'Edit Attribute Mapping', 'woo-feed' );
        } else {
		esc_html_e( 'New Attribute Mapping', 'woo-feed' );
        }
    ?>
    </h2>
	<?php WPFFWMessage()->displayMessages(); ?>
    <form action="<?php echo esc_url( admin_url( 'admin-post.php?action=_wf_save_attribute_mapping' ) ); ?>" name="feed" id="attribute-mapping-form" method="post" autocomplete="off">
		<?php wp_nonce_field( 'wf-attribute-mapping' ); ?>
        <input type="hidden" name="option_name" value="<?php echo esc_attr( $option_name ); ?>">
        <table class="table widefat ">
            <thead>
            <tr>
                <td></td>
                <th><label for="mapping_name"><?php esc_html_e( 'Mapping Name', 'woo-feed' ); ?> <span class="required" aria-label="<?php esc_attr_e( 'Required Field', 'woo-feed' ); ?>">*</span></label></th>
                <td>
                    <input type="text" id="mapping_name" name="mapping_name" value="<?php echo esc_attr( $value['name'] ); ?>" required>
                </td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <th><label for="mapping_glue"><?php esc_html_e( 'Attribute Separator', 'woo-feed' ); ?></label></th>
                <td>
                    <input type="text" id="mapping_glue" name="mapping_glue" value="<?php echo esc_attr( $value['glue'] ); ?>">
                </td>
                <td></td>
            </tr>
            </thead>
            <tbody>
            <?php if(!empty($value['mapping'])): foreach ( $value['mapping'] as $idx => $attr_map ) { ?>
                <tr>
                    <td>
                        <i class="wf_sortedtable dashicons dashicons-menu" aria-hidden="true"></i>
                    </td>
	                <th>
		                <label for="value_<?php echo absint( $idx + 1 ); ?>"><?php esc_html_e( 'Select Attribute', 'woo-feed' ); ?> <span class="required" aria-label="<?php esc_attr_e( 'Required Field', 'woo-feed' ); ?>">*</span></label>
	                </th>
	                <td>
                        <select name="value[<?php echo absint( $idx + 1 ); ?>]" id="value_<?php echo absint( $idx + 1 ); ?>" class="selectize" data-placeholder="<?php esc_attr_e( 'Search Attribute', 'woo-feed' ); ?>" data-create="true" required>
			                <?php
			                $attributeDropdown = $wooFeedDropDown->product_attributes_dropdown( $attr_map );
			                if ( ! empty( $attr_map ) && false === strpos( $attributeDropdown, 'value="' . $attr_map . '"' ) ) {
                            ?>
                                <option value="<?php echo esc_attr( $attr_map ); ?>" selected><?php echo esc_html( $attr_map ); ?></option>
                            <?php
                            }
			                echo $attributeDropdown; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			                ?>
                        </select>
	                </td>
                    <td>
                        <a href="#" aria-label="<?php esc_attr_e( 'Delete Current Row', 'woo-feed' ); ?>" class="delRow"><i class="dashicons dashicons-trash" aria-hidden="true"></i></a>
                    </td>
                </tr>
            <?php } endif;?>
            </tbody>
            <tfoot>
            <tr>
                <td colspan="4">
                    <script type="text/template" id="wf-attribute-mapping-row-template" aria-hidden="true">
                        <tr>
                            <td>
                                <i class="wf_sortedtable dashicons dashicons-menu" aria-hidden="true"></i>
                            </td>
	                        <th>
		                        <label for="value___idx__"><?php esc_html_e( 'Select Attribute', 'woo-feed' ); ?> <span class="required" aria-label="<?php esc_attr_e( 'Required Field', 'woo-feed' ); ?>">*</span></label>
	                        </th>
	                        <td>
		                        <select name="value[__idx__]" id="value___idx__" class="selectize" data-placeholder="<?php esc_attr_e( 'Search Attribute', 'woo-feed' ); ?>" data-create="true" required>
			                        <?php
			                        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			                        echo $wooFeedDropDown->product_attributes_dropdown();
			                        ?>
		                        </select>
	                        </td>
	                        <td>
		                        <a href="#" aria-label="<?php esc_attr_e( 'Delete Current Row', 'woo-feed' ); ?>" class="delRow"><i class="dashicons dashicons-trash" aria-hidden="true"></i></a>
	                        </td>
                        </tr>
                    </script>
                    <button type="button" class="button-small button-primary wf-add-row" data-template="#wf-attribute-mapping-row-template" data-target="#attribute-mapping-form table tbody" data-idx="<?php echo absint( count( $value['mapping'] ) ); ?>"><?php esc_html_e( 'Add New Row', 'woo-feed' ); ?></button>
                    <button name="save_mapping" type="submit" class="button button-large button-primary"><?php esc_html_e( 'Save Mapping', 'woo-feed' ); ?></button>
                </td>
            </tr>
            </tfoot>
        </table>
    </form>
</div>
