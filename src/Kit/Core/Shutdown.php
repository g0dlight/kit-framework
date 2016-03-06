<?php

namespace Kit\Core;

use \Kit\Exception\KitException;

final class Shutdown{
	public static function run(){
		register_shutdown_function(array('Kit\Core\Shutdown', 'execute'), getcwd());
	}

	public static function execute($workingDir){
		chdir($workingDir);
		$errorCatch = error_get_last();
		$notFatalError = [E_WARNING];

		if(isset($errorCatch['type']) && !in_array($errorCatch['type'], $notFatalError))
			Errors::fatal($errorCatch);

		if(Errors::$catch)
			Errors::flashErrors();

		Output::end();
		Output::flush();
	}
}
