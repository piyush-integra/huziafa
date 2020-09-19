<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

add_action( 'customize_register', function( $wp_customize ) {

	/**
	 * The custom control class
	 */
	class Kirki_Controls_ReySelect extends Kirki_Control_Base {

		public $type = 'rey-select';

		public function render_content() {

			$label = $this->label;
			$description = $this->description;

            $input_id = '_customize-input-' . $this->id;
			$name = $this->id;

			$active_value = $this->value();
			?>

			<div class="rey-control-wrap ">

				<?php if( !empty( $label ) ) : ?>
					<span class="customize-control-title rey-control-title"> <?php echo $label; ?> </span>
				<?php endif; ?>

				<?php if( !empty( $description ) ) : ?>
					<span class="customize-control-description rey-control-description"><?php echo $description; ?></span>
				<?php endif; ?>

				<div class="customize-control-content">

					<select id="<?php esc_attr_e( $input_id ); ?>" name="<?php esc_attr_e( $name ); ?>" <?php $this->link(); ?> >
						<?php

							if( ! array_key_exists($active_value, $this->choices) ){
								// $active_value = $this->setting->default;
							}

							foreach ($this->choices as $key => $value) {
								printf( '<option value="%1$s" %3$s>%2$s</option>', esc_attr($key), $value, selected($key, $active_value, false) );
							}
						?>
					</select>

				</div>
			</div>

            <?php
		}

	}

	add_filter( 'kirki_control_types', function( $controls ) {
		$controls['rey-select'] = 'Kirki_Controls_ReySelect';
		return $controls;
	} );
} );
