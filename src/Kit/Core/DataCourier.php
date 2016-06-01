<?php

namespace Kit\Core;

use \Kit\Exception\CoreException;

class DataCourier{
	public $url;
	public $options;
	public $query;

	public function __construct($url, $data = NULL, $options = NULL){
		$this->url = $url;

		$this->setData($data);

		$this->optionsInit($options);
	}

	private function setData($data){
		if(is_array($data)){
			$this->query = '?' . http_build_query($data);
		}
		elseif(is_string($data)){
			$this->query = $data;
		}
	}

	private function optionsInit($options){
		$defualtOptions = [
			// CURLOPT_HTTPHEADER => [],
			CURLOPT_RETURNTRANSFER => TRUE, // return web page
			CURLOPT_HEADER => FALSE, // don't return headers
			CURLOPT_FOLLOWLOCATION => FALSE, // follow redirects
			CURLOPT_MAXREDIRS => 10, // stop after 10 redirects
			// CURLOPT_ENCODING => '', // handle compressed
			CURLOPT_AUTOREFERER => TRUE, // set referrer on redirect
			CURLOPT_CONNECTTIMEOUT => 0, // time-out on connect
			CURLOPT_TIMEOUT => 0, // time-out on response
			CURLOPT_PROXY => FALSE
		];

		$this->options = $defualtOptions;

		if($options){
			foreach($options as $key => $value) {
				$this->setOption($key, $value);
			}
		}

		$this->setOption(CURLOPT_URL, $this->url);
	}

	public function setOption($key, $value){
		$this->options[$key] = $value;
	}

	public function post(){
		$this->setOption(CURLOPT_POST, TRUE);
		$this->setOption(CURLOPT_POSTFIELDS, $this->query);

		return self::curl();
	}

	public function get(){
		if($this->query)
			$this->setOption(CURLOPT_URL, $this->url . $this->query);

		return self::curl();
	}

	private function curl(){
		$ch = curl_init();

		curl_setopt_array($ch, $this->options);

		$result = curl_exec($ch);

		if(curl_errno($ch))
			throw new CoreException('CURL Error: '.curl_error($ch));

		curl_close($ch);

		return $result;
	}
}
