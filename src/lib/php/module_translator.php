<?php
    /**
     * iAddressBook Translator Class
     *
     * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
     * @author     Clemens Wacha <clemens.wacha@gmx.net>
     */

require_once(AB_BASEDIR.'/lib/php/common.php');

class Translator {

	private $default_langcode = 'en';

    private function __construct() {
    }

    public static function getInstance() {
        static $instance = null;
        if($instance == null) {
            $instance = new Translator();
        }
        return $instance;
    } 

    public function init() {
/*
        global $conf;

        $authenticator_plugin = array_get($conf, 'authenticator_plugin', 'authenticator_default');
        include_once(AB_BASEDIR.'/lib/php/auth/'.$authenticator_plugin.'.php');
        $this->authenticator = new Authenticator();
        $this->authenticator->init();

        $authorizer_plugin = array_get($conf, 'authorizer_plugin', 'authorizer_default');
        include_once(AB_BASEDIR.'/lib/php/auth/'.$authorizer_plugin.'.php');
        $this->authorizer = new Authorizer();
        $this->authorizer->init();
*/
    }

    public function do_action($request, $action) {
        switch($action) {
	        case 'translator_save':
	            $this->lang_save($request);        
	            break;
            default:
        }
    }

    private function lang_save($request) {
    	global $conf;

    	$new_lang = array();
    	$lang_code = $conf['lang'];
    	$new_lang['lang_code'] = $lang_code;

    	foreach($request as $key => $value) {
    		if(strpos($key, "lang_") !== 0)
    			continue;
    		$key = substr($key, 5);
    		if(!empty($value))
    			$new_lang[$key] = $value;
    	}

    	$author = array_get($new_lang, "lang_author", "");
    	if(empty($author)) {
    		// load author from header in original file
    		$filename = AB_LANGDIR.'/'.$lang_code.'/lang.php';
    		$author = $this->load_author_from_file($filename);
    		$new_lang['lang_author'] = $author['lang_author'];
    		$new_lang['lang_author_email'] = $author['lang_author_email'];
    	}

    	$this->create_config_lang_folder($lang_code);
    	$filename = AB_CONFDIR.'/lang/'.$lang_code.'/lang.php';
    	$this->write_file($filename, $new_lang);
		$_SESSION['viewname'] = '/admin';
    }

    private function create_config_lang_folder($lang_code) {
    	$conf_langdir = AB_CONFDIR.'/lang';
    	$conf_langcodedir = $conf_langdir . '/'.$lang_code;

    	if(!is_dir($conf_langdir)) {
    		mkdir($conf_langdir);
    	}
    	if(!is_dir($conf_langcodedir)) {
    		mkdir($conf_langcodedir);
    	}
    	fix_dmode($conf_langdir);
		fix_dmode($conf_langcodedir);
    }

    private function write_file($filename, $lang) {
		$fd = fopen($filename, "w");
		if(!$fd) {
			msg("could not write lang: $filename", -1);
			return;
		}

		$lang_name = array_get($lang, "lang_name", array_get($lang, "lang_code", ""));
		$lang_author = array_get($lang, "lang_author", "Unknown");
		$lang_author_email = array_get($lang, "lang_author_email", "");
		if(!empty($lang_author_email))
			$lang_author_email = "<" . $lang_author_email . ">";

		fwrite($fd, "<?php\n");
		fwrite($fd, "/*\n");
		fwrite($fd, " * " . $lang_name . " language file\n");
		fwrite($fd, " *\n");
		fwrite($fd, " * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)\n");
		fwrite($fd, " * @author     " . $lang_author. " " . $lang_author_email . "\n");
		fwrite($fd, " */\n\n");

		fwrite($fd, array_to_text($lang, '$lang'));

		fwrite($fd, "\n\n?>");

		fclose($fd);
		fix_fmode($filename);
    }

    // returns array with author name and author email
    private function load_author_from_file($filename) {
    	$ret = array();
    	$ret["lang_author"] = "";
    	$ret["lang_author_email"] = "";

    	$file_array = @file($filename);
    	if(!$file_array)
    		return $ret;

    	foreach($file_array as $line) {
    		$pos = strpos($line, "@author");
    		if($pos > 0) {
    			$line = trim(substr($line, $pos + 7));
    			$data = preg_split("/[<>]+/", $line);
    			if($data) {
    				if(count($data) >= 1) {
    					$ret["lang_author"] = trim($data[0]);
    				}
    				if(count($data) >= 2) {
    					$ret["lang_author_email"] = trim($data[1]);
    				}
    			}
    			break;
    		}
    	}
    	return $ret;
	}

	public function get_default_lang_array() {
    	$lang = array();
    	include(AB_LANGDIR.'/'.$this->default_langcode.'/lang.php');
    	return $lang;
	}

	public function get_conf_lang_array() {
		global $conf;
	    $lang = array();
	    @include(AB_LANGDIR.'/'.$conf['lang'].'/lang.php');
  		@include(AB_CONFDIR.'/lang/'.$conf['lang'].'/lang.php');
  		return $lang;
	}

}

?>
