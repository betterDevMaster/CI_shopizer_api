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

	function get_TableContentWithArrayResultAndCondition($where, $table)
	{
		$result =  $this->db->select('*')->get_where($table, $where)->result_array();
		return $result;
	}

	function get_TableContentWithRowResult($table)
	{
		$config = $this->db->select('*')->get($table)->row_array();
		return $config;
	}

	function get_TableContentWithRowResultAndCondition($where, $table)
	{
		$result =  $this->db->select('*')->get_where($table, $where)->row_array();
		return $result;
	}

	function get_UniqueTableRecord($where, $table)
	{
		$this->db->where($where);
		$q = $this->db->get($table);

		if ($q->num_rows() > 0)
			return array('exists' => true);
		else
			return array('exists' => false);
	}

	function delete_TableRecordWithCondition($where, $table)
	{
		$this->db->where($where);
		$this->db->delete($table);
		return true;
	}
} // END
