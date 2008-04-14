<div class="person">
    <img src="<?= $PHP_SELF ?>?do=img&id=<?= $contact->id ?>">
    <strong><?= $contact->title ?> <?= $contact->firstname ?> <?= $contact->firstname2 ?> <?= $contact->lastname ?> <?= $contact->suffix ?> </strong><br>
    <i><?php if($contact->nickname != "") echo "\"$contact->nickname\"" ?></i><br>
    <?= $contact->jobtitle ?><br>
    <?= $contact->department ?><br>
    <?= $contact->organization ?><br>
    <br>
    
    <?php if(!empty($contact->birthdate) and $contact->birthdate != "0000-00-00") echo 'Birthday: ' . date($conf['bdformat'], strtotime($contact->birthdate)) .' (dates before 1970 are note displayed correctly)<br>' ?>

    <?php if(!empty($contact->note)) echo "Note: ". str_replace("\n", "<br>", $contact->note). "<br>" ?>
    
    <br>
    <?php tpl_addresses() ?>
    <br>
    <?php tpl_phones() ?>
    <br>
    <?php tpl_emails() ?>
    <br>
    <?php tpl_chathandles() ?>
    <br>
    <?php tpl_urls() ?>
    <br>
    <?php tpl_relatednames() ?>
    <br>
    
    <?= "last modified on ". date($conf['dformat'], strtotime($contact->modificationdate)) ?><br>
    
    <br>
    <form method="POST" action="<?= $PHP_SELF ?>">
        <input type="hidden" name="do" value="edit">
        <input type="submit" value="<?= $lang['btn_edit']?>">
    </form>
    <form method="POST" action="<?= $PHP_SELF ?>">
        <input type="hidden" name="do" value="delete">
        <input type="hidden" name="id" value="<?= $contact->id ?>">
        <input type="submit" value="<?= $lang['btn_delete']?>" onClick="return confirm('<?= $lang['confirm_delete'] ?>')">
    </form>
    <form method="post" action="<?= $PHP_SELF ?>">
        <input type="hidden" name="do" value="export_vcard">
        <input type="hidden" name="id" value="<?= $contact->id ?>">
        <input type="submit" value="<?= $lang['btn_vcardexport']?>">
    </form>
    <br>
</div>
