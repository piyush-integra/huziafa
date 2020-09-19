<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

add_action( 'customize_register', function( $wp_customize ) {

	/**
	 * The custom control class
	 */
	class Kirki_Controls_ReyButton extends Kirki_Control_Base {

		public $type = 'rey-button';

		public function render_content() {

			$label = $this->label;
			$description = $this->description;

            $input_id = '_customize-input-' . $this->id;
            $name = $this->id;

            ?>
                <div class="rey-control-wrap">

                    <?php if( !empty( $label ) ) : ?>
						<label for="<?php esc_attr_e( $input_id ); ?>">
							<span class="customize-control-title rey-control-title"> <?php echo $label; ?> </span>
						</label>
					<?php endif; ?>

					<div class="customize-control-content">
						<?php
							$attributes[] = sprintf('id="%s"', esc_attr( $input_id ) );

							if( isset($this->choices['action']) && $action = $this->choices['action'] ){
								$attributes[] = sprintf('data-action="%s"', esc_attr( $action ) );
							}

							if( isset($this->choices['href']) && $href = $this->choices['href'] ){
								$attributes[] = sprintf('href="%s"', esc_attr( $href ) );

								if( isset($this->choices['target']) && $target = $this->choices['target'] ){
									$attributes[] = sprintf('target="%s"', esc_attr( $target ) );
								}
							}
						?>
						<a class="button js-rey-button" <?php echo implode(' ', $attributes) ?>>
							<?php echo $this->choices['text']; ?>
						</a>
					</div>

                    <?php if( !empty( $description ) ) : ?>
                            <span class="customize-control-description rey-control-description"><?php echo $description; ?></span>
                    <?php endif; ?>
                </div>
            <?php
		}

	}

	add_filter( 'kirki_control_types', function( $controls ) {
		$controls['rey-button'] = 'Kirki_Controls_ReyButton';
		return $controls;
	} );
} );


class Kirki_Field_Rey_Button extends Kirki_Field {

	protected function set_type() {
		$this->type = 'rey-button';
	}

	/**
	 * Sets the $choices
	 *
	 * @access protected
	 */
	protected function set_choices() {
		$this->choices = wp_parse_args(
			$this->choices,
			array(
				'action'  => '',
				'href'  => '',
				'target'  => '',
				'text'  => 'Click',
			)
		);
		$this->choices['action']  = esc_attr($this->choices['action']);
		$this->choices['href']  = esc_attr($this->choices['href']);
		$this->choices['target']  = esc_attr($this->choices['target']);
		$this->choices['text']  = esc_html($this->choices['text']);
	}

}
