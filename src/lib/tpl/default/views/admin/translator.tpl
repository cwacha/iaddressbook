<?php
    $translator = Translator::getInstance();
?>
<div class="row">
    <div class="col pt-4">
        <h2 class="pt-4 pb-4"><?php echo lang('translate'); ?></h2>
        <table class="table table-hover" id="langtable">
            <thead>
                <tr>
                	<th><?php echo lang('lang'); ?></th>
                	<th><?php echo lang('lang_code_name'); ?></th>
                	<th><?php echo lang('lang_author_name'); ?></th>
                	<th><?php echo lang('lang_translate_stats'); ?></th>
                	<th></th>
               	</tr>
            </thead>
            <tbody>
                <?php
                	global $meta;
                	foreach($translator->get_all_languages() as $lng) {
                		$lang_code = $lng['lang_code'];
                		$lang_name = $lng['lang_name'];
                		$lang_author = $lng['lang_author'];
                		$lang_author_email = $lng['lang_author_email'];
                		$count = $lng['count'];
                		$total = $lng['total'];
                		$percent = round($lng['percent']);
                		$stats = $lng['stats'];

                		$author = $lang_author;
                		if(!empty($lang_author_email))
                			$author .= " <" . $lang_author_email . ">";

                		echo "<tr id='".hsc($lang_code)."'>";
                		echo "<td>".hsc($lang_name)."</td>";
                		echo "<td>".hsc($lang_code)."</td>";
                		echo "<td>".hsc($author)."</td>";
                		echo "<td>";
                		echo "  <div class='progress'>";
                		echo "    <div class='progress-bar bg-success' role='progressbar' style='width: ".$percent."%' aria-valuenow='25' aria-valuemin='0' aria-valuemax='100'>".$percent." %</div>";
                		echo "  </div> ";
                		echo "</td>";
                		echo "<td>".$count."/".$total."</td>";
                		echo "</tr>";
                	}
                ?>
            </tbody>
        </table>

        <!-- Begin Buttons -->
	    <!--
	    <a role="button" class="btn btn-primary" href="javascript:$('#create_lang_modal').modal('toggle');" ><?php echo lang('lang_add'); ?></a>
    -->
        <a role="button" class="btn btn-primary" href="<?php echo $webappuri ?>/admin/translator/edit"><?php echo lang('lang_add'); ?></a>
        <a role="button" class="btn btn-outline-secondary" href="<?php echo $webappuri ?>/admin"><?php echo lang('cancel'); ?></a>
    </div>
</div>

<script type="text/javascript">
    $(document).ready( function () {
        $('#langtable > tbody > tr').click(function() {
            var trid = $(this).closest('tr').attr('id');
            window.location.href = "<?php echo $webappuri ?>/admin/translator/edit?lcode="+trid;
        });
    });
</script>

