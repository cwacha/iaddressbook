<form method="post" enctype="multipart/form-data" action="" name="person_edit">

<div class="container pt-4">
    <div class="row">
        <div class="col person_left">
            <!-- Begin Photo -->
            <img <?php if(!empty($conf['photo_size'])) echo "width='".$conf['photo_size']."'"; ?> src="?do=img&id=<?php echo $contact->id; ?>">
            <!-- End Photo -->
        </div>
        <div class="col person_right" >
            <!-- Begin Photo Edit -->
            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="photo_delete" id="photoedit1"/>
                <label class="form-check-label" for="photoedit1"><?php echo lang('label_photoremove'); ?></label>
            </div>
            <div class="form-group">
                <label for="photofile1"><?php echo lang('label_photochange'); ?></label>
                <input type="file" class="form-control-file" name="photo_file" id="photofile1" />
            </div>
            <!-- End Photo Edit -->
        </div>
        <div class="col-1"></div>
    </div>
    
    <div class="row pb-4" ></div>
    
    <!-- Begin Name / Company -->
    <div class="row">
        <div class="col"></div>
        <div class="col person_right">
            <fieldset class="input-group-vertical">
                <div class="form-group">
                    <input type="text" class="form-control form-control-sm" name="title" placeholder="<?php echo lang('label_title'); ?>" value="<?php echo $contact->title; ?>">
                </div>
                <div class="form-group">
                    <input type="text" class="form-control form-control-sm" name="firstname" placeholder="<?php echo lang('label_firstname'); ?>" value="<?php echo $contact->firstname; ?>" />
                </div>
                <div class="form-group">
                    <input type="text" class="form-control form-control-sm" name="firstname2" placeholder="<?php echo lang('label_firstname2'); ?>" value="<?php echo $contact->firstname2; ?>" />
                </div>
                <div class="form-group">
                  <input type="text" class="form-control form-control-sm" name="lastname" placeholder="<?php echo lang('label_lastname'); ?>" value="<?php echo $contact->lastname; ?>" />
                </div>
                <div class="form-group">
                    <input type="text" class="form-control form-control-sm" name="suffix" placeholder="<?php echo lang('label_suffix'); ?>" value="<?php echo $contact->suffix; ?>" />
                </div>
            </fieldset>
        </div>
        <div class="col-1"></div>
    </div>
    <div class="row">
        <div class="col"></div>
        <div class="col person_right">
            <fieldset class="input-group-vertical">
                <div class="form-group">
                    <input type="text" class="form-control form-control-sm" name="phoneticfirstname" placeholder="<?php echo lang('label_phoneticfirstname'); ?>" value="<?php echo $contact->phoneticfirstname; ?>" />
                </div>
                <div class="form-group">
                    <input type="text" class="form-control form-control-sm" name="phoneticlastname" placeholder="<?php echo lang('label_phoneticlastname'); ?>" value="<?php echo $contact->phoneticlastname; ?>" />
                </div>
            </fieldset>
        </div>
        <div class="col-1"></div>
    </div>
    <div class="row">
        <div class="col"></div>
        <div class="col person_right">
            <div class="form-group">
                <input type="text" class="form-control form-control-sm" name="nickname" placeholder="<?php echo lang('label_nickname'); ?>" value="<?php echo $contact->nickname; ?>" />
            </div>
        </div>
        <div class="col-1"></div>
    </div>

    <div class="row">
        <div class="col"></div>
        <div class="col person_right">
            <fieldset class="input-group-vertical">
                <div class="form-group">
                    <input type="text" class="form-control form-control-sm" name="jobtitle" placeholder="<?php echo lang('label_jobtitle'); ?>" value="<?php echo $contact->jobtitle; ?>" />
                </div>
                <div class="form-group">
                    <input type="text" class="form-control form-control-sm" name="department" placeholder="<?php echo lang('label_department'); ?>" value="<?php echo $contact->department; ?>" />
                </div>
                <div class="form-group">
                    <input type="text" class="form-control form-control-sm" name="organization" placeholder="<?php echo lang('label_organization'); ?>" value="<?php echo $contact->organization; ?>" />
                </div>
            </fieldset>
        </div>
        <div class="col-1"></div>
    </div>
    <div class="row">
        <div class="col"></div>
        <div class="col form-check person_right">
            <div class="person_text">
                <input type="checkbox" class="form-check-input" name="company" <?php if($contact->company == true) echo "checked"; ?> id="company1" />
                <label class="form-check-label" for="company1"><?php echo lang('label_isorganization'); ?></label>
            </div>
        </div>
        <div class="col-1"></div>
    </div>    
    <!-- End Name / Company -->

    <div class="row pb-4" ></div>

    <!-- Begin Phones -->
    <?php tpl_include('person_edit_phones.tpl'); ?>
    <!-- End Phones -->

    <div class="row pb-4" ></div>

    <!-- Begin Emails -->
    <?php tpl_include('person_edit_emails.tpl'); ?>
    <!-- End Emails -->

    <div class="row pb-4" ></div>

    <!-- Begin URLs -->
    <?php tpl_include('person_edit_urls.tpl'); ?>
    <!-- End URLs -->

    <div class="row pb-4" ></div>

    <!-- Begin Birthday -->
    <div class="row">
        <div class="col person_left">
            <div class="person_labels">
                <?php echo lang('label_birthday'); ?>
            </div>
        </div>
        <div class="col person_right">
            <div class="person_text">
                <input type="text" name="birthdate" placeholder="YYYY-MM-DD" value="<?php echo $contact->birthdate; ?>" class="form-control form-control-sm" />
            </div>
        </div>
        <div class="col-1"></div>
    </div>
    <!-- End Birthday -->

    <div class="row pb-4" ></div>

    <!-- Begin Relatednames -->
    <?php tpl_include('person_edit_relatednames.tpl'); ?>
    <!-- End Relatednames -->


    <div class="row pb-4" ></div>

    <!-- Begin Chathandles -->
    <?php tpl_include('person_edit_chathandles.tpl'); ?>
    <!-- End Chathandles -->

    <div class="row pb-4" ></div>

    <!-- Begin Addresses -->
    <?php tpl_include('person_edit_addresses.tpl'); ?>
    <!-- End Addresses -->

    <div class="row pb-4" ></div>
    
    <!-- Begin Notes -->
    <div class="row">
        <div class="col person_left">
            <div class="person_labels"><?php echo lang('label_notes');?></div>
        </div>
        <div class="col person_right">
            <div class="form-group">
                <textarea name="note" rows="5" cols="50" class="form-control"><?php echo $contact->note; ?></textarea>
            </div>
        </div>
        <div class="col-1"></div>
    </div>
    <!-- End Notes -->

    <div class="row pb-4" ></div>

    <!-- Begin Categories -->
    <div class="row">
        <div class="col person_left">
            <div class="person_labels"><?php echo lang('category');?></div>
        </div>
        <div class="col person_right">
            <div class="form-group">
                <textarea name="category" rows="5" cols="50" placeholder="<?php echo lang('category');?>" class="form-control"><?php
                    $categories = $contact->get_categories();
                    foreach($categories as $category) {
                    	if(strpos($category->name(), ' __') === 0)
                    		continue;
                        echo $category->displayName()."\n";
                    }
				?></textarea>
            </div>
        </div>
        <div class="col-1"></div>
    </div>
    <!-- End Categories -->

    <div class="row pb-4" ></div>
    
</div>

    <input type="hidden" name="id" value="<?php echo $contact->id; ?>" />
    <input type="hidden" name="do" value="" />
</form>

<!-- Begin Buttons -->
<div class="btn-group float-right pb-4" role="group">
    <button type="button" class="btn btn-primary btn-sm" onclick="javascript:person_action('save');"><?php echo lang('contact_save');?></button>
    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="javascript:person_action('show');"><?php echo lang('cancel');?></button>
</div>

<script type="text/javascript">
function person_action(act, confirmation) {
    if(confirmation) {
        if(!confirm(confirmation)) return;
    }
    document.person_edit.elements["do"].value = act;
    document.person_edit.submit();
}
</script>
<!-- End Buttons -->

