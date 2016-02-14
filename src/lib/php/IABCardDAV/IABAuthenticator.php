<?php

namespace Sabre\DAV\Auth\Backend;

use Sabre\DAV;
use Sabre\HTTP;
use Sabre\HTTP\RequestInterface;
use Sabre\HTTP\ResponseInterface;

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
	function validateUserPass($username, $password) {
		return auth_login($username, $password);
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
		
		if (!$conf ['auth_enabled']) {
			return [true, $this->principalPrefix . 'guest'];
		}

		// workaround fast_cgi Basic Auth functionality
		list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':' , base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));

        $auth = new HTTP\Auth\Basic(
            $this->realm,
            $request,
            $response
        );

        $userpass = $auth->getCredentials();
        if (!$userpass) {
            return [false, "No 'Authorization: Basic' header found. Either the client didn't send one, or the server is misconfigured (you might need to turn off FastCGI and use PHP as Apache module or use 'RewriteEngine on' and 'RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization},L]).' in .htaccess"];
        }
        if (!$this->validateUserPass($userpass[0], $userpass[1])) {
            return [false, "Username or password invalid"];
        }
        return [true, $this->principalPrefix . $userpass[0]];

    }
	
}
