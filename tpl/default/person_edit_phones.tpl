    <tr>
        <td class="person_left">
            <div class="person_labels"><?= $lang['label_phones'] ?></div>
        </td>
        <td></td>
    </tr>
    <tr id="phonelabel_template1" style="display: none;">
        <td class="person_left">
            <div class="person_labels">
                <select name="phonelabel_" size="1" class="text" >
                    <option value="HOME" selected ><?= tpl_label("HOME") ?></option>
                    <option value="CELL" ><?= tpl_label("CELL") ?></option>
                    <option value="WORK" ><?= tpl_label("WORK") ?></option>
                    <option value="MAIN" ><?= tpl_label("MAIN") ?></option>
                    <option value="HOME FAX" ><?= tpl_label("HOME FAX") ?></option>
                    <option value="WORK FAX" ><?= tpl_label("WORK FAX") ?></option>
                    <option value="PAGER" ><?= tpl_label("PAGER") ?></option>
                    <option value='_$!<Other>!$_' ><?= tpl_label('_$!<Other>!$_') ?></option>
                </select>
            </div>
        </td>
        <td class="person_right">
            <div class="person_text">
                <input type="text" name="phone_" value="" class="text" />
                <a href="#" onclick="add_phonelabel();return false;"><img src="<?= AB_TPL ?>images/plus.gif"></a>
                <a href="#" onclick="del_phonelabel(this);return false;"><img src="<?= AB_TPL ?>images/minus.gif"></a>
            </div>
        </td>
    </tr>
    
    <tr id="phonelabel_position"><td></td><td></td></tr>

    

<script type="text/javascript">
var phonelabel_counter = 0;

function add_phonelabel(label,content) {
    if(!label) label = '';
    if(!content) content = '';
    phonelabel_counter++;    

    var newBlock = document.getElementById('phonelabel_template1').cloneNode(true);
    newBlock.id = '';
    newBlock.style.display = 'table-row';
    var childNode = newBlock.getElementsByTagName("*");
    for (var i=0;i<childNode.length;i++) {
        var theName = childNode[i].name;
        if (theName) {
            if(theName == 'phone_') {
                childNode[i].value = content;
            }
            childNode[i].name = theName + phonelabel_counter;
        }
        if(childNode[i].tagName == 'OPTION') {
            if(childNode[i].value == label) {
                childNode[i].selected = true;
            } else {
                childNode[i].selected = false;
            }
        } 
    }
    var insertHere = document.getElementById('phonelabel_position');
    insertHere.parentNode.insertBefore(newBlock, insertHere);    
}
function del_phonelabel(object) {
    // careful! this code depends on the actual HTML code!    
    var block = object.parentNode.parentNode.parentNode;
    block.parentNode.removeChild(block);
}

<?php
foreach($contact->phones as $phone) {
    echo "add_phonelabel('".$phone['label']."', '".$phone['phone']."');\n";
}
?>

if(phonelabel_counter == 0) add_phonelabel();

</script>
