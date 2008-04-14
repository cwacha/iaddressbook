<tr>
    <td class="person_left">
        <div class="person_labels">
            <?= tpl_label($address['label']) ?><br>
            
            <div class="person_smalltext">
                <a href='http://map.search.ch/<?= $address['city'] ?>/<?= $address['street'] ?>' >Karte</a>
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