<?php

namespace Kit\Core;

use Kit\Exception\HttpNotFoundException;
use ReflectionMethod;

final class Router
{
	public static $route = false;
	public static $accessPath = false;
	public static $requestMethod;
	public static $httpMethod = ['get','post','delete','head','put','trace','options','connect','patch'];

	public static function getRoute()
    {
		if(!self::$accessPath)
			self::getAccessPath();

		$accessPath = self::$accessPath;

		$route = include BASE_PATH.'App/Route.php';

		self::$requestMethod = strtolower($_SERVER['REQUEST_METHOD']);

		$removedAccessPath = [];

		$routeTypes = array_merge(['any', 'controller'], self::$httpMethod);

		foreach($accessPath as $value){
			if(in_array($value, $routeTypes))
				continue;

			if(!isset($route[$value]))
				break;

			$route = $route[$value];
			$removedAccessPath[] = array_shift($accessPath);
		}

		if(!$removedAccessPath && isset($route['/']))
			$route = $route['/'];

		if(isset($route[self::$requestMethod])){
			$route = $route[self::$requestMethod];
		}
		elseif(isset($route['any'])){
			$route = $route['any'];
		}
		elseif(isset($route['controller'])){
			$method = array_shift($accessPath);

			if($method){
				$removedAccessPath[] = $method;
				self::cleanPath($method);
				$methods[] = self::$requestMethod.$method;
				$methods[] = 'any'.$method;
			}

			if($method != 'Index'){
				$methods[] = self::$requestMethod.'Index';
				$methods[] = 'anyIndex';
			}

			foreach($methods as $key => $method){
				if(method_exists('Controllers\\'.$route['controller'], $method))
					break;

				$method = '';
			}

			$methodsCount = count($methods);
			if($methodsCount - $key <= $methodsCount - 2)
				array_unshift($accessPath, array_pop($removedAccessPath));

			$route = $route['controller'].'@'.$method;
		}
		else{
			$accessPath = array_merge($removedAccessPath, $accessPath);
			$route = '';
		}

		return self::prepareRoute($route, $accessPath);
	}

	public static function prepareRoute($route, $params)
    {
		$route = explode('@', $route);

		if(count($route) != 2)
			throw new HttpNotFoundException('Routing error: route need to be assemble like: `controller@method`');

		return [
			'class' => 'Controllers\\'.array_shift($route),
			'method' => array_shift($route),
			'params' => $params
		];
	}

	public static function runRoute($sortRoute)
    {
		self::$route = $sortRoute;

		if(!class_exists($sortRoute['class']))
			throw new HttpNotFoundException('Routing error: undefined class');

		if(is_a($sortRoute['class'], '\Kit\Controllers\ApiJson', true))
			System::$jsonResponse = true;

		$run = new $sortRoute['class']();

		if(!method_exists($run, $sortRoute['method']))
			throw new HttpNotFoundException('Routing error: undefined method');

		$reflectionMethod = new ReflectionMethod($run, $sortRoute['method']);
		$totalParams = count($sortRoute['params']);

		if($reflectionMethod->getNumberOfRequiredParameters() > $totalParams)
			throw new HttpNotFoundException('Routing error: missing method parameters');

		if($reflectionMethod->getNumberOfParameters() < $totalParams)
			throw new HttpNotFoundException('Routing error: to many parameters');

		return call_user_func_array([$run, $sortRoute['method']], $sortRoute['params']);
	}

	public static function getAccessPath()
    {
		$accessPath = (isset($_SERVER['PATH_INFO']))? $_SERVER['PATH_INFO']:'';
		$accessPath = explode('/',$accessPath);

		$tmpAccessPath = [];
		foreach($accessPath as $value){
			if($value != '')
				$tmpAccessPath[] = $value;
		}

		return self::$accessPath = $tmpAccessPath;
	}

	public static function cleanPath(&$string)
    {
		$parts = explode('-', strtolower($string));
		$string = '';
		foreach($parts as $value){
			$string .= ucfirst($value);
		}
	}
}
