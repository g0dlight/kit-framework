<?php

namespace Kit\Core;

use \Kit\Exception\KitException, \Kit\Exception\HttpNotFoundException;

final class System{
	public $config;

	function __construct(){
		Output::run();
		Shutdown::run();
		Errors::run();
	}

	public function run(){
		try{
			$router = Router::getRoute();

			Router::runRoute($router);
		}
		catch(HttpNotFoundException $error){
			Errors::httpNotFound($error);
		}
		catch(KitException $error){
			Errors::fatal($error);
		}
	}

	function __destruct(){
		Output::end();
		echo 'aaa';
	}
}
