<?php

namespace Kit;

use Kit\Exception\CoreException;

class Config
{
	private static $files = [];

	private static function getFile(&$key)
    {
		$key = explode('.', $key);

		$fileName = array_shift($key);

		if(!isset(self::$files[$fileName])){
			$path = BASE_PATH.'Config/'.$fileName.'.php';

			if(!file_exists($path))
				throw new CoreException('The file: `'.$fileName.'` not exists in `Config` folder');

			self::$files[$fileName] = include $path;
		}

		return $fileName;
	}

	public static function get($key)
    {
		$fileName = self::getFile($key);

		return Useful::nestedValue(self::$files[$fileName], $key);
	}

	public static function set($key, $value)
    {
		$fileName = self::getFile($key);

		return Useful::nestedValue(self::$files[$fileName], $key, $value);
	}
}
