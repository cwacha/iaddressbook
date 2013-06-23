    <tr>
        <td class="person_left">
            <div class="person_labels"><?php echo $lang['label_relatednames']; ?></div>
        </td>
        <td></td>
    </tr>
    
    <tr id="relatedlabel_template1" style="display: none;">
        <td class="person_left">
            <div class="person_labels">
                <select name="relatednamelabel_" size="1" class="text" onchange="custom_relatedlabel(this);">
                    <option value='_$!<Father>!$_' ><?php echo tpl_label('_$!<Father>!$_'); ?></option>
                    <option value='_$!<Mother>!$_' ><?php echo tpl_label('_$!<Mother>!$_'); ?></option>
                    <option value='_$!<Parent>!$_' ><?php echo tpl_label('_$!<Parent>!$_'); ?></option>
                    <option value='_$!<Brother>!$_' ><?php echo tpl_label('_$!<Brother>!$_'); ?></option>
                    <option value='_$!<Sister>!$_' ><?php echo tpl_label('_$!<Sister>!$_'); ?></option>
                    <option value='_$!<Child>!$_' ><?php echo tpl_label('_$!<Child>!$_'); ?></option>
                    <option value='_$!<Friend>!$_' selected ><?php echo tpl_label('_$!<Friend>!$_'); ?></option>
                    <option value='_$!<Spouse>!$_' ><?php echo tpl_label('_$!<Spouse>!$_'); ?></option>
                    <option value='_$!<Partner>!$_' ><?php echo tpl_label('_$!<Partner>!$_'); ?></option>
                    <option value='_$!<Assistant>!$_' ><?php echo tpl_label('_$!<Assistant>!$_'); ?></option>
                    <option value='_$!<Manager>!$_' ><?php echo tpl_label('_$!<Manager>!$_'); ?></option>
                    <option value='_$!<Other>!$_' ><?php echo tpl_label('_$!<Other>!$_'); ?></option>
                    <option disabled>-------</option>
                    <option value='CUSTOM' ><?php echo tpl_label("CUSTOM"); ?></option>
                </select>
            </div>
        </td>
        <td class="person_right">
            <div class="person_text">
                <input type="text" name="relatedname_" value="" class="text" />
                <a href="#" onclick="add_relatedlabel('_$!<Friend>!$_');return false;"><img src="<?php echo AB_TPL; ?>images/plus.gif"></a>
                <a href="#" onclick="del_relatedlabel(this);return false;"><img src="<?php echo AB_TPL; ?>images/minus.gif"></a>
            </div>
        </td>
    </tr>

    <tr id="relatedlabel_position"><td></td><td></td></tr>
    

<script type="text/javascript">
var relatedlabel_counter = 0;

function add_relatedlabel(label,content) {
    if(!label) label = '';
    if(!content) content = '';
    relatedlabel_counter++;
    var custom_label = 1;

    var newBlock = document.getElementById('relatedlabel_template1').cloneNode(true);
    newBlock.id = '';
    newBlock.style.display = '';
    var childNode = newBlock.getElementsByTagName("*");
    for (var i=0;i<childNode.length;i++) {
        var theName = childNode[i].name;
        if (theName) {
            if(theName == 'relatedname_') {
                childNode[i].value = content;
            }
            childNode[i].name = theName + relatedlabel_counter;
        }
        if(childNode[i].tagName == 'OPTION') {
            if(childNode[i].value == label) {
                childNode[i].selected = true;
                custom_label = 0;
            } else {
                childNode[i].selected = false;
            }
        }
    }
    if(custom_label) {
        var object = newBlock.getElementsByTagName("select")[0];
        object.options[object.length] = new Option(label, label);
        object.selectedIndex = object.length - 1;
    }

    var insertHere = document.getElementById('relatedlabel_position');
    insertHere.parentNode.insertBefore(newBlock, insertHere);    
}
function del_relatedlabel(object) {
    // careful! this code depends on the actual HTML code!    
    var block = object.parentNode.parentNode.parentNode;
    block.parentNode.removeChild(block);
}
function custom_relatedlabel(object) {
    if(object.options[object.selectedIndex].value == 'CUSTOM') {
        // get custom label
        var label = prompt("<?php echo $lang['label_customprompt']; ?>", "");
        
        // add custom label to options
        if(label) {
            object.options[object.length] = new Option(label, label);
            object.selectedIndex = object.length - 1;
        } else {
            object.selectedIndex = 0;
        }
    }
}

<?php
foreach($contact->relatednames as $rname) {
    echo "add_relatedlabel('".$rname['label']."', '".$rname['name']."');\n";
}
?>

if(relatedlabel_counter == 0) add_relatedlabel('_$!<Friend>!$_');

</script>

