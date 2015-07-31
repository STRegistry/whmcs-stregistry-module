<?php
require_once dirname(__FILE__) . '/Model.php';
require_once dirname(__FILE__) . '/../objects/Host.php';

class Hosts extends Model
{
	public static function getInstance($class = __CLASS__)
	{
		return parent::getInstance($class);
	}
	/**
	 * Check if host already exist at ST Registry
	 * 
	 * @param str $hostname
	 * 
	 * @return boolean
	 */
	public function exist($hostname, $cltrid = false) 
	{
		$json = ApiRequest::GET(sprintf("/hosts/%s/check", $hostname), $cltrid ?: ApiRequest::defaultClientTransactionID(), STRegistry::Session()->getAuthToken());
		$response = CommonFunctions::fromJSON($json);

		return (bool)!$response->result['avail'];
	}

	/**
	 * Create new host
	 * 
	 * @param Host $host prepared Host object
	 * 
	 * @return str json response
	 */
	public function create(Host $host, $cltrid = false)
	{
		$requestData = $this->getRequestData(__FUNCTION__, $host);
		$json = APIRequest::PUT("/hosts/", $cltrid ?: ApiRequest::defaultClientTransactionID(), STRegistry::Session()->getAuthToken(), array(), $requestData);

		return $json;
	}

	/**
	 * Fetch host info
	 * 
	 * @param $hostname
	 * 
	 * @return str json info respose
	 */
	public function query($hostname, $cltrid = false)
	{
		$json = APIRequest::GET(sprintf("/hosts/%s", $hostname), $cltrid ?: ApiRequest::defaultClientTransactionID(), STRegistry::Session()->getAuthToken());

		return $json;
	}

	/**
	 * Perform host update operation
	 * 
	 * @param Host $host modified Host object
	 * 
	 * @return str json response
	 */
	public function update(Host $host, $cltrid = false)
	{
		$requestData = $this->getRequestData(__FUNCTION__, $host);
		// do not send api request if object state is the same
		if (!count($requestData['add']) && !count($requestData['rem']) && !count($requestData['chg'])) {
			return json_encode(array(
				"code"    => 1000,
				"message" =>  "OK; WARNING: No changes has been made;",
				"cltrid"  => $cltrid ?: ApiRequest::defaultClientTransactionID(),
				"svtrid"  => "NO_TRANSACTION",
				"time"    => 0,
			));
		}
		$json = APIRequest::POST(sprintf("/hosts/%s", $host->getName()), $cltrid ?: ApiRequest::defaultClientTransactionID(), STRegistry::Session()->getAuthToken(), array(), $requestData);

		return $json;
	}

	/**
	 * Delete host
	 * 
	 * @param $hostname
	 * 
	 * @return str json info respose
	 */
	public function delete($hostname, $cltrid = false)
	{
		$json = APIRequest::DELETE(sprintf("/hosts/%s", $hostname), $cltrid ?: ApiRequest::defaultClientTransactionID(), STRegistry::Session()->getAuthToken());

		return $json;
	}

	private function getRequestData($action, Host $host)
	{
		switch ($action) {
			case 'create':
			case 'validate':
				return $this->getRequestDataForCreate($host);
			break;
			case 'update':
				return $this->getRequestDataForUpdate($host);
			break;
		}
	}

	private function getRequestDataForCreate(Host $host)
	{
		$data = array(
			'name' => $host->getName(),
			'addr' => array(),
		);
		foreach ($host->getIPv4() as $addr) {
			$data['addr'][] = array(
				Host::IP_VERSION_4 => $addr,
			);
		}

		foreach ($host->getIPv6() as $addr) {
			$data['addr'][] = array(
				Host::IP_VERSION_6 => $addr,
			);	
		}
		return $data;
	}

	private function getRequestDataForUpdate(Host $host)
	{
		$data = array(
			'add' => array(),
			'rem' => array(),	
		);

		$json = STRegistry::Hosts()->query($host->getName());
		$currentHost = Host::fromJSON($json);

		foreach (array_merge($host->getIPv4(),$host->getIPv6()) as $id => $ip) {
			if (!in_array($ip, array_merge($currentHost->getIPv4(),$currentHost->getIPv6()))) {
				if (!isset($data['add']['addr'])) {
					$data['add']['addr'] = array();
				}
				$data['add']['addr'][] = array($id => array('version' => CommonFunctions::detectIPType($ip), 'address' => $ip));
			}
		}
		foreach (array_merge($currentHost->getIPv4(),$currentHost->getIPv6()) as $id => $ip) {
			if (!in_array($ip, array_merge($host->getIPv4(),$host->getIPv6()))) {
				if (!isset($data['rem']['addr'])) {
					$data['rem']['addr'] = array();
				}
				$data['rem']['addr'][] = array($id => array('version' => CommonFunctions::detectIPType($ip), 'address' => $ip));
			}
		}
		foreach ($host->getStatuses() as $status) {
			if (!in_array($status, $currentHost->getStatuses())) {
				if (!isset($data['add']['status'])) {
					$data['add']['status'] = array();
				}
				$data['add']['status'][] = $status;
			}
		}
		foreach ($currentHost->getStatuses() as $status) {
			if (!in_array($status, $host->getStatuses())) {
				if (!isset($data['rem']['status'])) {
					$data['rem']['status'] = array();
				}
				$data['rem']['status'][] = $status;
			}
		}

		return $data;
	}

	/**
	 * Make search over registrar hosts collection
	 * 
	 * @param SearchCriteria $criteria  Prepared search filters
	 * @param int $limit results limit	
	 * @param int $offset start rowset from
	 * @param array $sort rowset sort rule array('field' => 'asc|desc')
	 * 
	 * @return str json response
	 */
	public function search(SearchCriteria $criteria, $limit = 100, $offset = 0, array $sort = array(), $cltrid = false)
	{
		$get = $criteria->getCriteria();
		$get['do']     = 'search';
		$get['limit']  = $limit;
		$get['offset'] = $offset;

		foreach ($sort as $field => $direction) {
			$get['sort_field']     = $field;
			$get['sort_direction'] = $direction;
			// sorry. just one field
			break;
		}

		$json = APIRequest::GET('/hosts/', $cltrid ?: APIRequest::defaultClientTransactionID(), STRegistry::Session()->getAuthToken(), $get);

		return $json;
 	}
}