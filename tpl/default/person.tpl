<table width="100%">
    <tr><td style="height: 2em;"></td><td></td></tr>
    <tr>
        <td class="person_left" >
            <!-- Begin Photo -->
            <img <?php if(!empty($conf['photo_size'])) echo "width='".$conf['photo_size']."'"; ?> src="<?php echo $PHP_SELF; ?>?do=img&id=<?php echo $contact->id; ?>">
            <!-- End Photo -->
        </td>
        <td class="person_right" >
            <!-- Begin Name / Company -->
            <?php if($contact->company == false) {; ?>
            <div class="person_name">
                <?php echo $contact->title; ?>
                <?php echo $contact->firstname; ?>
                <?php echo $contact->firstname2; ?>
                <?php echo $contact->lastname; ?>
                <?php echo $contact->suffix; ?>
                <br>
            </div>
            <div class="person_nickname">
                <?php if($contact->nickname != "") echo "\"$contact->nickname\"<br>"; ?>
            </div>
            <div class="person_text">
                <?php echo $contact->jobtitle; ?><br>
                <?php echo $contact->department; ?><br>
                <?php echo $contact->organization; ?><br>                
            </div>
            <?php } else {; ?>
            <div class="person_name">
                <?php echo $contact->organization; ?><br>                
            </div>
            <div class="person_text">
                <?php echo $contact->title; ?>
                <?php echo $contact->firstname; ?>
                <?php echo $contact->firstname2; ?>
                <?php echo $contact->lastname; ?>
                <?php echo $contact->suffix; ?>
                <br>
            <div class="person_nickname">
                <?php if($contact->nickname != "") echo "\"$contact->nickname\"<br>"; ?>
            </div>
            <div class="person_text">
                <?php echo $contact->jobtitle; ?><br>
                <?php echo $contact->department; ?><br>
            </div>            
            <?php }; ?>
            <!-- End Name / Company -->
        </td>
    </tr>

    <tr><td style="height: 2em;"></td><td></td></tr>

    <!-- Begin Phones -->
    <?php tpl_phones(); ?>
    <!-- End Phones -->

    <tr><td style="height: 2em;"></td><td></td></tr>

    <!-- Begin Emails -->
    <?php tpl_emails(); ?>
    <!-- End Emails -->

    <tr><td style="height: 2em;"></td><td></td></tr>

    <!-- Begin URLs -->
    <?php tpl_urls(); ?>
    <!-- End URLs -->

    <tr><td style="height: 2em;"></td><td></td></tr>

    <!-- Begin Birthday -->
    <?php if(!empty($contact->birthdate) and $contact->birthdate != "0000-00-00") { ?>
    
    <tr>
        <td class="person_left">
            <div class="person_labels">
                <?php echo $lang['label_birthday']; ?>
            </div>
        </td>
        <td class="person_right">
            <div class="person_text">
                <?php echo nice_date($conf['bdformat'], $contact->birthdate); ?>
            </div>
        </td>
    </tr>
    <?php } else {}; ?>
    <!-- End Birthday -->

    <tr><td style="height: 2em;"></td><td></td></tr>

    <!-- Begin Relatednames -->
    <?php tpl_relatednames(); ?>
    <!-- End Relatednames -->


    <tr><td style="height: 2em;"></td><td></td></tr>

    <!-- Begin Chathandles -->
    <?php tpl_chathandles(); ?>
    <!-- End Chathandles -->

    <tr><td style="height: 2em;"></td><td></td></tr>

    <!-- Begin Addresses -->
    <?php tpl_addresses(); ?>
    <!-- End Addresses -->

    <tr><td style="height: 1em;"></td><td></td></tr>
    
    <!-- Begin Notes -->
    <?php if(!empty($contact->note)) { ?>
    <tr>
        <td class="person_left">
            <div class="person_labels"><?php echo $lang['label_notes']?></div>
        </td>
        <td class="person_right">
            <div class="person_text">
                <?php echo str_replace("\n", "<br>", $contact->note). "<br>"; ?>
            </div>
        </td>
    </tr>
    <?php } else {}; ?>
    <!-- End Notes -->

    <tr><td style="height: 2em;"></td><td></td></tr>

    <!-- Begin Categories -->
    <tr>
        <?php if(!empty($categories)) { ?>
            <td class="person_left">
                <div class="person_labels"><?php echo $lang['category']?></div>
            </td>
            <td class="person_right">
                <div class="person_text">
                    <?php
                    foreach($categories as $category) {
                        echo "<a href='?do=cat_select&cat_id=".$category->id."'>".$category->name."</a>  ";
                    }
                    ?>
                </div>
            </td>
        <?php }; ?>
    </tr>
    <!-- End Categories -->

    <tr><td style="height: 2em;"></td><td></td></tr>
    
</table>


<div class="person_smalltext">
    <?php echo $lang['label_updated'] . date($conf['dformat'], strtotime($contact->modificationdate)); ?>
</div>
<div class="separator100"></div>


<!-- Begin Buttons -->
    <form method="POST" action="<?php echo $PHP_SELF; ?>">
        <input type="hidden" name="id" value="<?php echo $contact->id; ?>" />
        <input type="hidden" name="do" value="edit" />
        <input type="submit" value="<?php echo $lang['btn_edit']?>" class="button" style="float: right;" />
    </form>
    <form method="POST" action="<?php echo $PHP_SELF; ?>">
        <input type="hidden" name="do" value="delete" />
        <input type="hidden" name="id" value="<?php echo $contact->id; ?>" />
        <input type="submit" value="<?php echo $lang['btn_delete']?>" onClick="return confirm('<?php echo $contact->name() .": " . $lang['confirm_del_contact']; ?>')" class="button" style="float: right;" />
    </form>
    <form method="post" action="<?php echo $PHP_SELF; ?>">
        <input type="hidden" name="do" value="export_vcard" />
        <input type="hidden" name="id" value="<?php echo $contact->id; ?>" />
        <input type="submit" value="<?php echo $lang['btn_vcardexport']?>" class="button" style="float: right;" />
    </form>
<!-- End Buttons -->
