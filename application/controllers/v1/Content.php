<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';
require APPPATH . 'helpers/authorization_helper.php';
require APPPATH . 'helpers/jwt_helper.php';

class Content extends REST_Controller
{
	public function __construct()
	{
		// Construct the parent class
		parent::__construct();

		// Configure limits on our controller methods
		// Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
		$this->load->model('content_model', 'content');
	}

	public function headerMessage_get()
	{
		$response = $this->content->get_HeaderMessage($_REQUEST['lang']);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function pages_get()
	{
		$content = $this->content->get_Pages($_REQUEST['page'], $_REQUEST['count'], $_REQUEST['store'], $_REQUEST['lang']);
		$response = array('items' => $content, 'number' => count($content), 'recordsFiltered' => 0, 'recordsTotal' => count($content), 'totalPages' => 1);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function category_get()
	{
		$content = $this->content->get_Category($_REQUEST['count'], $_REQUEST['page'], $_REQUEST['store'], $_REQUEST['lang'], null);
		$response = array('categories' => $content, 'number' => count($content), 'recordsFiltered' => 0, 'recordsTotal' => count($content), 'totalPages' => 1);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function pageDetail_post()
	{
		$response = $this->content->get_PageDetail($this->post());
		$this->response($response, REST_Controller::HTTP_OK);
	}
}
