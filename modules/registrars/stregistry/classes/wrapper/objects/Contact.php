<?php

require_once dirname(__FILE__) . '/BaseObject.php';

class Contact extends BaseObject
{

	const POSTALINFO_INT   = 'int';
	const POSTALINFO_LOCAL = 'loc';
	/**
	 * Contact ID
	 * 
	 * @var str
	 */
	private $_id = '';

	/**
	 * Contact phone number
	 * 
	 * @var str
	 */
	private $_phone = '';

	/**
	 * Contact fax number
	 * 
	 * @var str
	 */
	private $_fax = '';

	/**
	 * Contact email
	 * 
	 * @var str
	 */
	private $_email = '';

	private $_postalInfo = array(
		Contact::POSTALINFO_INT   => array('addr' => array()),
		Contact::POSTALINFO_LOCAL => array('addr' => array()),
	);

	public function __construct($id = '') 
	{
		$this->setContactId($id);
	}

	/**
	 * Set contact id
	 * 
	 * @param str $id contact id
	 * 
	 * @return Contact
	 */
	public function setContactId($id)
	{
		$this->_id = $id;

		return $this;
	}

	/**
	 * Return contact id
	 * 
	 * @return str
	 */
	public function getContactId()
	{
		return $this->_id;
	}

	/**
	 * Set contact phone number
	 * 
	 * @param str $phoneNumber
	 * 
	 * @return Contact
	 */
	public function setPhoneNumber($phoneNumber)
	{
		$this->_phone = $phoneNumber;

		return $this;
	}

	/**
	 * Return contact phone number
	 * 
	 * @return str
	 */
	public function getPhoneNumber()
	{
		return $this->_phone;
	}

	/**
	 * Set contact fax number
	 * 
	 * @param str $faxNumber
	 * 
	 * @return Contact
	 */
	public function setFaxNumber($faxNumber)
	{
		$this->_fax = $faxNumber;

		return $this;
	}

	/**
	 * Return contact fax number
	 * 
	 * @return str
	 */
	public function getFaxNumber()
	{
		return $this->_fax;
	}

	/**
	 * Set contact email
	 * 
	 * @param str $email
	 * 
	 * @return Contact
	 */
	public function setEmail($email)
	{
		$this->_email = $email;

		return $this;
	}

	/**
	 * Return contact email
	 *  
	 * @return str
	 */
	public function getEmail()
	{
		return $this->_email;
	}

	/**
	 * Set contact postal info name
	 * 
	 * @param str $name 
	 * @param str $type Contact type int|loc
	 * 
	 * @return Contact
	 */
	public function setName($name, $type = Contact::POSTALINFO_INT)
	{
		$this->_postalInfo[$type]['name'] = $name;

		return $this;
	}

	/**
	 * Return contact postal info name
	 * 
	 * @param $type Contact type int|loc
	 * 
	 * @return str
	 */
	public function getName($type = Contact::POSTALINFO_INT)
	{
		return $this->_postalInfo[$type]['name'];
	}

	/**
	 * Set contact postal info organization
	 * 
	 * @param str $organization 
	 * @param str $type Contact type int|loc
	 * 
	 * @return Contact
	 */
	public function setOrganization($organization, $type = Contact::POSTALINFO_INT)
	{
		$this->_postalInfo[$type]['org'] = $organization;

		return $this;
	}

	/**
	 * Return contact postal info organization
	 * 
	 * @param $type Contact type int|loc
	 * 
	 * @return str
	 */
	public function getOrganization($type = Contact::POSTALINFO_INT)
	{
		return $this->_postalInfo[$type]['org'];
	}

	/**
	 * Set contact postal info city
	 * 
	 * @param str $city 
	 * @param str $type Contact type int|loc
	 * 
	 * @return Contact
	 */
	public function setCity($city, $type = Contact::POSTALINFO_INT)
	{
		$this->_postalInfo[$type]['addr']['city'] = $city;

		return $this;
	}

	/**
	 * Return contact postal info city
	 * 
	 * @param $type Contact type int|loc
	 * 
	 * @return str
	 */
	public function getCity($type = Contact::POSTALINFO_INT)
	{
		return $this->_postalInfo[$type]['addr']['city'];
	}

	/**
	 * Set contact postal info addr
	 * 
	 * @param str $street1 
	 * @param str $street2
	 * @param str $street3
	 * @param str $type Contact type int|loc
	 * 
	 * @return Contact
	 */
	public function setAddress($street1, $street2 = '', $street3 = '', $type = Contact::POSTALINFO_INT)
	{
		$this->_postalInfo[$type]['addr']['street'] = array(
			$street1,
			$street2,
			$street3,
		);

		return $this;
	}

	/**
	 * Return contact postal info addr
	 * 
	 * @param $type Contact type int|loc
	 * 
	 * @return str
	 */
	public function getAddress($type = Contact::POSTALINFO_INT)
	{
		return $this->_postalInfo[$type]['addr']['street'];
	}

	/**
	 * Set contact postal info postal code
	 * 
	 * @param str $postalCode 
	 * @param str $type Contact type int|loc
	 * 
	 * @return Contact
	 */
	public function setPostalCode($postalCode, $type = Contact::POSTALINFO_INT)
	{
		$this->_postalInfo[$type]['addr']['pc'] = $postalCode;

		return $this;
	}

	/**
	 * Return contact postal info postal code
	 * 
	 * @param $type Contact type int|loc
	 * 
	 * @return str
	 */
	public function getPostalCode($type = Contact::POSTALINFO_INT)
	{
		return $this->_postalInfo[$type]['addr']['pc'];
	}

	/**
	 * Set contact postal info country code
	 * 
	 * @param str $countryCode 
	 * @param str $type Contact type int|loc
	 * 
	 * @return Contact
	 */
	public function setCountryCode($countryCode, $type = Contact::POSTALINFO_INT)
	{
		$this->_postalInfo[$type]['addr']['cc'] = $countryCode;

		return $this;
	}

	/**
	 * Return contact postal info country code
	 * 
	 * @param $type Contact type int|loc
	 * 
	 * @return str
	 */
	public function getCountryCode($type = Contact::POSTALINFO_INT)
	{
		return $this->_postalInfo[$type]['addr']['cc'];
	}

	/**
	 * Set contact postal info state
	 * 
	 * @param str $state 
	 * @param str $type Contact type int|loc
	 * 
	 * @return Contact
	 */
	public function setState($state, $type = Contact::POSTALINFO_INT)
	{
		$this->_postalInfo[$type]['addr']['sp'] = $state;

		return $this;
	}

	/**
	 * Return contact postal info state
	 * 
	 * @param $type Contact type int|loc
	 * 
	 * @return str
	 */
	public function getState($type = Contact::POSTALINFO_INT)
	{
		return $this->_postalInfo[$type]['addr']['sp'];
	}

	/**
	 * Return all contact postal info array
	 * 
	 * @param str $type Contact type int|loc
	 * 
	 * @return array
	 */
	public function getPostalInfo($type = Contact::POSTALINFO_INT) 
	{
		return $this->_postalInfo[$type];
	}

	/**
	 * Performs remote object validation
	 * 
	 * @return boolean
	 */
	public function validate()
	{
		// clean up errors
		parent::validate();

		$response = ResponseHelper::fromJSON(STRegistry::Contacts()->validate($this));
		if ($response->code != 1000) {
			$this->setValidationErrorCode($response->code)
				 ->setValidationErrorMessage($response->message);

			return false;
		}

		return true;
	}

	/**
	 * Create Contact object from json string
	 * 
	 * @param str $json json string representing contact state
	 * 
	 * @return Contact
	 */
	public static function fromJSON($json)
	{
		$data = CommonFunctions::fromJSON($json, 'info')->result;
		$contact = new Contact();
		$contact->setContactId($data['id'])
				->setEmail($data['email'])
			    ->setPhoneNumber($data['voice'])
			    ->setFaxNumber($data['fax']);
		foreach (array(Contact::POSTALINFO_INT, Contact::POSTALINFO_LOCAL) as $type) {
			if (isset($data['postalInfo'][$type])) {
				$pi = $data['postalInfo'][$type];

				$contact->setName($pi['name'], $type)
						->setOrganization($pi['org'], $type)
						->setAddress(@$pi['addr']['street'][0], @$pi['addr']['street'][1], @$pi['addr']['street'][2])
						->setCity($pi['addr']['city'])
						->setCountryCode($pi['addr']['cc'])
						->setState($pi['addr']['sp'])
						->setPostalCode($pi['addr']['pc']);
			}
		}

		return $contact;
	}
}