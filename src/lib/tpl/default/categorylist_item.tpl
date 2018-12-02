<?php
	$selected = "";
	if($CAT_ID == $category->id)
		$selected = "_selected";
?>
<div class="category_item<?php echo $selected; ?>" onClick='javascript:select_category(<?php echo $category->id; ?>);'>
    <a role="button"><?php echo $category->displayName(); ?></a>
</div>
