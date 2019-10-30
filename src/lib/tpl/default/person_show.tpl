<div class="container pt-4">
    <div class="row">
        <div class="col person_left">
            <!-- Begin Photo -->
            <img class="person_photo" <?php if(!empty($conf['photo_size'])) echo "width='".$conf['photo_size']."'"; ?> src="?do=img&id=<?php echo $contact->id; ?>">
            <!-- End Photo -->
        </div>
        <div class="col person_right">
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
                <div class="person_namephonetic">
                    <?php if(!empty($contact->phoneticfirstname)) echo $contact->phoneticfirstname; ?>
                    <?php if(!empty($contact->phoneticlastname)) echo $contact->phoneticlastname; ?>
                </div>
                <div class="person_nickname">
                    <?php if($contact->nickname != "") echo "\"$contact->nickname\""; ?>
                </div>
                <div class="person_text">
                    <?php echo $contact->organization; ?>
                    <?php if(!empty($contact->jobtitle)) echo "&ndash; " . $contact->jobtitle; ?>
                    <br>
                    <?php echo $contact->department; ?>
                </div>
            <?php } else {; ?>
                <div class="person_name">
                    <?php echo $contact->organization; ?>
                </div>
                <div class="person_text">
                    <?php echo $contact->title; ?>
                    <?php echo $contact->firstname; ?>
                    <?php echo $contact->firstname2; ?>
                    <?php echo $contact->lastname; ?>
                    <?php echo $contact->suffix; ?>
                    <br>
                </div>
                <div class="person_namephonetic">
                    <?php if(!empty($contact->phoneticfirstname)) echo $contact->phoneticfirstname; ?>
                    <?php if(!empty($contact->phoneticlastname)) echo $contact->phoneticlastname; ?>
                </div>
                <div class="person_nickname">
                    <?php if($contact->nickname != "") echo "\"$contact->nickname\"<br>"; ?>
                </div>
                <div class="person_text">
                    <?php echo $contact->jobtitle; ?>
                    <br>
                    <?php echo $contact->department; ?>
                </div>
            <?php }; ?>
            <!-- End Name / Company -->
        </div>
    </div>
    <div class="row pb-4" ></div>

    <!-- Begin Phones -->
    <?php
        foreach($contact->phones as $phone)
            include(template('person_show_phone.tpl'));
    ?>
    <!-- End Phones -->
    <div class="row pb-4" ></div>

    <!-- Begin Emails -->
    <?php
        foreach($contact->emails as $email)
            include(template('person_show_email.tpl'));
    ?>
    <!-- End Emails -->
    <div class="row pb-4" ></div>

    <!-- Begin URLs -->
    <?php
        foreach($contact->urls as $url)
            include(template('person_show_url.tpl'));
        ?>
    <!-- End URLs -->
    <div class="row pb-4" ></div>

    <div class="row">
        <!-- Begin Birthday -->
        <?php if(!empty($contact->birthdate) and $contact->birthdate != "0000-00-00") { ?>
        
        <div class="col person_left">
            <div class="person_labels">
                <?php echo lang('label_birthday'); ?>
            </div>
        </div>
        <div class="col person_right">
            <div class="person_text">
                <?php echo nice_date($conf['bdformat'], $contact->birthdate); ?>
            </div>
        </div>
        <?php } else {}; ?>
        <!-- End Birthday -->
    </div>
    <div class="row pb-4" ></div>

    <!-- Begin Relatednames -->
    <?php
        foreach($contact->relatednames as $relatedname)
            include(template('person_show_relatedname.tpl'));
    ?>
    <!-- End Relatednames -->
    <div class="row pb-4" ></div>

    <!-- Begin Chathandles -->
    <?php
        foreach($contact->chathandles as $chathandle)
            include(template('person_show_chathandle.tpl'));
    ?>
    <!-- End Chathandles -->
    <div class="row pb-4" ></div>

    <!-- Begin Addresses -->
    <?php
        foreach($contact->addresses as $address)
            include(template('person_show_address.tpl'));
    ?>
    <!-- End Addresses -->
    <div class="row pb-4" ></div>

    <!-- Begin Notes -->
    <?php if(!empty($contact->note)) { ?>
    <div class="row">
        <div class="col person_left">
            <div class="person_labels"><?php echo lang('label_notes');?></div>
        </div>
        <div class="col person_right">
            <div class="person_text">
                <?php echo str_replace("\n", "<br>", $contact->note). "<br>"; ?>
            </div>
        </div>
    </div>
    <?php }; ?>
    <!-- End Notes -->
    <div class="row pb-4" ></div>

    <!-- Begin Categories -->
    <div class="row">
        <?php
            $contact_categories = $contact->get_categories();
            if(!empty($contact_categories)) {
        ?><div class="col person_left">
                <div class="person_labels"><?php echo lang('category');?></div>
            </div>
            <div class="col person_right">
                <div class="person_text">
                    <?php
                    foreach($contact_categories as $category) {
                        echo "<a href='?do=cat_select&cat_id=".$category->id."'>".$category->displayName()."</a>  ";
                    }
                    ?>
                </div>
            </div>
        <?php }; ?>
    </div>
    <!-- End Categories -->
    <div class="row pb-4" ></div>
</div>

<div class="container pt-4">
    <div class="row pb-4" >
        <div class="col text-right text-muted">
            <small>
                <?php echo lang('label_updated') . date($conf['dformat'], $contact->modification_ts) ?>
                <br>
                <?php echo $contact->uid ?>
            </small>
        </div>
    </div>
</div>

<!-- Begin Buttons -->
<div class="btn-group float-right pb-4" role="group">
    <button type="button" class="btn btn-outline-primary btn-sm" onclick="javascript:person_action('edit');"><?php echo lang('contact_edit');?></button>
    <button type="button" class="btn btn-outline-primary btn-sm" onclick="javascript:person_action('export_vcard');"><?php echo lang('export_vcard');?></button>
    <button type="button" class="btn btn-outline-danger btn-sm" onclick="javascript:person_action('delete', '<?php echo $contact->name() .": " . lang('confirm_del_contact'); ?>');"><?php echo lang('contact_delete');?></button>
</div>

<form method="post" action="" name="person_buttons">
    <input type="hidden" name="id" value="<?php echo $contact->id; ?>" />
    <input type="hidden" name="do" value="" />
</form>

<script type="text/javascript">
function person_action(act, confirmation) {
    if(confirmation) {
        if(!confirm(confirmation)) return;
    }
    document.person_buttons.elements["do"].value = act;
    document.person_buttons.submit();
}
</script>
<!-- End Buttons -->
