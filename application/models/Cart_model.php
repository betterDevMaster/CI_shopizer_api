<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Cart_model extends CI_Model
{
	public $tblProducts = 'tbl_products';
	public $tblCart = 'tbl_cart';

	public function __construct()
	{
		parent::__construct();
	}

	function get_NewCart($pData)
	{
		$existingCart = $this->db->select('*')->get($this->tblCart)->row_array();
		$product = $this->db->select('*')->get_where($this->tblProducts, array('id' => $pData['product']))->row_array();
	}
} // END
