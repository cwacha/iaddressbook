<form method="post" enctype="multipart/form-data" action="<?php echo $PHP_SELF; ?>">

<table width="100%">
    <tr><td style="height: 2em;"></td><td></td></tr>
    <tr>
        <td class="person_left" >
            <!-- Begin Photo -->
            <img <?php if(!empty($conf['photo_size'])) echo "width='".$conf['photo_size']."'"; ?> src="<?php echo $PHP_SELF; ?>?do=img&id=<?php echo $contact->id; ?>">
            <!-- End Photo -->
        </td>
        <td class="person_right" >
            <div class="person_text">
                <!-- Begin Photo Edit -->
                <?php echo $lang['label_photoremove']; ?> <input type="checkbox" name="photo_delete" /><br><br>
                <?php echo $lang['label_photochange']; ?> <input type="file" name="photo_file" value="" /><br>
                <!-- End Photo Edit -->
            </div>
        </td>
    </tr>
    
    <tr><td style="height: 2em;"></td><td></td></tr>
    
    <!-- Begin Name / Company -->
    <tr>
        <td class="person_left">
            <div class="person_labels">
                <?php echo $lang['label_title']; ?>
            </div>
        </td>
        <td class="person_right">
            <div class="person_text">
                <input type="text" name="title" value="<?php echo $contact->title; ?>" class="text" />
            </div>
        </td>
    </tr>
    <tr>
        <td class="person_left">
            <div class="person_labels">
                <?php echo $lang['label_firstname']; ?>
            </div>
        </td>
        <td class="person_right">
            <div class="person_text">
                <input type="text" name="firstname" value="<?php echo $contact->firstname; ?>" class="text" />
            </div>
        </td>
    </tr>
    <tr>
        <td class="person_left">
            <div class="person_labels">
                <?php echo $lang['label_firstname2']; ?>
            </div>
        </td>
        <td class="person_right">
            <div class="person_text">
                <input type="text" name="firstname2" value="<?php echo $contact->firstname2; ?>" class="text" />
            </div>
        </td>
    </tr>
    <tr>
        <td class="person_left">
            <div class="person_labels">
                <?php echo $lang['label_lastname']; ?>
            </div>
        </td>
        <td class="person_right">
            <div class="person_text">
                <input type="text" name="lastname" value="<?php echo $contact->lastname; ?>" class="text" />
            </div>
        </td>
    </tr>
    <tr>
        <td class="person_left">
            <div class="person_labels">
                <?php echo $lang['label_suffix']; ?>
            </div>
        </td>
        <td class="person_right">
            <div class="person_text">
                <input type="text" name="suffix" value="<?php echo $contact->suffix; ?>" class="text" />
            </div>
        </td>
    </tr>
    
    <tr><td style="height: 1em;"></td><td></td></tr>
    
    <tr>
        <td class="person_left">
            <div class="person_labels">
                <?php echo $lang['label_nickname']; ?>
            </div>
        </td>
        <td class="person_right">
            <div class="person_text">
                <input type="text" name="nickname" value="<?php echo $contact->nickname; ?>" class="text" />
            </div>
        </td>
    </tr>

    <tr><td style="height: 1em;"></td><td></td></tr>

    <tr>
        <td class="person_left">
            <div class="person_labels">
                <?php echo $lang['label_jobtitle']; ?>
            </div>
        </td>
        <td class="person_right">
            <div class="person_text">
                <input type="text" name="jobtitle" value="<?php echo $contact->jobtitle; ?>" class="text" />
            </div>
        </td>
    </tr>
    <tr>
        <td class="person_left">
            <div class="person_labels">
                <?php echo $lang['label_department']; ?>
            </div>
        </td>
        <td class="person_right">
            <div class="person_text">
                <input type="text" name="department" value="<?php echo $contact->department; ?>" class="text" />
            </div>
        </td>
    </tr>
    <tr>
        <td class="person_left">
            <div class="person_labels">
                <?php echo $lang['label_organization']; ?>
            </div>
        </td>
        <td class="person_right">
            <div class="person_text">
                <input type="text" name="organization" value="<?php echo $contact->organization; ?>" class="text" />
            </div>
        </td>
    </tr>
    <tr>
        <td class="person_left">
            <div class="person_labels">
                <?php echo $lang['label_isorganization']; ?>
            </div>
        </td>
        <td class="person_right">
            <div class="person_text">
                <input type="checkbox" name="company" <?php if($contact->company == true) echo "checked"; ?> class="text" />
            </div>
        </td>
    </tr>    
    <!-- End Name / Company -->

    <tr><td style="height: 2em;"></td><td></td></tr>

    <!-- Begin Phones -->
    <?php tpl_include('person_edit_phones.tpl'); ?>
    <!-- End Phones -->

    <tr><td style="height: 2em;"></td><td></td></tr>

    <!-- Begin Emails -->
    <?php tpl_include('person_edit_emails.tpl'); ?>
    <!-- End Emails -->

    <tr><td style="height: 2em;"></td><td></td></tr>

    <!-- Begin URLs -->
    <?php tpl_include('person_edit_urls.tpl'); ?>
    <!-- End URLs -->

    <tr><td style="height: 2em;"></td><td></td></tr>

    <!-- Begin Birthday -->
    <tr>
        <td class="person_left">
            <div class="person_labels">
                <?php echo $lang['label_birthday']; ?>
            </div>
        </td>
        <td class="person_right">
            <div class="person_text">
                <input type="text" name="birthdate" value="<?php echo $contact->birthdate; ?>" class="text" />
                (YYYY-MM-DD)
            </div>
        </td>
    </tr>
    <!-- End Birthday -->

    <tr><td style="height: 2em;"></td><td></td></tr>

    <!-- Begin Relatednames -->
    <?php tpl_include('person_edit_relatednames.tpl'); ?>
    <!-- End Relatednames -->


    <tr><td style="height: 2em;"></td><td></td></tr>

    <!-- Begin Chathandles -->
    <?php tpl_include('person_edit_chathandles.tpl'); ?>
    <!-- End Chathandles -->

    <tr><td style="height: 2em;"></td><td></td></tr>

    <!-- Begin Addresses -->
    <?php tpl_include('person_edit_addresses.tpl'); ?>
    <!-- End Addresses -->

    <tr><td style="height: 2em;"></td><td></td></tr>
    
    <!-- Begin Notes -->
    <tr>
        <td class="person_left">
            <div class="person_labels"><?php echo $lang['label_notes']?></div>
        </td>
        <td class="person_right">
            <div class="person_text">
                <textarea name="note" rows="5" cols="50" class="text"><?php echo $contact->note; ?></textarea>
            </div>
        </td>
    </tr>
    <!-- End Notes -->

    <tr><td style="height: 2em;"></td><td></td></tr>

    <!-- Begin Categories -->
    <tr>
        <td class="person_left">
            <div class="person_labels"><?php echo $lang['category']?></div>
        </td>
        <td class="person_right">
            <div class="person_text">
                <textarea name="category" rows="5" cols="50" class="text">
					<?php
                    foreach($categories as $category) {
                        echo "$category->name\n";
                    }
					?>
				</textarea>
            </div>
        </td>
    </tr>
    <!-- End Categories -->

    <tr><td style="height: 2em;"></td><td></td></tr>
    
</table>

<div class="separator100"></div>


    <input type="hidden" name="id" value="<?php echo $contact->id; ?>" />
    <input type="hidden" name="do" value="save" />
    <input type="submit" value="<?php echo $lang['btn_save']?>" class="button" style="float: left;" />
</form>

<!-- Begin Buttons -->
<form method="POST" action="<?php echo $PHP_SELF; ?>">
    <input type="hidden" name="id" value="<?php echo $contact->id; ?>" />
    <input type="hidden" name="do" value="show" />
    <div class="person_text" style="text-align: right;">
    <input type="submit" value="<?php echo $lang['btn_cancel']?>" class="button" style="float: right;" />
    </div>
</form>
<!-- End Buttons -->

