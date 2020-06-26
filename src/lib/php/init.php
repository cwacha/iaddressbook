<?php
    /**
     * iAddressBook initialization routine
     *
     * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
     * @author     Clemens Wacha <clemens.wacha@gmx.net>
     */


function init() {
    global $conf;

    /**
    * Initialize some defaults
    */
    date_default_timezone_set('UTC');
    
    global $VERSION; 
    $VERSION = file_get_contents(AB_BASEDIR.'/VERSION');
    
    // set up error reporting to sane values
    @ini_set('display_errors', 'On');
    error_reporting(E_ALL);

    // make session rewrites XHTML compliant
    @ini_set('arg_separator.output', '&amp;');    
    
    //Mapping PHP errors to exceptions
    function exception_error_handler($errno, $errstr, $errfile, $errline ) {
    	if(error_reporting() != 0)
	    	msg("$errstr (errno=$errno errfile=$errfile:$errline)", -1);
    }
    set_error_handler("exception_error_handler");
        
    load_config();

    // define baseURL
    //AB_BASEURI = /uri
    //AB_URL  = http://www.example.com:80/uri
    if(!defined('AB_BASEURI'))  define('AB_BASEURI',getBaseURL());
    if(!defined('AB_URL'))  define('AB_URL',getBaseURL(true));

    // define Template baseURL
    if(!defined('AB_TPL')) {
        define('AB_TPL', AB_BASEURI.'lib/tpl/'.$conf['template'].'/');
    }

    // define cookie and session id
    if (!defined('AB_COOKIE')) define('AB_COOKIE', 'AB'.md5(AB_URL));
    
    // init session
    if(!empty($conf['session_name'])) session_name($conf['session_name']);
    else session_name('iAddressBook');

    if (!headers_sent()) {
        session_set_cookie_params(0, AB_BASEURI);
        session_start();
    }

    // set register_globals to off
    if (ini_get('register_globals')) {
        $array = array('_REQUEST', '_SESSION', '_SERVER', '_ENV', '_FILES');
        foreach ($array as $value) {
            foreach ($GLOBALS[$value] as $key => $var) {
                if ($var === $GLOBALS[$key]) {
                    unset($GLOBALS[$key]);
                }
            }
        }
    }
    
    // we have to re-register all variables... register_globals sucks...
    load_config();

    // load meta information
    global $meta;
    $meta = array();
    require_once(AB_BASEDIR.'/lib/php/meta.php');
    
    //prepare language array
    global $lang;
    $lang = array();
    
    if(!empty($_SESSION['lang'])) $conf['lang'] = $_SESSION['lang'];
    if(!empty($_REQUEST['lang']) && strlen($_REQUEST['lang']) < 6) $conf['lang'] = $_REQUEST['lang'];
    
    //load the language files
    require_once(AB_LANGDIR.'/en/lang.php');
    @include_once(AB_LANGDIR.'/'.$conf['lang'].'/lang.php');
    @include_once(AB_CONFDIR.'/lang/'.$conf['lang'].'/lang.php');

    $_SESSION['lang'] = $conf['lang'];

    //configure mobile template
	if(isset($_REQUEST['mobile']))
		$_SESSION['mobile'] = (bool)$_REQUEST['mobile'];

	// initial redirect to mobile template
	if(!isset($_SESSION['mobile'])) {
		$_SESSION['mobile'] = false;
		if(isMobileClient())
			$_SESSION['mobile'] = true;
	}
	
    if($_SESSION['mobile']) {
    	if(strpos($conf['template'], '-mobile') === false) {
    		$mobileTemplate = $conf['template'] . '-mobile';
    		$mobileTemplateDir = AB_BASEDIR.'/lib/tpl/'.$mobileTemplate.'/';
    		if(is_dir($mobileTemplateDir))
    			$conf['template'] = $mobileTemplate;
    	}
    }

    init_creationmodes();
}

function load_config() {
    global $defaults;
    global $conf;

    $defaults = array();
    $conf = array();

    // load defaults
    include(AB_BASEDIR.'/lib/default/config.php');
    $defaults = $conf;

    // load config
    $filename = AB_CONFDIR.'/config.php';
    @include($filename);
}


/**
 * Sets the internal config values fperm and dperm which, when set,
 * will be used to change the permission of a newly created dir or
 * file with chmod. Considers the influence of the system's umask
 * setting the values only if needed.
 */
function init_creationmodes() {
    global $conf;
    
    // make sure we have fmode/dmode as integers
    $conf['fmode'] = octdec($conf['fmode']);
    $conf['dmode'] = octdec($conf['dmode']);
    
    // get system umask, fallback to 0 if none available
    $umask = @umask();
    if(!$umask) $umask = 0000;
    
    // check what is set automatically by the system on file creation
    // and set the fperm param if it's not what we want
    $auto_fmode = 0666 & ~$umask;
    if($auto_fmode != $conf['fmode']) $conf['fperm'] = $conf['fmode'];
    
    // check what is set automatically by the system on file creation
    // and set the dperm param if it's not what we want
    $auto_dmode = $conf['dmode'] & ~$umask;
    if($auto_dmode != $conf['dmode']) $conf['dperm'] = $conf['dmode'];
}

/**
 * Returns the full absolute URL to the directory where
 * DokuWiki is installed in (includes a trailing slash)
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */

function getBaseURL($abs=false){
    global $conf;
    //if canonical url enabled always return absolute
    if($conf['canonical']) $abs = true;
    
    if($conf['basedir']){
        $dir = $conf['basedir'].'/';
    }elseif($_SERVER['SCRIPT_NAME']){
        $dir = dirname($_SERVER['SCRIPT_NAME']).'/';
    }elseif($_SERVER['DOCUMENT_ROOT'] && $_SERVER['SCRIPT_FILENAME']){
        $dir = preg_replace ('/^'.preg_quote($_SERVER['DOCUMENT_ROOT'],'/').'/','',
                             $_SERVER['SCRIPT_FILENAME']);
        $dir = dirname('/'.$dir).'/';
    }else{
        $dir = dirname($_SERVER['PHP_SELF']).'/';
    }
    
    $dir = str_replace('\\','/',$dir); #bugfix for weird WIN behaviour
    $dir = preg_replace('#//+#','/',$dir);
    
    //handle script in lib/exe dir
    $dir = preg_replace('!lib/exe/$!','',$dir);
    
    //finish here for relative URLs
    if(!$abs) return $dir;
    
    //use config option if available
    if($conf['baseurl']) return $conf['baseurl'].$dir;
    
    //split hostheader into host and port
    list($host, $port) = explode(':', $_SERVER['HTTP_HOST'] . ':');
    if(!$port)  $port = $_SERVER['SERVER_PORT'];
    if(!$port)  $port = 80;
    
    // see if HTTPS is enabled - apache leaves this empty when not available,
    // IIS sets it to 'off', 'false' and 'disabled' are just guessing
    
    $proto = 'http://';
    if(array_key_exists('HTTPS', $_SERVER)) {
        if (preg_match('/^(|off|false|disabled)$/i',$_SERVER['HTTPS'])){
            $proto = 'http://';
            if ($port == '80') {
                $port='';
            }
        } else {
            $proto = 'https://';
            if ($port == '443') {
                $port='';
            }
        }
    }
    
    if($port) $port = ':'.$port;
    
    return $proto.$host.$port.$dir;
}

function isMobileClient() {
	$mobileAgents = array("iPhone", "iPod", "Android", "Blackberry", "Series60" );
	$userAgent = $_SERVER['HTTP_USER_AGENT'];
	foreach($mobileAgents as $agent) {
		if( strpos($userAgent, $agent) === false) {
			continue;
		}
		return true;
	}
	return false;
}

function getWebAppURI() {
    global $webapp;
    $baseuri = AB_BASEURI;
    $webappuri = $baseuri . $webapp;

    $webappuri = preg_replace("/\/$/", "", $webappuri);
    return $webappuri;
}

?>