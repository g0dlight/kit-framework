<?php

namespace Kit\Core;

use \Kit\Exception\KitException, \Kit\Exception\HttpNotFoundException;

final class Router{
	public static $route = [];
	public static $accessPath = false;
	public static $httpMethod = ['any','get','post','delete','head','put','trace','options','connect','patch'];

	public static function getRoute(){
		if(!self::$accessPath)
			self::getAccessPath();

		$accessPath = self::$accessPath;

		$route = include BASE_PATH.'app/Route.php';

		$requestMethod = strtolower($_SERVER['REQUEST_METHOD']);

		$removedAccessPath = [];

		foreach($accessPath as $value){
			if(in_array($value, self::$httpMethod))
				continue;

			if(!isset($route[$value]))
				break;

			self::$route['route'] = $route[$value];
			$route = $route[$value];
			$removedAccessPath[] = array_shift($accessPath);
		}

		if(isset(self::$route['route'][$requestMethod]))
			self::$route['route'] = self::$route['route'][$requestMethod];

		elseif(isset(self::$route['route']['any']))
			self::$route['route'] = self::$route['route']['any'];

		elseif(isset(self::$route['route']['controller'])){
			$method = array_shift($accessPath);
			self::cleanPath($method);
			self::$route['route'] = self::$route['route']['controller'].'@'.$requestMethod.$method;
		}

		else{
			$accessPath = array_merge($removedAccessPath, $accessPath);
			self::$route['route'] = '';
		}

		self::$route = self::getSortRoute(self::$route['route'], $accessPath);

		return self::$route;
	}

	public static function getSortRoute($route, $accessPath){
		$route = explode('@', $route);

		if(count($route) != 2)
			throw new HttpNotFoundException('Routing error: route need to be assemble like: `controller@method`');

		return [
			'class' => 'Controllers\\'.$route[0],
			'method' => $route[1],
			'params' => $accessPath
		];
	}

	public static function runRoute($sortRoute){
		if(!class_exists($sortRoute['class']))
			throw new HttpNotFoundException('Routing error: undefined class');

		$run = new $sortRoute['class']();

		if(!in_array($sortRoute['method'], get_class_methods($run)))
			throw new HttpNotFoundException('Routing error: undefined method');

		call_user_func_array([$run, $sortRoute['method']], $sortRoute['params']);

	}

	public static function getAccessPath(){
		$accessPath = (isset($_SERVER['PATH_INFO']))? $_SERVER['PATH_INFO']:'';
		$accessPath = explode('/',$accessPath);

		foreach($accessPath as $key => $value){
			if($value != '')
				break;

			array_shift($accessPath);
		}

		if(!$accessPath)
			$accessPath[] = '/';
		
		return self::$accessPath = $accessPath;
	}

	public static function cleanPath(&$string){
		$parts = explode('-', strtolower($string));
		$string = '';
		foreach($parts as $value){
			$string .= ucfirst($value);
		}
	}
}