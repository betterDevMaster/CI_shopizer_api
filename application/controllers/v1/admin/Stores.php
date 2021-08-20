<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';
require APPPATH . 'helpers/authorization_helper.php';
require APPPATH . 'helpers/jwt_helper.php';

class Stores extends REST_Controller
{
	public $tblStore = 'tbl_store';

	public function __construct()
	{
		// Construct the parent class
		parent::__construct();

		// Configure limits on our controller methods
		// Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
		$this->load->model('common_model', 'common');
		$this->load->model('store_model', 'store');
	}

	public function list_get()
	{
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
		$response =	$this->common->delete_TableRecordWithCondition(array('code' => $_REQUEST['store']), $this->tblStore);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function unique_get()
	{
		$where = array('code' => $_REQUEST['code']);
		$response = $this->common->get_UniqueTableRecord($where, $this->tblStore);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function createStore_post()
	{
		$this->store->update_Store($this->post(), false);
		$this->response($this->post(), REST_Controller::HTTP_OK);
	}
}
