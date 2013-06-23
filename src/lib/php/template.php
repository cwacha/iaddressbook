<?php
    /**
     * iAddressBook template functions
     *
     * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
     * @author     Clemens Wacha <clemens.wacha@gmx.net>
     */

    if(!defined('AB_BASEDIR')) define('AB_BASEDIR',realpath(dirname(__FILE__).'/../../'));
    require_once(AB_BASEDIR.'/lib/php/include.php');
    require_once(AB_BASEDIR.'/lib/php/html.php');
    require_once(AB_BASEDIR.'/lib/php/common.php');


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

    if(@is_readable(AB_TPLDIR.'/'.$conf['template'].'/'.$tpl))
        return AB_TPLDIR.'/'.$conf['template'].'/'.$tpl;

    return AB_TPLDIR.'/default/'.$tpl;
}

function tpl_include($file) {
    global $ID;
    global $ACT;
    global $AB;
    global $QUERY;
    global $contact;
    global $lang;
    global $conf;
    global $userinfo;
    global $CAT;
    global $CAT_ID;
    global $categories;

    include(template($file));
}

function tpl_showcontactlist() {
    global $contactlist;
    global $contactlist_offset;
    global $contactlist_limit;
    global $contactlist_letter;
    global $lang;
    global $conf;
    global $CAT;
    global $CAT_ID;
    global $categories;

    include(template('contactlist.tpl'));
}

function tpl_showperson() {
    global $ID;
    global $ACT;
    global $AB;
    global $CAT;
    global $CAT_ID;
    global $contact;
    global $contact_categories;
    global $lang;
    global $conf;
    
    $categories = $contact_categories;
    
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
    global $contactlist_offset;
    global $contactlist_limit;
    global $contactlist_letter;
    global $lang;
    global $conf;
    $color = 1;
    $i = 0;
    

    foreach($contactlist as $contact) {
        $i++;
        if($i <= $contactlist_offset) continue;
        if($contactlist_limit > 0 and ($i > $contactlist_offset + $contactlist_limit)) continue;
        include(template('contactlist_item.tpl'));
        $color = 3 - $color;
    }
    
    if(count($contactlist) < 1) {
        include(template('contactlist_empty.tpl'));
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
    if(strtoupper($string) == 'SKYPE') return 'Skype';
    if(strtoupper($string) == 'YAHOO') return 'Yahoo';
    
    if(strtoupper($string) == 'CUSTOM') return $lang['label_custom'];

    // catch OS X AddressBook specific labels
    preg_match('_\$\!\<(.*)\>\!\$_', $string,  $match);
    if(!empty($match)) {
        $ab_label = strtolower($match[1]);
        return $lang['label_'.$ab_label];
        //echo "<pre>";
        //print_r($match);
        //echo "</pre>";
    }
//    if(strtoupper($string) == '_$!<OTHER>!$_') return $lang['label_other'];  // cwacha: was hani da füren seich gmacht? scho im preg_match, oder?
    
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
    global $CAT_ID;
    global $lang;
    
    $category = $CAT->get($CAT_ID);
    
    if(is_object($category)) return $category->name;
    return $lang['category_all'];
}


function tpl_catselect() {
?>
            <form method="post" action="<?php echo $PHP_SELF ?>" >
                <input type="hidden" name="do" value="cat_select" />
                <select name="cat_id" size="1" onChange="submit()" >
                    <?php
                        global $categories;
                        global $CAT_ID;
                        
                        foreach($categories as $category) {
                            $category->id == $CAT_ID? $sel = 'selected' : $sel = '';
                            echo "<option value='".$category->id."' $sel >".$category->name."</option> \n";
                        }
                    ?>
                </select>
            </form>
<?php
}

function tpl_abc() {
    global $contactlist_letter;
    
    $cll = 'A-Z';
    if($contactlist_letter) $cll = $contactlist_letter;
    
    $abc = array('#', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'A-Z');
    foreach($abc as $l) {
        if($cll == $l)
            echo "<u><a href=\"javascript:select_l('$l')\" > $l </a></u>|";
        else
            echo "<a href=\"javascript:select_l('$l')\" > $l </a>|";
    }
}

function tpl_pageselect() {
    global $contactlist;
    global $contactlist_limit;
    global $contactlist_offset;
    
    $size = count($contactlist);
    if($contactlist_limit > 0 and $size > $contactlist_limit) {
        for($i = 0; $i < $size; $i += $contactlist_limit) {
            $stop = $i + $contactlist_limit;
            if($stop > $size) $stop = $size;
            if($i >= $contactlist_offset and $i < $contactlist_offset + $contactlist_limit) {
                echo "| ". (string)($i+1) ." - $stop \n";
            } else {
                echo "| <a href=\"javascript:select_o('$i')\" >". (string)($i+1) ." - $stop</a> \n";
            }
        }
        echo "|";
    }
}

?>
