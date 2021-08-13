<?php
if (!function_exists('IsNullOrEmptyString')) {
	function IsNullOrEmptyString($str)
	{
		return (!isset($str) || trim($str) === '');
	}
}

if (!function_exists('GetTableDetails')) {
	function GetTableDetails($_this, $table, $where, $splitStr, $splitedArr = array())
	{
		$groups = explode(',', $splitStr);
		$user['groups'] = array();
		foreach ($groups as $k => $v) {
			if (!$v) continue;
			$group = $_this->db->select('*')->get_where($table, array($where => $v))->row_array();
			array_push($splitedArr, $group);
		}
		return $splitedArr;
	}
}
