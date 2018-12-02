<?php
    $accounts = $securitycontroller->get_accounts();
?>
<div class="row">
    <div class="col pt-4">
        <h2>Accounts</h2>
        <table class="table table-hover" id="accounttable">
            <thead>
                <tr><th>Account ID</th><th>Fullname</th><th>E-mail</th><th>Roles</th></tr>
            </thead>
            <tbody>
                <?php
                    foreach($accounts as $accountid => $ai) {
                        $rolelist = join(', ', $ai['roles']);
                        echo "<tr id='".$accountid."'><td>".$accountid."</td><td>".$ai['fullname']."</td><td>".$ai['email']."</td><td>".$rolelist."</td></tr>";
                    }
                ?>
            </tbody>
        </table>

        <!-- Begin Buttons -->
        <a role="button" class="btn btn-primary" href="<?php echo $webappuri ?>/admin/account/edit">Add Account</a>
        <a role="button" class="btn btn-outline-secondary" href="<?php echo $webappuri ?>/admin">Cancel</a>
    </div>
</div>

<script type="text/javascript">
    $(document).ready( function () {
        $('#accounttable > tbody > tr').click(function() {
            var trid = $(this).closest('tr').attr('id');
            window.location.href = "<?php echo $webappuri ?>/admin/account?accountid="+trid;
        });
    });
</script>