<div class="row">
    <div class="col person_left">
        <div class="person_labels">
            <?php echo tpl_label($address['label']); ?><br>
            
            <small>
                <?php if(map_link($address)) echo "<a href='".map_link($address)."' target='_blank' >". $lang['map'] ."</a>"; ?>
            </small>
        </div>
    </div>
    <div class="col person_right">
        <div class="person_text">
            <?php echo $address['street']; ?><br>
            <?php echo $address['zip']; ?> <?php echo $address['city']; ?><br>
            <?php echo $address['country']; ?><br>
            <?php echo $address['state']; ?>
            <div class="pb-2"></div>
        </div>
    </div>
</div>
