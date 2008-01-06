    <tr>
        <td class="person_left">
            <div class="person_labels"><?= $lang['label_emails'] ?></div>
        </td>
        <td></td>
    </tr>
    
    <tr id="emaillabel_template1" style="display: none;">
        <td class="person_left">
            <div class="person_labels">
                <select name="emaillabel_" size="1" class="text" >
                    <option value="HOME" selected ><?= tpl_label("HOME") ?></option>
                    <option value="WORK" ><?= tpl_label("WORK") ?></option>
                    <option value='_$!<Other>!$_' ><?= tpl_label('_$!<Other>!$_') ?></option>
                </select>
            </div>
        </td>
        <td class="person_right">
            <div class="person_text">
                <input type="text" name="email_" value="" class="text" />
                <a href="#" onclick="add_emaillabel();return false;"><img src="<?= AB_TPL ?>images/plus.gif"></a>
                <a href="#" onclick="del_emaillabel(this);return false;"><img src="<?= AB_TPL ?>images/minus.gif"></a>
            </div>
        </td>
    </tr>

    <tr id="emaillabel_position"><td></td><td></td></tr>
    

<script type="text/javascript">
var emaillabel_counter = 0;

function add_emaillabel(label, content) {
    if(!label) label = '';
    if(!content) content = '';
    emaillabel_counter++;

    var newBlock = document.getElementById('emaillabel_template1').cloneNode(true);
    newBlock.id = '';
    newBlock.style.display = 'table-row';
    var childNode = newBlock.getElementsByTagName("*");
    for (var i=0;i<childNode.length;i++) {
        var theName = childNode[i].name;
        if (theName) {
            if(theName == 'email_') {
                childNode[i].value = content;
            }
            childNode[i].name = theName + emaillabel_counter;
        }
        if(childNode[i].tagName == 'OPTION') {
            if(childNode[i].value == label) {
                childNode[i].selected = true;
            } else {
                childNode[i].selected = false;
            }
        }
    }
    var insertHere = document.getElementById('emaillabel_position');
    insertHere.parentNode.insertBefore(newBlock, insertHere);    
}
function del_emaillabel(object) {
    // careful! this code depends on the actual HTML code!    
    var block = object.parentNode.parentNode.parentNode;
    block.parentNode.removeChild(block);
}

<?php
foreach($contact->emails as $email) {
    echo "add_emaillabel('".$email['label']."', '".$email['email']."');\n";
}
?>

if(emaillabel_counter == 0) add_emaillabel();

</script>
