<?php
    /**
     * iAddressBook initialization routine
     *
     * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
     * @author     Clemens Wacha <clemens.wacha@gmx.net>
     */

    if(!defined('AB_BASEDIR')) define('AB_BASEDIR',realpath(dirname(__FILE__).'/../../'));
    require_once(AB_BASEDIR.'/lib/php/include.php');
    require_once(AB_BASEDIR.'/lib/php/common.php');

    /**
    * Initialize some defaults
    */
    
    global $VERSION; 
    $VERSION = file_get_contents(AB_BASEDIR.'/VERSION');
    
    // set up error reporting to sane values
    @ini_set('display_errors', 'On');
    error_reporting(E_ALL ^ E_NOTICE);
    //error_reporting(E_ALL);

    // define baseURL
    if(!defined('AB_BASE')) define('AB_BASE',getBaseURL());
    if(!defined('AB_URL'))  define('AB_URL',getBaseURL(true));
    
    // define cookie and session id
    if (!defined('AB_COOKIE')) define('AB_COOKIE', 'AB'.md5(AB_URL));
    
    //prepare config array()
    global $conf;
    $conf = array();
    
    // remember defaults
    global $defaults;
    $defaults = array();

    // load the config file(s)
    require_once(AB_BASEDIR.'/lib/default/config.php');
    $defaults = $conf;
    @include_once(AB_CONFDIR.'/config.php');
    
    // init session
    if(!empty($conf['session_name'])) session_name($conf['session_name']);
    else session_name('iAddressBook');

    if (!headers_sent()) {
        session_set_cookie_params(0, AB_BASE);
        session_start();
    }

    // set register_globals to off
    if (ini_get(register_globals)) {
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
    
    //prepare config array()
    $conf = array();
    $defaults = array();

    // load the config file(s) (again...)
    @include(AB_BASEDIR.'/lib/default/config.php');
    $defaults = $conf;
    @include(AB_CONFDIR.'/config.php');
        
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
    $_SESSION['lang'] = $conf['lang'];
        
    // define Template baseURL
    if(!defined('AB_TPL')) define('AB_TPL', AB_BASE.'lib/tpl/'.$conf['template'].'/');
    
    // make session rewrites XHTML compliant
    @ini_set('arg_separator.output', '&amp;');    
    
    // kill magic quotes
    if (get_magic_quotes_gpc()) {
        if (!empty($_GET))    remove_magic_quotes($_GET);
        if (!empty($_POST))   remove_magic_quotes($_POST);
        if (!empty($_COOKIE)) remove_magic_quotes($_COOKIE);
        if (!empty($_REQUEST)) remove_magic_quotes($_REQUEST);
        //if (!empty($_SESSION)) remove_magic_quotes($_SESSION);
        @ini_set('magic_quotes_gpc', 0);
    }
    @set_magic_quotes_runtime(0);
    @ini_set('magic_quotes_sybase',0);
    

    init_creationmodes();
    

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
 * remove magic quotes recursivly
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function remove_magic_quotes(&$array) {
  foreach (array_keys($array) as $key) {
    if (is_array($array[$key])) {
      remove_magic_quotes($array[$key]);
    }else {
      $array[$key] = stripslashes($array[$key]);
    }
  }
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
  list($host, $port) = explode(':', $_SERVER['HTTP_HOST']);
  if(!$port)  $port = $_SERVER['SERVER_PORT'];
  if(!$port)  $port = 80;

  // see if HTTPS is enabled - apache leaves this empty when not available,
  // IIS sets it to 'off', 'false' and 'disabled' are just guessing
  if (preg_match('/^(|off|false|disabled)$/i',$_SERVER['HTTPS'])){
    $proto = 'http://';
    if ($port == '80') {
      $port='';
    }
  }else{
    $proto = 'https://';
    if ($port == '443') {
      $port='';
    }
  }

  if($port) $port = ':'.$port;

  return $proto.$host.$port.$dir;
}


?>