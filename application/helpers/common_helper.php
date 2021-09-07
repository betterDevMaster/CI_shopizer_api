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

if (!function_exists('DeleteDescriptionsInTableWithCondition')) {
	function DeleteDescriptionsInTableWithCondition($_this, $table, $where)
	{
		$descriptionsId = $_this->db->get_where($table, $where)->row_array()['descriptions'];
		$descriptionList = explode(',', $descriptionsId);
		foreach ($descriptionList as $v) {
			$_this->db->delete('tbl_description', array('id' => $v));
		}
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

if (!function_exists('customFilterArray')) {
	function customFilterArray($array, $cond)
	{
		$matches = array();
		foreach ($array as $a) {
			if ($a['language'] == $cond)
				$matches[] = $a;
		}
		return $matches;
	}
}
