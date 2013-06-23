<?php
    /**
     * iAddressBook html functions
     *
     * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
     * @author     Clemens Wacha <clemens.wacha@gmx.net>
     */

    if(!defined('AB_BASEDIR')) define('AB_BASEDIR',realpath(dirname(__FILE__).'/../../'));
    require_once(AB_BASEDIR.'/lib/php/include.php');


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
function html_msgarea($msg_array = null) {
    global $MSG;
    
    if(!isset($msg_array)) $msg_array = $MSG;
    if(!isset($msg_array)) return;

    foreach($msg_array as $msg){
        print '<div class="'.$msg['lvl'].'" style="text-align: left;">';
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

function html_numberlayout($string) {
    global $conf;
    
    if(!is_array($conf['number_layout'])) return $string;

    $orig = $string;
    $string = preg_replace("/[0-9]/", "#", $string);
    $string_num = count_char($string, "#");

    foreach($conf['number_layout'] as $layout) {
        $layout_num = count_char($layout, "#");
    
        if($layout_num == $string_num) {
            // make layout adjustments to string
            $string = preg_replace("/[^0-9]/", "", $orig);
            for($t=0; $t < strlen($string); $t++) {
                $layout = preg_replace("/#/", $string[$t], $layout, 1);
            }
            return $layout;
        }
    }
    
    return $orig;
}

function count_char($string, $char) {
    $i = 0;
    for ($t = 0; $t < strlen($string); $t++) {
        if ($string[$t] == $char) $i++;
    }
    return $i;
}

function html_phpinfo() {
    phpinfo();    
}

?>
