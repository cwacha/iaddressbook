<?php
/**
 * AddressBook Birthday Reminder
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Clemens Wacha <clemens.wacha@gmx.net>
 */

    if(!defined('AB_CONF')) define('AB_CONF',AB_INC.'conf/');
    require_once(AB_CONF.'defaults.php');

    if(!defined('AB_INC')) define('AB_INC',realpath(dirname(__FILE__).'/../').'/');
    require_once(AB_INC.'functions/addressbook.php');
    require_once(AB_INC.'functions/common.php');
    

// collects all birthdays in the current month and the following month
function collect_birthdays() {
    global $db;
    global $db_config;
    global $AB;
    if(!$db) return array();
    $upcoming = array();
    
    if($db_config['dbtype'] == 'sqlite') {
        $sql  = "SELECT * FROM ".$db_config['dbtable_ab']." WHERE strftime('%m', birthdate) = strftime('%m', 'now')";
        $sql .= " OR strftime('%m', birthdate) = strftime('%m', 'now', '+1 month')";
        $sql .= " ORDER BY strftime('%j', birthdate) ASC LIMIT 100";
    } else {
        $sql  = "SELECT * FROM ".$db_config['dbtable_ab']." WHERE MONTH(birthdate) = MONTH(NOW())";
        $sql .= " OR MONTH(birthdate) = MONTH(NOW()) + 1";
        $sql .= " ORDER BY DAYOFYEAR(birthdate) ASC LIMIT 100";
    }
    
    
    $result = $db->Execute($sql);
    
    if($result) {
        while($row = $result->FetchRow()) {
            $contact = $AB->row2contact($row);
            $upcoming[] = $contact;
        }
    } else {
        // not found
        msg("DB error on birthday collect: ".$db->ErrorMsg(), -1);
    }
    
    return $upcoming;
}

function tpl_birthday() {
    global $conf;
    global $lang;
    
    $people = collect_birthdays();
    
    foreach($people as $contact) {
        $birthday = strtotime( date('Y') . nice_date('-$mm-$dd', $contact->birthdate) );
        //$birthday = strtotime( '20070630' );
        
        $age    = datediff('yyyy', strtotime(nice_date('$YYYY-01-01', $contact->birthdate)), time(), true);
        
        $days   = datediff('d', time(), $birthday, true) + 1;
        $weeks  = datediff('ww', time(), $birthday, true);
        $months = datediff('m', time(), $birthday, true);
        
        if($weeks > $conf['bday_advance_week']) continue;
        
        if($days < 0) continue;
        
        if($months > 0) {
            $n = $months;
            if($months == 1) $trans_text = $lang['bday_month'];
            else             $trans_text = $lang['bday_months'];
        } else if($weeks > 0) {
            $n = $weeks;
            if($weeks == 1)  $trans_text = $lang['bday_week'];
            else             $trans_text = $lang['bday_weeks'];
        } else if($days > 0) {
            $n = $days;
            if($days == 1)   $trans_text = $lang['bday_day'];
            else if($days == 2) $trans_text = $lang['bday_day2'];
            else             $trans_text = $lang['bday_days'];
        } else {
            // birthday today!
            $trans_text = $lang['bday_today'];
        }
        
        eval("\$text = \"$trans_text\";");
        
        if(!empty($contact->nickname)) $name = $contact->nickname;
        else $name = $contact->name(false);

        echo "<a href='?id=$contact->id'>$name</a> ($age) $text<br/>";
    }
    
    if(count($people) == 0) {
        echo $lang['bday_none']."<br/>";
    }
    
}



?>