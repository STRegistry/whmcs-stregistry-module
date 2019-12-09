<?php
/**
 * init wrapper and send authorization request
 * 
 * @param array $params module configurations
 * 
 * @return mixed true if authorization was sucessfull, error message in other case
 */
function __initConnectionAndAuthorize($params)
{
	if (!class_exists('STRegistry')) {
        require_once sprintf('%s%2$sclasses%2$swrapper%2$sSTRegistry.php', dirname(__FILE__), DIRECTORY_SEPARATOR);
        require_once sprintf('%s%2$sclasses%2$swrapper%2$sResponseHelper.php', dirname(__FILE__), DIRECTORY_SEPARATOR);
        require_once sprintf('%s%2$sclasses%2$sSTRegistryPrivacyContact.php', dirname(__FILE__), DIRECTORY_SEPARATOR);
    }
	STRegistry::Init(
	    $params['apiHost'],
        $params['apiHost'],
        $params['apiUseSSL'] === 'on',
        $params['apiVersion'] ?: '1.0',
        $params['apiUserAgent'] ?: 'WHMCS-MODULE'
    );

	$json = STRegistry::Session()->login($params['apiUsername'], $params['apiPassword']);
	if (!ResponseHelper::isSuccess($json)) {
		return ResponseHelper::fromJSON($json)->message;
	}

	return true;
}

/**
 * check if domain is handled by ST Registry
 * 
 * return boolean
 */
function __checkSTTLD($domain) 
{
    $domain = explode(".", strtolower($domain));

    return array_pop($domain) == 'st';
}

/**
 * query registrar module config from whmcs db
 * 
 * @return array wmhcs module config
 */
function __getSTRegistrarModuleConfig($registrar = 'stregistry')
{
    $settings = array();

    $sql = sprintf("SELECT `setting`, `value` FROM `tblregistrars` WHERE registrar = '%s'", $registrar);
    $res = mysql_query($sql);

    while ($res && ($row = mysql_fetch_assoc($res))) {
        $settings[$row['setting']] = decrypt($row['value']);
    }

    return $settings;
}

/**
 * query whmcs global settings from database
 * 
 * @return array key->value settings
 */
function __getWHMCSConfig()
{
    $settings = array();

    $sql = "SELECT * FROM `tblconfiguration`";
    $res = mysql_query($sql);

    while($res && ($row = mysql_fetch_assoc($res))) {
        $settings[$row['setting']] = $row['value'];
    }

    return $settings;
}

/**
 * return regular domain registration price
 * 
 * @param str $tld top level domain
 * @param int $period registration period
 * @param str $currency
 * 
 * @return float domain reg. price over period
 */
function __getDomainRegistrationPrice($tld, $period, $currency)
{
    $periods = array(
        1  => 'msetupfee', 2  => 'qsetupfee',
        3  => 'ssetupfee', 4  => 'asetupfee',
        5  => 'bsetupfee', 6  => 'monthly',
        7  => 'quarterly', 8  => 'semiannually',
        9  => 'annually', 10 => 'biennially'
    );

    $sql = sprintf("SELECT `p`.`%s` FROM `tblpricing` AS `p`
            INNER JOIN `tbldomainpricing` AS `d` 
            ON `p`.`relid` = `d`.`id`
            WHERE `p`.`type` = 'domainregister' AND `p`.`currency` = '%s' AND `d`.`extension` = '%s'", $periods[$period], $currency, $tld);

    $res = mysql_query($sql);
    $row = mysql_fetch_assoc($res);
    return $row[$periods[$period]] ?: false;
}

/**
 * Check if domain is one or two letters length
 * 
 * @param str $domainName
 * 
 * @return boolean
 */
function __domainIsPremium($domainName)
{
    $lastLevelName = stristr($domainName, '.', true);

    return strlen($lastLevelName) <= 2;
}

/**
 * Generates random authcode for domain
 * 
 * @return str
 */
function __generateAuthCode() {
	$an = array(
		0 => "abcdefghijklmnopqrstuwxyz",
		1 => "ABCDEFGHIJKLMNOPQRSTUWXYZ",
		2 => "0123456789",
	);

	$pass = substr(str_shuffle($an[0]), 0, 6);
	$pass .= substr(str_shuffle($an[1]), 0, 6);
	$pass .= substr(str_shuffle($an[2]), 0, 4);

	return str_shuffle($pass);
}

/**
 * Return WHMCS specific error format array 
 * 
 * @param str $message error message
 * 
 * @return array
 */
function __errorArray($message) 
{
	return array(
		'error' => $message,
	);
}


/**
 * update whmcs domain database and set domain status to Cancelled
 * 
 * @param int $whmcsDomainId
 * 
 * @return boolean
 */
function __markWHMCSDomainCancelled($whmcsDomainId)
{
	$sql = sprintf("UPDATE `tbldomains` SET `status` = 'Cancelled' WHERE `id` = %d", $whmcsDomainId);
	$res = mysql_query($sql);

	return is_resource($res);
}


/**
 * Check if ID Protection enabled.
 * Since in WHMCS v5.x id protection flag was identified as "on" and in WHMCS v6.x identified as (int) 1 - to keep
 * compatibility between both WHMCS versions we need to check both formats
 *
 * @param mixed $idprotection
 *
 * @return boolean
 */
function __isIDProtectionEnabled($idprotection)
{
    if ($idprotection=='on' || $idprotection==1) {
        return true;
    } else {
        return false;
    }
}
