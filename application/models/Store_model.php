<?php
defined('BASEPATH') OR exit('No direct script access allowed');

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

	function get_Default()
	{
		$default = $this->db->select('*')->get($this->tblDefault)->row_array();
		$deliveryAddress = $this->db->get_where($this->tblUserDelivery, array('id' => $default['address']))->row_array();
		$logo = $this->db->get_where($this->tblLogo, array('id' => $default['logo']))->row_array();
		$supportedLanguages = $this->db->select('*')->get($this->tblSupportedLanguages)->result_array();
		$default['address'] = $deliveryAddress;
		$default['logo'] = $logo;
		$default['supportedLanguages'] = $supportedLanguages;
		return $default;
	}
} // END
