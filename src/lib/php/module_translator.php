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
        global $meta;

        $meta['lang'] = array('multichoice', '_choices' => $this->get_all_lang_dirs());
    }

    public function do_action($request, $action) {
        switch($action) {
            case 'language_save':
                $this->lang_save($request);        
                break;
            case 'language_delete':
                $this->lang_delete($request);
                break;
            default:
        }
    }

    // TODO: function unused. translator not instantiated at time of init() /init.php
    public function activate_language() {
        global $conf;

        //prepare language array
        global $lang;
        $lang = array();
        
        if(!empty($_SESSION['lang'])) $conf['lang'] = $_SESSION['lang'];
        if(!empty($_REQUEST['lang']) && strlen($_REQUEST['lang']) < 6) $conf['lang'] = $_REQUEST['lang'];
        
        //load the language files
        require(AB_LANGDIR.'/en/lang.php');
        $file = AB_LANGDIR.'/'.$conf['lang'].'/lang.php';
        if(file_exists($file))
            include($file);

        $file = AB_CONFDIR.'/lang/'.$conf['lang'].'/lang.php';
        if(file_exists($file))
            include($file);

        $_SESSION['lang'] = $conf['lang'];
    }

    private function lang_save($request) {
    	$new_lang = array();
    	foreach($request as $key => $value) {
    		if(strpos($key, "lang_") !== 0)
    			continue;
    		$key = substr($key, 5);
    		if(!empty($value))
    			$new_lang[$key] = $value;
    	}
        if(!array_key_exists('lang_code', $new_lang)) {
            msg(lang("lang_error_code_missing", -1));
            return;
        }
        $lang_code = $new_lang['lang_code'];

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
    	$this->write_file($filename.'.txt', $new_lang);
        msg(str_replace('$1', $filename, lang('lang_saved')), 1);
		$_SESSION['viewname'] = '/admin/translator';
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
            msg(str_replace('$1', $filename, lang('lang_error_cannot_write')), -1);
			return;
		}

		$lang_name = array_get($lang, "lang_name", array_get($lang, "lang_code", ""));
		$lang_author = array_get($lang, "lang_author", "Unknown");
		$lang_author_email = array_get($lang, "lang_author_email", "");
		if(!empty($lang_author_email))
			$lang_author_email = "<" . $lang_author_email . ">";

		fwrite($fd, "<?php\n");
		fwrite($fd, "/*\n");
		fwrite($fd, " * language file:". $lang_name . "\n");
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

        if(file_exists($filename))
    	    $file_array = file($filename);
        
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

    public function get_translation($lang_code = "") {
        global $conf;

        $lang_code = $this->sanitize_lang_code($lang_code);

        $config = array();

        // load defaults
        $lang = array();
        include(AB_LANGDIR.'/'.$this->default_langcode.'/lang.php');
        $config['defaults'] = $lang;

        // load specified language
        $lang = array();
        if(!empty($lang_code)) {
            $file = AB_LANGDIR.'/'.$lang_code.'/lang.php';
            if(file_exists($file))
                include($file);

            $file = AB_CONFDIR.'/lang/'.$lang_code.'/lang.php';
            if(file_exists($file))
                include($file);
        }
        $lang['lang_code'] = $lang_code;
        $config['lang'] = $lang;

        return $config;
    }

    public function sanitize_lang_code($lang_code) {
        return preg_replace("/[^A-Za-z_]+/", "", $lang_code);
    }

    public function get_all_languages() {
        $lang_dirs = $this->get_all_lang_dirs();

        $lang = array();
        include(AB_LANGDIR.'/'.$this->default_langcode.'/lang.php');
        $total = count($lang);

        $ret = array();
        foreach($lang_dirs as $lang_code) {
            $lang = array();
            $file = AB_LANGDIR.'/'.$lang_code.'/lang.php';
            if(file_exists($file))
                include($file);

            $file = AB_CONFDIR.'/lang/'.$lang_code.'/lang.php';
            if(file_exists($file))
                include($file);

            $count = count($lang);
            $percent = $count/$total * 100;

            $ret[] = array(
                'lang_code' => $lang_code,
                'lang_name' => array_get($lang, 'lang_name', ""),
                'lang_author' => array_get($lang, 'lang_author', ""),
                'lang_author_email' => array_get($lang, 'lang_author_email', ""),
                'count' => $count,
                'total' => $total,
                'percent' => round($percent),
                'stats' => round($percent)."% ($count/$total)"
                );
        }
        return $ret;
    }

    public function get_custom_language_uri($lang_code) {
        if(!empty($lang_code)) {
            if(file_exists(AB_CONFDIR.'/lang/'.$lang_code.'/lang.php'))
                return AB_BASEURI.'conf/lang/'.$lang_code.'/lang.php.txt';
        }
        return null;
    }

    private function get_all_lang_dirs() {
        $lang_dirs = $this->get_lang_dirs(AB_BASEDIR.'/lib/lang');
        $lang_dirs2 = $this->get_lang_dirs(AB_CONFDIR.'/lang');
        $lang_dirs = array_unique(array_merge($lang_dirs, $lang_dirs2));
        return $lang_dirs;
    }
    private function get_lang_dirs($basedir = AB_BASEDIR.'/lib/lang') {
        $ret = array();
        $dirs = scandir($basedir);
        if ($dirs === false)
            return $ret;
        foreach ($dirs as $dir) {
            if(is_dir($basedir.'/'.$dir) && (substr($dir, 0, 1) != '.')) {
                $ret[] = $dir;
            }
        }
        return $ret;
    }

    private function lang_delete($request) {
        $lang_code = array_get($request, 'lang_code', '');
        if(empty($lang_code))
            return;

        unlink(AB_CONFDIR.'/lang/'.$lang_code.'/lang.php');
        unlink(AB_CONFDIR.'/lang/'.$lang_code.'/lang.php.txt');
        rmdir(AB_CONFDIR.'/lang/'.$lang_code);

        $_SESSION['viewname'] = '/admin/translator';

    }

}

?>
