    <div class="row">
        <div class="col person_left">
            <div class="person_labels"><?php echo lang('label_phones'); ?></div>
        </div>
        <div class="col"></div>
        <div class="col-1"></div>
    </div>

    <div class="row" id="phonelabel_template1" style="display: none;">
        <div class="col person_left">
            <div class="form-group">
                <select name="phonelabel_" size="1" class="form-control" onchange="custom_phonelabel(this);">
                    <option value="HOME" selected ><?php echo tpl_label("HOME"); ?></option>
                    <option value="CELL" ><?php echo tpl_label("CELL"); ?></option>
                    <option value="WORK" ><?php echo tpl_label("WORK"); ?></option>
                    <option value="MAIN" ><?php echo tpl_label("MAIN"); ?></option>
                    <option value="HOME FAX" ><?php echo tpl_label("HOME FAX"); ?></option>
                    <option value="WORK FAX" ><?php echo tpl_label("WORK FAX"); ?></option>
                    <option value="PAGER" ><?php echo tpl_label("PAGER"); ?></option>
                    <option value='_$!<Other>!$_' ><?php echo tpl_label('_$!<Other>!$_'); ?></option>
                    <option disabled>-------</option>
                    <option value='CUSTOM' ><?php echo tpl_label("CUSTOM"); ?></option>
                </select>
            </div>
        </div>
        <div class="col person_right">
            <div class="form-group">
                <input type="text" name="phone_" value="" class="form-control form-control-sm" />
            </div>
        </div>
        <div class="col-1 pl-0">
            <div class="btn-group" role="group">
                <a href="#" onclick="add_phonelabel('HOME');return false;" class="btn btn-success btn-sm">+</a>
                <a href="#" onclick="del_phonelabel(this);return false;" class="btn btn-danger btn-sm">-</a>
            </div>
        </div>
    </div>
    
    <div class="row" id="phonelabel_position"></div>

    

<script type="text/javascript">
var phonelabel_counter = 0;

function add_phonelabel(label,content) {
    if(!label) label = '';
    if(!content) content = '';
    phonelabel_counter++;
    var custom_label = 1;

    var newBlock = document.getElementById('phonelabel_template1').cloneNode(true);
    newBlock.id = '';
    newBlock.style.display = '';
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
    
    var insertHere = document.getElementById('phonelabel_position');
    insertHere.parentNode.insertBefore(newBlock, insertHere);    
}
function del_phonelabel(object) {
    // careful! this code depends on the actual HTML code!    
    var block = object.parentNode.parentNode.parentNode;
    block.parentNode.removeChild(block);
}
function custom_phonelabel(object) {
    if(object.options[object.selectedIndex].value == 'CUSTOM') {
        // get custom label
        var label = prompt("<?php echo lang('label_customprompt'); ?>", "");
        
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
foreach($contact->phones as $phone) {
    echo "add_phonelabel('".$phone['label']."', '".$phone['phone']."');\n";
}
?>

if(phonelabel_counter == 0) add_phonelabel('HOME');

</script>
