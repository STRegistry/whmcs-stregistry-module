<?php
require_once dirname(__FILE__) . '/Model.php';

class Session extends Model
{
	private $_sessionData = array(
		'login'	  => null,
		'token'   => null,
		'created' => 0,
		'expires'  => 0
	);
	/**
	 * Create session at Registry side and receive authToken
	 * 
	 * @param $login - Registrar login
	 * @param $password - Registrar password
	 * 
	 * @return str - Response json
	 */
	public function login($login, $password, $cltrid = false)
	{
		$json = ApiRequest::PUT('/auth', $cltrid ?: ApiRequest::defaultClientTransactionID(), '', array(), array(
			'login'    => $login,
			'password' => $password,
		));

		$response = CommonFunctions::fromJSON($json);
		if ($response->code == 1000) {
			$this->_sessionData['login']   = $response->result['login'];
			$this->_sessionData['token']   = $response->result['token'];
			$this->_sessionData['created'] = $response->result['created'];
			$this->_sessionData['expires'] = $response->result['expires'];
		}

		return $json;
	}

	/**
	 * Return registrar login
	 * 
	 * @return str
	 */
	public function getLogin()
	{
		return $this->_sessionData['login'] ?: '';
	}

	/**
	 * Return auth token
	 * 
	 * @return str|boolean Return session auth token or false if not exists
	 */
	public function getAuthToken()
	{
		return $this->_sessionData['token'] ?: false;
	}

	/**
	 * Return token expiration date in seconds
	 * 
	 * @return int
	 */
	public function getExpireDateTime()
	{
		return $this->_sessionData['expires'];
	}

	/**
	 * Validates current session at Registry side
	 * 
	 * @return json
	 */
	public function validate($cltrid = false)
	{
		$json = ApiRequest::GET('/auth',  $cltrid ?: ApiRequest::defaultClientTransactionID(), $this->getAuthToken());

		$response = CommonFunctions::fromJSON($json);
		if ($response->code == 1000) {
			$this->_sessionData['expires'] = $response->result['expires'];
		}

		return $json;
	}

	public function logout($cltrid = false)
	{
		$json = ApiRequest::DELETE('/auth',  $cltrid ?: ApiRequest::defaultClientTransactionID(), $this->getAuthToken());

		$this->_sessionData['login']   = null;
		$this->_sessionData['token']   = null;
		$this->_sessionData['created'] = 0;
		$this->_sessionData['expires'] = 0;

		return $json;
	}


	/**
	 * Return class instance
	 * 
	 * @return Session
	 */
	public static function getInstance($class = __CLASS__)
	{
		return parent::getInstance($class);
	}
}