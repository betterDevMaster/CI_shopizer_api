<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';
require APPPATH . 'helpers/authorization_helper.php';
require APPPATH . 'helpers/jwt_helper.php';

class Cart extends REST_Controller
{
	public $tblCart = 'tbl_cart';

	public function __construct()
	{
		// Construct the parent class
		parent::__construct();

		// Configure limits on our controller methods
		// Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
		$this->load->model('cart_model', 'cart');
		$this->load->model('common_model', 'common');
	}

	public function addCart_post()
	{
		// $code = $this->post('code');
		// $response = $this->cart->addNewCart($this->post(), $code);
		$response = $this->cart->addNewCart($this->post());
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function getUserCart_get()
	{
		$code = isset($_REQUEST['code']) ? $_REQUEST['code'] : null;
		$customer = isset($_REQUEST['customer']) ? $_REQUEST['customer'] : null;
		$lang = isset($_REQUEST['lang']) ? $_REQUEST['lang'] : 'es';
		$store = isset($_REQUEST['store']) ? $_REQUEST['store'] : 'DEFAULT';
		// $response = $this->cart->get_UserCart($code, $customer, $lang, $store);
		$response = $this->cart->getCartByCode($code, $lang, $store);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function promo_post()
	{
		// $response = $this->cart->get_UserPromoCart($this->post());
		// $response = $this->cart->getCartByCode($this->post('code'), null, $this->post('lang'), $this->post('promoCart'));
		$response = $this->cart->updateCartPromo($this->post());
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function deleteCart_delete($cardId)
	{
		$response =	$this->common->delete_TableRecordWithCondition(array('code' => $cardId), $this->tblCart);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function deleteProductOfCart_delete()
	{
		$code = isset($_REQUEST['code']) ? $_REQUEST['code'] : null;
		$productId = isset($_REQUEST['productId']) ? $_REQUEST['productId'] : 0;
		$store = isset($_REQUEST['store']) ? $_REQUEST['store'] : 'DEFAULT';
		$response = $this->cart->deleteProductOfCart($code, $productId, $store);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function shipping_post()
	{
		$response = $this->cart->get_Shipping($this->post());
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function total_get()
	{
		$quote = null;
		if (isset($_REQUEST['quote'])) {
			$quote = $_REQUEST['quote'];
		}
		$response = $this->cart->get_Total($_REQUEST['code'], $quote);
		$this->response($response, REST_Controller::HTTP_OK);
	}
}
