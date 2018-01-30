<?php /** @var PaymentDetailsResponse $payment_details */ ?>
<?php
if(!$enabled){
?>
<p><?php //echo $text_bitcoin_unavailable;?></p>
<?php
}elseif(isset($error)){
echo '<p>'.$error.'</p>';
}else{
?>
<div class="payment_errors"></div>
<div class="payment">
<ul>
<?php
$i = 1;
//$actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$addresses = [];
foreach($payment_details as $key => $value) {
  foreach($value as $key => $item){
    if( $item->available == true ) {
      echo "<li>";
      echo "<a class='toggle' href='javascript:void(0);'>".$item->name." (" . $key .")</a>";
      echo "<div class='panel' id='item-".$i."'>";
      echo "<p>".$key." address: ";
      echo "<a href=".$item->payment_url."><span class='address'>".$item->address."</span></a>";
      echo "</span></p>";
      echo "<p>Amount: ".$item->amount."</p>";
      echo "<p>";
      echo "<a href=".$item->payment_url.">";
      echo "<img src='data:image/png;base64,".$item->qr_code_base64."' alt='Send to ".$item->address."' style='width:200px;height:200px;max-height:200px;'>";
      echo "</a>";
      echo "</p>";
      echo "</div>";
      echo "</li>";
      array_push($addresses,$item->address);
      $i++;
    }
  }
}
 ?>
</ul>
<input type="hidden" id='addresses' value='<?= json_encode($addresses); ?>' />

  <p class="marked">
    <strong>
    <?= $text_after_payment ?>
    </strong>
  </p>
</div>
<!-- End of payment -->
<div class="buttons">
  <div class="right"><input type="button" value="<?php echo $button_confirm; ?>" id="button-confirm" class="button" /></div>
</div>
<script type="text/javascript">
(function($) {
  $('.toggle').click(function(e) {
    e.preventDefault();
    var $this = $(this);
    if($this.next().hasClass('show')) {
      $this.next().removeClass('show');
      //$this.next().slideUp('200');
    }else{
      $this.next().addClass('show');
    }
  });
})(jQuery);
</script>

<script type="text/javascript"><!--
$('#button-confirm').bind('click', function() {
	$.ajax({
		url: 'index.php?route=extension/payment/coinpay/send',
		type: 'post',
		data: $('#addresses').val(),
		dataType: 'json',
		beforeSend: function() {
			$('#button-confirm').attr('disabled', true);
			$('.attention, .warning').remove();
			$('#payment').before('<div class="attention"><i class="fa fa-refresh fa-spin"></i> <?php echo $text_wait; ?></div>');
		},
		complete: function(data) {
			$('#button-confirm').attr('disabled', false);
			$('.attention').remove();
		},
		success: function(json) {
console.log('success: ',json['error']);
			if (json['error']) {
				$('.payment_errors').html('<div style="color:red;padding:10px;border:1px solid pink;">'+json['error']+'</div>');
			}
			if (json['redirect']) {
				location.href = json['redirect'];
			}
		}
	});
});
//--></script>
<?php
}
?>
<style>
.payment ul {list-style:none;margin:0;padding:0}
.payment ul li a {
  display: block;
  text-decoration:none;
  background-color: #eee;
  color: #444;
  cursor: pointer;
  padding: 12px;
  width: 100%;
  border: none;
  text-align: left;
  outline: none;
  font-size: 15px;
  transition: 0.4s;
  margin-top: 5px;
}
.payment li .panel p img {float: none !important; margin:0 auto;}
.payment .panel {display: none;}
.payment .panel:target{display:block;border:1px solid #ddd; padding:5px;}
.payment .panel .address { font-size: 0.8em;color: #222; }

.payment .marked {
  border: #4f9135 solid 1px;
  background-color: #b5eeb8;
  padding: 10px;
  margin-top: 10px !important;
}
</style>
