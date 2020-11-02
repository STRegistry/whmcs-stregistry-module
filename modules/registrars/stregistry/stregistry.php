<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'common_functions.php';

/**
 * build whmcs module configuration array
 * 
 * @return array
 */
function stregistry_getConfigArray() {
	global $CONFIG;
    
    $configarray = array(
        "FriendlyName" => array(
            "Type"  => "System",
            "Value" => 'ST Registry'
        ),
        'Description'   => array(
            'Type'  => 'System',
            'Value'	=> 'Official ST Registry Module. Become ST Registrar here: <a href="http://www.registry.st/registrars/become-registrar" target="_blank">www.registry.st/registrars/become-registrar</a>',
        ),
        "apiHost" => array(
        	"Type"         => "text",
            "Size"         => "20",
            "FriendlyName" => "Api gateway hostname",
            "Description"  => "Enter your API hostname",
        ),
        "apiPort" => array(
        	"Type"         => "text",
            "Size"         => "10",
            "FriendlyName" => "Api gateway port",
            "Description"  => "Enter your API port",
        ),
        'apiUseSSL' => array(
            "Type"         => "yesno",
            "FriendlyName" => "SSL",
            "Description"  => "Check if you want to use SSL connection"
        ),
        "apiUsername"  => array(
            "Type"         => "text",
            "Size"         => "20",
            "FriendlyName" => "Api Username",
            "Description"  => "Enter your API username here",
        ),
        "apiPassword"  => array(
            "Type"         => "text",
            "Size"         => "32",
            "FriendlyName" => "Api Password",
            "Description"  => "Enter your API Password here",
        ),
        'allowPremium' => array(
            "Type"         => "yesno",
            "FriendlyName" => "Allow premium domains registration",
            "Description"  => "1 and 2 character(s) domains"
        ),
        "oneLetterFee" => array(
        	"Type"         => "text",
            "Size"         => "20",
            "FriendlyName" => "One letter premium fee",
        ),
        "twoLetterFee" => array(
        	"Type"         => "text",
            "Size"         => "20",
            "FriendlyName" => "Two letter premium fee",
        ),
        "tesConnection"     => array(
            "FriendlyName" => "Test Connection",
             ## This jQuery for Test Connection Button: sends request to ST Registry and shows corresponding message (success/fail) ##
            "Description"  => '<input type="button" id="st_testconnection" class="btn primary" value="Test"/><span id="canvasloader-container" class="result_con"></span>'
             . '<script src="../modules/registrars/stregistry/heartcode-canvasloader.js"></script>'
             . '<script>'
             . '$("#st_testconnection").click(function(){
                 if($("span#canvasloader-container").is(":not(:empty)")) {
                    $("span#canvasloader-container").children().remove();
                 }
                var cl = new CanvasLoader("canvasloader-container");
				cl.setDiameter(20); // default is 40
				cl.show(); // Hidden by default
				
				// This bit is only for positioning - not necessary
				  var loaderObj = document.getElementById("canvasLoader");
		  		loaderObj.style["top"] = cl.getDiameter() * -0.5 + "px";
		  		loaderObj.style["left"] = cl.getDiameter() * -0.5 + "px";
                

                var button = $(this);
	            jQuery.post(\'../modules/registrars/stregistry/stregistry.php\', {
	                   "st_action": "st_testconn",
	                    "apiUsername" : button.parents("tbody").find("input[name=apiUsername]").val(),
                        "apiPassword" : button.parents("tbody").find("input[name=apiPassword]").val(),
                        "apiHost" : button.parents("tbody").find("input[name=apiHost]").val(),
                        "apiPort" : button.parents("tbody").find("input[name=apiPost]").val(),
                        "apiUseSSL" : button.parents("#stregistryconfig").find("input[name=testMode]").attr("checked") ? "on" : ""
	                }, function(data){
	                    if(data == "success"){
	                        button.parent().find("span.result_con").html("<span style=\"color:green;font-weight:bold\"> Connection success<span>");
	                    } else {
	                        button.parent().find("span.result_con").html("<span style=\"color:red;font-weight:bold\"> Connection failed<span>");
	                    }
	                });
	            });'
             . '</script>',
        ),
    );

    return $configarray;
}


/*
 * test connection button fuction
 */
if(isset($_POST['st_action'])) {
    $params = array(
        'apiUsername' => $_POST['apiUsername'],
        'apiPassword' => $_POST['apiPassword'],
        'apiHost' => $_POST['apiHost'],
        'apiPort' => $_POST['apiPort'],
        'apiUseSSL' => $_POST['apiUseSSL'],
    );
    if (($status = __initConnectionAndAuthorize($params)) !== true) {
		die($status);
	}
    die("success");
}

/**
 * make domain registration api call
 * 
 * @param array $params call params with domain details
 * 
 * @return mixed array containig error message will return if error was ocurred, 'success' string in other case
 */
function stregistry_RegisterDomain($params)
{
	// init connection
	if (($status = __initConnectionAndAuthorize($params)) !== true) {
		return __errorArray($status);
	}

	$domain = new Domain($params['domainname']);

	// create domain contacts
	$contactIds = array();
	foreach (array('registrant', 'admin', 'tech', 'billing') as $contactType) {
		$contact = new Contact();
		$contact->setEmail($params['email'])
				->setPhoneNumber($params['phonenumberformatted'] ?: '')
				->setName($params['fullname'])
				->setOrganization($params['companyname'])
				->setAddress($params['address1'], $params['address1'] ?: '')
				->setCity($params['city'])
				->setState($params['fullstate'])
				->setPostalCode($params['postcode'])
				->setCountryCode($params['countrycode']);
		
		$json = STRegistry::Contacts()->create($contact);
		if (!ResponseHelper::isSuccess($json)) {
			return __errorArray(ResponseHelper::fromJSON($json)->message);
		}
		$contactIds[$contactType] = ResponseHelper::fromJSON($json, 'creData')->result['id'];
	}
	$domain->setContacts($contactIds['registrant'], $contactIds['admin'], $contactIds['tech'], $contactIds['billing']);

	if ($params['idprotection']) {
		$domain->setPrivacyContacts(STRegistryPrivacyContact::getRegistrantId(), STRegistryPrivacyContact::getAdminId(), STRegistryPrivacyContact::getTechId(), STRegistryPrivacyContact::getBillingId());
	}
	// add nameservers
	foreach (array('ns1', 'ns2', 'ns3', 'ns4', 'ns5') as $nsKey) {
		if (!empty($params[$nsKey])) {
			$domain->addNameServer($params[$nsKey]);
		}
	}

	// creating domain
	$json = STRegistry::Domains()->create($domain, $params['regperiod']);
	if (!ResponseHelper::isSuccess($json)) {
		return __errorArray(ResponseHelper::fromJSON($json)->message);
	}

	return 'success';
}

/**
 * make domain info call to return nameservers in whmcs format
 * 
 * @param array $params 
 * 
 * @return array containig domain ns names
 */
function stregistry_GetNameservers($params) 
{
	// init connection
	if (($status = __initConnectionAndAuthorize($params)) !== true) {
		return __errorArray($status);
	}

	// fetch domain
	$json = STRegistry::Domains()->query($params['domainname']);
	if (!ResponseHelper::isSuccess($json)) {
		return __errorArray(ResponseHelper::fromJSON($json)->message);
	}
	$domain = Domain::fromJSON($json);
	
	$nameservers = array();
	$nsCounter = 1;
	foreach ($domain->getNameServers() as $hostname => $ips) {
		if ($nsCounter > 5) {
			break;
		}
		$nameservers[sprintf("ns%d", $nsCounter++)] = $hostname;
	}

	return $nameservers;
}

/**
 * make domain udpate call for change nameservers
 * 
 * @param array $params array containing current whmcs domain nameservers
 * 
 * @return mixed array containig error message will return if error was ocurred, 'success' string in other case
 */
function stregistry_SaveNameservers($params) 
{
	// init connection
	if (($status = __initConnectionAndAuthorize($params)) !== true) {
		return __errorArray($status);
	}

	// fetch domain
	$json = STRegistry::Domains()->query($params['domainname']);
	if (!ResponseHelper::isSuccess($json)) {
		return __errorArray(ResponseHelper::fromJSON($json)->message);
	}
	$domain = Domain::fromJSON($json);

	foreach ($domain->getNameServers() as $nameserver => $addrs) {
		$domain->removeNameServer($nameserver);
	}
	//udpate nameservers
	foreach (array('ns1', 'ns2', 'ns3', 'ns4', 'ns5') as $nsKey) {
		if (!empty($params[$nsKey])) {
			$domain->addNameServer($params[$nsKey]);
		}
	}
	$json = STRegistry::Domains()->update($domain);
	if (!ResponseHelper::isSuccess($json)) {
		return __errorArray(ResponseHelper::fromJSON($json)->message);
	}

	return 'sucessfull';
}

/**
 * make domain info call to check if domain contains clientTransferProhibited status
 * 
 * @param array $params array containing whmcs params
 * 
 * @return mixed array containig error message will return if error was ocurred, 'success' string in other case
 */
function stregistry_GetRegistrarLock($params)
{
	// init connection
	if (($status = __initConnectionAndAuthorize($params)) !== true) {
		return __errorArray($status);
	}
	
	// fetch domain
	$json = STRegistry::Domains()->query($params['domainname']);
	if (!ResponseHelper::isSuccess($json)) {
		return __errorArray(ResponseHelper::fromJSON($json)->message);
	}
	$domain = Domain::fromJSON($json);

	return in_array(Domain::STATUS_TRANSFER_PROHIBITED, $domain->getStatuses()) ? "locked" : "unlocked";
}

/**
 * make domain udpate call to add/remove clientTransferProhibited status
 * 
 * @param array $params array containing whmcs params
 * 
 * @return mixed array containig error message will return if error was ocurred, 'success' string in other case
 */
function stregistry_SaveRegistrarLock($params)
{
	// init connection
	if (($status = __initConnectionAndAuthorize($params)) !== true) {
		return __errorArray($status);
	}
	// fetch domain
	$json = STRegistry::Domains()->query($params['domainname']);
	if (!ResponseHelper::isSuccess($json)) {
		return __errorArray(ResponseHelper::fromJSON($json)->message);
	}
	$domain = Domain::fromJSON($json);

	if ($params['lockenabled'] == 'locked') {
		$domain->addStatus(Domain::STATUS_TRANSFER_PROHIBITED);
	} else {
		$domain->removeStatus(Domain::STATUS_TRANSFER_PROHIBITED);
	}

	$json = STRegistry::Domains()->update($domain);
	if (!ResponseHelper::isSuccess($json)) {
		return __errorArray(ResponseHelper::fromJSON($json)->message);
	}


	return 'sucessfull';
}

/**
 * make domain trasfer request
 *
 * @param  array $params array containing whmcs params
 * 
 * @return mixed array containig error message will return if error was ocurred, 'success' string in other case
 */
function stregistry_TransferDomain($params)
{	
	// init connection
	if (($status = __initConnectionAndAuthorize($params)) !== true) {
		return __errorArray($status);
	}

	// request transfer
	$json = STRegistry::Domains()->transferRequest($params['domainname'] ?: $params['domain'], $params['regperiod'], $params['transfersecret']);
	if (!ResponseHelper::isSuccess($json)) {
		return __errorArray(ResponseHelper::fromJSON($json)->message);
	}

    return 'success';
}

/**
 * make domain renew request
 * 
 * @param  array $params array containing whmcs params
 * 
 * @return mixed array containig error message will return if error was ocurred, 'success' string in other case
 */
function stregistry_RenewDomain($params) {
	// init connection
	if (($status = __initConnectionAndAuthorize($params)) !== true) {
		return __errorArray($status);
	}

	// fetch domain
	$json = STRegistry::Domains()->query($params['domainname']);
	if (!ResponseHelper::isSuccess($json)) {
		return __errorArray(ResponseHelper::fromJSON($json)->message);
	}
	$domain = Domain::fromJSON($json);

	//renew domain
	$json = STRegistry::Domains()->renew($params['domainname'], $params['regperiod'], $domain->getDateExpire());
	if (!ResponseHelper::isSuccess($json)) {
		return __errorArray(ResponseHelper::fromJSON($json)->message);
	}
	
	return $values;
}

/**
 * make domain info request to get it contact information
 * 
 * @param  array $params array containing whmcs params
 * 
 * @return array containig domain contacts
 */
function stregistry_GetContactDetails($params)
{
	
	$contacts = array();
	// init connection
	if (($status = __initConnectionAndAuthorize($params)) !== true) {
		return __errorArray($status);
	}

	$mapping = array(
		'registrant' => 'Registrant',
		'admin'      => 'Admin',
		'tech'       => 'Technical',
		'billing'    => 'Billing',
	);
	// fetch domain
	$json = STRegistry::Domains()->query($params['domainname']);
	if (!ResponseHelper::isSuccess($json)) {
		return __errorArray(ResponseHelper::fromJSON($json)->message);
	}
	$domain = Domain::fromJSON($json);
	$idCache = array();
	foreach ($domain->getContacts() as $type => $contactId) {
		if (empty($contactId)) continue;

		if (!isset($idCache[$contactId])) {
			$json = STRegistry::Contacts()->query($contactId);
			if (!ResponseHelper::isSuccess($json)) {
				return __errorArray(ResponseHelper::fromJSON($json)->message);
			}
			$contact = $idCache[$contactId] = Contact::fromJSON($json);
		} else {
			$contact = $idCache[$contactId];
		}
		$addresses = $contact->getAddress();
		$data = array(
			'Name'         => $contact->getName(),
			'Email'        => $contact->getEmail(),
			'Organization' => $contact->getOrganization(),
			'Country'      => $contact->getCountryCode(),
			'City'         => $contact->getCity(),
			'State'        => $contact->getState(),
			'Postal Code'  => $contact->getPostalCode(),
			'Street1'      => $addresses[0],
			'Street2'      => $addresses[1],
			'Street3'      => $addresses[2],
			'Phone Number' => $contact->getPhoneNumber(),
			'Fax'          => $contact->getFaxNumber(),
		);

		$contacts[$mapping[$type]] = $data;
	}

	return $contacts;
}

/**
 * make contact update call 
 * 
 * @param array $params array containigs whmcs data
 * 
 * @return mixed array containig error message will return if error was ocurred, 'success' string in other case
 */
function stregistry_SaveContactDetails($params)
{
    // init connection
	if (($status = __initConnectionAndAuthorize($params)) !== true) {
		return __errorArray($status);
	}

	$mapping = array(
		'registrant' => 'Registrant',
		'admin'      => 'Admin',
		'tech'       => 'Technical',
		'billing'    => 'Billing',
	);

	// fetch domain
	$json = STRegistry::Domains()->query($params['domainname']);
	if (!ResponseHelper::isSuccess($json)) {
		return __errorArray(ResponseHelper::fromJSON($json)->message);
	}
	$domain = Domain::fromJSON($json);
	$updatedContacts = array();

	// udpate contacts
	foreach ($domain->getContacts() as $type => $contactId) {
		if (empty($contactId) || in_array($contactId, $updatedContacts)) {
			continue;
		}

		if (!empty($params['contactdetails'][$mapping[$type]])) {
			$data = $params['contactdetails'][$mapping[$type]];

			$contact = new Contact($contactId);
			$contact->setPhoneNumber($data['Phone Number'])
					->setFaxNumber($data['Fax'])
					->setEmail($data['Email'])
					->setName($data['Name'] ?: $data['Full Name'])
					->setOrganization($data['Organization'] ?: $data['Organisation Name'])
					->setCity($data['City'])
					->setPostalCode($data['Postal Code'] ?: $data['Postalcode'])
					->setCountryCode($data['Country'])
					->setState($data['State'])
					->setAddress($data['Street1'] ?: $data['Address 1'], $data['Street2'] ?: $data['Address 2'], isset($data['Street3']) ? $data['Street3'] : '');
			
			$json = STRegistry::Contacts()->update($contact);
			if (!ResponseHelper::isSuccess($json)) {
				return __errorArray(sprintf(ResponseHelper::fromJSON($json)->message . " in %s contact", $mapping[$type]));
			}

			$updatedContacts[] = $contact->getContactId();
		}
	}
    return 'success';
}

/**
 * make call to udpate domain auth code and return it
 * 
 * @param array $params whmcs data
 * 
 * @return array containing epp code or error message in case of any errors
 */
function stregistry_GetEPPCode($params) 
{
	// init connection
	if (($status = __initConnectionAndAuthorize($params)) !== true) {
		return __errorArray($status);
	}
	// fetch domain
	$json = STRegistry::Domains()->query($params['domainname']);
	if (!ResponseHelper::isSuccess($json)) {
		return __errorArray(ResponseHelper::fromJSON($json)->message);
	}
	$domain = Domain::fromJSON($json);

    return array(
    	'eppcode' => $domain->getAuthCode(),
    );
}


/**
 * make request to create hostname
 * 
 * @param array $params whmcs data
 * 
 * @return mixed array containig error message will return if error was occurred, 'success' string in other case
 */
function stregistry_RegisterNameserver($params)
{
	if (($status = __initConnectionAndAuthorize($params)) !== true) {
		return __errorArray($status);
	}
	try {
        $host = new Host($params['nameserver']);
        if ($params['ipaddress'] !== filter_var($params['ipaddress'], FILTER_VALIDATE_IP)) {
            throw new RuntimeException('Nameserver IP should be valid IPv4/IPv6 address');
        }
        $host->addIP($params['ipaddress'], CommonFunctions::detectIPType($params['ipaddress']));
        $json = STRegistry::Hosts()->create($host);
        if (!ResponseHelper::isSuccess($json)) {
            throw new RuntimeException(ResponseHelper::fromJSON($json)->message);
	    }
    } catch (RuntimeException $exception) {
        return __errorArray($exception->getMessage());
    }

	return 'success';
}

/**
 * make request to update host ip addresses
 * 
 * @param array $params whmcs data
 * 
 * @return mixed array containig error message will return if error was ocurred, 'success' string in other case
 */
function stregistry_ModifyNameserver($params) 
{
    // init connection
	if (($status = __initConnectionAndAuthorize($params)) !== true) {
		return __errorArray($status);
	}

	$json = STRegistry::Hosts()->query($params['nameserver']);
	if (!ResponseHelper::isSuccess($json)) {
		return __errorArray(ResponseHelper::fromJSON($json)->message);
	}
	$host = Host::fromJSON($json);
	if (!empty($params['currentipaddress'])) {
		$host->removeIP($params['currentipaddress'], CommonFunctions::detectIPType($params['currentipaddress']));
	}
	if (!empty($params['newipaddress'])) {
		$host->addIP($params['newipaddress'], CommonFunctions::detectIPType($params['newipaddress']));
	}

	$json = STRegistry::Hosts()->update($host);
	if (!ResponseHelper::isSuccess($json)) {
		return __errorArray(ResponseHelper::fromJSON($json)->message);
	}

	return 'success';
}


/**
 * make request to remove hostname
 * 
 * @param array $params whmcs data
 * 
 * @return mixed array containig error message will return if error was ocurred, 'success' string in other case
 */
function stregistry_DeleteNameserver($params) 
{	
	global $_LANG;
	// init connection
	if (($status = __initConnectionAndAuthorize($params)) !== true) {
		return __errorArray($status);
	}

	// drop host
	$json = STRegistry::Hosts()->delete($params['nameserver']);
	if (!ResponseHelper::isSuccess($json)) {
		$response = ResponseHelper::fromJSON($json);
		if ($response->code == 2304) {
			return __errorArray($_LANG['ordererrorserverhostnameinuse']);
		} else {
			return __errorArray($response->message);
		}
	}

	return 'success';
}

/**
 * add custom buttons in admin domain page
 * add hold domain button
 * add unhold domin button
 * 
 * @return array custom buttons
 */
function stregistry_AdminCustomButtonArray() 
{
    $buttonarray = array(
		"Hold Domain"   => "HoldDomain",
		"Unhold Domain" => "UnHoldDomain",
    );
    return $buttonarray;
}

/**
 * make domain udpate request to set clientHold status
 * 
 * @param array params
 * 
 * @return mixed array containig error message will return if error was ocurred, 'success' string in other case
 */
function stregistry_HoldDomain($params)
{
	// init connection
	if (($status = __initConnectionAndAuthorize($params)) !== true) {
		return __errorArray($status);
	}
	// fetch domain
	$json = STRegistry::Domains()->query($params['domainname']);
	if (!ResponseHelper::isSuccess($json)) {
		return __errorArray(ResponseHelper::fromJSON($json)->message);
	}

	$domain = Domain::fromJSON($json);
	$domain->addStatus(Domain::STATUS_HOLD);
	$json = STRegistry::Domains()->update($domain);
	if (!ResponseHelper::isSuccess($json)) {
		return __errorArray(ResponseHelper::fromJSON($json)->message);
	}

	return 'sucessfull';
}

/**
 * make domain udpate request to remove clientHold status
 * 
 * @param array params
 * 
 * @return mixed array containig error message will return if error was ocurred, 'success' string in other case
 */
function stregistry_UnHoldDomain($params)
{
	// init connection
	if (($status = __initConnectionAndAuthorize($params)) !== true) {
		return __errorArray($status);
	}
	// fetch domain
	$json = STRegistry::Domains()->query($params['domainname']);
	if (!ResponseHelper::isSuccess($json)) {
		return __errorArray(ResponseHelper::fromJSON($json)->message);
	}

	$domain = Domain::fromJSON($json);
	$domain->removeStatus(Domain::STATUS_HOLD);
	$json = STRegistry::Domains()->update($domain);
	if (!ResponseHelper::isSuccess($json)) {
		return __errorArray(ResponseHelper::fromJSON($json)->message);
	}

	return 'sucessfull';
}

/**
 * make domain udpate request to set/remove privacy contacts
 * 
 * @param array $params whmcs data
 * 
 * @return mixed array containig error message will return if error was ocurred, 'success' string in other case
 */
function stregistry_IDProtectToggle($params)
{
	// init connection
	if (($status = __initConnectionAndAuthorize($params)) !== true) {
		$actionData['vars']['registrarcustombuttonresult'] = $status;
		return $actionData;
	}
	// set/remove domain privacy
	if (!empty($params['protectenable'])) {
		$json = STRegistry::Domains()->setPrivacy($params['domainname'], STRegistryPrivacyContact::getRegistrantId(), STRegistryPrivacyContact::getAdminId(), STRegistryPrivacyContact::getTechId(), STRegistryPrivacyContact::getBillingId());
	} else {
		$json = STRegistry::Domains()->removePrivacy($params['domainname']);
	}
	if (!ResponseHelper::isSuccess($json)) {
		return __errorArray(ResponseHelper::fromJSON($json)->message);
	}
	
	return 'success';
}

// sync functions

/**
 * sync domain transfer with registry
 * 
 * @param array $params whmcs data
 * 
 * @return array with transfer status
 */
function stregistry_TransferSync($params)
{
	// init connection
	if (($status = __initConnectionAndAuthorize($params)) !== true) {
		return __errorArray($status);
	}
	// query transfer status
	$json = STRegistry::Domains()->transferQuery($params['domain']);
	if (!ResponseHelper::isSuccess($json)) {
		return __errorArray(ResponseHelper::fromJSON($json)->message);
	}
	// process transfer
	$transferData = ResponseHelper::fromJSON($json, 'trnData')->result;

	switch ($transferData['trStatus']) {
		case 'pending':
			$return['completed'] = false;
		break;
		case 'clientApproved':
		case 'serverApproved':
			$return['completed']   = true;
			$return['expirydate'] = date('Y-m-d', is_numeric($transferData['exDate']) ? $transferData['exDate'] : strtotime($transferData['exDate']));
			$json = STRegistry::Domains()->query($params['domain']);
			$domain = Domain::fromJSON($json);
			if (in_array(DOMAIN::STATUS_UPDATE_PROHIBITED, $domain->getStatuses())) {
				$domain->removeStatus(DOMAIN::STATUS_UPDATE_PROHIBITED);
				STRegistry::Domains()->update($domain);
			}
			if ($params['idprotection']) {
				$domain->setPrivacyContacts(STRegistryPrivacyContact::getRegistrantId(), STRegistryPrivacyContact::getAdminId(), STRegistryPrivacyContact::getTechId(), STRegistryPrivacyContact::getBillingId());
				$json = STRegistry::Domains()->update($domain);
				if (!ResponseHelper::isSuccess($json)) {
					//We got abnormal situation in transfer process which has to be reported
					$return['failed'] = true;
					$return['reason'] = 'Failed to complete transfer while trying to enable ID Protection. Reason: ' . ResponseHelper::fromJSON($json)->message;
					unset($return['completed']);
				}
			} else {
				STRegistry::Domains()->removePrivacy($params['domain']);
			}
		break;
		case 'clientRejected':
		case 'clientCancelled':
		case 'serverCancelled':
			$return['failed'] = true;
			$return['reason'] = $transferData['trStatus'];
		break;
		default:
			return __errorArray(sprintf('invalid transfer status: %s', $transferData['trStatus']));
		break;
	}

	return $return;
}

function stregistry_Sync($params)
{
	// init connection
	if (($status = __initConnectionAndAuthorize($params)) !== true) {
		return __errorArray($status);
	}
	// fetch domain
	$json = STRegistry::Domains()->query($params['domain']);
	if (!ResponseHelper::isSuccess($json)) {
		$response = ResponseHelper::fromJSON($json);
		if ($response->code == 2303 || $response->code == 2203) {
			// domain does not already exists or not own by current registarar
			__markWHMCSDomainCancelled($params['domainid']);
			return array(
				'active'  => false,
				'expired' => false,
			);
		} else {
			return __errorArray($response->message);	
		}
	}
	$domain = Domain::fromJSON($json);
	if ($domain->getDateExpire() < time()) {
 		return array(
 			'expired' => true,
 		);
	}
	
	return array(
		'active'  => true,
		'expired' => false,
		'expirydate' => $domain->getDateExpire('Y-m-d'),
	);
}
