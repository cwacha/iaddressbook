<?php
exit();
require_once 'XML/RPC.php';


$api_key = 'abcd';
$server = "iaddressbook.org";
$serverURI = "/demo/xmlrpc.php";
$debug = 0;

$search_string = '';
if(isset($_REQUEST['q']))
	$search_string = $_REQUEST['q'];
$limit = 0;
$offset = 0;

$request = array();
$request[] = XML_RPC_encode($api_key);
$request[] = XML_RPC_encode($search_string);
$request[] = XML_RPC_encode($limit);
$request[] = XML_RPC_encode($offset);

$msg = new XML_RPC_Message('get_contacts', $request);

$cli = new XML_RPC_Client($serverURI, $server);
$cli->setDebug($debug);
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

echo "<pre>";
echo "connection successful to http://$server$serverURI<br/>";

if($debug) {
	print_r($resp);
}

$val = XML_RPC_decode($resp->value());
$status = $val['status'];
$errmsg = $val['errmsg'];
$result = $val['result'];

echo "STATUS: $status<br/>";
if($status != 'success')
	echo "ERROR: $errmsg <br/>";

echo "RESULT:<br/>";
print_r($result);

echo "</pre>";


?>