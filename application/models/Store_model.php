<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Store_model extends CI_Model
{
	public $tblStore = 'tbl_store';
	public $tblUserDelivery = 'tbl_user_delivery';
	public $tblLogo = 'tbl_logo';
	public $tblSupportedLanguages = 'tbl_supported_languages';

	public function __construct()
	{
		parent::__construct();
	}

	function get_Default($store, $names, $list)
	{
		if (!$list) {
			if (!$names)
				$default = $this->db->select('*')->get_where($this->tblStore, array('code' => $store))->row_array();
			else
				$default = $this->db->select('*')->get($this->tblStore)->result_array();
		} else
			$default = $this->db->select('*')->get($this->tblStore)->result_array();

		if ($default) {
			if (array_key_exists('id', $default)) {
				$default = $this->defaultDetail($default['address'], $default['logo'], $default);
			} else {
				foreach ($default as $k => $v) {
					$default[$k] = $this->defaultDetail($v['address'], $v['logo'], $default[$k]);
				}
			}
			return $default;
		}
		return null;
	}

	function defaultDetail($defAddress, $defLogo, $default)
	{
		$deliveryAddress = $this->db->get_where($this->tblUserDelivery, array('id' => $defAddress))->row_array();
		$logo = $this->db->get_where($this->tblLogo, array('id' => $defLogo))->row_array();
		$default['address'] = $deliveryAddress;
		$default['logo'] = $logo;
		$default['supportedLanguages'] = GetTableDetails($this, $this->tblSupportedLanguages, 'id', $default['supportedLanguages']);
		return $default;
	}

	function update_Store($pData, $update = true)
	{
		// Update Delivery address table
		$delivery = array(
			'address' => $pData['address']['address'], 'city' => $pData['address']['city'],
			'country' => $pData['address']['country'], 'postalCode' => $pData['address']['postalCode'],
			'stateProvince' => $pData['address']['stateProvince']
		);

		if ($update)
			$this->db->where(array('id' => $pData['addressId']))->update($this->tblUserDelivery, $delivery);
		else {
			$delivery['userId'] = $pData['userId'];
			$this->db->insert($this->tblUserDelivery, $delivery);
			$deliveryId = $this->db->insert_id();
		}

		// Update Default table
		$default = array(
			'currency' => $pData['currency'], 'currencyFormatNational' => $pData['currencyFormatNational'],
			'defaultLanguage' => $pData['defaultLanguage'], 'dimension' => $pData['dimension'],
			'email' => $pData['email'], 'inBusinessSince' => $pData['inBusinessSince'],
			'name' => $pData['name'], 'phone' => $pData['phone'],
			'retailer' => $pData['retailer'], 'useCache' => $pData['useCache'],
			'weight' => $pData['weight'], 'code' => $pData['code']
		);

		$supportedLanguages = $this->db->select('*')->get($this->tblSupportedLanguages)->result_array();
		$supportedLanguageStr = '';
		foreach ($supportedLanguages as $k1 => $v1) {
			foreach ($pData['supportedLanguages'] as $k2 => $v2) {
				if ($v1['code'] == $v2) {
					$supportedLanguageStr = $supportedLanguageStr . $v1['id'] . ',';
					continue;
				}
			}
		}
		$default['supportedLanguages'] = $supportedLanguageStr;

		if ($update)
			$this->db->where(array('code' => $pData['code']))->update($this->tblStore, $default);
		else {
			$default['address'] = $deliveryId;
			$this->db->insert($this->tblStore, $default);
		}

		return true;
	}
} // END
