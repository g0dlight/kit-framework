<?php

namespace Kit\Core;

use \Kit\Exception\HttpNotFoundException;
use \Kit\Config;
use \Exception;

final class System{
	public static $jsonResponse = false;
	public static $argv = NULL;

	function __construct(){
		if(!is_null(self::$argv)){
			Output::run();

			Shutdown::run();

			Errors::run();
		}
	}

	public function run($route=null, $accessPath=[]){
		try{
			$this->setSettings();

			if(!is_null(self::$argv)){
				$route = array_shift(self::$argv);
				$accessPath = self::$argv;

				$route = Router::prepareRoute($route, $accessPath);
			}
			elseif(!$route){
				$route = Router::getRoute();
			}
			else{
				$route = Router::prepareRoute($route, $accessPath);
			}

			$result = Router::runRoute($route);

			if(self::$jsonResponse){
				echo JsonResponse::ok($result);
			}
			else{
				return $result;
			}
		}
		catch(HttpNotFoundException $error){
			Errors::httpNotFound($error);
		}
		catch(Exception $error){
			if(self::$jsonResponse){
				echo JsonResponse::error($error);
			}
			else{
				Errors::fatal($error);
			}
		}
	}

	public static function setArgv($argv){
		array_shift($argv);

		self::$argv = $argv;
	}

	private function setSettings(){
		$config = Config::get('system');

		date_default_timezone_set($config['defaultTimezone']);

		define('DATE', $config['dateTemplate']);
	}
}
