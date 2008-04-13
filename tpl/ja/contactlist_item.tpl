<div class="contactlist_item<?= $color ?>">
    <?php if($contact->company == true) { 
        $small_image = "images/company_small.png";
    } else {
        $small_image = "images/person_small.png";
    } ?>
    <input type="checkbox" name="ct_<?= $contact->id ?>" value="<?= $contact->id ?>" style='margin-left: 5px;'>
    <img src="<?= AB_TPL.$small_image ?>" >
    <a href='?id=<?= $contact->id ?>'><?= $contact->name() ?> </a><br>
</div>
