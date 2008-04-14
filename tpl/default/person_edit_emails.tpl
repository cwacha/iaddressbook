    <tr>
        <td class="person_left">
            <div class="person_labels"><?= $lang['label_emails'] ?></div>
        </td>
        <td></td>
    </tr>
    <?php
        $i = 1;
        foreach($contact->emails as $email) {
            $s = 0; //selected
            ?>
            <tr>
                <td class="person_left">
                    <div class="person_labels">
                        <select name="emaillabel<?= $i?>" size="1" class="text" >
                            <option value="HOME" <?php if($email['label'] == 'HOME') { echo "selected"; $s=1;} ?> ><?= tpl_label("HOME") ?></option>
                            <option value="WORK" <?php if($email['label'] == 'WORK') { echo "selected"; $s=1;} ?> ><?= tpl_label("WORK") ?></option>
                            <option value='_$!<Other>!$_' <?php if($email['label'] == '_$!<Other>!$_') { echo "selected"; $s=1;} ?> ><?= tpl_label('_$!<Other>!$_') ?></option>
                            
                            <?php if($s == 0) {
                                echo "<option value='" . $email['label'] . "' selected>" . $email['label'] . "</option>";
                                } else {
                                //echo "<option value='" . $phone['label'] . "' selected>" . $phone['label'] . "</option>";
                                }
                            ?>
                        </select>
                    </div>
                </td>
                <td class="person_right">
                    <div class="person_text">
                        <input type="text" name="email<?= $i?>" value="<?= $email['email'] ?>" class="text" />
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
                <select name="emaillabel<?= $i?>" size="1" class="text" >
                    <option value="HOME" selected ><?= tpl_label("HOME") ?></option>
                    <option value="WORK" ><?= tpl_label("WORK") ?></option>
                    <option value='_$!<Other>!$_' ><?= tpl_label('_$!<Other>!$_') ?></option>
                </select>
            </div>
        </td>
        <td class="person_right">
            <div class="person_text">
                <input type="text" name="email<?= $i?>" value="" class="text" />
            </div>
        </td>
    </tr>
