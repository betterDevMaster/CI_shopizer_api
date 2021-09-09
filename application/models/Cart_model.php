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
	public $tblOptionValues = 'tbl_option_values';
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
		** function: getFullProductDetails(...)
		*** main Param: productId
		**** get one product and get child categories by this product
	*/
	function getFullProductDetails($productId = null, $lang = 'es')
	{
		$product = $this->db->get_where($this->tblProducts, array('id' => $productId))->row_array();
		if ($product) {
			$product['attributes'] = array();
			// Categories
			$category = $this->db->get_where($this->tblCategories, array('id' => $product['category']))->row_array();
			$category['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $category['descriptions']);
			$newArr = customFilterArray($category['descriptions'], $lang);
			$category['description'] = count($newArr) > 0 && $newArr[0] ? $newArr[0] : null;
			$product['category'] = $category;

			// Description
			$product['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $product['descriptions']);
			$newArr = customFilterArray($product['descriptions'], $lang);
			$product['description'] = count($newArr) > 0 && $newArr[0] ? $newArr[0] : null;

			// Image
			$product['image'] = $this->db->get_where($this->tblImage, array('id' => $product['image']))->row_array();

			// Images
			$imageList = explode(',', $product['images']);
			$product['images'] = array();
			foreach ($imageList as $v4) {
				$image = $this->db->get_where($this->tblImage, array('id' => $v4))->row_array();
				array_push($product['images'], $image);
			}

			// Manufacturer
			$manufacturer = $this->db->get_where($this->tblManufacturer, array('id' => $product['manufacturer']))->row_array();
			$manufacturer['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $manufacturer['descriptions']);
			$newArr = customFilterArray($manufacturer['descriptions'], $lang);
			$manufacturer['description'] = count($newArr) > 0 && $newArr[0] ? $newArr[0] : null;
			$product['manufacturer'] = $manufacturer;

			// Options
			$optionList = explode(',', $product['options']);
			$product['options'] = array();
			foreach ($optionList as  $v5) {
				if (!$v5) continue;
				$options = $this->db->get_where($this->tblOptions, array('id' => $v5))->row_array();
				$optionValuesList = explode(',', $options['optionValues']);
				$options['optionValues'] = array();
				foreach ($optionValuesList as  $v7) {
					if (!$v7) continue;
					$optionValues = $this->db->get_where($this->tblOptionValues, array('id' => $v7))->row_array();
					$optionValues['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $optionValues['descriptions']);
					$newArr = customFilterArray($optionValues['descriptions'], $lang);
					$optionValues['description'] = count($newArr) > 0 && $newArr[0] ? $newArr[0] : null;
					array_push($options['optionValues'], $optionValues);
				}
				array_push($product['options'], $options);
			}

			// Product Price
			$productPrice = $this->db->get_where($this->tblProductPrice, array('id' => $product['productPrice']))->row_array();
			$productPrice['description'] = $this->db->get_where($this->tblDescription, array('id' => $productPrice['description']))->row_array();
			$product['productPrice'] = $productPrice;

			// Product Specification
			$productSpecification = $this->db->get_where($this->tblProductSpecification, array('id' => $product['productSpecifications']))->row_array();
			$product['productSpecifications'] = $productSpecification;

			// Properties
			$propertyList = explode(',', $product['properties']);
			$product['properties'] = array();
			foreach ($propertyList as  $v8) {
				$properties = $this->db->get_where($this->tblProperties, array('id' => $v8))->row_array();
				$property = $this->db->get_where($this->tblProperty, array('id' => $properties['property']))->row_array();
				if (!$property['optionValues']) $property['optionValues'] = array();
				$propertyValue = $this->db->get_where($this->tblPropertyValue, array('id' => $properties['propertyValue']))->row_array();
				if (!$propertyValue['values']) $propertyValue['values'] = array();
				$properties['property'] = $property;
				$properties['propertyValue'] = $propertyValue;
				array_push($product['properties'], $properties);
			}
			// Type
			$type = $this->db->get_where($this->tblPropertyType, array('id' => $product['type']))->row_array();
			$type['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $type['descriptions']);
			$newArr = customFilterArray($type['descriptions'], $lang);
			$type['description'] = count($newArr) > 0 && $newArr[0] ? $newArr[0] : null;
			$product['type'] = $type;

			return $product;
		}
	}

	/*
		** function: getCartByCode(...)
		*** main Param: customer, code, products, quantity
		**** param desc: products & quantity format: 1,2,3,4
	*/

	function getCartByCode($cart, $lang, $store)
	{
		$where = array('code' => $cart);
		$data = $this->db->get_where($this->tblCart, $where)->row_array();

		if ($data) {
			$products = explode(',', $data['products']);
			$quantities = explode(',', $data['quantity']);

			// Get the whole products detail
			$data['products'] = array();
			$totalPrice  = 0;
			$curTotalPrice = 0;
			foreach ($products as $k3 => $v3) {
				if (!$v3) continue;
				$product = $this->getFullProductDetails($v3, $lang);
				$curTotalPrice = (int)$product['price'] * (int)$quantities[$k3];
				$totalPrice  = $curTotalPrice + $totalPrice;
				$product['quantity'] = (int)$quantities[$k3];
				$product['subTotal'] = $product['total'] = $curTotalPrice;
				$product['displaySubTotal'] = $product['displayTotal'] = "USD" . number_format($curTotalPrice, 2);
				array_push($data['products'], $product);
			}

			// Calc whole quantity
			$quantity = 0;
			foreach ($quantities as $k4 => $v4) {
				if (!$v4) continue;
				$quantity = $quantity + (int)$v4;
			}

			$data['quantity'] = $quantity;
			$data['total'] = $data['subTotal'] = $totalPrice;
			$data['displayTotal'] = $data['displaySubTotal'] = "USD" . number_format($totalPrice, 2);
			$data['totals'] = $this->db->get($this->tblTotal)->result_array();
			$data['totals'][0]['value'] = number_format($curTotalPrice, 2);
			$data['totals'][1]['value'] = number_format($curTotalPrice, 2);

			$this->db->where($where)->update($this->tblCart, array('total' => $totalPrice, 'subTotal' => $totalPrice));
		}
		return $data;
	}

	function addNewCart($pData)
	{
		if (isset($pData['code'])) {
			$where = array('code' => $pData['code']);
			$data = $this->db->get_where($this->tblCart, $where)->row_array();

			$dupliCatedProductId = false;

			$products = explode(',', $data['products']);
			$quantities = explode(',', $data['quantity']);

			// Update current record product & quantity
			foreach ($products as $k1 => $v1) {
				if (!$v1) continue;
				if ($v1 == $pData['productId']) {
					$quantities[$k1] = $pData['quantity'];
					$dupliCatedProductId = true;
				}
			}
			if (!$dupliCatedProductId) {
				$data['products'] = $data['products'] . $pData['productId'] . ',';
				$data['quantity'] = $data['quantity'] . $pData['quantity'] . ',';
			} else {
				// update quantity & product when productID duplicates on the products
				$data['products'] = '';
				$data['quantity'] = '';
				foreach ($quantities as $k2 => $v2) {
					if ($v2 == 0) unset($products[$k2]);
					if (!$v2) continue;
					$data['quantity'] = $data['quantity'] . $v2 . ',';
				}
				foreach ($products as $k4 => $v4) {
					if (!$v4) continue;
					$data['products'] = $data['products'] . $v4 . ',';
				}
			}
			$this->db->where($where);
			$this->db->update($this->tblCart, $data);
		} else {
			if (isset($pData['customerId'])) {
				$newData['products'] = $pData['productId'] . ',';
				$newData['quantity'] = $pData['quantity'] . ',';
				$newData['customer'] = $pData['customerId'];
				$newData['code'] = md5($pData['customerId']);
				$this->db->insert($this->tblCart, $newData);
				return $newData['code'];
			} else {
				$newData['products'] = $pData['productId'] . ',';
				$newData['quantity'] = $pData['quantity'] . ',';
				$newData['customer'] = $this->uniqueCode(mt_rand(10000000, 99999999));
				$newData['code'] = md5($this->uniqueCode(mt_rand(10000000, 99999999)));
				$this->db->insert($this->tblCart, $newData);
				return $newData['code'];
			}
		}
		return $pData['code'];
	}

	// function get_AddCart($pData = null, $cart = null, $promoCart = null, $lang = null, $customer = null)
	// {
	// 	if (!$cart) {
	// 		$customerId = isset($pData['customerId']) ? $pData['customerId'] : $this->uniqueCode(mt_rand(10000000, 99999999));
	// 		$where = array('customer' => $customerId);
	// 		$data = $this->db->get_where($this->tblCart, $where)->row_array();
	// 		$data['code'] = md5($customerId);
	// 		$data['customer'] = $customerId;
	// 	} else {
	// 		if (!IsNullOrEmptyString($promoCart)) {
	// 			$this->db->set('promoCode', $promoCart); //value that used to update column
	// 			$this->db->where('code', $cart); //which row want to upgrade
	// 			$this->db->update($this->tblCart); //table name
	// 		}
	// 		$where = array('code' => $cart);
	// 		$data = $this->db->get_where($this->tblCart, $where)->row_array();
	// 	}

	// 	if ($data && isset($data['products']) && $data['quantity']) {
	// 		$dupliCatedProductId = false;

	// 		$products = explode(',', $data['products']);
	// 		$quantities = explode(',', $data['quantity']);

	// 		// Update current record product & quantity
	// 		foreach ($products as $k1 => $v1) {
	// 			if (!$v1) continue;
	// 			if ($v1 == $pData['productId']) {
	// 				$quantities[$k1] = $pData['quantity'];
	// 				$dupliCatedProductId = true;
	// 			}
	// 		}
	// 		if (!$dupliCatedProductId) {
	// 			$data['products'] = $data['products'] . $pData['productId'] . ',';
	// 			$data['quantity'] = $data['quantity'] . $pData['quantity'] . ',';
	// 		} else {
	// 			// update quantity & product when productID duplicates on the products
	// 			$data['products'] = '';
	// 			$data['quantity'] = '';
	// 			foreach ($quantities as $k2 => $v2) {
	// 				if ($v2 == 0) unset($products[$k2]);
	// 				if (!$v2) continue;
	// 				$data['quantity'] = $data['quantity'] . $v2 . ',';
	// 			}
	// 			foreach ($products as $k4 => $v4) {
	// 				if (!$v4) continue;
	// 				$data['products'] = $data['products'] . $v4 . ',';
	// 			}
	// 		}
	// 		$this->db->where($where);
	// 		$this->db->update($this->tblCart, $data);
	// 	} else {
	// 		// Insert New cart when login
	// 		$newData['products'] = isset($pData['productId']) ? $pData['productId'] . ',' : null;
	// 		$newData['quantity'] = isset($pData['quantity']) ? $pData['quantity'] . ',' : null;
	// 		$newData['code'] = $cart;
	// 		$newData['customer'] = isset($pData['customerId']) ? $pData['customerId'] : $customer;

	// 		$data = $this->db->get_where($this->tblCart, array('code' => $cart))->row_array();
	// 		if ($data) {
	// 			$this->db->where(array('code' => $cart))->update($this->tblCart, $newData);
	// 		} else {
	// 			$this->db->insert($this->tblCart, $newData);
	// 			$id = $this->db->insert_id();
	// 			$data = $this->db->get_where($this->tblCart, array('id' => $id))->row_array();
	// 		}
	// 		$products = explode(',', $data['products']);
	// 		$quantities = explode(',', $data['quantity']);
	// 	}

	// 	$totals = $this->db->get($this->tblTotal)->result_array();

	// 	// Get the whole products detail
	// 	$data['products'] = array();
	// 	$totalPrice  = 0;
	// 	$curTotalPrice = 0;
	// 	foreach ($products as $k3 => $v3) {
	// 		if (!$v3) continue;
	// 		$product = $this->getFullProductDetails($v3);
	// 		$curTotalPrice = $product['price'] * (int)$quantities[$k3];
	// 		$totalPrice  = $curTotalPrice + $totalPrice;
	// 		$product['quantity'] = (int)$quantities[$k3];
	// 		$product['subTotal'] = $product['total'] = $curTotalPrice;
	// 		$product['displaySubTotal'] = $product['displayTotal'] = "USD" . number_format($curTotalPrice, 2);
	// 		array_push($data['products'], $product);
	// 	}

	// 	// Calc whole quantity
	// 	$quantity = 0;
	// 	foreach ($quantities as $k4 => $v4) {
	// 		if (!$v4) continue;
	// 		$quantity = $quantity + (int)$v4;
	// 	}

	// 	$data['quantity'] = $quantity;
	// 	$data['total'] = $data['subTotal'] = $totalPrice;
	// 	$data['displayTotal'] = $data['displaySubTotal'] = "USD" . number_format($totalPrice, 2);
	// 	$data['totals'] = $totals;
	// 	$data['totals'][0]['value'] = number_format($curTotalPrice, 2);
	// 	$data['totals'][1]['value'] = number_format($curTotalPrice, 2);

	// 	$this->db->where($where)->update($this->tblCart, array('total' => $totalPrice, 'subTotal' => $totalPrice));

	// 	return $data;
	// }

	function updateCartWithCustomerIDWhenLogin($customer_id, $existingToken)
	{
		$originRecord = $this->db->get_where($this->tblCart, array('customer' => $customer_id))->row_array();
		$newRecord = $this->db->get_where($this->tblCart, array('code' => $existingToken))->row_array();
		$newRecord['customer'] = $customer_id;
		// if (count($originRecord) > 0) {
		// 	$newRecord['products'] = $newRecord['products'] . $originRecord['products'];
		// 	$newRecord['quantity'] = $newRecord['quantity'] . $originRecord['quantity'];

		// 	$this->db->where(array('customer' => $customer_id))->delete($this->tblCart);
		// }
		$this->db->where(array('code' => $existingToken))->update($this->tblCart, $newRecord);
	}

	function uniqueCode($rndNum)
	{
		$q = $this->db->get_where($this->tblCart, array('customer' => $rndNum));
		if ($q->num_rows() > 0)
			return $this->uniqueCode(mt_rand(10000000, 99999999));
		else
			return $rndNum;
	}

	// function get_UserCart($cart, $customer, $lang, $store)
	// {
	// 	$cart = $this->get_AddCart(null, $cart, null, $lang, $customer);
	// 	return $cart;
	// }

	// function get_UserPromoCart($pData)
	// {
	// 	$cart = $this->get_AddCart(null, $pData['code'], $pData['promoCart'], $pData['lang']);
	// 	return $cart;
	// }

	function updateCartPromo($pData)
	{
		$where = array('code' => $pData['cart']);
		$data = array('promoCode' => $pData['promoCart'], 'language' => $pData['lang']);
		$this->db->where($where);
		$this->db->update($this->tblCart, $data);
		$cart = $this->getCartByCode($pData['cart'], $pData['lang'], $pData['store']);
		return $cart;
	}

	// function updateCart($pData, $lang)
	// {
	// 	$where = array('code' => $pData['code']);
	// 	$data = $this->db->get_where($this->tblCart, $where)->row_array();

	// 	if (!$data) return;

	// 	$products = explode(',', $data['products']);
	// 	$quantities = explode(',', $data['quantity']);

	// 	foreach ($products as $k1 => $v1) {
	// 		if (!$v1) continue;
	// 		if ($v1 == $pData['productId']) {
	// 			$quantities[$k1] = $pData['quantity'];
	// 		}
	// 	}

	// 	$quantity = '';
	// 	foreach ($quantities as $k2 => $v2) {
	// 		if (!$v2) continue;
	// 		$quantity = $quantity . $v2 . ',';
	// 	}

	// 	$where = array('code' => $pData['code']);
	// 	$data['quantity'] = $quantity;
	// 	$this->db->where($where);
	// 	$this->db->update($this->tblCart, $data);

	// 	$cart = $this->get_AddCart(null, $pData['code'], null, $lang);
	// 	return $cart;
	// }

	function deleteCart($code, $productId, $store)
	{
		$where = array('code' => $code);
		$data = $this->db->get_where($this->tblCart, $where)->row_array();

		if (!$data) return false;

		$products = explode(',', $data['products']);
		$quantities = explode(',', $data['quantity']);

		foreach ($products as $k1 => $v1) {
			if (!$v1) continue;
			if ($v1 == $productId) {
				unset($products[$k1]);
				unset($quantities[$k1]);
			}
		}

		$product = '';
		$quantity = '';
		foreach ($quantities as $k2 => $v2) {
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
		$shipping = $this->db->get($this->tblShipping)->row_array();
		$shipping['delivery'] = $this->db->get_where($this->tblUserDelivery, array('id' => $shipping['delivery']))->row_array();
		$shippingOptions = explode(',', $shipping['shippingOptions']);
		$shipping['shippingOptions'] = array();
		foreach ($shippingOptions as $k => $v) {
			if (!$v) continue;
			$options = $this->db->get_where($this->tblShippingOptions, array('id' => $v))->row_array();
			array_push($shipping['shippingOptions'], $options);
		}

		return $shipping;
	}

	function get_Total($code, $quote = null)
	{
		$cart = $this->db->get_where($this->tblCart, array('code' => $code))->row_array();
		$totals = $this->db->get($this->tblTotal)->result_array();
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
