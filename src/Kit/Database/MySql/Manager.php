<?php

namespace Kit\Database\MySql;

use \Kit\Exception\DatabaseException;

abstract class Manager{
	public static $_server;
	public static $_scheme;
	public static $_table;

	public static function __callStatic($name, $arguments){
		$arguments = array_merge([self::$_scheme, self::$_table], $arguments);

		return call_user_func_array([__NAMESPACE__ . '\QueryBuilder', $name], $arguments);
	}

	// public function execute(){
	// 	$connection = Connection::get($this->_server);
	//
	// 	$statement = $connection->prepare($sql);
	//
	// 	$statement->bindParam(1, 'value', Connection::PARAM_INT);
	// 	$statement->bindParam(2, 'value', Connection::PARAM_STR);
	//
	// 	$statement->execute();
	//
	// 	$statement->rowCount();
	//
	// 	return $statement;
	// 	new static();
	// }
}
