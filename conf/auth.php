<?php
/**
 * This is the AddressBook's authentication file
 * This is a piece of PHP code so PHP syntax applies!
 *
 * add as many usernames and md5 passwords as you like
 * 
 * you can create an md5 sum if you enter 'make md5' as username and your password
 * as password into the login form of iAddressBook!
 * 
 */


///////////////////////
//     U S E R S     //
///////////////////////
// guest user

$auth['guest']['permissions']   = array();
$auth['guest']['groups']        = array('@guest');


$auth['test']['password']    = 'c03dc1575140c6d59a8cea5cf0828ac7';
$auth['test']['permissions'] = array();
$auth['test']['groups']      = array('@editor');
$auth['test']['fullname']    = 'Test User';
$auth['test']['email']       = '';

$auth['admin']['password']    = 'c03dc1575140c6d59a8cea5cf0828ac7';
$auth['admin']['permissions'] = array();
$auth['admin']['groups']      = array('@admin');
$auth['admin']['fullname']    = 'Administrator';
$auth['admin']['email']       = '';

///////////////////////
//    G R O U P S    //
///////////////////////
$auth['@admin']['permissions']  = array('show', 'img', 'search', 'edit', 'new', 'save', 'delete', 'delete_many', 'select_letter', 'select_offset',
                                        'cat_select', 'cat_add', 'cat_del', 'cat_del_empty', 'cat_add_contacts', 'cat_del_contacts',
                                        'import_vcard', 'export_vcard', 'export_vcard_cat', 'export_csv_cat', 'export_ldif_cat', 
                                        'login', 'logout',
                                        'debug', 'check');
                                        
$auth['@editor']['permissions'] = array('show', 'img', 'search', 'edit', 'new', 'save', 'delete', 'delete_many', 'select_letter', 'select_offset',
                                        'cat_select', 'cat_add', 'cat_del', 'cat_del_empty', 'cat_add_contacts', 'cat_del_contacts',
                                        'import_vcard', 'export_vcard', 'export_vcard_cat', 'export_csv_cat', 'export_ldif_cat',
                                        'login', 'logout');

$auth['@guest']['permissions']  = array('show', 'img', 'search', 'select_letter', 'select_offset',
                                        'cat_select', 
                                        'export_vcard', 'export_vcard_cat',
                                        'login', 'logout');


?>
