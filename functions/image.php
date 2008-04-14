<?php
/**
 * AddressBook image display functions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Clemens Wacha <clemens.wacha@gmx.net>
 */


	if(!defined('AB_INC')) define('AB_INC',realpath(dirname(__FILE__).'/../').'/');
    if(!defined('AB_CONF')) define('AB_CONF',AB_INC.'conf/');
	require_once(AB_CONF.'defaults.php');
    require_once(AB_INC."functions/html.php");

function img_display() {
    global $AB;
    global $contact;
    $picture_known = false;

    act_getcontact('show');

    if(!empty($contact->image)) {
        //$im = $contact->image;
        $im = imagecreatefromstring($contact->image);
        if ($im == false) {
           //echo 'An error occured.';
        } else {
        	$picture_known = true;
        }
    }

    if($picture_known == true) {
        // display loaded image
        header('Content-Type: image/png');
        //header('Content-Type: application/data');
        //echo $im;
        imagepng($im);
        imagedestroy($im);
    } else {
        // display unknown image
        if($contact->company == true) {
        	$im_file = "images/unknown_company.gif";
        } else {
        	$im_file = "images/unknown_person.gif";
        }

        header('Content-Type: image/gif');
        $im = imagecreatefromgif(template($im_file));
        imagegif($im);
        imagedestroy($im);
    }

    exit();
}

function img_convert($in_image, $type='png', $options='') {
    global $conf;
    
    $err = '';
    $out_image = '';

    $desc_spec = array(
        0 => array("pipe", "r"),
        1 => array("pipe", "w"),
        2 => array("pipe", "w")
    );

    if(!is_readable($conf['im_convert'])) {
        msg("Cannot convert image: ".$conf['im_convert']." does not exist", -1);
        return $out_image;
    }
    
    $process = proc_open($conf['im_convert']." - $options $type:-", $desc_spec, $pipes);

    if(is_resource($process)) {
        fwrite($pipes[0], $in_image);
        fclose($pipes[0]);

        $out_image = stream_get_contents($pipes[1]);
        $err = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

    } else {
        $err = "could not open pipe";
    }

    if(!empty($err)) {
        msg( "<pre>", -1);
        msg( "ERROR: Could not convert image to $type<br>", -1);
        msg( "      Message: $err <br>", -1);
        msg( "      in_image size: ". strlen($in_image) . "<br>", -1);
        msg( "      out_image size: ". strlen($out) . "<br>", -1);
        msg( "      out_image: ". $out. "<br>", -1);
        msg( "</pre>", -1);
    }

    return $out_image;
}

function display_type($data) {
   $desc_spec = array(
        0 => array("pipe", "r"),
        1 => array("pipe", "w"),
        2 => array("pipe", "w")
    );

    $process = proc_open("/usr/bin/file -", $desc_spec, $pipes);

    $err = "";
    $result = "";

    if(is_resource($process)) {
        fwrite($pipes[0], $data);
        fclose($pipes[0]);

        $result = stream_get_contents($pipes[1]);
        $err = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

    } else {
        $err = "could not open pipe";
    }

    if(!empty($err)) {
        echo "<pre>";
        echo "ERROR: could not detect file type<br>";
        echo "      Message: $err <br>";
        echo "      data size: ". strlen($data) . "<br>";
        echo "      result size: ". strlen($result) . "<br>";
        echo "      out_image: ". base64_encode($result). "<br>";
        echo "</pre>";
    }

    echo $result . "<br>";
}


?>