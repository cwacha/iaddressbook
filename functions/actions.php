<?php
/**
 * AddressBook Actions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Clemens Wacha <clemens.wacha@gmx.net>
 */

    if(!defined('AB_INC')) define('AB_INC',realpath(dirname(__FILE__).'/../').'/');
	require_once(AB_INC.'functions/addressbook.php');
	require_once(AB_INC.'functions/category.php');
	require_once(AB_INC.'functions/template.php');
	require_once(AB_INC.'functions/image.php');
	require_once(AB_INC.'functions/module_vcard.php');
	require_once(AB_INC.'functions/common.php');


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

    switch($ACT) {
        
        case 'search':
            if(empty($QUERY)) {
                act_search();
            } else {
                act_search_and_select();
            }
            act_getcontact();
            break;
        case 'new':
            act_search();
            act_new();
            break;
        case 'save':
            act_save();
            act_search();
            act_getcontact();
            break;
        case 'delete':
            act_delete();
            act_search();
            break;
        case 'delete_many':
            act_delete_many();
            act_search();
           break;
        
        case 'cat_select':
            act_search_and_select();
            act_getcontact();
            break;
        case 'cat_add':
            act_category_add();
            act_search_and_select();
            act_getcontact();
            break;
        case 'cat_del':
            act_category_del();
            act_search_and_select();
            act_getcontact();
            break;
        case 'cat_add_contacts':
            act_category_addcontacts();
            act_search();
            act_getcontact();
            break;
        case 'cat_del_contacts':
            act_category_delcontacts();
            act_search();
            act_getcontact();
            break;
        
        case 'import_vcard':
            set_time_limit(0);
            act_importvcard();
            act_search();
            act_getcontact();
            break;
        case 'export_vcard':
            act_exportvcard();
            break;
        case 'export_vcard_cat':
            act_search();  // we need the contactlist
            act_exportvcard_cat();
            break;
        
        case 'img':
            img_display();
            break;
        
        case 'debug':
            html_debug();
            act_search();
            act_getcontact();
            break;
        
        case 'edit':
        case 'show':
        default:
            act_search();
            act_getcontact();
    }
    
    act_hsc_everything();
    
    //call template FIXME: all needed vars available?
    header('Content-Type: text/html; charset=utf-8');
    include(template('main.tpl'));
}

function act_save() {
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

}

function act_getcontact() {
    global $AB;
    global $ID;
    global $contact;

    $contact = $AB->get($ID);

}

function act_new() {
    global $contact;

    $contact = new person;

}

function act_delete() {
    global $ID;
    global $AB;

    $AB->delete($ID);

}

function act_delete_many() {
    global $AB;

    foreach($_REQUEST as $key => $value) {
        if(strpos($key, 'ct_') === 0) {
            $AB->delete($value);
        }
    }

}

function act_search() {
    global $QUERY;
    global $AB;
    global $contactlist;
    global $ID;
    global $CAT;
    global $categories;

    if(empty($QUERY)) {
        $contactlist = $AB->getall();
    } else {
        $contactlist = $AB->find($QUERY);
    }
    $categories = $CAT->getall();
    
}

function act_search_and_select() {
    global $contactlist;
    global $ID;

    act_search();
    $contact = reset($contactlist);
    if($contact) $ID = $contact->id;
}

function act_category_add() {
    global $CAT;
    
    $category = new category;
    $category->name = trim($_REQUEST['cat_name']);
    //$category->type = $REQUEST['cat_type'];
    $category->type = 0; //set to manual: TODO smart queries
    
	if(empty($category->name)) return;
	
	if($CAT->exists($category->name) === false) $CAT->set($category);
}

function act_category_del() {
    global $CAT;
    $cat_id = (int)trim($_REQUEST['cat_id']);
    
    $CAT->delete($cat_id);

}

function act_category_addcontacts() {
    global $CAT;
    $cat_id = (int)trim($_REQUEST['cat_id']);

    foreach($_REQUEST as $key => $value) {
        if(strpos($key, 'ct_') === 0) {
            $CAT->add_contact($value, $cat_id);
        }
    }

}

function act_category_delcontacts() {
    global $CAT;
    $cat_id = (int)trim($_REQUEST['cat_id']);

    foreach($_REQUEST as $key => $value) {
        if(strpos($key, 'ct_') === 0) {
            $CAT->delete_contact($value, $cat_id);
        }
    }

}

function act_hsc_everything() {
    global $ID;
    global $ACT;
    global $QUERY;
    global $contact;
    global $contactlist;
    global $categories;

    if(is_object($contact)) $contact->html_escape();
    foreach($contactlist as $tmp) $tmp->html_escape();
    foreach($categories as $tmp) $tmp->html_escape();

    $ID = hsc($ID);
    $ACT = hsc($ACT);
    $QUERY = hsc($QUERY);
    $CAT_ID = hsc($CAT_ID);
}


?>