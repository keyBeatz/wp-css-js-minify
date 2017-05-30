<?php

namespace CJM;

use CJM\Exceptions\CjmException;

defined( 'ABSPATH' ) || exit();

class FileRegistrar extends FileBase
{

	/**
	 * FileRegistrar constructor.
	 */
	function __construct() {
		parent::__construct();

		if( !empty( $this->getMinifiedCss() ) || !empty( $this->getMinifiedJs() ) ) {
			try {
				$this->handle_files();

				add_filter( 'script_loader_tag', array( $this, 'add_async_scripts' ), 10, 3 );
				add_filter( 'style_loader_tag', array( $this, 'add_async_styles' ), 10, 3 );

			} catch( CjmException $e ) {
				echo 'Caught exception: ',  $e->getMessage(), "\n";
			}
		}
   }

	public function handle_files() {

		// deregister former files
		if( $this->isCssOn() === true )
			add_filter( 'print_styles_array', array( $this, 'deregister_css' ), 999 );
		if( $this->isJsOn() === true )
			add_filter( 'print_scripts_array', array( $this, 'deregister_js' ), 999 );

		// browse file_object and process files by type
		if( !empty( $this->getMinifiedCss() ) && is_array( $this->getMinifiedCss() ) ) {
			foreach( $this->getMinifiedCss() as $key => $chunk ) {
				if( $chunk['type'] == 'css' && $this->isCssOn() === true ) {
					$this->handle_css( $chunk );
				}
			}
		}
		// browse file_object and process files by type
		if( !empty( $this->getMinifiedJs() ) && is_array( $this->getMinifiedJs() ) ) {
			foreach( $this->getMinifiedJs() as $key => $chunk ) {
				if( $chunk['type'] == 'js' && $this->isJsOn() === true ) {
					$this->handle_js( $chunk );
				}
			}
		}
	}


	/**
	 * @param $tag
	 * @param $handle
	 * @param $src
	 *
	 * @return mixed
	 *
	 * Adding defer|async tag
	 */
	public function add_async_scripts( $tag, $handle, $src ) {
		if( !empty( $this->getMinifiedJs() ) && is_array( $this->getMinifiedJs() ) ) {
			foreach( $this->getMinifiedJs() as $file ) {
				if( ( $file['async'] == 'async' || $file['async'] == 'defer' ) && $file['name'] == $handle )
					$tag = str_replace( '<script', '<script '. esc_attr( $file['async'] ), $tag );
			}
		}

		return $tag;
	}

	/**
	 * @param $tag
	 * @param $handle
	 * @param $src
	 *
	 * @return string
	 *
	 * Adding wrapper and abality to load css asynchronously - by JS (handles noscript)
	 */
	public function add_async_styles( $tag, $handle, $src ) {
		if( !empty( $this->getMinifiedCss() ) && is_array( $this->getMinifiedCss() ) ) {
			foreach( $this->getMinifiedCss() as $file ) {
				if( ( $file['async'] == 'async' || $file['async'] == 'defer' ) && $file['name'] == $handle )
					$tag = "<noscript class='cjm_css_async'>{esc_html( $tag )}</noscript>";
			}
		}

		return $tag;
	}

	/**
	 * @param $file
	 *
	 * @return bool
	 */
	public function handle_css( $file ) {
		if( empty( $file ) || !is_array( $file ) )
			return false;

		// set parameters
		$handle 	= isset( $file['handle'] ) ? $file['handle'] : $file['name'];
		$src		= isset( $file['src'] ) ? $file['src'] : "";
		$deps		= isset( $file['deps'] ) ? $file['deps'] : array();
		$ver		= isset( $file['ver'] ) ? $file['ver'] : false;
		$media	    = isset( $file['media'] ) ? $file['media'] : "all";

		// custom params
		$priority = !empty( $file['priority'] ) && is_numeric( $file['priority'] ) && $file['priority'] > 0 ? $file['priority'] : $this->getDefaultPriority();

		// regster minified file
		wp_register_style( $handle, $src, array(), $ver, $media );

		if( !empty( $handle ) && $src && file_exists( $file['path'] ) ) {
			add_action(
				'wp_enqueue_scripts',
				function() use( $handle ) { wp_enqueue_style( $handle ); },
				(int) $priority
			);
		}

	}

	/**
	 * @param $file
	 *
	 * @return bool
	 */
	public function handle_js( $file ) {
		if( empty( $file ) || !is_array( $file ) )
			return false;

		// set parameters
		$handle 	= isset( $file['handle'] ) ? $file['handle'] : $file['name'];
		$src		= isset( $file['src'] ) ? $file['src'] : "";
		$deps		= isset( $file['deps'] ) ? $file['deps'] : array();
		$ver		= isset( $file['ver'] ) ? $file['ver'] : false;
		$in_footer	= isset( $file['in_footer'] ) ? $file['in_footer'] : false;

		// custom params
		$priority   = !empty( $file['priority'] ) && is_numeric( $file['priority'] ) && $file['priority'] > 0 ? $file['priority'] : $this->getDefaultPriority();

		// regster minified file
		wp_register_script( $handle, $src, $deps, $ver, $in_footer );

		if( !empty( $handle ) && $src && file_exists( $file['path'] ) ) {
			add_action(
				'wp_enqueue_scripts',
				function() use( $handle ) { wp_enqueue_script( $handle ); },
				(int) $priority
			);
		}

	}

	/**
	 * @param $to_do
	 *
	 * @return array|bool
	 */
	public function deregister_css( $to_do ) {
		if( empty( $to_do ) || !is_array( $to_do ) )
		 	return false;

		if( !empty( $this->getMinifiedCss() ) ) {
			foreach( $this->getMinifiedCss() as $key => $chunk ) {
				if( $chunk['type'] == 'css' && !empty( $chunk['files'] ) ) {
					$to_do = array_diff( $to_do, $chunk['files'] );
				}
			}
		}

	   return $to_do;
	}

	/**
	 * @param $to_do
	 *
	 * @return array|bool
	 */
	public function deregister_js( $to_do ) {
		if( empty( $to_do ) || !is_array( $to_do ) )
		 	return false;

		if( !empty( $this->getMinifiedJs() ) ) {
			foreach( $this->getMinifiedJs() as $key => $chunk ) {
				if( $chunk['type'] == 'js' && !empty( $chunk['files'] ) ) {
					$to_do = array_diff( $to_do, $chunk['files'] );
				}
			}
		}

	   return $to_do;
	}
}
