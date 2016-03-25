<?php

namespace Kit;

use \Kit\Exception\CoreException;

class Useful{
	public static function nestedValue(&$reference, $keys=null, $value=null){
		if($keys != null && !is_array($keys))
			$keys = explode('.', $keys);

		if($keys){
			foreach($keys as $key){
				if(!isset($reference[$key])){
					if($value != null){
						if(!is_array($reference))
							$reference = [];

						$reference[$key] = '';
					}
					
					else
						return null;
				}

				$reference = &$reference[$key];
			}
		}

		if($value != null)
			$reference = $value;


		return $reference;
	}
}
