<?php
/**
 * AddressBook authentication functions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Clemens Wacha <clemens.wacha@gmx.net>
 */


	if(!defined('AB_INC')) define('AB_INC',realpath(dirname(__FILE__).'/../').'/');
    require_once(AB_INC.'functions/common.php');
    require_once(AB_INC.'functions/template.php');
    
    if(!defined('AB_CONF')) define('AB_CONF',AB_INC.'conf/');
	require_once(AB_CONF.'defaults.php');


    // prepare authentication array
    global $auth;
    $auth = array();
    
    @include_once(AB_CONF.'auth.php');

function auth_check() {
    global $conf;
    global $lang;
    global $auth;
    
    if($conf['auth_enabled'] == false) return;

    if($_SESSION['authorized']) {
        return;
    } else {
        $auth_login = $_REQUEST['u'];
        $auth_pass = $_REQUEST['p'];

        if(array_key_exists($auth_login, $auth) && $auth[$auth_login] == md5($auth_pass)) {
            $_SESSION['authorized'] = 1;
            return;
        } else if($auth_login == "make md5") {
            msg("MD5: " . md5($auth_pass));
        } else if(!empty($auth_login) || !empty($auth_pass)) {
            msg($lang['wrong_userpass'], -1);
        } else {}
    
        tpl_include('auth.tpl');
        exit();
    }
}

function auth_logout() {
    
    $_SESSION['authorized'] = 0;

    header("Location: ". AB_URL);
    //include(template('auth.tpl'));
    exit();
}

?>