<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';
require APPPATH . 'helpers/authorization_helper.php';
require APPPATH . 'helpers/jwt_helper.php';

class Store extends REST_Controller
{
	public function __construct()
	{
		// Construct the parent class
		parent::__construct();

		// Configure limits on our controller methods
		// Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
		$this->load->model('store_model', 'store');
		$this->baseUrl = base_url();
		$this->methods['users_get']['limit'] = 500; // 500 requests per hour per user/key
		$this->methods['users_post']['limit'] = 100; // 100 requests per hour per user/key
		$this->methods['users_delete']['limit'] = 50; // 50 requests per hour per user/key
	}

	public function default_get($store = 'DEFAULT', $names = false)
	{
		$response = $this->store->get_Default($store, $names);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function names_get()
	{
		$response = $this->default_get($_REQUEST['store'], true);
		$this->response($response, REST_Controller::HTTP_OK);
	}
}
