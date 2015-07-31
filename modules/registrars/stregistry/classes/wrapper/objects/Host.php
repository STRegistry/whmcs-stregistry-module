<?php

require_once dirname(__FILE__) . '/BaseObject.php';

class Host extends BaseObject
{	

	/**
	 * Host client status values
	 */
	const STATUS_UPDATE_PROHIBITED = 'clientUpdateProhibited';
	const STATUS_DELETE_PROHIBITED = 'clientDeleteProhibited';

	/**
	 * IP versions
	 */
	const IP_VERSION_4 = 'v4';
	const IP_VERSION_6 = 'v6';

 	/**
	 * Hostname 
	 * 
	 * @var str
	 */
	private $_name = '';

	/**
	 * Host IP addresses
	 * 
	 * @var array
	 */
	private $_addresses = array(
		Host::IP_VERSION_4 => array(),
		Host::IP_VERSION_6 => array(),
	);

	/**
	 * Host statuses list
	 * 
	 * @var array
	 */
	private $_statuses = array();

	/**
	 * @param str $name hostname
	 */
	public function __construct($name)
	{
		$this->setName($name);
	}

	/**
	 * Set hostname
	 * 
	 * @param str $name
	 * 
	 * @return Host
	 */
	private function setName($name) 
	{
		$this->_name = $name;

		return $this;
	}

	/**
	 * Return hostaname
	 * 
	 * @return str
	 */
	public function getName()
	{
		return $this->_name;
	}

	/**
	 * Add ipv4 address to host object
	 * 
	 * @param str $IPAddress
	 * 
	 * @return Host
	 */
	public function addIPv4($IPAddress)
	{
		$this->addIP($IPAddress, Host::IP_VERSION_4);

		return $this;
	}

	/**
	 * Add ipv6 address to host object
	 * 
	 * @param str $IPAddress
	 *  
	 * @return Host
	 */
	public function addIPv6($IPAddress)
	{
		$this->addIP($IPAddress, Host::IP_VERSION_6);	

		return $this;
	}

	/**
	 * Remove ipv4 address from host object 
	 * 
	 * @param str $IPAddress
	 * 
	 * @return Host
	 */
	public function removeIPv4($IPAddress)
	{
		$this->removeIP($IPAddress, Host::IP_VERSION_4);

		return $this;
	}

	/**
	 * Remove ipv6 address from host object 
	 * 
	 * @param str $IPAddress
	 * 
	 * @return Host
	 */
	public function removeIPv6($IPAddress)
	{
		$this->removeIP($IPAddress, Host::IP_VERSION_6);

		return $this;
	}


	/**
	 * Add ip addr of specified type to the host object
	 * 
	 * @param str $addr ip address
	 * @param str $type ip version
	 * 
	 * @return Host
	 */
	public function addIP($addr, $version)
	{
		$this->_addresses[$version][] = $addr;
		$this->_addresses[$version] = array_unique($this->_addresses[$version]);

		return $this;
	}


	/**
	 * Remove ip addr of specified type from host object
	 * 
	 * @param str $addr ip address
	 * @param str $type ip version
	 * 
	 * @return Host
	 */
	public function removeIP($addr, $version)
	{
		foreach ($this->_addresses[$version] as $_id => $_addr) {
			if ($_addr == $addr) {
				unset($this->_addresses[$version][$_id]);
			}
		}

		return $this;
	}

	/**
	 * Return list of host ipv4 addresses
	 * 
	 * @return array
	 */
	public function getIPv4()
	{
		return $this->_addresses[Host::IP_VERSION_4];
	}

	/**
	 * Return list of host ipv4 addresses
	 * 
	 * @return array
	 */
	public function getIPv6()
	{
		return $this->_addresses[Host::IP_VERSION_6];
	}

	/**
	 * Add status to host
	 * 
	 * @param str $status One of host client statuses
	 * 
	 * @return Host
	 */
	public function addStatus($status) 
	{
		if (in_array($status, array(Host::STATUS_DELETE_PROHIBITED, Host::STATUS_UPDATE_PROHIBITED))) {
			$this->_statuses[] = $status;
		}
		$this->_statuses = array_unique($this->_statuses);

		return $this;
	}

	/**
	 * Remove client status from host
	 * 
	 * @param str $status One of host client statuses
	 * 
	 * @return Host
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
	 * Return host statuses list
	 * 
	 * @return array
	 */
	public function getStatuses()
	{
		return $this->_statuses;
	}

	/**
	 * Create Host object from json string
	 * 
	 * @param str $json json string representing domain state
	 * 
	 * @return Host
	 */
	public static function fromJSON($json)
	{
		$data = CommonFunctions::fromJSON($json, 'info')->result;
		$host = new Host($data['name']);

		foreach ($data['addr'] as $addr) {
			$keys = array_keys($addr);
			$version = array_shift($keys);
			if ($version == Host::IP_VERSION_4) {
				$host->addIPv4($addr[$version]);
			} else {
				$host->addIPv6($addr[$version]);
			}
		}
		foreach ($data['status'] as $status) {
			$host->addStatus($status);
		}

		return $host;
	}
}