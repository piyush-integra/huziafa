<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}
$filterConditions = woo_feed_get_conditions();
if ( isset( $filterConditions['between'] ) ) {
	unset( $filterConditions['between'] );
}
?><table class="table tree widefat fixed sorted_table mtable" style="width: 100%;" id="table-advanced-filter">
	<thead>
	<tr>
		<th></th>
		<th><?php _e( 'Attributes', 'woo-feed' ); ?></th>
		<th><?php _e( 'Condition', 'woo-feed' ); ?></th>
		<th><?php _e( 'Value', 'woo-feed' ); ?></th>
		<th></th>
	</tr>
	<tr style="border-bottom: 2px solid #ccc">
		<td></td>
		<td colspan="4">
			<label for="filterType" style="margin-right: 5px;"><?php _e( 'Filter', 'woo-feed' ); ?></label>
			<select name="filterType" id="filterType" class="wf_mattributes">
				<option <?php echo ( isset( $feedRules['filterType'] ) && '2' == $feedRules['filterType'] ) ? 'selected="selected"' : ''; ?>value="2"><?php _e( 'Together', 'woo-feed' ); ?></option>
				<option <?php echo ( isset( $feedRules['filterType'] ) && '1' == $feedRules['filterType'] ) ? 'selected="selected"' : ''; ?>value="1"><?php _e( 'Individually', 'woo-feed' ); ?></option>
			</select>
		</td>
	</tr>
	</thead>
	<tbody>
	<tr style="display:none;" class="daRow">
		<td>
			<i class="wf_sortedtable dashicons dashicons-menu"></i>
		</td>
		<td>
			<select name="fattribute[]"  disabled required class="fsrow">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $wooFeedDropDown->product_attributes_dropdown();
				?>
			</select>
		</td>
		<td>
			<select name="condition[]" disabled class="fsrow">
				<?php foreach ( $filterConditions as $k => $v ) { ?>
					<option value="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $v ); ?></option>
				<?php } ?>
			</select>
		</td>
		<td>
			<input type="text" name="filterCompare[]" disabled autocomplete="off" class="fsrow"/>
		</td>
		<td>
			<i class="delRow dashicons dashicons-trash"></i>
		</td>
	</tr>
	<?php

	if ( isset( $feedRules['fattribute'] ) && count( $feedRules['fattribute'] ) ) {
		foreach ( $feedRules['fattribute'] as $fkey => $fvalue ) {
			if ( ! empty( $fvalue ) ) {
				$condition     = $feedRules['condition'];
				$filterCompare = $feedRules['filterCompare'];
				?>
				<tr class="daRow">
					<td>
						<i class="wf_sortedtable dashicons dashicons-menu"></i>
					</td>
					<td>
						<select name="fattribute[]"  required class="fsrow">
							<?php
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo $wooFeedDropDown->product_attributes_dropdown( esc_attr( $fvalue ) );
							?>
						</select>
					</td>
					<td>
						<select name="condition[]" class=''>
							<?php foreach ( $filterConditions as $k => $v ) { ?>
								<option value="<?php echo esc_attr( $k ); ?>"<?php selected( $condition[ $fkey ], $k ); ?>><?php echo esc_html( $v ); ?></option>
							<?php } ?>
						</select>
					</td>
					<td>
						<input type="text" value="<?php echo isset( $filterCompare[ $fkey ] ) ? esc_attr( stripslashes( $filterCompare[ $fkey ] ) ) : ''; ?>" name="filterCompare[]" autocomplete="off" class="fsrow"/>
					</td>
					<td>
						<i class="delRow dashicons dashicons-trash"></i>
					</td>
				</tr>
				<?php
			}
		}
	}
	?>
	</tbody>
	<tfoot>
	<tr>
		<td>
			<button type="button" class="button-small button-primary" id="wf_newFilter"><?php _e( 'Add New Condition', 'woo-feed' ); ?></button>
		</td>
		<td colspan="4"></td>
	</tr>
	</tfoot>
</table>
