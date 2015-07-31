<?php
require_once dirname(__FILE__) . '/BaseObject.php';

class Domain extends BaseObject
{
	/**
	 * Domain contact types constants;
	 */
	const CONTACT_TYPE_REGISTRANT = 'registrant';
	const CONTACT_TYPE_ADMIN	  = 'admin';
	const CONTACT_TYPE_TECH		  = 'tech';
	const CONTACT_TYPE_BILLING    = 'billing';

	const CONTACT_TYPE_REGISTRANT_PRIVACY = 'registrant_privacy';
	const CONTACT_TYPE_ADMIN_PRIVACY	  = 'admin_privacy';
	const CONTACT_TYPE_TECH_PRIVACY		  = 'tech_privacy';
	const CONTACT_TYPE_BILLING_PRIVACY    = 'billing_privacy';

	/**
	 * Domain client status values
	 */
	const STATUS_HOLD                = 'clientHold';
	const STATUS_UPDATE_PROHIBITED   = 'clientUpdateProhibited';
	const STATUS_DELETE_PROHIBITED   = 'clientDeleteProhibited';
	const STATUS_RENEW_PROHIBITED    = 'clientRenewProhibited';
	const STATUS_TRANSFER_PROHIBITED = 'clientTransferProhibited';

	/**
	 * Domain name
	 * 
	 * @var str
	 */
	private $_name = '';

	/**
	 * Domain contacts collection
	 * 
	 * @var array
	 */
	private $_contacts = array(
		// regular contacts
		Domain::CONTACT_TYPE_REGISTRANT         => false,
		Domain::CONTACT_TYPE_ADMIN              => false,
		Domain::CONTACT_TYPE_TECH               => false,
		Domain::CONTACT_TYPE_BILLING            => false,
		// whois privacy contacts
		Domain::CONTACT_TYPE_REGISTRANT_PRIVACY => false,
		Domain::CONTACT_TYPE_ADMIN_PRIVACY      => false,
		Domain::CONTACT_TYPE_TECH_PRIVACY       => false,
		Domain::CONTACT_TYPE_BILLING_PRIVACY    => false,
	);

	/**
	 * Domain nameservers
	 * key of array is hostname
	 * value is array of IP addresses if specfied
	 * 
	 * @var array
	 */
	private $_nameservers = array();

	/**
	 * Domain statuses
	 * 
	 * @var array
	 */
	private $_statuses = array();

	/**
	 * Domain auth code
	 * 
	 * @var str
	 */
	private $_authCode = '';

	/**
	 * Domain creation date.
	 * NB! Setting this variable using setDateCreated method will cause nothing
	 * just use getDateCreated method to access it value
	 * 
	 * @var int
	 */
	private $_dateCreated = 0;

	/**
	 * Domain expiration date.
	 * NB! Setting this variable using setDateExpire method will cause nothing
	 * just use getDateExpire method to access it value
	 * 
	 * @var int
	 */
	private $_dateExpire = 0;

	/**
	 * Domain registration period in year
	 * 
	 * @var int
	 */
	private $_registrationPeriod = 1;


	/**
	 * Class constructor
	 * 
	 * @param str $name domain name
	 * @return Domain
	 */
	public function __construct($name)
	{
		$this->setName($name);
	}

	/**
	 * Setter for domain name
	 * Private. Use class constructor to set domain name
	 * 
	 * @param str $name Domain name
	 * 
	 */
	private function setName($name)
	{
		$this->_name = strtolower(trim($name));

		return $this;
	}

	/**
	 * Getter for domain name
	 * 
	 * @return str Domain name
	 */
	public function getName()
	{
		return $this->_name;
	}

	/**
	 * Setter for domain regular contacts.
	 * All params are obligatory.
	 * 
	 * @param str $registrantId Registrant contact id
	 * @param str $adminId Administrative contact id
	 * @param str $techId Technical contact id
	 * @param str $billingId Billing contact id
	 * 
	 * @return Domain
	 */
	public function setContacts($registrantId, $adminId, $techId, $billingId) 
	{
		$this->setRegistrantContactId($registrantId);
		$this->_contacts[Domain::CONTACT_TYPE_ADMIN]      = $adminId ?: null;
		$this->_contacts[Domain::CONTACT_TYPE_TECH]       = $techId ?: null;
		$this->_contacts[Domain::CONTACT_TYPE_BILLING]    = $billingId ?: null;

		return $this;
	}

	/**
	 * Set registrant contact id
	 * 
	 * @param str $registrantId
	 * 
	 * @return Domain
	 */
	public function setRegistrantContactId($registrantId)
	{
		$this->_contacts[Domain::CONTACT_TYPE_REGISTRANT] = $registrantId;

		return $this;
	}

	/**
	 * Set admin contact id
	 * 
	 * @param str $adminId
	 * 
	 * @return Domain
	 */
	public function setAdminContactId($adminId)
	{
		$this->_contacts[Domain::CONTACT_TYPE_ADMIN] = $adminId;

		return $this;
	}

	/**
	 * Set tech contact id
	 * 
	 * @param str $techId
	 * 
	 * @return Domain
	 */
	public function setTechContactId($techId)
	{
		$this->_contacts[Domain::CONTACT_TYPE_TECH] = $techId;

		return $this;
	}

	/**
	 * Set billing contact id
	 * 
	 * @param str $billingId
	 * 
	 * @return Domain
	 */
	public function setBillingContactId($billingId)
	{
		$this->_contacts[Domain::CONTACT_TYPE_BILLING] = $billingId;

		return $this;
	}

	/**
	 * Setter for domain privacy contacts.
	 * 
	 * @param str $registrantId Registrant privacy contact id
	 * @param str $adminId Administrative privacy contact id
	 * @param str $techId Technical privacy contact id
	 * @param str $billingId Billing privacy contact id
	 * 
	 * @return Domain
	 */
	public function setPrivacyContacts($registrantId = null, $adminId = null, $techId = null, $billingId = null) 
	{
		$this->_contacts[Domain::CONTACT_TYPE_REGISTRANT_PRIVACY] = $registrantId ?: null;
		$this->_contacts[Domain::CONTACT_TYPE_ADMIN_PRIVACY]      = $adminId ?: null;
		$this->_contacts[Domain::CONTACT_TYPE_TECH_PRIVACY]       = $techId ?: null;
		$this->_contacts[Domain::CONTACT_TYPE_BILLING_PRIVACY]    = $billingId ?: null;

		return $this;	
	}

	/**
	 * Set registrant Privacy contact id
	 * 
	 * @param str $registrantId
	 * 
	 * @return Domain
	 */
	public function setRegistrantPrivacyContactId($registrantId)
	{
		$this->_contacts[Domain::CONTACT_TYPE_REGISTRANT_PRIVACY] = $registrantId;

		return $this;
	}

	/**
	 * Set admin Privacy contact id
	 * 
	 * @param str $adminId
	 * 
	 * @return Domain
	 */
	public function setAdminPrivacyContactId($adminId)
	{
		$this->_contacts[Domain::CONTACT_TYPE_ADMIN_PRIVACY] = $adminId;

		return $this;
	}

	/**
	 * Set tech Privacy contact id
	 * 
	 * @param str $techId
	 * 
	 * @return Domain
	 */
	public function setTechPrivacyContactId($techId)
	{
		$this->_contacts[Domain::CONTACT_TYPE_TECH_PRIVACY] = $techId;

		return $this;
	}

	/**
	 * Set billing Privacy contact id
	 * 
	 * @param str $billingId
	 * 
	 * @return Domain
	 */
	public function setBillingPrivacyContactId($billingId)
	{
		$this->_contacts[Domain::CONTACT_TYPE_BILLING_PRIVACY] = $billingId;

		return $this;
	}

	/**
	 * Return all domain contacts collection
	 * 
	 * @return array
	 */
	public function getContacts()
	{
		return $this->_contacts;
	}

	/**
	 * Getter for domain registrant contact id
	 * 
	 * @return str 
	 */
	public function getRegistrantContactId()
	{
		return $this->_contacts[Domain::CONTACT_TYPE_REGISTRANT];
	}

	/**
	 * Getter for domain registrant private contact id
	 * 
	 * @return str 
	 */
	public function getRegistrantPrivacyContactId()
	{
		return $this->_contacts[Domain::CONTACT_TYPE_REGISTRANT_PRIVACY];
	}	

	/**
	 * Getter for domain admin contact id
	 * 
	 * @return str 
	 */
	public function getAdminContactId()
	{
		return $this->_contacts[Domain::CONTACT_TYPE_ADMIN];
	}

	/**
	 * Getter for domain admin private contact id
	 * 
	 * @return str 
	 */
	public function getAdminPrivacyContactId()
	{
		return $this->_contacts[Domain::CONTACT_TYPE_ADMIN_PRIVACY];
	}	

	/**
	 * Getter for domain tech contact id
	 * 
	 * @return str 
	 */
	public function getTechContactId()
	{
		return $this->_contacts[Domain::CONTACT_TYPE_TECH];	
	}

	/**
	 * Getter for domain tech private contact id
	 * 
	 * @return str 
	 */
	public function getTechPrivacyContactId()
	{
		return $this->_contacts[Domain::CONTACT_TYPE_TECH_PRIVACY];
	}	

	/**
	 * Getter for domain billing contact id
	 * 
	 * @return str 
	 */
	public function getBillingContactId()
	{
		return $this->_contacts[Domain::CONTACT_TYPE_BILLING];
	}

	/**
	 * Getter for domain billing private contact id
	 * 
	 * @return str 
	 */
	public function getBillingPrivacyContactId()
	{
		return $this->_contacts[Domain::CONTACT_TYPE_BILLING_PRIVACY];
	}	

	/**
	 * Add domain nameserver
	 * 
	 * @param $hostname Nameserver hostname
	 * 
	 * @return Domain
	 */
	public function addNameServer($hostname, $ips = array())
	{
		$hostname = strtolower(trim($hostname));
		$this->_nameservers[$hostname] = $ips;

		return $this;
	}

	/**
	 * Remove domain nameserver
	 * 
	 * @param str $hostname Nameserver hostname
	 * 
	 * @return Domain
	 */
	public function removeNameServer($hostname) 
	{
		$hostname = strtolower(trim($hostname));
		if (isset($this->_nameservers[$hostname])) {
			unset($this->_nameservers[$hostname]);
		}

		return $this;
	}

	/**
	 * Return all domain nameservers
	 * 
	 * @return array
	 */
	public function getNameServers()
	{
		return $this->_nameservers;
	}

	/**
	 * Add status to domain
	 * 
	 * @param str $status One of domain client statuses
	 * 
	 * @return Domain
	 */
	public function addStatus($status) 
	{

		$this->_statuses[] = $status;
		$this->_statuses = array_unique($this->_statuses);

		return $this;
	}

	/**
	 * Remove client status from domain
	 * 
	 * @param str $status One of domain client statuses
	 * 
	 * @return Domain
	 */
	public function removeStatus($status)
	{
		foreach ($this->_statuses as $_id => $_status) {
			if ($_status == $status) {
				unset($this->_statuses[$_id]);
			}
		}

		return $this;
	}

	/**
	 * Return all domain statuses
	 * 
	 * @return array
	 */
	public function getStatuses()
	{
		return $this->_statuses;
	}

	/**
	 * Set domain auth code
	 * 
	 * @params str $authCode
	 * 
	 * @return Domain
	 */
	public function setAuthCode($authCode) 
	{
		$this->_authCode = $authCode;

		return $this;
	}

	/**
	 * Return domain auth code
	 * 
	 * @return str Domain auth code
	 */
	public function getAuthCode()
	{
		return $this->_authCode;
	}

	/**
	 * Setter for domain creation date
	 * Setting this variable will cause nothing at registry side. Used just in wrapper needs
	 * 
	 * @param int $date domain creation timestamp
	 * 
	 * @return Domain
	 */
	public function setDateCreated($date)
	{
		$this->_dateCreated = $date;

		return $this;
	}

	/**
	 * Return domain creation date in specified $format or it timestamp if $format is empty
	 * 
	 * @param str $format date function suitable format
	 * 
	 * @return mixed
	 */
	public function getDateCreated($format = false) 
	{
		return $this->dateFormat($this->_dateCreated, $format);
	}

	/**
	 * Setter for domain exiration date
	 * Setting this variable will cause nothing at registry side. Used just in wrapper needs
	 * 
	 * @param int $date domain expiration timestamp
	 * 
	 * @return Domain
	 */
	public function setDateExpire($date)
	{
		$this->_dateExpire = $date;

		return $this;
	}

	/**
	 * Return domain expire date in specified $format or it timestamp if $format is empty
	 * 
	 * @param str $format date function suitable format
	 * 
	 * @return mixed
	 */
	public function getDateExpire($format = false) 
	{
		return $this->dateFormat($this->_dateExpire, $format);
	}

	/**
	 * Set domain registration period
	 * 
	 * @param int $years domain registration period in years
	 * 
	 * @return Domain
	 */
	public function setRegistationPeriod($years)
	{
		$this->_registrationPeriod = (int)$years;

		return $this;
	}

	/**
	 * Return domain registration period in years
	 * 
	 * @return int
	 */
	public function getRegistrationPeriod()
	{
		return $this->_registrationPeriod;
	}

	/**
	 * Performs remote object validation
	 * NB! All domain contacts must be exists on Registry side to pass validation
	 * 
	 * @return boolean
	 */
	public function validate()
	{
		// clean up errors
		parent::validate();

		$response = ResponseHelper::fromJSON(STRegistry::Domains()->validate($this));
		if ($response->code != 1000) {
			$this->setValidationErrorCode($response->code)
				 ->setValidationErrorMessage($response->message);

			return false;
		}

		return true;
	}

	/**
	 * Create Domain object from json string
	 * 
	 * @param str $json json string representing domain state
	 * 
	 * @return Domain
	 */
	public static function fromJSON($json)
	{
		$data = CommonFunctions::fromJSON($json, 'info')->result;

		$domain = new Domain($data['name']);
		$domain->setContacts($data['contacts'][Domain::CONTACT_TYPE_REGISTRANT], $data['contacts'][Domain::CONTACT_TYPE_ADMIN], $data['contacts'][Domain::CONTACT_TYPE_TECH], $data['contacts'][Domain::CONTACT_TYPE_BILLING])
			   ->setPrivacyContacts(@$data['contacts'][Domain::CONTACT_TYPE_REGISTRANT_PRIVACY], @$data['contacts'][Domain::CONTACT_TYPE_ADMIN_PRIVACY], @$data['contacts'][Domain::CONTACT_TYPE_TECH_PRIVACY], @$data['contacts'][Domain::CONTACT_TYPE_BILLING_PRIVACY])
			   ->setDateCreated($data['crDate'])
			   ->setDateExpire($data['exDate'])			   
			   ->setAuthCode($data['authInfo']['pw']);
		if (isset($data['ns']) && count($data['ns'])) {
			foreach ($data['ns'] as $nameserver) {
				$domain->addNameServer($nameserver);
			}	
		}
		foreach ($data['status'] as $status) {
			$domain->addStatus($status);
		}

		return $domain;
	}
}