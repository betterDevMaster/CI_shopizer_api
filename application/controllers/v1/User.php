<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';
require APPPATH . 'helpers/authorization_helper.php';
require APPPATH . 'helpers/jwt_helper.php';

class User extends REST_Controller
{
	public function __construct()
	{
		// Construct the parent class
		parent::__construct();

		// Configure limits on our controller methods
		// Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
		$this->load->model('user_model', 'user');
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
		$response = $this->user->get_CountryZonesList($_REQUEST['lang']);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function zone_get()
	{
		$response = $this->user->get_ZonesList($_REQUEST['code']);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function register_post()
	{
		$user_id = $this->user->add_NewUser($this->post());
		$response = array('id' => $user_id, 'token' => unique_token());
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function login_post()
	{
		// Check if any user exists with the given credentials
		$data = array(
			'userName' => $this->post('userName'),
			'password' => md5($this->post('password')),
		);
		$user_id = $this->user->check_Auth($data);

		if ($user_id) {
			$response = array('id' => $user_id, 'token' => unique_token());
			$this->response($response, REST_Controller::HTTP_OK);

			// if ($user['status']) {
			// 	$response = array(
			// 		'status' => true,
			// 		'message' => 'User login successful',
			// 		'meta' => $user['message'],
			// 	);
			// 	$this->response($response, REST_Controller::HTTP_OK);
			// } else {
			// 	$response = array(
			// 		'status' => false,
			// 		'message' => $user['message'],
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
		$response = $this->user->get_UserProfile($_REQUEST['id']);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function updateUser_post()
	{
		$data = array(
			'userName' => $this->post('userName'),
			'emailAddress' => $this->post('emailAddress'),
		);
		$this->user->update_User($data, $this->post('emailAddress'));
		$this->response($data, REST_Controller::HTTP_OK);
	}

	public function deleteUser_delete()
	{
		$this->user->delete_User($_REQUEST['id']);
		$this->response($_REQUEST['id'], REST_Controller::HTTP_OK);
	}

	public function updatePassword_post()
	{
		$ret = $this->user->update_UserPassword($this->post());
		if ($ret)
			$this->response($ret, REST_Controller::HTTP_OK);
		else
			$this->response($ret, REST_Controller::HTTP_NOT_FOUND);
	}

	public function updateBilling_post()
	{
		$this->updateUserBillingDelivery($this->post(), 'tbl_user_billing');
	}

	public function updateDelivery_post()
	{
		$this->updateUserBillingDelivery($this->post(), 'tbl_user_delivery');
	}

	function updateUserBillingDelivery($data, $table)
	{
		$ret =	$this->user->updateBillingDelivery_User($data, $table);
		$this->response($ret, REST_Controller::HTTP_OK);
	}
}
