<?php

namespace Kit\Database\MySql;

class WhereBuilder{
	public $queryBuilder;
	public $query;

	public function __construct($queryBuilder, $callback){
		$this->queryBuilder = $queryBuilder;

		$this->query = ' WHERE ' . $callback($this);
	}

	public function assert($key, $operator, $value){
		$query = '`' . $this->queryBuilder->getTable() . '`.`' . $key . '`' . $operator;

		if(is_array($value)){
			$query .= '(' . implode(',', array_fill(0, count($value), '?')) . ')';
			foreach($value as $val){
				$this->queryBuilder->addValue($val);
			}
		}
		else{
			$query .= '?';
			$this->queryBuilder->addValue($value);
		}

		return $query;
	}

	public function or(){
		$query = $this->combine('OR', func_get_args());

		return $query;
	}

	public function and(){
		$query = $this->combine('AND', func_get_args());

		return $query;
	}

	private function combine($delimiter, $args){
		$query = '';

		foreach($args as $arg){
			if(is_array($arg)){
				$query .= $this->assert($arg[0], $arg[1], $arg[2]);
			}
			else{
				$query .= '(' . $arg . ')';
			}

			$query .= ' ' . $delimiter . ' ';
		}

		$query = rtrim($query, ' ' . $delimiter . ' ');

		return $query;
	}
}
