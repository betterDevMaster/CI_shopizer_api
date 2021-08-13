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
		$this->load->model('admin_model', 'admin');
		$this->load->model('customer_model', 'customer');
		// $this->baseUrl = base_url();
		// $this->methods['users_get']['limit'] = 500; // 500 requests per hour per user/key
		// $this->methods['users_post']['limit'] = 100; // 100 requests per hour per user/key
		// $this->methods['users_delete']['limit'] = 50; // 50 requests per hour per user/key
	}

	public function login_post()
	{
		$id = $this->admin->get_AdminCredential($this->post());
		$length = 18;
		if ($id) {
			$response = array('id' => $id, 'token' => bin2hex(random_bytes($length)));
			$this->response($response, REST_Controller::HTTP_OK);
		} else {
			$response = array('message' => 'Bad credentials');
			$this->response($response, REST_Controller::HTTP_NOT_FOUND);
		}
	}

	public function language_get()
	{
		$languages = $this->admin->get_Languages();
		$this->response($languages, REST_Controller::HTTP_OK);
	}

	public function profile_get()
	{
		$response = $this->customer->get_UserProfile(null, 1);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function groups_get()
	{
		$response = $this->admin->get_Groups();
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function updatePassword_post()
	{
		$ret = $this->admin->update_UserPassword($this->post());
		if ($ret)
			$this->response($ret, REST_Controller::HTTP_OK);
		else
			$this->response($ret, REST_Controller::HTTP_NOT_FOUND);
	}

	public function deleteUser_delete()
	{
		$this->customer->delete_User($_REQUEST['userId']);
		$this->response($_REQUEST['id'], REST_Controller::HTTP_OK);
	}

	public function updateUser_post()
	{
		$response = $this->admin->update_UserWithDefault($this->post());
		if ($response)
			$this->response($response, REST_Controller::HTTP_OK);
		else
			$this->response($response, REST_Controller::HTTP_NOT_FOUND);
	}
}
