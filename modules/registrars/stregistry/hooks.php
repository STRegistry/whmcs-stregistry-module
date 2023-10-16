<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'common_functions.php';

/**
 * hook for validating domain epp code and transfer prohibition statuses before proceed with transfer
 * 
 * @param array $params
 * 
 * @return mixed array will be returned in case of some errors occured, empty string in other cases
 */
add_hook('ShoppingCartValidateDomainsConfig', 1, 'hook_stregistrar_ShoppingCartValidateDomainsConfig');
function hook_stregistrar_ShoppingCartValidateDomainsConfig($params)
{
    $errors = array();
    foreach ($_SESSION['cart']['domains'] as $key => $domain) {
        // check tld
        if (!__checkSTTLD($domain)) {
            continue;
        }
        // check premium domain registration
        if ($domain['type'] == 'register') {
            $config = __getSTRegistrarModuleConfig();
            if (__domainIsPremium($domain['domain']) && $config['allowPremium'] !== 'on') {
                return $errors[] = "Registration of premium domains is disabled by server configuration";
            }
        }

        if ($domain['type'] == 'transfer') {
            // init api connection
            if (($status = __initConnectionAndAuthorize(__getSTRegistrarModuleConfig())) === false) {
                return $errors[] = $status;
            }
            // try to query domain using specified auth code
            $json = STRegistry::Domains()->query($domain['domain'], $domain['eppcode']);
            if (!ResponseHelper::isSuccess($json)) {
                $status = ResponseHelper::fromJSON($json);
                if ($status->code == 2202) {
                    $errors[] = sprintf('EPP Code is invalid for domain: %s', $domain['domain']);
                } else {
                    $errors[] = $status->message;
                }
            }
            $domainObj = Domain::fromJSON($json);
            // check status prohibitions
            if (in_array(Domain::STATUS_TRANSFER_PROHIBITED, $domainObj->getStatuses()) || in_array('serverTransferProhibited', $domainObj->getStatuses())) {
                $errors[] = sprintf('Domain %s is not eligible for transfer. Please contact current domain registrar for assistance', $domain['domain']);
            }
        }
    }

    return count($errors) ? $errors : '';
}


/**
 * hook for fetching host gluerecords and show it on register nameserver page header
 * 
 * @param array $params whmcs params
 * 
 * @return mixed errors array will be returned if some error ocuured
 */
add_hook('ClientAreaHeaderOutput', 1, 'hook_stregistrar_ClientAreaHeaderOutput');
function hook_stregistrar_ClientAreaHeaderOutput($params)
{

    $errors = array();
    if ($params['clientareaaction'] == 'domainregisterns') {
        global $smarty;
        global $db_host;

        if (($status = __initConnectionAndAuthorize(__getSTRegistrarModuleConfig())) === false) {
            return $errors[] = $status;
        }
        $filter = new SearchCriteria();
        $filter->name->like("%." . $params['domain']);

        $json = STRegistry::Hosts()->search($filter, 30);
        if (!ResponseHelper::isSuccess($json)) {
            return $errors[] = ResponseHelper::fromJSON($json)->message;
        }
        $hosts = ResponseHelper::fromJSON($json, 'searchRes')->result;
        $gluerecords = array();
        foreach ($hosts as $host) {
            if (count($host['addr'])) {
                foreach ($host['addr'] as $record) {
                    $keys = array_keys($record);
                    $type = array_shift($keys);
                    $addr = array_shift($record);

                    $gluerecords[] = array(
                        'hostname' => $host['name'],
                        'address' => $addr,
                        'type' => $type == Host::IP_VERSION_4 ? 'IPv4' : 'IPv6',
                    );
                }
            } else {
                $gluerecords[] = array(
                    'hostname' => $host['name'],
                );
            }
        }
        $smarty->assign('module', "stregistry");
        $smarty->assign('gluerecords', $gluerecords);
    }
}

/**
 * hook for applying premium domains fee
 *
 * @param array $params whmcs params
 * 
 * @return float
 */
add_hook('OrderDomainPricingOverride', 1, 'hook_stregistrar_OrderDomainPricingOverride');
function hook_stregistrar_OrderDomainPricingOverride($params)
{
    if (!__checkSTTLD($params['domain'])) {
        return false;
    }

    if ($params['type'] != 'register') {
        return false;
    }

    $domain = explode('.', $params['domain']);
    $sld = $domain[0];
    $tld = '.' . $domain[1];

    $config = __getSTRegistrarModuleConfig();
    $premiumFee = 0;

    if (strlen($sld) == 2) {
        $premiumFee = $config['twoLetterFee'];
    } elseif (strlen($sld) == 1) {
        $premiumFee = $config['oneLetterFee'];
    }

    $currency = getCurrency($_SESSION['uid']);
    if (($regularPrice = __getDomainRegistrationPrice($tld, $params['regperiod'], $currency['id'])) === false) {
        return false;
    }

    if ($premiumFee > 0) {
        $total = $regularPrice + $premiumFee * $currency['rate'];
    } else {
        return false;
    }

    ob_clean();
    return $total;
}

/**
 * cronjob hook for transfer synchronisation
 * 
 * @param array $param whmcs data
 * 
 * @return void
 */
add_hook('DailyCronJob', 1, 'hook_stregistrar_DailyCronJob');
function hook_stregistrar_DailyCronJob($params)
{
    return;
}

function hook_stregistrar_ClientAreaHeadOutput($vars)
{


    $script = '<script>';

    if (($vars['filename'] == 'domainchecker' || ($vars['filename'] == 'cart' && isset($_GET['a']) && $_GET['a'] == 'view')) && (is_null($_REQUEST['ajax']) || $_REQUEST['ajax'] != 1)) {
        $script .= 'jQuery(function() {
                ';

        $script .= 'jQuery(document).ready(function(){'
            . 'jQuery("table.cart tr.carttableproduct strike").remove();'
            . '});';

        $script .= '
                });';
    }

    $script .= '</script>';

    return $script;
}
add_hook('ClientAreaHeadOutput', 1, 'hook_stregistrar_ClientAreaHeadOutput');