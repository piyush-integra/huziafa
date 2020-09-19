<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section = 'woocommerce_checkout';

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'checkout_distraction_free',
	'label'       => esc_html__( 'Distraction Free Checkout', 'rey-core' ),
	'description' => esc_html__( 'This option disables header for the checkout page, to prevent user distractions.', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
	'priority'    => 0
] );
