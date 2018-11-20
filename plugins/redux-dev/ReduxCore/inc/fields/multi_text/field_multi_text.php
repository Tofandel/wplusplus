<?php
/**
 * Multi Textbox Field
 *
 * @package     ReduxFramework
 * @subpackage  Field_Multi_Text
 * @author      Dovy Paukstys & Kevin Provance (kprovance)
 * @version     4.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Don't duplicate me!
if ( ! class_exists( 'ReduxFramework_Multi_Text', false ) ) {

	/**
	 * Main ReduxFramework_multi_text class
	 *
	 * @since       1.0.0
	 */
	class ReduxFramework_Multi_Text extends Redux_Field {

		/**
		 * Field Render Function.
		 * Takes the vars and outputs the HTML for the field in the settings
		 *
		 * @since       1.0.0
		 * @access      public
		 * @return      void
		 */
		public function render() {
			$this->add_text   = ( isset( $this->field['add_text'] ) ) ? $this->field['add_text'] : esc_html__( 'Add More', 'redux-framework' );
			$this->show_empty = ( isset( $this->field['show_empty'] ) ) ? $this->field['show_empty'] : true;

			echo '<ul id="' . esc_attr( $this->field['id'] ) . '-ul" class="redux-multi-text">';

			if ( isset( $this->value ) && is_array( $this->value ) ) {
				foreach ( $this->value as $k => $value ) {
					if ( '' !== $value || ( '' === $value && true === $this->show_empty ) ) {
						echo '<li>';
						echo '<input 
								type="text" 
								id="' . esc_attr( $this->field['id'] . '-' . $k ) . '" 
								name="' . esc_attr( $this->field['name'] . $this->field['name_suffix'] ) . '[]" 
								value="' . esc_attr( $value ) . '" 
								class="regular-text ' . esc_attr( $this->field['class'] ) . '" /> ';

						echo '<a 
								data-id="' . esc_attr( $this->field['id'] ) . '-ul" 
								href="javascript:void(0);" 
								class="deletion redux-multi-text-remove">' .
								esc_html__( 'Remove', 'redux-framework' ) . '</a>';
						echo '</li>';
					}
				}
			} elseif ( true === $this->show_empty ) {
				echo '<li>';
				echo '<input 
						type="text" 
						id="' . esc_attr( $this->field['id'] . '-0' ) . '" 
						name="' . esc_attr( $this->field['name'] . $this->field['name_suffix'] ) . '[]" 
						value="" 
						class="regular-text ' . esc_attr( $this->field['class'] ) . '" /> ';

				echo '<a 
						data-id="' . esc_attr( $this->field['id'] ) . '-ul"  
						href="javascript:void(0);" 
						class="deletion redux-multi-text-remove">' .
						esc_html__( 'Remove', 'redux-framework' ) . '</a>';

				echo '</li>';
			}

			$the_name = '';
			if ( isset( $this->value ) && empty( $this->value ) && false === $this->show_empty ) {
				$the_name = $this->field['name'] . $this->field['name_suffix'];
			}

			echo '<li style="display:none;">';
			echo '<input 
					type="text" 
					id="' . esc_attr( $this->field['id'] ) . '" 
					name="' . esc_attr( $the_name ) . '" 
					value="" 
					class="regular-text" /> ';

			echo '<a 
					data-id="' . esc_attr( $this->field['id'] ) . '-ul" 
					href="javascript:void(0);" 
					class="deletion redux-multi-text-remove">' .
					esc_html__( 'Remove', 'redux-framework' ) . '</a>';

			echo '</li>';
			echo '</ul>';

			echo '<span style="clear:both;display:block;height:0;"></span>';
			$this->field['add_number'] = ( isset( $this->field['add_number'] ) && is_numeric( $this->field['add_number'] ) ) ? $this->field['add_number'] : 1;
			echo '<a href="javascript:void(0);" class="button button-primary redux-multi-text-add" data-add_number="' . esc_attr( $this->field['add_number'] ) . '" data-id="' . esc_attr( $this->field['id'] ) . '-ul" data-name="' . esc_attr( $this->field['name'] . $this->field['name_suffix'] ) . '">' . esc_html( $this->add_text ) . '</a><br/>';
		}

		/**
		 * Enqueue Function.
		 * If this field requires any scripts, or css define this function and register/enqueue the scripts/css
		 *
		 * @since       1.0.0
		 * @access      public
		 * @return      void
		 */
		public function enqueue() {
			wp_enqueue_script(
				'redux-field-multi-text-js',
				ReduxCore::$_url . 'inc/fields/multi_text/field_multi_text' . Redux_Functions::isMin() . '.js',
				array( 'jquery', 'redux-js' ),
				$this->timestamp,
				true
			);

			if ( $this->parent->args['dev_mode'] ) {
				wp_enqueue_style(
					'redux-field-multi-text-css',
					ReduxCore::$_url . 'inc/fields/multi_text/field_multi_text.css',
					array(),
					$this->timestamp,
					'all'
				);
			}
		}
	}
}
