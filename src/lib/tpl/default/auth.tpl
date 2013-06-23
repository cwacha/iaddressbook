<?php

/**
 * iAddressBook Login Page
 *
 * You should leave the doctype at the very top - It should
 * always be the very first line of a document.
 *
 * @link   http://iaddressbook.org
 * @author Clemens Wacha (clemens.wacha@gmx.net)
 */

?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $conf['lang']; ?>"
 lang="<?php echo $conf['lang']; ?>" dir="<?php echo $lang['direction']; ?>">
<head>
    <title><?php echo $conf['title']; ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <link rel="shortcut icon" href="<?php echo AB_TPL; ?>images/favicon.ico" />
    <link rel="stylesheet" media="screen" type="text/css" href="<?php echo AB_TPL; ?>design.css" />

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
    <table border="0" width="100%">
        <tr>
            <td>
                <div class="logo">
                    <img src="<?php echo AB_TPL; ?>images/logo.png">
                </div>
            </td>
            <td>
                <div class="title">
                    <?php echo $conf['title']; ?>
                </div>
            </td>
        </tr>
    </table>
    <!-- End Logo -->

    <div style="height: 4em;"></div>
    <?php echo html_msgarea(); ?>
    <div style="height: 1em;"></div>
    
    <div class="centeralign">
        <!-- <?php echo $lang['username']; ?> = demo <br> -->
        <!-- <?php echo $lang['password']; ?> = demo <br> -->
        <form action="<?php echo $PHP_SELF; ?>" accept-charset="utf-8" method="post" name='f'>
          <fieldset>
            <legend><?php echo $lang['login']; ?></legend>
            <label class="block">
                <span><?php echo $lang['username']; ?></span>
                <input type="text" name="u" value="<?php echo $_REQUEST['u']; ?>" class="edit" />
            </label><br />
            <label class="block">
                <span><?php echo $lang['password']; ?></span>
                <input type="password" name="p" class="edit" />
            </label><br />
            <input type="checkbox" name="r" />
            <span><?php echo $lang['sticky']; ?></span>
            <br /><br />
                
            <input type="submit" value="<?php echo $lang['btn_login']; ?>" class="button" />
          </fieldset>
        </form>
    </div>

    <div style="height: 4em;"></div>

    <!-- Begin Footer --> 
    <div class="separator">&nbsp;</div>
    <div class="footer">
        <a href='http://iaddressbook.org/'>PHP iAddressbook <?php echo get_version(); ?></a>
    </div>
    <!-- End Footer --> 


</div>

</body>
</html>
