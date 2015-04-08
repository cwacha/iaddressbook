<?php

/**
 * iAddressBook database functions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Clemens Wacha <clemens.wacha@gmx.net>
 */

if(!defined('AB_BASEDIR')) define('AB_BASEDIR',realpath(dirname(__FILE__).'/../../'));
require_once(AB_BASEDIR.'/lib/php/include.php');


class DBConnector {
	
	var $dbtype;
	var $dbname;
	var $server;
	var $user;
	var $pass;
	var $debug;

	function DBConnector() {
		$this->dbtype = 'none';
		$this->debug = false;
	}
	
	// setup system so that we can start using the DB connection
	function init($server, $dbname, $user, $pass) {
		$this->server = $server;
		$this->dbname = $dbname;
		$this->user = $user;
		$this->pass = $pass;
	}

	// clean up and close DB connection
	function destroy() {}

	function logmsg($message, $level=0) {
		msg($message, $level);
	}
	
	// returns array of row with first result from SQL select, NULL otherwise 
	function selectOne($sql) {
		return NULL;
	}
	
	// returns array of row arrays with all results from SQL select, empty array otherwise
	function selectAll($sql) {
		$results = array();
		return $results;
	}

	// return the insert ID of the last insert statement
	function insertId() {
		return -1;
	}
		
	// returns true if execution of sql statement went fine
	// set second parameter to false to suppress error messages
	function execute($sql, $report_errors = true) {
		return false;
	}
	
	// returns string with all critical DB characters escaped
	function escape($stringToEscape) {
		return "'" . preg_replace("[']", "''", $stringToEscape) . "'";
	}
	
	// returns error string of last DB operation, empty string if no error occurred
	function lasterror() {
		return '';
	}
}


?>