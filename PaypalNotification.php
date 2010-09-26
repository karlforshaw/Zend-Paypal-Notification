<?php

class Application_Model_PaypalNotification
{
	
	const ipnUri = 'ssl://www.paypal.com';
	const sandboxUri = 'ssl://www.sandbox.paypal.com';

	private $_itemName;
	private $_itemNumber;
	private $_paymentStatus;
	private $_paymentAmount;
	private $_paymentCurrency;
	private $_txnId;
	private $_receiverEmail;
	private $_payerEmail;
	
	private $_primaryEmail;
	
	private $_sandboxMode = false;
	private $_sandboxPrimaryEmail;
	
	private $_postedArray; // Makes it easier to post back to paypal
	
	
	
	
	/**
	 * 
	 * @param array $postValues
	 * @param str $primaryPaypalAddr
	 */
	public function __construct( $postValues, $primaryPaypalAddr ) {
		
		$this->_postedArray = $postValues;
		$this->_primaryEmail = $primaryPaypalAddr;
		
		$validator = new Zend_Validate_EmailAddress();
		if (! $validator->isValid($this->_primaryEmail))
			throw new Exception( $this->_primaryEmail . ' is not a valid email address!');
			
		$this->_itemName = $postValues['item_name'];
		$this->_itemNumber = $postValues['item_number'];
		$this->_paymentStatus = $postValues['payment_status'];
		$this->_paymentAmount = $postValues['mc_gross'];
		$this->_paymentCurrency = $postValues['mc_currency'];
		$this->_txnId = $postValues['txn_id'];
		$this->_receiverEmail = $postValues['receiver_email'];
		$this->_payerEmail = $postValues['payer_email'];
		
	}
	
	
	
	
	/**
	 * @return bool
	 */
	public function validate() {
		$req = 'cmd=_notify-validate';
		
		foreach ($this->_postedArray as $key => $value) {
			$value = urlencode(stripslashes($value));
			$req .= "&$key=$value";
		}

		// post back to PayPal system to validate
		$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
		$fp = fsockopen ($this->getIpnUri(), 443, $errno, $errstr, 30);
		
		if (!$fp) 
			throw new Exception('Could not connect to Paypal!');
		else {
			
			fputs ($fp, $header . $req);
			
			while (!feof($fp)) {
				$res = fgets ($fp, 1024);
				if (strcmp ($res, "VERIFIED") == 0)
					$return = true;	
				else if (strcmp ($res, "INVALID") == 0)
					$return = false; // TODO log for manual investigation
			}
			fclose ($fp);
		}
		
		return $return;
	}	
	
	
	public function useSandbox() {
		$this->_sandboxMode = true;
	}
	
	
	public function setSandboxPrimaryEmail( $email ) {
		
		$validator = new Zend_Validate_EmailAddress();
		if (! $validator->isValid($email))
			throw new Exception( $email . ' is not a valid email address!');
			
		$this->_sandboxPrimaryEmail = $email;
	}
	
	
	public function isCompleted() {
		
		if (is_null($this->_paymentStatus))
			throw new Exception('Payment status hasn\'t been set');
		
		if ($this->_paymentStatus == 'Completed')
			return true;
		else
			return false;
			
	}
	
	
	public function isReversed() {
		
		if (is_null($this->_paymentStatus))
			throw new Exception('Payment status hasn\'t been set');
			
		if ($this->_paymentStatus == 'Reversed')
			return true;
		else
			return false;
		
	}
	
	
	
	public function isInMyPocket() {
		
		if (($this->_sandboxMode) && (! is_null( $this->_sandboxPrimaryEmail )))
			$primaryEmail = $this->_sandboxPrimaryEmail;
		else {	
		
			if (is_null($this->_primaryEmail))
				throw new Exception('Primary email not yet set');
				
			$primaryEmail = $this->_primaryEmail;
			
		}
		
		if (is_null($this->_receiverEmail))
				throw new Exception('Receiver email not yet set');
				
		if($primaryEmail == $this->_receiverEmail)
			return true;
		else
			return false;
		
	}
	
	
	public function isCorrectAmount( $correctAmount ) {
		if ($this->_paymentAmount != $correctAmount) 
			return false;
		else
			return true;
	}
	
	
	public function getItemName() {
		
		if (is_null($this->_itemName))
			throw new Exception('Item name not yet set');
			
		return $this->_itemName;
	}
	
	
	public function getItemNo() {
		
		if (is_null($this->_itemNumber))
			throw new Exception('Item No not yet set');
			
		return $this->_itemNumber;
	}
	
	
	public function getPaymentStatus() {
		
		if (is_null($this->_paymentStatus))
			throw new Exception('Payment status not yet set');
			
		return $this->_paymentStatus;
	}
	
	
	public function getPaymentAmount() {
		
		if (is_null($this->_paymentAmount))
			throw new Exception('Payment amount not yet set');
			
		return $this->_paymentAmount;
	}
	
	
	public function getPaymentCurrency() {
		
		if (is_null($this->_paymentCurrency))
			throw new Exception('Payment currency not yet set');
			
		return $this->_paymentCurrency;
	}
	
	
	public function getTxnId() {
		
		if (is_null($this->_txnId))
			throw new Exception('Transaction ID not yet set');
			
		return $this->_txnId;
	}
	
	
	public function getReceiverEmail() {
		
		if (is_null($this->_receiverEmail))
			throw new Exception('Receiver email not yet set');
			
		return $this->_receiverEmail;
	}
	
	
	public function getPayerEmail() {
		
		if (is_null($this->_payerEmail))
			throw new Exception('Payer email not yet set');
			
		return $this->_payerEmail;
	}
	
	
	private function getIpnUri() {
		if ($this->_sandboxMode)
			return sandboxUri;
		else
			return ipnUri;
	}
	
}

