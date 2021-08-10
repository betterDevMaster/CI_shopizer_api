<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class User_model extends CI_Model
{
    public $tblUser = 'tbl_user';
    public $tblCountry = 'tbl_country';
    public $tblZone = 'tbl_zone';
    public $tblUserBilling = 'tbl_user_billing';
    public $tblUserDelivery = 'tbl_user_delivery';
	
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

	function delete_User($id)
	{
		$this->db->where('id', $id);
		$this->db->delete($this->tblUser);
		return true;
	}

	function update_UserPassword($pData)
	{
		$where = array('id' => $pData['userId'], 'password' => md5($pData['current']));
		$this->db->where($where);
		$q = $this->db->get('user');

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

	function get_UserProfile($id)
	{
		$billing = $this->db->select('*')->get_where($this->tblUserBilling, array('userId' => $id))->row_array();
		$delivery = $this->db->select('*')->get_where($this->tblUserDelivery, array('userId' => $id))->row_array();
		$user = $this->db->select('*')->get_where($this->tblUser, array('id' => $id))->row_array();
		$profileData = array(
			'attributes' => array(),
			'groups' => array(),
			'emailAddress' => $user['emailAddress'],
			'firstName' => $user['firstName'],
			'gender' => $user['gender'],
			'id' => $user['id'],
			'language' => $user['language'],
			'lastName' => $user['lastName'],
			'provider' => $user['provider'],
			'rating' => $user['rating'],
			'ratingCount' => $user['ratingCount'],
			'storeCode' => $user['storeCode'],
			'userName' => $user['userName'],
		);
		$profileData['billing'] = array(
			'address' => $billing ? $billing['address'] : null,
			'city' => $billing ? $billing['city'] : null,
			'company' => $billing ? $billing['company'] : null,
			'country' => $billing ? $billing['country'] : null,
			'countryCode' => $billing ? $billing['countryCode'] : null,
			'firstName' => $user['firstName'],
			'lastName' => $user['lastName'],
			'latitude' => $billing ? $billing['latitude'] : null,
			'longitude' => $billing ? $billing['longitude'] : null,
			'phone' => $billing ? $billing['phone'] : null,
			'postalCode' => $billing ? $billing['postalCode'] : null,
			'stateProvince' => $billing ? $billing['stateProvince'] : null,
		);
		$profileData['delivery'] = array(
			'address' => $delivery ? $delivery['address'] : null,
			'city' => $delivery ? $delivery['city'] : null,
			'company' => $delivery ? $delivery['company'] : null,
			'country' => $delivery ? $delivery['country'] : null,
			'countryCode' => $delivery ? $delivery['countryCode'] : null,
			'firstName' => $user['firstName'],
			'lastName' => $user['lastName'],
			'latitude' => $delivery ? $delivery['latitude'] : null,
			'longitude' => $delivery ? $delivery['longitude'] : null,
			'phone' => $delivery ? $delivery['phone'] : null,
			'postalCode' => $delivery ? $delivery['postalCode'] : null,
			'stateProvince' => $delivery ? $delivery['stateProvince'] : null,
		);
		return $profileData;
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
} // END
