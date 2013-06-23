<?php
    /**
     * iAddressBook variables and includes
     *
     * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
     * @author     Clemens Wacha <clemens.wacha@gmx.net>
     */
    
    if(!defined('AB_BASEDIR')) define('AB_BASEDIR',realpath(dirname(__FILE__).'/../../'));
    if(!defined('AB_CONFDIR')) define('AB_CONFDIR',AB_BASEDIR.'/conf');
    if(!defined('AB_LANGDIR')) define('AB_LANGDIR',AB_BASEDIR.'/lib/lang');
    if(!defined('AB_TPLDIR')) define('AB_TPLDIR',AB_BASEDIR.'/lib/tpl');
    if(!defined('AB_SQLDIR')) define('AB_SQLDIR',AB_BASEDIR.'/lib/sql');
    if(!defined('AB_IMAGEDIR')) define('AB_IMAGEDIR',AB_BASEDIR.'/var/images');
    if(!defined('AB_IMPORTDIR')) define('AB_IMPORTDIR',AB_BASEDIR.'/var/import');
    if(!defined('AB_STATEDIR')) define('AB_STATEDIR',AB_BASEDIR.'/var/state');

    require_once(AB_BASEDIR.'/lib/default/config.php');
?>