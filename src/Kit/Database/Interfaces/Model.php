<?php

namespace Kit\Database\Interfaces;

Interface Model{
	public function __construct();

	public function find($find);

	public function save();

	public function delete();
}
