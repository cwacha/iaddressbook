<?php

require_once(AB_INC."functions/adodb/adodb.inc.php");
require_once(AB_INC."functions/person.php");

class addressbook {
	var $db;
	var $dbtype;
	var $dbname;
	var $dbserver;
	var $dbuser;
	var $dbpass;
	var $dbtable;
	
	
	function addressbook($config = NULL) {
		global $conf;
		
		if($config) {
			$this->dbtype = $config['dbtype'];
			$this->dbname = $config['dbname'];
			$this->dbserver = $config['dbserver'];
			$this->dbuser = $config['dbuser'];
			$this->dbpass = $config['dbpass'];
			$this->dbtable = $config['dbtable'];
		} else  {
			// defaults
			$this->dbtype = $conf['dbtype'];
			$this->dbname = $conf['dbname'];
			$this->dbserver = $conf['dbserver'];
			$this->dbuser = $conf['dbuser'];
			$this->dbpass = $conf['dbpass'];
			$this->dbtable = $conf['dbtable'];
		}
		$this->db = false;
	}
   
	function open() {
		$this->db = NewADOConnection($this->dbtype);
		//$this->db->debug = true;
		$this->db->Connect($this->dbserver, $this->dbuser, $this->dbpass, $this->dbname) or die("Unable to connect!");
        $sql = "SET NAMES 'utf8'";
        $result = $this->db->Execute($sql);
        //$sql = "SET CHARACTER_SET 'utf8'";
        //$result = $this->db->Execute($sql);
	}
   
	function close() {
		if($this->db) $this->db->Close();
		$this->db = false;
	}
	
	function row2contact($row) {
		$contact = new person;
		if(is_object($row)) {
			$contact->id = $row->ID;
			$contact->creationdate = $row->CREATIONDATE . " GMT";
			$contact->modificationdate = $row->MODIFICATIONDATE . " GMT";
			
			$contact->title = $row->TITLE;
			$contact->firstname = $row->FIRSTNAME;
			$contact->firstname2 = $row->FIRSTNAME2;
			$contact->lastname = $row->LASTNAME;
			$contact->suffix = $row->SUFFIX;
			$contact->nickname = $row->NICKNAME;
			
			$contact->jobtitle = $row->JOBTITLE;
			$contact->department = $row->DEPARTMENT;
			$contact->organization = $row->ORGANIZATION;
			$contact->company = (bool)$row->COMPANY;
			
			$contact->birthdate = $row->BIRTHDATE;
			$contact->image = $row->IMAGE;
			$contact->note = $row->NOTE;
			
			$contact->string2addresses($row->ADDRESSES);
			$contact->string2emails($row->EMAILS);
            $contact->string2phones($row->PHONES);
            $contact->string2chathandles($row->CHATHANDLES);
            $contact->string2relatednames($row->RELATEDNAMES);
            $contact->string2urls($row->URLS);
		}
		return $contact;
	}
   
	function find($searchstring, $limit = 1000) {
		if(!$this->db) return array();
		
		$contactlist = array();
		
		// quote db specific characters
		$searchstring = $this->db->qstr(addslashes($searchstring), get_magic_quotes_gpc());
		$searchstring = substr($searchstring, 1, -1);
		
		$sql  = "SELECT * FROM `$this->dbtable` WHERE ";
		$sql .= "(title LIKE '%$searchstring%') OR ";
		$sql .= "(lastname LIKE '%$searchstring%') OR ";
		$sql .= "(firstname LIKE '%$searchstring%') OR ";
		$sql .= "(firstname2 LIKE '%$searchstring%') OR ";
		$sql .= "(suffix LIKE '%$searchstring%') OR ";
		$sql .= "(nickname LIKE '%$searchstring%') OR ";
		$sql .= "(jobtitle LIKE '%$searchstring%') OR ";
		$sql .= "(department LIKE '%$searchstring%') OR ";
		$sql .= "(organization LIKE '%$searchstring%') OR ";
		$sql .= "(company LIKE '%$searchstring%') OR ";
		$sql .= "(birthdate LIKE '%$searchstring%') OR ";
		$sql .= "(note LIKE '%$searchstring%') OR ";
		$sql .= "(addresses LIKE '%$searchstring%') OR ";
		$sql .= "(emails LIKE '%$searchstring%') OR ";
		$sql .= "(phones LIKE '%$searchstring%') OR ";
		$sql .= "(chathandles LIKE '%$searchstring%') OR ";
		$sql .= "(relatednames LIKE '%$searchstring%') OR ";
		$sql .= "(urls LIKE '%$searchstring%') ";
		
		$sql .= " ORDER BY lastname ASC LIMIT $limit";
		
		//echo "SQL: $sql<br>\n";
		$result = $this->db->Execute($sql);
		
		if($result) {
			while ($row = $result->FetchNextObject()) {
				$contact = $this->row2contact($row);
				$contactlist[$contact->id] = $contact;
			}
		} else {
			// no results
		}
		
		return $contactlist;
	}
    
    function getall($limit = 1000) {
		if(!$this->db) return array();
		
		$contactlist = array();
		
		$sql  = "SELECT * FROM `$this->dbtable` ORDER BY lastname ASC LIMIT $limit";
		
		$result = $this->db->Execute($sql);
		
		if($result) {
			while ($row = $result->FetchNextObject()) {
				$contact = $this->row2contact($row);
				$contactlist[$contact->id] = $contact;
			}
		} else {
			// no results
		}
		
		return $contactlist;        
    }

	function get($id) {
		$contact = false;
		if(!$this->db or $id == 0) return $contact;
        
		// quote db specific characters
		$id = $this->db->qstr(addslashes($id), get_magic_quotes_gpc());
		
		$sql = "SELECT * FROM `$this->dbtable` WHERE id=$id LIMIT 1";
		$result = $this->db->Execute($sql);
		
		if($result) {
			$row = $result->FetchNextObject();
			if($row) $contact = $this->row2contact($row);
		} else {
			// not found
		}
		
		return $contact;		
	}
    
   
	function set($contact) {
		if(!$this->db) return false;
		
		if(is_object($contact)) {
        
            $contact->validate();
            
            // Input validation
			$id = (int)$contact->id;
			$title = $this->db->qstr(addslashes($contact->title), get_magic_quotes_gpc());
			$firstname = $this->db->qstr(addslashes($contact->firstname), get_magic_quotes_gpc());
			$firstname2 = $this->db->qstr(addslashes($contact->firstname2), get_magic_quotes_gpc());
			$lastname = $this->db->qstr(addslashes($contact->lastname), get_magic_quotes_gpc());
			$suffix = $this->db->qstr(addslashes($contact->suffix), get_magic_quotes_gpc());
			$nickname = $this->db->qstr(addslashes($contact->nickname), get_magic_quotes_gpc());
			$jobtitle = $this->db->qstr(addslashes($contact->jobtitle), get_magic_quotes_gpc());
			$department = $this->db->qstr(addslashes($contact->department), get_magic_quotes_gpc());
			$organization = $this->db->qstr(addslashes($contact->organization), get_magic_quotes_gpc());
			$company = "'" . (int)$contact->company . "'";
			$birthdate = $this->db->qstr(addslashes($contact->birthdate), get_magic_quotes_gpc());
			//$image = $this->db->qstr($contact->image, get_magic_quotes_gpc());
            $image = "'". addslashes($contact->image) ."'";
			$note = $this->db->qstr(addslashes($contact->note), get_magic_quotes_gpc());
            
            
            $a_line = $this->db->qstr(addslashes($contact->addresses_string()), get_magic_quotes_gpc());
			$e_line = $this->db->qstr(addslashes($contact->emails_string()), get_magic_quotes_gpc());
			$p_line = $this->db->qstr(addslashes($contact->phones_string()), get_magic_quotes_gpc());
			$c_line = $this->db->qstr(addslashes($contact->chathandles_string()), get_magic_quotes_gpc());
            $r_line = $this->db->qstr(addslashes($contact->relatednames_string()), get_magic_quotes_gpc());
			$u_line = $this->db->qstr(addslashes($contact->urls_string()), get_magic_quotes_gpc());
			
			$mod_date = gmdate('Y-m-d H:i:s') . ' GMT';
            
            if(empty($contact->birthdate)) $contact->birthdate = '0000-00-00';
            
			if($contact->id == 0) {
				// insert
				$sql = "INSERT INTO `$this->dbtable`  ";
				$sql .= "( id, title, firstname, firstname2, lastname, suffix, nickname, ";
				$sql .= "jobtitle, department, organization, company, birthdate, image, note, ";
				$sql .= "addresses, emails, phones, chathandles, relatednames, urls, creationdate, modificationdate ) VALUES ( ";
				//$sql .= "VALUES (";
				
				$sql .= "'', $title, $firstname, $firstname2, $lastname, $suffix, $nickname, ";
				$sql .= "$jobtitle, $department, $organization, $company, $birthdate, $image, $note, ";
				
				$sql .= "$a_line, $e_line, $p_line, $c_line, $r_line, $u_line, '$mod_date', '$mod_date' );";
				
			} else {
				// update
				$sql = "UPDATE `$this->dbtable` SET ";
					
				$sql .= "title=$title, ";
				$sql .= "firstname=$firstname, ";
				$sql .= "firstname2=$firstname2, ";
				$sql .= "lastname=$lastname, ";
				$sql .= "suffix=$suffix, ";
				$sql .= "nickname=$nickname, ";
				
				$sql .= "jobtitle=$jobtitle, ";
				$sql .= "department=$department, ";
				$sql .= "organization=$organization, ";
				$sql .= "company=$company, ";
				
				$sql .= "birthdate=$birthdate, ";
				$sql .= "image=$image, ";
				$sql .= "note=$note, ";
				
				$sql .= "addresses=$a_line, ";
				$sql .= "emails=$e_line, ";
				$sql .= "phones=$p_line, ";
				$sql .= "chathandles=$c_line, ";
                $sql .= "relatednames=$r_line, ";
				$sql .= "urls=$u_line, ";
	
				$sql .= "modificationdate = '$mod_date' ";
	
				$sql .= "WHERE id='$id'";
			}
			
			$result = $this->db->Execute($sql);
			
			if($result) return true;
			return false;
		}
	}
	
	function delete($id) {
		if(!$this->db) return;
		
		// quote db specific characters
		$id = $this->db->qstr(addslashes($id), get_magic_quotes_gpc());
		
		$sql = "DELETE FROM `$this->dbtable` WHERE id=$id LIMIT 1";
		$result = $this->db->Execute($sql);
	}
    
    function is_duplicate($contact) {
		if(!$this->db) return false;
        
        //return false;
        
        $contact->validate();
        
        // Input validation
        $id = (int)$contact->id;
        $title = $this->db->qstr(addslashes($contact->title), get_magic_quotes_gpc());
        $firstname = $this->db->qstr(addslashes($contact->firstname), get_magic_quotes_gpc());
        $firstname2 = $this->db->qstr(addslashes($contact->firstname2), get_magic_quotes_gpc());
        $lastname = $this->db->qstr(addslashes($contact->lastname), get_magic_quotes_gpc());
        $suffix = $this->db->qstr(addslashes($contact->suffix), get_magic_quotes_gpc());
        $nickname = $this->db->qstr(addslashes($contact->nickname), get_magic_quotes_gpc());
        $jobtitle = $this->db->qstr(addslashes($contact->jobtitle), get_magic_quotes_gpc());
        $department = $this->db->qstr(addslashes($contact->department), get_magic_quotes_gpc());
        $organization = $this->db->qstr(addslashes($contact->organization), get_magic_quotes_gpc());
        //$contact->company = $this->db->qstr($contact->company, get_magic_quotes_gpc());
        $birthdate = $this->db->qstr(addslashes($contact->birthdate), get_magic_quotes_gpc());
        $note = $this->db->qstr(addslashes($contact->note), get_magic_quotes_gpc());
        
        $a_line = $this->db->qstr(addslashes($contact->addresses_string()), get_magic_quotes_gpc());
        $e_line = $this->db->qstr(addslashes($contact->emails_string()), get_magic_quotes_gpc());
        $p_line = $this->db->qstr(addslashes($contact->phones_string()), get_magic_quotes_gpc());
        $c_line = $this->db->qstr(addslashes($contact->chathandles_string()), get_magic_quotes_gpc());
        $r_line = $this->db->qstr(addslashes($contact->relatednames_string()), get_magic_quotes_gpc());
        $u_line = $this->db->qstr(addslashes($contact->urls_string()), get_magic_quotes_gpc());

		
   		$sql  = "SELECT * FROM `$this->dbtable` WHERE ";
		
        $sql .= "(title = $title) AND ";
		$sql .= "(lastname = $lastname) AND ";
		$sql .= "(firstname = $firstname) AND ";
		$sql .= "(firstname2 = $firstname2) AND ";
		$sql .= "(suffix = $suffix) AND ";
		$sql .= "(nickname = $nickname) AND ";
		$sql .= "(jobtitle = $jobtitle) AND ";
		$sql .= "(department = $department) AND ";
		$sql .= "(organization = $organization) AND ";
		$sql .= "(company = '". (int)$contact->company."') AND ";
		
        $sql .= "(birthdate = $birthdate) AND ";
		$sql .= "(note = $note) AND ";
		
        $sql .= "(addresses = $a_line) AND ";
		$sql .= "(emails = $e_line) AND ";
		$sql .= "(phones = $p_line) AND ";
		$sql .= "(chathandles = $c_line) AND ";
		$sql .= "(relatednames = $r_line) AND ";
		$sql .= "(urls = $u_line) ";
        //$sql .= "LIMIT 1";
		
		$result = $this->db->Execute($sql);
        if($result) {
            $row = $result->FetchNextObject();
            if($row) return true;
        }
        
        return false;
    }


}


?>