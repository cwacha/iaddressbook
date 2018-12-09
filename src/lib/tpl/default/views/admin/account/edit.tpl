<?php
    $accounts = $securitycontroller->get_accounts();
    $roles = $securitycontroller->get_roles();
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

        <h2><?php echo lang('account_edit'); ?></h2>
        <div class="col-4">
            <form method="post" onsubmit="return validate()">
                <div id="account_edit_messages"></div>
                <fieldset class="input-group-vertical">
                    <div class="form-group">
                        <input type="text" class="form-control" name="accountid" placeholder="Account ID" value="<?php echo $accountid ?>" required>
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" name="fullname" placeholder="Fullname" value="<?php echo $account['fullname'] ?>">
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" name="email" placeholder="E-Mail" value="<?php echo $account['email'] ?>">
                    </div>
                </fieldset>
                <div class="form-group">
                    <label for="account_roles"><?php echo lang('roles'); ?></label>
                    <select multiple class="form-control" name="roles[]" id="account_roles" size="10" required>
                        <?php
                            foreach($roles as $roleid => $dummy) {
                                $selected = "";
                                if(in_array($roleid, $account['roles']))
                                    $selected = "selected";
                                echo "<option $selected>".$roleid."</option>";
                            }
                        ?>
                    </select>
                </div>
                <input type='hidden' name='do' value='account_save' />
                <button type="submit" class="btn btn-primary"><?php echo lang('save'); ?></button>
                <a role="button" class="btn btn-outline-secondary" href="<?php echo $webappuri ?>/admin/accounts"><?php echo lang('cancel'); ?></a>

            </form>
        </div>
    </div>
</div>


<script type="text/javascript">
    $(document).ready( function () {
    });
    function validate() {
        $("#account_edit_messages").html('');
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
        $("#account_edit_messages").append('<div class="alert alert-danger" role="alert">'+text+'</div>');
    }
</script>
