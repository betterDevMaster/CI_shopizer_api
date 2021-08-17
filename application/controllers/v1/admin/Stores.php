<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';
require APPPATH . 'helpers/authorization_helper.php';
require APPPATH . 'helpers/jwt_helper.php';

class Stores extends REST_Controller
{
	public function __construct()
	{
		// Construct the parent class
		parent::__construct();

		// Configure limits on our controller methods
		// Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
		$this->load->model('admin_model', 'admin');
		$this->load->model('customer_model', 'customer');
		$this->load->model('store_model', 'store');
	}

	public function list_get()
	{
		// $stores = $this->admin->get_StoreList($_REQUEST['start']);
		$stores = $this->store->get_Default('DEFAULT', false, true);
		$response = array('data' => $stores, 'number' => 10, 'recordsFiltered' => 10, 'recordsTotal' => count($stores), 'totalPages' => 1);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function update_put()
	{
		$this->store->update_Store($this->put(), true);
		$this->response($this->put(), REST_Controller::HTTP_OK);
	}

	public function deleteStore_delete()
	{
		$response =	$this->store->delete_Store($_REQUEST['store']);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function unique_get()
	{
		$response =	$this->store->unique_Store($_REQUEST['code']);
		$this->response(array('exists' => $response), REST_Controller::HTTP_OK);
	}

	public function createStore_post()
	{
		$this->store->update_Store($this->post(), false);
		$this->response($this->post(), REST_Controller::HTTP_OK);
	}
}
