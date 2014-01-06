<?php
/*
 * iAddressbook/CardDAV server
 * 
 * This server features CardDAV support
 * 
 */

if(!defined('AB_BASEDIR')) define('AB_BASEDIR',realpath(dirname(__FILE__).'/'));
require_once(AB_BASEDIR.'/lib/php/include.php');
require_once(AB_BASEDIR.'/lib/php/init.php');

global $conf;

if(!$conf['carddav_enable']) {
	echo "CardDAV server disabled.";
	exit();
}

// settings
date_default_timezone_set('Canada/Eastern');

// Make sure this setting is turned on and reflect the root url for your WebDAV server.
// This can be for example the root / or a complete path to your server script
$baseUri = $_SERVER['SCRIPT_NAME'];

//Mapping PHP errors to exceptions
function exception_error_handler($errno, $errstr, $errfile, $errline ) {
    //throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}
set_error_handler("exception_error_handler");

// Autoloader
require_once 'lib/php/SabreDav/vendor/autoload.php';

require_once 'lib/php/IABCardDAV/IABAuthenticator.php';
require_once 'lib/php/IABCardDAV/IABPrincipalBackend.php';
require_once 'lib/php/IABCardDAV/IABCardDAVBackend.php';

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

// And off we go!
$server->exec();
