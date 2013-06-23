<?php
    /**
     * iAddressBook Actions
     *
     * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
     * @author     Clemens Wacha <clemens.wacha@gmx.net>
     */
    
    if(!defined('AB_BASEDIR')) define('AB_BASEDIR',realpath(dirname(__FILE__).'/../../'));
    require_once(AB_BASEDIR.'/lib/php/include.php');
    require_once(AB_BASEDIR.'/lib/php/addressbook.php');
    require_once(AB_BASEDIR.'/lib/php/category.php');
    require_once(AB_BASEDIR.'/lib/php/template.php');
    require_once(AB_BASEDIR.'/lib/php/image.php');
    require_once(AB_BASEDIR.'/lib/php/common.php');
    require_once(AB_BASEDIR.'/lib/php/module_vcard.php');
    require_once(AB_BASEDIR.'/lib/php/module_csv.php');
    require_once(AB_BASEDIR.'/lib/php/module_ldif.php');
    require_once(AB_BASEDIR.'/lib/php/module_birthday.php');


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
            act_getcontact();
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
        case 'cat_del_empty':
            act_category_del_empty();
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
            @set_time_limit(0);
            act_importvcard_file();
            act_search();
            act_getcontact();
            break;
        case 'import_folder':
            act_importfolder();
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
        
        case 'export_csv_cat':
            act_search();
            act_csvexport();
            break;

        case 'export_ldif_cat':
            act_search();
            act_ldifexport();
            break;
        
        case 'img':
            img_display();
            break;
        
        case 'debug':
            html_debug();
            act_search();
            act_getcontact();
            break;

        case 'info':
            html_phpinfo();
            break;
        
        case 'check':
            act_check();
        
        case 'edit':
        case 'select_offset':
        case 'select_letter':
        case 'show':
        default:
            act_search();
            act_getcontact();
    }
    
    act_hsc_everything();
    
    //call template FIXME: all needed vars available?
    header('Content-Type: text/html; charset=utf-8');
    tpl_include('main.tpl');
}

function act_check() {
    global $VERSION;
    global $conf;

/*
    echo '<pre style="text-align: left;">';
    print_r($GLOBALS);
    echo '</pre>';
*/
    msg("PHP iAddressBook Version: ". $VERSION, 1);
    msg("PHP version ". phpversion(), 1);
    
    if(!is_writable(AB_IMAGEDIR)) {
        msg("Cannot use contact photos: No write permission to ".AB_IMAGEDIR, -1);
    } else {
        msg("Photo folder is writeable", 1);
    }

    if(!is_writable(AB_IMPORTDIR)) {
        msg("Cannot delete vCards from import folder: No write permission to ".AB_IMPORTDIR, -1);
    } else {
        msg("vCard import folder is writeable", 1);
    }

    if(!is_readable(AB_CONFDIR."/config.php")) {
        msg("Cannot read configuration ".AB_CONFDIR."/config.php", -1);
    } else {
        msg("Configuration ".AB_CONFDIR."/config.php is readable", 1);
    }

    if($conf['auth_enabled']) {
        if(!is_readable(AB_CONFDIR."/auth.php")) {
            msg("Cannot read authorizations in ".AB_CONFDIR."/auth.php", -1);
        } else {
            msg("Authorization in ".AB_CONFDIR."/auth.php is readable", 1);
        }
    } else {
        if(!is_readable(AB_CONFDIR."/auth.php")) {
            msg("Cannot read authorizations in ".AB_CONFDIR."/auth.php");
        }
    }
    
    $gd = gd_info();
    if(!empty($conf['im_convert'])) {
        if(!is_readable($conf['im_convert'])) {
            msg("Cannot find ImageMagick's convert at ". $conf['im_convert'].". Using GD to convert photos", -1);
            msg("GD version ".$gd['GD Version'], 1);
        } else {
            msg("ImageMagick's convert found. Using ImageMagick to convert photos", 1);
        }
    } else {
        msg("GD version ".$gd['GD Version'], 1);
    }

    if($conf['debug']) {
        msg("Debugging support enabled");
    } else {
        msg("Debugging support disabled", 1);
    }
    
    if($conf['debug_db']) {
        msg("Database debugging support enabled");
    } else {
        msg("Database debugging support disabled", 1);
    }
    
    //TODO: add database checks
}

function act_save() {
    global $AB;
    global $ID;
    global $conf;
    global $CAT;
    global $contact_categories;

    // load contact with image
    $contact = $AB->get($ID, true);
    if($contact == false) $contact = new person;

    $contact->title         = real_br2nl($_REQUEST['title']);
    $contact->firstname     = real_br2nl($_REQUEST['firstname']);
    $contact->firstname2    = real_br2nl($_REQUEST['firstname2']);
    $contact->lastname      = real_br2nl($_REQUEST['lastname']);
    $contact->suffix        = real_br2nl($_REQUEST['suffix']);
    $contact->nickname      = real_br2nl($_REQUEST['nickname']);
    $contact->phoneticfirstname = real_br2nl($_REQUEST['phoneticfirstname']);
    $contact->phoneticlastname = real_br2nl($_REQUEST['phoneticlastname']);

    $contact->jobtitle      = real_br2nl($_REQUEST['jobtitle']);
    $contact->department    = real_br2nl($_REQUEST['department']);
    $contact->organization  = real_br2nl($_REQUEST['organization']);

    if($_REQUEST['company'] == true) {
        $contact->company       = 1;
    } else {
        $contact->company       = 0;
    }

    $year = intval(substr($_REQUEST['birthdate'], 0, 4));
    $month = intval(substr($_REQUEST['birthdate'], 5, 2));
    $day = intval(substr($_REQUEST['birthdate'], 8, 2));
    $contact->birthdate = sprintf("%04u-%02u-%02u", $year, $month, $day);

    $contact->homepage      = real_br2nl($_REQUEST['homepage']);
    $contact->note          = str_replace("\r", "", $_REQUEST['note']);
    
    settype($_REQUEST['photo_delete'], "boolean");
    if($_REQUEST['photo_delete'] == true) {
        $contact->image = NULL;
    } else if($conf['photo_enable'] && !empty($_FILES['photo_file']['tmp_name']) ) {
        //change or add picture
        $contact->image = @file_get_contents($_FILES['photo_file']['tmp_name']);
    }
    
    $contact->addresses = array();
    $contact->emails = array();
    $contact->phones = array();
    $contact->chathandles = array();
    $contact->relatednames = array();
    $contact->urls = array();

    foreach($_REQUEST as $key => $web_value) {
        list($web_param, $web_id) = explode("_", $key);
        
        //msg("$web_param=$web_value ($web_id)");
        
        if($web_param == 'addresslabel') {
            $address = array();
            $address['label'] = $_REQUEST['addresslabel_'.$web_id];
            $address['street'] = real_br2nl($_REQUEST['street_'.$web_id]);
            $address['zip'] = real_br2nl($_REQUEST['zip_'.$web_id]);
            $address['city'] = real_br2nl($_REQUEST['city_'.$web_id]);
            $address['state'] = real_br2nl($_REQUEST['state_'.$web_id]);
            $address['country'] = real_br2nl($_REQUEST['country_'.$web_id]);
            $address['template'] = real_br2nl($_REQUEST['template_'.$web_id]);
            $contact->add_address($address);
        }

        if($web_param == 'emaillabel') {
            $email = array();
            $email['label'] = $_REQUEST['emaillabel_'.$web_id];
            $email['email'] = real_br2nl($_REQUEST['email_'.$web_id]);
            $contact->add_email($email);
        }

        if($web_param == 'phonelabel') {
            $phone = array();
            $phone['label'] = $_REQUEST['phonelabel_'.$web_id];
            $phone['phone'] = real_br2nl($_REQUEST['phone_'.$web_id]);
            $contact->add_phone($phone);
        }
        
        if($web_param == 'chathandlelabel') {
            $chat = array();
            $chat['label'] = $_REQUEST['chathandlelabel_'.$web_id];
            $chat['type'] = real_br2nl($_REQUEST['chathandletype_'.$web_id]);
            $chat['handle'] = real_br2nl($_REQUEST['chathandle_'.$web_id]);
            $contact->add_chathandle($chat);
        }

        if($web_param == 'relatednamelabel') {
            $rname = array();
            $rname['label'] = $_REQUEST['relatednamelabel_'.$web_id];
            $rname['name'] = real_br2nl($_REQUEST['relatedname_'.$web_id]);
            $contact->add_relatedname($rname);
        }

        if($web_param == 'urllabel') {
            $url = array();
            $url['label'] = $_REQUEST['urllabel_'.$web_id];
            $url['url'] = real_br2nl($_REQUEST['url_'.$web_id]);
            $contact->add_url($url);
        }
    }

    $person_id = $AB->set($contact);

    if($person_id == false) return;

    //since the contact is new or has changed, add it to the "changed" category!
    if($conf['mark_changed']) {
        $changed_id = 0;
        
        $changed_name = ' __changed__';
        $category = $CAT->exists($changed_name);
        if(is_object($category)) {
            $changed_id = $category->id;
        } else {
            $category = new category;
            $category->name = $changed_name;
            $changed_id = $CAT->set($category);
        }
        
        // add to changed category
        $CAT->add_contact($person_id, $changed_id);
    }
    
    //add contact to the categories
    
    //first create array of old category ids
    $old_cat = array();
    foreach($contact_categories as $category) {
        array_push($old_cat, $category->id);
    }
    
    // now create array of new category ids
    $new_cat = array();
    $new_cat_string = str_replace("\r", "", $_REQUEST['category']);
    $new_cat_names = explode("\n", $new_cat_string);
    foreach($new_cat_names as $name) {
        $category = new category;
        $category->name = trim($name);
        $category->type = 0;
        
        if(!empty($category->name)) {
            $ret = $CAT->exists($category->name);
            if($ret === false) {
                // new category, create it and add contact to category
                $id = $CAT->set($category);
                $CAT->add_contact($person_id, $id);
            } else {
                // category exists; add contact if necessary
                $CAT->add_contact($person_id, $ret->id);
                array_push($new_cat, $ret->id);
            }
        }
    }
    
    // calculate removed categories
    foreach($old_cat as $id) {
        $key = array_search($id, $new_cat);
        if($key === false) {
            // remove contact from category
            $CAT->delete_contact($person_id, $id);
        }
    }
}

function act_getcontact() {
    global $AB;
    global $ID;
    global $CAT;
    global $contact;
    global $contact_categories;

    $contact = $AB->get($ID);

    if($contact) $contact_categories = $CAT->find($contact->id);
    $contact_categories = $CAT->sort($contact_categories);
}

function act_new() {
    global $contact;

    $contact = new person;

}

function act_delete() {
    global $ID;
    global $AB;
    global $CAT;
    global $contact_categories;

    $contact_categories = $CAT->find($ID);
    foreach($contact_categories as $category) {
        $CAT->delete_contact($ID, $category->id);
    }
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
    global $contactlist_letter;
    global $ID;
    global $CAT;
    global $categories;
    global $lang;

    if(!isset($QUERY)) {
        $contactlist = $AB->getall();
    } else {
        $contactlist = $AB->find($QUERY);
    }

    // load sort rules
    $sort_rules_from = explode(',', $lang['sort_rules_from']);
    $sort_rules_to   = explode(',', $lang['sort_rules_to']);

    if(isset($contactlist_letter) and $contactlist_letter != 'A-Z') {
        $contactlist_letter = strtoupper($contactlist_letter{0});
        if($contactlist_letter == '#') {
            foreach($contactlist as $key => $value) {
                $name = str_replace($sort_rules_from, $sort_rules_to, strtoupper(substr($value->name(), 0, 4)));
                if( substr($name,0,1) >= 'A') {
                    unset($contactlist[$key]);
                }
            }
        } else if($contactlist_letter == 'Z') {
            foreach($contactlist as $key => $value) {
                $name = str_replace($sort_rules_from, $sort_rules_to, strtoupper(substr($value->name(), 0, 4)));
                if( substr($name,0,1) < 'Z') {
                    unset($contactlist[$key]);
                }
            }        
        } else {
            foreach($contactlist as $key => $value) {
                $name = str_replace($sort_rules_from, $sort_rules_to, strtoupper(substr($value->name(), 0, 4)));
                if( substr($name,0,1) != $contactlist_letter) {
                    unset($contactlist[$key]);
                }
            }
        }
    }
    
    // sort the list
    $contactlist = $AB->sort($contactlist);
    
    $categories = $CAT->getall();
    $categories = $CAT->sort($categories);
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
    global $CAT_ID;
    
    $CAT->delete($CAT_ID);
    $CAT_ID = 0;
}

function act_category_del_empty() {
    global $CAT;
    global $CAT_ID;
    global $AB;
    
    $categories = $CAT->getall();
    
	$i = 0;
	
    foreach($categories as $category) {
        $CAT_ID = $category->id;
        $contacts = $AB->getall();
        
        if(count($contacts) == 0) {
            // remove the category
            msg("Deleting empty category: ". $category->name);
            $CAT->delete($CAT_ID);
			$i++;
        }
    }
    $CAT_ID = 0;
	
	msg("$i categories deleted.");
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
    global $contact_categories;
    global $contactlist;
    global $categories;
    global $CAT_ID;

    if(is_object($contact)) $contact->html_escape();
    foreach($contact_categories as $tmp) $tmp->html_escape();
    foreach($contactlist as $tmp) $tmp->html_escape();
    foreach($categories as $tmp) $tmp->html_escape();

    $ID = hsc($ID);
    $ACT = hsc($ACT);
    $QUERY = hsc($QUERY);
    $CAT_ID = hsc($CAT_ID);
}

/*
function act_db_ex() {
    global $contactlist;
    global $db;
    
    echo "<pre style='text-align: left;'>";
    foreach($contactlist as $contact) {
        if(!empty($contact->emails)) {
            $name  = $db->Quote($contact->name());
            $email = $db->Quote($contact->emails[0]['email']);
            $first = $db->Quote($contact->firstname);
            $last  = $db->Quote($contact->lastname);
            echo "INSERT INTO contacts (user_id, changed, del, name, email, firstname, surname ) VALUES (2, '2006-09-03 23:30:00', 0, $name, $email, $first, $last ) ; \n";
        }    
    }
    echo "</pre>";
    exit();
}
*/

?>