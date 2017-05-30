<?php

namespace CJM;

defined( 'ABSPATH' ) || exit();

use MatthiasMullie\Minify as Minificator;
use CJM\Exceptions\CjmException;

class Minify extends FileBase
{

	/**
	 *	@var array
	 */
	public $file_obj;

	/**
	 *	@var string
	 *  File type (js|css)
	 */
	public $file_type;

	/**
	 *	@var string
	 *	Holds $wp_styles and $wp_scripts objects
	 */
	public $wp_objects;

	/**
	 *	@var bool
	 *	True when procedure is finished
	 */
	public $finished;

	/**
	 *	@const array
	 */
	const PERMITTED_FILE_TYPES = array( 'js', 'css' );
	/**
	 *	@const string
	 *  Final file name is assembled from self::DEFAULT_NAME . {$this->file_type} . {$priority} (eg.: "cjm_file_css_0")
	 */
	const DEFAULT_NAME 			= 'cjm_file';


	/**
	 * Minify constructor.
	 *
	 * @param $priority_parts
	 */
	function __construct( $priority_parts ) {
		parent::__construct();
		
		if( !empty( $priority_parts ) && is_array( $priority_parts ) ) {
			// set objects
			$this->file_obj = $priority_parts;
			$this->set_wp_objects();
			$this->finished = false;

			// run for testing purposes
			$this->run();
		}

	}

	public function run() {
		try {

			$this->load_minify_lib();
			$this->build_minified_files();

		} catch( CjmException $e ) {
			echo 'Caught exception: ',  $e->getMessage(), "\n";
		}
	}

	private function set_wp_objects() {

		// set registered objects from wp global variables
		$this->wp_objects['css'] 	= $this->getCssLog();
		$this->wp_objects['js'] 	= $this->getJsLog();

	}

	/**
	 * @throws CjmException
	 */
	private function build_minified_files() {
		if( empty( $this->file_obj ) || !is_array( $this->file_obj ) )
			throw new CjmException( 'The provided object must be array and not empty.' );


		// process file object
		foreach( $this->file_obj as $name => &$chunk ) {
			$this->file_type = $chunk['type'];
			if( !in_array( $this->file_type, self::PERMITTED_FILE_TYPES ) )
				throw new CjmException( 'Neplatný nebo neexistující typ souborů (koncovka)' );

			if( empty( $chunk['files'] ) || !is_array( $chunk['files'] ) )
				throw new CjmException( 'Neplatný blok souborů' );

			// init Minificator class
			if( $chunk['type'] == 'css' )
				$minifier = new Minificator\CSS();
			else if( $chunk['type'] == 'js' )
				$minifier = new Minificator\JS();
			else
				continue;

			// process files, merge and minify them
			foreach( $chunk['files'] as $priority => $file ) {
				$file_abspath = $this->get_file_location_from_slug( $file );

				// add file to Minificator
				if( file_exists( $file_abspath ) )
					$minifier->add( $file_abspath );
			}

			if( !$this->getCacheDir() )
				throw new CjmException( 'Cache dir was not set in plugin root file.' );

			if( !is_dir( $this->getCacheDir() ) )
				throw new CjmException( 'The folder ' . $this->getCacheDir() . ' was not found, try to install plugin again or create folder yourself (set chmod to 777).' );

			// generate name and path for the file
			$new_file = $this->minified_file_name( $name );


			if( !isset( $new_file['path'] ) )
				throw new CjmException( 'Missing path of minified file.' );

			if( !isset( $new_file['name'] ) )
				throw new CjmException( 'Missing name of minified file.' );

			// create the file
			$minifier->minify( $new_file['path'] );

			// update the file object
			$chunk['name'] 	= $new_file['name'];
			$chunk['path'] 	= $new_file['path'];
			$chunk['src'] 	= $new_file['src'];

			// settings
			$chunk['in_footer'] = $chunk['in_footer'] === 'true' || $chunk['in_footer'] === true ? true : false;
			$chunk['async']     = $chunk['async'] == 'async' || $chunk['async'] == 'defer' ? $chunk['async'] : false;
			$chunk['priority']  = !empty( $chunk['priority'] ) && is_numeric( $chunk['priority'] ) && $chunk['priority'] > 0 ? $chunk['priority'] : $this->getDefaultPriority();

		}

		// save everything to wp options for later use
		$this->save_object_as_option();
	}

	/**
	 * @return void
	 */
	private function save_object_as_option() {

		// split mixed data to css & js chunks
		$parts = cjm_get_minified_parts( $this->file_obj );

		// save chunks to separate wp options
		if( !empty( $parts['css'] ) )
			update_option( 'cjm_minified_files_css', $parts['css'], $autoload = true );
		if( !empty( $parts['js'] ) )
			update_option( 'cjm_minified_files_js', $parts['js'], $autoload = true );

		$this->finished = true;
	}

	/**
	 * @param $name
	 *
	 * @return mixed
	 */
	private function minified_file_name( $name ) {

		$hash = $this->generate_hash();

		// set file name (without hash) more info in self::DEFAULT_NAME annotation
		$data['name'] 	= is_numeric( $name ) ? self::DEFAULT_NAME . '_' . $this->file_type . '_' . $name : $name;
		// create absolute file path
		$data['path'] 	= $this->getCacheDir() . $data['name'] . '_' . $hash . '.' . $this->file_type;
		// create absolute file url
		$data['src'] 	= $this->getCacheUrl() . $data['name'] . '_' . $hash . '.' . $this->file_type;

		return $data;
	}

	/**
	 * @param bool $more_entropy
	 *
	 * @return string
	 */
	private function generate_hash( $more_entropy = false ) {
	    $s = uniqid( '', $more_entropy );
	    if ( !$more_entropy )
	        return base_convert( $s, 16, 36 );

	    $hex = substr( $s, 0, 13 );
	    $dec = $s[13] . substr( $s, 15 ); // skip the dot
	    return base_convert( $hex, 16, 36 ) . base_convert( $dec, 10, 36 );
	}

	/**
	 * @param $slug
	 *
	 * @return bool|string
	 */
	private function get_file_location_from_slug( $slug ) {
		if( empty( $slug ) || empty( $this->file_type  ) )
			return false;

		// find source of the file
		if( isset( $this->wp_objects[ $this->file_type ]{ $slug }->src ) ) {
			$url = $this->wp_objects[ $this->file_type ]{ $slug }->src;

			// create absolute path
			$path = ABSPATH . cjm_strip_site_from_url( $url );

			return $path;
		}
		return false;
	}

	/**
	 * @return bool
	 * @throws CjmException
	 */
	private function load_minify_lib() {
		$path = $this->getLibsDir();

		if( !$path )
			throw new CjmException( 'Library directory was not set in plugin root file.' );

		if( !is_dir( $path ) )
			throw new CjmException( 'Provided library directory path is not a folder.' );

		require_once $path . 'minify/src/Minify.php';
		require_once $path . 'minify/src/CSS.php';
		require_once $path . 'minify/src/JS.php';
		require_once $path . 'minify/src/Exception.php';
		require_once $path . 'minify/src/Exceptions/BasicException.php';
		require_once $path . 'minify/src/Exceptions/FileImportException.php';
		require_once $path . 'minify/src/Exceptions/IOException.php';
		require_once $path . 'path-converter/src/ConverterInterface.php';
		require_once $path . 'path-converter/src/Converter.php';

		return true;
	}

}
