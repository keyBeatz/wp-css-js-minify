<?php

namespace CJM\Admin;

defined( 'ABSPATH' ) || exit();

class AjaxHandler
{
	/**
	 *	@constant array
	 */
	const TASKS = array(
		"generate_minified_files",
		"main_toggle"
	);


	/**
	 * @param $data
	 * Admin handler: Generating minified files
	 */
	public static function generate_minified_files( $data ) {
		if( empty( $data ) )
			exit( 'empty-data' );

		// if no files was send delete all within type
		if( empty( $data['files'] ) ) {
			if( $data['mode'] == 'css' )
				update_option( 'cjm_minified_files_css', array() );
			else if( $data['mode'] == 'js' )
				update_option( 'cjm_minified_files_js', array() );

			exit( 'data-erased' );
		}

		// send files to Minify
		$minify = new \CJM\Minify( $data['files'] );

		if( $minify->finished == true )
			exit( 'saved' );

		exit( 'error' );
	}


	/**
	 * @param $data
	 * Ajax handler: Toggles minified css & js file loading
	 */
	public static function main_toggle( $data ) {
		if( empty( $data ) || empty( $data['state'] ) || empty( $data['mode']  ) )
			exit( 'empty-data' );

		$state = $data['state'] == 'on' ? true : false;


		if( $data['mode'] == 'css' )
			$option_name = 'cjm_is_css_on';
		else if( $data['mode'] == 'js' )
			$option_name = 'cjm_is_js_on';

		if( !empty( $option_name ) ) {
			update_option( $option_name, (bool) $state, true );

			if( get_option( $option_name ) === $state )
				exit( 'saved' );
		}
		exit( 'error' );
	}

	/**
	 * Route for every wp ajax request made to "cjm_ajax_admin" action. Only self::TASKS are permitted
	 */
	public static function init() {

		// get ajax posted vars
		$nonce	= !empty( $_POST['nonce'] ) ? $_POST['nonce'] : array();
		$task	= !empty( $_POST['task'] ) ? $_POST['task'] : array();
		$data 	= !empty( $_POST['data'] ) ? $_POST['data'] : array();

		// check if task exists in self::TASKS const and verify nonce
		if( in_array( $task, self::TASKS ) && $nonce && wp_verify_nonce( $nonce, $task ) ) {

			if( method_exists( get_class(), $task ) )
				self::{$task}( $data );
			else
				exit( 'error-2' );

			exit( 'error-3' );
		}
		exit( 'error-1' );
	}

}

add_action( 'wp_ajax_cjm_ajax_admin', array( 'CJM\Admin\AjaxHandler' , 'init' ) );

