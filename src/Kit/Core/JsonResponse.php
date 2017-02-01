<?php

namespace Kit\Core;

use Exception;

class JsonResponse
{
	const OK = 'OK';
	const ERROR = 'ERROR';

	public static function ok(array $data = NULL)
    {
		return self::make(self::OK, $data);
	}

	public static function error(Exception $error, array $data = NULL)
    {
		$data['error']['code'] = $error->getCode();
		$data['error']['message'] = $error->getMessage();

		return self::make(self::ERROR, $data);
	}

	private static function make($status, $data = NULL)
    {
		Response::setContentType('json');

		$response = [
			'status' => $status,
		];

		if(is_array($data))
			$response = array_merge($response, $data);

		return json_encode($response);
	}
}
