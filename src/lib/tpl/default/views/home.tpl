<div class='row'>
  <div class='sidebar' >
    <!-- Category List -->
    <?php tpl_include('categorylist.tpl'); ?>
  </div>
  <div class='sidebar-footer'>
    <div class='items'>
      <div class='btn-group float-right' role='group'>
          <a role='button' class='btn btn-outline-secondary btn-sm' href="javascript:$('#create_category_modal').modal('toggle');" data-toggle="tooltip" title="Create category" ><span class='glyphicon glyphicon-plus' /></a>
        <a role='button' class='btn btn-outline-secondary btn-sm' href="javascript:do_action('cat_del', '<?php echo $lang['confirm_cat_delete']; ?>');" data-toggle="tooltip" title="Delete category"><span class='glyphicon glyphicon-minus' /></a>
      </div>
    </div>
  </div>
  <div class='sidebar2' >
    <!-- Contact List -->
    <?php tpl_include('contactlist.tpl'); ?>
  </div>
  <div class='sidebar2-footer'>
    <div class='items'>
      <div class='btn-group float-right' role='group'>
        <a role='button' class='btn btn-outline-secondary btn-sm' href="javascript:do_action('new');" data-toggle="tooltip" title="Create contact"><span class='glyphicon glyphicon-plus' /></a>
        <a role='button' class='btn btn-outline-secondary btn-sm' href="javascript:do_action('delete_many', '<?php echo $lang['confirm_del_contacts']; ?>');" data-toggle="tooltip" title="Delete contacts"><span class='glyphicon glyphicon-minus' /></a>
      </div>
      <div class='btn-group float-right pr-2' role='group'>
          <a role='button' class='btn btn-outline-secondary btn-sm' href="javascript:$('#select_category_modal').modal('toggle');" data-toggle="tooltip" title="Add contacts to category"><span class='glyphicon glyphicon-arrow-left' /></a>
          <a role='button' class='btn btn-outline-secondary btn-sm' href="javascript:do_action('cat_del_contacts', '<?php echo $lang['confirm_cat_remove_contacts']; ?>');" data-toggle="tooltip" title="Remove contacts from category"><span class='glyphicon glyphicon-arrow-right' /></a>
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

<!-- Modal -->
<div class="modal fade" id="create_category_modal" tabindex="-1" role="dialog" aria-labelledby="create_category_modal" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="create_category_modal_label"><?php echo $lang['cat_add']; ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form method='post' action='' name='cat_add_form'>
        <input type='hidden' name='do' value='cat_add' />
        <div class="modal-body">
          <input type='text' name='cat_name' class='form-control' value='' placeholder='<?php echo $lang['category']; ?>'/>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary"><?php echo $lang['cat_add']; ?></button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="select_category_modal" tabindex="-1" role="dialog" aria-labelledby="select_category_modal" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="select_category_modal_label"><?php echo $lang['cat_add']; ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form method='post' action='' name='cat_add_form'>
        <input type='hidden' name='do' value='cat_add' />
        <div class="modal-body">
          <?php foreach($categories as $category) { 
            msg("category: " . $category->name());
            ?>

            <div class="category_item" > 
              <input type="checkbox" name="ct_<?php echo $contact->id; ?>" value="<?php echo $contact->id; ?>" onclick='javascript:event.stopPropagation();'>

              <a role="button"><?php echo $category->displayName(); ?></a>
            </div>
          <?php } ?>

        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary"><?php echo $lang['cat_add']; ?></button>
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
});

</script>
