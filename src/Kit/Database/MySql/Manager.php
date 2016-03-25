<?php

namespace Kit\Database\MySql;

use \Kit\Exception\DatabaseException,
	\ReflectionClass;

abstract class Manager{
	protected $_queryBuilder;
	protected $_statement;
	protected $_rowData;
	protected $_saveType = 'insert';

	public function __call($name, $arguments){
		if(!$this->_queryBuilder){
			throw new DatabaseException('Can not use `' . $name . '` method before useing primary method');
		}

		$this->_queryBuilder = call_user_func_array([$this->_queryBuilder, $name], $arguments);

		return $this;
	}

	public static function __callStatic($name, $arguments){
		return self::initQueryBuilder($name, $arguments);
	}

	public function __get($key){
		if(!isset($this->_rowData[$key])){
			throw new DatabaseException('trying to get property not exists');
		}

		return $this->_rowData[$key];
	}

	public function __set($key, $value){
		$this->_rowData[$key] = $value;
	}

	public function __isset($key){
		return isset($this->_rowData[$key]);
	}

	public function __unset($key){
		unset($this->_rowData[$key]);
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

		if($this->_queryBuilder->isInsert()){
			return $connection->lastInsertId();
		}
		else{
			$this->_saveType = 'update';
			return $this->_statement->rowCount();
		}
	}

	public function fetch(){
		if(!$this->_statement){
			$this->execute();
		}

		$result = $this->_statement->fetch(Connection::FETCH_ASSOC);
		if(!$result){
			return FALSE;
		}

		$this->_rowData = $result;

		return $this;
	}

	public function save(){
		$temp = $this->toArray();

		if($this->_saveType == 'update'){
			$method = 'update';
			$arguments = [$temp];

			$where = [
				['id', '=', $this->id]
			];
		}
		else{
			$method = 'insert';
			$arguments = [array_keys($temp), [$temp]];
		}

		$obj = static::initQueryBuilder($method, $arguments);

		if(isset($where)){
			$obj = $obj->where($where);
		}

		$result = $obj->execute();

		if($this->_saveType == 'insert'){
			$this->id = $result;
		}

		return $result;
	}

	public function delete(){
		return static::initQueryBuilder('delete')->where([
			['id', '=', $this->id]
		])->execute();
	}

	public function toArray(){
		return $this->_rowData;
	}

	private static function initQueryBuilder($name, $arguments = []){
		$obj = new static();

		$arguments = array_merge([$obj->_scheme, $obj->_table], $arguments);

		$obj->_queryBuilder = call_user_func_array([__NAMESPACE__ . '\QueryBuilder', $name], $arguments);

		return $obj;
	}
}
