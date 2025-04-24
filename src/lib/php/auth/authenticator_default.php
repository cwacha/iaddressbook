<?php
/**
     * iAddressBook adressbook functions
     *
     * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
     * @author     Clemens Wacha <clemens.wacha@gmx.net>
	 */
    require_once(AB_BASEDIR.'/lib/php/blowfish.php');

class Authenticator {
        var $accounts;
        var $accounts_default;

    function __construct() {
        $this->accounts = array();
        $this->accounts_default = array();
    }

    function Authenticator() {
        $this->__construct();
    }

    function init() {
        $this->load_accounts();
	}

    // return accountid on success, null else
    function authenticate($request) {
        global $conf;

        $auth_login = '';
        // check for basic headers
        if(isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
            //msg("php_auth found");
            $auth_login = $_SERVER['PHP_AUTH_USER'];
            $auth_pass = $_SERVER['PHP_AUTH_PW'];
        }
        if(isset($_SERVER['HTTP_AUTHORIZATION'])) {
            //msg("HTTP_AUTHORIZATION found");
            $credentials = explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)), 2);

            if (2 === count($credentials)) {
                $auth_login = $credentials[0];
                $auth_pass = $credentials[1];
            }
        }

        // check for request values
        if(isset($request['username'])) {
            //msg("username found");

            $auth_login = array_get($request, 'username');
            $auth_pass  = array_get($request, 'password');
        }
        $sticky = array_get($request, 'rememberme', false) ? true: false;            

        // read cookie information
        if(empty($auth_login) and isset($_COOKIE[AB_COOKIE])) {
            $cookie = base64_decode($_COOKIE[AB_COOKIE]);
            list($auth_login, $sticky, $auth_pass) = explode('\|', $cookie, 3);
            //msg("lg: $auth_login, sticky: $sticky, pw: $auth_pass");
            $auth_pass = PMA_blowfish_decrypt($auth_pass, $this->cookiesalt());
        }
        if($conf['auth_allow_guest'] && $auth_login == 'guest')
            return 'guest';

        if(!empty($auth_login) && $this->check_password($auth_login, $auth_pass)) {
            // login successful            
            if($sticky) {
                // user remains logged in
                $time = time()+60*60*24*365; //one year
                $auth_pass = PMA_blowfish_encrypt($auth_pass, $this->cookiesalt());
                $cookie = base64_encode("$auth_login|$sticky|$auth_pass");
                setcookie(AB_COOKIE, $cookie, $time, '/');
            }
            
            return $auth_login;
        }

        return null;
    }

    // validates accountid and password, return true if credentials mathc, false else
    function check_password($accountid, $password) {
        $account = $this->get_account($accountid);
        if($account == null) {
            //msg("login action=failed user=$accountid");
            return false;
        }

        $setpw = array_get($account, 'password');
        if($setpw == password_verify($password, $setpw)) {
            //msg("login action=success user=$userid");
            return true;
        }
        //msg("login action=failed user=$userid");
        return false;
    }

    /**
     * Creates a random key to encrypt the password in cookies
     *
     * This function tries to read the password for encrypting
     * cookies from AB_STATEDIR.'/_htcookiesalt'
     * if no such file is found a random key is created and
     * and stored in this file.
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     *
     * @return  string
     */
    function cookiesalt(){
        global $conf;
        $file = AB_STATEDIR."/_htcookiesalt";
        if(file_exists($file))
            $salt = file($file);
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

    function get_accounts() {
        return $this->accounts;
    }

    function get_account($accountid) {
        if(array_key_exists($accountid, $this->accounts))
            return $this->accounts[$accountid];
        return null;
    }

    function set_account($accountid, $account) {
        if(empty($accountid))
            return;

        if($account != null) {
            // save account
            // hash the password if its not yet hashed
            $password = array_get($account, 'password', '');
            $pw = password_get_info($password);
            if($pw['algo'] == 0 && $password != '!')
                $account['password'] = password_hash($password, PASSWORD_DEFAULT);

            $this->accounts[$accountid] = $account;
        } else {
            // delete account
            unset($this->accounts[$accountid]);
        }
    }

    function load_accounts() {
        // load accounts (account info and passwords)
        include(AB_BASEDIR.'/lib/default/accounts.php');
        $this->accounts_default = $accounts;
        
        $file = AB_CONFDIR.'/accounts.php';
        if(file_exists($file))
            include($file);
        $this->accounts = $accounts;
    }

    function save_accounts() {
        $filename = AB_CONFDIR.'/accounts.php';
    
        if(!is_array($this->accounts)) {
            msg("Internal error while saving accounts.php: accounts array empty", -1);
            return false;
        }

        $new_config = array();
        foreach($this->accounts as $accountid => $account) {
            if(!array_key_exists($accountid, $this->accounts_default) || $this->accounts[$accountid] != $this->accounts_default[$accountid]) {
                $new_config[$accountid] = $account;
            }
        }
        if(empty($new_config)) {
            unlink($filename);
            return true;
        }

        $header = array();
        $header[] = "/**";
        $header[] = " * This is the AddressBook's accounts file";
        $header[] = " * This is a piece of PHP code so PHP syntax applies!";
        $header[] = " *";
        $header[] = " * Automatically generated file. Do not modify!";
        $header[] = " */\n\n";

        $fd = fopen($filename, "w");
        if(!$fd) {
            msg(str_replace('$1', $filename, lang('account_error_cannot_write')), -1);
            return false;
        }
        fwrite($fd, "<?php\n\n");
        
        foreach($header as $line) {
            fwrite($fd, $line . "\n");
        }

        $data = array_to_text($new_config, '$accounts');
        fwrite($fd, $data);

        fwrite($fd, "\n\n?>");
        fclose($fd);
        fix_fmode($filename);

        return true;
    }

}

?>
