<tr>
    <td class="person_left">
        <div class="person_labels">
            <?php echo tpl_label($address['label']); ?><br>
            
            <div class="person_smalltext">
                <?php if(map_link($address)) echo "<a href='".map_link($address)."' target='_blank' >". $lang['map'] ."</a>"; ?>
            </div>
        </div>
    </td>
    <td class="person_right">
        <div class="person_text">
            <?php echo $address['street']; ?><br>
            <?php echo $address['zip']; ?> <?php echo $address['city']; ?><br>
            <?php echo $address['country']; ?><br>
            <?php echo $address['state']; ?>
            <div style="height: 1em;">&nbsp;</div>
        </div>
    </td>
</tr>
