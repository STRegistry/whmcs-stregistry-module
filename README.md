# ST Registry module for WHMCS
ST Registry module for WHMCS which provide full functionality coverage for .ST domains management and based on communication with ST Registry through REST API.
This module is based on PHP REST API Wrapper: https://github.com/STRegistry/restwrapper-php

Instructions and application to become accrediter registrar can be found on page: www.registry.st/registrars/become-registrar

## Installtion Steps
* Download the modules directory or Git clone the modules directory to root directory of WHMCS installtion.
* This should add module stregistry under <whmcs_root>/modules/registrars/ directory.

## Configure ST Registry module in WHMCS
* Login to WHMCS admin panel.
* Go to Setup > Products/Service > Domain registrars
* Activate <b>ST Registry</b> module
* Configure module by providing REST API access credentials

Access credentials for REST API can be found in your registrar console on page:
<b>OT&E and Integration</b> > <b>Live/Production access</b>

## Start using module
* Login to WHMCS admin panel.
* Go to Setup > Products/Service > Domain pricing
* Create ".st" TLD and selectg "Stregistry" module in "Auto Registration" field
* Define your pricing for different registration/renewal terms
* Optionally you can enable <b>ID Protection</b> and <b>EPP Code</b> addon functionality

## Configure ID Protection addon (optional)
It is possible to use pre-defined contacts for ID Protection on registry side OR dynamically generate new contacts for each domain. Functionality responsible for <b>WHOIS Privacy</b> contacts definition is located in file <whmcs_root>/modules/registrars/stregistry/classes/STRegistryPrivacyContact.php
By default used pre-defined Contact ID "STR-WHOISPRIVACY" for all domain contacts.

