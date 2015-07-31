<?php
require_once dirname(__FILE__) . '/system/CommonFunctions.php';
require_once dirname(__FILE__) . '/models/Session.php';
require_once dirname(__FILE__) . '/models/Domains.php';
require_once dirname(__FILE__) . '/models/Hosts.php';
require_once dirname(__FILE__) . '/models/Contacts.php';
require_once dirname(__FILE__) . '/models/Poll.php';
require_once dirname(__FILE__) . '/models/Client.php';

class STRegistry
{
	/**
	 * STRegistry REST API host
	 * 
	 * @var str
	 */
	private static $_apiHost = '';
	/**
	 * STRegistry REST API port
	 * 
	 * @var int
	 */
	private static $_apiPort = 80;
	
	/**
	 * STRegistry REST API version
	 * 
	 * @var int
	 */
	private static $_apiVersion = '1.0';

	/**
	 * STRegistry will use secure connect or not
	 * 
	 * @var boolean
	 */
	private static $_apiUseSSL  = false;

	/**
	 * STRegsitry api useragent
	 * 
	 * @var str
	 */
	private static $_apiUserAgent = '';

	/**
	 * STRegistry REST API version
	 * 
	 * @var int
	 */
	private static $_apiContentType = 'application/json';

	/**
	 * Session model object
	 * 
	 * @var Session
	 */
	private static $_session  = null;
	/**
	 * Domains model object 
	 * 
	 * @var Domains
	 */
	private static $_domains  = null;
	/**
	 * Hosts model object 
	 * 
	 * @var Hosts
	 */
	private static $_hosts    = null;
	/**
	 * Contacts model object
	 * 
	 * @var Contacts
	 */
	private static $_contacts = null;

	/**
	 * Initialization
	 * 
	 * @param str $apiHost REST API host
	 * @param int $apiPort REST API port
	 * @return void;
	 */
	public static function Init($apiHost, $apiPort = 80, $useSSL = false, $apiVersion='1.0', $apiUserAgent = 'RESTAPI-PHP-WRAPPER', $apiContentType='application/json')
	{	
		self::$_apiHost        = $apiHost;
		self::$_apiPort        = $apiPort;
		self::$_apiUseSSL      = $useSSL;
		self::$_apiVersion     = $apiVersion;
		self::$_apiUserAgent   = $apiUserAgent;
		self::$_apiContentType = $apiContentType;

		return;
	}

	/**
	 * Return rest api url
	 * 
	 * @return str
	 */
	public static function getAPIHost()
	{
		return self::$_apiHost;
	}

	/**
	 * Return rest api port 
	 * 
	 * @return int
	 */
	public static function getAPIPort()
	{
		return self::$_apiPort ?: 80;
	}

	/**
	 * Return secure connection option
	 * 
	 * @return str
	 */
	public static function isSSLEnabled()
	{
		return self::$_apiUseSSL;
	}

	/**
	 *	Return rest api version
	 * 
	 * @return str 
	 */
	public static function getAPIVersion()
	{
		return self::$_apiVersion;
	}

	/**
	 * Return api useragent option
	 * 
	 * @return str
	 */
	public static function getAPIUserAgent()
	{
		return self::$_apiUserAgent;
	}

	/**
	 * Return api content-type
	 * 
	 * @return string
	 */
	public static function getAPIContentType()
	{
		return self::$_apiContentType;
	}

	/**
	 * Return Session class instance
	 * 
	 * @return Session
	 */
	public static function Session()
	{
		return Session::getInstance();
	}

	/**
	 * Return Domains class instance
	 * 
	 * @return Domains
	 */
	public static function Domains() 
	{
		return Domains::getInstance();
	}

	/**
	 * Return Hosts class instance
	 * 
	 * @return Hosts
	 */
	public static function Hosts()
	{
		return Hosts::getInstance();
	}

	/**
	 * Return Contacts class instance
	 * 
	 * @return Contacts
	 */
	public static function Contacts()
	{
		return Contacts::getInstance();
	}

	/**
	 * Return Poll class instance
	 * 
	 * @return Poll
	 */
	public static function Poll()
	{
		return Poll::getInstance();
	}

	/**
	 * Returm Client class instance
	 * 
	 * @return Client
	 */
	public static function Client()
	{
		return Client::getInstance();
	}
}

class STRegistryException extends Exception 
{

}