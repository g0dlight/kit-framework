<?php

namespace Kit\Database\Interfaces;

Interface QueryBuilder{
	public function scheme($type, $name);

	public function table($name);

	public function select($columns);

	public function update($update);

	public function delete();

	public function where($where);

	public function order($order);

	public function limit($limit);

	public function offset($offset);

	public function build();

	public function execute();
}
