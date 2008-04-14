    <tr>
        <td class="person_left">
            <div class="person_labels"><?= $lang['label_urls'] ?></div>
        </td>
        <td></td>
    </tr>

    <tr id="urllabel_template1" style="display: none;">
        <td class="person_left">
            <div class="person_labels">
                <select name="urllabel_" size="1" class="text" onchange="custom_urllabel(this);">
                    <option value='_$!<HomePage>!$_' selected ><?= tpl_label('_$!<HomePage>!$_') ?></option>
                    <option value='HOME' selected ><?= tpl_label('HOME') ?></option>
                    <option value='WORK' ><?= tpl_label('WORK') ?></option>
                    <option value='_$!<Other>!$_' ><?= tpl_label('_$!<Other>!$_') ?></option>
                    <option disabled>-------</option>
                    <option value='CUSTOM' ><?= tpl_label("CUSTOM") ?></option>
                </select>
            </div>
        </td>
        <td class="person_right">
            <div class="person_text">
                <input type="text" name="url_" value="" class="text" />
                <a href="#" onclick="add_urllabel('HOME');return false;"><img src="<?= AB_TPL ?>images/plus.gif"></a>
                <a href="#" onclick="del_urllabel(this);return false;"><img src="<?= AB_TPL ?>images/minus.gif"></a>
            </div>
        </td>
    </tr>

    <tr id="urllabel_position"><td></td><td></td></tr>


<script type="text/javascript">
var urllabel_counter = 0;

function add_urllabel(label,content) {
    if(!label) label = '';
    if(!content) content = '';
    urllabel_counter++;
    var custom_label = 1;

    var newBlock = document.getElementById('urllabel_template1').cloneNode(true);
    newBlock.id = '';
    newBlock.style.display = '';
    var childNode = newBlock.getElementsByTagName("*");
    for (var i=0;i<childNode.length;i++) {
        var theName = childNode[i].name;
        if (theName) {
            if(theName == 'url_') {
                childNode[i].value = content;
            }
            childNode[i].name = theName + urllabel_counter;
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
        var newOption = document.createElement("option");
        newOption.value = label;
        newOption.text = label;
        var object = newBlock.getElementsByTagName("select")[0];
        object.appendChild(newOption);
        object.selectedIndex = object.length - 1;
    }
    
    var insertHere = document.getElementById('urllabel_position');
    insertHere.parentNode.insertBefore(newBlock, insertHere);    
}
function del_urllabel(object) {
    // careful! this code depends on the actual HTML code!    
    var block = object.parentNode.parentNode.parentNode;
    block.parentNode.removeChild(block);
}
function custom_urllabel(object) {
    if(object.options[object.selectedIndex].value == 'CUSTOM') {
        // get custom label
        var label = prompt("<?= $lang['label_customprompt'] ?>", "");
        
        // add custom label to options
        if(label) {
            var newOption = document.createElement("option");
            newOption.value = label;
            newOption.text = label;
            object.appendChild(newOption);
            object.selectedIndex = object.length - 1;
        } else {
            object.selectedIndex = 0;
        }
    }
}

<?php
foreach($contact->urls as $url) {
    echo "add_urllabel('".$url['label']."', '".$url['url']."');\n";
}
?>

if(urllabel_counter == 0) add_urllabel('HOME');

</script>

