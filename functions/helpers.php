<?php
defined( 'ABSPATH' ) || exit();

/**
 * @param $name
 * @param string $size
 * @param bool $src_only
 *
 * @return string
 */
function cjm_img( $name, $size = '16', $src_only = false ) {
	if( empty( $name ) )
		return '';

	// build src
	$src = \CJM\Plugin::getPublic( "pluginUrl" ) . "/assets/img/" . $name;

	// size in name
	if( !empty( $size ) )
		$src .= "-" . $size;

	// extension
	$src .= ".png";

	if( $src_only === true )
		return $src;
	else
		return '<img src="'. esc_url( $src ) . '" alt="Ikona '. esc_attr( $name ) . '">';
}

/**
 * @param string $name
 * @param int $size
 * @param string $file_type
 * @param bool $display
 * @param bool $src_only
 *
 * @return string
 */
function cjm_ajax_loader( $name = "gears", $size = 40, $file_type = "svg", $display = false, $src_only = false ) {
	$src = \CJM\Plugin::getPublic( "pluginUrl" ) . "/assets/img/" . $name . "." . $file_type;

	if( $src_only )
		return $src;
	else
		return '<img class="cjm_ajax_loader '. esc_attr( 'cjm_ajax_loader_'. $name ) .'" src="'. esc_url( $src ) .'" alt="Ajax loader icon" width="'. esc_attr( $size ) .'" height="'. esc_attr( $size ) .'" style="display:'. ($display ? 'true' : 'none' ) .';">';
}

/**
 * @param $html
 * @param bool $one_liner
 * @param bool $params
 *
 * @return mixed|string
 */
function cjm_render_as_string( $html, $one_liner = true, $params = false ) {
	ob_start();
	if( !empty( $params ) && is_array( $params ) )
		call_user_func_array( $html, $params );
	else
		$html();
	$string = ob_get_contents();
	ob_end_clean();

	if( $one_liner )
		return str_replace( array( "\n", "\r" ), '', $string );
	else
		return $string;
}

/**
 * @return bool
 */
function cjm_is_login() {
    $ABSPATH_MY = str_replace(array('\\','/'), DIRECTORY_SEPARATOR, ABSPATH);
    return ( (in_array(  $ABSPATH_MY.'wp-login.php', get_included_files() ) || in_array( $ABSPATH_MY.'wp-register.php', get_included_files() ) ) || $GLOBALS['pagenow'] === 'wp-login.php' || $_SERVER['PHP_SELF'] == '/wp-login.php' );
}

/**
 * @param $url
 * @param string $strip_more
 *
 * @return mixed|string
 */
function cjm_strip_site_from_url( $url, $strip_more = '' ) {
	if( empty( $url ) )
		return '';

	$pattern = "/" . preg_quote( site_url( $strip_more ) , '/' ) . "\//";

	return preg_replace( $pattern , "", $url );
}

/**
 * @param bool $obj
 *
 * @return array
 */
function cjm_get_minified_parts( $obj = false ) {
	if( empty( $obj ) || !is_array( $obj ) )
		return array();

	$obj_parts['css'] = array_filter( $obj, function( $var ) {
		return ( $var['type'] == 'css' );
	});
	$obj_parts['js'] = array_filter( $obj, function( $var ) {
		return ( $var['type'] == 'js' );
	});

	return $obj_parts;
}

/**
 * @return bool
 */
function cjm_get_all_minified_parts() {
	$obj['css'] = get_option( 'cjm_minified_files_css' );
	$obj['js'] 	= get_option( 'cjm_minified_files_js' );

	if( empty( $obj ) || !is_array( $obj ) )
		return false;

	$output['css'] = array();
	$output['js'] 	= array();

	if( !empty( $obj['css'] ) && is_array( $obj['css'] ) ) {
		foreach( $obj['css'] as $file )
			$output['css'] = array_merge( $output['css'], $file['files'] );
	}
	if( !empty( $obj['js'] ) && is_array( $obj['js'] ) ) {
		foreach( $obj['js'] as $file )
			$output['js'] = array_merge( $output['js'], $file['files'] );
	}

	return $output;
}
