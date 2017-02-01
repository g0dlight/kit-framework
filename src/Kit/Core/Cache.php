<?php

namespace Kit\Core;

use Kit\Exception\CoreException;
use Kit\Config;
use Memcached;

class Cache
{
	private static $memcached;

	private static $servers;
	private static $options;

	public static function init($servers = FALSE, $options = [])
    {
		if(self::$memcached)
			return;

		self::setConfig($servers, $options);

		self::$memcached = new Memcached();

		self::$memcached->addServers(self::$servers);

		foreach(self::$options as $key => $value){
			self::$memcached->setOption(constant('Memcached::'.$key), constant('Memcached::'.$value));
		}
	}

	private static function setConfig($servers, $options)
    {
		$config = Config::get('cache');

		if(!$servers){
			if(!$config['servers'])
				throw new CoreException('Cache missing servers');

			$servers = $config['servers'];
		}

		if(!$options){
			if(!isset($config['options']) || !is_array($config['options']))
				$config['options'] = [];

			$options = $config['options'];
		}

		self::$servers = $servers;
		self::$options = $options;
	}

	public static function __callStatic($name, $arguments)
    {
		self::init();

		return call_user_func_array([self::$memcached, $name], $arguments);
    }
}
