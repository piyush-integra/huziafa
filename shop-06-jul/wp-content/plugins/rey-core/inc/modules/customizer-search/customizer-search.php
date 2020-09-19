<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( !class_exists('ReyCore_CustomizerSearch') ):

class ReyCore_CustomizerSearch
{
	public function __construct()
	{
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'customize_controls_print_footer_scripts', array( $this, 'load_template_script' ) );
	}

	public function is_enabled()
	{
		return apply_filters('reycore/modules/customizer_search', true);
	}

	/**
	 * Enqueues scripts for the Customizer.
	 *
	 * @since 1.0
	 * @return void
	 */
	public function enqueue_scripts() {

		if( ! $this->is_enabled() ){
			return;
		}

		wp_enqueue_style( 'reycore-customizer-search', REY_CORE_MODULE_URI . basename(__DIR__) . '/style.css', [], REY_CORE_VERSION );
		wp_enqueue_script( 'reycore-customizer-search', REY_CORE_MODULE_URI . basename(__DIR__) . '/script.js', [], REY_CORE_VERSION , true);

	}

	/**
	 * Renders the Customizer footer scripts.
	 *
	 * @since 1.0
	 * @return void
	 */
	public function load_template_script() {

		if( ! $this->is_enabled() ){
			return;
		}
		?>

		<script type="text/html" id="tmpl-rey-customizer-search-form">
			<div id="rey-customizer-search" class="rey-customizerSearch">
				<div class="rey-customizerSearch-inner">
					<?php echo reycore__get_svg_icon(['id' => 'rey-icon-search', 'class' => 'icon-search']) ?>
					<input type="search" placeholder="<?php _e( 'Search Options ..', 'rey-core' ); ?>" name="rey-customizer-search-input" class="rey-customizerSearch-input">
					<span class="rey-customizerSearch-cancel"><?php _e( 'Cancel', 'rey-core' ); ?></span>
				</div>
			</div>
		</script>

		<script type="text/html" id="tmpl-rey-customizer-search-results">
			<li id="accordion-section-{{data.section}}" class="rey-searchResult accordion-section control-section control-section-default" aria-owns="sub-accordion-section-{{data.section}}" data-section="{{data.section}}" data-control="{{data.controlId}}">
				<h3 class="accordion-section-title" tabindex="0">

					<# if( data.label ){ #>
						<span class="rey-searchResult-title">{{{data.label}}}</span>
					<# } #>

					<# if( data.description ){ #>
						<span class="rey-searchResult-desc">{{{data.description}}}</span>
					<# } #>

					<span class="screen-reader-text"><?php _e('Press return or enter to open this section', 'rey-core') ?></span>

					<span class="rey-searchResult-trail">
						<# if( data.sectionName ){ #>
							{{{data.panelName}}} &rarr; {{{data.sectionName}}}
						<# } else { #>
							{{{data.panelName}}}
						<# } #>
					</span>

				</h3>
			</li>
		</script>
		<?php
	}
}

new ReyCore_CustomizerSearch;

endif;
