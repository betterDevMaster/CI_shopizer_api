<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';
require APPPATH . 'helpers/authorization_helper.php';
require APPPATH . 'helpers/jwt_helper.php';

class User extends REST_Controller
{
	public $tblUser = 'tbl_user';
	public $tblLang = 'tbl_lang';
	public $tblGroups = 'tbl_groups';
	public $tblPermissions = 'tbl_permissions';
	public $tblCurrency = 'tbl_currency';
	public $tblMeasures = 'tbl_measures';
	public $tblWeights = 'tbl_weights';

	public function __construct()
	{
		// Construct the parent class
		parent::__construct();

		// Configure limits on our controller methods
		// Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
		$this->load->model('admin_model', 'admin');
		$this->load->model('customer_model', 'customer');
		$this->load->model('common_model', 'common');
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
		$response  = $this->common->get_TableContentWithArrayResult($this->tblLang);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function profile_get()
	{
		$response = $this->customer->get_UserProfile(null, 1);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function groups_get()
	{
		$response  = $this->common->get_TableContentWithArrayResult($this->tblGroups);
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
		$this->response($_REQUEST['userId'], REST_Controller::HTTP_OK);
	}

	public function updateUser_post()
	{
		$response = $this->admin->update_UserWithDefault($this->post());
		if ($response)
			$this->response($response, REST_Controller::HTTP_OK);
		else
			$this->response($response, REST_Controller::HTTP_NOT_FOUND);
	}

	public function unique_post()
	{
		$response = $this->admin->get_UniqueUser($this->post());
		$this->response(array('exists' => $response), REST_Controller::HTTP_OK);
	}

	public function createUser_post()
	{
		$userId = $this->admin->createUser($this->post());
		$response = $this->customer->get_UserProfile($userId);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function users_get()
	{
		$users  = $this->common->get_TableContentWithArrayResult($this->tblUser);
		// $users  = $this->common->get_TableContentWithArrayResult($this->tblUser, $_REQUEST['lang'], $_REQUEST['store'], $_REQUEST['count'], $_REQUEST['page']);
		$response = array('data' => $users, 'number' => $_REQUEST['count'], 'recordsFiltered' => $_REQUEST['count'], 'recordsTotal' => count($users), 'totalPages' => 1);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function user_get()
	{
		$response = $this->customer->get_UserProfile($_REQUEST['userId']);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function insertCurrencyFromJson_get()
	{
		$currency = file_get_contents(dirname(__FILE__) . "\currency.json", false);
		$json = json_decode($currency, true);

		foreach ($json as $k => $v) {
			$this->db->insert('tbl_currency', $v);
		}
	}

	public function currency_get()
	{
		$response = $this->common->get_TableContentWithArrayResult($this->tblCurrency);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function measures_get()
	{
		$measures = $this->common->get_TableContentWithArrayResult($this->tblMeasures);
		$weights = $this->common->get_TableContentWithArrayResult($this->tblWeights);
		$response = array('measures' => $measures, 'weights' => $weights);
		$this->response($response, REST_Controller::HTTP_OK);
	}
}
