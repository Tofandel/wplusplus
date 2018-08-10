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
 * Date: 05/03/2018
 * Time: 15:04
 */

namespace Tofandel\Core\Objects;


class WP_Cron {
	protected static $crons = array();
	/**
	 * @var string
	 */
	protected $name;
	/**
	 * @var callable
	 */
	protected $function;
	/**
	 * @var int
	 */
	protected $recurrence = 60;
	protected $when = 0;
	protected $file;

	/**
	 * WP_Cron constructor.
	 *
	 * @param string $file
	 * @param string $name
	 * @param callable $function
	 * @param int $recurrence Interval in minutes
	 * @param int $when
	 */
	public function __construct( $file, $name, $function, $recurrence, $when = 0 ) {
		$this->name       = $name;
		$this->function   = $function;
		$this->recurrence = $recurrence;
		$this->file       = $file;

		if ( ! $when ) {
			$when = time();
		}
		$this->when = $when;

		add_filter( 'cron_schedules', [ $this, 'cron_schedule' ] );
		add_action( $name, [ $this, 'run' ] );

		if ( ! isset( self::$crons[ $file ] ) ) {
			self::$crons[ $file ]   = array();
			self::$crons[ $file ][] = &$this;
			register_activation_hook( $file, [ $this, 'activation' ] );
			register_deactivation_hook( $file, [ $this, 'deactivation' ] );
		} else {
			self::$crons[ $file ][] = $this;
		}

	}

	public function cron_schedule( $schedules ) {
		if ( ! isset( $schedules[ $this->recurrence . "min" ] ) ) {
			$schedules[ $this->recurrence . "min" ] = array(
				'interval' => $this->recurrence * 60,
				'display'  => sprintf( __( 'Once every %d minutes' ), $this->recurrence )
			);
		}

		return $schedules;
	}

	public function activation() {
		/**
		 * @var self $cron
		 */
		foreach ( self::$crons[ $this->file ] as $cron ) {
			$cron->register();
		}
	}

	public function register() {
		if ( ! wp_next_scheduled( $this->name ) ) {
			wp_schedule_event( $this->when, $this->recurrence . "min", $this->name );
		}
	}

	public function deactivation() {
		/**
		 * @var self $cron
		 */
		foreach ( self::$crons[ $this->file ] as $cron ) {
			$cron->unregister();
		}
	}

	public function unregister() {
		wp_clear_scheduled_hook( $this->name );
	}


	public function run() {
		call_user_func( $this->function );
	}
}