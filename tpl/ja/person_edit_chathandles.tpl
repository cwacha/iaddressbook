    <tr>
        <td class="person_left">
            <div class="person_labels"><?= $lang['label_chathandles'] ?></div>
        </td>
        <td></td>
    </tr>
    <?php
        $i = 1;
        foreach($contact->chathandles as $chathandle) {
            $s = 0; //selected
            ?>
            <tr>
                <td class="person_left">
                    <div class="person_labels">
                        <select name="chathandlelabel<?= $i?>" size="1" class="text" >
                            <option value='HOME' <?php if($chathandle['label'] == 'HOME') { echo "selected"; $s=1;} ?> ><?= tpl_label('HOME') ?></option>
                            <option value='WORK' <?php if($chathandle['label'] == 'WORK') { echo "selected"; $s=1;} ?> ><?= tpl_label('WORK') ?></option>
                            <option value='_$!<Other>!$_' <?php if($chathandle['label'] == '_$!<Other>!$_') { echo "selected"; $s=1;} ?> ><?= tpl_label('_$!<Other>!$_') ?></option>
                            
                            <?php if($s == 0) {
                                echo "<option value='" . $chathandle['label'] . "' selected>" . $chathandle['label'] . "</option>";
                                } else {
                                //echo "<option value='" . $phone['label'] . "' selected>" . $phone['label'] . "</option>";
                                }
                            ?>
                        </select>
                    </div>
                </td>
                <td class="person_right">
                    <div class="person_text">
                        <input type="text" name="chathandle<?= $i?>" value="<?= $chathandle['handle'] ?>" class="text" />
                        <select name="chathandletype<?= $i?>" size="1" class="text" >
                            <option value='AIM' <?php if($chathandle['type'] == 'AIM') { echo "selected"; $s=1;} ?> ><?= tpl_label('AIM') ?></option>
                            <option value='ICQ' <?php if($chathandle['type'] == 'ICQ') { echo "selected"; $s=1;} ?> ><?= tpl_label('ICQ') ?></option>
                            <option value='MSN' <?php if($chathandle['type'] == 'MSN') { echo "selected"; $s=1;} ?> ><?= tpl_label('MSN') ?></option>
                            <option value='JABBER' <?php if($chathandle['type'] == 'JABBER') { echo "selected"; $s=1;} ?> ><?= tpl_label('JABBER') ?></option>
                            <option value='SKYPE' <?php if($chathandle['type'] == 'SKYPE') { echo "selected"; $s=1;} ?> ><?= tpl_label('SKYPE') ?></option>
                            <option value='YAHOO' <?php if($chathandle['type'] == 'YAHOO') { echo "selected"; $s=1;} ?> ><?= tpl_label('YAHOO') ?></option>
                        </select>
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
                <select name="chathandlelabel<?= $i ?>" size="1" class="text" >
                    <option value="HOME" selected ><?= tpl_label("HOME") ?></option>
                    <option value="WORK" ><?= tpl_label("WORK") ?></option>
                    <option value='_$!<Other>!$_' ><?= tpl_label('_$!<Other>!$_') ?></option>
                </select>
            </div>
        </td>
        <td class="person_right">
            <div class="person_text">
                <input type="text" name="chathandle<?= $i?>" value="" class="text" />
                <select name="chathandletype<?= $i?>" size="1" class="text" >
                    <option value='AIM' ><?= tpl_label('AIM') ?></option>
                    <option value='ICQ' ><?= tpl_label('ICQ') ?></option>
                    <option value='MSN' ><?= tpl_label('MSN') ?></option>
                    <option value='JABBER' selected ><?= tpl_label('JABBER') ?></option>
                    <option value='SKYPE' ><?= tpl_label('SKYPE') ?></option>
                    <option value='YAHOO' ><?= tpl_label('YAHOO') ?></option>
                </select>
            </div>
        </td>
    </tr>
    
    <?php $i++; ?>

    <tr>
        <td class="person_left">
            <div class="person_labels">
                <input type="text" name="chathandlelabel<?= $i?>" value="" class="text" />
            </div>
        </td>
        <td class="person_right">
            <div class="person_text">
                <input type="text" name="chathandle<?= $i?>" value="" class="text" />
                <select name="chathandletype<?= $i?>" size="1" class="text" >
                    <option value='AIM' ><?= tpl_label('AIM') ?></option>
                    <option value='ICQ' ><?= tpl_label('ICQ') ?></option>
                    <option value='MSN' ><?= tpl_label('MSN') ?></option>
                    <option value='JABBER' selected ><?= tpl_label('JABBER') ?></option>
                    <option value='SKYPE' ><?= tpl_label('SKYPE') ?></option>
                    <option value='YAHOO' ><?= tpl_label('YAHOO') ?></option>
                </select>
            </div>
        </td>
    </tr>
    