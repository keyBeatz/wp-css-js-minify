<?php
/*
Plugin Name: CSS & JS Minify
Description: Friendly way to define custom blocks of style and javascript files with defer/async CSS & JS loading support.
Version: 0.9
Author: keyBeatz
*/

namespace CJM;

defined( 'ABSPATH' ) || exit();


/**
 * Autoload class files
 */
function autoload( $cls ) {
	$cls = ltrim( $cls, '\\' );
	if( strpos( $cls, __NAMESPACE__ ) !== 0 )
		return;

	$cls = str_replace( __NAMESPACE__, '', $cls );

	if( empty( $cls ) )
		return;

	$path = ABSPATH . "wp-content/plugins/css-js-minify/classes" . str_replace('\\', DIRECTORY_SEPARATOR, $cls) . '.php';
	require_once( $path );
}
spl_autoload_register(__NAMESPACE__ . '\\autoload');


class Plugin extends Settings
{

	private $plugin_dir;

	/**
	 * Initialize plugin
	 */
	public static function init() {
	  $class = __CLASS__;
	  new $class;
	}

	function __construct() {
		$this->defineSettings();

		// set vars
		$this->plugin_dir = plugin_dir_path( __FILE__ );

		// bootstrap plugin files
		$this->bootstrap();
		// inicializuj administrační rozhraní
		if( is_admin() ) $this->loadAdmin();
		// inicializuj identifikátor souborů (funguje pouze na frontendu)
		if( !is_admin() && !cjm_is_login() ) $this->identifyFiles();

		if( ( static::getPublic( "isCssOn" ) || static::getPublic( "isJsOn" ) ) && ( !is_admin() && !cjm_is_login() ) )
			add_action( 'template_redirect', array( "CJM\Plugin", 'loadFileRegistrar' ) );
	}

	private function defineSettings() {

		/** Folder paths & urls */

		static::setPublic( "cacheDir", ABSPATH . "wp-content/uploads/css-js-minify/" );
		static::setPublic( "cacheUrl", site_url( 'wp-content/uploads/css-js-minify/' ) );
		static::setPublic( "pluginDir", plugin_dir_path( __FILE__ ) );
		static::setPublic( "pluginUrl", plugins_url( 'css-js-minify' ) );
		static::setPublic( "libsDir", static::getPublic( "pluginDir" ) . "libs/" );

		/** Minify settings */

		/**
		 *	Turn off/on CSS & JS optimization
		 *	@var	bool
		 */
		static::setPublic( "isCssOn", (bool) get_option( 'cjm_is_css_on' ) );
		static::setPublic( "isJsOn", (bool) get_option( 'cjm_is_js_on' ) );

		/**
		 *  If priority of file block was not set this is default
		 *  @var int
		 */
		static::setPublic( "defaultPriority", 10 );

	}

	public static function loadFileRegistrar() {
		new FileRegistrar();
	}

	public function identifyFiles() {
		new FileIdentifier();
	}

	public function loadAdmin() {
		// admin ajax
		require_once( $this->plugin_dir . "functions/ajax/AjaxHandler.php" );
		// init admin page
		new \CJM\Admin\Minify;
	}

	private function bootstrap() {
		// functions files
		require_once( $this->plugin_dir . "functions/filters.php" );
		require_once( $this->plugin_dir . "functions/helpers.php" );
	}

	public static function install() {
		$uploadFolder = ABSPATH . "wp-content/uploads/";
		$pluginFolder = $uploadFolder . "css-js-minify/";

		// check if uploads dir exists
		if( !is_dir( $uploadFolder ) )
			trigger_error( "The /wp-content/uploads folder does not exists. Please create it manually with 777 chmod.", E_USER_ERROR );

		// check if plugin stash folder exists
		if( !is_dir( $pluginFolder ) ) {
			if( mkdir( $pluginFolder, 0777 ) ) {
				/* success */
			}
			else
				trigger_error( "The {$pluginFolder} folder could not be created, please create it manually with 777 chmod.", E_USER_ERROR );
		}
	}
}
add_action( 'plugins_loaded', array( __NAMESPACE__ . '\Plugin', 'init' ) );

register_activation_hook( __FILE__, array( 'CJM\Plugin', 'install' ) );

