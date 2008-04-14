<?php
/**
 * AddressBook Actions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Clemens Wacha <clemens.wacha@gmx.net>
 */

    if(!defined('AB_INC')) define('AB_INC',realpath(dirname(__FILE__).'/../').'/');
	require_once(AB_INC.'functions/addressbook.php');
	require_once(AB_INC.'functions/template.php');
	require_once(AB_INC.'functions/image.php');
	require_once(AB_INC.'functions/module_vcard.php');


/**
 * Call the needed action handlers
 *
 * @author Clemens Wacha <clemens.wacha@gmx.net>
 */
function act_dispatch(){
    global $ACT;
    global $QUERY;
    global $ID;
    global $lang;
    global $conf;

    //sanitize $ACT
    //$ACT = act_clean($ACT);
    if(empty($ACT)) $ACT = 'show';

    
    //save
    if($ACT == 'save') {
        $ACT = act_save($ACT);
        $ACT = act_getcontact($ACT);
    }

    if($ACT == 'delete') {
        $ACT = act_delete($ACT);
        act_search($ACT);
        //$ACT = act_getcontact($ACT);
    }

    if($ACT == 'import_vcard') {
        set_time_limit(0);
        $ACT = act_importvcard($ACT);
    }


    //edit or show
    if($ACT == 'edit' or $ACT == 'show') {
        //query for contacts
        act_search($ACT);
        $ACT = act_getcontact($ACT);
    }

    if($ACT == 'new') {
        //query for contacts
        act_search($ACT);
        $ACT = act_new($ACT);
    }

    if($ACT == 'search') {
        //query for contacts
        act_search_and_select($ACT);
        $ACT = act_getcontact($ACT);
    }

    if($ACT == 'img') {
        img_display();
    }

    if($ACT == 'export_vcard') {
        $ACT = act_exportvcard($ACT);
    }

    if($ACT == 'export_vcard_cat') {
        act_search($ACT);  // we need the contactlist
        $ACT = act_exportvcard_cat($ACT);
    }


    //display some infos
    if($ACT == 'debug') {
        html_debug();
        $ACT = 'show';
    }

    //call template FIXME: all needed vars available?
    header('Content-Type: text/html; charset=utf-8');
    include(template('main.tpl'));
}

function act_save($action) {
    global $AB;
    global $ID;
    global $conf;

    $contact = $AB->get($ID);
    if($contact == false) $contact = new person;

    $contact->title = $_REQUEST['title'];
    $contact->firstname = $_REQUEST['firstname'];
    $contact->firstname2 = $_REQUEST['firstname2'];
    $contact->lastname = $_REQUEST['lastname'];
    $contact->suffix = $_REQUEST['suffix'];
    $contact->nickname = $_REQUEST['nickname'];

    $contact->jobtitle = $_REQUEST['jobtitle'];
    $contact->department = $_REQUEST['department'];
    $contact->organization = $_REQUEST['organization'];

    settype($_REQUEST['company'], "boolean");
    $contact->company = $_REQUEST['company'];

    $year = intval(substr($_REQUEST['birthdate'], 0, 4));
    $month = intval(substr($_REQUEST['birthdate'], 5, 2));
    $day = intval(substr($_REQUEST['birthdate'], 8, 2));
    $contact->birthdate = sprintf("%04u-%02u-%02u", $year, $month, $day);

    $contact->homepage = $_REQUEST['homepage'];
    $contact->note = str_replace("\r", "", $_REQUEST['note']);
    
    settype($_REQUEST['photo_delete'], "boolean");
    if($_REQUEST['photo_delete'] == true) {
        $contact->image = NULL;
    } else if(!empty($_FILES['photo_file']['tmp_name'])) {
        //change or add picture
        if(!empty($conf['photo_resize'])) {
            $contact->image = img_convert(@file_get_contents($_FILES['photo_file']['tmp_name']), 'png', '-resize ' . $conf['photo_resize']);
        } else {
            $contact->image = img_convert(@file_get_contents($_FILES['photo_file']['tmp_name']));
        }
    }

    $contact->addresses = array();
    $count = 1;
    while(is_string($_REQUEST['addresslabel'.$count])) {
        $address = array();
        $address['label'] = $_REQUEST['addresslabel'.$count];
        $address['street'] = $_REQUEST['street'.$count];
        $address['zip'] = $_REQUEST['zip'.$count];
        $address['city'] = $_REQUEST['city'.$count];
        $address['state'] = $_REQUEST['state'.$count];
        $address['country'] = $_REQUEST['country'.$count];
        $address['template'] = $_REQUEST['template'.$count];
        $contact->add_address($address);
        $count++;
    }

    $contact->emails = array();
    $count = 1;
    while(is_string($_REQUEST['emaillabel'.$count])) {
        $email = array();
        $email['label'] = $_REQUEST['emaillabel'.$count];
        $email['email'] = $_REQUEST['email'.$count];
        $contact->add_email($email);
        $count++;
    }

    $contact->phones = array();
    $count = 1;
    while(is_string($_REQUEST['phonelabel'.$count])) {
        $phone = array();
        $phone['label'] = $_REQUEST['phonelabel'.$count];
        $phone['phone'] = $_REQUEST['phone'.$count];
        $contact->add_phone($phone);
        $count++;
    }

    $contact->chathandles = array();
    $count = 1;
    while(is_string($_REQUEST['chathandlelabel'.$count])) {
        $chat = array();
        $chat['label'] = $_REQUEST['chathandlelabel'.$count];
        $chat['type'] = $_REQUEST['chathandletype'.$count];
        $chat['handle'] = $_REQUEST['chathandle'.$count];
        $contact->add_chathandle($chat);
        $count++;
    }

    $contact->relatednames = array();
    $count = 1;
    while(is_string($_REQUEST['relatednamelabel'.$count])) {
        $rname = array();
        $rname['label'] = $_REQUEST['relatednamelabel'.$count];
        $rname['name'] = $_REQUEST['relatedname'.$count];
        $contact->add_relatedname($rname);
        $count++;
    }

    $contact->urls = array();
    $count = 1;
    while(is_string($_REQUEST['urllabel'.$count])) {
        $url = array();
        $url['label'] = $_REQUEST['urllabel'.$count];
        $url['url'] = $_REQUEST['url'.$count];
        $contact->add_url($url);
        $count++;
    }

    $AB->set($contact);

    return 'show';
}

function act_getcontact($action) {
    global $AB;
    global $ID;
    global $contact;

    $contact = $AB->get($ID);

    return $action;
}

function act_new($action) {
    global $contact;

    $contact = new person;

    return $action;
}

function act_delete($action) {
    global $ID;
    global $AB;

    $AB->delete($ID);

    return $action;
}

function act_search($action) {
    global $QUERY;
    global $AB;
    global $contactlist;
    global $ID;

    if(empty($QUERY)) {
        $contactlist = $AB->getall();
    } else {
        $contactlist = $AB->find($QUERY);
    }

    return $action;
}

function act_search_and_select($action) {
    global $QUERY;
    global $AB;
    global $contactlist;
    global $ID;

    if(empty($QUERY)) {
        $contactlist = $AB->getall();
    } else {
        $contactlist = $AB->find($QUERY);
        if(!empty($contactlist)) $ID = current($contactlist)->id;
    }

    return $action;
}


?>