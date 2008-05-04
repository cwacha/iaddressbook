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
require_once(AB_INC.'functions/actions.php');

$VERSION = "1.0 DEV";

// the state of the script
$state = array();

// initial state
$state['action'] = 'welcome';
$state['step_max'] = 4;
$state['step']   = 1;


function html_header() {
    global $conf;
    global $lang;
    
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
        window.onload = function () {
            //applesearch.init('<?= AB_TPL ?>applesearch/');
            //applesearch.onChange('srch_fld','srch_clear');
            //document.search.q.focus(); 
            //document.search.q.select();
        }
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
        -->
        </script>
    </head>
    <body>
    
    <form name="actions" action="" method="post">
        <fieldset>
            <input type="text" name="do" value="show" />
        </fieldset>
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
        <div style="height: 10px;" ></div>
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
    
    echo '<div style="width:80%; height:100px; margin: 0 auto; font-size: 500%; font-weight:bold;">';
    for($t = 1; $t <= $state['step_max']; $t++) {
        if($t == $state['step']) {
            echo "<div style='float:left; width: 25%; color:#000;'>";
            echo $t;
            echo "</div>";
        } else {
            echo "<div style='float:left; width: 25%; color:#bebebe;'>";
            echo $t;
            echo "</div>";
        }
    }
    echo '</div>';
}

function act_content() {
    global $state;
    
    echo '<div style="height: 350px; width: 80%; margin: 0 auto; text-align: left; font-size: 120%; ">';
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
        into this project and I am dedicated to make it better every day.
        Enjoy and have Fun!
        <p style="text-align: right;">&#8212; Clemens Wacha</p>
    <?php step_title("Prerequisites"); ?>    
        <p><p>
        This installer helps you with the first time installation and configuration
        of <a href="http://iaddressbook.org/">PHP iAddressBook</a>.
        You will need:
        <ul>
            <li>PHP version 4.3 or higher.</li>
            <li>MySQL version 4.0 or higher OR SQLite version 2.x</li>
            <li>(optional) iconv (if you want to import vcards that are not UTF-8 encoded)</li>
            <li>(optional) ImageMagick's convert or GD (if you want to import photos)</li>
        </ul>
        <p>
    <?php

    step_prev('');
    step_next('step_check');
}

function imsg($message, $lvl=0) {
    $errors[-1] = 'error';
    $errors[0]  = 'info';
    $errors[1]  = 'success';

    print '<div class="'.$errors[$lvl].'" style="text-align: left;">';
    print $message;
    print '</div>';
}



function step_check() {
    global $VERSION;
    global $conf;
    $errors = 0;
    
    step_title("Checking your installation");
    
    echo '<table border="0"><tr><td width="45%">';
    echo '<div>';
    
    if(!is_writable(AB_INC."_images")) {
        imsg("Cannot use contact photos: No write permission to ".AB_INC."_images");
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
                imsg("Cannot execute ImageMagick's convert at ".$conf['im_convert'].". Using GD to convert photos");
            }
        } else {
            imsg("Cannot find ImageMagick's convert at ". $conf['im_convert']);
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
        imsg("Contact photos are not supported! Neither ImageMagick's convert nor GD is available");
    }

    if(function_exists('iconv')) {
        imsg("iconv available (version ".ICONV_VERSION.")", 1);
    } else {
        imsg("iconv not available. vCards must be encoded in UTF-8 to be imported properly");
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
    step_title("Setting up the database");

    echo "Database";
    html_edit("value", "alert('bla')");
    echo "<p>";
    echo "Database";
    html_edit("value", "alert('bla')");

    step_prev('step_check');
    step_next('step_configure');
}

function step_configure() {
    step_title("Configure your PHP iAddressBook");

    step_prev('step_install');
    step_next('');
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
    
    echo "<input type='text' $id_val $value_val $onchange_val />";
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

    echo "<textarea $id_val $rows_val $cols_val $onchange_val>";
    echo hsc($value);
    echo "</textarea>";
}

function action_dispatch($action = '') {
    global $conf;
    global $state;
    
    print_r($_REQUEST);
    
    switch($action) {
    
        case 'bla':
            msg("bla");
            break;
        case 'check':
            act_check();
            break;
        case 'next':
            msg("deprecated: $action");
            $state['step']++;
            if($state['step'] > $state['step_max']) $state['step'] = $state['step_max'];
            break;
        case 'prev':
            msg("deprecated: $action");
            $state['step']--;
            if($state['step'] < 1) $state['step'] = 1;
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
        case 'phpinfo':
            phpinfo();
            break;
        default:
        
    }

    header('Content-Type: text/html; charset=utf-8');
    
    html_header();
    html_install_step();
    act_content();
    html_footer();
}

// restore session
if(is_array($_SESSION['state'])) {
    foreach($_SESSION['state'] as $key => $value) {
        $state[$key] = $value;
    }
}

if(isset($_REQUEST['do'])) {
    $state['action'] = $_REQUEST['do'];
}

// start processing
action_dispatch($state['action']);

$_SESSION['state'] = $state;
//close session
session_write_close();

//restore old umask
umask($conf['oldumask']);


?>