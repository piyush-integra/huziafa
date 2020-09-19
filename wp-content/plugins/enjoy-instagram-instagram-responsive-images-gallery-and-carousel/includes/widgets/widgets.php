<?php
/**
 * Class Slider_Widget
 */

if( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Slider_Widget extends WP_Widget {

    /**
     * Slider_Widget constructor.
     *
     * @since 4.0.0
     */
	public function __construct() {
		parent::__construct(
			'Slider_Widget', // Base ID
			__('EnjoyInstagram - Carousel', 'enjoy-instagram-instagram-responsive-images-gallery-and-carousel'), // Name
			array( 'description' => __( 'EnjoyInstagram Widget for Carousel View', 'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ), ) // Args
		);
	}

    /**
     * Widget callback
     *
     * @param array $args
     * @param array $instance
     */
	public function widget( $args, $instance ) {
        $title = apply_filters( 'widget_title', $instance['title'] );
        $number_images_in_slide = apply_filters( 'widget_content', $instance['number_images_in_slide'] );
        $navigation_y_n = apply_filters( 'widget_content', $instance['navigation_y_n'] );
        $user_or_hashtag = apply_filters( 'widget_content', $instance['user_or_hashtag'] );


        echo $args['before_widget'];
        if ( ! empty( $title ) ){
            echo $args['before_title'] . $title . $args['after_title'];
        }
        echo do_shortcode('[enjoyinstagram_mb_widget n="'.$number_images_in_slide.'" id="'.$args['widget_id'].'" n_y_n="'.$navigation_y_n.'" u_or_h="'.$user_or_hashtag.'"]');
        echo $args['after_widget'];
	}

    /**
     * Admin widget
     *
     * @param array $instance
     * @return string|void
     */
	public function form( $instance ) {

        $title = isset( $instance['title'] ) ? $instance['title'] : __( 'EnjoyInstagram', 'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' );
        $instance = wp_parse_args( (array) $instance, array(
            'number_images_in_slide'    => '4',
            'navigation_y_n'            => 'false',
            'user_or_hashtag'           => 'user'
		 ) );
		?>

		<p>
		    <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _ex( 'Title:', 'option label', 'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
        <p>
		    <label for="<?php echo $this->get_field_id( 'number_images_in_slide' ); ?>">
                <?php _ex( 'Images displayed at a time', 'option label', 'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ); ?>:
            </label><br />
            <select name="<?php echo $this->get_field_name( 'number_images_in_slide' ); ?>" id="<?php echo $this->get_field_id( 'number_images_in_slide' ); ?>">
                <?php for ($i = 1; $i <= 10; $i++) : ?>
                    <option value="<?php echo $i ?>" <?php selected($i, $instance['number_images_in_slide']); ?>>
                        <?php echo "&nbsp;" . $i; ?>
                    </option>
                <?php endfor; ?>
            </select>
        </p>
        <p>
		    <label for="<?php echo $this->get_field_id( 'navigation_y_n' ); ?>">
                <?php _ex( 'Navigation buttons', 'option label', 'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ); ?>:
            </label><br />
            <select name="<?php echo $this->get_field_name( 'navigation_y_n' ); ?>" id="<?php echo $this->get_field_id( 'navigation_y_n' ); ?>">
                <option value="true" <?php selected( 'true', $instance['navigation_y_n'] ); ?>>
                    <?php _e( 'Yes', 'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ); ?>
                </option>
                <option value="false" <?php selected( 'false', $instance['navigation_y_n'] ); ?>>
                    <?php _e( 'No', 'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ); ?>
                </option>
            </select>
		</p>
        <p>
            <?php _e( 'Show pics', 'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ); ?>: <br />
            <input type="radio" name="<?php echo $this->get_field_name( 'user_or_hashtag' ); ?>" <?php checked( 'user', $instance['user_or_hashtag'] ); ?> value="user">
            <?php _e( 'of Your Profile', 'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ); ?><br>
            <input type="radio" name="<?php echo $this->get_field_name( 'user_or_hashtag' ); ?>" <?php checked( 'hashtag', $instance['user_or_hashtag'] ); ?> value="hashtag">
            <?php _e( 'by Hashtag', 'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ); ?><br>
        </p>
		<?php
	}

    /**
     * Update widget options
     *
     * @param array $new_instance
     * @param array $old_instance
     * @return array
     */
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
		$instance['number_images_in_slide'] = ( ! empty( $new_instance['number_images_in_slide'] ) ) ? strip_tags( $new_instance['number_images_in_slide'] ) : '';
		$instance['navigation_y_n'] = ( ! empty( $new_instance['navigation_y_n'] ) ) ? strip_tags( $new_instance['navigation_y_n'] ) : '';
		$instance['user_or_hashtag'] = ( ! empty( $new_instance['user_or_hashtag'] ) ) ? strip_tags( $new_instance['user_or_hashtag'] ) : '';

        return $instance;
    }

}

function register_Slider_Widget() {
    register_widget( 'Slider_Widget' );
}
add_action( 'widgets_init', 'register_Slider_Widget' );