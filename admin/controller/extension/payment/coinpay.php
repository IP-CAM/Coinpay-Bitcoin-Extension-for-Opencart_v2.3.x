<?php
class Controllerextensionpaymentcoinpay extends Controller {
	private $error = array();
	private $payment_module_name  = 'coinpay';

	private function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/coinpay')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}

	public function index() {
		$this->load->language('extension/payment/'.$this->payment_module_name);
		$this->load->model('setting/setting');



		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {
			$this->model_setting_setting->editSetting($this->payment_module_name, $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true));
		}
 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		//$this->document->title = $this->language->get('heading_title'); // for 1.4.9
		$this->document->setTitle($this->language->get('heading_title')); // for 1.5.0 thanks rajds

		$data['heading_title'] 		= $this->language->get('heading_title');

		$data['text_enabled'] 		= $this->language->get('text_enabled');
		$data['text_disabled'] 		= $this->language->get('text_disabled');
		$data['text_all_zones'] 		= $this->language->get('text_all_zones');

		$data['text_signup_notice'] 		= $this->language->get('text_signup_notice');


		$data['entry_order_status'] 	= $this->language->get('entry_order_status');
		$data['entry_order_status_after'] 	= $this->language->get('entry_order_status_after');
		$data['entry_order_status_note'] 	= $this->language->get('entry_order_status_note');
		$data['entry_order_status_after_note'] 	= $this->language->get('entry_order_status_after_note');


		$data['entry_geo_zone'] 		= $this->language->get('entry_geo_zone');
		$data['entry_status'] 		= $this->language->get('entry_status');
		$data['entry_sort_order'] 	= $this->language->get('entry_sort_order');
		$data['entry_api_id'] 		= $this->language->get('entry_api_id');
		$data['entry_cryptocurrencies'] 		= $this->language->get('entry_cryptocurrencies');

		$data['button_save'] 			= $this->language->get('button_save');
		$data['button_cancel'] 		= $this->language->get('button_cancel');

		$data['tab_general'] 			= $this->language->get('tab_general');


 		if (isset($this->error['api_id'])) {
			$data['error_api_id'] = $this->error['api_id'];
		} else {
			$data['error_api_id'] = '';
		}
 		if (isset($this->error['cryptocurrencies'])) {
			$data['error_cryptocurrencies'] = $this->error['cryptocurrencies'];
		} else {
			$data['error_cryptocurrencies'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_payment'),
			'href' => $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payment/'.$this->payment_module_name, 'token=' . $this->session->data['token'], true)
		);

		$data['action'] = HTTPS_SERVER . 'index.php?route=extension/payment/'.$this->payment_module_name.'&token=' . $this->session->data['token'];

		$data['cancel'] = $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true);

		if (isset($this->request->post[$this->payment_module_name.'_order_status_id'])) {
			$data[$this->payment_module_name.'_order_status_id'] = $this->request->post[$this->payment_module_name.'_order_status_id'];
		} else {
			$data[$this->payment_module_name.'_order_status_id'] = $this->config->get($this->payment_module_name.'_order_status_id');
		}

		if (isset($this->request->post[$this->payment_module_name.'_order_status_id_after'])) {
			$data[$this->payment_module_name.'_order_status_id_after'] = $this->request->post[$this->payment_module_name.'_order_status_id_after'];
		} else {
			$data[$this->payment_module_name.'_order_status_id_after'] = $this->config->get($this->payment_module_name.'_order_status_id_after');
		}

		$this->load->model('localisation/order_status');
		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post[$this->payment_module_name.'_status'])) {
			$data[$this->payment_module_name.'_status'] = $this->request->post[$this->payment_module_name.'_status'];
		} else {
			$data[$this->payment_module_name.'_status'] = $this->config->get($this->payment_module_name.'_status');
		}

		if (isset($this->request->post[$this->payment_module_name.'_sort_order'])) {
			$data[$this->payment_module_name.'_sort_order'] = $this->request->post[$this->payment_module_name.'_sort_order'];
		} else {
			$data[$this->payment_module_name.'_sort_order'] = $this->config->get($this->payment_module_name.'_sort_order');
		}
		if (isset($this->request->post[$this->payment_module_name.'_api_id'])) {
			$data[$this->payment_module_name.'_api_id'] = $this->request->post[$this->payment_module_name.'_api_id'];
		} else {
			$data[$this->payment_module_name.'_api_id'] = $this->config->get($this->payment_module_name.'_api_id');
		}
		if (isset($this->request->post[$this->payment_module_name.'_cryptocurrencies'])) {
			$data[$this->payment_module_name.'_cryptocurrencies'] = $this->request->post[$this->payment_module_name.'_cryptocurrencies'];
		} else {
			$data[$this->payment_module_name.'_cryptocurrencies'] = $this->config->get($this->payment_module_name.'_cryptocurrencies');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/coinpay', $data));

	}

}
