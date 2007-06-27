<td class="contactlist_td">
    <div class="contactlist">
        <div class="panel">
            <table class="header">
                <tr class="header_tr">
                    <td class="endcap"><img src="<?= AB_TPL ?>images/split1_left.gif"></td>
                    <td class="middle"><?= $lang['contacts'] ?> (<?= count($contactlist) ?>)</td>
                    <td class="endcap"><img src="<?= AB_TPL ?>images/split1_right.gif"></td>
                </tr>
            </table>
        </div>
                
        <!-- Letter Filter Begin -->
        <div style="height: 0.5em;"></div>
        
        <div class="person_smalltext" style="text-align: center;" >
            <?= tpl_abc(); ?>
        </div>
        
        <div style="height: 0.5em;"></div>
        <!-- Letter Filter End -->
        
        <input type='checkbox' name="selectall" onClick="select_all('ct_form', this.checked)" style="float: left; margin-left: 5px; margin-top: 7px;"/>
        <div class="person_smalltext" style="margin-top: 5px;">
            <?= $lang['category'] ?>
            <?= tpl_catselect(); ?>
		</div>
		<div class="separator100">&nbsp;</div>
        
        <form method="post" action="<?= $PHP_SELF ?>" name='ct_form'>

            <?= tpl_contactlist() ?>
            
            <div class="separator100">&nbsp;</div>
            
            <!-- Navigation Begin -->
            <div class="person_smalltext">
                <div style="text-align: center;" >
                    <?= tpl_pageselect(); ?>
                </div>
            </div>
            
            <div style="height: 0.5em;"></div>
            
            <!-- Navigation End -->

            <div class="person_smalltext">
                
                <!-- Contacts Section Begin -->
                <?= $lang['contacts'] ?>
                <div class="separator100">&nbsp;</div>
                <div style="float: left;"> <a href="javascript:do_action('new')"><?= $lang['create_contact'] ?></a> </div>
                <div style="float: right;"> <a href="javascript:do_action('delete_many', '<?= $lang['confirm_del_contacts'] ?>')"><?= $lang['delete_contacts'] ?></a> </div>

                <div style="height: 3em;"></div>
                <!-- Contacts Section End -->

                <!-- Category Section Begin -->
                <?= $lang['category'] ?>
                <div class="separator100">&nbsp;</div>

                <div style="float: left; margin-right: 5px;"> <a href="javascript:do_action('cat_add_contacts')"><?= $lang['cat_add_to'] ?></a> /</div>
                <div style="float: left; margin-right: 5px;"> <a href="javascript:do_action('cat_del_contacts', '<?= $lang['confirm_cat_remove_contacts'] ?>')"><?= $lang['cat_delete_from'] ?></a> </div>

                <select name="cat_id" size="1" style="float: right;" >
                    <?php
                        foreach($categories as $category) {
                            $category->id == $CAT_ID? $sel = 'selected' : $sel = '';
                            echo "<option value='".$category->id."' $sel >".$category->name."</option> \n";
                        }
                    ?>
                </select>
                
                <div style="height: 2.2em;"></div>
                
                <div style="float: left; margin-right: 5px;"> <a href="javascript:do_action('cat_add')"><?= $lang['cat_add'] ?></a> </div>
                <input type="text" name="cat_name" class="text" style="float: right;" onkeypress="CheckEnter(event);" />
                
                <div style="height: 2.2em;"></div>

                <div style="float: left;"> <a href="javascript:do_action('cat_del', '<?= $lang['confirm_cat_delete'] ?>')"><?= $lang['cat_delete'] ?></a> </div>
                <div style="height: 3em;"></div>
                <!-- Category Section End -->

                <!-- Import Section Begin -->
                <?= $lang['import_export'] ?>
                <div class="separator100">&nbsp;</div>
                <div style="float: left;"> <a href="javascript:do_action('export_vcard_cat')"><?= $lang['export_vcard'] ?></a> </div>
                <div style="height: 1.5em;"></div>
                <div style="float: left;"> <a href="javascript:do_action('export_csv_cat')"><?= $lang['export_csv'] ?></a> </div>
                <div style="height: 1.5em;"></div>
                <div style="float: left;"> <a href="javascript:do_action('export_ldif_cat')"><?= $lang['export_ldif'] ?></a> </div>
                <div style="height: 1.5em;"></div>
                <div style="float: left;"> <a href="javascript:do_action('import_folder')"><?= $lang['import_folder'] ?></a> </div>
                <div style="height: 3em;"></div>
                <!-- Import Section End -->
                
                
                <input type="hidden" name="do" value="" />
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
function CheckEnter(evt) {
    var keyCode = document.layers?evt.which:evt.keyCode;
    if (keyCode != 13) {
        return true;
    }
    // Enter has been hit:
    do_action('cat_add');
    return false;
}
</script>
