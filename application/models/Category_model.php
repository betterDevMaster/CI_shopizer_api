<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Category_model extends CI_Model
{
	public $tblProducts = 'tbl_products';
	public $tblCategories = 'tbl_categories';
	public $tblDescription = 'tbl_description';
	public $tblManufacturer = 'tbl_manufacturer';
	public $tblOptions = 'tbl_options';
	public $tblOptionValues = 'tbl_option_values';

	public function __construct()
	{
		parent::__construct();
	}

	function get_CategoryDetail($id, $store, $lang, $count = null, $page = null, $filter = null, $code = null)
	{
		if (!$filter)
			$category = $this->db->select('*')->limit($count, $count * $page)->get_where($this->tblCategories, array('id' => $id))->result_array();
		else {
			if (!$code)
				$category = $this->db->select('*')->get($this->tblCategories, $count, $count * $page)->result_array();
			else {
				$this->db->where("code LIKE '%$code%'");
				$category = $this->db->get($this->tblCategories, $count, $count * $page)->result_array();
			}
		}

		for ($i = 0; $i < count($category); $i++) {
			$category[$i]['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $category[$i]['descriptions']);
			$category[$i]['description'] = count($category[$i]['descriptions']) > 0 && $category[$i]['descriptions'][0] ? $category[$i]['descriptions'][0] : null;
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

	function updateCategory($pData)
	{
		$descriptions = '';
		foreach ($pData['descriptions'] as $k => $v) {
			unset($v['id']);
			$this->db->insert($this->tblDescription, $v);
			$insertId = $this->db->insert_id();
			$descriptions = $descriptions . $insertId . ',';
		}

		$where = array('id' => $pData['id']);
		$data = array(
			'code' => $pData['code'],
			'descriptions' => $descriptions,
			'parent' => $pData['parent']['id'],
			'sortOrder' => $pData['sortOrder'],
			'store' => $pData['store'],
			'visible' => $pData['visible'],
		);
		$this->db->where($where)->update($this->tblCategories, $data);
		return $pData;
	}

	function addCategory($pData)
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
			'parent' => $pData['parent']['id'],
			'sortOrder' => $pData['sortOrder'],
			'store' => isset($pData['store']) ? $pData['store'] : 'DEFAULT',
			'visible' => $pData['visible'],
			'depth' => isset($pData['depth']) ? $pData['depth'] : 0,
			'featured' => isset($pData['featured']) ? $pData['featured'] : false,
		);
		$this->db->insert($this->tblCategories, $data);
		return $pData;
	}

	function visible($pData)
	{
		$where = array('id' => $pData['id']);
		$data = array(
			'visible' => $pData['visible'],
		);
		$this->db->where($where)->update($this->tblCategories, $data);
		return $pData;
	}

	function moveCategory($pData)
	{
		$where = array('id' => $pData['childId']);
		$data = array(
			'parent' => $pData['parentId'] == '-1' ? null : $pData['parentId'],
		);
		var_dump($where);
		var_dump($data);
		$this->db->where($where)->update($this->tblCategories, $data);
		return true;
	}
} // END
