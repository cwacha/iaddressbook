<?php

 /**
 * This is the AddressBook's Meta Configuration file
 * This is a piece of PHP code so PHP syntax applies!
 *
 *
 */

// read lang and template directory
function list_dirs($dir, $base=AB_INC) {
    return (is_dir($base.'/'.$dir) && (substr($dir, 0, 1) != '.'));
}
function is_lang_dir($dir) { return list_dirs($dir, AB_INC.'lang'); }
function is_tpl_dir($dir) { return list_dirs($dir, AB_INC.'tpl'); }
$lang_dirs = array_filter(scandir(AB_INC.'lang'), is_lang_dir);
$tpl_dirs = array_filter(scandir(AB_INC.'tpl'), is_tpl_dir);


$meta['fmode'] = array('string');
$meta['dmode'] = array('string');
$meta['basedir'] = array('string');
$meta['baseurl'] = array('string');
$meta['dbtype'] = array('multichoice', '_choices' => array('mysql', 'sqlite'));
$meta['dbname'] = array('string');
$meta['dbserver'] = array('string');
$meta['dbuser'] = array('string');
$meta['dbpass'] = array('string');
$meta['dbtable_ab'] = array('string');
$meta['dbtable_cat'] = array('string');
$meta['dbtable_catmap'] = array('string');
$meta['dbtable_truth'] = array('string');
$meta['dbtable_sync'] = array('string');
$meta['dbtable_action'] = array('string');
$meta['lang'] = array('multichoice', '_choices' => $lang_dirs);
$meta['title'] = array('string');
$meta['template'] = array('multichoice', '_choices' => $tpl_dirs);
$meta['bdformat'] = array('string');
$meta['dformat'] = array('string');
$meta['lastfirst'] = array('onoff');
$meta['photo_resize'] = array('string');
$meta['photo_size'] = array('string');
$meta['photo_format'] = array('string');
$meta['map_link'] = array('textarea');
$meta['contactlist_limit'] = array('string');
$meta['bday_advance_week'] = array('string');
$meta['canonical'] = array('onoff');
$meta['auth_enabled'] = array('onoff');
$meta['auth_allow_guest'] = array('onoff');
$meta['im_convert'] = array('string');
$meta['photo_enable'] = array('onoff');
$meta['session_name'] = array('string');
$meta['mark_changed'] = array('onoff');
$meta['debug'] = array('onoff');
$meta['debug_db'] = array('onoff');
$meta['vcard_fb_enc']= array('string');
$meta['ldif_base'] = array('textarea');
$meta['ldif_mozilla'] = array('onoff');
$meta['xmlrpc_enable'] = array('onoff');


?>