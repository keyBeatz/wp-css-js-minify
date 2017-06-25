<?php
/*
Plugin Name: CSS & JS Minify
Description: Friendly way to define custom blocks of style and javascript files with defer/async loading support.
Text Domain: css-js-minify
Domain Path: /languages
Version: 0.9.3
License: MIT
Author: keyBeatz
Author URI: https://github.com/keyBeatz
*/

namespace CJM;

defined( 'ABSPATH' ) || exit();

require_once 'classes/autoload.php';

$pluginName = plugin_basename( __FILE__ );

class Plugin extends Settings
{

	function __construct() {
		// start the plugin with defined settings
		$this->defineSettings();
		$this->bootstrap();
	}

	/**
	 * Initialize plugin
	 */
	public static function init() {
		$class = __CLASS__;
		new $class;
	}

	private function defineSettings() {

		/** Folder paths & urls */

		$uploadDirInfo = wp_upload_dir();
		static::addSetting( "cacheDir", $uploadDirInfo['basedir'] . "/css-js-minify/" );
		static::addSetting( "cacheUrl", $uploadDirInfo['baseurl'] . '/css-js-minify/' );
		static::addSetting( "pluginDir", plugin_dir_path( __FILE__ ) );
		static::addSetting( "pluginUrl", plugins_url( "css-js-minify" ) );
		static::addSetting( "libsDir", static::getSetting( "pluginDir" ) . "libs/" );

		/** Minify settings */

		/**
		 *	Turn off/on CSS & JS optimization
		 *	@var	bool
		 */
		static::addSetting( "isCssOn", (bool) get_option( 'cjm_is_css_on' ) );
		static::addSetting( "isJsOn", (bool) get_option( 'cjm_is_js_on' ) );

		/**
		 *  If priority of file block was not set this is default
		 *  @var int
		 */
		static::addSetting( "defaultPriority", 10 );

	}

	public static function loadFileRegistrar() {
		new FileRegistrar();
	}

	private function identifyFiles() {
		new FileIdentifier();
	}

	private function bootstrap() {
		// functions files
		require_once( static::getSetting( "pluginDir" ) . "functions/filters.php" );
		require_once( static::getSetting( "pluginDir" ) . "functions/helpers.php" );

		// initialize admin interface
		if( is_admin() ) $this->loadAdmin();
		// initialize file identifier (works only on frontend)
		if( !is_admin() && !cjm_is_login() ) $this->identifyFiles();
		// initialize file registrar (handler which deregisters former and registers minified files)
		if( ( static::getSetting( "isCssOn" ) || static::getSetting( "isJsOn" ) ) && ( !is_admin() && !cjm_is_login() ) )
			add_action( 'template_redirect', array( "CJM\Plugin", 'loadFileRegistrar' ) );
	}

	private function loadAdmin() {
		// admin ajax
		require_once( static::getSetting( "pluginDir" ) . "functions/ajax/AjaxHandler.php" );
		// init admin page
		new \CJM\Admin\Minify;
	}

	public static function loadTextdomain() {
		load_plugin_textdomain( 'css-js-minify', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
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

	public static function addPluginPageLinks( $links ) {
		$settings_link = '<a href="options-general.php?page=css-js-minify-settings">' . esc_html__( 'Settings' ) . '</a>';
		array_push( $links, $settings_link );
		return $links;
	}
}
add_action( 'plugins_loaded', array( __NAMESPACE__ . '\Plugin', 'init' ) );
add_action( 'plugins_loaded', array( __NAMESPACE__ . '\Plugin', 'loadTextdomain' ) );
add_filter( "plugin_action_links_$pluginName", array( __NAMESPACE__ . '\Plugin', 'addPluginPageLinks' ) );
register_activation_hook( __FILE__, array( __NAMESPACE__ . '\Plugin', 'install' ) );
