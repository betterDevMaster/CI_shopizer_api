<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Payment_model extends CI_Model
{
	public $tblPaymentHistory = 'tbl_payment_history';

	public function __construct()
    {
        parent::__construct();
    }
	
	public function addPayment($pData, $cartId){
		$data = array(
			'amount' => $pData['payment']['amount'],
			'currency' => $pData['currency'],
			'email' => $pData['userData']['email'],
			'orderId' => $cartId,
			'paymentModule' => $pData['payment']['paymentModule'],
			'paymentToken' => $pData['payment']['paymentToken'],
			'paymentType' => $pData['payment']['paymentType'],
			'shippingQuote' => $pData['shippingQuote'],
			'transactionType' => $pData['payment']['transactionType'],
		);
		$this->db->insert($this->tblPaymentHistory, $data);
	}
}
?>