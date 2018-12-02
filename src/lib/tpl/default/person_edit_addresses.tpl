    <div class="row">
        <div class="col person_left">
            <div class="person_labels"><?php echo $lang['label_addresses']; ?></div>
        </div>
        <div class="col"></div>
        <div class="col-1"></div>
    </div>

    <div class="row" id="addresslabel_template1" style="display: none;">
        <div class="col person_left">
            <div class="form-group">
                <select name="addresslabel_" size="1" class="form-control" onchange="custom_addresslabel(this);">
                    <option value='HOME' selected ><?php echo tpl_label('HOME'); ?></option>
                    <option value='WORK' ><?php echo tpl_label('WORK'); ?></option>
                    <option value='_$!<Other>!$_' ><?php echo tpl_label('_$!<Other>!$_'); ?></option>
                    <option disabled>-------</option>
                    <option value='CUSTOM' ><?php echo tpl_label("CUSTOM"); ?></option>
                </select>
            </div>
        </div>
        <div class="col pb-4">
                <input type="hidden" name="template_" value='' class="text" />
            <div class="form-group" >
                <input type="text" name="street_" placeholder="<?php echo $lang['label_street']; ?>" value='' class="form-control form-control-sm" />
            </div>
            <div class="form-group" >
                <input type="text" name="zip_" placeholder="<?php echo $lang['label_zip']; ?>" value='' class="form-control form-control-sm" size="10" />
            </div>
            <div class="form-group" >
                <input type="text" name="city_" placeholder="<?php echo $lang['label_city']; ?>" value='' class="form-control form-control-sm" />
            </div>
            <div class="form-group" >
                <input type="text" name="state_" placeholder="<?php echo $lang['label_state']; ?>" value='' class="form-control form-control-sm" />
            </div>
            <div class="form-group" >
                <input type="text" name="country_" placeholder="<?php echo $lang['label_country']; ?>" value='' class="form-control form-control-sm" />
            </div>
        </div>
        <div class="col-1 pl-0">
            <div class="btn-group" role="group">
                <a href="#" onclick="add_addresslabel('HOME');return false;" class="btn btn-success btn-sm">+</a>
                <a href="#" onclick="del_addresslabel(this);return false;" class="btn btn-danger btn-sm">-</a>
            </div>            
        </div>
    </div>

    <div class="row" id="addresslabel_position">
        <div class="col"></div>
        <div class="col"></div>
    </div>

    <div class="row pb-4" ></div>
    
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
    
