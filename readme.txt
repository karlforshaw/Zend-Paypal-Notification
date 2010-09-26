Example code:

class PaypalNotificationController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
    	$primaryPaypal = 'me@myemailprovider.com';
    	$ipn = new Application_Model_PaypalNotification($_POST, $primaryPaypal);
		
    	if ($ipn->validate()) {
			
			if ($ipn->isCompleted()) {
				
				// Check if already processed
				$paypalMapper = new Application_Model_PayPalMapper();
				
				// Check reciever email
				if (! $ipn->isInMyPocket())
					throw new Exception( 'Receiver email is not mine!' );
				
				// Does the payment amount cover the invoice cost?
				if (! $ipn->isCorrectAmount($correctAmount))
					throw new Exception('Incorrect amount paid!');
				
				// Process payment
					
			}
			elseif ($ipn->isReversed()) {
				// TODO Ban user!
			}
		}
		else 
			throw new Exception( 'IPN didnt validate' );
				
    }


}

