<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

add_filter('kirki_telemetry', '__return_false');

add_filter('kirki_googlefonts_transient_time', function(){
	return MONTH_IN_SECONDS;
}, 10);

function reycore__kirki_init() {

	// Force Kirki's Google fonts variants to load all
    if (class_exists('Kirki_Fonts_Google')) {
		Kirki_Fonts_Google::$force_load_all_variants = reycore__acf_get_field('kirki_font_variants', REY_CORE_THEME_NAME );
	}
}
add_action('init', 'reycore__kirki_init');


if(!function_exists('reycore_customizer__add_field')):
	/**
	 * Add fields
	 *
	 * @since 1.0.0
	 */
	function reycore_customizer__add_field( $args = [] ){

		if( empty($args) ){
			return;
		}

		ReyCoreKirki::add_field( 'rey_core_kirki', $args );
	}
endif;


if(!function_exists('reycore_customizer__add_responsive_field')):
	/**
	 * Add fields
	 *
	 * @since 1.0.0
	 */
	function reycore_customizer__add_responsive_field( $args = [] ){

		if( empty($args) ){
			return;
		}

		$setting = $args['settings'];

		add_action( 'customize_render_control_' . $setting, function( $customizer ) {
			$customizer->json['is_responsive'] = 'desktop';
		} );

		// avoid loopholes
		unset($args['rey_group_start']);
		unset($args['rey_group_end']);
		unset($args['responsive']);

		$breakpoints = [
			'tablet', 'mobile'
		];

		$mq = [
			'tablet'	=> '@media (min-width: 768px) and (max-width: 1025px)',
			'mobile'	=> '@media (max-width: 767px)',
		];

		foreach($breakpoints as $breakpoint){

			add_action("reycore/kirki_fields/after_field=" . $setting , function() use ($args, $breakpoint, $setting, $mq){

				// assign media queries
				if( isset($args['output']) ){
					foreach($args['output'] as $i => $rule){
						$args['output'][$i]['media_query'] = $mq[$breakpoint];
					}
				}

				// create setting name per device
				$args['settings'] = sprintf( '%s_%s', $setting, $breakpoint);

				// pass JSON attribute
				add_action( 'customize_render_control_' . $args['settings'], function( $customizer ) use ($breakpoint) {
					$customizer->json['is_responsive'] = $breakpoint;
				} );

				reycore_customizer__add_field( $args );
			});
		}

		reycore_customizer__add_field( $args );
	}
endif;


if(!function_exists('reycore__background_attachment_choices')):
	/**
	 * Background attachment choices
	 *
	 * @since 1.0.0
	 **/
	function reycore__background_attachment_choices()
	{
		return [
			'' => esc_html__('Default', 'rey-core'),
			'scroll' => esc_html__('Scroll', 'rey-core'),
			'fixed' => esc_html__('Fixed', 'rey-core'),
			'local' => esc_html__('Local', 'rey-core'),
			'initial' => esc_html__('Initial', 'rey-core'),
			'inherit' => esc_html__('Inherit', 'rey-core'),
		];
	}
endif;


if(!function_exists('reycore__background_size_choices')):
	/**
	 * Background size choices
	 *
	 * @since 1.0.0
	 **/
	function reycore__background_size_choices()
	{
		return [
			'' => esc_html__('Default', 'rey-core'),
			'auto' => esc_html__('Auto', 'rey-core'),
			'contain' => esc_html__('Contain', 'rey-core'),
			'cover' => esc_html__('Cover', 'rey-core'),
			'initial' => esc_html__('Initial', 'rey-core'),
			'inherit' => esc_html__('Inherit', 'rey-core'),
		];
	}
endif;


if(!function_exists('reycore__background_repeat_choices')):
	/**
	 * Background repeat choices
	 *
	 * @since 1.0.0
	 **/
	function reycore__background_repeat_choices()
	{
		return [
			'' => esc_html__('Default', 'rey-core'),
			'repeat' => esc_html__('Repeat', 'rey-core'),
			'no-repeat' => esc_html__('No Repeat', 'rey-core'),
			'repeat-x' => esc_html__('Repeat Horizontally', 'rey-core'),
			'repeat-y' => esc_html__('Repeat Vertically', 'rey-core'),
			'initial' => esc_html__('Initial', 'rey-core'),
			'inherit' => esc_html__('Inherit', 'rey-core'),
		];
	}
endif;

if(!function_exists('reycore__background_clip_choices')):
	/**
	 * Background clip choices
	 *
	 * @since 1.0.0
	 **/
	function reycore__background_clip_choices()
	{
		return [
			'' => esc_html__('Default', 'rey-core'),
			'border-box' => esc_html__('Border Box', 'rey-core'),
			'padding-box' => esc_html__('Padding Box', 'rey-core'),
			'content-box' => esc_html__('Content Box', 'rey-core'),
			'initial' => esc_html__('Initial', 'rey-core'),
			'inherit' => esc_html__('Inherit', 'rey-core'),
		];
	}
endif;


if(!function_exists('reycore__background_blend_choices')):
	/**
	 * Background blend mode choices
	 *
	 * @since 1.0.0
	 **/
	function reycore__background_blend_choices()
	{
		return [
			'' => esc_html__('Default', 'rey-core'),
			'normal' => esc_html__('Normal', 'rey-core'),
			'multiply' => esc_html__('Multiply', 'rey-core'),
			'screen' => esc_html__('Screen', 'rey-core'),
			'overlay' => esc_html__('Overlay', 'rey-core'),
			'darken' => esc_html__('Darken', 'rey-core'),
			'lighten' => esc_html__('Lighten', 'rey-core'),
			'color-dodge' => esc_html__('Color Dodge', 'rey-core'),
			'saturation' => esc_html__('Saturation', 'rey-core'),
			'color-burn' => esc_html__('Color burn', 'rey-core'),
			'hard-light' => esc_html__('Hard light', 'rey-core'),
			'soft-light' => esc_html__('Soft light', 'rey-core'),
			'difference' => esc_html__('Difference', 'rey-core'),
			'exclusion' => esc_html__('Exclusion', 'rey-core'),
			'hue' => esc_html__('Hue', 'rey-core'),
			'color' => esc_html__('Color', 'rey-core'),
			'luminosity' => esc_html__('Luminosity', 'rey-core'),
			'initial' => esc_html__('Initial', 'rey-core'),
			'inherit' => esc_html__('Inherit', 'rey-core'),
		];
	}
endif;


if(!function_exists('reycore__kirki_custom_bg_group')):
	/**
	 * Custom Background option group.
	 *
	 * @since 1.0.0
	 */
	function reycore__kirki_custom_bg_group($args = []){

		$defaults = [
			'settings' => 'bg_option',
			'section' => 'bg_section',
			'label' => esc_html__('Background', 'rey-core'),
			'description' => esc_html__('Change background settings.', 'rey-core'),
			'output_element' => '',
			'active_callback' => [],
			'color' => [
				'default' => '',
				'output_element' => '',
				'output_property' => 'background-color',
				'active_callback' => [],
			],
			'image' => [
				'default' => '',
				'output_element' => '',
				'output_property' => 'background-image',
				'active_callback' => [],
			],
			'repeat' => [
				'default' => '',
				'output_element' => '',
				'output_property' => 'background-repeat',
				'active_callback' => [],
			],
			'attachment' => [
				'default' => '',
				'output_element' => '',
				'output_property' => 'background-attachment',
				'active_callback' => [],
			],
			'size' => [
				'default' => '',
				'output_element' => '',
				'output_property' => 'background-size',
				'active_callback' => [],
			],
			'positionx' => [
				'default' => '50%',
				'output_element' => '',
				'output_property' => 'background-position-x',
				'active_callback' => [],
			],
			'positiony' => [
				'default' => '50%',
				'output_element' => '',
				'output_property' => 'background-position-y',
				'active_callback' => [],
			],
			'blend' => [
				'default' => '',
				'output_element' => '',
				'output_property' => 'background-blend-mode',
				'active_callback' => [],
			],
		];

		$args = reycore__wp_parse_args($args, $defaults);

		$has_image = [
			'setting'  => $args['settings']. '_img',
			'operator' => '!=',
			'value'    => '',
		];

		$active_callback[] = $has_image;

		if( $args['active_callback'] ){
			$active_callback[] = array_merge($active_callback, $args['active_callback']);
		}

		$priority = isset($args['priority']) ? $args['priority'] : '';

		reycore_customizer__title([
			'title'       => $args['label'],
			'description' => $args['description'],
			'section'     => $args['section'],
			'size'        => 'xs',
			'border'      => 'none',
			'upper'       => true,
			'priority'    => $priority,
		]);

		/**
		 * IMAGE
		 */
		ReyCoreKirki::add_field( 'rey_core_kirki', [
			'type'        => 'image',
			'settings'    => $args['settings']. '_img',
			'section'     => $args['section'],
			'default'     => $args['image']['default'],
			'priority'   => $priority,
			// 'transport'   => 'auto',
			'output'      => [
				[
					'element' => !empty( $args['image']['output_element'] ) ? $args['image']['output_element'] : $args['output_element'],
					'property' => $args['image']['output_property'],
					'value_pattern' => 'url($)'
				]
			],
			'active_callback' => $args['active_callback']
		] );

		/**
		 * REPEAT
		 */
		if( in_array('repeat', $args['supports']) ):
			ReyCoreKirki::add_field( 'rey_core_kirki', [
				'type'        => 'select',
				'settings'    => $args['settings']. '_repeat',
				'label'       => __( 'Background Repeat', 'rey-core' ),
				'section'     => $args['section'],
				'default'     => $args['image']['default'],
				'choices'     => reycore__background_repeat_choices(),
				'priority'   => $priority,
				'transport'   => 'auto',
				'output'      => [
					[
						'element' => !empty( $args['repeat']['output_element'] ) ? $args['repeat']['output_element'] : $args['output_element'],
						'property' => $args['repeat']['output_property'],
					]
				],
				'active_callback' => $active_callback
			] );
		endif;

		/**
		 * ATTACHMENT
		 */
		if( in_array('attachment', $args['supports']) ):
			ReyCoreKirki::add_field( 'rey_core_kirki', [
				'type'        => 'select',
				'settings'    => $args['settings']. '_attachment',
				'label'       => __( 'Background Attachment', 'rey-core' ),
				'section'     => $args['section'],
				'default'     => $args['attachment']['default'],
				'choices'     => reycore__background_attachment_choices(),
				'priority'   => $priority,
				'transport'   => 'auto',
				'output'      => [
					[
						'element' => !empty( $args['attachment']['output_element'] ) ? $args['attachment']['output_element'] : $args['output_element'],
						'property' => $args['attachment']['output_property'],
					]
				],
				'active_callback' => $active_callback
			] );
		endif;

		/**
		 * SIZE
		 */
		if( in_array('size', $args['supports']) ):
			ReyCoreKirki::add_field( 'rey_core_kirki', [
				'type'        => 'select',
				'settings'    => $args['settings']. '_size',
				'label'       => __( 'Background Size', 'rey-core' ),
				'section'     => $args['section'],
				'default'     => $args['size']['default'],
				'choices'     => reycore__background_size_choices(),
				'priority'   => $priority,
				'transport'   => 'auto',
				'output'      => [
					[
						'element' => !empty( $args['size']['output_element'] ) ? $args['size']['output_element'] : $args['output_element'],
						'property' => $args['size']['output_property'],
					]
				],
				'active_callback' => $active_callback
			] );
		endif;

		/**
		 * POSITION
		 */
		if( in_array('position', $args['supports']) ):
			/**
			 * POSITION X
			 */
			ReyCoreKirki::add_field( 'rey_core_kirki', [
				'type'        => 'text',
				'settings'    => $args['settings']. '_positionx',
				'label'       => __( 'Background Horizontal Position ', 'rey-core' ),
				'section'     => $args['section'],
				'default'     => $args['positionx']['default'],
				'priority'   => $priority,
				'transport'   => 'auto',
				'output'      => [
					[
						'element' => !empty( $args['positionx']['output_element'] ) ? $args['positionx']['output_element'] : $args['output_element'],
						'property' => $args['positionx']['output_property']
					]
				],
				'active_callback' => $active_callback
			] );

			/**
			 * POSITION Y
			 */
			ReyCoreKirki::add_field( 'rey_core_kirki', [
				'type'        => 'text',
				'settings'    => $args['settings']. '_positiony',
				'label'       => __( 'Background Vertical Position ', 'rey-core' ),
				'section'     => $args['section'],
				'default'     => $args['positiony']['default'],
				'priority'   => $priority,
				'transport'   => 'auto',
				'output'      => [
					[
						'element' => !empty( $args['positiony']['output_element'] ) ? $args['positiony']['output_element'] : $args['output_element'],
						'property' => $args['positiony']['output_property']
					]
				],
				'active_callback' => $active_callback
			] );
		endif;

		/**
		 * COLOR
		 */
		if( in_array('color', $args['supports']) ):
			ReyCoreKirki::add_field( 'rey_core_kirki', [
				'type'        => 'color',
				'settings'    => $args['settings']. '_color',
				'label'       => __( 'Background Color', 'rey-core' ),
				'section'     => $args['section'],
				'default'     => $args['color']['default'],
				'transport'   => 'auto',
				'priority'   => $priority,
				'choices'     => [
					'alpha' => true,
				],
				'output'      => [
					[
						'element' => !empty( $args['color']['output_element'] ) ? $args['color']['output_element'] : $args['output_element'],
						'property' => $args['color']['output_property'],
					]
				],
				'active_callback' => $args['active_callback']
			] );
		endif;


		/**
		 * BLEND
		 */
		if( in_array('blend', $args['supports']) ):
			ReyCoreKirki::add_field( 'rey_core_kirki', [
				'type'        => 'select',
				'settings'    => $args['settings']. '_blend',
				'label'       => __( 'Background Blend', 'rey-core' ),
				'section'     => $args['section'],
				'default'     => $args['blend']['default'],
				'choices'     => reycore__background_blend_choices(),
				'priority'   => $priority,
				'transport'   => 'auto',
				'output'      => [
					[
						'element' => !empty( $args['blend']['output_element'] ) ? $args['blend']['output_element'] : $args['output_element'],
						'property' => $args['blend']['output_property'],
					]
				],
				'active_callback' => $args['active_callback']
			] );
		endif;

		ReyCoreKirki::add_field( 'rey_core_kirki', [
			'type'        => 'custom',
			'settings'    => $args['settings']. '_end',
			'section'     => $args['section'],
			'priority'    => $priority,
			'default'     => '<hr>',
		] );

	}
endif;

/**
 * Typography Group Field
 *
 * @since 1.5.0
 **/
function reycore_customizer__typography_field( $args = [] )
{
	$defaults = [
		'settings'        => '',
		'section'         => '',
		'label'           => esc_html__('Typography', 'rey-core'),
		'transport'       => 'auto',
		'selector'        => '',
		'active_callback' => [],
		'exclude'         => [],
		'default' => [
			'font_family'    => [
				'font-family'      => '',
			],
			'font_size'      => '',
			'font_weight'    => '',
			'text_transform' => '',
			'font_style'     => '',
			'decoration'     => '',
			'line_height'    => '',
			'letter_spacing' => '',
		],
	];

	$args = reycore__wp_parse_args($args, $defaults);

	$fields['start'] = [
		'type'        => 'custom',
		'default'     => 'title + popover here',
	];

	$fields['font_family'] = [
		'type'        => 'typography',
		'label'    => esc_html__( 'Font Family', 'rey-core' ),
		'show_variants' => false,
		'load_choices' => true,
		'property' => 'font-family',
	];

	$fields['font_size'] = [
		'type'        => 'slider',
		'label'       => esc_html__( 'Font Size', 'rey-core' ),
		'choices'     => [
			'min'  => 0,
			'max'  => 200,
			'step' => 1,
			'suffix' => 'px'
		],
		'property'   => 'font-size',
		'responsive' => true
	];

	$fields['font_weight'] = [
		'type'        => 'select',
		'label'       => esc_html__( 'Font Weight', 'rey-core' ),
		'choices'     => [
			''       => esc_html__( 'Default', 'rey-core' ),
			'100'    => 100,
			'200'    => 200,
			'300'    => 300,
			'400'    => 400,
			'500'    => 500,
			'600'    => 600,
			'700'    => 700,
			'800'    => 800,
			'900'    => 900,
			'normal' => esc_html__( 'Normal', 'rey-core' ),
			'bold'   => esc_html__( 'Bold', 'rey-core' ),
		],
		'property' => 'font-weight',
	];

	$fields['text_transform'] = [
		'type'        => 'select',
		'label'       => esc_html__( 'Transform', 'rey-core' ),
		'choices'     => [
			''           => esc_html__( 'Default', 'rey-core' ),
			'uppercase'  => esc_html__( 'Uppercase', 'rey-core' ),
			'lowercase'  => esc_html__( 'Lowercase', 'rey-core' ),
			'capitalize' => esc_html__( 'Capitalize', 'rey-core' ),
			'none'       => esc_html__( 'Normal', 'rey-core' ),
		],
		'property' => 'text-transform',
	];

	$fields['font_style'] = [
		'type'        => 'select',
		'label'       => esc_html__( 'Style', 'rey-core' ),
		'choices'     => [
			''        => esc_html__( 'Default', 'rey-core' ),
			'normal'  => esc_html__( 'Normal', 'rey-core' ),
			'italic'  => esc_html__( 'Italic', 'rey-core' ),
			'oblique' => esc_html__( 'Oblique', 'rey-core' ),
		],
	];

	$fields['decoration'] = [
		'type'        => 'select',
		'label'       => esc_html__( 'Decoration', 'rey-core' ),
		'choices'     => [
			''             => esc_html__( 'Default', 'rey-core' ),
			'underline'    => esc_html__( 'Underline', 'rey-core' ),
			'overline'     => esc_html__( 'Overline', 'rey-core' ),
			'line-through' => esc_html__( 'Line through', 'rey-core' ),
			'none'         => esc_html__( 'none', 'rey-core' ),
		],
		'property' => 'text-decoration',
	];

	$fields['line_height'] = [
		'type'        => 'slider',
		'label'       => esc_html__( 'Line Height', 'rey-core' ),
		'choices'     => [
			'min'  => 0,
			'max'  => 200,
			'step' => 1,
			'suffix' => 'em'
		],
		'property'   => 'line-height',
		'responsive' => true
	];

	$fields['letter_spacing'] = [
		'type'        => 'slider',
		'label'       => esc_html__( 'Letter Spacing', 'rey-core' ),
		'choices'     => [
			'min'  => -5,
			'max'  => 10,
			'step' => 0.1,
			'suffix' => 'px',
		],
		'property'   => 'letter-spacing',
		'responsive' => true
	];

	$fields['end'] = [
		'type'        => 'custom',
		'default'     => 'end here',
	];

	foreach ($fields as $key => $field) {

		if( in_array($key, $args['exclude']) ){
			continue;
		}

		$field['settings'] = sprintf('typo_%s_%s', $args['settings'], $key);
		$field['section'] = $args['section'];

		if( isset($args['default'][$key]) ){
			$field['default'] = $args['default'][$key];
		}

		if( $args['selector'] ){
			$field['output'] = [
				[
					'element' => $args['selector'],
					'property' => $field['property'],
				]
			];
		}

		if( $args['active_callback'] ){
			$field['active_callback'] = $args['active_callback'];
		}

		$is_responsive = isset($field['responsive']) && $field['responsive'];

		unset($field['property']);
		unset($field['responsive']);

		if( $is_responsive ){
			reycore_customizer__add_responsive_field($field);
		}
		else {
			reycore_customizer__add_field($field);
		}
	}

}


if(!function_exists('reycore__customizer_to_acf')):
	/**
	 * Push customizer option into ACF
	 *
	 * @since 1.0.0
	 */
	function reycore__customizer_to_acf( $wp_customizer )
	{
		// Sync shop page cover with post's option
		if( class_exists('ACF') && class_exists('WooCommerce') && wc_get_page_id('shop') !== -1 ){
			update_field('page_cover', get_theme_mod('cover__shop_page'), wc_get_page_id('shop') );
		}
		// Sync blog page cover with post's option
		if( class_exists('ACF') && get_option( 'page_for_posts' ) ){
			update_field('page_cover', get_theme_mod('cover__blog_home'), get_option( 'page_for_posts' ) );
		}
	}
endif;
add_action('customize_save_after', 'reycore__customizer_to_acf', 20);


if(!function_exists('reycore_customizer__print_js')):
	/**
	 * Print JS script in Customizer Preview
	 *
	 * @since 1.0.0
	 */
	function reycore_customizer__print_js()
	{ ?>
		<script type="text/javascript">
			(function ( api ) {

				var lsName = "rey-active-view-selector-" + <?php echo is_multisite() ? absint( get_current_blog_id() ) : 0 ?>,
					removeLs = function(){
						localStorage.removeItem( lsName );
					};

				api.bind( "ready", function () {
					removeLs();
				});

				api.bind( "saved", function () {
					removeLs();
				});

				api( 'woocommerce_catalog_columns', function( value ) {
					value.bind( function( to ) {
						removeLs();
					} );
				} );
			})( wp.customize );
		</script>
		<?php
	}
endif;
add_action( 'customize_controls_print_scripts', 'reycore_customizer__print_js', 30 );


if(!function_exists('reycore_customizer__print_css')):
	/**
	 * Print CSS styles in Customizer Preview
	 *
	 * @since 1.0.0
	 */
	function reycore_customizer__print_css()
	{ ?>
		<style type="text/css">
			@media screen and (min-width: 1667px){
				.wp-full-overlay.expanded {
					margin-left: 345px;
				}
			}
			.wp-full-overlay-sidebar {
				width: 345px;
			}
			.preview-mobile .wp-full-overlay-main {
				margin: auto 0 auto -180px;
				width: 360px;
				height: 640px;
			}
			.preview-tablet .wp-full-overlay-main {
				margin: auto 0 auto -384px;
				width: 768px;
			}
		</style>
		<?php
	}
endif;
add_action( 'customize_controls_print_styles', 'reycore_customizer__print_css' );


if(!function_exists('reycore_customizer__help_link')):
	/**
	 * Display Help links in Customizer's panels
	 *
	 * @since 1.0.0
	 */
	function reycore_customizer__help_link( $args = [] ){

		$args = reycore__wp_parse_args($args, [
			'url' => '',
			'priority' => 500,
			'section'     => '',
		]);

		$content = '<hr class="--separator --separator-top2x"><h2 class="rey-helpTitle">' . esc_html__('Need help?', 'rey-core') . '</h2>';
		$content .= '<p>'. sprintf( __('Read more about <a href="%s" target="_blank">these options from this panel</a>.', 'rey-core'), $args['url']) . '</p>';

		ReyCoreKirki::add_field( 'rey_core_kirki', [
			'type'     => 'custom',
			'settings' => 'chelp_' . $args['section'],
			'section'  => $args['section'],
			'priority' => $args['priority'],
			'default'  => $content,
		] );
	}
endif;


if(!function_exists('reycore_customizer__title')):
	/**
	 * Display Help links in Customizer's panels
	 *
	 * @since 1.0.0
	 */
	function reycore_customizer__title( $args = [] ){

		$args = reycore__wp_parse_args($args, [
			'title'       => '',
			'description' => '',
			'default'     => '',
			'section'     => '',
			'type'        => 'custom',
			'size'        => 'md',
			'border'      => 'bottom',
			'color'       => 'inherit',
			'upper'       => false,
		]);

		$classes[] = '--fz-' . $args['size'];
		$classes[] = '--border-' . $args['border'];
		$classes[] = '--color-' . $args['color'];

		if( $args['upper'] ){
			$classes[] = '--upper';
		}

		$content = sprintf('<div class="rey-customizerTitle-wrapper %s">', implode(' ', $classes));
		$content .= sprintf('<h2 class="rey-customizerTitle">%s</h2>', $args['title']);
		if( $args['description'] !== '' ){
			$content .= sprintf('<div class="description">%s</div>', $args['description']);
		}
		$content .= '</div>';

		if( $args['default'] === '' ){
			$args['default'] = $content;
		}

		$args['settings'] = sprintf('title_%s_%s', $args['section'], str_replace('-','_', sanitize_title($args['title'])) );
		$args['description'] = '';

		ReyCoreKirki::add_field( 'rey_core_kirki', $args );
	}
endif;


if(!function_exists('reycore_customizer__separator')):
	/**
	 * Display Help links in Customizer's panels
	 *
	 * @since 1.0.0
	 */
	function reycore_customizer__separator( $args = [] ){

		$args = reycore__wp_parse_args($args, [
			'section'     => '',
			'id'     => '',
		]);

		ReyCoreKirki::add_field( 'rey_core_kirki', [
			'type'     => 'custom',
			'settings' => 'separator_' . $args['section'] . '_' . $args['id'],
			'section'  => $args['section'],
			'default'  => '<hr class="--separator-simple"/>',
		] );
	}
endif;


if(!function_exists('reycore_customizer__notice')):
	/**
	 * Display Help links in Customizer's panels
	 *
	 * @since 1.0.0
	 */
	function reycore_customizer__notice( $args = [] ){

		$args = reycore__wp_parse_args($args, [
			'type'        => 'custom',
			'default'     => '',
			'section'     => '',
			'notice_type'     => 'warning',
		]);

		if( $args['default'] === '' ){
			return;
		}

		$text = $args['default'];
		$slug = substr( strip_tags($text), 0, 15);

		$args['default'] = sprintf( '<p class="rey-cstInfo --%2$s">%1$s</p>', $text, $args['notice_type'] );
		$args['settings'] = sprintf('notice_%s_%s', $args['section'], str_replace('-','_', sanitize_title($slug)) );

		reycore_customizer__add_field( $args );
	}
endif;


if(!function_exists('reycore_customizer__title_tooltip')):
	/**
	 * Shows title with help tip
	 *
	 * @since 1.4.2
	 **/
	function reycore_customizer__title_tooltip( $title, $help = '', $args = [] )
	{
		$args = wp_parse_args($args, [
			'tip' => '?',
			'style' => 'qmark',
			'size' => '',
			'clickable' => false
		]);

		return sprintf('<div class="rey-csTitleHelp-popWrapper">
				<span class="rey-csTitleHelp-title">%1$s</span>
				<div class="rey-csTitleHelp-pop --pop-%4$s">
					<span class="rey-csTitleHelp-label">%2$s</span>
					<span class="rey-csTitleHelp-content %6$s" style="%5$s">%3$s</span>
				</div>
			</div>',
			$title,
			$args['tip'],
			$help,
			esc_attr($args['style']),
			!empty($args['size']) ? sprintf('min-width: %spx;', absint($args['size'])) : '',
			$args['clickable'] ? '' : '--pevn'
		);
	}
endif;


add_action( 'customize_register', function( $wp_customize ) {

	/**
	 * The custom control class
	 */
	class Kirki_Controls_Group_Start_Control extends Kirki_Control_Base {
		public $type = 'rey_group_start';
		public function render_content() {
			if( !empty($this->label) ){
				printf('<h4>%s</h4>', $this->label);
			}
		}
	}
	class Kirki_Controls_Group_End_Control extends Kirki_Control_Base {
		public $type = 'rey_group_end';
		public function render_content() {}
	}
	// Register our custom control with Kirki
	add_filter( 'kirki_control_types', function( $controls ) {
		$controls['rey_group_start'] = 'Kirki_Controls_Group_Start_Control';
		$controls['rey_group_end'] = 'Kirki_Controls_Group_End_Control';
		return $controls;
	} );

} );

if(!function_exists('reycore__customizer_group_start')):
/**
 * Add field group start automatically
 *
 * @since 1.3.0
 **/
function reycore__customizer_group_start($setting, $args)
{
	if( isset($args['rey_group_start']) ){

		if( $args['rey_group_start'] === true ){
			unset($args['label']);
		}

		$args = reycore__wp_parse_args($args['rey_group_start'], $args);

		$args['type'] = 'rey_group_start';
		$args['settings'] = $args['settings'] . '__start';

		if( isset($args['rey_group_start']['active_callback']) ){
			$args['active_callback'] = $args['rey_group_start']['active_callback'];
		}

		unset($args['description']);
		unset($args['tooltip']);
		unset($args['rey_group_start']);
		unset($args['rey_group_end']);
		unset($args['responsive']);
		unset($args['output']);

		ReyCoreKirki::add_field( 'rey_core_kirki', $args );
	}
}
endif;

add_action('reycore/kirki_fields/before_field', 'reycore__customizer_group_start', 10, 2);


if(!function_exists('reycore__customizer_group_end')):
/**
 * Add field group end automatically
 *
 * @since 1.3.0
 **/
function reycore__customizer_group_end($setting, $args)
{
	if( isset($args['rey_group_end']) ){

		$args = reycore__wp_parse_args($args['rey_group_end'], $args);

		$args['type'] = 'rey_group_end';
		$args['settings'] = $args['settings'] . '__end';

		unset($args['rey_group_start']);
		unset($args['rey_group_end']);
		unset($args['label']);
		unset($args['description']);
		unset($args['tooltip']);
		unset($args['active_callback']);
		unset($args['output']);
		unset($args['responsive']);

		ReyCoreKirki::add_field( 'rey_core_kirki', $args );
	}
}
endif;

add_action('reycore/kirki_fields/after_field', 'reycore__customizer_group_end', 10, 2);


if(!function_exists('reycore__customizer_responsive')):
	/**
	 * Add responsive fields
	 *
	 * @since 1.3.5
	 **/
	function reycore__customizer_responsive($setting, $args)
	{
		if( isset($args['responsive']) && $args['responsive'] === true ){

			$setting = $args['settings'];

			add_action( 'customize_render_control_' . $setting, function( $customizer ) {
				$customizer->json['is_responsive'] = 'desktop';
			} );

			// avoid loophole
			unset($args['rey_group_start']);
			unset($args['rey_group_end']);
			unset($args['responsive']);

			$breakpoints = [
				'tablet', 'mobile'
			];

			$mq = [
				'tablet'	=> '@media (min-width: 768px) and (max-width: 1025px)',
				'mobile'	=> '@media (max-width: 767px)',
			];

			foreach($breakpoints as $breakpoint){

				add_action("reycore/kirki_fields/after_field=" . $setting , function() use ($args, $breakpoint, $setting, $mq){

					// assign media queries
					if( isset($args['output']) ){
						foreach($args['output'] as $i => $rule){
							$args['output'][$i]['media_query'] = $mq[$breakpoint];
						}
					}

					// create setting name per device
					$args['settings'] = sprintf( '%s_%s', $setting, $breakpoint);

					// pass JSON attribute
					add_action( 'customize_render_control_' . $args['settings'], function( $customizer ) use ($breakpoint) {
						$customizer->json['is_responsive'] = $breakpoint;
					} );

					// render new device fields
					ReyCoreKirki::add_field( 'rey_core_kirki', $args );
				});

			}
		}
	}
endif;
add_action('reycore/kirki_fields/before_field', 'reycore__customizer_responsive', 10, 2);


if(!function_exists('reycore__customizer_responsive_handlers')):
	/**
	 * Add responsive handlers
	 *
	 * @since 1.3.5
	 **/
	function reycore__customizer_responsive_handlers()
	{ ?>

		<script type="text/html" id="tmpl-rey-customizer-responsive-handler">
			<div class="rey-cst-responsiveHandlers {{ data.device === 'desktop' ? '--inactive' : '' }}">
				<span data-breakpoint="desktop"><i class="dashicons dashicons-desktop"></i></span>
				<span data-breakpoint="tablet"><i class="dashicons dashicons-tablet"></i></span>
				<span data-breakpoint="mobile"><i class="dashicons dashicons-smartphone"></i></span>
			</div>
		</script>

		<script type="text/html" id="tmpl-rey-customizer-typo-handler">
			<div class="rey-cstTypo-wrapper">
				<button class="rey-cstTypo-btn">
					<span class="dashicons dashicons-edit"></span>
					<span class="rey-cstTypo-ff">{{ data.ff }}</span>
					<span class="rey-cstTypo-fz">{{ data.fz }}</span>
					<span class="rey-cstTypo-fw">{{ data.fw }}</span>
				</button>
			</div>
		</script>
		<?php
	}
endif;
add_action( 'customize_controls_print_scripts', 'reycore__customizer_responsive_handlers' );



if(!function_exists('reycore__compare_values')):
	/**
	 * Compares the 2 values given the condition
	 *
	 * @param mixed  $value1   The 1st value in the comparison.
	 * @param mixed  $value2   The 2nd value in the comparison.
	 * @param string $operator The operator we'll use for the comparison.
	 * @return boolean whether The comparison has succeded (true) or failed (false).
	 */
	function reycore__compare_values( $value1, $value2, $operator ) {
		if ( '===' === $operator ) {
			return $value1 === $value2;
		}
		if ( '!==' === $operator ) {
			return $value1 !== $value2;
		}
		if ( ( '!=' === $operator || 'not equal' === $operator ) ) {
			return $value1 != $value2; // phpcs:ignore WordPress.PHP.StrictComparisons
		}
		if ( ( '>=' === $operator || 'greater or equal' === $operator || 'equal or greater' === $operator ) ) {
			return $value2 >= $value1;
		}
		if ( ( '<=' === $operator || 'smaller or equal' === $operator || 'equal or smaller' === $operator ) ) {
			return $value2 <= $value1;
		}
		if ( ( '>' === $operator || 'greater' === $operator ) ) {
			return $value2 > $value1;
		}
		if ( ( '<' === $operator || 'smaller' === $operator ) ) {
			return $value2 < $value1;
		}
		if ( 'contains' === $operator || 'in' === $operator ) {
			if ( is_array( $value1 ) && is_array( $value2 ) ) {
				foreach ( $value2 as $val ) {
					if ( in_array( $val, $value1 ) ) { // phpcs:ignore WordPress.PHP.StrictInArray
						return true;
					}
				}
				return false;
			}
			if ( is_array( $value1 ) && ! is_array( $value2 ) ) {
				return in_array( $value2, $value1 ); // phpcs:ignore WordPress.PHP.StrictInArray
			}
			if ( is_array( $value2 ) && ! is_array( $value1 ) ) {
				return in_array( $value1, $value2 ); // phpcs:ignore WordPress.PHP.StrictInArray
			}
			return ( false !== strrpos( $value1, $value2 ) || false !== strpos( $value2, $value1 ) );
		}
		return $value1 == $value2; // phpcs:ignore WordPress.PHP.StrictComparisons
	}
endif;


if(!function_exists('reycore__get_fallback_mod')):
	function reycore__get_fallback_mod( $mod, $default, $fb_args = [] ){

		$override = apply_filters( "reycore_theme_mod_{$mod}", NULL );

		if( !is_null( $override ) ) {
			return $override;
		}

		// check cst. option default
		if( $mod === '' || is_null( get_theme_mod($mod, NULL) ) ){

			// Fallback args.
			$fb_args = wp_parse_args([
				'mod' => '',
				'value' => '',
				'compare' => '',
			], $fb_args);

			// it's a new installation so just return default
			if( is_null( get_theme_mod($fb_args['mod'], NULL) ) ){
				return $default;
			}

			// it's an existing installation
			// and have to return old value

			// is a true/false and needs comparison
			if( $fb_args['compare'] && $fb_args['value'] ){
				return reycore__compare_values( get_theme_mod($fb_args['mod']), $fb_args['value'], $fb_args['compare'] );
			}

			return get_theme_mod($fb_args['mod']);
		}

		// just return the new option
		else {
			return get_theme_mod($mod, $default);
		}

	}
endif;

if(!function_exists('reycore__get_post_types_list')):
/**
 * Get CTP list
 *
 * @since 1.6.6
 **/
function reycore__get_post_types_list()
{
	$exclude = ['attachment', 'elementor_library', 'rey-global-sections'];

	$post_types_objects = get_post_types([
			'public' => true,
		], 'objects'
	);

	$post_types_objects = apply_filters( 'reycore/internal/ajaxsearch/post_type_objects', $post_types_objects );

	$options = [];

	foreach ( $post_types_objects as $cpt_slug => $post_type ) {
		if ( in_array( $cpt_slug, $exclude, true ) ) {
			continue;
		}

		$options[ $cpt_slug ] = $post_type->labels->name;
	}

	return $options;
}
endif;


if(!function_exists('reycore_customizer__wc_taxonomies')):
	/**
	 * Get WooCommerce taxonomies
	 *
	 * @since 1.6.x
	 **/
	function reycore_customizer__wc_taxonomies( $args = [] )
	{
		$args = wp_parse_args($args, [
			'exclude' => []
		]);

		$wc_taxonomy_attributes = [
			'product_cat' => esc_html__( 'Product Catagories', 'rey-core' ),
			'product_tag' => esc_html__( 'Product Tags', 'rey-core' ),
		];

		if( !function_exists('wc_get_attribute_taxonomies()') ){
			return $wc_taxonomy_attributes;
		}

		foreach( wc_get_attribute_taxonomies() as $attribute ) {
			$attribute_name = wc_attribute_taxonomy_name( $attribute->attribute_name );
			$wc_taxonomy_attributes[$attribute_name] = $attribute->attribute_label;
		}

		if( !empty($args['exclude']) ){
			foreach ($args['exclude'] as $to_exclude) {
				unset($wc_taxonomy_attributes[$to_exclude]);
			}
		}

		return $wc_taxonomy_attributes;
	}
endif;
