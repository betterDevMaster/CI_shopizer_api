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
	}

	public function default_get($store = 'DEFAULT', $names = false, $list = false)
	{
		$response = $this->store->getDefault($store, $names, $list);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function names_get()
	{
		$store = isset($_REQUEST['store']) ? $_REQUEST['store'] : null;
		$response = $this->default_get($store, true, false);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function update_Store($pData)
	{
		$response = $this->default_get($_REQUEST['store'], true, false);
		$this->response($response, REST_Controller::HTTP_OK);
	}
}
