<?

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
$conf['dbuser']      = '';
$conf['dbpass']      = '';
$conf['dbtable']     = 'addressbook';

// Display Options

$conf['lang']        = 'en';              //your language

$conf['title']       = 'PHP iAddressBook';   //what to show in the title
$conf['template']    = 'default';            //see tpl directory


// Advanced Options

$conf['im_convert']  = '/usr/local/convert';


?>
