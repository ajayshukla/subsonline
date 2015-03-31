<?php

require 'app/code/core/Mage/Catalog/controllers/ProductController.php';
        
class Submarine_ProductView_ProductController extends Mage_Catalog_ProductController {

    public function compositionAction(){
        $productId = $this->getRequest()->getParam('id');
        // Prepare helper and params
        $viewHelper = Mage::helper('catalog/product_view');
 
        $params = new Varien_Object();
        $params->setCategoryId(false);
        $params->setSpecifyOptions(false);
        
        $oProductHelper = new Mage_Catalog_Helper_Product();
        $oProductSimple = $oProductHelper->getProduct($productId, null, 'id');
        
        $aSKU = explode('-', $oProductSimple->getSKU());
        $oProductBundle = Mage::getModel('catalog/product');
        $productId = $oProductBundle->getIdBySku($aSKU[0]);

        // Render page
        try {
            $viewHelper->prepareAndRender($productId, $this, $params);
        } catch (Exception $e) {
            if ($e->getCode() == $viewHelper->ERR_NO_PRODUCT_LOADED) {
                if (isset($_GET['store'])  && !$this->getResponse()->isRedirect()) {
                    $this->_redirect('');
                } elseif (!$this->getResponse()->isRedirect()) {
                    $this->_forward('noRoute');
                }
            } else {
                Mage::logException($e);
                $this->_forward('noRoute');
            }
        }
    }
    
}

?>
