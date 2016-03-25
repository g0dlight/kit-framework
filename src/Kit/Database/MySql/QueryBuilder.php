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

	private function __construct(){
	}

	public static function select($scheme, $table, $columns){
		$self = new self();
		$self->scheme = $scheme;
		$self->table = $table;
		$self->type = 'select';

		$placeHolders = [];
		foreach($columns as $column){
			$placeHolders[] = '?';
			$self->values[] = '`' . $self->table . '`.`' . $column . '`';
		}

		$self->query = 'SELECT ' . implode(',', $placeHolders);
		$self->query .= ' FROM `' . $self->scheme . '`.`' . $self->table . '`';

		return $self;
	}

	public static function insert($scheme, $table, $columns, $rows){
		$self = new self();
		$self->scheme = $scheme;
		$self->table = $table;
		$self->type = 'insert';

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

	public static function update($scheme, $table, $update){
		$self = new self();
		$self->scheme = $scheme;
		$self->table = $table;
		$self->type = 'update';

		$query = '';
		foreach($update as $key => $value){
			$query .= '`' . $self->table . '`.`' . $key . '`=?,'
			$self->values[] = $value;
		}

		$self->query = 'UPDATE `' . $self->scheme . '`.`' . $self->table . '`';
		$self->query .= ' SET ' . rtrim($query, ',');

		return $self;
	}

	public static function delete($scheme, $table){
		$self = new self();
		$self->scheme = $scheme;
		$self->table = $table;
		$self->type = 'delete';

		$self->query = 'DELETE FROM `' . $self->scheme . '`.`' . $self->table . '`';

		return $self;
	}

	public function where($where){
		return $this;
	}

	public function order($order){
		return $this;
	}

	public function limit($limit){
		return $this;
	}

	public function offset($offset){
		return $this;
	}

	public function build(){
		$this->sql = $query.$where.$order.$limit.$offset;
	}

	public function execute(){
		$this->build();
	}

	public function getSyntax(){
		if(!$this->sql){
			$this->build();
		}

		return $this->sql;
	}
}
