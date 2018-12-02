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


<td class="contactlist_td">
    <div class="contactlist">
                                
        <form method="post" action="" name='ctold_form'>

            
            <div class="separator100">&nbsp;</div>
            
            
            <div style="height: 0.5em;"></div>
            
            <!-- Navigation End -->

            <div class="person_smalltext">
                

                <!-- Category Section Begin -->
                <?php echo $lang['category']; ?>
                <div class="separator100">&nbsp;</div>

                <select name="cat_menu" size="1" style="float: right;" onChange="cat_menu_change()">
                    <option value='' selected><?php echo $lang['select_action']; ?></option>
                    <?php
                        foreach($categories as $category) {
                            if($category->id == $CAT_ID) {
                                if($category->name() != ' __all__')
	                                echo "<option value='catdel_$category->id' >".$lang['cat_delete']." ".$category->displayName()."</option>";
                            }
                        }
                    ?>
                    <optgroup label="<?php echo $lang['cat_add_to']; ?>">
                    <?php
                        foreach($categories as $category) {
                            if(strpos($category->name(), ' __') !== 0)
 	                           echo "<option value='addcon_$category->id' >".$category->displayName()."</option> \n";
                        }
                    ?>
                    </optgroup>
                    <optgroup label="<?php echo $lang['cat_delete_from']; ?>">
                    <?php
                        foreach($categories as $category) {
                            $disp = 1;
                            if($category->name() == ' __all__') $disp = 0;
                            if($category->name() == ' __lastimport__') $disp = 0;
                            if($CAT_ID != 0 && $category->id != $CAT_ID) $disp = 0;
                            if($disp) echo "<option value='delcon_$category->id' >".$category->displayName()."</option> \n";
                        }
                    ?>
                    </optgroup>
                </select>
                                
                <div style="height: 3em;"></div>
                <!-- Category Section End -->

                <!-- Import Section Begin -->
                <!-- Import Section End -->
                
                
                <input type="hidden" name="do" value="" />
                <input type="hidden" name="cat_id" value="" />
                <input type="hidden" name="l" value="" />
                <input type="hidden" name="o" value="" />
            </div>    
        </form>
        
    </div>
</td>

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
function do_action(act, confirmation) {
    if(confirmation) {
        if(!confirm(confirmation)) return;
    }
    document.ct_form.elements["do"].value = act;
    document.ct_form.submit();
}
function cat_menu_change() {
    var options = document.ct_form.cat_menu.options;
    for (var i=0; i < options.length; i++) {
        if (options[i].selected) {
            var o = options[i].value.split("_");
            var action = o[0];
            var id = o[1];
            //alert("action: " + action + " id: " + id);
            document.ct_form.elements["cat_id"].value = id;
            if(action == "catdel") do_action('cat_del', '<?php echo $lang['confirm_cat_delete']; ?>');
            if(action == "addcon") do_action('cat_add_contacts');
            if(action == "delcon") do_action('cat_del_contacts', '<?php echo $lang['confirm_cat_remove_contacts']; ?>');
        }
    }
}
function CheckEnter(evt) {
    var keyCode = (evt.charCode)? evt.charCode:((evt.which)? evt.which:evt.keyCode);
    if (keyCode != 13) {
        return true;
    }
    // Enter has been hit:
    do_action('cat_add');
    return false;
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
