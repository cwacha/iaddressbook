<?php
    /**
     * iAddressBook database functions
     *
     * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
     * @author     Clemens Wacha <clemens.wacha@gmx.net>
     */

    if(!defined('AB_BASEDIR')) define('AB_BASEDIR',realpath(dirname(__FILE__).'/../../'));
    require_once(AB_BASEDIR.'/lib/php/include.php');

    define('ADODB_ASSOC_CASE', 1); # use uppercase field names for ADODB_FETCH_ASSOC
    require_once(AB_BASEDIR.'/lib/php/adodb5/adodb.inc.php');
    require_once(AB_BASEDIR.'/lib/php/common.php');

function db_msg($msg, $newline=true) {
    msg($msg, -1);
}

function db_init($config = NULL) {
    global $conf;
    global $db_config;
    global $db;
    global $ADODB_OUTP;
    $ADODB_OUTP = 'db_msg';
    
    if($config) {
        $db_config['dbtype'] = strtolower($config['dbtype']);
        $db_config['dbname'] = $config['dbname'];
        $db_config['dbserver'] = $config['dbserver'];
        $db_config['dbuser'] = $config['dbuser'];
        $db_config['dbpass'] = $config['dbpass'];
        $db_config['dbtable_ab'] = $config['dbtable_ab'];
        $db_config['dbtable_cat'] = $config['dbtable_cat'];
        $db_config['dbtable_catmap'] = $config['dbtable_catmap'];
        $db_config['dbtable_truth'] = $config['dbtable_truth'];
        $db_config['dbtable_sync'] = $config['dbtable_sync'];
        $db_config['dbtable_action'] = $config['dbtable_action'];
        $db_config['dbtable_users'] = $config['dbtable_users'];
        $db_config['dbdebug'] = $config['debug_db'];
    } else  {
        // defaults
        $db_config['dbtype'] = strtolower($conf['dbtype']);
        $db_config['dbname'] = $conf['dbname'];
        $db_config['dbserver'] = $conf['dbserver'];
        $db_config['dbuser'] = $conf['dbuser'];
        $db_config['dbpass'] = $conf['dbpass'];
        $db_config['dbtable_ab'] = $conf['dbtable_ab'];
        $db_config['dbtable_cat'] = $conf['dbtable_cat'];
        $db_config['dbtable_catmap'] = $conf['dbtable_catmap'];
        $db_config['dbtable_truth'] = $conf['dbtable_truth'];
        $db_config['dbtable_sync'] = $conf['dbtable_sync'];
        $db_config['dbtable_action'] = $conf['dbtable_action'];
        $db_config['dbtable_users'] = $conf['dbtable_users'];
        $db_config['dbdebug'] = $conf['debug_db'];
    }
    
    $db = false;
}

function db_open() {
    global $db_config;
    global $db;

    $db = NewADOConnection($db_config['dbtype']);
        
    if($db === false or empty($db_config['dbname'])) {
        msg("Cannot connect to database: Error in database configuration.", -1);
        $db = false;
        return false;
    }
    
    if($db_config['dbdebug']) $db->debug = true;
    
    if($db_config['dbtype'] == 'sqlite') {
        $db_config['dbserver'] = AB_STATEDIR.'/'.$db_config['dbserver'];
    }
    
    if(!$db->Connect($db_config['dbserver'], $db_config['dbuser'], $db_config['dbpass'], $db_config['dbname'])) {
        msg("Cannot connect to database: Connection error to server or db file '".$db_config['dbserver']."' with user '".$db_config['dbuser']."'", -1);
        $db = false;
        return false;
    }
    
    if($db_config['dbtype'] == 'mysql') {
        $sql = "SET NAMES 'utf8'";
        $result = $db->Execute($sql);
    }
    $db->SetFetchMode(ADODB_FETCH_ASSOC);
    
    return true;
}

function db_close() {
    global $db;
    
    if($db) $db->Close();
    $db = false;
}

function db_createtables() {
    global $db;
    global $db_config;
    
    if(!$db) return false;
    
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
        if(empty($sql)) continue;
        $result = $db->Execute($sql);
        if(!$result) {
            $errors++;
            msg("Error creating db: ". $db->ErrorMsg(), -1);
        }
    }
    
    if($errors > 0) {
        msg("$errors error(s) during setup of the database", -1);
        return false;
    }

    return true;
}

?>