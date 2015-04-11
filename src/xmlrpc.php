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
require_once(AB_BASEDIR.'/lib/php/XML/RPC/Server.php');
require_once(AB_BASEDIR.'/lib/php/module_vcard.php');
require_once(AB_BASEDIR.'/lib/php/module_auth.php');
require_once(AB_BASEDIR.'/lib/php/common.php');

global $conf;

global $XMLRPC_VERSION;
$XMLRPC_VERSION = '3.0';

if(!$conf['xmlrpc_enable']) {
    $response = xml_error('XML-RPC api disabled');
    echo $response->serialize();
    exit();
}

/*
 * Declare the functions, etc.
 */

function xml_logmsg($message, $level=0) {
	msg($message, $level);
}

function xml_login($action, $params) {
	$api_key = XML_RPC_decode($params->getParam(0));
    if(auth_verify_action($api_key, $action)) {
        return true;
    }
	return false;
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
	global $XMLRPC_VERSION;
	
    if(!xml_login('xml_version', $params))
    	return xml_error('access denied');

    xml_logmsg("xmlrpc version: version=$XMLRPC_VERSION");
    return xml_success((string)$XMLRPC_VERSION);
}

function get_contact($params) {
    global $AB;
    
    if(!xml_login('xml_get_contact', $params))
    	return xml_error('access denied');
    
    $id = XML_RPC_decode($params->getParam(1));

    if(is_numeric($id))
    	$id = (int)$id;
    $contact = $AB->get($id);

    if(empty($contact)) {
        return xml_error('no such contact');
    }
    
    $person = $contact->get_array();

    xml_logmsg("xmlrpc get_contact: id=$id");
    return xml_success($person);
}

function get_contacts($params) {
    global $AB;
    global $CAT;
    
    if(!xml_login('xml_get_contacts', $params))
    	return xml_error('access denied');
    
    $results = array();
    
    $query = XML_RPC_decode($params->getParam(1));
    $limit = XML_RPC_decode($params->getParam(2));
    $offset = XML_RPC_decode($params->getParam(3));

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
    
    xml_logmsg("xmlrpc get_contacts: query='$query' limit=$limit offset=$offset num_cards=" . count($results));
    return xml_success($results);
}

function set_contact($params) {
    global $AB;
    
    if(!xml_login('xml_set_contact', $params))
    	return xml_error('access denied');
    
    $contact_array = XML_RPC_decode($params->getParam(1));

    $contact = new Person;
    $contact->set_array($contact_array);
    
    $id = $AB->set($contact);

    xml_logmsg("xmlrpc set_contact: id=$id");
    return xml_success($id);
}

function count_contacts($params) {
    global $AB;
    $contactlist = array();
    
    if(!xml_login('xml_count_contacts', $params))
    	return xml_error('access denied');
    
    $query = XML_RPC_decode($params->getParam(1));

    if(empty($query)) {
        $contactlist = $AB->getall();
    } else {
        $contactlist = $AB->find($query);
    }

    $result = count($contactlist);
    
    xml_logmsg("xmlrpc count_contacts: num_cards=" . count($result));
    return xml_success($result);
}

function delete_contact($params) {
    global $AB;
    global $CAT;

    if(!xml_login('xml_delete_contact', $params))
    	return xml_error('access denied');
    
    $id = XML_RPC_decode($params->getParam(1));
    
    if(is_numeric($id))
    	$id = (int)$id;
    $AB->delete($id);

    xml_logmsg("xmlrpc delete_contact: id=$id");
    return xml_success(msg_text());
}

function import_vcard($params) {
    if(!xml_login('xml_import_vcard', $params))
    	return xml_error('access denied');
	
    $vcard = XML_RPC_decode($params->getParam(1));

    act_importvcard($vcard);
    
    xml_logmsg("xmlrpc import_vcard");
    return xml_success(msg_text());
}

function export_vcard($params) {
    global $CAT;
    global $AB;
    $contacts_selected = array();

    if(!xml_login('xml_export_vcard', $params))
    	return xml_error('access denied');
    
    $id_list = XML_RPC_decode($params->getParam(1));

    $contactlist = $AB->getall();
    
    $vcarddata = '';
    foreach($id_list as $id) {
    	if(is_numeric($id))
    		$id = (int)$id;
    	$contact = $AB->get($id, true);
    	if($contact)
	    	$vcarddata .= contact2vcard($contact);
    }

    xml_logmsg("xmlrpc export_vcard");
    return xml_success($vcarddata);
}

/*
 * Initialize the database.
 */

db_init();
db_open();

$ABcatalog = new Addressbooks();
$books = $ABcatalog->getAddressBooksForUser('whatever');
$bookId = -1;
if(!empty($books)) {
	$bookId = $books[0]['id'];
}

$AB = new Addressbook($bookId);
$CAT = new Categories;

/*
 * Establish the dispatch map and XML_RPC server instance.
 */
// signature help: return type, input param1, input param2 etc.
$server = new XML_RPC_Server(
    array(
        'version' => array(
            'function' => 'version',
            'signature' => array( array('string', 'string') ),
            'docstring' => '@params: api_key; @return: version string'
        ),
        'get_contact' => array(
            'function' => 'get_contact',
            'signature' => array(
            					array('struct', 'string', 'int'),
        						array('struct', 'string', 'string')
            			),
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
        'delete_contact' => array(
            'function' => 'delete_contact',
            'signature' => array(
            					array('int', 'string', 'int'),
            					array('int', 'string', 'string')
            ),
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
    ),
    1  // serviceNow
);

db_close();

?>