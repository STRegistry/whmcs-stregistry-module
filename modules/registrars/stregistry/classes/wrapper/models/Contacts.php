<?php 
require_once dirname(__FILE__) . '/Model.php';
require_once dirname(__FILE__) . '/../objects/Contact.php';

class Contacts extends Model
{
	public static function getInstance($class = __CLASS__)
	{
		return parent::getInstance($class);
	}

	/**
	 * Check if contact already exist at ST Registry
	 * 
	 * @param str $contactId
	 * 
	 * @return boolean
	 */
	public function exist($contactId, $cltrid = false) 
	{
		$json = ApiRequest::GET(sprintf("/contacts/%s/check", $contactId), $cltrid ?: ApiRequest::defaultClientTransactionID(), STRegistry::Session()->getAuthToken());
		$response = CommonFunctions::fromJSON($json);

		return (bool)!$response->result['avail'];
	}

	/**
	 * Performs remote contact validation
	 * 
	 * @param Domain $contact
	 * @return str response json
	 */
	public function validate(Contact $contact, $cltrid = false)
	{
		$requestData = $this->getRequestData(__FUNCTION__, $contact);
		$json = APIRequest::POST(sprintf("/contacts/%s/validate", $contact->getContactId()), $cltrid ?: ApiRequest::defaultClientTransactionID(), STRegistry::Session()->getAuthToken(), array(), $requestData);

		return $json;
	}

	/**
	 * Create new contact
	 * 
	 * @param Contact $contact prepared Contact object
	 * 
	 * @return str json response
	 */
	public function create(Contact $contact, $cltrid = false)
	{
		$requestData = $this->getRequestData(__FUNCTION__, $contact);
		$json = APIRequest::PUT("/contacts/", $cltrid ?: ApiRequest::defaultClientTransactionID(), STRegistry::Session()->getAuthToken(), array(), $requestData);

		return $json;
	}

	/**
	 * Fetch contact info
	 * 
	 * @param $contactId
	 * 
	 * @return str json info respose
	 */
	public function query($contactId, $cltrid = false)
	{
		$json = APIRequest::GET(sprintf("/contacts/%s", $contactId), $cltrid ?: ApiRequest::defaultClientTransactionID(), STRegistry::Session()->getAuthToken());

		return $json;
	}

	/**
	 * Perform contact update operation
	 * 
	 * @param Contact $contact modified Contact object
	 * 
	 * @return str json response
	 */
	public function update(Contact $contact, $cltrid = false)
	{
		$requestData = $this->getRequestData(__FUNCTION__, $contact);
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
		$json = APIRequest::POST(sprintf("/contacts/%s", $contact->getContactId()), $cltrid ?: ApiRequest::defaultClientTransactionID(), STRegistry::Session()->getAuthToken(), array(), $requestData);

		return $json;
	}

	/**
	 * Delete contact
	 * 
	 * @param $contactId
	 * 
	 * @return str json info respose
	 */
	public function delete($contactId, $cltrid = false)
	{
		$json = APIRequest::DELETE(sprintf("/contacts/%s", $contactId), $cltrid ?: ApiRequest::defaultClientTransactionID(), STRegistry::Session()->getAuthToken());

		return $json;
	}

	private function getRequestData($action, Contact $contact)
	{
		switch ($action) {
			case 'create':
			case 'validate':
				return $this->getRequestDataForCreate($contact);
			break;
			case 'update':
				return $this->getRequestDataForUpdate($contact);
			break;
		}
	}

	private function getRequestDataForCreate(Contact $contact)
	{
		$data = array(
			'id' => $contact->getContactId(),
			'voice' => $contact->getPhoneNumber(),
			'fax' => $contact->getFaxNumber(),
			'email' => $contact->getEmail(),
			'postalInfo' => array(
				Contact::POSTALINFO_INT   => $contact->getPostalInfo(Contact::POSTALINFO_INT),
			),
		);
		if (!empty($contact->getPostalInfo(Contact::POSTALINFO_LOCAL)['name'])) {
			$data['postalInfo'][Contact::POSTALINFO_LOCAL] = $contact->getPostalInfo(Contact::POSTALINFO_LOCAL);
		}
		return $data;
	}

	private function getRequestDataForUpdate(Contact $contact)
	{
		$data = array(
			'chg' => array(
				'voice' => $contact->getPhoneNumber(),
				'fax' => $contact->getFaxNumber(),
				'email' => $contact->getEmail(),
				'postalInfo' => array(
					Contact::POSTALINFO_INT   => $contact->getPostalInfo(Contact::POSTALINFO_INT),
				),
			),
		);
		if (!empty($contact->getPostalInfo(Contact::POSTALINFO_LOCAL)['name'])) {
			$data['chg']['postalInfo'][Contact::POSTALINFO_LOCAL] = $contact->getPostalInfo(Contact::POSTALINFO_LOCAL);
		}

		return $data;
	}

	/**
	 * Make search over registrar contacts collection
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

		$json = APIRequest::GET('/contacts/', $cltrid ?: APIRequest::defaultClientTransactionID(), STRegistry::Session()->getAuthToken(), $get);

		return $json;
 	}
}