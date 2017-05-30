<?php

namespace CJM;

defined( 'ABSPATH' ) || exit();

abstract class FileBase
{
	private $minifiedCss;
	private $minifiedJs;
	private $cssLog;
	private $jsLog;
	private $cssOn;
	private $jsOn;
	private $defaultPriority;
	private $cacheDir;
	private $cacheUrl;
	private $pluginDir;
	private $pluginUrl;
	private $libsDir;

	function __construct() {
		$this->setAllSettings();
	}

	private function setAllSettings() {
		$this->minifiedCss  = get_option( 'cjm_minified_files_css' ) ?: array();
		$this->minifiedJs   = get_option( 'cjm_minified_files_js' ) ?: array();
		$this->cssLog       = get_option( 'cjm_css_log' ) ?: array();
		$this->jsLog        = get_option( 'cjm_js_log' ) ?: array();

		$this->cssOn        = Plugin::getPublic( "isCssOn" );
		$this->jsOn         = Plugin::getPublic( "isJsOn" );
		$this->defaultPriority = Plugin::getPublic( "defaultPriority" );
		$this->cacheDir     = Plugin::getPublic( "cacheDir" );
		$this->cacheUrl     = Plugin::getPublic( "cacheUrl" );
		$this->pluginDir    = Plugin::getPublic( "pluginDir" );
		$this->pluginUrl    = Plugin::getPublic( "pluginUrl" );
		$this->libsDir      = Plugin::getPublic( "libsDir" );
	}

	/**
	 * @return array
	 */
	public function getMinifiedCss() {
		return $this->minifiedCss;
	}

	/**
	 * @return array
	 */
	public function getMinifiedJs() {
		return $this->minifiedJs;
	}

	/**
	 * @return bool
	 */
	public function isCssOn() {
		return $this->cssOn;
	}

	/**
	 * @return bool
	 */
	public function isJsOn() {
		return $this->jsOn;
	}

	/**
	 * @return int
	 */
	public function getDefaultPriority() {
		return $this->defaultPriority;
	}

	/**
	 * @return string
	 */
	public function getCacheDir() {
		return $this->cacheDir;
	}

	/**
	 * @return string
	 */
	public function getCacheUrl() {
		return $this->cacheUrl;
	}

	/**
	 * @return string
	 */
	public function getPluginDir() {
		return $this->pluginDir;
	}

	/**
	 * @return string
	 */
	public function getLibsDir() {
		return $this->libsDir;
	}

	/**
	 * @return string
	 */
	public function getPluginUrl() {
		return $this->pluginUrl;
	}

	/**
	 * @return array
	 */
	public function getCssLog() {
		return $this->cssLog;
	}

	/**
	 * @return array
	 */
	public function getJsLog() {
		return $this->jsLog;
	}
}