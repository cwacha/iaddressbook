<?php

namespace Sabre\DAV\Auth\Backend;

use Sabre\HTTP;
use Sabre\DAV;

if(!defined('AB_BASEDIR')) define('AB_BASEDIR',realpath(dirname(__FILE__)).'/../../..');
require_once(AB_BASEDIR.'/lib/php/include.php');
require_once(AB_BASEDIR.'/lib/php/init.php');
require_once(AB_BASEDIR.'/lib/php/module_auth.php');

class IABAuthenticator extends AbstractBasic {

    /**
     * Validates a username and password
     *
     * This method should return true or false depending on if login
     * succeeded.
     *
     * @param string $username
     * @param string $password
     * @return bool
     */
	public function validateUserPass($username, $password) {
		return auth_login($username, $password);
	}

	public function authenticate(DAV\Server $server, $realm) {
		global $conf;
		
		if (!$conf ['auth_enabled']) {
			$this->currentUser = 'guest';
			return true;
		}

		$auth = new HTTP\BasicAuth();
		$auth->setHTTPRequest($server->httpRequest);
		$auth->setHTTPResponse($server->httpResponse);
		$auth->setRealm($realm);
		$userpass = $auth->getUserPass();
		if (!$userpass) {
			$auth->requireLogin();
			throw new DAV\Exception\NotAuthenticated('No basic authentication headers were found');
		}
	
		// Authenticates the user
		if (!$this->validateUserPass($userpass[0],$userpass[1])) {
			$auth->requireLogin();
			msg("login invalid");
			throw new DAV\Exception\NotAuthenticated('Username or password does not match');
		}
		$this->currentUser = $userpass[0];
		return true;
	}
	
}
