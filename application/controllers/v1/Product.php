<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';
require APPPATH . 'helpers/authorization_helper.php';
require APPPATH . 'helpers/jwt_helper.php';

class Product extends REST_Controller
{
	public $tblPropertyType = 'tbl_property_type';
	public $tblManufacturer = 'tbl_manufacturer';
	public $tblOptions = 'tbl_options';
	public $tblOptionValue = 'tbl_option_value';
	public $tblProperty = 'tbl_property';

	public function __construct()
	{
		// Construct the parent class
		parent::__construct();

		// Configure limits on our controller methods
		// Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
		$this->load->model('product_model', 'product');
		$this->load->model('common_model', 'common');
	}


	// Product / Products	
	public function featuredItem_get()
	{
		$products = $this->product->get_FeaturedItem($_REQUEST['store'], $_REQUEST['lang']);
		$response = array('number' => count($products), 'products' => $products, 'recordsFiltered' => 0, 'recordsTotal' => count($products),  'totalPages' => 1);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function price_post()
	{
		$response = $this->product->get_Price($this->post());
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function productList_post()
	{
		$products = $this->product->get_ProductList($this->post());
		$response = array('number' => count($products), 'products' => $products, 'recordsFiltered' => 0, 'recordsTotal' => count($products),  'totalPages' => 1);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function productDetail_post()
	{
		$response = $this->product->get_ProductDetail($this->post());
		$this->response($response[0], REST_Controller::HTTP_OK);
	}

	public function productReview_post()
	{
		$response = $this->product->get_ProductReview($this->post());
		$this->response($response, REST_Controller::HTTP_OK);
	}


	// Product / Options	
	public function options_get()
	{
		$count = isset($_REQUEST['count']) ? $_REQUEST['count'] : 10;
		$store = isset($_REQUEST['store']) ? $_REQUEST['store'] : 'DEFAULT';
		$lang = isset($_REQUEST['lang']) ? $_REQUEST['lang'] : 'en';
		$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 0;
		$options  = $this->product->get_Options($count, $store, $lang, $page);
		$response = array('options' => $options, 'number' => count($options), 'recordsFiltered' => 0, 'recordsTotal' => count($options), 'totalPages' => ceil(count($options) / $count));
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function getOption_get()
	{
		$count = isset($_REQUEST['count']) ? $_REQUEST['count'] : 10;
		$lang = isset($_REQUEST['lang']) ? $_REQUEST['lang'] : 'en';
		$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : 0;
		$response  = $this->product->get_OptionsById($count, $lang, $id);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function updateOption_put()
	{
		$response  = $this->product->updateOption($this->put());
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function deleteOption_delete()
	{
		$response =	$this->common->delete_TableRecord($_REQUEST['id'], $this->tblOptions);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function createOption_post()
	{
		$response = $this->product->createOption($this->post());
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function uniqueOption_get()
	{
		$where = array('code' => $_REQUEST['code']);
		$response = $this->common->get_UniqueTableRecord($where, $this->tblOptions);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function attributes_get()
	{
		$count = isset($_REQUEST['count']) ? $_REQUEST['count'] : 10;
		$store = isset($_REQUEST['store']) ? $_REQUEST['store'] : 'DEFAULT';
		$lang = isset($_REQUEST['lang']) ? $_REQUEST['lang'] : 'en';
		$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 0;
		$productId = isset($_REQUEST['productId']) ? $_REQUEST['productId'] : 0;
		$attributes  = $this->product->get_Attributes($productId, $count, $store, $lang, $page);
		$response = array('attributes' => $attributes, 'number' => count($attributes), 'recordsFiltered' => 0, 'recordsTotal' => count($attributes), 'totalPages' => ceil(count($attributes) / $count));
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function manufacturer_get()
	{
		$manufacturers  = $this->common->get_TableContentWithArrayResult($this->tblManufacturer);
		$response = array('number' => count($manufacturers), 'manufacturers' => $manufacturers, 'recordsFiltered' => 0, 'recordsTotal' => count($manufacturers),  'totalPages' => 1);
		$this->response($response, REST_Controller::HTTP_OK);
	}


	// Product / optionValues	
	public function optionValues_get()
	{
		$count = isset($_REQUEST['count']) ? $_REQUEST['count'] : 15;
		$store = isset($_REQUEST['store']) ? $_REQUEST['store'] : 'DEFAULT';
		$lang = isset($_REQUEST['lang']) ? $_REQUEST['lang'] : 'en';
		$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 0;
		$optionValues  = $this->product->get_OptionValues($count, $store, $lang, $page);
		$response = array('optionValues' => $optionValues, 'number' => count($optionValues), 'recordsFiltered' => 0, 'recordsTotal' => count($optionValues), 'totalPages' => ceil(count($optionValues) / $count));
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function uniqueOptionValue_get()
	{
		$where = array('code' => $_REQUEST['code']);
		$response = $this->common->get_UniqueTableRecord($where, $this->tblOptionValue);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function updateOptionValue_put()
	{
		$response  = $this->product->updateOptionValue($this->put());
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function deleteOptionValue_delete()
	{
		$response =	$this->common->delete_TableRecord($_REQUEST['id'], $this->tblOptionValue);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function createOptionValue_post()
	{
		$response = $this->product->createValueOption($this->post());
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function getOptionValue_get()
	{
		$count = isset($_REQUEST['count']) ? $_REQUEST['count'] : 10;
		$lang = isset($_REQUEST['lang']) ? $_REQUEST['lang'] : 'en';
		$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : 0;
		$response  = $this->product->get_OptionValueById($count, $lang, $id);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function createImage_post($optionValueId = 0)
	{
		if (isset($_FILES['file'])) {
			$target_dir = "assets/optionValue/";
			$file_tmp = $_FILES['file']['tmp_name'];
			$data = file_get_contents($file_tmp);
			$file_name = preg_replace('/\s+/', '', basename($_FILES["file"]["name"]));

			$target_file = $target_dir . $file_name;

			if (!file_exists($target_dir)) {
				mkdir($target_dir, 0777, true);
			}

			if (!file_exists($target_file)) {
				if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
					$this->product->createImage($optionValueId, $target_file);
					$response = array('status' => true, 'msg' => 'Image uploaded to Server');
				} else {
					$response = array('status' => false, 'msg' => 'Image cannot upload to the server');
				}
			} else {
				$response = array('status' => true, 'msg' => 'Image uploaded to Server');
			}
		}
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function deleteImage_delete()
	{
		$response =	$this->common->delete_TableRecord($_REQUEST['id'], $this->tblOptionValue);
		$this->response($response, REST_Controller::HTTP_OK);
	}


	// Product / Property	
	public function uniqueProperty_get()
	{
		$where = array('code' => $_REQUEST['code']);
		$response = $this->common->get_UniqueTableRecord($where, $this->tblProperty);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function getProperty_get()
	{
		$store = isset($_REQUEST['store']) ? $_REQUEST['store'] : 'DEFAULT';
		$lang = isset($_REQUEST['lang']) ? $_REQUEST['lang'] : 'en';
		$response  = $this->product->get_Property($store, $lang);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function createProperty_post()
	{
		$response = $this->product->createProperty($this->post());
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function deleteProperty_delete($id)
	{
		$response =	$this->common->delete_TableRecord($id, $this->tblProperty);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function getPropertyValue_get($id)
	{
		$lang = isset($_REQUEST['lang']) ? $_REQUEST['lang'] : 'en';
		$store = isset($_REQUEST['store']) ? $_REQUEST['store'] : 'DEFAULT';
		$response  = $this->product->get_PropertyValueById($id, $lang, $store);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function updateProperty_put($id)
	{
		$response  = $this->product->updateProperty($this->put(), $id);
		$this->response($response, REST_Controller::HTTP_OK);
	}


	// Product / Property Type
	public function types_get()
	{
		$types  = $this->common->get_TableContentWithArrayResult($this->tblPropertyType);
		$response = array('list' => $types, 'number' => count($types), 'recordsFiltered' => 0, 'recordsTotal' => count($types), 'totalPages' => 1);
		$this->response($response, REST_Controller::HTTP_OK);
	}


	// Product / Variation	
	public function uniqueVariation_get()
	{
		$where = array('code' => $_REQUEST['code']);
		$response = $this->common->get_UniqueTableRecord($where, $this->tblProperty);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function getVariation_get()
	{
		$store = isset($_REQUEST['store']) ? $_REQUEST['store'] : 'DEFAULT';
		$lang = isset($_REQUEST['lang']) ? $_REQUEST['lang'] : 'en';
		$count = isset($_REQUEST['count']) ? $_REQUEST['count'] : 10;
		$items  = $this->product->get_Variation($store, $lang);
		$response = array('items' => $items, 'number' => count($items), 'recordsFiltered' => 0, 'recordsTotal' => count($items), 'totalPages' => ceil(count($items) / $count));
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function createVariation_post()
	{
		$response = $this->product->createVariation($this->post());
		$this->response($response, REST_Controller::HTTP_OK);
	}
}
