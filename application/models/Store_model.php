<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Store_model extends CI_Model
{
	public $tblDefault = 'tbl_default';
	public $tblUserDelivery = 'tbl_user_delivery';
	public $tblLogo = 'tbl_logo';
	public $tblSupportedLanguages = 'tbl_supported_languages';

	public function __construct()
	{
		parent::__construct();
	}

	function get_Default($store, $names)
	{
		if (!$names)
			$default = $this->db->select('*')->get_where($this->tblDefault, array('code' => $store))->row_array();
		else
			$default = $this->db->select('*')->get_where($this->tblDefault, array('code' => $store))->result_array();

		if ($default) {
			if (count($default) > 1) {
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
		$supportedLanguages = $this->db->select('*')->get($this->tblSupportedLanguages)->result_array();
		$default['address'] = $deliveryAddress;
		$default['logo'] = $logo;
		$default['supportedLanguages'] = $supportedLanguages;
		return $default;
	}
} // END
