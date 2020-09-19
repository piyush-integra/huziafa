<?php
/**
 * Grid shortcode template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<script type="text/javascript">
  jQuery(function () {
    jQuery('#grid-<?php echo $i; ?>').gridrotator({
      rows: <?php echo $rows ?>,
      columns: <?php echo $cols ?>,
      animType: 'fadeInOut',
      onhover: false,
      interval: 7000,
      preventClick: false,
      w1400: {
        rows: <?php echo $rows ?>,
        columns: <?php echo $cols ?>
      },
      w1024: {
        rows: <?php echo $rows ?>,
        columns: <?php echo $cols ?>
      },
      w768: {
        rows: <?php echo $rows ?>,
        columns: <?php echo $cols ?>
      },
      w480: {
        rows: <?php echo $rows ?>,
        columns: <?php echo $cols ?>
      },
      w320: {
        rows: <?php echo $rows ?>,
        columns: <?php echo $cols ?>
      },
      w240: {
        rows: <?php echo $rows ?>,
        columns: <?php echo $cols ?>
      }
    });

    jQuery('#grid-<?php echo $i; ?>').fadeIn('1000');
  });
</script>
<div id="grid-<?php echo $i ?>" class="ri-grid ri-grid-size-2 ri-shadow" style="display:none;">
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