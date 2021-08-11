<?php
if (!function_exists('IsNullOrEmptyString')) {
	function IsNullOrEmptyString($str)
	{
		return (!isset($str) || trim($str) === '');
	}
}
