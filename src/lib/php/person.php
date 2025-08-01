<?php
    /**
     * iAddressBook person representation
     *
     * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
     * @author     Clemens Wacha <clemens.wacha@gmx.net>
     */

    if(!defined('AB_BASEDIR')) define('AB_BASEDIR',realpath(dirname(__FILE__).'/../../'));
    require_once(AB_BASEDIR.'/lib/php/common.php');


class Person {
    var $title;
    var $firstname;
    var $firstname2;
    var $lastname;
    var $suffix;
    var $nickname;
    var $name;
    var $phoneticfirstname;
    var $phoneticlastname;

    var $jobtitle;
    var $department;
    var $organization;
    var $company;    // boolean

    var $birthdate;    // date as string: "YYYY-MM-DD"
    var $birthday;     // text birthday for birthday module??
    var $image;    // picture
    var $note;

    var $modification_ts;     // unix timestamp
    var $id;    // DB id
    var $uid;   // UUID string (new and no check for uniqueness yet)
    var $etag;  // edit tag integer that increases everytime the person is saved

    // embedded objects
    var $addresses;
    var $emails;
    var $phones;
    var $chathandles;
    var $relatednames;
    var $urls;
    
    // categories
    var $categories; // conains array of categories (keys will not match DB IDs!)
    var $isgroup;  // boolean that is true when this entry is a GROUP (required for CARDDAV)
    var $groupmembers; // members of the group as list of UIDs (for carddav...)

    function __construct() {
        $this->company = 0;
        $this->birthdate = '';

        $this->addresses = array();
        $this->emails = array();
        $this->phones = array();
        $this->chathandles = array();
        $this->relatednames = array();
        $this->urls = array();
        
        $this->categories = array();
        
        $this->id = 0;
        $this->uid = '';
        $this->etag = 0;
        $this->modification_ts = time();

        $this->isgroup = false;
        $this->groupmembers = array();
    }

    function Person() {
        $this->__construct();
    }
    
    function get_array() {
        $person = array();
        $person['title'] = $this->title;
        $person['firstname'] = $this->firstname;
        $person['firstname2'] = $this->firstname2;
        $person['lastname'] = $this->lastname;
        $person['suffix'] = $this->suffix;
        $person['nickname'] = $this->nickname;
        $person['name'] = $this->name();
        $person['phoneticfirstname'] = $this->phoneticfirstname;
        $person['phoneticlastname'] = $this->phoneticlastname;

        $person['jobtitle'] = $this->jobtitle;
        $person['department'] = $this->department;
        $person['organization'] = $this->organization;
        $person['company'] = $this->company;

        $person['birthdate'] = $this->birthdate;
        $person['note'] = $this->note;
        $person['modification_ts'] = $this->modification_ts;
        $person['id'] = $this->id;
        $person['uid'] = $this->uid;
        $person['etag'] = $this->etag;

        $person['addresses'] = $this->addresses;
        $person['emails'] = $this->emails;
        $person['phones'] = $this->phones;
        $person['chathandles'] = $this->chathandles;
        $person['relatednames'] = $this->relatednames;
        $person['urls'] = $this->urls;
        $person['categories'] = $this->categories;

        $person['isgroup'] = $this->isgroup;
        $person['groupmembers'] = $this->groupmembers;

        return $person;
    }
    
    function set_array($person) {
        if(!is_array($person)) return;
        
        $this->title              = $person['title'];
        $this->firstname          = $person['firstname'];
        $this->firstname2         = $person['firstname2'];
        $this->lastname           = $person['lastname'];
        $this->suffix             = $person['suffix'];
        $this->nickname           = $person['nickname'];
        $this->name               = $person['name'];
        $this->phoneticfirstname  = $person['phoneticfirstname'];
        $this->phoneticlastname   = $person['phoneticlastname'];

        $this->jobtitle           = $person['jobtitle'];
        $this->department         = $person['department'];
        $this->organization       = $person['organization'];
        $this->company            = $person['company'];

        $this->birthdate          = $person['birthdate'];
        $this->note               = $person['note'];
        $this->modification_ts    = $person['modification_ts'];
        
        $this->id                 = $person['id'];
        $this->uid                = $person['uid'];
        $this->etag               = $person['etag'];

        $this->addresses          = $person['addresses'];
        $this->emails             = $person['emails'];
        $this->phones             = $person['phones'];
        $this->chathandles        = $person['chathandles'];
        $this->relatednames       = $person['relatednames'];
        $this->urls               = $person['urls'];
        
        $this->categories         = $person['categories'];

        $this->isgroup            = $person['isgroup'];
        $this->groupmembers       = $person['groupmembers'];
        
        $this->validate();
    }
    
    function name($lastfirst = NULL) {
        global $conf;
        
        $ret = "";
        
        if($this->company) {
            $ret = $this->organization;
        }
        
        if(empty($ret)) {
            if($lastfirst === NULL) $lastfirst = $conf['lastfirst'];
            if($lastfirst == false) {
                $ret = $this->firstname . " " . $this->lastname;
            } else {
                $ret = $this->lastname . ", " . $this->firstname;
            }
            $ret = trim($ret, " ,");
        }
        
        if(empty($ret)) {
            $ret = $this->nickname;
        }
        
        if(empty($ret)) {
            $ret = $this->organization;
        }
        
        if(empty($ret)) {
            foreach($this->emails as $tmp) {
                $ret = $tmp['email'];
                if(!empty($ret)) break;
            }
        }
        
        if(empty($ret)) {
            foreach($this->chathandles as $tmp) {
                $ret = $tmp['handle'];
                if(!empty($ret)) break;
            }
        }
        
        if(empty($ret)) {
            $ret = lang('no_name');
        }
        
        return $ret;
    }    
    
    function validate() {
        if(!is_string($this->title)) $this->title = '';
        if(!is_string($this->firstname)) $this->firstname = '';
        if(!is_string($this->firstname2)) $this->firstname2 = '';
        if(!is_string($this->lastname)) $this->lastname = '';
        if(!is_string($this->suffix)) $this->suffix = '';
        if(!is_string($this->nickname)) $this->nickname = '';
        if(!is_string($this->phoneticfirstname)) $this->phoneticfirstname = '';
        if(!is_string($this->phoneticlastname)) $this->phoneticlastname = '';
        if(!is_string($this->jobtitle)) $this->jobtitle = '';
        if(!is_string($this->department)) $this->department = '';
        if(!is_string($this->organization)) $this->organization = '';
        if(!is_string($this->note)) $this->note = '';
        
        if(!is_string($this->birthdate) or strlen($this->birthdate) != 10) $this->birthdate = '';
        
        if(!is_bool($this->company)) $this->company = (bool)$this->company;
        
        if(!is_array($this->addresses)) $this->addresses = array();
        if(!is_array($this->emails)) $this->emails = array();
        if(!is_array($this->phones)) $this->phones = array();
        if(!is_array($this->chathandles)) $this->chathandles = array();
        if(!is_array($this->relatednames)) $this->relatednames = array();
        if(!is_array($this->urls)) $this->urls = array();
        if(!is_array($this->categories)) $this->categories = array();

        if(!is_bool($this->isgroup)) $this->isgroup = (bool)$this->isgroup;
        if(!is_array($this->groupmembers)) $this->groupmembers = array();
    }

    function add_address($address) {
    	if(!is_array($address))
    		return;
		$item = array (
				'label' => array_get($address, 'label', ''),
				'pobox' => array_get($address, 'pobox', ''),
				'ext_adr' => array_get($address, 'ext_adr', ''),
				'street' => array_get($address, 'street', ''),
				'city' => array_get($address, 'city', ''),
				'state' => array_get($address, 'state', ''),
				'zip' => array_get($address, 'zip', ''),
				'country' => array_get($address, 'country', ''),
				'template' => array_get($address, 'template', '')
		);
        $ok = false;
        foreach($item as $key => $value) {
        	if($key != 'label' && !empty($value)) {
        		$ok = true;
        	}
        }
        if($ok == true)
            array_push($this->addresses, $item);
    }
   
    function del_address($address) {
        if(is_array($address)) {
            $key = array_search($address, $this->addresses);
            if($key) unset($this->addresses[$key]);
        }
    }
    
    function get_address($label) {
        foreach($this->addresses as $value) {
            if($value['label'] == $label) return $value;
        }
        return false;
    }

    function add_email($email) {
        if(is_array($email) and !empty($email['email'])) {
            array_push($this->emails, $email);
        }
    }
   
    function del_email($email) {
        if(is_array($email)) {
            $key = array_search($email, $this->emails);
            if($key) unset($this->emails[$key]);
        }
    }

    function get_email($label) {
        foreach($this->emails as $value) {
            if($value['label'] == $label) return $value['email'];
        }
        return false;
    }

    function add_phone($phone) {
        if(is_array($phone) and !empty($phone['phone'])) {
            array_push($this->phones, $phone);
        }
    }
    
    function del_phone($phone) {
        if(is_array($phone)) {
            $key = array_search($phone, $this->phones);
            if($key) unset($this->phones[$key]);
        }
    }

    function get_phone($label) {
        foreach($this->phones as $value) {
            if($value['label'] == $label) return $value['phone'];
        }
        return false;
    }

    function add_chathandle($handle) {
        if(is_array($handle) and !empty($handle['handle'])) {
            array_push($this->chathandles, $handle);
        }    
    }

    function del_chathandle($handle) {
        if(is_array($handle)) {
            $key = array_search($handle, $this->chathandles);
            if($key) unset($this->chathandles[$key]);
        }
    }

    function get_chathandle($label, $type) {
        foreach($this->chathandles as $value) {
            if($value['label'] == $label && $value['type'] == $type) return $value['handle'];
        }
        return false;
    }
    
    function add_url($url) {
        if(is_array($url) and !empty($url['url'])) {
            if(!empty($url['url']) and strstr($url['url'], "://") == false) $url['url'] = "http://" . $url['url'];
            array_push($this->urls, $url);
        }
    }

    function del_url($url) {
        if(is_array($url)) {
            $key = array_search($url, $this->urls);
            if($key) unset($this->urls[$key]);
        }
    }

    function get_url($label) {
        foreach($this->urls as $value) {
            if($value['label'] == $label) return $value['url'];
        }
        return false;
    }

    function add_relatedname($rname) {
        if(is_array($rname) and !empty($rname['name'])) {
            array_push($this->relatednames, $rname);
        }    
    }

    function del_relatedname($rname) {
        if(is_array($rname)) {
            $key = array_search($rname, $this->relatednames);
            if($key) unset($this->relatednames[$key]);
        }
    }

    function get_relatedname($label) {
        foreach($this->relatednames as $value) {
            if($value['label'] == $label) return $value['name'];
        }
        return false;
    }
    
    function add_category($category) {
    	if(!is_object($category))
			return;
		
		foreach ( $this->categories as $cat ) {
			if ($cat->name() == $category->name())
				return;
		}
		//msg("adding category: ". $category->name() .", " .$category->displayName());
		$this->categories [] = $category;
	}
    
    function del_category_by_name($categoryName) {
    	if(!is_string($categoryName) || empty($categoryName))
    		return;
    	
    	foreach ( $this->categories as $key => $category ) {
    		if ($category->name() == $categoryName) {
    			unset($this->categories[$key]);
    		}
    	}
    }
    
    function clear_categories() {
    	$this->categories = array();
    }
    
    function sort_categories() {
    	global $CAT;
    	
    	$this->categories = $CAT->sort($this->categories); 
    }
    
    // return array of category class objects
    function get_categories() {
    	return $this->categories;
    }
    
    function add_groupmember($uid) {
    	$uid = strtolower($uid);
    	$this->groupmembers [$uid] = true; 
    	$this->isgroup = true;
    }

	function delete_groupmember($uid) {
		$uid = strtolower($uid);
		if (array_key_exists($uid, $this->groupmembers))
			unset($this->groupmembers[$uid]);
    }
    
    function clear_groupmembers() {
    	$this->groupmembers = array();
    }
    
    function get_groupmembers() {
    	return array_keys($this->groupmembers);
    }
    
    function addresses_string() {
        $line = "";
        
        if(is_array($this->addresses)) {
            foreach ($this->addresses as $value) {
                $tmp = "";
                $tmp .= $this->escape($value['label']) .";";
                $tmp .= $this->escape($value['pobox']) .";";
                $tmp .= $this->escape($value['ext_adr']) .";";
                $tmp .= $this->escape($value['street']) .";";
                $tmp .= $this->escape($value['city']) .";";
                $tmp .= $this->escape($value['state']) .";";
                $tmp .= $this->escape($value['zip']) .";";
                $tmp .= $this->escape($value['country']) .";";
                $tmp .= $this->escape($value['template']);
                
                $line .= $tmp . "\n";
            }
        }
        return trim($line);
    }

    function string2addresses($string) {
        if(is_string($string) and !empty($string)) {
            $lines = explode("\n", $string);
            
            foreach ($lines as $line) {
                $new = array();
                list($label, $pobox, $ext_adr, $street, $city, $state, $zip, $country, $code) = $this->split_and_unescape($line, 9);
                
                $new['label'] = $label;
                $new['pobox'] = $pobox;
                $new['ext_adr'] = $ext_adr;
                $new['street'] = $street;
                $new['city'] = $city;
                $new['state'] = $state;
                $new['zip'] = $zip;
                $new['country'] = $country;
                $new['template'] = $code;
                
                array_push($this->addresses, $new);
            }
        }
    }

    function emails_string() {
        $line = "";
        if(is_array($this->emails)) {
            foreach ($this->emails as $value) {
                $tmp = "";
                $tmp .= $this->escape($value['label']) .";";
                $tmp .= $this->escape($value['email']);
                
                $line .= $tmp . "\n";
            }
        }
        return trim($line);
    }
    
    function string2emails($string) {
        if(is_string($string) and !empty($string)) {
            $lines = explode("\n", $string);
            
            foreach ($lines as $line) {
                $new = array();
                list($label, $email) = $this->split_and_unescape($line, 2);
                
                $new['label'] = $label;
                $new['email'] = $email;
                
                array_push($this->emails, $new);
            }
        }
    }

    function phones_string() {
        $line = "";
        if(is_array($this->phones)) {
            foreach ($this->phones as $value) {
                $tmp = "";
                $tmp .= $this->escape($value['label']) .";";
                $tmp .= $this->escape($value['phone']);
                
                $line .= $tmp . "\n";
            }
        }
        return trim($line);
    }
    
    function string2phones($string) {
        if(is_string($string) and !empty($string)) {
            $lines = explode("\n", $string);
            
            foreach ($lines as $line) {
                $new = array();
                list($label, $phone) = $this->split_and_unescape($line, 2);
                
                $new['label'] = $label;
                $new['phone'] = $phone;
                
                array_push($this->phones, $new);
            }
        }
    }

    function chathandles_string() {
        $line = "";
        if(is_array($this->chathandles)) {
            foreach ($this->chathandles as $value) {
                $tmp = "";
                $tmp .= $this->escape($value['label']) .";";
                $tmp .= $this->escape($value['handle']).";";
                $tmp .= $this->escape($value['type']);
                
                $line .= $tmp . "\n";
            }
        }
        return trim($line);
    }
    
    function string2chathandles($string) {
        if(is_string($string) and !empty($string)) {
            $lines = explode("\n", $string);
            
            foreach ($lines as $line) {
                $new = array();
                list($label, $handle, $type) = $this->split_and_unescape($line, 3);
                
                $new['label'] = $label;
                $new['handle'] = $handle;
                $new['type'] = $type;
                
                array_push($this->chathandles, $new);
            }
        }
    }

    function urls_string() {
        $line = "";
        if(is_array($this->urls)) {
            foreach ($this->urls as $value) {
                $tmp = "";
                $tmp .= $this->escape($value['label']) .";";
                $tmp .= $this->escape($value['url']);
                
                $line .= $tmp . "\n";
            }
        }
        return trim($line);
    }
    
    function string2urls($string) {
        if(is_string($string) and !empty($string)) {
            $lines = explode("\n", $string);
            
            foreach ($lines as $line) {
                $new = array();
                list($label, $url) = $this->split_and_unescape($line, 2);
                
                $new['label'] = $label;
                $new['url'] = $url;
                
                array_push($this->urls, $new);
            }
        }
    }

    function relatednames_string() {
        $line = "";
        if(is_array($this->relatednames)) {
            foreach ($this->relatednames as $value) {
                $tmp = "";
                $tmp .= $this->escape($value['label']) .";";
                $tmp .= $this->escape($value['name']);
                
                $line .= $tmp . "\n";
            }
        }
        return trim($line);
    }
    
    function string2relatednames($string) {
        if(is_string($string) and !empty($string)) {
            $lines = explode("\n", $string);
            
            foreach ($lines as $line) {
                $new = array();
                list($label, $name) = $this->split_and_unescape($line);
                
                $new['label'] = $label;
                $new['name'] = $name;
                
                array_push($this->relatednames, $new);
            }
        }
    }

    function split_and_unescape($string, $trim_number = 0) {
        // we use these double-backs (\\) because they get converted
        // to single-backs (\) by preg_split.  the quad-backs (\\\\) end
        // up as as double-backs (\\), which is what preg_split requires
        // to indicate a single backslash (\). what a mess.
        $regex = '(?<!\\\\)(\;)';
        $tmp = preg_split("/$regex/i", $string);
        
        foreach($tmp as $key => $value) {
            //$tmp[$key] = str_replace("\\;", ";", $value);
            $tmp[$key] = real_stripcslashes($value, ";n\\");
        }
        
        if($trim_number > 0) {
        	while(count($tmp) < $trim_number)
        		$tmp[] = "";
        }
        return $tmp;
    }
    
    function escape($string) {
        return real_addcslashes($string, ";\n\\");
    }
    
    function unescape($string) {
        return real_stripcslashes($string, ";n\\");
    }
    
    function html_escapelabel($string) {
        preg_match('_\$\!\<(.*)\>\!\$_', $string,  $match);
        if(!empty($match)) {
            return '_$!<' . htmlspecialchars($match[1]) . '>!$_';
        }

        //else
        return htmlspecialchars($string);
    }
    
    function html_escape() {
        $this->title              = real_nl2br(htmlspecialchars($this->title));
        $this->firstname          = real_nl2br(htmlspecialchars($this->firstname));
        $this->firstname2         = real_nl2br(htmlspecialchars($this->firstname2));
        $this->lastname           = real_nl2br(htmlspecialchars($this->lastname));
        $this->suffix             = real_nl2br(htmlspecialchars($this->suffix));
        $this->nickname           = real_nl2br(htmlspecialchars($this->nickname));
        $this->phoneticfirstname  = real_nl2br(htmlspecialchars($this->phoneticfirstname));
        $this->phoneticlastname   = real_nl2br(htmlspecialchars($this->phoneticlastname));
        $this->jobtitle           = real_nl2br(htmlspecialchars($this->jobtitle));
        $this->department         = real_nl2br(htmlspecialchars($this->department));
        $this->organization       = real_nl2br(htmlspecialchars($this->organization));
        $this->note               = htmlspecialchars($this->note);
        
        $this->birthdate          = real_nl2br(htmlspecialchars($this->birthdate));
        
        $this->company            = (int)$this->company;
        $this->id                 = (int)$this->id;
        $this->uid                = real_nl2br(htmlspecialchars($this->uid));
        $this->etag               = (int)$this->etag;        
        $this->modification_ts    = (int)$this->modification_ts;
        
        if(is_array($this->addresses)) {
            foreach($this->addresses as $key => $value) {
                $this->addresses[$key]['label']    = $this->html_escapelabel($this->addresses[$key]['label']);
                $this->addresses[$key]['pobox']    = real_nl2br(htmlspecialchars($this->addresses[$key]['pobox']));
                $this->addresses[$key]['ext_adr']  = real_nl2br(htmlspecialchars($this->addresses[$key]['ext_adr']));
                $this->addresses[$key]['street']   = real_nl2br(htmlspecialchars($this->addresses[$key]['street']));
                $this->addresses[$key]['city']     = real_nl2br(htmlspecialchars($this->addresses[$key]['city']));
                $this->addresses[$key]['state']    = real_nl2br(htmlspecialchars($this->addresses[$key]['state']));
                $this->addresses[$key]['zip']      = real_nl2br(htmlspecialchars($this->addresses[$key]['zip']));
                $this->addresses[$key]['country']  = real_nl2br(htmlspecialchars($this->addresses[$key]['country']));
                $this->addresses[$key]['template'] = real_nl2br(htmlspecialchars($this->addresses[$key]['template']));
            }
        }
        if(is_array($this->emails)) {
            foreach($this->emails as $key => $value) {
                $this->emails[$key]['label'] = $this->html_escapelabel($this->emails[$key]['label']);
                $this->emails[$key]['email'] = real_nl2br(htmlspecialchars($this->emails[$key]['email']));
            }
        }
        if(is_array($this->phones)) {
            foreach($this->phones as $key => $value) {
                $this->phones[$key]['label'] = $this->html_escapelabel($this->phones[$key]['label']);
                $this->phones[$key]['phone'] = real_nl2br(htmlspecialchars($this->phones[$key]['phone']));
            }
        }
        if(is_array($this->chathandles)) {
            foreach($this->chathandles as $key => $value) {
                $this->chathandles[$key]['label']  = $this->html_escapelabel($this->chathandles[$key]['label']);
                $this->chathandles[$key]['handle'] = real_nl2br(htmlspecialchars($this->chathandles[$key]['handle']));
                $this->chathandles[$key]['type']   = real_nl2br(htmlspecialchars($this->chathandles[$key]['type']));
            }
        }
        if(is_array($this->relatednames)) {
            foreach($this->relatednames as $key => $value) {
                $this->relatednames[$key]['label'] = $this->html_escapelabel($this->relatednames[$key]['label']);
                $this->relatednames[$key]['name']  = real_nl2br(htmlspecialchars($this->relatednames[$key]['name']));
            }
        }
        if(is_array($this->urls)) {
            foreach($this->urls as $key => $value) {
                $this->urls[$key]['label'] = $this->html_escapelabel($this->urls[$key]['label']);
                $this->urls[$key]['url']   = real_nl2br(htmlspecialchars($this->urls[$key]['url']));
            }
        }
        
        if(is_array($this->categories)) {
        	foreach($this->categories as $key => $value) {
        		$value->html_escape();
        	}
        }
        
    }
    
}



?>