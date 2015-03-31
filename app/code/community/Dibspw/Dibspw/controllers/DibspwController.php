<?php
/**
 * Dibs A/S
 * Dibs Payment Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Payments & Gateways Extensions
 * @package    Dibspw_Dibspw
 * @author     Dibs A/S
 * @copyright  Copyright (c) 2010 Dibs A/S. (http://www.dibs.dk/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Payment Controller
 **/

class Dibspw_Dibspw_DibspwController extends Mage_Core_Controller_Front_Action {

    private $oDibsModel;
    
    function _construct() {
        $this->oDibsModel= Mage::getModel('dibspw/Dibspw');
    }

    public function redirectAction(){
        // Load the session object
      	$session = Mage::getSingleton('checkout/session');
      	$session->setDibspwQuoteId($session->getQuoteId());

        $oOrder = Mage::getModel('sales/order');
        $oOrder->loadByIncrementId($session->getLastRealOrderId());
        $this->loadLayout();
        if($oOrder->getPayment() !== FALSE) {
            // Create the POST to DIBS (Inside Magento Checkout)
            $this->getLayout()->getBlock('content')->append($this->getLayout()->createBlock('dibspw/redirect'));
                
            // Create the POST to DIBS (In Separate "Blank" Window)
            // $this->getResponse()->setBody($this->getLayout()->createBlock('Dibspw/redirect')->toHtml());
      
            // Save order comment
            foreach($oOrder->getAllStatusHistory() as $oOrderStatusItem) {
                $sOrderComment = $oOrderStatusItem->getComment();
                break;
            }
            if($sOrderComment != $this->__('DIBSPW_LABEL_3')) {
                $oOrder->addStatusToHistory($oOrder->getStatus(), $this->__('DIBSPW_LABEL_3'));
            }
            $oOrder->setDataChanges(false);
            $oOrder->save();
            // Add items back on stock (if used)
            /*
             * I have commented this code 'couse hight load on the base when user ordered aproximatly 26 subs
             */
            //$this->addToStock();
        }
        else $this->getLayout()->getBlock('content')->append($this->getLayout()->createBlock('dibspw/failure'));
        $this->renderLayout();
    }
    
    public function successAction() {
        $session = Mage::getSingleton('checkout/session');
        $session->setQuoteId($session->getDibspwStandardQuoteId(true));
        $oOrder = Mage::getModel('sales/order');
        
        $iResult = $this->oDibsModel->api_dibs_action_success($oOrder);
        if(!empty($iResult)) {
            echo $this->oDibsModel->api_dibs_getFatalErrorPage($iResult);
            exit();
        }
        else {
            Mage::app()->getFrontController()->getResponse()->setRedirect(
                $this->oDibsModel->helper_dibs_tools_url('checkout/onepage/success')
            );
        }
    }
    
    public function callbackAction() {
        $oOrder = Mage::getModel('sales/order');
        $this->oDibsModel->api_dibs_action_callback($oOrder);
    }
    
    /**
     * When a customer cancel payment from dibs.
     */
    public function cancelAction() {
    	$session = Mage::getSingleton('checkout/session');
        $session->setQuoteId($session->getDibspwStandardQuoteId(true));
	$fields = array();

        // Save order comment
      	$oOrder = Mage::getModel('sales/order');
		
	if (isset($_POST['orderid'])) {
            $oOrder->loadByIncrementId((int)$_REQUEST['orderid']);
            $oOrder->registerCancellation($this->__('DIBSPW_LABEL_20'));
            $oOrder->save();

            // Add items back on stock (if used)
            $this->oDibsModel->removeFromStock();
            $this->oDibsModel->api_dibs_action_cancel();
            
	}
        // Give back cart to customer for new attempt to buy
        Mage::app()->getFrontController()->getResponse()->setRedirect(
                $this->oDibsModel->helper_dibs_tools_url('checkout/cart')
        );
    }
    
    protected function _expireAjax(){
        if (!Mage::getSingleton('checkout/session')->getQuote()->hasItems()) {
            $this->getResponse()->setHeader('HTTP/1.1','403 Session Expired');
            exit;
        }
    }

    public function addToStock() {
      	// Load the session object
      	$session = Mage::getSingleton('checkout/session');
      	$session->setDibspwStandardQuoteId($session->getQuoteId());

        $oOrder = Mage::getModel('sales/order');
        $oOrder->loadByIncrementId($session->getLastRealOrderId());
      
        // add items back on stock
        // Put the order back on stock as it is not yet paid!
        // http://www.magentocommerce.com/wiki/groups/132/protx_form_-_subtracting_stock_on_successful_payment

    	if (((int)$this->oDibsModel->getConfigData('handlestock')) == 1) {
            if(!isset($_SESSION['stock_removed']) || 
               $_SESSION['stock_removed'] != $session->getLastRealOrderId()) {
                /* Put the stock back on, we don't want it taken off yet */
                $items = $oOrder->getAllItems(); // Get all items from the order
                if ($items) {
                    foreach($items as $item) {
                        $quantity = $item->getQtyOrdered(); // get Qty ordered
                        $product_id = $item->getProductId(); // get it's ID
                        // Load the stock for this product
                        $stock = Mage::getModel('cataloginventory/stock_item')
                                 ->loadByProduct($product_id);
                        $stock->setQty($stock->getQty()+$quantity); // Set to new Qty            
                        $stock->save(); // Save
                        continue;                        
                    }
                } 
           
                // Flag so that stock is only updated once!
                $_SESSION['stock_removed'] = $session->getLastRealOrderId();

            }
        }
    }
}