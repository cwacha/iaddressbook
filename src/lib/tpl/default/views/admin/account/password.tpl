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
        <h2><?php echo lang('account_password'); ?></h2>
        <div class="col-4 pb-3">
            <small><?php echo lang('account_password_msg'); ?></small>
        </div>
        <div class="col-4">
            <form method="post" onsubmit="return validate()">
                <div id="account_password_messages"></div>
                <fieldset class="input-group-vertical">
                    <div class="form-group">
                        <input type="text" class="form-control" name="accountid" placeholder="<?php echo lang('account_id'); ?>" value="<?php echo $accountid ?>" readonly>
                    </div>
                    <div class="form-group">
                        <input type="password" class="form-control" name="password" id="password" placeholder="<?php echo lang('password'); ?>" onkeyup="calc();" required>
                    </div>
                    <div class="form-group">
                        <input type="password" class="form-control" name="password2" id="password2" placeholder="<?php echo lang('password_confirm'); ?>" required>
                    </div>
                </fieldset>
                <div class="form-check pb-3">
                    <input type="checkbox" id="showpassword" class="form-check-input" onchange="toggle_password()">
                    <label class="form-check-label" for="showpassword"><?php echo lang('password_show'); ?></label>
                </div>
				<div class="progress">
					<div id="progress" class="progress-bar progress-bar-danger" role="progressbar"></div>
				</div>
				<div class="pb-4" id="quality"><?php echo lang('password_very_weak'); ?></div>
                <input type='hidden' name='do' value='account_password' />
                <button type="submit" class="btn btn-primary"><?php echo lang('account_save'); ?></button>
                <a role="button" class="btn btn-outline-secondary" href="<?php echo $webappuri ?>/admin/accounts"><?php echo lang('cancel'); ?></a>
            </form>
        </div>
    </div>
</div>


<script type="text/javascript">
    function validate() {
        $("#account_password_messages").html('');
        var ok = true;
        if(!validate_password()) 
            ok = false;
        return ok;
    }
    function validate_password() {
        var $pass = $('#password');
        var $pass2 = $('#password2');
        if($pass.val() == $pass2.val()) {
            return true;
        }
        $pass2.addClass('is-invalid');
        validate_msg('Passwords don\'t match');
        return false;
    }
    function validate_msg(text) {
        $("#account_password_messages").append('<div class="alert alert-danger" role="alert">'+text+'</div>');
    }

    function toggle_password() {
        var $pass = $('#password');
        var $pass2 = $('#password2');
        if($pass.prop('type') == 'password') {
            $pass.prop('type', 'text');
            $pass2.prop('type', 'text');
        } else {
            $pass.prop('type', 'password');
            $pass2.prop('type', 'password');
        }
    }

	var pw = new PasswordHelper("password");
    qtext = {
        very_weak:      "<?php echo lang('password_very_weak'); ?>",
        weak:           "<?php echo lang('password_weak'); ?>",
        good:           "<?php echo lang('password_good'); ?>",
        strong:         "<?php echo lang('password_strong'); ?>",
        very_strong:    "<?php echo lang('password_very_strong'); ?>",
        very_strong128: "<?php echo lang('password_very_strong128'); ?>",
        very_strong256: "<?php echo lang('password_very_strong256'); ?>"
    }
    pw.qualitytext(qtext);

	function calc() {
		pw.update();

		var percent = pw.percent();
		var pobj = $("#progress");
		pobj.css("width", percent + "%");

		pobj.removeClass("bg-success bg-warning bg-danger");
		if(percent > 67)
			pobj.addClass("bg-success");
		else if(percent > 45)
			pobj.addClass("bg-warning");
		else
			pobj.addClass("bg-danger");

		$("#quality").html(pw.quality());
	}
</script>
