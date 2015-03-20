<?php

namespace Kit\Core;

use \Kit\Exception\KitException, \Kit\Exception\HttpNotFoundException;

final class System{
	public $config;

	function __construct(){
		try{
			$this->config = \Kit\Config::get('system');
			if(!isset($this->config['error_handler']) || !isset($this->config['http_not_found_handler']))
				throw new KitException();

			$errorsConfig = [
				'error_handler' => $this->config['error_handler'],
				'http_not_found_handler' => $this->config['http_not_found_handler']
			];
		}
		catch(KitException $error){
			$errorsConfig = [
				'error_handler' => '',
				'http_not_found_handler' => ''
			];
		}

		Shutdown::run();
		Errors::run($errorsConfig);
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
