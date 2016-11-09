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

	function __construct() {
		$this->dbtype = 'sqlite3';
		$this->connection = NULL;
		$this->initialized = false;
		//$this->debug = true;		
	}

	function DBConnector_sqlite3() {
		$this->__construct();
	}
	
	// setup system so that we can start using the DB connection
	function init($server, $dbname, $user, $pass) {
		// only dbname will be used as the filename of the DB
		parent::init($server, $dbname, $user, $pass);

		if(!class_exists('SQLite3')) {
			throw new Exception("This PHP installation does not support SQLite 3");
		}
	}
	
	// clean up and close DB connection
	function destroy() {
		if ($this->initialized && !empty($this->connection)) {
			$this->connection->close();
			$this->initialized = false;
		}
	}
	
	// returns true if DB could be opened, false otherwise
	function open() {
		if (!$this->initialized) {
			try {
				$this->connection = new SQLite3($this->dbname);
				$this->connection->busyTimeout(30000);
			} catch ( Exception $e ) {
				$this->logmsg('Failed to open DB: "' . $this->dbname . '": ' . $e->getMessage(), -1);
				return false;
			}
			
			$this->initialized = true;
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
	
	// return the insert ID of the last insert statement, -1 if insert ID cannot be queried
	function insertId() {
		if (!$this->open())
			return -1;
		
		$insertId = $this->connection->lastInsertRowID();
		return $insertId;
	}
	
	// returns true if execution of sql statement went fine
	function execute($sql, $report_errors = true) {
		if (!$this->open())
			return false;
		
		if ($this->debug)
			$this->logmsg("DB execute: $sql");
		
		try {
			@$ret = $this->connection->exec($sql);
		} catch ( Exception $e ) {
			if($report_errors)
				$this->logmsg('Failed to execute SQL statement: "' . $sql . '": ' . $e->getMessage(), -1);
			return false;
		}
		if($ret === false) {
			if($report_errors)
				$this->logmsg('Failed to execute SQL statement: "' . $sql . '": ' . $this->lasterror(), -1);
			return false;
		}
		return true;
	}
	
	// returns error string of last DB operation, empty string if no error occurred
	function lasterror() {
		if (!$this->open())
			return '';
		
		return $this->connection->lastErrorMsg();
	}
}

?>