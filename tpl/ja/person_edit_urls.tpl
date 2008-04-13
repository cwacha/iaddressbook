    <tr>
        <td class="person_left">
            <div class="person_labels"><?= $lang['label_urls'] ?></div>
        </td>
        <td></td>
    </tr>
    <?php
        $i = 1;
        foreach($contact->urls as $url) {
            $s = 0; //selected
            ?>
            <tr>
                <td class="person_left">
                    <div class="person_labels">
                        <select name="urllabel<?= $i?>" size="1" class="text" >
                            <option value='_$!<HomePage>!$_' <?php if($url['label'] == '_$!<HomePage>!$_') { echo "selected"; $s=1;} ?> ><?= tpl_label('_$!<HomePage>!$_') ?></option>
                            <option value='HOME' <?php if($url['label'] == 'HOME') { echo "selected"; $s=1;} ?> ><?= tpl_label("HOME") ?></option>
                            <option value='WORK' <?php if($url['label'] == 'WORK') { echo "selected"; $s=1;} ?> ><?= tpl_label("WORK") ?></option>
                            <option value='_$!<Other>!$_' <?php if($url['label'] == '_$!<Other>!$_') { echo "selected"; $s=1;} ?> ><?= tpl_label('_$!<Other>!$_') ?></option>
                            
                            <?php if($s == 0) {
                                echo "<option value='" . $url['label'] . "' selected>" . $url['label'] . "</option>";
                                } else {
                                //echo "<option value='" . $phone['label'] . "' selected>" . $phone['label'] . "</option>";
                                }
                            ?>
                        </select>
                    </div>
                </td>
                <td class="person_right">
                    <div class="person_text">
                        <input type="text" name="url<?= $i?>" value="<?= $url['url'] ?>" class="text" />
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
                <select name="urllabel<?= $i?>" size="1" class="text" >
                    <option value='_$!<HomePage>!$_' selected ><?= tpl_label('_$!<HomePage>!$_') ?></option>
                    <option value='HOME' selected ><?= tpl_label('HOME') ?></option>
                    <option value='WORK' ><?= tpl_label('WORK') ?></option>
                    <option value='_$!<Other>!$_' ><?= tpl_label('_$!<Other>!$_') ?></option>
                </select>
            </div>
        </td>
        <td class="person_right">
            <div class="person_text">
                <input type="text" name="url<?= $i?>" value="" class="text" />
            </div>
        </td>
    </tr>

    <?php $i++; ?>
    
    <tr>
        <td class="person_left">
            <div class="person_labels">
                <input type="text" name="urllabel<?= $i?>" value="" class="text" />
            </div>
        </td>
        <td class="person_right">
            <div class="person_text">
                <input type="text" name="url<?= $i?>" value="" class="text" />
            </div>
        </td>
    </tr>
