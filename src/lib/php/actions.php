<?php
    /**
     * iAddressBook Actions
     *
     * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
     * @author     Clemens Wacha <clemens.wacha@gmx.net>
     */

    require_once(AB_BASEDIR.'/lib/php/addressbook.php');
    require_once(AB_BASEDIR.'/lib/php/category.php');
    require_once(AB_BASEDIR.'/lib/php/template.php');
    require_once(AB_BASEDIR.'/lib/php/image.php');
    require_once(AB_BASEDIR.'/lib/php/module_vcard.php');
    require_once(AB_BASEDIR.'/lib/php/module_csv.php');
    require_once(AB_BASEDIR.'/lib/php/module_ldif.php');
    require_once(AB_BASEDIR.'/lib/php/module_birthday.php');
	
/**
 * Call the needed action handlers
 *
 * @author Clemens Wacha <clemens.wacha@gmx.net>
 */
function act_dispatch($request, $action) {
    global $QUERY;
    global $ID;
    global $lang;
    global $conf;

  //msg("test info");
  //msg("test success", 1);
  //msg("test error", -1);

    switch($action) {
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
            act_importvcard_folder();
            act_search();
            act_getcontact();
            break;
        case 'export_vcard':
            act_exportvcard();
            break;
        case 'export_vcard_cat':
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

        case 'config_save':
            act_config_save($request);
            $_SESSION['viewname'] = '/admin';
            break;
        
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
    //header('Content-Type: text/html; charset=utf-8');
    //tpl_include('main.tpl');
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

    // load contact with image
    $contact = $AB->get($ID, true);
    if($contact == false) $contact = new Person();

    $contact->title         = real_br2nl(array_get($_REQUEST, 'title'));
    $contact->firstname     = real_br2nl(array_get($_REQUEST, 'firstname'));
    $contact->firstname2    = real_br2nl(array_get($_REQUEST, 'firstname2'));
    $contact->lastname      = real_br2nl(array_get($_REQUEST, 'lastname'));
    $contact->suffix        = real_br2nl(array_get($_REQUEST, 'suffix'));
    $contact->nickname      = real_br2nl(array_get($_REQUEST, 'nickname'));
    $contact->phoneticfirstname = real_br2nl(array_get($_REQUEST, 'phoneticfirstname'));
    $contact->phoneticlastname = real_br2nl(array_get($_REQUEST, 'phoneticlastname'));

    $contact->jobtitle      = real_br2nl(array_get($_REQUEST, 'jobtitle'));
    $contact->department    = real_br2nl(array_get($_REQUEST, 'department'));
    $contact->organization  = real_br2nl(array_get($_REQUEST, 'organization'));

    if(array_get($_REQUEST, 'company') == true) {
        $contact->company       = 1;
    } else {
        $contact->company       = 0;
    }

    $birthdate = array_get($_REQUEST, 'birthdate', '');
    if(strlen($birthdate) == 10) {
        $year = intval(substr($birthdate, 0, 4));
        $month = intval(substr($birthdate, 5, 2));
        $day = intval(substr($birthdate, 8, 2));
        $contact->birthdate = sprintf("%04u-%02u-%02u", $year, $month, $day);
    } else {
        $contact->birthdate = '';
    }

    $contact->homepage      = real_br2nl(array_get($_REQUEST, 'homepage'));
    $contact->note          = str_replace("\r", "", array_get($_REQUEST, 'note'));
    
    $photodelete = array_get($_REQUEST, 'photo_delete', false);
    settype($photodelete, "boolean");
    if($photodelete == true) {
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
        list($web_param, $web_id) = explode("_", $key . "_");
        
        //msg("$web_param=$web_value ($web_id)");
        
        if($web_param == 'addresslabel') {
            $address = array();
            $address['label'] = array_get($_REQUEST, 'addresslabel_'.$web_id);
            $address['street'] = real_br2nl(array_get($_REQUEST, 'street_'.$web_id));
            $address['zip'] = real_br2nl(array_get($_REQUEST, 'zip_'.$web_id));
            $address['city'] = real_br2nl(array_get($_REQUEST, 'city_'.$web_id));
            $address['state'] = real_br2nl(array_get($_REQUEST, 'state_'.$web_id));
            $address['country'] = real_br2nl(array_get($_REQUEST, 'country_'.$web_id));
            $address['template'] = real_br2nl(array_get($_REQUEST, 'template_'.$web_id));
            $contact->add_address($address);
        }

        if($web_param == 'emaillabel') {
            $email = array();
            $email['label'] = array_get($_REQUEST, 'emaillabel_'.$web_id);
            $email['email'] = real_br2nl(array_get($_REQUEST, 'email_'.$web_id));
            $contact->add_email($email);
        }

        if($web_param == 'phonelabel') {
            $phone = array();
            $phone['label'] = array_get($_REQUEST, 'phonelabel_'.$web_id);
            $phone['phone'] = real_br2nl(array_get($_REQUEST, 'phone_'.$web_id));
            $contact->add_phone($phone);
        }
        
        if($web_param == 'chathandlelabel') {
            $chat = array();
            $chat['label'] = array_get($_REQUEST, 'chathandlelabel_'.$web_id);
            $chat['type'] = real_br2nl(array_get($_REQUEST, 'chathandletype_'.$web_id));
            $chat['handle'] = real_br2nl(array_get($_REQUEST, 'chathandle_'.$web_id));
            $contact->add_chathandle($chat);
        }

        if($web_param == 'relatednamelabel') {
            $rname = array();
            $rname['label'] = array_get($_REQUEST, 'relatednamelabel_'.$web_id);
            $rname['name'] = real_br2nl(array_get($_REQUEST, 'relatedname_'.$web_id));
            $contact->add_relatedname($rname);
        }

        if($web_param == 'urllabel') {
            $url = array();
            $url['label'] = array_get($_REQUEST, 'urllabel_'.$web_id);
            $url['url'] = real_br2nl(array_get($_REQUEST, 'url_'.$web_id));
            $contact->add_url($url);
        }
    }

    $contact->clear_categories();
    $new_categories = explode("\n", str_replace("\r", "", array_get($_REQUEST, 'category')));
    foreach($new_categories as $name) {
    	if( trim($name) === "")
    		continue;
    	$contact->add_category(new Category($name));
    }
    
    $person_id = $AB->set($contact);
}

function act_getcontact() {
    global $AB;
    global $ID;
    global $CAT;
    global $contact;

    $contact = $AB->get($ID);
    if($contact)
	    $contact->sort_categories();
}

function act_new() {
    global $contact;

    $contact = new Person;

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
            $AB->delete((int)$value);
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
    
    $categories = $CAT->getAllCategories();
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
    
    $categoryName = trim(array_get($_REQUEST, 'cat_name'));
    $category = new Category($categoryName);
    
    $CAT->set($category);
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
    
    $categories = $CAT->getAllCategories();
    
	$i = 0;
	
    foreach($categories as $category) {
        $contacts = $AB->getall();
        
        if(count($contacts) == 0) {
            // remove the category
            msg("Deleting empty category: ". $category->displayName());
            $CAT->delete($category->id);
			$i++;
        }
    }
    $CAT_ID = 0;
	
	msg("$i categories deleted.");
}

function act_category_addcontacts() {
	global $CAT;
	$cat_id = ( int ) trim($_REQUEST ['cat_id']);
	$category = $CAT->get($cat_id);
	if (!$category)
		return;
	
	$members = array ();
	foreach ( $_REQUEST as $key => $value ) {
		if (strpos($key, 'ct_') === 0) {
			$members [] = ( int ) $value;
		}
	}
	$CAT->addMembersToCategory($category->id, $members);
}

function act_category_delcontacts() {
	global $CAT;
    global $CAT_ID;

    $cat_id = $CAT_ID;
	$category = $CAT->get($cat_id);
	if (!$category)
		return;
	
	$members = array ();
	foreach ( $_REQUEST as $key => $value ) {
		if (strpos($key, 'ct_') === 0) {
			$members [] = ( int ) $value;
		}
	}
	$CAT->deleteMembersFromCategory($category->id, $members);
}

function act_config_save($request) {
    global $conf;
    global $defaults;

    $config = array();
    $config['fmode']             = (int)array_get($request, 'fmode', $defaults['fmode']);
    $config['dmode']             = (int)array_get($request, 'dmode', $defaults['dmode']);
    $config['basedir']           = array_get($request, 'basedir', $defaults['basedir']);
    $config['baseurl']           = array_get($request, 'baseurl', $defaults['baseurl']);

    $config['dbtype']            = array_get($request, 'dbtype', $conf['dbtype']);
    $config['dbname']            = array_get($request, 'dbname', $conf['dbname']);
    $config['dbserver']          = array_get($request, 'dbserver', $conf['dbserver']);
    $config['dbuser']            = array_get($request, 'dbuser', $conf['dbuser']);
    $config['dbpass']            = array_get($request, 'dbpass', $conf['dbpass']);
    $config['dbtable_abs']       = array_get($request, 'dbtable_abs', $conf['dbtable_abs']);
    $config['dbtable_ab']        = array_get($request, 'dbtable_ab', $conf['dbtable_ab']);
    $config['dbtable_cat']       = array_get($request, 'dbtable_cat', $conf['dbtable_cat']);
    $config['dbtable_catmap']    = array_get($request, 'dbtable_catmap', $conf['dbtable_catmap']);

    $config['lang']              = array_get($request, 'lang', $defaults['lang']);
    $config['title']             = array_get($request, 'title', $defaults['title']);
    $config['template']          = array_get($request, 'template', $defaults['template']);
    $config['bdformat']          = array_get($request, 'bdformat', $defaults['bdformat']);
    $config['dformat']           = array_get($request, 'dformat', $defaults['dformat']);
    $config['lastfirst']         = (int)array_get($request, 'lastfirst', 0);
    $config['photo_resize']      = array_get($request, 'photo_resize', $defaults['photo_resize']);
    $config['photo_size']        = array_get($request, 'photo_size', $defaults['photo_size']);
    $config['photo_format']      = array_get($request, 'photo_format', $defaults['photo_format']);
    $config['map_link']          = array_get($request, 'map_link', $defaults['map_link']);
    $config['contactlist_limit'] = (int)array_get($request, 'contactlist_limit', $defaults['contactlist_limit']);
    $config['contactlist_abc']   = (int)array_get($request, 'contactlist_abc', 0);
    $config['bday_advance_week'] = (int)array_get($request, 'bday_advance_week', $defaults['bday_advance_week']);

    $config['canonical']         = (int)array_get($request, 'canonical', 0);
    $config['auth_enabled']      = (int)array_get($request, 'auth_enabled', 0);
    $config['auth_allow_guest']  = (int)array_get($request, 'auth_allow_guest', 0);
    $config['im_convert']        = array_get($request, 'im_convert', $defaults['im_convert']);
    $config['photo_enable']      = (int)array_get($request, 'photo_enable', 0);
    $config['session_name']      = array_get($request, 'session_name', $defaults['session_name']);
    $config['session_lifetime_min'] = array_get($request, 'session_lifetime_min', $defaults['session_lifetime_min']);
    $config['mark_changed']      = (int)array_get($request, 'mark_changed', 0);

    $config['debug']             = (int)array_get($request, 'debug', 0);
    $config['debug_db']          = (int)array_get($request, 'debug_db', 0);

    $config['vcard_fb_enc']      = array_get($request, 'vcard_fb_enc', $defaults['vcard_fb_enc']);
    $config['ldif_base']         = array_get($request, 'ldif_base', $defaults['ldif_base']);
    $config['ldif_mozilla']      = (int)array_get($request, 'ldif_mozilla', 0);
    $config['xmlrpc_enable']     = (int)array_get($request, 'xmlrpc_enable', 0);
    $config['carddav_enable']    = (int)array_get($request, 'carddav_enable', 0);

    $filename = AB_CONFDIR.'/config.php';

    $new_config = array();
    foreach($config as $key => $value) {
        if(!array_key_exists($key, $defaults) || $config[$key] != $defaults[$key]) {
            $new_config[$key] = $value;
        }
    }
    if(empty($new_config)) {
        unlink($filename);
        return true;
    }

    $header = array();
    $header[] = "/**";
    $header[] = " * This is the AddressBook's config file";
    $header[] = " * This is a piece of PHP code so PHP syntax applies!";
    $header[] = " *";
    $header[] = " * Automatically generated file. Do not modify!";
    $header[] = " */\n\n";

    $fd = fopen($filename, "w");
    if(!$fd) {
        msg("Error writing $type.php to file $filename", -1);
        return false;
    }
    fwrite($fd, "<?php\n\n");
    
    foreach($header as $line) {
        fwrite($fd, $line . "\n");
    }

    $data = array_to_text($new_config, '$conf');
    fwrite($fd, $data);

    fwrite($fd, "\n\n?>");
    fclose($fd);
    fix_fmode($filename);

    return true;
}

function act_hsc_everything() {
    global $ID;
    global $ACT;
    global $QUERY;
    global $contact;
    global $contactlist;
    global $categories;
    global $CAT_ID;

    if(is_object($contact)) $contact->html_escape();
    foreach($contactlist as $tmp) $tmp->html_escape();
    foreach($categories as $tmp) $tmp->html_escape();

    $ID = hsc($ID);
    $ACT = hsc($ACT);
    $QUERY = hsc($QUERY);
    $CAT_ID = hsc($CAT_ID);
}

?>