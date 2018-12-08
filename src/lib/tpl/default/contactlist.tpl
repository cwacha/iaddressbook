<h4><?php echo $lang['contacts']; ?></h4>

<?php if($conf['contactlist_abc'] == true) { ?>
    <p class="pl-2 pr-2 small text-center">
        <?php echo tpl_abc(); ?>
    </p>
<?php } ?>

<div class="contactlist_item">
    <div class="text-muted">
        <input type='checkbox' name="selectall" onClick="select_all('ct_form', this.checked)" />
        <?php echo $lang['category_all']; ?>
    </div>
</div>
<form method="post" action="" name='ct_form'>
    <?php
        global $contact_entry;

        $i = 0;
        foreach($contactlist as $contact_entry) {
            $i++;
            if($i <= $contactlist_offset) continue;
            if($contactlist_limit > 0 and ($i > $contactlist_offset + $contactlist_limit)) continue;
            include(template('contactlist_item.tpl'));
        }
        
        if(count($contactlist) < 1) {
            tpl_include('contactlist_empty.tpl');
        }
    ?>

    <p class="mt-2 text-muted text-center">
        <?php echo count($contactlist); ?> <?php echo $lang['contacts']; ?>
    </p>

    <p class="pl-2 pr-2 small">
        <?php echo tpl_pageselect(); ?>
    </p>

    <input type="hidden" name="do" value="" />
    <input type="hidden" name="cat_id" value="" />
    <input type="hidden" name="l" value="" />
    <input type="hidden" name="o" value="" />
</form>

<script type="text/javascript">
    function select_all(formname, checkvalue) {
        var theForm = this.document.forms[formname], z = 0;
        for(z=0; z<theForm.length;z++) {
            if(theForm[z].type == 'checkbox' && theForm[z].name != 'checkall') {
                theForm[z].checked = !theForm[z].checked;
            }
        }
    }
    function select_l(letter) {
        document.ct_form.elements["do"].value = 'select_letter';
        document.ct_form.elements["l"].value = letter;
        document.ct_form.submit();
    }
    function select_o(offset) {
    	document.ct_form.elements["do"].value = 'select_offset';
    	document.ct_form.elements["o"].value = offset;
    	document.ct_form.submit();
    }

    function select_contact(id) {
        document.select_contact_form.elements['id'].value = id;
        document.select_contact_form.submit();
    }
    function check_contact(id) {
        event.stopPropagation();
    }
</script>

<form method='post' action='' name='select_contact_form'>
    <input type='hidden' name='do' value='show' />
    <input type='hidden' name='id' value='' />
</form>
