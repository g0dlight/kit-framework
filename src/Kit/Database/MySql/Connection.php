<?php

namespace Kit\Database\MySql;

use \PDO,
	\Kit\Config;

class Connection extends PDO{
	private static $mysqlConnection = [];

	private function __construct($user, $password, $server, $charset){
		$dsn = 'mysql:host=' . $server . ';charset=' . $charset;

		parent::__construct($dsn, $user, $password);

		return $this;
	}

	public static function get($serverKey){
		$config = Config::get('databases.' . $serverKey);

		$user = (isset($config[$serverKey]['user']))? $config[$serverKey]['user']:'root';
		$password = (isset($config[$serverKey]['password']))? $config[$serverKey]['password']:NULL;
		$server = (isset($config[$serverKey]['server']))? $config[$serverKey]['server']:'localhost';
		$charset = (isset($config[$serverKey]['charset']))? $config[$serverKey]['charset']:'utf8';

		if(self::$mysqlConnection[$serverKey]){
			return self::$mysqlConnection[$serverKey];
		}

		self::$mysqlConnection[$serverKey] = new self($user, $password, $server, $charset);

		return self::$mysqlConnection;
	}
}
