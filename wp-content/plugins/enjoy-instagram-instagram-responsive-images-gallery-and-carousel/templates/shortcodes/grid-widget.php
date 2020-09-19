<?php
/**
 * Grid shortcode template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="rigrid-<?php echo "{$id}"; ?>" class="ri-grid ri-grid-size-2 ri-shadow" style="display:none">
    <ul>
		<?php foreach ( $result as $entry ) :

			$url = $entry['images']['standard_resolution']['url'];
			if ( $entry['type'] === 'video' ) {
				$url .= '&swipeboxvideo=1';
			}

			?>
            <li>
                <a title="<?php echo $entry['caption']['text'] ?>" class="swipebox_grid <?php echo $entry['type'] === 'video' ? 'ei-media-type-video' : 'ei-media-type-image' ?>"
                   href="<?php echo $url ?>">
                    <img alt="<?php echo $entry['caption']['text'] ?>"
                         src="<?php echo $entry['images']['thumbnail']['url'] ?>">
                </a>
            </li>
		<?php endforeach; ?>
    </ul>
</div>

<script type="text/javascript">
  jQuery(function () {
    jQuery('#rigrid-<?php echo "{$id}"; ?>').gridrotator({
      rows: <?php echo "{$n_r}"; ?>,
      columns: <?php echo "{$n_c}"; ?>,
      animType: 'fadeInOut',
      onhover: false,
      interval: 7000,
      preventClick: false,
      w1024: {
        rows: <?php echo "{$n_r}"; ?>,
        columns: <?php echo "{$n_c}"; ?>
      },
      w768: {
        rows: <?php echo "{$n_r}"; ?>,
        columns: <?php echo "{$n_c}"; ?>
      },
      w480: {
        rows: <?php echo "{$n_r}"; ?>,
        columns: <?php echo "{$n_c}"; ?>
      },
      w320: {
        rows: <?php echo "{$n_r}"; ?>,
        columns: <?php echo "{$n_c}"; ?>
      },
      w240: {
        rows: <?php echo "{$n_r}"; ?>,
        columns: <?php echo "{$n_c}"; ?>
      }
    });
    jQuery('#rigrid-<?php echo "{$id}"; ?>').fadeIn('slow');
  });
</script>