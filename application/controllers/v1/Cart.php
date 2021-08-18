<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';
require APPPATH . 'helpers/authorization_helper.php';
require APPPATH . 'helpers/jwt_helper.php';

class Cart extends REST_Controller
{
	public function __construct()
	{
		// Construct the parent class
		parent::__construct();

		// Configure limits on our controller methods
		// Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
		$this->load->model('cart_model', 'cart');
	}

	public function addCart_post()
	{
		$response = $this->cart->get_AddCart($this->post());
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function getUserCart_get()
	{
		$response = $this->cart->get_UserCart($_REQUEST['code'], $_REQUEST['lang'], $_REQUEST['store']);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function promo_post()
	{
		$response = $this->cart->get_UserPromoCart($this->post());
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function updateCart_post()
	{
		$response = $this->cart->get_updateCart($this->post());
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function deleteCart_delete()
	{
		$response = $this->cart->get_DeleteCart($_REQUEST['code'], $_REQUEST['productId'], $_REQUEST['store']);
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
