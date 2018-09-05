<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'reduxCoreRequired', false ) ) {
	class reduxCoreRequired {
		public $parent = null;

		public function __construct( $parent ) {
			$this->parent             = $parent;
			Redux_Functions::$_parent = $parent;


			/**
			 * action 'redux/page/{opt_name}/'
			 */
			do_action( "redux/page/{$parent->args['opt_name']}/" );

		}


	}
}