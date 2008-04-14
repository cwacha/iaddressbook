<tr>
    <td class="person_left">
        <div class="person_labels">
            <?= tpl_label($address['label']) ?><br>
            
            <div class="person_smalltext">
                <?php if(map_link($address)) echo "<a href='".map_link($address)."' target='_blank' >". $lang['map'] ."</a>"; ?>
            </div>
        </div>
    </td>
    <td class="person_right">
        <div class="person_text">
            <?= $address['street'] ?><br>
            <?= $address['zip'] ?> <?= $address['city'] ?><br>
            <?= $address['country'] ?><br>
            <?= $address['state'] ?>
            <div style="height: 1em;">&nbsp;</div>
        </div>
    </td>
</tr>