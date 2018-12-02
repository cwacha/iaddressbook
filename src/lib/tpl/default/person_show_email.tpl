<div class="row">
    <div class="col person_left">
        <div class="person_labels">
            <?php echo tpl_label($email['label']); ?>
        </div>
    </div>
    <div class="col person_right">
        <div class="person_text">
            <a href='mailto:<?php echo $email['email']; ?>' ><?php echo $email['email']; ?></a>
        </div>
    </div>
</div>