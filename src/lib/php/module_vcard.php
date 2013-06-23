<?php
    /**
     * iAddressBook VCard Import
     *
     * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
     * @author     Clemens Wacha <clemens.wacha@gmx.net>
     */

    if(!defined('AB_BASEDIR')) define('AB_BASEDIR',realpath(dirname(__FILE__).'/../../'));
    require_once(AB_BASEDIR.'/lib/php/include.php');
    require_once(AB_BASEDIR.'/lib/php/addressbook.php');
    require_once(AB_BASEDIR.'/lib/php/Contact_Vcard_Parse.php');
    require_once(AB_BASEDIR.'/lib/php/Contact_Vcard_Build.php');
    require_once(AB_BASEDIR.'/lib/php/image.php');
    require_once(AB_BASEDIR.'/lib/php/category.php');

    

function act_importvcard_file($filename = '') {
    global $conf;
        
    if($filename == '') $filename = $_FILES['vcard_file']['tmp_name'];
    
    if(!is_string($filename)) {
        msg("file was not uploaded!", -1);
        return;
    }
    if(!file_exists($filename)) {
        msg("file $filename does not exist!", -1);
        return;
    }
    
    $data = file_get_contents($filename);
    
    act_importvcard($data);
    
    if(is_writeable($filename)) {
        unlink($filename);
    } else {
        msg("Cannot remove $filename: read-only file", -1);
    }
}

function act_importvcard($vcard_string) {
    global $AB;
    global $conf;
    global $CAT;
    global $ID;
    
    $error = 0;
    $imported = 0;
    $person_id = false;
        
    // instantiate a parser object
    $parse = new Contact_Vcard_Parse();
    
    // parse it
    $data = $parse->fromText($vcard_string, true, $conf['vcard_fb_enc']);
    
    if(!is_array($data)) {
        msg("Error importing vcard!!", -1);
        //msg("error importing vcard");
        return;
    }
    
    //clear "last import" category
    $import_cat = $CAT->exists(' __lastimport__');
    if(is_object($import_cat)) $CAT->delete($import_cat->id);
    
    $import_cat = new category;
    $import_cat->name = ' __lastimport__';
    $import_id = $CAT->set($import_cat);
    
    foreach($data as $card) {
        $contact = vcard2contact($card);
        
        if($AB->is_duplicate($contact)) {
            msg($contact->name() . " is duplicate!!", -1);
            $error++;
        } else {
            //import
            $person_id = $AB->set($contact);
            if($person_id === false) {
                $error++;
                msg("Could not import contact ".$contact->name() , -1);
            } else {
                $imported++;
                // add to last import category
                $CAT->add_contact($person_id, $import_id);

                if(is_array($card['CATEGORIES']['0']['value']['0'])) {
                    foreach($card['CATEGORIES']['0']['value']['0'] as $cat_name) {
                        // add to corresponding categories
                        $category = $CAT->exists($cat_name);
                        if(is_object($category)) {
                            $cat_id = $category->id;
                        } else {
                            $category = new category;
                            $category->name = $cat_name;
                            $cat_id = $CAT->set($category);
                        }
                        $CAT->add_contact($person_id, $cat_id);
                    }
                }
            }
        }
    }
    
    if($imported > 0) msg("$imported vCard(s) succesfully imported!", 1);
    
    $n = count($data);
    if($n == 0) {
        msg("no vCards found to import.");
        $error++;
    }
    
    if($error != 0) {
        if($error > 1) msg("Import ended with $error errors.");
        else           msg("Import ended with $error error.");
    }

    $ID = (int)$person_id;

    // output results
    //echo '<pre style="text-align: left;">';
    //print_r($data);
    //echo '</pre>';
}

function act_importfolder($folder = '') {
    if($folder == '') $folder = AB_IMPORTDIR;

    $dh = opendir($folder);
    if(!is_resource($dh)) {
        msg("Could not open $folder");
        return;
    }
    
    $processed = 0;
    
    while(false !== ($file = readdir($dh))) {
        if(is_file($folder . $file) and strtolower(substr($file, -4, 4)) == ".vcf") {
            msg("Processing file: $file", 1);
            act_importvcard_file($folder . $file);
            $processed++;
        }
    }
    
    if($processed > 0) {
        msg("$processed files processed", 1);
    } else {
        msg("No vCards found in import folder: " . $folder, -1);
        msg("Make sure the folder is readable by the webserver and the files have a '.vcf' extension!");
    }
    
    closedir($dh);
}

function act_exportvcard() {
    global $AB;
    global $ID;
    global $CAT;
    
    $contact = $AB->get($ID, true);
    $categories = $CAT->find($ID);

    $vcard = contact2vcard($contact, $categories);
   
    
    // send the vcard
    $filename = $vcard['name'];
    $filename = trim($filename);
    $filename .= ".vcf";
    //$filename .= ".txt";
    
    header("Content-Disposition: attachment; filename=\"$filename\"");
    //header("Content-Length: ".strlen($card));
    header("Connection: close");
    header("Content-Type: text/x-vCard; name=\"$filename\"");
    echo $vcard['vcard'];
    exit();
    
}

function act_exportvcard_cat() {
    global $contactlist;
    global $conf;
    global $CAT;
    
    $contacts_selected = array();

    $vcard_list = '';
    
    foreach($_REQUEST as $key => $value) {
        if(strpos($key, 'ct_') === 0) {
            $contacts_selected[$value] = $contactlist[$value];
        }
    }
    if(count($contacts_selected) == 0) $contacts_selected = $contactlist;

    foreach ($contacts_selected as $contact) {
        $contact->image = img_load($contact->id);
        $categories = $CAT->find($contact->id);
        $vcard = contact2vcard($contact, $categories);
        $vcard_list .= $vcard['vcard'];
    }
    
    if(!empty($contacts_selected)) {
        // send the vcard
        $filename = "All Contacts (" . count($contacts_selected) . ")";
        $filename = trim($filename);
        $filename .= ".vcf";
        
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Connection: close");
        header("Content-Type: text/x-vCard; name=\"$filename\"");
        echo $vcard_list;
        exit();    
    }

}

function contact2vcard($contact, $categories) {
    global $conf;
    
    $card = new Contact_Vcard_Build();
    
    $card->setFormattedName( $contact->name() );
    $card->setName($contact->lastname, $contact->firstname, $contact->firstname2, $contact->title, $contact->suffix);
    if(!empty($contact->nickname)) $card->addNickname($contact->nickname);    
    if(!empty($contact->phoneticlastname)) $card->setValue('X-PHONETIC-LAST-NAME', 0 , 0 ,$contact->phoneticlastname);
    if(!empty($contact->phoneticfirstname)) $card->setValue('X-PHONETIC-FIRST-NAME', 0 , 0 ,$contact->phoneticfirstname);
    if(!empty($contact->jobtitle)) $card->setTitle($contact->jobtitle);
    if(!empty($contact->organization)) $card->addOrganization($contact->organization);
    if(!empty($contact->department)) $card->addOrganization($contact->department);
    // add $company boolean
    if($contact->company == true) $card->setValue('X-ABShowas', 0, 0, 'COMPANY');
    
    if($contact->birthdate != '0000-00-00') $card->setBirthday($contact->birthdate);

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
    
    if(!empty($contact->image) and !empty($conf['photo_format'])) {
        $conf['photo_format'] = strtoupper($conf['photo_format']);
		$card->setValue('PHOTO', 0, 0, base64_encode(img_convert($contact->image, $conf['photo_format'])));
		$card->addParam('ENCODING', 'B');
		$card->addParam('TYPE', strtoupper($conf['photo_format']));        
/*
        if($conf['photo_format'] == 'PNG') {
            // don't convert native format
            $card->setValue('PHOTO', 0, 0, base64_encode($contact->image));
            $card->addParam('ENCODING', 'B');
            $card->addParam('TYPE', 'PNG');
        } else {
            $card->setValue('PHOTO', 0, 0, base64_encode(img_convert($contact->image, $conf['photo_format'])));
            $card->addParam('ENCODING', 'B');
            $card->addParam('TYPE', strtoupper($conf['photo_format']));        
        }
*/
	}
    
    if(is_array($categories)) {
        foreach($categories as $category) {
            $card->addCategories($category->name);
        }
    }

    $vcard['name'] = $contact->name();
    $vcard['vcard'] = $card->fetch();

    return $vcard;
}

function vcard2contact($card) {
    global $conf;
    
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
    
    $contact->note = $card['NOTE'][0]['value'][0][0];        
    $contact->birthdate = substr($card['BDAY'][0]['value'][0][0], 0, 10);
    
    $contact->company = ($card['X-ABSHOWAS'][0]['value'][0][0] == "COMPANY");
    
    if(is_array($card['EMAIL'])) {
        foreach($card['EMAIL'] as $email) {
            $tmp = array();
            $tmp['label'] = 'WORK';    //default
            if(is_array($email['param']['TYPE'])) {
                foreach($email['param']['TYPE'] as $key => $value) $email['param']['TYPE'][$key] = strtoupper($value);
                
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
            $tmp['label'] = 'WORK';    //default
            if(is_array($phone['param']['TYPE'])) {
                foreach($phone['param']['TYPE'] as $key => $value) $phone['param']['TYPE'][$key] = strtoupper($value);
                
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
            $tmp['label'] = 'WORK';    //default
            if(is_array($address['param']['TYPE'])){
                foreach($address['param']['TYPE'] as $key => $value) $address['param']['TYPE'][$key] = strtoupper($value);
                
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
            $tmp['label'] = 'WORK';    //default
            if(is_array($url['param']['TYPE'])){
                foreach($url['param']['TYPE'] as $key => $value) $url['param']['TYPE'][$key] = strtoupper($value);
                
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
            $tmp['label'] = 'WORK';    //default
            if(is_array($rname['param']['TYPE'])){
                foreach($rname['param']['TYPE'] as $key => $value) $rname['param']['TYPE'][$key] = strtoupper($value);
                
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
            $tmp['label'] = 'WORK';    //default
            if(is_array($chat['param']['TYPE'])){
                foreach($chat['param']['TYPE'] as $key => $value) $chat['param']['TYPE'][$key] = strtoupper($value);
                
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
            $tmp['label'] = 'WORK';    //default
            if(is_array($chat['param']['TYPE'])){
                foreach($chat['param']['TYPE'] as $key => $value) $chat['param']['TYPE'][$key] = strtoupper($value);
                
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
            $tmp['label'] = 'WORK';    //default
            if(is_array($chat['param']['TYPE'])){
                foreach($chat['param']['TYPE'] as $key => $value) $chat['param']['TYPE'][$key] = strtoupper($value);
                
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
            $tmp['label'] = 'WORK';    //default
            if(is_array($chat['param']['TYPE'])){
                foreach($chat['param']['TYPE'] as $key => $value) $chat['param']['TYPE'][$key] = strtoupper($value);
                
                if(in_array('WORK', $chat['param']['TYPE'])) $tmp['label'] = 'WORK';
                if(in_array('HOME', $chat['param']['TYPE'])) $tmp['label'] = 'HOME';
            }
            if(!empty($chat['X-ABLABEL'])) $tmp['label'] = $chat['X-ABLABEL']['value'][0][0];
            
            $tmp['type'] = "JABBER";
            $tmp['handle'] = $chat['value'][0][0];
            $contact->add_chathandle($tmp);
        }
    }
    
    if(is_array($card['X-SKYPE'])) {
        foreach($card['X-SKYPE'] as $chat) {
            $tmp = array();
            $tmp['label'] = 'WORK';    //default
            if(is_array($chat['param']['TYPE'])){
                foreach($chat['param']['TYPE'] as $key => $value) $chat['param']['TYPE'][$key] = strtoupper($value);
                
                if(in_array('WORK', $chat['param']['TYPE'])) $tmp['label'] = 'WORK';
                if(in_array('HOME', $chat['param']['TYPE'])) $tmp['label'] = 'HOME';
            }
            if(!empty($chat['X-ABLABEL'])) $tmp['label'] = $chat['X-ABLABEL']['value'][0][0];
            
            $tmp['type'] = "SKYPE";
            $tmp['handle'] = $chat['value'][0][0];
            $contact->add_chathandle($tmp);
        }
    }
    
    if(is_array($card['X-YAHOO'])) {
        foreach($card['X-YAHOO'] as $chat) {
            $tmp = array();
            $tmp['label'] = 'WORK';    //default
            if(is_array($chat['param']['TYPE'])){
                foreach($chat['param']['TYPE'] as $key => $value) $chat['param']['TYPE'][$key] = strtoupper($value);
                
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
            $tmp['label'] = 'WORK';    //default
            if(is_array($chat['param']['TYPE'])){
                foreach($chat['param']['TYPE'] as $key => $value) $chat['param']['TYPE'][$key] = strtoupper($value);
                
                if(in_array('WORK', $chat['param']['TYPE'])) $tmp['label'] = 'WORK';
                if(in_array('HOME', $chat['param']['TYPE'])) $tmp['label'] = 'HOME';
            }
            if(!empty($chat['X-ABLABEL'])) $tmp['label'] = $chat['X-ABLABEL']['value'][0][0];
            
            $tmp['type'] = "IRC";
            $tmp['handle'] = $chat['value'][0][0];
            $contact->add_chathandle($tmp);
        }
    }
    
    if($conf['photo_enable']) {
        if(is_array($card['PHOTO'])) {
            $enc = strtoupper($card['PHOTO'][0]['param']['ENCODING'][0]);
            $picture = $card['PHOTO'][0]['value'][0][0];
            if($enc == "BASE64" || $enc == "B") {
                //$contact->image = img_convert(base64_decode($picture));
                //if(empty($contact->image)) msg("No image returned for ".$contact->name());
                $contact->image = base64_decode($picture);
            } else {
                msg("Cannot import image. Unknown encoding $enc", -1);
            }
        }
    }
    
    //$contact->show();

    return $contact;
}


?>