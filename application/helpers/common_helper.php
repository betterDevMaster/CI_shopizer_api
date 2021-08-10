<?php

// fetch all records
function get_all_records($table)
{
	$ci = &get_instance();
	$ci->db->select('*');
	return  $ci->db->get($table)->result_array();
}

function sof_record_comapny_name($record_id = NULL)
{
	$ci = &get_instance();
	$ci->db->select('vessel.created_by,record_id,ci_sof_settings.company_name');
	$ci->db->join('vessel.created_by = ci_sof_settings.user_id');
	$ci->db->where('vessel.record_id', $record_id);
	return $ci->db->get('ci_sof_settings')->row_array()['company_name'];
}

function session_company_name()
{
	$ci = &get_instance();

	if ($ci->session->user_role_id == '1')
		$user =  $ci->session->user_id;
	if ($ci->session->user_role_id == '2')
		$user = $ci->session->user_parent;

	$ci->db->select('company_name');
	$ci->db->where('user_id', $user);
	return $ci->db->get('ci_sof_settings')->row_array()['company_name'];
}

function unique_token()
{
	$length = 8;
	$token = "";
	$codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$codeAlphabet .= "abcdefghijklmnopqrstuvwxyz";
	$codeAlphabet .= "0123456789";
	$max = strlen($codeAlphabet);

	for ($i = 0; $i < $length; $i++) {
		$token .= $codeAlphabet[random_int(0, $max - 1)];
	}

	return $token;
}

// get cargo activity name by id

function get_cargo_activity_name($id = 0)
{
	$ci = &get_instance();
	$ci->db->select('*');
	$ci->db->where('activity_id', $id);
	return  $ci->db->get('activity_type')->row_array()['activity_name'];
}

function get_expection_description($id = 0)
{
	$ci = &get_instance();
	$ci->db->select('*');
	$ci->db->where('id', $id);
	return  $ci->db->get('deduction_description')->row_array()['value'];
}

// fetch all records
function get_all_records_where($table, $where)
{
	$ci = &get_instance();
	$ci->db->select('*');
	$ci->db->where($where);
	return  $ci->db->get($table)->result_array();
}

function get_record_where($table, $where)
{
	$ci = &get_instance();
	$ci->db->select('*');
	$ci->db->where($where);
	return  $ci->db->get($table)->row_array();
}

function get_customer_id()
{
	$ci = &get_instance();
	return $ci->db->select('customer_id')
		->where('user_id', $ci->session->user_id)
		->order_by('id', 'desc')
		->limit(1)
		->get('user_cc_details')
		->row_array()['customer_id'];
}

function subscription_check()
{
	$ci = &get_instance();
	if ($_SESSION['user_sub_status'] != 'active') {
		$ci->session->set_flashdata('error', 'Subsription Expired');
		redirect(base_url('payment-settings'));
	}
}


function get_user_roles()
{
	$ci = &get_instance();
	$ci->db->select('*');
	$ci->db->where('status', 1);
	return  $ci->db->get('user_roles')->result_array();
}

function bc_date_format($date = '')
{
	// $date = strtr($date, '/', '-');
	return date('d M, Y', strtotime($date));
}

function bc_date_time_format($date = '')
{
	return date('Y-m-d H:i', strtotime($date));
}

function bc_time_format($time = '')
{
	return date('H:i', strtotime($time));
}
// get user added vessel record

function get_user_menu_vessel_records()
{
	$ci = &get_instance();

	if ($ci->session->user_role_id == '1')
		$user_id = $ci->session->user_id;
	if ($ci->session->user_role_id == '2')
		$user_id = $ci->session->user_parent;

	$ci->db->select('vessel.id as vid,vessel,year,activity,port,code,record_id,eta,created_by,is_active,created_date,ship.*,port.name as port_name,activity_type.*');
	$ci->db->join('ship', 'ship.id = vessel.vessel');
	$ci->db->join('port', 'port.id = vessel.port');
	$ci->db->join('activity_type', 'activity_type.activity_id = vessel.activity');
	$ci->db->where('vessel.created_by', $user_id);

	if ($ci->session->user_role_id == '1')
		$ci->db->where('vessel.show_in_menu', 1);

	$ci->db->order_by('vessel.id', 'DESC');
	return $ci->db->get('vessel')->result_array();
}

function get_user_invited_records_by($record_id)
{
	$ci = &get_instance();
	$ci->db->select('vessel.created_by');
	$ci->db->join('invitations', 'vessel.record_id = invitations.sof');
	$ci->db->where('vessel.record_id', $record_id);
	$ci->db->order_by('vessel.id', 'DESC');
	return $ci->db->get('vessel')->result_array();
}

function get_user_vessel_detail_by_record_id($record_id)
{
	$ci = &get_instance();
	$ci->db->select('vessel.id as vid,vessel,year,activity,port,code,record_id,eta,created_by,is_active,created_date,ship.*,port.name as port_name,activity_type.*');
	$ci->db->join('ship', 'ship.id = vessel.vessel');
	$ci->db->join('port', 'port.id = vessel.port');
	$ci->db->join('activity_type', 'activity_type.activity_id = vessel.activity');
	$ci->db->where('vessel.record_id', $record_id);
	return $ci->db->get('vessel')->row();
}

function get_itinerary_record_by_event($event_id, $itinerary_id)
{
	$ci = &get_instance();
	$ci->db->select('itinerary.*,itinerary_records.*');
	$ci->db->join('itinerary_records', 'itinerary_records.itinerary_id = itinerary.id');
	$ci->db->where('itinerary.id', $itinerary_id);
	$ci->db->where('itinerary.created_by', $ci->session->user_id);
	$ci->db->where('itinerary_records.event_id', $event_id);
	return $ci->db->get('itinerary')->row_array();
}

// -----------------------------------------------------------------------------
// Make Slug Function
if (!function_exists('make_slug')) {
	function make_slug($string)
	{
		$lower_case_string = strtolower($string);
		$string1 = preg_replace('/[^a-zA-Z0-9 ]/s', '', $lower_case_string);
		return strtolower(preg_replace('/\s+/', '-', $string1));
	}
}

// -----------------------------------------------------------------------------
// Get Role by ID
if (!function_exists('get_role_by_id')) {
	function get_role_by_id($id)
	{
		$ci = &get_instance();
		$ci->db->select('name');
		$ci->db->where('id', $id);
		return $ci->db->get('user_role')->row_array()['name'];
	}
}

// -----------------------------------------------------------------------------
// Get details about Package
if (!function_exists('get_package_detail')) {
	function get_package_detail($id)
	{
		$ci = &get_instance();
		$ci->db->select('*');
		$ci->db->where('id', $id);
		return $ci->db->get('packages')->row_array();
	}
}

// -----------------------------------------------------------------------------
if (!function_exists('user_detail_by_user_number')) {
	function user_detail_by_user_number($unumber)
	{
		$ci = &get_instance();
		$ci->db->select('*');
		$ci->db->where('user_number', $unumber);
		return $ci->db->get('users')->row_array();
	}
}

// -----------------------------------------------------------------------------
// Get details about Package features
if (!function_exists('get_package_features')) {
	function get_package_features($id)
	{
		$ci = &get_instance();
		$ci->db->select('feature');
		$ci->db->where('package_id', $id);
		$ci->db->where('included', 1);
		return $ci->db->get('package_detail')->result_array();
	}
}

// -----------------------------------------------------------------------------
// Get package id
if (!function_exists('get_package_by_stripe_plan')) {
	function get_package_by_stripe_plan($plan_id)
	{
		$ci = &get_instance();
		$ci->db->select('*');
		$ci->db->where('stripe_plan_id', $plan_id);
		return $ci->db->get('packages')->row_array();
	}
}

// -----------------------------------------------------------------------------
// Vessel Lables
if (!function_exists('insert_vessel_default_labels')) {
	function insert_vessel_default_labels()
	{
		$ci = &get_instance();
		$colors = array('plain', 'primany', 'success', 'info', 'warning', 'danger');
		return $colors;
	}
}
