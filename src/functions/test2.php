<?php
exit();
require_once 'XML/RPC.php';


$api_key = 'abcd';

$search_string = $_REQUEST['q'];
$id = 2;
$limit = 40;
$offset = 0;

$request = array();
$request[] = XML_RPC_encode($api_key);
//$request[] = XML_RPC_encode($search_string);
$request[] = XML_RPC_encode($limit);
$request[] = XML_RPC_encode($offset);

$msg = new XML_RPC_Message('get_contacts', $request);

$cli = new XML_RPC_Client('/xmlrpc.php', 'ab.wacha.ch');
$cli->setDebug(1);
$resp = $cli->send($msg);

header('Content-Type: text/html; charset=utf-8');

if (!$resp) {
    echo 'Communication error: ' . $cli->errstr .' ('.$cli->errno.')';
    exit;
}

if ($resp->faultCode()) {
    /*
     * Display problems that have been gracefully cought and
     * reported by the xmlrpc.php script.
     */
    echo 'Fault Code: ' . $resp->faultCode() . "<br>";
    echo 'Fault Reason: ' . $resp->faultString() . "<br>";
    exit();
}

$val = XML_RPC_decode($resp->value());

/*
echo "<pre>";
//print_r($resp->value());
print_r($val['result']);
echo "</pre>";
*/

$contact = $val['result'];


echo "<pre>";
print_r($contact);
echo "</pre>";




/*
foreach ($val['result'] as $result) {
    echo "Name: ". $result['name'] . "<br>";
    echo "e-mail: ". $result['email'] . "<br>";
    echo "<br>";
}


if(count($val['result']) == 0) {
    echo "No matches.<br>";
}
*/


?>