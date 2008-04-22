<?php
/**
 * AddressBook image display functions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Clemens Wacha <clemens.wacha@gmx.net>
 */


    if(!defined('AB_CONF')) define('AB_CONF',AB_INC.'conf/');
    require_once(AB_CONF.'defaults.php');

    if(!defined('AB_INC')) define('AB_INC',realpath(dirname(__FILE__).'/../').'/');
    require_once(AB_INC."functions/html.php");


function img_create($id, $image) {
    global $conf;
    
    if(empty($image)) return;
    
    $id = (int)$id;
    $format = strtolower($conf['photo_format']);
    $image_file = AB_INC."_images/".$id.".".$format;
    $fd = @fopen($image_file, "wb");
    if(is_resource($fd)) {
        fwrite($fd, $image);
        fclose($fd);
    } else {
        msg("Could not write image $image_file", -1);
    }
}

function img_delete($id) {
    global $conf;
    
    $id = (int)$id;
    if($id != 0) {
        $format = strtolower($conf['photo_format']);
        $image_file = AB_INC."_images/".$id.".".$format;
        if(is_readable($image_file)) {
            unlink($image_file);
        }
    }
}

function img_load($id) {
    global $conf;

    $id = (int)$id;    
    $format = strtolower($conf['photo_format']);
    $image_file = AB_INC."_images/".$id.".".$format;
    $image = @file_get_contents($image_file);

    return $image;
}

function img_display() {
    global $AB;
    global $contact;
    global $conf;
    
    $picture_known = false;
    $image = '';

    act_getcontact();

    $format = strtolower($conf['photo_format']);
    $image_file = AB_INC."_images/".$contact->id.".".$format;
    if(is_readable($image_file)) {
        $image = @file_get_contents($image_file);
    }

    // now we have the image loaded in $image or no image at all

    if(!empty($image)) {
        // display loaded image
        header("Content-Type: image/$format");
        echo $image;
    } else {
        // display unknown image
        if($contact->company == true) {
            $im_file = "images/unknown_company.gif";
        } else {
            $im_file = "images/unknown_person.gif";
        }

        $image = @file_get_contents(template($im_file));
        
        header('Content-Type: image/gif');
        echo $image;
    }

	//echo "\n";
	//html_msgarea();
    exit();
}

function img_convert($in_image, $type='png', $options='') {
    global $conf;
    
    $out_image = '';
    $type = strtolower($type);

    if(!empty($conf['im_convert']) and is_readable($conf['im_convert'])) {

        // write in_image into a temp file
        $tmp_file = AB_INC."_images/tmp.xxx";
        $fd = fopen($tmp_file, "wb");
        if(is_resource($fd)) {
            fwrite($fd, $in_image);
            fclose($fd);

            // now try to convert the file using ImageMagick
            $pipe = popen($conf['im_convert']." ".$tmp_file." $options $type:-", "r");
            if(is_resource($pipe)) {
                while(!feof($pipe)) {
                    $out_image .= fread($pipe, 8192);
                }
                pclose($pipe);
            }
            
            // remove the temporary file
            if(is_readable($tmp_file)) {
                unlink($tmp_file);
            }

        } else {
            msg("Could not write temporary image $tmp_file", -1);
        }

	}

	if(empty($out_image) or !empty($err)) {
		// ImageMagick did not work, try with GD
		
		$tmp_image = AB_INC."_images/tmp.$type";
		
		$im = @imagecreatefromstring($in_image);
		if($im !== false) {
            // create temporary copy
            switch($type) {
                case 'png':
                    imagepng($im, $tmp_image);
                    break;
                case 'gif':
                    imagegif($im, $tmp_image);
                    break;
                case 'jpg':
                case 'jpeg':
                    imagejpeg($im, $tmp_image);
                    break;
                default:
                    msg("Cannot convert image: GD only supports gif, jpg and png", -1);
            }
			imagedestroy($im);
			
			$out_image = @file_get_contents($tmp_image);
			
	        if(is_writeable($tmp_image)) {
	            unlink($tmp_image);
	        }
		} else {
            msg("Cannot convert image with GD: Data is not in a recognized format", -1);
		}
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