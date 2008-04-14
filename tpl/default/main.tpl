<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
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
 * @link   http://??
 * @author me
 */
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $conf['lang']?>"
 lang="<?php echo $conf['lang']?>" dir="<?php echo $lang['direction']?>">
<head>
  <title>AddressBook of <?= $conf['title'] ?></title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />


</head>

<body>
<div class="mainview">

<?php tpl_include('search.tpl') ?>

<form method="POST" action="<?= $PHP_SELF ?>">
    <input type="hidden" name="do" value="new">
    <input type="submit" value="New Contact">
</form>

<table border="0" cellspacing="0" cellpadding="0" width="100%">
 <tr>
  <td valign="top">
    <?php tpl_showcontactlist() ?>
  </td>
  <td>
    <?php tpl_showperson() ?>
  </td>
 </tr>
 <tr>
   <td>
    <?php html_debug() ?>
  </td>
 </tr>
</table>

</div>

</body>
</html>
