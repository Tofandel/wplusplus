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
 * @subpackage  Field_Background
 * @author      Dovy Paukstys
 * @author      Kevin Provance (kprovance)
 * @version     4.0.0
 */
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Don't duplicate me!
if (!class_exists('ReduxFramework_background', false)) {

    /**
     * Main ReduxFramework_background class
     *
     * @since       3.1.5
     */
    class ReduxFramework_background extends Redux_Field {

        public function set_defaults() {
            $defaults = array(
                'background-color' => true,
                'background-repeat' => true,
                'background-attachment' => true,
                'background-position' => true,
                'background-image' => true,
                'background-gradient' => false,
                'background-clip' => false,
                'background-origin' => false,
                'background-size' => true,
                'preview_media' => false,
                'preview' => true,
                'preview_height' => '200px',
                'transparent' => true,
            );

            $this->field = wp_parse_args($this->field, $defaults);

            // No errors please
            $defaults = array(
                'background-color' => '',
                'background-repeat' => '',
                'background-attachment' => '',
                'background-position' => '',
                'background-image' => '',
                'background-clip' => '',
                'background-origin' => '',
                'background-size' => '',
                'media' => array(),
            );

            $this->value = wp_parse_args($this->value, $defaults);

            $defaults = array(
                'id' => '',
                'width' => '',
                'height' => '',
                'thumbnail' => '',
            );

            $this->value['media'] = wp_parse_args($this->value['media'], $defaults);
        }

        /**
         * Field Render Function.
         * Takes the vars and outputs the HTML for the field in the settings
         *
         * @since       1.0.0
         * @access      public
         * @return      void
         */
        public function render() {
            $this->select2_config['allowClear'] = true;

            if (isset($this->field['select2'])) {
                $this->field['select2'] = wp_parse_args($this->field['select2'], $this->select2_config);
            } else {
                $this->field['select2'] = $this->select2_config;
            }

            $this->field['select2'] = Redux_Functions::sanitize_camel_case_array_keys($this->field['select2']);

            $select2_data = Redux_Functions::create_data_string($this->field['select2']);

            if ($this->field['background-color'] === true) {
                if (isset($this->value['color']) && empty($this->value['background-color'])) {
                    $this->value['background-color'] = $this->value['color'];
                }

                $def_bg_color = isset($this->field['default']['background-color']) ? $this->field['default']['background-color'] : "";

                echo '<input ';
                echo 'data-id="' . esc_attr($this->field['id']) . '"';
                echo 'name="' . esc_attr($this->field['name'] . $this->field['name_suffix']) . '[background-color]"';
                echo 'id="' . esc_attr($this->field['id']) . '-color"';
                echo 'class="color-picker redux-color redux-background-input redux-color-init ' . esc_attr($this->field['class']) . '"';
                echo 'type="text" value="' . esc_attr($this->value['background-color']) . '"';
                echo 'data-default-color="' . esc_attr($def_bg_color) . '"';

                if (ReduxCore::$_pro_loaded) {
                    $data = array(
                        'field' => $this->field,
                        'index' => 'color'
                    );

                    echo apply_filters('redux/pro/render/color_alpha', $data);
                }

                echo '/>';

                echo '<input type="hidden" class="redux-saved-color" id="' . esc_attr($this->field['id']) . '-saved-color' . '" value="">';

                if (!isset($this->field['transparent']) || $this->field['transparent'] !== false) {
                    $tChecked = "";
                    if ($this->value['background-color'] == "transparent") {
                        $tChecked = ' checked="checked"';
                    }
                    echo '<label for="' . esc_attr($this->field['id']) . '-transparency" class="color-transparency-check"><input type="checkbox" class="checkbox color-transparency redux-background-input ' . esc_attr($this->field['class']) . '" id="' . esc_attr($this->field['id']) . '-transparency" data-id="' . esc_attr($this->field['id']) . '-color" value="1"' . $tChecked . '> ' . esc_html__('Transparent', 'redux-framework') . '</label>';
                }

                if ($this->field['background-repeat'] === true || $this->field['background-position'] === true || $this->field['background-attachment'] === true) {
                    echo '<br />';
                }
            }

            if ($this->field['background-image']) {
                if ($this->field['background-repeat'] === true) {
                    $array = array(
                        'no-repeat' => esc_html__('No Repeat', 'redux-framework'),
                        'repeat' => esc_html__('Repeat All', 'redux-framework'),
                        'repeat-x' => esc_html__('Repeat Horizontally', 'redux-framework'),
                        'repeat-y' => esc_html__('Repeat Vertically', 'redux-framework'),
                        'inherit' => esc_html__('Inherit', 'redux-framework')
                    );

                    echo '<select id="' . esc_attr($this->field['id']) . '-repeat-select" data-placeholder="' . esc_html__('Background Repeat', 'redux-framework') . '" name="' . esc_attr($this->field['name'] . $this->field['name_suffix']) . '[background-repeat]" class="redux-select-item redux-background-input redux-background-repeat ' . esc_attr($this->field['class']) . '"' . esc_attr($select2_data) . '>';
                    echo '<option></option>';

                    foreach ($array as $k => $v) {
                        echo '<option value="' . esc_attr($k) . '"' . selected($this->value['background-repeat'], $k, false) . '>' . $v . '</option>';
                    }

                    echo '</select>';
                }

                if ($this->field['background-clip'] === true) {
                    $array = array(
                        'inherit' => esc_html__('Inherit', 'redux-framework'),
                        'border-box' => esc_html__('Border Box', 'redux-framework'),
                        'content-box' => esc_html__('Content Box', 'redux-framework'),
                        'padding-box' => esc_html__('Padding Box', 'redux-framework')
                    );

                    echo '<select id="' . esc_attr($this->field['id']) . '-clip-select" data-placeholder="' . esc_html__('Background Clip', 'redux-framework') . '" name="' . esc_attr($this->field['name'] . $this->field['name_suffix']) . '[background-clip]" class="redux-select-item redux-background-input redux-background-clip ' . esc_attr($this->field['class']) . '"' . esc_attr($select2_data) . '>';
                    echo '<option></option>';

                    foreach ($array as $k => $v) {
                        echo '<option value="' . esc_attr($k) . '"' . selected($this->value['background-clip'], $k, false) . '>' . $v . '</option>';
                    }

                    echo '</select>';
                }

                if ($this->field['background-origin'] === true) {
                    $array = array(
                        'inherit' => esc_html__('Inherit', 'redux-framework'),
                        'border-box' => esc_html__('Border Box', 'redux-framework'),
                        'content-box' => esc_html__('Content Box', 'redux-framework'),
                        'padding-box' => esc_html__('Padding Box', 'redux-framework')
                    );

                    echo '<select id="' . esc_attr($this->field['id']) . '-origin-select" data-placeholder="' . esc_html__('Background Origin', 'redux-framework') . '" name="' . esc_attr($this->field['name'] . $this->field['name_suffix']) . '[background-origin]" class="redux-select-item redux-background-input redux-background-origin ' . esc_attr($this->field['class']) . '"' . esc_attr($select2_data) . '>';
                    echo '<option></option>';

                    foreach ($array as $k => $v) {
                        echo '<option value="' . esc_attr($k) . '"' . selected($this->value['background-origin'], $k, false) . '>' . $v . '</option>';
                    }

                    echo '</select>';
                }

                if ($this->field['background-size'] === true) {
                    $array = array(
                        'inherit' => esc_html__('Inherit', 'redux-framework'),
                        'cover' => esc_html__('Cover', 'redux-framework'),
                        'contain' => esc_html__('Contain', 'redux-framework')
                    );

                    echo '<select id="' . esc_attr($this->field['id']) . '-size-select" data-placeholder="' . esc_html__('Background Size', 'redux-framework') . '" name="' . esc_attr($this->field['name'] . $this->field['name_suffix']) . '[background-size]" class="redux-select-item redux-background-input redux-background-size ' . esc_attr($this->field['class']) . '"' . esc_attr($select2_data) . '>';
                    echo '<option></option>';

                    foreach ($array as $k => $v) {
                        echo '<option value="' . esc_attr($k) . '"' . selected($this->value['background-size'], $k, false) . '>' . $v . '</option>';
                    }

                    echo '</select>';
                }

                if ($this->field['background-attachment'] === true) {
                    $array = array(
                        'fixed' => esc_html__('Fixed', 'redux-framework'),
                        'scroll' => esc_html__('Scroll', 'redux-framework'),
                        'inherit' => esc_html__('Inherit', 'redux-framework')
                    );

                    echo '<select id="' . esc_attr($this->field['id']) . '-attachment-select" data-placeholder="' . esc_html__('Background Attachment', 'redux-framework') . '" name="' . esc_attr($this->field['name'] . $this->field['name_suffix']) . '[background-attachment]" class="redux-select-item redux-background-input redux-background-attachment ' . esc_attr($this->field['class']) . '"' . esc_attr($select2_data) . '>';
                    echo '<option></option>';

                    foreach ($array as $k => $v) {
                        echo '<option value="' . esc_attr($k) . '"' . selected($this->value['background-attachment'], $k, false) . '>' . $v . '</option>';
                    }

                    echo '</select>';
                }

                if ($this->field['background-position'] === true) {
                    $array = array(
                        'left top' => esc_html__('Left Top', 'redux-framework'),
                        'left center' => esc_html__('Left center', 'redux-framework'),
                        'left bottom' => esc_html__('Left Bottom', 'redux-framework'),
                        'center top' => esc_html__('Center Top', 'redux-framework'),
                        'center center' => esc_html__('Center Center', 'redux-framework'),
                        'center bottom' => esc_html__('Center Bottom', 'redux-framework'),
                        'right top' => esc_html__('Right Top', 'redux-framework'),
                        'right center' => esc_html__('Right center', 'redux-framework'),
                        'right bottom' => esc_html__('Right Bottom', 'redux-framework')
                    );

                    echo '<select id="' . esc_attr($this->field['id']) . '-position-select" data-placeholder="' . esc_html__('Background Position', 'redux-framework') . '" name="' . esc_attr($this->field['name'] . $this->field['name_suffix']) . '[background-position]" class="redux-select-item redux-background-input redux-background-position ' . esc_attr($this->field['class']) . '"' . esc_attr($select2_data) . '>';
                    echo '<option></option>';

                    foreach ($array as $k => $v) {
                        echo '<option value="' . esc_attr($k) . '"' . selected($this->value['background-position'], $k, false) . '>' . $v . '</option>';
                    }

                    echo '</select>';
                }
            }

            if ($this->field['background-image']) {
                echo '<br />';

                if (empty($this->value) && !empty($this->field['default'])) {
                    if (is_array($this->field['default'])) {
                        if (!empty($this->field['default']['media']['id'])) {
                            $this->value['media']['id'] = $this->field['default']['media']['id'];
                        } else if (!empty($this->field['default']['id'])) {
                            $this->value['media']['id'] = $this->field['default']['id'];
                        }

                        if (!empty($this->field['default']['url'])) {
                            $this->value['background-image'] = $this->field['default']['url'];
                        } else if (!empty($this->field['default']['media']['url'])) {
                            $this->value['background-image'] = $this->field['default']['media']['url'];
                        } else if (!empty($this->field['default']['background-image'])) {
                            $this->value['background-image'] = $this->field['default']['background-image'];
                        }
                    } else {
                        if (is_numeric($this->field['default'])) { // Check if it's an attachment ID
                            $this->value['media']['id'] = $this->field['default'];
                        } else { // Must be a URL
                            $this->value['background-image'] = $this->field['default'];
                        }
                    }
                }

                if (empty($this->value['background-image']) && !empty($this->value['media']['id'])) {
                    $img = wp_get_attachment_image_src($this->value['media']['id'], 'full');
                    $this->value['background-image'] = $img[0];
                    $this->value['media']['width'] = $img[1];
                    $this->value['media']['height'] = $img[2];
                }

                $hide = 'hide ';

                if (( isset($this->field['preview_media']) && $this->field['preview_media'] === false)) {
                    $this->field['class'] .= " noPreview";
                }

                if ((!empty($this->field['background-image']) && $this->field['background-image'] === true ) || isset($this->field['preview']) && $this->field['preview'] === false) {
                    $hide = '';
                }

                $placeholder = isset($this->field['placeholder']) ? $this->field['placeholder'] : esc_html__('No media selected', 'redux-framework');

                echo '<input placeholder="' . $placeholder . '" type="text" class="redux-background-input ' . $hide . 'upload ' . esc_attr($this->field['class']) . '" name="' . esc_attr($this->field['name'] . $this->field['name_suffix']) . '[background-image]" id="' . esc_attr($this->parent->args['opt_name']) . '[' . $this->field['id'] . '][background-image]" value="' . esc_url($this->value['background-image']) . '" />';
                echo '<input type="hidden" class="upload-id ' . esc_attr($this->field['class']) . '" name="' . esc_attr($this->field['name'] . $this->field['name_suffix']) . '[media][id]" id="' . esc_attr($this->parent->args['opt_name']) . '[' . $this->field['id'] . '][media][id]" value="' . esc_attr($this->value['media']['id']) . '" />';
                echo '<input type="hidden" class="upload-height" name="' . esc_attr($this->field['name'] . $this->field['name_suffix']) . '[media][height]" id="' . esc_attr($this->parent->args['opt_name']) . '[' . esc_attr($this->field['id']) . '][media][height]" value="' . esc_attr($this->value['media']['height']) . '" />';
                echo '<input type="hidden" class="upload-width" name="' . esc_attr($this->field['name'] . $this->field['name_suffix']) . '[media][width]" id="' . esc_attr($this->parent->args['opt_name']) . '[' . esc_attr($this->field['id']) . '][media][width]" value="' . esc_attr($this->value['media']['width']) . '" />';
                echo '<input type="hidden" class="upload-thumbnail" name="' . esc_attr($this->field['name'] . $this->field['name_suffix']) . '[media][thumbnail]" id="' . esc_attr($this->parent->args['opt_name']) . '[' . esc_attr($this->field['id']) . '][media][thumbnail]" value="' . esc_url($this->value['media']['thumbnail']) . '" />';

                //Preview
                $hide = '';

                if (( isset($this->field['preview_media']) && $this->field['preview_media'] === false ) || empty($this->value['background-image'])) {
                    $hide = 'hide ';
                }

                if (empty($this->value['media']['thumbnail']) && !empty($this->value['background-image'])) { // Just in case
                    if (!empty($this->value['media']['id'])) {
                        $image = wp_get_attachment_image_src($this->value['media']['id'], array(
                            150,
                            150
                                ));
                        $this->value['media']['thumbnail'] = $image[0];
                    } else {
                        $this->value['media']['thumbnail'] = $this->value['background-image'];
                    }
                }

                echo '<div class="' . $hide . 'screenshot">';
                echo '<a class="of-uploaded-image" href="' . esc_url($this->value['background-image']) . '" target="_blank">';

                $alt = wp_prepare_attachment_for_js($this->value['media']['id']);
                $alt = isset($alt['alt']) ? $alt['alt'] : '';

                echo '<img class="redux-option-image" id="image_' . esc_attr($this->value['media']['id']) . '" src="' . esc_url($this->value['media']['thumbnail']) . '" alt="' . esc_attr($alt) . '" target="_blank" rel="external" />';
                echo '</a>';
                echo '</div>';

                //Upload controls DIV
                echo '<div class="upload_button_div">';

                //If the user has WP3.5+ show upload/remove button
                echo '<span class="button redux-background-upload" id="' . esc_attr($this->field['id']) . '-media">' . esc_html__('Upload', 'redux-framework') . '</span>';

                $hide = '';
                if (empty($this->value['background-image']) || $this->value['background-image'] == '') {
                    $hide = ' hide';
                }

                echo '<span class="button removeCSS redux-remove-background' . $hide . '" id="reset_' . esc_attr($this->field['id']) . '" rel="' . esc_attr($this->field['id']) . '">' . esc_html__('Remove', 'redux-framework') . '</span>';

                echo '</div>';
            }


            /**
             * Preview
             * */
            if (!isset($this->field['preview']) || $this->field['preview'] !== false) {
                $css = $this->css_style($this->value);

                $is_bg = strpos($css, 'background-image');

                if (empty($css) || !$is_bg) {
                    $css = "display:none;";
                }

                $css .= "height: " . esc_attr($this->field['preview_height']) . ";";
                echo '<p class="clear ' . esc_attr($this->field['id']) . '_previewer background-preview" style="' . esc_attr($css) . '">&nbsp;</p>';
            }
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
            if (function_exists('wp_enqueue_media')) {
                wp_enqueue_media();
            } else {
                if (!wp_script_is('media-upload')) {
                    wp_enqueue_script('media-upload');
                }
            }

            if (!wp_style_is('select2-css')) {
                wp_enqueue_style('select2-css');
            }

            if (!wp_style_is('wp-color-picker')) {
                wp_enqueue_style('wp-color-picker');
            }

            $dep_array = array('jquery', 'wp-color-picker', 'select2-js', 'redux-js');

            wp_enqueue_script(
                    'redux-field-background-js', ReduxCore::$_url . 'inc/fields/background/field_background' . Redux_Functions::isMin() . '.js', $dep_array, $this->timestamp, true
            );

            if ($this->parent->args['dev_mode']) {
                wp_enqueue_style(
                        'redux-field-background-css', ReduxCore::$_url . 'inc/fields/background/field_background.css', array(), $this->timestamp, 'all'
                );

                wp_enqueue_style('redux-color-picker-css');
            }
        }

        public function css_style($value = array()) {
            $css = '';

            if (!empty($value) && is_array($value)) {
                foreach ($value as $key => $value) {
                    if (!empty($value) && $key != "media") {
                        if ($key == "background-image") {
                            $css .= $key . ":url('" . esc_url($value) . "');";
                        } else {
                            $css .= $key . ":" . esc_attr($value) . ";";
                        }
                    }
                }
            }

            return $css;
        }

    }

}