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

    public function init() {
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

    public function authenticate($request) {
        // authenticating
        $accountid = $this->authenticator->authenticate($request);
        if ($accountid == null) {
            //log.warn(SAT, "[action=\"login\" account=\"null\" result=\"failed\"] Authentication failed");
            return null;
        }

        return $accountid;
    }

    // return true if account has permission, false else
    public function authorize($accountid, $permission) {
        return $this->authorizer->authorize($accountid, $permission);
    }

    // return true if user is logged in and has permission, or true if authentication is disabled
    public function has_permission($permission) {
        global $conf;

        if($conf['auth_enabled'] == true) {
            if(!$this->authorize(array_get($_SESSION, 'accountid'), $permission)) {
                return false;
            }            
        }

        return true;
    }

    public function get_account($accountid) {
        $account = $this->authenticator->get_account($accountid);
        if($account == null)
            return null;
        $roles = $this->authorizer->get_roles($accountid);
        $permissions = $this->authorizer->get_permissions($accountid);
        $account['roles'] = $roles;
        $account['permissions'] = $permissions;

        return $account;
    }

    public function get_accounts() {
        $a = $this->authenticator->get_accounts();
        $accounts = array();
        foreach($a as $accountid => $dummy) {
            $accounts[$accountid] = $this->get_account($accountid);
        }
        return $accounts;
    }

    public function get_roles() {
        return $this->authorizer->get_roles();
    }

    public function do_action($request, $action) {
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

    public function account_save($request) {
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

    public function account_password($request) {
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

    public function account_mypassword($request) {
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

    public function account_delete($request) {
        global $_SESSION;

        $accountid = $request['accountid'];

        $this->authenticator->set_account($accountid, null);
        $this->authenticator->save_accounts();
        $this->authorizer->set_roles($accountid, null);
        $this->authorizer->save_access();
        
        $_SESSION['viewname'] = '/admin/accounts';        
    }

    public function role_save($request) {
        global $_SESSION;

        $roleid = $request['roleid'];
        $permissions = $request['permissions'];

        $this->authorizer->set_role_permissions($roleid, $permissions);
        $this->authorizer->save_roles();
        
        $_SESSION['viewname'] = '/admin/roles';
    }

    public function role_delete($request) {
        global $_SESSION;

        $roleid = $request['roleid'];
        $this->authorizer->set_role_permissions($roleid, null);
        $this->authorizer->save_roles();

        $_SESSION['viewname'] = '/admin/roles';
    }

}

?>