<?php

namespace CJM;

defined( 'ABSPATH' ) || exit();

abstract class Settings
{

	static protected $protected = array();
	static protected $public = array();

	public static function getProtected( $key )
	{
		return isset( self::$protected[$key] ) ? self::$protected[$key] : null;
	}

	public static function getPublic( $key )
	{
		return isset( self::$public[$key] ) ? self::$public[$key] : null;
	}

	public static function setProtected( $key, $value )
	{
		self::$protected[$key] = $value;
	}

	public static function setPublic($key,$value)
	{
		self::$public[$key] = $value;
	}

	public function __get( $key )
	{
		return isset( self::$public[$key] ) ? self::$public[$key] : null;
	}

	public function __isset( $key)
	{
		return isset( self::$public[$key] );
	}
}