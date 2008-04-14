<?

 /**
 * This is the AddressBook's Main Configuration file
 * This is a piece of PHP code so PHP syntax applies!
 *
 *
 */


// Datastorage

$conf['basedir']     = '';                //relative dir to serveroot - blank for autodetection
$conf['baseurl']     = '';                //URL to server including protocol - blank for autodetect

$conf['dbtype']      = 'mysql';
$conf['dbname']      = 'addressbook';
$conf['dbserver']    = 'localhost';
$conf['dbuser']      = '';
$conf['dbpass']      = '';
$conf['dbtable']     = 'addressbook';

// Display Options

$conf['lang']        = 'en';              //your language (for list of supported see: ./lang/ folder)

$conf['title']       = 'Your Name';       //what to show in the title
$conf['template']    = 'slim';            //see tpl directory (the 'default' template is outdated)
$conf['bdformat']    = 'd.m.Y';           //dateformat for birthday as accepted by PHPs date() function
$conf['dformat']     = 'd.m.Y H:i';       //dateformat as accepted by PHPs date() function
$conf['lastfirst']   = true;              // Displayformat for contact list: Lastname, Firstname if true; Firstname Lastname else


// Advanced Options

$conf['canonical']   = 0;                 //Should all URLs use full canonical http://... style?
$conf['im_convert']  = '/usr/bin/convert';//path to ImageMagicks convert

// Import / Export

$conf['vcard_fb_enc']= 'ISO-8859-1';      // Fallback encoding if we cannot detect the proper encoding of a vcard on import


?>