<?php

/**
 * iAddressBook sqlite database functions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Clemens Wacha <clemens.wacha@gmx.net>
 */

if(!defined('AB_BASEDIR')) define('AB_BASEDIR',realpath(dirname(__FILE__).'/../../'));
require_once(AB_BASEDIR.'/lib/php/include.php');


class DBConnector_sqlite extends DBConnector {

	var $connection;
	var $initialized;
	
	function DBConnector_sqlite() {
		$this->dbtype = 'sqlite';
		$this->connection = NULL;
		$this->initialized = false;
	}

	// setup system so that we can start using the DB connection
	function init($url, $database, $user, $pass) {
		parent::init($url, $database, $user, $pass);
	}
	
	// clean up and close DB connection
	function destroy() {
		if($this->initialized && !empty($this->connection)) {
			sqlite_close($this->connection);
			$this->initialized = false;
		}
	}
	
	// returns true if DB could be opened, false otherwise
	function open() {
		if(!$this->initialized) {
			$this->connection = sqlite_open($this->url);
			$this->initialized = true;
			return true;
		}
	}
	
	// returns array of row with first result from SQL select, NULL otherwise 
	function selectOne($sql) {
		if(!$this->open())
			return NULL;
		
		if($this->debug)
			$this->logmsg("DB selectOne: $sql");
		
		$rst = sqlite_query($this->connection, $sql);
		if (!$rst) {
			msg("DB error during selectOne: " . $this->lasterror());
			return NULL;
		}
		
		$result = sqlite_fetch_array($rst, SQLITE_ASSOC);
		return $result;
	}
		
		// returns array of row arrays with all results from SQL select, empty array otherwise
	function selectAll($sql) {
		if(!$this->open())
			return array();
				
		if($this->debug)
			$this->logmsg("DB selectAll: $sql");

		$results = array ();
		
		$rst = sqlite_query($this->connection, $sql);
		if ($rst) {
			while ( $row = sqlite_fetch_array($rst, SQLITE_ASSOC) ) {
				$results [] = $row;
			}
		} else {
			msg("DB error during selectAll: " . $this->lasterror());
		}

		return $results;
	}
	
	// return true on success
	function insert($sql) {
		return $this->execute($sql);
	}
	
	// return the insert ID of the last insert statement
	function insertId() {
		if(!$this->open())
			return -1;
				
		$insertId = sqlite_last_insert_rowid($this->connection);
		return $insertId;
	}
	
	// return true on success
	function update($sql) {
		return $this->execute($sql);
	}
	
	// return true on success
	function delete($sql) {
		return $this->execute($sql);
	}
	
	// returns true if execution of sql statement went fine
	function execute($sql) {
		if(!$this->open())
			return false;
				
		if($this->debug)
			$this->logmsg("DB execute: $sql");
		
		return sqlite_exec($this->connection, $sql);
	}

	// returns error string of last DB operation, empty string if no error occurred
	function lasterror() {
		$errCode = sqlite_last_error($this->connection);
		if($errCode != 0)
			return sqlite_error_string($errCode);
		return '';
	}
}
	
?>