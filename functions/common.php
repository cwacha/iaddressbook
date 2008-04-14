<?php



function display_version() {
    global $VERSION;
    
    return $VERSION;
}

/*
 *	use like:
 *	$fmt = "$d. $month $YYYY";
 *	$date_string = nice_date("2006-03-01", $fmt);
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

/*
 * use like:
 * $conf['map_link'] = "http://map.search.ch/$city/$street";
 *
 */
function map_link($address) {
    global $conf;
    if(empty($conf['map_link'])) return '';
    
    $pobox   = urlencode($address['pobox']);
    $ext_adr = urlencode($address['ext_adr']);
    $street  = urlencode($address['street']);
    $city    = urlencode($address['city']);
    $state   = urlencode($address['state']);
    $zip     = urlencode($address['zip']);
    $country = urlencode($address['country']);
    
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
function msg($message,$lvl=0,$line='',$file=''){
  global $MSG;
  $errors[-1] = 'error';
  $errors[0]  = 'info';
  $errors[1]  = 'success';

  if($line || $file) $message.=' ['.basename($file).':'.$line.']';

  if(!headers_sent()){
    if(!isset($MSG)) $MSG = array();
    $MSG[]=array('lvl' => $errors[$lvl], 'msg' => $message);
  }else{
    $MSG = array();
    $MSG[]=array('lvl' => $errors[$lvl], 'msg' => $message);
    if(function_exists('html_msgarea')){
      html_msgarea();
    }else{
      print "ERROR($lvl) $message";
    }
  }
}

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

?>