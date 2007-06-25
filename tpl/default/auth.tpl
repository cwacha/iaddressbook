<?php

/**
 * AddressBook Login Page
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

    <script>
    <!--
        window.onload = function () {
            if(document.f.u.value.length > 0) {
                document.f.p.focus();
            } else {
                document.f.u.focus();
            }
        }
    -->
    </script>

</head>

<body>


<div class="mainview">

    <!-- Begin Logo -->
    <table>
        <tr>
            <td>
                <div class="logo">
                    <img src="<?= AB_TPL ?>images/logo.png">
                </div>
            </td>
            <td>
                <div class="title">
                    <?= $conf['title'] ?>
                </div>
            </td>
        </tr>
    </table>
    <!-- End Logo -->

    <div style="height: 4em;"></div>
    <?= html_msgarea() ?>
    <div style="height: 1em;"></div>
    
    <div class="centeralign">
        <!-- <?= $lang['username'] ?> = demo <br> -->
        <!-- <?= $lang['password'] ?> = demo <br> -->
        <form action="<?= $PHP_SELF ?>" accept-charset="utf-8" method="post" name='f'>
          <fieldset>
            <legend><?= $lang['login'] ?></legend>
            <label class="block">
                <span><?= $lang['username'] ?></span>
                <input type="text" name="u" value="<?= $_REQUEST['u'] ?>" class="edit" />
            </label><br />
            <label class="block">
              <span><?= $lang['password'] ?></span>
              <input type="password" name="p" class="edit" />
            </label><br />
            <input type="submit" value="<?= $lang['btn_login'] ?>" class="button" />
          </fieldset>
        </form>
    </div>

    <div style="height: 4em;"></div>

    <!-- Begin Footer --> 
    <div class="separator">&nbsp;</div>
    <div class="footer">
        <a href='http://wacha.ch/addressbook/'>PHP iAddressbook <?= display_version() ?></a>
    </div>
    <!-- End Footer --> 


</div>

</body>
</html>
