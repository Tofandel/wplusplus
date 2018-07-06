<?php

namespace Tofandel\Core\Objects;

use Redux;

/**
 * Class ReduxConfig
 * A Proxy Class to configure Redux more easily
 *
 * @package Tofandel\Core\Objects
 */
class ReduxConfig implements \Tofandel\Core\Interfaces\ReduxConfig {
	/**
	 * @var string
	 */
	protected $opt_name;

	/**
	 * ReduxConfig constructor.
	 *
	 * @param string $opt_name
	 * @param array|null $args
	 */
	public function __construct( $opt_name, $args = null ) {
		$this->opt_name = $opt_name;

		self::loadRedux();
		self::loadExtensions( $opt_name );

		if ( isset( $args ) ) {
			$this->setArgs( $args );
		}
	}


	public static function loadRedux() {
		$plugins = get_option( 'active_plugins' );

		if ( ! class_exists( Redux::class ) ) {
			$file = get_transient( 'wpp_redux_install' );
			if ( $file && file_exists( $file ) ) {
				require_once $file;
			} else {
				foreach ( $plugins as $plugin ) {
					if ( strpos( $plugin, 'redux-framework' ) !== false ) {
						//We load redux's plugin
						if ( file_exists( WP_PLUGIN_DIR . $plugin ) ) {
							if ( preg_match( '#version[: ]*([0-9\.]+)#i', file_get_contents( WP_PLUGIN_DIR . $plugin ), $matches ) ) {
								if ( version_compare( '3.6.9', $matches[1], '==' ) ) {
									set_transient( 'wpp_redux_install', WP_PLUGIN_DIR . $plugin, 86400 );
									require_once WP_PLUGIN_DIR . $plugin;
									break;
								}
							}
						}
					}
				}
			}
		}
		if ( ! class_exists( Redux::class ) ) {
			global $WPlusPlusCore;
			if ( file_exists( $f = $WPlusPlusCore->folder( 'admin/redux-framework/framework.php' ) ) ) {
				require_once $f;
			}
		}
	}

	public static function loadExtensions( $opt_name ) {
		global $WPlusPlusCore;
		// All extensions placed within the extensions directory will be auto-loaded for your Redux instance.
		Redux::setExtensions( $opt_name, $WPlusPlusCore->folder( 'admin/redux-extensions' ) );
	}

	public function setArgs( $args = array() ) {
		if ( ! class_exists( Redux::class ) ) {
			return;
		}
		//Just some defaults, can override
		$def_args = array(
			'opt_name'           => $this->opt_name,
			'dev_mode'           => false,
			'use_cdn'            => true,
			'display_version'    => false,
			'update_notice'      => false,
			'menu_type'          => 'menu',
			'menu_title'         => 'W++',
			'allow_sub_menu'     => false,
			'page_priority'      => '39',
			'customizer'         => true,
			//'default_mark'       => ' (default)',
			'hints'              => array(
				'icon'          => 'el el-question-sign',
				'icon_position' => 'right',
				'icon_color'    => '#071f49',
				'icon_size'     => 'normal',
				'tip_style'     => array(
					'color'   => 'light',
					'shadow'  => '1',
					'rounded' => '1',
					'style'   => 'bootstrap',
				),
				'tip_position'  => array(
					'my' => 'top left',
					'at' => 'bottom right',
				),
				'tip_effect'    => array(
					'show' => array(
						'effect'   => 'fade',
						'duration' => '400',
						'event'    => 'mouseover',
					),
					'hide' => array(
						'effect'   => 'fade',
						'duration' => '400',
						'event'    => 'mouseleave unfocus',
					),
				),
			),
			'output'             => true,
			'output_tag'         => true,
			'settings_api'       => true,
			'cdn_check_time'     => '1440',
			'compiler'           => true,
			'page_permissions'   => 'manage_options',
			'save_defaults'      => true,
			'show_import_export' => true,
			'open_expanded'      => false,
			'database'           => 'options',
			'transient_time'     => '3600',
			'network_sites'      => true,
		);

		global $WPlusPlusCore;
		$args['share_icons']['tofandel_github']   = array(
			'url'   => 'https://github.com/Tofandel/',
			'title' => __( 'Check me out on GitHub', $WPlusPlusCore->text_domain ),
			'icon'  => 'el el-github'
			//'img'   => '', // You can use icon OR img. IMG needs to be a full URL.
		);
		$args['share_icons']['tofandel_linkedin'] = array(
			'url'   => 'https://www.linkedin.com/in/adrien-foulon/',
			'title' => __( 'Find me on LinkedIn', $WPlusPlusCore->text_domain ),
			'icon'  => 'el el-linkedin'
		);
		Redux::setArgs( $this->opt_name, $args );
	}


	public function setField( $field = array() ) {
		Redux::setField( $this->opt_name, $field );
	}

	public function setHelpTab( $tab = array() ) {
		Redux::setHelpTab( $this->opt_name, $tab );
	}

	public function setHelpSidebar( $content = "" ) {
		Redux::setHelpSidebar( $this->opt_name, $content );
	}

	public function setOption( $key = "", $option = "" ) {
		Redux::setOption( $this->opt_name, $key, $option );
	}

	public function setSections( $sections = array() ) {
		Redux::setSections( $this->opt_name, $sections );
	}

	public function setSection( $section = array() ) {
		Redux::setSection( $this->opt_name, $section );
	}
}