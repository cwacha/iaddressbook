<?php
    /**
     * iAddressBook Birthday Reminder
     *
     * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
     * @author     Clemens Wacha <clemens.wacha@gmx.net>
     */

    if(!defined('AB_BASEDIR')) define('AB_BASEDIR',realpath(dirname(__FILE__).'/../../'));
    require_once(AB_BASEDIR.'/lib/php/include.php');
    require_once(AB_BASEDIR.'/lib/php/addressbook.php');
    require_once(AB_BASEDIR.'/lib/php/common.php');
    

// collects all birthdays in the current month and the following month
function collect_birthdays() {
    global $db;
    global $db_config;
    global $AB;
    if(!$db) return array();
    $upcoming = array();
    
    $thismonth = date('n');
    //$thismonth = 12;
    $nextmonth = ($thismonth + 1) % 12;
    if($nextmonth == 0)
        $nextmonth = 12;
    
    $thism = $db->escape(sprintf('%%-%02d-%%', $thismonth));
    $nextm = $db->escape(sprintf('%%-%02d-%%', $nextmonth));
    
    $sql = "SELECT * FROM " . $db_config['dbtable_ab'] . " WHERE birthdate LIKE $thism";
	$sql .= " OR birthdate LIKE $nextm";
	$sql .= " ORDER BY birthdate ASC LIMIT 20";
	
    $result = $db->selectAll($sql);
    
    $now = time();
    if($result !== false) {
        foreach($result as $row) {
            $contact = $AB->row2contact($row);

            // if we are in december and the persons birthday is in january
            // we have to add 1 to his year
            if(date('n', $now) == 12 and date('n', strtotime($contact->birthdate)) == 1) {
                $bd_year = date('Y', $now) + 1;
            } else {
                $bd_year = date('Y', $now);
            }        
            $contact->birthday = $bd_year . nice_date('-$mm-$dd', $contact->birthdate);

            $upcoming[] = $contact;
        }
    } else {
        // not found
        msg("DB error on birthday collect: ".$db->lasterror(), -1);
    }

    // sort by actual birthday
    usort($upcoming, function($a, $b) {
        return $a->birthday > $b->birthday;
    });

    return $upcoming;
}

function tpl_birthday() {
    global $conf;
    global $lang;
    $num_bdays = 0;
    
    $people = collect_birthdays();
        
    foreach($people as $contact) {
        $now = time();
        //$now = strtotime('2011-12-30');
        
        $bd_year = date('Y', strtotime($contact->birthday));
        $age    = $bd_year - nice_date('$YYYY', $contact->birthdate);
        
        $days   = datediff('d', $now, strtotime($contact->birthday), true) + 1;
        $weeks  = datediff('ww', $now, strtotime($contact->birthday), true);
        $months = datediff('m', $now, strtotime($contact->birthday), true);

        if($weeks > $conf['bday_advance_week']) continue;
        
        if($days < 0) continue;
        
        if($days > 30) {
            $n = floor($days/30);
            if($days > 60)   $trans_text = $lang['bday_months'];
            else             $trans_text = $lang['bday_month'];
        } else if($days > 6) {
            $n = floor($days/7);
            if($days > 13)   $trans_text = $lang['bday_weeks'];
            else             $trans_text = $lang['bday_week'];
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
        $num_bdays++;
    }
    
    if($num_bdays == 0) {
        echo $lang['bday_none']."<br/>";
    }
}



?>