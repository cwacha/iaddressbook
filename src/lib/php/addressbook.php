<?php
    /**
     * iAddressBook adressbook functions
     *
     * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
     * @author     Clemens Wacha <clemens.wacha@gmx.net>
     */

    if(!defined('AB_BASEDIR')) define('AB_BASEDIR',realpath(dirname(__FILE__).'/../../'));
    require_once(AB_BASEDIR.'/lib/php/include.php');
    require_once(AB_BASEDIR.'/lib/php/db.php');
    require_once(AB_BASEDIR.'/lib/php/person.php');
    require_once(AB_BASEDIR.'/lib/php/common.php');

class addressbook {
    
    function addressbook() {
    }
    
    function row2contact($row) {
        global $db_config;
        $prefix = '';
        
        $contact = new person;
        if(empty($row)) return $contact;
        
        if(is_object($row)) $row = get_object_vars($row);
        
        $row = array_change_key_case($row, CASE_UPPER);
        
        if(!array_key_exists('ID', $row)) {
            $prefix = strtoupper($db_config['dbtable_ab']) . '.';
        }
        
        $contact->id = (int)$row[$prefix . 'ID'];
        $contact->creationdate = $contact->unescape($row[$prefix . 'CREATIONDATE']);
        $contact->modificationdate = $contact->unescape($row[$prefix . 'MODIFICATIONDATE']);
        
        $contact->title = $contact->unescape($row[$prefix . 'TITLE']);
        $contact->firstname = $contact->unescape($row[$prefix . 'FIRSTNAME']);
        $contact->firstname2 = $contact->unescape($row[$prefix . 'FIRSTNAME2']);
        $contact->lastname = $contact->unescape($row[$prefix . 'LASTNAME']);
        $contact->suffix = $contact->unescape($row[$prefix . 'SUFFIX']);
        $contact->nickname = $contact->unescape($row[$prefix . 'NICKNAME']);
        $contact->phoneticfirstname = $contact->unescape($row[$prefix . 'PHONETICFIRSTNAME']);
        $contact->phoneticlastname = $contact->unescape($row[$prefix . 'PHONETICLASTNAME']);

        $contact->jobtitle = $contact->unescape($row[$prefix . 'JOBTITLE']);
        $contact->department = $contact->unescape($row[$prefix . 'DEPARTMENT']);
        $contact->organization = $contact->unescape($row[$prefix . 'ORGANIZATION']);
        $contact->company = (int)$row[$prefix . 'COMPANY'];
        
        $contact->birthdate = $contact->unescape($row[$prefix . 'BIRTHDATE']);
        $contact->note = $contact->unescape($row[$prefix . 'NOTE']);
        
        $contact->string2addresses($row[$prefix . 'ADDRESSES']);
        $contact->string2emails($row[$prefix . 'EMAILS']);
        $contact->string2phones($row[$prefix . 'PHONES']);
        $contact->string2chathandles($row[$prefix . 'CHATHANDLES']);
        $contact->string2relatednames($row[$prefix . 'RELATEDNAMES']);
        $contact->string2urls($row[$prefix . 'URLS']);
        
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
            $value = $db->Quote("%" . $value . "%");
            $search_array[$key] = $value;
        }
        $selected = $db->Quote($CAT_ID);
        
        if($CAT_ID == 0) {
            $sql  = "SELECT * FROM ".$db_config['dbtable_ab']." WHERE ";
            foreach($search_array as $key => $value) {
                $sql1  = "(title               LIKE $value) OR ";
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
                $sql1  = "(title               LIKE $value) OR ";
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
        $result = $db->Execute($sql);
        
        if($result) {
            while ($row = $result->FetchRow()) {
                $contact = $this->row2contact($row);
                $contactlist[$contact->id] = $contact;
            }
        } else {
            // no results
            msg("DB error on find: ".$db->ErrorMsg(), -1);
        }
        
        return $contactlist;
    }
    
    function getall($limit = 1000, $offset = 0) {
        global $db;
        global $db_config;
        global $CAT;
        global $CAT_ID;
        
        $contactlist = array();
        
        if(!$db) return $contactlist;
        
        $selected = $db->Quote($CAT_ID);
        
        if($CAT_ID == 0) {
            $sql  = "SELECT * FROM ".$db_config['dbtable_ab']." ORDER BY lastname ASC LIMIT $limit OFFSET $offset";
        } else {
            $sql_select  = $db_config['dbtable_ab'].".*, ";
            $sql_select .= $db_config['dbtable_catmap'].".category_id ";
            
            $sql  = "SELECT ".$sql_select." FROM ".$db_config['dbtable_catmap'].", ".$db_config['dbtable_ab']." WHERE (";
            $sql .= $db_config['dbtable_catmap'].".person_id = ".$db_config['dbtable_ab'].".id AND ".$db_config['dbtable_catmap'].".category_id = ".$selected.") ";
            $sql .= " ORDER BY lastname ASC LIMIT $limit OFFSET $offset";
        }
        
        $result = $db->Execute($sql);
        
        if($result) {
            while ($row = $result->FetchRow()) {
                $contact = $this->row2contact($row);
                $contactlist[$contact->id] = $contact;
            }
        } else {
            // no results
            msg("DB error on getall: ".$db->ErrorMsg(), -1);
        }
        
        return $contactlist;
    }

    function get($id, $with_image = false) {
        global $db;
        global $db_config;
        $contact = false;
        if(!$db or $id == 0) return $contact;
        
        // quote db specific characters
        $id = $db->Quote((int)$id);
        
        $sql = "SELECT * FROM ".$db_config['dbtable_ab']." WHERE id=$id LIMIT 1";
        $result = $db->Execute($sql);
        
        if($result) {
            $row = $result->FetchRow();
            if($row) {
                $contact = $this->row2contact($row);
                if($with_image === true) $contact->image = img_load($contact->id);
            }
        } else {
            // not found
            msg("DB error on get: ".$db->ErrorMsg(), -1);
        }
        
        return $contact;        
    }
    
   
    function set($contact) {
        global $db;
        global $db_config;
        global $conf;
        if(!$db) return false;
        
        if(is_object($contact)) {
            
            $contact->validate();
            
            // Input validation
            $id = $db->Quote((int)$contact->id);
            $title = $db->Quote($contact->escape($contact->title));
            $firstname = $db->Quote($contact->escape($contact->firstname));
            $firstname2 = $db->Quote($contact->escape($contact->firstname2));
            $lastname = $db->Quote($contact->escape($contact->lastname));
            $suffix = $db->Quote($contact->escape($contact->suffix));
            $nickname = $db->Quote($contact->escape($contact->nickname));
            $phoneticfirstname = $db->Quote($contact->escape($contact->phoneticfirstname));
            $phoneticlastname = $db->Quote($contact->escape($contact->phoneticlastname));
            $jobtitle = $db->Quote($contact->escape($contact->jobtitle));
            $department = $db->Quote($contact->escape($contact->department));
            $organization = $db->Quote($contact->escape($contact->organization));
            $company = $db->Quote( (int)$contact->company);
            $birthdate = $db->Quote($contact->escape($contact->birthdate));
            $note = $db->Quote($contact->escape($contact->note));
                        
            $a_line = $db->Quote($contact->addresses_string());
            $e_line = $db->Quote($contact->emails_string());
            $p_line = $db->Quote($contact->phones_string());
            $c_line = $db->Quote($contact->chathandles_string());
            $r_line = $db->Quote($contact->relatednames_string());
            $u_line = $db->Quote($contact->urls_string());
            
            $mod_date = $db->Quote($contact->escape(gmdate('Y-m-d H:i:s') . ' GMT'));
            
            if(empty($contact->birthdate)) $contact->birthdate = '0001-01-01';
            
            if($contact->id == 0) {
                // insert
                $sql = "INSERT INTO ".$db_config['dbtable_ab']."  ";
                $sql .= "( title, firstname, firstname2, lastname, suffix, nickname, phoneticfirstname, phoneticlastname, ";
                $sql .= "jobtitle, department, organization, company, birthdate, note, ";
                $sql .= "addresses, emails, phones, chathandles, relatednames, urls, creationdate, modificationdate ) VALUES ( ";
                //$sql .= "VALUES (";
                
                $sql .= "$title, $firstname, $firstname2, $lastname, $suffix, $nickname, $phoneticfirstname, $phoneticlastname, ";
                $sql .= "$jobtitle, $department, $organization, $company, $birthdate, $note, ";
                
                $sql .= "$a_line, $e_line, $p_line, $c_line, $r_line, $u_line, $mod_date, $mod_date );";
                
            } else {
                // update
                $sql = "UPDATE ".$db_config['dbtable_ab']." SET ";
                    
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
    
                $sql .= "modificationdate = $mod_date ";
    
                $sql .= "WHERE id=$id";
            }
            
            $result = $db->Execute($sql);
            if(!$result) {
                msg("DB error on set: ". $db->ErrorMsg(), -1);
                return false;
            }
            
            if($contact->id == 0) $contact->id = $db->Insert_ID();
            //msg("InsertID: ". $db->Insert_ID());
            
            if($contact->id > 0) {
                if(!empty($contact->image)) {
                    // convert and create image file
                    $contact->image = img_convert($contact->image, $conf['photo_format'], $conf['photo_resize']);
                    img_create($contact->id, $contact->image);
                } else {
                    img_delete($contact->id);
                }
            }
            
            return $contact->id;
        }
    }
    
    function delete($id) {
        global $db;
        global $db_config;
        if(!$db) return;
        
        img_delete($id);

        // quote db specific characters
        $id = $db->Quote((int)$id);
        
        $sql = "DELETE FROM ".$db_config['dbtable_ab']." WHERE id=$id";
        $result = $db->Execute($sql);
        if(!$result) {
            msg("DB error on delete: ". $db->ErrorMsg(), -1);
            print_r($result);
        }        
    }
    
    function is_duplicate($contact) {
        global $db;
        global $db_config;
        if(!$db) return false;
        
        //return false;

        $contact->validate();
        
        // Input validation
        $id = (int)$contact->id;
        $title = $db->Quote($contact->escape($contact->title));
        $firstname = $db->Quote($contact->escape($contact->firstname));
        $firstname2 = $db->Quote($contact->escape($contact->firstname2));
        $lastname = $db->Quote($contact->escape($contact->lastname));
        $suffix = $db->Quote($contact->escape($contact->suffix));
        $nickname = $db->Quote($contact->escape($contact->nickname));
        $phoneticfirstname = $db->Quote($contact->escape($contact->phoneticfirstname));
        $phoneticlastname = $db->Quote($contact->escape($contact->phoneticlastname));
        $jobtitle = $db->Quote($contact->escape($contact->jobtitle));
        $department = $db->Quote($contact->escape($contact->department));
        $organization = $db->Quote($contact->escape($contact->organization));
        $company = $db->Quote((int)$contact->company);
        $birthdate = $db->Quote($contact->escape($contact->birthdate));
        $note = $db->Quote($contact->escape($contact->note));
        // TODO: what to do with the photo??
        
        $a_line = $db->Quote($contact->addresses_string());
        $e_line = $db->Quote($contact->emails_string());
        $p_line = $db->Quote($contact->phones_string());
        $c_line = $db->Quote($contact->chathandles_string());
        $r_line = $db->Quote($contact->relatednames_string());
        $u_line = $db->Quote($contact->urls_string());

        
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
        //$sql .= "LIMIT 1";
        
        $result = $db->Execute($sql);
        if($result) {
            $row = $result->FetchRow();
            if($row) return true;
        } else {
            msg("DB error on is_duplicate: ". $db->ErrorMsg(), -1);
        }
        
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


}


?>
