<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Customer_model extends CI_Model
{
	public $tblUser = 'tbl_user';
	public $tblCountry = 'tbl_country';
	public $tblZone = 'tbl_zone';
	public $tblUserBilling = 'tbl_user_billing';
	public $tblUserDelivery = 'tbl_user_delivery';
	public $tblConfig = 'tbl_config';
	public $tblProductGroups = 'tbl_product_groups';
	public $tblPermissions = 'tbl_permissions';

	public function __construct()
	{
		parent::__construct();
	}

	function get_CountryZonesList($lang)
	{
		$countryList = $this->db->select('*')->get($this->tblCountry)->result_array();
		for ($i = 0; $i < count($countryList); $i++) {
			$zoneList = $this->db->get_where($this->tblZone, array('countryCode' => $countryList[$i]['code']))->result_array();
			$countryList[$i]['zones'] = $zoneList;
		}
		return $countryList;
	}

	function get_ZonesList($code)
	{
		$zoneList = $this->db->select('*')->get_where($this->tblZone, array('countryCode' => $code))->result_array();
		return $zoneList;
	}

	function add_NewUser($pData)
	{
		$userData = array(
			'firstName' => $pData['billing']['firstName'],
			'lastName' => $pData['billing']['lastName'],
			'emailAddress' => $pData['emailAddress'],
			'userName' => $pData['userName'],
			'password' => md5($pData['password']),
			'country' => $pData['billing']['country'],
			'countryCode' => $pData['billing']['countryCode'],
			'stateProvince' => $pData['billing']['stateProvince'],
			'gender' => $pData['gender'],
			'language' => $pData['language'],
			'postalCode' => $pData['postalCode'],
		);
		$this->db->insert($this->tblUser, $userData);
		return $this->db->insert_id();
	}

	function update_User($data, $email)
	{
		$this->db->where('email', $email);
		$this->db->update($this->tblUser, $data);
		return true;
	}

	function update_UserPassword($pData)
	{
		$where = array('id' => $pData['userId'], 'password' => md5($pData['current']));
		$this->db->where($where);
		$q = $this->db->get($this->tblUser);

		if ($q->num_rows() > 0) {
			$this->db->where($where);
			$this->db->update($this->tblUser, array('password' => md5($pData['password'])));
			return true;
		} else {
			return false;
		}
	}

	function check_Auth($data)
	{
		$this->db->select('*');
		$this->db->where($data);
		$query = $this->db->get($this->tblUser);
		if ($query->num_rows() > 0) {
			return $query->row_array()['id'];
			// 	if ($query->row_array()['status'] == '0') {
			// 		return array('status' => false, 'message' => 'Your account is not active yet. Please check you email to verify email address.');
			// 	} else {
			// 		if ($query->row_array()['access_status'] == '0')
			// 			return array('status' => false, 'message' => 'Your account access is blocked by your Lead');
			// 		else
			// 			return array('status' => true, 'message' => $query->row_array());
			// 	}
			// } else {
			// 	return array('status' => false, 'message' => 'Email or Password is not valid');
		} else
			return null;
	}

	function getUserProfile($id = null, $isAdmin = 0)
	{
		if ($id)
			$user = $this->db->select('*')->get_where($this->tblUser, array('id' => $id))->row_array();
		else
			$user = $this->db->select('*')->get_where($this->tblUser, array('isAdmin' => $isAdmin))->row_array();

		$user['groups'] = GetTableDetails($this, $this->tblProductGroups, 'id', $user['groups']);
		$user['permissions'] = GetTableDetails($this, $this->tblPermissions, 'id', $user['permissions']);
		$user['billing'] = $this->db->select('*')->get_where($this->tblUserBilling, array('id' => $user['billing']))->row_array();
		$user['delivery'] = $this->db->select('*')->get_where($this->tblUserDelivery, array('id' => $user['delivery']))->row_array();

		return $user;
	}

	function updateBillingDelivery_User($pData, $table)
	{
		$where = array('userId' => $pData['userId']);
		$this->db->where($where);
		$q = $this->db->get($table);

		if ($q->num_rows() > 0) {
			$this->db->where($where);
			$this->db->update($table, $pData);
			return true;
		} else {
			$this->db->insert($table, $pData);
			return false;
		}
	}

	function getList($store, $lang, $count, $page, $id)
	{
		if (!$id) {
			$recordsTotal = $this->db->from($this->tblUser)->count_all_results();
			$totalPages = ceil($recordsTotal / $count);
			$contents = $this->db->select('*')->limit($count, $count * $page)->get($this->tblUser)->result_array();
			foreach ($contents as $k5 => $v5) {
				if (!$v5) continue;
				$contents[$k5]['billing'] = $this->db->select('*')->get_where($this->tblUserBilling, array('id' => $v5['billing']))->row_array();
				$contents[$k5]['delivery'] = $this->db->select('*')->get_where($this->tblUserDelivery, array('id' => $v5['delivery']))->row_array();
			}
			$result = array($recordsTotal, $totalPages, $contents);
		} else {
			$result = $this->db->get_where($this->tblUser, array('id' => $id))->row_array();
			$result['billing'] = $this->db->select('*')->get_where($this->tblUserBilling, array('id' => $result['billing']))->row_array();
			$result['delivery'] = $this->db->select('*')->get_where($this->tblUserDelivery, array('id' => $result['delivery']))->row_array();
		}

		return $result;
	}

	function addCustomerList($pData)
	{
	}

	function updateCustomerList($pData, $id)
	{
		$country = $pData['billing']['country'];
		$stateProvince = $pData['billing']['zone'];
		unset($pData['billing']['country']);
		unset($pData['billing']['zone']);
		unset($pData['delivery']['country']);
		unset($pData['delivery']['zone']);

		$billingData = array(
			'countryCode' => $country,
			'stateProvince' => $stateProvince,
		);
		$billingData = $billingData + $pData['billing'];
		$this->db->where(array('id' => $id))->update($this->tblUserBilling, $billingData);

		$deliveryData = array(
			'countryCode' => $country,
			'stateProvince' => $stateProvince,
		);
		$deliveryData = $deliveryData + $pData['delivery'];
		$this->db->where(array('id' => $id))->update($this->tblUserDelivery, $deliveryData);

		$userData = array(
			'firstName' => $pData['billing']['firstName'],
			'lastName' => $pData['billing']['lastName'],
			'countryCode' => $country,
			'stateProvince' => $stateProvince,
			'postalCode' => $pData['billing']['postalCode'],
			'emailAddress' => $pData['emailAddress'],
		);
		$this->db->where(array('id' => $id))->update($this->tblUser, $userData);
		return true;
	}

	function resetPassword($pData)
	{
		$this->db->where(array('userName' => $pData['username']))->update($this->tblUser, array('password' => md5('123123123@')));
		return true;
	}
} // END
