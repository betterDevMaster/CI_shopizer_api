<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';
require APPPATH . 'helpers/authorization_helper.php';
require APPPATH . 'helpers/jwt_helper.php';

class Content extends REST_Controller
{
	public $tblContent = 'tbl_content';
	public $tblContentImages = 'tbl_content_images';

	public function __construct()
	{
		// Construct the parent class
		parent::__construct();

		// Configure limits on our controller methods
		// Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
		$this->load->model('content_model', 'content');
		$this->load->model('common_model', 'common');
	}

	public function headerMessage_get()
	{
		$response = $this->content->get_HeaderMessage($_REQUEST['lang']);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function pages_get($uniqueCode = null, $box = false)
	{
		$store = isset($_REQUEST['store']) ? $_REQUEST['store'] : 'DEFAULT';
		$lang = isset($_REQUEST['lang']) ? $_REQUEST['lang'] : 'en';
		$count = isset($_REQUEST['count']) ? (int)$_REQUEST['count'] : 10;
		$page = isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 0;
		if (!$uniqueCode) {
			$content = $this->content->get_Pages($page, $count, $store, $lang, $box);
			$response = array('items' => $content[2], 'number' => count($content[2]), 'recordsFiltered' => 0, 'recordsTotal' => $content[0], 'totalPages' => $content[1]);
		} else {
			$response = $this->content->get_PageDetail(array('contentID' => $uniqueCode), $box);
		}
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function category_get()
	{
		$store = isset($_REQUEST['store']) ? $_REQUEST['store'] : 'DEFAULT';
		$lang = isset($_REQUEST['lang']) ? $_REQUEST['lang'] : 'en';
		$count = isset($_REQUEST['count']) ? (int)$_REQUEST['count'] : 10;
		$page = isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 0;
		$content = $this->content->getCategory($page, $count, $store, $lang, null);
		$response = array('categories' => $content[2], 'number' => count($content[2]), 'recordsFiltered' => 0, 'recordsTotal' => $content[0], 'totalPages' => $content[1]);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function pageDetail_post()
	{
		$response = $this->content->get_PageDetail($this->post());
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function pageExists_get($code)
	{
		$where = array('code' => $code);
		$response = $this->common->get_UniqueTableRecord($where, $this->tblContent);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function updatePage_put($id)
	{
		$response = $this->content->updatePage($this->put(), $id, false);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function createPage_post()
	{
		$response = $this->content->createPage($this->post(), false);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function deleteContent_delete($id)
	{
		$response =	$this->common->delete_TableRecordWithCondition(array('id' => $id), $this->tblContent);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function boxes_get($uniqueCode = null)
	{
		$this->pages_get($uniqueCode, true);
	}

	public function boxExists_get($code)
	{
		$where = array('code' => $code);
		$response = $this->common->get_UniqueTableRecord($where, $this->tblContent);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function updateBox_put($id)
	{
		$response = $this->content->updatePage($this->put(), $id, true);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function createBox_post()
	{
		$response = $this->content->createPage($this->post(), true);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function deleteBox_delete($id)
	{
		$response =	$this->common->delete_TableRecordWithCondition(array('id' => $id), $this->tblContent);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function list_get()
	{
		$parentPath = isset($_REQUEST['parentPath']) ? $_REQUEST['parentPath'] : null;
		$response =	$this->common->get_TableContentWithArrayResult($this->tblContentImages);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function addImage_post()
	{
		$parentPath = isset($_REQUEST['parentPath']) ? $_REQUEST['parentPath'] : null;
		$qquuid = isset($_REQUEST['qquuid']) ? $_REQUEST['qquuid'] : null;
		$qqfilename = isset($_REQUEST['qqfilename']) ? $_REQUEST['qqfilename'] : null;
		$qqtotalfilesize = isset($_REQUEST['qqtotalfilesize']) ? $_REQUEST['qqtotalfilesize'] : null;
		if (isset($_FILES['qqfile'])) {
			$target_dir = "assets/contentImage/";
			$file_tmp = $_FILES['qqfile']['tmp_name'];
			$data = file_get_contents($file_tmp);
			$file_name = preg_replace('/\s+/', '', basename($_FILES["qqfile"]["name"]));

			$target_file = $target_dir . $file_name;

			if (!file_exists($target_dir)) {
				mkdir($target_dir, 0777, true);
			}

			if (!file_exists($target_file)) {
				if (move_uploaded_file($_FILES["qqfile"]["tmp_name"], $target_file)) {
					$response =	$this->content->addImage($parentPath, $qquuid, $qqfilename, $qqtotalfilesize, $target_file);
				} else {
					$response = array('success' => false, 'error' => 'File Existed', 'preventRetry' => false);
				}
			} else {
				$response = array('success' => true, 'error' => null, 'preventRetry' => true);
			}
		}

		$this->response($response, REST_Controller::HTTP_OK);
	}
}
