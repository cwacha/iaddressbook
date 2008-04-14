<?php
/**
 * AddressBook VCard Import
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Clemens Wacha <clemens.wacha@gmx.net>
 */

    if(!defined('AB_INC')) define('AB_INC',realpath(dirname(__FILE__).'/../').'/');
	require_once(AB_INC.'functions/addressbook.php');
    require_once(AB_INC.'functions/Contact_Vcard_Parse.php');
    require_once(AB_INC.'functions/Contact_Vcard_Build.php');
    require_once(AB_INC.'functions/image.php');

    

function act_importvcard() {
    global $AB;
    global $conf;
    
    $error = 0;
    $imported = 0;
    
    if(!is_string($_FILES['vcard_file']['tmp_name'])) {
        echo "file was not uploaded<br>";
        return;
    }
    if(!file_exists($_FILES['vcard_file']['tmp_name'])) {
        echo "file does not exist";
        return;
    }
    
    // instantiate a parser object
    $parse = new Contact_Vcard_Parse();
    
    // parse it
    $data = $parse->fromFile($_FILES['vcard_file']['tmp_name'], true, $conf['vcard_fb_enc']);
    
    if(!is_array($data)) {
        echo "Error importing vcard!!";
        //msg("error importing vcard");
        return;
    }
    
    foreach($data as $card) {
        
        $contact = new person;
        
        $contact->lastname = $card['N'][0]['value'][0][0];
        $contact->firstname = $card['N'][0]['value'][1][0];
        $contact->firstname2 = $card['N'][0]['value'][2][0];
        $contact->title = $card['N'][0]['value'][3][0];
        $contact->suffix = $card['N'][0]['value'][4][0];
        $contact->nickname = $card['NICKNAME'][0]['value'][0][0];
        
        $contact->organization = $card['ORG'][0]['value'][0][0];
        $contact->department = $card['ORG'][0]['value'][1][0];
        
        $contact->jobtitle = $card['TITLE'][0]['value'][0][0];
        
        $contact->note = $card['NOTE'][0][value][0][0];        
        $contact->birthdate = $card['BDAY'][0][value][0][0];
        
        $contact->company = ($card['X-ABSHOWAS'][0]['value'][0][0] == "COMPANY");
        
        if(is_array($card['EMAIL'])) {
            foreach($card['EMAIL'] as $email) {
                $tmp = array();
                if(is_array($email['param']['TYPE'])) {
                    if(in_array('WORK', $email['param']['TYPE'])) $tmp['label'] = 'WORK';
                    if(in_array('HOME', $email['param']['TYPE'])) $tmp['label'] = 'HOME';
                }
                if(!empty($email['X-ABLABEL'])) $tmp['label'] = $email['X-ABLABEL']['value'][0][0];
                
                $tmp['email'] = $email['value'][0][0];
                $contact->add_email($tmp);
            }
        }
        
        if(is_array($card['TEL'])) {
            foreach($card['TEL'] as $phone) {
                $tmp = array();
                if(is_array($phone['param']['TYPE'])) {
                    if(in_array('WORK', $phone['param']['TYPE'])) $tmp['label'] = 'WORK';
                    if(in_array('HOME', $phone['param']['TYPE'])) $tmp['label'] = 'HOME';
                    if(in_array('CELL', $phone['param']['TYPE'])) $tmp['label'] = 'CELL';
                    if(in_array('MAIN', $phone['param']['TYPE'])) $tmp['label'] = 'MAIN';
                    if(in_array('PAGER', $phone['param']['TYPE'])) $tmp['label'] = 'PAGER';
                    if(in_array('FAX', $phone['param']['TYPE'])) $tmp['label'] .= ' FAX';
                }
                if(!empty($phone['X-ABLABEL'])) $tmp['label'] = $phone['X-ABLABEL']['value'][0][0];
                
                $tmp['phone'] = $phone['value'][0][0];
                $contact->add_phone($tmp);
            }
        }

        if(is_array($card['ADR'])) {
            foreach($card['ADR'] as $address) {
                $tmp = array();
                if(is_array($address['param']['TYPE'])){
                    if(in_array('WORK', $address['param']['TYPE'])) $tmp['label'] = 'WORK';
                    if(in_array('HOME', $address['param']['TYPE'])) $tmp['label'] = 'HOME';
                }
                if(!empty($address['X-ABLABEL'])) $tmp['label'] = $address['X-ABLABEL']['value'][0][0];
                
                $tmp['pobox'] = $address['value'][0][0];
                $tmp['ext_adr'] = $address['value'][1][0];
                $tmp['street'] = $address['value'][2][0];
                $tmp['city'] = $address['value'][3][0];
                $tmp['state'] = $address['value'][4][0];
                $tmp['zip'] = $address['value'][5][0];
                $tmp['country'] = $address['value'][6][0];
                $tmp['template'] = $address['X-ABADR']['value'][0][0];
                $contact->add_address($tmp);
            }
        }
        
        if(is_array($card['URL'])) {
            foreach($card['URL'] as $url) {
                $tmp = array();
                if(is_array($url['param']['TYPE'])){
                    if(in_array('WORK', $url['param']['TYPE'])) $tmp['label'] = 'WORK';
                    if(in_array('HOME', $url['param']['TYPE'])) $tmp['label'] = 'HOME';
                }
                if(!empty($url['X-ABLABEL'])) $tmp['label'] = $url['X-ABLABEL']['value'][0][0];
                
                $tmp['url'] = $url['value'][0][0];
                $contact->add_url($tmp);
            }
        }
        
        if(is_array($card['X-ABRELATEDNAMES'])) {
            foreach($card['X-ABRELATEDNAMES'] as $rname) {
                $tmp = array();
                if(is_array($rname['param']['TYPE'])){
                    if(in_array('WORK', $rname['param']['TYPE'])) $tmp['label'] = 'WORK';
                    if(in_array('HOME', $rname['param']['TYPE'])) $tmp['label'] = 'HOME';
                }
                if(!empty($rname['X-ABLABEL'])) $tmp['label'] = $rname['X-ABLABEL']['value'][0][0];
                
                $tmp['name'] = $rname['value'][0][0];
                $contact->add_relatedname($tmp);
            }
        }
        
        if(is_array($card['X-AIM'])) {
            foreach($card['X-AIM'] as $chat) {
                $tmp = array();
                if(is_array($chat['param']['TYPE'])){
                    if(in_array('WORK', $chat['param']['TYPE'])) $tmp['label'] = 'WORK';
                    if(in_array('HOME', $chat['param']['TYPE'])) $tmp['label'] = 'HOME';
                }
                if(!empty($chat['X-ABLABEL'])) $tmp['label'] = $chat['X-ABLABEL']['value'][0][0];
                
                $tmp['type'] = "AIM";
                $tmp['handle'] = $chat['value'][0][0];
                $contact->add_chathandle($tmp);
            }
        }
        
        if(is_array($card['X-ICQ'])) {
            foreach($card['X-ICQ'] as $chat) {
                $tmp = array();
                if(is_array($chat['param']['TYPE'])){
                    if(in_array('WORK', $chat['param']['TYPE'])) $tmp['label'] = 'WORK';
                    if(in_array('HOME', $chat['param']['TYPE'])) $tmp['label'] = 'HOME';
                }
                if(!empty($chat['X-ABLABEL'])) $tmp['label'] = $chat['X-ABLABEL']['value'][0][0];
                
                $tmp['type'] = "ICQ";
                $tmp['handle'] = $chat['value'][0][0];
                $contact->add_chathandle($tmp);
            }
        }
        
        if(is_array($card['X-MSN'])) {
            foreach($card['X-MSN'] as $chat) {
                $tmp = array();
                if(is_array($chat['param']['TYPE'])){
                    if(in_array('WORK', $chat['param']['TYPE'])) $tmp['label'] = 'WORK';
                    if(in_array('HOME', $chat['param']['TYPE'])) $tmp['label'] = 'HOME';
                }
                if(!empty($chat['X-ABLABEL'])) $tmp['label'] = $chat['X-ABLABEL']['value'][0][0];
                
                $tmp['type'] = "MSN";
                $tmp['handle'] = $chat['value'][0][0];
                $contact->add_chathandle($tmp);
            }
        }
        
        if(is_array($card['X-JABBER'])) {
            foreach($card['X-JABBER'] as $chat) {
                $tmp = array();
                if(is_array($chat['param']['TYPE'])){
                    if(in_array('WORK', $chat['param']['TYPE'])) $tmp['label'] = 'WORK';
                    if(in_array('HOME', $chat['param']['TYPE'])) $tmp['label'] = 'HOME';
                }
                if(!empty($chat['X-ABLABEL'])) $tmp['label'] = $chat['X-ABLABEL']['value'][0][0];
                
                $tmp['type'] = "JABBER";
                $tmp['handle'] = $chat['value'][0][0];
                $contact->add_chathandle($tmp);
            }
        }
        
        if(is_array($card['X-YAHOO'])) {
            foreach($card['X-YAHOO'] as $chat) {
                $tmp = array();
                if(is_array($chat['param']['TYPE'])){
                    if(in_array('WORK', $chat['param']['TYPE'])) $tmp['label'] = 'WORK';
                    if(in_array('HOME', $chat['param']['TYPE'])) $tmp['label'] = 'HOME';
                }
                if(!empty($chat['X-ABLABEL'])) $tmp['label'] = $chat['X-ABLABEL']['value'][0][0];
                
                $tmp['type'] = "YAHOO";
                $tmp['handle'] = $chat['value'][0][0];
                $contact->add_chathandle($tmp);
            }
        }
        
        if(is_array($card['X-IRC'])) {
            foreach($card['X-IRC'] as $chat) {
                $tmp = array();
                if(is_array($chat['param']['TYPE'])){
                    if(in_array('WORK', $chat['param']['TYPE'])) $tmp['label'] = 'WORK';
                    if(in_array('HOME', $chat['param']['TYPE'])) $tmp['label'] = 'HOME';
                }
                if(!empty($chat['X-ABLABEL'])) $tmp['label'] = $chat['X-ABLABEL']['value'][0][0];
                
                $tmp['type'] = "IRC";
                $tmp['handle'] = $chat['value'][0][0];
                $contact->add_chathandle($tmp);
            }
        }
        
        if(is_array($card['PHOTO'])) {
            $enc = $card['PHOTO'][0]['param']['ENCODING'][0];
            $picture = $card['PHOTO'][0]['value'][0][0];
            if($enc == "BASE64" || $enc == "B") {
                $contact->image = img_convert(base64_decode($picture));
            } else {
                echo "Cannot import image. Unknown encoding $enc<br>";
            }
        }
        
        //$contact->show();
        
        if($AB->is_duplicate($contact)) {
            echo "$contact->firstname $contact->lastname is duplicate!!<br>";
            //msg("$contact->firstname $contact->lastname is duplicate!!);
            $error++;
        } else {
            //import
            if($AB->set($contact) == false) {
                $error++;
                echo "Could not import contact $contact->firstname $contact->lastname<br>";
            } else $imported++;
        }
    }
    
    unlink($_FILES['vcard_file']['tmp_name']);

    if($imported > 0) echo "$imported vCard(s) succesfully imported!<br>";
    
    $n = count($data);
    if($n == 0) {
        echo "no vCards found to import.<br>";
        $error++;

        echo '<pre>';
        print_r($data);
        echo '</pre>';
    }
    
    if($error != 0) {
        echo "Import ended with $error error(s).<br>";
    }    

    // output results
    //echo '<pre>';
    //print_r($data);
    //echo '</pre>';
    
    return 'show';
}

function act_exportvcard($action) {
    global $AB;
    global $ID;
    
    $contact = $AB->get($ID);

    $card = new Contact_Vcard_Build();
    
    $card->setFormattedName("$contact->firstname $contact->lastname");
    $card->setName($contact->lastname, $contact->firstname, $contact->firstname2, $contact->title, $contact->suffix);
    if(!empty($contact->nickname)) $card->addNickname($contact->nickname);
    
    if(!empty($contact->jobtitle)) $card->setTitle($contact->jobtitle);
    if(!empty($contact->organization)) $card->addOrganization($contact->organization);
    if(!empty($contact->department)) $card->addOrganization($contact->department);
    // add $company boolean
    if($contact->company == true) $card->setValue('X-ABShowas', 0, 0, 'COMPANY');
    
    if($contact->birthdate != '0000-00-00') $card->setBirthday($contact->birthdate);
    if(!empty($contact->homepage)) $card->setURL($contact->homepage);

	//var $image;	// picture
	if(!empty($contact->note)) $card->setNote($contact->note);
    
    foreach($contact->addresses as $item) {
        $iter = $card->countIter('ADR');
        $card->addAddress($item['pobox'], $item['ext_adr'], $item['street'], $item['city'], $item['state'], $item['zip'], $item['country']);
        if(in_array($item['label'] , array('HOME', 'WORK'))) {
            $card->addParam('TYPE', $item['label']);
        } else {
            $card->setItem('ADR', $iter, 'X-ABLabel', $item['label']);
        }
        if(!empty($item['template'])) $card->setItem('ADR', $iter, 'X-ABADR', $item['template']);
    }
    
    foreach($contact->emails as $item) {
        $iter = $card->countIter('EMAIL');
        $card->addEmail($item['email']);
        if(in_array($item['label'] , array('HOME', 'WORK'))) {
            $card->addParam('TYPE', $item['label']);
        } else {
            $card->setItem('EMAIL', $iter, 'X-ABLabel', $item['label']);
        }
    }
    
    foreach($contact->phones as $item) {
        $iter = $card->countIter('TEL');
        $card->addTelephone($item['phone']);
        if(in_array($item['label'] , array('HOME', 'WORK', 'CELL', 'PAGER', 'MAIN'))) {
            $card->addParam('TYPE', $item['label']);
        } else if(in_array($item['label'] , array('HOME FAX', 'WORK FAX'))) {
            $card->addParam('TYPE', substr($item['label'], 0, 4));
            $card->addParam('TYPE', 'FAX');
        } else {
            $card->setItem('TEL', $iter, 'X-ABLabel', $item['label']);
        }
    }

    foreach($contact->chathandles as $item) {
        $iter = $card->countIter('X-'.$item['type']);
        $card->setValue('X-'.$item['type'], $iter, 0, $item['handle']);
        if(in_array($item['label'] , array('HOME', 'WORK'))) {
            $card->addParam('TYPE', $item['label']);
        } else {
            $card->setItem('X-'.$item['type'], $iter, 'X-ABLabel', $item['label']);
        }
    }

    foreach($contact->urls as $item) {
        $iter = $card->countIter('URL');
        $card->addURL($item['url']);
        if(in_array($item['label'] , array('HOME', 'WORK'))) {
            $card->addParam('TYPE', $item['label']);
        } else {
            $card->setItem('URL', $iter, 'X-ABLabel', $item['label']);
        }
    }

    foreach($contact->relatednames as $item) {
        $iter = $card->countIter('X-ABRELATEDNAMES');
        $card->setValue('X-ABRELATEDNAMES', $iter, 0, $item['name']);
        if(in_array($item['label'] , array('HOME', 'WORK'))) {
            $card->addParam('TYPE', $item['label']);
        } else {
            $card->setItem('X-ABRELATEDNAMES', $iter, 'X-ABLabel', $item['label']);
        }
    }
    
    if(!empty($contact->image)) {
        $card->setValue('PHOTO', 0, 0, base64_encode(img_convert($contact->image, 'jpg')));
        $card->addParam('ENCODING', 'B');
        //$card->addParam('TYPE', 'TIFF');
    }    
    
    // send the vcard
    $filename = "$contact->title $contact->firstname $contact->firstname2 $contact->lastname $contact->suffix";
    $filename = trim($filename);
    $filename .= ".vcf";
    //$filename .= ".txt";
    
    header("Content-Disposition: attachment; filename=\"$filename\"");
    //header("Content-Length: ".strlen($card));
    header("Connection: close");
    header("Content-Type: text/x-vCard; name=\"$filename\"");
    echo $card->fetch();
    //header("Content-Type: text/plain;");
    //echo base64_encode(img_convert($contact->image, 'tif'));
    exit();
    
    return $action;
}


?>