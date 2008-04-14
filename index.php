<?php
/**
* AddressBook mainscript
*
* @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
* @author     Clemens Wacha <clemens.wacha@gmx.net>
*/

	if(!defined('AB_INC')) define('AB_INC',realpath(dirname(__FILE__)).'/');
	require_once(AB_INC.'functions/init.php');
    require_once(AB_INC.'functions/auth_module.php');
	require_once(AB_INC.'functions/actions.php');
    
    $VERSION = "0.93";

    // language
    if(!empty($_REQUEST['lang']) && strlen($_REQUEST['lang']) == 2) $_SESSION['lang'] = $_REQUEST['lang'];
    if(!empty($_SESSION['lang'])) {
        @include(AB_INC.'lang/'.$_SESSION['lang'].'/lang.php');    
    }
	
    // check if user has to login
    auth_check();

	//import variables
	$ID = (int)trim($_REQUEST['id']);
	$QUERY = $_REQUEST['q'];
	$ACT   = trim($_REQUEST['do']);
	
    // session handling!
    if($ACT == 'search') {
        $_SESSION['q'] = $QUERY;
    } else {
        $QUERY = $_SESSION['q'];
    }
    
    // check if we logout
    if($ACT == 'logout') {
        auth_logout();
    }
        
	//close session
	session_write_close();
    
    $contactlist = array();
    $contact = false;
    
    $AB = new addressbook;
    
    $AB->open();
		
	//do the work
	act_dispatch();
    
    $AB->close();

	//restore old umask
	//umask($conf['oldumask']);
	
	//  xdebug_dump_function_profile(1);
?>