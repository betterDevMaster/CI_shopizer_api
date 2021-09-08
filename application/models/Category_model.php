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

	function getCategoryList($id = 0, $count = null, $page = null, $code = null, $store = null, $lang = null, $filter = null)
	{
		$category = $this->db->limit($count, $count * $page)->get($this->tblCategories)->result_array();
		// $category = $this->db->limit($count, $count * $page)->get_where($this->tblCategories, array('parent' => $id))->result_array();
		for ($i = 0; $i < count($category); $i++) {
			$category[$i]['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $category[$i]['descriptions']);
			$newArr = customFilterArray($category[$i]['descriptions'], $lang);
			$category[$i]['description'] = count($newArr) > 0 && $newArr[0] ? $newArr[0] : null;
			$category[$i]['parent'] = $this->db->select('id, code')->get_where($this->tblCategories, array('id' => $category[$i]['parent']))->row_array();
			// $category[$i]['children'] = $this->getCategoryList($category[$i]['id'], $count, $page);
		}
		return $category;
	}

	function getCategoryHierarchyList($id = 0, $count = null, $page = null, $store = null, $lang = null)
	{
		$category = $this->db->get_where($this->tblCategories, array('parent' => $id))->result_array();
		for ($i = 0; $i < count($category); $i++) {
			$category[$i]['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $category[$i]['descriptions']);
			$newArr = customFilterArray($category[$i]['descriptions'], $lang);
			$category[$i]['description'] = count($newArr) > 0 && $newArr[0] ? $newArr[0] : null;
			$category[$i]['parent'] = $this->db->select('id, code')->get_where($this->tblCategories, array('id' => $category[$i]['parent']))->row_array();
			$category[$i]['children'] = $this->getCategoryHierarchyList($category[$i]['id'], $count, $page, $store, $lang);
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
		$newArr = customFilterArray($category['descriptions'], $lang);
		$category['description'] = count($newArr) > 0 && $newArr[0] ? $newArr[0] : null;
		$category['parent'] = $this->db->select('id, code')->get_where($this->tblCategories, array('id' => $category['parent']))->row_array();
		return $category;
	}

	function getManufacturers($store, $lang)
	{
		$manufacturers = $this->db->get($this->tblManufacturer)->result_array();
		for ($i = 0; $i < count($manufacturers); $i++) {
			$manufacturers[$i]['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $manufacturers[$i]['descriptions']);
			$newArr = customFilterArray($manufacturers[$i]['descriptions'], $lang);
			$manufacturers[$i]['description'] = count($newArr) > 0 && $newArr[0] ? $newArr[0] : null;
		}
		return $manufacturers;
	}

	function getVariants($pData)
	{
		$options = $this->db->get_where($this->tblOptions, array('id' => $pData['id']))->row_array();
		if ($options) {
			$options['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $options['descriptions']);
			$newArr = customFilterArray($options['descriptions'], $pData['lang']);
			$options['description'] = count($newArr) > 0 && $newArr[0] ? $newArr[0] : null;
		}
		return $options;
	}

	function updateCategory($pData)
	{
		DeleteDescriptionsInTableWithCondition($this, $this->tblCategories, array('id' => $pData['id']));

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

		if (isset($pData['image']) && !$pData['image']) {
			$data = $data + array('image' => null);
		}
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
		$id = $this->db->insert_id();
		return array('id' => $id);
	}

	function addCategoryImage($file_name, $target_file, $id)
	{
		$this->db->where(array('id' => $id))->update($this->tblCategories, array('image' => '/' . $target_file));
		return true;
	}

	function deleteCategoryImage($id)
	{
		$where = array('id' => $id);
		$this->db->where($where);
		$this->db->update($this->tblCategories, array('image' => null));
		return true;
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
