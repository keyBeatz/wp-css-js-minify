<?php

namespace CJM;

defined( 'ABSPATH' ) || exit();

abstract class Settings
{

	static protected $public = array();

	public static function getSetting( $key )
	{
		return isset( self::$public[$key] ) ? self::$public[$key] : null;
	}

	public static function addSetting($key,$value)
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