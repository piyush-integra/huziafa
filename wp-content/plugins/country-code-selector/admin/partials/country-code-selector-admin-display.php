<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://www.intolap.com
 * @since      1.2
 *
 * @package    Country_Code_Selector
 * @subpackage Country_Code_Selector/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<style type="text/css">
	.label {
	    /*min-width: 200px !important;
	    display: inline-block !important*/
	}

  	.switch {
		position: relative;
		display: inline-block;
		width: 60px;
		height: 34px;
	}

	.switch input {display:none;}

	.slider {
		position: absolute;
		cursor: pointer;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		background-color: #ccc;
		-webkit-transition: .4s;
		transition: .4s;
	}

	.slider:before {
		position: absolute;
		content: "";
		height: 26px;
		width: 26px;
		left: 4px;
		bottom: 4px;
		background-color: white;
		-webkit-transition: .4s;
		transition: .4s;
	}

	input:checked + .slider {
		background-color: #2196F3;
	}

	input:focus + .slider {
		box-shadow: 0 0 1px #2196F3;
	}

	input:checked + .slider:before {
		-webkit-transform: translateX(26px);
		-ms-transform: translateX(26px);
		transform: translateX(26px);
	}

	/* Rounded sliders */
	.slider.round {
		border-radius: 34px;
	}

	.slider.round:before {
		border-radius: 50%;
	}
</style>
<?php
if( get_option('selected_gform') == '' || get_option('gform_phone_field_id') == '' ){
	update_option('enable_on_gform','');
}
if( get_option('selected_cform7') == '' || get_option('cform7_phone_field_id') == '' ){
	update_option('enable_on_cform7','');
}
if( empty(get_option('selected_countries')) ){
	update_option('show_selected','');
}
if( !is_plugin_active('woocommerce/woocommerce.php') ){
	update_option('enable_on_woocommerce', '');
}
if( !is_plugin_active('shopp/Shopp.php') ){
	update_option('enable_on_shopp', '');
}
if( !is_plugin_active('gravityforms/gravityforms.php') ){
	update_option('enable_on_gform', '');
	update_option('gform_phone_field_id', '');
}
if( !is_plugin_active('contact-form-7/wp-contact-form-7.php') ){
	update_option('enable_on_cform7', '');
	update_option('cform7_phone_field_id', '');
}
?>
<div id="pluginwrap">
	<h2>Country Code Selector Settings</h2>
	<form method="post" action="options.php">
		<?php settings_fields( 'country_code_selector_group' ); ?>
		<table style="text-align: left;">
		  	<tr valign="middle">
			  	<th scope="row">
			  		<label for="enable_on_woocommerce">Enable on WooCommerce Checkout Page</label><br/>
			  		<small class="description"><span style="color: red;"><?php if(!is_plugin_active('woocommerce/woocommerce.php')){echo 'Note: WooCommerce plugin is not active.';}?></span></small>
			  	</th>
			  	<td>
			  		<label class="switch">
                        <input id="enable_on_woocommerce" name="enable_on_woocommerce" data-plugin-name="WooCommerce" type="checkbox" <?php if(get_option('enable_on_woocommerce') == 'on'){ echo 'checked'; }?>>
                        <span class="slider"></span>
                    </label>
                    <small class="description">(<em><strong>Billing Phone</strong></em> field)</small>
			  	</td>
		  	</tr>
		  	<tr valign="middle">
			  	<th scope="row">
			  		<label for="enable_on_shopp">Enable on Shopp Checkout Page</label><br/>
			  		<small class="description"><span style="color: red;"><?php if(!is_plugin_active('shopp/Shopp.php')){echo 'Note: Shopp plugin is not active.';}?></span></small>
			  	</th>
			  	<td>
			  		<label class="switch">
                        <input id="enable_on_shopp" name="enable_on_shopp" data-plugin-name="Shopp" type="checkbox" <?php if(get_option('enable_on_shopp') == 'on'){ echo 'checked'; }?>>
                        <span class="slider"></span>
                    </label>
                    <small class="description">(<em><strong>Phone</strong></em> field)</small>
			  	</td>
		  	</tr>
		  	<tr valign="middle">
			  	<th scope="row">
			  		<label for="gform">Enable on Gravity Forms</label><br/>
			  		<small class="description"><span style="color: red;"><?php if(!is_plugin_active('gravityforms/gravityforms.php')){echo 'Note: Gravity Forms plugin is not active.';}?></span></small>
			  	</th>
			  	<td>
			  		<label class="switch">
                        <input id="enable_on_gform" data-plugin-name="Gravity Forms" name="enable_on_gform" type="checkbox" <?php if(get_option('enable_on_gform') == 'on'){ echo 'checked'; }?>>
                        <span class="slider"></span>
                    </label>
                    
			  	</td>
		  	</tr>
		  	<?php
		  	if(class_exists('GFAPI')){
		  	?>
		  	<tr valign="middle" class="gform" style="<?php echo (get_option('enable_on_gform') == 'on')? 'visibility: visible': 'visibility: hidden';?>">
			  	<th scope="row"><label for="selected_gforms"><small>- Select a Gravity Forms form</small></label></th>
			  	<td>
			  		<?php
			  		$forms = GFAPI::get_forms();
			  		?>
			        <select name="selected_gform">
					    <option value="">Select a form</option>
					    <?php
					    if(!empty($forms)){
					    	$selected_form = get_option('selected_gform');
				            foreach ($forms as $form) {
								echo '<option value="'.$form['id'].'" '.selected($form['id'],$selected_form,false).'>'.$form['title'].' (Form ID:'.$form['id'].')</option>';
							}	
					    }else{
					    	echo '<option value="">Please create a form first.</option>';
					    }
			            ?>
					</select>
					<input id="gform_phone_field_id" name="gform_phone_field_id" type="text" value="<?php echo get_option('gform_phone_field_id');?>" <?php (get_option('enable_on_gform') == 'on')?'':'disabled';?> placeholder="Field ID">
                    <small class="description"><span style="color: red;"><?php if(!is_plugin_active('gravityforms/gravityforms.php')){echo 'Note: Gravity Forms plugin is not active.';}?></span></small>
			  	</td>
		  	</tr>
		  	<?php
		  	}
		  	?>
		  	
		  	<tr valign="middle">
			  	<th scope="row">
			  		<label for="enable_on_cform7">Enable on Contact Form 7</label><br/>
			  		<small class="description"><span style="color: red;"><?php if(!is_plugin_active('contact-form-7/wp-contact-form-7.php')){echo 'Note: Contact Form 7 plugin is not active.';}?></span></small>
			  	</th>
			  	<td>
			  		<label class="switch">
                        <input id="enable_on_cform7" data-plugin-name="Contact Form 7" name="enable_on_cform7" type="checkbox" <?php if(get_option('enable_on_cform7') == 'on'){ echo 'checked'; }?>>
                        <span class="slider"></span>
                    </label>
                    <small class="description"></small>
			  	</td>
		  	</tr>

		  	<tr valign="middle" class="cform7" style="<?php echo (get_option('enable_on_cform7') == 'on')? 'visibility: visible': 'visibility: hidden';?>">
			  	<th scope="row"><label for="selected_cform7"><small>- Select a Contact Form 7 form</small></label></th>
			  	<td>
			  		<?php
		  			$dbValue = get_option('selected_cform7'); //example!
				    $posts = get_posts(array(
				        'post_type'     => 'wpcf7_contact_form',
				        'numberposts'   => -1
				    ));
			  		?>
			  		<select name="selected_cform7" id="selected_cform7"> 
					    <option value="">Select a form</option>
					    <?php
					    if(!empty($posts)){
					    	foreach ( $posts as $p ) {
						        echo '<option value="'.$p->ID.'"'.selected($p->ID,$dbValue,false).'>'.$p->post_title.' (Form ID:'.$p->ID.')</option>';
						    }	
					    }else{
					    	echo '<option value="">Please create a form first.</option>';
					    }
					    ?>
					</select>
					<input id="cform7_phone_field_id" name="cform7_phone_field_id" type="text" value="<?php echo get_option('cform7_phone_field_id');?>" <?php (get_option('enable_on_gform') == 'on')?'':'disabled';?> placeholder="Field ID">
                    <small class="description"><span style="color: red;"><?php if(!is_plugin_active('contact-form-7/wp-contact-form-7.php')){echo 'Note: Contact Form 7 plugin is not active.';}?></span></small>
			  	</td>
		  	</tr>

		  	
		  	<tr valign="middle">
			  	<th scope="row"><label for="show_selected">Show Selected Countries Only</label></th>
			  	<td>
			  		<label class="switch">
                        <input id="show_selected" name="show_selected" type="checkbox" <?php if(get_option('show_selected') == 'on'){ echo 'checked'; }?>>
                        <span class="slider"></span>
                    </label>
                    <small class="description"></small>
			  	</td>
		  	</tr>
		  	<tr valign="middle" class="clist" style="<?php echo (get_option('show_selected') == 'on')? 'visibility: visible': 'visibility: hidden';?>">
			  	<th scope="row"><label for="enable_on_shopp"><small>- Select Countries</small></label></th>
			  	<td>
			  		<?php 
			  		$allCountries = json_decode(file_get_contents(esc_url( plugin_dir_url( __FILE__ ).'../js/countries.json')), true);
			            // print_r($allCountries);
			  			// print_r(get_option('selected_countries'));
			            $onlyCountries = ["al", "ad", "at", "by", "be", "ba", "bg", "hr", "cz", "dk",
						"ee", "fo", "fi", "fr", "de", "gi", "gr", "va", "hu", "is", "ie", "it", "lv",
						"li", "lt", "lu", "mk", "mt", "md", "mc", "me", "nl", "no", "pl", "pt", "ro",
						"ru", "sm", "rs", "sk", "si", "es", "se", "ch", "ua", "gb"];
			  		?>
			        <select name="selected_countries[]" multiple id="countryOpt">
					    <?php
			            foreach ($onlyCountries as $onlyCountry) {
							foreach ($allCountries as $allCountry) {
								if(in_array(strtolower($allCountry['code']), get_option('selected_countries'))){
									$selected = 'selected';
								}else{
									$selected = '';
								}

								if(strtolower($allCountry['code']) == $onlyCountry)
									echo '<option value="'.$onlyCountry.'" '.$selected.'>'.$allCountry['name'].'</option>';
						
							}
						}
			            ?>
					</select>
					<small class="description">(List of selected countries)</small>
			  	</td>
		  	</tr>  
		  	<tr valign="middle">
			  	<th scope="row"><label for="gform">Enable JS phone validation</label></th>
			  	<td>
			  		<label class="switch">
                        <input id="js_validation" name="js_validation" type="checkbox" <?php if(get_option('js_validation') == 'on'){ echo 'checked'; }?>>
                        <span class="slider"></span>
                    </label>
                    <small class="description">Use in addition to respective form validation, if required.</small>
			  	</td>
		  	</tr>
		</table>
		<?php  submit_button(); ?>
	</form>

	<script type="text/javascript">
		$(document).ready(function(){
			$('#countryOpt').multiselect({
				columns: 1,
				placeholder: 'Select Countries',
				search: true
			});
		});
	</script>
</div>