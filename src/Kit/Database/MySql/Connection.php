<?php

namespace Kit\Database\MySql;

use PDO;
use Kit\Config;

class Connection extends PDO
{
	private static $mysqlConnection = [];

	public function __construct($user, $password, $server, $charset)
    {
		$dsn = 'mysql:host=' . $server . ';charset=' . $charset;

		parent::__construct($dsn, $user, $password, [
			self::ATTR_ERRMODE => self::ERRMODE_EXCEPTION
		]);

		return $this;
	}

	public static function get($serverKey)
    {
		$config = Config::get('databases.' . $serverKey);

		$user = (isset($config['user']))? $config['user']:'root';
		$password = (isset($config['password']))? $config['password']:NULL;
		$server = (isset($config['server']))? $config['server']:'localhost';
		$charset = (isset($config['charset']))? $config['charset']:'utf8';

		if(isset(self::$mysqlConnection[$serverKey])){
			return self::$mysqlConnection[$serverKey];
		}

		self::$mysqlConnection[$serverKey] = new self($user, $password, $server, $charset);

		return self::$mysqlConnection[$serverKey];
	}
}
