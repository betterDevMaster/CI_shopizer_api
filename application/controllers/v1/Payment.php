<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class Payment extends REST_Controller
{
	public $tblUser = 'tbl_user';
	public $tblDelivery = 'tbl_user_delivery';
	public $tblBilling = 'tbl_user_billing';
	public $tblConfig = 'tbl_config';

	public function __construct()
	{
		// Construct the parent class
		parent::__construct();

		// Configure limits on our controller methods
		// Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
		$this->load->model('payment_model', 'payment');
	}

	//  Make Stripe Payment
    public function checkout_post($cartId)
    {
		require_once('application/third_party/stripe/stripe-php/init.php');
		
		$stripeSecret = 'sk_test_51JIJYUAaEfbqTh3lNmfAaIFx5bNmMyiDFoHo51RYLxcAfng3o60y5W1XdYUc9tVXLYrv32OFfGqvEJNkSqPrrJg400VrNXwJb0';
		$pData = $this->post();
		\Stripe\Stripe::setApiKey($stripeSecret);
	
		try { 
			// Charge a credit or a debit card 
			$stripe = \Stripe\Charge::create ([
				"amount" => (int)$pData['payment']['amount'] * 100,
				"currency" => $pData['currency'],
				"source" => $pData['payment']['paymentToken'],
				"description" => "This is from ". $pData['userData']['email']
			]);
			// Save charge details 
			$this->payment->addPayment($pData, $cartId);
			$this->response(array('id'=>$cartId), REST_Controller::HTTP_OK);
		}catch(Exception $e) { 
			$this->response($e->getMessage(), REST_Controller::HTTP_NOT_FOUND);
		} 
    }
}
