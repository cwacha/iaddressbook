<?php

 /**
 * This is the AddressBook's Main Configuration file
 * This is a piece of PHP code so PHP syntax applies!
 *
 *
 */


// Datastorage

$conf['dbtype']      = 'mysql';
$conf['dbname']      = 'addressbook';
$conf['dbserver']    = 'localhost';
$conf['dbuser']      = 'abook';
$conf['dbpass']      = 'pho5Abae';
$conf['dbtable_ab']     = 'addressbook_test';
$conf['dbtable_cat']    = 'addressbook_test_cat';
$conf['dbtable_catmap'] = 'addressbook_test_catmap';
$conf['dbtable_truth']  = 'addressbook_test_truth';
$conf['dbtable_sync']   = 'addressbook_test_sync';
$conf['dbtable_action'] = 'addressbook_test_action';

// Display Options

$conf['lang']        = 'de';              //your language

$conf['title']       = 'PHP iAddressBook DEVEL';   //what to show in the title
$conf['template']    = 'default';            //see tpl directory
//$conf['contactlist_limit'] = 0;                  // maximum number of contacts to display in contactlist
//$conf['photo_format'] = '';

// Advanced Options
$conf['auth_enabled'] = 1;                // enable authentication - login and password can be set in conf/auth.php
$conf['auth_allow_guest'] = 1;

$conf['im_convert']  = '/usr/local/convert';
$conf['session_name'] = 'iAddressBook-dev';      // override session name if you have more than one addressbook on your server
                                                 // only use alphanumeric characters (0-9, a-z, A-Z). No dots, does not consist of numbers only!


$conf['ldif_base'] = 'ou=kunden, dc=gamenet, dc=lan';
$conf['ldif_mozilla'] = 0;

$conf['debug'] = 0;
$conf['debug_db'] = 0;
$conf['xmlrpc_enable'] = 0;

?>
