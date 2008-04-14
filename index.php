<?php
/**
* AddressBook mainscript
*
* @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
* @author     Clemens Wacha <clemens.wacha@gmx.net>
*/

$VERSION = "0.92";

function display_version() {
    global $VERSION;
    
    return $VERSION;
}

	//  xdebug_start_profiling();

	if(!defined('AB_INC')) define('AB_INC',realpath(dirname(__FILE__)).'/');
	require_once(AB_INC.'functions/init.php');
	require_once(AB_INC.'functions/actions.php');
	

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