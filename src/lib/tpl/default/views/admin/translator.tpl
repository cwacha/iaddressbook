<?php
    $translator = Translator::getInstance();
?>

<div class="row pt-4 pl-3 pr-3">
	<form method="post" class="" name="actions">
		<input type="hidden" name="do" value="translator_save" />
		<div id="translator_table"></div>

	    <!-- Begin Buttons -->
        <button type="submit" class="btn btn-primary"><?php echo lang('btn_save');?></button>
	    <a role="button" class="btn btn-outline-secondary" href="<?php echo $webappuri ?>/admin"><?php echo lang('btn_cancel');?></a>
	</form>
</div>

<script>
	$(document).ready(function() {
		config = {};
		config.defaults = <?php echo json_encode($translator->get_default_lang_array()); ?>;
		config.lang = <?php echo json_encode($translator->get_conf_lang_array()); ?>;

		create_table(config);
	});
	
    function create_table(config) {
    	defaults = config.defaults;
        for (var property in defaults) {
            if(['lang_code'
                ].indexOf(property) > -1)
                continue;
            if (!defaults.hasOwnProperty(property))
                continue;
            create_table_line(property, config);
        }
    }
    function create_table_line(property, config) {
    	conf = config.lang;
    	defaults = config.defaults;
    	$table = $("#translator_table");

    	option_name = hsc(array_get(defaults, property, property));

        changed_class = ''
        conf_property = conf[property];
        if(!conf.hasOwnProperty(property)) {
            changed_class = 'text-danger';
            conf_property = '';
        }
            

    	block = "";
    	block += '<div class="form-group row">';
    	block += '  <label for="'+property+'" class="col-sm col-form-label '+changed_class+'">'+option_name+' <br/><small class="text-muted">'+hsc(property)+'</small></label>';
        block += html_textarea('lang_' + property, conf_property, '');

        //block += '<div class="col-sm-2" />';
    	block += '</div>';

    	$table.append(block);
    }

    function array_get(array, key, default_value) {
    	return key in array ? array[key] : default_value;
    }

    function html_edit(id, value, onchange) {
    	id_val = '';
    	value_val = '';
    	onchange_val = '';
    	if(id !== "") id_val='id="'+hsc(id)+'"';
    	if(id !== "") name_val='name="'+hsc(id)+'"';
    	if(value !== "") value_val='value="'+hsc(value)+'"';
    	if(onchange !== "") onchange_val='onchange="'+hsc(onchange)+'"';
    	block = "";
    	block += '<div class="col-sm">';
    	block += '  <input type="text" class="form-control" '+name_val+' '+id_val+" "+value_val+" "+onchange_val+" />";
    	block += '</div>';
    	return block;
    }
    function html_textarea(id, value, onchange, rows, cols) {
        id_val = '';
        onchange_val = '';
        rows_val = '';
        cols_val = '';

        if(id !== "") id_val='id="'+hsc(id)+'"';
        if(id !== "") name_val='name="'+hsc(id)+'"';
        if(onchange !== "") onchange_val='onchange="'+hsc(onchange)+'"';
        if(rows !== "") rows_val='rows="'+hsc(rows)+'"';
        if(cols !== "") cols_val='cols="'+hsc(cols)+'"';

        block = "";
        block += '<div class="col-sm">';
        block += '<textarea class="form-control" '+name_val+' '+id_val+' '+rows_val+' '+cols_val+' '+onchange_val+' >';
        block += hsc(value);
        block += '</textarea>';
        block += '</div>';

        return block;
    }
    function hsc(text) {
    	if(typeof text !== "string")
    		return text;
		var map = {
			'&': '&amp;',
			'<': '&lt;',
			'>': '&gt;',
			'"': '&quot;',
			"'": '&#039;'
		};
		return text.replace(/[&<>"']/g, function(m) { return map[m]; });
	}
</script>
