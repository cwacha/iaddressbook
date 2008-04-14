<div class="address">
	<br> Address Header <br>
	<?= $address['label'] ?><br>
	<?= $address['street'] ?><br>
	<?= $address['zip'] ?> <?= $address['city'] ?><br>
	<?= $address['country'] ?> <?php if($address['countrycode'] != "") echo "({$address['countrycode']})" ?><br>
	<?= $address['state'] ?><br>
    
	<br> Address Footer <br>
</div>