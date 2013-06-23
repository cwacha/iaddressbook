    <tr>
        <td class="person_left">
            <div class="person_labels"><?php echo $lang['label_addresses']; ?></div>
        </td>
        <td></td>
    </tr>

    <tr id="addresslabel_template1" style="display: none;">
        <td class="person_left">
            <div class="person_labels" style="height: 20px;">
                <select name="addresslabel_" size="1" class="text" onchange="custom_addresslabel(this);">
                    <option value='HOME' selected ><?php echo tpl_label('HOME'); ?></option>
                    <option value='WORK' ><?php echo tpl_label('WORK'); ?></option>
                    <option value='_$!<Other>!$_' ><?php echo tpl_label('_$!<Other>!$_'); ?></option>
                    <option disabled>-------</option>
                    <option value='CUSTOM' ><?php echo tpl_label("CUSTOM"); ?></option>
                </select>
            </div>
            <div class="person_labels" style="height: 20px;"><?php echo $lang['label_street']; ?></div>
            <div class="person_labels" style="height: 20px;"><?php echo $lang['label_zip']; ?></div>
            <div class="person_labels" style="height: 20px;"><?php echo $lang['label_city']; ?></div>
            <div class="person_labels" style="height: 20px;"><?php echo $lang['label_state']; ?></div>
            <div class="person_labels" style="height: 20px;"><?php echo $lang['label_country']; ?></div>
        </td>
        <td class="person_right">
            <div class="person_text" style="height: 20px;">
                <input type="hidden" name="template_" value='' class="text" />
                &nbsp;
            </div>
            <div class="person_text" style="height: 20px;">
                <input type="text" name="street_" value='' class="text" />
                <a href="#" onclick="add_addresslabel('HOME');return false;"><img src="<?php echo AB_TPL; ?>images/plus.gif"></a>
                <a href="#" onclick="del_addresslabel(this);return false;"><img src="<?php echo AB_TPL; ?>images/minus.gif"></a>
            </div>
            <div class="person_text" style="height: 20px;">
                <input type="text" name="zip_" value='' class="text" size="10" />
            </div>
            <div class="person_text" style="height: 20px;">
                <input type="text" name="city_" value='' class="text" />
            </div>
            <div class="person_text" style="height: 20px;">
                <input type="text" name="state_" value='' class="text" />
            </div>
            <div class="person_text" style="height: 20px;">
                <input type="text" name="country_" value='' class="text" />
            </div>
        </td>
    </tr>

    <tr id="addresslabel_position"><td></td><td></td></tr>
    
    <tr><td style="height: 1em;"></td><td></td></tr>
    
<script type="text/javascript">
var addresslabel_counter = 0;

function add_addresslabel(label, street, zip, city, state, country, template) {
    if(!label) label = '';
    if(!street) street = '';
    if(!zip) zip = '';
    if(!city) city = '';
    if(!state) state = '';
    if(!country) country = '';
    if(!template) template = '';
    addresslabel_counter++;
    var custom_label = 1;

    var newBlock = document.getElementById('addresslabel_template1').cloneNode(true);
    newBlock.id = '';
    newBlock.style.display = '';
    var childNode = newBlock.getElementsByTagName("*");
    for (var i=0;i<childNode.length;i++) {
        var theName = childNode[i].name;
        if (theName) {
            if(theName == 'street_') childNode[i].value = street;
            if(theName == 'zip_') childNode[i].value = zip;
            if(theName == 'city_') childNode[i].value = city;
            if(theName == 'state_') childNode[i].value = state;
            if(theName == 'country_') childNode[i].value = country;
            if(theName == 'template_') childNode[i].value = template;
            childNode[i].name = theName + addresslabel_counter;
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

    var insertHere = document.getElementById('addresslabel_position');
    insertHere.parentNode.insertBefore(newBlock, insertHere);    
}
function del_addresslabel(object) {
    // careful! this code depends on the actual HTML code!    
    var block = object.parentNode.parentNode.parentNode;
    block.parentNode.removeChild(block);
}
function custom_addresslabel(object) {
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
foreach($contact->addresses as $address) {
    echo "add_addresslabel('" . $address['label'] . "','"
                              . $address['street'] . "','"
                              . $address['zip'] . "','"
                              . $address['city'] . "','"
                              . $address['state'] . "','"
                              . $address['country'] . "','"
                              . $address['template'] . "');\n";
}
?>

if(addresslabel_counter == 0) add_addresslabel('HOME');

</script>
    
