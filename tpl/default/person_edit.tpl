<div class="person">
	<br> Person Header <br>
    <form method="post" action="<?= $PHP_SELF ?>">
        <input type="text" name="title" value="<?= $contact->title ?>">
        <input type="text" name="firstname" value="<?= $contact->firstname ?>">
        <input type="text" name="firstname2" value="<?= $contact->firstname2 ?>">
        <input type="text" name="lastname" value="<?= $contact->lastname ?>">
        <input type="text" name="suffix" value="<?= $contact->suffix ?>"><br>
        <br>
        
        <input type="text" name="nickname" value="<?= $contact->nickname ?>"><br>
        <input type="text" name="jobtitle" value="<?= $contact->jobtitle ?>"><br>
        <input type="text" name="department" value="<?= $contact->department ?>"><br>
        <input type="text" name="organization" value="<?= $contact->organization ?>"><br>
        Company <input type="checkbox" name="company" <?php if(is_bool($contact->company) and $contact->company == true) echo "checked" ?> ><br>
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
        
        <input type="hidden" name="id" value="<?= $contact->id ?>">
        <input type="hidden" name="do" value="save">
        <input type="submit" value="save"><br>
        <br>
    </form>
    <form method="POST" action="<?= $PHP_SELF ?>">
        <input type="hidden" name="id" value="<?= $contact->id ?>" >
        <input type="hidden" name="do" value="show">
        <input type="submit" value="cancel"><br>
    </form>


	<br> Person Footer <br>
</div>
