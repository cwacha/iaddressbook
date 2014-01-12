<?php
/**
     * iAddressBook adressbook functions
     *
     * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
     * @author     Clemens Wacha <clemens.wacha@gmx.net>
     */
if (!defined('AB_BASEDIR'))
	define('AB_BASEDIR', realpath(dirname(__FILE__) . '/../../'));

require_once (AB_BASEDIR . '/lib/php/include.php');
require_once (AB_BASEDIR . '/lib/php/db.php');
require_once (AB_BASEDIR . '/lib/php/person.php');
require_once (AB_BASEDIR . '/lib/php/common.php');

class Addressbooks {

	function Addressbooks() {
	}
	
	function row2book($row) {
		global $db_config;
		$prefix = '';
		$row = array_change_key_case($row, CASE_LOWER);
		
		if(!array_key_exists('id', $row)) {
			$prefix = strtolower($db_config['dbtable_abs']) . '.';
		}

		$book = array(
				'id' => (int)$row[$prefix . 'id'],
				'displayname' => $row[$prefix . 'displayname'],
				'uri' => $row[$prefix . 'uri'],
				'description' => $row[$prefix . 'description'],
				'ctag' => (int)$row[$prefix . 'ctag']
		);
		return $book;
	}

	function createAddressbook($principaluri, $displayname = 'default', $uri = 'default', $description = '') {
		global $db;
		global $db_config;
		
		$principaluri = $db->escape($principaluri);
		$displayname = $db->escape($displayname);
		$uri = $db->escape($uri);
		$description = $db->escape($description);
		
		$sql = "INSERT INTO " . $db_config ['dbtable_abs'] . "  (";
		$sql .= "principaluri, displayname, uri, description, ctag";
		$sql .= ") VALUES ( ";
		$sql .= "$principaluri, $displayname, $uri, $description, 1 );";
		
		$db->execute($sql);
		$id = $db->insertId();
		if ($id == -1) {
			msg("DB error on createAddressbook: " . $db->lasterror(), -1);
		}
		return $id;
	}

	function getAddressBooksForUser($principaluri) {
        global $db;
        global $db_config;
        $books = array();
        
        $principaluri = $db->escape($principaluri);
        
        $sql = "SELECT * FROM ".$db_config['dbtable_abs']." WHERE principaluri=$principaluri";
		$result = $db->selectAll($sql);

		foreach($result as $row) {
			$books[] = $this->row2book($row);
		}
		return $books;
	}

	function getAddressBookForId($addressbookId) {
		global $db;
		global $db_config;
		
		$addressbookId = $db->escape((int)$addressbookId);
		
		$sql = "SELECT * FROM " . $db_config ['dbtable_abs'] . " WHERE id=$addressbookId";
		$row = $db->selectOne($sql);
		if($row) {
			return $this->row2book($row);
		}
		return NULL;
	}
	
}
    
class Addressbook {
    
	var $addressbookId;
	
    function Addressbook($id=-1) {
    	$this->addressbookId = $id;
    }
    
    function row2contact($row) {
        global $db_config;
        $prefix = '';
        
        $contact = new Person;
        if(empty($row)) return $contact;
        
        if(is_object($row)) $row = get_object_vars($row);
        
        $row = array_change_key_case($row, CASE_LOWER);
        
        if(!array_key_exists('id', $row)) {
            $prefix = strtolower($db_config['dbtable_ab']) . '.';
        }
        
        $contact->id = (int)$row[$prefix . 'id'];
        $contact->uid = $contact->unescape($row[$prefix . 'uid']);
        $contact->etag = (int)$row[$prefix . 'etag'];
        $contact->modification_ts = (int)$row[$prefix . 'modification_ts'];
        
        $contact->title = $contact->unescape($row[$prefix . 'title']);
        $contact->firstname = $contact->unescape($row[$prefix . 'firstname']);
        $contact->firstname2 = $contact->unescape($row[$prefix . 'firstname2']);
        $contact->lastname = $contact->unescape($row[$prefix . 'lastname']);
        $contact->suffix = $contact->unescape($row[$prefix . 'suffix']);
        $contact->nickname = $contact->unescape($row[$prefix . 'nickname']);
        $contact->phoneticfirstname = $contact->unescape($row[$prefix . 'phoneticfirstname']);
        $contact->phoneticlastname = $contact->unescape($row[$prefix . 'phoneticlastname']);

        $contact->jobtitle = $contact->unescape($row[$prefix . 'jobtitle']);
        $contact->department = $contact->unescape($row[$prefix . 'department']);
        $contact->organization = $contact->unescape($row[$prefix . 'organization']);
        $contact->company = (int)$row[$prefix . 'company'];
        
        $contact->birthdate = $contact->unescape($row[$prefix . 'birthdate']);
        $contact->note = $contact->unescape($row[$prefix . 'note']);
        
        $contact->string2addresses($row[$prefix . 'addresses']);
        $contact->string2emails($row[$prefix . 'emails']);
        $contact->string2phones($row[$prefix . 'phones']);
        $contact->string2chathandles($row[$prefix . 'chathandles']);
        $contact->string2relatednames($row[$prefix . 'relatednames']);
        $contact->string2urls($row[$prefix . 'urls']);
        
        return $contact;
    }
   
    function find($searchstring, $limit = 1000, $offset = 0) {
        global $db;
        global $db_config;
        global $CAT;
        global $CAT_ID;

        $contactlist = array();
        
        if(!$db) return $contactlist;
        
        $search_array = explode(" ", $searchstring);
        $sql_searchstring = "";
        foreach($search_array as $key => $value) {
            // escape %
            $value = real_addcslashes($value, "%\\");
            $value = $db->escape("%" . $value . "%");
            $search_array[$key] = $value;
        }
        $selected = $db->escape($CAT_ID);
        
        if($CAT_ID == 0) {
            $sql  = "SELECT * FROM ".$db_config['dbtable_ab']." WHERE ";
            foreach($search_array as $key => $value) {
                $sql1  = "(uid                 LIKE $value) OR ";
            	$sql1 .= "(title               LIKE $value) OR ";
                $sql1 .= "(lastname            LIKE $value) OR ";
                $sql1 .= "(firstname           LIKE $value) OR ";
                $sql1 .= "(firstname2          LIKE $value) OR ";
                $sql1 .= "(suffix              LIKE $value) OR ";
                $sql1 .= "(nickname            LIKE $value) OR ";
                $sql1 .= "(phoneticfirstname   LIKE $value) OR ";
                $sql1 .= "(phoneticlastname    LIKE $value) OR ";
                $sql1 .= "(jobtitle            LIKE $value) OR ";
                $sql1 .= "(department          LIKE $value) OR ";
                $sql1 .= "(organization        LIKE $value) OR ";
                $sql1 .= "(birthdate           LIKE $value) OR ";
                $sql1 .= "(note                LIKE $value) OR ";
                $sql1 .= "(addresses           LIKE $value) OR ";
                $sql1 .= "(emails              LIKE $value) OR ";
                $sql1 .= "(phones              LIKE $value) OR ";
                $sql1 .= "(chathandles         LIKE $value) OR ";
                $sql1 .= "(relatednames        LIKE $value) OR ";
                $sql1 .= "(urls                LIKE $value)";

                $sql .= "(" . $sql1 . ") AND ";
            }
            $sql = substr($sql, 0, -5);
            
            $sql .= " ORDER BY lastname ASC LIMIT $limit OFFSET $offset";
        } else {
            $sql  = "SELECT * FROM ".$db_config['dbtable_catmap'].", ".$db_config['dbtable_ab']." WHERE (";
            $sql .= $db_config['dbtable_catmap'].".person_id = ".$db_config['dbtable_ab'].".id AND ".$db_config['dbtable_catmap'].".category_id = ".$selected.") AND (";
            foreach($search_array as $key => $value) {
                $sql1  = "(uid                 LIKE $value) OR ";
            	$sql1 .= "(title               LIKE $value) OR ";
                $sql1 .= "(lastname            LIKE $value) OR ";
                $sql1 .= "(firstname           LIKE $value) OR ";
                $sql1 .= "(firstname2          LIKE $value) OR ";
                $sql1 .= "(suffix              LIKE $value) OR ";
                $sql1 .= "(nickname            LIKE $value) OR ";
                $sql1 .= "(phoneticfirstname   LIKE $value) OR ";
                $sql1 .= "(phoneticlastname    LIKE $value) OR ";
                $sql1 .= "(jobtitle            LIKE $value) OR ";
                $sql1 .= "(department          LIKE $value) OR ";
                $sql1 .= "(organization        LIKE $value) OR ";
                $sql1 .= "(birthdate           LIKE $value) OR ";
                $sql1 .= "(note                LIKE $value) OR ";
                $sql1 .= "(addresses           LIKE $value) OR ";
                $sql1 .= "(emails              LIKE $value) OR ";
                $sql1 .= "(phones              LIKE $value) OR ";
                $sql1 .= "(chathandles         LIKE $value) OR ";
                $sql1 .= "(relatednames        LIKE $value) OR ";
                $sql1 .= "(urls                LIKE $value)";

                $sql .= "(" . $sql1 . ") AND ";
            }
            $sql = substr($sql, 0, -5);
            
            $sql .= ") ORDER BY lastname ASC LIMIT $limit OFFSET $offset";
        }
        $result = $db->selectAll($sql);
		foreach ( $result as $row ) {
			$contact = $this->row2contact( $row );
			$contactlist [$contact->id] = $contact;
		}
        
        return $contactlist;
    }
    
    function getall($limit = 1000, $offset = 0) {
        global $db;
        global $db_config;
        //global $CAT;
        global $CAT_ID;
        
        $contactlist = array();
        
        if(!$db) return $contactlist;
        
        $selected = $db->escape($CAT_ID);
        
        if($CAT_ID == 0) {
            $sql  = "SELECT * FROM ".$db_config['dbtable_ab']." ORDER BY lastname ASC LIMIT $limit OFFSET $offset";
        } else {
            $sql_select  = $db_config['dbtable_ab'].".*, ";
            $sql_select .= $db_config['dbtable_catmap'].".category_id ";
            
            $sql  = "SELECT ".$sql_select." FROM ".$db_config['dbtable_catmap'].", ".$db_config['dbtable_ab']." WHERE (";
            $sql .= $db_config['dbtable_catmap'].".person_id = ".$db_config['dbtable_ab'].".id AND ".$db_config['dbtable_catmap'].".category_id = ".$selected.") ";
            $sql .= " ORDER BY lastname ASC LIMIT $limit OFFSET $offset";
        }
        
        $result = $db->selectAll($sql);
        
        foreach($result as $row) {
        	$contact = $this->row2contact($row);        	 
        	$contactlist[$contact->id] = $contact;
        }
        return $contactlist;
    }

    function get($id, $with_image = false) {
        global $db;
        global $db_config;
        global $CAT;
        $contact = false;
		if (!$db or $id === 0)
			return $contact;
			
			// quote db specific characters
		if (is_string($id)) {
			$uid = $db->escape(strtolower($id));
			$sql = "SELECT * FROM " . $db_config ['dbtable_ab'] . " WHERE uid=$uid LIMIT 1";
		} else {
			$id = $db->escape(( int ) $id);
			$sql = "SELECT * FROM " . $db_config ['dbtable_ab'] . " WHERE id=$id LIMIT 1";
		}
        
		$row = $db->selectOne($sql);
		if (!$row)
			return false;

		$contact = $this->row2contact($row);
		$categories = $CAT->getCategoriesForPerson($contact->id);
		foreach ( $categories as $category ) {
			$contact->add_category($category);
		}
		
		if ($with_image === true)
			$contact->image = img_load($contact->id);
		
		return $contact;        
    }
    
   
    function set($contact) {
        global $db;
        global $db_config;
        global $conf;
        global $CAT;
        if(!$db) return false;
        
        if(!is_object($contact)) {
        	return false;
        }
        
		$contact->validate();

		if (empty($contact->uid))
			$contact->uid = generate_uuid();
		
		// Input validation
		$id = $db->escape(( int ) $contact->id);
		$uid = $db->escape($contact->escape(strtolower($contact->uid)));
		$title = $db->escape($contact->escape($contact->title));
		$firstname = $db->escape($contact->escape($contact->firstname));
		$firstname2 = $db->escape($contact->escape($contact->firstname2));
		$lastname = $db->escape($contact->escape($contact->lastname));
		$suffix = $db->escape($contact->escape($contact->suffix));
		$nickname = $db->escape($contact->escape($contact->nickname));
		$phoneticfirstname = $db->escape($contact->escape($contact->phoneticfirstname));
		$phoneticlastname = $db->escape($contact->escape($contact->phoneticlastname));
		$jobtitle = $db->escape($contact->escape($contact->jobtitle));
		$department = $db->escape($contact->escape($contact->department));
		$organization = $db->escape($contact->escape($contact->organization));
		$company = $db->escape(( int ) $contact->company);
		$birthdate = $db->escape($contact->escape($contact->birthdate));
		$note = $db->escape($contact->escape($contact->note));
		
		$a_line = $db->escape($contact->addresses_string());
		$e_line = $db->escape($contact->emails_string());
		$p_line = $db->escape($contact->phones_string());
		$c_line = $db->escape($contact->chathandles_string());
		$r_line = $db->escape($contact->relatednames_string());
		$u_line = $db->escape($contact->urls_string());
		
		$mod_date = time();
		
		if (empty($contact->birthdate))
			$contact->birthdate = '0000-00-00';
		
		if ($contact->id == 0) {
			// insert
			$sql = "INSERT INTO " . $db_config ['dbtable_ab'] . "  (";
			$sql .= "uid, etag, ";
			$sql .= "title, firstname, firstname2, lastname, suffix, nickname, phoneticfirstname, phoneticlastname, ";
			$sql .= "jobtitle, department, organization, company, birthdate, note, ";
			$sql .= "addresses, emails, phones, chathandles, relatednames, urls, modification_ts ";
			$sql .= ") VALUES ( ";
			
			$sql .= "$uid, 1, ";
			$sql .= "$title, $firstname, $firstname2, $lastname, $suffix, $nickname, $phoneticfirstname, $phoneticlastname, ";
			$sql .= "$jobtitle, $department, $organization, $company, $birthdate, $note, ";
			$sql .= "$a_line, $e_line, $p_line, $c_line, $r_line, $u_line, $mod_date );";
			
			$db->execute($sql);
			$insertid = $db->insertId();
			if ($insertid == -1) {
				msg("DB error on set: " . $db->lasterror(), -1);
				return false;
			}
			$contact->id = $insertid;
		} else {
			// update
			$sql = "UPDATE " . $db_config ['dbtable_ab'] . " SET ";
			
			$sql .= "uid=$uid, ";
			$sql .= "etag=etag+1, ";
			$sql .= "title=$title, ";
			$sql .= "firstname=$firstname, ";
			$sql .= "firstname2=$firstname2, ";
			$sql .= "lastname=$lastname, ";
			$sql .= "suffix=$suffix, ";
			$sql .= "nickname=$nickname, ";
			$sql .= "phoneticfirstname=$phoneticfirstname, ";
			$sql .= "phoneticlastname=$phoneticlastname, ";
			
			$sql .= "jobtitle=$jobtitle, ";
			$sql .= "department=$department, ";
			$sql .= "organization=$organization, ";
			$sql .= "company=$company, ";
			
			$sql .= "birthdate=$birthdate, ";
			$sql .= "note=$note, ";
			
			$sql .= "addresses=$a_line, ";
			$sql .= "emails=$e_line, ";
			$sql .= "phones=$p_line, ";
			$sql .= "chathandles=$c_line, ";
			$sql .= "relatednames=$r_line, ";
			$sql .= "urls=$u_line, ";
			
			$sql .= "modification_ts = $mod_date ";
			
			$sql .= "WHERE id=$id";
			$result = $db->execute($sql);
			if (!$result) {
				msg("DB error on set: " . $db->lasterror(), -1);
				return false;
			}
		}

		if($conf['mark_changed']) {
			//since the contact is new or has changed, add it to the "changed" category!
			$contact->add_category(new Category(' __changed__'));
		}
		
		$CAT->setCategoriesForPerson($contact->id, $contact->get_categories());
		
		$this->update_ctag();
		
		if ($contact->id > 0) {
			if (!empty($contact->image)) {
				// convert and create image file
				$contact->image = img_convert($contact->image, $conf ['photo_format'], $conf ['photo_resize']);
				img_create($contact->id, $contact->image);
			} else {
				img_delete($contact->id);
			}
		}
		
		return $contact->id;
    }
    
    function delete($personId) {
        global $db;
        global $db_config;
        global $CAT;
        if(!$db) return false;

		if (is_string($personId)) {
			$person = $this->get($personId);
			if (!$person)
				return false;
			$personId = $person->id;
		}
        
		img_delete($personId);
        $CAT->deletePersonFromAllCategories($personId);
        
        // quote db specific characters
        $personId = $db->escape((int)$personId);
        
        $sql = "DELETE FROM ".$db_config['dbtable_ab']." WHERE id=$personId";
        $result = $db->execute($sql);
        if(!$result) {
            msg("DB error on delete: ". $db->lasterror(), -1);
            return false;
        }
        
        $this->update_ctag();
        return true;
    }
    
    function is_duplicate($contact) {
        global $db;
        global $db_config;
        if(!$db) return false;
        
        $contact->validate();
        
        // Input validation
        $id = (int)$contact->id;
        $uid = $db->escape($contact->escape($contact->uid));
        $title = $db->escape($contact->escape($contact->title));
        $firstname = $db->escape($contact->escape($contact->firstname));
        $firstname2 = $db->escape($contact->escape($contact->firstname2));
        $lastname = $db->escape($contact->escape($contact->lastname));
        $suffix = $db->escape($contact->escape($contact->suffix));
        $nickname = $db->escape($contact->escape($contact->nickname));
        $phoneticfirstname = $db->escape($contact->escape($contact->phoneticfirstname));
        $phoneticlastname = $db->escape($contact->escape($contact->phoneticlastname));
        $jobtitle = $db->escape($contact->escape($contact->jobtitle));
        $department = $db->escape($contact->escape($contact->department));
        $organization = $db->escape($contact->escape($contact->organization));
        $company = $db->escape((int)$contact->company);
        $birthdate = $db->escape($contact->escape($contact->birthdate));
        $note = $db->escape($contact->escape($contact->note));
        // TODO: what to do with the photo??
        
        $a_line = $db->escape($contact->addresses_string());
        $e_line = $db->escape($contact->emails_string());
        $p_line = $db->escape($contact->phones_string());
        $c_line = $db->escape($contact->chathandles_string());
        $r_line = $db->escape($contact->relatednames_string());
        $u_line = $db->escape($contact->urls_string());
        
        $sql  = "SELECT * FROM ".$db_config['dbtable_ab']." WHERE ";
        
        $sql .= "(title = $title) AND ";
        $sql .= "(lastname = $lastname) AND ";
        $sql .= "(firstname = $firstname) AND ";
        $sql .= "(firstname2 = $firstname2) AND ";
        $sql .= "(suffix = $suffix) AND ";
        $sql .= "(nickname = $nickname) AND ";
        $sql .= "(phoneticfirstname = $phoneticfirstname) AND ";
        $sql .= "(phoneticlastname = $phoneticlastname) AND ";
        $sql .= "(jobtitle = $jobtitle) AND ";
        $sql .= "(department = $department) AND ";
        $sql .= "(organization = $organization) AND ";
        $sql .= "(company = $company) AND ";
        
        $sql .= "(birthdate = $birthdate) AND ";
        $sql .= "(note = $note) AND ";
        
        $sql .= "(addresses = $a_line) AND ";
        $sql .= "(emails = $e_line) AND ";
        $sql .= "(phones = $p_line) AND ";
        $sql .= "(chathandles = $c_line) AND ";
        $sql .= "(relatednames = $r_line) AND ";
        $sql .= "(urls = $u_line) ";
			// $sql .= "LIMIT 1";
		$row = $db->selectOne($sql);
		if ($row)
			return true;
		
		return false;
    }
    
    function sort($contactlist) {
        global $lang;
        
        //$contactlist is an array of persons
        $sorted_names = array();
        $sorted = array();

        // load sort rules
        $sort_rules_from = explode(',', $lang['sort_rules_from']);
        $sort_rules_to   = explode(',', $lang['sort_rules_to']);
        
        if(is_array($contactlist)) {
            foreach($contactlist as $contact) {
                $sorted_names[$contact->id] = str_replace($sort_rules_from, $sort_rules_to, strtoupper($contact->name()));
            }

            asort($sorted_names);
            
            foreach($sorted_names as $key => $value) $sorted[$key] = $contactlist[$key];
        }
        
        return $sorted;
    }
        
    function update_ctag() {
    	global $db;
    	global $db_config;
    	
        $id = $db->escape($this->addressbookId);
    	
    	$sql = "UPDATE ".$db_config['dbtable_abs']." SET ctag=ctag+1 WHERE id=$id";
    	$db->execute($sql);
    }
    
}


?>
