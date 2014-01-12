<?php
/**
     * iAddressBook category functions
     *
     * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
     * @author     Clemens Wacha <clemens.wacha@gmx.net>
     */
if (!defined('AB_BASEDIR'))
	define('AB_BASEDIR', realpath(dirname(__FILE__) . '/../../'));
require_once (AB_BASEDIR . '/lib/php/include.php');
require_once (AB_BASEDIR . '/lib/php/db.php');
require_once (AB_BASEDIR . '/lib/php/common.php');

class Category {
	var $id;
	var $uid;
	var $name;
	var $modification_ts; // unix timestamp
	var $etag;

	function Category($name = '') {
		$this->id = 0;
		$this->uid = '';
		$this->name = $name;
		$this->modification_ts = time();
		$this->etag = 0;
	}

	public function name() {
		return $this->name;
	}

	function displayName() {
		global $lang;
		
		if ($this->name == ' __all__')
			return $lang ['category_all'];
		if ($this->name == ' __lastimport__')
			return $lang ['cat_lastimport'];
		if ($this->name == ' __changed__')
			return $lang ['cat_changed'];
		
		return $this->name;
	}

	public function html_escape() {
		$this->id = ( int ) $this->id;
		$this->uid = htmlspecialchars($this->uid);
		$this->name = htmlspecialchars($this->name);
	}
}
class Categories {

	function Categories($config = NULL) {
	}

	function row2category($row) {
		global $lang;
		global $db_config;
		
		$category = new Category();
		if (empty($row))
			return $category;
		
		if (is_object($row))
			$row = get_object_vars($row);
		
		$row = array_change_key_case($row, CASE_LOWER);
		
		$prefix = '';
		if (!array_key_exists('id', $row)) {
			$prefix = 'c.';
		}

		$category->id = ( int ) $row [$prefix . 'id'];
		$category->uid = $row [$prefix . 'uid'];
		$category->name = $row [$prefix . 'name'];
		$category->modification_ts = ( int ) $row [$prefix . 'modification_ts'];
		$category->etag = ( int ) $row [$prefix . 'etag'];
		
		return $category;
	}
	
	// returns array of all categories, empty array else
	function getAllCategories($limit = 1000) {
		global $db;
		global $db_config;
		$categories = array ();
		
		if (!$db)
			return $categories;
				
		$sql = "SELECT * FROM " . $db_config ['dbtable_cat'] . " ORDER BY name ASC LIMIT $limit";
		
		$result = $db->selectAll($sql);
		
		foreach ( $result as $row ) {
			$category = $this->row2category($row);
			$categories [] = $category;
		}
		
		$categories [] = new Category(' __all__');
		
		return $categories;
	}
	
	// return array of categories for person, empty array otherwise
	function getCategoriesForPerson($personId, $limit = 1000) {
		global $db;
		global $db_config;
		if (!$db)
			return array ();
		
		$categories = array ();
		
		// quote db specific characters
		$personId = $db->escape(( int ) $personId);
		
		$sql = "SELECT c.* FROM " . $db_config ['dbtable_cat'] . " c, " . $db_config ['dbtable_catmap'] . " cm ";
		$sql .= "WHERE c.id=cm.category_id AND ";
		$sql .= "( cm.person_id = $personId ) AND ( c.name NOT LIKE ' __all__' ) ";
		$sql .= "ORDER BY c.name ";
		$sql .= "LIMIT $limit";
		
		$result = $db->selectAll($sql);
		
		foreach ( $result as $row ) {
			$category = $this->row2category($row);
			$categories [] = $category;
		}
		
		return $categories;
	}
	
	function addMembersToCategory($categoryId, $memberIds) {
		global $db;
		global $db_config;
		global $AB;
		if (!$db)
			return false;
		
		$category = $this->get($categoryId);
		if (!$category)
			return false;
		$categoryId = $category->id;
		
		$new_members = array();
		foreach($memberIds as $memberId) {
			$person = $AB->get($memberId);
			if(!$person)
				continue;
			$new_members [$person->id] = $person->uid;
		}
		
		$etag_updates = array();
		foreach($new_members as $personId => $personUUID) {
			if ($this->personIsMemberOf($personId, $categoryId))
				continue;
			
			$etag_updates[$categoryId] = true; 

			// Input validation
			$personId = $db->escape(( int ) $personId);
			$categoryId = $db->escape(( int ) $categoryId);
			
			// insert
			$sql = "INSERT INTO " . $db_config ['dbtable_catmap'] . "  ";
			$sql .= "( category_id, person_id ) VALUES ( ";
			$sql .= " $categoryId, $personId );";
			
			$result = $db->execute($sql);
				
			if (!$result) {
				msg("DB error on addMembersToCategory: " . $db->lasterror(), -1);
				return false;
			}
		}
		
		foreach($etag_updates as $categoryId => $value) {
			$this->update_etag($categoryId);
		}
		$AB->update_ctag();
		
		return true;
	}
		
	function deleteMembersFromCategory($categoryId, $members) {
		global $db;
		global $db_config;
		global $AB;
		if (!$db)
			return false;
		
		$category = $this->get($categoryId);
		if (!$category)
			return false;
		$categoryId = $category->id;

		$new_members = array ();
		foreach ( $members as $memberId ) {
			$person = $AB->get($memberId);
			if (!$person)
				continue;
			$new_members [$person->id] = $person->uid;
		}
		
		$etag_updates = array();
		foreach ( $new_members as $personId => $personUUID ) {
			if (!$this->personIsMemberOf($personId, $categoryId))
				continue;
				
			$etag_updates[$categoryId] = true; 

			// Input validation
			$personId = $db->escape(( int ) $personId);
			$categoryId = $db->escape(( int ) $categoryId);
			
			$sql = "DELETE FROM " . $db_config ['dbtable_catmap'] . " WHERE ";
			$sql .= "category_id=$categoryId AND person_id=$personId;";
			$result = $db->execute($sql);
			
			if (!$result) {
				msg("DB error on deleteMembersFromCategory: " . $db->lasterror(), -1);
				return false;
			}
		}

		foreach($etag_updates as $categoryId => $value) {
			$this->update_etag($categoryId);
		}
		$AB->update_ctag();
		
		return true;
	}
		
	function deletePersonFromAllCategories($personId) {
		return $this->setCategoriesForPerson($personId, array());
	}

	function personIsMemberOf($personId, $categoryId) {
		global $db;
		global $db_config;
		if (!$db)
			return false;
		
		if (is_string($categoryId)) {
			$category = $this->get($categoryId);
			if (!$category)
				return false;
			$categoryId = $category->id;
		}
				
		$personId = $db->escape(( int ) $personId);
		$categoryId = $db->escape(( int ) $categoryId);
		
		$sql = "SELECT * FROM " . $db_config ['dbtable_catmap'] . " WHERE ";
		$sql .= "(category_id = $categoryId) AND ";
		$sql .= "(person_id = $personId) ";
		
		$row = $db->selectOne($sql);
		if ($row)
			return true;
		
		return false;
	}

	function getByName($categoryName) {
		global $db;
		global $db_config;
		if (!$db)
			return false;
		
		if(!$categoryName)
			return false;
			
			// quote db specific characters
		$categoryName = $db->escape($categoryName);
		
		$sql = "SELECT * FROM " . $db_config ['dbtable_cat'] . " WHERE name=$categoryName LIMIT 1";
		$row = $db->selectOne($sql);
		
		if (!$row)
			return false;
		
		$category = $this->row2category($row);
		return $category;
	}

	function get($categoryId) {
		global $db;
		global $db_config;
		if (!$db)
			return false;
			
			// quote db specific characters
		if (is_string($categoryId)) {
			$uid = $db->escape(strtolower($categoryId));
			$sql = "SELECT * FROM " . $db_config ['dbtable_cat'] . " WHERE uid=$uid LIMIT 1";
		} else {
			$id = $db->escape(( int ) $categoryId);
			$sql = "SELECT * FROM " . $db_config ['dbtable_cat'] . " WHERE id=$id LIMIT 1";
		}
		
		$row = $db->selectOne($sql);
		
		if (!$row)
			return false;
		
		$category = $this->row2category($row);
		return $category;
	}
	
	function getMembersForCategory($categoryId) {
		global $db;
		global $db_config;
		if (!$db)
			return false;
		
		//msg("getMembersForCategory: " . $categoryId);

		if (is_string($categoryId)) {
			$category = $this->get($categoryId);
			if (!$category)
				return false;
			$categoryId = $category->id;
		}
		
		// quote db specific characters
		$categoryId = $db->escape(( int ) $categoryId);
		
		$sql = "SELECT a.* FROM " . $db_config ['dbtable_cat'] . " c, " . $db_config ['dbtable_catmap'] . " cm, " . $db_config['dbtable_ab'] . " a ";
		$sql .= "WHERE c.id=cm.category_id AND a.id=cm.person_id AND ";
		$sql .= "( c.id=$categoryId ) ";
		//$sql .= " LIMIT $limit";
		
		$result = $db->selectAll($sql);
		
		$members = array ();
		foreach ( $result as $row ) {
			$row = array_change_key_case($row, CASE_LOWER);
			
			$prefix = '';
			if (!array_key_exists('id', $row)) {
				$prefix = 'a.';
			}
			
			$members [$row [$prefix . 'id']] = $row [$prefix . 'uid'];
		}
		
		return $members;
	}
	
	function setMembersForCategory($categoryId, $memberUUIDs) {
		$category = $this->get($categoryId);
		if (!$category)
			return false;
		
		// calculate elements to remove
		$old_members = $this->getMembersForCategory($category->id);
		$memberUUIDs = array_flip($memberUUIDs);
		foreach ($old_members as $oldMemberId => $oldMemberUUID) {
			if(isset($memberUUIDs[$oldMemberUUID]) || isset($memberUUIDs[$oldMemberId])) {
				unset($old_members[$oldMemberId]);
			}
		}
		
		// remove old elements
		$this->deleteMembersFromCategory($category->id, $old_members);

		// add new elements
		$this->addMembersToCategory($category->id, $memberUUIDs);

		return true;
	}
	
	function setCategoriesForPerson($personId, $categories) {
		if(!is_array($categories))
			return false;
		
		$newCategories = array();
		foreach($categories as $category) {
			$categoryName = $category->name();
			if(empty($categoryName))
				continue;
			
			$newCategory = $this->getByName($categoryName);
			if(!$newCategory) {
				$newCategory = new Category($categoryName);
				$newCategory->id = $this->set($newCategory);
			}
			
			$newCategories [$newCategory->id] = $newCategory;
		}
		
		// calculate elements to remove
		$oldCategories = $this->getCategoriesForPerson($personId);
		foreach ($oldCategories as $oldCategoryId => $oldCategory) {
			if(isset($newCategories[$oldCategoryId])) {
				unset($oldCategories[$oldCategoryId]);
			}
		}
		
		$memberIds = array( $personId );
		// remove elements
		foreach($oldCategories as $oldCategory) {
			$this->deleteMembersFromCategory($oldCategory->id, $memberIds);
		}
		
		// add elements
		foreach($newCategories as $category) {
			$this->addMembersToCategory($category->id, $memberIds);
		}
		return true;
	}
	
	// sets a category (usually add)
	function set($category) {
		global $db;
		global $db_config;
		global $AB;
		if (!$db)
			return false;
		
		if (!is_object($category))
			return false;

		if (empty($category->uid))
			$category->uid = generate_uuid();
		
			// Input validation
		$id = $db->escape(( int ) $category->id);
		$uid = $db->escape($category->uid);
		$name = $db->escape($category->name);
		
		if ($id == 0) {
			// insert
			$mod_ts = (int)$category->modification_ts;

			$sql = "INSERT INTO " . $db_config ['dbtable_cat'] . "  ";
			$sql .= "( uid, name, modification_ts, etag ) VALUES ( $uid, $name, $mod_ts, 2 );";
			$db->execute($sql);
			$insertid = $db->insertId();
			
			if ($insertid == -1) {
				msg("DB error on set: " . $db->lasterror(), -1);
				return false;
			}
			$id = $insertid;
		} else {
			// update
			$mod_ts = time();
			
			$sql = "UPDATE " . $db_config ['dbtable_cat'] . " SET ";
			
			$sql .= "uid=$uid, name=$name ";
			$sql .= "modification_ts=$mod_ts, ";
			$sql .= "etag=etag+1, ";
			$sql .= "WHERE id=$id";
			
			$result = $db->execute($sql);
			if (!$result) {
				msg("DB error on set: " . $db->lasterror(), -1);
				return false;
			}
		}
		$AB->update_ctag();
		
		return $id;
	}

	function delete($categoryId) {
		global $db;
		global $db_config;
		global $AB;
		if (!$db)
			return false;

		if (is_string($categoryId)) {
			$category = $this->get($categoryId);
			if (!$category)
				return false;
			$categoryId = $category->id;
		}
		
		// quote db specific characters
		$categoryId = $db->escape(( int ) $categoryId);
		
		$sql = "DELETE FROM " . $db_config ['dbtable_cat'] . " WHERE id=$categoryId";
		$result = $db->execute($sql);
		if (!$result) {
			msg("DB error delete: " . $db->lasterror(), -1);
			return false;
		}
		
		$sql = "DELETE FROM " . $db_config ['dbtable_catmap'] . " WHERE category_id=$categoryId";
		$result = $db->execute($sql);
		if (!$result) {
			msg("DB error on delete: " . $db->lasterror(), -1);
			return false;
		}
		$AB->update_ctag();
		
		return true;
	}

	function deleteByName($categoryName) {
		$category = $this->getByName($categoryName);
		if (!$category)
			return false;
		return $this->delete($category->id);
	}
	
	function update_etag($categoryId) {
		global $db;
		global $db_config;
		if (!$db)
			return false;

		if (is_string($categoryId)) {
			$category = $this->get($categoryId);
			if (!$category)
				return false;
			$categoryId = $category->id;
		}
		$categoryId = $db->escape(( int ) $categoryId);
		$mod_ts = time();
			
		// update
		$sql = "UPDATE " . $db_config ['dbtable_cat'] . " SET ";
		$sql .= "modification_ts=$mod_ts, ";
		$sql .= "etag=etag+1 ";
		$sql .= "WHERE id=$categoryId";
			
		$result = $db->execute($sql);
		if (!$result) {
			msg("DB error on update_etag: " . $db->lasterror(), -1);
			return false;
		}
		
		return true;		
	}

	function sort($categories) {
		global $lang;

		// $categories is an array of category
		$sorted_names = array ();
		$sorted = array ();
		
		if (!is_array($categories))
			return $categories;
		
		$new_cat = array();
		foreach($categories as $category) {
			$new_cat[$category->id] = $category;
		}
			
		// load sort rules
		$sort_rules_from = explode(',', $lang ['sort_rules_from']);
		$sort_rules_to = explode(',', $lang ['sort_rules_to']);
		
		foreach ( $categories as $cat ) {
			$sorted_names [$cat->id] = str_replace($sort_rules_from, $sort_rules_to, strtoupper($cat->name()));
		}
		asort($sorted_names);
		
		foreach ( $sorted_names as $key => $value )
			$sorted [$key] = $new_cat [$key];
		
		return $sorted;
	}
}



?>