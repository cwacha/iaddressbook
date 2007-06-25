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

function img_removecache($id) {
    
    $id = (int)$id;
    if($id != 0) {
        $cached_image = AB_INC."_cache/".$id.".png";
        if(is_readable($cached_image)) {
            unlink($cached_image);
        }
    }
}

function img_display() {
    global $AB;
    global $contact;
    global $conf;
    
    $picture_known = false;
    $image = '';

    act_getcontact();
    

    if(!empty($contact->image)) {
        // check for cached copy
        $cached_image = AB_INC."_cache/".$contact->id.".png";
        if(is_readable($cached_image)) {
            $fd = fopen($cached_image, "rb");
            if(is_resource($fd)) {
                while(!feof($fd)) $image .= fread($fd, 8192);
                fclose($fd);
            }
			//msg("using cached copy");
        } else {
            // no cache available
			$image = img_convert($contact->image);
			if(!empty($image)) {
				// create cached copy
				$fd = @fopen($cached_image, "wb");
				if(is_resource($fd)) {
					fwrite($fd, $image);
					fclose($fd);
				}
			}
        }
    }

    // now we have the image as PNG loaded in $image or no image at all

    if(!empty($image)) {
        // display loaded image
        header('Content-Type: image/png');
        echo $image;
    } else {
        // display unknown image
        if($contact->company == true) {
            $im_file = "images/unknown_company.gif";
        } else {
            $im_file = "images/unknown_person.gif";
        }

        $fd = @fopen(template($im_file), "rb");
        if(is_resource($fd)) {
            while(!feof($fd)) $image .= fread($fd, 8192);
            fclose($fd);
        }
        
        header('Content-Type: image/gif');
        echo $image;
    }

	//echo "\n";
	//html_msgarea();
    exit();
}

function img_convert($in_image, $type='png', $options='') {
    global $conf;
    
    $err = '';
    $out_image = '';

    if(!empty($conf['im_convert']) and is_readable($conf['im_convert'])) {
		
	    $desc_spec = array(
	        0 => array("pipe", "rb"),
	        1 => array("pipe", "w"),
	        2 => array("pipe", "w")
	    );
		
		$process = proc_open($conf['im_convert']." - $options $type:-", $desc_spec, $pipes);
		
		if(is_resource($process)) {
			fwrite($pipes[0], $in_image);
			fclose($pipes[0]);
			
			while(!feof($pipes[1])) {
				$out_image .= fread($pipes[1], 8192);
			}
			while(!feof($pipes[2])) {
				$err .= fread($pipes[2], 8192);
			}
			fclose($pipes[1]);
			fclose($pipes[2]);
			
			//msg("Using ImageMagick");
			
		} else {
			$err = "could not open pipe";
		}
	}

	if(empty($out_image) or !empty($err)) {
		// ImageMagick did not work, try with GD
		
		$tmp_image = AB_INC."_cache/".$contact->id.".tmp.png";
		
		$im = @imagecreatefromstring($in_image);
		if($im !== false) {
			// create temporary copy
			imagepng($im, $tmp_image);
			imagedestroy($im);
			
			$fd = fopen($tmp_image, "rb");
			if(is_resource($fd)) {
				while(!feof($fd)) $out_image .= fread($fd, 8192);
				fclose($fd);
			}
			
	        if(is_readable($tmp_image)) {
	            unlink($tmp_image);
	        }
			//msg("Using GD");
		}
	}

    if(!empty($err)) {
        msg( "<pre style='text-align: left;' >", -1);
        msg( "ERROR: Could not convert image to $type", -1);
        msg( "      Message: $err", -1);
        msg( "      in_image size: ". strlen($in_image), -1);
        msg( "      out_image size: ". strlen($out), -1);
        msg( "      out_image: ". $out, -1);
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