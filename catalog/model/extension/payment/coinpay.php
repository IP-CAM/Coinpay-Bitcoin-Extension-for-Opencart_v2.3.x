<?php

require_once(DIR_SYSTEM . 'payment/coinpay/coinpay_api_client.php');

class ModelExtensionPaymentcoinpay extends Model {

  public function getMethod($address) {
  $this->load->language('extension/payment/coinpay');

  if ($this->config->get('coinpay_status')) {
    $status = TRUE;
  } else {
    $status = FALSE;
  }

  $method_data = [];

  if ($status) {
    $method_data = [
      'code'         	=> 'coinpay',
      'title'      	=> $this->language->get('text_title'),
      'terms' => '',
      'sort_order' 	=> $this->config->get('coinpay_sort_order'),
    ];
  }

  return $method_data;
  }
}
?>
