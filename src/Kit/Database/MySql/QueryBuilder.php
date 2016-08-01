<?php

namespace Kit\Database\MySql;

class QueryBuilder{
	private $sql;

	private $scheme;
	private $table;

	private $type;
	private $query;

	private $where = '';
	private $group = '';
	private $order = '';
	private $limit = '';
	private $offset = '';
	private $timestamps = TRUE;

	private $values = [];

	public static $timestampFormat = 'Y-m-d H:i:s';

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
				$row['created_at'] = date(self::$timestampFormat);
				$row['updated_at'] = date(self::$timestampFormat);
			}

			$query .= '(';
			foreach($row as $value){
				$query .= '?,';
				$self->values[] = $value;
			}
			$query = rtrim($query, ',') . '),';
		}

		$columns = '';
		foreach(array_keys($row) as $column){
			$columns .= '`' . $column . '`,';
		}
		$columns = rtrim($columns, ',');

		$self->query = 'INSERT INTO `' . $self->scheme . '`.`' . $self->table . '`';
		$self->query .= '(' . $columns . ')';
		$self->query .= ' VALUES ' . rtrim($query, ',');

		return $self;
	}

	public static function select($model, $columns = NULL){
		$self = new self($model, 'select');

		if(!$columns){
			$query = '*';
		}
		else{
			$query = '';
			foreach($columns as $column){
				$function = false;
				$as = false;

				if(is_array($column))
					list($column, $as) = $column;

				if(is_array($column))
					list($function, $column) = $column;

				$columnQuery = '`' . $self->table . '`.`' . $column . '`';

				if($function)
					$columnQuery = $function . '(' . $columnQuery . ')';

				$query .= $columnQuery;

				if($as)
					$query .= ' AS ' . $as;

				$query .= ',';
			}
		}

		$self->query = 'SELECT ' . rtrim($query, ',');
		$self->query .= ' FROM `' . $self->scheme . '`.`' . $self->table . '`';

		return $self;
	}

	public static function update($model, $rows){
		$self = new self($model, 'update');

		if($self->timestamps){
			$rows['updated_at'] = date(self::$timestampFormat);
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

	public function where($callback){
		$where = new WhereBuilder($this, $callback);

		$this->where = $where->query;

		return $this;
	}

 	public function group($group){
		$this->group = ' GROUP BY ';

		foreach($group as $column){
			$this->group .= '`' . $this->table . '`.`' . $column . '`,';
		}

		$this->group = rtrim($this->group, ',');

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
		$this->sql = $this->query.$this->where.$this->group.$this->order.$this->limit.$this->offset;
	}

	public function getSyntax(){
		$this->build();

		return $this->sql;
	}

	public function getValues(){
		return $this->values;
	}

	public function getTable(){
		return $this->table;
	}

	public function addValue($value){
		$this->values[] = $value;
	}

	public function isInsert(){
		if($this->type == 'insert'){
			return TRUE;
		}

		return FALSE;
	}

	public function isWithTimestamps(){
		return $this->timestamps;
	}
}
