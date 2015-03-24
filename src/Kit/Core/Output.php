<?php

namespace Kit\Core;

use \Kit\Exception\KitException;

final class Output{
	private static $content = '';

	public static function run(){
		ob_start();

	}

	public static function get(){
		self::$content .= ob_get_contents();

		ob_clean();

		return self::$content;
	}

	public static function clean(){
		self::$content = '';

		ob_clean();

		return true;
	}

	public static function end(){
		self::get();
		ob_end_clean();
	}

	public static function flush(){
		echo self::$content;

		return true;
	}
}