<div class="address">
	<?= tpl_label($address['label']) ?>:<br>
	<?= $address['street'] ?><br>
	<?= $address['zip'] ?> <?= $address['city'] ?><br>
	<?= $address['country'] ?> <?php if($address['template'] != "") echo "({$address['template']})" ?><br>
	<?= $address['state'] ?><br>
    <a href='http://map.search.ch/<?= $address['city'] ?>/<?= $address['street'] ?>' >Karte</a>
    <br>
    <br>
</div>