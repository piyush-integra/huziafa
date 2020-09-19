<?php
/**
 * Class Grid_Widget
 */

if( ! defined( 'ABSPATH' ) ) {
    exit;
}


class Grid_Widget extends WP_Widget {

    /**
     * Grid_Widget constructor.
     *
     * @since 4.0.0
     */
	public function __construct() {
		parent::__construct(
			'Grid_Widget', // Base ID
            __('EnjoyInstagram - Grid', 'enjoy-instagram-instagram-responsive-images-gallery-and-carousel'), // Name
            array( 'description' => __( 'EnjoyInstagram Widget for Grid View', 'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ), ) // Args
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
        $number_cols_in_grid = apply_filters( 'widget_content', $instance['number_cols_in_grid'] );
        $number_rows_in_grid = apply_filters( 'widget_content', $instance['number_rows_in_grid'] );
        $user_or_hashtag_in_grid = apply_filters( 'widget_content', $instance['user_or_hashtag_in_grid'] );

        echo $args['before_widget'];
        if ( ! empty( $title ) ) {
            echo $args['before_title'] . $title . $args['after_title'];
        }
        echo do_shortcode('[enjoyinstagram_mb_grid_widget id="'.$args['widget_id'].'" n_c="'.$number_cols_in_grid.'" n_r="'.$number_rows_in_grid.'" u_or_h="'.$user_or_hashtag_in_grid.'"]');
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
            'number_cols_in_grid'       => '4',
            'number_rows_in_grid'       => '2',
            'user_or_hashtag_in_grid'   => 'user'
        ) );

        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _ex( 'Title:', 'option label', 'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'number_cols_in_grid' ); ?>">
                <?php _ex( 'Number of Columns', 'option label', 'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ); ?>:
            </label><br>
            <select name="<?php echo $this->get_field_name( 'number_cols_in_grid' ); ?>" id="<?php echo $this->get_field_id( 'number_cols_in_grid' ); ?>">
                <?php for ($i = 1; $i <= 10; $i++) : ?>
                    <option value="<?php echo $i ?>" <?php selected($i, $instance['number_cols_in_grid']); ?>>
                        <?php echo "&nbsp;" . $i; ?>
                    </option>
                <?php endfor; ?>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'number_rows_in_grid' ); ?>">
                <?php _ex( 'Number of Rows', 'option label', 'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ); ?>:
            </label><br>
            <select name="<?php echo $this->get_field_name( 'number_rows_in_grid' ); ?>" id="<?php echo $this->get_field_id( 'number_rows_in_grid' ); ?>">
                <?php for ($i = 1; $i <= 10; $i++) : ?>
                    <option value="<?php echo $i ?>" <?php selected($i, $instance['number_rows_in_grid']); ?>>
                        <?php echo "&nbsp;" . $i; ?>
                    </option>
                <?php endfor; ?>
            </select>
        </p>
        <p>
            <?php _e( 'Show pics', 'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ); ?>: <br />
            <input type="radio" name="<?php echo $this->get_field_name( 'user_or_hashtag_in_grid' ); ?>" <?php checked( 'user', $instance['user_or_hashtag_in_grid'] ); ?> value="user">
            <?php _e( 'of Your Profile', 'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ); ?><br>
            <input type="radio" name="<?php echo $this->get_field_name( 'user_or_hashtag_in_grid' ); ?>" <?php checked( 'hashtag', $instance['user_or_hashtag_in_grid'] ); ?> value="hashtag">
            <?php _e( 'by Hashtag', 'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ); ?><br>
        </p>
        <?php
    }


    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        $instance['number_cols_in_grid'] = ( ! empty( $new_instance['number_cols_in_grid'] ) ) ? strip_tags( $new_instance['number_cols_in_grid'] ) : '';
        $instance['number_rows_in_grid'] = ( ! empty( $new_instance['number_rows_in_grid'] ) ) ? strip_tags( $new_instance['number_rows_in_grid'] ) : '';
        $instance['user_or_hashtag_in_grid'] = ( ! empty( $new_instance['user_or_hashtag_in_grid'] ) ) ? strip_tags( $new_instance['user_or_hashtag_in_grid'] ) : '';

        return $instance;
    }

}

function register_Grid_Widget() {
    register_widget( 'Grid_Widget' );
}
add_action( 'widgets_init', 'register_Grid_Widget' );