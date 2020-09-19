<?php
if ( ! defined( 'ABSPATH' ) ) {
	die(); // silence
}
?>
<table class="table tree widefat fixed woo-feed-filters">
	<tbody>
	<tr>
		<td><label><?php _e( 'Remove Out Of Stock Products', 'woo-feed' ); ?> <span class="requiredIn">*</span></label></td>
		<td>
			<label><input type="radio"  name="is_outOfStock"  class='' value="y" <?php checked( $feedRules['is_outOfStock'], 'y' ); ?>> <?php esc_attr_e( 'Yes', 'woo-feed' ); ?></label>
			<label><input type="radio" name="is_outOfStock"  class='' value="n" <?php checked( $feedRules['is_outOfStock'], 'n' ); ?>> <?php esc_attr_e( 'No', 'woo-feed' ); ?></label>
			<span class="help"><span class="dashicons dashicons-info" aria-hidden="true"></span> <?php esc_html_e( 'Select Yes to exclude Out-Of-Stock products.', 'woo-feed' ); ?></span>
		</td>
	</tr>
	<tr>
		<td><label for="product_visibility"><?php _e( 'Include Hidden Products', 'woo-feed' ); ?></label></td>
		<td>
			<label><input type="radio" name="product_visibility" value="1"<?php checked( $feedRules['product_visibility'], '1' ); ?>> <?php esc_attr_e( 'Yes', 'woo-feed' ); ?></label>
			<label><input type="radio" name="product_visibility" value="0"<?php checked( $feedRules['product_visibility'], '0' ); ?>> <?php esc_attr_e( 'No', 'woo-feed' ); ?></label>
			<span class="help"><span class="dashicons dashicons-info" aria-hidden="true"></span> <?php esc_html_e( 'Select Yes to include hidden products.', 'woo-feed' ); ?></span>
		</td>
	</tr>
	<tr class="out-of-stock-visibility" style="display: none;">
		<td><label for="outofstock_visibility"><?php _e( 'Override Out-Of-Stock Visibility', 'woo-feed' ); ?></label></td>
		<td>
			<label><input type="radio" name="outofstock_visibility" value="1"<?php checked( $feedRules['outofstock_visibility'], '1' ); ?>> <?php esc_attr_e( 'Yes', 'woo-feed' ); ?></label>
			<label><input type="radio" name="outofstock_visibility" value="0"<?php checked( $feedRules['outofstock_visibility'], '0' ); ?>> <?php esc_attr_e( 'No', 'woo-feed' ); ?></label>
			<span class="help"><span class="dashicons dashicons-info" aria-hidden="true"></span> <?php esc_html_e( 'Select Yes to ignore WooCommerce settings for hiding Out-Of-Stock product form catalog.', 'woo-feed' ); ?></span>
		</td>
	</tr>
	<tr>
		<td><label for="wf_product_post_status"><?php _e( 'Product Status', 'woo-feed' ); ?></label></td>
		<td>
			<div>
				<select name="post_status[]" id="wf_product_post_status" class="generalInput" data-placeholder="<?php esc_attr_e( 'Select Post Status', 'woo-feed' ); ?>" multiple>
					<?php
					foreach ( woo_feed_get_post_statuses() as $key => $value ) {
						/** @noinspection HtmlUnknownAttribute */
						printf( '<option value="%1$s" %3$s>%2$s</option>', esc_attr( $key ), esc_html( $value ), selected( in_array( $key, $feedRules['post_status'] ), true, false ) );
					}
					?>
				</select>
			</div>
			<div>
				<label class="screen-reader-text" for="post_type_filter_mode"><?php _e( 'Filter Type', 'woo-feed' ); ?></label>
				<select name="filter_mode[post_status]" id="post_type_filter_mode" class="filter_mode">
					<option value="include"<?php selected( $feedRules['filter_mode']['post_status'], 'include' ); ?>><?php esc_attr_e( 'Include', 'woo-feed' ); ?></option>
					<option value="exclude"<?php selected( $feedRules['filter_mode']['post_status'], 'exclude' ); ?>><?php esc_attr_e( 'Exclude', 'woo-feed' ); ?></option>
				</select>
			</div>
		</td>
	</tr>
	<tr>
		<td><label for="wf_product_ids"><?php _e( 'Product IDs', 'woo-feed' ); ?></label></td>
		<td>
			<div>
				<textarea type="text" name="product_ids" id="wf_product_ids" class="generalInput"><?php echo esc_attr( $feedRules['product_ids'] ); ?></textarea>
			</div>
			<div>
				<label class="screen-reader-text" for="product_ids_filter_mode"><?php _e( 'Filter Type', 'woo-feed' ); ?></label>
				<select name="filter_mode[product_ids]" id="product_ids_filter_mode" class="filter_mode">
					<option value="include"<?php selected( $feedRules['filter_mode']['product_ids'], 'include' ); ?>><?php esc_attr_e( 'Include', 'woo-feed' ); ?></option>
					<option value="exclude"<?php selected( $feedRules['filter_mode']['product_ids'], 'exclude' ); ?>><?php esc_attr_e( 'Exclude', 'woo-feed' ); ?></option>
				</select>
			</div>
			<span class="help"><span class="dashicons dashicons-info" aria-hidden="true"></span> <?php esc_html_e( 'Multiple Product IDs must be separated by comma.', 'woo-feed' ); ?></span>
		</td>
	</tr>
	<tr>
		<td><label for="wf_product_categories"><?php _e( 'Categories', 'woo-feed' ); ?></label></td>
		<td>
			<div class="">
				<select name="categories[]" id="wf_product_categories" class="wf_catsss selectize wf_categories generalInput" data-placeholder="<?php esc_attr_e( 'Select Categories', 'woo-feed' ); ?>" multiple>
					<?php
					foreach ( $wooFeedDropDown->categories() as $key => $value ) {
						/** @noinspection HtmlUnknownAttribute */
						printf( '<option value="%1$s" %3$s>%2$s</option>', esc_attr( $key ), esc_attr( $value ), selected( in_array( $key, $feedRules['categories'] ), true, false ) );
					}
					?>
				</select>
			</div>
			<div>
				<label class="screen-reader-text" for="category_filter_mode"><?php _e( 'Filter Type', 'woo-feed' ); ?></label>
				<select name="filter_mode[categories]" id="category_filter_mode" class="filter_mode">
					<option value="include"<?php selected( $feedRules['filter_mode']['categories'], 'include' ); ?>><?php esc_attr_e( 'Include', 'woo-feed' ); ?></option>
					<option value="exclude"<?php selected( $feedRules['filter_mode']['categories'], 'exclude' ); ?>><?php esc_attr_e( 'Exclude', 'woo-feed' ); ?></option>
				</select>
			</div>
			<span class="help"><span class="dashicons dashicons-info" aria-hidden="true"></span> <?php esc_html_e( 'Keep blank to select all categories', 'woo-feed' ); ?></span>
		</td>
	</tr>
	<tr>
		<td><label for="wpf_ptitle"><?php _e( 'Extended Product Title', 'woo-feed' ); ?></label></td>
		<td>
			<select name="wpf_ptitle" id="wpf_ptitle" placeholder="<?php esc_attr_e( 'Search Attributes', 'woo-feed' ); ?>" class="generalInput selectize">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $wooFeedDropDown->product_attributes_dropdown();
				?>
			</select>
			<div class="clear"></div>
			<label for="ptitle_show" class="screen-reader-text"><?php esc_html_e( 'Extended Product Title Elements', 'woo-feed' ); ?></label>
			<textarea name="ptitle_show" id="ptitle_show" class="generalInput" cols="29" rows="4"><?php echo esc_attr( stripslashes( $feedRules['ptitle_show'] ) ); ?></textarea>
		</td>
	</tr>
	<tr>
		<td colspan="2"><?php _e( 'Number Format', 'woo-feed' ); ?></td>
	</tr>
	<tr>
		<td style="text-align: right;"><label for="thousand_separator"><?php _e( 'Thousand separator', 'woo-feed' ); ?></label></td>
		<td><input type="text" name="thousand_separator" id="thousand_separator" value="<?php echo esc_attr( $feedRules['thousand_separator'] ); ?>"></td>
	</tr>
	<tr>
		<td style="text-align: right;"><label for="decimal_separator"><?php _e( 'Decimal separator', 'woo-feed' ); ?></label></td>
		<td><input type="text" name="decimal_separator" id="decimal_separator" value="<?php echo esc_attr( $feedRules['decimal_separator'] ); ?>"></td>
	</tr>
	<tr>
		<td style="text-align: right;"><label for="decimals"><?php _e( 'Number of decimal', 'woo-feed' ); ?></label></td>
		<td><input type="number" id="decimals" name="decimals" value="<?php echo absint( $feedRules['decimals'] ); ?>"></td>
	</tr>
	<tr>
		<td colspan="2"><?php _e( 'Campaign URL Builder', 'woo-feed' ); ?></td>
	</tr>
	<tr>
		<td colspan="2">
			<table class="table widefat fixed" id="wf_campaign_url_builder">
				<tbody>
				<tr>
					<td>
						<label class="screen-reader-text" for="utm_source"><?php esc_html_e( 'Campaign Source', 'woo-feed' ); ?> <span class="required" aria-label="<?php esc_attr_e( 'Required', 'woo-feed' ); ?>">*</span></label>
						<input type="text" name="campaign_parameters[utm_source]" id="utm_source" class="regular-text" placeholder="*<?php esc_attr_e( 'Campaign Source', 'woo-feed' ); ?>" value="<?php echo esc_attr( $feedRules['campaign_parameters']['utm_source'] ); ?>">
						<label for="utm_source">
							<span class="description" style="color:#8a8a8a;"><?php esc_html_e( 'The referrer: (e.g. google, newsletter)', 'woo-feed' ); ?></span>
						</label>
					</td>
					<td>
						<label class="screen-reader-text" for="utm_medium"><?php esc_html_e( 'Campaign Medium', 'woo-feed' ); ?> <span class="required" aria-label="<?php esc_attr_e( 'Required', 'woo-feed' ); ?>">*</span></label>
						<input type="text" name="campaign_parameters[utm_medium]" id="utm_medium" class="regular-text" placeholder="*<?php esc_attr_e( 'Campaign Medium', 'woo-feed' ); ?>" value="<?php echo esc_attr( $feedRules['campaign_parameters']['utm_medium'] ); ?>">
						<label for="utm_medium">
							<span class="description" style="color:#8a8a8a;"><?php esc_html_e( 'Marketing medium: (e.g. cpc, banner, email)', 'woo-feed' ); ?></span>
						</label>
					</td>
					<td>
						<label class="screen-reader-text" for="utm_campaign"><?php esc_html_e( 'Campaign Name', 'woo-feed' ); ?> <span class="required" aria-label="<?php esc_attr_e( 'Required', 'woo-feed' ); ?>">*</span></label>
						<input type="text" name="campaign_parameters[utm_campaign]" id="utm_campaign" class="regular-text" placeholder="*<?php esc_attr_e( 'Campaign Name', 'woo-feed' ); ?>" value="<?php echo esc_attr( $feedRules['campaign_parameters']['utm_campaign'] ); ?>">
						<label for="utm_campaign">
							<span class="description" style="color:#8a8a8a;"><?php esc_html_e( 'Product, promo code, or slogan (e.g. spring_sale)', 'woo-feed' ); ?></span>
						</label>
					</td>
					<td>
						<label class="screen-reader-text" for="utm_term"><?php esc_html_e( 'Campaign Term', 'woo-feed' ); ?></label>
						<input type="text" name="campaign_parameters[utm_term]" id="utm_term" class="regular-text" placeholder="<?php esc_attr_e( 'Campaign Term', 'woo-feed' ); ?>" value="<?php echo esc_attr( $feedRules['campaign_parameters']['utm_term'] ); ?>">
						<label for="utm_term">
							<span class="description" style="color:#8a8a8a;"><?php esc_html_e( 'Identify the keywords', 'woo-feed' ); ?></span>
						</label>
					</td>
					<td>
						<label class="screen-reader-text" for="utm_content"><?php esc_html_e( 'Campaign Content', 'woo-feed' ); ?></label>
						<input type="text" name="campaign_parameters[utm_content]" id="utm_content" class="regular-text" placeholder="<?php esc_attr_e( 'Campaign Content', 'woo-feed' ); ?>" value="<?php echo esc_attr( $feedRules['campaign_parameters']['utm_content'] ); ?>">
						<label for="utm_content">
							<span class="description" style="color:#8a8a8a;"><?php esc_html_e( 'Use to differentiate ads', 'woo-feed' ); ?></span>
						</label>
					</td>
				</tr>
				</tbody>
				<tfoot>
				<tr>
					<td colspan="5">
						<p>
							<span class="description"><?php esc_html_e( 'Fill out the required fields (marked with *) in the form above, if any required field is empty, then the parameters will not be applied.', 'woo-feed' ); ?></span>
							<a href="https://support.google.com/analytics/answer/1033863#parameters" target="_blank"><?php esc_html_e( 'Learn more about Campaign URL', 'woo-feed' ); ?></a>
						</p>
					</td>
				</tr>
				</tfoot>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan="2"><?php _e( 'String Replace', 'woo-feed' ); ?></td>
	</tr>
	<tr>
		<td colspan="2">
			<table class="table widefat fixed" id="wf_str_replace">
				<tbody>
				<?php foreach ( $feedRules['str_replace'] as $k => $v ) { ?>
				<tr>
					<td>
						<label class="screen-reader-text" for="str_replace_subject_<?php echo esc_attr( $k ); ?>"><?php esc_html_e( 'Select Attribute For String Replace', 'woo-feed' ); ?></label>
						<select name="str_replace[<?php echo esc_attr( $k ); ?>][subject]" id="str_replace_subject_<?php echo esc_attr( $k ); ?>">
							<?php
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo $wooFeedDropDown->product_attributes_dropdown( esc_attr( $v['subject'] ) );
							?>
						</select>
					</td>
					<td>
						<label class="screen-reader-text" for="str_replace_search_<?php echo esc_attr( $k ); ?>"><?php esc_html_e( 'String to search', 'woo-feed' ); ?></label>
						<input type="text" name="str_replace[<?php echo esc_attr( $k ); ?>][search]" id="str_replace_search_<?php echo esc_attr( $k ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'String to search', 'woo-feed' ); ?>" value="<?php echo esc_attr( htmlentities( $v['search'] ) ); ?>">
					</td>
					<td>
						<label class="screen-reader-text" for="str_replace_replace_<?php echo esc_attr( $k ); ?>"><?php esc_html_e( 'String to replace', 'woo-feed' ); ?></label>
						<input type="text" name="str_replace[<?php echo esc_attr( $k ); ?>][replace]" id="str_replace_replace_<?php echo esc_attr( $k ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'String to replace', 'woo-feed' ); ?>" value="<?php echo esc_attr( htmlentities( $v['replace'] ) ); ?>">
					</td>
					<td>
						<i class="delRow dashicons dashicons-trash"></i>
					</td>
				</tr>
				<?php } ?>
				</tbody>
				<tfoot>
				<tr>
					<td colspan="2">
						<script type="text/template" id="wf_str_replace_template">
							<tr>
								<td>
									<label class="screen-reader-text" for="str_replace_subject___idx__"><?php esc_html_e( 'Select Attribute For String Replace', 'woo-feed' ); ?></label>
									<select name="str_replace[__idx__][subject]" id="str_replace_subject___idx__">
										<?php
										// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
										echo $wooFeedDropDown->product_attributes_dropdown();
										?>
									</select>
								</td>
								<td>
									<label class="screen-reader-text" for="str_replace_search___idx__"><?php esc_html_e( 'String to search', 'woo-feed' ); ?></label>
									<input type="text" name="str_replace[__idx__][search]" id="str_replace_search___idx__" class="regular-text" placeholder="<?php esc_attr_e( 'String to search', 'woo-feed' ); ?>" value="">
								</td>
								<td>
									<label class="screen-reader-text" for="str_replace_replace___idx__"><?php esc_html_e( 'String to replace', 'woo-feed' ); ?></label>
									<input type="text" name="str_replace[__idx__][replace]" id="str_replace_replace___idx__" class="regular-text" placeholder="<?php esc_attr_e( 'String to replace', 'woo-feed' ); ?>" value="">
								</td>
								<td>
									<i class="delRow dashicons dashicons-trash"></i>
								</td>
							</tr>
						</script>
						<button type="button" class="button-small button-primary" id="wf_new_str_replace"><?php esc_html_e( 'Add New Row', 'woo-feed' ); ?></button>
					</td>
					<td></td>
					<td></td>
				</tr>
				</tfoot>
			</table>
		</td>
	</tr>
	<?php if ( woo_feed_has_composite_product_plugin() ) { ?>
		<tr>
			<td><label for="composite_price"><?php _e( 'Composite Product Price', 'woo-feed' ); ?></label></td>
			<td>
				<select name="composite_price" id="composite_price">
					<option value=""><?php esc_attr_e( 'Choose...', 'woo-feed' ); ?></option>
					<option value="parent_product_price"<?php selected( $feedRules['composite_price'], 'parent_product_price' ); ?>><?php esc_attr_e( 'Parent Product Price', 'woo-feed' ); ?></option>
					<option value="all_product_price"<?php selected( $feedRules['composite_price'], 'all_product_price' ); ?>><?php esc_attr_e( 'All Product Price', 'woo-feed' ); ?></option>
				</select>
			</td>
		</tr>
	<?php } ?>
	</tbody>
</table>
