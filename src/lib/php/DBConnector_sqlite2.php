<?php

/**
 * iAddressBook sqlite database functions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Clemens Wacha <clemens.wacha@gmx.net>
 */

if(!defined('AB_BASEDIR')) define('AB_BASEDIR',realpath(dirname(__FILE__).'/../../'));

class DBConnector_sqlite2 extends DBConnector {

	var $connection;
	var $initialized;
	
	function __construct() {
		$this->dbtype = 'sqlite2';
		$this->connection = NULL;
		$this->initialized = false;
	}

	function DBConnector_sqlite2() {
		$this->__construct();
	}

	// setup system so that we can start using the DB connection
	function init($server, $dbname, $user, $pass) {
		// only dbname will be used as the filename of the DB
		parent::init($server, $dbname, $user, $pass);
		
		if(!function_exists('sqlite_open')) {
			throw new Exception("This PHP installation does not support native SQLite 2 (using sqlite_*).");
		}
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
			$errormsg = '';
			$this->connection = sqlite_open($this->dbname, 0666, $errormsg);
			if(!is_resource($this->connection)) {
				$this->logmsg("Failed to open DB: '" . $this->dbname . "': " . $errormsg, -1);
				return false;
			}
			$this->initialized = true;
		}
		return true;
	}
	
	// returns array of row with first result from SQL select, NULL otherwise 
	function selectOne($sql) {
		if(!$this->open())
			return NULL;
		
		if($this->debug)
			$this->logmsg("DB selectOne: $sql");
		
		$rst = sqlite_query($this->connection, $sql);
		if (!$rst) {
			$this->logmsg("DB error during selectOne: " . $this->lasterror());
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
			$this->logmsg("DB error during selectAll: " . $this->lasterror());
		}

		return $results;
	}
	
	// return the insert ID of the last insert statement
	function insertId() {
		if(!$this->open())
			return -1;
				
		$insertId = sqlite_last_insert_rowid($this->connection);
		return $insertId;
	}
	
	// returns true if execution of sql statement went fine
	function execute($sql, $report_errors = true) {
		if(!$this->open())
			return false;
				
		if($this->debug)
			$this->logmsg("DB execute: $sql");
		
		$ret = sqlite_exec($this->connection, $sql);
		if($ret === false) {
			if($report_errors)
				$this->logmsg('Failed to execute SQL statement: "' . $sql . '": ' . $this->lasterror(), -1);
			return false;
		}
		
		return true;
	}

	// returns error string of last DB operation, empty string if no error occurred
	function lasterror() {
		if(!is_resource($this->connection))
			return 'DB not open (' .$this->dbname . ')';

		$errCode = sqlite_last_error($this->connection);
		if($errCode != 0)
			return sqlite_error_string($errCode);

		return '';
	}
}
	
?>