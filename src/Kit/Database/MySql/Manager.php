<?php

namespace Kit\Database\MySql;

use \Kit\Exception\DatabaseException;

abstract class Manager{
	private $_queryBuilder;
	private $_statement;

	public function __call($name, $arguments){
		if(!$this->_queryBuilder){
			throw new DatabaseException('Can not use `' . $name . '` method before useing primary method');
		}

		$this->_queryBuilder = call_user_func_array([$this->_queryBuilder, $name], $arguments);

		return $this;
	}

	public static function __callStatic($name, $arguments){
		$obj = new static();

		$arguments = array_merge([$obj->_scheme, $obj->_table], $arguments);

		$obj->_queryBuilder = call_user_func_array([__NAMESPACE__ . '\QueryBuilder', $name], $arguments);

		return $obj;
	}

	public function getQuerySyntax(){
		return $this->_queryBuilder->getSyntax();
	}

	public function execute(){
		$connection = Connection::get($this->_server);

		$this->_statement = $connection->prepare($this->getQuerySyntax());

		foreach($this->_queryBuilder->getValues() as $key => $value){
			// Connection::PARAM_INT, Connection::PARAM_STR
			$this->_statement->bindValue($key + 1, $value);
		}

		$this->_statement->execute();

		return $this->_statement->rowCount();
	}

	public function fetch(){
		$inctance = new static();
		$inctance->_queryBuilder =& $this->_queryBuilder;
		$inctance->_statement =& $this->_statement;

		$result = $this->_statement->fetch(Connection::FETCH_ASSOC);
		if(!$result){
			return FALSE;
		}

		foreach($result as $key => $value){
			$key = lcfirst(str_replace('_', '', ucwords($key, '_')));
			$inctance->$key = $value;
		}

		return $inctance;
	}
}
