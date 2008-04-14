    <tr>
        <td class="person_left">
            <div class="person_labels"><?= $lang['label_phones'] ?></div>
        </td>
        <td></td>
    </tr>
    <?php
        $i = 1;
        foreach($contact->phones as $phone) {
            $s = 0; //selected
            ?>
            <tr>
                <td class="person_left">
                    <div class="person_labels">
                        <select name="phonelabel<?= $i?>" size="1" class="text" >
                            <option value="HOME" <?php if($phone['label'] == 'HOME') { echo "selected"; $s=1;} ?> ><?= tpl_label("HOME") ?></option>
                            <option value="CELL" <?php if($phone['label'] == 'CELL') { echo "selected"; $s=1;} ?> ><?= tpl_label("CELL") ?></option>
                            <option value="WORK" <?php if($phone['label'] == 'WORK') { echo "selected"; $s=1;} ?> ><?= tpl_label("WORK") ?></option>
                            <option value="MAIN" <?php if($phone['label'] == 'MAIN') { echo "selected"; $s=1;} ?> ><?= tpl_label("MAIN") ?></option>
                            <option value="HOME FAX" <?php if($phone['label'] == 'HOME FAX') { echo "selected"; $s=1;} ?> ><?= tpl_label("HOME FAX") ?></option>
                            <option value="WORK FAX" <?php if($phone['label'] == 'WORK FAX') { echo "selected"; $s=1;} ?> ><?= tpl_label("WORK FAX") ?></option>
                            <option value="PAGER" <?php if($phone['label'] == 'PAGER') { echo "selected"; $s=1;} ?> ><?= tpl_label("PAGER") ?></option>
                            <option value='_$!<Other>!$_' <?php if($phone['label'] == '_$!<Other>!$_') { echo "selected"; $s=1;} ?> ><?= tpl_label('_$!<Other>!$_') ?></option>
                            
                            <?php if($s == 0) {
                                echo "<option value='" . $phone['label'] . "' selected>" . $phone['label'] . "</option>";
                                } else {
                                //echo "<option value='" . $phone['label'] . "' selected>" . $phone['label'] . "</option>";
                                }
                            ?>
                        </select>
                    </div>
                </td>
                <td class="person_right">
                    <div class="person_text">
                        <input type="text" name="phone<?= $i?>" value="<?= $phone['phone'] ?>" class="text" />
                    </div>
                </td>
            </tr>
            <?php
            $i++;
        }
    ?>
    <tr>
        <td class="person_left">
            <div class="person_labels">
                <select name="phonelabel<?= $i?>" size="1" class="text" >
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
                <input type="text" name="phone<?= $i?>" value="" class="text" />
            </div>
        </td>
    </tr>
