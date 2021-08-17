<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';
require APPPATH . 'helpers/authorization_helper.php';
require APPPATH . 'helpers/jwt_helper.php';

class Category extends REST_Controller
{
	public function __construct()
	{
		// Construct the parent class
		parent::__construct();

		// Configure limits on our controller methods
		// Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
		$this->load->model('category_model', 'category');
		$this->baseUrl = base_url();
		$this->methods['users_get']['limit'] = 500; // 500 requests per hour per user/key
		$this->methods['users_post']['limit'] = 100; // 100 requests per hour per user/key
		$this->methods['users_delete']['limit'] = 50; // 50 requests per hour per user/key
	}

	public function list_get()
	{
		$count = isset($_REQUEST['count']) ? $_REQUEST['count'] : 15;
		$filter = isset($_REQUEST['filter']) ? $_REQUEST['filter'] : 'admin';
		$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 0;
		$store = isset($_REQUEST['store']) ? $_REQUEST['store'] : 'DEFAULT';
		$lang = isset($_REQUEST['lang']) ? $_REQUEST['lang'] : 'en';
		$code = isset($_REQUEST['code']) ? $_REQUEST['code'] : '';
		$categories = $this->category->get_CategoryDetail(null, $store, $lang, $count, $page, $filter, $code);
		$response = array(
			'categories' => $categories, 'number' => count($categories), 'recordsFiltered' => 0, 'recordsTotal' => count($categories), 'totalPages' => 1
		);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function categoryDetail_get()
	{
		$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : 0;
		$store = isset($_REQUEST['store']) ? $_REQUEST['store'] : 'DEFAULT';
		$lang = isset($_REQUEST['lang']) ? $_REQUEST['lang'] : 'en';
		$response = $this->category->get_CategoryDetail($id, $store, $lang);
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
		$response = $this->category->deleteCategory($_REQUEST['id']);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function unique_get() 
	{
		$response = $this->category->uniqueCategory($_REQUEST['code']);
		$this->response(array('exists' => $response), REST_Controller::HTTP_OK);
	}

	public function move_put() 
	{
		$response = $this->category->moveCategory($this->put());
		$this->response($response, REST_Controller::HTTP_OK);
	}
}
