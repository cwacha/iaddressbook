<?php
    $selected = "";
    if($ID == $contact_entry->id)
        $selected = "_selected";
?>
<div class="contactlist_item<?php echo $selected; ?>" id="ct_<?php echo $contact_entry->id; ?>" onClick='javascript:select_contact(<?php echo $contact_entry->id; ?>);'>
    <?php if($contact_entry->company == true) { 
        $small_image = "images/company_small.png";
    } else {
        $small_image = "images/person_small.png";
    }; ?>
    <input type="checkbox" name="ct_<?php echo $contact_entry->id; ?>" value="<?php echo $contact_entry->id; ?>" onclick='javascript:event.stopPropagation();'>
    <img src="<?php echo AB_TPL.$small_image; ?>" >
    <a role="button"><?php echo $contact_entry->name(); ?> </a>
</div>
