<?php
/**
 * class for accessing/generating privacy contacts 
 * 
 * $registrantPrivateContactID = STRegistryPrivacyContact::getRegistrantId();
 * $adminPrivateContactID      = STRegistryPrivacyContact::getAdminId();
 * $techPrivateContactID       = STRegistryPrivacyContact::getTechId();
 * $billingPrivateContactID    = STRegistryPrivacyContact::getBillingId();
 */
class STRegistryPrivacyContact

{
	const DEFAULT_CONTACT_ID = "STR-WHOISPRIVACY";

	/**
	 * return contact id used for hide domain registrant information
	 * 
	 * @return str contact id
	 */
	public static function getRegistrantId()
	{
		return STRegistryPrivacyContact::DEFAULT_CONTACT_ID;
	}

	/**
	 * return contact id used for hide domain admin information
	 * 
	 * @return str contact id
	 */
	public static function getAdminId()
	{
		return STRegistryPrivacyContact::DEFAULT_CONTACT_ID;
	}

	/**
	 * return contact id used for hide domain technical information
	 * 
	 * @return str contact id
	 */
	public static function getTechId()
	{
		return STRegistryPrivacyContact::DEFAULT_CONTACT_ID;
	}

	/**
	 * return contact id used for hide domain billing information
	 * 
	 * @return str contact id
	 */
	public static function getBillingId()
	{
		return STRegistryPrivacyContact::DEFAULT_CONTACT_ID;
	}
}