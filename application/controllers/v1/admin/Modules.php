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
	public $tblTax = 'tbl_tax';
	public $tblTaxRate = 'tbl_tax_rate';

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

	public function taxClass_get($code = null)
	{
		$lang = isset($_REQUEST['lang']) ? $_REQUEST['lang'] : 'es';
		$count = isset($_REQUEST['count']) ? (int)$_REQUEST['count'] : 10;
		$page = isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 0;
		$response = $this->module->getTaxModule($lang, $count, $page, $code, $this->tblTax);
		if (!$code)
			$response = array('number' => count($response[2]), 'items' => $response[2], 'recordsFiltered' => 0, 'recordsTotal' => $response[0],  'totalPages' => $response[1]);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function taxClass_post()
	{
		$response = $this->module->addTaxModule($this->post(), $this->tblTax);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function taxClass_put($id)
	{
		$response = $this->module->updateTaxModule($this->put(), $id, $this->tblTax);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function taxClass_delete($id)
	{
		$response =	$this->common->delete_TableRecordWithCondition(array('id' => $id), $this->tblTax);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function uniqueTaxClass_get()
	{
		$where = array('code' => $_REQUEST['code']);
		$response = $this->common->get_UniqueTableRecord($where, $this->tblTax);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	
	public function taxRates_get($id = null)
	{
		$lang = isset($_REQUEST['lang']) ? $_REQUEST['lang'] : 'es';
		$count = isset($_REQUEST['count']) ? (int)$_REQUEST['count'] : 10;
		$page = isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 0;
		$response = $this->module->getTaxModule($lang, $count, $page, $id, $this->tblTaxRate);
		if (!$id)
			$response = array('number' => count($response[2]), 'items' => $response[2], 'recordsFiltered' => 0, 'recordsTotal' => $response[0],  'totalPages' => $response[1]);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function taxRate_post()
	{
		$response = $this->module->addTaxModule($this->post(), $this->tblTaxRate);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function taxRate_put($id)
	{
		$response = $this->module->updateTaxModule($this->put(), $id, $this->tblTaxRate);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function taxRate_delete($id)
	{
		$response =	$this->common->delete_TableRecordWithCondition(array('id' => $id), $this->tblTaxRate);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function uniqueTaxRate_get()
	{
		$where = array('code' => $_REQUEST['code']);
		$response = $this->common->get_UniqueTableRecord($where, $this->tblTaxRate);
		$this->response($response, REST_Controller::HTTP_OK);
	}
}
