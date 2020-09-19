<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://www.intolap.com
 * @since      1.2
 *
 * @package    Country_Code_Selector
 * @subpackage Country_Code_Selector/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Country_Code_Selector
 * @subpackage Country_Code_Selector/public
 * @author     INTOLAP <developer@intolap.com>
 */
class Country_Code_Selector_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.2
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.2
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.2
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.2
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Country_Code_Selector_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Country_Code_Selector_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/country-code-selector-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.2
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Country_Code_Selector_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Country_Code_Selector_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/country-code-selector-public.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Register the JavaScript for the public-facing side of the woocommerce checkout page.
	 *
	 * @since    1.2
	 */
	public function ccs_enable_on_woocomerce() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Country_Code_Selector_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Country_Code_Selector_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		if(is_plugin_active('woocommerce/woocommerce.php') && is_checkout()){
	?>
		<script>
			var selection = document.querySelector("#billing_phone") !== null;
		    if(selection){
	            var input = document.querySelector("#billing_phone");
	            var iti = window.intlTelInput(input, {
	                hiddenInput: "billing_phone",
	                separateDialCode: true,
	                formatOnDisplay: true,
				    <?php
				    if(get_option('show_selected') == 'on' && !empty(get_option('selected_countries'))){
				   	?>
				   	onlyCountries: <?php echo json_encode(get_option('selected_countries')); ?>,
				    <?php
					}
				    ?>
	                utilsScript: "<?php echo esc_url( plugin_dir_url( __FILE__ ) .'js/wc-utils.js' );?>",
	            });
	        }
        </script>
        <?php 
			if(get_option('js_validation') == 'on') { 
?>
        	<script>
        		var input = document.querySelector("#billing_phone");

				var errorMap = ["Invalid number", "Invalid country code", "Too short", "Too long", "Invalid number"];
				
				var preventAlert = true;

				var reset = function() {
				  preventAlert = true;
				};

				input.addEventListener('blur', function() {
				  if (input.value.trim()) {
				    if (iti.isValidNumber()) {
				      // 
				    } else {
				      var errorCode = iti.getValidationError();

				      if(preventAlert){
				      	alert(errorMap[errorCode]);
				      	preventAlert = false;
				      }
				    }
				  }
				});

				// on keyup / change flag: reset
				input.addEventListener('change', reset);
				input.addEventListener('keyup', reset);

				jQuery('select#billing_country').on( 'change', function (){
		            var billingCountry = jQuery('#billing_country :selected').val();
		            iti.setCountry(billingCountry.toLowerCase());
		        });

				input.addEventListener("countrychange", function() {
		          var country_data = iti.getSelectedCountryData();
		          // alert(country_data.name+'-'+country_data.iso2+'-'+country_data.dialCode);
		          jQuery('select#billing_country').val(country_data.iso2.toUpperCase()).trigger('change');
		        });
				
        	</script>
 <?php
    		}

		}
	}

	/**
	 * Register the JavaScript for the public-facing side of the shopp checkout page.
	 *
	 * @since    1.2
	 */
	public function ccs_enable_on_shopp() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Country_Code_Selector_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Country_Code_Selector_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		if(is_plugin_active('shopp/Shopp.php') && is_shopp_checkout_page()){
	?>
		<script>
			var selection = document.querySelector("#phone") !== null;
		    if(selection){
            var input = document.querySelector("#phone");
	            window.intlTelInput(input, {
	                hiddenInput: "phone",
	                separateDialCode: true,
	                formatOnDisplay: true,
				    <?php
				    if(get_option('show_selected') == 'on' && !empty(get_option('selected_countries'))){
				   	?>
				   	onlyCountries: <?php echo json_encode(get_option('selected_countries')); ?>,
				    <?php
					}
				    ?>
	                utilsScript: "<?php echo esc_url( plugin_dir_url( __FILE__ ) .'js/wc-utils.js' );?>",
	            });
	        }
        </script>

        <?php if(get_option('js_validation') == 'on') { ?>
        	<script>
        		var input = document.querySelector("#phone");

				var errorMap = ["Invalid number", "Invalid country code", "Too short", "Too long", "Invalid number"];

				input.addEventListener('blur', function() {
				  if (input.value.trim()) {
				    if (iti.isValidNumber()) {
				      // 
				    } else {
				      var errorCode = iti.getValidationError();
				      alert(errorMap[errorCode]);
				    }
				  }
				});

				/*input.addEventListener("countrychange", function() {
		          var country_data = iti.getSelectedCountryData();
		          alert(country_data.name+'-'+country_data.iso2+'-'+country_data.dialCode);
		          jQuery('#billing_country').val(country_data.iso2.toUpperCase()).trigger('change');;
		        });*/
				
        	</script>
 <?php
    		}
		}
	}


	public function ccs_override_phone_format($phone_formats){	 	
	 	
	 	$phone_formats = array(
	        'standard'      => array(
	            'label'       => '(###) ###-####',
	            'mask'        => '(999) 999-9999',
	            'regex'       => '/^\D?(\d{3})\D?\D?(\d{3})\D?(\d{4})$/',
	            'instruction' => '(###) ###-####',
	        ),
	        /*'international' => array(
	            'label'       => __( 'International', 'gravityforms' ),
	            'mask'        => false,
	            'regex'       => false,
	            'instruction' => false,
	        ),*/
	    );

	    // return $phone_formats;
	}

	/**
	 * Register the JavaScript for the public-facing side of the woocommerce checkout page.
	 *
	 * @since    1.2
	 */
	public function ccs_enable_on_gravity_form( $form, $field_values ) {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Country_Code_Selector_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Country_Code_Selector_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		if(is_plugin_active('gravityforms/gravityforms.php')){
			// echo "<pre>"; print_r($form); print_r($field_values); echo "</pre>";
			$field_id = 'input_'.$form['id'].'_'.get_option('gform_phone_field_id');
		    $script = '';
		    // $script = '(function($){';
		    $script .= 'var selection = document.querySelector("#'.$field_id.'") !== null;';
		    $script .= 'if(selection){';
		    $script .= 'var input = document.querySelector("#'.$field_id.'");';
		    $script .= 'input.style.width = "100%";';
		    $script .= 'var iti = window.intlTelInput(input, {';
            $script .= 'hiddenInput: input.getAttribute("name"),';
            $script .= 'separateDialCode: true,';
            $script .= 'formatOnDisplay: true,';
			
			if(get_option('show_selected') == 'on' && !empty(get_option('selected_countries'))){
			$script .= 'onlyCountries: '.json_encode(get_option('selected_countries')).',';
			}

            $script .= 'utilsScript: "' . esc_url( plugin_dir_url( __FILE__ ) .'js/wc-utils.js' ).'",';
            $script .= '});';

            if(get_option('js_validation') == 'on') { 
            $script .= 'var errorMap = ["Invalid number", "Invalid country code", "Too short", "Too long", "Invalid number"];';
			$script .= 'input.addEventListener("blur", function() {';
			$script .= '  if (input.value.trim()) {';
			$script .= '    if (iti.isValidNumber()) {';
			$script .= '    } else {';
			$script .= '      var errorCode = iti.getValidationError();';
			$script .= '      alert(errorMap[errorCode]);';
			$script .= '    }';
			$script .= '  }';
			$script .= '});';
            }
            $script .= '}';
            // $script .= '})(jQuery);';
		 	
		    GFFormDisplay::add_init_script( $form['id'], 'country_code_selector', GFFormDisplay::ON_PAGE_RENDER, $script );
		}
	}


	/**
	 * Register the JavaScript for the public-facing side of the woocommerce checkout page.
	 *
	 * @since    1.2
	 */
	public function ccs_enable_on_contact_form7() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Country_Code_Selector_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Country_Code_Selector_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		if(is_plugin_active('contact-form-7/wp-contact-form-7.php')){
			$selected_cform7 = get_option('selected_cform7');
			$cform7_field_id = get_option('cform7_phone_field_id');

			// Detect if the selected contact form shortcode exist in this page content
			$content = get_the_content();
			$shortcode = '[contact-form-7 id="'.$selected_cform7.'"';

			$check = strpos($content,$shortcode);
			if($check=== false) {
			  // echo 'short code does not exist';
			} else {
?>
			    <script type="text/javascript">
			    	var selection = document.querySelector("#<?php echo $cform7_field_id?>") !== null;
			    	if(selection){
			    		var input = document.querySelector("#<?php echo $cform7_field_id;?>");
			            var iti = window.intlTelInput(input, {
			                hiddenInput: input.getAttribute("name"),
			                separateDialCode: true,
			                formatOnDisplay: true,
						    <?php
						    if(get_option('show_selected') == 'on' && !empty(get_option('selected_countries'))){
						   	?>
						   	onlyCountries: <?php echo json_encode(get_option('selected_countries')); ?>,
						    <?php
							}
						    ?>
			                utilsScript: "<?php echo esc_url( plugin_dir_url( __FILE__ ) .'js/wc-utils.js' );?>",
			            });
			    	}
	        	</script>
				<?php if(get_option('js_validation') == 'on') { ?>
	        	<script>
	        		var input = document.querySelector("#<?php echo $cform7_field_id?>");

					var errorMap = ["Invalid number", "Invalid country code", "Too short", "Too long", "Invalid number"];

					input.addEventListener('blur', function() {
					  if (input.value.trim()) {
					    if (iti.isValidNumber()) {
					      // 
					    } else {
					      var errorCode = iti.getValidationError();
					      alert(errorMap[errorCode]);
					    }
					  }
					});
	        	</script>
	 <?php
	    		}
			}
		}
	}

}
