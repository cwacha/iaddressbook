<h4><?php echo lang('category'); ?></h4>

<form method='post' action='' name='cat_select_form'>
	<input type='hidden' name='do' value='cat_select' />
	<input type='hidden' name='cat_id' value='' />
</form>

<script type='text/javascript'>
	function select_category(id) {
		document.cat_select_form.elements['cat_id'].value = id;
		document.cat_select_form.submit();
	}
</script>
<?php
    foreach($categories as $category) {
        include(template('categorylist_item.tpl'));
    }
?>
