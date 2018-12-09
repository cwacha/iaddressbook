<?php
    $roles = $securitycontroller->get_roles();
    $roleid = array_get($_REQUEST, 'roleid', '');
    $permissions = array_get($roles, $roleid, array());

    $editable = 'readonly';
    if(empty($roleid))
        $editable = 'required';

?>
<div class="row">
    <div class="col pt-4 pb-4">
        <h2><?php echo lang('role_edit'); ?></h2>
        <div class="col-4">
            <form method="post">
                <div id="role_edit_messages"></div>
                <fieldset class="input-group-vertical">
                    <div class="form-group">
                        <input type="text" class="form-control" name="roleid" placeholder="<?php echo lang('role_id'); ?>" value="<?php echo $roleid ?>" <?php echo $editable ?> >
                    </div>
                </fieldset>
                <div class="form-group">
                    <label for="role_permissions"><?php echo lang('permissions'); ?></label>
                    <select multiple class="form-control" name="permissions[]" size="25" required>
                        <?php
                            foreach($roles['all'] as $dummy => $permission) {
                                $selected = "";
                                if(in_array($permission, $permissions))
                                    $selected = "selected";
                                echo "<option $selected>".$permission."</option>";
                            }
                        ?>
                    </select>
                </div>
                <input type='hidden' name='do' value='role_save' />
                <button type="submit" class="btn btn-primary">Save</button>
				<a role="button" class="btn btn-danger" href="javascript:do_action('role_delete', '<?php echo $roleid .": " . lang('confirm_del_contact'); ?>');"><?php echo lang('btn_delete');?></a>
                <a role="button" class="btn btn-outline-secondary" href="<?php echo $webappuri ?>/admin/roles"><?php echo lang('cancel'); ?></a>
            </form>
        </div>
    </div>
</div>

<form method="post" name="action_form">
    <input type="hidden" name="roleid" value="<?php echo $roleid ?>" />
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
