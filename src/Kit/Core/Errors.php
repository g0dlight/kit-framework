<?php

namespace Kit\Core;

use Kit\Config;

final class Errors
{
	public static $config = [];
	public static $catch = [];

	public static function run()
    {
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

	public static function getTitle($errorNumber=0)
    {
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

	public static function fatal($error)
    {
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

	public static function nonfatal($errorNumber, $errorMessage, $errorFileName, $errorLineNumber)
    {
		$error = [
			'fatal' => false,
			'title' => self::getTitle($errorNumber),
			'type' => $errorNumber,
			'message' => $errorMessage,
			'file' => str_replace(dirname(getcwd()), '', $errorFileName),
			'line' => $errorLineNumber
		];

        if( ! System::isTerminalInterface() )
            $error['trace'] = debug_backtrace();

		self::$catch[] = $error;
	}

	public static function flashErrors()
    {
		Response::setCode(500);

		$output = Output::get();
		Output::clean();

        if( self::runCustomHandler('500', [self::$catch, $output]) )
            return;

		$errors = self::$catch;

		Response::setContentType('html');

		if( System::isTerminalInterface() ){
			print_r( $errors );
		}
		else{
			include dirname(__DIR__).'/Views/Errors.php';
		}
	}

	public static function httpNotFound($error)
    {
		Response::setCode(404);

		$output = Output::get();
		Output::clean();

		if( self::runCustomHandler('404', [$error, $output]) )
		    return;

		Response::setContentType('html');

        if( System::isTerminalInterface() ){
			echo $error;
		}
		else{
			include dirname(__DIR__).'/Views/404.php';
		}
	}

	public static function runCustomHandler($type, $arguments)
    {
        $handler = self::$config[$type . '_handler'];

        if( ! $handler )
            return FALSE;

        $handler = Router::prepareRoute($handler, $arguments);

        Router::runRoute($handler);

        return TRUE;
    }
}
