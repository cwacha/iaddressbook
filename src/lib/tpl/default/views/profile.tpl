<?php
	$accountid = $_SESSION['accountid'];
	$account = $_SESSION['account'];
?>
<div class="row">
    <div class="col pt-4">
        <h2><?php echo lang('account_my'); ?></h2>
        <div class="col-4">
        	<div class="row">
        		<div class="col"><strong><?php echo lang('account_id'); ?></strong></div>
        		<div class="col"><?php echo $accountid ?></div>
    		</div>
        	<div class="row">
        		<div class="col"><strong><?php echo lang('fullname'); ?></strong></div>
        		<div class="col"><?php echo $account['fullname'] ?></div>
    		</div>
        	<div class="row">
        		<div class="col"><strong><?php echo lang('email'); ?></strong></div>
        		<div class="col"><?php echo $account['email'] ?></div>
    		</div>
        	<div class="row pb-4">
        		<div class="col"><strong><?php echo lang('roles'); ?></strong></div>
        		<div class="col"><?php echo join(', ', $account['roles']) ?></div>
    		</div>
        </div>
    </div>
</div>
<div class="row">
	<div class="col">
		<a role="button" class="btn btn-primary" href="<?php echo $webappuri ?>/profile/password"><?php echo lang('password_change'); ?></a>
        <a role="button" class="btn btn-outline-secondary" href="?do=logout"><?php echo lang('logout'); ?></a>
		<a role="button" class="btn btn-outline-secondary" href="<?php echo $webappuri ?>/home"><?php echo lang('cancel'); ?></a>
	</div>
</div>
