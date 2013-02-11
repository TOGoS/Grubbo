<?php

class Grubbo_Util_ArrayUtil
{
	public static function coalesce( &$value, $default=null ) {
		return isset($value) ? $value : $default;
	}
	
	public static function coalesceArr( $values, $default=null ) {
		foreach( $values as $k=>$v ) {
			if( isset($v) ) return $v;
		}
		return $default;
	}
}
