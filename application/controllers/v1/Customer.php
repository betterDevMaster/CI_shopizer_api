<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';
require APPPATH . 'helpers/authorization_helper.php';
require APPPATH . 'helpers/jwt_helper.php';

class Customer extends REST_Controller
{
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
		$this->baseUrl = base_url();
		$this->methods['users_get']['limit'] = 500; // 500 requests per hour per user/key
		$this->methods['users_post']['limit'] = 100; // 100 requests per hour per user/key
		$this->methods['users_delete']['limit'] = 50; // 50 requests per hour per user/key
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
		$lang = null;
		if (isset($_REQUEST['lang']))
			$lang = $_REQUEST['lang'];
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
		$customer_id = $this->customer->check_Auth($data);

		if ($customer_id) {
			$response = array('id' => $customer_id, 'token' => md5($customer_id));
			$this->response($response, REST_Controller::HTTP_OK);

			// if ($customer['status']) {
			// 	$response = array(
			// 		'status' => true,
			// 		'message' => 'User login successful',
			// 		'meta' => $customer['message'],
			// 	);
			// 	$this->response($response, REST_Controller::HTTP_OK);
			// } else {
			// 	$response = array(
			// 		'status' => false,
			// 		'message' => $customer['message'],
			// 		'meta' => array(),
			// 	);
			// 	$this->response($response, REST_Controller::HTTP_OK);
			// }
		} else {
			$response = array(
				'status' => false,
				'message' => 'Wrong email or password',
			);
			$this->response($response, REST_Controller::HTTP_NOT_FOUND);
		}
	}

	public function profile_get()
	{
		$response = $this->customer->get_UserProfile($_REQUEST['id']);
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
		$this->customer->delete_User($_REQUEST['id']);
		$this->response($_REQUEST['id'], REST_Controller::HTTP_OK);
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
}
