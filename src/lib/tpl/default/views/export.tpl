<div class="container">
    <div class="row">
        <div class="col pt-4">
            <h2><?php echo lang('import_export'); ?></h2>
        </div>
    </div>
    <form method="post" enctype="multipart/form-data">
        <div class="form-row">
            <div class="col-auto">
                <input type="hidden" name="do" value="import_vcard" >
                <input type="file" class="form-control" id="file1" name="vcard_file" >
            </div>
            <div class="col-auto">
                <input type="submit" class="btn btn-primary" value="<?php echo lang('import_vcard'); ?>" >
            </div>
        </div>
    </form>
    <div class="row">
        <div class="col pt-4">
            <p>
                <a href="javascript:do_action('import_folder')"><?php echo lang('import_folder'); ?></a>
            </p>
        </div>
    </div>
    <div class="row">
        <div class="col pt-4">
            <h2><?php echo lang('import_export'); ?></h2>
            <p>
                <a href="javascript:do_action('export_vcard_cat')"><?php echo lang('export_vcard'); ?></a>
            </p>
            <p>
                <a href="javascript:do_action('export_csv_cat')"><?php echo lang('export_csv'); ?></a>
            </p>
            <p>
                <a href="javascript:do_action('export_ldif_cat')"><?php echo lang('export_ldif'); ?></a>
            </p>
        </div>
    </div>
</div>

<form method='post' action='' name='action_form'>
    <input type='hidden' name='do' value='show' />
    <input type='hidden' name='id' value='' />
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