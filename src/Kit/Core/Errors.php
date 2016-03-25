<?php

namespace Kit\Core;

use \Kit\Exception\CoreException,
	\Kit\Config;

final class Errors{
	public static $config = array();
	public static $catch = array();

	public static function run(){
		self::$config = [
			'500_handler' => '',
			'404_handler' => ''
		];

		$errorsConfig = Config::get('system.errors');

		if(!isset($errorsConfig['500_handler']))
			self::$config['500_handler'] = $errorsConfig['500_handler'];

		if(!isset($errorsConfig['404_handler']))
			self::$config['404_handler'] = $errorsConfig['404_handler'];

		ini_set('error_reporting', E_ALL);
		ini_set('display_errors', false);
		ini_set('log_errors', true);

		set_error_handler(array('Kit\Core\Errors', 'nonfatal'));
	}

	public static function getTitle($errorNumber=0){
		$errorType = [
			E_ERROR              => 'Error',
			E_WARNING            => 'Warning',
			E_PARSE              => 'Parsing Error',
			E_NOTICE             => 'Notice',
			E_CORE_ERROR         => 'Core Error',
			E_CORE_WARNING       => 'Core Warning',
			E_COMPILE_ERROR      => 'Compile Error',
			E_COMPILE_WARNING    => 'Compile Warning',
			E_USER_ERROR         => 'User Error',
			E_USER_WARNING       => 'User Warning',
			E_USER_NOTICE        => 'User Notice',
			E_STRICT             => 'Runtime Notice',
			E_RECOVERABLE_ERROR  => 'Catchable Fatal Error',
			E_DEPRECATED         => 'Deprecated',
			E_USER_DEPRECATED    => 'User Deprecated'
		];

		if(isset($errorType[$errorNumber])){
			return $errorType[$errorNumber];
		}
		else{
			return 'Unknown Error '.$errorNumber;
		}
	}

	public static function fatal($error){
		if(is_object($error)){
			$trace = $error->getTrace();

			$error = [
				'type' => $error->getCode(),
				'title' => 'Uncaught Exception',
				'message' => $error->getMessage(),
				'file' => 'unknown',
				'line' => 'unknown',
				'trace' => $trace
			];

			if(isset($trace[0]['file'])){
				$error['file'] = $trace[0]['file'];
			}
			if(isset($trace[0]['line'])){
				$error['line'] = $trace[0]['line'];
			}
		}
		else{
			$error['trace'] = debug_backtrace();
		}

		$error['fatal'] = true;
		$error['file'] = str_replace(dirname(getcwd()), '', $error['file']);

		if(!isset($error['title']))
			$error['title'] = self::getTitle($error['type']);

		$error['message'] = explode(' in ', $error['message'])[0];

		self::$catch[] = $error;
	}

	public static function nonfatal($errorNumber, $errorMessage, $errorFileName, $errorLineNumber){
		$error = [
			'fatal' => false,
			'title' => self::getTitle($errorNumber),
			'type' => $errorNumber,
			'message' => $errorMessage,
			'file' => str_replace(dirname(getcwd()), '', $errorFileName),
			'line' => $errorLineNumber,
			'trace' => debug_backtrace()
		];

		self::$catch[] = $error;
	}

	public static function flashErrors(){
		$output = Output::get();
		Output::clean();

		$handler = self::$config['500_handler'];
		if($handler){
			$handler = Router::prepareRoute($handler, [self::$catch, $output]);
			Router::runRoute($handler);
			return;
		}

		$errors = self::$catch;

		http_response_code(500);

		include dirname(__DIR__).'/Views/Errors.php';
	}

	public static function httpNotFound($error){
		$output = Output::get();
		Output::clean();

		$handler = self::$config['404_handler'];
		if($handler){
			$handler = Router::prepareRoute($handler, [$error, $output]);
			Router::runRoute($handler);
			return;
		}

		http_response_code(404);

		include dirname(__DIR__).'/Views/404.php';
	}
}
