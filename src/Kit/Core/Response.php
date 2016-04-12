<?php

namespace Kit\Core;

class Response{
	private static $code = 202;
	private static $contentType;

	public static function setHeaders(){
		if(!self::$contentType)
			self::setContentType('html');

		http_response_code(self::$code);
		header('Content-Type:' . self::$contentType);

		return headers_list();
	}

	public static function setCode($code){
		self::$code = $code;

		return self::$code;
	}

	public static function setContentType($contentType){
		switch($contentType){
			case 'html':
				self::$contentType = 'text/html';
				break;
			case 'json':
				self::$contentType = 'application/json';
				break;
		}

		return self::$contentType;
	}
}
