
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