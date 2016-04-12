<?php

namespace Kit\Core;

use \Kit\Exception\HttpNotFoundException;
use \Kit\Config;
use \Exception;

final class System{
	public static $jsonResponse = false;

	function __construct(){
		Output::run();

		Shutdown::run();

		Errors::run();
	}

	public function run($route=null, $accessPath=[]){
		try{
			$this->setSettings();

			if(!$route){
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

	private function setSettings(){
		$config = Config::get('system');

		date_default_timezone_set($config['defaultTimezone']);
	}
}
