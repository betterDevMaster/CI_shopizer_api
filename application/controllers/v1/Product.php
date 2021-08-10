<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';
require APPPATH . 'helpers/authorization_helper.php';
require APPPATH . 'helpers/jwt_helper.php';

class Product extends REST_Controller
{
	public function __construct()
	{
		// Construct the parent class
		parent::__construct();

		// Configure limits on our controller methods
		// Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
		$this->load->model('product_model', 'product');
		$this->baseUrl = base_url();
		$this->methods['users_get']['limit'] = 500; // 500 requests per hour per user/key
		$this->methods['users_post']['limit'] = 100; // 100 requests per hour per user/key
		$this->methods['users_delete']['limit'] = 50; // 50 requests per hour per user/key
	}

	public function featuredItem_get()
	{
		$products = $this->product->get_FeaturedItem($_REQUEST['store'], $_REQUEST['lang']);
		$response = array('number' => count($products), 'products' => $products, 'recordsFiltered' => 0, 'recordsTotal' => count($products),  'totalPages' => 1);
		$this->response($response, REST_Controller::HTTP_OK);
	}
	
	public function price_post()
	{
		$response = $this->product->get_Price($this->post());
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function productDetail_post()
	{
		$products = $this->product->get_ProductDetail($this->post());
		$response = array('number' => count($products), 'products' => $products, 'recordsFiltered' => 0, 'recordsTotal' => count($products),  'totalPages' => 1);
		$this->response($response, REST_Controller::HTTP_OK);
	}
}
