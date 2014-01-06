<?php
/**
* iAddressBook xmlrpc automation
*
* @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
* @author     Clemens Wacha <clemens.wacha@gmx.net>
*/

if(!defined('AB_BASEDIR')) define('AB_BASEDIR',realpath(dirname(__FILE__).'/'));
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
    
    $api_key = XML_RPC_decode($params->getParam(0));
    $id = XML_RPC_decode($params->getParam(1));

    if(!auth_verify_action($api_key, 'xml_get_contact')) {
        return xml_error('access denied');
    }

    $contact = $AB->get($id);

    if(empty($contact)) {
        return xml_error('no such contact');
    }
    
    $person = $contact->get_array();

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

        $results[] = $person;
    }
    return xml_success($results);
}

function set_contact($params) {
    global $AB;

    $api_key = XML_RPC_decode($params->getParam(0));
    $contact_array = XML_RPC_decode($params->getParam(1));

    if(!auth_verify_action($api_key, 'xml_set_contact')) {
        return xml_error('access denied');
    }
    
    $contact = new Person;
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

    $api_key = XML_RPC_decode($params->getParam(0));
    $id = XML_RPC_decode($params->getParam(1));

    if(!auth_verify_action($api_key, 'xml_delete_contact')) {
        return xml_error('access denied');
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
    	// FIXME: category handling and vcard has changed....
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

$AB = new Addressbook;
$CAT = new Categories;

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