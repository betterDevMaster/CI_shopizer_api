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

	public function store_get($store = 'DEFAULT', $names = false, $list = false)
	{
		$response = $this->store->getStoreById($store, $names, $list);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function store_put()
	{
		$this->store->updateStore($this->put(), true);
		$this->response($this->put(), REST_Controller::HTTP_OK);
	}

	public function store_delete()
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

	public function store_post()
	{
		$this->store->updateStore($this->post(), false);
		$this->response($this->post(), REST_Controller::HTTP_OK);
	}
	
	public function names_get()
	{
		$store = isset($_REQUEST['store']) ? $_REQUEST['store'] : null;
		$response = $this->store_get($store, true, false);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function list_get()
	{
		$start = isset($_REQUEST['start']) ? (int)$_REQUEST['start'] : 0;
		$length = isset($_REQUEST['length']) ? (int)$_REQUEST['length'] : 1000;
		$retailers = isset($_REQUEST['retailers']) ? $_REQUEST['retailers'] : true;
		$store = isset($_REQUEST['store']) ? $_REQUEST['store'] : 'DEFAULT';
		$count = isset($_REQUEST['count']) ? (int)$_REQUEST['count'] : 10;
		$page = isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 0;

		$stores = $this->store->getStoreById($store, false, true, $count);
		$recordsTotal = $this->db->from($this->tblStore)->count_all_results();
		$totalPages = ceil($recordsTotal / $count);
		$response = array('data' => $stores, 'number' => count($stores), 'recordsFiltered' => 0, 'recordsTotal' => $recordsTotal, 'totalPages' => $totalPages);

		$this->response($response, REST_Controller::HTTP_OK);
	}

}
