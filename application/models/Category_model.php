<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Category_model extends CI_Model
{
	public $tblProducts = 'tbl_products';
	public $tblCategories = 'tbl_categories';
	public $tblDescription = 'tbl_description';
	public $tblManufacturer = 'tbl_manufacturer';
	public $tblOptions = 'tbl_options';
	public $tblOptionValues = 'tbl_option_value';

	public function __construct()
	{
		parent::__construct();
	}

	function get_CategoryDetail($id, $store, $lang)
	{
		$category = $this->db->select('*')->get_where($this->tblCategories, array('id' => $id))->result_array();
		for ($i = 0; $i < count($category); $i++) {
			$category[$i]['description'] = $this->db->select('*')->get_where($this->tblDescription, array('id' => $category[$i]['description']))->row_array();
			$category[$i]['parent'] = $this->db->select('id, code')->get_where($this->tblCategories, array('id' => $category[$i]['parent']))->row_array();
			$category[$i]['children'] = $this->get_CategoryDetail($category[$i]['children'],  $store, $lang);
		}
		return $category;
	}

	function get_Manufacturers($pData)
	{
		$manufacturers = $this->db->select('*')->get($this->tblManufacturer)->result_array();
		for ($i = 0; $i < count($manufacturers); $i++) {
			$manufacturers[$i]['description'] = $this->db->select('*')->get_where($this->tblDescription, array('id' => $manufacturers[$i]['description']))->row_array();
		}
		return $manufacturers;
	}

	function get_Variants($pData)
	{
		$id = $pData['id'];
		$this->db->where("optionValues LIKE '%$id%'");
		$options = $this->db->get($this->tblOptions)->result_array();

		foreach ($options as $k1 => $v1) {
			$optionValuesList = explode(',', $v1['optionValues']);
			$options[$k1]['optionValues'] = array();
			foreach ($optionValuesList as $k2 => $v2) {
				if (!$v2) continue;
				$optionValues = $this->db->select('*')->get_where($this->tblOptionValues, array('id' => $v2))->row_array();
				$optionValues['description'] = $this->db->select('*')->get_where($this->tblDescription, array('id' => $optionValues['description']))->row_array();
				array_push($options[$k1]['optionValues'], $optionValues);
			}
		}
		return $options;
	}
} // END
