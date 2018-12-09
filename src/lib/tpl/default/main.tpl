<!doctype html>
<?php

/**
 * iAddressBook Default Template
 *
 * This is the template you need to change for the overall look
 * of AddressBook.
 *
 * You should leave the doctype at the very top - It should
 * always be the very first line of a document.
 *
 * @link   http://iaddressbook.org
 * @author Clemens Wacha (clemens@wacha.ch)
 */

?>
<html lang="<?php echo $conf['lang']; ?>">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="<?php echo AB_TPL; ?>images/favicon.ico">

    <title><?php echo $conf['title']; ?></title>

    <!-- Bootstrap core JavaScript -->
    <script src="<?php echo AB_TPL; ?>js/jquery-3.3.1.min.js"></script>
    <script src="<?php echo AB_TPL; ?>js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo AB_TPL; ?>js/jquery.growl.js"></script>
    <script src="<?php echo AB_TPL; ?>js/helpers.js"></script>

    <!-- Bootstrap core CSS -->
    <link href="<?php echo AB_TPL; ?>css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="<?php echo AB_TPL; ?>css/glyphicons.css" rel="stylesheet">
    <link href="<?php echo AB_TPL; ?>css/jquery.growl.css" rel="stylesheet">   
    <link href="<?php echo AB_TPL; ?>css/dashboard.css" rel="stylesheet">
  </head>

  <body>
    <header>
      <nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
        <a class="navbar-brand" href="<?php echo $baseuri ?>">
          <img src="<?php echo AB_TPL; ?>images/logo.png" width="30" height="30" class="d-inline-block align-top" alt="">
          <?php echo $conf['title']; ?>
        </a>
        <button class="navbar-toggler d-lg-none" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbar">
          <ul class="navbar-nav mr-auto">
            <?php
              if($_SESSION['authorized'] && in_array('/home', $_SESSION['account']['permissions'])) {
            ?>
              <li class="nav-item">
                <a class="nav-link" href="<?php echo $baseuri ?>"><?php echo lang('contacts'); ?></a>
              </li>
            <?php } ?>
            <?php
              if($_SESSION['authorized'] && in_array('/export', $_SESSION['account']['permissions'])) {
            ?>
              <li class="nav-item">
                <a class="nav-link" href="<?php echo $webappuri ?>/export"><?php echo lang('import_export'); ?></a>
              </li>
            <?php } ?>
            <?php
              if($_SESSION['authorized'] && in_array('/admin', $_SESSION['account']['permissions'])) {
            ?>
              <li class="nav-item">
                <a class="nav-link" href="<?php echo $webappuri ?>/admin"><?php echo lang('admin'); ?></a>
              </li>
            <?php } ?>
          </ul>

          <!-- Search Box -->
          <?php
            if($_SESSION['authorized'] && in_array('search', $_SESSION['account']['permissions'])) {
          ?>
            <form class="form-inline mt-2 mt-md-0">
              <input class="form-control mr-sm-2" type="text" name="q" value="<?php echo $QUERY; ?>" placeholder="<?php echo lang('btn_search'); ?>" />
              <button class="btn btn-outline-success my-2 my-sm-0" type="submit"><?php echo lang('btn_search'); ?></button>
              <input type="hidden" name="do" value="search" />
              <input type="hidden" name="id" value="<?php echo $ID; ?>" />
            </form>
          <?php } ?>

          <!-- User Profile -->
          <?php
            if($_SESSION['authorized']) {
          ?>
            <ul class="navbar-nav navbar-right ml-sm-2">
              <li class="dropdown">
                <a href="javascript:void(0)" class="nav-link dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                  <span class="glyphicon glyphicon-user"></span><span class="caret"></span>
                  <?php echo $_SESSION['account']['fullname']; ?>
              </a>
                <ul class="dropdown-menu" role="menu">
                  <li><a class="dropdown-item" href="<?php echo $webappuri ?>/profile"><?php echo lang('profile');?></a></li>
                  <li class="dropdown-divider"></li>
                  <li><a class="dropdown-item" href="<?php echo $webappuri ?>/?do=logout"><?php echo lang('logout');?></a></li>
                </ul>
              </li>
            </ul>
          <?php } ?>
          <ul class="navbar-nav navbar-right ml-sm-2">
            <li class="dropdown">
              <a class="nav-link" data-toggle="modal" data-target="#notification_modal">
                <span class="glyphicon glyphicon-list"></span><span id="notification_badge" class="invisible badge badge-danger">0</span>
              </a>
          </ul>

        </div>
      </nav>
    </header>

    <div class="container-fluid" role="main">
      <?php
        include($viewdocument);
      ?>
    </div>

    <div class="container-fluid pt-5">
      <?php
        if(array_get($conf, 'debug', false) == true)
          include($tpldir . '/views/internal/debug.tpl');
      ?>
    </div>

    <!-- Notification Modal -->

    <div class="invisible">
      <div class="alert alert-danger" role="alert" id="infobox_template_error" ></div>
      <div class="alert alert-warning" role="alert" id="infobox_template_warning"></div>
      <div class="alert alert-info" role="alert" id="infobox_template_info"></div>
      <div class="alert alert-success" role="alert" id="infobox_template_success"></div>
    </div>

    <div class="modal fade" id="notification_modal" tabindex="-1" role="dialog">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="notification_modal_label"><?php echo lang('notifications');?></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div id="infobox"></div>
          </div>
        </div>
      </div>
    </div>

    <script type="text/javascript">
      var infobox = new MessageBox("infobox");

      $(document).ready( function () {

        infobox.clear();
      
        var messages = <?php echo msg_json(); ?>;
        //notify("error", "test error");
        //notify("warning", "test warning");
        //notify("success", "test success");
        //notify("info", "test info");
        for(msg in messages) {
          var o = messages[msg];
          notify(o.type, o.msg);
        };

        if(infobox.length() > 0) {
          $("#notification_badge").html(infobox.length());
          $("#notification_badge").removeClass("invisible");
        }
      } );

      function notify(type, message) {
        infobox.log(type, message);
        var title = type[0].toUpperCase() + type.substr(1);
        $.growl.error({title: title, message: message, style: type, duration: 8000 });        
      }
    </script>
  </body>
</html>
