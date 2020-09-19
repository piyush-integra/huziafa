<?php

/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package rey
 */

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="//gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">
	<!-- <link rel="shortcut icon" type="image/x-icon" href="https://www.alhuzaifa.com/assets/frontend/images/favicon.ico" /> -->
	<link rel="shortcut icon" type="image/x-icon" href="https://www.alhuzaifa.com/wp-content/uploads/2020/07/fav-1.png" />
	<?php wp_head(); ?>
	<meta name="google-site-verification" content="mSYVm_ZMqP5gM3fl8qv50NjMypgNFb62xjbBOW9p674" />
	<!-- Global site tag (gtag.js) - Google Analytics -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=UA-72843040-1"></script>
	<script>
		window.dataLayer = window.dataLayer || [];

		function gtag() {
			dataLayer.push(arguments);
		}
		gtag('js', new Date());
		gtag('config', 'UA-72843040-1');
	</script>
</head>

<body <?php body_class(); ?> <?php rey__render_attributes('body'); ?>>

	<?php
	/**
	 * Prints content before site wrapper
	 * @hook rey/before_site_wrapper
	 */
	rey_action__before_site_wrapper(); ?>

	<div id="page" class="rey-siteWrapper <?php echo esc_attr(implode(' ', apply_filters('rey/site_wrapper_classes', []))) ?>">

		<?php
		/**
		 * Prints content after site wrapper starts
		 * @hook rey/after_site_wrapper_start
		 */
		rey_action__after_site_wrapper_start(); ?>

		<?php
		/**
		 * Prints Header
		 */
		rey_action__header();

		/**
		 * Prints content after header (outside of header)
		 * @hook rey/after_header_outside
		 */
		rey_action__after_header_outside(); ?>

		<div id="content" class="rey-siteContent <?php echo esc_attr(implode(' ', apply_filters('rey/site_content_classes', []))) ?>">