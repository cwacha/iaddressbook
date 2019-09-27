<?php
    $accounts = $securitycontroller->get_accounts();
    $accountid = array_get($_REQUEST, 'accountid', '');
    $account = array_get($accounts, $accountid, array());

    if(!array_key_exists('fullname', $account)) {
        $account['fullname'] = '';
        $account['email'] = '';
        $account['roles'] = array();
    }
?>
<div class="row">
    <div class="col pt-4">
        <h2><?php echo lang('account'); ?></h2>
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
		<a role="button" class="btn btn-primary" href="<?php echo $webappuri ?>/admin/account/edit?accountid=<?php echo $accountid ?>"><?php echo lang('edit'); ?></a>
		<a role="button" class="btn btn-primary" href="<?php echo $webappuri ?>/admin/account/password?accountid=<?php echo $accountid ?>"><?php echo lang('password_change'); ?></a>
		<a role="button" class="btn btn-danger" href="javascript:do_action('account_delete', '<?php echo $accountid .": " . lang('confirm_del_account'); ?>');"><?php echo lang('btn_delete');?></a>
		<a role="button" class="btn btn-outline-secondary" href="<?php echo $webappuri ?>/admin/accounts"><?php echo lang('cancel'); ?></a>
	</div>
</div>

<form method="post" name="action_form">
    <input type="hidden" name="accountid" value="<?php echo $accountid ?>" />
    <input type="hidden" name="do" value="" />
</form>

<script type="text/javascript">
	function do_action(act, confirmation) {
	    if(confirmation) {
	        if(!confirm(confirmation)) return;
	    }
	    document.action_form.elements["do"].value = act;
	    document.action_form.submit();
	}
</script>
