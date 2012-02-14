<td class="contactlist_td">
    <div class="contactlist">
        <div class="panel">
            <table class="header">
                <tr class="header_tr">
                    <td class="endcap"><img src="<?php echo AB_TPL; ?>images/split1_left.gif"></td>
                    <td class="middle"><?php echo $lang['contacts']; ?> (<?php echo count($contactlist); ?>)</td>
                    <td class="endcap"><img src="<?php echo AB_TPL; ?>images/split1_right.gif"></td>
                </tr>
            </table>
        </div>
                
        <!-- Letter Filter Begin -->
        <div style="height: 0.5em;"></div>
        
        <div class="person_smalltext" style="text-align: center; padding-left: 10px; padding-right: 10px;" >
            <?php echo tpl_abc(); ?>
        </div>
        
        <div style="height: 0.5em;"></div>
        <!-- Letter Filter End -->
        
        <input type='checkbox' name="selectall" onClick="select_all('ct_form', this.checked)" style="float: left; margin-left: 5px; margin-top: 7px;"/>
        <div class="person_smalltext" style="margin-top: 5px;">
            <?php echo $lang['category']; ?>
            <?php echo tpl_catselect(); ?>
		</div>
		<div class="separator100">&nbsp;</div>
        
        <form method="post" action="<?php echo $PHP_SELF; ?>" name='ct_form'>

            <?php echo tpl_contactlist(); ?>
            
            <div class="separator100">&nbsp;</div>
            
            <!-- Navigation Begin -->
            <div class="person_smalltext">
                <div style="text-align: center;" >
                    <?php echo tpl_pageselect(); ?>
                </div>
            </div>
            
            <div style="height: 0.5em;"></div>
            
            <!-- Navigation End -->

            <div class="person_smalltext">
                
                <!-- Contacts Section Begin -->
                <?php echo $lang['contacts']; ?>
                <div class="separator100">&nbsp;</div>
                <div style="float: left;"> <a href="javascript:do_action('new')"><?php echo $lang['create_contact']; ?></a> </div>
                <div style="float: right;"> <a href="javascript:do_action('delete_many', '<?php echo $lang['confirm_del_contacts']; ?>')"><?php echo $lang['delete_contacts']; ?></a> </div>

                <div style="height: 3em;"></div>
                <!-- Contacts Section End -->

                <!-- Category Section Begin -->
                <?php echo $lang['category']; ?>
                <div class="separator100">&nbsp;</div>

                <select name="cat_menu" size="1" style="float: right;" onChange="cat_menu_change()">
                    <option value='' selected><?php echo $lang['select_action']; ?></option>
                    <?php
                        foreach($categories as $category) {
                            if($category->id == $CAT_ID) {
                                if(substr($category->int_name,0,1) != ' ')
                                    echo "<option value='catdel_$category->id' >".$lang['cat_delete']." $category->name</option>";
                            }
                        }
                    ?>
                    <optgroup label="<?php echo $lang['cat_add_to']; ?>">
                    <?php
                        foreach($categories as $category) {
                            if(substr($category->int_name,0,1) != ' ')
                                echo "<option value='addcon_$category->id' $sel >$category->name</option> \n";
                        }
                    ?>
                    </optgroup>
                    <optgroup label="<?php echo $lang['cat_delete_from']; ?>">
                    <?php
                        foreach($categories as $category) {
                            $disp = 1;
                            if($category->int_name == ' __all__') $disp = 0;
                            if($category->int_name == ' __lastimport__') $disp = 0;
                            if($CAT_ID != 0 && $category->id != $CAT_ID) $disp = 0;
                            if($disp) echo "<option value='delcon_$category->id' $sel >$category->name</option> \n";
                        }
                    ?>
                    </optgroup>
                </select>
                
                <div style="height: 2.2em;"></div>
                
                <div style="float: left; margin-right: 5px;"> <a href="javascript:do_action('cat_add')"><?php echo $lang['cat_add']; ?></a> </div>
                <input type="text" name="cat_name" class="text" style="float: right;" onkeypress="CheckEnter(event);" />
                
                <div style="height: 3em;"></div>
                <!-- Category Section End -->

                <!-- Import Section Begin -->
                <?php echo $lang['import_export']; ?>
                <div class="separator100">&nbsp;</div>
                <div style="float: left;"> <a href="javascript:do_action('export_vcard_cat')"><?php echo $lang['export_vcard']; ?></a> </div>
                <div style="height: 1.5em;"></div>
                <div style="float: left;"> <a href="javascript:do_action('export_csv_cat')"><?php echo $lang['export_csv']; ?></a> </div>
                <div style="height: 1.5em;"></div>
                <div style="float: left;"> <a href="javascript:do_action('export_ldif_cat')"><?php echo $lang['export_ldif']; ?></a> </div>
                <div style="height: 1.5em;"></div>
                <div style="float: left;"> <a href="javascript:do_action('import_folder')"><?php echo $lang['import_folder']; ?></a> </div>
                <div style="height: 3em;"></div>
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
</script>
