<div class="row justify-content-md-center pt-4">
    <div class="col-8">
		<div class="alert alert-info" role="alert">
			<div class="alert-body">You are now logged out.</div>
		</div>


		<a role="button" class="btn btn-primary" href="<?php echo $webappuri ?>/login">Log-In</a>
	</div>
</div>

<script type="text/javascript" charset="utf-8">
	var delayMS = 5000;
	setTimeout(function(){ window.location = "<?php echo $webappuri ?>/login"; }, delayMS);
</script>