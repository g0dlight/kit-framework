<?php

namespace Kit\Database\Interfaces;

Interface Manager{
	public function getConnection();

	public function getQueryBuilder();
}
