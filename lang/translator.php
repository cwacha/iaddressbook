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


// define languages to be translated
$all_languages = array('de', 'es', 'nl', 'it', 'fr', 'ch');

// define the master language that all others are based upon
global $base_language;
$base_language = 'en';



/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 *  n o   m o d i f i c a t i o n   b e l o w   t h i s   l i n e  *
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
global $header;
$header = array();

// define the include path
if(!defined('AB_INC')) define('AB_INC',realpath(dirname(__FILE__).'/../').'/');
//require_once(AB_INC.'functions/common.php');
//require_once(AB_INC.'functions/html.php');

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
            $line = str_pad($line, 30);
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
    
    $file = AB_INC.'lang/'.$lng.'.php';
    $fd = fopen($file, "w");
    if(!$fd) {
        echo "could not write $file<br>";
        return;
    }
    fwrite($fd, "<?php\n\n");
    
    foreach($header as $line) {
        fwrite($fd, $line);
    }

    $data = array_to_text($array);
    fwrite($fd, $data);

    fwrite($fd, "\n\n?>");

    fclose($fd);
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
    
    echo "Updating languages...<br>";
    foreach($all_languages as $lng) {
        $lang = update_language($lng);
    /*    echo '<pre>';
        echo 'Language: '.$lng.'<br>';
        print_r($lang);
        echo '</pre>';
    */
        if(has_new_text($lang)) {
            echo 'Writing updated language: '.$lng.'<br>';
            write_lang_php($lang, $lng);
        } else {
            // delete old temporary language file if it exists
            del_lang_php($lng);
        }
    }
    echo "done.<br>";




?>