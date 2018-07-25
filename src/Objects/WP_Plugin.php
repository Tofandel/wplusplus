<?php

namespace Tofandel\Core\Objects;

use Exception;
use ReduxFrameworkPlugin;
use ReflectionClass;
use Tofandel\Core\Traits\Singleton;


/**
 * Class WP_Plugin
 *
 * @author Adrien Foulon <tofandel@tukan.hu>
 *
 */
abstract class WP_Plugin implements \Tofandel\Core\Interfaces\WP_Plugin {
	use Singleton;

	static $required_php_version = '5.5';

	protected $text_domain;
	protected $slug;
	protected $name;
	protected $file;
	protected $version = false;
	protected $class;
	protected $redux_opt_name;

	protected $is_muplugin = false;

	protected $download_url;
	protected $is_licensed = false;

	protected function isLicenseValid() {

		return true;
	}

	private function getLicenseKey() {
		return $this->getOption( 'license_key', '' );
	}

	private function getDownloadUrl() {
		if ( $this->is_licensed ) {
			return add_query_arg( array(
				'license_key' => $this->getLicenseKey(),
				'site_url'    => get_option( 'home' )
			), $this->download_url );
		}

		return $this->download_url;
	}

	public function getFile() {
		return $this->file;
	}

	public function getTextDomain() {
		return $this->text_domain;
	}

	public function getVersion() {
		return $this->version;
	}


	/**
	 * Plugin constructor.
	 *
	 * @throws Exception
	 */
	public function __construct() {

		$this->class = new ReflectionClass( $this );
		$this->slug  = $this->class->getShortName();
		$this->file  = $this->class->getFileName();

		if ( strpos( $this->file, 'mu-plugin' ) ) {
			$this->is_muplugin = true;
		}

		$comment = $this->class->getDocComment();

		$this->extractFromComment( $comment );

		$version = get_option( $this->slug . '_version' );
		if ( version_compare( $version, $this->version, '!=' ) ) {
			add_action( 'init', [ $this, 'activate' ], 1 );
		}

		if ( ! isset( $this->redux_opt_name ) ) {
			$this->redux_opt_name = strtolower( $this->class->getShortName() ) . '_options';
		}

		//We define a global with the name of the class
		if ( ! isset( $GLOBALS[ $this->class->getShortName() ] ) ) {
			$GLOBALS[ $this->class->getShortName() ] = $this;
		}

		$this->setup();
	}

	public function getReduxOptName() {
		return $this->redux_opt_name;
	}

	protected function extractFromComment( $comment ) {

		//Read the version of the plugin from the comments
		if ( $comment && preg_match( '#version[: ]*([0-9\.]+)#i', $comment, $matches ) ) {
			$this->version = trim( $matches[1] );
		} else {
			$this->version = '1.0';
		}

		//Read the name of the plugin from the comments
		if ( $comment && preg_match( '#(?:plugin|theme)[- ]?name[: ]*([^\r\n]*)#i', $comment, $matches ) ) {
			$this->name = trim( $matches[1] );
		} else {
			$this->name = $this->slug;
		}

		//Read the text domain of the plugin from the comments
		if ( $comment && preg_match( '#text[- ]?domain[: ]*([^\r\n]*)#i', $comment, $matches ) ) {
			$this->text_domain = trim( $matches[1] );
			define( strtoupper( $this->class->getShortName() ) . '_TD', $this->text_domain );
		}

		if ( $comment && preg_match( '#download[- ]?url[: ]*([^\r\n]*)#i', $comment, $matches ) ) {
			$this->download_url = trim( $matches[1] );
			//require __DIR__ . '/../../vendor/yahnis-elsts/plugin-update-checker/plugin-update-checker.php';
			\Puc_v4_Factory::buildUpdateChecker(
				$this->download_url,
				$this->file, //Full path to the main plugin file or functions.php.
				$this->slug
			);
		}
	}

	/**
	 * Setup default plugin actions
	 */
	protected function setup() {
		add_action( 'plugins_loaded', array( $this, 'loadTextdomain' ) );
		register_activation_hook( $this->file, array( $this, 'activate' ) );
		register_deactivation_hook( $this->file, array( $this, 'deactivate' ) );
		register_uninstall_hook( $this->file, get_called_class() . '::uninstallHook' );
		$this->definitions();
		$this->actionsAndFilters();

		if ( is_admin() && ! ( isset ( $_POST['action'] ) && $_POST['action'] == 'heartbeat' ) ) {
			$this->_reduxOptions();
			add_action( 'admin_init', [ $this, 'checkCompat' ] );
			add_action( 'init', array( $this, 'removeDemoModeLink' ) );
		} else {
			$GLOBALS[ $this->redux_opt_name ] = get_option( $this->redux_opt_name );
			do_action( 'redux_not_loaded' );
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueueReduxFonts' ], 999 );
		}

		if ( ! add_option( $this->slug . '_version', $this->version ) ) {
			//An old version existed before
			$last_version = get_option( $this->slug . '_version' );
			//Fresh install

			//Check the version number
			if ( version_compare( $last_version, $this->version, '<' ) ) {
				$this->multisiteUpgrade( $last_version );
			} elseif ( version_compare( $last_version, $this->version, '>' ) ) {
				$this->multisiteDowngrade( $last_version );
			}
			if ( $last_version != $this->version ) {
				update_option( $this->slug . '_version', $this->version );
			}
		}
	}


	public function removeDemoModeLink() { // Be sure to rename this function to something more unique
		if ( class_exists( 'ReduxFrameworkPlugin' ) ) {
			remove_filter( 'plugin_row_meta', array( ReduxFrameworkPlugin::get_instance(), 'plugin_metalinks' ), null );
		}
		if ( class_exists( 'ReduxFrameworkPlugin' ) ) {
			remove_action( 'admin_notices', array( ReduxFrameworkPlugin::get_instance(), 'admin_notices' ) );
		}
	}


	/**
	 * Add the tables and settings and any plugin variable specifics here
	 *
	 * @return void
	 */
	abstract public function definitions();

	/**
	 * Add actions and filters here
	 */
	abstract public function actionsAndFilters();

	/**
	 * @throws \ReflectionException
	 */
	public static function uninstallHook() {
		$ref = new ReflectionClass( static::class );
		delete_option( $ref->getShortName() . '_version' );
		/**
		 * @var self $plugin
		 */
		$plugin = static::__init__();
		$plugin->uninstall();
		delete_option( $plugin->redux_opt_name );
	}

	/**
	 * Called function if a plugin is uninstalled
	 * @throws \ReflectionException
	 */
	abstract protected function uninstall();

	/**
	 * Magic method that returns the plugin text domain if trying to convert the plugin object to a string
	 * @return string
	 */
	public function __toString() {
		return $this->getTextDomain();
	}


	public function pluginName() {
		return esc_html__( str_replace( array( '-', '_' ), ' ', (string) $this ) );
	}

	public static function deleteTransients() {
		global $wpdb;

		$wpdb->query( $wpdb->prepare(
			"DELETE FROM {$wpdb->options}
		WHERE option_name LIKE %s OR option_name LIKE %s",
			$wpdb->esc_like( '_transient_wpp_file_' ) . '%',
			$wpdb->esc_like( '_transient_timeout_wpp_file_' ) . '%'
		) );
	}

	public function getOption( $option = null, $default = false ) {
		return self::getReduxOption( $this->redux_opt_name, $option, $default );
	}

	/**
	 * @param string $opt_name
	 * @param string|array|null $option
	 * @param mixed $default
	 *
	 * @return array|string
	 */
	public static function getReduxOption( $opt_name, $option = null, $default = false ) {
		static $options = array();

		if ( ! isset( $options[ $opt_name ] ) ) {
			if ( isset( $GLOBALS[ $opt_name ] ) ) {
				$options[ $opt_name ] = $GLOBALS[ $opt_name ];
			}
		}

		if ( ! isset( $options[ $opt_name ] ) ) {
			$options[ $opt_name ] = get_option( $opt_name, array() );
			if ( ! isset( $GLOBALS[ $opt_name ] ) ) {
				$GLOBALS[ $opt_name ] = $options[ $opt_name ];
			}
		}

		if ( is_array( $option ) ) {
			$option = array_reverse( $option );
			$v      = $options[ $opt_name ];
			while ( $k = array_pop( $option ) ) {
				if ( isset( $v[ $k ] ) ) {
					$v = $v[ $k ];
				} else {
					return $default;
				}
			}

			return $v;
		} elseif ( is_string( $option ) ) {
			return $options[ $opt_name ][ $opt_name ];
		} else {
			return $options[ $opt_name ];
		}
	}

	/**
	 * Searchs if a file exists in the plugin folder (minified or not)
	 *
	 * @param string $name
	 * @param string $type
	 * @param bool $cache
	 * @param string|false $folder
	 *
	 * @return string
	 */
	public function searchFile( $name, $type = '', $cache = false, $folder = false ) {
		global $plugin_page;


		if ( strpos( $name, '//' ) === 0 || strpos( $name, 'http' ) === 0 ) {
			return $name;
		}

		$name = self::removeExtension( self::removeExtension( $name, $type ), 'min' );

		if ( ! WP_DEBUG && $cache && $f = get_transient( 'wpp_file_' . $type . '_' . $name ) ) {
			return $f;
		}

		$folder = trailingslashit( $this->folder( $folder ) );

		if ( WP_DEBUG ) {
			$files = array(
				$folder . $name . '.' . $type,
				$folder . trailingslashit( $plugin_page ) . $name . '.' . $type,
				$folder . $name . '.min.' . $type,
				$folder . trailingslashit( $plugin_page ) . $name . '.min.' . $type,
				$folder . $name
			);
		} else {
			$files = array(
				$folder . $name . '.min.' . $type,
				$folder . trailingslashit( $plugin_page ) . $name . '.min.' . $type,
				$folder . $name . '.' . $type,
				$folder . trailingslashit( $plugin_page ) . $name . '.' . $type,
				$folder . $name
			);
		}

		foreach ( $files as $file ) {
			if ( is_file( $file ) ) {
				$file = str_replace( ABSPATH, '/', $file );
				if ( $cache ) {
					//1 month file path cache to minimize I/O
					set_transient( 'wpp_file_' . $type . '_' . $name, $file, 2592000 );
				}

				return $file;
			}
		}

		return false;
	}

	/**
	 * Removes the extension from a filename
	 *
	 * @param string $string
	 * @param string $ext
	 *
	 * @return string
	 */
	public static function removeExtension( $string, $ext ) {
		$ext     = '.' . $ext;
		$ext_len = strlen( $ext );
		if ( strrpos( $string, $ext, $ext_len ) === 0 ) {
			$string = substr( $string, 0, - $ext_len );
		}

		return $string;
	}

	/**
	 * @param string $folder
	 *
	 * @return string Path to the plugin's folder
	 */
	public function folder( $folder = '' ) {
		if ( strpos( $folder, ABSPATH ) === 0 ) {
			return trailingslashit( $folder );
		}

		return trailingslashit( trailingslashit( dirname( $this->file ) ) . ltrim( "$folder", '/' ) );
	}

	/**
	 * @param string $file
	 *
	 * @return string Path to the plugin's file
	 */
	public function file( $file = '' ) {
		if ( strpos( $file, ABSPATH ) === 0 ) {
			return $file;
		}

		return trailingslashit( dirname( $this->file ) ) . ltrim( "$file", '/' );
	}

	/**
	 * @param string $js Filename (optional extension)
	 * @param array $require
	 * @param bool $localize
	 * @param bool $in_footer
	 *
	 * @return string
	 */
	public function addScript( $js, $require = array(), $localize = false, $in_footer = false ) {

		$name = basename( $js );
		$file = $this->registerScript( $js, $require, $localize, $in_footer );

		if ( $file && wp_script_is( $name, 'registered' ) ) {
			wp_enqueue_script( $name );
		} else {
			$file = false;
		}

		return isset( $file ) ? $file : $name;
	}

	public function registerScript( $js, $require = array(), $localize = false, $in_footer = false ) {
		$name = basename( $js );
		if ( ! wp_script_is( $name, 'registered' ) ) {
			if ( $file = $this->searchFile( $js, 'js', true, 'js' ) ) {
				wp_register_script( $name, $file, $require, $this->version, $in_footer );
			}
		}
		if ( wp_script_is( $name, 'registered' ) ) {
			if ( ! empty( $localize ) ) {
				wp_localize_script( $name, str_replace( array(
					'-',
					'.'
				), '_', $name ), array_merge( array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ), $localize ) );
			}
		} else {
			$file = false;
		}

		return isset( $file ) ? $file : $name;
	}

	/**
	 * @param string $css Filename (extension is optional)
	 * @param string $media
	 *
	 * @return string
	 */
	public function addStyle( $css, $media = 'all' ) {
		$name = basename( $css );
		$file = $this->registerStyle( $css, $media );

		if ( $file && wp_style_is( $name, 'registered' ) ) {
			wp_enqueue_style( $name );
		} else {
			$file = false;
		}

		return isset( $file ) ? $file : $name;
	}

	/**
	 * @param string $css Filename (extension is optional)
	 * @param string $media
	 *
	 * @return string
	 */
	public function registerStyle( $css, $media = 'all' ) {
		$name = basename( $css );
		if ( ! wp_style_is( $name, 'registered' ) ) {
			if ( $file = $this->searchFile( $css, 'css', true, 'css' ) ) {
				wp_register_style( $name, $file, array(), $this->version, $media );
			}
		}

		if ( ! wp_style_is( $name, 'registered' ) ) {
			$file = false;
		}

		return isset( $file ) ? $file : $name;
	}

	public function webPath( $folder = '' ) {
		return plugin_dir_url( $this->file ) . "$folder";
	}

	/**
	 * Prepare plugin internationalisation
	 */
	public function loadTextdomain() {
		call_user_func( 'load_' . ( $this->is_muplugin ? 'mu' : '' ) . 'plugin_textdomain', $this->text_domain, false, dirname( plugin_basename( $this->file ) ) . '/languages/' );
	}

	public function checkCompat() {
		if ( ! self::checkCompatibility() ) {
			if ( is_plugin_active( $this->file ) ) {
				deactivate_plugins( $this->file );
				add_action( 'admin_notices', array( $this, 'disabled_notice' ) );
				if ( isset( $_GET['activate'] ) ) {
					unset( $_GET['activate'] );
				}
			}
		}

	}

	/**
	 * Check that the required version of php is installed before activating the plugin
	 *
	 * @return bool
	 */
	public static function checkCompatibility() {
		if ( version_compare( phpversion(), static::$required_php_version, '<' ) ) {
			return false;
		}

		return true;
	}

	public function disabled_notice() {
		echo '<strong>' . sprintf( esc_html__( '%1$s requires PHP %2$s or higher! (Current version is %3$s)', $this->text_domain ), $this->name, static::$required_php_version, PHP_VERSION ) . '</strong>';
	}

	/**
	 * Called function on plugin activation
	 */
	public function activate() {
		if ( ! self::checkCompatibility() ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die( sprintf( __( '%1$s requires PHP %2$s or higher! (Current version is %3$s)', $this->text_domain ), $this->name, static::$required_php_version, PHP_VERSION ) );
		}

		//Setup default plugin folders
		//$this->mkdir( 'languages' );
		//$this->mkdir( 'css' );
		//$this->mkdir( 'js' );
		self::deleteTransients();
		add_action( 'init', 'flush_rewrite_rules' );
	}

	private function multisiteUpgrade( $last_version ) {
		if ( ! is_multisite() ) {
			$this->upgrade( $last_version );
		} else {
			$sites = get_sites();
			foreach ( $sites as $site ) {
				switch_to_blog( $site->blog_id );
				$this->upgrade( $last_version );
				restore_current_blog();
			}
		}
	}

	/**
	 * Called function after a plugin update
	 * Can be used if options needs to be added or if previous database entries need to be modified
	 */
	abstract protected function upgrade( $last_version );

	protected function multisiteDowngrade( $last_version ) {
		if ( ! is_multisite() ) {
			$this->downgrade( $last_version );
		} else {
			$sites = get_sites();
			foreach ( $sites as $site ) {
				switch_to_blog( $site->blog_id );
				$this->downgrade( $last_version );
				restore_current_blog();
			}
		}
	}

	/**
	 * Called function if a plugin is downgraded (incompatibility or something)
	 * Rarely supported/used but still including it just in case
	 *
	 * @param $last_version
	 */
	protected function downgrade( $last_version ) {
	}

	static $reduxInstance;

	public static function getAReduxInstance() {
		if ( empty( static::$reduxInstance ) ) {
			ReduxConfig::loadRedux();
			if ( ! class_exists( \ReduxFrameworkInstances::class, true ) ) {
				return false;
			}
			$reduxInstances = \ReduxFrameworkInstances::get_all_instances();
			if ( empty( $reduxInstances ) ) {
				return false;
			}
			static::$reduxInstance = array_pop( $reduxInstances );
		}

		return true;
	}

	/**
	 * @param $folder
	 *
	 * @return bool
	 */
	public function mkdir( $folder ) {
		try {
			return WP_Filesystem::__init__()->mkdir( $this->folder( $folder ) );
		} catch ( \ReflectionException $e ) {
			return false;
		}
	}

	/**
	 * @param $file
	 * @param $dest
	 *
	 * @param bool $overwrite
	 *
	 * @return bool
	 */
	public function copy( $file, $dest, $overwrite = true ) {
		try {
			return WP_Filesystem::__init__()->copy( $this->file( $file ), $this->file( $dest ), $overwrite );
		} catch ( \ReflectionException $e ) {
			return false;
		}
	}

	/**
	 * @param $file
	 * @param $content
	 *
	 * @return bool
	 */
	public function put_contents( $file, $content ) {
		try {
			return WP_Filesystem::__init__()->putContents( $this->file( $file ), $content );
		} catch ( \ReflectionException $e ) {
			return false;
		}
	}

	/**
	 * @param $file
	 *
	 * @return bool|mixed|string
	 */
	public function get_contents( $file ) {
		try {
			return WP_Filesystem::__init__()->getContents( $this->file( $file ) );
		} catch ( \ReflectionException $e ) {
			return false;
		}
	}

	/**
	 * @param $file
	 *
	 * @return bool
	 */
	public function delete_file( $file ) {
		try {
			return WP_Filesystem::__init__()->deleteFile( $this->file( $file ) );
		} catch ( \ReflectionException $e ) {
			return false;
		}
	}

	/**
	 * @param $folder
	 * @param bool $recursive
	 *
	 * @return bool
	 */
	public function delete_dir( $folder, $recursive = true ) {
		try {
			return WP_Filesystem::__init__()->deleteDir( $this->folder( $folder ), $recursive );
		} catch ( \ReflectionException $e ) {
			return false;
		}
	}

	/**
	 * Add redux framework menus, sub-menus and settings page in this function
	 */
	abstract public function reduxOptions();

	public function _reduxOptions() {
		$this->reduxOptions();
	}

	/**
	 * Called function on plugin deactivation
	 * Options and plugin data should only be removed in the uninstall function
	 */
	public function deactivate() {
		flush_rewrite_rules();
	}

	/**
	 * Helper function to check if the new plugin version is a minor update
	 *
	 * @param $last_version
	 *
	 * @return bool
	 */
	protected function isMinorUpdate( $last_version ) {
		$new_versions  = explode( '.', $this->version, 2 );
		$last_versions = explode( '.', $last_version, 2 );

		if ( $new_versions[0] == $last_versions[0] && $new_versions[1] != $last_versions[1] ) {
			return true;
		}

		return false;
	}

	/**
	 * Helper function to check if the new plugin version is a major update
	 *
	 * @param $last_version
	 *
	 * @return bool
	 */
	protected function isMajorUpdate( $last_version ) {
		$new_versions  = explode( '.', $this->version, 2 );
		$last_versions = explode( '.', $last_version, 2 );

		if ( $new_versions[0] != $last_versions[0] ) {
			return true;
		}

		return false;
	}

	/** To enqueue redux fonts since we disable it on front for performance */

	/**
	 * makeGoogleWebfontLink Function.
	 * Creates the google fonts link.
	 *
	 * @since ReduxFramework 3.0.0
	 */
	private function makeGoogleWebfontLink( $fonts ) {
		$link    = "";
		$subsets = array();

		foreach ( $fonts as $family => $font ) {
			if ( ! empty( $link ) ) {
				$link .= "%7C"; // Append a new font to the string
			}
			$link .= $family;

			if ( ! empty( $font['font-style'] ) || ! empty( $font['all-styles'] ) ) {
				$link .= ':';
				if ( ! empty( $font['all-styles'] ) ) {
					$link .= implode( ',', $font['all-styles'] );
				} else if ( ! empty( $font['font-style'] ) ) {
					$link .= implode( ',', $font['font-style'] );
				}
			}

			if ( ! empty( $font['subset'] ) ) {
				foreach ( $font['subset'] as $subset ) {
					if ( ! in_array( $subset, $subsets ) ) {
						array_push( $subsets, $subset );
					}
				}
			}
		}

		if ( ! empty( $subsets ) ) {
			$link .= "&subset=" . implode( ',', $subsets );
		}


		return '//fonts.googleapis.com/css?family=' . str_replace( '|', '%7C', $link );
	}

	/**
	 * makeGoogleWebfontString Function.
	 * Creates the google fonts link.
	 *
	 * @since ReduxFramework 3.1.8
	 */
	private function makeGoogleWebfontString( $fonts ) {
		$link    = "";
		$subsets = array();

		foreach ( $fonts as $family => $font ) {
			if ( ! empty( $link ) ) {
				$link .= "', '"; // Append a new font to the string
			}
			$link .= $family;

			if ( ! empty( $font['font-style'] ) || ! empty( $font['all-styles'] ) ) {
				$link .= ':';
				if ( ! empty( $font['all-styles'] ) ) {
					$link .= implode( ',', $font['all-styles'] );
				} else if ( ! empty( $font['font-style'] ) ) {
					$link .= implode( ',', $font['font-style'] );
				}
			}

			if ( ! empty( $font['subset'] ) ) {
				foreach ( $font['subset'] as $subset ) {
					if ( ! in_array( $subset, $subsets ) ) {
						array_push( $subsets, $subset );
					}
				}
			}
		}

		if ( ! empty( $subsets ) ) {
			$link .= "&subset=" . implode( ',', $subsets );
		}

		return "'" . $link . "'";
	}

	const async_typography = false;
	const disable_google_fonts_link = false;

	private function getFonts() {
		$fonts     = array();
		$opt_fonts = array_filter( $this->getOption(), function ( $key ) {
			return strpos( $key, 'font-' ) === 0;
		}, ARRAY_FILTER_USE_KEY );
		foreach ( $opt_fonts as $key => $value ) {
			if ( (bool) $value['google'] == true ) {
				if ( ! isset( $fonts[ $value['font-family'] ] ) ) {
					$fonts[ $value['font-family'] ] = array(
						'font-style' => array( $value['font-style'] ),
						'subset'     => array( $value['subsets'] )
					);
				} else {
					$fonts[ $value['font-family'] ]['font-style'][] = $value['font-style'];
					$fonts[ $value['font-family'] ]['subset'][]     = $value['subsets'];
				}
			}
		}
	}

	public function enqueueReduxFonts() {
		$fonts = $this->getFonts();
		if ( empty( $fonts ) ) {
			return;
		}
		if ( static::async_typography ) {
			$families = array();
			foreach ( $fonts as $key => $value ) {
				$families[] = $key;
			}
			?>
			<script>
				/* You can add more configuration options to webfontloader by previously defining the WebFontConfig with your options */
				if (typeof WebFontConfig === "undefined") {
					WebFontConfig = {};
				}
				WebFontConfig['google'] = {families: [<?php echo $this->makeGoogleWebfontString( $fonts ) ?>]};

				(function () {
					var wf = document.createElement('script');
					wf.src = 'https://ajax.googleapis.com/ajax/libs/webfont/1.5.3/webfont.js';
					wf.type = 'text/javascript';
					wf.async = 'true';
					var s = document.getElementsByTagName('script')[0];
					s.parentNode.insertBefore(wf, s);
				})();
			</script>
			<?php
		} elseif ( ! static::disable_google_fonts_link ) {
			wp_enqueue_style( 'redux-google-fonts-' . $this->redux_opt_name, $this->makeGoogleWebfontLink( $fonts ), '', $this->version );
		}
	}
}