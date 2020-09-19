<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Request a quote button
 *
 * @since 1.2.0
 */
if( !class_exists('ReyCore_WooCommerce_RequestQuote') ):

class ReyCore_WooCommerce_RequestQuote {

	private $defaults = [];

	private $type = '';

	public function __construct() {
		add_action('init', [$this, 'init']);
	}

	public function init(){

		if( !($this->type = get_theme_mod('request_quote__type', '')) ) {
			return;
		}

		$this->set_defaults();

		add_filter( 'reycore/modal_template/show', '__return_true' );
		add_action( 'rey/after_site_wrapper', [$this, 'add_cf7_form_modal'], 50);
		add_action( 'woocommerce_single_product_summary', [$this, 'get_button_html'], 30);
		add_action( "wpcf7_before_send_mail", [$this, 'before_send_mail']);

	}

	/**
	 * Set defaults
	 *
	 * @since 1.2.0
	 **/
	public function set_defaults()
	{
		$this->defaults = apply_filters('reycore/woocommerce/request_quote_defaults', [
			'title' => get_theme_mod('request_quote__btn_text', esc_html__( 'Request a Quote', 'rey-core' ) ),
			'product_title' => esc_html__( 'PRODUCT: ', 'rey-core' ),
			'close_position' => 'inside',
			'show_in_quickview' => false,
		]);
	}


	public function maybe_show_button(){

		if( $this->type === 'products' ){

			if( ! ($products = get_theme_mod('request_quote__products', '')) ){
				return false;
			}

			$get_products_ids = array_map( 'absint', array_map( 'trim', explode( ',', $products ) ) );

			if( ! in_array(get_the_ID(), $get_products_ids) ){
				return false;
			}
		}

		elseif( $this->type === 'categories' ){

			if( ! ($categories = get_theme_mod('request_quote__categories', [])) ){
				return false;
			}

			$terms = wp_get_post_terms( get_the_ID(), 'product_cat' );
			foreach ( $terms as $term ) $product_categories[] = $term->slug;

			if ( ! array_intersect($product_categories, $categories) ) {
				return false;
			}
		}

		if( get_query_var('rey__is_quickview', false) === true && $this->defaults['show_in_quickview'] === false ) {
			return false;
		}

		return true;
	}

	public function add_cf7_form_modal(){

		if( !function_exists('wpcf7_contact_form') ){
			return;
		}

		if( ! ($cf7_form = get_theme_mod('request_quote__cf7', '')) ){
			return;
		}

		?>
		<div class="rey-requestQuote-modal --hidden" data-id="<?php echo get_the_ID(); ?>">
			<?php

				printf('<h3 class="rey-requestQuote-modalTitle">%s</h3>', $this->defaults['title'] );

				if( is_product() ){
					printf('<p>%s <strong>%s</strong></p>', $this->defaults['product_title'], get_the_title() );
				}

				if ( $contact_form = wpcf7_contact_form($cf7_form) ) {
					echo $contact_form->form_html([
						'html_class' => 'rey-cf7--basic'
					]);
				}
			?>
		</div>
		<?php
	}

	/**
	* Add the button
	*
	* @since 1.2.0
	*/
	public function get_button_html(){

		if( ! $this->maybe_show_button() ){
			return;
		}
		?>

		<div class="rey-requestQuote-wrapper">

			<a href="#" class="rey-requestQuote-btn btn <?php echo esc_attr( get_theme_mod('request_quote__btn_style', 'btn-line-active') ) ?> js-requestQuote" data-id="<?php echo get_the_ID() ?>">
				<?php echo get_theme_mod('request_quote__btn_text', esc_html__( 'Request a Quote', 'rey-core' ) ); ?>
			</a>

			<?php if( $after_text = get_theme_mod('request_quote__btn_text_after', '') ): ?>
				<div class="rey-requestQuote-text"><?php echo $after_text ?></div>
			<?php endif; ?>

		</div>

		<?php
	}


	public function before_send_mail( $WPCF7_ContactForm )
	{
		//Get current form
		$wpcf7 = WPCF7_ContactForm::get_current();

		// get current SUBMISSION instance
		$submission = WPCF7_Submission::get_instance();

		// Ok go forward
		if ( $submission ) {

			// get submission data
			$data = $submission->get_posted_data();

			if ( empty( $data ) ) {
				return;
			}

			$mail = $wpcf7->prop( 'mail' );

			$extra = '';

			if( isset($data['rey-request-quote-product-id']) && $product_id = absint($data['rey-request-quote-product-id']) ){

				$extra .= 'Product ID: <strong>'. $product_id .'</strong>.<br>';
				$extra .= 'Product: <a href="'. esc_url( get_the_permalink( $product_id ) ) .'"><strong>' . get_the_title( $product_id ) . '</strong></a>.<br>';

				if( strpos($mail['subject'], '[your-subject]') !== false ){
					$mail['subject'] = str_replace( '[your-subject]', str_replace('&#8211;', '-', get_the_title( $product_id )), $mail['subject']);
				}
				else {
					$mail['subject'] = $mail['subject'] . ' - ' . get_the_title( $product_id );
				}

				$extra = apply_filters('reycore/woocommerce/request_quote_mail', $extra, $product_id);
			}

			$mail['body'] = $extra . '<br><br>' . $mail['body'];
			$mail['use_html'] = true;

			// Save the email body
			$wpcf7->set_properties( [
				"mail" => $mail,
			 ]);

			return $wpcf7;
		}
	}

}
endif;

new ReyCore_WooCommerce_RequestQuote;
