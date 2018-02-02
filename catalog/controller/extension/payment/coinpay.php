<?php
require_once(DIR_SYSTEM . 'payment/coinpay/coinpay_api_client.php');
class Controllerextensionpaymentcoinpay extends Controller {
	private $payment_module_name  = 'coinpay';
	private $api, $order, $moduledata, $cryptocurrencies;

	function init($load_order = true){
		$this->load->model('checkout/order');
		if($load_order){
			$this->order = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		}

    $this->api_id = $this->config->get($this->payment_module_name.'_api_id');
		$this->api = new CoinpayApiClient($this->api_id);
    $this->cryptocurrencies = $this->config->get($this->payment_module_name.'_cryptocurrencies');

		$this->moduledata['enabled'] = true;
		if(!$this->api){
			$this->moduledata['enabled'] = false;
		}
	}

	public function index() {

		$this->init();

		$this->moduledata['api'] = $this->api;

		$this->language->load('extension/payment/'.$this->payment_module_name);

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/extension/payment/coinpay.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/extension/payment/coinpay.tpl';
		} else {
			$this->template = 'default/template/extension/payment/coinpay.tpl';
		}

		if($this->moduledata['enabled']){

      // Get payemnt addresses
      $request = new PaymentDetailsRequest(
        HTTPS_SERVER . 'index.php?route=extension/payment/coinpay/callback',
        $this->order['total'],
        $this->order['currency_code'],
        $this->cryptocurrencies,
        'Payment on Open Cart'
      );

      // create all sessions
      $this->session->data['payment_details'] = false;
      $this->session->data['payment_details_hash'] = false;
      $this->session->data['expected_amount_note'] = '';

      // Refresh for new sessions, or use details from session
      if( $this->paymentDetailsMustBeRefreshed($request) ) {
        $payment_details = $this->api->getPaymentDetails($request);
        $this->session->data['payment_details'] = $payment_details;
        $this->session->data['payment_details_hash'] = $request->hash();
      }else{
        $payment_details = $this->session->data['payment_details'];
      }

      $this->moduledata['payment_details'] = $payment_details;
      $this->moduledata['text_after_payment'] = $this->language->get('text_after_payment');
      $this->moduledata['button_confirm'] 			= $this->language->get('button_bitcoin_confirm');

      // Loop through all address and save in session
      $addresses_arr = [];
      foreach( $payment_details as $key => $value ) {
        foreach($value as $key => $item) {
          array_push( $addresses_arr, $item->address);
        }
      } // End loop
      $this->session->data['bx_payment_addresses'] = $addresses_arr;



      // Load payment fields view
      if($payment_details) {
        return $this->load->view('extension/payment/coinpay', $this->moduledata);
      }else{
        // TODO Error
        var_dump('Error: no payment data received');
        die();
      }


			//$this->session->data['bitcoin_order_id'] = $this->api->order_id;
			//$this->moduledata['paybox'] = $paybox;
			//$this->moduledata['btc_url'] = 'bitcoin:'.$paybox->address.'?amount='.$paybox->btc_amount.'&label='.urlencode($this->config->get('config', 'store_name').' Order '.$this->_basket['cart_order_id']);
		}

		$this->moduledata['button_back'] = $this->language->get('button_back');
		$this->moduledata['text_bitcoin_unavailable'] = $this->language->get('text_bitcoin_unavailable');
		$this->moduledata['text_bitcoin_title'] = $this->language->get('text_bitcoin_title');
		$this->moduledata['text_pay_msg'] = $this->language->get('text_pay_msg');
		$this->moduledata['text_afterpay'] = $this->language->get('text_afterpay');
		$this->moduledata['text_countdown'] = $this->language->get('text_countdown');
		$this->moduledata['text_countdown_exp'] = $this->language->get('text_countdown_exp');
		$this->moduledata['text_wait'] = $this->language->get('text_wait');

		$this->moduledata['continue'] = HTTPS_SERVER . 'index.php?route=checkout/success';
		if ($this->request->get['route'] != 'checkout/guest_step_3') {
			$this->moduledata['back'] = HTTPS_SERVER . 'index.php?route=checkout/payment';
		} else {
			$this->moduledata['back'] = HTTPS_SERVER . 'index.php?route=checkout/guest_step_2';
		}

		$this->moduledata['url_success'] 	= HTTPS_SERVER . 'index.php?route=checkout/success';
		$this->moduledata['url_cancel'] 	= HTTPS_SERVER . 'index.php?route=checkout/payment';

		$this->moduledata['store_name']		=$this->order['store_name'];
		$this->moduledata['order_id']			=$this->order['order_id'];
		$this->moduledata['order_total']		=$this->order['total'];
		$this->moduledata['order_currency']	=$this->order['currency_code'];

		return $this->load->view('extension/payment/coinpay', $this->moduledata);
	}

	public function send() {
		$this->load->model('checkout/order');

		$this->init();
    $input = file_get_contents('php://input');
    $addresses = json_decode($input);

    //$this->session->data['bx_payment_addresses'] = $addresses;
    $result = $this->api->checkPaymentReceived(
      $this->session->data['bx_payment_addresses']
    );

    $this->add_expected_amount_note();

    if( $result->payment_received === false ) {
      $json['error'] = "Did you already pay it? We still did not see your payment! It can take a few seconds for your payment to appear. If you already paid - press COMPLETE ORDER button again.";
      return $this->json_response($json);
    }

    if( $result->is_enough === false ) {
      $this->session->data['bx_paid'] = $result->paid;
      $json['error'] = $this->notEnoughError($result);
      $order_status_id = $this->config->get($this->payment_module_name.'_order_status_id');
      $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $order_status_id, $this->notEnoughError($result), true);
      return $this->json_response($json);
    }

    $this->session->data['bx_paid_by'] = $result->paid_by;
    $this->session->data['payment_received'] = $result;

    $order_saved = $this->api->saveOrderId(
      $this->session->data['bx_payment_addresses'],
      $this->session->data['order_id']
    );

    if( $order_saved === false ) {
      $error = "Somethig went wrong ". $order_saved->error;
      $json['error'] = $error;
    }

    $this->awaiting_confirmation_note();

    $json['redirect'] = $this->url->link('checkout/success', '', true);

    return $this->json_response($json);
  }

  private function json_response($data)
  {
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($data));
  }

  private function add_expected_amount_note()
  {
    $str = "Extectin ";
    if( isset($this->session->data['payment_details']) ) {
      foreach( $this->session->data['payment_details']->addresses as $key => $value ) {
        if( $value->available ) {
          $str .= " {$value->amount} in {$key} to {$value->address}; ";
        }
      }
    }
    if( $this->session->data['expected_amount_note'] === false ) {
      $order_status_id = $this->config->get($this->payment_module_name.'_order_status_id');
      $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $order_status_id, $str, true);
    }else{
      $this->session->data['expected_amount_note'] = true;
    }
  }

  private function paid_by()
  {
    $str = "Paid: ";
    $paid = $this->session->data['payment_received']->paid_by;
    if( isset($paid) ) {
      $str .= " {$paid->amount} in {$paid->name} {{$paid->ticker}) to {$paid->address} proof link: {$paid->proof_link}; ";
    }
  }

  private function notEnoughError($result)
  {
    $str = "Not enough error: ";
    foreach( $result->paid as $paid ) {
      if( $paid->amount > 0 ) {
        $str .= " Payment amount is not enough: {$paid->amount} {$paid->cryptocurrency}; ";
      }
    }
    return $str;
  }

  private function awaiting_confirmation_note()
  {
    $str = "[Coinpay: Payment awaiting confirmation] ". $this->paid_by();
    $order_status_id = $this->config->get($this->payment_module_name.'_order_status_id');
    $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $order_status_id, $str, true);
  }


	public function callback() {
		$this->load->model('checkout/order');

		$this->init(false);

    $data = json_decode(file_get_contents('php://input'), true );

		if($ipn = $this->api->validIPN($data)){
			$order_id	= $data['order_id'];
			$order_info = $this->model_checkout_order->getOrder($order_id);
			if (!empty($order_id) && !empty($data)) {
				$order_status_id = $this->config->get($this->payment_module_name.'_order_status_id_after');
				$this->model_checkout_order->addOrderHistory($order_id, $order_status_id, 'BX CoinPay IPN: '.$data['message'], true);

				echo 'IPN Success';
				exit();
			}
		}
		header("HTTP/1.0 403 Forbidden");
		echo 'IPN Failed';
		exit();
	}

  private function paymentDetailsMustBeRefreshed($request)
  {
    // Hash will change if cart has changes significant to payment
    return $this->session->data['payment_details_hash'] != $request->hash()
      OR !$this->session->data['payment_details'];
  }
}
?>
