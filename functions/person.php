<?php


class person {
	var $title;
	var $firstname;
	var $firstname2;
	var $lastname;
	var $suffix;
	var $nickname;

	var $jobtitle;
	var $department;
	var $organization;
	var $company;	// boolean

	var $birthdate;	// date in format: "YYYY-MM-DD"
	var $image;	// picture
	var $note;

	var $creationdate;	// date in format: "YYYY-MM-DD HH:MM:SS GMT"
	var $modificationdate;	// date in format: "YYYY-MM-DD HH:MM:SS GMT"
	var $id;

	// embedded objects
	var $addresses;
	var $emails;
	var $phones;
	var $chathandles;
    var $relatednames;
	var $urls;

    function person() {
        $this->company = false;
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
    
    function validate() {
        if(!is_string($this->title)) $this->title = '';
        if(!is_string($this->firstname)) $this->firstname = '';
        if(!is_string($this->firstname2)) $this->firstname2 = '';
        if(!is_string($this->lastname)) $this->lastname = '';
        if(!is_string($this->suffix)) $this->suffix = '';
        if(!is_string($this->nickname)) $this->nickname = '';
        if(!is_string($this->jobtitle)) $this->jobtitle = '';
        if(!is_string($this->department)) $this->department = '';
        if(!is_string($this->note)) $this->note = '';
        if(!is_string($this->note)) $this->note = '';
        
        if(!is_string($this->birthdate) or empty($this->birthdate) or strlen($this->birthdate) != 10) $this->birthdate = '0000-00-00';
        
        if(!is_bool($this->company)) $this->company = (boolean)((int)$this->company);
        
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
	
    
	function vcard($version = 3) {
		if($version == 3) {
			$num = 0;

			$out =  "BEGIN:VCARD\n";
			$out .= "VERSION:3.0\n";
			$out .= "N:$this->lastname;$this->firstname;$this->firstname2;$this->title;$this->suffix\n";
			$out .= "FN:$this->firstname $this->lastname\n";
			if($this->nickname) $out .= "NICKNAME:$this->nickname\n";
			if($this->organization or $this->department) $out .= "ORG:$this->organization;$this->department\n";
			if($this->jobtitle) $out .= "TITLE:$this->jobtitle\n";
			
			$first = true;
			$pref = ";type=pref";
			foreach ($this->emails as $email) {
				if($first) $first = false; else $pref = "";
				
				$label = strtoupper($email['label']);
				if(!in_array($label, array("WORK", "HOME")) ) {
					$num++;
					$item = "item$num.";
				} else {
					$item = "";
				}
				
				$out .= $item ."EMAIL;type=INTERNET;type=$label$pref:{$email['email']}\n";
				if($item) $out .= "$itemX-ABLabel:{$email['label']}\n";
			}

			$first = true;
			$pref = ";type=pref";
			foreach ($this->phones as $phone) {
				if($first) $first = false; else $pref = "";
				
				$label = strtoupper($phone['label']);
				if(in_array($label, array("WORK", "CELL", "HOME", "MAIN", "PAGER")) ) {
					$item = "";
				} else {
					// FAX HACK
					if(in_array($label, array( "WORK FAX", "HOME FAX")) ) {
						$label = str_replace(" ", ";type=", $label);
					}
					$num++;
					$item = "item$num.";
				}
				
				$out .= $item ."TEL;type=$label$pref:{$phone['phone']}\n";
				if($item) $out .= $item ."X-ABLabel:{$phone['label']}\n";
			}

			$first = true;
			$pref = ";type=pref";
			foreach ($this->addresses as $address) {
				if($first) $first = false; else $pref = "";
				
				$label = strtoupper($address['label']);
				if(in_array($label, array( "WORK", "HOME")) ) {
					$item = "";
				} else {
					$num++;
					$item = "item$num.";
				}
				
				$out .= $item ."ADR;type=$label$pref:{$address['pobox']};{$address['ext_adr']};{$address['street']};{$address['city']};{$address['state']};{$address['zip']};{$address['country']}\n";
				$out .= $item."X-ABLabel:{$address['label']}\n";
                if($address['countrycode']) $out .= $item."X-ABADR:".$address['countrycode']."\n";
			}

			$first = true;
			$pref = ";type=pref";
			foreach ($this->chathandles as $handle) {
				if($first) $first = false; else $pref = "";
				
				$label = strtoupper($handle['type']);
				$vtype = strtoupper($handle['label']);
				switch($label) {
					case "AIM":
						$vname = "X-AIM";
						break;
					case "JABBER":
						$vname = "X-JABBER";
						break;
					case "MSN":
						$vname = "X-MSN";
						break;
					case "YAHOO":
						$vname = "X-YAHOO";
						break;
					case "ICQ":
						$vname = "X-ICQ";
						break;
					case "IRC":
						$vname = "X-IRC";
						break;
					default:
						$vname = ""; $vtype = "";
				}
                
				if($vname) $out .= "$vname;type=$vtype$pref:{$handle['handle']}\n";
			}
			
			
			// FIXME with \n
			if($this->note) $out .= "NOTE:".str_replace("\n", "\\n", $this->note)."\n";
			if($this->homepage) $out .= "URL:$this->homepage\n";
			if(!empty($this->birthdate) and $this->birthdate != "0000-00-00") $out .= "BDAY;value=date:$this->birthdate\n";
			
			// TODO
			// $out .= "PHOTO;BASE64:\n";
			// $out .= "  $this->image\n";
			
			//$out .= "CATEGORIES:\n";
			
			$out .= "END:VCARD\n";
			
			return $out;
		}
	}
	
    
	function show() {
		echo "$this->title $this->firstname $this->firstname2 $this->lastname $this->suffix<br>\n";
		if($this->nickname != "") echo "\"$this->nickname\"<br>\n";
		echo "<br>\n";
		if($this->company == true) {
		
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

    function name($reverse = false) {
        $ret = "";
        if($this->company == true) {
            $ret = $this->organization;
        } else {
            if($reverse == false) {
                $ret = $this->firstname . " " . $this->lastname;
            } else {
                $ret = $this->lastname . ", " . $this->firstname;
            }
        }
        $ret = trim($ret, " ,");
        if(empty($ret)) {
            $ret = "(No name)";
            if(!empty($this->organization)) $ret = $this->organization;
        }

        return $ret;
    }

    function addresses_string() {
        $line = "";
        
        if(is_array($this->addresses)) {
            foreach ($this->addresses as $value) {
                $tmp = "";
                $tmp .= addcslashes($value['label'], ";") .";";
                $tmp .= addcslashes($value['pobox'], ";") .";";
                $tmp .= addcslashes($value['ext_adr'], ";") .";";
                $tmp .= addcslashes($value['street'], ";") .";";
                $tmp .= addcslashes($value['city'], ";") .";";
                $tmp .= addcslashes($value['state'], ";") .";";
                $tmp .= addcslashes($value['zip'], ";") .";";
                $tmp .= addcslashes($value['country'], ";") .";";
                $tmp .= addcslashes($value['template'], ";");
                
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
                list($label, $pobox, $ext_adr, $street, $city, $state, $zip, $country, $code) = $this->split_and_unquote($line);
                
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
                $tmp .= addcslashes($value['label'], ";") .";";
                $tmp .= addcslashes($value['email'], ";");
                
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
                list($label, $email) = $this->split_and_unquote($line);
                
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
                $tmp .= addcslashes($value['label'], ";") .";";
                $tmp .= addcslashes($value['phone'], ";");
                
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
                list($label, $phone) = $this->split_and_unquote($line);
                
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
                $tmp .= addcslashes($value['label'], ";") .";";
                $tmp .= addcslashes($value['handle'], ";").";";
                $tmp .= addcslashes($value['type'], ";");
                
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
                list($label, $handle, $type) = $this->split_and_unquote($line);
                
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
                $tmp .= addcslashes($value['label'], ";") .";";
                $tmp .= addcslashes($value['url'], ";");
                
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
                list($label, $url) = $this->split_and_unquote($line);
                
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
                $tmp .= addcslashes($value['label'], ";") .";";
                $tmp .= addcslashes($value['name'], ";");
                
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
                list($label, $name) = $this->split_and_unquote($line);
                
                $new['label'] = $label;
                $new['name'] = $name;
                
                array_push($this->relatednames, $new);
            }
        }
    }

    function split_and_unquote($string) {
        // we use these double-backs (\\) because they get converted
        // to single-backs (\) by preg_split.  the quad-backs (\\\\) end
        // up as as double-backs (\\), which is what preg_split requires
        // to indicate a single backslash (\). what a mess.
        $regex = '(?<!\\\\)(\;)';
        $tmp = preg_split("/$regex/i", $string);
        
        foreach($tmp as $key => $value) {
            $tmp[$key] = str_replace("\\;", ";", $value);
        }
        
        return $tmp;
    }
}



/*

ACHTUNG!! outdated!!

$contact = new person;
$contact->firstname = "Klark";
$contact->lastname = "Kent";

$contact->birthdate = "1973-02-24";
$contact->set_email("work;klark@dailyplanet.com");
$contact->set_address("home;;;Street mit embedded 39;New York;;12345;USA;US");
$contact->add_chathandle("aim;superman");
$contact->add_chathandle("Jabber;klark.kent@jabber.org");
$contact->del_chathandle("Jabber;klark.kent@jabber.org");
$contact->add_chathandle("icq;12233342");
$contact->show();

*/

/*
// Vcard test
$filename = "$contact->lastname.vcf";
$card = $contact->vcard();
Header("Content-Disposition: attachment; filename=$filename");
Header("Content-Length: ".strlen($card));
Header("Connection: close");
Header("Content-Type: text/x-vCard; name=$filename");
echo $card;
*/

?>