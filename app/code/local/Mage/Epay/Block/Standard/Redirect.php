<?php
/**
 * Copyright ePay | Dit Online Betalingssystem, (c) 2010.
 * This program is free software. You are allowed to use the software but NOT allowed to modify the software. 
 * It is also not legal to do any changes to the software and distribute it in your own name / brand. 
 */

class Mage_Epay_Block_Standard_Redirect extends Mage_Core_Block_Template
{
	function calculateTransactionfee($cardnoprefix, $amount, $currency)
    {
		$standard = Mage::getModel('epay/standard');
    	$client = new SoapClient('https://ssl.ditonlinebetalingssystem.dk/remote/payment.asmx?WSDL');
		
			$param = array
		(
			'merchantnumber' => $standard->getConfigData('merchantnumber'),
			'cardno_prefix' => $cardnoprefix,
			'amount' => $amount,
			'currency' => $currency,
			'acquirer' => "",
			'fee' => "",
			'cardtype' => 'ALL',
			'epayresponse' => 0,
			'pwd' => $standard->getConfigData('remoteinterfacepassword')
		);
		   
			$result = $client->getcardinfo($param);
			//echo "<br><br>";
			//echo print_r($result);
			//echo "<br><br>";
			if ($result->getcardinfoResult == 1) {
				echo $result->fee . ";" . number_format($result->fee / 100, 2, ',', ' ') . ";" . $result->cardtype . ";" . $result->cardtypetext;
			} else {
				echo $result->epayresponse;
			}
			exit();
    }
    
    public function __construct()
    {
        parent::__construct();
        $standard = Mage::getModel('epay/standard');
        //
        // If transaction fee is to be calculated by the cardnumber entered
        //
        $webserviceexception = false;
        if (isset($_GET['calculatefee']) && $_GET['calculatefee'] == '1') {
        	for ($i = 0; $i < 3; $i++) {
	        	try {
	        		$webserviceexception = false;
	        		$this->calculateTransactionfee($_GET['prefix'], $_GET['amount'], $_GET['currency']);
	        		break;
	        	} catch(Exception $e) {
	        		$webserviceexception = true;
						}
						//
						// If any error - sleep one second and try again - but MAX 3 times
						//
						sleep(1);
					}
					//
					// If exception - print error to the user.
					//
					if ($webserviceexception) {
						echo Mage::helper('epay')->__('EPAY_LABEL_99');
					}
        	exit();
        } else {
	        if ($standard->getConfigData('integrated') == '1') {
	        	$this->setTemplate('epay/standard/redirect_paymentform.phtml');
	        } else {
	        	$this->setTemplate('epay/standard/redirect_standardwindow.phtml');
	        }
	        
	        //
        	// Save the order into the epay_order_status table
        	//
        	$write = Mage::getSingleton('core/resource')->getConnection('core_write');
    			$write->insert('epay_order_status', Array('orderid'=>$standard->getCheckout()->getLastRealOrderId()));
	      }
    }
}
