<?php
require_once dirname(__FILE__) . '/Model.php';
require_once dirname(__FILE__) . '/../objects/Domain.php';

class Domains extends Model
{
	public static function getInstance($class = __CLASS__)
	{
		return parent::getInstance($class);
	}

	/**
	 * Check if domain already exist at ST Registry
	 * 
	 * @param str $domainName
	 * 
	 * @return boolean
	 */
	public function exist($domainName, $cltrid = false) 
	{
		$json = ApiRequest::GET(sprintf("/domains/%s/check", $domainName), $cltrid ?: ApiRequest::defaultClientTransactionID(), STRegistry::Session()->getAuthToken());
		$response = CommonFunctions::fromJSON($json);

		return (bool)!$response->result['avail'];
	}

	/**
	 * Perform domain whois query
	 * 
	 * @param str $domainName
	 * 
	 * @return str json whois response
	 */
	public function whois($domainName, $cltrid = false)
	{
		return ApiRequest::GET(sprintf("/whois/%s", $domainName), $cltrid ?: ApiRequest::defaultClientTransactionID(), STRegistry::Session()->getAuthToken());
	}

	/**
	 * Fetch domain info
	 * 
	 * @param $domainName
	 * @param $authCode if specify will try to query domain using this authCode even if domain is not owned by registrar
	 * 
	 * @return str json info respose
	 */
	public function query($domainName, $authCode = null, $cltrid = false)
	{
		if (!empty($authCode)) {
			$data = array(
				'authInfo' => array(
					'pw' => $authCode,
				),
			);
			$json = APIRequest::POST(sprintf("/domains/%s/epp", $domainName), $cltrid ?: ApiRequest::defaultClientTransactionID(), STRegistry::Session()->getAuthToken(), array(), $data);
		} else {
			$json = APIRequest::GET(sprintf("/domains/%s", $domainName), $cltrid ?: ApiRequest::defaultClientTransactionID(), STRegistry::Session()->getAuthToken());
		}

		return $json;
	}

	/**
	 * Perform renew operation
	 * 
	 * @param str $domainName
	 * @param int $period Renew period in years
	 * @param 
	 * 
	 * @return str json response
	 */
	public function renew($domainName, $period, $currentExpirationDate, $cltrid = false)
	{
		$data = array(
			'period' => array(
				'unit'  => 'y',
				'value' => (int)$period,
			),
			'curExpDate' => CommonFunctions::dateFormat($currentExpirationDate, 'c'),
		);
		$json = APIRequest::POST(sprintf("/domains/%s/renew", $domainName), $cltrid ?: ApiRequest::defaultClientTransactionID(), STRegistry::Session()->getAuthToken(), array(), $data);

		return $json;
	}

	/**
	 * Register new domain
	 * 
	 * @param Domain $domain prepared Domain object
	 * @param int registrationPeriod registration period in years
	 * 
	 * @return str json response
	 */
	public function create(Domain $domain, $registrationPeriod, $cltrid = false)
	{
		$domain->setRegistationPeriod($registrationPeriod);
		$requestData = $this->getRequestData(__FUNCTION__, $domain);
		$json = APIRequest::PUT("/domains/", $cltrid ?: ApiRequest::defaultClientTransactionID(), STRegistry::Session()->getAuthToken(), array(), $requestData);

		return $json;
	}

	/**
	 * Perform domain update operation
	 * 
	 * @param Domain $domain modified Domain object
	 * 
	 * @return str json response
	 */
	public function update(Domain $domain, $cltrid = false)
	{
		$requestData = $this->getRequestData(__FUNCTION__, $domain);
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
		$json = APIRequest::POST(sprintf("/domains/%s", $domain->getName()), $cltrid ?: ApiRequest::defaultClientTransactionID(), STRegistry::Session()->getAuthToken(), array(), $requestData);

		return $json;
	}

	/**
	 * Performs remote domain validation
	 * 
	 * @param Domain $domain
	 * @return str response json
	 */
	public function validate(Domain $domain, $cltrid = false)
	{
		$requestData = $this->getRequestData(__FUNCTION__, $domain);
		$json = APIRequest::POST(sprintf("/domains/%s/validate", $domain->getName()), $cltrid ?: ApiRequest::defaultClientTransactionID(), STRegistry::Session()->getAuthToken(), array(), $requestData);

		return $json;
	}

	/**
	 * delete domain
	 * 
	 * @param str $domainName
	 * 
	 * @return str json response
	 */
	public function delete($domainName, $cltrid = false)
	{
		$json = APIRequest::DELETE(sprintf("/domains/%s", $domainName), $cltrid ?: ApiRequest::defaultClientTransactionID(), STRegistry::Session()->getAuthToken());

		return $json;
	}


	/**
	 * Request domain transfer
	 * 
	 * @param str $domainName
	 * @param int $renewalYears
	 * @param str $authCode
	 * 
	 * @return str json response
	 */
	public function transferRequest($domainName, $renewalYears, $authCode, $cltrid = false)
	{
		$requestData = array(
			'op' => 'request',
			'period' => array(
				'unit' => 'y',
				'value' => $renewalYears,
			),
			'authInfo' => array(
				'pw' => $authCode,
			)
		);
		$json = APIRequest::POST(sprintf("/domains/%s/transfer", $domainName), $cltrid ?: ApiRequest::defaultClientTransactionID(), STRegistry::Session()->getAuthToken(), array(), $requestData);

		return $json;
	}


	/**
	 * Query domain transfer info
	 * 
	 * @param str $domainName
	 * @param str $authCode
	 * 
	 * @return str json response
	 */
	public function transferQuery($domainName, $authCode, $cltrid = false)
	{
		$requestData = array(
			'op' => 'query',
			'authInfo' => array(
				'pw' => $authCode,
			)
		);
		$json = APIRequest::POST(sprintf("/domains/%s/transfer", $domainName), $cltrid ?: ApiRequest::defaultClientTransactionID(), STRegistry::Session()->getAuthToken(), array(), $requestData);

		return $json;
	}

	/**
	 * Cancel domain transfer
	 * 
	 * @param str $domainName
	 * 
	 * @return str json response
	 */
	public function transferCancel($domainName, $cltrid = false)
	{
		$requestData = array(
			'op' => 'cancel',
		);
		$json = APIRequest::POST(sprintf("/domains/%s/transfer", $domainName), $cltrid ?: ApiRequest::defaultClientTransactionID(), STRegistry::Session()->getAuthToken(), array(), $requestData);

		return $json;
	}

	/**
	 * Reject domain transfer
	 * 
	 * @param str $domainName
	 * 
	 * @return str json response
	 */
	public function transferReject($domainName, $cltrid = false)
	{
		$requestData = array(
			'op' => 'reject',
		);
		$json = APIRequest::POST(sprintf("/domains/%s/transfer", $domainName), $cltrid ?: ApiRequest::defaultClientTransactionID(), STRegistry::Session()->getAuthToken(), array(), $requestData);

		return $json;
	}

	/**
	 * Approve domain transfer
	 * 
	 * @param str $domainName
	 * 
	 * @return str json response
	 */
	public function transferApprove($domainName, $cltrid = false)
	{
		$requestData = array(
			'op' => 'approve',
		);
		$json = APIRequest::POST(sprintf("/domains/%s/transfer", $domainName), $cltrid ?: ApiRequest::defaultClientTransactionID(), STRegistry::Session()->getAuthToken(), array(), $requestData);

		return $json;
	}

	/**
	 * return domain transfers hitory related to current domain owner
	 * 
	 * @param str $domainName
	 * 
	 * @return str json response
	 */
	public function transfersHistory($domainName, $cltrid = false)
	{
		$json = APIRequest::GET(sprintf("/domains/%s/transfers", $domainName), $cltrid ?: ApiRequest::defaultClientTransactionID(), STRegistry::Session()->getAuthToken());

		return $json;
	}

	/**
	 * Set domain privacy contacts
	 * 
	 * @param str $domainName
	 * @param str $registrantId
	 * @param str $adminId
	 * @param str $techId
	 * @param str $billingId
	 * 
	 * @return str json response
	 */
	public function setPrivacy($domainName, $registrantId = null, $adminId = null, $techId = null, $billingId = null, $cltrid = false)
	{
		$json = $this->query($domainName);
		$domain = Domain::fromJSON($json);

		$domain->setPrivacyContacts($registrantId, $adminId, $techId, $billingId);
		$json = $this->update($domain, $cltrid);

		return $json;
	}

	/**
	 * Remove domain privacy
	 * 
	 * @return str json response
	 */
	public function removePrivacy($domainName, $cltrid = false)
	{	
		return $this->setPrivacy($domainName, '', '', '', '', $cltrid);
	}



	private function getRequestData($action, Domain $domain)
	{
		switch ($action) {
			case 'create':
			case 'validate':
				return $this->getRequestDataForCreate($domain);
			break;
			case 'update':
				return $this->getRequestDataForUpdate($domain);
			break;
		}
	}

	private function getRequestDataForCreate(Domain $domain)
	{
		$data = array(
			'name' => $domain->getName(),
			'period' => array(
				'unit' => 'y',
				'value' => $domain->getRegistrationPeriod()
			),
			'contacts' => array(
				Domain::CONTACT_TYPE_ADMIN      => $domain->getAdminContactId(),
				Domain::CONTACT_TYPE_TECH       => $domain->getTechContactId(),
				Domain::CONTACT_TYPE_BILLING    => $domain->getBillingContactId(),
				Domain::CONTACT_TYPE_REGISTRANT => $domain->getRegistrantContactId(),
			),
			'ns'       => array(),
			'authInfo' => array(
				'pw' => $domain->getAuthCode(),
			),
			'status'   => $domain->getStatuses(),
		);

		foreach ($domain->getNameServers() as $hostname => $ips) {
			$nsData = array(
				'name' => $hostname,
			);
			foreach ($ips as $ip) {
				if ($type = CommonFunctions::detectIPType($ip)) {
					if (!isset($nsData['addr'])) {
						$nsData['addr'] = array();
					}
					$nsData['addr'][] = array($type => $ip);
				}
			}
			$data['ns'][] = $nsData;
		}

		if ($private = $domain->getRegistrantPrivacyContactId()) {
			$data['contacts'][Domain::CONTACT_TYPE_REGISTRANT_PRIVACY] = $private;
		}

		if ($private = $domain->getAdminPrivacyContactId()) {
			$data['contacts'][Domain::CONTACT_TYPE_ADMIN_PRIVACY] = $private;
		}

		if ($private = $domain->getTechPrivacyContactId()) {
			$data['contacts'][Domain::CONTACT_TYPE_TECH_PRIVACY] = $private;
		}

		if ($private = $domain->getBillingPrivacyContactId()) {
			$data['contacts'][Domain::CONTACT_TYPE_BILLING_PRIVACY] = $private;
		}
		return $data;
	}

	/**
	 * 
	 */
	public function getRequestDataForUpdate(Domain $domain)
	{
		$data = array(

		);

		$json = STRegistry::Domains()->query($domain->getName());
		$currentDomain = Domain::fromJSON($json);

		if ($currentDomain->getAuthCode() != $domain->getAuthCode()) {
			if (!isset($data['chg'])) {
				$data['chg'] = array();
			}
			$data['chg']['authInfo'] = array(
				'pw' => $domain->getAuthCode(),
			);
		}

		foreach ($currentDomain->getContacts() as $type => $contact) {
			$newContacts = $domain->getContacts();
			if ($newContacts[$type] !== false && $newContacts[$type] != $contact) {
				if (!isset($data['rem']['contact'])) {
					$data['rem']['contact'] = array();
				}
				if (!isset($data['add']['contact'])) {
					$data['add']['contact'] = array();
				}

				$data['rem']['contact'][] = array($type => $contact);
				if (!empty($newContacts[$type])) {
					$data['add']['contact'][] = array($type => $newContacts[$type]);	
				}
			}
		}

		foreach ($domain->getNameServers() as $nameserver => $ips) {
			if (!in_array($nameserver, array_keys($currentDomain->getNameServers()))) {
				if (!isset($data['add']['ns'])) {
					$data['add']['ns'] = array();
				}
				$upd = array(
					'name' => $nameserver,
				);
				if (count($ips)) {
					$upd['addr'] = array();
					foreach ($ips as $ip) {
						$upd['addr'][] = array(CommonFunctions::detectIPType($ip) => $ip);
					}
				}
				$data['add']['ns'][] = $upd;
			}
		}

		foreach ($currentDomain->getNameServers() as $nameserver => $ips) {
			if (!in_array($nameserver, array_keys($domain->getNameServers()))) {
				if (!isset($data['rem']['ns'])) {
					$data['rem']['ns'] = array();
				}
				$data['rem']['ns'][] = $nameserver;
			}
		}

		foreach ($domain->getStatuses() as $status) {
			if (!in_array($status, array(Domain::STATUS_HOLD, Domain::STATUS_DELETE_PROHIBITED, Domain::STATUS_RENEW_PROHIBITED, Domain::STATUS_TRANSFER_PROHIBITED, Domain::STATUS_UPDATE_PROHIBITED))) {
				continue;
			}
			if (!in_array($status, $currentDomain->getStatuses())) {
				if (!isset($data['add']['status'])) {
					$data['add']['status'] = array();
				}
				$data['add']['status'][] = $status;
			}
		}
		foreach ($currentDomain->getStatuses() as $status) {
			if (!in_array($status, array(Domain::STATUS_HOLD, Domain::STATUS_DELETE_PROHIBITED, Domain::STATUS_RENEW_PROHIBITED, Domain::STATUS_TRANSFER_PROHIBITED, Domain::STATUS_UPDATE_PROHIBITED))) {
				continue;
			}
			if (!in_array($status, $domain->getStatuses())) {
				if (!isset($data['rem']['status'])) {
					$data['rem']['status'] = array();
				}
				$data['rem']['status'][] = $status;
			}
		}

		return $data;
	}

	/**
	 * Make search over registrar domains collection
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

		$json = APIRequest::GET('/domains/', $cltrid ?: APIRequest::defaultClientTransactionID(), STRegistry::Session()->getAuthToken(), $get);

		return $json;
 	}
}