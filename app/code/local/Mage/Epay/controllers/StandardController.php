<?php
/**
 * Copyright ePay | Dit Online Betalingssystem, (c) 2010.
 * This program is free software. You are allowed to use the software but NOT allowed to modify the software. 
 * It is also not legal to do any changes to the software and distribute it in your own name / brand. 
 */
class Mage_Epay_StandardController extends Mage_Core_Controller_Front_Action
{
	
    //
    // Flag only used for callback
    protected $_callbackAction = false;
    protected $_orderObj = null;
    
    
    protected function _expireAjax()
    {
        if (!Mage::getSingleton('checkout/session')->getQuote()->hasItems()) {
            $this->getResponse()->setHeader('HTTP/1.1','403 Session Expired');
            exit;
        }
    }

    /**
     * Get singleton with epay strandard order transaction information
     *
     * @return Mage_Epay_Model_Standard
     */
    public function getStandard()
    {
        return Mage::getSingleton('epay/standard');
    }

    /**
     * When a customer chooses Epay on Checkout/Payment page
     *
     */
    public function redirectAction()
    {
		//
		// Load layout
		//
		$this->loadLayout();
		$this->getLayout()->getBlock('content')->append($this->getLayout()->createBlock('epay/standard_redirect'));
		$this->renderLayout();

		//
		// Load teh session object
		//
		$session = Mage::getSingleton('checkout/session');
		$session->setEpayStandardQuoteId($session->getQuoteId());

		//
		// Save order comment
		//
		$this->_orderObj = Mage::getModel('sales/order');
		$this->_orderObj->loadByIncrementId($session->getLastRealOrderId());
		$this->_orderObj->addStatusToHistory($this->_orderObj->getStatus(), $this->__('EPAY_LABEL_31'));
		//$this->_orderObj->
		$this->_orderObj->save();
		

		//
		// Add items back on stock (if used)
		//
		//$this->addToStock();
    }
    
	
    public function addToStock()
    {
		//
		// Load the payment object
		//
		$payment = Mage::getModel('epay/standard');

		//
		// Load teh session object
		//
		$session = Mage::getSingleton('checkout/session');
		$session->setEpayStandardQuoteId($session->getQuoteId());
	  
	  	//
		// add items back on stock
		// Put the order back on stock as it is not yet paid!
		//
		// http://www.magentocommerce.com/wiki/groups/132/protx_form_-_subtracting_stock_on_successful_payment
		//
		if (((int)$payment->getConfigData('handlestock', $payment->getOrder() ? $payment->getOrder()->getStoreId() : null)) == 1) {
		  	if(!isset($_SESSION['stock_removed']) || $_SESSION['stock_removed'] != $session->getLastRealOrderId())
			{
		        /* Put the stock back on, we don't want it taken off yet */
		        $items = $this->_orderObj->getAllItems(); // Get all items from the order
		        if ($items) 
				{
		            foreach($items as $item) 
					{
						$quantity = $item->getQtyOrdered(); // get Qty ordered
						$product_id = $item->getProductId(); // get it's ID

						/* Removed
						$stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product_id); // Load the stock for this product
						$stock->setQty($stock->getQty()+$quantity); // Set to new Qty            
						$stock->save(); // Save
						Replaced by following line */

						Mage::getModel("cataloginventory/stock")->backItemQty($product_id, $quantity);

						continue;                        
		            }
		    	} 
		       
				//
				// Flag so that stock is only updated once!
				//
				$_SESSION['stock_removed'] = $session->getLastRealOrderId();
		    }
		}
	}
	
    //
    // Changes the order status after payment is made
    //
    public function setOrderStatusAfterPayment($payment)
    {
		//$this->_orderObj->setOrderStatus($payment->getConfigData('order_status_after_payment', $payment->getOrder() ? $payment->getOrder()->getStoreId() : null));
		$this->_orderObj->addStatusToHistory($payment->getConfigData('order_status_after_payment', $payment->getOrder() ? $payment->getOrder()->getStoreId() : null));
		$this->_orderObj->save();
		return;


		//
		// Set the status to the new epay status after payment
		// and save to database
		//
		$this->_orderObj->addStatusToHistory($payment->getConfigData('order_status_after_payment', $payment->getOrder() ? $payment->getOrder()->getStoreId() : null), 'Payment approved', false);
		$this->_orderObj->save();
    }
    
    //
    // Remove from stock (if used)
    //
    public function removeFromStock()
    {
		//
		// Load the payment object
		//
		$payment = Mage::getModel('epay/standard');

		//
    	// add items back on stock
    	// Put the order back on stock as it is not yet paid!
    	//
    	// http://www.magentocommerce.com/wiki/groups/132/protx_form_-_subtracting_stock_on_successful_payment
    	//
    	if (((int)$payment->getConfigData('handlestock', $payment->getOrder() ? $payment->getOrder()->getStoreId() : null)) == 1)
		{
	        // Put the stock back on, we don't want it taken off yet 
	        $items = $this->_orderObj->getAllItems(); // Get all items from the order
	        if ($items) 
			{
	            foreach($items as $item) 
				{
					$quantity = $item->getQtyOrdered(); // get Qty ordered
					$product_id = $item->getProductId(); // get it's ID

					$stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product_id); // Load the stock for this product
					$stock->setQty($stock->getQty()-$quantity); // Set to new Qty            
					$stock->save(); // Save
					continue;                        
	            }
	       	}            
      	}
    }

    /**
     * When a customer cancel payment from epay.
     */
    public function cancelAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setQuoteId($session->getEpayStandardQuoteId(true));
        $this->_redirect('checkout/cart');
    }
     
	public function getOrderUpdatedWithEpayData($orderid)
	{
		// Read info directly from the database   	
		$read = Mage::getSingleton('core/resource')->getConnection('core_read');
		$row = $read->fetchRow("select * from epay_order_status where orderid = '" . $orderid . "'");
		
		$standard = Mage::getModel('epay/standard');
		return ($row['status'] == '1');
	}
	 
    protected function _fillPaymentByResponse(Varien_Object $payment)
    {
        $payment->setTransactionId($_GET["tid"])
            ->setParentTransactionId(null)
            ->setIsTransactionClosed(0)
            ->setTransactionAdditionalInfo("Transaction ID", $_GET["tid"]);
    }
	
    protected function _authOrder(Mage_Sales_Model_Order $order)
    {
       
        $payment = $order->getPayment();
        $this->_fillPaymentByResponse($payment);

        $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH);

        $order->save();

    }

    /**
     * when epay returns
     * The order information at this point is in POST
     * variables.  However, you don't want to "process" the order until you
     * get validation from the IPN.
     */
    public function  successAction()
    {   
        $session = Mage::getSingleton('checkout/session');
        $session->setQuoteId($session->getEpayStandardQuoteId(true));
        /**
         * set the quote as inactive after back from epay
         */
        //Mage::getSingleton('checkout/session')->getQuote()->setIsActive(false)->save();

        
        $this->_orderObj = Mage::getModel('sales/order');
        $payment = Mage::getModel('epay/standard');

        //
        // Load the order number
        if (Mage::getSingleton('checkout/session')->getLastOrderId()) 
		{
			$this->_orderObj->load(Mage::getSingleton('checkout/session')->getLastOrderId());
		} 
		else 
		{
			if (isset($_GET["orderid"])) 
			{
				$this->_orderObj->loadByIncrementId($_GET["orderid"]);
			} 
			else 
			{
				echo "<h1>An error occured!</h1>";
				echo "No orderid was supplied to the system!";
				exit();
			}
        }
        
        //
        // Validate the order and send email confirmation if enabled
        if(!$this->_orderObj->getId()){
			echo "<h1>An error occured!</h1>";
			echo "The order id was not known to the system";
			exit();
        }
        
        if (!isset($_GET["amount"])) {
            echo "<h1>An error occured!</h1>";
            echo "No amount supplied to the system!";
            exit();
        }
        
        if (!isset($_GET["cur"])) {
            echo "<h1>An error occured!</h1>";
            echo "No currency (cur) supplied to the system!";
            exit();
        }
        
        //
        // Validate currency
        if (((int)$payment->convertToEpayCurrency($this->_orderObj->getBaseCurrency()->getCode())) != (int)$_GET["cur"])
        {
			echo "<h1>An error occured!</h1>";
			echo "The currency received from ePay did not match the order currency!";
			exit();
        }


        //
        // validate md5 if enabled
        if (((int)$payment->getConfigData('md5type', $payment->getOrder() ? $payment->getOrder()->getStoreId() : null)) != 0) {
    		
			$shippingfee = 0;	
    		//
    		// Remove the transaction fee, if add to shipping is used, as the totals is with transaction fees
    		//
    		if (((int)$payment->getConfigData('addfeetoshipping', $payment->getOrder() ? $payment->getOrder()->getStoreId() : null)) == 1) {
        		if (isset($_GET['transfee']) && strlen($_GET['transfee']) > 0) 
				{
        			if ($this->getOrderUpdatedWithEpayData($_GET["orderid"])) 
					{
        				$shippingfee = $_GET['transfee'];
        			}
        		}
        	}
			
            $tempstr = $_GET["amount"] . $this->_orderObj->getRealOrderId() . $_GET["tid"] . $payment->getConfigData('md5key', $payment->getOrder() ? $payment->getOrder()->getStoreId() : null);

            //
            // Validate currency
            if (!isset($_GET["eKey"])) 
			{
				echo "<h1>An error occured!</h1>";
				echo "No eKey supplied to the system!";
				exit();
            }
            
            if (md5($tempstr) != $_GET["eKey"]) 
			{
				echo ($this->_orderObj->getRealOrderId());
				echo "<h1>An error occured!</h1>";
				echo "The MD5 key does not match!<br />Please be sure that the correct MD5 key has been set in the ePay administration and the payment method settings.";
				exit();
            }
        }
		
		$this->_authOrder($this->_orderObj);
		
		//
		// Create an invoice if the the setting instantinvoice is set to Yes
		if((int)$payment->getConfigData('instantinvoice') == 1)
		{
			if($this->_orderObj->canInvoice()) {
				$invoice = $this->_orderObj->prepareInvoice();
				$invoice->register();
				Mage::getModel('core/resource_transaction')
					->addObject($invoice)
					->addObject($invoice->getOrder())
					->save();
				
				if((int)$payment->getConfigData('instantinvoicemail') == 1)
				{
					$invoice->setEmailSent(true);
					$invoice->save();
					$invoice->sendEmail();
				}
				
			}
		}

        //
		// Remove items from stock if either not yet removed or only if stock handling is enabled
		//														    									
		$read = Mage::getSingleton('core/resource')->getConnection('core_read');
		$row = $read->fetchRow("select * from epay_order_status where orderid = '" . $_GET['orderid'] . "'");

		if ($row['status'] == '0') 		
		{
	    	//
	        // Save the order into the epay_order_status table
	        // IMPORTANT to update the status as 1 to ensure that the stock is handled correctly!
	        //
    		$write = Mage::getSingleton('core/resource')->getConnection('core_write');
			$write->query('update epay_order_status set tid = "' . ((isset($_GET['tid'])) ? $_GET['tid'] : '0') . '", status = 1, ' .
									'amount = "' . ((isset($_GET['amount'])) ? $_GET['amount'] : '0') . '", '.
									'cur = "' . ((isset($_GET['cur'])) ? $_GET['cur'] : '0') . '", '.
									'date = "' . ((isset($_GET['date'])) ? $_GET['date'] : '0') . '", '.
									'eKey = "' . ((isset($_GET['eKey'])) ? $_GET['eKey'] : '0') . '", '.
									'fraud = "' . ((isset($_GET['fraud'])) ? $_GET['fraud'] : '0') . '", '.
									'subscriptionid = "' . ((isset($_GET['subscriptionid'])) ? $_GET['subscriptionid'] : '0') . '", '.
									'cardid = "' . ((isset($_GET['cardid'])) ? $_GET['cardid'] : '0') . '", '.
									'cardnopostfix = "' . ((isset($_GET['tcardno'])) ? $_GET['tcardno'] : '') . '", '.
									'transfee = "' . ((isset($_GET['transfee'])) ? $_GET['transfee'] : '0') . '" where orderid = "' . $_GET['orderid'] . '"');
									
			//
			// Remove items from stock as the payment now has been made
			//
			//$this->removeFromStock();						

			//
			// Change order to status paid
			//
			//$this->setOrderStatusAfterPayment($payment);

			$this->_orderObj->addStatusToHistory($payment->getConfigData('order_status_after_payment', $payment->getOrder() ? $payment->getOrder()->getStoreId() : null));
			$this->_orderObj->save();

			//
			// Add the transaction fee to the shipping and handling amount
			//
			if (isset($_GET['transfee']) && strlen($_GET['transfee']) > 0) 
			{
				if (((int)$payment->getConfigData('addfeetoshipping', $payment->getOrder() ? $payment->getOrder()->getStoreId() : null)) == 1)
				{
				  	$this->_orderObj->setBaseShippingAmount($this->_orderObj->getBaseShippingAmount() + (((int)$_GET['transfee']) / 100));
				  	$this->_orderObj->setBaseGrandTotal($this->_orderObj->getBaseGrandTotal() + (((int)$_GET['transfee']) / 100));
 
					$storefee = Mage::helper('directory')->currencyConvert((((int)$_GET['transfee']) / 100), $this->_orderObj->getBaseCurrencyCode(), $this->_orderObj->getOrderCurrencyCode());
					
					$this->_orderObj->setShippingAmount($this->_orderObj->getShippingAmount() + $storefee);
					$this->_orderObj->setGrandTotal($this->_orderObj->getGrandTotal() + $storefee);
					
					$this->_orderObj->save();
				}
			}
          
			//
			// Send email order confirmation (if enabled). May be done only once!
			//        	
			if (((int)$payment->getConfigData('sendmailorderconfirmation', $payment->getOrder() ? $payment->getOrder()->getStoreId() : null)) == 1)
			{
				$this->_orderObj->setEmailSent(true);
			    $this->_orderObj->sendNewOrderEmail();
			    $this->_orderObj->save();
			}
    	}
    		
    	//
        // If not callback - redirect the user to the success page
        if (!$this->_callbackAction) 
		{
          	$this->_redirect('checkout/onepage/success');
        } 
		else 
		{
			//
			// Callback from ePay - just respond ok
			echo "OK";
			exit();
        }
    }
    
    //
    // When callback is called from epay
    // just reflect to the success action
    //
    public function callbackAction()
    {
		$this->_callbackAction = true;
		$this->successAction();
    }
	
}
