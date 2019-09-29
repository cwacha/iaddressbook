<div class='row'>
  <div class='sidebar' >
    <!-- Category List -->
    <?php tpl_include('categorylist.tpl'); ?>
  </div>
  <!-- Category Buttons -->
  <div class='sidebar-footer'>
    <div class='items'>
      <div class='btn-group float-right' role='group'>
          <a role='button' class='btn btn-outline-secondary btn-sm' href="javascript:$('#create_category_modal').modal('toggle');" data-toggle="tooltip" title="<?php echo lang('cat_add'); ?>" ><span class='glyphicon glyphicon-plus' /></a>
        <a role='button' class='btn btn-outline-secondary btn-sm' href="javascript:do_post({'do': 'cat_del'}, '<?php echo lang('confirm_cat_delete'); ?>');" data-toggle="tooltip" title="<?php echo lang('cat_delete');?>"><span class='glyphicon glyphicon-minus' /></a>
      </div>
    </div>
  </div>
  <div class='sidebar2' >
    <!-- Contact List -->
    <?php tpl_include('contactlist.tpl'); ?>
  </div>
  <!-- Contact Buttons -->
  <div class='sidebar2-footer'>
    <div class='items'>
      <div class='btn-group float-right' role='group'>
        <a role='button' class='btn btn-outline-secondary btn-sm' href="javascript:do_post({'do': 'new'});" data-toggle="tooltip" title="<?php echo lang('contact_create');?>"><span class='glyphicon glyphicon-plus' /></a>
        <a role='button' class='btn btn-outline-secondary btn-sm' href="javascript:do_delete_many('<?php echo lang('confirm_del_contacts'); ?>');" data-toggle="tooltip" title="<?php echo lang('contacts_delete');?>"><span class='glyphicon glyphicon-minus' /></a>
      </div>
      <div class='btn-group float-right pr-2' role='group'>
          <a role='button' class='btn btn-outline-secondary btn-sm' href="javascript:$('#select_category_modal').modal('toggle');" data-toggle="tooltip" title="<?php echo lang('cat_add_contacts');?>"><span class='glyphicon glyphicon-tag' /></a>
          <a role='button' class='btn btn-outline-secondary btn-sm' href="javascript:do_cat_del_contacts('<?php echo lang('confirm_cat_remove_contacts'); ?>');" data-toggle="tooltip" title="<?php echo lang('cat_delete_contacts');?>"><span class='glyphicon glyphicon-remove' /></a>
      </div>
    </div>
  </div>
  <div class='col-sm-6 offset-sm-6 main'>
    <!-- Person Area -->
    <?php
      if(is_object($contact)) {
          if($ACT == 'edit' or $ACT == 'new') {
              tpl_include('person_edit.tpl');
          } else {
              tpl_include('person_show.tpl');
          }
      } else {
          tpl_include('person_empty.tpl');
      }
    ?>
  </div>
</div>

<!-- Modal Dialog Create Category -->
<div class="modal fade" id="create_category_modal" tabindex="-1" role="dialog" aria-labelledby="create_category_modal" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="create_category_modal_label"><?php echo lang('cat_add'); ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form method='post' action='' name='cat_add_form'>
        <input type='hidden' name='do' value='cat_add' />
        <div class="modal-body">
          <input type='text' name='cat_name' class='form-control' value='' placeholder='<?php echo lang('category'); ?>'/>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary"><?php echo lang('cat_add'); ?></button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Dialog Add Contacts to Category -->
<div class="modal fade" id="select_category_modal" tabindex="-1" role="dialog" aria-labelledby="select_category_modal" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="select_category_modal_label"><?php echo lang('cat_add_contacts'); ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form method='post' action='' name='cat_add_contacts_form'>
        <input type='hidden' name='do' value='cat_add_contacts' />
        <div class="modal-body">
          <select class="form-control" name="cat_id">
            <?php foreach($categories as $category) { 
              if(substr($category->name, 0, 3) === " __")
                continue;
            ?>
              <option value="<?php echo $category->id; ?>"><?php echo $category->displayName(); ?></option>
            <?php } ?>
          </select>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary"><?php echo lang('cat_add_contacts'); ?></button>
        </div>

      </form>
    </div>
  </div>
</div>

<script>
  $(document).ready(function(){
    // set focus to the search bar
    $("input[name=q]").focus();
    $('[data-toggle="tooltip"]').tooltip();

    // scroll to selected contact
    contact_id = <?php echo $ID; ?>;
    $('.sidebar2').scrollTop( $('#ct_'+contact_id).offset().top-100);
  });

  $('#select_category_modal').on('show.bs.modal', function (e) {
    $('form[name="ct_form"] input:checked').each(function() {
        var name = $(this).attr('name');
        var value = $(this).attr('value');

        $('<input>').attr({
          type: 'hidden',
          name: name,
          value: value
        }).appendTo('form[name="cat_add_contacts_form"]');
    });
  });

  function do_delete_many(confirmation) {
    var selected_contacts = [];
    $('form[name="ct_form"] input:checked').each(function() {
      selected_contacts.push($(this).attr('value'));
    });
    //alert(JSON.stringify({'do': 'delete_many', 'ct_': selected_contacts}));
    do_post({'do': 'delete_many', 'ct_': selected_contacts}, confirmation);
  }

  function do_cat_del_contacts(confirmation) {
    var selected_contacts = [];
    $('form[name="ct_form"] input:checked').each(function() {
      selected_contacts.push($(this).attr('value'));
    });
    do_post({'do': 'cat_del_contacts', 'ct_': selected_contacts}, confirmation);
  }

  function do_post(params, confirmation) {
    if(confirmation) {
      if(!confirm(confirmation)) return;
    }

    var $form = $('<form></form>');
    $form.attr("method", "post");
    $form.attr("action", "");

    $.each(params, function(key, value) {
      if ( typeof value == 'object' || typeof value == 'array' ){
        $.each(value, function(subkey, subvalue) {
          var $field = $('<input/>');
          $field.attr("type", "hidden");
          $field.attr("name", key+'[]');
          $field.attr("value", subvalue);
          $form.append($field);
        });
      } else {
        var $field = $('<input/>');
        $field.attr("type", "hidden");
        $field.attr("name", key);
        $field.attr("value", value);
        $form.append($field);
      }
    });
    $(document.body).append($form);
    $form.submit();
  }
</script>
