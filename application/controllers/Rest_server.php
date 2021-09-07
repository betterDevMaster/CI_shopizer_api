<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Rest_server extends CI_Controller
{

	public function index()
	{
		$this->load->helper('url');

		$this->load->view('rest_server');
		$this->createImageFromBase64();
	}

	function createImageFromBase64()
	{
		$wdata = $this->db->get('tbl_logo')->result_array();
		foreach ($wdata as $v) {
			$base64img = $v['path'];
			$file_name = $v['name'];
			// $file_name = $v['name'] . '.png';
			$target_dir = "assets/logo/";
			$target_file = $target_dir . $file_name;

			if (!file_exists($target_dir)) {
				mkdir($target_dir, 0777, true);
			}
			if (!file_exists($target_file)) {
				file_put_contents($target_file, base64_decode($base64img));
				echo 'File Uploaded.<br>';
			} else {
				echo 'File existed.<br>';
			}
		}
	}
}
