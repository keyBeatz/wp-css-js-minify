<?php

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

$path = ABSPATH . "wp-content/plugins/css-js-minify/classes" . str_replace( '\\', DIRECTORY_SEPARATOR, $cls ) . '.php';
require_once( $path );
}
spl_autoload_register( __NAMESPACE__ . '\\autoload' );