<?php
class dibs_pw_helpers_cms extends Mage_Payment_Model_Method_Abstract {   
    protected $_code  = 'Dibspw';
    protected $_formBlockType = 'Dibspw_Dibspw_block_form';
    protected $_infoBlockType = 'Dibspw_Dibspw_block_info';
    protected $_canUseInternal = false;
    protected $_canUseForMultishipping = false;
    
    public function cms_dibs_getOrderInfo() {
        $aPayInfo = array();
        $bMailing = false;
        $this->api_dibs_checkTable();
        $oOrder = Mage::registry('current_order');
        if($oOrder === NULL) {
            if (isset($_POST['orderid'])) {
                $oOrder = Mage::getModel('sales/order')->loadByIncrementId($_POST['orderid']);
            }
            $bMailing = true;
        } 
        if($oOrder !== NULL &&  is_callable(array($oOrder, 'getIncrementId'))) {
            $iOid = $oOrder->getIncrementId();
            if(!empty($iOid)) {
                $oRead = Mage::getSingleton('core/resource')->getConnection('core_read');
                $aRow = $oRead->fetchRow("SELECT `status`, `transaction`, `paytype`, `fee` FROM `" . 
                                         Mage::getConfig()->getTablePrefix() . 
                                         dibs_pw_api::api_dibs_get_tableName() .
                                        "` WHERE `orderid` = " . $iOid . " LIMIT 1;");
                if(count($aRow) > 0) {
                    if($aRow['status'] == 'ACCEPTED') {
                        if($aRow['transaction'] != '0') {
                            $aPayInfo[Mage::helper('dibspw')->__('DIBSPW_LABEL_8')] = $aRow['transaction'];
                        }
                       
                        if($bMailing === FALSE) {
                            if(!empty($aRow['paytype'])) {
                                $aPayInfo[Mage::helper('dibspw')->__('DIBSPW_LABEL_12')] = $aRow['paytype'];
                            }
                        
                            if(!empty($aRow['fee'])) {
                                $aPayInfo[Mage::helper('dibspw')->__('DIBSPW_LABEL_11')] = 
                                            $oOrder->getOrderCurrencyCode() . " " . 
                                            number_format(((int) $aRow['fee']) / 100, 2, ',', ' ');
                            }
                        }
                    }
                    else $aPayInfo[Mage::helper('dibspw')->__('DIBSPW_LABEL_25')] = Mage::helper('dibspw')->__('DIBSPW_LABEL_19');
                }
            }
        }
        
        return $aPayInfo;
    }    
    
    public function cms_dibs_getAdminOrderInfo() {
        $res = array();
        $this->api_dibs_checkTable();
        $oOrder = Mage::registry('current_order');
        if($oOrder !== NULL &&  is_callable(array($oOrder, 'getIncrementId'))) {
            $iOid = $oOrder->getIncrementId();
            if(!empty($iOid)) {
                $read = Mage::getSingleton('core/resource')->getConnection('core_read');
                $row = $read->fetchRow("SELECT `status`, `transaction`, `amount`, `currency`, `fee`, 
                                `paytype`, `ext_info` FROM " . Mage::getConfig()->getTablePrefix() .
                                dibs_pw_api::api_dibs_get_tableName() . "
                                WHERE orderid = " . $iOid . " LIMIT 1;");

                if(count($row) > 0) {
                    if($row['status'] == 'ACCEPTED') {
                        $row['ext'] = (isset($row['ext_info']) && $row['ext_info'] != NULL) ?
                                      unserialize($row['ext_info']) : array();
                        
                        if(!empty($row['transaction'])) {
                            $res[Mage::helper('dibspw')->__('DIBSPW_LABEL_8')] = $row['transaction'];
                        }

                        if(!empty($row['amount'])) {
                            $res[Mage::helper('dibspw')->__('DIBSPW_LABEL_9')] = $oOrder->getOrderCurrencyCode() . 
                                    " " . number_format(((int) $row['amount']) / 100, 2, ',', ' ');
                        }

                        if(!empty($row['currency'])) {
                            $res[Mage::helper('dibspw')->__('DIBSPW_LABEL_10')] = $row['currency'];
                        }

                        if(!empty($row['fee'])) {
                            $res[Mage::helper('dibspw')->__('DIBSPW_LABEL_11')] = $oOrder->getOrderCurrencyCode() . 
                                    " " . number_format(((int) $row['fee']) / 100, 2, ',', ' ');
                        }

                        if(!empty($row['paytype'])) {
                            $res[Mage::helper('dibspw')->__('DIBSPW_LABEL_12')] = $row['paytype'];
                        }
                    
                        if($row['ext']['acquirer'] != '0') {
                            $res[Mage::helper('dibspw')->__('DIBSPW_LABEL_16')] = $row['ext']['acquirer'];
                        }

                        if(isset($row['ext']['enrolled']) && $row['ext']['enrolled'] != '0') {
                            $res[Mage::helper('dibspw')->__('DIBSPW_LABEL_17')] = $row['ext']['enrolled'];
                        }

                        $res[Mage::helper('dibspw')->__('DIBSPW_LABEL_25')] = Mage::helper('dibspw')->__('DIBSPW_LABEL_18') . 
                                ': <a href="https://payment.architrade.com/admin/">https://payment.architrade.com/admin/</a>';
                    }
                    else $res[Mage::helper('dibspw')->__('DIBSPW_LABEL_25')] = Mage::helper('dibspw')->__('DIBSPW_LABEL_19');
                }
            }
        }
        
        return $res;
    }
    
    public function cms_get_imgHtml($sLogo) {
        $sImgUrl = Mage::getDesign()->getSkinUrl('images/Dibspw/Dibspw/' . 
                                                 preg_replace("/(\(|\)|_)/s", "",
                                                 strtolower($sLogo)) . '.gif');
        return (file_exists("." . strstr($sImgUrl, "/skin/"))) ?
               '<img src="' . $sImgUrl . '" alt="' . htmlentities($sLogo) . '" />' : "";
    }

    public function setOrderStatusAfterPayment(){
	$order = Mage::getModel('sales/order');
	$order->loadByIncrementId($_REQUEST['orderid']);
	$order->setState($this->getConfigData('order_status_after_payment'),
                         true,
                         Mage::helper('dibspw')->__('DIBSPW_LABEL_22'));

	$order->save();
    }
    
    // Remove from stock (if used)
    public function removeFromStock() {
    	// Load the session object
      	$session = Mage::getSingleton('checkout/session');
     	$session->setDibspwStandardQuoteId($session->getQuoteId());
      
      	// Load the order object
	$order = Mage::getModel('sales/order');
	$order->loadByIncrementId($_POST['orderid']);
      
// remove items from stock
// http://www.magentocommerce.com/wiki/groups/132/protx_form_-_subtracting_stock_on_successful_payment
        if (((int)$this->getConfigData('handlestock')) == 1) {
            $items = $order->getAllItems(); // Get all items from the order
            if ($items) {
                foreach($items as $item) {
                    $quantity = $item->getQtyOrdered(); // get Qty ordered
                    $product_id = $item->getProductId(); // get it's ID
                    // Load the stock for this product
                    $stock = Mage::getModel('cataloginventory/stock_item')
                             ->loadByProduct($product_id);
                    $stock->setQty($stock->getQty()-$quantity); // Set to new Qty            
                    $stock->save(); // Save
                    continue;                        
                }
            }
        }
    }
}
?>
