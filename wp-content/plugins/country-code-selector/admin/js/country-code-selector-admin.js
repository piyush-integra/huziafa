(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	$(function() {
		$('.switch input[type="checkbox"]').click(function(){
			if($(this).prop("checked") == true){
				if(
					$(this).attr('id') !== 'show_selected' &&
					$(this).attr('id') !== 'js_validation'
				){
					alert('Make sure the '+$(this).attr('data-plugin-name')+' plugin is installed and activated.');
				}
							
				if($(this).attr('id') == 'enable_on_gform'){
					$('input#gform_phone_field_id').removeAttr('disabled');
					$('input#gform_phone_field_id').attr('required');
					$('tr.gform').css('visibility', 'visible');
				}else if($(this).attr('id') == 'enable_on_cform7'){
					$('input#cform7_phone_field_id').removeAttr('disabled');
					$('input#cform7_phone_field_id').attr('required');
					$('tr.cform7').css('visibility', 'visible');
				}else if($(this).attr('id') == 'show_selected'){
					$('tr.clist').css('visibility', 'visible');
				}
			}else{
				if($(this).attr('id') == 'enable_on_gform'){
					$('input#gform_phone_field_id').attr('disabled', true);
					$('input#gform_phone_field_id').removeAttr('required');
					$('tr.gform').css('visibility', 'hidden');
				}else if($(this).attr('id') == 'enable_on_cform7'){
					$('input#cform7_phone_field_id').attr('disabled', true);
					$('input#cform7_phone_field_id').removeAttr('required');
					$('tr.cform7').css('visibility', 'hidden');
				}else if($(this).attr('id') == 'show_selected'){
					$('tr.clist').css('visibility', 'hidden');
				}
			}
		});
	});

})( jQuery );
