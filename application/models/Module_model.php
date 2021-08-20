<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Module_model extends CI_Model
{
	public $tblModules = 'tbl_modules';
	public $tblModulesDetail = 'tbl_modules_detail';
	public $tblShippingCountry = 'tbl_shipping_country';
	public $tblShippingServicesDomestic = 'tbl_shipping_services_domestic';
	public $tblShippingServicesIntl = 'tbl_shipping_services_intl';
	public $tblShippingServicesUsa = 'tbl_shipping_services_usa';
	public $tblShippingPackages = 'tbl_shipping_packages';
	public $tblShippingInterationKeys = 'tbl_shipping_integration_keys';
	public $tblShippingInterationOptions = 'tbl_shipping_integration_options';
	public $tblShippingOrigin = 'tbl_shipping_origin';
	public $tblZone = 'tbl_zone';

	public function __construct()
	{
		parent::__construct();
	}

	function getExpedition($store)
	{
		$zoneList = $this->db->get($this->tblShippingCountry)->result_array();
		$expeditions = array();
		foreach ($zoneList as $k => $v) {
			array_push($expeditions, $v['countryCode']);
		}
		return $expeditions;
	}

	function updateExpedition($pData, $store)
	{
		$country = array();
		foreach ($pData['shipToCountry'] as $k => $v) {
			$country[$k]['countryCode'] = $v;
		}
		$this->db->truncate($this->tblShippingCountry);
		$this->db->insert_batch($this->tblShippingCountry, $country);
		return $country;
	}

	function getShippingMethods($type)
	{
		$detail = $this->db->get_where($this->tblModulesDetail, array('moduleCode' => $type))->row_array();
		if ($detail) {
			$detail['integrationKeys'] = $this->db->get_where($this->tblShippingInterationKeys, array('id' => $detail['integrationKeys']))->row_array();
			$detail['integrationOptions'] = $this->db->get_where($this->tblShippingInterationOptions, array('id' => $detail['integrationOptions']))->row_array();
			if ($detail['integrationOptions']) {
				$detail['integrationOptions']['services-domestic'] = GetTableDetails($this, $this->tblShippingServicesDomestic, 'id', $detail['integrationOptions']['services-domestic']);
				$detail['integrationOptions']['services-intl'] = GetTableDetails($this, $this->tblShippingServicesIntl, 'id', $detail['integrationOptions']['services-intl']);
				$detail['integrationOptions']['services-usa'] = GetTableDetails($this, $this->tblShippingServicesUsa, 'id', $detail['integrationOptions']['services-usa']);
			}
		} else {
			$detail = array(
				'active' => false,
				'defaultSelected' => false,
				'environment' => null,
				'integrationKeys' => null,
				'integrationOptions' => null,
				'moduleCode' => $type
			);
		}

		return $detail;
	}

	function updateShippingMethods($pData)
	{
		$detail = $this->db->get_where($this->tblModulesDetail, array('moduleCode' => $pData['code']))->row_array();

		// Integration Keys table
		$insertKeysId = 0;
		if (isset($pData['integrationKeys']) && count($pData['integrationKeys']) > 0) {
			$keysData = array(
				// Payment Canada Post
				'account' => isset($pData['integrationKeys']['account']) ? $pData['integrationKeys']['account'] : null,
				'apikey' => isset($pData['integrationKeys']['apikey']) ? $pData['integrationKeys']['apikey'] : null,
				'password' => isset($pData['integrationKeys']['password']) ? $pData['integrationKeys']['password'] : null,
				'username' => isset($pData['integrationKeys']['username']) ? $pData['integrationKeys']['username'] : null,
				'accessKey' => isset($pData['integrationKeys']['accessKey']) ? $pData['integrationKeys']['accessKey'] : null,
				'userId' => isset($pData['integrationKeys']['userId']) ? $pData['integrationKeys']['userId'] : null,
				// Payment Store Pick Up
				'note' => isset($pData['integrationKeys']['note']) ? $pData['integrationKeys']['note'] : null,
				'price' => isset($pData['integrationKeys']['price']) ? $pData['integrationKeys']['price'] : null,
				// Payment Money Order
				'address' => isset($pData['integrationKeys']['address']) ? $pData['integrationKeys']['address'] : null,
				// Payment Express checkout
				'api' => isset($pData['integrationKeys']['api']) ? $pData['integrationKeys']['api'] : null,
				'signature' => isset($pData['integrationKeys']['signature']) ? $pData['integrationKeys']['signature'] : null,
				'transaction' => isset($pData['integrationKeys']['transaction']) ? $pData['integrationKeys']['transaction'] : null,
				// Payment Beanstream
				'merchantid' => isset($pData['integrationKeys']['merchantid']) ? $pData['integrationKeys']['merchantid'] : null,
				// Payment Stripe
				'publishableKey' => isset($pData['integrationKeys']['publishableKey']) ? $pData['integrationKeys']['publishableKey'] : null,
				'secretKey' => isset($pData['integrationKeys']['secretKey']) ? $pData['integrationKeys']['secretKey'] : null,
				// Payment Braintree
				'private_key' => isset($pData['integrationKeys']['private_key']) ? $pData['integrationKeys']['private_key'] : null,
				'public_key' => isset($pData['integrationKeys']['public_key']) ? $pData['integrationKeys']['public_key'] : null,
				'tokenization_key' => isset($pData['integrationKeys']['tokenization_key']) ? $pData['integrationKeys']['tokenization_key'] : null,
			);
			if (!$detail['integrationKeys']) {
				$this->db->insert($this->tblShippingInterationKeys, $keysData);
				$insertKeysId = $this->db->insert_id();
			} else {
				$this->db->where(array('id' => $detail['integrationKeys']))->update($this->tblShippingInterationKeys, $keysData);
			}
		}

		// Integration Options table
		$insertOptionsId = 0;
		if (isset($pData['integrationKeys']) && count($pData['integrationKeys']) > 0) {
			if (isset($pData['integrationOptions']['services-domestic'])) {
				$domestic = $this->db->select('*')->where_in('code', $pData['integrationOptions']['services-domestic'])->get($this->tblShippingServicesDomestic)->result_array();
				$domestics = '';
				foreach ($domestic as $v1) {
					$domestics = $domestics . $v1['id'] . ',';
				}
			}
			if (isset($pData['integrationOptions']['services-domestic'])) {
				$intl = $this->db->select('*')->where_in('code', $pData['integrationOptions']['services-intl'])->get($this->tblShippingServicesIntl)->result_array();
				$intls = '';
				foreach ($intl as $v1) {
					$intls = $intls . $v1['id'] . ',';
				}
			}
			if (isset($pData['integrationOptions']['services-domestic'])) {
				$usa = $this->db->select('*')->where_in('code', $pData['integrationOptions']['services-usa'])->get($this->tblShippingServicesUsa)->result_array();
				$usas = '';
				foreach ($usa as $v1) {
					$usas = $usas . $v1['id'] . ',';
				}
			}
			if (
				isset($pData['integrationOptions']['services-domestic'])
				&& isset($pData['integrationOptions']['services-domestic'])
				&& isset($pData['integrationOptions']['services-domestic'])
			) {
				$optionsData = array(
					'services-domestic' => $domestics,
					'services-intl' => $intls,
					'services-usa' => $usas,
				);
				if (!$detail['integrationOptions']) {
					$this->db->insert($this->tblShippingInterationOptions, $optionsData);
					$insertOptionsId = $this->db->insert_id();
				} else {
					$this->db->where(array('id' => $detail['integrationOptions']))->update($this->tblShippingInterationOptions, $optionsData);
				}
			}
		}
		// MethodsDetail table
		$detailData = array(
			'active' => (int)$pData['active'],
		);
		if ($detail) {
			$detailWhere = array('code' => $pData['code']);
			$data = array('configured' => (int)$pData['defaultSelected']);
			$detailData = $detailData + $data;
			$this->db->where($detailWhere)->update($this->tblModules, $detailData);
		} else {
			$detailWhere = array('id' => $detail['id']);
			$data = array(
				'defaultSelected' => (int)$pData['defaultSelected'],
				'moduleCode' => $pData['code'],
				'integrationKeys' => $insertKeysId,
				'integrationOptions' => $insertOptionsId,
			);
			$detailData = $detailData + $data;
			$this->db->insert($this->tblModulesDetail, $detailData);
		}

		return true;
	}

	function updateShippingOrigin($pData)
	{
		$this->db->truncate($this->tblShippingOrigin);
		$this->db->insert($this->tblShippingOrigin, $pData);
		return $this->db->insert_id();
	}

	function addShippingPackage($pData)
	{
		$this->db->insert($this->tblShippingPackages, $pData);
		return $this->db->insert_id();
	}

	function updateShippingPackage($pData)
	{
		$this->db->where(array('code' => $pData['code']))->update($this->tblShippingPackages, $pData);
		return true;
	}
} // END
