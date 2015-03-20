<?php

namespace Kit;

use \Kit\Exception\KitException;

class Config{
	private static $files = [];

	private static function getFile(&$key){
		$key = explode('.', $key);

		$fileName = array_shift($key);

		if(!isset(self::$files[$fileName])){
			$path = BASE_PATH.'Config/'.$fileName.'.php';

			if(!file_exists($path))
				throw new KitException('config file do not exists');

			self::$files[$fileName] = include $path;
		}

		return $fileName;
	}

	public static function get($key){
		$fileName = self::getFile($key);

		return Useful::nestedValue(self::$files[$fileName], $key);
	}

	public static function set($key, $value){
		$fileName = self::getFile($key);

		return Useful::nestedValue(self::$files[$fileName], $key, $value);
	}
}
