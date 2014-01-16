<?php

namespace Sabre\CardDAV\Backend;

use Sabre\CardDAV;
use Sabre\DAV;
use iAddressbook;
    
    
    if(!defined('AB_BASEDIR')) define('AB_BASEDIR',realpath(dirname(__FILE__)).'/../../..');
    require_once(AB_BASEDIR.'/lib/php/include.php');
    require_once(AB_BASEDIR.'/lib/php/init.php');
    require_once(AB_BASEDIR.'/lib/php/db.php');
    require_once(AB_BASEDIR.'/lib/php/module_vcard.php');
    require_once(AB_BASEDIR.'/lib/php/common.php');

/**
 * iAddressBook CardDAV backend
 *
 * This CardDAV backend uses iAddressBook to store addressbooks
 *
 * @copyright Copyright (C) 2007-2014 Clemens Wacha (http://iaddressbook.org/).
 * @author Clemens Wacha
 */
class IABCardDAVBackend extends AbstractBackend {
    
    protected $catalog;

    public function __construct() {
        db_init();
        db_open();

        $this->catalog = new \Addressbooks();
    }

	/**
	 * Returns the list of addressbooks for a specific user.
	 *
	 * @param string $principalUri        	
	 * @return array
	 */
	public function getAddressBooksForUser($principalUri) {
		$books = $this->catalog->getAddressBooksForUser($principalUri);
    	$addressBooks = array();
		foreach($books as $book) {
	        $addressBooks[] = array(
                                'id'  => $book['id'],
                                'uri' => $book['uri'],
                                'principaluri' => $principalUri,
                                '{DAV:}displayname' => $book['displayname'],
                                '{' . CardDAV\Plugin::NS_CARDDAV . '}addressbook-description' => $book['description'],
                                '{http://calendarserver.org/ns/}getctag' => $book['ctag'],
                                '{' . CardDAV\Plugin::NS_CARDDAV . '}supported-address-data' =>
                                new CardDAV\Property\SupportedAddressData(),
                                );
		}
    	error_log("getAddressBooksForUser: $principalUri, " . $book['ctag']);
		return $addressBooks;
    }


    /**
     * Updates an addressbook's properties
     *
     * See Sabre\DAV\IProperties for a description of the mutations array, as
     * well as the return value.
     *
     * @param mixed $addressBookId
     * @param array $mutations
     * @see Sabre\DAV\IProperties::updateProperties
     * @return bool|array
     */
    public function updateAddressBook($addressBookId, array $mutations) {
    	msg("updateAddressBook: $addressBookId");
    	return true;
    }

    /**
     * Creates a new address book
     *
     * @param string $principalUri
     * @param string $url Just the 'basename' of the url.
     * @param array $properties
     * @return void
     */
    public function createAddressBook($principalUri, $url, array $properties) {
    	msg("createAddressBook: $principalUri, $url");
    }

    /**
     * Deletes an entire addressbook and all its contents
     *
     * @param int $addressBookId
     * @return void
     */
    public function deleteAddressBook($addressBookId) {
    	msg("deleteAddressBook: $addressBookId");
    }

    /**
     * Returns all cards for a specific addressbook id.
     *
     * This method should return the following properties for each card:
     *   * carddata - raw vcard data
     *   * uri - Some unique url
     *   * lastmodified - A unix timestamp
     *
     * It's recommended to also return the following properties:
     *   * etag - A unique etag. This must change every time the card changes.
     *   * size - The size of the card in bytes.
     *
     * If these last two properties are provided, less time will be spent
     * calculating them. If they are specified, you can also ommit carddata.
     * This may speed up certain requests, especially with large cards.
     *
     * @param mixed $addressbookId
     * @return array
     */
    public function getCards($addressBookId) {
    	//msg("getCards: addressBookId=$addressBookId");
		global $AB;
		global $CAT;
		
    	$book = $this->catalog->getAddressBookForId($addressBookId);
		$AB = new \Addressbook($book['id']);
        $CAT = new \Categories;        
        
        $results = array();
        
        $contactlist = $AB->getall();
        
        foreach ($contactlist as $contact) {
			$contact->image = img_load($contact->id);
			$contact->image = '';
			$categories = $CAT->getCategoriesForPerson($contact->id);
			foreach ( $categories as $category ) {
				$contact->add_category($category);
			}
			$vcarddata = contact2vcard($contact);
            
            $item = array();
            $item['uri'] = $contact->uid;
            $item['lastmodified'] = $contact->modification_ts;
            //$item['carddata'] = $vcarddata;
            $item['etag'] = (string)$contact->etag;
            $item['size'] = strlen($vcarddata);
            
            $results[] = $item;
        }
        
        // now repeat for all the categories
        $categories = $CAT->getAllCategories();
        
        foreach ($categories as $category) {
        	if(strpos($category->name(), ' __') === 0)
        		continue;
        	
        	// check if it is a group
			$contact = new \Person();
			$contact->isgroup = true;
			$contact->uid = $category->uid;
			$contact->modification_ts = $category->modification_ts;
			$contact->etag = $category->etag;
			$contact->lastname = $category->name();
			$members = $CAT->getMembersForCategory($category->id);
			foreach($members as $key => $value) {
				$contact->add_groupmember($value);
			}
				
			$vcarddata = contact2vcard($contact);
        
        	$item = array();
        	$item['uri'] = $contact->uid;
        	$item['lastmodified'] = $contact->modification_ts;
        	//$item['carddata'] = $vcarddata;
        	$item['etag'] = (string)$contact->etag;
        	$item['size'] = strlen($vcarddata);
        
        	$results[] = $item;
        }
        
        msg("getCards: addressBookId=$addressBookId numCards=" . count($results));
        return $results;
    }

	/**
	 * Returns a specfic card.
	 *
	 * The same set of properties must be returned as with getCards. The only
	 * exception is that 'carddata' is absolutely required.
	 *
	 * @param mixed $addressBookId        	
	 * @param string $cardUri        	
	 * @return array
	 */
	public function getCard($addressBookId, $cardUri) {
		$start_ts = microtime(true);
		//msg("getCard: addressBookId=$addressBookId cardUri=$cardUri");
		global $AB;
		global $CAT;
		$uri = basename($cardUri, '.vcf');
		
    	$book = $this->catalog->getAddressBookForId($addressBookId);
		$AB = new \Addressbook($book['id']);
        $CAT = new \Categories;        
				
		$results = array ();
		
		$contact = $AB->get($uri, false);
		
		if (!$contact) {
			// check if it is a group
			$category = $CAT->get($uri);
			if(!$category) {
				error_log("not found:     $uri");
				return false;
			}
			$contact = new \Person();
			$contact->isgroup = true;
			$contact->uid = $category->uid;
			$contact->modification_ts = $category->modification_ts;
			$contact->etag = $category->etag;
			$contact->lastname = $category->name();
			$members = $CAT->getMembersForCategory($category->id);
			foreach($members as $key => $value) {
				$contact->add_groupmember($value);
			}
		}

		$vcarddata = contact2vcard($contact);
		//msg("vcard: " . print_r($vcarddata, true));
		$item = array (
				'uri' => $cardUri,
				'lastmodified' => $contact->modification_ts,
				'carddata' => $vcarddata,
				'etag' => (string)$contact->etag,
				'size' => strlen($vcarddata) 
		);
		
		$stop_ts = microtime(true);
		//msg("getCard: addressBookId=$addressBookId cardUri=$cardUri etag=" . $contact->etag . " delay_ms=" . (int)(($stop_ts - $start_ts)*1000));
		return $item;
	}

    /**
     * Creates a new card.
     *
     * The addressbook id will be passed as the first argument. This is the
     * same id as it is returned from the getAddressbooksForUser method.
     *
     * The cardUri is a base uri, and doesn't include the full path. The
     * cardData argument is the vcard body, and is passed as a string.
     *
     * It is possible to return an ETag from this method. This ETag is for the
     * newly created resource, and must be enclosed with double quotes (that
     * is, the string itself must contain the double quotes).
     *
     * You should only return the ETag if you store the carddata as-is. If a
     * subsequent GET request on the same card does not have the same body,
     * byte-by-byte and you did return an ETag here, clients tend to get
     * confused.
     *
     * If you don't return an ETag, you can just return null.
     *
     * @param mixed $addressBookId
     * @param string $cardUri
     * @param string $cardData
     * @return string|null
     */
	
	/**
	 * Example of OSX addressbook GROUP entry
	 * 
	 * BEGIN:VCARD
	 * VERSION:3.0
	 * PRODID:-//Apple Inc.//AddressBook 7.1//EN
	 * N:Neue Gruppe
	 * FN:Neue Gruppe
	 * X-ADDRESSBOOKSERVER-KIND:group
	 * X-ADDRESSBOOKSERVER-MEMBER:urn:uuid:7119696e-c552-6115-c77e-0cb34efe2b30
	 * REV:2014-01-03T10:30:07Z
	 * UID:9c82155a-1d63-4893-aef9-7b2057f5476d
	 * END:VCARD
	 * 
	 */
    public function createCard($addressBookId, $cardUri, $cardData) {
    	msg("createCard: $addressBookId, $cardUri");
    	global $AB;
		global $CAT;
    	
    	$book = $this->catalog->getAddressBookForId($addressBookId);
		$AB = new \Addressbook($book['id']);
        $CAT = new \Categories;        
    	
    	//msg("createCard: $cardData");
    	$contacts = vcard2contacts($cardData);
    	    	
    	foreach ( $contacts as $contact ) {
			if ($contact->isgroup) {
				$category = $CAT->get($contact->uid);
				if (!$category) {
					$category = new \Category($contact->name());
					$category->uid = $contact->uid;
				}
				$category->name = $contact->name();
				$category->id = $CAT->set($category);
				$CAT->setMembersForCategory($category->id, $contact->get_groupmembers());
			} else {
	    		$person_id = $AB->set($contact);
    		}
    	}

    	return null;
    }

    /**
     * Updates a card.
     *
     * The addressbook id will be passed as the first argument. This is the
     * same id as it is returned from the getAddressbooksForUser method.
     *
     * The cardUri is a base uri, and doesn't include the full path. The
     * cardData argument is the vcard body, and is passed as a string.
     *
     * It is possible to return an ETag from this method. This ETag should
     * match that of the updated resource, and must be enclosed with double
     * quotes (that is: the string itself must contain the actual quotes).
     *
     * You should only return the ETag if you store the carddata as-is. If a
     * subsequent GET request on the same card does not have the same body,
     * byte-by-byte and you did return an ETag here, clients tend to get
     * confused.
     *
     * If you don't return an ETag, you can just return null.
     *
     * @param mixed $addressBookId
     * @param string $cardUri
     * @param string $cardData
     * @return string|null
     */
    public function updateCard($addressBookId, $cardUri, $cardData) {
    	msg("updateCard: $addressBookId, $cardUri");
    	global $AB;
    	global $CAT;
    	
    	$book = $this->catalog->getAddressBookForId($addressBookId);
		$AB = new \Addressbook($book['id']);
        $CAT = new \Categories;        

		$contacts = vcard2contacts($cardData);

		foreach ( $contacts as $contact ) {
			if ($contact->isgroup) {
				$category = $CAT->get($contact->uid);
				if (!$category) {
					$category = new \Category($contact->name());
					$category->uid = $contact->uid;
				}
				$category->name = $contact->name();
				$category->id = $CAT->set($category);
				$CAT->setMembersForCategory($category->id, $contact->get_groupmembers());
			} else {
				$person_id = $AB->set($contact);
			}
		}
		
		return null;
	}

	/**
	 * Deletes a card
	 *
	 * @param mixed $addressBookId        	
	 * @param string $cardUri        	
	 * @return bool
	 */
	public function deleteCard($addressBookId, $cardUri) {
		msg("deleteCard: $addressBookId, $cardUri");
		global $AB;
		global $CAT;		

		$book = $this->catalog->getAddressBookForId($addressBookId);
		$AB = new \Addressbook($book['id']);
		$CAT = new \Categories;
		
		$uri = basename($cardUri, '.vcf');
				
		$contact = $AB->get($uri);
		if ($contact)
			return $AB->delete($contact->id);
			
			// check if it is a group
		$category = $CAT->get($uri);
		if ($category)
			return $CAT->delete($category->id);
		
		error_log("not found:     $uri");
		return false;
	}
}
