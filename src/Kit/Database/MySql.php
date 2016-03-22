<?php

namespace Kit\Database;

use \PDO, \PDOException, \Exception;

class MySql extends PDO{
	public function __construct($user, $password = NULL, $server = 'localhost', $charset = 'utf8'){
		try{
			$dsn = 'mysql:host=' . $server . ';charset=' . $charset;
			//dbname='.$database.';

			parent::__construct($dsn, $user, $password);

			return $this;
		}
		catch(PDOException $e){
			throw new Exception('Connection failed: '.$e->getMessage());
		}
	}
}
