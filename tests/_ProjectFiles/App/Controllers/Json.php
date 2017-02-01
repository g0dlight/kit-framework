<?php

namespace Controllers;

use Kit\Controllers\ApiJson;

class Json extends ApiJson
{
	public function getIndex()
    {
		return ['status' => 'ok'];
	}
}