<?php

namespace Kit\Core;

use \Kit\Exception\KitException, \Kit\Exception\HttpNotFoundException;

final class System{
	public $config;

	function __construct(){
		Shutdown::run();
		Errors::run();
	}

	public function run(){
		try{
			$router = Router::getRoute();

			ob_start();

			Router::runRoute($router);

			$output = ob_get_contents();

			ob_end_clean();

			echo $output;
		}
		catch(HttpNotFoundException $error){
			Errors::httpNotFound($error);
		}
		catch(KitException $error){
			Errors::fatal($error);
		}
	}
}
