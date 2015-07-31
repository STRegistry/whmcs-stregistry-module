<?php 
require_once dirname(__FILE__) . '/../system/ApiConnectorCURL.php';
require_once dirname(__FILE__) . '/../system/ApiRequest.php';

abstract class Model 
{
	private static $_instances = array();
	private static $_inited = false;

	protected function __construct() 
	{
		if (!self::$_inited) {
			ApiRequest::init(array(
				'api'       => array(
					'host'         => STRegistry::getAPIHost(),
					'port'         => STRegistry::getAPIPort(),
					'ssl'		   => STRegistry::isSSLEnabled(),
					'version'      => STRegistry::getAPIVersion(),
					'userAgent'    => STRegistry::getAPIUserAgent(),
					'content-type' => STRegistry::getAPIContentType(),
				),
				'debug'     => false,
				'connector' => 'ApiConnectorCURL',
			));

			self::$_inited = true;
		}
	}

	public static function getInstance($class = __CLASS__)
	{
		if (empty(self::$_instances[$class]) || !self::$_instances[$class] instanceof $class) {
			self::$_instances[$class] = new $class();
		}

		return self::$_instances[$class];
	}

	public function __destruct()
	{
		
	}
}