<?php

 /**
 * This is the AddressBook's Main Configuration file
 * This is a piece of PHP code so PHP syntax applies!
 *
 *
 */


// Datastorage

$conf['fmode']          = 644;                  //set file creation mode
$conf['dmode']          = 755;                  //set directory creation mode
$conf['basedir']        = '';                    //relative dir to serveroot - blank for autodetection
$conf['baseurl']        = '';                    //URL to server including protocol - blank for autodetect

$conf['dbtype']         = 'mysql';               // database type: mysql, postgres, sqlite etc.
$conf['dbname']         = 'addressbook';         // database name
$conf['dbserver']       = 'localhost';           // server to connect to
$conf['dbuser']         = '';                    // username to connect to server
$conf['dbpass']         = '';                    // cleartext password to connect to server
$conf['dbtable_ab']     = 'addressbook';         // table inside database for addressbook entries
$conf['dbtable_cat']    = 'addressbook_cat';     // table inside database for categories
$conf['dbtable_catmap'] = 'addressbook_catmap';  // table inside database for mapping contacts to categories
$conf['dbtable_truth']  = 'addressbook_truth';   // table inside database that holds the sync truth of the last sync
$conf['dbtable_sync']   = 'addressbook_sync';    // table inside database that contains the situation on the remote side
$conf['dbtable_action'] = 'addressbook_action';  // table inside database that contains sync actions to be performed
$conf['dbtable_users']  = 'addressbook_users';   // table inside database that contains user information (fullname, password etc.)


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
$conf['lastfirst']   = 1;                     // Displayformat for contact list: Lastname, Firstname if true; Firstname Lastname else
$conf['photo_resize'] = '128';                   // sets max. photo size on manual photo import (default: 100 times 100 pixels). set to '' to disable
$conf['photo_size'] = '128';                     // sets max. height of photo display size in default template
$conf['photo_format'] = 'png';                   // image format to be used when exporting vCards (default: png) (the internal format is always png)
                                                 // if set to '' images are NOT exported.

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
$conf['contactlist_limit'] = 25;                 // maximum number of contacts to display in contactlist
$conf['bday_advance_week'] = 2;                  // display upcoming birthdays in the next XX weeks (default 2, max 4)

// Advanced Options

$conf['canonical']   = 0;                        // Should all URLs use full canonical http://... style?
$conf['auth_enabled'] = 0;                       // enable authentication - login and password can be set in conf/auth.php
$conf['auth_allow_guest'] = 0;                   // allow guest access (no login required) - the permissions can be conficured in conf/auth.php
$conf['im_convert']  = '/usr/bin/convert';       // path to ImageMagicks convert
$conf['photo_enable'] = 1;                       // enable photo usage (disable, if you are using sqlite 2.x)
$conf['session_name'] = '';                      // override session name if you have more than one addressbook on your server
                                                 // only use alphanumeric characters (0-9, a-z, A-Z). No dots, does not consist of numbers only!
$conf['mark_changed'] = 1;                    // every contact that is added or modified will be automatically added to a new category called "modified contacts"
$conf['debug'] = 0;                              // enable debug mode: use ?do=debug to show debug information
$conf['debug_db']       = 0;					 // enable database debugging 

// Import / Export

$conf['vcard_fb_enc']= 'ISO-8859-1';             // Fallback encoding if we cannot detect the proper encoding of a vcard on import
//@ini_set('upload_tmp_dir', '/tmp');            // Change temporary upload directory

$conf['ldif_base'] = 'ou=customers, dc=example, dc=com';
$conf['ldif_mozilla'] = 1;                       // use mozilla LDAP classes (mozillaOrgPerson, mozillaAddressBookEntry)

// Synchronization
$conf['xmlrpc_enable'] = 0;                  // Enable XMLRPC synchronization (not yet implemented)

?>
