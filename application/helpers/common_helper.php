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
		if ($splitStr) {
			$groups = explode(',', $splitStr);
			foreach ($groups as $v) {
				if (!$v) continue;
				$group = $_this->db->select('*')->get_where($table, array($where => $v))->row_array();
				array_push($splitedArr, $group);
			}
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

if (!function_exists('insertCurrencyFromJson_get')) {
	function insertContentFromJson_get($_this, $targetDirFile, $table)
	{
		$content = file_get_contents(dirname(__FILE__) . $targetDirFile, false);
		$json = json_decode($content, true);

		foreach ($json as $k => $v) {
			$_this->db->insert($table, $v);
		}
	}
}
