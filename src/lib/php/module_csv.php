<?php
    /**
     * iAddressBook CSV Export
     *
     * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
     * @author     Clemens Wacha <clemens.wacha@gmx.net>
     */

    if(!defined('AB_BASEDIR')) define('AB_BASEDIR',realpath(dirname(__FILE__).'/../../'));
    require_once(AB_BASEDIR.'/lib/php/include.php');
    require_once(AB_BASEDIR.'/lib/php/addressbook.php');
    require_once(AB_BASEDIR.'/lib/php/category.php');


function act_csvexport() {
    global $contactlist;
    global $conf;
    global $CAT;
    
    $contacts_selected = array();

    $contents = '';
    
    $contents = csv_title() ."\n";
    
    foreach($_REQUEST as $key => $value) {
        if(strpos($key, 'ct_') === 0) {
            $contacts_selected[$value] = $contactlist[$value];
        }
    }
    if(count($contacts_selected) == 0) $contacts_selected = $contactlist;

    foreach ($contacts_selected as $contact) {
        //$categories = $CAT->find($contact->id);
        $contents .= contact2csvline($contact) . "\n";
    }
    
    if(!empty($contacts_selected)) {
        // send the csv file
        $filename = "All Contacts (" . count($contacts_selected) . ")";
        $filename = trim($filename);
        $filename .= ".csv";
        
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Connection: close");
        header("Content-Type: text/plain; name=\"$filename\"");
        echo $contents;
        exit();    
    }
}

function contact2csvline($contact) {
    $line = array();
    
    $line['title']              = csv_escape($contact->title);
    $line['firstname']          = csv_escape($contact->firstname);
    $line['firstname2']         = csv_escape($contact->firstname2);
    $line['lastname']           = csv_escape($contact->lastname);
    $line['suffix']             = csv_escape($contact->suffix);
    $line['nickname']           = csv_escape($contact->nickname);
    $line['phoneticfirstname']  = csv_escape($contact->phoneticfirstname);
    $line['phoneticlastname']   = csv_escape($contact->phoneticlastname);
    $line['jobtitle']           = csv_escape($contact->jobtitle);
    $line['department']         = csv_escape($contact->department);
    $line['organization']       = csv_escape($contact->organization);
    $line['company']            = csv_escape($contact->company);
    $line['birthdate']          = csv_escape($contact->birthdate);
    $line['note']               = csv_escape($contact->note);
    $line['creationdate']       = csv_escape($contact->creationdate);
    $line['modificationdate']   = csv_escape($contact->modificationdate);
    //$line['id']                 = csv_escape($contact->id);

    $line['street1']            = csv_escape($contact->addresses[0]['street']);
    $line['city1']              = csv_escape($contact->addresses[0]['city']);
    $line['state1']             = csv_escape($contact->addresses[0]['state']);
    $line['zip1']               = csv_escape($contact->addresses[0]['zip']);
    $line['country1']           = csv_escape($contact->addresses[0]['country']);

    $line['street2']            = csv_escape($contact->addresses[1]['street']);
    $line['city2']              = csv_escape($contact->addresses[1]['city']);
    $line['state2']             = csv_escape($contact->addresses[1]['state']);
    $line['zip2']               = csv_escape($contact->addresses[1]['zip']);
    $line['country2']           = csv_escape($contact->addresses[1]['country']);

    $line['street3']            = csv_escape($contact->addresses[2]['street']);
    $line['city3']              = csv_escape($contact->addresses[2]['city']);
    $line['state3']             = csv_escape($contact->addresses[2]['state']);
    $line['zip3']               = csv_escape($contact->addresses[2]['zip']);
    $line['country3']           = csv_escape($contact->addresses[2]['country']);

    $line['street4']            = csv_escape($contact->addresses[3]['street']);
    $line['city4']              = csv_escape($contact->addresses[3]['city']);
    $line['state4']             = csv_escape($contact->addresses[3]['state']);
    $line['zip4']               = csv_escape($contact->addresses[3]['zip']);
    $line['country4']           = csv_escape($contact->addresses[3]['country']);

    $line['street5']            = csv_escape($contact->addresses[4]['street']);
    $line['city5']              = csv_escape($contact->addresses[4]['city']);
    $line['state5']             = csv_escape($contact->addresses[4]['state']);
    $line['zip5']               = csv_escape($contact->addresses[4]['zip']);
    $line['country5']           = csv_escape($contact->addresses[4]['country']);

    $line['email1']             = csv_escape($contact->emails[0]['email']);
    $line['email2']             = csv_escape($contact->emails[1]['email']);
    $line['email3']             = csv_escape($contact->emails[2]['email']);
    $line['email4']             = csv_escape($contact->emails[3]['email']);
    $line['email5']             = csv_escape($contact->emails[4]['email']);

    $line['phone1']             = csv_escape($contact->phones[0]['phone']);
    $line['phone2']             = csv_escape($contact->phones[1]['phone']);
    $line['phone3']             = csv_escape($contact->phones[2]['phone']);
    $line['phone4']             = csv_escape($contact->phones[3]['phone']);
    $line['phone5']             = csv_escape($contact->phones[4]['phone']);
    
    $line['chathandle1']        = csv_escape($contact->chathandles[0]['handle']);
    $line['chathandle2']        = csv_escape($contact->chathandles[1]['handle']);
    $line['chathandle3']        = csv_escape($contact->chathandles[2]['handle']);
    $line['chathandle4']        = csv_escape($contact->chathandles[3]['handle']);
    $line['chathandle5']        = csv_escape($contact->chathandles[4]['handle']);

    $line['relatedname1']       = csv_escape($contact->relatednames[0]['name']);
    $line['relatedname2']       = csv_escape($contact->relatednames[1]['name']);
    $line['relatedname3']       = csv_escape($contact->relatednames[2]['name']);
    $line['relatedname4']       = csv_escape($contact->relatednames[3]['name']);
    $line['relatedname5']       = csv_escape($contact->relatednames[4]['name']);
    
    $line['url1']               = csv_escape($contact->urls[0]['url']);
    $line['url2']               = csv_escape($contact->urls[1]['url']);
    $line['url3']               = csv_escape($contact->urls[2]['url']);
    $line['url4']               = csv_escape($contact->urls[3]['url']);
    $line['url5']               = csv_escape($contact->urls[4]['url']);

    return implode(",", $line);
}

function csv_title() {
    $contact = new person;
    
    $contact->title             = "Title";
    $contact->firstname         = "Firstname";
    $contact->firstname2        = "Second Firstname";
    $contact->lastname          = "Lastname";
    $contact->suffix            = "Suffix";
    $contact->nickname          = "Nickname";
    $contact->phoneticfirstname = "Phonetic Firstname";
    $contact->phoneticlastname  = "Phonetic Lastname";
    $contact->jobtitle          = "Jobtitle";
    $contact->department        = "Department";
    $contact->organization      = "Organization";
    $contact->company           = "Company";
    $contact->birthdate         = "Birthdate";
    $contact->note              = "Note";
    $contact->creationdate      = "Created";
    $contact->modificationdate  = "Modified";
    
    for($i = 1; $i <= 5; $i++) {
        $contact->add_address( array('street' => 'Street'.$i,
                                     'city'   => 'City'.$i,
                                     'state'  => 'State'.$i,
                                     'zip'    => 'Zip'.$i,
                                     'country'=> 'Country'.$i
                                     ) );
    }

    for($i = 1; $i <= 5; $i++) {
        $contact->add_email( array('email' => 'E-Mail'.$i ) );
    }

    for($i = 1; $i <= 5; $i++) {
        $contact->add_phone( array('phone' => 'Phone'.$i ) );
    }

    for($i = 1; $i <= 5; $i++) {
        $contact->add_chathandle( array('handle' => 'IM Label'.$i ) );
    }

    for($i = 1; $i <= 5; $i++) {
        $contact->add_url( array('url' => 'URL'.$i ) );
    }

    for($i = 1; $i <= 5; $i++) {
        $contact->add_relatedname( array('name' => 'Related Name'.$i ) );
    }
    
    
    return contact2csvline($contact);
}

function csv_escape($string) {
    return '"' . str_replace('"', '""', $string) . '"';
}

?>