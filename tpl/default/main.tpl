<?php

/**
 * AddressBook Default Template
 *
 * This is the template you need to change for the overall look
 * of AddressBook.
 *
 * You should leave the doctype at the very top - It should
 * always be the very first line of a document.
 *
 * @link   http://wacha.ch/AddressBook
 * @author Clemens Wacha (clemens.wacha@gmx.net)
 */

?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $conf['lang'] ?>"
 lang="<?php echo $conf['lang'] ?>" dir="<?php echo $lang['direction'] ?>">
<head>
    <title><?= $conf['title'] ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <link rel="shortcut icon" href="<?= AB_TPL ?>images/favicon.ico" />
    <link rel="stylesheet" media="screen" type="text/css" href="<?= AB_TPL ?>design.css" />

	<link rel="stylesheet" type="text/css" href="<?= AB_TPL ?>applesearch/default.css" id="default"  />
	<link rel="stylesheet" type="text/css" href="<?= AB_TPL ?>applesearch/dummy.css" id="dummy_css"  />
	<script type="text/javascript" src="<?= AB_TPL ?>applesearch/applesearch.js"></script>

    <script type="text/javascript">
    <!--
    window.onload = function () {
        applesearch.init('<?= AB_TPL ?>applesearch/');
        applesearch.onChange('srch_fld','srch_clear');
        document.search.q.focus(); 
        document.search.q.select();
    }
    -->
    </script>
</head>

<body>

<?= html_msgarea() ?>

<div class="mainview">
    <!-- Begin Logo -->
    <table width="100%">
        <tr>
            <td>
                <div class="logo"><img src="<?= AB_TPL ?>images/logo.png"></div>
            </td>
            <td>
                <div class="title"><?= $conf['title'] ?></div>
            </td>
            <td align="right" valign="top">
                <div class="login_box">
                    <?php if($conf['auth_enabled']) {
                            if($userinfo['logged_in'] == true) {
                                echo "<div class='person_smalltext'>" . $lang['logged_in_as'] . " " . $userinfo['fullname'] . "</div>";
                                echo "<a href='?do=logout'>". $lang['btn_logout'] ."</a>";
                            } else {
                                echo "<a href='?do=login'>". $lang['btn_login'] ."</a>";
                            }
                        }
                    ?>
                </div>
        
                <div class="birthday_box">
                    <div class="birthday_title"><?= $lang['birthdays'] ?></div>
                    <div class="birthday_text"><?php tpl_birthday() ?></div>
                </div>                
            </td>
        </tr>
    </table>
    <!-- End Logo -->

    <div style="height: 30px;" ></div>
    
    <!-- Begin Menu --> 
    <table border="0" width="100%">
        <tr>
            <td align="left">
                <!-- Begin Import VCard -->
                <form method="POST" enctype="multipart/form-data" action="<?= $PHP_SELF ?>" style="float: left; padding-left: 10px;">
                    <input type="hidden" name="do" value="import_vcard" />
                    <input type="file" name="vcard_file" value="" size="" class="button" />
                    <input type="submit" value="<?= $lang['btn_vcardimport'] ?>" class="button" />
                </form>
                <!-- End Import VCard -->
            </td>
            <td align="right" style="padding-right: 10px;" >
                <?php tpl_include('search.tpl') ?>
            </td>
        </tr>
    </table>
    <!-- End Menu --> 

    <!-- Begin Main --> 
    <table border="0" cellspacing="0" cellpadding="0" width="100%">
     <tr>
        <td width="10"></td>
        
        <!-- Begin Contact List -->
        <?php tpl_showcontactlist() ?>
        <!-- End Contact List -->
        
        <td width="50"></td>
        
        <!-- Begin Person View -->
        <td class="personview">
            <div class="personview">
                <div class="panel">
                    <table class="header">
                        <tr class="header_tr">
                            <td class="endcap"><img src="<?= AB_TPL ?>images/split1_left.gif"></td>
                            <td class="middle"> </td>
                            <td class='endcap'><img src="<?= AB_TPL ?>images/split1_right.gif"></td>
                        </tr>
                    </table>
                </div>
                
                <?php tpl_showperson() ?>
                
                <br>
            </div>
        </td>
        <!-- End Person View -->
        
        <td width="10"></td>

     </tr>
    </table>
    <!-- End Main --> 

    <!-- Begin Footer --> 
    <div class="separator">&nbsp;</div>
    <div class="footer">
        <a href='http://wacha.ch/addressbook/'>PHP iAddressbook <?= display_version() ?></a>
    </div>
    <!-- End Footer --> 
    <?php tpl_birthday() ?>
</div>


</body>
</html>
