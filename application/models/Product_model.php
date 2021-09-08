<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Product_model extends CI_Model
{
	public $tblProducts = 'tbl_products';
	public $tblCategories = 'tbl_categories';
	public $tblDescription = 'tbl_description';
	public $tblImage = 'tbl_image';
	public $tblManufacturer = 'tbl_manufacturer';
	public $tblOptions = 'tbl_options';
	public $tblOptionValue = 'tbl_option_values';
	public $tblProductPrice = 'tbl_product_price';
	public $tblProductSpecification = 'tbl_product_specification';
	public $tblProductGroups = 'tbl_product_groups';
	public $tblProperties = 'tbl_properties';
	public $tblProperty = 'tbl_property';
	public $tblPropertyValue = 'tbl_property_value';
	public $tblPropertyType = 'tbl_property_type';
	public $tblProductUnit = 'tbl_product_unit';
	public $tblPropertyVariation = 'tbl_property_variation';
	public $tblReview = 'tbl_review';
	public $tblUserBilling = 'tbl_user_billing';
	public $tblUserDelivery = 'tbl_user_delivery';
	public $tblAttributes = 'tbl_attributes';
	public $tblSupportedLanguages = 'tbl_supported_languages';
	public $tblCartItemAttributes = 'tbl_cart_item_attributes';

	public function __construct()
	{
		parent::__construct();
	}

	function getFeaturedItem($count, $store, $lang, $page)
	{
		$products = $this->db->limit($count, $count * $page)->get($this->tblProducts)->result_array();

		foreach ($products as $k1 => $v1) {
			$products[$k1] = $this->getDetailedProduct($products[$k1], $count, $store, $lang, $page);
		}

		$recordsTotal = $this->db->from($this->tblProducts)->count_all_results();
		$totalPages = ceil($recordsTotal / $count);
		$products = array($recordsTotal, $totalPages, $products);
		return $products;
	}

	function getBestSellersItem($count, $store, $lang, $page)
	{
		$products = $this->db->order_by("preOrder", "desc")->limit(15)->get($this->tblProducts)->result_array();
		foreach ($products as $k1 => $v1) {
			$products[$k1] = $this->getDetailedProduct($products[$k1], $count, $store, $lang, $page);
		}
		return $products;
	}

	function getDetailedProduct($product, $count, $store, $lang, $page)
	{
		if (!$product['attributes']) $product['attributes'] = array();

		// Category
		$category = $this->db->select('*')->get_where($this->tblCategories, array('id' => $product['category']))->row_array();
		$category['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $category['descriptions']);
		$newArr = customFilterArray($category['descriptions'], $lang);
		$category['description'] = count($newArr) > 0 && $newArr[0] ? $newArr[0] : null;
		$product['category'] = $category;

		// CartItemattributes
		$product['cartItemattributes'] = GetTableDetails($this, $this->tblCartItemAttributes, 'id', $product['cartItemattributes']);
		foreach ($product['cartItemattributes'] as $k9 => $v9) {
			$product['cartItemattributes'][$k9]['option'] = $this->calcOption($v9['option'], $lang);
			$product['cartItemattributes'][$k9]['optionValue'] = $this->calcOptionValue($v9['optionValue'], $lang);
		}

		// Description
		$product['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $product['descriptions']);
		$newArr = customFilterArray($product['descriptions'], $lang);
		$product['description'] = count($newArr) > 0 && $newArr[0] ? $newArr[0] : null;

		// Image
		$product['image'] = $this->db->select('*')->get_where($this->tblImage, array('id' => $product['image']))->row_array();

		// Images
		$imageList = explode(',', $product['images']);
		$product['images'] = array();
		foreach ($imageList as $k4 => $v4) {
			if (!$v4) continue;
			$image = $this->db->select('*')->get_where($this->tblImage, array('id' => $v4))->row_array();
			array_push($product['images'], $image);
		}

		// Manufacturer
		$manufacturer = $this->db->select('*')->get_where($this->tblManufacturer, array('id' => $product['manufacturer']))->row_array();
		$manufacturer['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $manufacturer['descriptions']);
		$newArr = customFilterArray($manufacturer['descriptions'], $lang);
		$manufacturer['description'] = count($newArr) > 0 && $newArr[0] ? $newArr[0] : null;
		$product['manufacturer'] = $manufacturer;

		// Options
		$optionList = explode(',', $product['options']);
		$product['options'] = array();
		foreach ($optionList as $k5 => $v5) {
			if (!$v5) continue;
			$product['options'] = $this->calcOption($v5, $lang);
		}

		// Product Price
		$productPrice = $this->db->select('*')->get_where($this->tblProductPrice, array('id' => $product['productPrice']))->row_array();
		$productPrice['description'] = $this->db->select('*')->get_where($this->tblDescription, array('id' => $productPrice['description']))->row_array();
		$product['productPrice'] = $productPrice;

		// Product Specification
		$productSpecification = $this->db->select('*')->get_where($this->tblProductSpecification, array('id' => $product['productSpecifications']))->row_array();
		$product['productSpecifications'] = $productSpecification;

		// Properties
		$propertyList = explode(',', $product['properties']);
		$product['properties'] = array();
		foreach ($propertyList as $k8 => $v8) {
			if (!$v8) continue;
			$properties = $this->db->select('*')->get_where($this->tblProperties, array('id' => $v8))->row_array();
			$property = $this->db->select('*')->get_where($this->tblProperty, array('id' => $properties['property']))->row_array();
			if (!$property['optionValues']) $property['optionValues'] = array();
			$propertyValue = $this->db->select('*')->get_where($this->tblPropertyValue, array('id' => $properties['propertyValue']))->row_array();
			if (!$propertyValue['values']) $propertyValue['values'] = array();
			$properties['property'] = $property;
			$properties['propertyValue'] = $propertyValue;
			array_push($product['properties'], $properties);
		}

		// Type
		$propertyType = $this->db->select('*')->get_where($this->tblPropertyType, array('id' => $product['type']))->row_array();
		$propertyType['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $propertyType['descriptions']);
		$newArr = customFilterArray($propertyType['descriptions'], $lang);
		$propertyType['description'] = count($newArr) > 0 && $newArr[0] ? $newArr[0] : null;
		$product['type'] = $propertyType;

		// Unit
		$propertyUnit = $this->db->select('*')->get_where($this->tblProductUnit, array('id' => $product['unit']))->row_array();
		$propertyUnit['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $propertyUnit['descriptions']);
		$newArr = customFilterArray($propertyUnit['descriptions'], $lang);
		$propertyUnit['description'] = count($newArr) > 0 && $newArr[0] ? $newArr[0] : null;
		$product['unit'] = $propertyUnit;

		return $product;
	}

	function createProduct($pData)
	{
		$descriptions = '';
		foreach ($pData['descriptions'] as $k => $v) {
			unset($v['id']);
			$this->db->insert($this->tblDescription, $v);
			$insertId = $this->db->insert_id();
			$descriptions = $descriptions . $insertId . ',';
		}

		if ($pData['manufacturer'] != '')
			$manufacturerId = $this->db->select('*')->get_where($this->tblManufacturer, array('code' => $pData['manufacturer']))->row_array()['id'];
		if ($pData['type'] != '')
			$typeId = $this->db->select('*')->get_where($this->tblPropertyType, array('code' => $pData['type']))->row_array()['id'];
		if ($pData['unit'] != '')
			$unitId = $this->db->select('*')->get_where($this->tblProductUnit, array('code' => $pData['unit']))->row_array()['id'];

		$productSpecificationId = null;
		if (
			$pData['productSpecifications']['height'] != '' && $pData['productSpecifications']['length'] != ''
			&& $pData['productSpecifications']['weight'] != '' && $pData['productSpecifications']['width'] != ''
		) {
			$data = array(
				'height' => $pData['productSpecifications']['height'],
				'length' => $pData['productSpecifications']['length'],
				'weight' => $pData['productSpecifications']['weight'],
				'width' => $pData['productSpecifications']['width']
			);
			$this->db->insert($this->tblProductSpecification, $data);
			$productSpecificationId = $this->db->insert_id();
		}

		$data = array(
			'canBePurchased' => $pData['canBePurchased'],
			'capacity' => $pData['capacity'],
			'dateAvailable' => $pData['dateAvailable'],
			'descriptions' => $descriptions,
			'discounted' => $pData['discounted'],
			'identifier' => $pData['identifier'],
			'manufacturer' => (int)$manufacturerId,
			'originalPrice' => $pData['price'],
			'price' => $pData['price'],
			'productSpecifications' => $productSpecificationId,
			'quantity' => $pData['quantity'],
			'type' => (int)$typeId,
			'unit' => (int)$unitId,
			'visible' => $pData['visible'],
		);

		if ($pData['discounted'])
			$data['finalPrice'] = $pData['finalPrice'];
		else
			$data['finalPrice'] = $pData['price'];

		$this->db->insert($this->tblProducts, $data);
		return $pData;
	}

	function updateProduct($pData)
	{
		DeleteDescriptionsInTableWithCondition($this, $this->tblProducts, array('identifier' => $pData['identifier']));

		$descriptions = '';
		foreach ($pData['descriptions'] as $k => $v) {
			unset($v['id']);
			$this->db->insert($this->tblDescription, $v);
			$insertId = $this->db->insert_id();
			$descriptions = $descriptions . $insertId . ',';
		}

		if ($pData['manufacturer'] != '')
			$manufacturerId = $this->db->select('*')->get_where($this->tblManufacturer, array('code' => $pData['manufacturer']))->row_array()['id'];
		if ($pData['type'] != '')
			$typeId = $this->db->select('*')->get_where($this->tblPropertyType, array('code' => $pData['type']))->row_array()['id'];
		if ($pData['unit'] != '')
			$unitId = $this->db->select('*')->get_where($this->tblProductUnit, array('code' => $pData['unit']))->row_array()['id'];

		$productSpecificationId = null;
		if (
			$pData['productSpecifications']['height'] != '' && $pData['productSpecifications']['length'] != ''
			&& $pData['productSpecifications']['weight'] != '' && $pData['productSpecifications']['width'] != ''
		) {
			$data = array(
				'height' => $pData['productSpecifications']['height'],
				'length' => $pData['productSpecifications']['length'],
				'weight' => $pData['productSpecifications']['weight'],
				'width' => $pData['productSpecifications']['width']
			);
			$this->db->insert($this->tblProductSpecification, $data);
			$productSpecificationId = $this->db->insert_id();
		}

		$data = array(
			'canBePurchased' => $pData['canBePurchased'],
			'capacity' => $pData['capacity'],
			'dateAvailable' => $pData['dateAvailable'],
			'descriptions' => $descriptions,
			'discounted' => $pData['discounted'],
			'identifier' => $pData['identifier'],
			'manufacturer' => (int)$manufacturerId,
			'originalPrice' => $pData['price'],
			'price' => $pData['price'],
			'productSpecifications' => $productSpecificationId,
			'quantity' => $pData['quantity'],
			'type' => (int)$typeId,
			'unit' => (int)$unitId,
			'visible' => $pData['visible'],
		);

		if ($pData['discounted'])
			$data['finalPrice'] = $pData['finalPrice'];
		else
			$data['finalPrice'] = $pData['price'];

		$this->db->where(array('identifier' => $pData['identifier']))->update($this->tblProducts, $data);
		return true;
	}

	function updateProductCategory($productId, $categoryId)
	{
		$this->db->where(array('id' => $productId))->update($this->tblProducts, array('category' => $categoryId));
		return true;
	}

	function addImage($file_name, $target_file, $id)
	{
		$this->db->insert($this->tblImage, array('imageName' => $file_name, 'imageUrl' => '/' . $target_file));
		$insertId = $this->db->insert_id();

		$product = $this->db->select('image, images')->get_where($this->tblProducts, array('id' => $id))->row_array();
		$productImages = $product['images'] . $insertId . ',';
		if (!$product['image'])
			$where = array('image' => $insertId, 'images' => $productImages);
		else
			$where = array('images' => $productImages);

		$this->db->where(array('id' => $id))->update($this->tblProducts, $where);
		return true;
	}

	function removeImage($productId, $imageId)
	{
		$productImages = $this->db->select('*')->get_where($this->tblProducts, array('id' => $productId))->row_array()['images'];
		$images = str_replace($imageId . ',', '', $productImages);
		if ($images == '' || !$images)
			$data = array('images' => null, 'image' => null);
		else {
			$image = explode(',', $images);
			$data = array('images' => $images, 'image' => $image[0]);
		}
		$this->db->where(array('id' => $productId))->update($this->tblProducts, $data);
	}

	function calcOption($id, $lang)
	{
		$optionsArr = array();
		$options = $this->db->select('*')->get_where($this->tblOptions, array('id' => $id))->row_array();
		$options['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $options['descriptions']);
		$newArr = customFilterArray($options['descriptions'], $lang);
		$options['description'] = count($newArr) > 0 && $newArr[0] ? $newArr[0] : null;

		array_push($optionsArr, $options);
		return $optionsArr;
	}

	function calcOptionValue($id, $lang)
	{
		$optionValueArr = array();
		$optionValues = $this->db->select('*')->get_where($this->tblOptionValue, array('id' => $id))->row_array();
		$optionValues['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $optionValues['descriptions']);
		$newArr = customFilterArray($optionValues['descriptions'], $lang);
		$optionValues['description'] = count($newArr) > 0 && $newArr[0] ? $newArr[0] : null;
		array_push($optionValueArr, $optionValues);
		return $optionValueArr;
	}

	function get_Price($pData, $lang)
	{
		$products = $this->db->select('id, descriptions, discounted, finalPrice, originalPrice')->get_where($this->tblProducts, array('id' => $pData['id']))->row_array();
		$products['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $products['descriptions']);
		$newArr = customFilterArray($products['descriptions'], $lang);
		$products['description'] = count($newArr) > 0 && $newArr[0] ? $newArr[0] : null;
		return $products;
	}

	function getProductLists($count, $store, $lang, $page, $categoryId = null, $manufacturerId = null, $promo = false)
	{
		$ids = $this->getCategoryIDsWithChildByParent($categoryId);
		if (count($ids) != 0) {
			$productList = array();
			foreach ($ids as $v) {
				$productList = array_merge($productList, $this->getProductList($count, $store, $lang, $page, $v['id'], $manufacturerId, $promo));
			}
		} else {
			$productList = $this->getProductList($count, $store, $lang, $page, $categoryId, $manufacturerId, $promo);
		}
		
		$products = array_slice($productList, $count * $page, $count);
		$recordsTotal = count($productList);
		$totalPages = ceil($recordsTotal / $count);
		$products = array($recordsTotal, $totalPages, $products);
		return $products;
	}

	function getCategoryIDsWithChildByParent($id = 0)
	{
		$category = $this->db->select('id')->get_where($this->tblCategories, array('parent' => $id))->result_array();
		for ($i = 0; $i < count($category); $i++) {
			$category = array_merge($category, $this->getCategoryIDsWithChildByParent($category[$i]['id']));
		}
		return $category;
	}

	function getProductList($count, $store, $lang, $page, $categoryId = null, $manufacturerId = null, $promo = false)
	{
		if ($categoryId) {
			$where = array('category' => $categoryId);
			if ($promo) $where = array('category' => $categoryId, 'discounted' => true);
			$products = $this->db->get_where($this->tblProducts, $where)->result_array();
		} else
			$products = $this->db->get($this->tblProducts)->result_array();

		$productList = array();
		if ($manufacturerId) {
			$manufacturerList = explode(',', $manufacturerId);
			foreach ($manufacturerList as $k => $v) {
				if (!$v) continue;

				$new = array_filter($products, function ($var) use ($v) {
					return ($var['manufacturer'] == $v);
				});
				$productList = array_merge($productList, $new);
			}
			$products = $productList;
		}

		foreach ($products as $k1 => $v1) {
			$products[$k1] = $this->getDetailedProduct($products[$k1], $count, $store, $lang, $page);
		}

		return $products;
	}

	function getProductDetail($count, $store, $lang, $page, $productId)
	{
		$product = $this->db->get_where($this->tblProducts, array('id' => $productId))->row_array();
		$product = $this->getDetailedProduct($product, $count, $store, $lang, $page);
		return $product;
	}

	function searchProductList($count, $store, $lang, $page, $key)
	{
		$this->db->where("identifier LIKE '%$key%'");
		$products = $this->db->limit($count, $count * $page)->get($this->tblProducts)->result_array();
		foreach ($products as $k1 => $v1) {
			$products[$k1] = $this->getDetailedProduct($products[$k1], $count, $store, $lang, $page);
		}

		$productCount = $this->db->from($this->tblProducts)->where("identifier LIKE '%$key%'")->count_all_results();
		$totalPages = ceil($productCount / $count);
		$products = array($productCount, $totalPages, $products);
		return $products;
	}

	function get_ProductReview($pData)
	{
		$reviews = $this->db->select('*')->get_where($this->tblReview, array('productId' => $pData['productId']))->result_array();
		// foreach ($reviews as $k => $v) {
		// 	$reviews[$k]['customer'] = $this->db->select('*')->get_where($this->tblProperties, array('id' => $pData['userId']))->row_array();
		// 	$reviews[$k]['customer']['billing'] = $this->db->select('*')->get_where($this->tblUserBilling, array('userId' => $pData['userId']))->row_array();
		// 	$reviews[$k]['customer']['delivery'] = $this->db->select('*')->get_where($this->tblUserDelivery, array('userId' => $pData['userId']))->row_array();
		// }
		return $reviews;
	}

	function get_Options($count = null, $store = null, $lang = null, $page = null)
	{
		$options = $this->db->select('*')->limit($count, $count * $page)->get_where($this->tblOptions, array('store' => $store))->result_array();
		foreach ($options as $k5 => $v5) {
			if (!$v5) continue;
			$options[$k5]['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $options[$k5]['descriptions']);
			$newArr = customFilterArray($options[$k5]['descriptions'], $lang);
			$options[$k5]['description'] = count($newArr) > 0 && $newArr[0] ? $newArr[0] : null;
		}
		$recordsTotal = $this->db->from($this->tblOptions)->count_all_results();
		$totalPages = ceil($recordsTotal / $count);
		$result = array($recordsTotal, $totalPages, $options);
		return $result;
	}

	function get_OptionsById($count = null, $lang = null, $id = null)
	{
		$options = $this->db->select('*')->limit($count)->get_where($this->tblOptions, array('id' => $id))->row_array();
		$options['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $options['descriptions']);
		$newArr = customFilterArray($options['descriptions'], $lang);
		$options['description'] = count($newArr) > 0 && $newArr[0] ? $newArr[0] : null;
		return $options;
	}

	function updateOption($pData)
	{
		DeleteDescriptionsInTableWithCondition($this, $this->tblOptions, array('id' => $pData['id']));

		$descriptions = '';
		foreach ($pData['descriptions'] as $k => $v) {
			unset($v['id']);
			$this->db->insert($this->tblDescription, $v);
			$insertId = $this->db->insert_id();
			$descriptions = $descriptions . $insertId . ',';
		}

		$where = array('id' => $pData['id']);
		$data = array(
			'code' => $pData['code'],
			'selectedLanguage' => $pData['selectedLanguage'],
			'type' => $pData['type'],
			'descriptions' => $descriptions,
		);
		$this->db->where($where)->update($this->tblOptions, $data);
		return true;
	}

	function createOption($pData)
	{
		$descriptions = '';
		foreach ($pData['descriptions'] as $k => $v) {
			unset($v['id']);
			$this->db->insert($this->tblDescription, $v);
			$insertId = $this->db->insert_id();
			$descriptions = $descriptions . $insertId . ',';
		}

		$data = array(
			'code' => $pData['code'],
			'selectedLanguage' => $pData['selectedLanguage'],
			'type' => $pData['type'],
			'descriptions' => $descriptions,
			'store' => $pData['store']
		);
		$this->db->insert($this->tblOptions, $data);
		$insertId = $this->db->insert_id();
		$result = $this->db->select('*')->get_where($this->tblOptions, array('id' => $insertId))->row_array();
		return $result;
	}

	function getAttributes($productId, $count, $store, $lang, $page)
	{
		$attributes = $this->db->select('*')->get($this->tblAttributes, $count, $count * $page)->result_array();
		foreach ($attributes as $k => $v) {
			$attributes[$k]['option'] = $this->get_OptionsById(null, $v['option']);
			$attributes[$k]['optionValue'] = $this->get_OptionValueById(null, null, $v['optionValue']);
		}

		$recordsTotal = $this->db->from($this->tblAttributes)->count_all_results();
		$totalPages = ceil($recordsTotal / $count);
		$result = array($recordsTotal, $totalPages, $attributes);
		return $result;
	}

	function getAttributesById($productId, $attributeId)
	{
		$attribute = $this->db->select('*')->get_where($this->tblAttributes, array('id' => $attributeId))->row_array();
		$attribute['option'] = $this->get_OptionsById(null, $attribute['option']);
		$attribute['optionValue'] = $this->get_OptionValueById(null, null, $attribute['optionValue']);
		return $attribute;
	}

	function createAttribute($pData, $productId)
	{
		$optionId = $this->db->select('*')->get_where($this->tblOptions, array('code' => $pData['option']['code']))->row_array()['id'];
		$optionValueId = $this->db->select('*')->get_where($this->tblOptionValue, array('code' => $pData['optionValue']['code']))->row_array()['id'];
		$pData['option'] = $optionId;
		$pData['optionValue'] = $optionValueId;
		$this->db->insert($this->tblAttributes, $pData);
		$insertId = $this->db->insert_id();
		return array('id' => $insertId);
	}

	function updateAttribute($productId, $attributeId, $pData)
	{
		$optionId = $this->db->select('*')->get_where($this->tblOptions, array('code' => $pData['option']['code']))->row_array()['id'];
		$optionValueId = $this->db->select('*')->get_where($this->tblOptionValue, array('code' => $pData['optionValue']['code']))->row_array()['id'];
		$pData['option'] = $optionId;
		$pData['optionValue'] = $optionValueId;
		$this->db->where(array('id' => $attributeId))->update($this->tblAttributes, $pData);
		return true;
	}

	function getOptionValues($count = null, $store = null, $lang = null, $page = null)
	{
		$optionValues = $this->db->select('*')->limit($count, $count * $page)->get_where($this->tblOptionValue, array('store' => $store))->result_array();
		foreach ($optionValues as $k5 => $v5) {
			if (!$v5) continue;
			$optionValues[$k5]['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $optionValues[$k5]['descriptions']);
			$newArr = customFilterArray($optionValues[$k5]['descriptions'], $lang);
			$optionValues[$k5]['description'] = count($newArr) > 0 && $newArr[0] ? $newArr[0] : null;
		}

		$recordsTotal = $this->db->from($this->tblOptionValue)->count_all_results();
		$totalPages = ceil($recordsTotal / $count);
		$result = array($recordsTotal, $totalPages, $optionValues);
		return $result;
	}

	function createOptionValue($pData)
	{
		$descriptions = '';
		foreach ($pData['descriptions'] as $k => $v) {
			unset($v['id']);
			$this->db->insert($this->tblDescription, $v);
			$insertId = $this->db->insert_id();
			$descriptions = $descriptions . $insertId . ',';
		}

		$data = array(
			'code' => $pData['code'],
			'selectedLanguage' => $pData['selectedLanguage'],
			'descriptions' => $descriptions,
		);
		$this->db->insert($this->tblOptionValue, $data);
		$insertId = $this->db->insert_id();
		$result = $this->db->select('*')->get_where($this->tblOptionValue, array('id' => $insertId))->row_array();
		return $result;
	}

	function updateOptionValue($pData)
	{
		DeleteDescriptionsInTableWithCondition($this, $this->tblOptionValue, array('id' => $pData['id']));

		$descriptions = '';
		// $enName = $pData['descriptions'][0]['name'];
		foreach ($pData['descriptions'] as $k => $v) {
			unset($v['id']);
			$this->db->insert($this->tblDescription, $v);
			$insertId = $this->db->insert_id();
			$descriptions = $descriptions . $insertId . ',';
		}

		$where = array('id' => $pData['id']);
		$data = array(
			'code' => $pData['code'],
			'selectedLanguage' => $pData['selectedLanguage'],
			'store' => $pData['store'],
			'descriptions' => $descriptions,
			// 'name' => $enName,
		);
		$this->db->where($where);
		$this->db->update($this->tblOptionValue, $data);
		return true;
	}

	function deleteOptionValueImage($id)
	{
		$where = array('id' => $id);
		$this->db->where($where);
		$this->db->update($this->tblOptionValue, array('image' => null));
		return true;
	}

	function get_OptionValueById($count = null, $lang = null, $id = null)
	{
		$optionValues = $this->db->select('*')->limit($count)->get_where($this->tblOptionValue, array('id' => $id))->row_array();
		$optionValues['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $optionValues['descriptions']);
		$newArr = customFilterArray($optionValues['descriptions'], $lang);
		$optionValues['description'] = count($newArr) > 0 && $newArr[0] ? $newArr[0] : null;
		return $optionValues;
	}

	function createImage($file_name, $target_file, $id)
	{
		$this->db->where(array('id' => $id));
		$this->db->update($this->tblOptionValue, array('image' => '/' . $target_file));
		return true;
	}

	function get_Property($store, $lang, $productType)
	{
		$properties = $this->db->select('*')->get($this->tblProperty)->result_array();
		foreach ($properties as $k5 => $v5) {
			if (!$v5) continue;

			$options = GetTableDetails($this, $this->tblOptions, 'id', $properties[$k5]['option']);
			$properties[$k5]['option'] = $options[0];
			$properties[$k5]['option']['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $properties[$k5]['option']['descriptions']);
			$newArr = customFilterArray($properties[$k5]['option']['descriptions'], $lang);
			$properties[$k5]['option']['description'] = count($newArr) > 0 && $newArr[0] ? $newArr[0] : null;

			$properties[$k5]['optionValues'] = GetTableDetails($this, $this->tblOptionValue, 'id', $properties[$k5]['optionValues']);
			foreach ($properties[$k5]['optionValues'] as $k2 => $v2) {
				$properties[$k5]['optionValues'][$k2]['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $v2['descriptions']);
				$newArr = customFilterArray($properties[$k5]['optionValues'][$k2]['descriptions'], $lang);
				$properties[$k5]['optionValues'][$k2]['description'] = count($newArr) > 0 && $newArr[0] ? $newArr[0] : null;
			}

			$properties[$k5]['productTypes'] = GetTableDetails($this, $this->tblOptionValue, 'id', $properties[$k5]['productTypes']);
			foreach ($properties[$k5]['productTypes'] as $k3 => $v3) {
				$properties[$k5]['productTypes'][$k3]['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $v3['descriptions']);
				$newArr = customFilterArray($properties[$k5]['productTypes'][$k3]['descriptions'], $lang);
				$properties[$k5]['productTypes'][$k3]['description'] = count($newArr) > 0 && $newArr[0] ? $newArr[0] : null;
			}
		}
		return $properties;
	}

	function createProperty($pData)
	{
		$optionValues = '';
		foreach ($pData['optionValues'] as $k1 => $v1) {
			$optionValues = $optionValues . $v1 . ',';
		}
		$productTypes = '';
		foreach ($pData['productTypes'] as $k2 => $v2) {
			$productTypes = $productTypes . $v2 . ',';
		}

		$data = array(
			'code' => $pData['code'],
			'option' => (int)$pData['option'],
			'readOnly' => $pData['readOnly'],
			'optionValues' => $optionValues,
			'productTypes' => $productTypes,
		);
		$this->db->insert($this->tblProperty, $data);
		return true;
	}

	function get_PropertyValueById($id = null, $lang = null, $store = null)
	{
		$property = $this->db->select('*')->get_where($this->tblProperty, array('id' => $id))->row_array();
		$property['option'] = $this->get_OptionsById(null, $property['option']);

		$property['optionValues'] = GetTableDetails($this, $this->tblOptionValue, 'id', $property['optionValues']);
		foreach ($property['optionValues'] as $k3 => $v3) {
			$property['optionValues'][$k3]['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $v3['descriptions']);
			$newArr = customFilterArray($property['optionValues'][$k3]['descriptions'], $lang);
			$property['optionValues'][$k3]['description'] = count($newArr) > 0 && $newArr[0] ? $newArr[0] : null;
		}

		$property['productTypes'] = GetTableDetails($this, $this->tblOptionValue, 'id', $property['productTypes']);
		foreach ($property['productTypes'] as $k2 => $v2) {
			$property['productTypes'][$k2]['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $v2['descriptions']);
			$newArr = customFilterArray($property['productTypes'][$k2]['descriptions'], $lang);
			$property['productTypes'][$k2]['description'] = count($newArr) > 0 && $newArr[0] ? $newArr[0] : null;
		}
		return $property;
	}

	function updateProperty($pData, $id)
	{
		$optionValues = '';
		foreach ($pData['optionValues'] as $k1 => $v1) {
			$optionValues = $optionValues . $v1 . ',';
		}
		$productTypes = '';
		foreach ($pData['productTypes'] as $k2 => $v2) {
			$productTypes = $productTypes . $v2 . ',';
		}

		$where = array('id' => $id);

		$data = array(
			'option' => $pData['option'],
			'readOnly' => $pData['readOnly'],
			'optionValues' => $optionValues,
			'productTypes' => $productTypes,
		);

		$this->db->where($where);
		$this->db->update($this->tblProperty, $data);
		return true;
	}


	function getVariation($store, $lang, $count, $page)
	{
		$variations = $this->db->select('*')->get($this->tblPropertyVariation)->result_array();
		foreach ($variations as $k1 => $v1) {
			if (!$v1) continue;
			$variations[$k1]['optionValue'] = $this->db->select('*')->get_where($this->tblOptionValue, array('id' => $v1['optionValue']))->row_array();
			$variations[$k1]['optionValue']['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $variations[$k1]['optionValue']['descriptions']);
			$newArr = customFilterArray($variations[$k1]['optionValue']['descriptions'], $lang);
			$variations[$k1]['optionValue']['description'] = count($newArr) > 0 && $newArr[0] ? $newArr[0] : null;

			$variations[$k1]['option'] = $this->db->select('*')->get_where($this->tblOptions, array('id' => $v1['option']))->row_array();
			$variations[$k1]['option']['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $variations[$k1]['option']['descriptions']);
			$newArr = customFilterArray($variations[$k1]['option']['descriptions'], $lang);
			$variations[$k1]['option']['description'] = count($newArr) > 0 && $newArr[0] ? $newArr[0] : null;
		}

		$recordsTotal = $this->db->from($this->tblPropertyVariation)->count_all_results();
		$totalPages = ceil($recordsTotal / $count);
		$result = array($recordsTotal, $totalPages, $variations);
		return $result;
	}

	function createVariation($pData)
	{
		$data = array(
			'code' => $pData['code'],
			'option' => (int)$pData['option'],
			'optionValue' => (int)$pData['optionValue'],
		);
		$this->db->insert($this->tblPropertyVariation, $data);
		return true;
	}

	function getManufacturers($store, $lang, $page, $count)
	{
		$manufacturers = $this->db->get($this->tblManufacturer)->result_array();
		for ($i = 0; $i < count($manufacturers); $i++) {
			$manufacturers[$i]['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $manufacturers[$i]['descriptions']);
			$newArr = customFilterArray($manufacturers[$i]['descriptions'], $lang);
			$manufacturers[$i]['description'] = count($newArr) > 0 && $newArr[0] ? $newArr[0] : null;
		}

		$recordsTotal = $this->db->from($this->tblManufacturer)->count_all_results();
		$totalPages = ceil($recordsTotal / $count);
		$result = array($recordsTotal, $totalPages, $manufacturers);
		return $result;
	}

	function getManufacturerById($id, $lang)
	{
		$manufacturer = $this->db->select('*')->get_where($this->tblManufacturer, array('id' => $id))->row_array();
		$manufacturer['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $manufacturer['descriptions']);
		$newArr = customFilterArray($manufacturer['descriptions'], $lang);
		$manufacturer['description'] = count($newArr) > 0 && $newArr[0] ? $newArr[0] : null;
		return $manufacturer;
	}

	function updateManufacturer($pData, $id)
	{
		DeleteDescriptionsInTableWithCondition($this, $this->tblManufacturer, array('id' => $id));

		$descriptions = '';
		foreach ($pData['descriptions'] as $k => $v) {
			unset($v['id']);
			$this->db->insert($this->tblDescription, $v);
			$insertId = $this->db->insert_id();
			$descriptions = $descriptions . $insertId . ',';
		}
		$where = array('id' => $id);
		$data = array(
			'code' => $pData['code'],
			'descriptions' => $descriptions,
			'order' => (int)$pData['order'],
			'selectedLanguage' => $pData['selectedLanguage'],
		);

		$this->db->where($where);
		$this->db->update($this->tblManufacturer, $data);
		return true;
	}

	function createManufacturer($pData)
	{
		$descriptions = '';
		foreach ($pData['descriptions'] as $k => $v) {
			unset($v['id']);
			$this->db->insert($this->tblDescription, $v);
			$insertId = $this->db->insert_id();
			$descriptions = $descriptions . $insertId . ',';
		}
		$data = array(
			'code' => $pData['code'],
			'descriptions' => $descriptions,
			'order' => (int)$pData['order'],
			'selectedLanguage' => $pData['selectedLanguage'],
		);
		$this->db->insert($this->tblManufacturer, $data);
		return true;
	}


	function getProductsByGroup($groupCode, $count)
	{
		$group = $this->db->select('*')->get_where($this->tblProductGroups, array('code' => $groupCode))->row_array();
		$productLists = explode(',', $group['products']);
		$group['products'] = array();
		foreach ($productLists as $k1 => $v1) {
			if (!$v1) continue;
			$product = $this->getProductDetail(null, null, null, null, $v1);
			$group['products'][$k1] = $product;
		}

		$recordsTotal = $this->db->from($this->tblProductGroups)->count_all_results();
		$totalPages = ceil($recordsTotal / $count);
		$result = array($recordsTotal, $totalPages, $group['products']);
		return $result;
	}

	function addProductToGroup($productId, $groupCode)
	{
		$group = $this->db->select('*')->get_where($this->tblProductGroups, array('code' => $groupCode))->row_array();
		$productStr = $group['products'] . $productId . ',';

		$where = array('code' => $groupCode);
		$data = array('products' => $productStr);

		$this->db->where($where);
		$this->db->update($this->tblProductGroups, $data);
		return true;
	}

	function removeProductFromGroup($productId, $groupCode)
	{
		$group = $this->db->select('*')->get_where($this->tblProductGroups, array('code' => $groupCode))->row_array();
		$productArr = explode(',', $group['products']);

		if (($key = array_search($productId, $productArr)) !== false)
			unset($productArr[$key]);

		$newProductStr = '';
		foreach ($productArr as $k => $v) {
			if (!$v) continue;
			$newProductStr = $newProductStr . $v . ',';
		}

		$where = array('code' => $groupCode);
		$data = array('products' => $newProductStr);

		$this->db->where($where);
		$this->db->update($this->tblProductGroups, $data);
	}

	function createProductGroup($pData)
	{
		$data = array(
			'code' => $pData['code'],
			'active' => $pData['active'],
		);
		$this->db->insert($this->tblProductGroups, $data);
		return true;
	}

	function updateGroupActiveValue($pData, $groupCode)
	{
		$where = array('code' => $groupCode);
		$data = array('active' => (int)$pData['active']);
		$this->db->where($where)->update($this->tblProductGroups, $data);
		return true;
	}

	function getTypeById($id = null, $lang = null, $store = null)
	{
		$types = $this->db->select('*')->get_where($this->tblPropertyType, array('id' => $id))->row_array();
		$types['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $types['descriptions']);
		$newArr = customFilterArray($types['descriptions'], $lang);
		$types['description'] = count($newArr) > 0 && $newArr[0] ? $newArr[0] : null;
		return $types;
	}

	function createType($pData)
	{
		$descriptions = '';
		foreach ($pData['descriptions'] as $k => $v) {
			unset($v['id']);
			$this->db->insert($this->tblDescription, $v);
			$insertId = $this->db->insert_id();
			$descriptions = $descriptions . $insertId . ',';
		}
		$data = array(
			'code' => $pData['code'],
			'allowAddToCart' => $pData['allowAddToCart'],
			'selectedLanguage' => $pData['selectedLanguage'],
			'visible' => $pData['visible'],
			'descriptions' => $descriptions,
		);
		$this->db->insert($this->tblPropertyType, $data);
		return true;
	}

	function updateType($pData, $id)
	{
		DeleteDescriptionsInTableWithCondition($this, $this->tblPropertyType, array('id' => $id));

		$descriptions = '';
		foreach ($pData['descriptions'] as $k => $v) {
			unset($v['id']);
			$this->db->insert($this->tblDescription, $v);
			$insertId = $this->db->insert_id();
			$descriptions = $descriptions . $insertId . ',';
		}
		$where = array('id' => $id);
		$data = array(
			'allowAddToCart' => (int)$pData['allowAddToCart'],
			'selectedLanguage' => $pData['selectedLanguage'],
			'visible' => (int)$pData['visible'],
			'descriptions' => $descriptions,
		);

		$this->db->where($where)->update($this->tblPropertyType, $data);
		return true;
	}

	function getUnitById($id = null, $lang = null, $store = null)
	{
		$units = $this->db->select('*')->get_where($this->tblProductUnit, array('id' => $id))->row_array();
		$units['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $units['descriptions']);
		$newArr = customFilterArray($units['descriptions'], $lang);
		$units['description'] = count($newArr) > 0 && $newArr[0] ? $newArr[0] : null;
		return $units;
	}

	function createUnit($pData)
	{
		$descriptions = '';
		foreach ($pData['descriptions'] as $k => $v) {
			unset($v['id']);
			$this->db->insert($this->tblDescription, $v);
			$insertId = $this->db->insert_id();
			$descriptions = $descriptions . $insertId . ',';
		}
		$data = array(
			'code' => $pData['code'],
			'selectedLanguage' => $pData['selectedLanguage'],
			'visible' => $pData['visible'],
			'descriptions' => $descriptions,
		);
		$this->db->insert($this->tblProductUnit, $data);
		return true;
	}

	function updateUnit($pData, $id)
	{
		DeleteDescriptionsInTableWithCondition($this, $this->tblProductUnit, array('id' => $id));

		$descriptions = '';
		foreach ($pData['descriptions'] as $k => $v) {
			unset($v['id']);
			$this->db->insert($this->tblDescription, $v);
			$insertId = $this->db->insert_id();
			$descriptions = $descriptions . $insertId . ',';
		}
		$where = array('id' => $id);
		$data = array(
			'selectedLanguage' => $pData['selectedLanguage'],
			'visible' => (int)$pData['visible'],
			'descriptions' => $descriptions,
		);

		$this->db->where($where)->update($this->tblProductUnit, $data);
		return true;
	}
} // END
