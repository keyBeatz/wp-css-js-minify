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
			'ajax_url' => admin_url( 'admin-ajax.php' )
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
