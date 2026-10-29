<?php

namespace Sabre\DAV\Auth\Backend;

use Sabre\DAV;
use Sabre\HTTP;
use Sabre\HTTP\RequestInterface;
use Sabre\HTTP\ResponseInterface;

class IABAuthenticator extends AbstractBasic {

    private $sc = null;

    function __construct() {
        //parent::__construct();
        $this->sc = \SecurityController::getInstance();
    }

    /**
     * Validates a username and password
     *
     * This method is unused here. All checking happens directly from check()
     *
     * @param string $username
     * @param string $password
     * @return bool
     */
    
	function validateUserPass($username, $password) {
		return true;
	}

    /**
     * When this method is called, the backend must check if authentication was
     * successful.
     *
     * The returned value must be one of the following
     *
     * [true, "principals/username"]
     * [false, "reason for failure"]
     *
     * If authentication was successful, it's expected that the authentication
     * backend returns a so-called principal url.
     *
     * Examples of a principal url:
     *
     * principals/admin
     * principals/user1
     * principals/users/joe
     * principals/uid/123457
     *
     * If you don't use WebDAV ACL (RFC3744) we recommend that you simply
     * return a string such as:
     *
     * principals/users/[username]
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return array
     */
    function check(RequestInterface $request, ResponseInterface $response) {
        global $conf;

        if($conf['auth_enabled'] == false) {
            return [true, $this->principalPrefix . 'guest'];
        }

        if(array_get($_SESSION, 'authorized', false)) {
            // the user is already logged in
            $accountid = $_SESSION['accountid'];
            $account = $this->sc->get_account($accountid);
            $_SESSION['account'] = $account;
            return [true, $this->principalPrefix . $accountid];
        }
        
        $accountid = $this->sc->authenticate($_REQUEST);
        /*
        if($accountid == null && $conf['auth_allow_guest']) {
            $accountid = 'guest';
        }
        */

        $account = $this->sc->get_account($accountid);
        if($account == null) {
            // authentication failed or no roles present
            $_SESSION['authorized'] = false;
            unset($_SESSION['accountid']);
            unset($_SESSION['account']);

            msg("IABAuthenticator::check: Username or password invalid");
            return [false, "Username or password invalid"];
        }

        $_SESSION['authorized'] = true;
        $_SESSION['accountid'] = $accountid;
        $_SESSION['account'] = $account;
        msg("IABAuthenticator::check: login successful for ".$this->principalPrefix . $accountid);
        return [true, $this->principalPrefix . $accountid];
    }
	
}
