    <tr>
        <td class="person_left">
            <div class="person_labels"><?= $lang['label_chathandles'] ?></div>
        </td>
        <td></td>
    </tr>

    <tr id="chatlabel_template1" style="display: none;">
        <td class="person_left">
            <div class="person_labels">
                <select name="chathandlelabel_" size="1" class="text" >
                    <option value="HOME" ><?= tpl_label("HOME") ?></option>
                    <option value="WORK" ><?= tpl_label("WORK") ?></option>
                    <option value='_$!<Other>!$_' ><?= tpl_label('_$!<Other>!$_') ?></option>
                </select>
            </div>
        </td>
        <td class="person_right">
            <div class="person_text">
                <input type="text" name="chathandle_" value="" class="text" />
                <select name="chathandletype_" size="1" class="text" >
                    <option value='AIM' ><?= tpl_label('AIM') ?></option>
                    <option value='ICQ' ><?= tpl_label('ICQ') ?></option>
                    <option value='MSN' ><?= tpl_label('MSN') ?></option>
                    <option value='JABBER' ><?= tpl_label('JABBER') ?></option>
                    <option value='SKYPE' ><?= tpl_label('SKYPE') ?></option>
                    <option value='YAHOO' ><?= tpl_label('YAHOO') ?></option>
                </select>
                <a href="javascript:add_chatlabel()"><img src="<?= AB_TPL ?>images/plus.gif"></a>
                <a href="javascript:true" onclick="del_chatlabel(this);"><img src="<?= AB_TPL ?>images/minus.gif"></a>
            </div>
        </td>
    </tr>
    
    <tr id="chatlabel_position"><td></td><td></td></tr>
    

<script type="text/javascript">
var chatlabel_counter = 0;

function add_chatlabel(label,type,handle) {
    if(!label) label = '';
    if(!type) type = '';
    if(!handle) handle = '';
    chatlabel_counter++;    

    var newBlock = document.getElementById('chatlabel_template1').cloneNode(true);
    newBlock.id = '';
    newBlock.style.display = 'table-row';
    var childNode = newBlock.getElementsByTagName("*");
    for (var i=0;i<childNode.length;i++) {
        var theName = childNode[i].name;
        if (theName) {
            if(theName == 'chathandle_') {
                childNode[i].value = handle;
            }
            if(theName == 'chathandletype_') {
                var slist = 'type';
            }
            if(theName == 'chathandlelabel_') {
                var slist = 'label';
            }
            childNode[i].name = theName + chatlabel_counter;
        }
        if(childNode[i].tagName == 'OPTION' && slist == 'label' && childNode[i].value == label) {
            childNode[i].setAttribute("selected", 1);
        }
        if(childNode[i].tagName == 'OPTION' && slist == 'type' && childNode[i].value == type) {
            childNode[i].setAttribute("selected", 1);
        }
    }
    var insertHere = document.getElementById('chatlabel_position');
    insertHere.parentNode.insertBefore(newBlock, insertHere);    
}
function del_chatlabel(object) {
    // careful! this code depends on the actual HTML code!    
    var block = object.parentNode.parentNode.parentNode;
    block.parentNode.removeChild(block);
}

<?php
foreach($contact->chathandles as $chathandle) {
    echo "add_chatlabel('".$chathandle['label']."', '".$chathandle['type']."', '".$chathandle['handle']."');\n";
}
?>

if(chatlabel_counter == 0) add_chatlabel();

</script>
