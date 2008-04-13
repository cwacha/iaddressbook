<?php

    /**
    * Initialize some defaults
    */
    
    // define the include path
    if(!defined('AB_INC')) define('AB_INC',realpath(dirname(__FILE__).'/../').'/');
    require_once(AB_INC.'functions/common.php');
    
    // define config path 
    if(!defined('AB_CONF')) define('AB_CONF',AB_INC.'conf/');
    
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

    // load the config file(s)
    require_once(AB_CONF.'defaults.php');
    @include_once(AB_CONF.'config.php');
    
    // init session
    if(!empty($conf['session_name'])) session_name($conf['session_name']);
    else session_name('iAddressBook');

    if (!headers_sent()) {
        session_set_cookie_params(0, AB_BASE);
        session_start();
    }
    
    // set register_globals to off
    if (ini_get('register_globals')) {
        foreach($GLOBALS as $s_variable_name => $m_variable_value) {
            if (!in_array($s_variable_name, array('GLOBALS', 'argv', 'argc', '_FILES', '_COOKIE', '_POST', '_GET', '_SERVER', '_ENV', '_SESSION', '_REQUEST', 's_variable_name', 'm_variable_value', 'conf'))) {
               unset($GLOBALS[$s_variable_name]);
            }
        }
        unset($GLOBALS['s_variable_name']);
        unset($GLOBLAS['m_variable_value']);
        
        @ini_set('register_globals', 'Off');
    }    

    // EVIL HACK: we have to re-read the configuration... register_globals sucks
    @include(AB_CONF.'defaults.php');
    @include(AB_CONF.'config.php');
    
    //prepare language array
    global $lang;
    $lang = array();
    
    if(!empty($_SESSION['lang'])) $conf['lang'] = $_SESSION['lang'];
    if(!empty($_REQUEST['lang']) && strlen($_REQUEST['lang']) < 6) $conf['lang'] = $_REQUEST['lang'];
    
    //load the language files
    require_once(AB_INC.'lang/en/lang.php');
    @include_once(AB_INC.'lang/'.$conf['lang'].'/lang.php');
    $_SESSION['lang'] = $conf['lang'];
    
    // define main script
    //if(!defined('DOKU_SCRIPT')) define('DOKU_SCRIPT','doku.php');
    
    // define Template baseURL
    if(!defined('AB_TPL')) define('AB_TPL', AB_BASE.'tpl/'.$conf['template'].'/');
    
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
    
    /*
    // disable gzip if not available
    if($conf['usegzip'] && !function_exists('gzopen')){
    $conf['usegzip'] = 0;
    }
    
    
    // make real paths and check them
    init_paths();
    init_files();
    
    // automatic upgrade to script versions of certain files
    scriptify(DOKU_CONF.'users.auth');
    scriptify(DOKU_CONF.'acl.auth');
    
*/

/**
 * Sets the internal config values fperm and dperm which, when set,
 * will be used to change the permission of a newly created dir or
 * file with chmod. Considers the influence of the system's umask
 * setting the values only if needed.
 */
function init_creationmodes() {
  global $conf;

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


/**
 * Append a PHP extension to a given file and adds an exit call
 *
 * This is used to migrate some old configfiles. An added PHP extension
 * ensures the contents are not shown to webusers even if .htaccess files
 * do not work
 *
 * @author Jan Decaluwe <jan@jandecaluwe.com>
 */
function scriptify($file) {
  // checks
  if (!is_readable($file)) {
    return;
  }
  $fn = $file.'.php';
  if (@file_exists($fn)) {
    return;
  }
  $fh = fopen($fn, 'w');
  if (!$fh) {
    die($fn.' is not writable!');
  }
  // write php exit hack first
  fwrite($fh, "# $fn\n");
  fwrite($fh, '# <?php exit()?>'."\n");
  fwrite($fh, "# Don't modify the lines above\n");
  fwrite($fh, "#\n");
  // copy existing lines
  $lines = file($file);
  foreach ($lines as $line){
    fwrite($fh, $line);
  }
  fclose($fh);
  //try to rename the old file
  @rename($file,"$file.old");
}




?>