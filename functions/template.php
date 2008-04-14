<?php
/**
 * AddressBook template functions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Clemens Wacha <clemens.wacha@gmx.net>
 */


	if(!defined('AB_INC')) define('AB_INC',realpath(dirname(__FILE__).'/../').'/');
    if(!defined('AB_CONF')) define('AB_CONF',AB_INC.'conf/');
	require_once(AB_CONF.'defaults.php');
    require_once(AB_INC.'functions/html.php');
    require_once(AB_INC.'functions/common.php');


/**
 * Wrapper around htmlspecialchars()
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @see    htmlspecialchars()
 */
function hsc($string){
  return htmlspecialchars($string);
}

/**
 * print a newline terminated string
 *
 * You can give an indention as optional parameter
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function ptln($string,$intend=0){
  for($i=0; $i<$intend; $i++) print ' ';
  print"$string\n";
}

/**
 * Returns the path to the given template, uses
 * default one if the custom version doesn't exist
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function template($tpl){
  global $conf;

  if(@is_readable(AB_INC.'tpl/'.$conf['template'].'/'.$tpl))
    return AB_INC.'tpl/'.$conf['template'].'/'.$tpl;

  return AB_INC.'tpl/default/'.$tpl;
}

function tpl_include($file) {
    global $ID;
    global $ACT;
    global $AB;
    global $QUERY;
    global $contact;
    global $lang;
    global $conf;
    global $CAT;
    global $categories;
    
	include(template($file));
}

function tpl_showcontactlist() {
    global $contactlist;
    global $lang;
    global $conf;
    global $CAT;
    global $categories;

    include(template('contactlist.tpl'));
}

function tpl_showperson() {
    global $ID;
    global $ACT;
    global $AB;
    global $contact;
    global $lang;
    global $conf;
    
    if(is_object($contact)) {
        if($ACT == 'edit' or $ACT == 'new') {
            include(template('person_edit.tpl'));
        } else {
            include(template('person.tpl'));
        }
    } else {
        include(template('person_empty.tpl'));
    }
}

function tpl_contactlist() {
    global $contactlist;
    global $lang;
    global $conf;
	$color = 1;
	
	$contactlist_sorted = array();
	
	foreach($contactlist as $contact) {
		$contactlist_sorted[$contact->name()] = $contact;
	}
	
	ksort($contactlist_sorted);	

	foreach($contactlist_sorted as $contact) {
		include(template('contactlist_item.tpl'));
		$color = 3 - $color;
	}
}

function tpl_addresses($limit = 0) {
    global $ID;
    global $lang;
    global $conf;
    global $contact;
    $color = 1;
    $count = 0;
    
	foreach($contact->addresses as $address) {
        if($limit > 0 and $count >= $limit) break;
		include(template('address.tpl'));
		$color = 3 - $color;
        $count++;
	}
}

function tpl_phones($limit = 0) {
    global $ID;
    global $contact;
    $color = 1;
    $count = 0;
    
	foreach($contact->phones as $phone) {
        if($limit > 0 and $count >= $limit) break;
		include(template('phone.tpl'));
		$color = 3 - $color;
        $count++;
	}
}

function tpl_emails($limit = 0) {
    global $ID;
    global $contact;
    $color = 1;
    $count = 0;
    
	foreach($contact->emails as $email) {
        if($limit > 0 and $count >= $limit) break;
		include(template('email.tpl'));
		$color = 3 - $color;
        $count++;
	}
}

function tpl_chathandles($limit = 0) {
    global $ID;
    global $contact;
    $color = 1;
    $count = 0;
    
	foreach($contact->chathandles as $chathandle) {
        if($limit > 0 and $count >= $limit) break;
		include(template('chathandle.tpl'));
		$color = 3 - $color;
        $count++;
	}
}

function tpl_urls($limit = 0) {
    global $ID;
    global $contact;
    $color = 1;
    $count = 0;
    
	foreach($contact->urls as $url) {
        if($limit > 0 and $count >= $limit) break;
		include(template('url.tpl'));
		$color = 3 - $color;
        $count++;
	}
}

function tpl_relatednames($limit = 0) {
    global $ID;
    global $contact;
    $color = 1;
    $count = 0;
    
	foreach($contact->relatednames as $rname) {
        if($limit > 0 and $count >= $limit) break;
		include(template('relatedname.tpl'));
		$color = 3 - $color;
        $count++;
	}
}

function tpl_label($string) {
    global $lang;
    
    if(strtoupper($string) == 'WORK') return $lang['label_work'];
    if(strtoupper($string) == 'HOME') return $lang['label_home'];

    if(strtoupper($string) == 'CELL') return $lang['label_cell'];
    if(strtoupper($string) == 'PAGER') return $lang['label_pager'];
    if(strtoupper($string) == 'MAIN') return $lang['label_main'];
    if(strtoupper($string) == 'WORK FAX') return $lang['label_workfax'];
    if(strtoupper($string) == 'HOME FAX') return $lang['label_homefax'];
    
    if(strtoupper($string) == 'JABBER') return 'Jabber';
    if(strtoupper($string) == 'YAHOO') return 'Yahoo';
    

    // catch OS X AddressBook specific labels
    preg_match('_\$\!\<(.*)\>\!\$_', $string,  $match);
    if(!empty($match)) {
        $ab_label = strtolower($match[1]);
        return $lang['label_'.$ab_label];
        //echo "<pre>";
        //print_r($match);
        //echo "</pre>";
    }
    if(strtoupper($string) == '_$!<OTHER>!$_') return $lang['label_other'];
    
    return $string;
}

function tpl_categorylist() {
    global $categorylist;
    global $lang;
    global $conf;
	$color = 1;

	foreach($categorylist as $category) {
		include(template('categorylist_item.tpl'));
		$color = 3 - $color;
	}
}

function tpl_selectedcategory() {
    global $CAT;
    global $lang;
    
    $category = $CAT->get($CAT->selected);
    
    if(is_object($category)) return $category->name;
    return $lang['category_all'];
}


?>