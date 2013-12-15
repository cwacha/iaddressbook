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
	var $name;

	function Category($name = '') {
		$this->id = 0;
		$this->name = $name;
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
		$this->name = htmlspecialchars($this->name);
	}
}
class Categories {

	function Categories($config = NULL) {
	}

	function row2category($row) {
		global $lang;
		global $db_config;
		$prefix = '';
		
		$category = new Category();
		if (empty($row))
			return $category;
		
		if (is_object($row))
			$row = get_object_vars($row);
		
		$row = array_change_key_case($row, CASE_UPPER);
		
		if (!array_key_exists('ID', $row)) {
			$prefix = strtoupper($db_config ['dbtable_cat']) . '.';
		}
		
		$category->id = ( int ) $row [$prefix . 'ID'];
		$category->name = $row [$prefix . 'NAME'];
		
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
		
		$all_category = new Category(' __all__');
		$categories [] = $all_category;
		
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
		$searchstring = $db->escape(( int ) $person_id);
		$internal = $db->escape(' __%__');
		
		$sql = "SELECT " . $db_config ['dbtable_cat'] . ".* FROM " . $db_config ['dbtable_cat'] . ", " . $db_config ['dbtable_catmap'] . " WHERE (";
		$sql .= $db_config ['dbtable_cat'] . ".id = " . $db_config ['dbtable_catmap'] . ".category_id ) AND ";
		$sql .= "( person_id = $searchstring ) AND ( name NOT LIKE " . $internal . " ) ";
		
		$sql .= " LIMIT $limit";
		
		$result = $db->selectAll($sql);
		
		foreach ( $result as $row ) {
			$category = $this->row2category($row);
			$categories [] = $category;
		}
		
		return $categories;
	}
	
	// adds person to category (and creates it if does not yet exist)
	function addPersonToCategory($personId, $categoryName) {
		global $db;
		global $db_config;
		if (!$db)
			return false;
		
		$category = $this->get($categoryName);
		if (!$category) {
			$category = new Category($categoryName);
			$category->id = $this->set($category);
		}
		
		if ($this->personIsMemberOf($personId, $categoryName))
			return true;
			
			// Input validation
		$personId = $db->escape(( int ) $personId);
		$categoryId = $db->escape(( int ) $category->id);
		
		// insert
		$sql = "INSERT INTO " . $db_config ['dbtable_catmap'] . "  ";
		$sql .= "( category_id, person_id ) VALUES ( ";
		$sql .= " $categoryId, $personId );";
		
		$result = $db->insert($sql);
		
		if ($result)
			return true;
		
		msg("DB error on addPersonToCategory: " . $db->lasterror(), -1);
		return false;
	}
	
	// deletes person from category
	function deletePersonFromCategory($personId, $categoryName) {
		global $db;
		global $db_config;
		if (!$db)
			return;
		
		if (!$this->personIsMemberOf($personId, $categoryName))
			return true;
		
		$category = $this->get($categoryName);
		if (!$category)
			return false;
		
		$categoryId = $db->escape(( int ) $category->id);
		$personId = $db->escape(( int ) $personId);
		
		$sql = "DELETE FROM " . $db_config ['dbtable_catmap'] . " WHERE ";
		$sql .= "category_id=$categoryId AND person_id=$personId;";
		$result = $db->delete($sql);
		
		if ($result)
			return true;
		
		msg("DB error on deletePersonFromCategory: " . $db->lasterror(), -1);
		return false;
	}

	function deletePersonFromAllCategories($personId) {
		global $db;
		global $db_config;
		if (!$db)
			return;
		
		$personId = $db->escape(( int ) $personId);
		
		$sql = "DELETE FROM " . $db_config ['dbtable_catmap'] . " WHERE ";
		$sql .= "person_id=$personId;";
		$result = $db->delete($sql);
		
		if ($result)
			return true;
		
		msg("DB error on deletePersonFromAllCategories: " . $db->lasterror(), -1);
		return false;
	}

	function personIsMemberOf($personId, $categoryName) {
		global $db;
		global $db_config;
		if (!$db)
			return false;
		
		$category = $this->get($categoryName);
		if (!$category)
			return false;
		
		$personId = $db->escape(( int ) $personId);
		$categoryId = $db->escape(( int ) $category->id);
		
		$sql = "SELECT * FROM " . $db_config ['dbtable_catmap'] . " WHERE ";
		$sql .= "(category_id = $categoryId) AND ";
		$sql .= "(person_id = $personId) ";
		
		$row = $db->selectOne($sql);
		if (row)
			return true;
		
		return false;
	}

	function get($categoryName) {
		global $db;
		global $db_config;
		if (!$db)
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

	function getById($categoryId) {
		global $db;
		global $db_config;
		if (!$db)
			return false;
			
			// quote db specific characters
		$categoryId = $db->escape($categoryId);
		
		$sql = "SELECT * FROM " . $db_config ['dbtable_cat'] . " WHERE id=$categoryId LIMIT 1";
		$row = $db->selectOne($sql);
		
		if (!$row)
			return false;
		
		$category = $this->row2category($row);
		return $category;
	}
	
	// sets a category (usually add)
	function set($category) {
		global $db;
		global $db_config;
		if (!$db)
			return false;
		
		if (!is_object($category))
			return false;
			
			// Input validation
		$id = $db->escape(( int ) $category->id);
		$name = $db->escape($category->name);
		
		if ($id == 0) {
			// insert
			$sql = "INSERT INTO " . $db_config ['dbtable_cat'] . "  ";
			$sql .= "( name ) VALUES ( $name );";
			$db->insert($sql);
			$insertid = $db->insertId();
			
			if ($insertid == -1) {
				msg("DB error on set: " . $db->lasterror(), -1);
				return false;
			}
			$id = $insertid;
		} else {
			// update
			$sql = "UPDATE " . $db_config ['dbtable_cat'] . " SET ";
			
			$sql .= "name=$name ";
			$sql .= "WHERE id=$id";
			
			$result = $db->update($sql);
			if (!$result) {
				msg("DB error on set: " . $db->lasterror(), -1);
				return false;
			}
		}
		
		return $id;
	}

	function deleteById($categoryId) {
		global $db;
		global $db_config;
		if (!$db)
			return false;

		// quote db specific characters
		$categoryId = $db->escape(( int ) $categoryId);
		
		$sql = "DELETE FROM " . $db_config ['dbtable_cat'] . " WHERE id=$categoryId";
		$result = $db->delete($sql);
		if (!$result) {
			msg("DB error delete: " . $db->lasterror(), -1);
			return false;
		}
		
		$sql = "DELETE FROM " . $db_config ['dbtable_catmap'] . " WHERE category_id=$categoryId";
		$result = $db->delete($sql);
		if (!$result) {
			msg("DB error on delete: " . $db->lasterror(), -1);
			return false;
		}
		
		return true;
	}

	function delete($categoryName) {
		$category = $this->get($categoryName);
		if (!$category)
			return false;
		return $this->deleteById($category->id);
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