<?php
/**
 * Add New Dynamic Attribute View
 *
 * @link       https://webappick.com/
 * @since      1.0.0
 *
 * @package    Woo_Feed
 * @subpackage Woo_Feed/admin/partial
 * @author     Ohidul Islam <wahid@webappick.com>
 */

$wooFeedDropDown = new Woo_Feed_Dropdown_Pro();
$value           = '';
if ( isset( $_GET['action'] ) && isset( $_GET['dattribute'] ) ) { // phpcs:ignore
	$option = get_option( sanitize_text_field( $_GET['dattribute'] ) ); // phpcs:ignore
	$value  = maybe_unserialize( $option );
}
?>
<div class="wrap">
	<h2><?php _e( 'Dynamic Attribute', 'woo-feed' ); ?></h2>
	<?php WPFFWMessage()->displayMessages(); ?>
	<form action="" name="feed" id="dynamic-attribute-form" method="post" autocomplete="off">
		<?php wp_nonce_field( 'woo-feed-dynamic-attribute' ); ?>
		<table class="widefat fixed">
			<tbody>
			<tr>
				<td ><b><?php _e( 'Attribute Name', 'woo-feed' ); ?><span class="requiredIn">*</span></b></td>
				<td>
					<input wftitle="<?php esc_attr_e( 'Type Attribute Name', 'woo-feed' ); ?>" type="text" name="wfDAttributeName" required="required" class="wfmasterTooltip" value="<?php echo isset( $value['wfDAttributeName'] ) ? esc_attr( $value['wfDAttributeName'] ) : ''; ?>">
				</td>
			</tr>
			<tr>
				<td><label for="wfDAttributeCode"></label><b><?php _e( 'Attribute Code', 'woo-feed' ); ?><span class="requiredIn">*</span></b></td>
				<td>
					<input id="wfDAttributeCode" wftitle="<?php esc_attr_e( 'Attribute Code should be unique and don\'t use space. Otherwise it will override the existing Attribute Code. Example: newPrice or new_price', 'woo-feed' ); ?>" class="wfmasterTooltip" type="text" name="wfDAttributeCode" value="<?php echo isset( $value['wfDAttributeName'] ) ? esc_attr( $value['wfDAttributeCode'] ) : ''; ?>" required="required">
				</td>
			</tr>

			</tbody>
		</table>
		<br/>
		<table class="widefat fixed  sorted_table"  id="table-1">
			<thead>
			<tr>
				<th></th>
				<th><?php _e( 'Attributes', 'woo-feed' ); ?></th>
				<th><?php _e( 'Condition', 'woo-feed' ); ?></th>
				<th></th>
				<th><?php _e( 'Output Type', 'woo-feed' ); ?></th>
				<th><?php _e( 'Prefix', 'woo-feed' ); ?></th>
				<th><?php _e( 'Value', 'woo-feed' ); ?></th>
				<th><?php _e( 'Suffix', 'woo-feed' ); ?></th>
				<th></th>
			</tr>
			</thead>
			<tbody>
			<tr style="display:none;" class="daRow">
				<td>
					<i class="wf_sortedtable dashicons dashicons-menu"></i>
				</td>
				<td>
					<select name="attribute[]"  disabled required class="fsrow">
						<?php
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo $wooFeedDropDown->product_attributes_dropdown();
						?>
					</select>
				</td>
				<td>
					<select name="condition[]" disabled class="fsrow woo_feed_dynamic_attr_condition_select">
						<?php foreach ( woo_feed_get_conditions() as $k => $v ) { ?>
							<option value="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $v ); ?></option>
						<?php } ?>
					</select>
				</td>
				<td>
					<input type="text" name="compare[]" disabled class="fsrow woo_feed_dynamic_attr_condition_value">
				</td>
				<td>
					<select name="type[]" class="dType fsrow" disabled>
						<option value="attribute"><?php _e( 'Attribute', 'woo-feed' ); ?></option>
						<option value="pattern"><?php _e( 'Pattern', 'woo-feed' ); ?></option>
					</select>
				</td>
				<td>
					<input type="text" name="prefix[]" disabled class="fsrow">
				</td>
				<td>
					<select name="value_attribute[]"  disabled class="value_attribute fsrow">
						<?php
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo $wooFeedDropDown->product_attributes_dropdown();
								?>
					</select>
					<input type="text" name="value_pattern[]" disabled  style="display: none;" class="value_pattern fsrow">
				</td>
				<td>
					<input type="text" name="suffix[]" disabled class="fsrow">
				</td>
				<td>
					<span class="delRow dashicons dashicons-trash"></span>
				</td>
			</tr>
			<?php
			$default_type = 'attribute';
			if ( isset( $value['default_value_attribute'] ) ) {
				$default_type            = $value['default_type'];
				$default_value_attribute = $value['default_value_attribute'];
				$default_value_pattern   = $value['default_value_pattern'];
			}
			if ( isset( $value['attribute'] ) ) {
				$attributes      = $value['attribute'];
				$condition       = $value['condition'];
				$compare         = $value['compare'];
				$prefix          = isset( $value['prefix'] ) ? $value['prefix'] : '';
				$suffix          = isset( $value['suffix'] ) ? $value['suffix'] : '';
				$attr_type       = $value['type'];
				$value_attribute = $value['value_attribute'];
				$value_pattern   = $value['value_pattern'];

				foreach ( $attributes as $key => $attribute ) {
					?>
					<tr class="daRow">
						<td>
							<i class="wf_sortedtable dashicons dashicons-menu"></i>
						</td>
						<td>
							<select name="attribute[]"  required class=''>
								<?php
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo $wooFeedDropDown->product_attributes_dropdown( $attributes[ $key ] );
								?>
							</select>
						</td>
						<td>
							<select name="condition[]" class="woo_feed_dynamic_attr_condition_select">
								<?php foreach ( woo_feed_get_conditions() as $k => $v ) { ?>
									<option value="<?php echo esc_attr( $k ); ?>"<?php selected( $condition[ $key ], $k ); ?>><?php echo esc_html( $v ); ?></option>
								<?php } ?>
							</select>
						</td>
						<td>
							<input type="text" value="<?php echo isset( $compare[ $key ] ) ? esc_attr( $compare[ $key ] ) : ''; ?>"
							name="compare[]"
							<?php
							if ( isset( $condition[ $key ] ) && 'between' == $condition[ $key ] ) {
								echo 'placeholder="Ex: 10,20"';
							}
							?>
                            class="woo_feed_dynamic_attr_condition_value">
						</td>
						<td>
							<select name="type[]" class="dType">
								<option <?php echo isset( $attr_type[ $key ] ) && 'attribute' == $attr_type[ $key ] ? 'selected="selected"' : ''; ?>value="attribute"><?php _e( 'Attribute', 'woo-feed' ); ?></option>
								<option <?php echo isset( $attr_type[ $key ] ) && 'pattern' == $attr_type[ $key ] ? 'selected="selected"' : ''; ?>value="pattern"><?php _e( 'Pattern', 'woo-feed' ); ?></option>
							</select>
						</td>
						<td>
							<input type="text" value="<?php echo isset( $prefix[ $key ] ) ? esc_attr( $prefix[ $key ] ) : ''; ?>" name="prefix[]" class=''>
						</td>
						<td>
							<select name="value_attribute[]"  class="value_attribute" style="<?php if ( 'attribute' != $attr_type[ $key ] ) echo 'display:none'; ?>">
								<?php
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo $wooFeedDropDown->product_attributes_dropdown( $value_attribute[ $key ] );
								?>
							</select>
							<input type="text" name="value_pattern[]" value="<?php echo isset( $value_pattern[ $key ] ) ? esc_attr( $value_pattern[ $key ] ) : ''; ?>" style="<?php if ( 'pattern' != $attr_type[ $key ] ) echo 'display:none;'; ?>" class="value_pattern">

						</td>
						<td>
							<input type="text" value="<?php echo isset( $suffix[ $key ] ) ? esc_attr( $suffix[ $key ] ) : ''; ?>" name="suffix[]" class=''>
						</td>
						<td>
							<span class="delRow dashicons dashicons-trash"></span>
						</td>
					</tr>
					<?php
				}
			}
			?>

			</tbody>
			<tfoot>
			<tr>
				<td></td>
				<td></td>
				<td></td>
				<td style="text-align: right;">Default</td>
				<td>
					<select name="default_type" class="dType">
						<option <?php echo isset( $default_type ) && 'attribute' == $default_type ? 'selected="selected"' : ''; ?>value="attribute"><?php _e( 'Attribute', 'woo-feed' ); ?></option>
						<option <?php echo isset( $default_type ) && 'pattern' == $default_type ? 'selected="selected"' : ''; ?>value="pattern"><?php _e( 'Pattern', 'woo-feed' ); ?></option>
					</select>
				</td>
				<td></td>
				<td>
					<select name="default_value_attribute" style="<?php if ( isset( $default_type ) && 'attribute' != $default_type ) echo 'display:none'; ?>" class="value_attribute">
						<?php
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo $wooFeedDropDown->product_attributes_dropdown( isset( $default_value_attribute ) ? $default_value_attribute : '' );
						?>
					</select>
					<input type="text" name="default_value_pattern" value="<?php echo isset( $default_value_pattern ) ? esc_attr( $default_value_pattern ) : ''; ?>" style="<?php if ( ( isset( $default_type ) && 'pattern' != $default_type ) || 'add-attribute' == $_GET['action'] ) echo 'display:none;'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>" class="value_pattern">
				</td>
				<td></td>
				<td></td>
			</tr>
			<tr>
				<td>
					<button type="button" class="button-small button-primary" id="wf_newCon"><?php _e( 'Add Condition', 'woo-feed' ); ?></button>
				</td>
				<td colspan="8"></td>
			</tr>
			</tfoot>
		</table>
		<table class=" widefat fixed">
			<tr>
				<td style="text-align: right;">
					<button type="submit" class="wfbtn" name="<?php echo isset( $_GET['action'] ) ? esc_attr( sanitize_text_field( $_GET['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>"><?php _e( 'Save', 'woo-feed' ); ?></button>
				</td>
			</tr>
		</table>
	</form>
</div>
