<div class="contactlist_item<?php echo $color; ?>">
    <?php if($contact->company == true) { 
        $small_image = "images/company_small.png";
    } else {
        $small_image = "images/person_small.png";
    }; ?>
    <input type="checkbox" name="ct_<?php echo $contact->id; ?>" value="<?php echo $contact->id; ?>" style='margin-left: 5px;'>
    <img src="<?php echo AB_TPL.$small_image; ?>" >
    <a href='?id=<?php echo $contact->id; ?>'><?php echo $contact->name(); ?> </a><br>
</div>
