<?php
/**
* AddressBook xmlrpc automation
*
* @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
* @author     Clemens Wacha <clemens.wacha@gmx.net>
*/

if(!defined('AB_INC')) define('AB_INC',realpath(dirname(__FILE__)).'/');
require_once(AB_INC.'functions/init.php');
require_once(AB_INC.'functions/db.php');
require_once(AB_INC.'functions/XML/Server.php');
require_once(AB_INC.'functions/module_vcard.php');
require_once(AB_INC.'functions/common.php');


global $conf;

if($conf['xmlrpc_enable'] == false) exit();

/*
 * Declare the functions, etc.
 */

function convert_iso8601($date) {
	$y  = intval(substr($date, 0, 4));
	$m  = intval(substr($date, 4, 2));
	$d  = intval(substr($date, 6, 2));
	$hh = intval(substr($date, 9, 2));
	$mm = intval(substr($date, 12, 2));
	$ss = intval(substr($date, 15, 2));
	
	$m = sprintf("%02u", $m);
	$d = sprintf("%02u", $d);
	$hh = sprintf("%02u", $hh);
	$mm = sprintf("%02u", $mm);
	$ss = sprintf("%02u", $ss);
	
	return "$y-$m-$d $hh:$mm:$ss";
}

function normalize_row(&$row, $prefix_db) {
    if(empty($row)) return;
    
    if(is_object($row)) $row = get_object_vars($row);
    
    $row = array_change_key_case($row, CASE_UPPER);
    
    if(!array_key_exists('ID', $row)) {
        $prefix = strtoupper($prefix_db) . '.';
    }
    
    $fields = array('ID', 'SYNCPARTNER_ID', 'REMOTE_ID', 'LOCAL_ID', 'MOD_DATE', 'SYNCACTION', 'VCARD_DATA', 'SIDE');
    
    foreach($fields as $val) $row[$val] = $row[$prefix . $val];
}

function cleanup_sync($syncpartner_id = 0) {
    global $db;
    global $db_config;

    if(!$db) return false;
    
	$sql  = "DELETE FROM ".$db_config['dbtable_sync']." WHERE (syncpartner_id = $syncpartner_id);";

	$result = $db->Execute($sql);
	if(!$result) {
		msg("DB error on cleanup_sync: ". $db->ErrorMsg(), -1);
		return false;
	}

	$sql  = "DELETE FROM ".$db_config['dbtable_action']." WHERE (syncpartner_id = $syncpartner_id);";

	$result = $db->Execute($sql);
	if(!$result) {
		msg("DB error on cleanup_sync: ". $db->ErrorMsg(), -1);
		return false;
	}

    return true;
}

function create_remote_side($change_list, $syncpartner_id = 0) {
    global $db;
    global $db_config;

    if(!$db or !is_array($change_list)) return false;
    
    foreach($change_list as $key => $value) {
        // quote db specific characters
        $syncpartner_id = $db->Quote((int)$syncpartner_id);
        $remote_id      = $db->Quote($value['remote_id']);
        $sync_state     = $db->Quote(0);
        $mod_date       = $db->Quote(convert_iso8601($value['mod_date']));
                
        $sql  = "INSERT INTO ".$db_config['dbtable_sync']." (syncpartner_id, remote_id, sync_state, mod_date) VALUES ( ";
        $sql .= " $syncpartner_id, $remote_id, $sync_state, $mod_date );";
        
        $result = $db->Execute($sql);
        if(!$result) {
            msg("DB error on create_remote_side: ". $db->ErrorMsg(), -1);
            return false;
        }
    }
    return true;
}

function calc_remote_added($syncpartner_id = 0) {
    global $db;
    global $db_config;

    if(!$db) return false;
    
    // quote db specific characters
    $sp_id = $db->Quote((int)$syncpartner_id);
    
    $sql  = "SELECT * FROM ".$db_config['dbtable_sync'];
    $sql .= " LEFT JOIN ".$db_config['dbtable_truth'];
    $sql .= " ON (".$db_config['dbtable_sync'].".remote_id = ".$db_config['dbtable_truth'].".remote_id) AND ( ";
    $sql .= $db_config['dbtable_sync'].".syncpartner_id = ".$db_config['dbtable_truth'].".syncpartner_id) ";
    $sql .= " WHERE (".$db_config['dbtable_sync'].".syncpartner_id = $sp_id) AND ( ";
    $sql .= $db_config['dbtable_truth'].".remote_id is NULL);";
    
    $result = $db->Execute($sql);
    if($result) {
        while ($row = $result->FetchRow()) {
            if($row) {
            	normalize_row($row, $db_config['dbtable_sync']);
            	$local_id = 0;
            	$remote_id = $row['REMOTE_ID'];
            	create_sync_action('add', 'remote', $local_id, $remote_id, '', $syncpartner_id);
            }
        }
    } else {
        //query error
        msg("DB error on remote add: ". $db->ErrorMsg(), -1);
        return false;
    }
    
    return true;
}

function calc_remote_deleted($syncpartner_id = 0) {
    global $db;
    global $db_config;

    if(!$db) return false;
    
    // quote db specific characters
    $sp_id = $db->Quote((int)$syncpartner_id);
    
    $sql  = "SELECT * FROM ".$db_config['dbtable_truth'];
    $sql .= " LEFT JOIN ".$db_config['dbtable_sync'];
    $sql .= " ON (".$db_config['dbtable_sync'].".remote_id = ".$db_config['dbtable_truth'].".remote_id) AND ( ";
    $sql .= $db_config['dbtable_sync'].".syncpartner_id = ".$db_config['dbtable_truth'].".syncpartner_id) ";
    $sql .= " WHERE (".$db_config['dbtable_sync'].".syncpartner_id = $sp_id) AND ( ";
    $sql .= $db_config['dbtable_sync'].".remote_id is NULL);";
    
    $result = $db->Execute($sql);
    if($result) {
        while ($row = $result->FetchRow()) {
            if($row) {
            	normalize_row($row, $db_config['dbtable_truth']);
            	$local_id = $row['LOCAL_ID'];
            	$remote_id = 0;
            	create_sync_action('delete', 'remote', $local_id, $remote_id, '', $syncpartner_id);
            }
        }
    } else {
        //query error
        msg("DB error on remote delete: ". $db->ErrorMsg(), -1);
        return false;
    }
    
    return true;
}

function calc_remote_changed($syncpartner_id = 0) {
    global $db;
    global $db_config;

    if(!$db) return false;
    
    // quote db specific characters
    $sp_id = $db->Quote((int)$syncpartner_id);
    
    $sql  = "SELECT * FROM ".$db_config['dbtable_sync'].", ".$db_config['dbtable_truth'];
    $sql .= " WHERE (".$db_config['dbtable_sync'].".remote_id = ".$db_config['dbtable_truth'].".remote_id) AND ( ";
    $sql .= $db_config['dbtable_sync'].".syncpartner_id = ".$db_config['dbtable_truth'].".syncpartner_id) AND ( ";
    $sql .= $db_config['dbtable_sync'].".syncpartner_id = $sp_id) AND ( ";
    $sql .= $db_config['dbtable_truth'].".mod_date != ".$db_config['dbtable_sync'].".mod_date);";
    
    $result = $db->Execute($sql);
    if($result) {
        while ($row = $result->FetchRow()) {
            	normalize_row($row, $db_config['dbtable_truth']);
            	$local_id = $row['LOCAL_ID'];
            	$remote_id = $row['REMOTE_ID'];
            	create_sync_action('change', 'remote', $local_id, $remote_id, '', $syncpartner_id);
        }
    } else {
        //query error
        msg("DB error on remote changed: ". $db->ErrorMsg(), -1);
        return false;
    }
    
    return true;
}

function calc_local_added($syncpartner_id = 0) {
    global $db;
    global $db_config;

    if(!$db) return false;
    
    // quote db specific characters
    $sp_id = $db->Quote((int)$syncpartner_id);
    
    $sql  = "SELECT * FROM ".$db_config['dbtable_ab'];
    $sql .= " LEFT JOIN ".$db_config['dbtable_truth'];
    $sql .= " ON (".$db_config['dbtable_ab'].".id = ".$db_config['dbtable_truth'].".local_id) ";
    $sql .= " WHERE ( ".$db_config['dbtable_truth'].".local_id is NULL);";
    
    $result = $db->Execute($sql);
    if($result) {
        while ($row = $result->FetchRow()) {
            if($row) {
            	normalize_row($row, $db_config['dbtable_ab']);
            	$local_id = $row['ID'];
            	$remote_id = 0;
            	create_sync_action('add', 'local', $local_id, $remote_id, '', $syncpartner_id);
            }
        }
    } else {
        //query error
        msg("DB error on remote add: ". $db->ErrorMsg(), -1);
        return false;
    }
    
    return true;
}

function calc_local_deleted($syncpartner_id = 0) {
    global $db;
    global $db_config;

    if(!$db) return false;
    
    // quote db specific characters
    $sp_id = $db->Quote((int)$syncpartner_id);
    
    $sql  = "SELECT * FROM ".$db_config['dbtable_truth'];
    $sql .= " LEFT JOIN ".$db_config['dbtable_ab'];
    $sql .= " ON (".$db_config['dbtable_ab'].".id = ".$db_config['dbtable_truth'].".local_id) ";
    $sql .= " WHERE (".$db_config['dbtable_ab'].".id is NULL);";
    
    $result = $db->Execute($sql);
    if($result) {
        while ($row = $result->FetchRow()) {
            if($row) {
            	normalize_row($row, $db_config['dbtable_truth']);
            	$local_id = 0;
            	$remote_id = $row['REMOTE_ID'];
            	create_sync_action('delete', 'local', $local_id, $remote_id, '', $syncpartner_id);
            }
        }
    } else {
        //query error
        msg("DB error on remote delete: ". $db->ErrorMsg(), -1);
        return false;
    }
    
    return true;
}

function calc_local_changed($syncpartner_id = 0) {
    global $db;
    global $db_config;

    if(!$db) return false;
    
    // quote db specific characters
    $sp_id = $db->Quote((int)$syncpartner_id);
    
    $sql  = "SELECT * FROM ".$db_config['dbtable_ab'].", ".$db_config['dbtable_truth'];
    $sql .= " WHERE (".$db_config['dbtable_ab'].".id = ".$db_config['dbtable_truth'].".local_id) AND ( ";
    $sql .= $db_config['dbtable_truth'].".mod_date != ".$db_config['dbtable_ab'].".modificationdate);";
    
    $result = $db->Execute($sql);
    if($result) {
        while ($row = $result->FetchRow()) {
            	normalize_row($row, $db_config['dbtable_truth']);
            	$local_id = $row['LOCAL_ID'];
            	$remote_id = $row['REMOTE_ID'];
            	create_sync_action('change', 'local', $local_id, $remote_id, '', $syncpartner_id);
        }
    } else {
        //query error
        msg("DB error on remote changed: ". $db->ErrorMsg(), -1);
        return false;
    }
    
    return true;
}

function create_sync_action($action, $side, $local_id, $remote_id, $vcard, $syncpartner_id = 0) {
    global $db;
    global $db_config;
    if(!$db) return false;
    
    $prefix = '';
    $act = 0;
    $s = 0;
        
    $sp_id = $db->Quote((int)$syncpartner_id);
    
    switch($action) {
        case 'add':
            $act = 1;
            break;
        case 'delete':
            $act = 2;
            break;
        case 'change':
            $act = 3;
            break;
        case 'conflict':
            $act = 5;
            break;
        default:
            $act = 0;
    }
    $act = $db->Quote($act);
    
    if($side == 'local') $side = 1;
    else $side = 2;
    
    $local_id = $db->Quote($local_id);
    $remote_id = $db->Quote($remote_id);
    $vcard = $db->Quote($vcard);
    
    $sql  = "INSERT INTO ".$db_config['dbtable_action'];
    $sql .= " (syncpartner_id, side, syncaction, local_id, remote_id, vcard_data) VALUES ( ";
    $sql .= "$sp_id, $side, $act, $local_id, $remote_id, $vcard);";

    $result = $db->Execute($sql);
    if(!$result) {
        msg("DB error on create_sync_action: ". $db->ErrorMsg(), -1);
        return false;
    }
    
    return true;
}

function display_actions($syncpartner_id = 0) {
    global $db;
    global $db_config;

    if(!$db) return false;
    
    // quote db specific characters
    $sp_id = $db->Quote((int)$syncpartner_id);
    
    $sql  = "SELECT * FROM ".$db_config['dbtable_action'];
    $sql .= " WHERE (syncpartner_id = $sp_id);";
    
    $result = $db->Execute($sql);
    if($result) {
        while ($row = $result->FetchRow()) {
            if($row) {
            	normalize_row($row, $db_config['dbtable_action']);
            	msg("side: ".$row['SIDE']. " action: ".$row['SYNCACTION']. " local_id: ". $row['LOCAL_ID']." remote_id: ".$row['REMOTE_ID']);
            }
        }
    } else {
        //query error
        msg("DB error on display_actions: ". $db->ErrorMsg(), -1);
        return false;
    }
    
    return true;
	
}

function start_sync($params) {
	$change_list = array();
	$local_list = array();
	
	$param = $params->getParam(0);
	if(!XML_RPC_Value::isValue($param)) {
		msg("called with wrong number of parameters");
		$val = new XML_RPC_Value(msg_text(), 'string');
		return new XML_RPC_Response($val);
	}
	
	$change_list = XML_RPC_decode($param);
	if(!is_array($change_list)) {
		msg("parameter is not an array");
		$val = new XML_RPC_Value(msg_text(), 'string');
		return new XML_RPC_Response($val);
	}
	
	if(!cleanup_sync()) msg("cleanup_sync failed");
	
	if(!create_remote_side($change_list)) msg("create_remote_side failed");
    
    if(!calc_remote_added()) msg("calc_remote_added failed");
    if(!calc_remote_deleted()) msg("calc_remote_deleted failed");;
    if(!calc_remote_changed()) msg("calc_remote_changed failed");;
    
    if(!calc_local_added()) msg("calc_local_added failed");;
    if(!calc_local_deleted()) msg("calc_local_deleted failed");;
    if(!calc_local_changed()) msg("calc_local_changed failed");;
    
    display_actions();
    msg("done");
	$val = new XML_RPC_Value(msg_text(), 'string');
	return new XML_RPC_Response($val);
    
}

function push_changes($params) {

}

function pull_changes($params) {

}

function finish_sync($params) {

}

function vcard_upload($params) {
    global $AB;
    global $conf;
    global $CAT;
        
    $ret = '';
    $error = 0;
    $imported = 0;

	$param = $params->getParam(0);
	
	if(!XML_RPC_Value::isValue($param)) {
		$ret = "Error: wrong parameter given for upload";
		$val = new XML_RPC_Value($ret, 'string');
		return new XML_RPC_Response($val);
	}

	$vcard = $param->scalarval();
	
	$parse = new Contact_Vcard_Parse();
	
	$data = $parse->fromText($vcard);

    if(!is_array($data)) {
        $ret = "Error importing vcard!!";
		$val = new XML_RPC_Value($ret, 'string');
		return new XML_RPC_Response($val);
    }
    
    //clear "last import" category
    $import_cat = $CAT->exists('__lastimport__');
    if(is_object($import_cat)) $CAT->delete($import_cat->id);
    
    $import_cat = new category;
    $import_cat->name = '__lastimport__';
    $import_id = $CAT->set($import_cat);
    
    foreach($data as $card) {
        $contact = vcard2contact($card);
        
        if($AB->is_duplicate($contact)) {
            //$ret .= $contact->name() . " is duplicate!!\n";
            //$error++;
        } else {
            //import
            $person_id = $AB->set($contact);
            if($person_id === false) {
                $error++;
                $ret .= "Could not import contact ".$contact->name() ."\n";
            } else {
                $imported++;
                // add to last import category
                $CAT->add_contact($person_id, $import_id);

                if(is_array($card['CATEGORIES']['0']['value']['0'])) {
                    foreach($card['CATEGORIES']['0']['value']['0'] as $cat_name) {
                        // add to corresponding categories
                        $category = $CAT->exists($cat_name);
                        if(is_object($category)) {
                            $cat_id = $category->id;
                        } else {
                            $category = new category;
                            $category->name = $cat_name;
                            $cat_id = $CAT->set($category);
                        }
                        $CAT->add_contact($person_id, $cat_id);
                    }
                }
            }
        }
    }

    if($imported > 0) {
    	$ret .= "$imported vCard(s) succesfully imported!\n";
   	} else {
   		$ret .= "no new vCards imported.\n";
   	}
    
    $n = count($data);
    if($n == 0) {
        $ret .= "no vCards found to import.\n";
        $error++;
    }
    
    if($error != 0) {
        $ret .= "Import ended with $error error(s).\n";
    }
	
	$val = new XML_RPC_Value($ret, 'string');
	return new XML_RPC_Response($val);
}

/*
 * Initialize the database.
 */

db_init();
db_open();

$AB = new addressbook;
$CAT = new categories;


/*
 * Establish the dispatch map and XML_RPC server instance.
 */
$server = new XML_RPC_Server(
    array(
        'vcard_upload' => array(
            'function' => 'vcard_upload'
        ),
        'sync.start_sync' => array(
            'function' => 'start_sync'
        ),
        'sync.push_changes' => array(
            'function' => 'push_changes'
        ),
        'sync.pull_changes' => array(
            'function' => 'pull_changes'
        ),
        'sync.finish_sync' => array(
            'function' => 'finish_sync'
        ),
    ),
    1  // serviceNow
);

db_close();

?>