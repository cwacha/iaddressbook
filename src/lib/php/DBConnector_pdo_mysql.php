<?php

/**
 * iAddressBook sqlite database functions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Clemens Wacha <clemens.wacha@gmx.net>
 */
if (!defined('AB_BASEDIR'))
	define('AB_BASEDIR', realpath(dirname(__FILE__) . '/../../'));


class DBConnector_pdo_mysql extends DBConnector {
	var $connection;
	var $initialized;

	function __construct() {
		$this->dbtype = 'pdo_mysql';
		$this->connection = NULL;
		$this->initialized = false;
		//$this->debug = true;
	}

	function DBConnector_pdo_mysql() {
		$this->__construct();
	}
	
	// setup system so that we can start using the DB connection
	function init($server, $dbname, $user, $pass) {
		// only dbname will be used as the filename of the DB
		parent::init($server, $dbname, $user, $pass);

		try {
			if(!class_exists('PDO'))
				throw new Exception();

			$drivers = PDO::getAvailableDrivers();
			if(array_search('mysql', $drivers) === FALSE)
				throw new Exception();
		} catch (Exception $e) {
			throw new Exception("This PHP installation does not support PDO MySQL.");
		}
	}
	
	// clean up and close DB connection
	function destroy() {
		if ($this->initialized && !empty($this->connection)) {
			$this->connection = NULL;
			$this->initialized = false;
		}
	}
	
	// returns true if DB could be opened, false otherwise
	function open() {
		if (!$this->initialized) {
			try {
				$this->connection = new PDO('mysql:host='.$this->server.';dbname='.$this->dbname, $this->user, $this->pass);
				$this->connection->setAttribute(PDO::ATTR_TIMEOUT, 30);
				$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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
			$rst = $this->connection->query($sql);
			if (!$rst) {
				msg("DB error during selectOne: " . $this->lasterror());
				return NULL;
			}
			
			$result = $rst->fetch(PDO::FETCH_ASSOC);
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
			while ( $row = $rst->fetch(PDO::FETCH_ASSOC) ) {
				$results [] = $row;
			}
		} catch ( Exception $e ) {
			$this->logmsg('Failed executing SQL statement: "' . $sql . '": ' . $e->getMessage(), -1);
		}
			
		return $results;
	}
	
	// return the insert ID of the last insert statement, -1 if insert ID cannot be queried
	function insertId() {
		if (!$this->open())
			return -1;
		
		$insertId = intval($this->connection->lastInsertId());
		return $insertId;
	}
	
	// returns true if execution of sql statement went fine
	function execute($sql, $report_errors = true) {
		if (!$this->open())
			return false;
		
		if ($this->debug)
			$this->logmsg("DB execute: $sql");
		
		try {
			$ret = $this->connection->exec($sql);
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
		
		$errmsg = $this->connection->errorInfo();
		if(is_array($errmsg))
			$errmsg = $errmsg[2];
		return $errmsg != NULL ? $errmsg : '';
	}
}

?>