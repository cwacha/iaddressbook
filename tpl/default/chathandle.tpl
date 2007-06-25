<tr>
    <td class="person_left">
        <div class="person_labels">
            <?= tpl_label($chathandle['label']) ?>
        </div>
    </td>
    <td class="person_right">
        <div class="person_text">
            <?php if($chathandle['type'] == 'SKYPE') { ?>
                    <a href='callto://<?= $chathandle['handle'] ?>' ><?= $chathandle['handle'] ?></a> (<?= tpl_label($chathandle['type']) ?>)
            <?php } else { ?>
                    <?= $chathandle['handle'] ?> (<?= tpl_label($chathandle['type']) ?>)
            <?php } ?>
        </div>
    </td>
</tr>