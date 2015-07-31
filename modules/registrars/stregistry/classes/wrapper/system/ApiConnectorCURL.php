<?php

class ApiConnectorCURL {
	
	private $_clHandler = null;
	private $_host = '';
	private $_port = 80;
	private $_ssl  = false;
	
	/**
	 * Init curl connection to specified host:port
	 * 
	 * @param str $host
	 * @param str $port
	 * 
	 * @return APIConnectorCURL
	 */
	public function Open($host, $port = 80, $useSSL = false) {
		$this->_clHandler = curl_init();
		$this->_host = $host;
		$this->_port = $port;
		$this->_ssl  = $useSSL;
		
		return $this;
	}
	
	/**
	 * Set url
	 * 
	 * @param str $url
	 * 
	 * @return APIConnectorCURL 
	 */
	public function Url($url) {
		
		// make sure we have a slash as first char as url
		if ($url[0] != '/') {
			$url = '/' . $url; 
		}
		
		$this->setOption(CURLOPT_URL, ($this->_ssl ? 'https://' : 'http://') . $this->_host . $url);
		$this->setOption(CURLOPT_PORT, $this->_port);
		
		return $this;
	}
	
	/**
	 * Set request headers to be sent
	 * 
	 * @param array $headers
	 * 
	 * @return APIConnectorCURL
	 */
	public function Headers(array $headers) {
		
		$this->setOption(CURLOPT_HTTPHEADER, $headers);
		
		return $this;
	}
	
	/**
	 * 
	 * @param str $method
	 * 
	 * @return APIConnectorCURL
	 */
	public function Method($method = 'GET') {
			
		switch ($method) {
			case 'POST':
				$this->setOption(CURLOPT_CUSTOMREQUEST, 'POST');
			break;
			case 'PUT' :
				$this->setOption(CURLOPT_CUSTOMREQUEST, 'PUT');
			break;
			case 'HEAD' :
				$this->setOption(CURLOPT_CUSTOMREQUEST, 'HEAD');
			break;
			case 'OPTIONS' :
				$this->setOption(CURLOPT_CUSTOMREQUEST, 'OPTIONS');
			break;
			case 'DELETE' :
				$this->setOption(CURLOPT_CUSTOMREQUEST, 'DELETE');
			break;
			case 'GET' :
			default :
				$this->setOption(CURLOPT_CUSTOMREQUEST, 'GET');
			break;
		}
		
		return $this;
	}
	
	/**
	 * 
	 * @param mixed $data
	 * 
	 * @return APIConnectorCURL
	 */
	public function Data($data) {
		
		$this->setOption(CURLOPT_POSTFIELDS, $data);
		
		return $this;
	}
	
	/**
	 * Shows entire RAW curl request
	 * 
	 * @param $send - if set to true - sends request to server and shows RAW response
	 * 
	 * @return mixed
	 */
	public function Debug() {		
		echo "\nTransfer Info: \n";
		
		$this->setOption(CURLOPT_VERBOSE , true);
		$data = $this->Send(true);
		
		echo "\nData Recieved:\n" . $data . "\n";
		echo "\nInfo:\n" . var_export(curl_getinfo($this->_clHandler), true) . "\n";
		
		return $data;
	}
	
	/**
	 * Send request to sever
	 * @param boolean $return_transfer
	 * 
	 * @return mixed
	 */
	public function Send($return_transfer = true) {
		
		if ($return_transfer == true) {
			$this->setOption(CURLOPT_RETURNTRANSFER, true);
		}
		
		if (($data = curl_exec($this->_clHandler)) === false) {
			$this->CURLException();
		}
		
		return $data;
	}
	
	/**
	 * Close connection with server
	 * 
	 * @return void;
	 */
	public function Close() {
		
		curl_close($this->_clHandler);
		
		return ;
	}
	
	/**
	 * Set curl option
	 * 
	 * @param int $option
	 * @param mixed $value
	 * 
	 * @return boolean
	 */
	private function setOption($option, $value) {
		$succeed = curl_setopt($this->_clHandler, $option, $value);
		if ($succeed !== true) {
			$this->CURLException();
		}
		
		return true;
	}
	
	/**
	 * Throws specific exception
	 * 
	 * @throws ConnectorException
	 */
	private function CURLException() {
		throw new ConnectorException(curl_error($this->_clHandler), curl_errno($this->_clHandler));
	}
}

class ConnectorException extends Exception 
{
	
}