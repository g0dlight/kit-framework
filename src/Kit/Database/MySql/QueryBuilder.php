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
	private $timestamps = TRUE;

	private $values = [];

	private function __construct(Manager $model, $type){
		$this->scheme = $model->_scheme;
		$this->table = $model->_table;
		$this->timestamps = $model->_timestamps;
		$this->type = $type;

		return $this;
	}

	public static function insert($model, $rows){
		$self = new self($model, 'insert');

		$query = '';
		foreach($rows as $row){
			if($self->timestamps){
				$row['updated_at'] = date('Y-m-d H:i:s');
				$row['created_at'] = date('Y-m-d H:i:s');
			}

			$query .= '(';
			foreach($row as $value){
				$query .= '?,';
				$self->values[] = $value;
			}
			$query = rtrim($query, ',') . '),';
		}

		$columns = array_keys($row);

		$self->query = 'INSERT INTO `' . $self->scheme . '`.`' . $self->table . '`';
		$self->query .= '(' . implode(',', $columns) . ')';
		$self->query .= ' VALUES ' . rtrim($query, ',');

		return $self;
	}

	public static function select($model, $columns = NULL){
		$self = new self($model, 'select');

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

	public static function update($model, $rows){
		$self = new self($model, 'update');

		if($self->timestamps){
			$rows['updated_at'] = date('Y-m-d H:i:s');
		}

		$query = '';
		foreach($rows as $key => $value){
			$query .= '`' . $self->table . '`.`' . $key . '`=?,';
			$self->values[] = $value;
		}

		$self->query = 'UPDATE `' . $self->scheme . '`.`' . $self->table . '`';
		$self->query .= ' SET ' . rtrim($query, ',');

		return $self;
	}

	public static function delete($model){
		$self = new self($model, 'delete');

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

	public function isInsert(){
		if($this->type == 'insert'){
			return TRUE;
		}

		return FALSE;
	}
}
