<div class="row pt-4 pl-3 pr-3">
	<form method="post" class="" name="actions">
		<input type="hidden" name="do" value="config_save" />
		<div id="config_table"></div>

	    <!-- Begin Buttons -->
        <button type="submit" class="btn btn-primary"><?php echo lang('btn_save');?></button>
	    <a role="button" class="btn btn-outline-secondary" href="<?php echo $webappuri ?>/admin"><?php echo lang('btn_cancel');?></a>
	</form>
</div>

<script>
    <?php global $lang; ?>
	$(document).ready(function() {
		// revert octdec() for fmode and dmode
		<?php 
			$conf['fmode'] = sprintf('%03d', decoct($conf['fmode']));
			$conf['dmode'] = sprintf('%03d', decoct($conf['dmode']));
			$defaults
		?>

		defaults = <?php echo json_encode($defaults); ?>;
		conf = <?php echo json_encode($conf); ?>;
		meta = <?php echo json_encode($meta); ?>;
		lang = <?php echo json_encode($lang); ?>;

		config = {};
		config.defaults = defaults;
		config.conf = conf;
		config.meta = meta;
		config.lang = lang;

		create_table(config);
	});
    function create_table(config) {
    	defaults = config.defaults;
    	for (var property in defaults) {
            if(['dbtype',
                'dbname',
                'dbserver',
                'dbuser',
                'dbpass',
                'dbtable_abs',
                'dbtable_ab',
                'dbtable_cat',
                'dbtable_catmap'
                ].indexOf(property) > -1)
                continue;
    		if (!defaults.hasOwnProperty(property))
    			continue;
    		create_table_line(property, config);
    	}
    }
    function create_table_line(property, config) {
    	conf = config.conf;
    	defaults = config.defaults;
    	meta = config.meta;
    	lang = config.lang;
    	$table = $("#config_table");

    	option_name = hsc(array_get(lang, property, property));

    	option_help = array_get(lang, property+'_help', "no help available");
    	option_default = hsc(defaults[property]);
        changed_class = ''
        if(conf[property] != defaults[property])
            changed_class = 'text-danger';

    	block = "";
    	block += '<div class="form-group row">';
    	block += '  <label for="'+property+'" class="col-sm-3 col-form-label '+changed_class+'">'+hsc(option_name)+' <br/><small class="text-muted">'+property+'</small></label>';
    	switch(meta[property][0]) {
	        case 'multichoice':
	            block += html_select(property, conf[property], '', meta[property]['_choices'], );
	            break;
	        case 'onoff':
	            block += html_onoff(property, conf[property], ''); 
	            break;
	        case 'string':
	            block += html_edit(property, conf[property], '');
	            break;
	        case 'textarea':
	        default:
	            block += html_textarea(property, conf[property], '', 5, 30);
	            break;
    	}
    	block += '  <div class="col-5">'
    	block += '    <small class="text-muted">'+option_help+' <br/>[default="'+option_default+'"]</small>';
    	block += '  </div>';
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
    	block += '<div class="col-sm-4">';
    	block += '  <input type="text" class="form-control" '+name_val+' '+id_val+" "+value_val+" "+onchange_val+" />";
    	block += '</div>';
    	return block;
    }
    function html_onoff(id, value, onchange) {
    	id_val = '';
    	value_val = '';
    	onchange_val = '';
    	if(id !== "") id_val='id="'+hsc(id)+'"';
    	if(id !== "") name_val='name="'+hsc(id)+'"';
    	if(value) value_val='checked';
    	if(onchange !== "") onchange_val='onchange="'+hsc(onchange)+'"';
    	block = "";
    	block += '<div class="col-sm-4">';
    	block += '  <input type="checkbox" class="form-control" '+name_val+' '+id_val+" value='1' "+value_val+" "+onchange_val+" />";
    	block += '</div>';
    	return block;
    }
    function html_select(id, value, onchange, options) {
    	id_val = '';
    	onchange_val = '';

    	if(id !== "") id_val='id="'+hsc(id)+'"';
    	if(id !== "") name_val='name="'+hsc(id)+'"';
    	if(onchange !== "") onchange_val='onchange="'+hsc(onchange)+'"';

    	block = "";
    	block += '<div class="col-sm-4">';
    	block += '<select size="1" '+name_val+' '+id_val+' '+onchange_val+'>';
    	if(options instanceof Array) {
    		for (var i = 0; i < options.length; i++) {
    			if(value == options[i]) {
    				block += "<option selected>"+options[i]+"</option>";
    			} else {
    				block += "<option>"+options[i]+"</option>";
    			}
    		}
    	} else if(typeof options === "object") {
    		for (var i in options) {
    			if (!options.hasOwnProperty(i))
    				continue;
    			if(value == options[i]) {
    				block += "<option selected>"+options[i]+"</option>";
    			} else {
    				block += "<option>"+options[i]+"</option>";
    			}
    		}
    	}
    	block += '</select>';
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
    	block += '<div class="col-sm-4">';
    	block += '<textarea class="text" '+name_val+' '+id_val+' '+rows_val+' '+cols_val+' '+onchange_val+' >';
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

