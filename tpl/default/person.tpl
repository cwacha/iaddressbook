<div class="person">
	<br> Person Header <br>
	<?= $contact->title ?> <?= $contact->firstname ?> <?= $contact->firstname2 ?> <?= $contact->lastname ?> <?= $contact->suffix ?> <br>
    <?php if($contact->nickname != "") echo "\"$contact->nickname\"" ?><br>
    <?= $contact->jobtitle ?><br>
    <?= $contact->department ?><br>
    <?= $contact->organization ?><br>
    <br>
    
    <?php if($contact->birthdate != "" and $contact->birthdate != "0000-00-00") echo 'Birthday: ' . date($conf['dformat'], strtotime($contact->birthdate)) .'<br>' ?>
    <?php if($contact->homepage != "") echo "Homepage: $contact->homepage<br>" ?>
    <?php if($contact->note != "") echo 'Note: $contact->note<br>(TODO: convert slash n to br!)<br>' ?>
    
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
    
    <?= "created: ". date('r', strtotime($contact->creationdate)) ." ($contact->creationdate)" ?><br>
    <?= "modified: ". date('r', strtotime($contact->modificationdate)) ." ($contact->modificationdate)" ?><br>
    <?= "id: $contact->id" ?><br>
    
    <br>
    <form method="POST" action="<?= $PHP_SELF ?>">
        <input type="hidden" name="do" value="edit">
        <input type="submit" value="Edit"><br>
    </form>
    <form method="POST" action="<?= $PHP_SELF ?>">
        <input type="hidden" name="do" value="delete">
        <input type="hidden" name="id" value="<?= $contact->id ?>">
        <input type="submit" value="Delete"><br>
    </form>

	<br> Person Footer <br>
</div>
