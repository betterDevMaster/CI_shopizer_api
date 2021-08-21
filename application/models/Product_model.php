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
	public $tblOptionValue = 'tbl_option_value';
	public $tblProductPrice = 'tbl_product_price';
	public $tblProductSpecification = 'tbl_product_specification';
	public $tblProductGroups = 'tbl_product_groups';
	public $tblProperties = 'tbl_properties';
	public $tblProperty = 'tbl_property';
	public $tblPropertyValue = 'tbl_property_value';
	public $tblPropertyType = 'tbl_property_type';
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

	function get_FeaturedItem($store = null, $lang = null, $category = null, $manufacturer = null, $productId = null)
	{
		if (!$productId) {
			if (!$category)
				$products = $this->db->select('*')->get($this->tblProducts)->result_array();
			else {
				if (IsNullOrEmptyString($manufacturer)) {
					$manufacturers = $this->db->select('id')->get($this->tblManufacturer)->result_array();
					foreach ($manufacturers as $k0 => $v0) {
						$manuId = $v0['id'];
						$this->db->or_where("manufacturer LIKE '%$manuId%'");
					}
				} else {
					$manufacturerList = explode(',', $manufacturer);
					foreach ($manufacturerList as $k => $v) {
						if (!$v) continue;
						$this->db->or_where("manufacturer LIKE '%$v%'");
					}
				}
				$this->db->where("categories LIKE '%$category%'");
				$products = $this->db->get($this->tblProducts)->result_array();
			}
		} else {
			$products = $this->db->select('*')->get_where($this->tblProducts, array('id' => $productId))->result_array();
		}

		foreach ($products as $k1 => $v1) {
			if (!$v1['attributes']) $products[$k1]['attributes'] = array();

			// Categories
			$categoryList = explode(',', $v1['categories']);
			$products[$k1]['categories'] = array();
			foreach ($categoryList as $k2 => $v2) {
				if (!$v2) continue;
				$categories = $this->db->select('*')->get_where($this->tblCategories, array('id' => $v2))->row_array();
				if ($categories) {
					$categories['description'] = $this->db->select('*')->get_where($this->tblDescription, array('id' => $categories['description']))->row_array();
					array_push($products[$k1]['categories'], $categories);
				} else {
					$products[$k1]['categories'] = null;
				}
			}

			// CartItemattributes
			$products[$k1]['cartItemattributes'] = GetTableDetails($this, $this->tblCartItemAttributes, 'id', $products[$k1]['cartItemattributes']);
			foreach ($products[$k1]['cartItemattributes'] as $k9 => $v9) {
				$products[$k1]['cartItemattributes'][$k9]['option'] = $this->calcOption($v9['option']);
				$products[$k1]['cartItemattributes'][$k9]['optionValue'] = $this->calcOptionValue($v9['optionValue']);
			}

			// Description
			$products[$k1]['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $products[$k1]['descriptions']);
			$products[$k1]['description'] = count($products[$k1]['descriptions']) > 0 ? $products[$k1]['descriptions'][0] : null;

			// Image
			$products[$k1]['image'] = $this->db->select('*')->get_where($this->tblImage, array('id' => $v1['image']))->row_array();

			// Images
			$imageList = explode(',', $v1['images']);
			foreach ($imageList as $k4 => $v4) {
				$image = $this->db->select('*')->get_where($this->tblImage, array('id' => $v4))->result_array();
				$products[$k1]['images'] = array();
				$products[$k1]['images'] = $image + $products[$k1]['images'];
			}

			// Manufacturer
			$manufacturer = $this->db->select('*')->get_where($this->tblManufacturer, array('id' => $v1['manufacturer']))->row_array();
			$manufacturer['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $manufacturer['descriptions']);
			$products[$k1]['manufacturer'] = $manufacturer;

			// Options
			$optionList = explode(',', $v1['options']);
			$products[$k1]['options'] = array();
			foreach ($optionList as $k5 => $v5) {
				if (!$v5) continue;
				$products[$k1]['options'] = $this->calcOption($v5);
			}

			// Product Price
			$productPrice = $this->db->select('*')->get_where($this->tblProductPrice, array('id' => $v1['productPrice']))->row_array();
			$productPrice['description'] = $this->db->select('*')->get_where($this->tblDescription, array('id' => $productPrice['description']))->row_array();
			$products[$k1]['productPrice'] = $productPrice;

			// Product Specification
			$productSpecification = $this->db->select('*')->get_where($this->tblProductSpecification, array('id' => $v1['productSpecifications']))->row_array();
			$products[$k1]['productSpecifications'] = $productSpecification;

			// Properties
			$propertyList = explode(',', $v1['properties']);
			$products[$k1]['properties'] = array();
			foreach ($propertyList as $k8 => $v8) {
				if (!$v8) continue;
				$properties = $this->db->select('*')->get_where($this->tblProperties, array('id' => $v8))->row_array();
				$property = $this->db->select('*')->get_where($this->tblProperty, array('id' => $properties['property']))->row_array();
				if (!$property['optionValues']) $property['optionValues'] = array();
				$propertyValue = $this->db->select('*')->get_where($this->tblPropertyValue, array('id' => $properties['propertyValue']))->row_array();
				if (!$propertyValue['values']) $propertyValue['values'] = array();
				$properties['property'] = $property;
				$properties['propertyValue'] = $propertyValue;
				array_push($products[$k1]['properties'], $properties);
			}

			// Type
			$propertyType = $this->db->select('*')->get_where($this->tblPropertyType, array('id' => $v1['type']))->row_array();
			$propertyType['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $propertyType['descriptions']);
			// $propertyType['description'] = $this->db->select('*')->get_where($this->tblDescription, array('id' => $propertyType['description']))->row_array();
			$products[$k1]['type'] = $propertyType;
		}
		return $products;
	}


	function calcOption($id)
	{
		$optionsArr = array();
		$options = $this->db->select('*')->get_where($this->tblOptions, array('id' => $id))->row_array();

		$options['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $options['descriptions']);
		$options['description'] = count($options['descriptions']) > 0 ? $options['descriptions'][0] : null;

		$optionValuesList = explode(',', $options['optionValues']);
		$options['optionValues'] = array();
		foreach ($optionValuesList as $k7 => $v7) {
			if (!$v7) continue;
			$options['optionValues'] = $this->calcOptionValue($v7);
		}
		array_push($optionsArr, $options);
		return $optionsArr;
	}

	function calcOptionValue($id)
	{
		$optionValueArr = array();
		$optionValues = $this->db->select('*')->get_where($this->tblOptionValue, array('id' => $id))->row_array();
		$optionValues['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $optionValues['descriptions']);
		$optionValues['description'] = count($optionValues['descriptions']) > 0 ? $optionValues['descriptions'][0] : null;
		array_push($optionValueArr, $optionValues);
		return $optionValueArr;
	}

	function get_Price($pData)
	{
		$products = $this->db->select('id, descriptions, discounted, finalPrice, originalPrice')->get_where($this->tblProducts, array('id' => $pData['id']))->row_array();
		$products['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $products['descriptions']);
		$products['description'] = count($products['descriptions']) > 0 ? $products['descriptions'][0] : null;
		return $products;
	}

	function get_ProductList($pData)
	{
		$manufacturer = IsNullOrEmptyString($pData['manufacturer']) ? '' : $pData['manufacturer'] . ',';
		$product = $this->get_FeaturedItem($pData['store'], $pData['lang'], $pData['category'] . ',', $manufacturer);
		return $product;
	}

	function get_ProductDetail($pData)
	{
		$store = isset($pData['store']) ? $pData['store'] : 'DEFAULT';
		$lang = isset($pData['lang']) ? $pData['lang'] : 'en';
		$product = $this->get_FeaturedItem($store, $lang, null, '', $pData['id']);
		return $product;
	}

	function get_ProductReview($pData)
	{
		$reviews = $this->db->select('*')->get_where($this->tblReview, array('productId' => $pData['productId']))->result_array();
		foreach ($reviews as $k => $v) {
			$reviews[$k]['customer'] = $this->db->select('*')->get_where($this->tblProperties, array('id' => $pData['userId']))->row_array();
			$reviews[$k]['customer']['billing'] = $this->db->select('*')->get_where($this->tblUserBilling, array('userId' => $pData['userId']))->row_array();
			$reviews[$k]['customer']['delivery'] = $this->db->select('*')->get_where($this->tblUserDelivery, array('userId' => $pData['userId']))->row_array();
		}
		return $reviews;
	}

	function get_Options($count = null, $store = null, $lang = null, $page = null)
	{
		$options = $this->db->select('*')->limit($count, $count * $page)->get_where($this->tblOptions, array('store' => $store))->result_array();
		foreach ($options as $k5 => $v5) {
			if (!$v5) continue;

			$options[$k5]['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $options[$k5]['descriptions']);

			$optionValuesList = explode(',', $v5['optionValues']);
			$options[$k5]['optionValues'] = array();
			foreach ($optionValuesList as $k7 => $v7) {
				if (!$v7) continue;
				$optionValues = $this->db->select('*')->get_where($this->tblOptionValue, array('id' => $v7))->row_array();
				$optionValues['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $optionValues['descriptions']);
				array_push($options[$k5]['optionValues'], $optionValues);
			}
		}
		return $options;
	}

	function get_OptionsById($count = null,  $id = null)
	{
		$options = $this->db->select('*')->limit($count)->get_where($this->tblOptions, array('id' => $id))->row_array();
		$options['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $options['descriptions']);

		$optionValuesList = explode(',', $options['optionValues']);
		$options['optionValues'] = array();
		foreach ($optionValuesList as $v7) {
			if (!$v7) continue;
			$optionValues = $this->db->select('*')->get_where($this->tblOptionValue, array('id' => $v7))->row_array();
			$optionValues['description'] = $this->db->select('*')->get_where($this->tblDescription, array('id' => $optionValues['description']))->row_array();
			array_push($options['optionValues'], $optionValues);
		}
		return $options;
	}

	function updateOption($pData)
	{
		$descriptions = '';
		foreach ($pData['descriptions'] as $k => $v) {
			unset($v['id']);
			$this->db->insert($this->tblDescription, array('language' => $v['language'], 'name' => $v['name']));
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
		$this->db->where($where);
		$this->db->update($this->tblOptions, $data);
		return true;
	}

	function createOption($pData)
	{
		$descriptions = '';
		foreach ($pData['descriptions'] as $k => $v) {
			unset($v['id']);
			$this->db->insert($this->tblDescription, array('language' => $v['language'], 'name' => $v['name']));
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

	function get_Attributes($productId, $count, $store, $lang, $page)
	{
		$attributes = $this->db->select('*')->get($this->tblAttributes, $count, $count * $page)->result_array();
		return $attributes;
	}

	function get_OptionValues($count, $store, $lang, $page)
	{
		$optionValues = $this->db->select('*')->limit($count, $count * $page)->get_where($this->tblOptionValue, array('store' => $store))->result_array();
		foreach ($optionValues as $k5 => $v5) {
			if (!$v5) continue;
			$optionValues[$k5]['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $optionValues[$k5]['descriptions']);
		}
		return $optionValues;
	}

	function createValueOption($pData)
	{
		$descriptions = '';
		foreach ($pData['descriptions'] as $k => $v) {
			unset($v['id']);
			$this->db->insert($this->tblDescription, array('language' => $v['language'], 'name' => $v['name']));
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
		$descriptions = '';
		$enName = $pData['descriptions'][0]['name'];
		foreach ($pData['descriptions'] as $k => $v) {
			unset($v['id']);
			$this->db->insert($this->tblDescription, array('language' => $v['language'], 'name' => $v['name']));
			$insertId = $this->db->insert_id();
			$descriptions = $descriptions . $insertId . ',';
		}

		$where = array('id' => $pData['id']);
		$data = array(
			'code' => $pData['code'],
			'selectedLanguage' => $pData['selectedLanguage'],
			'store' => $pData['store'],
			'descriptions' => $descriptions,
			'name' => $enName,
		);
		$this->db->where($where);
		$this->db->update($this->tblOptionValue, $data);
		return true;
	}

	function get_OptionValueById($count = null, $lang = null, $id = null)
	{
		$optionValues = $this->db->select('*')->limit($count)->get_where($this->tblOptionValue, array('id' => $id))->row_array();
		$optionValues['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $optionValues['descriptions']);
		return $optionValues;
	}

	function createImage($optionValueId, $target_file)
	{
		$this->db->where(array('id' => $optionValueId));
		$this->db->update($this->tblOptionValue, array('image' => $target_file));
		return true;
	}

	function get_Property($store, $lang)
	{
		$properties = $this->db->select('*')->get($this->tblProperty)->result_array();
		foreach ($properties as $k5 => $v5) {
			if (!$v5) continue;
			$properties[$k5]['option'] = $this->db->select('*')->get_where($this->tblOptions, array('id' => $v5['option']))->row_array();
			if ($properties[$k5]['option']) {
				$descs = $properties[$k5]['option']['descriptions'];
				$values = $properties[$k5]['option']['optionValues'];
			} else {
				$descs = null;
				$values = null;
			}
			$properties[$k5]['option']['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $descs);
			$properties[$k5]['option']['optionValues'] = GetTableDetails($this, $this->tblOptionValue, 'id', $values);
			foreach ($properties[$k5]['option']['optionValues'] as $k1 => $v1) {
				$properties[$k5]['option']['optionValues'][$k1]['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $v1['descriptions']);
			}

			$properties[$k5]['optionValues'] = GetTableDetails($this, $this->tblOptionValue, 'id', $properties[$k5]['optionValues']);
			foreach ($properties[$k5]['optionValues'] as $k2 => $v2) {
				$properties[$k5]['optionValues'][$k2]['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $v2['descriptions']);
			}

			$properties[$k5]['productTypes'] = GetTableDetails($this, $this->tblOptionValue, 'id', $properties[$k5]['productTypes']);
			foreach ($properties[$k5]['productTypes'] as $k3 => $v3) {
				$properties[$k5]['productTypes'][$k3]['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $v2['descriptions']);
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

	function get_PropertyValueById($id, $lang, $store)
	{
		$property = $this->db->select('*')->get_where($this->tblProperty, array('id' => $id))->row_array();
		$property['option'] = $this->db->select('*')->get_where($this->tblOptions, array('id' => $property['option']))->row_array();
		$property['option']['optionValues'] = GetTableDetails($this, $this->tblOptionValue, 'id', $property['option']['optionValues']);
		$property['optionValues'] = GetTableDetails($this, $this->tblOptionValue, 'id', $property['optionValues']);
		$property['productTypes'] = GetTableDetails($this, $this->tblPropertyType, 'id', $property['productTypes']);
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


	function get_Variation($store, $lang)
	{
		$variations = $this->db->select('*')->get($this->tblPropertyVariation)->result_array();
		foreach ($variations as $k1 => $v1) {
			if (!$v1) continue;
			$variations[$k1]['optionValue'] = $this->db->select('*')->get_where($this->tblOptionValue, array('id' => $v1['optionValue']))->row_array();
			$variations[$k1]['optionValue']['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $variations[$k1]['optionValue']['descriptions']);

			$variations[$k1]['option'] = $this->db->select('*')->get_where($this->tblOptions, array('id' => $v1['option']))->row_array();
			$variations[$k1]['option']['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $variations[$k1]['option']['descriptions']);
			$variations[$k1]['option']['optionValues'] = GetTableDetails($this, $this->tblOptionValue, 'id', $variations[$k1]['option']['optionValues']);
		}
		return $variations;
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


	function get_Manufacturers($store, $lang, $page, $count)
	{
		$manufacturers = $this->db->select('*')->get($this->tblManufacturer)->result_array();
		foreach ($manufacturers as $k1 => $v1) {
			if (!$v1) continue;
			$manufacturers[$k1]['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $v1['descriptions']);
			foreach ($manufacturers[$k1]['descriptions'] as $k2 => $v2) {
				if ($v2['language'] == $lang) {
					$manufacturers[$k1]['description'] = $v2;
					break;
				}
			}
		}
		return $manufacturers;
	}

	function get_ManufacturerById($id, $lang)
	{
		$manufacturer = $this->db->select('*')->get_where($this->tblManufacturer, array('id' => $id))->row_array();
		$manufacturer['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $manufacturer['descriptions']);

		foreach ($manufacturer['descriptions'] as $k2 => $v2) {
			if ($v2['language'] == $lang || $lang == '_all') {
				$manufacturer['description'] = $v2;
				break;
			}
		}
		return $manufacturer;
	}

	function updateManufacturer($pData, $id)
	{
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


	function get_ProductsByGroup($groupCode)
	{
		$group = $this->db->select('*')->get_where($this->tblProductGroups, array('code' => $groupCode))->row_array();
		$productLists = explode(',', $group['products']);
		$group['products'] = array();
		foreach ($productLists as $k1 => $v1) {
			if (!$v1) continue;
			$product = $this->get_FeaturedItem(null, null, null, null, $v1);
			$group['products'][$k1] = $product[0];
		}
		return $group['products'];
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
} // END
