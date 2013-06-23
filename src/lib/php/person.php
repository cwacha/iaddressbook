<?php
    /**
     * iAddressBook person representation
     *
     * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
     * @author     Clemens Wacha <clemens.wacha@gmx.net>
     */

    if(!defined('AB_BASEDIR')) define('AB_BASEDIR',realpath(dirname(__FILE__).'/../../'));
    require_once(AB_BASEDIR.'/lib/php/include.php');
    require_once(AB_BASEDIR.'/lib/php/common.php');


class person {
    var $title;
    var $firstname;
    var $firstname2;
    var $lastname;
    var $suffix;
    var $nickname;
    var $phoneticfirstname;
    var $phoneticlastname;

    var $jobtitle;
    var $department;
    var $organization;
    var $company;    // boolean

    var $birthdate;    // date in format: "YYYY-MM-DD"
    var $image;    // picture
    var $note;

    var $creationdate;    // date in format: "YYYY-MM-DD HH:MM:SS GMT"
    var $modificationdate;    // date in format: "YYYY-MM-DD HH:MM:SS GMT"
    var $id;

    // embedded objects
    var $addresses;
    var $emails;
    var $phones;
    var $chathandles;
    var $relatednames;
    var $urls;

    function person() {
        $this->company = 0;
        $this->birthdate = '0000-00-00';

        $this->addresses = array();
        $this->emails = array();
        $this->phones = array();
        $this->chathandles = array();
        $this->relatednames = array();
        $this->urls = array();
        
        $this->id = 0;
        $this->creationdate = gmdate('Y-m-d H:i:s') . ' GMT';
        $this->modificationdate = gmdate('Y-m-d H:i:s') . ' GMT';
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
        $person['creationdate'] = $this->creationdate;
        $person['modificationdate'] = $this->modificationdate;
        $person['id'] = $this->id;

        $person['addresses'] = $this->addresses;
        $person['emails'] = $this->emails;
        $person['phones'] = $this->phones;
        $person['chathandles'] = $this->chathandles;
        $person['relatednames'] = $this->relatednames;
        $person['urls'] = $this->urls;

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
        $this->creationdate       = $person['creationdate'];
        //$this->modificationdate   = $person['modificationdate'];
        $this->modificationdate   = gmdate('Y-m-d H:i:s') . ' GMT';

        $this->id                 = $person['id'];

        $this->addresses          = $person['addresses'];
        $this->emails             = $person['emails'];
        $this->phones             = $person['phones'];
        $this->chathandles        = $person['chathandles'];
        $this->relatednames       = $person['relatednames'];
        $this->urls               = $person['urls'];

        $this->validate();
    }
    
    function name($lastfirst = NULL) {
        global $conf;
        global $lang;
        
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
            $ret = $lang['no_name'];
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
        
        if(!is_string($this->birthdate) or empty($this->birthdate) or strlen($this->birthdate) != 10) $this->birthdate = '0000-00-00';
        
        if(!is_integer($this->company)) $this->company = (int)$this->company;
        
        if(!is_array($this->addresses)) $this->addresses = array();
        if(!is_array($this->emails)) $this->emails = array();
        if(!is_array($this->phones)) $this->phones = array();
        if(!is_array($this->chathandles)) $this->chathandles = array();
        if(!is_array($this->relatednames)) $this->relatednames = array();
        if(!is_array($this->urls)) $this->urls = array();
    }

    function add_address($address) {
        $tmp = $address['pobox'] . $address['ext_adr'] . $address['street'] . $address['city'] . $address['state'] . $address['zip'] . $address['country'];
        if(is_array($address) and !empty($tmp)) {
            array_push($this->addresses, $address);
        }
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
        
    function show() {
        echo "$this->title $this->firstname $this->firstname2 $this->lastname $this->suffix<br>\n";
        if($this->nickname != "") echo "\"$this->nickname\"<br>\n";
        echo "<br>\n";
        if($this->company) {
        
        } else {
            
        }
        if($this->jobtitle != "") echo "Title: $this->jobtitle<br>\n";
        if($this->department != "") echo "Dept. : $this->department<br>\n";
        if($this->organization != "") echo "Organization: $this->organization<br>\n";
        echo "<br>\n";
        
        
        if(!empty($this->birthdate) and $this->birthdate != "0000-00-00") echo "Birth day: " . date('d.m.Y', strtotime($this->birthdate)) . "<br>\n";
        if($this->homepage != "") echo "Homepage: $this->homepage<br>\n";
        if($this->image != "") echo "Image exists<br>\n";
        echo "<br>\n";
        if($this->note) echo "Note: $this->note<br>\n";
        

        foreach ($this->addresses as $address) {
            echo "address {$address['label']}:<br>\n";
            echo "    {$address['street']}<br>\n";
            echo "    {$address['zip']} {$address['city']}<br>\n";
            echo "    {$address['country']} ({$address['template']})<br>\n";
            echo "    {$address['state']}<br>\n";
            echo "<br>\n";
        }
        
        foreach ($this->emails as $email) {
            echo "email {$email['label']}: {$email['email']}<br>\n";
        }
        echo "<br>\n";

        foreach ($this->phones as $phone) {
            echo "phone {$phone['label']}: {$phone['phone']}<br>\n";
        }
        echo "<br>\n";

        foreach ($this->urls as $url) {
            echo "url {$url['label']}: {$url['url']}<br>\n";
        }
        echo "<br>\n";
        
        foreach ($this->chathandles as $handle) {
            echo "{$handle['label']} (type: {$handle['type']}): {$handle['handle']}<br>\n";
        }
        echo "<br>\n";

        foreach ($this->relatednames as $name) {
            echo "{$name['label']}: {$name['name']}<br>\n";
        }
        echo "<br>\n";

        echo "created: ". date('r', strtotime($this->creationdate)) ." ($this->creationdate)<br>\n";
        echo "modified: ". date('r', strtotime($this->modificationdate)) ." ($this->modificationdate)<br>\n";
        echo "id: $this->id<br>\n";
        echo "<br>\n";

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
                list($label, $pobox, $ext_adr, $street, $city, $state, $zip, $country, $code) = $this->split_and_unescape($line);
                
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
                list($label, $email) = $this->split_and_unescape($line);
                
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
                list($label, $phone) = $this->split_and_unescape($line);
                
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
                list($label, $handle, $type) = $this->split_and_unescape($line);
                
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
                list($label, $url) = $this->split_and_unescape($line);
                
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

    function split_and_unescape($string) {
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
        
        $this->creationdate       = real_nl2br(htmlspecialchars($this->creationdate));
        $this->modificationdate   = real_nl2br(htmlspecialchars($this->modificationdate));
        
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
    }
    
}



?>