<?php
/**
 * Copyright (c) Adrien Foulon - 2018. All rights reserved.
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * Created by PhpStorm.
 * User: Adrien
 * Date: 22/05/2018
 * Time: 14:33
 */

//TODO

namespace Tofandel\Core\Objects;


class WP_Translations {
	public $textdomain;

	public function __construct( $textdomain = 'default' ) {
		$this->textdomain = $textdomain;
		$plugins          = WP_Plugin::getSingletons();
		foreach ( $plugins as $plugin ) {

		}
	}

	public function __( $string ) {
		return __( $string, $this->textdomain );
	}

	public function _e( $string ) {
		return _e( $string, $this->textdomain );
	}

	public function _x( $string, $context ) {
		return _x( $string, $context, $this->textdomain );
	}

	public function _n( $single, $plural, $number ) {
		return _n( $single, $plural, $number, $this->textdomain );
	}

	public function _ex( $string, $context ) {
		return _ex( $string, $context, $this->textdomain );
	}

	public function _nx( $single, $plural, $number, $context ) {
		return _nx( $single, $plural, $number, $context, $this->textdomain );
	}

	public function esc_attr__( $string ) {
		return esc_attr__( $string, $this->textdomain );
	}

	public function esc_attr_e( $string ) {
		return esc_attr_e( $string, $this->textdomain );
	}


	public function esc_attr_x( $string, $context ) {
		return esc_attr_x( $string, $context, $this->textdomain );
	}

	public function esc_html__( $string ) {
		return esc_html__( $string, $this->textdomain );
	}

	public function esc_html_e( $string ) {
		return esc_html_e( $string, $this->textdomain );
	}

	public function esc_html_x( $string, $context ) {
		return esc_html_x( $string, $context, $this->textdomain );
	}
}