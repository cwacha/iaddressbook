<?php

namespace Sabre\CardDAV\Backend;

use Sabre\CardDAV;
use Sabre\DAV;
use iAddressbook;
    
    
    if(!defined('AB_BASEDIR')) define('AB_BASEDIR',realpath(dirname(__FILE__)).'/../../../../../../..');
    require_once(AB_BASEDIR.'/lib/php/include.php');
    require_once(AB_BASEDIR.'/lib/php/init.php');
    require_once(AB_BASEDIR.'/lib/php/db.php');
    require_once(AB_BASEDIR.'/lib/php/module_vcard.php');
    require_once(AB_BASEDIR.'/lib/php/common.php');

/**
 * PDO CardDAV backend
 *
 * This CardDAV backend uses PDO to store addressbooks
 *
 * @copyright Copyright (C) 2007-2013 fruux GmbH (https://fruux.com/).
 * @author Evert Pot (http://evertpot.com/)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
class IABCardDAVBackend extends AbstractBackend {
    
    protected $catalog;

    public function __construct() {
        db_init();
        db_open();

        $this->catalog = new \Addressbooks();
        
        $this->pdo = $pdo;
        $this->addressBooksTableName = $addressBooksTableName;
        $this->cardsTableName = $cardsTableName;
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
    	error_log("getAddressBooksForUser: $principalUri");
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
    	error_log("updateAddressBook: $addressBookId");
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
    	error_log("createAddressBook: $principalUri, $url");
    }

    /**
     * Deletes an entire addressbook and all its contents
     *
     * @param int $addressBookId
     * @return void
     */
    public function deleteAddressBook($addressBookId) {
    	error_log("deleteAddressBook: $addressBookId");
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
    	error_log("getCards: $addressBookId");
    	$book = $this->catalog->getAddressBookForId($addressBookId);
		$AB = new \Addressbook($book['id']);
        $CAT = new \Categories;        
        
        $results = array();
        
        $contactlist = $AB->getall();
        
        foreach ($contactlist as $contact) {
			$contact->image = img_load($contact->id);
			$categories = $CAT->getCategoriesForPerson($contact->id);
			foreach ( $categories as $category ) {
				$contact->add_category($category);
			}
			$vcarddata = contact2vcard($contact);
            
            $item = array();
            $item['uri'] = $contact->uid;
            $item['lastmodified'] = strtotime($contact->modificationdate);
            $item['carddata'] = $vcarddata;
            $item['etag'] = $contact->etag;
            $item['size'] = strlen($vcarddata);
            
            $results[] = $item;
        }

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
		global $CAT;
		error_log("getCard: $addressBookId, $cardUri");

		$book = $this->catalog->getAddressBookForId($addressbookId);
		$AB = new \Addressbook($book ['id']);
		$CAT = new \Categories();
		
		$results = array ();
		
		$contact = $AB->get($cardUri, true);
		
		if (!$contact) {
			return false;
		}
		
		$vcarddata = contact2vcard($contact);
		$item = array (
				'uri' => $contact->uid,
				'lastmodified' => strtotime($contact->modificationdate),
				'carddata' => $vcarddata,
				'etag' => $contact->etag,
				'size' => strlen($vcarddata) 
		);
		
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
    public function createCard($addressBookId, $cardUri, $cardData) {
    	error_log("createCard: $addressBookId, $cardUri");
    	
    	$book = $this->catalog->getAddressBookForId($addressbookId);
    	$AB = new \Addressbook($book ['id']);
    	$CAT = new \Categories();

    	$contacts = vcard2contacts($cardData);
    	    	
    	foreach ( $contacts as $contact ) {
    		$person_id = $AB->set($contact);
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
    	error_log("updateCard: $addressBookId, $cardUri");
    	 
    	$book = $this->catalog->getAddressBookForId($addressbookId);
		$AB = new \Addressbook($book ['id']);
		$CAT = new \Categories();
		
		$contacts = vcard2contacts($vcard_string);
		
		foreach ( $contacts as $contact ) {
			$person_id = $AB->set($contact);
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
    	error_log("deleteCard: $addressBookId, $cardUri");
    	 
    	$book = $this->catalog->getAddressBookForId($addressbookId);
    	$AB = new \Addressbook($book ['id']);
    	$CAT = new \Categories();
    	
    	$contact = $AB->get($cardUri, true);
    	return $AB->delete($contact->id);
    }
}
