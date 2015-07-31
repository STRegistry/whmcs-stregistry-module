<?php

class APIRequest {
	
	private static $_options = array();
	
	public static function Init(array $options) {
		self::$_options = $options;
	}
	
	public static function GET($url, $transaction_id, $auth_token = '', array $params = array(), array $headers = array()) {
		return self::makeRequest('GET', $url, $transaction_id, $auth_token, $headers, $params);
	}
	
	public static function HEAD($url, $transaction_id, $auth_token = '', array $params = array(), array $headers = array()) {
		return self::makeRequest('HEAD', $url, $transaction_id, $auth_token, $headers, $params);
	}
	
	public static function POST($url, $transaction_id, $auth_token = '', array $params = array(), array $post_data = array(), array $headers = array()) {
		return self::makeRequest('POST', $url, $transaction_id, $auth_token, $headers, $params, $post_data);
	}
	
	public static function PUT($url, $transaction_id, $auth_token = '', array $params = array(), array $put_data = array(), array $headers = array()) {
		return self::makeRequest('PUT', $url, $transaction_id, $auth_token, $headers, $params, $put_data);
	}
	
	public static function DELETE($url, $transaction_id, $auth_token = '', array $headers = array()) {
		return self::makeRequest('DELETE', $url, $transaction_id, $auth_token, $headers);
	}
	
	private static function makeRequest($method, $url, $transaction_id, $auth_token = '', array $headers = array(), array $params = array(), array $post_params = array()) {
		
		if (empty(self::$_options['connector'])) {
			throw new APIClientException('Connector class doesn\'t specified');
		}
		
		/**
		 * Connector
		 * @var APIConnectorCURL
		 */
		$connector = new self::$_options['connector']();

		try {
			
			//prepare query params
			if (is_array($params) && count($params)) {
				$params = http_build_query($params);
				$url = $url . '?' . $params;
			}

			//init connector
			$connector->Open(self::$_options['api']['host'], self::$_options['api']['port'], self::$_options['api']['ssl'])
					  ->Method($method)
					  ->Url($url);
			
			$default_headers = array(
				'Content-Type: ' . @self::$_options['api']['content-type'],
			  	'Api-Version: ' . @self::$_options['api']['version'], 
			  	'Api-UserAgent: ' . @self::$_options['api']['userAgent'] ?: '',
				'Api-ClientTransactionId: ' . $transaction_id,
			);
			
			if (!empty($auth_token)) {
				array_push($default_headers, 'Api-ClientToken: ' . $auth_token);	
			}
			
			if (is_array($headers) && count($headers)) {
				$default_headers = array_merge($default_headers, $headers);
			}
			
			//set headers
			$connector->Headers($default_headers);

			//set post data 
			if (is_array($post_params) && count($post_params)) {
				$connector->Data(self::prepareRequestData($post_params));
			}
			
			//send request
			if (!isset(self::$_options['debug']) || self::$_options['debug'] == false) {
				$data = $connector->Send(true);
			} else {
				$data = $connector->Debug();
			}
			$connector->Close();
			return $data;
			
		} catch (ConnectorException $e) {
			throw new APIClientException($e->getMessage(), $e->getCode());
		}
	}
	
	
	private static function prepareRequestData(array $data) {
		if (self::$_options['api']['content-type'] == 'application/json') {
			return json_encode($data);
		}
	}
	
	private static function prepareResponseData($response_data) {
		if (self::$_options['api']['content-type'] == 'application/json') {
			return json_decode($response_data);
		}
	}

	public static function defaultClientTransactionID()
	{
		return md5(uniqid());
	}
}



class APIClientException extends Exception {
	
}
?>