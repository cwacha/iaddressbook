<?php
	$accountid = $_SESSION['accountid'];
	$account = $_SESSION['account'];
?>
<div class="row">
    <div class="col pt-4">
        <h2>My Account</h2>
        <div class="col-4">
        	<div class="row">
        		<div class="col"><strong>Account ID</strong></div>
        		<div class="col"><?php echo $accountid ?></div>
    		</div>
        	<div class="row">
        		<div class="col"><strong>Fullname</strong></div>
        		<div class="col"><?php echo $account['fullname'] ?></div>
    		</div>
        	<div class="row">
        		<div class="col"><strong>E-Mail</strong></div>
        		<div class="col"><?php echo $account['email'] ?></div>
    		</div>
        	<div class="row pb-4">
        		<div class="col"><strong>Roles</strong></div>
        		<div class="col"><?php echo join(', ', $account['roles']) ?></div>
    		</div>
        </div>
    </div>
</div>
<div class="row">
	<div class="col">
		<a role="button" class="btn btn-primary" href="<?php echo $webappuri ?>/profile/password">Change Password</a>
        <a role="button" class="btn btn-outline-secondary" href="?do=logout">Logout</a>
		<a role="button" class="btn btn-outline-secondary" href="<?php echo $webappuri ?>/home">Cancel</a>
	</div>
</div>
