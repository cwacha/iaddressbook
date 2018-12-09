<?php
    $roles = $securitycontroller->get_roles();
?>
<div class="row">
    <div class="col pt-4">
        <h2 class="pt-4"><?php echo lang('roles'); ?></h2>
        <table class="table table-hover" id="roletable">
            <thead>
                <tr><th><?php echo lang('role_id'); ?></th><th><?php echo lang('permissions'); ?></th></tr>
            </thead>
            <tbody>
                <?php
                    foreach($roles as $roleid => $permissions) {
                        $permissionlist = join(', ', $permissions);
                        echo "<tr id='".$roleid."'><td>".$roleid."</td><td>".$permissionlist."</td></tr>";
                    }
                ?>
            </tbody>
        </table>

        <!-- Begin Buttons -->
        <a role="button" class="btn btn-primary" href="<?php echo $webappuri ?>/admin/role/edit"><?php echo lang('role_add'); ?></a>
        <a role="button" class="btn btn-outline-secondary" href="<?php echo $webappuri ?>/admin"><?php echo lang('cancel'); ?></a>
    </div>
</div>

<script type="text/javascript">
    $(document).ready( function () {
        $('#roletable > tbody > tr').click(function() {
            var trid = $(this).closest('tr').attr('id');
            window.location.href = "<?php echo $webappuri ?>/admin/role/edit?roleid="+trid;
        });
    });
</script>