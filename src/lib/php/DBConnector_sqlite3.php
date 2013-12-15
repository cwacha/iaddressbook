<?php

/**
 * iAddressBook sqlite database functions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Clemens Wacha <clemens.wacha@gmx.net>
 */
if (!defined('AB_BASEDIR'))
	define('AB_BASEDIR', realpath(dirname(__FILE__) . '/../../'));

require_once (AB_BASEDIR . '/lib/php/include.php');

class DBConnector_sqlite3 extends DBConnector {
	var $connection;
	var $initialized;

	function DBConnector_sqlite3() {
		$this->dbtype = 'sqlite3';
		$this->connection = NULL;
		$this->initialized = false;
		//$this->debug = true;
	}
	
	// setup system so that we can start using the DB connection
	function init($url, $database, $user, $pass) {
		parent::init($url, $database, $user, $pass);
	}
	
	// clean up and close DB connection
	function destroy() {
		if ($initialized && !empty($connection)) {
			$connection->close();
			$initialized = false;
		}
	}
	
	// returns true if DB could be opened, false otherwise
	function open() {
		if (!$initialized) {
			try {
				$this->connection = new SQLite3($this->url);
			} catch ( Exception $e ) {
				$this->logmsg('Cannot open db file: "' . $this->url . '": ' . $e->getMessage(), -1);
				return false;
			}
			
			$initialized = true;
		}
		return true;
	}
	
	// returns array of row with first result from SQL select, NULL otherwise
	function selectOne($sql) {
		$result = NULL;
		
		if (!$this->open())
			return $result;
		
		if ($this->debug)
			$this->logmsg("DB selectOne: $sql");

		try {
			$result = $this->connection->querySingle($sql, true);
		} catch ( Exception $e ) {
			$this->logmsg('Failed executing SQL statement: "' . $sql . '": ' . $e->getMessage(), -1);
			return NULL;
		}

		return $result;
	}
	
	// returns array of row arrays with all results from SQL select, empty array otherwise
	function selectAll($sql) {
		$results = array ();
		
		if (!$this->open())
			return $results;
		
		if ($this->debug)
			$this->logmsg("DB selectAll: $sql");
		
		try {
			$rst = $this->connection->query($sql);
			if(!$rst)
				return $results;
			
			while ( $row = $rst->fetchArray(SQLITE3_ASSOC) ) {
				$results [] = $row;
			}
		} catch ( Exception $e ) {
			$this->logmsg('Failed executing SQL statement: "' . $sql . '": ' . $e->getMessage(), -1);
			return NULL;
		}
		
		return $results;
	}
	
	// return true on success
	function insert($sql) {
		return $this->execute($sql);
	}
	
	// return the insert ID of the last insert statement, -1 if insert ID cannot be queried
	function insertId() {
		if (!$this->open())
			return -1;
		
		$insertId = $this->connection->lastInsertRowID();
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
		if (!$this->open())
			return false;
		
		if ($this->debug)
			$this->logmsg("DB execute: $sql");
		
		$result = false;
		try {
			$result = $this->connection->exec($sql);
		} catch ( Exception $e ) {
			$this->logmsg('Failed executing SQL statement: "' . $sql . '": ' . $e->getMessage(), -1);
			return false;
		}
		
		return $result;
	}
	
	// returns error string of last DB operation, empty string if no error occurred
	function lasterror() {
		if (!$this->open())
			return '';
		
		return $this->connection->lastErrorMsg();
	}
}

?>