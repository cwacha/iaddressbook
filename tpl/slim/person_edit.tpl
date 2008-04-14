<div class="person">
    <form method="post" action="<?= $PHP_SELF ?>">
        <input type="text" name="title" value="<?= $contact->title ?>">
        <input type="text" name="firstname" value="<?= $contact->firstname ?>">
        <input type="text" name="firstname2" value="<?= $contact->firstname2 ?>">
        <input type="text" name="lastname" value="<?= $contact->lastname ?>">
        <input type="text" name="suffix" value="<?= $contact->suffix ?>"><br>
        <br>
        
        Nick: <input type="text" name="nickname" value="<?= $contact->nickname ?>"><br>
        Title: <input type="text" name="jobtitle" value="<?= $contact->jobtitle ?>"><br>
        Dept.:<input type="text" name="department" value="<?= $contact->department ?>"><br>
        Org.: <input type="text" name="organization" value="<?= $contact->organization ?>"><br>
        Company <input type="checkbox" name="company" <?php if(is_bool($contact->company) and $contact->company == true) echo "checked" ?> ><br>
        <br>
        
        Birthday (YYYY-MM-DD): <input type="text" name="birthdate" value="<?= $contact->birthdate ?>"><br>
        Note: <textarea name="note" rows="5" cols="50"><?= $contact->note ?></textarea><br>
        
        <br><strong>Addreses</strong><br>
        <?php
            $i = 1;
            foreach($contact->addresses as $address) {
                ?>
                <input type="text" name="addresslabel<?= $i?>" value="<?= $address['label'] ?>"><br>
                <input type="text" name="street<?= $i?>" value="<?= $address['street'] ?>"><br>
                <input type="text" name="zip<?= $i?>" value="<?= $address['zip'] ?>"><br>
                <input type="text" name="city<?= $i?>" value="<?= $address['city'] ?>"><br>
                <input type="text" name="state<?= $i?>" value="<?= $address['state'] ?>"><br>
                <input type="text" name="country<?= $i?>" value="<?= $address['country'] ?>"><br>
                <input type="text" name="template<?= $i?>" value="<?= $address['template'] ?>"><br>
                <br>
                <?php
                $i++;
            }
        ?>
        label: <input type="text" name="addresslabel<?= $i?>" value=""><br>
        street: <input type="text" name="street<?= $i?>" value=""><br>
        zip: <input type="text" name="zip<?= $i?>" value=""><br>
        city: <input type="text" name="city<?= $i?>" value=""><br>
        state: <input type="text" name="state<?= $i?>" value=""><br>
        country: <input type="text" name="country<?= $i?>" value=""><br>
        template: <input type="text" name="template<?= $i?>" value=""><br>
        <br>

        <br><strong>Phones</strong><br>
        <?php
            $i = 1;
            foreach($contact->phones as $phone) {
                ?>
                <input type="text" name="phonelabel<?= $i?>" value="<?= $phone['label'] ?>"><br>
                <input type="text" name="phone<?= $i?>" value="<?= $phone['phone'] ?>"><br>
                <br>
                <?php
                $i++;
            }
        ?>
        label: <input type="text" name="phonelabel<?= $i?>" value=""><br>
        <input type="text" name="phone<?= $i?>" value=""><br>
        <br>

        <br><strong>Emails</strong><br>
        <?php
            $i = 1;
            foreach($contact->emails as $email) {
                ?>
                <input type="text" name="emaillabel<?= $i?>" value="<?= $email['label'] ?>"><br>
                <input type="text" name="email<?= $i?>" value="<?= $email['email'] ?>"><br>
                <br>
                <?php
                $i++;
            }
        ?>
        label: <input type="text" name="emaillabel<?= $i?>" value=""><br>
        <input type="text" name="email<?= $i?>" value=""><br>
        <br>

        <br><strong>Chathandles</strong><br>
        <?php
            $i = 1;
            foreach($contact->chathandles as $chathandle) {
                ?>
                <input type="text" name="chathandlelabel<?= $i?>" value="<?= $chathandle['label'] ?>"><br>
                <input type="text" name="chathandletype<?= $i?>" value="<?= $chathandle['type'] ?>"><br>
                <input type="text" name="chathandle<?= $i?>" value="<?= $chathandle['handle'] ?>"><br>
                <br>
                <?php
                $i++;
            }
        ?>
        label: <input type="text" name="chathandlelabel<?= $i?>" value=""><br>
        type: <input type="text" name="chathandletype<?= $i?>" value=""><br>
        <input type="text" name="chathandle<?= $i?>" value=""><br>
        <br>

        <br><strong>URLs</strong><br>
        <?php
            $i = 1;
            foreach($contact->urls as $url) {
                ?>
                <input type="text" name="urllabel<?= $i?>" value="<?= $url['label'] ?>"><br>
                <input type="text" name="url<?= $i?>" value="<?= $url['url'] ?>"><br>
                <br>
                <?php
                $i++;
            }
        ?>
        <input type="text" name="urllabel<?= $i?>" value=""><br>
        <input type="text" name="url<?= $i?>" value=""><br>
        <br>

        <br><strong>Related Names</strong><br>
        <?php
            $i = 1;
            foreach($contact->relatednames as $rname) {
                ?>
                <input type="text" name="relatednamelabel<?= $i?>" value="<?= $rname['label'] ?>"><br>
                <input type="text" name="relatedname<?= $i?>" value="<?= $rname['name'] ?>"><br>
                <br>
                <?php
                $i++;
            }
        ?>
        <input type="text" name="relatednamelabel<?= $i?>" value=""><br>
        <input type="text" name="relatedname<?= $i?>" value=""><br>
        <br>

        <input type="hidden" name="id" value="<?= $contact->id ?>">
        <input type="hidden" name="do" value="save">
        <input type="submit" value="<?= $lang['btn_save']?>">
    </form>
    <form method="POST" action="<?= $PHP_SELF ?>">
        <input type="hidden" name="id" value="<?= $contact->id ?>" >
        <input type="hidden" name="do" value="show">
        <input type="submit" value="<?= $lang['btn_cancel']?>">
    </form>
    <br>
</div>
