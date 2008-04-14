<?php

 /**
 * This is the AddressBook's Main Configuration file
 * This is a piece of PHP code so PHP syntax applies!
 *
 *
 */


// Datastorage

$conf['dbtype']         = 'sqlite';               // database type: mysql, postgres, sqlite etc.
$conf['dbname']         = 'addressbook';         // database name
$conf['dbserver']       = 'conf/localhost';           // server to connect to
$conf['dbuser']         = '';                    // username to connect to server
$conf['dbpass']         = '';                    // cleartext password to connect to server
$conf['dbtable_ab']     = 'addressbook';         // table inside database for addressbook entries
$conf['dbtable_cat']    = 'addressbook_cat';     // table inside database for categories
$conf['dbtable_catmap'] = 'addressbook_catmap';  // table inside database for mapping contacts to categories
$conf['dbtable_truth']  = 'addressbook_truth';   // table inside database that holds the sync truth of the last sync
$conf['dbtable_sync']   = 'addressbook_sync';    // table inside database that contains the situation on the remote side
$conf['dbtable_action'] = 'addressbook_action';  // table inside database that contains sync actions to be performed

// Display Options

$conf['lang']        = 'en';                     // your language (for list of supported see: ./lang/ folder)

$conf['title']       = 'PHP iAddressBook';       // what to show in the title
$conf['template']    = 'default';                // see ./tpl/ folder (the 'default' is the default :-) )

// Advanced Options

$conf['auth_enabled'] = 0;                       // enable authentication - login and password can be set in ./conf/auth.php
$conf['im_convert']  = '/usr/bin/convert';       // path to ImageMagicks convert


?>
