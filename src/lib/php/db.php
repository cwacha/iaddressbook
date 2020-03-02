<?php
    /**
     * iAddressBook database functions
     *
     * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
     * @author     Clemens Wacha <clemens.wacha@gmx.net>
     */

    if(!defined('AB_BASEDIR')) define('AB_BASEDIR',realpath(dirname(__FILE__).'/../../'));
    require_once(AB_BASEDIR.'/lib/php/DBConnector.php');
    require_once(AB_BASEDIR.'/lib/php/DBConnector_pdo_sqlite3.php');
    require_once(AB_BASEDIR.'/lib/php/DBConnector_pdo_mysql.php');
    require_once(AB_BASEDIR.'/lib/php/DBConnector_sqlite2.php');
    require_once(AB_BASEDIR.'/lib/php/DBConnector_sqlite3.php');
    
function db_msg($msg, $newline=true) {
    msg($msg, -1);
}

function db_init($config = NULL) {
	global $conf;
	global $db_config;
	global $db;
	
	if (empty($config)) {
		$config = $conf;
	}
	// defaults
	$db_config['dbtype'] = strtolower( $config ['dbtype'] );
	$db_config['dbname'] = $config['dbname'];
	$db_config['dbserver'] = $config['dbserver'];
	$db_config['dbuser'] = $config['dbuser'];
	$db_config['dbpass'] = $config['dbpass'];
	$db_config['dbtable_abs'] = $config['dbtable_abs'];
	$db_config['dbtable_ab'] = $config['dbtable_ab'];
	$db_config['dbtable_cat'] = $config['dbtable_cat'];
	$db_config['dbtable_catmap'] = $config['dbtable_catmap'];
	$db_config['dbdebug'] = $config['debug_db'];
	    
    $db = false;
}

function db_open() {
    global $db_config;
    global $db;
    
    if($db_config['dbtype'] == 'sqlite2') {
    	$db_config['dbname'] = AB_STATEDIR.'/'.$db_config['dbname'];
    	$db = new DBConnector_sqlite2();
    }
    if($db_config['dbtype'] == 'sqlite3') {
    	$db_config['dbname'] = AB_STATEDIR.'/'.$db_config['dbname'];
    	$db = new DBConnector_sqlite3();
    }
    if($db_config['dbtype'] == 'pdo_sqlite3') {
    	$db_config['dbname'] = AB_STATEDIR.'/'.$db_config['dbname'];
    	$db = new DBConnector_pdo_sqlite3();
    }
    if($db_config['dbtype'] == 'pdo_mysql') {
    	$db = new DBConnector_pdo_mysql();
    }

    if($db === false or empty($db_config['dbname'])) {
        msg("Cannot connect to database: Error in database configuration! dbtype=" . $db_config['dbtype'], -1);
        $db = false;
        return false;
    }
        
    try {
	    $db->init($db_config['dbserver'], $db_config['dbname'], $db_config['dbuser'], $db_config['dbpass']);
	    if($db_config['dbdebug']) $db->debug = true;    
	        
	    if($db_config['dbtype'] == 'pdo_mysql') {
	        @$db->execute("SET NAMES 'utf8'");
	    }
    } catch (Exception $e) {
    	msg("Cannot setup DB connection! Exception: " . $e->getMessage(), -1);
    	return false;
    }
    
    // the tables will be created from install.php (its not required to repeat on every call)
    //db_createtables();
    
    return true;
}

function db_close() {
    global $db;
    
    if($db) $db->destroy();
    $db = false;
}

function db_createtables() {
    global $db;
    global $db_config;
    
    if(!$db)
    	return false;
    
    $sql = "SELECT id from " . $db_config['dbtable_abs'] . " LIMIT 1";
    if($db->execute($sql, false) === true)
    	return true;
    
    $filename = AB_SQLDIR.'/'.$db_config['dbtype'].'.sql';
    $queries = file_get_contents($filename);
    if($queries === false) {
        msg("Error creating db: Cannot open file $filename", -1);
        return false;
    }

    $queries = explode(";", $queries);
    
    $errors = 0;
    
    foreach($queries as $sql) {
        $sql = trim($sql);
        if(empty($sql))
        	continue;
        $result = $db->execute($sql);
        if(!$result) {
            $errors++;
            //msg("Error creating db: ". $db->lasterror(), -1);
        }
    }
    
    if($errors > 0) {
        msg("$errors error(s) during setup of the database", -1);
        return false;
    }

    return true;
}

?>