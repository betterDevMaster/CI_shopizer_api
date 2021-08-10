<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cart_model extends CI_Model 
{
    public $tblDefault = 'tbl_default';
    public $tblUserDelivery = 'tbl_user_delivery';
    public $tblLogo = 'tbl_logo';
    public $tblSupportedLanguages = 'tbl_supported_languages';
	
	public function __construct()
	{
		parent::__construct();
	}

} // END
