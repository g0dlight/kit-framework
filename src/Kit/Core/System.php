<?php

namespace Kit\Core;

use \Kit\Exception\CoreException, \Kit\Exception\HttpNotFoundException;

final class System{
	function __construct(){
		Output::run();

		Shutdown::run();

		Errors::run();
	}

	public function run($route=null, $accessPath=[]){
		try{
			if(!$route){
				$route = Router::getRoute();
			}
			else{
				$route = Router::prepareRoute($route, $accessPath);
			}

			Router::runRoute($route);
		}
		catch(HttpNotFoundException $error){
			Errors::httpNotFound($error);
		}
		catch(CoreException $error){
			Errors::fatal($error);
		}
	}
}
