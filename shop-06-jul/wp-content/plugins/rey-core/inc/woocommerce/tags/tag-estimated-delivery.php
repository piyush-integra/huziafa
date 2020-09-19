<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( !class_exists('ReyCore_WooCommerce_EstimatedDelivery') ):

	class ReyCore_WooCommerce_EstimatedDelivery
	{
		public $product = 0;
		public $is_enabled = false;

		public function __construct() {
			add_action('wp', [$this, 'init']);
			add_filter('acf/prepare_field/name=estimated_delivery__days', [$this, 'append_suffix_acf_option']);
		}

		function append_suffix_acf_option( $field ) {
			if( isset($field['append']) ) {
				$field['append'] = sprintf(esc_html__('Global: %s', 'rey-core'), get_theme_mod('estimated_delivery__days', 3));
			}
			return $field;
		}

		function init(){

			if( ! is_product() ){
				return;
			}

			$this->product = wc_get_product();

			if( ! $this->product ){
				return;
			}

			$this->is_enabled = get_theme_mod('single_extras__estimated_delivery', false);
			$this->settings = apply_filters('reycore/woocommerce/estimated_delivery', [
				'days_text' => esc_html__('days', 'rey-core'),
				'date_format' => "l, M dS",
				'exclude_dates' => [], // array (YYYY-mm-dd) eg. array("2012-05-02","2015-08-01")
				'margin_excludes' => [], // ["Saturday", "Sunday"]
				'position' => [
					'tag' => 'woocommerce_single_product_summary',
					'priority' => 39
				]
			]);

			add_action($this->settings['position']['tag'], [$this, 'display'], $this->settings['position']['priority']);
		}

		public function display(){

			if( ! $this->is_enabled ){
				return;
			}

			if( ! in_array($this->product->get_stock_status(), get_theme_mod('estimated_delivery__inventory', ['instock']), true) ){
				return;
			}

			echo '<div class="rey-estimatedDelivery">';

				printf('<span class="rey-estimatedDelivery-title">%s</span>&nbsp;', get_theme_mod('estimated_delivery__prefix', esc_html__('Estimated delivery:', 'rey-core')));

				$display_type = get_theme_mod('estimated_delivery__display_type', 'number');
				$days = reycore__get_option('estimated_delivery__days', 3);

				$margin = get_theme_mod('estimated_delivery__days_margin', '');
				$margin_date = '';

				if( $display_type === 'date' ){

					if( $margin ){
						$margin_date = ' - ' . $this->calculate_date(strtotime('today'), absint($days) + absint($margin), $this->settings['margin_excludes'] );
					}

					printf('<span class="rey-estimatedDelivery-date">%s%s</span>',
						$this->calculate_date(strtotime('today'), absint($days), get_theme_mod('estimated_delivery__exclude', ["Saturday", "Sunday"]) ),
						$margin_date
					);
				}
				else {

					if( $margin ){
						$margin_date = ' - ' . absint($margin);
					}

					printf('<span class="rey-estimatedDelivery-date">%1$s %2$s</span>',
						$days . $margin_date,
						$this->settings['days_text']
					);
				}


			echo '</div>';
		}


		function calculate_date($timestamp, $days, $skipdays = []) {

			$i = 1;

			while ($days >= $i) {
				$timestamp = strtotime("+1 day", $timestamp);
				if ( (in_array(date("l", $timestamp), $skipdays)) || (in_array(date("Y-m-d", $timestamp), $this->settings['exclude_dates'])) )
				{
					$days++;
				}
				$i++;
			}

			return date($this->settings['date_format'], $timestamp);

		}

	}

	new ReyCore_WooCommerce_EstimatedDelivery;

endif;
