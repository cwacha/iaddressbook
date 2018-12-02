<?php
    /**
     * iAddressBook authentication functions
     *
     * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
     * @author     Clemens Wacha <clemens.wacha@gmx.net>
     */

    require_once(AB_BASEDIR.'/lib/php/template.php');

    /*
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
*/

class SecurityController {
    private $authenticator = null;
    private $authorizer = null;

    private function __construct() {
    }

    /*
    function SecurityController() {
        $this->__construct();
    }
    */

    public static function getInstance() {
        static $instance = null;
        if($instance == null) {
            $instance = new SecurityController();
        }
        return $instance;
    } 

    function init() {
        global $conf;

        $authenticator_plugin = array_get($conf, 'authenticator_plugin', 'authenticator_default');
        include_once(AB_BASEDIR.'/lib/php/auth/'.$authenticator_plugin.'.php');
        $this->authenticator = new Authenticator();
        $this->authenticator->init();

        $authorizer_plugin = array_get($conf, 'authorizer_plugin', 'authorizer_default');
        include_once(AB_BASEDIR.'/lib/php/auth/'.$authorizer_plugin.'.php');
        $this->authorizer = new Authorizer();
        $this->authorizer->init();
    }

    function authenticate($request) {
        // authenticating
        $accountid = $this->authenticator->authenticate($request);
        if ($accountid == null) {
            //log.warn(SAT, "[action=\"login\" account=\"null\" result=\"failed\"] Authentication failed");
            return null;
        }

        return $accountid;
    }

    // return true if account has permission, false else
    function authorize($accountid, $permission) {
        return $this->authorizer->authorize($accountid, $permission);
    }

    function get_account($accountid) {
        $account = $this->authenticator->get_account($accountid);
        if($account == null)
            return null;
        $roles = $this->authorizer->get_roles($accountid);
        $permissions = $this->authorizer->get_permissions($accountid);
        $account['roles'] = $roles;
        $account['permissions'] = $permissions;

        return $account;
    }

    function get_accounts() {
        $a = $this->authenticator->get_accounts();
        $accounts = array();
        foreach($a as $accountid => $dummy) {
            $accounts[$accountid] = $this->get_account($accountid);
        }
        return $accounts;
    }

    function get_roles() {
        return $this->authorizer->get_roles();
    }

    function do_action($request, $action) {
        switch($action) {
            case 'account_save':
                $this->account_save($request);
                break;
            case 'account_password':
                $this->account_password($request);
                break;
            case 'account_mypassword':
                $this->account_mypassword($request);
                break;
            case 'account_delete':
                $this->account_delete($request);
                break;
            case 'role_save':
                $this->role_save($request);
                break;
            case 'role_delete':
                $this->role_delete($request);
                break;
            default:
        }
    }

    function account_save($request) {
        global $_SESSION;

        $accountid = $request['accountid'];
        $fullname = $request['fullname'];
        $email = $request['email'];
        $roles = $request['roles'];

        $account = $this->authenticator->get_account($accountid);
        if($account == null) {
            $account = array();
            $account['fullname'] = '';
            $account['email'] = '';
            $account['password'] = '';
        }
        $account['fullname'] = $fullname;
        $account['email'] = $email;

        $this->authenticator->set_account($accountid, $account);
        $this->authenticator->save_accounts();
        $this->authorizer->set_roles($accountid, $roles);
        $this->authorizer->save_access();
        
        $_SESSION['viewname'] = '/admin/accounts';
    }

    function account_password($request) {
        global $_SESSION;

        $accountid = $request['accountid'];
        $password = $request['password'];

        $account = $this->authenticator->get_account($accountid);
        if($account == null) {
            $account = array();
            $account['fullname'] = '';
            $account['email'] = '';
            $account['password'] = '';
        }
        $account['password'] = $password;

        $this->authenticator->set_account($accountid, $account);
        $this->authenticator->save_accounts();
        
        $_SESSION['viewname'] = '/admin/accounts';
    }

    function account_mypassword($request) {
        global $_SESSION;

        $accountid = $_SESSION['accountid'];
        $password = $request['password'];

        $account = $this->authenticator->get_account($accountid);
        if($account == null)
            return;
        $account['password'] = $password;

        $this->authenticator->set_account($accountid, $account);
        $this->authenticator->save_accounts();
        
        $_SESSION['viewname'] = '/profile';        
    }

    function account_delete($request) {
        global $_SESSION;

        $accountid = $request['accountid'];

        $this->authenticator->set_account($accountid, null);
        $this->authenticator->save_accounts();
        $this->authorizer->set_roles($accountid, null);
        $this->authorizer->save_access();
        
        $_SESSION['viewname'] = '/admin/accounts';        
    }

    function role_save($request) {
        global $_SESSION;

        $roleid = $request['roleid'];
        $permissions = $request['permissions'];

        $this->authorizer->set_role_permissions($roleid, $permissions);
        $this->authorizer->save_roles();
        
        $_SESSION['viewname'] = '/admin/roles';
    }

    function role_delete($request) {
        global $_SESSION;

        $roleid = $request['roleid'];
        $this->authorizer->set_role_permissions($roleid, null);
        $this->authorizer->save_roles();

        $_SESSION['viewname'] = '/admin/roles';
    }

}
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
/*
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
*/
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
/*
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
*/

/**
 * Log Out
 *
 * This function destroys the session cookie and logs the user out.
 *
 * @author  Clemens Wacha <clemens.wacha@gmx.net>
 *
 */
/*
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
*/

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
/*
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
*/
/**
 * Get all Accounts (except guest user)
 * This function returns an array of accountinfo arrays with all configured accounts
 * Array key is the accountid. An accountinfo array looks as follows:
 * accountinfo: (
 *  [accountid] => 'fred'
 *  [fullname] => 'Fred the Geek'
 *  [email] => 'fred@geeks.com'
 *  [groups] => array('@editor')
 * )
 *
 * @author  Clemens Wacha <clemens.wacha@gmx.net>
 *
 * @return  array of accountinfo arrays
 */
/*
function auth_get_accounts() {
	global $auth;
	global $conf;
	$accounts = array();

	foreach($auth as $accountid => $dummy) {
		if (substr($accountid, 0, 1) === "@")
			continue;
		$accountinfo = auth_get_userinfo($accountid);
		$accounts[$accountid] = $accountinfo;
	}

    return $accounts;
}
*/
/**
 * Get all Roles
 * This function returns an array of roleinfo arrays with all configured roles
 * Array key is the roleid. A roleinfo array looks as follows:
 * roleinfo: (
 *  [roleid] => '@editor'
 *  [permissions] => array('show', 'img', 'search', ...)
 * )
 *
 * @author  Clemens Wacha <clemens.wacha@gmx.net>
 *
 * @return  array of roleinfo arrays
 */
/*
function auth_get_roles() {
    global $auth;
    global $conf;
    $roles = array();

    foreach($auth as $roleid => $dummy) {
        if (substr($roleid, 0, 1) !== "@")
            continue;
        $roleinfo = array();
        $roleinfo['roleid'] = $roleid;
        $roleinfo['permissions'] = array_get($auth[$roleid], 'permissions', array());;
        $roles[$roleid] = $roleinfo;
    }

    return $roles;
}

function auth_save_user($userinfo) {
    global $auth;

    if(!is_array($userinfo)) {
        return false;
    }

    $userid = $userinfo['userid'];
    if(!array_key_exists($userid, $auth)) {
        $auth[$userid] = array();
    }
    $auth[$userid]['fullname'] = $userinfo['fullname'];
    $auth[$userid]['email'] = $userinfo['email'];
    $auth[$userid]['groups'] = $userinfo['groups'];
    $auth[$userid]['password'] = md5($userinfo['password']);

    auth_save($auth);
}

function auth_save_group($groupinfo) {
    global $auth;

    if(!is_array($groupinfo)) {
        return false;
    }

    $groupid = $groupinfo['groupid'];
    if(!array_key_exists($groupid, $auth)) {
        $auth[$groupid] = array();
    }
    $auth[$groupid]['permissions'] = $groupinfo['permissions'];

    auth_save($auth);
}
*/


?>