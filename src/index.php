<?php
/**
 * iAddressBook mainscript
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Clemens Wacha <clemens.wacha@gmx.net>
 */

if(!defined('AB_BASEDIR')) define('AB_BASEDIR',realpath(dirname(__FILE__).'/'));
require_once(AB_BASEDIR.'/lib/php/include.php');
require_once(AB_BASEDIR.'/lib/php/init.php');
require_once(AB_BASEDIR.'/lib/php/db.php');
require_once(AB_BASEDIR.'/lib/php/module_auth.php');
require_once(AB_BASEDIR.'/lib/php/actions.php');

if(!file_exists(AB_CONFDIR.'/config.php')) {
    // no config found. redirect to installer
    header('Location: '.AB_URL.'install.php?do=reset');
    exit;
}

db_init();
db_open();

//import variables
$ACT = trim(array_get($_REQUEST, 'do', 'show'));

// check if user has to login
if($ACT == 'login') {
    // force login window
    auth_check(true);
} else {
    // only display login window if needed
    auth_check();
}

//
// access control
//
// accept everything if authentication is disabled
if($conf['auth_enabled']) {
    if(!auth_verify_action($userinfo['userid'], $ACT)) {
        // user is not allowed to execute $ACT, change to 'show'
        $ACT = 'show';
    }
}


// contact list offset
if($ACT == 'select_offset') {
    if(isset($_REQUEST['o'])) {
        $_SESSION['o'] = (int)trim($_REQUEST['o']);
    }
}

// letter filter
if($ACT == 'select_letter') {
    if(isset($_REQUEST['l'])) {
        $_SESSION['l'] = trim($_REQUEST['l']);
        $_SESSION['o'] = 0;
    }
}

// remember search query
if($ACT == 'search') {
	$_SESSION['q'] = $_REQUEST['q'];
    $_SESSION['o'] = 0;
    $_SESSION['l'] = 0;
}

// remember selected category
if($ACT == 'cat_select') {
	$_SESSION['cat_id'] = (int)trim($_REQUEST['cat_id']);
	$_SESSION['o'] = 0;
    $_SESSION['l'] = 0;
}

$QUERY = array_get($_SESSION, 'q', '');
$contactlist_offset = array_get($_SESSION, 'o', 0);
$contactlist_letter = array_get($_SESSION, 'l', 0);

$CAT_ID = (int)array_get($_SESSION, 'cat_id', 0);

if($ACT == 'cat_del') {
	$_SESSION['cat_id'] = 0;
}

// remember selected person, but prefer ID from $_REQUEST
if(isset($_REQUEST['id']))
	$_SESSION['id'] = (int)$_REQUEST['id'];
	
$ID = array_get($_SESSION, 'id', 0);
	
// check if we logout
if($ACT == 'logout') {
	auth_logout();
}

if($ACT == 'reset') {
    // reset the internal state
    $_SESSION['id'] = 0;
    $_SESSION['cat_id'] = 0;
    $_SESSION['q'] = '';
    $_SESSION['o'] = 0;
    $_SESSION['l'] = 0;
    $ID = 0;
    $CAT_ID = 0;
    $QUERY = '';
    $contactlist_offset = 0;
    $contactlist_letter = 0;
}

//close session
session_write_close();

/*
	echo '<pre style="text-align: left;">';
	print_r($GLOBALS);
	echo '</pre>';
*/

$contactlist = array();         // key = contact->id, value = contact
$contactlist_limit = $conf['contactlist_limit'];

$contact = false;               // the contact
$categories = array();          // all categories

$principaluri = 'principals/' . $userinfo['userid'];
$ABcatalog = new Addressbooks();
$books = $ABcatalog->getAddressBooksForUser($principaluri);
$bookId = -1;
if(empty($books)) {
	$bookId = $ABcatalog->createAddressbook($principaluri);
} else {
	$bookId = $books[0]['id'];
}

$AB = new Addressbook($bookId);
$CAT = new Categories();
//$CAT->selected = $CAT_ID;

//do the work
act_dispatch();

db_close();

?>