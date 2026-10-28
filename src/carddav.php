<?php
/*
 * iAddressbook/CardDAV server
 * 
 * This server features CardDAV support
 * 
 */

if(!defined('AB_BASEDIR')) define('AB_BASEDIR',__DIR__);
require_once(AB_BASEDIR.'/lib/php/include.php');
require_once(AB_BASEDIR.'/lib/php/common.php');
require_once(AB_BASEDIR.'/lib/php/init.php');
require_once(AB_BASEDIR.'/lib/php/module_auth.php');

global $conf;

init();

if(!$conf['carddav_enable']) {
	echo "CardDAV server disabled.";
	exit();
}

// Make sure this setting is turned on and reflect the root url for your WebDAV server.
// This can be for example the root / or a complete path to your server script
$baseUri = $_SERVER['SCRIPT_NAME'];

// Autoloader
require_once(AB_BASEDIR.'/lib/php/SabreDAV/vendor/autoload.php');

require_once(AB_BASEDIR.'/lib/php/IABCardDAV/IABAuthenticator.php');
require_once(AB_BASEDIR.'/lib/php/IABCardDAV/IABPrincipalBackend.php');
require_once(AB_BASEDIR.'/lib/php/IABCardDAV/IABCardDAVBackend.php');

// Backends
$authBackend      = new Sabre\DAV\Auth\Backend\IABAuthenticator();
$principalBackend = new Sabre\DAVACL\PrincipalBackend\IABPrincipalBackend();
$carddavBackend   = new Sabre\CardDAV\Backend\IABCardDAVBackend();

// Setting up the directory tree //
$nodes = array(
    new Sabre\DAVACL\PrincipalCollection($principalBackend),
    new Sabre\CardDAV\AddressBookRoot($principalBackend, $carddavBackend),
);

// The object tree needs in turn to be passed to the server class
$server = new Sabre\DAV\Server($nodes);
$server->setBaseUri($baseUri);

// Plugins
$server->addPlugin(new Sabre\DAV\Auth\Plugin($authBackend,'iAddressBook'));
$server->addPlugin(new Sabre\DAV\Browser\Plugin());
$server->addPlugin(new Sabre\CardDAV\Plugin());
$server->addPlugin(new Sabre\DAVACL\Plugin());

// Apple may query addressbook-home-set against the endpoint root. SabreDAV's
// CardDAV plugin normally supplies it only on an IPrincipal resource.
$server->on('propFind', function ($propFind) use ($server) {
    if (trim($propFind->getPath(), '/') !== '') {
        return;
    }

    $propFind->handle('{urn:ietf:params:xml:ns:carddav}addressbook-home-set', function () use ($server) {
        $authPlugin = $server->getPlugin('auth');
        $principal = $authPlugin ? $authPlugin->getCurrentPrincipal() : null;
        if (!$principal) {
            return null;
        }

        $user = basename(trim($principal, '/'));
        return new Sabre\DAV\Xml\Property\LocalHref('addressbooks/' . rawurlencode($user) . '/');
    });
}, 140);

$securitycontroller = SecurityController::getInstance();
$securitycontroller->init();

// And off we go!
$server->exec();
