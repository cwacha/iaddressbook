<div class="row justify-content-md-center pt-4">
    <div class="col-8">
		<div class="alert alert-info" role="alert">
			<div class="alert-body"><?php echo lang('logged_out_msg'); ?></div>
		</div>


		<a role="button" class="btn btn-primary" href="<?php echo $webappuri ?>/login"><?php echo lang('login'); ?></a>
	</div>
</div>

<script type="text/javascript" charset="utf-8">
	var delayMS = 5000;
	setTimeout(function(){ window.location = "<?php echo $webappuri ?>/login"; }, delayMS);
</script>