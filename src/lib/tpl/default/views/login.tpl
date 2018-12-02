<?php

/**
 * iAddressBook Login Page
 *
 * You should leave the doctype at the very top - It should
 * always be the very first line of a document.
 *
 * @link   http://iaddressbook.org
 * @author Clemens Wacha (clemens.wacha@gmx.net)
 */

?>

<div class="row justify-content-md-center pt-4">
    <div class="col-4">
        <form method="post">
            <h2 class="form-signin-heading"><?php echo $lang['login']; ?></h2>

            <fieldset class="input-group-vertical">
                <div class="form-group">
                    <label class="sr-only"><?php echo $lang['username']; ?></label>
                    <input type="text" class="form-control" placeholder="<?php echo $lang['username']; ?>" name="username" value="<?php echo array_get($_REQUEST, 'username'); ?>" required >
                </div>
                <div class="form-group">
                    <label class="sr-only"><?php echo $lang['password']; ?></label>
                    <input type="password" class="form-control" placeholder="<?php echo $lang['password']; ?>" name="password" >
                </div>
            </fieldset>
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="rememberme"> <?php echo $lang['sticky']; ?>
                </label>
            </div>
            <input type="hidden" name="do" value="login" >
            <button class="btn btn-lg btn-primary btn-block" type="submit"><?php echo $lang['btn_login']; ?></button>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        // set focus to the search bar
        $u = $("input[name=username]");
        $p = $("input[name=password]");
        if($u.val())
            $p.focus();
        else
            $u.focus();

    });
</script>
