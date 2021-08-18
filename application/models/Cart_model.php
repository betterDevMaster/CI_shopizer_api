<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Cart_model extends CI_Model
{
	public $tblProducts = 'tbl_products';
	public $tblCart = 'tbl_cart';
	public $tblTotal = 'tbl_total';
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
	public $tblPropertyType = 'tbl_property_type';
	public $tblReview = 'tbl_review';
	public $tblUserBilling = 'tbl_user_billing';
	public $tblUserDelivery = 'tbl_user_delivery';
	public $tblShipping = 'tbl_shipping';
	public $tblShippingOptions = 'tbl_shipping_options';

	public function __construct()
	{
		parent::__construct();
	}

	/*
		** function: get_FullProduct(...)
		*** main Param: productId
		**** get one product and get child categories by this product
	*/
	function get_FullProduct($productId = null)
	{
		$product = $this->db->select('*')->get_where($this->tblProducts, array('id' => $productId))->row_array();
		$product['attributes'] = array();

		// Categories
		$categoryList = explode(',', $product['categories']);
		$product['categories'] = array();
		foreach ($categoryList as $v2) {
			if (!$v2) continue;
			$categories = $this->db->select('*')->get_where($this->tblCategories, array('id' => $v2))->row_array();
			$categories['description'] = $this->db->select('*')->get_where($this->tblDescription, array('id' => $categories['description']))->row_array();
			array_push($product['categories'], $categories);
		}

		// Description
		$product['description'] = $this->db->select('*')->get_where($this->tblDescription, array('id' => $product['description']))->row_array();

		// Image
		$product['image'] = $this->db->select('*')->get_where($this->tblImage, array('id' => $product['image']))->row_array();

		// Images
		$imageList = explode(',', $product['images']);
		$product['images'] = array();
		foreach ($imageList as $v4) {
			$image = $this->db->select('*')->get_where($this->tblImage, array('id' => $v4))->row_array();
			array_push($product['images'], $image);
		}

		// Manufacturer
		$manufacturer = $this->db->select('*')->get_where($this->tblManufacturer, array('id' => $product['manufacturer']))->row_array();
		$manufacturer['description'] = $this->db->select('*')->get_where($this->tblDescription, array('id' => $manufacturer['description']))->row_array();
		$product['manufacturer'] = $manufacturer;

		// Options
		$optionList = explode(',', $product['options']);
		$product['options'] = array();
		foreach ($optionList as  $v5) {
			if (!$v5) continue;
			$options = $this->db->select('*')->get_where($this->tblOptions, array('id' => $v5))->row_array();
			$optionValuesList = explode(',', $options['optionValues']);
			$options['optionValues'] = array();
			foreach ($optionValuesList as  $v7) {
				if (!$v7) continue;
				$optionValues = $this->db->select('*')->get_where($this->tblOptionValues, array('id' => $v7))->row_array();
				$optionValues['description'] = $this->db->select('*')->get_where($this->tblDescription, array('id' => $optionValues['description']))->row_array();
				array_push($options['optionValues'], $optionValues);
			}
			array_push($product['options'], $options);
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
		foreach ($propertyList as  $v8) {
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
		$type = $this->db->select('*')->get_where($this->tblPropertyType, array('id' => $product['type']))->row_array();
		$type['description'] = $this->db->select('*')->get_where($this->tblDescription, array('id' => $type['description']))->row_array();
		$product['type'] = $type;

		return $product;
	}

	/*
		** function: get_AddCart(...)
		*** main Param: customer, code, products, quantity
		**** param desc: products & quantity format: 1,2,3,4
	*/
	function get_AddCart($pData = null, $cart = null, $promoCart = null)
	{
		if (!$cart) {
			$where = array('customer' => $pData['customerId']);
			$data = $this->db->get_where($this->tblCart, $where)->row_array();
			$data['code'] = md5($pData['customerId']);
			$data['customer'] = $pData['customerId'];
		} else {
			if (!IsNullOrEmptyString($promoCart)) {
				$this->db->set('promoCode', $promoCart); //value that used to update column
				$this->db->where('code', $cart); //which row want to upgrade
				$this->db->update($this->tblCart); //table name
			}
			$where = array('code' => $cart);
			$data = $this->db->get_where($this->tblCart, $where)->row_array();
		}

		if (!$data) return;
		if (count($data) > 0) {
			$dupliCatedProductId = false;

			$products = explode(',', $data['products']);
			$quantitys = explode(',', $data['quantity']);

			if (!$cart) {
				foreach ($products as $k1 => $v1) {
					if (!$v1) continue;
					if ($v1 == $pData['productId']) {
						$quantitys[$k1] = $pData['quantity'];
						$dupliCatedProductId = true;
					}
				}

				if (!$dupliCatedProductId) {
					$data['products'] = $data['products'] . $pData['productId'] . ',';
					$data['quantity'] = $data['quantity'] . $pData['quantity'] . ',';
				} else {
					$data['products'] = $data['products'];
					$data['quantity'] = '';
					foreach ($quantitys as $k2 => $v2) {
						if (!$v2) continue;
						$data['quantity'] = $data['quantity'] . $v2 . ',';
					}
				}
				$this->db->where($where);
				$this->db->update($this->tblCart, $data);
			}
		} else {
			$data['products'] = $pData['productId'] . ',';
			$data['quantity'] = $pData['quantity'] . ',';
			$this->db->insert($this->tblCart, $data);
		}

		$totals = $this->db->select('*')->get($this->tblTotal)->result_array();

		$data['products'] = array();
		$totalPrice  = 0;
		foreach ($products as $k3 => $v3) {
			if (!$v3) continue;
			$product = $this->get_FullProduct($v3);
			$curTotalPrice = $product['price'] * (int)$quantitys[$k3];
			$totalPrice  = $curTotalPrice + $totalPrice;
			$product['quantity'] = (int)$quantitys[$k3];
			$product['subTotal'] = $product['total'] = $curTotalPrice;
			$product['displaySubTotal'] = $product['displayTotal'] = "USD" . number_format($curTotalPrice, 2);
			array_push($data['products'], $product);
		}

		$quantity = 0;
		foreach ($quantitys as $k4 => $v4) {
			if (!$v4) continue;
			$quantity = $quantity + (int)$v4;
		}

		$data['quantity'] = $quantity;
		$data['total'] = $data['subTotal'] = $totalPrice;
		$data['displayTotal'] = $data['displaySubTotal'] = "USD" . number_format($totalPrice, 2);
		$data['totals'] = $totals;
		$data['totals'][0]['value'] = number_format($curTotalPrice, 2);
		$data['totals'][1]['value'] = number_format($curTotalPrice, 2);

		$this->db->where($where);
		$this->db->update($this->tblCart, array('total' => $totalPrice, 'subTotal' => $totalPrice));

		return $data;
	}

	function get_UserCart($cart, $lang, $store)
	{
		$cart = $this->get_AddCart(null, $cart);
		return $cart;
	}

	function get_UserPromoCart($pData)
	{
		$cart = $this->get_AddCart(null, $pData['code'], $pData['promoCart']);
		return $cart;
	}

	function get_UpdateCart($pData)
	{
		$where = array('code' => $pData['code']);
		$data = $this->db->get_where($this->tblCart, $where)->row_array();

		if (!$data) return;

		$products = explode(',', $data['products']);
		$quantitys = explode(',', $data['quantity']);

		foreach ($products as $k1 => $v1) {
			if (!$v1) continue;
			if ($v1 == $pData['productId']) {
				$quantitys[$k1] = $pData['quantity'];
			}
		}

		$quantity = '';
		foreach ($quantitys as $k2 => $v2) {
			if (!$v2) continue;
			$quantity = $quantity . $v2 . ',';
		}

		$where = array('code' => $pData['code']);
		$data['quantity'] = $quantity;
		$this->db->where($where);
		$this->db->update($this->tblCart, $data);

		$cart = $this->get_AddCart(null, $pData['code']);
		return $cart;
	}

	function get_DeleteCart($code, $productId, $store)
	{
		$where = array('code' => $code);
		$data = $this->db->get_where($this->tblCart, $where)->row_array();

		if (!$data) return false;

		$products = explode(',', $data['products']);
		$quantitys = explode(',', $data['quantity']);

		foreach ($products as $k1 => $v1) {
			if (!$v1) continue;
			if ($v1 == $productId) {
				unset($products[$k1]);
				unset($quantitys[$k1]);
			}
		}

		$product = '';
		$quantity = '';
		foreach ($quantitys as $k2 => $v2) {
			if (!$v2) continue;
			$quantity = $quantity . $v2 . ',';
		}
		foreach ($products as $k3 => $v3) {
			if (!$v3) continue;
			$product = $product . $v3 . ',';
		}

		$where = array('code' => $code);
		$data['products'] = $product;
		$data['quantity'] = $quantity;
		$this->db->where($where);
		$this->db->update($this->tblCart, $data);

		return true;
	}

	function get_Shipping($pData)
	{
		$shipping = $this->db->select('*')->get($this->tblShipping)->row_array();
		$shipping['delivery'] = $this->db->select('*')->get_where($this->tblUserDelivery, array('id' => $shipping['delivery']))->row_array();
		$shippingOptions = explode(',', $shipping['shippingOptions']);
		$shipping['shippingOptions'] = array();
		foreach ($shippingOptions as $k => $v) {
			if (!$v) continue;
			$options = $this->db->select('*')->get_where($this->tblShippingOptions, array('id' => $v))->row_array();
			array_push($shipping['shippingOptions'], $options);
		}

		return $shipping;
	}

	function get_Total($code, $quote = null)
	{
		$cart = $this->db->select('*')->get_where($this->tblCart, array('code' => $code))->row_array();
		$totals = $this->db->select('*')->get($this->tblTotal)->result_array();
		$cart['totals'] = $totals;
		$cart['totals'][0]['value'] = $cart['subTotal'];
		$cart['totals'][1]['value'] = $cart['total'];

		$cart['totals'][0]['total'] = 'USD' . $cart['subTotal'];
		$cart['totals'][1]['total'] = 'USD' . $cart['total'];
		$cart['subTotal'] = 'USD' . $cart['subTotal'];
		$cart['total'] = 'USD' . $cart['total'];
		return $cart;
	}
} // END
