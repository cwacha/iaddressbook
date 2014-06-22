<?php
    /**
     * iAddressBook authentication functions
     *
     * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
     * @author     Clemens Wacha <clemens.wacha@gmx.net>
     */

    if(!defined('AB_BASEDIR')) define('AB_BASEDIR',realpath(dirname(__FILE__).'/../../'));
    require_once(AB_BASEDIR.'/lib/php/include.php');
    require_once(AB_BASEDIR.'/lib/php/blowfish.php');
    require_once(AB_BASEDIR.'/lib/php/common.php');
    require_once(AB_BASEDIR.'/lib/php/template.php');

    // prepare authentication array
    global $auth;
    $auth = array();
    
    // load users and permissions
    @include_once(AB_BASEDIR.'/lib/default/auth.php');
    @include(AB_CONFDIR.'/auth.php');

    // userinfo array, contains information about logged in user (or guest)
    // logged_in is just informative (for template code). $_SESSION['authorized'] contains
    // the real information if the user is logged in or not!
    global $userinfo;
    $userinfo = array();
    $userinfo = auth_get_userinfo('guest');
    $userinfo['logged_in']   = 0;

/**
 * Checks if a user is authenticated (and has a valid session cookie)
 *
 * This function checks if the users is already logged in (i.e. has
 * a valid session cookie) and returns. If the user is not logged in
 * the user is redirected to the login page and the PHP processing stops.
 *
 * @author  Clemens Wacha <clemens.wacha@gmx.net>
 *
 * @return  if user is logged in or guest access is granted. does not return otherwise.
 */
function auth_check($force_login = false) {
    global $conf;
    global $lang;
    global $auth;
    global $userinfo;
    
    if($conf['auth_enabled'] == false) return;
    
    // authentication enabled

    if(array_get($_SESSION, 'authorized', false)) {
        // the user is already logged in
        $userinfo = auth_get_userinfo($_SESSION['username']);
        $userinfo['logged_in']   = 1;
        return;
    }

    $auth_login = array_get($_REQUEST, 'u');
    $auth_pass  = array_get($_REQUEST, 'p');
    $sticky     = array_get($_REQUEST, 'r', false) ? true: false;

    // read cookie information
    if(empty($auth_login) and isset($_COOKIE[AB_COOKIE])) {
        $cookie = base64_decode($_COOKIE[AB_COOKIE]);
        list($auth_login, $sticky, $auth_pass) = explode('\|', $cookie, 3);
        //msg("lg: $auth_login, sticky: $sticky, pw: $auth_pass");
        $auth_pass = PMA_blowfish_decrypt($auth_pass, auth_cookiesalt());
    }

    if(!empty($auth_login) && auth_login($auth_login, $auth_pass)) {
        // the user is logging in now
        $_SESSION['authorized'] = 1;
        $_SESSION['username'] = $auth_login;
        
        $userinfo = auth_get_userinfo($auth_login);
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
    $userinfo = auth_get_userinfo('guest');
    $userinfo['logged_in']   = 0;
    
    if($conf['auth_allow_guest'] and $force_login == false) {
        // guest access granted
        return;
    }
    
    // no access or login forced
    tpl_include('auth.tpl');
    exit();
}

/**
 * Checks username and password
 *
 * This function returns true if username and password are correct.
 * Returns false otherwise.
 *
 * @author  Clemens Wacha <clemens.wacha@gmx.net>
 *
 * @return  boolean
 */
function auth_login($userid, $password) {
    global $auth;
    global $conf;
    
    if(!$conf['auth_enabled'])
    	return true;

    $userinfo = array_get($auth, $userid);
    if($userid == 'guest' && $conf['auth_allow_guest']) {
    	$password = 'guest';
    	$userinfo = array(
    		'password' => '084e0343a0486ff05530df6c705c8bb4'
    	);
    }
    
    if(!is_array($userinfo)) {
    	//msg("login action=failed user=$userid");
    	return false;
    }
     
    $setpw = array_get($userinfo, 'password');
    if($setpw == md5((string)$password)) {
    	//msg("login action=success user=$userid");
        return true;
    }
    
    //msg("login action=failed user=$userid");
    return false;
}


/**
 * Log Out
 *
 * This function destroys the session cookie and logs the user out.
 *
 * @author  Clemens Wacha <clemens.wacha@gmx.net>
 *
 */
function auth_logout() {
	msg("logout!");

    $_SESSION = array();
    $_SESSION['authorized'] = 0;

    if(isset($_COOKIE[AB_COOKIE])) {
        setcookie(AB_COOKIE, '', time()-600000, '/');
    }

    session_destroy();
    
    header("Location: ". AB_URL);
    exit();
}


/**
 * Checks permissions
 * This function returns true if @username may execute @action.
 * Returns false otherwise. Does not check if user is logged in or not!
 *
 * @author  Clemens Wacha <clemens.wacha@gmx.net>
 *
 * @param   username
 * @param   action
 * @return  boolean isAllowed 
 */
function auth_verify_action($userid, $action) {
    global $conf;
    global $lang;
    global $auth;
    
    if(empty($userid)) return false;

    if(!is_array($auth)) {
        msg("No authentication array found! Access granted. Does conf/auth.php exist?", -1);
        return true;
    }

    $userinfo = auth_get_userinfo($userid);    
    if(!is_array($userinfo))
    	return false;

    $groups = array_get($userinfo, 'groups');
	if (!is_array($groups))
		return false;
	
	foreach ( $groups as $group ) {
		if (in_array($action, $auth[$group]['permissions']))
			return true;
	}
	
	msg($lang['action_not_allowed'] . " ($action)", -1);
    
    return false;
}

function auth_get_userinfo($userid) {
    global $auth;
    $ui = array();
    
    if($userid == 'guest') {
    	$ui['userid'] = 'guest';
    	$ui['fullname'] = 'Guest';
    	$ui['email'] = '';
        $ui['groups'] = array('@guest');
    	return $ui;
    }
    if(array_key_exists($userid, $auth)) {
        $ui['userid'] = $userid;
        $ui['fullname'] = array_get($auth[$userid], 'fullname', $userid);
        $ui['email']    = array_get($auth[$userid], 'email', '');
        $ui['groups']   = array_get($auth[$userid], 'groups', array());
        return $ui;
    }

    return null;
}

function auth_get_users() {
	global $auth;
	global $conf;
	$users = array();

    if(!$conf['auth_enabled'] || $conf['auth_allow_guest'])
    	$users['guest'] = auth_get_userinfo('guest');
	
	foreach($auth as $userid => $dummy) {
		if (substr($userid, 0, 1) === "@")
			continue;
		$userinfo = auth_get_userinfo($userid);
		$users[$userid] = $userinfo;
	}

    return $users;
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
    $file = AB_STATEDIR."/_htcookiesalt";
    $salt = @file($file);
    if(empty($salt)){
        $salt = uniqid(rand(), true);
        $fd = fopen($file, "w");
        if(!$fd) return $salt;
        fwrite($fd, $salt);
        fclose($fd);
        fix_fmode($file);
    }
    return $salt;
}


?>