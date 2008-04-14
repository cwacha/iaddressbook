<?php

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

?>