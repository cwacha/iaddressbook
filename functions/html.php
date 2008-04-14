<?php


function html_contactlist_item($contact, $color) {
?>
    <div class="contactlist_item<?= $color ?>">
        <a href='?id=<?= $contact->id ?>'><?= $contact->lastname ?>, <?= $contact->firstname ?> </a><br>
    </div>
<?php
}

function html_debug() {
    global $ACT;
    global $QUERY;
    global $ID;
    global $AB;
    global $contactlist;

    echo "Action: $ACT<br>\n";
    echo "ID: $ID<br>\n";
    echo "Query: $QUERY<br>\n";
    echo "AB: $AB<br>\n";
    echo "contactlist: $contactlist<br>\n";
    
    //print_r($_REQUEST);
}

?>