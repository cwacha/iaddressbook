<?php

 /**
 * This is the AddressBook's Main Configuration file
 * This is a piece of PHP code so PHP syntax applies!
 *
 *
 */


// Datastorage

$conf['basedir']     = '';                       //relative dir to serveroot - blank for autodetection
$conf['baseurl']     = '';                       //URL to server including protocol - blank for autodetect

$conf['dbtype']      = 'mysql';                  // database type: mysql, postgres, sqlite etc.
$conf['dbname']      = 'addressbook';            // database name
$conf['dbserver']    = 'localhost';              // server to connect to
$conf['dbuser']      = '';                       // username to connect to server
$conf['dbpass']      = '';                       // cleartext password to connect to server
$conf['dbtable_ab']     = 'addressbook';         // table inside database for addressbook entries
$conf['dbtable_cat']    = 'addressbook_cat';     // table inside database for categories
$conf['dbtable_catmap'] = 'addressbook_catmap';  // table inside database for mapping contacts to categories

// Display Options

$conf['lang']        = 'en';                     // your language (for list of supported see: ./lang/ folder)

$conf['title']       = 'PHP iAddressBook';       // what to show in the title
$conf['template']    = 'default';                // see tpl directory (the 'default' template is outdated)
$conf['bdformat']    = '$d. $month $YYYY';       // dateformat for birthday (europe version)
//$conf['bdformat']    = '$month $d. $YYYY';     // dateformat for birthday (nice US version)
//$conf['bdformat']    = '$m/$d/$YYYY';          // dateformat for birthday (default US version)
                                                 // $d - Day
                                                 // $dd - Day (incl. leading zero)
                                                 // $m - Month
                                                 // $mm - Month (incl. leading zero)
                                                 // $month - Month as localized text string
                                                 // $YYYY - Year (4-digit)

$conf['dformat']     = 'd.m.Y H:i';              // dateformat as accepted by PHPs date() function
$conf['lastfirst']   = true;                     // Displayformat for contact list: Lastname, Firstname if true; Firstname Lastname else
$conf['photo_resize'] = '128x128';               // sets max. photo size on manual photo import (default: 100 times 100 pixels). set to '' to disable
$conf['map_link']    = 'http://maps.google.com/maps?f=q&hl=en&q=$zip,+$street,+$city,+$state';
//$conf['map_link']    = 'http://map.search.ch/$city/$street';
                                                 // sets the map link to be used for addresses.
                                                 // $street
                                                 // $city
                                                 // $zip
                                                 // $state
                                                 // $country
                                                 // $pobox - P.O. Box
                                                 // $ext_adr - Extended address


// Advanced Options

$conf['canonical']   = 0;                        // Should all URLs use full canonical http://... style?
$conf['auth_enabled'] = 0;                       // enable authentication - login and password can be set in conf/auth.php
$conf['im_convert']  = '/usr/bin/convert';       // path to ImageMagicks convert
$conf['session_name'] = '';                      // override session name if you have more than one addressbook on your server
                                                 // only use alphanumeric characters (0-9, a-z, A-Z). No dots, does not consist of numbers only!


// Import / Export

$conf['vcard_fb_enc']= 'ISO-8859-1';             // Fallback encoding if we cannot detect the proper encoding of a vcard on import
//@ini_set('upload_tmp_dir', '/tmp');            // Change temporary upload directory

// Synchronization
$conf['xmlrpc_enable'] = false;

?>