<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';
require APPPATH . 'helpers/authorization_helper.php';
require APPPATH . 'helpers/jwt_helper.php';

class Modules extends REST_Controller
{
	public $tblMethods = 'tbl_modules';
	public $tblShippingOrigin = 'tbl_shipping_origin';
	public $tblShippingPackages = 'tbl_shipping_packages';

	public function __construct()
	{
		// Construct the parent class
		parent::__construct();

		// Configure limits on our controller methods
		// Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
		$this->load->model('module_model', 'module');
		$this->load->model('common_model', 'common');
	}

	public function expedition_get()
	{
		$store = isset($_REQUEST['store']) ? $_REQUEST['store'] : null;
		$expeditions = $this->module->getExpedition($store);
		$response = array('iternationalShipping' => true, 'shipToCountry' => $expeditions, 'taxOnShipping' => false);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function expedition_post()
	{
		$store = isset($_REQUEST['store']) ? $_REQUEST['store'] : null;
		$expeditions = $this->module->updateExpedition($this->post(), $store);
		$response = array('iternationalShipping' => true, 'shipToCountry' => $expeditions, 'taxOnShipping' => false);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function shippingMethods_get($type = null)
	{
		if (!$type)
			$response = $this->common->get_TableContentWithArrayResultAndCondition(array('mode' => 'shipping'), $this->tblMethods);
		else
			$response = $this->module->getShippingMethods($type);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function shippingMethods_post()
	{
		$response = $this->module->updateShippingMethods($this->post());
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function shippingOrigin_get()
	{
		$store = isset($_REQUEST['store']) ? $_REQUEST['store'] : null;
		$recordsTotal = $this->db->from($this->tblShippingOrigin)->count_all_results();
		if ($recordsTotal == 0)
			$this->db->insert($this->tblShippingOrigin, array('active' => 1));

		$response = $this->common->get_TableContentWithRowResult($this->tblShippingOrigin);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function shippingOrigin_post()
	{
		$response = $this->module->updateShippingOrigin($this->post());
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function payment_get($type = null)
	{
		if (!$type)
			$response = $this->common->get_TableContentWithArrayResultAndCondition(array('mode' => 'payment'), $this->tblMethods);
		else
			$response = $this->module->getShippingMethods($type);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function payment_post()
	{
		$response = $this->module->updateShippingMethods($this->post());
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function shippingPackages_get($code = null)
	{
		if (!$code)
			$response = $this->common->get_TableContentWithArrayResult($this->tblShippingPackages);
		else
			$response = $this->common->get_TableContentWithRowResultAndCondition(array('code' => $code), $this->tblShippingPackages);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function shippingPackage_post()
	{
		$response = $this->module->addShippingPackage($this->post());
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function shippingPackage_put()
	{
		$response = $this->module->updateShippingPackage($this->put());
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function shippingPackage_delete($code = null)
	{
		$response =	$this->common->delete_TableRecordWithCondition(array('code' => $code), $this->tblShippingPackages);
		$this->response($response, REST_Controller::HTTP_OK);
	}
}
