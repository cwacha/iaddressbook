<?php
/**
 * iAddressBook VCard Import
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Clemens Wacha <clemens.wacha@gmx.net>
 */
if (!defined('AB_BASEDIR'))
	define('AB_BASEDIR', realpath(dirname(__FILE__) . '/../../'));
require_once (AB_BASEDIR . '/lib/php/include.php');
require_once (AB_BASEDIR . '/lib/php/addressbook.php');
require_once (AB_BASEDIR . '/lib/php/Contact_Vcard_Parse.php');
require_once (AB_BASEDIR . '/lib/php/Contact_Vcard_Build.php');
require_once (AB_BASEDIR . '/lib/php/image.php');
require_once (AB_BASEDIR . '/lib/php/category.php');

function act_importvcard_file() {
	global $conf;
	
	$filename = $_FILES['vcard_file']['tmp_name'];
	
	if (!is_string($filename)) {
		msg("file was not uploaded!", -1);
		return;
	}
	if (!file_exists($filename)) {
		msg("file $filename does not exist!", -1);
		return;
	}
	
	$data = file_get_contents($filename);
	
	import_vcards($data);
	
	if (is_writeable($filename)) {
		unlink($filename);
	} else {
		msg("Cannot remove $filename: read-only file", -1);
	}
}

function import_vcards($vcard_string) {
	global $AB;
	global $conf;
	global $CAT;
	global $ID;
	
	$error = 0;
	$imported = 0;
	$person_id = false;
		
	$contacts = vcard2contacts($vcard_string);
	
	$lastImportName = ' __lastimport__';
	$CAT->deleteByName($lastImportName);
	$import_cat = new Category(' __lastimport__');
	$CAT->set($import_cat);
	
	foreach ( $contacts as $contact ) {
		if ($AB->is_duplicate($contact)) {
			msg($contact->name() . " is duplicate!!", -1);
			$error++;
			continue;
		}
		
		// add to last import category
		$contact->add_category($import_cat);
		
		// import
		$person_id = $AB->set($contact);
		if ($person_id === false) {
			$error++;
			msg("Could not import contact " . $contact->name(), -1);
			continue;
		}
		$imported++;
	}
	
	if ($imported > 0)
		msg("$imported vCard(s) succesfully imported!", 1);
	
	if (count($contacts) == 0) {
		msg("no vCards found to import.");
		$error++;
	}
	
	if ($error != 0) {
		if ($error > 1)
			msg("Import ended with $error errors.");
		else
			msg("Import ended with $error error.");
	}
	
	$ID = (int) $person_id;
}

function act_importvcard_folder() {
	$folder = AB_IMPORTDIR;
	
	$dh = opendir($folder);
	if (!is_resource($dh)) {
		msg("Could not open $folder");
		return;
	}
	
	$processed = 0;
	
	while (false !== ($file = readdir($dh))) {
		if (is_file($folder . $file) and strtolower(substr($file, -4, 4)) == ".vcf") {
			msg("Processing file: $file", 1);
			act_importvcard_file($folder . $file);
			$processed++;
		}
	}
	
	if ($processed > 0) {
		msg("$processed files processed", 1);
	} else {
		msg("No vCards found in import folder: " . $folder, -1);
		msg("Make sure the folder is readable by the webserver and the files have a '.vcf' extension!");
	}
	
	closedir($dh);
}

function export_vcards($vcardData, $filename = 'Contacts.vcf') {
	if (empty($vcardData))
		exit();
	
	$filename = trim($filename);
	
	// send the vcard
	header("Content-Disposition: attachment; filename=\"$filename\"");
	header("Connection: close");
	header("Content-Type: text/x-vCard; name=\"$filename\"");
	echo $vcardData;
	exit();
}

function act_exportvcard() {
	global $AB;
	global $ID;
	
	$contact = $AB->get($ID, true);
	$vcarddata = contact2vcard($contact);
	
	$filename = trim($contact->name()) . '.vcf';
	export_vcards($vcarddata, $filename);
}

function act_exportvcard_cat() {
	global $AB;
	global $CAT;
	global $QUERY;
	global $conf;
	
	if (!isset($QUERY)) {
		$contactlist = $AB->getall();
	} else {
		$contactlist = $AB->find($QUERY);
	}
	
	$contacts_selected = array ();
	foreach ( $_REQUEST as $key => $value ) {
		if (strpos($key, 'ct_') === 0) {
			$contacts_selected[$value] = $contactlist[$value];
		}
	}
	if (count($contacts_selected) == 0)
		$contacts_selected = $contactlist;
	
	$contacts = array ();
	foreach ( $contacts_selected as $contact ) {
		$contact = $AB->get($contact->id, true);
		$contacts[] = $contact;
	}
	
	if (empty($contacts))
		return;
	
	$vcarddata = contacts2vcard($contacts);
	
	$filename = "All Contacts (" . count($contacts_selected) . ").vcf";
	export_vcards($vcarddata, $filename);
}

function contacts2vcard($contacts) {
	$vcarddata = '';
	foreach ( $contacts as $contact ) {
		$vcarddata .= contact2vcard($contact);
	}
	return $vcarddata;
}

function contact2vcard($contact) {
	global $conf;
	
	$card = new Contact_Vcard_Build();
	
	$card->setUniqueID($contact->uid);
	$card->setRevision(gmdate("Y-m-d\TH:i:s\Z", $contact->modification_ts));
	//$card->setProductID('-//Apple Inc.//AddressBook 7.1//EN');
	
	$card->setFormattedName($contact->name());
	$card->setName($contact->lastname, $contact->firstname, $contact->firstname2, $contact->title, $contact->suffix);
	if (!empty($contact->nickname))
		$card->addNickname($contact->nickname);
	if (!empty($contact->phoneticlastname))
		$card->setValue('X-PHONETIC-LAST-NAME', 0, 0, $contact->phoneticlastname);
	if (!empty($contact->phoneticfirstname))
		$card->setValue('X-PHONETIC-FIRST-NAME', 0, 0, $contact->phoneticfirstname);
	if (!empty($contact->jobtitle))
		$card->setTitle($contact->jobtitle);
	if (!empty($contact->organization))
		$card->addOrganization($contact->organization);
	if (!empty($contact->department))
		$card->addOrganization($contact->department);
		// add $company boolean
	if ($contact->company == true)
		$card->setValue('X-ABShowas', 0, 0, 'COMPANY');
	
	if ($contact->birthdate != '0000-00-00')
		$card->setBirthday($contact->birthdate);
	
	if (!empty($contact->note))
		$card->setNote($contact->note);
	
	foreach ( $contact->addresses as $item ) {
		$iter = $card->countIter('ADR');
		$card->addAddress($item['pobox'], $item['ext_adr'], $item['street'], $item['city'], $item['state'], $item['zip'], $item['country']);
		if (in_array($item['label'], array (
				'HOME',
				'WORK' 
		))) {
			$card->addParam('TYPE', $item['label']);
		} else {
			$card->setItem('ADR', $iter, 'X-ABLabel', $item['label']);
		}
		if (!empty($item['template']))
			$card->setItem('ADR', $iter, 'X-ABADR', $item['template']);
	}
	
	foreach ( $contact->emails as $item ) {
		$iter = $card->countIter('EMAIL');
		$card->addEmail($item['email']);
		if (in_array($item['label'], array (
				'HOME',
				'WORK' 
		))) {
			$card->addParam('TYPE', $item['label']);
		} else {
			$card->setItem('EMAIL', $iter, 'X-ABLabel', $item['label']);
		}
	}
	
	foreach ( $contact->phones as $item ) {
		$iter = $card->countIter('TEL');
		$card->addTelephone($item['phone']);
		if (in_array($item['label'], array (
				'HOME',
				'WORK',
				'CELL',
				'PAGER',
				'MAIN' 
		))) {
			$card->addParam('TYPE', $item['label']);
		} else if (in_array($item['label'], array (
				'HOME FAX',
				'WORK FAX' 
		))) {
			$card->addParam('TYPE', substr($item['label'], 0, 4));
			$card->addParam('TYPE', 'FAX');
		} else {
			$card->setItem('TEL', $iter, 'X-ABLabel', $item['label']);
		}
	}
	
	foreach ( $contact->chathandles as $item ) {
		$iter = $card->countIter('X-' . $item['type']);
		$card->setValue('X-' . $item['type'], $iter, 0, $item['handle']);
		if (in_array($item['label'], array (
				'HOME',
				'WORK' 
		))) {
			$card->addParam('TYPE', $item['label']);
		} else {
			$card->setItem('X-' . $item['type'], $iter, 'X-ABLabel', $item['label']);
		}
	}
	
	foreach ( $contact->urls as $item ) {
		$iter = $card->countIter('URL');
		$card->addURL($item['url']);
		if (in_array($item['label'], array (
				'HOME',
				'WORK' 
		))) {
			$card->addParam('TYPE', $item['label']);
		} else {
			$card->setItem('URL', $iter, 'X-ABLabel', $item['label']);
		}
	}
	
	foreach ( $contact->relatednames as $item ) {
		$iter = $card->countIter('X-ABRELATEDNAMES');
		$card->setValue('X-ABRELATEDNAMES', $iter, 0, $item['name']);
		if (in_array($item['label'], array (
				'HOME',
				'WORK' 
		))) {
			$card->addParam('TYPE', $item['label']);
		} else {
			$card->setItem('X-ABRELATEDNAMES', $iter, 'X-ABLabel', $item['label']);
		}
	}
	
	if (!empty($contact->image) and !empty($conf['photo_format'])) {
		$conf['photo_format'] = strtoupper($conf['photo_format']);
		$card->setValue('PHOTO', 0, 0, base64_encode(img_convert($contact->image, $conf['photo_format'])));
		$card->addParam('ENCODING', 'B');
		$card->addParam('TYPE', strtoupper($conf['photo_format']));
	}
	
	$categories = $contact->get_categories();
	foreach ( $categories as $category ) {
		$card->addCategories($category->displayName());
	}
	
	/**
	 * X-ADDRESSBOOKSERVER-KIND:group
	 * X-ADDRESSBOOKSERVER-MEMBER:urn:uuid:7119696e-c552-6115-c77e-0cb34efe2b30
	 */
	if ($contact->isgroup) {
		$card->setValue('X-ADDRESSBOOKSERVER-KIND', 0, 0, 'group');
		$members = $contact->get_groupmembers();
		foreach ( $members as $member ) {
			$iter = $card->countIter('X-ADDRESSBOOKSERVER-MEMBER:urn:uuid');
			$card->setValue('X-ADDRESSBOOKSERVER-MEMBER:urn:uuid', $iter, 0, $member);
		}
	}
	$vcard = $card->fetch();
	
	return $vcard;
}

function array2contact($card) {
	global $conf;
	$contact = new Person();

	if (isset($card['UID']))
		$contact->uid = $card['UID'][0]['value'][0][0];
	if (isset($card['REV']) && is_array($card['REV'])) {
		$contact->modification_ts = strtotime($card['REV'][0]['value'][0][0]);
	}
	if (isset($card['N'])) {
		$contact->lastname = $card['N'][0]['value'][0][0];
		$contact->firstname = $card['N'][0]['value'][1][0];
		$contact->firstname2 = $card['N'][0]['value'][2][0];
		$contact->title = $card['N'][0]['value'][3][0];
		$contact->suffix = $card['N'][0]['value'][4][0];
	}
	
	if (isset($card['NICKNAME']))
		$contact->nickname = $card['NICKNAME'][0]['value'][0][0];

	if (isset($card['ORG'])) {
		$contact->organization = $card['ORG'][0]['value'][0][0];
		$contact->department = $card['ORG'][0]['value'][1][0];
	}

	if (isset($card['TITLE']))
		$contact->jobtitle = $card['TITLE'][0]['value'][0][0];
	
	if (isset($card['NOTE']))
		$contact->note = $card['NOTE'][0]['value'][0][0];

	if (isset($card['BDAY']))
		$contact->birthdate = substr($card['BDAY'][0]['value'][0][0], 0, 10);
	
	if (isset($card['X-ABSHOWAS']))
		$contact->company = ($card['X-ABSHOWAS'][0]['value'][0][0] == "COMPANY");
	
	if (isset($card['EMAIL']) && is_array($card['EMAIL'])) {
		foreach ( $card['EMAIL'] as $email ) {
			$tmp = array ();
			$tmp['label'] = 'WORK'; // default
			if (isset($email['param']['TYPE']) && is_array($email['param']['TYPE'])) {
				foreach ( $email['param']['TYPE'] as $key => $value )
					$email['param']['TYPE'][$key] = strtoupper($value);
				
				if (in_array('WORK', $email['param']['TYPE']))
					$tmp['label'] = 'WORK';
				if (in_array('HOME', $email['param']['TYPE']))
					$tmp['label'] = 'HOME';
			}
			if (!empty($email['X-ABLABEL']))
				$tmp['label'] = $email['X-ABLABEL']['value'][0][0];
			
			$tmp['email'] = $email['value'][0][0];
			$contact->add_email($tmp);
		}
	}
	
	if (isset($card['TEL']) && is_array($card['TEL'])) {
		foreach ( $card['TEL'] as $phone ) {
			$tmp = array ();
			$tmp['label'] = 'WORK'; // default
			if (isset($phone['param']['TYPE']) && is_array($phone['param']['TYPE'])) {
				foreach ( $phone['param']['TYPE'] as $key => $value )
					$phone['param']['TYPE'][$key] = strtoupper($value);
				
				if (in_array('WORK', $phone['param']['TYPE']))
					$tmp['label'] = 'WORK';
				if (in_array('HOME', $phone['param']['TYPE']))
					$tmp['label'] = 'HOME';
				if (in_array('CELL', $phone['param']['TYPE']))
					$tmp['label'] = 'CELL';
				if (in_array('MAIN', $phone['param']['TYPE']))
					$tmp['label'] = 'MAIN';
				if (in_array('PAGER', $phone['param']['TYPE']))
					$tmp['label'] = 'PAGER';
				if (in_array('FAX', $phone['param']['TYPE']))
					$tmp['label'] .= ' FAX';
				if (in_array('IPHONE', $phone['param']['TYPE']))
					$tmp['label'] = 'iPhone';
			}
			if (!empty($phone['X-ABLABEL']))
				$tmp['label'] = $phone['X-ABLABEL']['value'][0][0];
			
			$tmp['phone'] = $phone['value'][0][0];
			$contact->add_phone($tmp);
		}
	}
	
	if (isset($card['ADR']) && is_array($card['ADR'])) {
		foreach ( $card['ADR'] as $address ) {
			$tmp = array ();
			$tmp['label'] = 'WORK'; // default
			if (isset($address['param']['TYPE']) && is_array($address['param']['TYPE'])) {
				foreach ( $address['param']['TYPE'] as $key => $value )
					$address['param']['TYPE'][$key] = strtoupper($value);
				
				if (in_array('WORK', $address['param']['TYPE']))
					$tmp['label'] = 'WORK';
				if (in_array('HOME', $address['param']['TYPE']))
					$tmp['label'] = 'HOME';
			}
			if (!empty($address['X-ABLABEL']))
				$tmp['label'] = $address['X-ABLABEL']['value'][0][0];
			
			$tmp['pobox'] = $address['value'][0][0];
			$tmp['ext_adr'] = $address['value'][1][0];
			$tmp['street'] = $address['value'][2][0];
			$tmp['city'] = $address['value'][3][0];
			$tmp['state'] = $address['value'][4][0];
			$tmp['zip'] = $address['value'][5][0];
			$tmp['country'] = $address['value'][6][0];
			$tmp['template'] = '';
			if(isset($address['X-ABADR']))
				$tmp['template'] = $address['X-ABADR']['value'][0][0];
			$contact->add_address($tmp);
		}
	}
	
	if (isset($card['URL']) && is_array($card['URL'])) {
		foreach ( $card['URL'] as $url ) {
			$tmp = array ();
			$tmp['label'] = 'WORK'; // default
			if (isset($url['param']['TYPE']) && is_array($url['param']['TYPE'])) {
				foreach ( $url['param']['TYPE'] as $key => $value )
					$url['param']['TYPE'][$key] = strtoupper($value);
				
				if (in_array('WORK', $url['param']['TYPE']))
					$tmp['label'] = 'WORK';
				if (in_array('HOME', $url['param']['TYPE']))
					$tmp['label'] = 'HOME';
			}
			if (!empty($url['X-ABLABEL']))
				$tmp['label'] = $url['X-ABLABEL']['value'][0][0];
			
			$tmp['url'] = $url['value'][0][0];
			$contact->add_url($tmp);
		}
	}
	
	if (isset($card['X-ABRELATEDNAMES']) && is_array($card['X-ABRELATEDNAMES'])) {
		foreach ( $card['X-ABRELATEDNAMES'] as $rname ) {
			$tmp = array ();
			$tmp['label'] = 'WORK'; // default
			if (isset($rname['param']['TYPE']) && is_array($rname['param']['TYPE'])) {
				foreach ( $rname['param']['TYPE'] as $key => $value )
					$rname['param']['TYPE'][$key] = strtoupper($value);
				
				if (in_array('WORK', $rname['param']['TYPE']))
					$tmp['label'] = 'WORK';
				if (in_array('HOME', $rname['param']['TYPE']))
					$tmp['label'] = 'HOME';
			}
			if (!empty($rname['X-ABLABEL']))
				$tmp['label'] = $rname['X-ABLABEL']['value'][0][0];
			
			$tmp['name'] = $rname['value'][0][0];
			$contact->add_relatedname($tmp);
		}
	}
	
	if (isset($card['X-AIM']) && is_array($card['X-AIM'])) {
		foreach ( $card['X-AIM'] as $chat ) {
			$tmp = array ();
			$tmp['label'] = 'WORK'; // default
			if (isset($chat['param']['TYPE']) && is_array($chat['param']['TYPE'])) {
				foreach ( $chat['param']['TYPE'] as $key => $value )
					$chat['param']['TYPE'][$key] = strtoupper($value);
				
				if (in_array('WORK', $chat['param']['TYPE']))
					$tmp['label'] = 'WORK';
				if (in_array('HOME', $chat['param']['TYPE']))
					$tmp['label'] = 'HOME';
			}
			if (!empty($chat['X-ABLABEL']))
				$tmp['label'] = $chat['X-ABLABEL']['value'][0][0];
			
			$tmp['type'] = "AIM";
			$tmp['handle'] = $chat['value'][0][0];
			$contact->add_chathandle($tmp);
		}
	}
	
	if (isset($card['X-ICQ']) && is_array($card['X-ICQ'])) {
		foreach ( $card['X-ICQ'] as $chat ) {
			$tmp = array ();
			$tmp['label'] = 'WORK'; // default
			if (isset($chat['param']['TYPE']) && is_array($chat['param']['TYPE'])) {
				foreach ( $chat['param']['TYPE'] as $key => $value )
					$chat['param']['TYPE'][$key] = strtoupper($value);
				
				if (in_array('WORK', $chat['param']['TYPE']))
					$tmp['label'] = 'WORK';
				if (in_array('HOME', $chat['param']['TYPE']))
					$tmp['label'] = 'HOME';
			}
			if (!empty($chat['X-ABLABEL']))
				$tmp['label'] = $chat['X-ABLABEL']['value'][0][0];
			
			$tmp['type'] = "ICQ";
			$tmp['handle'] = $chat['value'][0][0];
			$contact->add_chathandle($tmp);
		}
	}
	
	if (isset($card['X-MSN']) && is_array($card['X-MSN'])) {
		foreach ( $card['X-MSN'] as $chat ) {
			$tmp = array ();
			$tmp['label'] = 'WORK'; // default
			if (isset($chat['param']['TYPE']) && is_array($chat['param']['TYPE'])) {
				foreach ( $chat['param']['TYPE'] as $key => $value )
					$chat['param']['TYPE'][$key] = strtoupper($value);
				
				if (in_array('WORK', $chat['param']['TYPE']))
					$tmp['label'] = 'WORK';
				if (in_array('HOME', $chat['param']['TYPE']))
					$tmp['label'] = 'HOME';
			}
			if (!empty($chat['X-ABLABEL']))
				$tmp['label'] = $chat['X-ABLABEL']['value'][0][0];
			
			$tmp['type'] = "MSN";
			$tmp['handle'] = $chat['value'][0][0];
			$contact->add_chathandle($tmp);
		}
	}
	
	if (isset($card['X-JABBER']) && is_array($card['X-JABBER'])) {
		foreach ( $card['X-JABBER'] as $chat ) {
			$tmp = array ();
			$tmp['label'] = 'WORK'; // default
			if (isset($chat['param']['TYPE']) && is_array($chat['param']['TYPE'])) {
				foreach ( $chat['param']['TYPE'] as $key => $value )
					$chat['param']['TYPE'][$key] = strtoupper($value);
				
				if (in_array('WORK', $chat['param']['TYPE']))
					$tmp['label'] = 'WORK';
				if (in_array('HOME', $chat['param']['TYPE']))
					$tmp['label'] = 'HOME';
			}
			if (!empty($chat['X-ABLABEL']))
				$tmp['label'] = $chat['X-ABLABEL']['value'][0][0];
			
			$tmp['type'] = "JABBER";
			$tmp['handle'] = $chat['value'][0][0];
			$contact->add_chathandle($tmp);
		}
	}
	
	if (isset($card['X-SKYPE']) && is_array($card['X-SKYPE'])) {
		foreach ( $card['X-SKYPE'] as $chat ) {
			$tmp = array ();
			$tmp['label'] = 'WORK'; // default
			if (isset($chat['param']['TYPE']) && is_array($chat['param']['TYPE'])) {
				foreach ( $chat['param']['TYPE'] as $key => $value )
					$chat['param']['TYPE'][$key] = strtoupper($value);
				
				if (in_array('WORK', $chat['param']['TYPE']))
					$tmp['label'] = 'WORK';
				if (in_array('HOME', $chat['param']['TYPE']))
					$tmp['label'] = 'HOME';
			}
			if (!empty($chat['X-ABLABEL']))
				$tmp['label'] = $chat['X-ABLABEL']['value'][0][0];
			
			$tmp['type'] = "SKYPE";
			$tmp['handle'] = $chat['value'][0][0];
			$contact->add_chathandle($tmp);
		}
	}
	
	if (isset($card['X-YAHOO']) && is_array($card['X-YAHOO'])) {
		foreach ( $card['X-YAHOO'] as $chat ) {
			$tmp = array ();
			$tmp['label'] = 'WORK'; // default
			if (isset($chat['param']['TYPE']) && is_array($chat['param']['TYPE'])) {
				foreach ( $chat['param']['TYPE'] as $key => $value )
					$chat['param']['TYPE'][$key] = strtoupper($value);
				
				if (in_array('WORK', $chat['param']['TYPE']))
					$tmp['label'] = 'WORK';
				if (in_array('HOME', $chat['param']['TYPE']))
					$tmp['label'] = 'HOME';
			}
			if (!empty($chat['X-ABLABEL']))
				$tmp['label'] = $chat['X-ABLABEL']['value'][0][0];
			
			$tmp['type'] = "YAHOO";
			$tmp['handle'] = $chat['value'][0][0];
			$contact->add_chathandle($tmp);
		}
	}
	
	if (isset($card['X-IRC']) && is_array($card['X-IRC'])) {
		foreach ( $card['X-IRC'] as $chat ) {
			$tmp = array ();
			$tmp['label'] = 'WORK'; // default
			if (isset($chat['param']['TYPE']) && is_array($chat['param']['TYPE'])) {
				foreach ( $chat['param']['TYPE'] as $key => $value )
					$chat['param']['TYPE'][$key] = strtoupper($value);
				
				if (in_array('WORK', $chat['param']['TYPE']))
					$tmp['label'] = 'WORK';
				if (in_array('HOME', $chat['param']['TYPE']))
					$tmp['label'] = 'HOME';
			}
			if (!empty($chat['X-ABLABEL']))
				$tmp['label'] = $chat['X-ABLABEL']['value'][0][0];
			
			$tmp['type'] = "IRC";
			$tmp['handle'] = $chat['value'][0][0];
			$contact->add_chathandle($tmp);
		}
	}
	
	if ($conf['photo_enable']) {
		if (isset($card['PHOTO']) && is_array($card['PHOTO'])) {
			$enc = strtoupper($card['PHOTO'][0]['param']['ENCODING'][0]);
			$picture = $card['PHOTO'][0]['value'][0][0];
			if ($enc == "BASE64" || $enc == "B") {
				// $contact->image = img_convert(base64_decode($picture));
				// if(empty($contact->image)) msg("No image returned for ".$contact->name());
				$contact->image = base64_decode($picture);
			} else {
				msg("Cannot import image. Unknown encoding $enc", -1);
			}
		}
	}
	
	if (isset($card['CATEGORIES']) && is_array($card['CATEGORIES']['0']['value']['0'])) {
		foreach ( $card['CATEGORIES']['0']['value']['0'] as $cat_name ) {
			// add to corresponding categories
			$contact->add_category(new Category($cat_name));
		}
	}
	
	/**
	 * X-ADDRESSBOOKSERVER-KIND:group
	 * X-ADDRESSBOOKSERVER-MEMBER:urn:uuid:7119696e-c552-6115-c77e-0cb34efe2b30
	 */
	if(isset($card['X-ADDRESSBOOKSERVER-KIND']))
		$contact->isgroup = (strtoupper($card['X-ADDRESSBOOKSERVER-KIND'][0]['value'][0][0]) == "GROUP");

	if (isset($card['X-ADDRESSBOOKSERVER-MEMBER']) && is_array($card['X-ADDRESSBOOKSERVER-MEMBER']['0']['value']['0'])) {
		foreach ( $card['X-ADDRESSBOOKSERVER-MEMBER']['0']['value']['0'] as $member ) {
			list ($dummy1, $dummy2, $uuid) = explode(":", $member);
			$contact->add_groupmember($uuid);
		}
	}
	
	return $contact;
}

function vcard2contacts($vcard_string) {
	global $conf;
	
	$contacts = array ();
	
	// instantiate a parser object
	$parse = new Contact_Vcard_Parse();
	
	// parse it
	$cards = $parse->fromText($vcard_string, true, $conf['vcard_fb_enc']);
	
	if (!is_array($cards)) {
		msg("Failed to parse vcard!!", -1);
		return $contacts;
	}
	
	foreach ( $cards as $card ) {
		$contact = array2contact($card);
		$contacts[] = $contact;
	}
	
	return $contacts;
}


?>