<?php
/**
* iAddressBook xmlrpc automation
*
* @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
* @author     Clemens Wacha <clemens.wacha@gmx.net>
*/


if(!defined('AB_BASEDIR')) define('AB_BASEDIR',realpath(dirname(__FILE__)).'/');
require_once(AB_BASEDIR.'/lib/php/include.php');
require_once(AB_BASEDIR.'/lib/php/init.php');
require_once(AB_BASEDIR.'/lib/php/db.php');
require_once(AB_BASEDIR.'/lib/php/XML/Server.php');
require_once(AB_BASEDIR.'/lib/php/module_vcard.php');
require_once(AB_BASEDIR.'/lib/php/module_auth.php');
require_once(AB_BASEDIR.'/lib/php/common.php');

global $conf;

if(!$conf['xmlrpc_enable']) {
    $response = xml_error('XML-RPC api disabled');
    echo $response->serialize();
    exit();
}

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

function xml_reply($retval, $contents = null, $errormsg = null) {
    $ret = array();
    $ret['status'] = 'success';
    $ret['result'] = $contents;

    if(!$retval) {
        $ret['status'] = 'error';
        $ret['errmsg'] = $errormsg;
        $ret['result'] = null;
        if(empty($errormsg)) $ret['errmsg'] = msg_text();
    }
    
    $val = XML_RPC_encode($ret);
    $b = new XML_RPC_Response($val);
    return $b;
}

function xml_error($errormsg = null) {
    return xml_reply(0, null, $errormsg);
}

function xml_success($results = null) {
    return xml_reply(1, $results, null);
}

function version($params) {
    $api_key = XML_RPC_decode($params->getParam(0));
    
    if(!auth_verify_action($api_key, 'xml_version')) {
        return xml_error('access denied');
    }

    return xml_success(get_version());
}

function get_contact($params) {
    global $AB;
    global $CAT;
    
    $api_key = XML_RPC_decode($params->getParam(0));
    $id = XML_RPC_decode($params->getParam(1));

    if(!auth_verify_action($api_key, 'xml_get_contact')) {
        return xml_error('access denied');
    }

    $contact = $AB->get($id);

    if($contact) $contact_categories = $CAT->find($contact->id);
    $contact_categories = $CAT->sort($contact_categories);
    
    $person = $contact->get_array();
    $person['categories'] = array();
    foreach($contact_categories as $key => $value) {
        $person['categories'][$key] = $value->name;
    }

    return xml_success($person);
}

function get_contacts($params) {
    global $AB;
    global $CAT;
    
    $results = array();
    
    $api_key = XML_RPC_decode($params->getParam(0));
    $query = XML_RPC_decode($params->getParam(1));
    $limit = XML_RPC_decode($params->getParam(2));
    $offset = XML_RPC_decode($params->getParam(3));

    if(!auth_verify_action($api_key, 'xml_get_contacts')) {
        return xml_error('access denied');
    }

    $contactlist = array();
    if(empty($query)) {
        $contactlist = $AB->getall($limit, $offset);
    } else {
        $contactlist = $AB->find($query, $limit, $offset);
    }

    foreach($contactlist as $contact) {
        $person = $contact->get_array();

        $contact_categories = $CAT->find($contact->id);
        $contact_categories = $CAT->sort($contact_categories);
        
        $person['categories'] = array();
        foreach($contact_categories as $key => $value) {
            $person['categories'][$key] = $value->name;
        }

        $results[] = $person;
    }
    //$results = array();
    //msg("bla3");
    //return xml_error();
    return xml_success($results);
}

function set_contact($params) {
    global $AB;

    $api_key = XML_RPC_decode($params->getParam(0));
    $contact_array = XML_RPC_decode($params->getParam(1));

    if(!auth_verify_action($api_key, 'xml_set_contact')) {
        return xml_error('access denied');
    }
    
    $contact = new person;
    $contact->set_array($contact_array);
    
    $id = $AB->set($contact);

    return xml_success($id);
}

function count_contacts($params) {
    global $AB;
    $contactlist = array();
    
    $api_key = XML_RPC_decode($params->getParam(0));
    $query = XML_RPC_decode($params->getParam(1));

    if(!auth_verify_action($api_key, 'xml_count_contacts')) {
        return xml_error('access denied');
    }

    if(empty($query)) {
        $contactlist = $AB->getall();
    } else {
        $contactlist = $AB->find($query);
    }

    $result = count($contactlist);
    
    return xml_success($result);
}

/*
function search_email($params) {
    global $AB;
    $contactlist = array();
    $results = array();

    $api_key = XML_RPC_decode($params->getParam(0));
    $query = XML_RPC_decode($params->getParam(1));

    if(!auth_verify_action($api_key, 'xml_search_email')) {
        return xml_error('access denied');
    }

    if(empty($query)) {
        $contactlist = $AB->getall();
    } else {
        $contactlist = $AB->find($query);
    }

    foreach($contactlist as $contact) {
        $person = array();
        $person['name'] = $contact->name();
        
        foreach($contact->emails as $key => $value) {
            $person['email'] = $value['email'];
            $results[] = $person;
        }
    }
    
    return xml_success($results);
}
*/

function delete_contact($params) {
    global $AB;
    global $CAT;
    global $contact_categories;

    $api_key = XML_RPC_decode($params->getParam(0));
    $id = XML_RPC_decode($params->getParam(1));

    if(!auth_verify_action($api_key, 'xml_delete_contact')) {
        return xml_error('access denied');
    }

    $contact_categories = $CAT->find($id);
    foreach($contact_categories as $category) {
        $CAT->delete_contact($id, $category->id);
    }
    $AB->delete($id);

    return xml_success(msg_text());
}

function import_vcard($params) {
    $api_key = XML_RPC_decode($params->getParam(0));
    $vcard = XML_RPC_decode($params->getParam(1));

    if(!auth_verify_action($api_key, 'xml_import_vcard')) {
        return xml_error('access denied');
    }

    act_importvcard($vcard);
    
    return xml_success(msg_text());
}

function export_vcard($params) {
    global $CAT;
    global $AB;
    $contacts_selected = array();
    $vcard_list = '';

    $api_key = XML_RPC_decode($params->getParam(0));
    $id_list = XML_RPC_decode($params->getParam(1));

    if(!auth_verify_action($api_key, 'xml_export_vcard')) {
        return xml_error('access denied');
    }

    $contactlist = $AB->getall();
    
    foreach($id_list as $id) {
        if(array_key_exists($id, $contactlist)) {
            $contacts_selected[$id] = $contactlist[$id];
        }
    }

    foreach ($contacts_selected as $contact) {
        $contact->image = img_load($contact->id);
        $categories = $CAT->find($contact->id);
        $vcard = contact2vcard($contact, $categories);
        $vcard_list .= $vcard['vcard'];
    }

    return xml_success($vcard_list);
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
        'version' => array(
            'function' => 'version',
            'signature' => array( array('string', 'string') ),
            'docstring' => '@params: api_key; @return: version string'
        ),
        'get_contact' => array(
            'function' => 'get_contact',
            'signature' => array( array('struct', 'string', 'int') ),
            'docstring' => '@params: api_key, contact id; @return: contact'
        ),
        'get_contacts' => array(
            'function' => 'get_contacts',
            'signature' => array( array('struct', 'string', 'string', 'int', 'int') ),
            'docstring' => '@params: api_key, search_string, limit, offset; @return: contacts'
        ),
        'set_contact' => array(
            'function' => 'set_contact',
            'signature' => array( array('int', 'string', 'struct') ),
            'docstring' => '@params: api_key, contact; @return: contact id'
        ),
        'count_contacts' => array(
            'function' => 'count_contacts',
            'signature' => array( array('int', 'string', 'string') ),
            'docstring' => '@params: api_key, search_string; @return: number of id\'s'
        ),  
/*      
        'search_email' => array(
            'function' => 'search_email',
            'signature' => array( array('struct', 'string', 'string') ),
            'docstring' => '@params: api_key, search_string; @return: array of e-mail name pairs'
        ),
*/
        'delete_contact' => array(
            'function' => 'delete_contact',
            'signature' => array( array('int', 'string', 'int') ),
            'docstring' => '@params: api_key, contact id; @return: 1 if success'
        ),
        'import_vcard' => array(
            'function' => 'import_vcard',
            'signature' => array( array('int', 'string', 'string') ),
            'docstring' => '@params: api_key, vCard string; @return: 1 if success'
        ),
        'export_vcard' => array(
            'function' => 'export_vcard',
            'signature' => array( array('string', 'string', 'array') ),
            'docstring' => '@params: api_key, contact ids; @return: vcards as string'
        ),
        /*
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
        */
    ),
    1  // serviceNow
);

db_close();

?>