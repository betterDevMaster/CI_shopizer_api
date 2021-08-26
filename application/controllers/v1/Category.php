<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';
require APPPATH . 'helpers/authorization_helper.php';
require APPPATH . 'helpers/jwt_helper.php';

class Category extends REST_Controller
{
	public $tblCategories = 'tbl_categories';

	public function __construct()
	{
		// Construct the parent class
		parent::__construct();

		// Configure limits on our controller methods
		// Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
		$this->load->model('category_model', 'category');
		$this->load->model('common_model', 'common');
	}

	public function list_get()
	{
		$count = isset($_REQUEST['count']) ? $_REQUEST['count'] : 100;
		$filter = isset($_REQUEST['filter']) ? $_REQUEST['filter'] : 'admin';
		$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 0;
		$store = isset($_REQUEST['store']) ? $_REQUEST['store'] : 'DEFAULT';
		$lang = isset($_REQUEST['lang']) ? $_REQUEST['lang'] : 'en';
		$code = isset($_REQUEST['code']) ? $_REQUEST['code'] : '';

		// $categories = $this->category->get_Category(null, $store, $lang, $count, $page);

		$categories = $this->category->get_CategoryDetail(0, $code, $store, $lang, $count, $page, $filter);
		$response = array(
			'categories' => $categories, 'number' => count($categories), 'recordsFiltered' => 0, 'recordsTotal' => count($categories), 'totalPages' => ceil(count($categories) / $count)
		);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function categoryDetail_get()
	{
		$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : 0;
		$store = isset($_REQUEST['store']) ? $_REQUEST['store'] : 'DEFAULT';
		$lang = isset($_REQUEST['lang']) ? $_REQUEST['lang'] : 'en';
		$response = $this->category->get_CategoryDetail($id, null, $store, $lang);
		$this->response($response[0], REST_Controller::HTTP_OK);
	}

	public function manufacturers_post()
	{
		$response = $this->category->get_Manufacturers($this->post());
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function variants_post()
	{
		$response = $this->category->get_Variants($this->post());
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function updateCategory_post()
	{
		$response = $this->category->updateCategory($this->post());
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function addCategory_post()
	{
		$response = $this->category->addCategory($this->post());
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function visible_post()
	{
		$response = $this->category->visible($this->post());
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function deleteCategory_delete()
	{
		$response =	$this->common->delete_TableRecordWithCondition(array('id' => $_REQUEST['id']), $this->tblCategories);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function unique_get()
	{
		$where = array('code' => $_REQUEST['code']);
		$response = $this->common->get_UniqueTableRecord($where, $this->tblCategories);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function move_put()
	{
		$response = $this->category->moveCategory($this->put());
		$this->response($response, REST_Controller::HTTP_OK);
	}
}
