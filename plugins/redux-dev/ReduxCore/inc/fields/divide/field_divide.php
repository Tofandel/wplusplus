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
 * @subpackage  Field_Divide
 * @author      Dovy Paukstys
 * @author      Kevin Provance (kprovance)
 * @version     4.0.0
 */
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Don't duplicate me!
if (!class_exists('ReduxFramework_divide', false)) {

    /**
     * Main ReduxFramework_divide class
     *
     * @since       1.0.0
     */
    class ReduxFramework_divide extends Redux_Field {

        /**
         * Field Render Function.
         * Takes the vars and outputs the HTML for the field in the settings
         *
         * @since         1.0.0
         * @access        public
         * @return        void
         */
        public function render() {
            echo '</td></tr></table>';
            echo '<div data-id="' . esc_attr($this->field['id']) . '" id="divide-' . esc_attr($this->field['id']) . '" class="divide ' . esc_attr($this->field['class']) . '"><div class="inner"><span>&nbsp;</span></div></div>';
            echo '<table class="form-table no-border"><tbody><tr><th></th><td>';
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
            if ($this->parent->args['dev_mode']) {
                wp_enqueue_style(
                        'redux-field-divide', ReduxCore::$_url . 'inc/fields/divide/field_divide.css', array(), $this->timestamp, 'all'
                );
            }
        }

    }

}