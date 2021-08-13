<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Admin_model extends CI_Model
{
	public $tblUser = 'tbl_user';
	public $tblLang = 'tbl_lang';
	public $tblGroups = 'tbl_groups';

	public function __construct()
	{
		parent::__construct();
	}

	function get_AdminCredential($pData)
	{
		$data = array(
			'userName' => $pData['username'],
			'password' => md5($pData['password']),
		);
		$query = $this->db->select('*')->where($data)->get($this->tblUser);

		if ($query->num_rows() > 0)
			return $query->row_array()['id'];
		else
			return null;
	}

	function get_Languages()
	{
		$lang = $this->db->select('*')->get($this->tblLang)->result_array();
		return $lang;
	}

	function get_Groups()
	{
		$lang = $this->db->select('*')->get($this->tblGroups)->result_array();
		return $lang;
	}

	function update_UserPassword($pData)
	{
		$where = array('id' => $pData['userId'], 'password' => md5($pData['password']));
		$this->db->where($where);
		$q = $this->db->get($this->tblUser);

		if ($q->num_rows() > 0) {
			$this->db->where($where);
			$this->db->update($this->tblUser, array('password' => md5($pData['changePassword'])));
			return true;
		} else {
			return false;
		}
	}

	function update_UserWithDefault($pData)
	{
		$where = array('emailAddress' => $pData['emailAddress']);
		$this->db->where($where);
		$q = $this->db->get($this->tblUser);

		if ($q->num_rows() > 0) {
			$data = array(
				'active'=> $pData['active'],
				'defaultLanguage'=> $pData['defaultLanguage'],
				'emailAddress'=> $pData['emailAddress'],
				'firstName'=> $pData['firstName'],
				'lastName'=> $pData['lastName'],
				'merchant'=> $pData['store'],
				'userName'=> $pData['userName'],
			);
			$this->db->where($where);
			$this->db->update($this->tblUser, $data);
			return $data;
		} else {
			return null;
		}
	}
} // END
