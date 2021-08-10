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
	public $tblOptionValues = 'tbl_option_value';
	public $tblProductPrice = 'tbl_product_price';
	public $tblProductSpecification = 'tbl_product_specification';
	public $tblProperties = 'tbl_properties';
	public $tblProperty = 'tbl_property';
	public $tblPropertyValue = 'tbl_property_value';
	public $tblType = 'tbl_type';
	public $tblReview = 'tbl_review';
	public $tblUserBilling = 'tbl_user_billing';
	public $tblUserDelivery = 'tbl_user_delivery';

	public function __construct()
	{
		parent::__construct();
	}

	function get_FeaturedItem($store, $lang, $category = null, $manufacturer = '', $productId = null)
	{
		if (!$productId) {
			if (!$category)
				$products = $this->db->select('*')->get($this->tblProducts)->result_array();
			else {
				if ($this->IsNullOrEmptyString($manufacturer)) {
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
				$categories['description'] = $this->db->select('*')->get_where($this->tblDescription, array('id' => $categories['description']))->row_array();
				array_push($products[$k1]['categories'], $categories);
			}

			// Description
			$products[$k1]['description'] = $this->db->select('*')->get_where($this->tblDescription, array('id' => $v1['description']))->row_array();

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
			$manufacturer['description'] = $this->db->select('*')->get_where($this->tblDescription, array('id' => $manufacturer['description']))->row_array();
			$products[$k1]['manufacturer'] = $manufacturer;

			// Options
			$optionList = explode(',', $v1['options']);
			$products[$k1]['options'] = array();
			foreach ($optionList as $k5 => $v5) {
				if (!$v5) continue;
				$options = $this->db->select('*')->get_where($this->tblOptions, array('id' => $v5))->row_array();
				$optionValuesList = explode(',', $options['optionValues']);
				$options['optionValues'] = array();
				foreach ($optionValuesList as $k7 => $v7) {
					if (!$v7) continue;
					$optionValues = $this->db->select('*')->get_where($this->tblOptionValues, array('id' => $v7))->row_array();
					$optionValues['description'] = $this->db->select('*')->get_where($this->tblDescription, array('id' => $optionValues['description']))->row_array();
					array_push($options['optionValues'], $optionValues);
				}
				array_push($products[$k1]['options'], $options);
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
			$type = $this->db->select('*')->get_where($this->tblType, array('id' => $v1['type']))->row_array();
			$type['description'] = $this->db->select('*')->get_where($this->tblDescription, array('id' => $type['description']))->row_array();
			$products[$k1]['type'] = $type;
		}
		return $products;
	}

	function get_Price($pData)
	{
		$products = $this->db->select('id, description, discounted, finalPrice, originalPrice')->get_where($this->tblProducts, array('id' => $pData['id']))->row_array();
		$products['description'] = $this->db->select('*')->get_where($this->tblDescription, array('id' => $products['description']))->row_array();
		return $products;
	}

	function get_ProductList($pData)
	{
		$manufacturer = $this->IsNullOrEmptyString($pData['manufacturer']) ? '' : $pData['manufacturer'] . ',';
		$product = $this->get_FeaturedItem($pData['store'], $pData['lang'], $pData['category'] . ',', $manufacturer);
		return $product;
	}

	function get_ProductDetail($pData)
	{
		$product = $this->get_FeaturedItem($pData['store'], $pData['lang'], null, '', $pData['id']);
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

	function IsNullOrEmptyString($str)
	{
		return (!isset($str) || trim($str) === '');
	}
} // END
