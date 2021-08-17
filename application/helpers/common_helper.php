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
		foreach ($groups as $v) {
			if (!$v) continue;
			$group = $_this->db->select('*')->get_where($table, array($where => $v))->row_array();
			array_push($splitedArr, $group);
		}
		return $splitedArr;
	}
}

if (!function_exists('SetTableIDsToString')) {
	function SetTableIDsToString($ids)
	{
		// supportedLanguages
	}
}
