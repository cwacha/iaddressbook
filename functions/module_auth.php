<?php
/**
 * AddressBook authentication functions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Clemens Wacha <clemens.wacha@gmx.net>
 */

    if(!defined('AB_CONF')) define('AB_CONF',AB_INC.'conf/');
    require_once(AB_CONF.'defaults.php');

    if(!defined('AB_INC')) define('AB_INC',realpath(dirname(__FILE__).'/../').'/');
    require_once(AB_INC.'functions/blowfish.php');
    require_once(AB_INC.'functions/common.php');
    require_once(AB_INC.'functions/template.php');

    // prepare authentication array
    global $auth;
    $auth = array();
    
    @include_once(AB_CONF.'auth.php');

function auth_check($force_login = false) {
    global $conf;
    global $lang;
    global $auth;
    global $userinfo;
    
    if($conf['auth_enabled'] == false) return;
    
    // authentication enabled

    if($_SESSION['authorized']) {
        // the user is already logged in
        $userinfo['username']    = $_SESSION['username'];
        $userinfo['fullname']    = $auth[ $_SESSION['username'] ]['fullname'];
        $userinfo['email']       = $auth[ $_SESSION['username'] ]['email'];
        $userinfo['permissions'] = $auth[ $_SESSION['username'] ]['permissions'];
        $userinfo['logged_in']   = 1;
        
        return;
    }

    $auth_login = $_REQUEST['u'];
    $auth_pass  = $_REQUEST['p'];
    $sticky     = $_REQUEST['r'] ? true: false;

    // read cookie information
    if(empty($auth_login) and isset($_COOKIE[AB_COOKIE])) {
        $cookie = base64_decode($_COOKIE[AB_COOKIE]);
        list($auth_login, $sticky, $auth_pass) = split('\|', $cookie, 3);
        //msg("lg: $auth_login, sticky: $sticky, pw: $auth_pass");
        $auth_pass = PMA_blowfish_decrypt($auth_pass, auth_cookiesalt());
    }

    if(array_key_exists($auth_login, $auth) && $auth[$auth_login]['password'] == md5($auth_pass)) {
        // the user is logging in now
        $_SESSION['authorized'] = 1;
        $_SESSION['username'] = $auth_login;
        
        $userinfo['username']    = $auth_login;
        $userinfo['fullname']    = $auth[$auth_login]['fullname'];
        $userinfo['email']       = $auth[$auth_login]['email'];
        $userinfo['permissions'] = $auth[$auth_login]['permissions'];
        $userinfo['logged_in']   = 1;
        
        if($sticky) {
            // user remains logged in
            $time = time()+60*60*24*365; //one year
            $auth_pass = PMA_blowfish_encrypt($auth_pass, auth_cookiesalt());
            $cookie = base64_encode("$auth_login|$sticky|$auth_pass");
            setcookie(AB_COOKIE, $cookie, $time, '/');
        }
        
        return;
    } else if($auth_login == "make md5") {
        // password creation request
        msg("MD5: " . md5($auth_pass));
    } else if(!empty($auth_login) || !empty($auth_pass)) {
        // wrong username or wrong password supplied
        msg($lang['wrong_userpass'], -1);
    } else {}

    // no login, or login invalid --> map to guest
    
    // only for informative purpose!!!
    $userinfo['username']    = 'guest';
    $userinfo['fullname']    = $auth['guest']['fullname'];
    $userinfo['email']       = $auth['guest']['email'];
    $userinfo['permissions'] = $auth['guest']['permissions'];
    $userinfo['logged_in']   = 0;
    
    if($conf['auth_allow_guest'] and $force_login == false) {
        // guest access granted
        return;
    }
    
    // no access
    tpl_include('auth.tpl');
    exit();
}

function auth_logout() {

    $_SESSION = array();
    $_SESSION['authorized'] = 0;

    if(isset($_COOKIE[AB_COOKIE])) {
        setcookie(AB_COOKIE, '', time()-600000, '/');
    }

    session_destroy();
    
    header("Location: ". AB_URL);
    //include(template('auth.tpl'));
    exit();
}

function auth_verify_action($action, $deny_action = 'show') {
    global $conf;
    global $lang;
    global $auth;

    // accept everything if authentication is disabled
    if($conf['auth_enabled'] == false) return $action;
    
    $username = 'guest';
    
    if($_SESSION['authorized']) {
        $username = $_SESSION['username'];
    }
    
    if( in_array($action, $auth[$username]['permissions']) ) return $action;
    
    if(is_array($auth[$username]['groups'])) {
        foreach($auth[$username]['groups'] as $group) {
            if( in_array($action, $auth[$group]['permissions']) ) return $action;
        }
    }
    
    if($action != $deny_action) {
        msg($lang['action_not_allowed'] . " ($action)", -1);
    }
    
    return $deny_action;
}

/**
 * Creates a random key to encrypt the password in cookies
 *
 * This function tries to read the password for encrypting
 * cookies from $conf['metadir'].'/_htcookiesalt'
 * if no such file is found a random key is created and
 * and stored in this file.
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 *
 * @return  string
 */
function auth_cookiesalt(){
    global $conf;
    $file = AB_INC."_images/_htcookiesalt";
    $salt = @file($file);
    if(empty($salt)){
        $salt = uniqid(rand(), true);
        $fd = fopen($file, "w");
        if(!$fd) return $salt;
        fwrite($fd, $salt);
        fclose($fd);
    }
    return $salt;
}


?>