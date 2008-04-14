<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php
/**
 * AddressBook Slim Template
 *
 * This is the template you need to change for the overall look
 * of AddressBook.
 *
 * You should leave the doctype at the very top - It should
 * always be the very first line of a document.
 *
 * @link   http://wacha.ch/AddressBook
 * @author Clemens Wacha <clemens.wacha@gmx.net>
 */
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $conf['lang']?>"
 lang="<?php echo $conf['lang']?>" dir="<?php echo $lang['direction']?>">
<head>
    <title><?= $conf['title'] ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <link rel="shortcut icon" href="<?= AB_TPL?>images/favicon.ico" />
    <link rel="stylesheet" media="screen" type="text/css" href="<?= AB_TPL?>layout.css" />
    <link rel="stylesheet" media="screen" type="text/css" href="<?= AB_TPL?>design.css" />

</head>

<body>
<div class="mainview">

<table border="0" width="100%">
    <tr>
        <td align="left">
            <strong><?= $conf['title'] ?></strong>
        </td>
        <td align="right">
            <?php tpl_include('search.tpl') ?>
        </td>
    </tr>
</table>

<form method="POST" action="<?= $PHP_SELF ?>">
    <input type="hidden" name="do" value="new">
    <input type="submit" value="<?= $lang['btn_new']?>">
</form>
<form method="POST" enctype="multipart/form-data" action="<?= $PHP_SELF ?>">
    <input type="hidden" name="do" value="import_vcard"/>
    <input type="file" name="vcard_file" value=""/>
    <input type="hidden" name="MAX_FILE_SIZE" value="10000000" />
    <input type="submit" value="<?= $lang['btn_vcardimport']?>"/>
</form>



<table border="0" cellspacing="0" cellpadding="0" width="100%">
 <tr>
  <td valign="top" width="200">
    <?php tpl_showcontactlist() ?>
  </td>
  <td width="100">
  </td>
  <td valign="top">
    <?php tpl_showperson() ?>
  </td>
 </tr>
 <tr>
   <td>
    <?php //html_debug() ?>
  </td>
 </tr>
</table>

</div>


<br><br>
<a href='http://wacha.ch/addressbook/'>PHP iAddressbook v0.9</a>

</body>
</html>
