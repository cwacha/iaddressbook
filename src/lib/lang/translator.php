<?php
/*
 * Translation Helper written by Clemens Wacha
 * 
 * updates language files based on a base language
 * and writes them to /lang/lang_<COUNTRY CODE>.php
 * if they need to be adjusted.
 *
 */

//disable if not used
echo "translator disabled"; exit(0);

// define the include path
if(!defined('AB_BASEDIR')) define('AB_BASEDIR',realpath(dirname(__FILE__).'/../../'));
require_once(AB_BASEDIR.'/lib/php/include.php');
require_once(AB_BASEDIR.'/lib/php/init.php');
require_once(AB_BASEDIR.'/lib/php/common.php');

$conf['fmode'] = 666;
init_creationmodes();

// define languages to be translated
global $all_languages;
$all_languages = array('bg', 'cs', 'de', 'de_ch', 'eo', 'es', 'fr', 'hu', 'id', 'it', 'ja', 'nl', 'pt', 'ru', 'se', 'sk');

// define the master language that all others are based upon
global $base_language;
$base_language = 'en';



/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 *  N O   M O D I F I C A T I O N   B E L O W   T H I S   L I N E  *
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
global $header;
$header = array();


function rewrite($array) {
	foreach($array as $key => $value) {
		if(gettype($value) == 'string') {
			$array[$key] = 'TRANSLATE:' . $value;
		} else if(gettype($value) == 'array') {
			$array[$key] = rewrite($value);
		} else {
			echo "unsupported type!";
		}
	}
	return $array;
}

function update_language($language) {
	global $base_language;
	$lang = array();
	
	require(AB_LANGDIR.'/'.$base_language.'/lang.php');
	
	$lang = rewrite($lang);
	
	@include(AB_LANGDIR.'/'.$language.'/lang.php');
	read_header($language);
	return $lang;
}

function read_header($language) {
	global $header;
	
	$file = AB_LANGDIR.'/'.$language.'/lang.php';
	
	$header = @file($file);
	
	if(is_array($header)) {
		array_shift($header);
		$ln = '';
		while( trim($ln) != '*/' and count($header) > 1) {
			$ln = array_pop($header);
		}
		while( trim($header[0]) == '' and count($header) > 1) {
			array_shift($header);
		}
		$header[] = " */\n\n\n";
	} else {
		$header = array();
	}

/*
	echo '<pre>';
	echo 'Header: '.$language.'<br>';
	print_r($header);
	echo '</pre>';
*/	

}

function write_lang_php($array, $lng) {
	global $header;
	global $conf;
	
	$file = AB_LANGDIR.'/'.$lng.'.php';
	$fd = fopen($file, "w");
	if(!$fd) {
		echo "could not write $file<br>";
		return;
	}
	fwrite($fd, "<?php\n");
	
	foreach($header as $line) {
		fwrite($fd, $line);
	}

	$data = array_to_text($array);
	fwrite($fd, $data);

	fwrite($fd, "\n\n?>");

	fclose($fd);
	fix_fmode($file);
}

function del_lang_php($lng) {
	$file = AB_LANGDIR.'/'.$lng.'.php';
	@unlink($file);
}

function has_new_text($array) {
	$count = 0;
	$total = 0;
	foreach($array as $value) {
		if(gettype($value) == 'string') {
			if(strpos($value, 'TRANSLATE:') === 0) $count++;
			$total++;
		} else if(gettype($value) == 'array') {
			list($count1, $total1) = has_new_text($value);
			$count += $count1;
			$total += $total1;
		} else {
			echo "unsupported type!";
		}
	}
	return array($count,$total);
}

/* * * * * * * * * * * * * *
 *						 *
 *		M A I N		  *
 *						 *
 * * * * * * * * * * * * * */
	echo "<pre>";
	echo "Updating languages...<br>";
	foreach($all_languages as $lng) {
		echo "Processing $lng ";
		$lang = update_language($lng);
/*
		echo '<pre>';
		echo 'Language: '.$lng.'<br>';
		print_r($lang);
		echo '</pre>';
*/
		list($count, $total) = has_new_text($lang);
		$percent = (($total-$count)/$total) * 100;
		if($count) {
			echo ": ".number_format($percent,0)."% ($count new strings)";
			write_lang_php($lang, $lng);
		} else {
			echo ": ".number_format($percent,0)."%";
			// delete old temporary language file if it exists
			del_lang_php($lng);
		}
		echo "<br>";
	}
	echo "done.<br>";
	echo "</pre>";


?>
