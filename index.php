<?php
/**
 * AddressBook mainscript
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Clemens Wacha <clemens.wacha@gmx.net>
 */

if(!defined('AB_INC')) define('AB_INC',realpath(dirname(__FILE__)).'/');
require_once(AB_INC.'functions/init.php');
require_once(AB_INC.'functions/db.php');
require_once(AB_INC.'functions/module_auth.php');
require_once(AB_INC.'functions/actions.php');

$VERSION = "0.99 DEVEL";

//import variables
$ACT = trim($_REQUEST['do']);
if(empty($ACT)) $ACT = 'show';


$userinfo = array();

// check if user has to login
if($ACT == 'login') {
    // force login window
    auth_check(true);
} else {
    // only display login window if needed
    auth_check();
}


// access control
$ACT = auth_verify_action($ACT);


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

$QUERY = $_SESSION['q'];
$contactlist_offset = $_SESSION['o'];
$contactlist_letter = $_SESSION['l'];

$CAT_ID = (int)$_SESSION['cat_id'];

if($ACT == 'cat_del') {
	$_SESSION['cat_id'] = 0;
}

// remember selected person
//if($ACT == 'show' || $ACT == 'img') {
if(isset($_REQUEST['id'])) {
	$_SESSION['id'] = (int)trim($_REQUEST['id']);
}
$ID = $_SESSION['id'];

// check if we logout
if($ACT == 'logout') {
	auth_logout();
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

$contact = false;
$contact_categories = array();
$categories = array();

db_init();
db_open();

$AB = new addressbook;
$CAT = new categories;
//$CAT->selected = $CAT_ID;

//do the work
act_dispatch();

db_close();

//restore old umask
umask($conf['oldumask']);

//  xdebug_dump_function_profile(1);
?>