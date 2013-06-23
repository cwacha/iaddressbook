<?php
/**
* PHP iAddressBook Installation Script
*
* @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
* @author     Clemens Wacha <clemens.wacha@gmx.net>
*/


if(!defined('AB_BASEDIR')) define('AB_BASEDIR',realpath(dirname(__FILE__)));
require_once(AB_BASEDIR.'/lib/php/include.php');
require_once(AB_BASEDIR.'/lib/php/init.php');
require_once(AB_BASEDIR.'/lib/php/db.php');
require_once(AB_BASEDIR.'/lib/php/template.php');
require_once(AB_BASEDIR.'/lib/php/common.php');

// the state of the script
global $state;
$state = array();

function stop_if_installed() {
    if(file_exists(AB_CONFDIR.'/config.php')) {
        html_header();
        echo '<div style="min-height: 350px; width: 80%; margin: 0 auto; text-align: left; font-size: 110%;">';
        step_disabled();
        echo '</div>';
        html_footer();
        exit;
    }
}

function init_session_defaults() {
    global $state;
    global $defaults;
    global $conf;
    
    // initial state
    $state = array();
    $state['action'] = 'step_welcome';
    $state['step']   = 1;
    $state['db_created'] = 0;
    
    $conf = $defaults;
}

function load_session() {
    global $state;
    global $conf;
    
    // restore session
    if(is_array($_SESSION['state'])) $state = $_SESSION['state'];
    if(is_array($_SESSION['config']))  $conf = $_SESSION['config'];
}

function save_session() {
    global $state;
    global $conf;

    // save session
    $_SESSION['state'] = $state;
    $_SESSION['config'] = $conf;
}

function html_header() {
    global $conf;
    global $lang;
    global $state;
    global $meta;
    
    ?>
    <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $conf['lang']; ?>"
     lang="<?php echo $conf['lang']; ?>" dir="<?php echo $lang['direction']; ?>">
    <head>
        <title><?php echo $conf['title']; ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    
        <link rel="shortcut icon" href="<?php echo AB_TPL; ?>images/favicon.ico" />
        <link rel="stylesheet" media="screen" type="text/css" href="<?php echo AB_TPL; ?>design.css" />
        
        <script type="text/javascript" language="JavaScript">
        <!--
        function do_action($action) {
            if($action != null) {
                document.actions.elements["do"].value = $action;
            }
            //alert("do_action: " + document.actions.elements["do"].value);
            document.actions.submit();
        }
        function do_set($key, $value) {
            if($value != null) {
                document.actions.elements[$key].value = $value;
                //alert("setting " + $key + " = " + document.actions.elements[$key].value);
            }
        }
        function do_setbool($key, $value) {
            if($value != null) {
                if($value) {
                    document.actions.elements[$key].value = 1;
                } else {
                    document.actions.elements[$key].value = 0;
                }
                //alert("setting " + $key + " = " + document.actions.elements[$key].value);
            }
        }
        -->
        </script>
    </head>
    <body>
    
    <form name="actions" action="" method="post">
        <input type="hidden" name="do" value="show" />

        <input type="hidden" name="fmode" value="<?php echo $conf['fmode']; ?>" />
        <input type="hidden" name="dmode" value="<?php echo $conf['dmode']; ?>" />
        <input type="hidden" name="basedir" value="<?php echo $conf['basedir']; ?>" />
        <input type="hidden" name="baseurl" value="<?php echo $conf['baseurl']; ?>" />

        <input type="hidden" name="dbtype" value="<?php echo $conf['dbtype']; ?>" />
        <input type="hidden" name="dbname" value="<?php echo $conf['dbname']; ?>" />
        <input type="hidden" name="dbserver" value="<?php echo $conf['dbserver']; ?>" />
        <input type="hidden" name="dbuser" value="<?php echo $conf['dbuser']; ?>" />
        <input type="hidden" name="dbpass" value="<?php echo $conf['dbpass']; ?>" />
        <input type="hidden" name="debug_db" value="<?php echo $conf['debug_db']; ?>" />
        <input type="hidden" name="dbtable_ab" value="<?php echo $conf['dbtable_ab']; ?>" />
        <input type="hidden" name="dbtable_cat" value="<?php echo $conf['dbtable_cat']; ?>" />
        <input type="hidden" name="dbtable_catmap" value="<?php echo $conf['dbtable_catmap']; ?>" />
        <input type="hidden" name="dbtable_truth" value="<?php echo $conf['dbtable_truth']; ?>" />
        <input type="hidden" name="dbtable_sync" value="<?php echo $conf['dbtable_sync']; ?>" />
        <input type="hidden" name="dbtable_action" value="<?php echo $conf['dbtable_action']; ?>" />

        <input type="hidden" name="lang" value="<?php echo $conf['lang']; ?>" />
        <input type="hidden" name="title" value="<?php echo $conf['title']; ?>" />
        <input type="hidden" name="template" value="<?php echo $conf['template']; ?>" />
        <input type="hidden" name="bdformat" value="<?php echo $conf['bdformat']; ?>" />
        <input type="hidden" name="dformat" value="<?php echo $conf['dformat']; ?>" />
        <input type="hidden" name="lastfirst" value="<?php echo $conf['lastfirst']; ?>" />
        <input type="hidden" name="photo_resize" value="<?php echo $conf['photo_resize']; ?>" />
        <input type="hidden" name="photo_size" value="<?php echo $conf['photo_size']; ?>" />
        <input type="hidden" name="photo_format" value="<?php echo $conf['photo_format']; ?>" />
        <input type="hidden" name="map_link" value="<?php echo $conf['map_link']; ?>" />
        <input type="hidden" name="contactlist_limit" value="<?php echo $conf['contactlist_limit']; ?>" />
        <input type="hidden" name="bday_advance_week" value="<?php echo $conf['bday_advance_week']; ?>" />

        <input type="hidden" name="canonical" value="<?php echo $conf['canonical']; ?>" />
        <input type="hidden" name="auth_enabled" value="<?php echo $conf['auth_enabled']; ?>" />
        <input type="hidden" name="auth_allow_guest" value="<?php echo $conf['auth_allow_guest']; ?>" />
        <input type="hidden" name="im_convert" value="<?php echo $conf['im_convert']; ?>" />
        <input type="hidden" name="photo_enable" value="<?php echo $conf['photo_enable']; ?>" />
        <input type="hidden" name="session_name" value="<?php echo $conf['session_name']; ?>" />
        <input type="hidden" name="mark_changed" value="<?php echo $conf['mark_changed']; ?>" />
        <input type="hidden" name="debug" value="<?php echo $conf['debug']; ?>" />

        <input type="hidden" name="vcard_fb_enc" value="<?php echo $conf['vcard_fb_enc']; ?>" />
        <input type="hidden" name="ldif_base" value="<?php echo $conf['ldif_base']; ?>" />
        <input type="hidden" name="ldif_mozilla" value="<?php echo $conf['ldif_mozilla']; ?>" />
        <input type="hidden" name="xmlrpc_enable" value="<?php echo $conf['xmlrpc_enable']; ?>" />
 
    </form>
    
    <div class="mainview">
        <?php echo html_msgarea(); ?>

        <!-- Begin Logo -->
        <table width="100%">
            <tr>
                <td>
                    <div class="logo">
                        <img src="<?php echo AB_TPL; ?>images/logo.png">
                    </div>
                </td>
                <td>
                    <div class="title"><?php echo $lang['installation']; ?></div>
                </td>
                <td>
                    <?php echo $lang['lang']; ?>:<br/>
                    <?php html_select($conf['lang'], $meta['lang']['_choices'], "do_set('lang',this.value);do_action('show');"); ?>
                </td>
            </tr>
        </table>
        <!-- End Logo -->
    <?php

}

function html_footer() {
    global $_SESSION;
    global $conf;
    ?>
        <div style="height: 10px; clear: both;" ></div>
        <!-- Begin Footer --> 
        <div class="separator">&nbsp;</div>
        <div class="footer">
            <a href='http://iaddressbook.org/'>PHP iAddressBook <?php echo get_version(); ?></a>
        </div>
        <!-- End Footer -->
    </div>
    </body>
    </html>
    <?php
}

function html_install_step() {
    global $state;
    global $lang;
    
    $step = $state['step'];
    $step_max = 5;
    $space = (string)(100 / $step_max);
    $color = '';
    
    echo '<div style="width:80%; height:100px; margin: 0 auto; font-size: 500%; font-weight:bold;">';
    for($t = 1; $t <= $step_max; $t++) {
        if($t == $state['step']) {
            $color = 'color:#000;';
        } else {
            $color = 'color:#bebebe;';
        }
        echo "<div style='float:left; width: $space%; $color'>";
        echo $t;
        echo "<div style='font-size: 20%;'>";
        switch ($t) {
            case 1:
                echo $lang['step_welcome'];
                break;
            case 2:
                echo $lang['step_check'];
                break;
            case 3:
                echo $lang['step_install'];
                break;
            case 4:
                echo $lang['step_configure'];
                break;
            case 5:
                echo $lang['step_finish'];
                break;
            default:
                break;
        }
        echo "</div>";
        echo "</div>";
    }
    echo '</div>';
}

function act_content() {
    global $state;
    
    echo '<div style="min-height: 350px; width: 80%; margin: 0 auto; text-align: left; font-size: 110%;">';
    switch($state['step']) {
        case 1:
            step_welcome();
            break;
        case 2:
            step_check();
            break;
        case 3:
            step_install();
            break;
        case 4:
            step_configure();
            break;
        case 5:
            step_finish();
            break;
        default:
            step_title("step not defined.");
    }
    echo '</div>';
}

function step_prev($target='prev', $title='Back') {
    global $lang;

    if(empty($title) || empty($target)) return;
    if($title == 'Back') $title = $lang['back'];
    echo '<div style="height: 50px; margin: 0 auto; font-size: 250%; padding-top: 50px; float:left;">';
    html_link($title, "do_action('".hsc($target)."')");
    echo '</div>';    
}

function step_next($target='next', $title='Next') {
    global $lang;
    
    if(empty($title) || empty($target)) return;
    if($title == 'Next') $title = $lang['next'];
    echo '<div style="height: 50px; margin: 0 auto; font-size: 250%; padding-top: 50px; float:right;">';
    html_link($title, "do_action('".hsc($target)."')");
    echo '</div>';
}

function step_title($title) {
    echo "<h1><b>$title</b></h1>";
}

function step_welcome() {
    global $lang;
    
    step_title($lang['step_welcome']);

    echo $lang['welcome_message'];
    
    step_title($lang['step_prerequisites']);
    echo $lang['welcome_prerequisites'];

    step_prev('');
    step_next('step_check');
}

function step_check() {
    global $VERSION;
    global $conf;
    global $lang;
    global $state;
    $errors = 0;
    
    step_title($lang['step_check']);

    echo '<table border="0"><tr><td width="45%">';
    step_title($lang['step_tests']);
    echo '<div>';
    
    if(!is_writable(AB_STATEDIR)) {
        imsg(str_replace('$1', AB_STATEDIR, $lang['error_statefolder']), -1);
        $errors++;
    }
    
    if(!is_writable(AB_IMAGEDIR)) {
        imsg(str_replace('$1', AB_IMAGEDIR, $lang['error_imagefolder']), -1);
        $errors++;
    }
    
    if(!is_writable(AB_IMPORTDIR)) {
        imsg(str_replace('$1', AB_IMPORTDIR, $lang['error_importfolder']), 0);
    }
    
    if(!is_writeable(AB_CONFDIR)) {
        imsg(str_replace('$1', AB_CONFDIR, $lang['error_conffolder']), -1);
        $errors++;
    }
    
    $use_im = 0;
    $use_gd = 0;
    if(!empty($conf['im_convert'])) {
        if(is_readable($conf['im_convert'])) {
            // check popen()
            $pipe = popen($conf['im_convert']." -version", "r");
            if(is_resource($pipe)) {
                while(!feof($pipe)) $output .= fread($pipe, 8192);
                pclose($pipe);
                imsg(str_replace('$1', $conf['im_convert'], $lang['info_im']), 1);
                imsg(str_replace('$1', $output, $lang['info_im_version']), 1);
                $use_im = 1;
            } else {
                imsg(str_replace('$1', $conf['im_convert'], $lang['error_im']), 0);
            }
        } else {
            imsg(str_replace('$1', $conf['im_convert'], $lang['error_im2']), 0);
        }
    }
    
    if(function_exists('gd_info')) {
        $gd = gd_info();
        imsg(str_replace('$1', $gd['GD Version'], $lang['info_gd']), 1);
        $use_gd = 1;
    }
    
    if($use_im == 1 || $use_gd == 1) {
        if($use_im == 1) {
            imsg($lang['info_usingim'], 1);
        } else {
            imsg($lang['info_usinggd'], 1);
        }
    } else {
        imsg($lang['error_usingimgd'], 1);
    }

    if(function_exists('iconv')) {
        imsg(str_replace('$1', ICONV_VERSION, $lang['info_iconv']), 1);
    } else {
        imsg($lang['error_iconv']);
    }

    if(function_exists('sqlite_open')) {
        // pre-select sqlite if it is available
        $conf['dbtype'] = 'sqlite';
        $conf['dbserver'] = 'addressbook.sqlite';
        imsg(str_replace('$1', sqlite_libversion(), $lang['info_sqlite']), 1);
    } else {
        imsg($lang['error_sqlite']);
    }
    
    echo "&nbsp;<p>";
    html_button($lang['retry'], "do_action('show')");
            
    echo '</div>';
    echo '</td><td width="10%"></td><td style="vertical-align: top;">';

    step_title($lang['step_results']);

    if($errors > 0) {
        if($errors > 1) {
            imsg(str_replace('$1', $errors, $lang['errors_total']), -1);
        } else {
            imsg(str_replace('$1', $errors, $lang['error_total']), -1);
        }
        imsg($lang['configure_errors'], -1);
    } else {
        imsg($lang['configure_ok'], 1);
    }

    echo '</td></tr></table>';
    
    step_prev('step_welcome');
    if($errors == 0) step_next('step_install');
}

function step_install() {
    global $state;
    global $lang;
    global $conf;
    
    step_title($lang['step_install']);

    $col = array();

    if($state['action'] == 'create_db') setup_db();

    html_sform_begin();
    html_sform_line('dbtype');
    html_sform_line('dbname');
    html_sform_line('dbserver');
    html_sform_line('dbuser');
    html_sform_line('dbpass');
    html_sform_line('debug_db');
    html_sform_end();
        
    if(!$state['db_created']) {
        echo "&nbsp;<p>";
        html_button($lang['install_createdb'], "do_action('create_db')");
    }

    step_prev('step_check');
    if($state['db_created']) step_next('step_configure');
}

function step_configure() {
    global $defaults;
    global $lang;
    
    step_title($lang['step_configure']);

    html_sform_begin();
    foreach($defaults as $key => $value) {
        if(!in_array($key, array('dbtype', 'dbname', 'dbserver', 'dbuser', 'dbpass',
            'dbtable_ab', 'dbtable_cat', 'dbtable_catmap', 'dbtable_truth', 'dbtable_sync',
            'dbtable_action', 'dbtable_users', 'debug', 'debug_db'))) {
            html_sform_line($key);
        }
    }
    html_sform_end();

    step_prev('step_install');
    step_next('step_finish', $lang['finish']);
}

function step_finish() {
    global $defaults;
    global $conf;
    global $state;
    global $lang;
    
    step_title($lang['step_finish']);
        
    // save config file (only add vars that are not on default value)
    $new_config = array();
    foreach($conf as $key => $value) {
        if($conf[$key] != $defaults[$key]) {
            $new_config[$key] = $value;
        }
    }
    
    if(!$state['config_saved']) {
        // make sure we have creation modes setup correctly
        init_creationmodes();
        $ret = save_config($new_config);
        if($ret) {
            $state['config_saved'] = 1;
        }
        
        // fix fmode if using sqlite!
        if($conf['dbtype'] == 'sqlite') {
            fix_fmode(AB_STATEDIR.'/'.$conf['dbserver']);
        }
    }
    
    if(!$state['config_saved']) {
        echo "&nbsp;<p>";
        html_button($lang['retry'], "do_action('step_finish')");
        step_prev('step_configure');
        step_next('');
    } else {
        echo $lang['finish_message'];
        //step_prev('step_configure');
        step_next('open_addressbook', $lang['step_open_ab']);    
    }
    
}

function step_disabled() {
    global $lang;
    
    step_title($lang['step_disabled']);

    echo $lang['disabled_message'];
    
    step_next('open_addressbook', $lang['step_open_ab']);        
}

function save_config($config, $filename = 'config.php', $overwrite = 0) {
    global $lang;
    
    if(!is_array($config) || empty($filename)) {
        imsg("Internal error while saving configuration: no array given or filename empty ($filename)", -1);
        return false;
    }

    $header = array();
    $header[] = "/**";
    $header[] = " * This is the AddressBook's local configuration file";
    $header[] = " * This is a piece of PHP code so PHP syntax applies!";
    $header[] = " *";
    $header[] = " */\n\n";

    $file = AB_CONFDIR.'/'.$filename;
    if(!$overwrite && file_exists($filename)) {
        imsg(str_replace('$1', $file, $lang['error_saveconfig2']), -1);
        return false;
    }
    $fd = fopen($file, "w");
    if(!$fd) {
        imsg(str_replace('$1', $file, $lang['error_saveconfig']), -1);
        return false;
    }
    fwrite($fd, "<?php\n\n");
    
    foreach($header as $line) {
        fwrite($fd, $line . "\n");
    }

    $data = array_to_text($config, '$conf');
    fwrite($fd, $data);

    fwrite($fd, "\n\n?>");
    fclose($fd);
    fix_fmode($file);

    imsg($lang['info_saveconfig'], 1);
    return true;
}

function setup_db() {
    global $state;
    global $conf;
    global $lang;

    db_init($conf);
    db_open();
    if(db_createtables()) {
        imsg($lang['info_db1'], 1);
        imsg($lang['info_db2'], 1); 
        $state['db_created'] = 1;
        $conf['debug_db'] = 0;
    }
    db_close();
}

function html_sform_begin() {
    echo "<table style='width: 100%;'>";
    echo "<colgroup>";
    echo "<col style='width: 30%;'";
    echo "<col style=''";
    echo "<col style=''";
}

function html_sform_line($col) {
    global $conf;
    global $defaults;
    global $meta;
    global $lang;

    echo "<tr>";

    echo "<td style='text-align: right; vertical-align: top; padding-right: 2em;'>".$lang[$col]."</td>";
    echo "<td style='vertical-align: top;'>";
    switch($meta[$col][0]) {
        case 'multichoice':
            html_select($conf[$col], $meta[$col]['_choices'], "do_set('".$col."',this.value)");
            break;
        case 'onoff':
            html_onoff($conf[$col], "do_setbool('".$col."',this.value)"); 
            break;
        case 'string':
            html_edit($conf[$col], "do_set('".$col."',this.value)");
            break;
        case 'textarea':
        default:
            html_textarea($conf[$col], "do_set('".$col."',this.value)", '', 5, 30);
            break;
    }
    echo "</td>";
    echo "<td style='text-align: left; vertical-align: top; padding-left: 2em; padding-bottom:8px;'>";
    echo $lang[$col.'_help'];
    echo " [".$defaults[$col]."]";
    echo "</td>";

    echo "</tr>";
}

function html_sform_end() {
    echo "</table>";
}

function html_button($name='no name', $action="alert('clicked!')" ) {
    echo "<button name='html_button' type='button' onclick=\"".hsc($action).";\" class='button'>";
    echo hsc($name);
    echo "</button>";
}

function html_link($name='no name', $action="alert('clicked!')" ) {
    echo "<a href='#' onclick=\"".hsc($action).";return false;\">";
    echo hsc($name);
    echo "</a>";
}

function html_edit($value='', $onchange='', $id='') {
    $id_val='';
    $value_val='';
    $onchange_val='';
    if(!empty($id)) $id_val="id=\"".hsc($id)."\"";
    if(!empty($value)) $value_val="value=\"".hsc($value)."\"";
    if(!empty($onchange)) $onchange_val="onchange=\"".hsc($onchange)."\"";
    
    echo "<input type='text' class='text' size='30' $id_val $value_val $onchange_val />";
}

function html_onoff($value=false, $onchange='', $id='') {
    $id_val='';
    $value_val='';
    $onchange_val='';
    if(!empty($id)) $id_val="id=\"".hsc($id)."\"";
    if($value) $value_val="checked='checked'";
    if(!empty($onchange)) $onchange_val="onchange=\"".hsc($onchange)."\"";
    echo "<input type='checkbox' $id_val $value_val $onchange_val />";    
}

function html_textarea($value='', $onchange='', $id='', $rows='', $cols='') {
    $id_val='';
    $onchange_val='';
    $rows_val='';
    $cols_val='';
    if(!empty($id)) $id_val="id=\"".hsc($id)."\"";
    if(!empty($onchange)) $onchange_val="onchange=\"".hsc($onchange)."\"";
    if(!empty($rows)) $rows_val="rows=\"".hsc($rows)."\"";
    if(!empty($cols)) $cols_val="cols=\"".hsc($cols)."\"";

    echo "<textarea class='text' $id_val $rows_val $cols_val $onchange_val>";
    echo hsc($value);
    echo "</textarea>";
}

function html_select($value = '', $options = null, $onchange='', $id='') {
    $id_val='';
    $onchange_val='';
    if(!empty($id)) $id_val="id=\"".hsc($id)."\"";
    if(!empty($onchange)) $onchange_val="onchange=\"".hsc($onchange)."\"";

    echo "<select size='1' $id_val $onchange_val>";
    if(is_array($options)) {
        foreach($options as $opt) {
            if($value == $opt) {
                echo "<option selected='selected' >$opt</option>";
            } else {
                echo "<option>$opt</option>";
            }
        }
    }
    echo "</select>";
}

function action_dispatch($action = '') {
    global $conf;
    global $state;
    global $_SESSION;
    
    switch($action) {
        case 'reset':
            msg("Session reset!");
            $_SESSION = array();
            init_session_defaults();
            $target = rtrim(AB_URL, '/') . '/install.php';
            header('Location: '.$target);
            exit;
            break;
        case 'step_welcome':
            $state['step'] = 1;
            break;
        case 'step_check':
            $state['step'] = 2;
            break;
        case 'step_install':
            $state['step'] = 3;
            break;
        case 'step_configure':
            $state['step'] = 4;
            break;
        case 'step_finish':
            $state['step'] = 5;
            break;
        case 'phpinfo':
            phpinfo();
            break;
        case 'open_addressbook':
            header('Location: '.AB_URL);
            exit;
            break;
        case 'create_db':
            // db is created from within step_install()
        case 'show':
            break;
        default:
            msg("unknown action: $action", -1);
        
    }
    
    // check if already installed
    stop_if_installed();

    header('Content-Type: text/html; charset=utf-8');
    
    html_header();
    html_install_step();
    act_content();
    html_footer();
}

function post_var($value, $default) {
    if(isset($value)) return $value;
    return $default;
}


init_session_defaults();
load_session();

$state['action'] = post_var($_REQUEST['do'], $state['action']);

// process request variables
switch($state['step']) {
    case 3:
        if(!$state['db_created']) {
            $conf['dbtype']   = post_var($_REQUEST['dbtype'],   $conf['dbtype']);
            $conf['dbname']   = post_var($_REQUEST['dbname'],   $conf['dbname']);
            $conf['dbserver'] = post_var($_REQUEST['dbserver'], $conf['dbserver']);
            $conf['dbuser']   = post_var($_REQUEST['dbuser'],   $conf['dbuser']);
            $conf['dbpass']   = post_var($_REQUEST['dbpass'],   $conf['dbpass']);
            $conf['debug_db'] = (int)post_var($_REQUEST['debug_db'], $conf['debug_db']);

            // TODO: fix db creation!
            $conf['dbtable_ab']   = post_var($_REQUEST['dbtable_ab'],   $conf['dbtable_ab']);
            $conf['dbtable_cat']   = post_var($_REQUEST['dbtable_cat'],   $conf['dbtable_cat']);
            $conf['dbtable_catmap']   = post_var($_REQUEST['dbtable_catmap'],   $conf['dbtable_catmap']);
            $conf['dbtable_truth']   = post_var($_REQUEST['dbtable_truth'],   $conf['dbtable_truth']);
            $conf['dbtable_sync']   = post_var($_REQUEST['dbtable_sync'],   $conf['dbtable_sync']);
            $conf['dbtable_action']   = post_var($_REQUEST['dbtable_action'],   $conf['dbtable_action']);
        }
        break;
    case 4:
        $conf['fmode']   = (int)post_var($_REQUEST['fmode'],   $conf['fmode']);
        $conf['dmode']   = (int)post_var($_REQUEST['dmode'],   $conf['dmode']);
        $conf['basedir']   = post_var($_REQUEST['basedir'],   $conf['basedir']);
        $conf['baseurl']   = post_var($_REQUEST['baseurl'],   $conf['baseurl']);

        $conf['lang']   = post_var($_REQUEST['lang'],   $conf['lang']);
        $conf['title']   = post_var($_REQUEST['title'],   $conf['title']);
        $conf['template']   = post_var($_REQUEST['template'],   $conf['template']);
        $conf['bdformat']   = post_var($_REQUEST['bdformat'],   $conf['bdformat']);
        $conf['dformat']   = post_var($_REQUEST['dformat'],   $conf['dformat']);
        $conf['lastfirst']   = (int)post_var($_REQUEST['lastfirst'],   $conf['lastfirst']);
        $conf['photo_resize']   = post_var($_REQUEST['photo_resize'],   $conf['photo_resize']);
        $conf['photo_size']   = post_var($_REQUEST['photo_size'],   $conf['photo_size']);
        $conf['photo_format']   = post_var($_REQUEST['photo_format'],   $conf['photo_format']);
        $conf['map_link']   = post_var($_REQUEST['map_link'],   $conf['map_link']);
        $conf['contactlist_limit']   = (int)post_var($_REQUEST['contactlist_limit'],   $conf['contactlist_limit']);
        $conf['bday_advance_week']   = (int)post_var($_REQUEST['bday_advance_week'],   $conf['bday_advance_week']);

        $conf['canonical']   = (int)post_var($_REQUEST['canonical'],   $conf['canonical']);
        $conf['auth_enabled']   = (int)post_var($_REQUEST['auth_enabled'],   $conf['auth_enabled']);
        $conf['auth_allow_guest']   = (int)post_var($_REQUEST['auth_allow_guest'],   $conf['auth_allow_guest']);
        $conf['im_convert']   = post_var($_REQUEST['im_convert'],   $conf['im_convert']);
        $conf['photo_enable']   = (int)post_var($_REQUEST['photo_enable'],   $conf['photo_enable']);
        $conf['session_name']   = post_var($_REQUEST['session_name'],   $conf['session_name']);
        $conf['mark_changed']   = (int)post_var($_REQUEST['mark_changed'],   $conf['mark_changed']);

        $conf['debug']   = (int)post_var($_REQUEST['debug'],   $conf['debug']);

        $conf['vcard_fb_enc']   = post_var($_REQUEST['vcard_fb_enc'],   $conf['vcard_fb_enc']);
        $conf['ldif_base']   = post_var($_REQUEST['ldif_base'],   $conf['ldif_base']);
        $conf['ldif_mozilla']   = (int)post_var($_REQUEST['ldif_mozilla'],   $conf['ldif_mozilla']);
        $conf['xmlrpc_enable']   = (int)post_var($_REQUEST['xmlrpc_enable'],   $conf['xmlrpc_enable']);
        break;
    default:
        break;
}

// language can be changed in every step!
$conf['lang']   = post_var($_REQUEST['lang'],   $conf['lang']);


// start processing
action_dispatch($state['action']);

//close session
save_session();
session_write_close();

?>
