<?php
/**
 * Content Settings Table
 *
 * @package WooFeed
 * @subpackage Editor
 * @version 1.0.0
 * @since WooFeed 3.2.6
 * @author KD <mhamudul.hk@gmail.com>
 * @copyright 2019 WebAppick <support@webappick.com>
 */
if ( ! defined( 'ABSPATH' ) ) {
	die(); // silence
}
/**
 * @global array $feedRules
 * @global Woo_Feed_Dropdown_Pro $wooFeedDropDown
 * @global Woo_Feed_Merchant_Pro $merchant
 */
global $feedRules, $wooFeedDropDown, $merchant;
?>
<table class="widefat fixed">
	<thead>
		<tr>
			<th colspan="2"><?php _e( 'Content Settings', 'woo-feed' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<th><label for="provider"><?php _e( 'Template', 'woo-feed' ); ?> <span class="requiredIn">*</span></label></th>
			<td>
				<select wftitle="<?php esc_attr_e( 'Select a template', 'woo-feed' ); ?>" name="provider" id="provider" class="generalInput wfmasterTooltip" required>
					<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo $wooFeedDropDown->merchantsDropdown( $feedRules['provider'] );
					?>
				</select>
			</td>
		</tr>
		<tr>
			<th><label for="filename"><?php _e( 'File Name', 'woo-feed' ); ?> <span class="requiredIn">*</span></label></th>
			<td>
				<input name="filename" value="<?php echo isset( $feedRules['filename'] ) ? esc_attr( $feedRules['filename'] ) : ''; ?>" type="text" id="filename" class="generalInput wfmasterTooltip" wftitle="<?php esc_attr_e( 'Filename should be unique. Otherwise it will override the existing filename.', 'woo-feed' ); ?>" required>
			</td>
		</tr>
		<tr>
			<th><label for="feedType"><?php _e( 'Feed Type', 'woo-feed' ); ?> <span class="requiredIn">*</span></label></th>
			<td>
				<select name="feedType" id="feedType" class="generalInput" required>
					<option value=""></option>
					<?php
					foreach ( woo_feed_get_file_types() as $file_type => $label ) {
						printf( '<option value="%1$s" %3$s>%2$s</option>', esc_attr( $file_type ), esc_html( $label ), selected( $feedRules['feedType'], $file_type, false ) );
					}
					?>
				</select>
				<span class="spinner" style="float: none; margin: 0;"></span>
			</td>
		</tr>
		<tr>
			<th><label for="is_variations"><?php _e( 'Include Variations?', 'woo-feed' ); ?> <span class="requiredIn">*</span></label></th>
			<td>
				<select name="is_variations" id="is_variations" class="WFisVariations generalInput">
					<?php
					foreach ( woo_feed_get_variable_visibility_options() as $k => $v ) {
						/** @noinspection HtmlUnknownAttribute */
						printf( '<option value="%1$s" %3$s>%2$s</option>', esc_attr( $k ), esc_html( $v ), selected( $feedRules['is_variations'], $k, false ) );
					}
					?>
				</select>
			</td>
		</tr>
		<tr class="WFVariablePriceTR">
			<th><label for="variable_price"><?php _e( 'Variable Product Price', 'woo-feed' ); ?></label></th>
			<td>
				<select name="variable_price" id="variable_price" class="generalInput">
					<?php
					foreach ( woo_feed_get_variable_price_options() as $k => $v ) {
						/** @noinspection HtmlUnknownAttribute */
						printf( '<option value="%1$s" %3$s>%2$s</option>', esc_attr( $k ), esc_html( $v ), selected( $feedRules['variable_price'], $k, false ) );
					}
					?>
				</select>
			</td>
		</tr>
		<tr>
			<th><label for="variable_quantity"><?php _e( 'Variable Product Quantity', 'woo-feed' ); ?></label></th>
			<td>
				<select name="variable_quantity" id="variable_quantity" class="generalInput">
					<?php
					foreach ( woo_feed_get_variable_quantity_options() as $k => $v ) {
						/** @noinspection HtmlUnknownAttribute */
						printf( '<option value="%1$s" %3$s>%2$s</option>', esc_attr( $k ), esc_html( $v ), selected( $feedRules['variable_quantity'], $k, false ) );
					}
					?>
				</select>
			</td>
		</tr>
		<?php
		$languages = $wooFeedDropDown->getActiveLanguages( $feedRules['feedLanguage'] );
		if ( ! empty( $languages ) ) {
			?>
			<tr>
				<th><label for="feedLanguage"><?php _e( 'Language', 'woo-feed' ); ?> <span class="requiredIn">*</span></label></th>
				<td>
					<select name="feedLanguage" id="feedLanguage" class="generalInput" required>
						<?php
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo $languages;
						?>
					</select>
				</td>
			</tr>
		<?php } ?>
		<?php
		$currencies = $wooFeedDropDown->getActiveCurrencies( $feedRules['feedCurrency'] );
		if ( ! empty( $currencies ) ) {
			?>
			<tr>
				<th><label for="feedCurrency"><?php _e( 'Currency', 'woo-feed' ); ?> <span class="requiredIn">*</span></label></th>
				<td>
					<select name="feedCurrency" id="feedCurrency" class="generalInput" required>
						<?php
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo $currencies;
						?>
					</select>
				</td>
			</tr>
		<?php } ?>
		<tr class="itemWrapper" style="display: none;">
			<th><label for="itemsWrapper"><?php _e( 'Items Wrapper', 'woo-feed' ); ?> <span class="requiredIn">*</span></label></th>
			<td>
				<input name="itemsWrapper" id="itemsWrapper" type="text" value="<?php echo esc_attr( $feedRules['itemsWrapper'] ); ?>" class="generalInput" required="required">
			</td>
		</tr>
		<tr class="itemWrapper" style="display: none;">
			<th><label for="itemWrapper"><?php _e( 'Single Item Wrapper', 'woo-feed' ); ?> <span class="requiredIn">*</span></label></th>
			<td>
				<input name="itemWrapper" id="itemWrapper" type="text" value="<?php echo esc_attr( $feedRules['itemWrapper'] ); ?>" class="generalInput" required="required">
			</td>
		</tr>
		<?php
		/*
		<tr class="itemWrapper" style="display: none;">
			<th><label for="extraHeader"><?php _e( 'Extra Header', 'woo-feed' ); ?> </label></th>
			<td>
				<textarea name="extraHeader" id="extraHeader"  style="width: 100%" placeholder="<?php esc_html_e( 'Insert Extra Header value. Press enter at the end of each line.', 'woo-feed' ); ?>" rows="3"><?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo isset( $feedRules['extraHeader'] ) ? $feedRules['extraHeader'] : '';
				?></textarea>
			</td>
		</tr>
		 */
		?>
		<tr class="wf_csvtxt" style="display: none;">
			<th><label for="delimiter"><?php _e( 'Delimiter', 'woo-feed' ); ?> <span class="requiredIn">*</span></label></th>
			<td>
				<select name="delimiter" id="delimiter" class="generalInput">
					<?php
					foreach ( woo_feed_get_csv_delimiters() as $k => $v ) {
						printf( '<option value="%1$s" %3$s>%2$s</option>', esc_attr( $k ), esc_html( $v ), selected( $feedRules['delimiter'], $k, false ) );
					}
					?>
				</select>
			</td>
		</tr>
		<tr class="wf_csvtxt" style="display: none;">
			<th><label for="enclosure"><?php _e( 'Enclosure', 'woo-feed' ); ?> <span class="requiredIn">*</span></label></th>
			<td>
				<select name="enclosure" id="enclosure" class="generalInput">
					<?php
					foreach ( woo_feed_get_csv_enclosure() as $k => $v ) {
						/** @noinspection HtmlUnknownAttribute */
						printf( '<option value="%1$s" %3$s>%2$s</option>', esc_attr( $k ), esc_html( $v ), selected( $feedRules['enclosure'], $k, false ) );
					}
					?>
				</select>
			</td>
		</tr>
		<?php
		$vendors = $wooFeedDropDown->getAllVendors();
		if ( ! empty( $vendors ) ) {
			?>
			<tr>
				<th><label for="wf_product_vendors"><?php _e( 'Select Vendors', 'woo-feed' ); ?></label></th>
				<td>
					<select name="vendors[]" id="wf_product_vendors" class="wf_vendors selectize wf_categories generalInput" data-plugins="remove_button" multiple>
						<?php
						foreach ( $vendors as $vendor ) {
							printf( '<option value="%1$s" %3$s>%2$s</option>', esc_attr( $vendor->ID ), esc_html( $vendor->display_name ), selected( in_array( $vendor->ID, $feedRules['vendors'] ), true, false ) );
						}
						?>
					</select>
					<span style="font-size: x-small"><i><?php esc_html_e( 'Keep blank to select all vendors', 'woo-feed' ); ?></i></span>
				</td>
			</tr>
			<?php
		}
		?>
	</tbody>
</table>
<?php
// End of file woo-feed-content-settings.php
