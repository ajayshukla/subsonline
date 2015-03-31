<?php
class dibs_pw_helpers extends dibs_pw_helpers_cms implements dibs_pw_helpers_interface {

    public static $bTaxAmount = true;
    
    
    /**
     * Process write SQL query (insert, update, delete) with build-in CMS ADO engine.
     * 
     * @param string $sQuery 
     */
    function helper_dibs_db_write($sQuery) {
        $oWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        return $oWrite->query($sQuery);
    }
    
    /**
     * Read single value ($sName) from SQL select result.
     * If result with name $sName not found null returned.
     * 
     * @param string $sQuery
     * @param string $sName
     * @return mixed 
     */
    function helper_dibs_db_read_single($sQuery, $sName) {
	$oRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $mResult = $oRead->fetchRow($sQuery);
        return isset($mResult[$sName]) ? $mResult[$sName] : null;
    }
    
    /**
     * Return settings with CMS method.
     * 
     * @param string $sVar
     * @param string $sPrefix
     * @return string 
     */
    function helper_dibs_tools_conf($sVar, $sPrefix = 'DIBSPW_') {
        return $this->getConfigData($sPrefix . $sVar);
    }
    
    /**
     * Return CMS DB table prefix.
     * 
     * @return string 
     */
    function helper_dibs_tools_prefix() {
        return Mage::getConfig()->getTablePrefix();
    }
    
    /**
     * Returns text by key using CMS engine.
     * 
     * @param type $sKey
     * @return type 
     */
    function helper_dibs_tools_lang($sKey, $sType = 'msg') {
        return Mage::helper('dibspw')->__("dibspw_txt_" . $sType . "_" . $sKey);
    }

    /**
     * Get full CMS url for page.
     * 
     * @param string $sLink
     * @return string 
     */
    function helper_dibs_tools_url($sLink) {
        return Mage::getUrl($sLink, array('_secure' => true));
    }
    
    /**
     * Build CMS order information to API object.
     * 
     * @param mixed $mOrderInfo
     * @param bool $bResponse
     * @return object 
     */
    function helper_dibs_obj_order($mOrderInfo, $bResponse = FALSE) {
        if($bResponse === TRUE) {
            $mOrderInfo->loadByIncrementId((int)$_POST['orderid']);
        }
        
        return (object)array(
                    'orderid'  => $mOrderInfo->getRealOrderId(),
                    'amount'   => $mOrderInfo->getTotalDue(),
                    'currency' => $this->api_dibs_get_currencyValue(
                                       $mOrderInfo->getOrderCurrency()->getCode()
                                  )
               );
    }
    
    /**
     * Build CMS each ordered item information to API object.
     * 
     * @param mixed $mOrderInfo
     * @return object 
     */
    function helper_dibs_obj_items($mOrderInfo) {
        $aItems = array();
        foreach($mOrderInfo->getAllItems() as $oItem) {
            $aItems[] = (object)array(
                'id'    => $oItem->getProductId(),
                'name'  => $oItem->getName(),
                'sku'   => $oItem->getSku(),
                'price' => $oItem->getPrice(),
                'qty'   => $oItem->getQtyOrdered(),
                'tax'   => $oItem->getTaxAmount() / $oItem->getQtyOrdered()
            );
        }
        return $aItems;
    }
    
    /**
     * Build CMS shipping information to API object.
     * 
     * @param mixed $mOrderInfo
     * @return object 
     */
    function helper_dibs_obj_ship($mOrderInfo) {
        return (object)array(
            'id'    => 'shipping0',
            'name'  => 'Shipping Cost',
            'sku'   => '',
            'price' => isset($mOrderInfo['shipping_amount']) ?
                             $mOrderInfo['shipping_amount'] : (int)0,
            'qty'   => 1,
            'tax'   => isset($mOrderInfo['shipping_tax_amount']) ? 
                             $mOrderInfo['shipping_tax_amount'] : (int)0
        );
    }
    
    /**
     * Build CMS customer addresses to API object.
     * 
     * @param mixed $mOrderInfo
     * @return object 
     */
    function helper_dibs_obj_addr($mOrderInfo) {
        $aShipping = $mOrderInfo->getShippingAddress();
        $aBilling  = $mOrderInfo->getBillingAddress();

        return (object)array(
            'shippingfirstname'  => $aShipping['firstname'],
            'shippinglastname'   => $aShipping['lastname'],
            'shippingpostalcode' => $aShipping['postcode'],
            'shippingpostalplace'=> $aShipping['city'],
            'shippingaddress2'   => $aShipping['street'],
            'shippingaddress'    => $aShipping['country_id'] . " " . 
                                    $aShipping['region'],
            
            'billingfirstname'   => $aBilling['firstname'],
            'billinglastname'    => $aBilling['lastname'],
            'billingpostalcode'  => $aBilling['postcode'],
            'billingpostalplace' => $aBilling['city'],
            'billingaddress2'    => $aBilling['street'],
            'billingaddress'     => $aBilling['country_id'] . " " . 
                                    $aBilling['region'],
            
            'billingmobile'      => $aBilling['telephone'],
            'billingemail'       => $mOrderInfo['customer_email']
        );
    }
    
    /**
     * Returns object with URLs needed for API, 
     * e.g.: callbackurl, acceptreturnurl, etc.
     * 
     * @param mixed $mOrderInfo
     * @return object 
     */
    function helper_dibs_obj_urls($mOrderInfo = null) {
        return (object)array(
                    'acceptreturnurl' => "Dibspw/Dibspw/success",
                    'callbackurl'     => "Dibspw/Dibspw/callback",
                    'cancelreturnurl' => "Dibspw/Dibspw/cancel",
                    'carturl'         => "customer/account/index"
                );
    }
    
    /**
     * Returns object with additional information to send with payment.
     * 
     * @param mixed $mOrderInfo
     * @return object 
     */
    function helper_dibs_obj_etc($mOrderInfo) {
        return (object)array(
                    'sysmod'      => 'mgn1_4_1_2',
                    'callbackfix' => $this->helper_dibs_tools_url("Dibspw/Dibspw/callback")
                );
    }
    
    function helper_dibs_hook_callback($oOrder) {
        $session = Mage::getSingleton('checkout/session');
        $session->setQuoteId($session->getDibspwStandardQuoteId(true));            
            
        if (((int)$this->helper_dibs_tools_conf('sendmailorderconfirmation', '')) == 1) {
            $oOrder->sendNewOrderEmail();
        }
            
	$this->removeFromStock();
        $this->setOrderStatusAfterPayment();
        $session->setQuoteId($session->getDibspwStandardQuoteId(true));
    }
}
?>