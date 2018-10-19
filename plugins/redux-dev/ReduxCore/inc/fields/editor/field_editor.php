<?php

/**
 * Redux Framework is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 * Redux Framework is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with Redux Framework. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package     ReduxFramework
 * @subpackage  Field_Editor
 * @author      Dovy Paukstys
 * @author      Kevin Provance (kprovance)
 * @version     4.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Don't duplicate me!
if ( ! class_exists( 'ReduxFramework_editor', false ) ) {

	/**
	 * Main ReduxFramework_editor class
	 *
	 * @since       1.0.0
	 */
	class ReduxFramework_editor extends Redux_Field {

		/**
		 * Field Render Function.
		 * Takes the vars and outputs the HTML for the field in the settings
		 *
		 * @since       1.0.0
		 * @access      public
		 * @return      void
		 */
		public function render() {
			if ( ! isset( $this->field['args'] ) ) {
				$this->field['args'] = array();
			}

			$this->field['args']['onchange_callback'] = "alert('here')";

			// Setup up default args
			$defaults = array(
				'textarea_name' => esc_attr( $this->field['name'] . $this->field['name_suffix'] ),
				'editor_class'  => esc_attr( $this->field['class'] ),
				'textarea_rows' => 10, //Wordpress default
				'teeny'         => true,
			);

			if ( isset( $this->field['editor_options'] ) && empty( $this->field['args'] ) ) {
				$this->field['args'] = $this->field['editor_options'];
				unset( $this->field['editor_options'] );
			}

			$this->field['args'] = wp_parse_args( $this->field['args'], $defaults );

			wp_editor( $this->value, $this->field['id'], $this->field['args'] );
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
			if ( $this->parent->args['dev_mode'] ) {
				wp_enqueue_style(
					'redux-field-editor-css',
					ReduxCore::$_url . 'inc/fields/editor/field_editor.css',
					array(),
					$this->timestamp,
					'all'
				);
			}

			wp_enqueue_script(
				'redux-field-editor-js',
				ReduxCore::$_url . 'inc/fields/editor/field_editor' . Redux_Functions::isMin() . '.js',
				array( 'jquery', 'redux-js' ),
				$this->timestamp,
				true
			);
		}
	}
}