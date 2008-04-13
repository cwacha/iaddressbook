<?php
/**
 * AddressBook database functions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Clemens Wacha <clemens.wacha@gmx.net>
 */

    if(!defined('AB_CONF')) define('AB_CONF',AB_INC.'conf/');
    require_once(AB_CONF.'defaults.php');

    define('ADODB_ASSOC_CASE', 1); # use uppercase field names for ADODB_FETCH_ASSOC

    if(!defined('AB_INC')) define('AB_INC',realpath(dirname(__FILE__).'/../').'/');
    require_once(AB_INC."functions/adodb/adodb.inc.php");
    require_once(AB_INC."functions/common.php");


function db_init($config = NULL) {
    global $conf;
    global $db_config;
    global $db;
    
    if($config) {
        $db_config['dbtype'] = $config['dbtype'];
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
    } else  {
        // defaults
        $db_config['dbtype'] = $conf['dbtype'];
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
    }
    
    $db = false;
}

function db_open() {
    global $db_config;
    global $db;
    global $conf;

    $db = NewADOConnection($db_config['dbtype']);
    
    if($db === false or empty($conf['dbname'])) {
        msg("Cannot connect to database: Error in database configuration (check config.php).", -1);
        return;
    }
    
    if($conf['debug_db']) $db->debug = true;
        
    if(!$db->Connect($db_config['dbserver'], $db_config['dbuser'], $db_config['dbpass'], $db_config['dbname'])) {
      msg("Unable to connect to database: Connection error to server '".$db_config['dbserver']."' with user '".$db_config['dbuser']."'", -1);
      return;
    }
    
    if($db_config['dbtype'] == 'mysql') {
        $sql = "SET NAMES 'utf8'";
        $result = $db->Execute($sql);
    }
    $db->SetFetchMode(ADODB_FETCH_ASSOC);
}

function db_close() {
    if($db) $db->Close();
    $db = false;
}

?>