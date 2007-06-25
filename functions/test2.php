<?php
require_once 'XML/RPC.php';


$input = 8;
$params = array(new XML_RPC_Value($input, 'int'));
$msg = new XML_RPC_Message('function_test', array());

$cli = new XML_RPC_Client('/~cwacha/ab2/functions/test.php', 'localhost');
// $cli->setDebug(1);
$resp = $cli->send($msg);

if (!$resp) {
    echo 'Communication error: ' . $cli->errstr;
    exit;
}

if (!$resp->faultCode()) {
    $val = $resp->value();
    echo $input . ' times 2 is ' . $val->scalarval();
} else {
    /*
     * Display problems that have been gracefully cought and
     * reported by the xmlrpc.php script.
     */
    echo 'Fault Code: ' . $resp->faultCode() . "\n";
    echo 'Fault Reason: ' . $resp->faultString() . "\n";
}
?>