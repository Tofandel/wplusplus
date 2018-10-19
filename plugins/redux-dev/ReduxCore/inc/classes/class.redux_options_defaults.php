<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Redux_Options_Defaults', false ) ) {
	class Redux_Options_Defaults {
		public $options_defaults = array();
		public $fields = array();

		public function default_values( $opt_name, $sections = array(), $wp_data_class = array() ) {
			# We want it to be clean each time this is run.
			$this->options_defaults = array();

			if ( ! is_null( $sections ) ) {
				// fill the cache
				foreach ( $sections as $sk => $section ) {
					if ( ! isset ( $section[ 'id' ] ) ) {
						if ( ! is_numeric( $sk ) || ! isset ( $section[ 'title' ] ) ) {
							$section[ 'id' ] = $sk;
						}
						else {
							$section[ 'id' ] = sanitize_title( $section[ 'title' ], $sk );
						}

						$sections[ $sk ] = $section;
					}
					if ( isset ( $section[ 'fields' ] ) ) {
						foreach ( $section[ 'fields' ] as $k => $field ) {
							if ( empty ( $field[ 'id' ] ) && empty ( $field[ 'type' ] ) ) {
								continue;
							}

							if ( in_array( $field[ 'type' ], array( 'ace_editor' ) ) && isset ( $field[ 'options' ] ) ) {
								$sections[ $sk ][ 'fields' ][ $k ][ 'args' ] = $field[ 'options' ];
								unset ( $sections[ $sk ][ 'fields' ][ $k ][ 'options' ] );
							}

							if ( $field[ 'type' ] == "section" && isset ( $field[ 'indent' ] ) && $field[ 'indent' ] == "true" ) {
								$field[ 'class' ]                  = isset ( $field[ 'class' ] ) ? $field[ 'class' ] : '';
								$field[ 'class' ]                  .= " redux-section-indent-start";
								$sections[ $sk ][ 'fields' ][ $k ] = $field;
							}
							$this->field_default_values( $opt_name, $field, $wp_data_class );
						}
					}
				}
			}


			return $this->options_defaults;
		}


		public function field_default_values( $opt_name = "", $field, $wp_data_class = null ) {

			if ( $wp_data_class === null && class_exists( 'Redux_WordPress_Data' ) ) {
				$wp_data_class = new Redux_WordPress_Data();
			}

			// Detect what field types are being used
			if ( ! isset ( $core->fields[ $field[ 'type' ] ][ $field[ 'id' ] ] ) ) {
				$this->fields[ $field[ 'type' ] ][ $field[ 'id' ] ] = 1;
			}
			else {
				$this->fields[ $field[ 'type' ] ] = array( $field[ 'id' ] => 1 );
			}

			if ( isset ( $field[ 'default' ] ) ) {
				$this->options_defaults[ $field[ 'id' ] ] = apply_filters( "redux/{$opt_name}/field/{$field['type']}/defaults", $field[ 'default' ], $field );
			}
			elseif ( ( $field[ 'type' ] != "ace_editor" ) ) {
				// Sorter data filter
				if ( isset( $field[ 'data' ] ) && ! empty( $field[ 'data' ] ) ) {
					if ( ! isset( $field[ 'args' ] ) ) {
						$field[ 'args' ] = array();
					}
					if ( is_array( $field[ 'data' ] ) && ! empty( $field[ 'data' ] ) ) {
						foreach ( $field[ 'data' ] as $key => $data ) {
							if ( ! empty( $data ) ) {
								if ( ! isset ( $field[ 'args' ][ $key ] ) ) {
									$field[ 'args' ][ $key ] = array();
								}
								if ( $wp_data_class != "null" ) {
									$field[ 'options' ][ $key ] = $wp_data_class->wordpress_data->get( $data, $field[ 'args' ][ $key ], $opt_name );
								}
							}
						}
					}
					elseif ( $wp_data_class != "null" ) {
						{
							$field[ 'options' ] = $wp_data_class->get( $field[ 'data' ], $field[ 'args' ], $opt_name );
						}
					}

					if ( $field[ 'type' ] == "sorter" && isset ( $field[ 'data' ] ) && ! empty ( $field[ 'data' ] ) && is_array( $field[ 'data' ] ) ) {
						if ( ! isset ( $field[ 'args' ] ) ) {
							$field[ 'args' ] = array();
						}
						foreach ( $field[ 'data' ] as $key => $data ) {
							if ( ! isset ( $field[ 'args' ][ $key ] ) ) {
								$field[ 'args' ][ $key ] = array();
							}
							if ( $wp_data_class != "null" ) {
								$field[ 'options' ][ $key ] = $wp_data_class->get( $data, $field[ 'args' ][ $key ], $opt_name );
							}
						}
					}

					if ( isset ( $field[ 'options' ] ) ) {
						if ( $field[ 'type' ] == "sortable" ) {
							$this->options_defaults[ $field[ 'id' ] ] = array();
						}
						elseif ( $field[ 'type' ] == "image_select" ) {
							$this->options_defaults[ $field[ 'id' ] ] = '';
						}
						elseif ( $field[ 'type' ] == "select" ) {
							$this->options_defaults[ $field[ 'id' ] ] = '';
						}
						else {
							$this->options_defaults[ $field[ 'id' ] ] = $field[ 'options' ];
						}
					}
				}
			}
		}
	}
}