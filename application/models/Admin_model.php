<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Admin_model extends CI_Model
{
	public $tblUser = 'tbl_user';
	public $tblSupportedLanguages = 'tbl_supported_languages';
	public $tblProductGroups = 'tbl_product_groups';
	public $tblPermissions = 'tbl_permissions';
	public $tblCurrency = 'tbl_currency';
	public $tblMeasures = 'tbl_measures';
	public $tblWeights = 'tbl_weights';

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
				'active' => $pData['active'],
				'defaultLanguage' => $pData['defaultLanguage'],
				'emailAddress' => $pData['emailAddress'],
				'firstName' => $pData['firstName'],
				'lastName' => $pData['lastName'],
				'merchant' => $pData['store'],
				'userName' => $pData['userName'],
			);
			$this->db->where($where);
			$this->db->update($this->tblUser, $data);
			return $data;
		} else {
			return null;
		}
	}

	function createUser($pData)
	{
		$groups = '';
		foreach ($pData['groups'] as $k => $v) {
			$groups = $groups.$v['id'] . ',';
		}

		$permissions = '';
		$perArr = $this->db->select('*')->get($this->tblPermissions)->result_array();
		foreach ($perArr as $k1 => $v1) {
			$permissions = $permissions. $v1['id'] . ',';
		}

		$pData['groups'] = $groups;
		$pData['permissions'] = $permissions;
		$pData['merchant'] = $pData['store'];
		$pData['password'] = md5($pData['password']);
        unset($pData['store']);
        unset($pData['repeatPassword']);

		$this->db->insert($this->tblUser, $pData);
		return $this->db->insert_id();
	}

	function createNewUser($pData)
	{
		$userData = array(
			'firstName' => $pData['firstName'],
			'lastName' => $pData['lastName'],
			'emailAddress' => $pData['email'],
			'storeCode' => $pData['code'],
			'storeName' => $pData['name'],
			'password' => md5($pData['password']),
			'postalCode' => $pData['postalCode'],
			'country' => $pData['country'],
			'countryCode' => $pData['countryCode'],
			'stateProvince' => $pData['stateProvince'],
			'userName' => $pData['email'],
		);

		$this->db->insert($this->tblUser, $userData);
		return $this->db->insert_id();
	}
} // END
