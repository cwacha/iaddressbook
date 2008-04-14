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
    
    $VERSION = "0.96";
	
    // check if user has to login
    auth_check();

	//import variables
	$ACT = trim($_REQUEST['do']);
    if(empty($ACT)) $ACT = 'show';
	
    // remember search query
    if($ACT == 'search') {
        $_SESSION['q'] = $_REQUEST['q'];
    }
    $QUERY = $_SESSION['q'];
    
    // remember selected category
    if($ACT == 'cat_select') {
        $_SESSION['cat_id'] = (int)trim($_REQUEST['cat_id']);
    }
    if($ACT == 'cat_del') {
        $_SESSION['cat_id'] = 0;
    }
    $CAT_ID = (int)$_SESSION['cat_id'];
    
    // remember selected person
    if($ACT == 'show' || $ACT == 'img') {
        $_SESSION['id'] = (int)trim($_REQUEST['id']);
    }
	$ID = (int)$_SESSION['id'];
    
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
    $contact = false;
    $categories = array();
    
    db_init();
    db_open();

    $AB = new addressbook;
    $CAT = new categories;
    $CAT->selected = $CAT_ID;
    
	//do the work
	act_dispatch();
    
    db_close();

	//restore old umask
	//umask($conf['oldumask']);
	
	//  xdebug_dump_function_profile(1);
?>