<?php

class CommonFunctions
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
	 * Format specified date using $format or return it timestamp
	 * 
	 * @param mixed $date formated date or timestamp
	 * @param str $format date function suitable date format
	 * 
	 * @return mixed formated date or it timestamp
	 */
	public static function dateFormat($date, $format = false)
	{
		if (!is_numeric($date)) {
			$date = strtotime($date);
		}
		if (!empty($format)) {
			$date = date($format, $date);
		}

		return $date;
	}

	/**
	 * Return type of specified ip address
	 * 
	 * @return str v4 for ipv4, v6 for ipv6, false in other cases
	 */
	public static function detectIPType($ip)
	{
		if ($ip == filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
			return 'v4';
		} else if ($ip == filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
			return 'v6';
		}
		return false;
	}
}