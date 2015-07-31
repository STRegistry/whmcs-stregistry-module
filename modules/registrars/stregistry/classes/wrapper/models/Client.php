<?php
require_once dirname(__FILE__) . '/Model.php';

class Client extends Model
{
	public static function getInstance($class = __CLASS__)
	{
		return parent::getInstance($class);
	}

	/**
	 * Return registrar profile details
	 * 
	 * @return str json response
	 */
	public function query($cltrid = false)
	{
		$json = APIRequest::GET(sprintf('/clients/%s', STRegistry::Session()->getLogin()), $cltrid ?: APIRequest::defaultClientTransactionID(), STRegistry::Session()->getAuthToken());

		return $json;
	}

	/**
	 * Return registrar billing details
	 * 
	 * @return str json response
	 */
	public function billingRecords($cltrid = false)
	{
		$json = APIRequest::GET(sprintf('/billing/%s', STRegistry::Session()->getLogin()), $cltrid ?: APIRequest::defaultClientTransactionID(), STRegistry::Session()->getAuthToken());

		return $json;
	}
}