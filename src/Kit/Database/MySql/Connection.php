<?php

namespace Kit\Database\MySql;

use \PDO;

class Connection extends PDO{
	private static $mysqlConnection = [];

	private function __construct($user, $password, $server, $charset){
		$dsn = 'mysql:host=' . $server . ';charset=' . $charset;

		parent::__construct($dsn, $user, $password);

		return $this;
	}

	public static function get($user, $password = NULL, $server = 'localhost', $charset = 'utf8'){
		if(self::$mysqlConnection[$server]){
			return self::$mysqlConnection[$server];
		}

		self::$mysqlConnection[$server] = new self($user, $password, $server, $charset);

		return self::$mysqlConnection;
	}
}
