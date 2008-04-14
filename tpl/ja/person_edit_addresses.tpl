    <tr>
        <td class="person_left">
            <div class="person_labels"><?= $lang['label_addresses'] ?></div>
        </td>
        <td></td>
    </tr>
    <?php
        $i = 1;
        foreach($contact->addresses as $address) {
            $s = 0; //selected
            ?>
            <tr>
                <td class="person_left">
                    <div class="person_labels">
                        <select name="addresslabel<?= $i?>" size="1" class="text" >
                            <option value='HOME' <?php if($address['label'] == 'HOME') { echo "selected"; $s=1;} ?> ><?= tpl_label('HOME') ?></option>
                            <option value='WORK' <?php if($address['label'] == 'WORK') { echo "selected"; $s=1;} ?> ><?= tpl_label('WORK') ?></option>
                            <option value='_$!<Other>!$_' <?php if($address['label'] == '_$!<Other>!$_') { echo "selected"; $s=1;} ?> ><?= tpl_label('_$!<Other>!$_') ?></option>
                            
                            <?php if($s == 0) {
                                echo "<option value='" . $address['label'] . "' selected>" . $address['label'] . "</option>";
                                } else {
                                //echo "<option value='" . $phone['label'] . "' selected>" . $phone['label'] . "</option>";
                                }
                            ?>
                        </select>
                    </div>
                </td>
                <td>
                <input type="hidden" name="template<?= $i?>" value="<?= $address['template'] ?>" class="text" />
                </td>
            </tr>
            <tr>
                <td class="person_left">
                    <div class="person_labels"><?= $lang['label_zip'] ?></div>
                </td>
                <td class="person_right">
                    <div class="person_text">
                        <input type="text" name="zip<?= $i?>" value="<?= $address['zip'] ?>" class="text" size="4" />
                    </div>
                </td>
            </tr>
            <tr>
                <td class="person_left">
                    <div class="person_labels"><?= $lang['label_country'] ?></div>
                </td>
                <td class="person_right">
                    <div class="person_text">
                        <input type="text" name="country<?= $i?>" value="<?= $address['country'] ?>" class="text" />
                    </div>
                </td>
            </tr>
            <tr>
                <td class="person_left">
                    <div class="person_labels"><?= $lang['label_state'] ?></div>
                </td>
                <td class="person_right">
                    <div class="person_text">
                        <input type="text" name="state<?= $i?>" value="<?= $address['state'] ?>" class="text" />
                    </div>
                </td>
            </tr>
            <tr>
                <td class="person_left">
                    <div class="person_labels"><?= $lang['label_city'] ?></div>
                </td>
                <td class="person_right">
                    <div class="person_text">
                        <input type="text" name="city<?= $i?>" value="<?= $address['city'] ?>" class="text" />
                    </div>
                </td>
            </tr>
            <tr>
                <td class="person_left">
                    <div class="person_labels"><?= $lang['label_street'] ?></div>
                </td>
                <td class="person_right">
                    <div class="person_text">
                        <input type="text" name="street<?= $i?>" value="<?= $address['street'] ?>" class="text" />
                    </div>
                </td>
            </tr>
            <?php
            $i++;
        }
    ?>
    
    <tr><td style="height: 1em;"></td><td></td></tr>
    
    <tr>
        <td class="person_left">
            <div class="person_labels">
                <select name="addresslabel<?= $i?>" size="1" class="text" >
                    <option value='HOME' selected><?= tpl_label('HOME') ?></option>
                    <option value='WORK' ><?= tpl_label('WORK') ?></option>
                    <option value='_$!<Other>!$_' ><?= tpl_label('_$!<Other>!$_') ?></option>
                </select>
            </div>
        </td>
        <td>
        <input type="hidden" name="template<?= $i?>" value='' class="text" />
        </td>
    </tr>
    <tr>
        <td class="person_left">
            <div class="person_labels"><?= $lang['label_zip'] ?></div>
        </td>
        <td class="person_right">
            <div class="person_text">
                <input type="text" name="zip<?= $i?>" value='' class="text" size="4" />
            </div>
        </td>
    </tr>
    <tr>
        <td class="person_left">
            <div class="person_labels"><?= $lang['label_country'] ?></div>
        </td>
        <td class="person_right">
            <div class="person_text">
                <input type="text" name="country<?= $i?>" value='' class="text" />
            </div>
        </td>
    </tr>
    <tr>
        <td class="person_left">
            <div class="person_labels"><?= $lang['label_state'] ?></div>
        </td>
        <td class="person_right">
            <div class="person_text">
                <input type="text" name="state<?= $i?>" value='' class="text" />
            </div>
        </td>
    </tr>
    <tr>
        <td class="person_left">
            <div class="person_labels"><?= $lang['label_city'] ?></div>
        </td>
        <td class="person_right">
            <div class="person_text">
                <input type="text" name="city<?= $i?>" value='' class="text" />
            </div>
        </td>
    </tr>
    <tr>
        <td class="person_left">
            <div class="person_labels"><?= $lang['label_street'] ?></div>
        </td>
        <td class="person_right">
            <div class="person_text">
                <input type="text" name="street<?= $i?>" value='' class="text" />
            </div>
        </td>
    </tr>