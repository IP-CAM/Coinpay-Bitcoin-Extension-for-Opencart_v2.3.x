<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">

  <!-- header -->
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-coinpay" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary">
          <i class="fa fa-save"></i>
        </button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default">
          <i class="fa fa-reply"></i>
        </a>
      </div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
          <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <!-- #header -->

  <!-- content -->
  <div class="container-fluid">

    <?php if ($error_warning) { ?>
      <div class="alert alert-danger">
        <i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
      </div>
    <?php } ?>

    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $heading_title; ?></h3>
      </div>
      <div class="panel-body">
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-coinpay" class="form-horizontal">

          <!-- field -->
          <div class="form-group required">
            <label class="col-sm-2 control-label" for="input-login"><?php echo $entry_api_id; ?></label>
            <div class="col-sm-10">
              <input type="text" name="coinpay_api_id" value="<?php echo $coinpay_api_id; ?>" placeholder="<?php echo $entry_api_id; ?>" id="coinpay_api_id" class="form-control" />
              <?php if ($error_api_id) { ?>
                <div class="text-danger"><?php echo $error_api_id; ?></div>
              <?php } ?>
            </div>
          </div>

          <!-- field -->
          <div class="form-group required">
            <label class="col-sm-2 control-label" for="input-login"><?php echo $entry_cryptocurrencies; ?></label>
            <div class="col-sm-10">
              <input type="text" name="coinpay_cryptocurrencies" value="<?php echo $coinpay_cryptocurrencies; ?>" placeholder="<?php echo $entry_cryptocurrencies; ?>" id="coinpay_cryptocurrencies" class="form-control" />
              <div class="currency-example">Example: BTC, BCH, DAS, DOG, LTC</div>
              <?php if ($error_cryptocurrencies) { ?>
                <div class="text-danger"><?php echo $error_cryptocurrencies; ?></div>
              <?php } ?>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="entry_order_status"><?php echo $entry_order_status; ?></label>
            <div class="col-sm-10">
              <select name="coinpay_order_status_id">
              <?php foreach ($order_statuses as $order_status ) { ?>
              <?php if ($order_status['order_status_id'] == $coinpay_order_status_id || ($coinpay_order_status_id == 0 && strtolower($order_status['name']) == 'pending')) { ?>
              <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
              <?php } else { ?>
              <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
              <?php } ?>
              <?php } ?>
            </select> <?php echo $entry_order_status_note;?>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="entry_order_status_after"><?php echo $entry_order_status_after; ?></label>
            <div class="col-sm-10">
              <select name="coinpay_order_status_id_after">
              <?php foreach ($order_statuses as $order_status) { ?>
              <?php if ($order_status['order_status_id'] == $coinpay_order_status_id_after || ($coinpay_order_status_id_after == 0 && strtolower($order_status['name']) == 'processing')) { ?>
              <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
              <?php } else { ?>
              <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
              <?php } ?>
              <?php } ?>
            </select> <?php echo $entry_order_status_after_note;?>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="entry_status"><?php echo $entry_status; ?></label>
            <div class="col-sm-10">
              <select name="coinpay_status">
              <?php if ($coinpay_status) { ?>
              <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
              <option value="0"><?php echo $text_disabled; ?></option>
              <?php } else { ?>
              <option value="1"><?php echo $text_enabled; ?></option>
              <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
              <?php } ?>
            </select>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-login"><?php echo $entry_sort_order; ?></label>
            <div class="col-sm-10">
              <input type="text" name="coinpay_sort_order" value="<?php echo $coinpay_sort_order; ?>" placeholder="<?php echo $entry_sort_order; ?>" id="coinpay_sort_order" class="form-control" size="1" />
            </div>
          </div>

        </form>

      </div>
    </div>
  </div>
</div>
<script type="text/javascript"><!--
jQuery(function($) {
  var text, pass = false, form = $("#form-coinpay");
  var form_field = $('input[name="coinpay_cryptocurrencies"]');
  if( form_field.get(0) ) {
    form.on('submit', function(e) {
      e.preventDefault();
      if( form_field.val().length > 0 ) {

        // RegEx
        ticker_array = form_field.val().split(/[., ]+/);
        for(var i = 0; i < ticker_array.length; i++)
        {
          let data = ticker_array[i].trim(); // Trim
          if( data.length <= 2 || data.length > 5 || data.match(/^[a-zA-Z0-9]*$/) === null ) {
            pass = false;
            break;
          }else{
            pass = true;
          }
        } // end loop

      }else{
        pass = true;
      }
      if( pass === true ) {
        $(this).off('submit').submit();
      }else{
        $(".currency-example").html("<p class='text-danger'>Input not valid: Example: BTC, BCH, DAS, DOG, LTC</p>");
      }
    }); // on submit
  }
});
//--></script>

<?php echo $footer; ?>
