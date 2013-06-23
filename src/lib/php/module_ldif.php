<?php
    /**
     * iAddressBook LDIF Export
     *
     * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
     * @author     Clemens Wacha <clemens.wacha@gmx.net>
     */

    if(!defined('AB_BASEDIR')) define('AB_BASEDIR',realpath(dirname(__FILE__).'/../../'));
    require_once(AB_BASEDIR.'/lib/php/include.php');
    require_once(AB_BASEDIR.'/lib/php/addressbook.php');
    require_once(AB_BASEDIR.'/lib/php/category.php');


function act_ldifexport() {
    global $contactlist;
    global $conf;
    global $CAT;
    
    $contacts_selected = array();

    $contents = '';
    
    $contents = ldif_title() ."\n";
    
    foreach($_REQUEST as $key => $value) {
        if(strpos($key, 'ct_') === 0) {
            $contacts_selected[$value] = $contactlist[$value];
        }
    }
    if(count($contacts_selected) == 0) $contacts_selected = $contactlist;

    foreach ($contacts_selected as $contact) {
        //$categories = $CAT->find($contact->id);
        $contents .= contact2ldif($contact) . "\n";
    }
    
    if(!empty($contacts_selected)) {
        // send the ldif file
        $filename = "All Contacts (" . count($contacts_selected) . ")";
        $filename = trim($filename);
        $filename .= ".ldif";
        
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Connection: close");
        header("Content-Type: text/plain; name=\"$filename\"");
        echo $contents;
        exit();    
    }
}

function contact2ldif($contact, $base = 'ou=customers, dc=example, dc=com') {
    global $conf;
    
    $line = array();
    
    if(!empty($conf['ldif_base'])) $base = $conf['ldif_base'];
    
    $workMail    = $contact->get_email('WORK');
    $homeMail    = $contact->get_email('HOME');
    $workPhone   = $contact->get_phone('WORK');
    $homePhone   = $contact->get_phone('HOME');
    $mobile      = $contact->get_phone('CELL');
    $pager       = $contact->get_phone('PAGER');
    $workFax     = $contact->get_phone('WORK FAX');
    $homeFax     = $contact->get_phone('HOME FAX');
    $workUrl     = $contact->get_url('WORK');
    $homeUrl     = $contact->get_url('HOME');
    
    $workAddress = $contact->get_address('WORK');
    $homeAddress = $contact->get_address('HOME');
    
    $moz_custom1 = $contact->get_relatedname('CUSTOM1');
    $moz_custom2 = $contact->get_relatedname('CUSTOM2');
    $moz_custom3 = $contact->get_relatedname('CUSTOM3');
    $moz_custom4 = $contact->get_relatedname('CUSTOM4');
    
    
    $line[] .= rtrim('dn: cn='. ldif_escape($contact->id).', '.$base, ", ");
    $line[] .= 'objectClass: inetOrgPerson';
    $line[] .= 'objectClass: person';
    $line[] .= 'objectClass: top';
    $line[] .= 'cn: '.                                                                      ldif_escape($contact->name());
    $line[] .= 'cn: '.                                                                      ldif_escape($contact->id);
    if(!empty($contact->lastname))           $line[] .= 'sn: '.                             ldif_escape($contact->lastname);
    else                                     $line[] .= 'sn: -';
    if(!empty($contact->firstname))          $line[] .= 'givenName: '.                      ldif_escape($contact->firstname);
    if(!empty($contact->firstname2))         $line[] .= 'initials: '.                       ldif_escape($contact->firstname2);
    if(!empty($contact->jobtitle))           $line[] .= 'title: '.                          ldif_escape($contact->jobtitle);
    if(!empty($contact->department))         $line[] .= 'ou: '.                             ldif_escape($contact->department);
    if(!empty($contact->organization))       $line[] .= 'o: '.                              ldif_escape($contact->organization);
    if($workMail)                            $line[] .= 'mail: '.                           ldif_escape($workMail);
    if($homeMail)                            $line[] .= 'mail: '.                           ldif_escape($homeMail);
    if($workPhone)                           $line[] .= 'telephoneNumber: '.                ldif_escape($workPhone);
    if($homePhone)                           $line[] .= 'homeTelephoneNumber: '.            ldif_escape($homePhone);
    if($mobile)                              $line[] .= 'mobile: '.                         ldif_escape($mobile);
    if($pager)                               $line[] .= 'pager: '.                          ldif_escape($pager);
    if($workFax)                             $line[] .= 'facsimileTelephoneNumber: '.       ldif_escape($workFax);
    //if($homeFax)                             $line[] .= 'otherFacsimileTelephoneNumber: '.  ldif_escape($homeFax);
    if($workUrl)                             $line[] .= 'labeledURI: '.                     ldif_escape($WorkUrl) . "Work";
    if($homeUrl)                             $line[] .= 'labeledURI: '.                     ldif_escape($homeUrl) . "Home";
    if($workAddress) {
        if(!empty($workAddress['street']))   $line[] .= 'street: ' .                        ldif_escape($workAddress['street']);
        if(!empty($workAddress['city']))     $line[] .= 'l: ' .                             ldif_escape($workAddress['city']);
        if(!empty($workAddress['state']))    $line[] .= 'st: ' .                            ldif_escape($workAddress['state']);
        if(!empty($workAddress['zip']))      $line[] .= 'postalCode: ' .                    ldif_escape($workAddress['zip']);
    }
    if($homeAddress) {
        if(!empty($homeAddress['street']))   $line[] .= 'homePostalAddress: ' .             ldif_escape($homeAddress['street']);
    }
    
    
    // Begin Mozilla specific stuff
    if($conf['ldif_mozilla']) {
                                             $line[] .= 'objectClass: mozillaOrgPerson';
                                             $line[] .= 'objectClass: mozillaAddressBookEntry';
        if(!empty($contact->nickname))       $line[] .= 'mozillaNickname: '.                ldif_escape($contact->nickname);
        if($homeAddress) {
            if(!empty($homeAddress['city']))    $line[] .= 'mozillaHomeLocalityName: '.     ldif_escape($homeAddress['city']);
            if(!empty($homeAddress['state']))   $line[] .= 'mozillaHomeState: '.            ldif_escape($homeAddress['state']);
            if(!empty($homeAddress['zip']))     $line[] .= 'mozillaHomePostalCode: '.       ldif_escape($homeAddress['zip']);
            if(!empty($homeAddress['country'])) $line[] .= 'mozillaHomeCountryName: '.      ldif_escape($homeAddress['country']);
        }
        if($workUrl)                         $line[] .= 'mozillaWorkUrl: '.                 ldif_escape($workUrl);
        if($homeUrl)                         $line[] .= 'mozillaHomeUrl: '.                 ldif_escape($homeUrl);

        if($moz_custom1)                     $line[] .= 'mozillaCustom1: '.                 ldif_escape($moz_custom1);
        if($moz_custom2)                     $line[] .= 'mozillaCustom2: '.                 ldif_escape($moz_custom2);
        if($moz_custom3)                     $line[] .= 'mozillaCustom3: '.                 ldif_escape($moz_custom3);
        if($moz_custom4)                     $line[] .= 'mozillaCustom4: '.                 ldif_escape($moz_custom4);

        if($homeMail)                        $line[] .= 'mozillaSecondEmail: '.             ldif_escape($homeMail);    
    }
    // End Mozilla specific stuff
    $line[] .= '';

    return implode("\n", $line);
}

function ldif_title() {
    $tmp = "#version: 1\n";

    return $tmp;
}

function ldif_escape($string) {
    // TODO
    //if(strpos($string, "'") !== false) $string = "'" . real_addcslashes($string, "\\'") . "'";
    return $string;
}

?>