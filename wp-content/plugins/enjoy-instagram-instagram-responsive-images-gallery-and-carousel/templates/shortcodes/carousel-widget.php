<?php
/**
 * Carousel widget shortcode template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<script>
  jQuery(function () {

    $slider = jQuery("#owl-<?php echo $i ?>");

    jQuery(document.body)
      .on('click touchend', '#swipebox-slider .current img', function (e) {
        jQuery('#swipebox-next').click();
        return false;
      })
      .on('click touchend', '#swipebox-slider .current', function (e) {
        jQuery('#swipebox-close').trigger('click');
      });


    jQuery(".swipebox").swipebox({
      hideBarsDelay: 0
    });

    $slider.owlCarousel({
      items: <?php echo "{$n}"; ?>,
      navigation: <?php echo "{$n_y_n}"; ?>,
      autoPlay: true,
      afterAction: function () {
        let it = $slider.find('.owl-item a');
        it.css('height', it.first().outerWidth());
      }
    });

    $slider.fadeIn('slow');
  });
</script>

<div id="owl-<?php echo $i ?>" class="owl-example enjoy-instagram-carousel" style="display:none;">
	<?php foreach ( $result as $entry ) :

		$url = $entry['images']['standard_resolution']['url'];
		if ( $entry['type'] === 'video' ) {
			$url .= '&swipeboxvideo=1';
		}

		$link_style = "style=\"background-image: url('{$entry['images']['thumbnail']['url']}'); background-size: cover; display: block; opacity: 1;\"";
		?>
        <div class="box <?php echo $entry['type'] === 'video' ? 'ei-media-type-video' : 'ei-media-type-image' ?>">
            <a title="<?php echo $entry['caption']['text'] ?>" rel="gallery_swypebox"
               class="<?php echo $entry['type'] === 'video' ? 'swipebox swipebox_video' : 'swipebox' ?>"
               href="<?php echo $url ?>" <?php echo $link_style ?>>
                <img alt="<?php echo $entry['caption']['text'] ?>"
                     src="<?php echo $entry['images']['thumbnail']['url'] ?>">
            </a>
        </div>
	<?php endforeach; ?>
</div>