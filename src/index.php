<?php
/**
 * iAddressBook mainscript
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Clemens Wacha <clemens@wacha.ch>
 */

    if(!defined('AB_BASEDIR')) define('AB_BASEDIR',__DIR__);
    require_once(AB_BASEDIR.'/lib/php/include.php');
    require_once(AB_BASEDIR.'/lib/php/common.php');
    require_once(AB_BASEDIR.'/lib/php/init.php');
    require_once(AB_BASEDIR.'/lib/php/db.php');
    require_once(AB_BASEDIR.'/lib/php/module_auth.php');
    require_once(AB_BASEDIR.'/lib/php/module_translator.php');
    require_once(AB_BASEDIR.'/lib/php/actions.php');

    if(!file_exists(AB_CONFDIR.'/config.php')) {
        // no config found. redirect to installer
        header('Location: '.AB_URL.'install.php?do=reset');
        exit;
    }

    $webapp = "index.php";

    function doPost($request) {
        global $ID;                 // ID of current selected contact or 0 if no contact is selected
        global $ACT;                // Action that should be executed (e.g. show)
        global $AB;                 // Addressbook class
        global $QUERY;              // Currently active search
        global $contact;            // Current selected contact class, or false if no contact selected
        global $conf;               // configuration array
        global $CAT;                // Category class
        global $CAT_ID;             // Current selected category ID or 0 if no category is selected (all)
        global $categories;         // FIXME: array for all categories, but seems to be used only locally (remove?)
        global $contactlist;        // array of all contacts (gets filled by act_search)
        global $contactlist_offset; // offset int for contact display
        global $contactlist_limit;  // int of max displayed contacts (per page)
        global $contactlist_letter; // selected letter from ABC for contact list
        global $securitycontroller; // class for authentication and authorization

        //session_start(); // session already started in init.php
        
        $ACT = getAction($request);
        $_SESSION['viewname'] = getViewName($request);
        $_SESSION['action'] = $ACT;

        //
        // access control
        //
        $securitycontroller = SecurityController::getInstance();
        $securitycontroller->init();

        // check if login is successful. redirect to login page if necessary
        if(!login($request))
            return;
        
        // accept everything if authentication is disabled
        if($conf['auth_enabled']) {
            // test permission for action
            if(!$securitycontroller->authorize($_SESSION['accountid'], $ACT)) {
                msg(lang('action_not_allowed') . " ($ACT)", -1);
                // user is not allowed to execute $ACT, change to 'show'
                $ACT = 'show';
            }
            // test permission for view
            if(!$securitycontroller->authorize($_SESSION['accountid'], $_SESSION['viewname'])) {
                msg(lang('action_not_allowed') . " (".$_SESSION['viewname'].")", -1);
                // user is not allowed to view $ACT, change to '/home'
                $_SESSION['viewname'] = '/home';
            }
        }

        switch($ACT) {
            case 'logout':
                // check if we logout
                logout($request);
                return;
                break;
            case 'select_offset':
                // contact list offset
                if(isset($request['o'])) {
                    $_SESSION['o'] = (int)trim($request['o']);
                }
                break;
            case 'select_letter':
                // letter filter
                if(isset($request['l'])) {
                    $_SESSION['l'] = trim($request['l']);
                    $_SESSION['o'] = 0;
                }
                break;
            case 'search': 
                // remember search query
                $_SESSION['q'] = $request['q'];
                $_SESSION['o'] = 0;
                $_SESSION['l'] = 0;
                break;
            case 'cat_select':
                // remember selected category
                $_SESSION['cat_id'] = (int)trim($request['cat_id']);
                $_SESSION['o'] = 0;
                $_SESSION['l'] = 0;
                break;
            case 'debug':
                $conf['debug'] = 1;
                break;
            default:
        }

        $QUERY = array_get($_SESSION, 'q', '');
        $contactlist_offset = array_get($_SESSION, 'o', 0);
        $contactlist_letter = array_get($_SESSION, 'l', 0);

        $CAT_ID = (int)array_get($_SESSION, 'cat_id', 0);

        if($ACT == 'cat_del') {
            $_SESSION['cat_id'] = 0;
        }

        // remember selected person, but prefer ID from $_REQUEST
        if(isset($_REQUEST['id']))
            $_SESSION['id'] = (int)$_REQUEST['id'];
            
        $ID = array_get($_SESSION, 'id', 0);
        
        if($ACT == 'reset') {
            // reset the internal state
            $_SESSION['id'] = 0;
            $_SESSION['cat_id'] = 0;
            $_SESSION['q'] = '';
            $_SESSION['o'] = 0;
            $_SESSION['l'] = 0;
            $ID = 0;
            $CAT_ID = 0;
            $QUERY = '';
            $contactlist_offset = 0;
            $contactlist_letter = 0;
        }

        $contactlist = array();         // key = contact->id, value = contact
        $contactlist_limit = $conf['contactlist_limit'];

        $contact = false;               // the contact
        $categories = array();          // all categories

        $ABcatalog = new Addressbooks();
        $books = $ABcatalog->getAddressBooksForUser($_SESSION['accountid']);
        $bookId = -1;
        if(empty($books)) {
            $bookId = $ABcatalog->createAddressbook($_SESSION['accountid']);
        } else {
            $bookId = $books[0]['id'];
        }

        $AB = new Addressbook($bookId);
        $CAT = new Categories();

        //$shop->doAction($ACT, $request);
        $translator = Translator::getInstance();
        $translator->init();

        //do the work
        $securitycontroller->do_action($request, $ACT);
        $translator->do_action($request, $ACT);
        act_dispatch($request, $ACT);
        
        renderAndRedirect($_SESSION['viewname'], $request);
    }

    function renderAndRedirect($viewname, $request) {       
        if($viewname == getViewName($request)) {
            render($viewname, $request);
            return;
        }
        msg("redirect");
        $webappuri = getWebAppURI();
        header("Location: {$webappuri}{$viewname}");
    }
    
    function render($viewname, $request) {
        global $ID;
        global $ACT;
        global $AB;
        global $QUERY;
        global $contact;
        global $CAT;
        global $CAT_ID;
        global $categories;
        global $contactlist;
        global $contactlist_offset;
        global $contactlist_limit;
        global $contactlist_letter;
        global $securitycontroller;

        global $conf;
        global $defaults;
        global $meta;
        global $webapp;
        $action = hsc(getAction($request));
        $baseurl = getBaseURL(true);
        $baseuri = getBaseURL();
        $webappuri = getWebAppURI();
        $basedir = AB_BASEDIR;
        $tpldir = AB_TPLDIR.'/'.$conf['template'];
        //msg_clear();
        session_write_close();
        
        $viewdocument = getViewDocument($viewname);
        /*
        print("<pre>");
        print("action: $action\n");
        print("baseurl: $baseurl\n");
        print("baseuri: $baseuri\n");
        print("webappuri: $webappuri\n");
        print("basedir: $basedir\n");
        print("viewdocument: $viewdocument\n");
        print("</pre>");
        */

        header('Content-Type: text/html; charset=utf-8');
        include($tpldir.'/main.tpl');
    }

    // return true if login is successful, false if failed
    function login($request) {
        global $_SESSION;
        global $conf;
        global $securitycontroller;

        if($conf['auth_enabled'] == false) {
            $_SESSION['accountid'] = 'guest';
            $_SESSION['account'] = $securitycontroller->get_account('guest');
            return true;
        }

        // authentication enabled

        if(array_get($_SESSION, 'authorized', false)) {
            // the user is already logged in
            $account = $securitycontroller->get_account($_SESSION['accountid']);
            $_SESSION['account'] = $account;
            if($_SESSION['viewname'] == '/login')
                $_SESSION['viewname'] = '/home';
            return true;
        }

        // check if login is successful
        $action = $_SESSION['action'];
        $viewname = $_SESSION['viewname'];
        $accountid = $securitycontroller->authenticate($request);
        if($accountid == null && $conf['auth_allow_guest']) {
            // map to guest unless user tries to login or login page is displayed
            if($action != 'login' && $viewname != '/login')
                $accountid = 'guest';
        }

        $account = $securitycontroller->get_account($accountid);
        if($account == null) {
            // authentication failed or no roles present
            $_SESSION['authorized'] = false;
            unset($_SESSION['accountid']);
            unset($_SESSION['account']);
            if($action == 'login') {
                // wrong username or wrong password supplied
                msg(lang('wrong_userpass'), -1);
            }
            render('/login', $request);
            return false;
        }

        $_SESSION['authorized'] = true;
        $_SESSION['accountid'] = $accountid;
        $_SESSION['account'] = $account;
        if($_SESSION['viewname'] == '/login')
            $_SESSION['viewname'] = '/home';

        return true;
    }

    function logout($request) {
        //msg("logout!");

        $_SESSION = array();
        $_SESSION['authorized'] = 0;
        unset($_SESSION['accountid']);
        unset($_SESSION['account']);

        if(isset($_COOKIE[AB_COOKIE])) {
            setcookie(AB_COOKIE, '', time()-600000, '/');
        }

        session_destroy();
        
        render('/logout', $request);
    }

    function getViewName($request) {
        global $webapp;
        $request_uri = strtolower($_SERVER['REQUEST_URI']);
        $webappuri = getWebAppURI();
        $baseuri = getBaseURL();
        
        if (substr($request_uri, 0, strlen($webappuri)) == $webappuri)
            $request_uri = substr($request_uri, strlen($webappuri));

        if (substr($request_uri, 0, strlen($baseuri)) == $baseuri)
            $request_uri = substr($request_uri, strlen($baseuri));
        
        list($view, $dummy) = explode("?", $request_uri . '?');
        
        $view = preg_replace("/^([^\/])/", "/\$1", $view);
        $view = preg_replace("/\/$/", "", $view);
        $validchars = "/[^a-zA-Z0-9\/_-]/";
        $view = preg_replace($validchars, "_", $view);

        if(strlen($view) == 0)
            $view = "/home";

        //msg("request_uri=$request_uri view=$view webappuri=$webappuri");
        return $view;
    }
    
    function getViewDocument($viewname) {
        global $conf;
        $viewdir = AB_TPLDIR.'/'.$conf['template'].'/views';
        $viewdocument = $viewdir . $viewname . '.tpl';
        if(@is_readable($viewdocument)) {
            return $viewdocument;
        }
        return $viewdir .'/internal/notfound.tpl';
    }
    
    function getAction($request) {
        return array_get($request, "do", "show");
    }
        
    db_init();
    if(!db_open()) {
    	msg("DB connection failed. Stop.", -1);
    	header('Content-Type: text/html; charset=utf-8');
    	print msg_text();
    	exit();
    }

    doPost($_REQUEST);

    db_close();

?>