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
	public $tblOptionValue = 'tbl_option_values';
	public $tblProperty = 'tbl_property';
	public $tblProductGroups = 'tbl_product_groups';
	public $tblDescription = 'tbl_description';
	public $tblProducts = 'tbl_products';
	public $tblImage = 'tbl_image';
	public $tblPropertyVariation = 'tbl_property_variation';

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

	public function uniqueProduct_get()
	{
		$where = array('identifier' => $_REQUEST['code']);
		$response = $this->common->get_UniqueTableRecord($where, $this->tblProducts);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function deleteProduct_delete($id)
	{
		$response =	$this->common->delete_TableRecordWithCondition(array('id' => $id), $this->tblProducts);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function definition_post()
	{
		$response =	$this->product->createProduct($this->post());
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function definition_put()
	{
		$response =	$this->product->updateProduct($this->put());
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function addImage_post($id)
	{
		$response = array('status' => false, 'message' => 'image open failed');
		if (isset($_FILES['file'])) {
			$file_tmp = $_FILES['file']['tmp_name'];
			$data = file_get_contents($file_tmp);
			$file_name = preg_replace('/\s+/', '', basename($_FILES["file"]["name"]));
			$response =	$this->product->addImage($file_name, base64_encode($data), $id);
			$this->response($response, REST_Controller::HTTP_OK);
		} else
			$this->response($response, REST_Controller::HTTP_NOT_FOUND);
	}

	public function removeImage_delete($productId, $imageId)
	{
		$this->product->removeImage($productId, $imageId);
		$response =	$this->common->delete_TableRecordWithCondition(array('id' => $imageId), $this->tblImage);
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
		$response =	$this->common->delete_TableRecordWithCondition(array('id' => $_REQUEST['id']), $this->tblOptions);
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

	public function attribute_post($productId)
	{
		$response  = $this->product->createAttribute($this->post(), $productId);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function attribute_get($productId, $attributeId)
	{
		$response  = $this->product->getAttributesById($this->post(), $productId);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function attributes_put($productId, $attributeId)
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
		$response =	$this->common->delete_TableRecordWithCondition(array('id' => $_REQUEST['id']), $this->tblOptionValue);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function createOptionValue_post()
	{
		$response = $this->product->createOptionValue($this->post());
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
		$response = array('status' => false, 'message' => 'image open failed');
		if (isset($_FILES['file'])) {
			$file_tmp = $_FILES['file']['tmp_name'];
			$data = file_get_contents($file_tmp);
			$file_name = preg_replace('/\s+/', '', basename($_FILES["file"]["name"]));
			$this->product->createImage($file_name, base64_encode($data), $optionValueId);
			$this->response($response, REST_Controller::HTTP_OK);
		} else
			$this->response($response, REST_Controller::HTTP_NOT_FOUND);
	}

	public function deleteImage_delete()
	{
		$response =	$this->common->delete_TableRecordWithCondition(array('id' => $_REQUEST['id']), $this->tblOptionValue);
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
		$response =	$this->common->delete_TableRecordWithCondition(array('id' => $id), $this->tblProperty);
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

	public function getType_get($id)
	{
		// $lang = isset($_REQUEST['lang']) ? $_REQUEST['lang'] : 'en';
		// $store = isset($_REQUEST['store']) ? $_REQUEST['store'] : 'DEFAULT';
		// $response  = $this->common->get_TableContentWithRowResultAndCondition(array('id' => $id), $this->tblPropertyType);
		// $response['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $response['descriptions']);
		// $response['description'] = count($response['descriptions']) > 0 && $response['descriptions'][0] ? $response['descriptions'][0] : null;
		// $this->response($response, REST_Controller::HTTP_OK);

		$lang = isset($_REQUEST['lang']) ? $_REQUEST['lang'] : 'en';
		$store = isset($_REQUEST['store']) ? $_REQUEST['store'] : 'DEFAULT';
		$response  = $this->product->getTypeById($id, $lang, $store);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function createType_post()
	{
		$response = $this->product->createType($this->post());
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function updateType_put($id)
	{
		$response  = $this->product->updateType($this->put(), $id);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function deleteType_delete($id)
	{
		$response =	$this->common->delete_TableRecordWithCondition(array('id' => $id), $this->tblPropertyType);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function uniqueType_get()
	{
		$where = array('code' => $_REQUEST['code']);
		$response = $this->common->get_UniqueTableRecord($where, $this->tblPropertyType);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	// Product / Variation	
	public function uniqueVariation_get()
	{
		$where = array('code' => $_REQUEST['code']);
		$response = $this->common->get_UniqueTableRecord($where, $this->tblPropertyVariation);
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

	public function deleteVariation_delete($id)
	{
		$response =	$this->common->delete_TableRecordWithCondition(array('id' => $id), $this->tblPropertyVariation);
		$this->response($response, REST_Controller::HTTP_OK);
	}


	// Product / Manufacturer	
	public function manufacturer_get()
	{
		$store = isset($_REQUEST['store']) ? $_REQUEST['store'] : 'DEFAULT';
		$lang = isset($_REQUEST['lang']) ? $_REQUEST['lang'] : 'en';
		$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 0;
		$count = isset($_REQUEST['count']) ? $_REQUEST['count'] : 10;

		$manufacturers  = $this->product->get_Manufacturers($store, $lang, $page, $count);
		$response = array('number' => count($manufacturers), 'manufacturers' => $manufacturers, 'recordsFiltered' => 0, 'recordsTotal' => count($manufacturers),  'totalPages' => 1);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function getManufacturer_get($id)
	{
		$lang = isset($_REQUEST['lang']) ? $_REQUEST['lang'] : 'en';
		$response  = $this->product->get_ManufacturerById($id, $lang);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function updateManufacturer_put($id)
	{
		$response  = $this->product->updateManufacturer($this->put(), $id);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function createManufacturer_post()
	{
		$response = $this->product->createManufacturer($this->post());
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function deleteManufacturer_delete($id)
	{
		$response =	$this->common->delete_TableRecordWithCondition(array('id' => $id), $this->tblManufacturer);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function uniqueManufacturer_get()
	{
		$where = array('code' => $_REQUEST['code']);
		$response = $this->common->get_UniqueTableRecord($where, $this->tblManufacturer);
		$this->response($response, REST_Controller::HTTP_OK);
	}


	// Product / Groups	
	public function groups_get()
	{
		$response = $this->common->get_TableContentWithArrayResult($this->tblProductGroups);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function getProductsByGroup_get($groupCode)
	{
		$products = $this->product->get_ProductsByGroup($groupCode);
		$response = array('number' => count($products), 'products' => $products, 'recordsFiltered' => 0, 'recordsTotal' => count($products),  'totalPages' => 1);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function addProductToGroup_post($productId, $groupCode)
	{
		$response = $this->product->addProductToGroup($productId, $groupCode);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function removeProductFromGroup_delete($productId, $groupCode)
	{
		$response = $this->product->removeProductFromGroup($productId, $groupCode);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function deleteProductGroup_delete($groupCode)
	{
		$response =	$this->common->delete_TableRecordWithCondition(array('code' => $groupCode), $this->tblManufacturer);
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function createProductGroup_post()
	{
		$response =	$this->product->createProductGroup($this->post());
		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function updateGroupActiveValue_post($groupCode)
	{
		$response =	$this->product->updateGroupActiveValue($this->post(), $groupCode);
		$this->response($response, REST_Controller::HTTP_OK);
	}
}
