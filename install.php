<?php
/**
* PHP iAddressBook Installation Script
*
* @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
* @author     Clemens Wacha <clemens.wacha@gmx.net>
*/

if(!defined('AB_INC')) define('AB_INC',realpath(dirname(__FILE__)).'/');
require_once(AB_INC.'functions/init.php');
require_once(AB_INC.'functions/db.php');
require_once(AB_INC.'functions/template.php');
require_once(AB_INC.'functions/common.php');
require_once(AB_INC.'functions/db.php');

$VERSION = "1.0 DEV";

// the state of the script
global $state;
$state = array();

function init_session_defaults() {
    global $state;
    global $defaults;
    global $conf;
    
    // initial state
    $state['action'] = 'welcome';
    $state['step']   = 1;
    $state['db_created'] = 0;
    
    $conf = $defaults;
}

function load_session() {
    global $_SESSION;
    global $state;
    global $conf;
    
    // restore session
    if(is_array($_SESSION['state'])) {
        $state = $_SESSION['state'];
        $conf = $_SESSION['conf'];
    }
}

function save_session() {
    global $_SESSION;
    global $state;
    global $conf;

    // save session
    $_SESSION['state'] = $state;
    $_SESSION['conf'] = $conf;
}

function html_header() {
    global $conf;
    global $lang;
    global $state;
    
    ?>
    <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?= $conf['lang'] ?>"
     lang="<?= $conf['lang'] ?>" dir="<?= $lang['direction'] ?>">
    <head>
        <title><?= $conf['title'] ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    
        <link rel="shortcut icon" href="<?= AB_TPL ?>images/favicon.ico" />
        <link rel="stylesheet" media="screen" type="text/css" href="<?= AB_TPL ?>design.css" />
        
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

        <input type="hidden" name="fmode" value="<?= $conf['fmode'] ?>" />
        <input type="hidden" name="dmode" value="<?= $conf['dmode'] ?>" />
        <input type="hidden" name="basedir" value="<?= $conf['basedir'] ?>" />
        <input type="hidden" name="baseurl" value="<?= $conf['baseurl'] ?>" />

        <input type="hidden" name="dbtype" value="<?= $conf['dbtype'] ?>" />
        <input type="hidden" name="dbname" value="<?= $conf['dbname'] ?>" />
        <input type="hidden" name="dbserver" value="<?= $conf['dbserver'] ?>" />
        <input type="hidden" name="dbuser" value="<?= $conf['dbuser'] ?>" />
        <input type="hidden" name="dbpass" value="<?= $conf['dbpass'] ?>" />
        <input type="hidden" name="debug_db" value="<?= $conf['debug_db'] ?>" />
        <input type="hidden" name="dbtable_ab" value="<?= $conf['dbtable_ab'] ?>" />
        <input type="hidden" name="dbtable_cat" value="<?= $conf['dbtable_cat'] ?>" />
        <input type="hidden" name="dbtable_catmap" value="<?= $conf['dbtable_catmap'] ?>" />
        <input type="hidden" name="dbtable_truth" value="<?= $conf['dbtable_truth'] ?>" />
        <input type="hidden" name="dbtable_sync" value="<?= $conf['dbtable_sync'] ?>" />
        <input type="hidden" name="dbtable_action" value="<?= $conf['dbtable_action'] ?>" />

        <input type="hidden" name="lang" value="<?= $conf['lang'] ?>" />
        <input type="hidden" name="title" value="<?= $conf['title'] ?>" />
        <input type="hidden" name="template" value="<?= $conf['template'] ?>" />
        <input type="hidden" name="bdformat" value="<?= $conf['bdformat'] ?>" />
        <input type="hidden" name="dformat" value="<?= $conf['dformat'] ?>" />
        <input type="hidden" name="lastfirst" value="<?= $conf['lastfirst'] ?>" />
        <input type="hidden" name="photo_resize" value="<?= $conf['photo_resize'] ?>" />
        <input type="hidden" name="photo_size" value="<?= $conf['photo_size'] ?>" />
        <input type="hidden" name="photo_format" value="<?= $conf['photo_format'] ?>" />
        <input type="hidden" name="map_link" value="<?= $conf['map_link'] ?>" />
        <input type="hidden" name="contactlist_limit" value="<?= $conf['contactlist_limit'] ?>" />
        <input type="hidden" name="bday_advance_week" value="<?= $conf['bday_advance_week'] ?>" />

        <input type="hidden" name="canonical" value="<?= $conf['canonical'] ?>" />
        <input type="hidden" name="auth_enabled" value="<?= $conf['auth_enabled'] ?>" />
        <input type="hidden" name="auth_allow_guest" value="<?= $conf['auth_allow_guest'] ?>" />
        <input type="hidden" name="im_convert" value="<?= $conf['im_convert'] ?>" />
        <input type="hidden" name="photo_enable" value="<?= $conf['photo_enable'] ?>" />
        <input type="hidden" name="session_name" value="<?= $conf['session_name'] ?>" />
        <input type="hidden" name="mark_changed" value="<?= $conf['mark_changed'] ?>" />
        <input type="hidden" name="debug" value="<?= $conf['debug'] ?>" />

        <input type="hidden" name="vcard_fb_enc" value="<?= $conf['vcard_fb_enc'] ?>" />
        <input type="hidden" name="ldif_base" value="<?= $conf['ldif_base'] ?>" />
        <input type="hidden" name="ldif_mozilla" value="<?= $conf['ldif_mozilla'] ?>" />
        <input type="hidden" name="xmlrpc_enable" value="<?= $conf['xmlrpc_enable'] ?>" />
 
    </form>
    
    <div class="mainview">
        <?= html_msgarea() ?>

        <!-- Begin Logo -->
        <table width="100%">
            <tr>
                <td>
                    <div class="logo">
                        <img src="<?= AB_TPL ?>images/logo.png">
                    </div>
                </td>
                <td>
                    <div class="title"><?= $lang['installation'] ?></div>
                </td>
            </tr>
        </table>
        <!-- End Logo -->
    <?php

}

function html_footer() {
    ?>
        <div style="height: 10px; clear: both;" ></div>
        <!-- Begin Footer --> 
        <div class="separator">&nbsp;</div>
        <div class="footer">
            <a href='http://iaddressbook.org/'>PHP iAddressBook <?= display_version() ?></a>
        </div>
        <!-- End Footer -->
    </div>
    </body>
    </html>
    <?php
}

function html_install_step() {
    global $state;
    
    $step = $state['step'];
    $step_max = 5;
    $space = (string)(100 / $step_max);
    
    echo '<div style="width:80%; height:100px; margin: 0 auto; font-size: 500%; font-weight:bold;">';
    for($t = 1; $t <= $step_max; $t++) {
        if($t == $state['step']) {
            echo "<div style='float:left; width: $space%; color:#000;'>";
            echo $t;
            echo "</div>";
        } else {
            echo "<div style='float:left; width: $space%; color:#bebebe;'>";
            echo $t;
            echo "</div>";
        }
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
    if(empty($title) || empty($target)) return;
    echo '<div style="height: 50px; margin: 0 auto; font-size: 250%; padding-top: 50px; float:left;">';
    html_link($title, "do_action('".hsc($target)."')");
    echo '</div>';    
}

function step_next($target='next', $title='Next') {
    if(empty($title) || empty($target)) return;
    echo '<div style="height: 50px; margin: 0 auto; font-size: 250%; padding-top: 50px; float:right;">';
    html_link($title, "do_action('".hsc($target)."')");
    echo '</div>';
}

function step_title($title) {
    echo "<h1><b>$title</b></h1>";
}

function step_welcome() {
    step_title("Welcome!");
    ?>
        Welcome to PHP iAddressBook!
        <p><p>
        This project is very special to me as it solved one major issue in keeping all
        my addresses handy and in sync with my other devices. I put hundreds of hours
        into this project and I am dedicated to make it better every day. If you like
        this program, have suggestions or blame please tell me or
        <a href="https://sourceforge.net/project/project_donations.php?group_id=199169" target="_blank">donate</a>!
        Enjoy and have Fun!
        <p style="text-align: right;">&#8212; Clemens Wacha</p>
    <?php step_title("Prerequisites"); ?>    
        <p><p>
        This installer helps you with the first time installation and configuration
        of <a href="http://iaddressbook.org/">PHP iAddressBook</a>.
        You will need:
        <ul>
            <li>PHP version 4.3 or higher.</li>
            <li>MySQL version 4.0 or higher or SQLite version 2.x</li>
            <li>(optional) iconv (if you want to import vcards that are not UTF-8 encoded)</li>
            <li>(optional) ImageMagick's convert or GD (if you want to import photos)</li>
        </ul>
        <p>
    <?php

    step_prev('');
    step_next('step_check');
}

function step_check() {
    global $VERSION;
    global $conf;
    $errors = 0;
    
    step_title("Checking your installation");

    echo '<table border="0"><tr><td width="45%">';
    echo '<div>';
    
    if(!is_writable(AB_INC."_images")) {
        imsg("Cannot use contact photos: No write permission to ".AB_INC."_images", -1);
    }
    
    if(!is_writable(AB_INC."_import")) {
        imsg("Cannot delete vCards from import folder: No write permission to ".AB_INC."_import", 0);
    }
    
    if(!is_writeable(AB_INC."conf")) {
        imsg("Cannot create configuration/authorizations: No write permission to ". AB_INC."conf", -1);
        $errors++;
    }
    
    // TODO: remove this
    //$conf['im_convert'] = '/Users/cwacha/Desktop/ImageMagick-6.4.0/bin/convert';
    
    $use_im = 0;
    $use_gd = 0;
    if(!empty($conf['im_convert'])) {
        if(is_readable($conf['im_convert'])) {
            // check popen()
            $pipe = popen($conf['im_convert']." -version", "r");
            if(is_resource($pipe)) {
                while(!feof($pipe)) $output .= fread($pipe, 8192);
                pclose($pipe);
                imsg("ImageMagick's convert found at: ".$conf['im_convert'], 1);
                imsg("convert available (version ".$output.")", 1);
                $use_im = 1;
            } else {
                imsg("Cannot execute ImageMagick's convert at ".$conf['im_convert'], 0);
            }
        } else {
            imsg("Cannot find ImageMagick's convert at ". $conf['im_convert'], 0);
        }
    }
    
    if(function_exists('gd_info')) {
        $gd = gd_info();
        imsg("GD available (version ".$gd['GD Version'].")", 1);
        $use_gd = 1;
    }
    
    if($use_im == 1 || $use_gd == 1) {
        if($use_im == 1) {
            imsg("Using ImageMagick to convert photos.", 1);
        } else {
            imsg("Using GD to convert photos", 1);
        }
    } else {
        imsg("Contact photos are not supported! Neither ImageMagick's convert nor GD is available", 1);
    }

    if(function_exists('iconv')) {
        imsg("iconv available (version ".ICONV_VERSION.")", 1);
    } else {
        imsg("iconv not available. vCards must be encoded in UTF-8 to be imported properly", 1);
    }

    if(function_exists('sqlite_open')) {
        imsg("SQLite available (version ".sqlite_libversion().")", 1);
    }
    
    echo "&nbsp;<p>";
    html_button("Retry", "do_action('show')");
            
    echo '</div>';
    echo '</td><td width="10%"></td><td style="vertical-align: top;">';

    if($errors > 0) {
        if($errors > 1) {
            imsg("$errors problems found!", -1);
        } else {
            imsg("$errors problem found!", -1);
        }
        imsg("You cannot continue with the installation until you have fixed all problems.", -1);
    } else {
        imsg("Everything is ok.<p>You may continue with the installation.", 1);
    }

    echo '</td></tr></table>';
    
    step_prev('step_welcome');
    if($errors == 0) step_next('step_install');
}

function step_install() {
    global $state;
    step_title("Setting up the database");

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
        html_button("Create Database and Tables", "do_action('create_db')");
    }

    step_prev('step_check');
    if($state['db_created']) step_next('step_configure');
}

function step_configure() {
    global $defaults;
    
    step_title("Configure your PHP iAddressBook");

    html_sform_begin();
    foreach($defaults as $key => $value) {
        if(!in_array($key, array('dbtype', 'dbname', 'dbserver', 'dbuser', 'dbpass', 'debug', 'debug_db'))) {
            html_sform_line($key);
        }
    }
    html_sform_end();

    step_prev('step_install');
    step_next('step_finish', 'Finish');
}

function step_finish() {
    global $defaults;
    global $conf;
    global $state;
    
    step_title("Installation Complete!");
        
    // save config file (only add vars that are not on default value)
    $new_config = array();
    foreach($conf as $key => $value) {
        if($conf[$key] != $defaults[$key]) {
            $new_config[$key] = $value;
        }
    }

    if(!$state['config_saved']) {
        $ret = save_config($new_config, 'conf/config.php');
        if($ret) $state['config_saved'] = 1;
    }
    
    if(!$state['config_saved']) {
        echo "&nbsp;<p>";
        html_button("Retry", "do_action('step_finish')");
        step_prev('step_configure');
        step_next('');
    } else {
        ?>
            &nbsp;<p>
            <b>Congratulations!</b>
            <p><p>
            You have completed the installation of PHP iAddressBook. You may start using
            it immediately. Please send me your feedback and comments to
            <a href="mailto:clemens.wacha@gmx.net">clemens.wacha@gmx.net</a>.
            <p>
            <a href="https://sourceforge.net/project/project_donations.php?group_id=199169" target="_blank">
            Don't forget to donate if you like this program!</a>
            <p>
            Enjoy and have Fun!
            <p style="text-align: right;">&#8212; Clemens Wacha</p>
        <?php
        //step_prev('step_configure');
        step_next('open_addressbook', 'Open the AddressBook');    
    }
    
}

function save_config($config, $filename = 'conf/config.php', $overwrite = 0) {
    if(empty($config) || empty($filename)) return false;

    $header = array();
    $header[] = "/**";
    $header[] = " * This is the AddressBook's local configuration file";
    $header[] = " * This is a piece of PHP code so PHP syntax applies!";
    $header[] = " *";
    $header[] = " */\n\n";

    $file = AB_INC.$filename;
    if(!$overwrite && file_exists($filename)) {
        imsg("Error saving configuration: file $file already exists!", -1);
        return false;
    }
    $fd = fopen($file, "w");
    if(!$fd) {
        imsg("Error saving configuration: could not write $file", -1);
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
    
    if($config['fperm']) chmod($file, $config['fperm']);

    imsg("Configuration saved successfully!", 1);
    return true;
}

function setup_db() {
    global $state;
    global $conf;

    db_init($conf);
    db_open();
    if(db_createtables()) {
        imsg("Database setup successfully", 1);
        $state['db_created'] = 1;
        $conf['debug_db'] = 0;
    }
    db_close();
    html_msgarea();
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
        case 'option':
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
    echo "<td style='text-align: left; vertical-align: top; padding-left: 2em;'>";
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

function action_dispatch($action = '') {
    global $conf;
    global $state;
    
    switch($action) {
        case 'reset':
            msg("Session reset!");
            $state = array();
            init_session_defaults();
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

// start processing
action_dispatch($state['action']);

//close session
save_session();
session_write_close();

//restore old umask
umask($conf['oldumask']);


?>