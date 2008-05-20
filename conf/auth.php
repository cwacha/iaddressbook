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

// Idee User: username:fullname:email:password:group1,group2:perm1,perm2
// Idee Gruppe: gruppe:::::perm1,perm2



///////////////////////
//     U S E R S     //
///////////////////////
// guest user - if you have activated guest access, these are the permissions
$auth['guest']['permissions']   = array();
$auth['guest']['groups']        = array('@guest');


// This is an example user with
// username = fred
// password = banana
//
//   Fred has additional permissions ('check') and is also part of the group '@editor'.
//   This means that Fred is able to execute all actions that the @editor group may
//   execute as well as the action check.
//
// For an explanation of all actions see the documentation at http://wacha.ch/addressbook:docs#permissions_access_controls

// $auth['fred']['password']    = '72b302bf297a228a75730123efef7c41';
// $auth['fred']['permissions'] = array( 'check' );
// $auth['fred']['groups']      = array('@editor');
// $auth['fred']['fullname']    = 'Fred the Geek';
// $auth['fred']['email']       = 'fred@geeks.com';


// $auth['your_admin_username']['password']    = '--fill-in-your-password--';
// $auth['your_admin_username']['permissions'] = array();
// $auth['your_admin_username']['groups']      = array('@admin');
// $auth['your_admin_username']['fullname']    = 'Administrator';
// $auth['your_admin_username']['email']       = '';

$auth['hermann']['password']    = 'a08f6dcee64691d6907b0e3560046565';
$auth['hermann']['permissions'] = array();
$auth['hermann']['groups']      = array('@admin');
$auth['hermann']['fullname']    = 'Hermann Giesser';
$auth['hermann']['email']       = '';

$auth['ingrid']['password']    = '0c5e8dc20d5f7778d3da083ceb16e841';
$auth['ingrid']['permissions'] = array();
$auth['ingrid']['groups']      = array('@admin');
$auth['ingrid']['fullname']    = 'Ingrid Giesser';
$auth['ingrid']['email']       = '';


///////////////////////
//    G R O U P S    //
///////////////////////
$auth['@admin']['permissions']  = array('show', 'img', 'search', 'edit', 'new', 'save', 'delete', 'delete_many', 'select_letter', 'select_offset',
                                        'cat_select', 'cat_add', 'cat_del', 'cat_del_empty', 'cat_add_contacts', 'cat_del_contacts',
                                        'import_vcard', 'import_folder', 'export_vcard', 'export_vcard_cat', 'export_csv_cat', 'export_ldif_cat', 
                                        'login', 'logout', 'reset',
                                        'debug', 'check', 'info');

$auth['@editor']['permissions'] = array('show', 'img', 'search', 'edit', 'new', 'save', 'delete', 'delete_many', 'select_letter', 'select_offset',
                                        'cat_select', 'cat_add', 'cat_del', 'cat_del_empty', 'cat_add_contacts', 'cat_del_contacts',
                                        'import_vcard', 'import_folder', 'export_vcard', 'export_vcard_cat', 'export_csv_cat', 'export_ldif_cat',
                                        'login', 'logout', 'reset');

$auth['@guest']['permissions']  = array('show', 'img', 'search', 'select_letter', 'select_offset',
                                        'cat_select', 
                                        'export_vcard', 'export_vcard_cat', 'export_csv_cat', 'export_ldif_cat',
                                        'login', 'logout', 'reset');

    
?>