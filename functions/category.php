<?php
/**
 * AddressBook category functions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Clemens Wacha <clemens.wacha@gmx.net>
 */

	if(!defined('AB_CONF')) define('AB_CONF',AB_INC.'conf/');
	require_once(AB_CONF.'defaults.php');

	if(!defined('AB_INC')) define('AB_INC',realpath(dirname(__FILE__).'/../').'/');
    require_once(AB_INC.'functions/db.php');
    require_once(AB_INC.'functions/common.php');


class category {
    var $id;
    var $name;
    var $type;
    var $query;
    
    function category() {
        $this->id = 0;
        $this->name = '';
        $this->type = 0;
        $this->query = '';
    }

    function html_escape() {
        $this->id = (int)$this->id;
        $this->name = htmlspecialchars($this->name);
        $this->type = (int)$this->type;
		//this->query??
    }

}

class categories {

    var $selected;

    function categories($config = NULL) {
        $this->selected = 0;
    }
    
    function row2category($row) {
        global $lang;
        global $db_config;
        $prefix = '';
        
        
        $category = new category;
        if(empty($row)) return $category;

        if(is_object($row)) $row = get_object_vars($row);
        
        $row = array_change_key_case($row, CASE_UPPER);
        
       	if(!array_key_exists('ID', $row)) {
       		$prefix = strtoupper($db_config['dbtable_cat']) . '.';
       	}

		$category->id = (int)$row[$prefix . 'ID'];
		$category->name = $row[$prefix . 'NAME'];
		$category->type = (int)$row[$prefix . 'TYPE'];
		$category->query = $row[$prefix . 'QUERY'];
		
		if($category->name == ' __lastimport__') $category->name = $lang['cat_lastimport'];
		if($category->name == ' __changed__') $category->name = $lang['cat_changed'];
        
        return $category;
    }

    function get($id) {
        global $db;
        global $db_config;
		$category = new category;
		if(!$db or $id == 0) return false;
        
		// quote db specific characters
		$id = $db->Quote((int)$id);
		
		$sql = "SELECT * FROM ".$db_config['dbtable_cat']." WHERE id=$id LIMIT 1";
		$result = $db->Execute($sql);
		
		if($result) {
			$row = $result->FetchRow();
			if($row) $category = $this->row2category($row);
            return $category;
		} else {
			// not found
            msg("DB error on get: ". $db->ErrorMsg(), -1);
		}
		
		return false;	    
    }
    
    function set($category) {
        global $db;
        global $db_config;
		if(!$db) return false;
		
		if(is_object($category)) {
            
            // Input validation
            $id = $db->Quote((int)$category->id);
			$name = $db->Quote($category->name);
            $type = $db->Quote((int)$category->type);
            $query = $db->Quote($category->query);
            
			if($id == 0) {
				// insert
				$sql = "INSERT INTO ".$db_config['dbtable_cat']."  ";
				$sql .= "( name, type, query ) VALUES ( ";
				
				$sql .= " $name, $type, $query );";
				
			} else {
				// update
				$sql = "UPDATE ".$db_config['dbtable_cat']." SET ";
				
				$sql .= "name=$name, ";
				$sql .= "type=$type, ";
				$sql .= "query=$query ";
                
				$sql .= "WHERE id=$id";
			}
			
			$result = $db->Execute($sql);
            if(!$result) {
                msg("DB error on set: ". $db->ErrorMsg(), -1);
                return false;
            }
			
            if($id == 0) $id = $db->Insert_ID();
            
			return $id;
		}
        return false;
    }
    
    function delete($id) {
        global $db;
        global $db_config;
		if(!$db) return false;
        
		// quote db specific characters
		$id = $db->Quote((int)$id);
		
		$sql = "DELETE FROM ".$db_config['dbtable_cat']." WHERE id=$id";
		$result = $db->Execute($sql);
		if(!$result) {
            msg("DB error delete: ". $db->ErrorMsg(), -1);
            return false;
        }
        
        $sql = "DELETE FROM ".$db_config['dbtable_catmap']." WHERE category_id=$id";
		$result = $db->Execute($sql);
		if(!$result) {
            msg("DB error on delete: ". $db->ErrorMsg(), -1);
            return false;
        }
        
        return true;
    }
    
    function find($person_id, $limit = 1000) {
        global $db;
        global $db_config;
		if(!$db) return array();
        
		$categorylist = array();
		
		// quote db specific characters
        $searchstring = $db->Quote((int)$person_id);
        $internal = $db->Quote(' __%__');
		
		$sql  = "SELECT ".$db_config['dbtable_cat'].".* FROM ".$db_config['dbtable_cat'].", ".$db_config['dbtable_catmap']." WHERE (";
        $sql .= $db_config['dbtable_cat'].".id = ".$db_config['dbtable_catmap'].".category_id ) AND "; 
		$sql .= "( person_id = $searchstring ) AND ( name NOT LIKE ".$internal." ) ";
		
		$sql .= " LIMIT $limit";
		
		$result = $db->Execute($sql);
		
		if($result) {
			while ($row = $result->FetchRow()) {
				$category = $this->row2category($row);
				$categorylist[$category->id] = $category;
			}
		} else {
			// no results
            msg("DB error on find: ". $db->ErrorMsg(), -1);
		}
		
		return $categorylist;
    }
    
    function getall($limit = 1000) {
        global $db;
        global $db_config;
		if(!$db) return array();
		
		$categorylist = array();
		
		$sql  = "SELECT * FROM ".$db_config['dbtable_cat']." ORDER BY name ASC LIMIT $limit";
		
		$result = $db->Execute($sql);
		
		if($result) {
			while ($row = $result->FetchRow()) {
				$category = $this->row2category($row);
				$categorylist[$category->id] = $category;
			}
		} else {
			// no results
            msg("DB error on getall: ". $db->ErrorMsg(), -1);
		}
		
		return $categorylist;
    }
    
    function add_contact($contact_id, $category_id) {
        global $db;
        global $db_config;
		if(!$db) return false;
        
        // Input validation
        $c_id = $db->Quote((int)$category_id);
        $p_id = $db->Quote((int)$contact_id);
        
        if(!$this->is_member($contact_id, $category_id)) {
           
            // insert
            $sql = "INSERT INTO ".$db_config['dbtable_catmap']."  ";
            $sql .= "( category_id, person_id ) VALUES ( ";
            
            $sql .= " $c_id, $p_id );";
            
            $result = $db->Execute($sql);
            if($result) return true;
            
            msg("DB error on add_contact: ". $db->ErrorMsg(), -1);
            return false;
        }
        
        return true;
    }
    
    function delete_contact($contact_id, $category_id) {
        global $db;
        global $db_config;
		if(!$db) return;
        
		$id = $this->is_member($contact_id, $category_id);
		
		while($id) {
            $sql = "DELETE FROM ".$db_config['dbtable_catmap']." WHERE id=$id";
            $result = $db->Execute($sql);
            if(!$result) {
                msg("DB error on delete_contact: ". $db->ErrorMsg(), -1);
                return false;
            }
            $id = $this->is_member($contact_id, $category_id);
        }
        return true;
    }
    
    function is_member($contact_id, $category_id) {
        global $db;
        global $db_config;
		if(!$db) return false;
        
		if($this->exists((int)$category_id) === false) return false;

		// quote db specific characters
        $contact_id = $db->Quote((int)$contact_id);
        $category_id = $db->Quote((int)$category_id);
        
		$sql  = "SELECT * FROM ".$db_config['dbtable_catmap']." WHERE ";
		$sql .= "(category_id = $category_id) AND ";
		$sql .= "(person_id = $contact_id) ";
        
		$result = $db->Execute($sql);
		if($result) {
			$row = $result->FetchRow();
            $category = $this->row2category($row);
            if($row) return $category->id;
		} else {
            msg("DB error on is_member: ". $db->ErrorMsg(), -1);
        }
        
		return false;
    }
    
    function exists($mixed) {
        global $db;
        global $db_config;
		if(!$db) return false;
        
        $sql  = "SELECT * FROM ".$db_config['dbtable_cat']." WHERE ";
        
        if(is_string($mixed)) {
            $name = $db->Quote($mixed);
            $sql .= "(name = $name) ";
        } else if(is_object($mixed)) {
            $name = $db->Quote($mixed->name);
            $sql .= "(name = $name) ";
        } else if(is_int($mixed)) {
            $id = $db->Quote((int)$mixed);
            $sql .= "(id = $id) ";
        } else return false;
        
		$result = $db->Execute($sql);
		
		if($result) {
			$row = $result->FetchRow();
            if($row) {
				$category = $this->row2category($row);
                return $category;
			}
		} else {
			// no results
            msg("DB error on exists: ". $db->ErrorMsg(), -1);
		}

        return false;
    }
    
    function sort($categories) {
        //$categories is an array of category
        $sorted_names = array();
        $sorted = array();
        
        if(is_array($categories)) {
            foreach($categories as $cat) $sorted_names[$cat->id] = strtoupper($cat->name);
            asort($sorted_names);
            
            foreach($sorted_names as $key => $value) $sorted[$key] = $categories[$key];
        }
        
        return $sorted;
    }
    
}



?>