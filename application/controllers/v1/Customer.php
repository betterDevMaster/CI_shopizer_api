<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';
require APPPATH . 'helpers/authorization_helper.php';
require APPPATH . 'helpers/jwt_helper.php';

class Customer extends REST_Controller
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
		$this->load->model('customer_model', 'customer');
		$this->load->model('common_model', 'common');
		$this->load->model('cart_model', 'cart');
	}

	public function ping_get()
	{
		$response = array('status' => 'UP');
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function gender_get()
	{
		// Get the request data
		$genders = array(['type' => 'M', 'name' => 'Male'], ['type' => 'F', 'name' => 'Female']);
		$this->response($genders, REST_Controller::HTTP_OK);
	}

	public function country_get()
	{
		$lang = isset($_REQUEST['lang']) ? $_REQUEST['lang'] : 'es';
		$response = $this->customer->get_CountryZonesList($lang);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function zone_get()
	{
		$response = $this->customer->get_ZonesList($_REQUEST['code']);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function register_post()
	{
		$customer_id = $this->customer->add_NewUser($this->post());
		$response = array('id' => $customer_id, 'token' => md5($customer_id));
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function login_post()
	{
		// Check if any customer exists with the given credentials
		$data = array(
			'userName' => $this->post('userName'),
			'password' => md5($this->post('password')),
		);
		$existingToken = $this->post('existingToken');
		$customer_id = $this->customer->check_Auth($data);
		if ($customer_id) {
			if ($existingToken) {
				$response = array('id' => $customer_id, 'token' => $existingToken);
				$this->cart->updateCartWithCustomerIDWhenLogin($customer_id, $existingToken);
			} else {
				$response = array('id' => $customer_id, 'token' => md5($customer_id));
			}
			$_SESSION['user_id'] = $customer_id;
			$_SESSION['user_token'] = md5($customer_id);
			$this->response($response, REST_Controller::HTTP_OK);
		} else {
			$response = array(
				'status' => false,
				'message' => 'Wrong email or password',
			);
			$this->response($response, REST_Controller::HTTP_NOT_FOUND);
		}
	}

	public function logout_post()
	{
		$this->session->sess_destroy();
	}

	public function profile_get()
	{
		$response = $this->customer->getUserProfile($_REQUEST['id']);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function updateUser_post()
	{
		$data = array(
			'userName' => $this->post('userName'),
			'emailAddress' => $this->post('emailAddress'),
		);
		$this->customer->update_User($data, $this->post('emailAddress'));
		$this->response($data, REST_Controller::HTTP_OK);
	}

	public function deleteUser_delete()
	{
		$response =	$this->common->delete_TableRecordWithCondition(array('id' => $_REQUEST['id']), $this->tblUser);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function updatePassword_post()
	{
		$response = $this->customer->update_UserPassword($this->post());
		if ($response)
			$this->response($response, REST_Controller::HTTP_OK);
		else
			$this->response($response, REST_Controller::HTTP_NOT_FOUND);
	}

	public function updateBilling_post()
	{
		$this->updateUserBillingDelivery($this->post(), $this->tblBilling);
	}

	public function updateDelivery_post()
	{
		$this->updateUserBillingDelivery($this->post(), $this->tblDelivery);
	}

	function updateUserBillingDelivery($data, $table)
	{
		$response =	$this->customer->updateBillingDelivery_User($data, $table);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	function config_get()
	{
		$response  = $this->common->get_TableContentWithRowResult($this->tblConfig);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	function list_get($id = null)
	{
		$store = isset($_REQUEST['store']) ? $_REQUEST['store'] : 'DEFAULT';
		$lang = isset($_REQUEST['lang']) ? $_REQUEST['lang'] : 'es';
		$count = isset($_REQUEST['count']) ? (int)$_REQUEST['count'] : 10;
		$page = isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 0;
		$customers = $this->customer->getList($store, $lang, $count, $page, $id);
		if (!$id)
			$response = array('customers' => $customers[2], 'number' => count($customers[2]), 'recordsFiltered' => 0, 'recordsTotal' => $customers[0], 'totalPages' => $customers[1]);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function list_post()
	{
		$response = $this->customer->addCustomerList($this->post());
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function list_put($id)
	{
		$response = $this->customer->updateCustomerList($this->put(), $id);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	function orders_get($id = null)
	{
		$store = isset($_REQUEST['store']) ? $_REQUEST['store'] : 'DEFAULT';
		$lang = isset($_REQUEST['lang']) ? $_REQUEST['lang'] : 'es';
		$count = isset($_REQUEST['count']) ? (int)$_REQUEST['count'] : 10;
		$page = isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 0;
		$response = null;
		// $orders = $this->customer->getOrder($store, $lang, $count, $page, $id);
		// if (!$id)
		// 	$response = array('orders' => $orders[2], 'number' => count($orders[2]), 'recordsFiltered' => 0, 'recordsTotal' => $orders[0], 'totalPages' => $orders[1]);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	function request_post()
	{
		$response = $this->customer->resetPassword($this->post());
		$this->response($response, REST_Controller::HTTP_OK);
	}
}
