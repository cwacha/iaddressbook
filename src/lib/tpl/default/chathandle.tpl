<tr>
    <td class="person_left">
        <div class="person_labels">
            <?php echo tpl_label($chathandle['label']); ?>
        </div>
    </td>
    <td class="person_right">
        <div class="person_text">
            <?php if($chathandle['type'] == 'SKYPE') {; ?>
                    <a href='callto://<?php echo $chathandle['handle']; ?>' ><?php echo $chathandle['handle']; ?></a> (<?php echo tpl_label($chathandle['type']); ?>)
            <?php } else {; ?>
                    <?php echo $chathandle['handle']; ?> (<?php echo tpl_label($chathandle['type']); ?>)
            <?php }; ?>
        </div>
    </td>
</tr>
