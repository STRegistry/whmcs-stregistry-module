<?php

class ResponseHelper
{
	/**
	 * Converts json api response to formated object
	 * 
	 * @param str $jsonString string in JSON format
	 * @param str $setResultFrom respose field from which result object param will be set
	 * 
	 * @return stdClass
	 * 
	 * $obj->code - api response code
	 * $obj->message - api response code
	 * $obj->cltrid - api client transaction id
	 * $obj->svtrid - api server transaction id
	 * $obj->result - other data from response or from $setResultFrom field if specified
	 */
	public static function fromJSON($jsonString, $setResultFrom = false)
	{
		$json = json_decode($jsonString, true);
		
		$obj = new stdClass();
		$obj->code    = $json['code'];
		$obj->message = $json['message'];
		$obj->svtrid  = $json['svtrid'];
		$obj->cltrid  = $json['cltrid'];

		unset($json['code'], $json['message'], $json['svtrid'], $json['cltrid']);
		
		if ($setResultFrom && isset($json[$setResultFrom])) {	
			$obj->result = $json[$setResultFrom];
		} else  {
			$obj->result = $json;
		}

		return $obj;
	}

	/**
	 * Check if operation was succeed
	 * 
	 * @param str $jsonString operation response
	 * 
	 * @return bool
	 */
	public static function isSuccess($jsonString)
	{
		$json = ResponseHelper::fromJSON($jsonString);

		return $json->code == 1000;
	}
}