<?php

    if(!defined('AB_CONF')) define('AB_CONF',AB_INC.'conf/');
    require_once(AB_CONF.'defaults.php');


/*
function html_debug() {
    global $ACT;
    global $QUERY;
    global $ID;
    global $AB;
    global $contactlist;

    echo "Action: $ACT<br>\n";
    echo "ID: $ID<br>\n";
    echo "Query: $QUERY<br>\n";
    echo "AB: $AB<br>\n";
    echo "contactlist: $contactlist<br>\n";
    
    //print_r($_REQUEST);
}
*/
/**
 * Prints the global message array
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_msgarea(){
  global $MSG;

  if(!isset($MSG)) return;

  foreach($MSG as $msg){
    print '<div class="'.$msg['lvl'].'">';
    print $msg['msg'];
    print '</div>';
  }
}

/**
 * prints some debug info
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_debug(){
    global $conf;
    global $lang;
    global $auth;
    //remove sensitive data
    $cnf = $conf;
    $cnf['auth']='***';
    $cnf['notify']='***';
    $cnf['dbuser']='***';
    $cnf['dbpass']='***';
    
    if($conf['debug'] != 1) {
        msg("Debugging disabled", -1);
        return;
    }
    
    print '<html><body>';
    
    print '<p>When reporting bugs please send all the following ';
    print 'output as a mail to clemens.wacha@gmx.net ';
    print 'The best way to do this is to save this page in your browser</p>';
    
    print '<b>$_SERVER:</b><pre>';
    print_r($_SERVER);
    print '</pre>';
    
    print '<b>$conf:</b><pre>';
    print_r($cnf);
    print '</pre>';
    
    print '<b>rel FILE_BASE:</b><pre>';
    print dirname($_SERVER['PHP_SELF']).'/';
    print '</pre>';
    
    print '<b>PHP Version:</b><pre>';
    print phpversion();
    print '</pre>';
    
    print '<b>locale:</b><pre>';
    print setlocale(LC_ALL,0);
    print '</pre>';
    
    print '<b>encoding:</b><pre>';
    print $lang['encoding'];
    print '</pre>';
    
    if($auth){
    print '<b>Auth backend capabilities:</b><pre>';
    print_r($auth->cando);
    print '</pre>';
    }
    
    print '<b>$_SESSION:</b><pre>';
    print_r($_SESSION);
    print '</pre>';
    
    print '<b>Environment:</b><pre>';
    print_r($_ENV);
    print '</pre>';
    
    print '<b>PHP settings:</b><pre>';
    $inis = ini_get_all();
    print_r($inis);
    print '</pre>';
    
    print '</body></html>';
    
    exit();
}


?>