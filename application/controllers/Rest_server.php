<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Rest_server extends CI_Controller
{

	public function index()
	{
		$this->load->helper('url');

		$this->load->view('rest_server');
		// $this->createImageFromBase64();
	}

	function createImageFromBase64()
	{
		$wdata = $this->db->get('tbl_image')->result_array();
		foreach ($wdata as $v) {
			$base64img = $v['baseImage'];
			$file_name = $v['imageName'];
			$target_file_name = str_replace('%20', '-', $file_name);
			// $file_name = $v['name'] . '.png';
			$target_dir = "assets/product/";
			$target_file = $target_dir . $target_file_name;
			echo 'fileNmae; --------- '.$file_name.'   ';
			$this->db->where(array('imageName' => $file_name))->update('tbl_image', array('imageUrl' => '/assets/product/'.$target_file_name));

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
