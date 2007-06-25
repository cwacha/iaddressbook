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
//echo "translator disabled"; exit(0);

// define the include path
if(!defined('AB_INC')) define('AB_INC',realpath(dirname(__FILE__).'/../').'/');
require_once(AB_INC.'functions/init.php');


// define languages to be translated
global $all_languages;
$all_languages = array('de', 'de_ch', 'eo', 'es', 'fr', 'it', 'ja', 'nl', 'pt', 'ru', 'sk');

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
    
    require(AB_INC.'lang/'.$base_language.'/lang.php');
    
    $lang = rewrite($lang);
    
    @include(AB_INC.'lang/'.$language.'/lang.php');
    read_header($language);
    return $lang;
}

function read_header($language) {
    global $header;
    
    $file = AB_INC.'lang/'.$language.'/lang.php';
    
    $header = @file($file);
    
    if(is_array($header)) {
        unset($header[0]);
        $ln = '';
        while( trim($ln) != '*/' and count($header) > 1) {
            $ln = array_pop($header);
        }
        while( trim($header[0]) == '' and count($header) > 1) {
            $ln = array_shift($header);
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

function array_to_text($array, $prefix='') {
    $text = '';
    
    if(empty($prefix)) $prefix = '$lang';
    
    foreach($array as $key => $value) {
        if(gettype($value) == 'string') {
            $line = $prefix .'[\''.$key.'\']';
            $line = str_pad($line, 40);
            $line .= '= "'.$value.'";';
            $text .= $line . "\n";
        } else if(gettype($value) == 'array') {
            $text .= "\n";
            $text .= array_to_text($value, $prefix . '[\''.$key.'\']');
        } else {
            echo "cannot convert: unsupported object";
        }
    }
    return $text;
}

function write_lang_php($array, $lng) {
    global $header;
    global $conf;
    
    $file = AB_INC.'lang/'.$lng.'.php';
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
    
    if($conf['fperm']) chmod($file, $conf['fperm']);
}

function del_lang_php($lng) {
    $file = AB_INC.'lang/'.$lng.'.php';
    @unlink($file);
}

function has_new_text($array) {
    foreach($array as $value) {
        if(strpos($value, 'TRANSLATE:') === 0) return true;
    }
    return false;
}

/* * * * * * * * * * * * * *
 *                         *
 *        M A I N          * 
 *                         *
 * * * * * * * * * * * * * */
    echo "<pre>";
    echo "Updating languages...<br>";
    foreach($all_languages as $lng) {
        echo "Processing $lng ";
        $lang = update_language($lng);
    /*    echo '<pre>';
        echo 'Language: '.$lng.'<br>';
        print_r($lang);
        echo '</pre>';
    */
        if(has_new_text($lang)) {
            echo ": updated!";
            write_lang_php($lang, $lng);
        } else {
            // delete old temporary language file if it exists
            del_lang_php($lng);
        }
        echo "<br>";
    }
    echo "done.<br>";
    echo "</pre>";


?>