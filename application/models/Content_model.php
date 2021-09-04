<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Content_model extends CI_Model
{
	public $tblContent = 'tbl_content';
	public $tblContentImages = 'tbl_content_images';
	public $tblCategories = 'tbl_categories';
	public $tblDescription = 'tbl_description';

	public function __construct()
	{
		parent::__construct();
	}

	function get_HeaderMessage($lang)
	{
		$content = $this->db->select('*')->get($this->tblContent)->row_array();
		$content['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $content['descriptions']);
		// $content['description'] = customFilterArray($content['descriptions'], $term = 'language', $lang)[0];
		$content['description'] = count($content['descriptions']) > 0 && $content['descriptions'][0] ? $content['descriptions'][0] : null;
		return $content;
	}

	function get_Pages($page, $count, $store, $lang, $boxes)
	{
		if (!$boxes)
			$where = array('contentType' => null);
		else
			$where = array('contentType' => 'BOX');

		$recordsTotal = $this->db->from($this->tblContent)->where($where)->count_all_results();
		$totalPages = ceil($recordsTotal / $count);
		$contents = $this->db->select('*')->limit($count, $count * $page)->get_where($this->tblContent, $where)->result_array();
		foreach ($contents as $k1 => $v1) {
			if (!$v1) continue;
			$contents[$k1]['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $v1['descriptions']);
			// $contents[$k1]['description']  = customFilterArray($contents[$k1]['descriptions'], $term = 'language', $lang)[0];
			$contents[$k1]['description'] = count($contents[$k1]['descriptions']) > 0 && $contents[$k1]['descriptions'][0] ? $contents[$k1]['descriptions'][0] : null;
		}
		$result = array($recordsTotal, $totalPages, $contents);
		return $result;
	}

	function getCategory($page, $count, $store, $lang, $parentId)
	{
		$contents = $this->db->select('*')->get_where($this->tblCategories, array('parent' => $parentId))->result_array();
		for ($i = 0; $i < count($contents); $i++) {
			$contents[$i]['description'] = $this->db->select('*')->get_where($this->tblDescription, array('id' => $contents[$i]['description']))->row_array();
			$contents[$i]['parent'] = $this->db->select('id, code')->get_where($this->tblCategories, array('id' => $contents[$i]['parent']))->row_array();
			$contents[$i]['children'] = $this->getCategory($page, $count, $store, $lang, $contents[$i]['id']);
		}

		$recordsTotal = $this->db->from($this->tblCategories)->count_all_results();
		$totalPages = ceil($recordsTotal / $count);
		$result = array($recordsTotal, $totalPages, $contents);
		return $result;
	}

	function get_PageDetail($pData, $box = false, $lang = null)
	{
		if (!$box)
			$where = array('code' => $pData['contentID'], 'contentType' => null);
		else
			$where = array('code' => $pData['contentID'], 'contentType' => 'BOX');

		$contents = $this->db->select('*')->get_where($this->tblContent, $where)->row_array();
		$contents['descriptions'] = GetTableDetails($this, $this->tblDescription, 'id', $contents['descriptions']);
		// $contents['description']  = customFilterArray($contents['descriptions'], $term = 'language', $lang)[0];
		$contents['description'] = count($contents['descriptions']) > 0 && $contents['descriptions'][0] ? $contents['descriptions'][0] : null;
		return $contents;
	}

	function updatePage($pData, $id, $box = false)
	{
		DeleteDescriptionsInTableWithCondition($this, $this->tblContent, array('id' => $id));

		$descriptions = '';
		foreach ($pData['descriptions'] as $k => $v) {
			unset($v['id']);
			$this->db->insert($this->tblDescription, $v);
			$insertId = $this->db->insert_id();
			$descriptions = $descriptions . $insertId . ',';
		}
		$where = array('id' => $id);
		if (!$box) {
			$data = array(
				'code' => $pData['code'],
				'mainmenu' => (int)$pData['mainmenu'],
				'order' => (int)$pData['order'],
				'visible' => (int)$pData['visible'],
				'descriptions' => $descriptions,
			);
		} else {
			$data = array(
				'code' => $pData['code'],
				'contentType' => 'BOX',
				'visible' => (int)$pData['visible'],
				'descriptions' => $descriptions,
			);
		}
		$this->db->where($where)->update($this->tblContent, $data);
		return true;
	}

	function createPage($pData, $box = false)
	{
		$descriptions = '';
		foreach ($pData['descriptions'] as $k => $v) {
			unset($v['id']);
			$this->db->insert($this->tblDescription, $v);
			$insertId = $this->db->insert_id();
			$descriptions = $descriptions . $insertId . ',';
		}
		if (!$box) {
			$data = array(
				'code' => $pData['code'],
				'mainmenu' => (int)$pData['mainmenu'],
				'order' => (int)$pData['order'],
				'visible' => (int)$pData['visible'],
				'descriptions' => $descriptions,
			);
		} else {
			$data = array(
				'code' => $pData['code'],
				'contentType' => 'BOX',
				'visible' => (int)$pData['visible'],
				'descriptions' => $descriptions,
			);
		}
		$this->db->insert($this->tblContent, $data);
		return true;
	}

	function addImage($parentPath, $qquuid, $qqfilename, $qqtotalfilesize, $target_file)
	{
		$data = array(
			'name' => $qqfilename,
			'size' => $qqtotalfilesize,
			'ptah' => 'image.png',
			'url' => $target_file
		);
		$this->db->insert($this->tblContentImages, $data);
		$result = array('error' => null, 'preventRetry' => true, 'success' => true);
		return $result;
	}
} // END
