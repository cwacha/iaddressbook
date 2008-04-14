<form method="post" enctype="multipart/form-data" action="<?= $PHP_SELF ?>">

<table width="100%">
    <tr><td style="height: 2em;"></td><td></td></tr>
    <tr>
        <td class="person_left" >
            <!-- Begin Photo -->
            <img <?php if(!empty($conf['photo_size'])) echo "height='".$conf['photo_size']."'" ?> src="<?= $PHP_SELF ?>?do=img&id=<?= $contact->id ?>">
            <!-- End Photo -->
        </td>
        <td class="person_right" >
            <div class="person_text">
                <!-- Begin Photo Edit -->
                <?= $lang['label_photoremove'] ?> <input type="checkbox" name="photo_delete" /><br><br>
                <?= $lang['label_photochange'] ?> <input type="file" name="photo_file" value="" /><br>
                <!-- End Photo Edit -->
            </div>
        </td>
    </tr>
    
    <tr><td style="height: 2em;"></td><td></td></tr>
    
    <!-- Begin Name / Company -->
    <tr>
        <td class="person_left">
            <div class="person_labels">
                <?= $lang['label_title'] ?>
            </div>
        </td>
        <td class="person_right">
            <div class="person_text">
                <input type="text" name="title" value="<?= $contact->title ?>" class="text" />
            </div>
        </td>
    </tr>
    <tr>
        <td class="person_left">
            <div class="person_labels">
                <?= $lang['label_lastname'] ?>
            </div>
        </td>
        <td class="person_right">
            <div class="person_text">
                <input type="text" name="lastname" value="<?= $contact->lastname ?>" class="text" />
            </div>
        </td>
    </tr>
    <tr>
        <td class="person_left">
            <div class="person_labels">
                <?= $lang['label_firstname'] ?>
            </div>
        </td>
        <td class="person_right">
            <div class="person_text">
                <input type="text" name="firstname" value="<?= $contact->firstname ?>" class="text" />
            </div>
        </td>
    </tr>
    <tr>
        <td class="person_left">
            <div class="person_labels">
                <?= $lang['label_firstname2'] ?>
            </div>
        </td>
        <td class="person_right">
            <div class="person_text">
                <input type="text" name="firstname2" value="<?= $contact->firstname2 ?>" class="text" />
            </div>
        </td>
    </tr>
    <tr>
        <td class="person_left">
            <div class="person_labels">
                <?= $lang['label_suffix'] ?>
            </div>
        </td>
        <td class="person_right">
            <div class="person_text">
                <input type="text" name="suffix" value="<?= $contact->suffix ?>" class="text" />
            </div>
        </td>
    </tr>
    
    <tr><td style="height: 1em;"></td><td></td></tr>
    
    <tr>
        <td class="person_left">
            <div class="person_labels">
                <?= $lang['label_nickname'] ?>
            </div>
        </td>
        <td class="person_right">
            <div class="person_text">
                <input type="text" name="nickname" value="<?= $contact->nickname ?>" class="text" />
            </div>
        </td>
    </tr>

    <tr><td style="height: 1em;"></td><td></td></tr>

    <tr>
        <td class="person_left">
            <div class="person_labels">
                <?= $lang['label_jobtitle'] ?>
            </div>
        </td>
        <td class="person_right">
            <div class="person_text">
                <input type="text" name="jobtitle" value="<?= $contact->jobtitle ?>" class="text" />
            </div>
        </td>
    </tr>
    <tr>
        <td class="person_left">
            <div class="person_labels">
                <?= $lang['label_department'] ?>
            </div>
        </td>
        <td class="person_right">
            <div class="person_text">
                <input type="text" name="department" value="<?= $contact->department ?>" class="text" />
            </div>
        </td>
    </tr>
    <tr>
        <td class="person_left">
            <div class="person_labels">
                <?= $lang['label_organization'] ?>
            </div>
        </td>
        <td class="person_right">
            <div class="person_text">
                <input type="text" name="organization" value="<?= $contact->organization ?>" class="text" />
            </div>
        </td>
    </tr>
    <tr>
        <td class="person_left">
            <div class="person_labels">
                <?= $lang['label_organization'] ?>
            </div>
        </td>
        <td class="person_right">
            <div class="person_text">
                <input type="checkbox" name="company" <?php if($contact->company == true) echo "checked" ?> class="text" />
            </div>
        </td>
    </tr>    
    <!-- End Name / Company -->

    <tr><td style="height: 2em;"></td><td></td></tr>

    <!-- Begin Phones -->
    <?php tpl_include('person_edit_phones.tpl') ?>
    <!-- End Phones -->

    <tr><td style="height: 2em;"></td><td></td></tr>

    <!-- Begin Emails -->
    <?php tpl_include('person_edit_emails.tpl') ?>
    <!-- End Emails -->

    <tr><td style="height: 2em;"></td><td></td></tr>

    <!-- Begin URLs -->
    <?php tpl_include('person_edit_urls.tpl') ?>
    <!-- End URLs -->

    <tr><td style="height: 2em;"></td><td></td></tr>

    <!-- Begin Birthday -->
    <tr>
        <td class="person_left">
            <div class="person_labels">
                <?= $lang['label_birthday'] ?>
            </div>
        </td>
        <td class="person_right">
            <div class="person_text">
                <input type="text" name="birthdate" value="<?= $contact->birthdate ?>" class="text" />
                (YYYY-MM-DD)
            </div>
        </td>
    </tr>
    <!-- End Birthday -->

    <tr><td style="height: 2em;"></td><td></td></tr>

    <!-- Begin Relatednames -->
    <?php tpl_include('person_edit_relatednames.tpl') ?>
    <!-- End Relatednames -->


    <tr><td style="height: 2em;"></td><td></td></tr>

    <!-- Begin Chathandles -->
    <?php tpl_include('person_edit_chathandles.tpl') ?>
    <!-- End Chathandles -->

    <tr><td style="height: 2em;"></td><td></td></tr>

    <!-- Begin Addresses -->
    <?php tpl_include('person_edit_addresses.tpl') ?>
    <!-- End Addresses -->

    <tr><td style="height: 2em;"></td><td></td></tr>
    
    <!-- Begin Notes -->
    <tr>
        <td class="person_left">
            <div class="person_labels"><?= $lang['label_notes']?></div>
        </td>
        <td class="person_right">
            <div class="person_text">
                <textarea name="note" rows="5" cols="50" class="text"><?= $contact->note ?></textarea>
            </div>
        </td>
    </tr>
    <!-- End Notes -->

    <tr><td style="height: 2em;"></td><td></td></tr>
    
</table>

<div class="separator100"></div>


    <input type="hidden" name="id" value="<?= $contact->id ?>" />
    <input type="hidden" name="do" value="save" />
    <input type="submit" value="<?= $lang['btn_save']?>" class="button" style="float: left;" />
</form>

<!-- Begin Buttons -->
<form method="POST" action="<?= $PHP_SELF ?>">
    <input type="hidden" name="id" value="<?= $contact->id ?>" />
    <input type="hidden" name="do" value="show" />
    <div class="person_text" style="text-align: right;">
    <input type="submit" value="<?= $lang['btn_cancel']?>" class="button" style="float: right;" />
    </div>
</form>
<!-- End Buttons -->

