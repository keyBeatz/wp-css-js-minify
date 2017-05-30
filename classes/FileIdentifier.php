<?php

namespace CJM;

defined( 'ABSPATH' ) || exit();

class FileIdentifier extends FileBase
{

	private $enabled;
	private $css_to_do_collector;
	private $js_to_do_collector;

	function __construct() {
		parent::__construct();

		$this->enabled = true;
		if( $this->enabled )
			$this->log_files();
   }

	public function log_files() {

		if( $this->isCssOn() !== true )
			add_filter( 'print_styles_array', array( $this, 'log_css' ), 998 );
		if( $this->isJsOn() !== true )
			add_filter( 'print_scripts_array', array( $this, 'log_js' ), 998 );

	}

	/**
	 * @param $to_do
	 *
	 * @return bool
	 */
	public function log_css( $to_do ) {
	   global $wp_styles;

		if( empty( $to_do ) )
			return false;

		$files = array();

		// process queue files (to do) and add their info to $file var
		foreach( $to_do as $handle )
			if( isset( $wp_styles->registered[$handle] ) ) $files[$handle] = $wp_styles->registered[$handle];

		// process queue files (to do) and add their info to $file var
		foreach( $to_do as $handle ) {
			if( isset( $wp_styles->registered[$handle] ) ) {
				// přidej skript do collectoru
				$this->css_to_do_collector[$handle] = $wp_styles->registered[$handle];
			}

		}

		// if any files were found add them to collector for later use
		update_option( 'cjm_css_log', $this->css_to_do_collector, false );

		return $to_do;
	}

	/**
	 * @param $to_do
	 *
	 * @return bool
	 */
	public function log_js( $to_do ) {
	   global $wp_scripts;


		if( empty( $to_do ) )
			return false;


		// process queue files (to do) and add their info to $file var
		foreach( $to_do as $handle ) {
			if( isset( $wp_scripts->registered[$handle] ) ) {
				// přidej skript do collectoru
				$this->js_to_do_collector[$handle] = $wp_scripts->registered[$handle];
				// pokud má být skript načítán v patičce, přidej příznak
				if( !empty( $wp_scripts->in_footer ) && in_array( $handle, $wp_scripts->in_footer ) )
					$this->js_to_do_collector[$handle]->in_footer = true;
			}

		}

		// if any files were found add them to collector for later use
		update_option( 'cjm_js_log', $this->js_to_do_collector, false );

		return $to_do;
	}
}

