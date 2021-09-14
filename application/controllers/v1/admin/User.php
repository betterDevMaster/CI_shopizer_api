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
	public $tblSupportedLanguages = 'tbl_supported_languages';
	public $tblProductGroups = 'tbl_product_groups';
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
		$response  = $this->common->get_TableContentWithArrayResult($this->tblSupportedLanguages);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function profile_get()
	{
		$response = $this->customer->getUserProfile(null, 1);
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
		$response =	$this->common->delete_TableRecordWithCondition(array('id' => $_REQUEST['userId']), $this->tblUser);
		$this->response($response, REST_Controller::HTTP_OK);
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
		$where = array('merchant' => $this->post('merchant'), 'emailAddress' => $this->post('unique'));
		$response = $this->common->get_UniqueTableRecord($where, $this->tblUser);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function createUser_post()
	{
		$userId = $this->admin->createUser($this->post());
		$response = $this->customer->getUserProfile($userId);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function users_get()
	{
		$count = isset($_REQUEST['count']) ? (int)$_REQUEST['count'] : 10;
		$users  = $this->common->get_TableContentWithArrayResult($this->tblUser);
		$recordsTotal = $this->db->from($this->tblProducts)->count_all_results();
		$totalPages = ceil($recordsTotal / $count);
		$response = array('data' => $users, 'number' => count($users), 'recordsFiltered' => 0, 'recordsTotal' => $recordsTotal, 'totalPages' => $totalPages);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function user_get()
	{
		$response = $this->customer->getUserProfile($_REQUEST['userId']);
		$this->response($response, REST_Controller::HTTP_OK);
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

	public function signup_post()
	{
		$customer_id = $this->admin->createNewUser($this->post());
		$response = array('id' => $customer_id, 'token' => md5($customer_id));
		$this->response($response, REST_Controller::HTTP_OK);
	}
}
