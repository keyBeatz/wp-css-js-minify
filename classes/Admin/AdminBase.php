<?php
namespace CJM\Admin;

use CJM\FileBase;

defined( 'ABSPATH' ) || exit();


abstract class AdminBase extends FileBase
{

	private $options;

	function __construct() {
		parent::__construct();

		$this->setAllAdminSettings();
		$this->initFilters();
    }

    private function setAllAdminSettings() {
	    $this->options 	= get_option( 'cjm_options' ) ?: array();
    }

	private function initFilters() {
		add_action( 'admin_enqueue_scripts', array( $this, 'cssJsLoader' ), 9999 );
	}

	public function cssJsLoader() {
		// css
		wp_enqueue_style( 'cjm-jquery-ui-theme', $this->getPluginUrl() . '/assets/jquery-ui/jquery-ui.min.css' );
		wp_enqueue_style( 'cjm-admin', $this->getPluginUrl() . '/assets/css/admin.css' );

		// js
		//wp_enqueue_script( 'cjm-jquery-ui', $this->getPluginUrl() . '/assets/jquery-ui/jquery-ui.min.js' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script( 'jquery-ui-tooltip' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-ui-tabs' );
		wp_enqueue_script( 'cjm-admin-minify', $this->getPluginUrl() . '/assets/js/admin-minify.js' );
		//wp_enqueue_script( 'cjm-jquery-ui-theme', $this->getPluginUrl() . '/assets/jquery-ui/jquery-ui.theme.min.js' );

		$translation_array = array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'msg_error' => esc_html__( "Error, minified files could not be generated. Please try again.", "css-js-minify" ),
			'msg_error_empty' => esc_html__( "You probably trying to delete all blocks. To achieve this please use button Delete All.", "css-js-minify" ),
			'msg_success_erased' => esc_html__( "Successfully deleted.", "css-js-minify" ),
			'msg_success_saved' => esc_html__( "Successfully saved.", "css-js-minify" ),
			'msg_confirm_save' => esc_html__( "Do you really want to generate new files? The old ones will be deleted.", "css-js-minify" ),
			'msg_confirm_block_delete' => esc_html__( "Do you really want to delete this block?", "css-js-minify" ),
			'msg_confirm_all_blocks_delete' => esc_html__( "Do you really want to delete all blocks?", "css-js-minify" )
		);
		wp_localize_script( 'cjm-admin-minify', 'cjm', $translation_array );
	}

	/**
	 * @return array
	 */
	public function getOptions() {
		return $this->options;
	}
}
