<?php

namespace Kit\Database\MySql;

class QueryBuilder{
	private $sql;

	private $scheme;
	private $table;

	private $type;
	private $query;

	private $where = '';
	private $order = '';
	private $limit = '';
	private $offset = '';

	private $values = [];

	private function __construct($scheme, $table, $type){
		$this->scheme = $scheme;
		$this->table = $table;
		$this->type = $type;

		return $this;
	}

	public static function insert($scheme, $table, $columns, $rows){
		$self = new self($scheme, $table, 'insert');

		$query = '';
		foreach($rows as $row){
			$query .= '(';
			foreach($row as $value){
				$query .= '?,';
				$self->values[] = $value;
			}
			$query = rtrim($query, ',') . '),';
		}

		$self->query = 'INSERT INTO `' . $self->scheme . '`.`' . $self->table . '`';
		$self->query .= '(' . implode(',', $columns) . ')';
		$self->query .= ' VALUES ' . rtrim($query, ',');

		return $self;
	}

	public static function select($scheme, $table, $columns = NULL){
		$self = new self($scheme, $table, 'select');

		if(!$columns){
			$query = '*';
		}
		else{
			$query = '`' . $self->table . '`.`id`,';
			foreach($columns as $column){
				$query .= '`' . $self->table . '`.`' . $column . '`,';
			}
		}

		$self->query = 'SELECT ' . rtrim($query, ',');
		$self->query .= ' FROM `' . $self->scheme . '`.`' . $self->table . '`';

		return $self;
	}

	public static function update($scheme, $table, $update){
		$self = new self($scheme, $table, 'update');

		$query = '';
		foreach($update as $key => $value){
			$query .= '`' . $self->table . '`.`' . $key . '`=?,';
			$self->values[] = $value;
		}

		$self->query = 'UPDATE `' . $self->scheme . '`.`' . $self->table . '`';
		$self->query .= ' SET ' . rtrim($query, ',');

		return $self;
	}

	public static function delete($scheme, $table){
		$self = new self($scheme, $table, 'delete');

		$self->query = 'DELETE FROM `' . $self->scheme . '`.`' . $self->table . '`';

		return $self;
	}

	public function where($where, $delimiter = 'and', $level = 1){
		$delimiter = strtoupper($delimiter);

		$query = '';
		foreach($where as $key => $value){
			if($key === 'and' || $key === 'or'){
				$result = $this->where($value, $key, $level + 1);
				if($level != 1){
					$query .= ' ' . $delimiter . ' (' . $result . ')';
				}
				else{
					$query .= $result;
				}
			}
			else{
				if($query != ''){
					$query .= ' ' . $delimiter . ' ';
				}
				$query .= '`' . $this->table . '`.`' . $value[0] . '`' . $value[1] . '?';
				$this->values[] = $value[2];
			}
		}

		if($level != 1){
			return $query;
		}

		$this->where = ' WHERE ' . $query;

		return $this;
	}

	public function order($order){
		$this->order = ' ORDER BY ';
		foreach($order as $set){
			foreach($set as $column => $sort){
				$type = ($sort == -1)? 'DESC':'ASC';
				$this->order .= '`' . $this->table . '`.`' . $column . '` ' . $type . ',';
			}
		}

		$this->order = rtrim($this->order, ',');

		return $this;
	}

	public function limit($limit){
		$this->limit = ' LIMIT '.$limit;

		return $this;
	}

	public function offset($offset){
		$this->offset = ' OFFSET '.$offset;

		return $this;
	}

	private function build(){
		$this->sql = $this->query.$this->where.$this->order.$this->limit.$this->offset;
	}

	public function getSyntax(){
		$this->build();

		return $this->sql;
	}

	public function getValues(){
		return $this->values;
	}
}
