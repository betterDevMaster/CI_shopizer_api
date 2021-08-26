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

	function getCategoryList($id = null, $code = null, $store = null, $lang = null, $count = null, $page = null, $filter = null)
	{
		$category = $this->db->select('*')->get_where($this->tblCategories, array('parent' => $id))->result_array();
		for ($i = 0; $i < count($category); $i++) {
			$category[$i]['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $category[$i]['descriptions']);
			$category[$i]['description'] = count($category[$i]['descriptions']) > 0 && $category[$i]['descriptions'][0] ? $category[$i]['descriptions'][0] : null;
			$category[$i]['parent'] = $this->db->select('id, code')->get_where($this->tblCategories, array('id' => $category[$i]['parent']))->row_array();
			$category[$i]['children'] = $this->getCategoryList($category[$i]['id']);
		}
		return $category;
	}

	function getCategoryDetailById($id = null, $code = null, $store = null, $lang = null)
	{
		$category = $this->db->select('*')->get_where($this->tblCategories, array('id' => $id))->row_array();
		if ($code) {
			$this->db->where("code LIKE '%$code%'");
			$category = $this->db->select('*')->get_where($this->tblCategories, array('id' => $id))->result_array();
		}
		$category['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $category['descriptions']);
		$category['description'] = count($category['descriptions']) > 0 && $category['descriptions'][0] ? $category['descriptions'][0] : null;
		$category['parent'] = $this->db->select('id, code')->get_where($this->tblCategories, array('id' => $category['parent']))->row_array();
		return $category;
	}

	function getManufacturers($store, $lang)
	{
		$manufacturers = $this->db->get($this->tblManufacturer)->result_array();
		for ($i = 0; $i < count($manufacturers); $i++) {
			$manufacturers[$i]['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $manufacturers[$i]['descriptions']);
			$manufacturers[$i]['description'] = count($manufacturers[$i]['descriptions']) > 0 && $manufacturers[$i]['descriptions'][0] ? $manufacturers[$i]['descriptions'][0] : null;
		}
		return $manufacturers;
	}

	function getVariants($pData)
	{
		$id = $pData['id'];
		$options = $this->db->get_where($this->tblOptions, array('id' => $id))->row_array();
		$options['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $options['descriptions']);
		$options['description'] = count($options['descriptions']) > 0 && $options['descriptions'][0] ? $options['descriptions'][0] : null;
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
		// Child - Parent
		$where = array('id' => $pData['childId']);
		$data = array(
			'parent' => $pData['parentId'] == '-1' ? 0 : $pData['parentId'],
		);
		$this->db->where($where)->update($this->tblCategories, $data);
		return true;
	}
} // END
