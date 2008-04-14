    <tr>
        <td class="person_left">
            <div class="person_labels"><?= $lang['label_relatednames'] ?></div>
        </td>
        <td></td>
    </tr>
    <?php
        $i = 1;
        foreach($contact->relatednames as $rname) {
            $s = 0; //selected
            ?>
            <tr>
                <td class="person_left">
                    <div class="person_labels">
                        <select name="relatednamelabel<?= $i?>" size="1" class="text" >
                            <option value='_$!<Father>!$_' <?php if($rname['label'] == '_$!<Father>!$_') { echo "selected"; $s=1;} ?> ><?= tpl_label('_$!<Father>!$_') ?></option>
                            <option value='_$!<Mother>!$_' <?php if($rname['label'] == '_$!<Mother>!$_') { echo "selected"; $s=1;} ?> ><?= tpl_label('_$!<Mother>!$_') ?></option>
                            <option value='_$!<Parent>!$_' <?php if($rname['label'] == '_$!<Parent>!$_') { echo "selected"; $s=1;} ?> ><?= tpl_label('_$!<Parent>!$_') ?></option>
                            <option value='_$!<Brother>!$_' <?php if($rname['label'] == '_$!<Brother>!$_') { echo "selected"; $s=1;} ?> ><?= tpl_label('_$!<Brother>!$_') ?></option>
                            <option value='_$!<Sister>!$_' <?php if($rname['label'] == '_$!<Sister>!$_') { echo "selected"; $s=1;} ?> ><?= tpl_label('_$!<Sister>!$_') ?></option>
                            <option value='_$!<Child>!$_' <?php if($rname['label'] == '_$!<Child>!$_') { echo "selected"; $s=1;} ?> ><?= tpl_label('_$!<Child>!$_') ?></option>
                            <option value='_$!<Friend>!$_' <?php if($rname['label'] == '_$!<Friend>!$_') { echo "selected"; $s=1;} ?> ><?= tpl_label('_$!<Friend>!$_') ?></option>
                            <option value='_$!<Spouse>!$_' <?php if($rname['label'] == '_$!<Spouse>!$_') { echo "selected"; $s=1;} ?> ><?= tpl_label('_$!<Spouse>!$_') ?></option>
                            <option value='_$!<Partner>!$_' <?php if($rname['label'] == '_$!<Partner>!$_') { echo "selected"; $s=1;} ?> ><?= tpl_label('_$!<Partner>!$_') ?></option>
                            <option value='_$!<Assistant>!$_' <?php if($rname['label'] == '_$!<Assistant>!$_') { echo "selected"; $s=1;} ?> ><?= tpl_label('_$!<Assistant>!$_') ?></option>
                            <option value='_$!<Manager>!$_' <?php if($rname['label'] == '_$!<Manager>!$_') { echo "selected"; $s=1;} ?> ><?= tpl_label('_$!<Manager>!$_') ?></option>
                            <option value='_$!<Other>!$_' <?php if($rname['label'] == '_$!<Other>!$_') { echo "selected"; $s=1;} ?> ><?= tpl_label('_$!<Other>!$_') ?></option>
                            
                            <?php if($s == 0) {
                                echo "<option value='" . $rname['label'] . "' selected>" . $rname['label'] . "</option>";
                                } else {
                                //echo "<option value='" . $phone['label'] . "' selected>" . $phone['label'] . "</option>";
                                }
                            ?>
                        </select>
                    </div>
                </td>
                <td class="person_right">
                    <div class="person_text">
                        <input type="text" name="relatedname<?= $i?>" value="<?= $rname['name'] ?>" class="text" />
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
                <select name="relatednamelabel<?= $i?>" size="1" class="text" >
                    <option value='_$!<Father>!$_' ><?= tpl_label('_$!<Father>!$_') ?></option>
                    <option value='_$!<Mother>!$_' ><?= tpl_label('_$!<Mother>!$_') ?></option>
                    <option value='_$!<Parent>!$_' ><?= tpl_label('_$!<Parent>!$_') ?></option>
                    <option value='_$!<Brother>!$_' ><?= tpl_label('_$!<Brother>!$_') ?></option>
                    <option value='_$!<Sister>!$_' ><?= tpl_label('_$!<Sister>!$_') ?></option>
                    <option value='_$!<Child>!$_' ><?= tpl_label('_$!<Child>!$_') ?></option>
                    <option value='_$!<Friend>!$_' selected ><?= tpl_label('_$!<Friend>!$_') ?></option>
                    <option value='_$!<Spouse>!$_' ><?= tpl_label('_$!<Spouse>!$_') ?></option>
                    <option value='_$!<Partner>!$_' ><?= tpl_label('_$!<Partner>!$_') ?></option>
                    <option value='_$!<Assistant>!$_' ><?= tpl_label('_$!<Assistant>!$_') ?></option>
                    <option value='_$!<Manager>!$_' ><?= tpl_label('_$!<Manager>!$_') ?></option>
                    <option value='_$!<Other>!$_' ><?= tpl_label('_$!<Other>!$_') ?></option>

                </select>
            </div>
        </td>
        <td class="person_right">
            <div class="person_text">
                <input type="text" name="relatedname<?= $i?>" value="" class="text" />
            </div>
        </td>
    </tr>
