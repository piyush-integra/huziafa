<?php
/**
 * The template for displaying the footer
 * Contains the closing of the #content div and all content after.
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 * @package rey
 */ ?>

	</div>
	
	<div class="sticky-widget">
			<a href="https://wa.me/971505598758?text=I'm%20interested%20in%20alhuzaifa%20products" class="whatsapp-badge" id="chat" target="_blank"><i class="fab fa-whatsapp" aria-hidden="true"></i> <span></span></a>
			</div>
	<!-- .rey-siteContent -->

	<?php
	/**
	 * Prints Footer
	 */
	rey_action__footer();
	?>

</div>
<!-- .rey-siteWrapper -->

<?php
/**
 * Prints content after site wrapper
 * @hook rey/after_site_wrapper
 */
rey_action__after_site_wrapper(); ?>
	<script>
	    	jQuery(document).ready(function() {
		jQuery(".set > a").on("click", function() {
    if (jQuery(this).hasClass("active")) {
		jQuery(this).removeClass("active");
		jQuery(this)
        .siblings(".content")
        .slideUp(200);
		jQuery(".set > a i")
        .removeClass("fa-caret-up")
        .addClass("fa-caret-down");
    } else {
		jQuery(".set > a i")
        .removeClass("fa-caret-up")
        .addClass("fa-caret-down");
		jQuery(this)
        .find("i")
        .removeClass("fa-caret-down")
        .addClass("fa-caret-up");
		jQuery(".set > a").removeClass("active");
		jQuery(this).addClass("active");
		jQuery(".content").slideUp(200);
		jQuery(this)
        .siblings(".content")
        .slideDown(200);
    }
  });
});
	</script> 
	<script>
jQuery(document).ready(function(){

   jQuery("#menu-item-43>a").attr("href", "#")
    
});


</script>

<?php wp_footer(); ?>
</body>
</html>
