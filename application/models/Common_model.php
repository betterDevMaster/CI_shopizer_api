<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Common_model extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
	}

	function get_TableContentWithArrayResult($table)
	{
		$result =  $this->db->select('*')->get($table)->result_array();
		return $result;
	}

	function get_TableContentWithRowResult($table)
	{
		$config = $this->db->select('*')->get($table)->row_array();
		return $config;
	}
} // END
