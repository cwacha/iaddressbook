<?php
    /**
     * iAddressBook common functions
     *
     * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
     * @author     Clemens Wacha <clemens.wacha@gmx.net>
     */

    if(!defined('AB_BASEDIR')) define('AB_BASEDIR',realpath(dirname(__FILE__).'/../../'));
    require_once(AB_BASEDIR.'/lib/php/include.php');

function get_version() {
    global $VERSION;
    
    return $VERSION;
}

/*
 *    use like:
 *    $fmt = "$d. $month $YYYY";
 *    $date_string = nice_date("2006-03-01", $fmt);
 */
function nice_date($format_string, $iso_date) {
    global $lang;

    $YYYY  = intval(substr($iso_date, 0, 4));
    $m     = intval(substr($iso_date, 5, 2));
    $d     = intval(substr($iso_date, 8, 2));

    $mm    = sprintf("%02u", $m);
    $dd    = sprintf("%02u", $d);
    
    $month = $lang['month'][$m];

    eval("\$ret = \"$format_string\";");

    return $ret;
}

function datediff($interval, $datefrom, $dateto, $using_timestamps = false) {
  /*
    $interval can be:
    yyyy - Number of full years
    q - Number of full quarters
    m - Number of full months
    y - Difference between day numbers
      (eg 1st Jan 2004 is "1", the first day. 2nd Feb 2003 is "33". The datediff is "-32".)
    d - Number of full days
    w - Number of full weekdays
    ww - Number of full weeks
    h - Number of full hours
    n - Number of full minutes
    s - Number of full seconds (default)
  */
  
  if (!$using_timestamps) {
    $datefrom = strtotime($datefrom, 0);
    $dateto = strtotime($dateto, 0);
  }
  $difference = $dateto - $datefrom; // Difference in seconds
   
  switch($interval) {
   
    case 'yyyy': // Number of full years

      $years_difference = floor($difference / 31536000);
      if (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom), date("j", $datefrom), date("Y", $datefrom)+$years_difference) > $dateto) {
        $years_difference--;
      }
      if (mktime(date("H", $dateto), date("i", $dateto), date("s", $dateto), date("n", $dateto), date("j", $dateto), date("Y", $dateto)-($years_difference+1)) > $datefrom) {
        $years_difference++;
      }
      $datediff = $years_difference;
      break;

    case "q": // Number of full quarters

      $quarters_difference = floor($difference / 8035200);
      while (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom)+($quarters_difference*3), date("j", $dateto), date("Y", $datefrom)) < $dateto) {
        $months_difference++;
      }
      $quarters_difference--;
      $datediff = $quarters_difference;
      break;

    case "m": // Number of full months

      $months_difference = floor($difference / 2678400);
      while (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom)+($months_difference), date("j", $dateto), date("Y", $datefrom)) < $dateto) {
        $months_difference++;
      }
      $months_difference--;
      $datediff = $months_difference;
      break;

    case 'y': // Difference between day numbers

      $datediff = date("z", $dateto) - date("z", $datefrom);
      break;

    case "d": // Number of full days

      $datediff = floor($difference / 86400);
      break;

    case "w": // Number of full weekdays

      $days_difference = floor($difference / 86400);
      $weeks_difference = floor($days_difference / 7); // Complete weeks
      $first_day = date("w", $datefrom);
      $days_remainder = floor($days_difference % 7);
      $odd_days = $first_day + $days_remainder; // Do we have a Saturday or Sunday in the remainder?
      if ($odd_days > 7) { // Sunday
        $days_remainder--;
      }
      if ($odd_days > 6) { // Saturday
        $days_remainder--;
      }
      $datediff = ($weeks_difference * 5) + $days_remainder;
      break;

    case "ww": // Number of full weeks

      $datediff = floor($difference / 604800);
      break;

    case "h": // Number of full hours

      $datediff = floor($difference / 3600);
      break;

    case "n": // Number of full minutes

      $datediff = floor($difference / 60);
      break;

    default: // Number of full seconds (default)

      $datediff = $difference;
      break;
  }    

  return $datediff;

}


/*
 * use like:
 * $conf['map_link'] = "http://map.search.ch/$city/$street";
 *
 */
function map_link($address) {
    global $conf;
    if(empty($conf['map_link'])) return '';
    
    $pobox   = urlencode(str_replace('/', '', $address['pobox']));
    $ext_adr = urlencode(str_replace('/', '', $address['ext_adr']));
    $street  = urlencode(str_replace('/', '', $address['street']));
    $city    = urlencode(str_replace('/', '', $address['city']));
    $state   = urlencode(str_replace('/', '', $address['state']));
    $zip     = urlencode(str_replace('/', '', $address['zip']));
    $country = urlencode(str_replace('/', '', $address['country']));
    
    eval("\$ret = \"".$conf['map_link']."\";");
    
    return $ret;
}


/**
 * print a message
 *
 * If HTTP headers were not sent yet the message is added
 * to the global message array else it's printed directly
 * using html_msgarea()
 *
 *
 * Levels can be:
 *
 * -1 error
 *  0 info
 *  1 success
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @see    html_msgarea
 */
function msg($message, $lvl=0){
    global $MSG;
    $errors[-1] = 'error';
    $errors[0]  = 'info';
    $errors[1]  = 'success';

    if(!headers_sent()) {
        if(!isset($MSG)) $MSG = array();
        $MSG[]=array('lvl' => $errors[$lvl], 'msg' => $message);
    } else {
        $MSG = array();
        $MSG[]=array('lvl' => $errors[$lvl], 'msg' => $message);
        if(function_exists('html_msgarea')) {
            html_msgarea();
        } else {
            print "ERROR($lvl) $message";
        }
    }
}

function imsg($message, $lvl=0) {
    $errors[-1] = 'error';
    $errors[0]  = 'info';
    $errors[1]  = 'success';

    $msg = array();
    $msg[]=array('lvl' => $errors[$lvl], 'msg' => $message);
    if(function_exists('html_msgarea')) {
        html_msgarea($msg);
    } else {
        print "ERROR($lvl) $message";
    }
}

function msg_text() {
  global $MSG;
  if(!isset($MSG)) return '';

  foreach($MSG as $msg){
    $ret .= $msg['lvl']. ": ".$msg['msg'] . "\n";
  }
  
  return $ret;
}


/*
 * array_to_text
 * Returns a string with PHP code representing the array given.
 *
 * @param $array    the array to convert
 * @param $prefix   the name the array should have in PHP
 * @return          the serialized PHP code 
 */
function array_to_text($array, $prefix='') {
    $text = '';
    
    if(empty($prefix)) $prefix = '$lang';
    
    foreach($array as $key => $value) {
        if(gettype($value) == 'string') {
            $line = $prefix .'[\''.$key.'\']';
            $line = str_pad($line, 40);
            $line .= "= '".addcslashes($value, "'")."';";
            $text .= $line . "\n";
        } else if(gettype($value) == 'array') {
            $text .= "\n";
            $text .= array_to_text($value, $prefix . '[\''.$key.'\']');
        } else if(gettype($value) == 'integer') {
            $line = $prefix .'[\''.$key.'\']';
            $line = str_pad($line, 40);
            $line .= "= ".(string)$value.";";
            $text .= $line . "\n";
        } else {
            msg("array_to_text: cannot convert: unsupported object ".gettype($value), -1);
        }
    }
    return $text;
}


/*
 * real_stripcslashes
 * Returns a string with backslashes stripped off.
 * Note:    This function behaves identical to stripcslashes
 *          except that it also works correctly for newlines!
 *
 * @param $string   the string to unescape
 * @param $escapes  the charaters that should be unescaped
 * @return          the unescaped string 
 */
function real_stripcslashes($string, $escapes) {
    $escape_mode = 0;
    $out_string = '';
    
    $len = strlen($string);
    for($i = 0; $i < $len; $i++) {
        switch ($escape_mode) {
            case 0:
                // normal mode
                if( $string{$i} == '\\') {
                    $escape_mode = 1;
                } else {
                    $out_string .= $string{$i};
                }
                break;
            case 1:
                // escape mode
                if( strpos($escapes, $string{$i}) === false) {
                    //not found - nothing to unescape
                    $out_string .= '\\' . $string{$i};                    
                } else {
                    if($string{$i} == "a") $out_string .= "\a";
                    else if($string{$i} == "b") $out_string .= "\b";
                    else if($string{$i} == "f") $out_string .= "\f";
                    else if($string{$i} == "n") $out_string .= "\n";
                    else if($string{$i} == "r") $out_string .= "\r";
                    else if($string{$i} == "t") $out_string .= "\t";
                    else if($string{$i} == "v") $out_string .= "\v";
                    else if($string{$i} == "0") $out_string .= "\0";
                    else $out_string .= $string{$i};
                }
                $escape_mode = 0;
                break;
            default:
                // non existent
        }
    }
    
    return $out_string;
}

/*
 * real_addcslashes
 * escapes every character in $string with a backslash \
 * if the character appears in $escapes and returns
 * the escaped version.
 * Note:    This function behaves identical to addcslashes
 *          except that it also works correctly for newlines!
 *
 * @param $string   the string to escape
 * @param $escapes  the charaters that should be escaped
 * @return          the escaped string 
 */
function real_addcslashes($string, $escapes) {
    $out_string = '';
    
    $len = strlen($string);
    for($i = 0; $i < $len; $i++) {
        if( strpos($escapes, $string{$i}) === false) {
            // not found - nothing to escape
            $out_string .= $string{$i};
        } else {
            switch ($string{$i}) {
                case "\a":  $out_string .= "\\a"; break;
                case "\b":  $out_string .= "\\b"; break;
                case "\f":  $out_string .= "\\f"; break;
                case "\n":  $out_string .= "\\n"; break;
                case "\r":  $out_string .= "\\r"; break;
                case "\t":  $out_string .= "\\t"; break;
                case "\v":  $out_string .= "\\v"; break;
                case "\0":  $out_string .= "\\0"; break;
                default:    $out_string .= "\\" . $string{$i};
            }
        }
    }
    
    return $out_string;
}

// this function converts nl into br and not nl into br nl..!!
function real_nl2br($string) {
    return str_replace("\n", "<br />", $string);
}

function real_br2nl($string) {
    return preg_replace("/\<\s*br\s*\/*\>/", "\n", $string);
}

if(!function_exists('scandir')) {
    function scandir($dir, $sort_order=0) {
        $files = array();
        $dh  = opendir($dir);
        while (false !== ($filename = readdir($dh))) {
            $files[] = $filename;
        }
        return $files;
    }
}

function fix_fmode($file) {
    global $conf;
    
    if($conf['fperm']) chmod($file, $conf['fperm']);
}

function fix_dmode($dir) {
    global $conf;
    
    if($conf['dperm']) chmod($dir, $conf['dperm']);
}

?>