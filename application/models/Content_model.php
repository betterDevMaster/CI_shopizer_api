<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Content_model extends CI_Model
{
	public $tblContent = 'tbl_content';
	public $tblCategories = 'tbl_categories';
	public $tblDescription = 'tbl_description';

	public function __construct()
	{
		parent::__construct();
	}

	function get_HeaderMessage($lang)
	{
		$content = $this->db->select('*')->get($this->tblContent)->row_array();
		$description = $this->db->get_where($this->tblDescription, array('id' => $content['description']))->row_array();
		$content['description'] = $description;
		return $content;
	}

	function get_Pages($page, $count, $store, $lang)
	{
		$contents = $this->db->select('*')->get($this->tblContent)->result_array();
		for ($i = 0; $i < count($contents); $i++) {
			$contents[$i]['description'] = $this->db->select('*')->get_where($this->tblDescription, array('id' => $contents[$i]['description']))->row_array();
		}
		return $contents;
	}

	function get_Category($page, $count, $store, $lang, $parentId)
	{
		$contents = $this->db->select('*')->get_where($this->tblCategories, array('parent' => $parentId))->result_array();
		for ($i = 0; $i < count($contents); $i++) {
			$contents[$i]['description'] = $this->db->select('*')->get_where($this->tblDescription, array('id' => $contents[$i]['description']))->row_array();
			$contents[$i]['parent'] = $this->db->select('id, code')->get_where($this->tblCategories, array('id' => $contents[$i]['parent']))->row_array();
			$contents[$i]['children'] = $this->get_Category($page, $count, $store, $lang, $contents[$i]['id']);
		}
		return $contents;
	}

	function get_PageDetail($pData)
	{
		$contents = $this->db->select('*')->get_where($this->tblContent, array('code' => $pData['contentID']))->row_array();
		$contents['description'] = $this->db->select('*')->get_where($this->tblDescription, array('id' => $contents['description']))->row_array();
		return $contents;
	}
} // END
