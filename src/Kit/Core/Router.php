<?php

namespace Kit\Core;

use \Kit\Exception\CoreException;
use \Kit\Exception\HttpNotFoundException;
use \ReflectionMethod;

final class Router{
	public static $route = false;
	public static $accessPath = false;
	public static $requestMethod;
	public static $httpMethod = ['any','get','post','delete','head','put','trace','options','connect','patch'];

	public static function getRoute(){
		if(!self::$accessPath)
			self::getAccessPath();

		$accessPath = self::$accessPath;

		$route = include BASE_PATH.'App/Route.php';

		self::$requestMethod = strtolower($_SERVER['REQUEST_METHOD']);

		$removedAccessPath = [];

		foreach($accessPath as $value){
			if(in_array($value, self::$httpMethod))
				continue;

			if(!isset($route[$value]))
				break;

			$route = $route[$value];
			$removedAccessPath[] = array_shift($accessPath);
		}

		if(isset($route[self::$requestMethod])){
			$route = $route[self::$requestMethod];
		}
		elseif(isset($route['any'])){
			$route = $route['any'];
		}
		elseif(isset($route['controller'])){
			$method = array_shift($accessPath);

			if(!$method)
				$method = 'index';

			self::cleanPath($method);

			$route = $route['controller'].'@'.self::$requestMethod.$method.'@any'.$method;
		}
		else{
			$accessPath = array_merge($removedAccessPath, $accessPath);
			$route = '';
		}

		return self::prepareRoute($route, $accessPath);
	}

	public static function prepareRoute($route, $params){
		$route = explode('@', $route);

		if(count($route) < 2)
			throw new HttpNotFoundException('Routing error: route need to be assemble like: `controller@method`');

		return [
			'class' => 'Controllers\\'.array_shift($route),
			'method' => $route,
			'params' => $params
		];
	}

	public static function runRoute($sortRoute){
		self::$route = $sortRoute;

		if(!class_exists($sortRoute['class']))
			throw new HttpNotFoundException('Routing error: undefined class');

		if(is_a($sortRoute['class'], '\Kit\Controllers\ApiJson', true))
			System::$jsonResponse = true;

		$run = new $sortRoute['class']();

		$controllerMethods = get_class_methods($run);

		$method = false;
		foreach($sortRoute['method'] as $value){
			if(in_array($value, $controllerMethods)){
				$method = $value;
				break;
			}
		}

		if(!$method)
			throw new HttpNotFoundException('Routing error: undefined method');

		$reflectionMethod = new ReflectionMethod($run, $method);

		if($reflectionMethod->getNumberOfRequiredParameters() > count($sortRoute['params']))
			throw new HttpNotFoundException('Routing error: missing method parameters');

		return call_user_func_array([$run, $method], $sortRoute['params']);
	}

	public static function getAccessPath(){
		$accessPath = (isset($_SERVER['PATH_INFO']))? $_SERVER['PATH_INFO']:'';
		$accessPath = explode('/',$accessPath);

		foreach($accessPath as $value){
			if(!empty($value))
				continue;

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
